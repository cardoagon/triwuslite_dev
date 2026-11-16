<?php


class CLWxcli_lped extends FS_lista
                implements IPanel_cxcli2 {

  public $id_site = null;

  public function __construct() {
    parent::__construct("lped", new CLWxcliP_ehtml());

    $this->selectfrom = "select * from v_pedido";
    $this->orderby    = "ano DESC, numero DESC";

    $this->limit_f    = 10;
    
    $this->pon_obxeto( new CLWxcliP_solicFra() );
  }

  public function id() {     //* IMPLEMENTS IPanel_cxcli2.id();
    return $this->nome;
  }

  public function titulo() { //* IMPLEMENTS IPanel_cxcli2.titulo();
    return Msx::$cxcli[$this->pai->idioma->codigo]["lped"]["t"];
  }

  public function notas() {  //* IMPLEMENTS IPanel_cxcli2.notas();
    return Msx::$cxcli[$this->pai->idioma->codigo]["lped"]["n"];
  }

  public function post_u(FGS_usuario $u = null) { //* IMPLEMENTS IPanel_cxcli2.post_u();
    if ($u == null) return;
    
    //* vincula pedidos non vinculados.
    Pedido_obd::pon_cliente(new FS_cbd(), $u, false);
    
    //* devolve lista de pedidos.
    $this->id_site = $u->atr("id_site")->valor;

    $this->where   = "id_site = {$this->id_site} and id_cliente = " . $u->atr("id_usuario")->valor;
    
//~ echo $this->sql_vista();
  }

  public function tipo_btnr() {  //* IMPLEMENTS IPanel_cxcli2.tipo_btnr();
    return 1;
  }

  public function operacion(EstadoHTTP $e) {
    if (($e_aux = $this->obxeto("cxcli_lped_contacto")->operacion($e)) != null) return $e_aux;
    
    $evento = $e->evento();

    //~ echo $evento->html();

    if ($this->control_fslevento($evento, "lsolicita"   )) return $this->operacion_lsolicita($e, false );
    if ($this->control_fslevento($evento, "lresolicfra" )) return $this->operacion_lsolicita($e, true);
    if ($this->control_fslevento($evento, "lverfactura" )) return $this->operacion_lverfactura($e);
    if ($this->control_fslevento($evento, "lretomarpago")) return $this->operacion_lretomarpago($e);
    if ($this->control_fslevento($evento, "lpedido"     )) return $this->operacion_lverpedido($e);

    return parent::operacion($e);
  }

  public function operacion_bCancelar(IEfspax_xestor $e) {} //* IMPLEMENTS IPanel_cxcli2.operacion_bCancelar();

  public function operacion_bAceptar(IEfspax_xestor $e) {} //* IMPLEMENTS IPanel_cxcli2.operacion_bAceptar();

  private function operacion_lsolicita(IEfspax_xestor $e, bool $resolicita) {
    list($ano, $num) = explode("-", $e->evento()->subnome(0));
    
    $this->obxeto("cxcli_lped_contacto")->pon_up( $e->usuario(), $this->id_site, $ano, $num, $resolicita );
    
    $this->preparar_saida($e->ajax());
    
    return $e;
  }

  private function operacion_lretomarpago(IEfspax_xestor $e) {
    //* Empregaremos un obxeto do tipo Cambio_pago_obd 'virtual', para realizar esta operación.
    
    list($ano, $numero) = explode("-", $e->evento()->subnome(0));

    $c = new Cambio_pago_obd();

    $c->atr("id_site")->valor = $this->id_site ;
    $c->atr("ano"    )->valor = $ano;
    $c->atr("numero" )->valor = $numero ;

    $c->post_u( $e->usuario() );


    $cbd = new FS_cbd();

    $cbd->transaccion();

    if (!$c->operacion_click($cbd)) {
      $cbd->rollback();

      return $e;
    }

    $cbd->commit();

    
    $e->obxeto("cpagar2")->config_usuario( $e );


    $e->redirect("pago.php");
    

    return $e;
  }

  private function operacion_lverpedido(IEfspax_xestor $e) {
    list($ano, $numero) = explode("-", $e->evento()->subnome(0));

    $cbd = new FS_cbd();

    $p = Pedido_obd::inicia($cbd, $this->id_site, $ano, $numero);


    $ehtml = new CLista_pedido(true, "triwus/");

    $ehtml->__pedido($p);

    $p_nome = "pedido." . $e->evento()->subnome(0) . ".pdf";

    try {
      $html2pdf = new HTML2PDF('P', 'A4', 'es');
      $html2pdf->setDefaultFont('Arial');
      $html2pdf->writeHTML($ehtml->html());
    }
    catch(HTML2PDF_exception $e) {
      echo $e;
      exit;
    }

    $e->pon_mime($p_nome, "application/pdf", $html2pdf->Output('', true), null);

    return $e;
  }

  private function operacion_lverfactura(IEfspax_xestor $e) {
    list($ano, $numero) = explode("-", $e->evento()->subnome(0));
    
    $p_obd = Pedido_obd::inicia(new FS_cbd(), $this->id_site, $ano, $numero);
    
    $fra_ano = $p_obd->atr("factura_momento")->fvalor("Y");
    $fra_nom = $p_obd->atr("id_factura"     )->valor();
    $fra_pdf = Pedido_obd::ref($ano, $numero);
    
    $src = "triwus/" . TrwlConfig::priv . "/{$this->id_site}/facturas/{$fra_ano}/{$fra_pdf}.pdf";
    
    if (!is_file($src)) {
      $e->post_msx("ERROR, lo sentimos, pero no se encuentra la factura solicitada.");
      
      return $e;
    }

    $e->pon_mime("{$fra_nom}.pdf", "application/pdf", file_get_contents($src), filesize($src));

    return $e;
  }

  public static function validar_contacto_fra(Pedido_obd $p, FS_cbd $cbd = null) {
    if ($cbd == null) $cbd = new FS_cbd();


/*
    if (($u = $p->cliente($cbd)) == null)
      $c = $p->destino($cbd);
    else
      $c = $u->contacto_obd($cbd);
*/

    if (($u = $p->cliente($cbd)) == null) return "El usuario no está registrado.<br />";

    $c = $u->contacto_obd($cbd);

    $erro = null;

    if ($c->atr("cnif_d"    )->valor == null) $erro .= "Debes teclear el campo 'CIF / NIF / NIE', válido.<br />";
    if ($c->atr("nome"      )->valor == null) $erro .= "Debes teclear su 'Nombre'.<br />";
                            

    $e = $c->enderezo_obd();
    
    if ($e->atr("codtipovia")->valor == null) $erro .= "Debes teclear el 'Tipo de v&iacute;a'.<br />";
    if ($e->atr("via"       )->valor == null) $erro .= "Debes teclear el campo 'Nombre de la v&iacute;a'.<br />";
    if ($e->atr("num"       )->valor == null) $erro .= "Debes teclear el campo 'N&ordm;'.<br />";
    if ($e->atr("cp"        )->valor == null) $erro .= "Debes teclear el campo 'Cód. Postal'.<br />";
    if ($e->atr("localidade")->valor == null) $erro .= "Debes teclear la 'Localidad'.<br />";


    return $erro;
  }

  private function __contacto_fra(FS_cbd $cbd, $id_contacto) {
    $c = Contacto_obd::inicia($cbd, $id_contacto, "f");
    
    if ( !$c->baleiro() ) return $c; 
    
    return Contacto_obd::inicia($cbd, $id_contacto, "p");
  }

  protected function html_baleiro() {
    return "<div class='lista_baleira txt_adv'>" . Msx::$_pedidos[$this->pai->idioma->codigo] . "</div>";
  }

}

