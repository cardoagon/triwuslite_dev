<?php


class CLWxcli_lserv extends FS_lista
                 implements IPanel_cxcli2 {

  public $id_site = null;

  public function __construct() {
    parent::__construct("lserv", new CLWxcliS_ehtml());

    $this->selectfrom = "
select b.pago_momento, a.ano, a.numero, a.id_articulo, a.k_aux, a.nome,
       DATE_FORMAT(a.servizo_data, '%d-%m-%Y') as dini, 
       DATE_FORMAT( DATE_ADD(a.servizo_data, INTERVAL a.servizo_dias DAY), '%d-%m-%Y' ) as dfin,
       a.servizo_dias, a.servizo_renove,
       (DATE_ADD(a.servizo_data, INTERVAL a.servizo_dias DAY) > now()) as envigor
  from pedido_articulo a inner join pedido b on (a.id_site = b.id_site and a.ano = b.ano and a.numero = b.numero)";
             
    $this->orderby    = "envigor DESC, a.ano DESC, a.numero DESC ";

    $this->limit_f    = 10;
  }

  public function id() {     //* IMPLEMENTS IPanel_cxcli2.id();
    return $this->nome;
  }

  public function titulo() { //* IMPLEMENTS IPanel_cxcli2.titulo();
   
    return Msx::$cxcli[$this->pai->idioma->codigo]["panel_3"]["t"];

  }

  public function notas() {  //* IMPLEMENTS IPanel_cxcli2.notas();
  
    return Msx::$cxcli[$this->pai->idioma->codigo]["panel_3"]["n"];

  }

  public function post_u(FGS_usuario $u = null) { //* IMPLEMENTS IPanel_cxcli2.post_u();
    if ($u == null     ) return;
    if (!$u->validado()) return;
    
    //* vincula pedidos non vinculados.
    Pedido_obd::pon_cliente(new FS_cbd(), $u, false);
    
    //* devolve lista de pedidos.
    $this->id_site = $u->atr("id_site")->valor;
               
    $this->where   = "( a.id_site = {$this->id_site} ) and ( a.tipo = 's' ) and 
                      ( not b.pago_momento is null ) and ( not a.servizo_permisos is null ) and ( not a.servizo_dias is null ) and 
                      ( b.id_cliente = " . $u->atr("id_usuario")->valor . " )";
    
//~ echo $this->sql_vista();
  }

  public function tipo_btnr() {  //* IMPLEMENTS IPanel_cxcli2.tipo_btnr();
    return 1;
  }

  public function operacion(EstadoHTTP $e) {
   
    $evento = $e->evento();

    //~ echo $evento->html();

    if ($this->control_fslevento($evento, "chrenove")) return $this->operacion_chrenove($e);


    return parent::operacion($e);
  }

  public function operacion_bCancelar(IEfspax_xestor $e) {} //* IMPLEMENTS IPanel_cxcli2.operacion_bCancelar();

  public function operacion_bAceptar(IEfspax_xestor $e) {} //* IMPLEMENTS IPanel_cxcli2.operacion_bAceptar();

  protected function html_baleiro() {
    return "<div class='lista_baleira txt_adv'>No hay servicios asociados.</div>";
  }

  public function operacion_chrenove(IEfspax_xestor $e):IEfspax_xestor {
    list($ano, $numero, $id_articulo) = explode("-", $e->evento()->subnome(0));
    
    
    $cbd = new FS_cbd();
    
    $pa = Pedido_articulo_obd::inicia($cbd, $this->id_site, $ano, $numero, $id_articulo);  
    
    
    if (!$pa->update_renove($cbd)) {
      $e->post_msx("Error. Sucedió un error al actualizar la BD");
    }
    
    $this->preparar_saida($e->ajax());
    
    
    return $e;
  } 
}

//---------------------------------

final class CLWxcliS_ehtml extends FS_ehtml {
  public function __construct() {
    parent::__construct();

    $this->class_table = "lista_00";
    $this->style_table = "border: 1px #aaa solid;";
    $this->align       = "left";
    $this->width       = "95%";
    $this->cellspacing = 0;
    $this->cellpadding = 0;
    $this->border      = 0;
  }

  protected function cabeceira($df = null) {
    return "<tr>
              <th>#Servicio</th>
              <th>"   . $this->__lOrdenar_html("nome", "Nombre")     . "</th>
              <th>Desde</th>
              <th>Hasta</th>
              <th style='text-align:center;'>" . $this->__lOrdenar_html("envigor"       , "Activo" ) . "</th>
              <th style='text-align:center;'>" . $this->__lOrdenar_html("servizo_renove", "Renovar") . "</th>
            </tr>";
  }

  protected function linha_detalle($df, $f) {
    $ref = Pedido_obd::ref($f['ano'], $f['numero']) . Articulo_obd::ref($f['k_aux'], 3);
    
    return "<tr>
              <td>{$ref}</td>
              <td>{$f['nome']}</td>
              <td>{$f['dini']}</td>
              <td>{$f['dfin']}</td>
              <td align=center>" . $this->__chactivo($f) . "</td>
              <td align=center>" . $this->__chrenove($this, $f) . "</td>
            </tr>";
  }

  protected function totais() {
    return "<tr>
              <td align=center colspan=9>" . $this->__paxinador_html() . "</td>
            </tr>";
  }

  private static function __chactivo($f) {
    $ch = new Checkbox("chpagado", ($f['envigor'] != null));

    $ch->readonly = true;

    $ch->style("default" , "cursor:pointer; width:1em;");
    $ch->style("readonly", "cursor:initial; width:1em;");

    return $ch->html();
  }

  private static function __chrenove(CLWxcliS_ehtml $ehtml, $f) {
    if ($f['envigor'] == null) return "--";
    
    $ch = new Checkbox("chrenove", ($f['servizo_renove'] == 1));

    $ch->style("default" , "cursor:pointer; width:1em;");
    $ch->style("readonly", "cursor:initial; width:1em;");
    
    //~ $ch->envia_submit("onclick");
    $ch->envia_ajax("onclick");

    return $ehtml->__fslControl($ch, "{$f['ano']}-{$f['numero']}-{$f['id_articulo']}")->html();
  }
}

