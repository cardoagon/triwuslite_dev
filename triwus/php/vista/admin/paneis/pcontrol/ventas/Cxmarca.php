<?php

final class Cxmarca extends Componente implements ICPC {
  const ptw_0 = "ptw/paneis/pcontrol/ventas/marca/cxmarca_0.html";

  public $id_site = null;

  public function __construct($id_site, $id_almacen) {
    parent::__construct("cxmarca", self::ptw_0);

    $this->id_site = $id_site;

    $this->pon_obxeto(Panel_fs::__bmais("bAlta", "Añadir una marca", "&nbsp;&nbsp;Añadir&nbsp;&nbsp;"));

    $this->pon_obxeto( new Cxmarca_ficha($id_site) );
    $this->pon_obxeto( new Cxmarca_lista($id_site, $id_almacen) );
  }

  public function seleccionado() {
    return $this->obxeto("cmarcaf")->seleccionado();
  }

  public function cpc_miga() {
    return "marca";
  }

  public function operacion(EstadoHTTP $e) {
    if ($this->ptw == null) return null;

    if ($this->obxeto("bAlta")->control_evento()) {
      $this->obxeto("cmarcaf")->post_marca( new Articulo_marca_obd($this->id_site) );

      $this->preparar_saida( $e->ajax() );

      return $e;
    }

    if (($e_aux = $this->obxeto("cmarcal")->operacion($e)) != null) return $e_aux;

    if (($e_aux = $this->obxeto("cmarcaf")->operacion($e)) != null) return $e_aux;


    return null;
  }

  public function operacion_marca(Epcontrol $e, $id_marca = null) {
    $p = Articulo_marca_obd::inicia(new FS_cbd(), $this->id_site, $id_marca);

    $fmarca = $this->obxeto("cmarcaf");

    $fmarca->post_marca($p);

    $fmarca->preparar_saida($e->ajax());


    $this->obxeto("cmarcal")->preparar_saida($e->ajax());


    return $e;
  }

  public function operacion_fra(Epcontrol $e, $id_marca = null) {
    $p = Almacen_obd::inicia(new FS_cbd(), $this->id_site, $id_marca);

    $f = $this->obxeto("cmarcaffra");

    $f->post_marca($p);

    $f->preparar_saida($e->ajax());


    return $e;
  }

  public function operacion_buscador(Epcontrol $e, $where = null) {
    $this->obxeto("cmarcal")->limit_0 = 0;

    $this->obxeto("cmarcal")->__where($where);

    $this->obxeto("cmarcal")->preparar_saida($e->ajax());

//~ echo $this->obxeto("cmarcal")->sql_vista() . "<br>";

    return $e;
  }

}

//************************************

