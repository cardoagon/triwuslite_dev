<?php


class Elmt_ventas extends AElemento {
  const iva_incluido = "<div style='font-size:7px; color:#000000;'>IVA incluido</div>";

  const ptw_1 = "ptw/paragrafo/elmt/elmt_ventas_post-mobil.html";
  const ptw_2 = "ptw/paragrafo/elmt/elmt_ventas_post-prsto-mobil.html";


  public $id_articulo   = null;
  public $tipo_articulo = null;

  public $hai_prsto     = false;

  public $detallado     = false;
  public $multivendedor = false;

  public $pvp_ive       = true;

  public $relacions     = true;


  public $id_site       = null;

  private $mobil        = false;

  private $unidades     = 0;
  private $descricion   = 0;

  public function __construct(Elmt_ventas_obd $pe_obd) {
    parent::__construct($pe_obd, null, "celda_elmtventas_marcado");


    $this->inicia($pe_obd->articulo_obd());

    $this->pon_obxeto(new Span("serro"));

    $this->obxeto("serro")->clase_css("default", "texto3_erro");
  }


  public static function inicia_articulo(Articulo_obd $a) {
    $ea_obd = new Elmt_ventas_obd();

    $ea_obd->atr("nome"       )->valor = $a->atr("nome"       )->valor;
    $ea_obd->atr("notas"      )->valor = $a->atr("notas"      )->valor;
    $ea_obd->atr("id_site"    )->valor = $a->atr("id_site"    )->valor;
    $ea_obd->atr("id_articulo")->valor = $a->atr("id_articulo")->valor;


    $ea = new Elmt_ventas($ea_obd);

    $ea->config_edicion();

    return $ea;
  }

  public function inicia_ptw(Config_obd $c, Site_config_obd $sc, $detallado = false, $multivendedor = false) {
    $this->detallado     = $detallado;
    $this->multivendedor = $multivendedor;

    $pvp_ive    = $sc->atr("ventas_producto_ive")->valor == 1;

    $cbd = new FS_cbd();


    $arti = Articulo_obd::inicia($cbd, $this->id_site, $this->id_articulo);

    $promo = Promo_obd::inicia_articulo($cbd, $this->id_site, $this->id_articulo);

    $this->pon_obxeto(self::__pvp      ($arti, $pvp_ive, $promo));
    $this->pon_obxeto(self::__pvpoferta($arti, $pvp_ive, $promo));
    $this->pon_obxeto(self::__doferta  ($arti, $promo));


    if ($this->unidades >= $arti->atr("pedido_min")->valor)
      $this->pon_obxeto(self::__bcarrito($this->hai_prsto, $c, $detallado));
    elseif ($sc->atr("ventas_producto_agotados")->valor == 1)
      $this->pon_obxeto(self::__bcarrito_agotado($detallado));
    else
      $this->visible = false;


    $this->pon_obxeto(self::__ldetalle($c));

    $this->pon_obxeto( self::__enome($this->enome, $this->detallado, $arti->destacado()) );
    $this->pon_obxeto( self::__describe($this->descricion, $this->detallado) );



    if (!$detallado) {
      $nptw = "elmt_ventas";

      $this->ptw = $c->ptw_elmt($nptw);

      return;
    }

    //* configuracións de elmt_ventas detallados.

    $this->pon_obxeto(new Elmt_ventas_cc());
    
    $this->obxeto("elmt_cc")->post_articulo($arti, false);
    
    

    $nptw = ($this->hai_prsto)?"elmt_ventas_detallado_p":"elmt_ventas_detallado";


    $this->ptw = $c->ptw_elmt($nptw);



    if ($this->relacions) $this->obxeto("elmtventasrel")->inicia_ptw($c, $sc, false, $multivendedor);

    $this->obxeto("ldetalle" )->visible = false; //* non se permite unha paxina detalle dunha paxina detalle.

    $this->obxeto("pvpoferta")->clase_css("default", "ventas_pvp_oferta_detallado");
    $this->obxeto("pvp"      )->clase_css("default", "ventas_pvp_detallado");
    $this->obxeto("doferta"  )->clase_css("default", "ventas_oferta_detallado");

    $this->obxeto("describe" )->clase_css("default", "ventas_entrada_detallado");
  }

  public function config_usuario(IEfspax_xestor $e = null) {
    $this->mobil = $e->config()->atr("tipo")->valor == "mobil";

    $this->obxeto("elmtventasrel")->config_usuario($e);

    $url = Paxina_reservada_obd::action_producto($this->id_articulo, $this->enome, $e->url_limpa);

    $this->obxeto("clogoarti")->url_redirect = $url;
  }

  public function config_lectura() {
    AElemento::config_lectura();

    $r = $this->detallado;

    $this->obxeto("clogoarti")->obxeto("logo")->readonly = $r;
    $this->obxeto("clogoarti")->obxeto("logo")->title    = $this->enome;

    $this->obxeto("enome")->readonly = $r;

    $this->obxeto("ldetalle")->visible  = !$r;
    $this->obxeto("ldetalle")->readonly = $r;
    $this->obxeto("ldetalle")->title    = "Ver detalle";
  }

  public function config_edicion() {
    AElemento::config_edicion();


    $this->obxeto("ldetalle")->visible = !$this->detallado;

    $this->obxeto("clogoarti")->obxeto("logo")->readonly = $this->detallado;

    $this->obxeto("enome")->readonly = $this->detallado;
  }

  public function editor() {
    return null;
  }

  protected function pon_btnr() {
    $btnr = new Btnr_elmt();

    if ($this->detallado)
      $btnr->config_visibles(false, false, false, false);
    elseif ($this->pai->orderby != 1) {
      $btnr->config_visibles(false, false, false, true);
    }
    else {
      $btnr->obxeto("bMove")->pon_eventos("onmousedown", "detalle_btnr_bElmt_mousedown(this, '{$this->ecss_marcado}')");

      $btnr->config_visibles(false, false, true, true);
    }

    $this->pon_obxeto($btnr);
  }

