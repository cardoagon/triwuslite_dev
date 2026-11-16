<?php


final class CXA_ficha extends Componente {
  const ptw_1 = "ptw/paneis/pcontrol/ventas/articulo/cxaficha_1.html";
  const ptw_2 = "ptw/paneis/pcontrol/ventas/articulo/cxaficha_2.html";
  const ptw_3 = "ptw/paneis/pcontrol/ventas/articulo/cxaficha_3.html";

  public $id_site;

  public $id_articulo;
  public $id_almacen;
  public $id_grupo;
  public $id_grupo_cc;
  public $id_perfil;

  public $imx_proporcion;

  public $ean_0;

  public $moeda;

  public function __construct($id_site, $id_almacen) {
    parent::__construct("cxaf");

    $site_cconfig = Site_obd::inicia( new FS_cbd(), $id_site )->config_obd();

    $this->id_almacen = $id_almacen;

    $this->imx_proporcion = 1 / $site_cconfig->atr("factor_h")->valor;

    $this->pon_obxeto(self::__idarticulo());

    $this->pon_obxeto(self::__stipo());

    $this->pon_obxeto(self::__text("nome"));

    $this->pon_obxeto(new CEditor_2("notas"));

    $this->pon_obxeto(self::__text("ref_externa"));
    $this->pon_obxeto(self::__text("ean"        ));

    $this->pon_obxeto(self::__smarca($id_site, $id_almacen));

    $this->pon_obxeto(self::__smoeda());

    $this->pon_obxeto(Cxarticulos_busca::__salmacen($id_site, $id_almacen, null));


    $this->pon_obxeto(Panel_fs::__bmais("bEngadir" , "Crear un producto", "A&ntilde;adir un nuevo producto al cat&aacute;logo" ));
    $this->pon_obxeto(Panel_fs::__bmais("lDuplicar", "Duplicar", "Crea una copia producto"));

    $this->pon_obxeto(new CXAF_duplicar());

    $this->pon_obxeto(self::__legend("cxafd", "Precio, IVA, unidades, descuentos"));
    $this->pon_obxeto(new CXAF_desconto());

    $this->pon_obxeto(self::__legend("cxafimaxe", "Imágenes"));
    $this->pon_obxeto(new CXAF_imaxes());

    $this->pon_obxeto(self::__legend("cxafpubs", "Publicaciones del producto"));
    $this->pon_obxeto(new CXAF_publicacions($id_almacen));

    $this->pon_obxeto(self::__legend("cxafcat", "Categoría del producto"));
    $this->pon_obxeto(new CXAF_categoria($id_site));

    $this->pon_obxeto(self::__legend("cxafcc", "Campos cústom"));
    $this->pon_obxeto(new CXAF_cc($id_almacen));

    $this->pon_obxeto(self::__legend("cxafdim", "Dimensiones"));
    $this->pon_obxeto(new CXAF_dimension());

    $this->pon_obxeto(self::__legend("cxafseo", "SEO"));
    $this->pon_obxeto(new CXAF_seo());

    $this->pon_obxeto(self::__legend("cxafaux", "Auxiliares"));
    $this->pon_obxeto(new CXAF_aux());

    $this->pon_obxeto(self::__legend("cxafpto", "Definición del presupuesto"));
    $this->pon_obxeto(new CXAF_prpsto_paso_lista());


    $this->pon_obxeto(new Div("dgardando", "Guardando ..."));

    $this->pon_obxeto(Panel_fs::__baceptar());
    $this->pon_obxeto(Panel_fs::__bcancelar());


    $this->obxeto("nome"       )->style("default", "width: 100%;");

    $this->obxeto("dgardando")->clase_css("default", "texto3_erro");

    $this->obxeto("dgardando")->visible = false;

    $this->obxeto("id_articulo")->readonly = true;


    $this->obxeto("bEngadir" )->envia_ajax("onclick");

    $this->obxeto("bCancelar")->envia_ajax("onclick");
    $this->obxeto("lDuplicar")->envia_ajax("onclick");
    $this->obxeto("bAceptar" )->pon_eventos("onclick", "cxaf_gardando(this)");
  }

  public function id_perfil() {
    return $this->id_perfil;
  }

  public function id_grupo() {
    return $this->id_grupo;
  }

  public function seleccionado() {
    return $this->obxeto("id_articulo")->valor();
  }

