<?php

final class Cxalmacen extends Componente implements ICPC {
  const ptw_0 = "ptw/paneis/pcontrol/ventas/almacen/cxalmacen_0.html";

  public $id_site = null;

  public function __construct($id_site, $id_almacen) {
    parent::__construct("calmacen", self::ptw_0);


    $this->id_site = $id_site;

    $this->pon_obxeto(Panel_fs::__bmais("bAlta", "Añadir un nuevo almacén", "&nbsp;&nbsp;Añadir&nbsp;&nbsp;"));

    $this->pon_obxeto( new Cxalmacen_busca($id_site, $id_almacen) );
    $this->pon_obxeto( new Cxalmacen_ficha($id_site, $id_almacen) );
    $this->pon_obxeto( new Cxalmacen_lista($id_site, $id_almacen) );

    $this->pon_obxeto( new Cxalmacen_ficha_fra($id_site) );

    if ($id_almacen > 1)
      $this->obxeto("bAlta")->visible = false;
    else
      $this->obxeto("bAlta")->envia_ajax("onclick");
  }

  public function seleccionado() {
    return $this->obxeto("calmacenf")->seleccionado();
  }

  public function cpc_miga() {
    return "almacén";
  }

  public function operacion(EstadoHTTP $e) {
    if ($this->ptw == null) return null;

    if ($this->obxeto("bAlta")->control_evento()) {
      $this->obxeto("calmacenf")->post_almacen( new Almacen_obd($this->id_site) );

      $this->preparar_saida( $e->ajax() );

      return $e;
    }

    if (($e_aux = $this->obxeto("calmacen_lista")->operacion($e)) != null) return $e_aux;

    if (($e_aux = $this->obxeto("calmacenb")->operacion($e)) != null) return $e_aux;
    if (($e_aux = $this->obxeto("calmacenf")->operacion($e)) != null) return $e_aux;

    if (($e_aux = $this->obxeto("calmacenffra")->operacion($e)) != null) return $e_aux;


    return null;
  }

  public function operacion_almacen(Epcontrol $e, $id_almacen = null) {
    $p = Almacen_obd::inicia(new FS_cbd(), $this->id_site, $id_almacen);

    $falmacen = $this->obxeto("calmacenf");

    $falmacen->post_almacen($p);

    $falmacen->preparar_saida($e->ajax());


    $this->obxeto("calmacen_lista")->preparar_saida($e->ajax());


    return $e;
  }

  public function operacion_fra(Epcontrol $e, $id_almacen = null) {
    $p = Almacen_obd::inicia(new FS_cbd(), $this->id_site, $id_almacen);

    $f = $this->obxeto("calmacenffra");

    $f->post_almacen($p);

    $f->preparar_saida($e->ajax());


    return $e;
  }

  public function operacion_buscador(Epcontrol $e, $where = null) {
    $this->obxeto("calmacen_lista")->limit_0 = 0;

    $this->obxeto("calmacen_lista")->__where($where);

    $this->obxeto("calmacen_lista")->preparar_saida($e->ajax());

//~ echo $this->obxeto("calmacen_lista")->sql_vista() . "<br>";

    return $e;
  }

}


//************************************


final class Cxalmacen_busca extends Componente {
  const ptw_0 = "ptw/paneis/pcontrol/ventas/almacen/cxalmacen_busca.html";

  public function __construct($id_site, $id_almacen) {
    parent::__construct("calmacenb", self::ptw_0);


    $this->pon_obxeto(Panel_fs::__bbusca   ("bBuscar", null, null));
    $this->pon_obxeto(Panel_fs::__bcancelar(null, null, "bLimpa"));

    $this->pon_obxeto(Cxarticulos_busca::__salmacen($id_site, $id_almacen));

    $this->pon_obxeto(Panel_fs::__text("txt", 11, 111, " "));

    $this->obxeto("txt")->clase_css("default", "tbuscador"  );

    $this->obxeto("bLimpa")->visible = false;
    $this->obxeto("bLimpa")->envia_ajax("onclick");

    $this->obxeto("bBuscar")->envia_ajax("onclick");
  }

  public function operacion(EstadoHTTP $e) {
    if ($this->obxeto("bBuscar")->control_evento()) return $this->operacion_bBuscar($e);
    if ($this->obxeto("bLimpa" )->control_evento()) return $this->operacion_bLimpa ($e);


    return null;
  }