  public function elmt_obd() {
    $e = $this->obd_base( new Elmt_ventas_obd() );

    $e->atr("id_site"    )->valor = $this->id_site;
    $e->atr("id_articulo")->valor = $this->id_articulo;

    return $e;
  }

  public function operacion(EstadoHTTP $e) {
    //~ echo "evento:" . $e->evento()->html();

    if (!$this->visible) return null;

    $this->obxeto("serro")->post(null);

    if ($this->obxeto("bCarrito" )->control_evento()) return $this->operacion_bcarrito($e);

    if ($this->obxeto("enome"    )->control_evento()) return $this->operacion_ldetalle($e);

    if ($this->obxeto("ldetalle" )->control_evento()) return $this->operacion_ldetalle($e);


    if (($e_aux = $this->obxeto("elmt_cc")->operacion($e)) != null) return $e_aux;

    if (($e_aux = $this->obxeto("elmtventasrel")->operacion($e)) != null) return $e_aux;


    if (!$this->hai_prsto) return parent::operacion($e);


    if (($e_aux = $this->obxeto("lprsto1")->operacion($e)) != null) return $e_aux;


    if ($this->obxeto("bpvp_prsto")->control_evento()) return $this->operacion_bpvp_prsto($e);


    return parent::operacion($e);
  }

  public function operacion_bpvp_prsto(IEfspax_xestor $e, $validar = true) {
    $this->obxeto("pvp_prsto")->clase_css("readonly", "ventas_pp_pvp"); //* activase a propiedade para dar unha posible msx de erro.

    //* Calcula o prezo
    $this->pon_obxeto( $this->__pvp_prsto() );

    //* Indicamos que faltan pasos por completar
    if ($validar) {
      if (!$this->obxeto("lprsto1")->validar()) {
        $this->obxeto("pvp_prsto")->clase_css("readonly", "ventas_pp_pvp_erro"); //* activase a propiedade para dar unha msx de erro
      }
    }

    $e->ajax()->pon_ok();


    return $e;
  }

  protected function html_propio() {
    if (!$this->visible) {
      if ($this->readonly) return "";
    }

    $html = $this->obxeto("unidades_pedir")->html() . $this->html00();


    if (!$this->detallado) return $html;

    if ($this->hai_prsto) {
      //~ $this->pon_obxeto( $this->__pvp_prsto() );

      $html = str_replace("[lprsto]", $this->obxeto("lprsto1")->html(), $html);
    }

    $html .= $this->obxeto("elmtventasrel")->html();

    return $html;
  }

  public function preparar_saida(Ajax $a = null) {
    parent::preparar_saida($a);

    $this->obxeto("clogoarti")->preparar_saida($a);

    if ($a == null) return;

    $a->pon_ok(true, "elmt_contol_aux()");
  }

  private function operacion_ldetalle(IEfspax_xestor $e) {
    return $this->operacion_detalle($e);
  }

  private function operacion_detalle(IEfspax_xestor $e) {
    $r = Paxina_reservada_obd::action_producto($this->id_articulo, $this->enome, $e->url_limpa);

    $e->redirect( $r );

    return $e;
  }

  private function operacion_bcarrito(IEfspax_xestor $e) {
    $cbd = new FS_cbd();

    //~ $id_idioma = $e->id_idioma();

    $a      = Articulo_obd::inicia($cbd, $this->id_site, $this->id_articulo);
    $ucarro = $e->usuario()->cpago()->carro();

    if (!$this->hai_prsto) {
      $articulo_i = $ucarro->post($a, -1, $this->obxeto("unidades_pedir")->valor());


      $this->preparar_saida_bcarrito($e, $ucarro, $articulo_i);

      return $e;
    }

    //* para articulos de tipo "p".

    if (!$this->obxeto("lprsto1")->validar()) {
      $this->obxeto("lprsto1")->preparar_saida( $e->ajax() );

      $e->ajax()->pon_ok(true, "elmt_ventas_prsto_erro('Alguna opción obligatoria NO está seleccionada, revisa el presupuesto.')");

      return $e;
    }

    $pc = new Presuposto_carrito_obd($a);

    $pc->a_ops( $this->obxeto("lprsto1")->a_opcions() );

    $articulo_i = $ucarro->post($pc, -1, $this->obxeto("unidades_pedir")->valor());


    $this->preparar_saida_bcarrito_prsto($e, $ucarro, $articulo_i);


    return $e;
  }

  private function preparar_saida_bcarrito(IEfspax_xestor $e, Ususario_carro $c, $articulo_i, $ok = 1, $prsto = false) {
    if (($ajax = $e->ajax()) == null) return;


    $html = ($prsto)?self::ptw_2:self::ptw_1;

    $html = LectorPlantilla::plantilla( Refs::url($html) );


    $r = self::__bcarrito_resposta_json($c, $articulo_i, $html, $prsto, $e->url_limpa);


    $ajax->pon_ok(true, "elmt_ventas_postResposta({$r})");


    //* Escribe saída ajax para obxeto "ccarro"
    $e->obxeto("ccarro")->config_usuario($e);

    $e->obxeto("ccarro")->preparar_saida($ajax);
  }

  private function preparar_saida_bcarrito_prsto(IEfspax_xestor $e, Ususario_carro $c, $articulo_i, $ok = 1) {
    $this->preparar_saida($e->ajax());

    $this->preparar_saida_bcarrito($e, $c, $articulo_i, $ok, true);
  }