  public function operacion(EstadoHTTP $e) {
    if ($this->ptw == null) return null;


    if ($this->obxeto("bAceptar"        )->control_evento()) return $this->operacion_bAceptar($e);
    if ($this->obxeto("bCancelar"       )->control_evento()) return $this->operacion_bCancelar($e);

    if ($this->obxeto("legend_cxafd"    )->control_evento()) return $this->operacion_legend($e, "cxafd");
    if ($this->obxeto("legend_cxafimaxe")->control_evento()) return $this->operacion_legend($e, "cxafimaxe");
    if ($this->obxeto("legend_cxafpubs" )->control_evento()) return $this->operacion_legend($e, "cxafpubs");
    if ($this->obxeto("legend_cxafcc"   )->control_evento()) return $this->operacion_legend($e, "cxafcc");
    if ($this->obxeto("legend_cxafdim"  )->control_evento()) return $this->operacion_legend($e, "cxafdim");
    if ($this->obxeto("legend_cxafseo"  )->control_evento()) return $this->operacion_legend($e, "cxafseo");
    if ($this->obxeto("legend_cxafaux"  )->control_evento()) return $this->operacion_legend($e, "cxafaux");
    if ($this->obxeto("legend_cxafpto"  )->control_evento()) return $this->operacion_legend($e, "cxafpto");
    if ($this->obxeto("legend_cxafcat"  )->control_evento()) return $this->operacion_legend($e, "cxafcat");


    if ($this->obxeto("bEngadir" )->control_evento()) return $this->pai->operacion_articulo($e);

    if ($this->obxeto("lDuplicar")->control_evento()) return $this->operacion_lDuplicar($e);

    if (($e_aux = $this->obxeto("cxafdupli")->operacion($e)) != null) return $e_aux;


    if (($e_aux = $this->obxeto("cxafimaxe")->operacion($e)) != null) return $e_aux;
    if (($e_aux = $this->obxeto("cxafcc"   )->operacion($e)) != null) return $e_aux;
    if (($e_aux = $this->obxeto("cxafcat"  )->operacion($e)) != null) return $e_aux;
    if (($e_aux = $this->obxeto("cxafpto"  )->operacion($e)) != null) return $e_aux;
    if (($e_aux = $this->obxeto("cxafpubs" )->operacion($e)) != null) return $e_aux;


    return null;
  }

  public function reinicia() {
    $a = Articulo_obd::inicia(new FS_cbd(), $this->id_site, $this->id_articulo);

    $this->pon_articulo($a);
  }

  public function pon_articulo(Articulo_obd $a, $alta = false) {
    //~ $cbd = new FS_cbd();

    if ($alta)
      $this->ptw = self::ptw_3;
    elseif ($a->atr("tipo")->valor == "s")
      $this->ptw = self::ptw_2;
    else
      $this->ptw = self::ptw_1;


    $this->id_site       = $a->atr("id_site"    )->valor;
    $this->id_articulo   = $a->atr("id_articulo")->valor;
    $this->id_grupo      = $a->atr("id_grupo"   )->valor;
    $this->id_grupo_cc   = $a->atr("id_grupo_cc")->valor;

    $this->ean_0         = $a->atr("ean")->valor;

    $this->moeda         = $a->atr("moeda")->valor;

    $this->obxeto("nome" )->post($a->atr("nome"       )->valor);
    $this->obxeto("notas")->post_txt($a->atr("notas"      )->valor);

    $this->obxeto("id_articulo")->post(Articulo_obd::ref($this->id_articulo, 6));
    $this->obxeto("id_articulo")->readonly = true;

    $this->obxeto("salmacen")->post($a->atr("id_almacen")->valor);

    $this->obxeto("stipo")->post($a->atr("tipo")->valor);
    $this->obxeto("stipo")->readonly = !$alta;

    $this->obxeto("ref_externa")->post($a->atr("ref_externa")->valor);
    $this->obxeto("ean"        )->post($a->atr("ean"        )->valor);
    $this->obxeto("marca"      )->post($a->atr("id_marca"   )->valor);

    $this->obxeto("cxafcc"   )->pon_articulo($a);
    $this->obxeto("cxafd"    )->pon_articulo($a);
    $this->obxeto("cxafimaxe")->pon_articulo($a);
    //~ $this->obxeto("cxafrel"  )->pon_articulo($a);
    $this->obxeto("cxafcat"  )->pon_articulo($a); //* IMPORTANTE. 'cxafcat'.
    $this->obxeto("cxafpto"  )->pon_articulo($a);
    $this->obxeto("cxafdim"  )->pon_articulo($a);
    $this->obxeto("cxafseo"  )->pon_articulo($a);
    $this->obxeto("cxafaux"  )->pon_articulo($a);
    $this->obxeto("cxafdupli")->pon_articulo($a);

    $this->obxeto("cxafpubs" )->pon_articulo($a); //* IMPORTANTE. 'cxafpubs' sempre despois de 'cxafcat'.

    $this->obxeto("moeda")->post($a->atr("moeda")->valor);
  }

  public function preparar_saida(Ajax $a = null) {
    $html = ($this->ptw == null)?"":$this->html00();

    $this->obxeto("illa")->post($html);

    if ($a == null) return;

    $a->pon($this->obxeto("illa"));

    $this->obxeto("notas")->preparar_saida($a);

    $this->obxeto("illa")->post(null);
  }

  public function operacion_lDuplicar(Epcontrol $e) {
    return $this->obxeto("cxafdupli")->operacion_lDuplicar($e);
  }

  public function operacion_bCancelar(Epcontrol $e) {
    $this->pai->configurar();

    $this->ptw = null;

    $this->obxeto("id_articulo")->post(null);

    $this->pai->preparar_saida($e->ajax());

    return $e;
  }

  public function operacion_bAceptar(Epcontrol $e) {
    if (($erro = $this->validar()) != null) {
      $e->post_msx($erro);

      return $e;
    }


    $cbd = new FS_cbd();

    $cbd->transaccion(true);

    $a     = $this->articulo_obd($cbd);
    $_pubs = $this->obxeto("cxafpubs")->_pubs();

    if (!$this->pai->update($cbd, $a, null, $_pubs)) {
      $cbd->rollback();

      return $e;
    }

    $cbd->commit();


    $this->pon_articulo($a);

    $this->pai->preparar_saida($e->ajax());

    $this->obxeto("notas")->preparar_saida($e->ajax());

    $e->ajax()->pon_ok(true, "pcontrol_cab_0()");

    $e->post_msx("Producto guardado correctamente", "m");


    return $e;
  }