  private function operacion_bBuscar(EstadoHTTP $e, $blimpa = true) {
    $this->obxeto("bLimpa")->visible = $blimpa;

    $this->preparar_saida($e->ajax());

    return $this->pai->operacion_buscador($e, $this->__where());
  }

  private function operacion_bLimpa(EstadoHTTP $e) {
    $this->obxeto("txt")->post("");

    if ( !$this->obxeto("salmacen" )->readonly ) $this->obxeto("salmacen")->post("**");

    return $this->operacion_bBuscar($e, false);
  }

  private function __where() {
    $wh_txt = Valida::buscador_txt2sql($this->obxeto("txt")->valor(), array("a.nome", "a.email"));

    if ($wh_txt == null) $wh_txt = "(1=1)";

    $wh_almacen = "(1=1)";
    if (($almacen = $this->obxeto("salmacen")->valor()) != "**") $wh_almacen = "(a.id_almacen = '{$almacen}')";

    $where = "{$wh_txt} and {$wh_almacen}";


    return str_replace(" and (1=1)", "", $where);
  }
}


//************************************


final class Cxalmacen_ficha extends Componente {
  const ptw_0 = "ptw/paneis/pcontrol/ventas/almacen/cxalmacen_ficha.html";
  const ptw_1 = "ptw/paneis/pcontrol/ventas/almacen/cxalmacen_ficha_alta_0.html";
  const ptw_2 = "ptw/paneis/pcontrol/ventas/almacen/cxalmacen_ficha_alta_1.html";

  public $id_site;
  public $id_almacen;

  public function __construct($id_site, $id_almacen) {
    parent::__construct("calmacenf", self::ptw_0);


    $this->visible = false;

    $this->id_site = $id_site;


    $this->pon_obxeto(new CEditor_2("CES_editor"));

    $this->pon_obxeto(new Cxalmacen_usuario($id_site));

    $this->pon_obxeto(new Cxalmacen_contacto());


    $this->pon_obxeto(self::__legend("calmacensemana", "Días abierto"));
    $this->pon_obxeto(new Cxalmacen_semana());


    $this->pon_obxeto(self::__legend("calmacenfportes", " Portes: precios y tiempos"));
    $this->pon_obxeto(new Cxalmacen_portes($id_site, $id_almacen));

    $this->pon_obxeto(self::__pdominio($id_site));

    $this->pon_obxeto(self::__number("capacidade"    , "Capacidad del almacén"));
    $this->pon_obxeto(self::__number("comision_venta", "% comisión venta"     ));


    $this->pon_obxeto(new Checkbox("ch_recollida", false, "Habilitar la recogida de pedidos en la direción postal del almacén."));

    $this->pon_obxeto(Panel_fs::__baceptar());
    $this->pon_obxeto(Panel_fs::__bcancelar());

    $this->obxeto("bCancelar")->envia_ajax("onclick");
    //~ $this->obxeto("bAceptar" )->envia_ajax("onclick");


    if ($id_almacen > 1) {
      $this->obxeto("capacidade"    )->readonly = true;
      $this->obxeto("comision_venta")->readonly = true;
    }
  }

  public function seleccionado() {
    return $this->id_almacen;
  }

  public function operacion(EstadoHTTP $e) {
    //~ echo $e->evento()->html();

    if ($this->obxeto("bAceptar" )->control_evento()) return $this->operacion_bAceptar ($e);
    if ($this->obxeto("bCancelar")->control_evento()) return $this->operacion_bCancelar($e);

    if (($e_aux = $this->obxeto("calmacenfportes")->operacion($e)) != null) return $e_aux;

    if (($e_aux = $this->obxeto("calmacenu")->operacion($e)) != null) return $e_aux;

    if (($e_aux = $this->obxeto("ccontacto")->operacion($e)) != null) return $e_aux;

    if ($this->obxeto("legend_calmacensemana" )->control_evento()) return $this->operacion_legend($e, "calmacensemana");
    if ($this->obxeto("legend_calmacenfportes")->control_evento()) return $this->operacion_legend($e, "calmacenfportes");


    return null;
  }