  public function inicia(Articulo_obd $arti) {
    $this->id_site       = $arti->atr("id_site")->valor;
    $this->id_articulo   = $arti->atr("id_articulo")->valor;
    $this->enome         = $arti->atr("nome")->valor;
    $this->tipo_articulo = $arti->atr("tipo")->valor;
    $this->hai_prsto     = $arti->hai_presuposto();

    $this->descricion    = $arti->atr("notas")->valor;


    if (($this->unidades = $arti->atr("unidades")->valor) < 0) $this->unidades = 0;


    $this->pon_obxeto(new Param("id_site"    , $this->id_site    ));
    $this->pon_obxeto(new Param("id_articulo", $this->id_articulo));

    $this->pon_obxeto(new Param("aux_0", $arti->atr("aux_0")->valor));
    $this->pon_obxeto(new Param("aux_1", $arti->atr("aux_1")->valor));
    $this->pon_obxeto(new Param("aux_2", $arti->atr("aux_2")->valor));

    $this->pon_obxeto(new CLogo_elmtventas($arti, 0));

    $this->pon_obxeto( self::__ldetalle() ); //* inicia temporalmente, evita erro ao engadir o elmt_ventas a unha páxina

    $this->pon_obxeto( self::__uds($this->unidades, false) );




      $al = $arti ->almacen_obd();
  //~ echo "<pre>" . print_r($al->a_resumo(), true) . "</pre>";

      if ($al != null ) {
        $co = $al->contacto_obd();

        $this->ini_ml($co);

        $this->ini_vendedor($al, $co, false);
      }



    $_cats = Categoria_obd::_miga($this->id_site, $arti->atr("id_categoria")->valor);
 //* echo "<pre>" . print_r($_cats, true) . "</pre>";

    $this->pon_obxeto( self::__cat(0, $_cats) );
    $this->pon_obxeto( self::__cat(1, $_cats) );
    $this->pon_obxeto( self::__cat(2, $_cats) );

    $this->pon_obxeto( new Hidden("unidades_pedir", "1") ); //* pode ser cambiado polo cliente (pex. cunha calculadora de papel de parede), antas de picar 'bCarrito'.


    $pm = ""; if ($arti->atr("pedido_min")->valor > 1) $pm = "Pedido mínimo " . $arti->atr("pedido_min")->valor . " uds.";

    $this->pon_obxeto( new Param("pedido_min",  $pm) );


    if ($this->obxeto("elmtventasrel") == null) $this->pon_obxeto(new Elmt_ventas_rel()); //* non recarga relacionados ao cambiar un cc


    $this->pon_obxeto(new Param("elmt_cc")); //* para a opción "resumo", Elmt_ventas_cc so se inicia para a opción "detallado", ( ver: this.inicia_ptw() ).


    if (!$this->hai_prsto) return;

    $this->pon_obxeto(new Elmt_ventas_lprto1($arti));

    $this->pon_obxeto( self::__bpvp_prsto( ) );
    $this->pon_obxeto( $this->__pvp_prsto($arti) );
  }

  private static function __describe($descricion, $detallado) {
    if ($detallado)
      $css = "ventas_entrada_detallado";
    else {
      $descricion = Valida::split( $descricion, 99 );

      $css = "ventas_entrada";
    }

    $d = new Div("describe", $descricion);

    $d->clase_css("default", $css);

    return $d;
  }

  private static function __uds($uds, $detallado) {
    if ($detallado)
      $css = "ventas_unidades_detallado";
    else
      $css = "ventas_unidades";


    $d = new Div("uds", "{$uds} unidades");

    $d->clase_css("default", $css);

    return $d;
  }

  public static function __enome($enome, $detallado, $destacado) {
    $d = new Div("enome", $enome);

    if ($detallado) {
      $css = "ventas_titulo_detallado";

      $d->element = "h1";
    }
    else
      $css = "ventas_titulo";

    if ($destacado) $css .= " ventas_destacado";

    $d->clase_css("default", $css);

    $d->style("default", "cursor: pointer;");
    $d->style("readonly", "cursor: initial;");

    return $d;
  }

  public static function __cat(int $k, array $_cats):Div {
    if (!isset($_cats[$k])) {
      $d = new Div("cat{$k}");
      
      $d->visible = false;
      
      return $d;
    }
    
    $d = new Div("cat{$k}", $_cats[$k][1]);
    
    $d->clase_css("default", " ventas_etq-cat{$k} ventas_etq-cat{$k}_{$_cats[$k][0]}");

    return $d;
  }

  public static function __doferta(Articulo_obd $a, Promo_obd $p = null) {
    $ok_p = false; if ($p != null) if ($p->atr("tipo_dto")->valor == "p") $ok_p = true;

    $txt = ($ok_p)?$p->html_ventas():"OFERTA";


    $d = new Div("doferta", $txt);

    $d->clase_css("default", "ventas_oferta");

//* neste caso destacamos coa palabra oferta, calquera producto que teña un desconto directo sen importar outras condicións
    if ($ok_p)
      $d->visible = (($p->atr("compra_min")->valor == 0) && ($p->atr("codigo")->valor == null));
    else
      $d->visible = ($a->atr("desc_p")->valor > 0);


    return $d;
  }

  public static function __pvp(Articulo_obd $a, $pvp_ive, Promo_obd $p = null) {
    $ok_p = false; if ($p != null) if ($p->atr("tipo_dto")->valor == "p") $ok_p = true;

    $pvp = $a->pvp(1, $pvp_ive, false, null);

    $txt = number_format($pvp, 2, ",", ".") . "&nbsp;" . Articulo_obd::$a_moeda[$a->atr("moeda")->valor];

    if ($ok_p) {
      if (($p->atr("compra_min")->valor == 0) && ($p->atr("codigo")->valor == null)) $txt = "<s>{$txt}</s>";
    }
    elseif ($a->atr("desc_p")->valor > 0) $txt = "<s>{$txt}</s>";


    $d = new Div("pvp", $txt);

    $d->clase_css("default", "ventas_pvp");


    return $d;
  }

  public static function __pvpoferta(Articulo_obd $a, $pvp_ive, Promo_obd $p = null) {
    $ok_p = false; if ($p != null) if ($p->atr("tipo_dto")->valor == "p") $ok_p = true;

    $pvp = ($ok_p)?$a->pvp(1, $pvp_ive, true, $p):$a->pvp(1, $pvp_ive, true, null);

    $txt = number_format($pvp, 2, ",", ".") . "&nbsp;" . Articulo_obd::$a_moeda[$a->atr("moeda")->valor];

    //~ if ($a->atr("pedido_min")->valor > 1) $txt .= "/ud.";

    $d = new Div("pvpoferta", $txt);

    $d->clase_css("default", "ventas_pvp_oferta");

    if ($ok_p)
      $d->visible = (($p->atr("compra_min")->valor == 0) && ($p->atr("codigo")->valor == null));
    else
      $d->visible = ($a->atr("desc_p")->valor > 0);


    return $d;
  }