  public static function __text($id, $titulo = null, $placeholder = null, $align = "left") {
    $t = Panel_fs::__text($id, null, null);

    $t->title       = $titulo;
    $t->placeholder = $placeholder;

    $t->style("default" , "text-align: {$align};");
    $t->style("readonly", "text-align: {$align};");

    return $t;
  }

  public static function __number($id, $titulo = null, $placeholder = null) {
    return self::__text($id, $titulo, $placeholder, "right");
  }

  public function articulo_obd(FS_cbd $cbd = null) {
    if ($cbd == null) $cbd = new FS_cbd();

    $a = Articulo_obd::inicia($cbd, $this->id_site, $this->id_articulo);


    $a->atr("id_almacen" )->valor = ($this->id_almacen > 1)?$this->id_almacen:$this->obxeto("salmacen")->valor();

    $a->atr("tipo"       )->valor = $this->obxeto("stipo")->valor();
    $a->atr("nome"       )->valor = str_replace( array("\"", "'"), array("`", "`"), trim($this->obxeto("nome")->valor()) );
    $a->atr("ref_externa")->valor = trim($this->obxeto("ref_externa")->valor());
    $a->atr("ean"        )->valor = trim($this->obxeto("ean"        )->valor());
    $a->atr("notas"      )->valor = trim($this->obxeto("notas"      )->valor());
    $a->atr("id_marca"   )->valor = $this->obxeto("marca")->valor();
    $a->atr("moeda"      )->valor = $this->moeda;


    if ($a->atr("id_marca")->valor == "**") $a->atr("id_marca")->valor = null;

    $a = $this->obxeto("cxafcat")->completar_articulo($a);
    $a = $this->obxeto("cxafd"  )->completar_articulo($a);
    $a = $this->obxeto("cxafdim")->completar_articulo($a);
    $a = $this->obxeto("cxafseo")->completar_articulo($a);
    $a = $this->obxeto("cxafaux")->completar_articulo($a);

//~ echo "<pre>" . print_r($a->a_resumo(), true) . "</pre>";

    return $a;
  }

  private function operacion_legend(Epcontrol $e, string $k) {
    $o = $this->obxeto($k);

    $o->visible = !$o->visible;


    switch($k) {
      case "cxafpto":
      //~ case "cxafrel":
        $this->preparar_saida($e->ajax()); //* debemos solucionar este caso.
        break;

      default:
        $o->preparar_saida($e->ajax());
    }


    return $e;
  }

  private function validar() {
    $this->obxeto("moeda")->post("euro");

    if (trim($this->obxeto("nome")->valor()) == "") return "Error. El campo 'nombre' no puede estar vacío";

//~ echo trim($this->obxeto("ean")->valor()) . "{$this->ean_0}::\n";

    if (($ean = $this->obxeto("ean")->valor()) == null) 
      $ean = "";
    else
      $ean = trim($ean);

    if ($ean != $this->ean_0) {
//~ echo "11111111111\n";
      if ( Articulo_obd::ean_dupli($this->id_site, $ean) ) {
        return "ERROR, EAN está asignado a otro producto.";
      }
    }

    if (($erro = $this->obxeto("cxafd"  )->validar()) != null) return $erro;
    if (($erro = $this->obxeto("cxafseo")->validar()) != null) return $erro;
    if (($erro = $this->obxeto("cxafdim")->validar()) != null) return $erro;
    if (($erro = $this->obxeto("cxafaux")->validar()) != null) return $erro;


    return null;
  }

  private static function __idarticulo() {
    $t = self::__text("id_articulo", "... Identificador del producto", "000000000");

    $t->readonly = true;

    return $t;
  }

  private static function __stipo() {
    $s = new Select("stipo", Articulo_obd::a_tipo());

    //~ $s->clase_css("default" , "text");
    //~ $s->clase_css("readonly", "text");

    $s->post("a");

    return $s;
  }

  private static function __smarca($id_site, $id_almacen) {
    $_o = Articulo_marca_obd::_marca($id_site);

    $s = new Select("marca", $_o);


    return $s;
  }

  private static function __smoeda() {
    $opcions = null;
    foreach (Articulo_obd::$a_moeda as $m_bd=>$m_html) $opcions[$m_bd] = $m_html;

    $s = new Select("moeda", $opcions);


    return $s;
  }

  private static function __legend(string $id, string $etq):Etiqueta {
    $b = new Etiqueta("legend_{$id}", $etq);

    $b->element = "legend";

    $b->clase_css("default" , "cxaf_legend");

    $b->pon_eventos("onclick", "cxaf_legeng_click(this)");

    return $b;
  }
}

//**************************************

final class CXAF_desconto extends Componente {
  const ptw_0 = "ptw/paneis/pcontrol/ventas/articulo/cxaf_dto_0.html";
  const ptw_1 = "ptw/paneis/pcontrol/ventas/articulo/cxaf_dto_1.html";

  //~ private $sw_momento_fs = -1; //* controla se devemos actualizar momento_fs, sii se cambian as unidades do produto.