//---------------------------------

final class CLWxcliP_ehtml extends FS_ehtml {
  public function __construct() {
    parent::__construct();

    $this->class_table   = "lista_00";
    $this->style_table = "border: 1px #aaa solid;";
    $this->align       = "left";
    $this->width       = "95%";
    $this->cellspacing = 0;
    $this->cellpadding = 0;
    $this->border      = 0;
  }


  protected function inicia_saida() {
    return $this->xestor->obxeto("cxcli_lped_contacto")->html() . parent::inicia_saida();
  }

  protected function cabeceira($df = null) {
    return "<tr>
              <th></th>
              <th>"   . $this->__lOrdenar_html("ref", "Referencia")     . "</th>
              <th>" . $this->__lOrdenar_html("momento", "Fecha")      . "</th>
              <th style='text-align:right;'>" . $this->__lOrdenar_html("tpvpive", "Total")  . "</th>
              <th></th>
              <th style='text-align:center;'>Pagado</th>
              <th></th>
            </tr>";
  }

  protected function linha_detalle($df, $f) {
    return "<tr>
              <td>" . self::__iestado($f) . "</td>
              <td>" . self::__lpedido($this, $f) . "</td>
              <td>{$f['momento']}</td>
              <td align=right>" . self::__tpvpive($this, $f) . "</td>
              <td align=center></td>
              <td align=center>" . $this->__chpagado($f) . "</td>
              <td align=center>" . self::__lfactura($this, $f) . "</td>
            </tr>";
  }