  public static function __ldetalle(Config_obd $c = null) {
    $src = ($c == null)?null:$c->venta("bdetalle_arti");

    $b = self::__bimage("ldetalle", $src, "Ampliar detalle", "&nbsp;Detalle");

    $b->clase_css("default", "ventas_bdetalle");
    $b->clase_css("readonly", "ventas_bdetalle");


    return $b;
  }

  public static function __bcarrito($hai_prsto, Config_obd $c = null, $detallado = false) {
    $sdet = ($detallado)?"_detallado":"";

    $src = ($detallado)?null:$c->venta("bcarro_elmt");

    $b = self::__bimage("bCarrito", $src, "Añadir este producto al carrito de la compra", "Comprar");


    $b->clase_css("default", "ventas_bcomprar{$sdet}");

    $b->visible = ($detallado)?true:(!$hai_prsto);


    $b->pon_eventos("onclick", "elmt_ventas_post(this)");


    return $b;
  }

  public static function __bcarrito_agotado($detallado = false) {
    $sdet = ($detallado)?"_detallado":"";

    //~ $src = ($detallado)?null:$c->venta("bcarro_agtd");

    $b = self::__bimage("bCarrito", null, "Producto Agotado", "Agotado");

    $b->readonly = true;

    $b->clase_css("readonly", "ventas_bcomprar{$sdet}");
    $b->style("readonly", "cursor: initial;");


    return $b;
  }

  public static function __bimage($nome, $src, $title = null, $txt = null) {
    if ($src == null) return Panel_fs::__button($nome, $txt, true, $title);


    $imx = "<img align='center' width=99% src='" . Refs::url($src) . "' />";


    return Panel_fs::__button($nome, "{$imx}&nbsp;{$txt}", true, $title);
  }

  public static function __bcarrito_resposta_json(Ususario_carro $c, $articulo_i, $html, $prsto = false, $url_limpa = false) {
    $_cinfo = $c->_info();

    $_a = $c->a_arti($articulo_i);


    $pvp = $c->pvp($articulo_i, true, true, 1);

    //* Cálcula plantilla
    $html = str_replace("[foto]"      , $_a['url']                                   , $html);
    $html = str_replace("[nome]"      , $_a[1]                                       , $html);
    $html = str_replace("[pvp]"       , number_format($pvp, 2, ",", ".")             , $html);
    $html = str_replace("[cc]"        , $_a['describe_html']                         , $html);
    $html = str_replace("[articulo_i]", $articulo_i                                  , $html);
    $html = str_replace("[articulo_u]", $_a[3]                                       , $html);
    $html = str_replace("[lpago]"     , Paxina_reservada_obd::action_pago($url_limpa), $html);
    $html = str_replace("[carro_u]"   , $_cinfo['u']                                 , $html);
    $html = str_replace("[carro_t]"   , $_cinfo['t']                                 , $html);

    if ($prsto) {
      $html = str_replace("[prsto_html]", $_a['prsto_html'], $html);
    }


    return json_encode(array("ok" => 1, "html" => $html));
  }

  public function ini_ml(Contacto_obd $c) {
    $cbd = new FS_cbd();

    $id_concello = $c->atr("id_concello")->valor;

    $_ml = Contacto_obd::_migas_concello($id_concello, $cbd);

    $this->pon_obxeto( self::ml_1($_ml[1]) );
    $this->pon_obxeto( self::ml_2($_ml[2]) );
  }

  private function __pvp_prsto(Articulo_obd $a = null) {
    return new Param("pvp_prsto");

/*
    if ($a == null) {
      $cbd = new FS_cbd();

      $a = Articulo_obd::inicia($cbd, $this->id_site, $this->id_articulo);
    }

    $pc = new Presuposto_carrito_obd($a);

    $pc->a_ops( $this->obxeto("lprsto1")->a_opcions() );


    $d = new Span("pvp_prsto", number_format( $pc->pvp(), 2, ",", "." ) . " &euro;");

    $d->clase_css("default", "ventas_pp_pvp");


    return $d;
*/
  }

  private static function ml_1($_ml_1) {
    $d = new Div("ml_1", $_ml_1[1]);

    $d->clase_css("default", "ventas_etq-ml_1");

    //~ $d->style("default", "cursor: pointer;");
    //~ $d->style("readonly", "cursor: initial;");

    return $d;
  }

  private static function ml_2($_ml_2) {
    $d = new Div("ml_2", $_ml_2[1]);

    $d->clase_css("default", "ventas_etq-ml_2");

    //~ $d->style("default", "cursor: pointer;");
    //~ $d->style("readonly", "cursor: initial;");

    return $d;
  }

  private static function __bpvp_prsto() {
    $b = Panel_fs::__bimage("bpvp_prsto", "",
                            "Comprueba el precio de tu presupuesto",
                            false, "Total");

    $b->envia_ajax("onclick");

    $b->clase_css("default", "ventas_bcomprar_detallado ventas_bcomprar_detallado_total");


    return $b;
  }

  public function ini_vendedor(Almacen_obd $a, Contacto_obd $c, $detallado) {
    $this->pon_obxeto(self::vendedor_imx($a, $c, $detallado));
    $this->pon_obxeto(self::vendedor_rs ($a, $c, $detallado));
    $this->pon_obxeto(self::vendedor_na ($a, $detallado)    );
    $this->pon_obxeto(self::vendedor_de ($c, $detallado)    );
    $this->pon_obxeto(self::vendedor_aps($a, $detallado)    );
  }

  protected static function vendedor_imx(Almacen_obd $a, Contacto_obd $c, $detallado) {
    $css = ($detallado)?"ventas_vendedor-01-detallado":"ventas_vendedor-01";

    if (($s = $c->atr("razon_social")->valor) == null) $s = $c->atr("nome")->valor;

    $cbd = new FS_cbd();

    if (($f = $c->iclogo_src($cbd)) == null) $f = Refs::url(Imaxe_obd::noimx);


    $l = new Link("vendedor_imx",
                  "<img src='{$f}' alt='{$s}'  title='{$s}'  class='{$css}' />",
                  "_self",
                  Paxina_reservada_obd::action_almacen($a->atr("id_almacen")->valor, $s)
                 );

    return $l;
  }