  public function __construct() {
    parent::__construct("cxafd");

    $this->visible = false;

    $this->pon_obxeto(CXA_ficha::__number("unidades"  , "", "0"));
    $this->pon_obxeto(CXA_ficha::__number("pedido_min", "Pedido mínimo.", "1"));
    $this->pon_obxeto(CXA_ficha::__number("pedido_max", "Pedido máximo, ( 0 => no se usa ).", "0"));

    $this->pon_obxeto(Panel_fs ::__dataInput("servizo_data", false, "Fecha de inicio del servicio. En caso de ser nula, se tomará la fecha de pago del servicio, como fecha de inicio."));
    $this->pon_obxeto(CXA_ficha::__number   ("servizo_dias"));
    $this->pon_obxeto(CXA_ficha::__text     ("servizo_permisos", "Permisos del site asociados al producto"));

    $this->pon_obxeto(new Hidden("pvp")); //* necesitamos este para gradar o precio con 3 decimais, ainda que so se mostren dous.

    $this->pon_obxeto(self::__stive());

    $this->pon_obxeto(CXA_ficha::__number("pvp_2"  , "PVP", "0"));
    $this->pon_obxeto(CXA_ficha::__number("pvp_aux", "precio", "0"));
    $this->pon_obxeto(CXA_ficha::__number("pvp_d"  , "", "0"));
    $this->pon_obxeto(CXA_ficha::__number("desc_p" , "% de descuento para este producto", "0"));
    $this->pon_obxeto(CXA_ficha::__number("desc_u" , "Aplica el descuento a partir de 1, 2, 3, ... unidades del producto"));

    $this->obxeto("pive"   )->pon_eventos("onchange", "cxafd_pvp_2()");
    $this->obxeto("pvp_aux")->pon_eventos("onchange", "cxafd_pvp_2()");
    $this->obxeto("pvp_2"  )->pon_eventos("onchange", "cxafd_pvp_1()");

    $this->obxeto("desc_p")->pon_eventos("onchange", "cxafd_pvp_d_1()");
    $this->obxeto("pvp_d" )->pon_eventos("onchange", "cxafd_pvp_d_2()");

    $this->obxeto("desc_u")->readonly = true;
  }

  public function pon_articulo(Articulo_obd $a) {
//~ echo "<pre>" . print_r($a->a_resumo(), true) . "</pre>";

    $this->ptw = ($a->atr("tipo")->valor == "s")?self::ptw_1:self::ptw_0;

    //~ $this->sw_momento_fs = $a->atr("unidades" )->valor;

    $this->obxeto("servizo_data"    )->post($a->atr("servizo_data"    )->valor);
    $this->obxeto("servizo_dias"    )->post($a->atr("servizo_dias"    )->valor);
    $this->obxeto("servizo_permisos")->post($a->atr("servizo_permisos")->valor);

    $this->obxeto("unidades"    )->post($a->atr("unidades"    )->valor);
    $this->obxeto("pedido_min"  )->post($a->atr("pedido_min"  )->valor);
    $this->obxeto("pedido_max"  )->post($a->atr("pedido_max"  )->valor);


    $pvp = $a->pvp();

    $this->obxeto("pvp")->post($a->atr("pvp")->valor);
    $this->obxeto("pvp_2")->post($pvp);
    $this->obxeto("pive")->post($a->atr("id_ive")->valor);

    $this->obxeto("desc_u")->post(1);
    $this->obxeto("desc_p")->post($a->atr("desc_p")->valor);

    $this->obxeto("pvp_d")->post($a->pvp(1, true, true));

    $this->obxeto("pvp_aux")->post( round(floatval($a->atr("pvp")->valor), 2) );

    $this->obxeto("pvp_d")->readonly = true;
  }

  public function validar() {
    $this->obxeto("pvp")->post( Erro::valida_numero( $this->obxeto("pvp")->valor(), 10 ) );


    if (trim($this->obxeto("unidades"  )->valor()) == "") $this->obxeto("unidades"  )->post("0");
    if (trim($this->obxeto("pedido_min")->valor()) == "") $this->obxeto("pedido_min")->post("1");
    if (trim($this->obxeto("pedido_max")->valor()) == "") $this->obxeto("pedido_max")->post("0");

    if ($this->pai->obxeto("stipo")->valor() == "s") $this->obxeto("pedido_max")->post("1");

    $permisos = str_replace("admin", "", $this->obxeto("servizo_permisos")->valor() );

    $permisos = str_replace("pub, ", "", FGS_usuario::valida_permisos( $permisos ));

    $this->obxeto("servizo_permisos")->post( $permisos );

    $this->obxeto("desc_p")->post( Erro::valida_numero( $this->obxeto("desc_p")->valor(), 1 ) );
    $this->obxeto("desc_u")->post( Erro::valida_numero( 1 ) );

    return null;
  }