  public function post_almacen(Almacen_obd $a) {
    $this->visible = true;

    $this->id_almacen = $a->atr("id_almacen")->valor;


    if ($this->id_almacen == null) {
      $this->ptw = self::ptw_1;

      $this->obxeto("calmacenu")->reset();
    }
    else
      $this->ptw = self::ptw_0;

    $this->obxeto("calmacenfportes")->post_almacen($a);
    $this->obxeto("calmacensemana" )->post_almacen($a);

    $this->obxeto("ch_recollida"   )->post    ( $a->atr("ch_recollida")->valor == 1 );

    $this->obxeto("comision_venta" )->post    ( $a->atr("comision_venta")->valor );
    $this->obxeto("capacidade"     )->post    ( $a->atr("capacidade"    )->valor );

    $this->obxeto("CES_editor"     )->post_txt( $a->atr("ces"           )->valor );


    $this->post_contacto( $a->contacto_obd() );
  }

  public function post_contacto(Contacto_obd $c) {
    $this->obxeto("ccontacto")->post_contacto( $c );
  }

  public function operacion_bCancelar(Epcontrol $e) {
    $this->visible = false;

    $this->id_almacen = null;

    $this->pai->preparar_saida($e->ajax());

    return $e;
  }

  public function preparar_saida(Ajax $a = null) {
    parent::preparar_saida($a);

    if ($a == null) return;

    $this->obxeto("ccontacto")->obxeto("atnotas")->preparar_saida($a);
    $this->obxeto("CES_editor")->preparar_saida($a);
  }

  private function operacion_bAceptar(Epcontrol $e) {
    if ($this->ptw == self::ptw_1) {
      //* neste caso debemos devolver a operación de buscar usuario rexistrado por email.

      return $this->obxeto("calmacenu")->operacion_bemail($e);
    }

    if (($erro = $this->validar()) != null) {
      $e->post_msx( $erro );

      return $e;
    }

    $cbd = new FS_cbd();

    $cbd->transaccion(true);

    $a    = $this->almacen_obd($cbd);
    $c    = $this->obxeto("ccontacto")->contacto_obd();
    $foto = $this->obxeto("ccontacto")->url();

    $c->atr("tipo")->valor = "a"; //* aseguramos que o tipo='a'. PAra altas, insertara o contacto tipo 'a'.

    if (!$a->update($cbd, $c, $foto)) {
      $cbd->rollback();

      $e->post_msx("Sucedió un error al actualizar la BD.");

      return $e;
    }

    //~ $cbd->rollback();
    $cbd->commit();

//~ echo "<pre>" . print_r($a->a_resumo(), true) . "</pre>";

    $this->post_almacen($a);

    $this->pai->preparar_saida($e->ajax());

    $this->obxeto("ccontacto")->obxeto("atnotas")->preparar_saida($e->ajax());
    $this->obxeto("CES_editor")->preparar_saida($e->ajax());

    return $e;
  }

  private function almacen_obd(FS_cbd $cbd = null) {
    if ($cbd == null) $cbd = new FS_cbd();

    $a = Almacen_obd::inicia($cbd, $this->id_site, $this->id_almacen);

    $a->atr("ch_recollida"  )->valor = ( $this->obxeto("ch_recollida")->valor() )?1:0;

    $a->atr("capacidade"    )->valor = round( $this->obxeto("capacidade"    )->valor() );
    $a->atr("comision_venta")->valor = round( $this->obxeto("comision_venta")->valor() );

    $a->atr("ces"           )->valor = $this->obxeto("CES_editor"           )->valor();

    $a = $this->obxeto("calmacenfportes")->completar_almacen($a);
    $a = $this->obxeto("calmacensemana" )->completar_almacen($a);


    return $a;
  }

  private function validar() {
    return $this->obxeto("ccontacto")->validar();
  }


  private function operacion_legend(Epcontrol $e, string $k) {
    $o = $this->obxeto($k);

    $o->visible = !$o->visible;

    $o->preparar_saida($e->ajax());

    return $e;
  }

  private static function __idalmacen() {
    $t = Panel_fs::__text("id_almacen", 11, 9, "000");

    $t->title    = "... N&uacute;mero que identifica el almacén";
    $t->readonly = true;


    return $t;
  }

  private static function __pdominio($id_site) {
    //~ $s = Site_obd::inicia(new FS_cbd(), $id_site);

    //~ if (($d = $s->atr("dominio")->valor) ==  null) $d = "sitio-sin-dominio.com";

    return new Param("pdominio", TrwlConfig::dominio);
  }

  public static function __number($id, $title) {
    $t = Panel_fs::__text($id, 5, 10, " ");

    $t->title = $title;

    $t->clase_css("default" , "number");
    $t->clase_css("readonly", "number");

    return $t;
  }