  protected static function vendedor_rs(Almacen_obd $a, Contacto_obd $c, $detallado) {
    if (($s = $c->atr("razon_social")->valor) == null) $s = $c->atr("nome")->valor;

    $css = ($detallado)?"ventas_vendedor-02-detallado":"ventas_vendedor-02";

    $d = new Div("vendedor_rs", $s);

    $d->clase_css("default", $css);



    $l = new Link("vendedor_rs",
                  $d->html(),
                  "_self",
                  Paxina_reservada_obd::action_almacen($a->atr("id_almacen")->valor, $s)
                 );

    return $l;
  }

  protected static function vendedor_na(Almacen_obd $a, $detallado) {
    $css = ($detallado)?"ventas_vendedor-03-detallado":"ventas_vendedor-03";

    $d = new Div("vendedor_na", $a->contar() . " productos");

    $d->clase_css("default", $css);


    return $d;
  }

  protected static function vendedor_de(Contacto_obd $c, $detallado = false) {
    $css = ($detallado)?"ventas_vendedor-04-detallado":"ventas_vendedor-04";

    $d = new Div("vendedor_de", $c->atr("notas")->valor);

    $d->clase_css("default", $css);


    return $d;
  }

  protected static function vendedor_aps(Almacen_obd $a, $detallado = false) {
    $css = ($detallado)?"ventas_vendedor-05-detallado":"ventas_vendedor-05";

    $d = new Div("pechado", "Cerrado");

    $d->clase_css("default", $css);

    $d->visible = $a->pechado();


    return $d;
  }
}


//*****************************************************


final class Elmt_ventas_rel extends Componente {
  const ptw_0 = "ptw/paragrafo/elmt/elmt_ventas_rel.html";
  const ptw_1 = "ptw/paragrafo/elmt/elmt_ventas_post-mobil.html";

  public $ct_max = 8;

  private $id_site     = null;
  private $id_articulo = null; //* dato útil para non repetir articulo, cando a relación ven dunha páxina tipo producto.

  private $ievr = null;
  private $t    = null;

  public function __construct(IElmt_ventas_rel $ievr = null) {
    parent::__construct( "elmtventasrel", Refs::url(self::ptw_0) );

    $this->ievr    = $ievr;
    $this->visible = false;

    $this->pon_obxeto(new Hidden("_h2")); //* canal E. solicitude agregar artículo ao carriño.

    $this->obxeto("illa")->style("default", "display: inline-block; width: 100%; text-align: left;");
  }

  public function inicia_ptw(Config_obd $c, Site_config_obd $sc, $detallado = false, $multivendedor = false) {
    if ($this->t != null) return;

    $this->visible = true;

    $this->id_site     = $sc  ->atr("id_site")->valor;
    $this->id_articulo = $this->pai->id_articulo;

    $cbd = new FS_cbd();

    if ($this->ievr == null) $this->ievr = $this->inicia_ievr_predeterminado($cbd);

    if ($this->ievr == null) return;

    $this->t = $this->ievr->titulo();

    $a_arti = $this->ievr->_articulo($cbd, $this->ct_max);

    if (!is_array($a_arti)) return;

    $id_c = $this->obxeto("_h2")->nome_completo();
    $vpa  = $sc->atr("ventas_producto_agotados")->valor == 1;
    foreach ($a_arti as $k=>$x) {
      if ($k == $this->id_articulo) continue;


      $ev_clr = new Elmt_ventas_clr_resumo($this->id_site, $k, $vpa, $c, $cbd);


      $ev_clr->obxeto("cat0")->readonly = true; //* elimina filtrado
      $ev_clr->obxeto("cat1")->readonly = true; //* elimina filtrado

      $this->pon_obxeto($ev_clr, $k); //* !!! importante posición.


      $ev_clr->config_urls($id_c);
    }
  }


/*
  public function inicia_ptw(Config_obd $c, Site_config_obd $sc, $detallado = false, $multivendedor = false) {
    if ($this->t != null) return;

    $this->visible = true;

    $this->id_site     = $sc  ->atr("id_site")->valor;
    $this->id_articulo = $this->pai->id_articulo;

    $cbd = new FS_cbd();

    if ($this->ievr == null) $this->ievr = $this->inicia_ievr_predeterminado($cbd);

    if ($this->ievr == null) return;

    $this->t = $this->ievr->titulo();

    $a_arti = $this->ievr->_articulo($cbd, $this->ct_max);

    if (!is_array($a_arti)) return;

    $ct   = -1;
    $id_c = $this->obxeto("_h2")->nome_completo();
    foreach ($a_arti as $k=>$x) {
      $ct++;

      if ($k == $this->id_articulo) continue;

      $a = Articulo_obd::inicia($cbd, $this->id_site, $k);

      if ($a->baleiro()) continue;

      //~ if (($a->atr("unidades")->valor <= 0) && ($sc->atr("ventas_producto_agotados")->valor != 1)) continue;

      $ev_clr = new Elmt_ventas_clr($a, $c, $detallado, $multivendedor, $cbd);

      $ev_clr->obxeto("cat0")->readonly = true; //* elimina filtrado
      $ev_clr->obxeto("cat1")->readonly = true; //* elimina filtrado

      $this->pon_obxeto($ev_clr, $k); //* !!! importante posición.


      //* outras configuracións multivendedor.

      //* a partir de agora multivendededor ~= ventas2
      $ev_clr->config_ventas2($id_c, $a);
    }
  }
*/
  public function config_usuario(IEfspax_xestor $e = null) {
    if (!$this->visible) return;

    $a_elmt = $this->obxetos("elmt");

    if (count($a_elmt) < 1) return;

    foreach ($a_elmt as $elmt) $elmt->config_usuario($e);
  }