  public function completar_articulo(Articulo_obd $a) {
    $a->atr("pvp"   )->valor = $this->obxeto("pvp")->valor();
    $a->atr("desc_p")->valor = $this->obxeto("desc_p")->valor();
    $a->atr("desc_u")->valor = $this->obxeto("desc_u")->valor();
    $a->atr("id_ive")->valor = $this->obxeto("pive")->valor();
    $a->atr("pive"  )->valor = $this->obxeto("pive")->desc();

    $a->atr("servizo_data"    )->valor = $this->obxeto("servizo_data")->valor();
    $a->atr("servizo_dias"    )->valor = Valida::numero( trim($this->obxeto("servizo_dias")->valor()) );
    $a->atr("servizo_permisos")->valor = $this->obxeto("servizo_permisos")->valor();

    $a->atr("pedido_min")->valor = trim($this->obxeto("pedido_min")->valor());
    $a->atr("pedido_max")->valor = trim($this->obxeto("pedido_max")->valor());
    $a->atr("unidades"  )->valor = Valida::numero( trim($this->obxeto("unidades")->valor()) );

    $a->atr("momento_fs")->valor = date("YmdHis");

/*
    if ( $this->sw_momento_fs != $a->atr("unidades")->valor ) {
      $this->sw_momento_fs = $a->atr("unidades")->valor;

      $a->atr("momento_fs")->valor = date("YmdHis");
    }
*/
    if ($a->atr("id_ive")->valor == null) $a->atr("id_ive")->valor = 1;

    return $a;
  }

  private static function __stive() {
    $s = new Select("pive", Articulo_obd::a_tipoive());


    //~ $s->clase_css("default", "text");

    //~ $s->style("default",  "text-align: right;");
    //~ $s->style("readonly", "text-align: right;");

    $s->title = "% IVA";


    return $s;
  }
}

//**************************************

final class CXAF_duplicar extends    Componente
                          implements ICXAF_cc_lista {

  const ptw_0 = "ptw/paneis/pcontrol/ventas/articulo/cxaf_dupli_0.html";
  const ptw_1 = "ptw/paneis/pcontrol/ventas/articulo/cxaf_dupli_1.html";

  public $id_s = null;
  public $id_a = null;
  public $id_g = null;

  public $_v   = null;

  public function __construct() {
    parent::__construct("cxafdupli");

    $this->visible = false;

    $this->pon_obxeto(Panel_fs::__text("nome"       , 22, 99, " "));
    $this->pon_obxeto(Panel_fs::__text("ref_externa", 22, 22, " "));
    $this->pon_obxeto(Panel_fs::__text("ean"        , 22, 22, " "));

    $this->pon_obxeto( new CXAF_cc_lista(true) );

    $this->pon_obxeto(Panel_fs::__baceptar());
    $this->pon_obxeto(Panel_fs::__bcancelar());


    $this->obxeto("ref_externa")->style("default", "width: 99%;");
    $this->obxeto("ean"        )->style("default", "width: 99%;");
    $this->obxeto("nome"       )->style("default", "width: 99%;");

    $this->obxeto("bCancelar")->envia_ajax("onclick");
    //~ $this->obxeto("bAceptar" )->pon_eventos("onclick", "cxaf_gardando(this)");
  }

  public function pon_articulo(Articulo_obd $a) {
    $this->sup_obxeto("svalor"); //* Eliminamos os selectores, se hai.

    $this->visible = false; //* aseguramos o valor FALSE da propiedade cando cambiamos de producto.

    $this->obxeto("nome"       )->post( $a->atr("nome"       )->valor );
    $this->obxeto("ean"        )->post( "" );
    $this->obxeto("ref_externa")->post( $a->atr("ref_externa")->valor );

    $this->id_s = $a->atr("id_site"    )->valor;
    $this->id_a = $a->atr("id_articulo")->valor;
    $this->id_g = $a->atr("id_grupo_cc")->valor;

    $this->_v = Articulo_cc_obd::_tupla($this->id_s, $this->id_a);

    //* IMPORTANTE posición.
    $this->ptw = ($this->id_g == null)?self::ptw_1:self::ptw_0;

    $this->obxeto("cxafcc_lista")->config_where();
  }

  public function operacion(EstadoHTTP $e) {
    if ($this->ptw == null) return null;

    if ($this->obxeto("bAceptar" )->control_evento()) return $this->operacion_bAceptar ($e);
    if ($this->obxeto("bCancelar")->control_evento()) return $this->operacion_bCancelar($e);


    return null;
  }

  public function operacion_bCancelar(Epcontrol $e) {
    $this->visible = false;

    $this->preparar_saida($e->ajax());

    return $e;
  }

  public function operacion_bAceptar(Epcontrol $e) {
    if (($erro = $this->validar()) != null) {
      $e->post_msx($erro);

      return $e;
    }

    $cbd = new FS_cbd();

    $cbd->transaccion(true);

    $a = $this->pai->articulo_obd($cbd);

    $a->atr("nome"       )->valor = trim( $this->obxeto("nome"       )->valor() );
    $a->atr("ref_externa")->valor = trim( $this->obxeto("ref_externa")->valor() );
    $a->atr("ean"        )->valor = trim( $this->obxeto("ean"        )->valor() );


    $_cc = $this->_cc();
    
    $cc = (isset($_cc[0]))?$_cc[0]:null;

    if ( !$a->duplicar($cbd, $cc) ) {
      $cbd->rollback();

      return $e;
    }

    $cbd->commit();


    $this->pai->pon_articulo($a);


    $e->obxeto("cxarticulos")->preparar_saida($e->ajax());

    $this->visible = false;


    return $e;
  }

  public function operacion_lDuplicar(Epcontrol $e) {
    $this->visible = true;

    $this->pai->preparar_saida($e->ajax());

    return $e;
  }

  public function id_s() { //* IMPLEMENTA ICXAF_cc_lista
    return $this->id_s;
  }

  public function id_a() { //* IMPLEMENTA ICXAF_cc_lista
    return $this->id_a;
  }

  public function id_g() { //* IMPLEMENTA ICXAF_cc_lista
    return $this->id_g;
  }

  public function __scampo() { //* IMPLEMENTA ICXAF_cc_lista
    $_ = Articulo_cc_obd::_campo_disponible($this->id_s, $this->pai->id_almacen, $this->id_g, "...");

    $s = new Select("scampo", $_);

    $s->envia_ajax("onchange");
    //~ $s->envia_submit("onchange");


    return $s;
  }

  public function __svalor($id_cc_0 = null) { //* IMPLEMENTA ICXAF_cc_lista
    //* PRECOND. $id_cc_0 != null

    $_ = Articulo_cc_obd::_valor($this->id_s, $id_cc_0);

    $s = new Select("svalor", $_);

    $s->post( $this->_v[$id_cc_0][0] ); //* asignamos o valor do campo duplicado.


    return $s;
  }

  private function validar() {
    $er = null;

    if (trim($this->obxeto("nome")->valor()) == null) $er .= "ERROR, teclea un nombre.<br />";

    if (Articulo_obd::ean_dupli($this->id_s, $this->obxeto("ean")->valor())) $er .= "ERROR, EAN está asignado a otro producto.<br />";


    if ($this->id_g == null) return $er;



    return $er . $this->validar_cc();
  }

  private function validar_cc() {
    if (($_cc = $this->_cc()) == null) return null;

//~ echo "<pre>" . print_r($_cc, true) . "</pre>";

    if ( Articulo_cc_obd::hai_tupla($this->id_s, $this->id_g, $_cc[0]) == null ) return null;

    $s = null;
    foreach($_cc[1] as $id_cc_0=>$valor) {
      if ($s != null) $s .= ", ";

      $s .= $valor;
    }

    return "ERROR, selecciona valores para los campos cústom distintos a la tupla {{$s}}.<br />";
  }

  private function _cc() {
    $_svalor = $this->obxetos("svalor");

    if ($_svalor == null) return null;

    $_0 = null;
    $_1 = null;
    foreach($_svalor as $k=>$svalor) {
      list($n, $id_cc_0, $id_cc_1) = explode(Escritor_html::csubnome, $k);

      $_0[$id_cc_0] = $svalor->valor();
      $_1[$id_cc_0] = $svalor->desc();
    }

    return array($_0, $_1);
  }
}