  private static function __legend(string $id, string $etq):Etiqueta {
    $b = new Etiqueta("legend_{$id}", $etq);

    $b->element = "legend";

    $b->clase_css("default" , "cxaf_legend");

    $b->pon_eventos("onclick", "cxaf_legeng_click(this)");

    return $b;
  }
}


//************************************


final class Cxalmacen_usuario extends Componente {
  const ptw_0 = "ptw/paneis/pcontrol/ventas/almacen/cxalmacen_usuario.html";

  private $id_site;
  private $id_contacto;

  public function __construct($id_site) {
    parent::__construct("calmacenu", self::ptw_0);

    $this->id_site = $id_site;

    $this->pon_obxeto(self::__temail());
    $this->pon_obxeto(self::__bemail());
  }

  public function reset() {
    $this->obxeto("temail"  )->post(null);
  }

  public function operacion(EstadoHTTP $e) {
//~ echo $e->evento()->html();

    if ($this->obxeto("bemail")->control_evento()) return $this->operacion_bemail($e);

    return null;
  }

  public function operacion_bemail(EstadoHTTP $e) {
    $this->id_contacto = null;

    $this->pai->ptw = Cxalmacen_ficha::ptw_1;


    $email = $this->obxeto("temail")->valor();

    if (!Valida::email($email)) {
      $e->post_msx("Teclea un email válido");

      return $e;
    }


    $sql = "select a.id_contacto, b.id_almacen
              from usuario a left join almacen b on (a.id_contacto = b.id_contacto)
             where (a.id_site = {$this->id_site}) and (a.login = '{$email}')";

    $cbd = new FS_cbd();

    $r = $cbd->consulta($sql);

    if (!$_r = $r->next()){
      $e->post_msx("No existe un usuario registrado con el email: {$email}");

      return $e;
    }

    if ($_r['id_almacen'] != null) {
      $e->post_msx("El usuario registrado, {$email}, ya gestiona el almacén {$_r['id_almacen']}.");

      return $e;
    }

    $c = Contacto_obd::inicia($cbd, $_r['id_contacto']);

    if ($c->baleiro()) {
      $e->post_msx("(2) No existe un usuario registrado con el email: {$email}.");

      return $e;
    }

    $this->pai->ptw = Cxalmacen_ficha::ptw_2;

    $this->pai->post_contacto($c, true);

    $this->id_contacto = $_r['id_contacto'];

    $this->pai->preparar_saida( $e->ajax() );

    return $e;
  }

  private function validar() {
    $erro = "";

    if ($this->id_contacto == null) return "Error, debes seleccionar un contacto válido.</br>";

    return $erro;
  }

  private static function __temail() {
    $s = new Email("temail");

    $s->title = "Teclea el email de un usuario registrado";

    $s->clase_css("default", "tbuscador");


    return $s;
  }

  private static function __bemail() {
    $b = Panel_fs::__bbusca("bemail", "Buscar", "Verifica email de usuario registrado");

    //~ $b->envia_ajax("onclick");
    $b->envia_submit("onclick");

    return $b;
  }
}


//************************************


final class Cxalmacen_contacto extends CContacto {
  public function __construct() {
    parent::__construct("a", "ccontacto", true);

    $this->ptw         = "ptw/paneis/pcontrol/ventas/almacen/cxalmacen_contacto.html";
    $this->_validar    = ["razon_social", "cnif_d", "email", "notas", "mobil"];
    $this->validar_url = true;


    $cenz = $this->obxeto("cenderezo");

    $cenz->ptw         = "ptw/paneis/pcontrol/ventas/almacen/cxalmacen_enderezo.html";
    $cenz->_validar    = ["codtipovia", "via", "num", "cp", "localidade", "id_prov", "id_concello"];
  }
}


//************************************


final class Cxalmacen_lista extends FS_lista {
   public $id_site;
   public $id_almacen;

  public function __construct($id_site, $id_almacen) {
    parent::__construct("calmacen_lista", new Cxalmacen_ehtml());

    $this->id_site     = $id_site;
    $this->id_almacen  = $id_almacen;

    $this->selectfrom = "select a.id_site, a.id_almacen, a.id_contacto, a.nome as almacen, a.email from v_almacen a";
    $this->orderby    = "a.id_almacen";

    $this->__where();

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

    if ($this->control_fslevento($evento, "lalmacen")) return $this->pai->operacion_almacen($e, $evento->subnome(0));
    if ($this->control_fslevento($evento, "lfra"))     return $this->pai->operacion_fra    ($e, $evento->subnome(0));
    if ($this->control_fslevento($evento, "borrar"))   return $this->operacion_bborrar     ($e, $evento->subnome(0));


    return parent::operacion($e);
  }