  public function operacion(EstadoHTTP $e) {
    if (!$this->visible) return null;

    if ($this->obxeto("_h2")->control_evento()) return $this->operacion_h2($e);

    //* pasamos eventos á relación de elmt_veta`s.
    $a_elmt = $this->obxetos("elmt");

    if (count($a_elmt) < 1) return null;

    foreach ($a_elmt as $elmt) if (($e_aux = $elmt->operacion($e)) != null) return $e_aux;


    return null;
  }

  private function operacion_h2(EstadoHTTP $e) {
    $_h2 = $this->obxeto("_h2");


    $cbd = new FS_cbd();

    $a = Articulo_obd::inicia($cbd, $this->id_site, $_h2->valor());

    $u_carro = $e->usuario()->cpago()->carro();

    $i_carro = $u_carro->post($a, -1, $a->atr("pedido_min")->valor());

    $html = LectorPlantilla::plantilla( Refs::url(self::ptw_1) );

    $json = Elmt_ventas::__bcarrito_resposta_json($u_carro, $i_carro, $html, false, $e->url_limpa);

    $e->ajax()->pon_ok( true, "elmt_ventas_postResposta({$json})" );

    return $e;
  }

  public function preparar_saida(Ajax $a = null) {
    //* 0.- hai relacions ?
    $a_elmt = $this->obxetos("elmt");

    if (count($a_elmt) < 1) {
      $this->visible = false;

      parent::preparar_saida($a);

      return;
    }


    //* 1.- saida predeterminda do componente.
    parent::preparar_saida(); //* sen ajax intencionadamente.

    $html = $this->obxeto("illa")->valor();

    //* 2.- escribimos o html específico
    $t = $this->t;
    $l = $this->html_l($a_elmt);

    $html = str_replace(array("[t]", "[l]"), array($t, $l), $html);


    //* 3.- prepara a saida
    $this->obxeto("illa")->post($html);

    if ($a == null) return;


    $a->pon($this->obxeto("illa"));

    $this->obxeto("illa")->post(null);
  }

  private function html_l($a_elmt) {
    $html_l = $this->obxeto("_h2")->html();

    foreach ($a_elmt as $elmt) $html_l .= $this->html_elmt($elmt);

    return $html_l;
  }

  private function html_elmt(Elmt_ventas $elmt) {
    if (!$elmt->visible) return "";

    return "<div class='ventas_div_c'>" . $elmt->html() . "</div>";
  }

  private function inicia_ievr_predeterminado(FS_cbd $cbd) {
    if ($this->id_articulo == null) return null;

    //~ $ievr = Articulo_grupo_obd::inicia_articulo($cbd, $this->id_site, $this->id_articulo);

    //~ if ($ievr != null) return $ievr;

    return Categoria_obd::inicia_articulo($cbd, $this->id_site, $this->id_articulo);
  }
}


//*****************************************************


class Elmt_ventas_clr extends Elmt_ventas {
  public function __construct(Articulo_obd $a, Config_obd $c, $detallado = false, $multivendedor = false, FS_cbd $cbd = null) {
    if ($cbd == null) $cbd = new FS_cbd();

    parent::__construct( self::inicia_eobd($a, $cbd) );


    $sc = Site_config_obd::inicia($cbd, $a->atr("id_site")->valor);

    $this->inicia_ptw($c, $sc, $detallado, $multivendedor);

    $this->config_lectura();
  }

  public function config_lectura() {
    AElemento::config_lectura();

    $r = $this->detallado;

    //~ $this->obxeto("clogoarti")->obxeto("logo")->readonly = $r;
    //~ $this->obxeto("clogoarti")->obxeto("logo")->title    = $this->enome;

    $this->obxeto("enome")->readonly = $r;

    $this->obxeto("ldetalle")->visible  = !$r;
    $this->obxeto("ldetalle")->readonly = $r;
    $this->obxeto("ldetalle")->title    = "Ver detalle";

    //~ $this->obxeto("clogoarti")->obxeto("logo")->envia_submit("onclick");
    $this->obxeto("enome"    )->envia_submit("onclick");
    $this->obxeto("ldetalle" )->envia_submit("onclick");
  }
/*
  public function config_cxcli_detalle(Articulo_obd $a) {

    //* outras configuracións multivendedor.
    $al = $a ->almacen_obd();
    $co = $al->contacto_obd();

    $this->ini_ml($co);

    $this->ini_vendedor($al, $co, true);

  }
*/
  public function config_ventas2($id_js, Articulo_obd $ar, Almacen_obd $al = null, $editor = false) {
    if ($editor        ) return;
    if (!$this->visible) return;


    $id_a = $ar->atr("id_articulo")->valor;

    //* configura bcomprar.
    $this->obxeto("bCarrito")->pon_eventos("onclick", "ventas2_bcarrito_post('{$id_js}', {$id_a})");

    //* configura ampliar detalle.

    $url     = $ar->action(true);
    $f_click = "document.location.href = '{$url}'";

    $this->obxeto("clogoarti")->url_redirect = $url;

    $this->obxeto("ldetalle" )->pon_eventos("onclick", $f_click);
    $this->obxeto("enome"    )->pon_eventos("onclick", $f_click);
  }

  public function prgf() {
    return $this; //* compatibilidade con Elmt_ventas_detalle::__scc() - Btnr::__ancla($this->prgf())
  }

  public static function inicia_eobd(Articulo_obd $a, FS_cbd $cbd = null) {
    //* 1.- iniciamos un prototipo Elmt_ventas_obd
    $ea_obd = new Elmt_ventas_obd();

    $ea_obd->atr("nome"       )->valor = $a->atr("nome")->valor;
    $ea_obd->atr("notas"      )->valor = $a->atr("notas")->valor;
    $ea_obd->atr("id_site"    )->valor = $a->atr("id_site")->valor;
    $ea_obd->atr("id_articulo")->valor = $a->atr("id_articulo")->valor;


    //~ echo "<pre>" . print_r($ea_obd->a_resumo(), true) . "</pre>";

    return $ea_obd;
  }
}