//**************************************

final class CXAF_categoria extends Componente {
  const ptw_0 = "ptw/paneis/pcontrol/ventas/articulo/cxaf_categoria.html";

  private $id_site = null;

  public function __construct() {
    parent::__construct("cxafcat", self::ptw_0);

    $this->visible =  false;

    for ($i = 0; $i <= Categoria_obd::nivel_max; $i++) {
      $this->pon_obxeto( self::__scat($i, 0) );
    }
 }


  public function pon_articulo(Articulo_obd $a) {
    $cbd = new FS_cbd();

    $this->id_site = $a->atr("id_site")->valor;

    $_miga = Categoria_obd::_miga($this->id_site, $a->atr("id_categoria")->valor);

//~ echo "<pre>" . print_r( $_miga, 1) . "</pre>";

    //* inicia selectores
    for ($i = 0; $i <= Categoria_obd::nivel_max; $i++) {
      $this->pon_obxeto( self::__scat_miga($i, $this->id_site, $_miga) );
    }
  }

  public function validar() {
    return true;
  }

  public function operacion(EstadoHTTP $e) {
    if ($this->obxeto("scat_1")->control_evento()) return $this->operacion_scat_1($e);
    if ($this->obxeto("scat_2")->control_evento()) return $this->operacion_scat_2($e);

    return null;
  }

  public function completar_articulo(Articulo_obd $a) {
        if ($this->obxeto("scat_3")->valor() != "**") $k = $this->obxeto("scat_3")->valor();
    elseif ($this->obxeto("scat_2")->valor() != "**") $k = $this->obxeto("scat_2")->valor();
    elseif ($this->obxeto("scat_1")->valor() != "**") $k = $this->obxeto("scat_1")->valor();
    else                                              $k = 1;


    $a->atr("id_categoria")->valor = $k;


    return $a;
  }


  private function operacion_scat_1(EstadoHTTP $e) {
    if (($p = $this->obxeto("scat_1")->valor()) == "**") {
      $this->pon_obxeto(self::__scat(1, $this->id_site)); //* scat_2
    }
    else {
      $this->pon_obxeto(self::__scat(1, $this->id_site, $p)); //* scat_2
    }


    return $this->operacion_scat_2($e);
  }

  private function operacion_scat_2(EstadoHTTP $e) {
    if (($p = $this->obxeto("scat_2")->valor()) == "**") {
      $this->pon_obxeto(self::__scat(2, $this->id_site)); //* scat_3
    }
    else {
      $this->pon_obxeto(self::__scat(2, $this->id_site, $p)); //* scat_3
    }


    $this->preparar_saida($e->ajax());


    return $e;
  }


  private static function __scat(int $i, int $s, ?int $p = null, ?int $c = null):Select {
    $_o = ($p === null) ? ["**" => "···"] : Categoria_obd::_categoria($s, null, $p);

    $i2 = $i + 1;

    $s = new Select("scat_{$i2}", $_o);


    if ($p !== null) {
      if ($i < Categoria_obd::nivel_max) $s->envia_ajax("onchange");
      //~ if ($i < Categoria_obd::nivel_max) $s->envia_submit("onchange");
    }

    if ($c === null) $c = "**";

    $s->post( $c );


    return $s;
  }