  private function operacion_bBorrar(Epcontrol $e, $id_almacen) {
    $cbd = new FS_cbd();

    $cbd->transaccion();

    $p = Almacen_obd::inicia($cbd, $this->pai->id_site, $id_almacen);

    if (!$p->delete($cbd)) {
      $cbd->rollback();

      $e->post_msx("Sucied&oacute; un error al tratar de actualizar la BD.");

      return $e;
    }

    $cbd->commit();

    $this->control_limits_sup();

    if ($this->seleccionado() == $id_almacen) return $this->pai->obxeto("calmacenf")->operacion_bCancelar($e);

    $this->preparar_saida($e->ajax());

    return $e;
  }

  protected function html_baleiro() {
    if (!$this->visible) return "";

    return "<div class='lista_baleira txt_adv'>No se encontraron coincidencias.</div>";
  }
}

//-----------------------------------

final class Cxalmacen_ehtml extends FS_ehtml {
  public function __construct() {
    parent::__construct();

    $this->class_table   = "lista_00";
    $this->style_table = "5px;border: 1px #aaa solid;";
    $this->align       = "center";
    $this->width       = "100%";
    $this->cellspacing = 0;
    $this->cellpadding = 0;
    $this->border      = 0;
  }

  protected function cabeceira($df = null) {
    return "<tr>
              <th style='width:44px'></th>
              <th style='width:44px;'>"   . $this->__lOrdenar_html("id_almacen", "&nbsp;#almacén&nbsp;") . "</th>
              <th>" . $this->__lOrdenar_html("almacen", "Nombre") . "</th>
              <th>" . $this->__lOrdenar_html("email", "Email") . "</th>
              <th style='width:30px;'>&nbsp;</th>
              <th style='width:1px; text-align: center'>&nbsp;</th>
            </tr>";
  }

  protected function linha_detalle($df, $f) {
    $style_tr = ($this->xestor->seleccionado() != $f['id_almacen'])?FS_ehtml::style_tr:FS_ehtml::style_tr_a;

    return "<tr {$style_tr} >
              <td> " . self::__logo($f) . "</td>
              <td style='text-align: center'>" . self::__lalmacen($this, $f) . "</td>
              <td>{$f['almacen']}</td>
              <td>{$f['email']}</td>
              <td>" . self::__lfra($this, $f) . "</td>
              <td>" . self::__bborrar($this, $f) . "</td>
            </tr>";
  }

  protected function totais() {
    return "<tr style='background-color: #fff;'>
              <td align=center colspan=11 style='border-top: 1px solid #aaa;'></td>
            </tr>
            <tr style='background-color: #fff;'>
              <td align=center colspan=11>" . $this->__paxinador_html() . "</td>
            </tr>";
  }

  private static function __logo($f) {
    $cbd = new FS_cbd();

    $c = Contacto_obd::inicia($cbd, $f['id_contacto'], "a");

    $url = (($logo = $c->iclogo_obd($cbd)) != null)?$logo->url():Imaxe_obd::noimx;


    return "<div style='background-image: url({$url});
                        width:  40px;
                        height: 40px;
                        margin: 2px;
                        background-size: cover;
                        background-repeat: no-repeat;
                        background-position: left center;' ></div>";
  }

  private static function __lalmacen(Cxalmacen_ehtml $ehtml, $f) {
    $b = new Div("lalmacen", Articulo_obd::ref($f['id_almacen'], 6));

    $b->clase_css("default", "lista_elemento_a");

    $b->title = "Editar almacención";

    $b->envia_ajax("onclick");
    //~ $b->envia_submit("onclick");

    return $ehtml->__fslControl($b, $f['id_almacen'])->html();
  }

  private static function __lfra(Cxalmacen_ehtml $ehtml, $f) {
    $b = new Div("lfra", "Info.Fra.");

    $b->clase_css("default", "lista_elemento_a");

    $b->title = "Ver facturación";

    $b->envia_ajax("onclick");
    //~ $b->envia_submit("onclick");

    return $ehtml->__fslControl($b, $f['id_almacen'])->html();
  }