final class Cxmarca_ficha extends Componente
                       implements IXestorCEditor_imx {

  const ptw_0 = "ptw/paneis/pcontrol/ventas/marca/cxmarca_ficha.html";
  //~ const ptw_1 = "ptw/paneis/pcontrol/ventas/marca/cxmarca_ficha_alta.html";

  private $id_site;
  private $id_marca;

  private $url = null;

  public function __construct($id_site) {
    parent::__construct("cmarcaf", self::ptw_0);

    $this->visible = false;

    $this->id_site = $id_site;

    //~ $this->pon_obxeto(new Div("erro"));

    $this->pon_obxeto(Panel_fs::__text("nome"));

    $this->pon_obxeto(Panel_fs::__textarea("notas"));

    $this->obxeto("nome" )->style("default", "width: 100%; max-width: 55em;");

    $this->obxeto("notas")->maxlength = 555;
    $this->obxeto("notas")->style("default", "width: 100%; max-width: 55em; height: 9em;");


    $this->pon_obxeto(new CEditor_imx());

    $this->pon_obxeto(CEditor_imx::__logo());


    $this->pon_obxeto(Panel_fs::__baceptar());
    $this->pon_obxeto(Panel_fs::__bcancelar());

    //~ $this->obxeto("erro")->clase_css("default", "texto3_erro");

    $this->obxeto("bCancelar")->envia_ajax("onclick");
    $this->obxeto("bAceptar" )->envia_ajax("onclick");
  }

  public function seleccionado() {
    return $this->id_marca;
  }

  public function operacion(EstadoHTTP $e) {
    //~ echo $e->evento()->html();

    //~ $this->obxeto("erro")->post(null);

    $this->obxeto("ceditor_imx")->config_fimaxe($e);
    
    if ($this->obxeto("bAceptar" )->control_evento()) return $this->operacion_bAceptar ($e);
    if ($this->obxeto("bCancelar")->control_evento()) return $this->operacion_bCancelar($e);


    if ($this->obxeto("logo")->control_evento()) return $this->obxeto("ceditor_imx")->operacion_logo($e);

    if (($e_aux = $this->obxeto("ceditor_imx")->operacion($e)) != null) return $e_aux;


    return null;
  }

  public function post_marca(Articulo_marca_obd $a) {
    $this->visible = true;

    $this->id_marca = $a->atr("id_marca")->valor;

/*
    if ($this->id_marca == null) {
      $this->ptw = self::ptw_1;

      //~ $this->obxeto("cmarcau")->reset();
    }
    else
*/
      $this->ptw = self::ptw_0;


    $this->obxeto("nome" )->post( $a->atr("nome" )->valor );
    $this->obxeto("notas")->post( $a->atr("notas")->valor );


    $url = (($logo = $a->iclogo_obd(new FS_cbd())) != null)?$logo->url():Imaxe_obd::noimx;

    $this->post_url($url);
    
    $this->obxeto("ceditor_imx")->pon_src($url);
  }

  public function operacion_bCancelar(Epcontrol $e) {
    $this->visible = false;

    $this->id_marca = null;

    $this->pai->preparar_saida($e->ajax());

    return $e;
  }

  public function url(?string $k = null) {          //* IMPLEMENTA IXestorCEditor_imx
    if ($this->url != Imaxe_obd::noimx) return $this->url;

    return null;
  }

  public function post_url($url = null, ?string $k = null) { //* IMPLEMENTA IXestorCEditor_imx
    $this->url = $url;

    //~ $this->url = $this->obxeto("ceditor_imx")->pon_src($url);

    $this->obxeto("logo")->style("default" , CEditor_imx::__style_logo($this->url, false, "left"));
    $this->obxeto("logo")->style("readonly", CEditor_imx::__style_logo($this->url, true , "left"));
  }

  private function operacion_bAceptar(Epcontrol $e) {
    if (($erro = $this->validar()) != null) {
      $e->post_msx($erro);
      
      //~ $this->obxeto("erro")->post($erro);

      //~ $e->ajax()->pon( $this->obxeto("erro") );

      return $e;
    }

    $cbd = new FS_cbd();

    $cbd->transaccion(true);

    $a = $this->marca_obd($cbd);

    if (!$a->update($cbd, $this->url())) {
      $cbd->rollback();
      
      $e->post_msx("Sucedió un error al tratar de actualizar la BD.");

      return $e;
    }

    //~ $cbd->rollback();
    $cbd->commit();


    $this->post_marca($a);

    $this->pai->preparar_saida($e->ajax());


    return $e;
  }

  private function marca_obd(FS_cbd $cbd = null) {
    if ($cbd == null) $cbd = new FS_cbd();

    $a = Articulo_marca_obd::inicia($cbd, $this->id_site, $this->id_marca);

    $a->atr("nome" )->valor = trim( $this->obxeto("nome" )->valor() );
    $a->atr("notas")->valor = trim( $this->obxeto("notas")->valor() );
    
    if (($url_imx = $this->obxeto("ceditor_imx")->aceptar_imx()) != null) {
      $this->post_url( $url_imx );
    }

    return $a;
  }

  private function validar() {
    $erro = null;

    if (trim( $this->obxeto("nome" )->valor() ) == "") $erro .= "ERROR, debes teclear el nombre de la marca.<br />";

    if ($this->url() == null) $erro .= "ERROR, debes seleccionar el logotipo de la marca.<br />";

    return $erro;
  }
}

//************************************

final class Cxmarca_lista extends FS_lista {

  private $id_site;
  private $id_almacen;

  public function __construct($id_site, $id_almacen) {
    parent::__construct("cmarcal", new Cxmarca_ehtml());

    $this->id_site    = $id_site;
    $this->id_almacen = $id_almacen;

    $this->selectfrom = "select a.id_site, a.id_marca, a.nome, a.notas, a.id_logo from articulo_marca a";
    $this->orderby    = "a.id_marca";

    $this->__where();

//~ echo $this->sql_vista() . "<br>";

    $this->limit_f    = 10;
  }

  public function __where($where = null) {
    $this->where  = "a.id_site = {$this->id_site}";

    if ($this->id_almacen > 1) $this->where .= " and a.id_almacen = {$this->id_almacen}";

    if ($where == null) return;

    $this->where .= " and {$where}";
  }

  public function seleccionado() {
    return $this->pai->seleccionado();
  }