  protected function totais() {
    return "<tr>
              <td align=center colspan=7>" . $this->__paxinador_html() . "</td>
            </tr>";
  }

  private static function __tpvpive(CLWxcliP_ehtml $ehtml, $f) {
    $cbd = new FS_cbd();

    $m = Articulo_obd::$a_moeda[$f['moeda']];

    $p = Pedido_obd::inicia($cbd, $ehtml->xestor->id_site, $f['ano'], $f['numero']);

    return number_format($p->calcula_total(-1, $cbd), 2, ".", ",") . "<small>&nbsp;{$m}</small>";
  }

  private static function __lpedido(CLWxcliP_ehtml $ehtml, $f) {
    $e = Pedido_obd::ref($f['ano'], $f['numero']);

    $l = new Link("lpedido", $e, "_blank");

    $l->clase_css("default", "lista_elemento_a");
    $l->style("default", "color: #0000ba");

    $l->title = "Ver hoja de pedido";

    $l->envia_mime("onclick");


    return $ehtml->__fslControl($l, "{$f['ano']}-{$f['numero']}")->html();
  }

  private static function __lfactura(CLWxcliP_ehtml $ehtml, $f) {
    $id = "lsolicita"; $m = ""; $t = ""; $onclick = "submit";

        if ($f['id_pago']      == Pedido_obd::pago_simulado) return "";
    elseif ($f['pago_momento'] == null) {
      $id      = "lretomarpago";
      $e       = "Retomar&nbsp;pago";
      $m       = "&iquest; Deseas retomar el pago del pedido ?";
      $t       = "Retomar el pago del pedido";
    }
    elseif ($f['id_factura']   == null) {
      $e       = "Solicitar&nbsp;factura";
      $t       = "Solicitar una factura de tu pedido";
      //~ $onclick = "ajax";
    }
    elseif ($f['id_factura']   == Pedido_obd::fra_solicita) {
      $id      = "lresolicfra";
      $e       = "Factura&nbsp;solicitada";
      $t       = "Volver a solicitar la factura de tu pedido";
      //~ $onclick = "ajax";
    }
    else { //* hai unha factura.
      $id      = "lverfactura";
      $e       = $f['id_factura'];
      $t       = "Descargar la factura de tu pedido";
      $onclick = "mime";
    }


    $l = new Link($id, $e, "_blank");

    $l->clase_css("default", "lista_elemento_a");
    $l->style("default", "color: #0000ba");

    $l->title = $t;

        if ($onclick == "ajax"  ) $l->envia_ajax  ("onclick", $m);
    elseif ($onclick == "mime"  ) $l->envia_mime  ("onclick");
    elseif ($onclick == "submit") $l->envia_submit("onclick", $m);


    return $ehtml->__fslControl($l, "{$f['ano']}-{$f['numero']}")->html();
  }

  private static function __iestado($f) {
    $s = new Div("icoestado", "&nbsp;");

    $s->title = $f['estado'];

        if ($f['estado'] == "Aceptado") $color_estado = "#F88602";
    elseif ($f['estado'] == "Denegado") $color_estado = "#ff0000";
    elseif ($f['estado'] == "Enviado")   {
      $color_estado = "#00ff00";

      if ($f['id_fpago'] == 3) $s->title = "Disponible";
    }
    else
      $color_estado = "transparent";

    $s->style("default", "width:11px; background-color: {$color_estado};");


    return $s->html();
  }

  private static function __chpagado($f) {
    $ch = new Checkbox("chpagado", ($f['pago_momento'] != null));

    $ch->readonly = true;

    $ch->style("default" , "cursor:pointer; width:1em;");
    $ch->style("readonly", "cursor:initial; width:1em;");

    return $ch->html();
  }
}


//*********************************


class CLWxcliP_solicFra extends Componente {
  
  private $s       = null;
  private $a       = null;
  private $n       = null;

  private $email_a = null;
  private $email_u = null;

  public function __construct() {
    parent::__construct("cxcli_lped_contacto", Refs::url("ptw/clogin/web/cxcli3LPed_contacto.html"));
    
        
    $this->visible = false;
    
    $this->pon_obxeto(new CContacto("f"));
    
    $this->obxeto("ccontacto")->ptw = Refs::url("ptw/clogin/web/cxcli3LPed_contacto_1.html");

    $this->pon_obxeto(new Param("msx_adv"));

    $this->pon_obxeto(Panel_fs::__baceptar ());
    $this->pon_obxeto(Panel_fs::__bcancelar());
    
    $this->obxeto("bAceptar" )->envia_ajax("onclick");
    $this->obxeto("bCancelar")->envia_ajax("onclick");
  }
  