  private static function __bborrar(Cxalmacen_ehtml $ehtml, $f) {
    if ($ehtml->xestor->readonly) return "";

    if ($f['id_almacen'] == 1) return ""; //* nunca se pode borrar o almacén 1.

    if ($ehtml->xestor->id_almacen > 1) return ""; //* un vendedor non pode borrar o seu almacén.

    $b = Panel_fs::__bsup("borrar", "", "Eliminar el almacén {$f['nome']}");

    $m = "¿ Quieres eliminar el almacén {$f['nome']} ?";

    //~ $b->envia_ajax("onclick", $m);
    $b->envia_submit("onclick", $m);

    return $ehtml->__fslControl($b, $f['id_almacen'])->html();
  }
}


//************************************


final class Cxalmacen_ficha_fra extends Componente {
  const ptw_0 = "ptw/paneis/pcontrol/ventas/almacen/cxalmacen_ficha_fra.html";

  private $id_site;
  private $id_almacen;

  public function __construct($id_site) {
    parent::__construct("calmacenffra", self::ptw_0);

    $this->visible = false;

    $this->id_site = $id_site;

    $this->pon_obxeto(new Param("palinfo"));

    $this->pon_obxeto(new Cxalmacen_busca_fra());

    $this->pon_obxeto(new Cxalmacen_lista_fra($id_site));

    $this->pon_obxeto(Panel_fs::__baceptar());
    $this->pon_obxeto(Panel_fs::__bcancelar());

    $this->obxeto("bCancelar")->envia_ajax("onclick");
    $this->obxeto("bAceptar" )->envia_ajax("onclick");
  }

  public function operacion(EstadoHTTP $e) {
//~ echo $e->evento()->html();

    if (($e_aux = $this->obxeto("calmacenlfra")->operacion($e)) != null) return $e_aux;
    if (($e_aux = $this->obxeto("calmacenbfra")->operacion($e)) != null) return $e_aux;

    if ($this->obxeto("bAceptar" )->control_evento()) return $this->operacion_bAceptar ($e);
    if ($this->obxeto("bCancelar")->control_evento()) return $this->operacion_bCancelar($e);


    return null;
  }

  public function post_almacen(Almacen_obd $a) {
    $this->visible = true;

    $this->id_almacen = $a->atr("id_almacen")->valor;

    $this->obxeto("palinfo")->post( Articulo_obd::ref($this->id_almacen, 6) . " - " . $a->atr("nome")->valor );


    $this->obxeto("calmacenlfra")->id_almacen = $this->id_almacen;

    $this->obxeto("calmacenlfra")->__where( $this->obxeto("calmacenbfra")->__where() );
  }

  public function operacion_buscador(Epcontrol $e, $where = null) {
    $this->obxeto("calmacenlfra")->limit_0 = 0;

    $this->obxeto("calmacenlfra")->__where($where);

    $this->obxeto("calmacenlfra")->preparar_saida($e->ajax());

//~ echo $this->obxeto("calmacenlfra")->sql_vista() . "<br>";

    return $e;
  }

  public function operacion_bCancelar(Epcontrol $e) {
    $this->visible = false;

    $this->id_almacen = null;

    $this->pai->preparar_saida($e->ajax());

    return $e;
  }

  private function operacion_bAceptar(Epcontrol $e) {
    return $this->operacion_bCancelar($e);
  }
}


//************************************


final class Cxalmacen_busca_fra extends Componente {
  const ptw_0 = "ptw/paneis/pcontrol/ventas/almacen/cxalmacen_busca_fra.html";

  public function __construct() {
    parent::__construct("calmacenbfra", self::ptw_0);


    $this->pon_obxeto(Panel_fs::__bbusca   ("bBuscar", null, null));
    $this->pon_obxeto(Panel_fs::__bcancelar(null, null, "bLimpa"));

    $this->pon_obxeto(new DataInput("data_0"));
    $this->pon_obxeto(new DataInput("data_1"));

    $this->obxeto("bLimpa")->visible = false;
    $this->obxeto("bLimpa")->envia_ajax("onclick");

    $this->obxeto("bBuscar")->envia_ajax("onclick");
  }

  public function operacion(EstadoHTTP $e) {
    if ($this->obxeto("bBuscar")->control_evento()) return $this->operacion_bBuscar($e);
    if ($this->obxeto("bLimpa" )->control_evento()) return $this->operacion_bLimpa ($e);


    return null;
  }

  private function operacion_bBuscar(EstadoHTTP $e, $blimpa = true) {
    $this->obxeto("bLimpa")->visible = $blimpa;

    $this->preparar_saida($e->ajax());

    return $this->pai->operacion_buscador($e, $this->__where());
  }