  private static function __scat_miga(int $i, int $s, array $_miga):Select {
    if ( !isset($_miga[$i]) ) {
      if ( isset($_miga[$i - 1]) ) return self::__scat($i, $s, $_miga[$i - 1][0]);
      
      return self::__scat($i, $s);
    }


    return self::__scat($i, $s, $_miga[$i][2], $_miga[$i][0]);
  }
}

/*
final class CXAF_categoria extends Componente {
  const ptw_0 = "ptw/paneis/pcontrol/ventas/articulo/cxaf_categoria.html";

  public function __construct(int $id_site) {
    parent::__construct("cxafcat", self::ptw_0);

    $this->visible =  false;

    $this->pon_obxeto( self::smiga($id_site) );
 }

  public function pon_articulo(Articulo_obd $a) {
    $this->obxeto("smiga")->post( $a->atr("id_categoria")->valor );
  }

  public function validar() {
    return true;
  }

  public function completar_articulo(Articulo_obd $a) {
    if (($k = $this->obxeto("smiga")->valor()) == "**") $k = 1;

    $a->atr("id_categoria")->valor = $k;

    return $a;
  }

  private static function smiga(int $s):DataList {
    $o = new DataList("smiga", Categoria_obd::_migas($s));

    return $o;
  }
}
*/
//**************************************

final class CXAF_dimension extends Componente {
  public function __construct() {
    parent::__construct("cxafdim", "ptw/paneis/pcontrol/ventas/articulo/cxaf_dimension.html");

    $this->visible = false;

    $this->pon_obxeto(CXA_ficha::__number("peso"));
  }

  public function pon_articulo(Articulo_obd $a) {
    $this->obxeto("peso")->post($a->atr("peso")->valor);
  }

  public function completar_articulo(Articulo_obd $a) {
    $a->atr("peso")->valor = Valida::numero( $this->obxeto("peso")->valor(), 2 );

    return $a;
  }

  public function validar() {
    return null;
  }

}

//**************************************

final class CXAF_seo extends Componente {
  public function __construct() {
    parent::__construct("cxafseo", "ptw/paneis/pcontrol/ventas/articulo/cxaf_seo.html");

    $this->visible = false;

    $this->pon_obxeto(CXA_ficha::__text("nome_seo"));
    $this->pon_obxeto(new Textarea("notas_seo"));
    
    $this->obxeto("notas_seo")->maxlength = 255;
  }

  public function pon_articulo(Articulo_obd $a) {
     $this->obxeto("nome_seo" )->post($a->atr("nome_seo" )->valor);
     $this->obxeto("notas_seo")->post($a->atr("notas_seo")->valor);
  }

  public function completar_articulo(Articulo_obd $a) {

    $a->atr("nome_seo" )->valor = trim($this->obxeto("nome_seo" )->valor());
    $a->atr("notas_seo")->valor = trim($this->obxeto("notas_seo")->valor());

    return $a;
  }

  public function validar() {
    if (strlen(trim($this->obxeto("notas_seo")->valor())) > 255) return "Error, descrición seo, máx. 255 caracteres.";
     
    return null;
  }

}

//**************************************

final class CXAF_aux extends Componente {
  public function __construct() {
    parent::__construct("cxafaux", "ptw/paneis/pcontrol/ventas/articulo/cxaf_aux.html");

    $this->visible = false;

    $this->pon_obxeto(CXA_ficha::__text("aux_0"));
    $this->pon_obxeto(CXA_ficha::__text("aux_1"));
    $this->pon_obxeto(CXA_ficha::__text("aux_2"));

    $this->obxeto("aux_0")->style("default", "width: 100%;");
    $this->obxeto("aux_1")->style("default", "width: 100%;");
    $this->obxeto("aux_2")->style("default", "width: 100%;");
  }

  public function pon_articulo(Articulo_obd $a) {
    $this->obxeto("aux_0")->post( $a->atr("aux_0")->valor );
    $this->obxeto("aux_1")->post( $a->atr("aux_1")->valor );
    $this->obxeto("aux_2")->post( $a->atr("aux_2")->valor );
  }

  public function completar_articulo(Articulo_obd $a) {
    $a->atr("aux_0")->valor = $this->obxeto("aux_0")->valor();
    $a->atr("aux_1")->valor = $this->obxeto("aux_1")->valor();
    $a->atr("aux_2")->valor = $this->obxeto("aux_2")->valor();

    return $a;
  }

  public function validar() {
    return null;
  }
}

//**************************************

final class CXAF_publicacions extends Componente {
  const ptw_0 = "ptw/paneis/pcontrol/ventas/articulo/cxaf_pubs_1.html";

  public function __construct($id_almacen) {
    parent::__construct("cxafpubs", self::ptw_0);

    $this->pon_obxeto( new Checkbox("chcats") );
    $this->pon_obxeto( new Checkbox("chdatas") );

    $this->pon_obxeto( new Param("pcats") );

    $this->pon_obxeto( Panel_fs::__baceptar("Aplicar restricciones", "Aplica las restricciones chequeadas a la tabla de publicaciones.") );

    $this->pon_obxeto( new CXAFlistapubs() );


    $this->visible = false;
  }