//*****************************************************


class Elmt_ventas_clr_resumo extends Elmt_ventas {
  private $ar   = null;
  private $_gcc = null;

  public function __construct(int $s, int $a, bool $vpa, Config_obd $c, FS_cbd $cbd = null) {
    if ($cbd == null) $cbd = new FS_cbd();

    list($this->_gcc, $this->ar) = self::inicia_gcc($cbd, $s, $a, $vpa);

    parent::__construct( self::inicia_eobd($this->ar, $cbd) );


    $sc = Site_config_obd::inicia($cbd, $s);

    $this->inicia_ptw($c, $sc, false, false);

    $this->config_lectura();
  }

  public function config_lectura() {
    AElemento::config_lectura();

    $r = $this->detallado;

    //~ $this->obxeto("clogoarti")->obxeto("logo")->readonly = $r;
    //~ $this->obxeto("clogoarti")->obxeto("logo")->title    = $this->enome;

    $this->obxeto("enome")->readonly = $r;

    $this->obxeto("ldetalle")->visible  = !$r;
    $this->obxeto("ldetalle")->readonly = $r;
    $this->obxeto("ldetalle")->title    = "Ver detalle";

    //~ $this->obxeto("clogoarti")->obxeto("logo")->envia_submit("onclick");
    $this->obxeto("enome"    )->envia_submit("onclick");
    $this->obxeto("ldetalle" )->envia_submit("onclick");
  }
/*
  public function config_cxcli_detalle(Articulo_obd $a) {

    //* outras configuracións multivendedor.
    $al = $a ->almacen_obd();
    $co = $al->contacto_obd();

    $this->ini_ml($co);

    $this->ini_vendedor($al, $co, true);

  }
*/
  public function config_urls($id_js, $editor = false) {
    if ($editor        ) return;
    if (!$this->visible) return;


    $id_a = $this->ar->atr("id_articulo")->valor;

    //* configura bcomprar.
    $this->obxeto("bCarrito")->pon_eventos("onclick", "ventas2_bcarrito_post('{$id_js}', {$id_a})");

    //* configura ampliar detalle.

    $url     = $this->ar->action(true);
    $f_click = "document.location.href = '{$url}'";

    $this->obxeto("clogoarti")->url_redirect = $url;

    $this->obxeto("ldetalle" )->pon_eventos("onclick", $f_click);
    $this->obxeto("enome"    )->pon_eventos("onclick", $f_click);
  }

  public function prgf() {
    return $this; //* compatibilidade con Elmt_ventas_detalle::__scc() - Btnr::__ancla($this->prgf())
  }

  public static function inicia_eobd(Articulo_obd $a, FS_cbd $cbd = null) {
    //* 1.- iniciamos un prototipo Elmt_ventas_obd
    $ea_obd = new Elmt_ventas_obd();

    $ea_obd->atr("nome"       )->valor = $a->atr("nome")->valor;
    $ea_obd->atr("notas"      )->valor = $a->atr("notas")->valor;
    $ea_obd->atr("id_site"    )->valor = $a->atr("id_site")->valor;
    $ea_obd->atr("id_articulo")->valor = $a->atr("id_articulo")->valor;


    //~ echo "<pre>" . print_r($ea_obd->a_resumo(), true) . "</pre>";

    return $ea_obd;
  }

  protected function html_propio() {
    $this->obxeto("elmt_cc")->post( self::html_gcc($this->_gcc) );
    
    return parent::html_propio();
  }

  private static function inicia_gcc(FS_cbd $cbd, int $id_site, int $id_articulo, bool $vpa):array {
    /* Inicia o grupo_cc, e selecciona o primeiro artículo con stock dispoñible.
     */

    $ar = Articulo_obd::inicia($cbd, $id_site, $id_articulo);

//~ print_r($ar->a_resumo());

    $_gcc = Articulo_cc_obd::_grupo_resumo($id_site, $ar->atr("id_grupo_cc")->valor, $vpa, $cbd); //* so buscamos artículos NON vpa.

//~ print_r($_gcc);

    if ($ar->atr("unidades")->valor >= $ar->atr("pedido_min")->valor) return [$_gcc, $ar]; 

    foreach($_gcc as $n => $_x) {
      if ($_x["a"] == $id_articulo) continue; //* xa foi evaluado e xa sabemos que non vale.
      
      if ($_x["u"] < $_x["m"]) continue;
      
      $ar = Articulo_obd::inicia($cbd, $id_site, $_x["a"]);
            
      return [$_gcc, $ar];
    }


    return [$_gcc, $ar]; //* se non atopamos ningún con unidades
  }

  private static function html_gcc(array $_gcc):string {
    if ($_gcc == []) return "";

//~ print_r($_gcc, 1);
    
    $html_cc = "";
    foreach($_gcc as $n => $_x) {
      $html_cc .= ($_x["u"] >= $_x["m"]) ? "<div unid='{$_x["u"]}'>{$n}</div>" : "<div class='elmt_cc__sin-stock'>{$n}</div>";
    }

    return "<div class='elmt_cc'>
              <div class='elmt_cc__btn'></div>
              <div class='elmt_cc__lista'>{$html_cc}</div>
            </div>";
  }
}

//*****************************************************


class Elmt_ventas_FSLproducto extends Elmt_ventas { //* 20250828. En deshuso.
  private $ar   = null;
  private $_gcc = null;

  public function __construct(int $s, int $a, bool $editor, bool $vpa, Config_obd $c, Site_config_obd $sc, FS_cbd $cbd = null) {
    if ($cbd == null) $cbd = new FS_cbd();

    list($this->_gcc, $this->ar) = self::inicia_gcc($cbd, $s, $a, $vpa);

    parent::__construct( self::inicia_eobd($this->ar, $cbd) );


    $this->inicia_ptw($c, $sc, false, false);
    
    $this->config_urls("", $editor);
  }