  private function operacion_bLimpa(EstadoHTTP $e) {
    $this->obxeto("data_0"   )->post(null);
    $this->obxeto("data_1"   )->post(null);

    return $this->operacion_bBuscar($e, false);
  }

  public function __where() {
    $wh_d0 = self::__where_momento($this->obxeto("data_0")->valor(), ">=");
    $wh_d1 = self::__where_momento($this->obxeto("data_1")->valor(), "<=");

    $where = "{$wh_d0} and {$wh_d1}";


    return str_replace(" and (1=1)", "", $where);
  }

  private static function __where_momento($data, $op) {
    return Valida::buscador_data2sql("c.momento", $op, $data);
  }
}


//************************************


final class Cxalmacen_lista_fra extends FS_lista {
   public $id_site;
   public $id_almacen;


  public function __construct($id_site) {
    parent::__construct("calmacenlfra", new Cxalmacen_lfra_ehtml());

    $this->id_site     = $id_site;
    $this->id_almacen  = -1;

    $this->selectfrom = "select a.id_site, a.id_almacen, a.comision_venta,
                                sum(b.tpvp) as tbi, sum(b.tpvp_plus) as tbi_plus, b.pive
                           from v_almacen a left join v_pedido_articulo b on (a.id_site = b.id_site and a.id_almacen = b.id_almacen)
                                            left join pedido c on (b.id_site = c.id_site and
                                                                   b.ano     = c.ano     and
                                                                   b.numero  = c.numero
                                                                  )";
    $this->orderby    = "a.id_almacen, b.pive";
    $this->groupby    = "a.id_site, a.id_almacen, b.pive";

    $this->__where();

    $this->limit_f    = 10;
  }

  public function post_almacen($id_almacen = -1) {
    $this->id_almacen  = $id_almacen;

    $this->__where();

//~ echo $this->sql_vista();
  }

  public function __where($where = null) {
    $this->where  = "a.id_site = {$this->id_site} and a.id_almacen = {$this->id_almacen} and (not c.pago_momento is null)";

    if ($where == null) return;

    $this->where .= " and {$where}";
  }

  protected function html_baleiro() {
    if (!$this->visible) return "";

    return "<div class='lista_baleira txt_adv'>No hay información de facturación para el periodo seleccionado.</div>";
  }
}

//-----------------------------------

final class Cxalmacen_lfra_ehtml extends FS_ehtml {
  private $t0 = 0;
  private $t1 = 0;
  private $t2 = 0;
  private $t3 = 0;
  private $t4 = 0;

  public function __construct() {
    parent::__construct();

    $this->class_table   = "lista_00";
    $this->style_table = "margin: 11px 0;border: 1px #aaa solid;";
    $this->align       = "center";
    $this->width       = "97%";
    $this->cellspacing = 0;
    $this->cellpadding = 0;
    $this->border      = 0;
  }

  protected function cabeceira($df = null) {

    $this->t0 = 0;
    $this->t1 = 0;
    $this->t2 = 0;
    $this->t3 = 0;
    $this->t4 = 0;

    return "<tr>
              <th width=77 style='text-align: center'>" . $this->__lOrdenar_html("tbi", "BaseImp.") . "</th>
              <th colspan=2 style='text-align: center'>" . $this->__lOrdenar_html("comision_venta", "Comisión") . "</th>
              <th width=77 style='text-align: center'>Neto</th>
              <th colspan=2 width=77 style='text-align: center'>" . $this->__lOrdenar_html("pive", "IVA") . "</th>
              <th width=77 style='text-align: center'>Facturar</th>
            </tr>";
  }

  protected function linha_detalle($df, $f) {
    $bi        = $f['tbi'] + $f['tbi_plus'];
    $xcom      = $bi - ($bi * (100 / (100 + $f['comision_venta'])));
    $neto      = $bi - $xcom;
    $ive       = $neto * ($f['pive'] / 100);
    $fact      = $neto + $ive;


    $this->t0 += round($bi, 2);
    $this->t1 += $xcom;
    $this->t2 += $neto;
    $this->t3 += $ive;
    $this->t4 += $fact;


    $bi        = number_format($bi, 2, ",", ".");
    $ive       = number_format($ive, 2, ",", ".");
    $fact      = number_format($fact, 2, ",", ".");
    $comision  = number_format($xcom, 2, ",", ".");
    $pcomision = number_format($f['comision_venta'], 0, ",", ".");
    $neto      = number_format($neto, 2, ",", ".");


    return "<tr style='background-color: #fff;'>
              <td align=right width=77px style='background-color: #eee;'>{$bi}&nbsp;€</td>
              <td align=right width=55px style='background-color: #fff;'>{$comision}&nbsp;€</td>
              <td align=right width=1px  style='background-color: #fff;color: #669;'>{$pcomision}%</td>
              <td align=right width=77px style='background-color: #eee;'>{$neto}&nbsp;€</td>
              <td align=right width=55px style='background-color: #fff;'>{$ive}&nbsp;€</td>
              <td align=right width=1px  style='background-color: #fff;color: #669;'>{$f['pive']}%</td>
              <td align=right            style='background-color: #eee;'>{$fact}&nbsp;€</td>
            </tr>";
  }