  public function operacion(EstadoHTTP $e) {
    $evento = $e->evento();

//~ echo $evento->html();

    if ($this->control_fslevento($evento, "lmarca")) return $this->pai->operacion_marca($e, $evento->subnome(0));
    if ($this->control_fslevento($evento, "borrar")) return $this->operacion_bborrar   ($e, $evento->subnome(0));


    return parent::operacion($e);
  }

  private function operacion_bBorrar(Epcontrol $e, $id_marca) {
    $cbd = new FS_cbd();

    $cbd->transaccion();

    $p = Articulo_marca_obd::inicia($cbd, $this->pai->id_site, $id_marca);

    if (!$p->delete($cbd)) {
      $cbd->rollback();
      
      $e->post_msx("Sucedió un error al tratar de actualizar la BD.");

      //~ $this->obxeto("erro")->post("Sucedió un error al tratar de actualizar la BD.");

      //~ $this->preparar_saida($e->ajax());

      return $e;
    }

    $cbd->commit();

    $this->control_limits_sup();

    if ($this->seleccionado() == $id_marca) return $this->pai->obxeto("cmarcaf")->operacion_bCancelar($e);

    $this->preparar_saida($e->ajax());

    return $e;
  }

  protected function html_baleiro() {
    if (!$this->visible) return "";

    return "<div class='lista_baleira txt_adv'>No se encontraron coincidencias.</div>";
  }
}

//-----------------------------------

final class Cxmarca_ehtml extends FS_ehtml {
  public function __construct() {
    parent::__construct();

    $this->class_table   = "lista_00";
    $this->style_table = "border: 1px #aaa solid;";
    $this->align       = "center";
    $this->width       = "100%";
    $this->cellspacing = 0;
    $this->cellpadding = 0;
    $this->border      = 0;
  }

  protected function cabeceira($df = null) {
    return "<tr>
              <th style='width:1px' ></th>
              <th style='width:11px'>"   . $this->__lOrdenar_html("id_marca", "&nbsp;#marca&nbsp;") . "</th>
              <th style='width:30px'>" . $this->__lOrdenar_html("nome", "Nombre") . "</th>
              <th>" . $this->__lOrdenar_html("notas", "Notas") . "</th>
              <th style='width:1px' style='text-align: center'>&nbsp;</th>
            </tr>";
  }

  protected function linha_detalle($df, $f) {
    $style_tr = ($this->xestor->seleccionado() != $f['id_marca'])?FS_ehtml::style_tr:FS_ehtml::style_tr_a;


    return "<tr {$style_tr} >
              <td>" . self::__logo($f) . "</td>
              <td>" . self::__lmarca($this, $f) . "</td>
              <td>{$f['nome']}</td>
              <td>{$f['notas']}</td>
              <td>" . self::__bborrar($this, $f) . "</td>
            </tr>";
  }

  protected function totais() {
    return "<tr style='background-color: #fff;'>
              <td align=center colspan=11 style='border-top: 1px solid #aaa;'>" . $this->__paxinador_html() . "</td>
            </tr>";
  }

  private static function __logo($f) {
    $cbd = new FS_cbd();

    $c = Articulo_marca_obd::inicia($cbd, $f['id_site'], $f['id_marca']);

    $url = (($logo = $c->iclogo_obd($cbd)) != null)?$logo->url():Imaxe_obd::noimx;


    return "<div style='background-image: url({$url});
                        width:  60px;
                        height: 40px;
                        margin: 2px;
                        background-size: cover;
                        background-repeat: no-repeat;
                        background-position: center center;' ></div>";
  }

  private static function __lmarca(Cxmarca_ehtml $ehtml, $f) {
    $b = new Div("lmarca", Articulo_obd::ref($f['id_marca'], 4));

    $b->clase_css("default", "lista_elemento_a");

    $b->title = "Editar marca";

    //~ $b->envia_ajax("onclick");
    $b->envia_submit("onclick");

    return $ehtml->__fslControl($b, $f['id_marca'])->html();
  }

  private static function __bborrar(Cxmarca_ehtml $ehtml, $f) {
    if ($ehtml->xestor->readonly) return "";

    //~ if ($f['id_marca'] == 1) return ""; //* nunca se pode borrar o almacén 1.

    $b = Panel_fs::__bsup("borrar", "", "Eliminar la marca {$f['nome']}");

    $m = "¿ Quieres borrar la marca {$f['nome']} ?";

    //~ $b->envia_ajax("onclick", $m);
    $b->envia_submit("onclick", $m);

    return $ehtml->__fslControl($b, $f['id_marca'])->html();
  }
}