  public function config_urls($id_js, $editor = false) {
    if ($editor        ) return;
    if (!$this->visible) return;


    $id_a = $this->ar->atr("id_articulo")->valor;

    //* configura bcomprar.
    $this->obxeto("bCarrito")->pon_eventos("onclick", "ventas2_bcarrito_post('{$id_js}', {$id_a})");

    //* configura ampliar detalle.

    $url     = $this->ar->action(true);
    $f_click = "document.location.href = '{$url}'";

    $this->obxeto("clogoarti")->url_redirect = $url;

    $this->obxeto("ldetalle" )->pon_eventos("onclick", $f_click);
    $this->obxeto("enome"    )->pon_eventos("onclick", $f_click);
  }

  public function prgf() {
    return $this; //* compatibilidade con Elmt_ventas_detalle::__scc() - Btnr::__ancla($this->prgf())
  }

  public static function inicia_eobd(Articulo_obd $a, FS_cbd $cbd = null) {
    //* 1.- iniciamos un prototipo Elmt_ventas_obd
    $ea_obd = new Elmt_ventas_obd();

    $ea_obd->atr("nome"       )->valor = $a->atr("nome")->valor;
    $ea_obd->atr("notas"      )->valor = $a->atr("notas")->valor;
    $ea_obd->atr("id_site"    )->valor = $a->atr("id_site")->valor;
    $ea_obd->atr("id_articulo")->valor = $a->atr("id_articulo")->valor;


    //~ echo "<pre>" . print_r($ea_obd->a_resumo(), true) . "</pre>";

    return $ea_obd;
  }

  protected function html_propio() {
    $this->obxeto("elmt_cc")->post( self::html_gcc($this->_gcc) );
    
    return parent::html_propio();
  }

  private static function inicia_gcc(FS_cbd $cbd, int $id_site, int $id_articulo, bool $vpa):array {
    //* Inicia o grupo_cc, e selecciona o primeiro artículo con stock dispoñible.


    $ar = Articulo_obd::inicia($cbd, $id_site, $id_articulo);

//~ print_r($ar->a_resumo());

    $_gcc = Articulo_cc_obd::_grupo_resumo($id_site, $ar->atr("id_grupo_cc")->valor, $vpa, $cbd); //* so buscamos artículos NON vpa.

//~ print_r($_gcc);

    if ($ar->atr("unidades")->valor >= $ar->atr("pedido_min")->valor) return [$_gcc, $ar]; 

    foreach($_gcc as $n => $_x) {
      if ($_x["a"] == $id_articulo) continue; //* xa foi evaluado e xa sabemos que non vale.
      
      if ($_x["u"] < $_x["m"]) continue;
      
      $ar = Articulo_obd::inicia($cbd, $id_site, $_x["a"]);
            
      return [$_gcc, $ar];
    }


    return [$_gcc, $ar]; //* se non atopamos ningún con unidades
  }

  private static function html_gcc(array $_gcc):string {
    if ($_gcc == []) return "";

//~ print_r($_gcc, 1);
    
    $html_cc = "";
    foreach($_gcc as $n => $_x) {
      $html_cc .= ($_x["u"] >= $_x["m"]) ? "<div unid='{$_x["u"]}'>{$n}</div>" : "<div class='elmt_cc__sin-stock'>{$n}</div>";
    }

    return "<div class='elmt_cc'>
              <div class='elmt_cc__btn'></div>
              <div class='elmt_cc__lista'>{$html_cc}</div>
            </div>";
  }
}


//*****************************************************

final class CLogo_elmtventas extends Componente {
  public $url_redirect   = null;

  private $enome = null; //* compitibilidade con galeria2P.
  private $_imx  = null;
  private string $id_json;

  public function __construct(Articulo_obd $a) {
    parent::__construct("clogoarti");

    $this->enome = substr(md5(time()), 0, 12);

    $this->id_json = "trw-galeria" . Escritor_html::csubnome . $this->enome;

    $this->inicia_imx($a);
  }

  public function js_callback() {
    return "galeria2_iniciar(" . $this->json() . ")";
  }

  public function html():string {
    if (!$this->pai->detallado) return $this->html_resumo();

    return "<div id='{$this->id_json}'></div><script>" . $this->js_callback() . ";</script>";
  }

  public function preparar_saida(Ajax $a = null) {
    parent::preparar_saida( $a );

    if ($a == null) return;

    $a->pon_ok(true, $this->js_callback());
  }

  private function inicia_imx(Articulo_obd $a) {
//~ echo "<pre>" . print_r($a->a_resumo(), 1) . "</pre>";
    $cbd = new FS_cbd();

    $s = Site_obd::inicia($cbd, $a->atr("id_site")->valor);

    $url_s = "";
    //~ if ($this->pai->detallado) $url_s = Refs::url_sites . "/" . $s->atr("nome")->valor . "/";

    $_0 = $a->iclogo_src( $cbd );


    if (!is_array($_0)) return;

//~ echo $url_s . "\n";
//~ print_r($_0);


    $this->_imx = [];
    foreach($_0 as $url) {
//~ echo $url . "\n";
      $this->_imx[] = array("url"  => $url_s . $url,
                            "nome" => $a->atr("nome")->valor,
                            "m"    => "0"
                           );
    }
  }

  private function json() {
    $nome     = $this->enome;
    $tipo     = "p";
    $frec     = "0";
    $editor   = "1"; //* en realidade este parametro non se usa para productos
    $bd       = $this->bd_json();
    $numcols  = 1;


    return "['{$this->id_json}', {$numcols}, {$bd}, '{$nome}', {$frec}, {$editor}, '{$tipo}']";
  }

  private function bd_json() {
    return json_encode($this->_imx);

    //~ if ($this->pai->detallado) return json_encode($this->_imx);

    //~ return json_encode( array($this->_imx[0]) );
  }

  private function html_resumo() {
//~ echo $this->_imx[0]["url"]  . "\n";

    $nom = $this->_imx[0]["nome"];
    $url = $this->_imx[0]["url" ];

    $href = "";
    if ($this->url_redirect != null) $href = " href = '{$this->url_redirect}'";

    return "<a {$href}><img class='celda_imaxe_ventas' style='cursor:pointer;'  title='{$nom}' alt='{$nom}' src='{$url}'></a>";
  }
}