  public function pon_up(FGS_usuario $u, int $s, int $a, int $n, bool $resolicita = false) {
    $cbd = new FS_cbd();
    
    $this->visible = true;

    $this->s = $s;
    $this->a = $a;
    $this->n = $n;
    
    $p = Pedido_obd::ref($a, $n);

    $this->email_u = $u->atr("email")->valor;
    $this->email_a = Site_obd::inicia($cbd, $s)->usuario_obd($cbd)->atr("login")->valor;


    $msx_adv = "&bull;&nbsp;Sucedió un error al iniciar la modal.";
    if ($resolicita) {
      $p_obd = Pedido_obd::inicia($cbd, $s, $a, $n);
      
      $us = $p_obd->atr("factura_momento")->fvalor("d-m-Y");
      
      $msx_adv = 
"&bull;&nbsp;Soliciar factura para el pedido: {$p}.
<br />
&bull;&nbsp;Última solicitud a {$us}.
<br />
&bull;&nbsp;Verifica tus datos de facturación y pulsa el botón Aceptar, para volver a solicitar una factura.";
    }
    else {
      $msx_adv = 
"&bull;&nbsp;Soliciar factura para el pedido: {$p}.
<br />
&bull;&nbsp;Verifica tus datos de facturación y pulsa el botón Aceptar, para solicitar una factura.";
    }
    
    //~ $this->obxeto("bAceptar")->visible = !$resolicita;
    //~ $this->obxeto("bVolver" )->visible = $resolicita;
    
    $this->obxeto("msx_adv")->post($msx_adv);
    
    
    $this->obxeto("ccontacto")->post_contacto( $u->contacto_obd_2("f") );
  }

  public function operacion(EstadoHTTP $e) {
    if ($this->obxeto("bCancelar")->control_evento()) return $this->operacion_bCancelar($e);
    if ($this->obxeto("bAceptar" )->control_evento()) return $this->operacion_baceptar ($e);
    //~ if ($this->obxeto("bVolver"  )->control_evento()) return $this->operacion_baceptar ($e);

    return null;
  }

  private function operacion_bCancelar(IEfspax_xestor $e) {
    $this->visible = false;
    
    $this->pai->preparar_saida($e->ajax());
    
    return $e;
  }

  private function operacion_bAceptar(IEfspax_xestor $e) {
    if (($erro = $this->obxeto("ccontacto")->validar()) != null) {
      $e->post_msx($erro);
      
      return $e;
    }
    
    $cbd = new FS_cbd();
    
    $cbd->transaccion();
    
    $c_f = $this->obxeto("ccontacto")->contacto_obd(null, true, $cbd);
    
    $ped = Pedido_obd::inicia($cbd, $this->s, $this->a, $this->n);
    
    if (!$ped->fra_solicita($cbd, $c_f)) {
      $cbd->rollback();
      
      $e->post_msx("Sucedió un error al intentar actualizar la BD.");
      
      return $e;
    }
    
    $cbd->commit();
    
    try {
      $this->enviar_email();
    }
    catch (Exception $e) {
      throw $e;
    }   
        
    $e->post_msx("La solicitud de factura ha sido enviada correctamente.", "m");
    
    return $this->operacion_bCancelar($e);
  }

  private function update(FS_cbd $cbd) {
    //* update contacto.
    if (!$this->obxeto("ccontacto")->update_contacto($cbd)) return false;
    
    //* update pedido.
    $sql = "update pedido set id_factura = '" . Pedido_obd::fra_solicita . "' where id_site = {$this->s} and ano = {$this->a} and numero = {$this->n}";
    
    
    return $cbd->executa( $sql );
  }

  private function enviar_email() {
    $p = Pedido_obd::ref($this->a, $this->n);
    
    $texto = "
<pre>
Hola,

el usuario: <b>{$this->email_u}</b> a solicitado una factura para el pedido: <b>{$p}</b>.


</pre> 
";

    $ticket = Elmt_contacto_obd::__colle_ticket($this->s);

   // $m = new XMail2(false);
    $m = new XMail3($this->s); 

    (($_x = $m->_config()) != [])
    ? $m->pon_rmtnt($_x['email'])
    : $m->pon_rmtnt($this->email_a);

    try {
      $m->pon_asunto   ("solicitud factura: #id = {$ticket}");

      //* copia usuario
      $m->pon_direccion($this->email_u);
      $m->pon_cco      ($this->email_a);
      $m->pon_corpo    ($texto);

      $m->enviar();
    }
    catch (Exception $e) {
      throw $e;
    }
  }
}