  protected function totais() {
    $t0 = number_format($this->t0, 2, ",", ".");
    $t1 = number_format($this->t1, 2, ",", ".");
    $t2 = number_format($this->t2, 2, ",", ".");
    $t3 = number_format($this->t3, 2, ",", ".");
    $t4 = number_format($this->t4, 2, ",", ".");

    return "<tr style='background-color: #fff;'>
              <td align=center colspan=11 style='margin:0; padding:0;border-top: 2px solid #aaa;'></td>
            </tr>
            <tr style='background-color: #fff; font-weight: bold;'>
              <td style='background-color: #eee;' align=right>{$t0}&nbsp;€</td>
              <td align=right>{$t1}&nbsp;€</td>
              <td></td>
              <td style='background-color: #eee;' align=right>{$t2}&nbsp;€</td>
              <td align=right>{$t3}&nbsp;€</td>
              <td></td>
              <td style='background-color: #eee;' align=right>{$t4}&nbsp;€</td>
            </tr>
            <tr style='background-color: #fff;'>
              <td align=center colspan=11 style='margin:0; padding:0;'>" . $this->__paxinador_html() . "</td>
            </tr>";
  }
}


//************************************


final class Cxalmacen_semana extends Componente {
  const ptw_0 = "ptw/paneis/pcontrol/ventas/almacen/cxalmacen_ficha_semana.html";

  public function __construct() {
    parent::__construct("calmacensemana", self::ptw_0);

    $this->pon_obxeto(new Checkbox("ch_1", true, "Lunes"    ));
    $this->pon_obxeto(new Checkbox("ch_2", true, "Martes"   ));
    $this->pon_obxeto(new Checkbox("ch_3", true, "Miércores"));
    $this->pon_obxeto(new Checkbox("ch_4", true, "Jueves"   ));
    $this->pon_obxeto(new Checkbox("ch_5", true, "Viernes"  ));
    $this->pon_obxeto(new Checkbox("ch_6", true, "Sábado"   ));
    $this->pon_obxeto(new Checkbox("ch_0", true, "Domingo"  ));
  }

  public function post_almacen(Almacen_obd $a) {

    $this->visible = false;

    $this->obxeto("ch_1")->post( $a->atr("ch_1")->valor == 1 );
    $this->obxeto("ch_2")->post( $a->atr("ch_2")->valor == 1 );
    $this->obxeto("ch_3")->post( $a->atr("ch_3")->valor == 1 );
    $this->obxeto("ch_4")->post( $a->atr("ch_4")->valor == 1 );
    $this->obxeto("ch_5")->post( $a->atr("ch_5")->valor == 1 );
    $this->obxeto("ch_6")->post( $a->atr("ch_6")->valor == 1 );
    $this->obxeto("ch_0")->post( $a->atr("ch_0")->valor == 1 );
  }

  public function completar_almacen(Almacen_obd $a) {
    $a->atr("ch_1")->valor = ( $this->obxeto("ch_1")->valor() )?1:"0";
    $a->atr("ch_2")->valor = ( $this->obxeto("ch_2")->valor() )?1:"0";
    $a->atr("ch_3")->valor = ( $this->obxeto("ch_3")->valor() )?1:"0";
    $a->atr("ch_4")->valor = ( $this->obxeto("ch_4")->valor() )?1:"0";
    $a->atr("ch_5")->valor = ( $this->obxeto("ch_5")->valor() )?1:"0";
    $a->atr("ch_6")->valor = ( $this->obxeto("ch_6")->valor() )?1:"0";
    $a->atr("ch_0")->valor = ( $this->obxeto("ch_0")->valor() )?1:"0";

    return $a;
  }
}