  public function operacion(EstadoHTTP $e) {
    if (($e_aux = $this->obxeto("cxaflpubs")->operacion($e)) != null) return $e_aux;


    return null;
  }

  public function pon_articulo(Articulo_obd $a) {
    $this->obxeto("pcats")->post( $this->pai->obxeto("cxafcat") );

    $this->obxeto("cxaflpubs")->pon_articulo($a);
  }

  public function validar() {
    return true;
  }

  public function _pubs() {
    return $this->obxeto("cxaflpubs")->_pax;
  }

  private static function __tdata($id) {
    $s = new DataInput($id);

    $s->clase_css("default", "text");


    return $s;
  }
}

//---------------------------------

final class CXAFlistapubs extends FS_lista {

  public $id_s        = null;
  public $id_a        = null;

  public $_pax        = null;
  public $_pax_valida = null;

  public $actualizar  = false;

  public function __construct() {
    parent::__construct("cxaflpubs", new CXAFlistapubs_ehtml());

    $this->selectfrom = "select a.id_paxina, a.id_idioma, if(a.titulo is null, a.nome_paxina, a.titulo) as nome_paxina, a.estado,
                                b.id_paragrafo, b.iventas_categoria,
                                c.nome as categoria, c.id_pai as categoria_pai
                           from v_site_paxina a inner join paragrafo b on (a.id_prgf_indice = b.id_paragrafo)
                                                left  join categoria c on (a.id_site = c.id_site and b.iventas_categoria = c.id_categoria)
                            ";
    $this->orderby    = "a.nome_paxina";
    $this->where      = "1=0";
  }

  public function operacion(EstadoHTTP $e) {
    $this->_pax_valida = null;

    if (($e_aux = $this->operacion_chpax($e)) != null) return $e_aux;


    return parent::operacion($e);
  }

  public function pon_articulo(Articulo_obd $a) {
    $this->id_s = $a->atr("id_site"    )->valor;
    $this->id_a = $a->atr("id_articulo")->valor;

    $this->where  = "a.id_site = {$this->id_s} and a.tipo = 'ventas2' and estado = 'publicada'";

    $this->_pax       = APubs_obd::_paxina($a);
    $this->actualizar = false;

//~ echo $this->sql_vista();
  }

  protected function html_baleiro() {
    if (!$this->visible) return "";

    return "<div class='lista_baleira txt_adv'>El site no tiene páginas del tipo 'Venta online(2)'.</div>";
  }

  private function operacion_chpax(Epcontrol $e) {
    $evento = $e->evento();

    if (!$this->control_fslevento($evento, "chpax")) return null;


    $p = $evento->subnome(0);
    $b = $evento->subnome(1);

    $this->_pax[$p] = ($b == -1)?date("YmdHis"):-1;

    if ($this->_pax[$p] == -1) {
      $e->post_msx("El producto ya NO está publicado", "m");
    }
    else {
      $_val = $this->valida($p);

      $e->post_msx($_val[1], $_val[0]);
      
      if ($_val[0] == "e") $this->_pax[$p] = -1; //* despublicamos producto.
    }

    $this->preparar_saida($e->ajax()); //* reimprima lista (checks).


    return $e;
  }

  private function valida($id_p) {
    return APubs_obd::valida($this->id_s, $this->id_a, $id_p, $this->pai->pai->imx_proporcion, new FS_cbd());
  }
}

//---------------------------------

final class CXAFlistapubs_ehtml extends FS_ehtml {
  const style_tr   = "";

  public $nf = 0;

  public function __construct() {
    parent::__construct();

    $this->style_table = "border: 1px solid #e6e6e6;";
    $this->width       = "88%";
    $this->align       = "left";
    $this->cellspacing = 0;
    $this->cellpadding = 0;
    $this->border      = 0;
  }

  protected function cabeceira($df = null) {
    return "<tr style='background-color: #fff;'>
              <th style='width:1em;'></th>
              <th style='width:1.5em;'></th>
              <th>" . $this->__lOrdenar_html("nome_paxina", "Página")      . "</th>
            </tr>";
  }

  protected function linha_detalle($df, $f) {
    return "<tr style='background-color: #fff;'>
              <td>" . self::__check($this, $f) . "</td>
              <td>" . self::__bandeira($f['id_idioma']) . "</td>
              <td>{$f['id_paxina']} - {$f['nome_paxina']}</td>
            </tr>";
  }

  private static function __check(CXAFlistapubs_ehtml $ehtml, $f) {
    $p = $f['id_paxina'];

    if ( !isset( $ehtml->xestor->_pax[$p] ) ) $ehtml->xestor->_pax[$p] = "-1"; //* se a páxina non está rexistrada, engadímola ao rexistro.

    $b = $ehtml->xestor->_pax[$p];
    //~ $k = $p . Escritor_html::csubnome . $b;
    $k = $p . Escritor_html::csubnome . "$b";


    $ch = new Checkbox("chpax", ($b != -1));

    //~ $ch->envia_SUBMIT("onclick");
    $ch->envia_ajax("onclick");

    return $ehtml->__fslControl($ch, $k)->html();
  }

  private static function __restricion_0(CXAFlistapubs_ehtml $ehtml, $f) {
    if (($r = $f['categoria']) == 1) $r = "  --";


    return $r;
  }

  private static function __bandeira($id_idioma) {
    return "<img src='imx/bandeiras/{$id_idioma}.png' style='border: 1px solid #ccc;' />";
  }

}

