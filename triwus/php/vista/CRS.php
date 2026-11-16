<?php

final class CRS extends Componente {

  private $css_marco = "CRS_pax_marco";
  private $css_ico   = "0"; //* $css_ico IN ('0', 'novas')

  public $id_crs;

  public $rs_activado;

  public $rs_facebook;
  public $rs_twitter ;
  public $rs_linkedin;
  public $rs_whatsapp;

  private $disparador = null;

  public function __construct(CRS_obd $crs_obd = null, $novas = false) {
    parent::__construct("crs");

    $this->pon_obxeto(new Link("rs_facebook_url", null, "_blank"));
    $this->pon_obxeto(new Link("rs_twitter_url" , null, "_blank"));
    $this->pon_obxeto(new Link("rs_linkedin_url", null, "_blank"));
    $this->pon_obxeto(new Link("rs_whatsapp_url", null, "_blank"));

    $p = ($novas)?"this":"";

    $this->obxeto("rs_facebook_url")->pon_eventos("onclick", "trw2facebook({$p})");
    $this->obxeto("rs_twitter_url" )->pon_eventos("onclick", "trw2twitter ({$p})");
    $this->obxeto("rs_linkedin_url")->pon_eventos("onclick", "trw2linkedin({$p})");
    $this->obxeto("rs_whatsapp_url")->pon_eventos("onclick", "trw2whatsapp({$p})");

    $this->crs_obd($crs_obd);
  }

  public function css_marco($css_marco = null) {
    //* para que xurda efecto a operacion POST, debemos chamar a esta función antes de config_usuario() ou config_rs()

    if ($css_marco == null) return $this->css_marco;

    $this->css_marco = $css_marco;
  }

  public function css_ico($css_ico = null) {
    //* para que xurta efecto a operacion POST, debemos chamar a esta funci&oacute;n antes de config_usuario();

    if ($css_ico == null) return $this->css_ico;

    $this->css_ico = $css_ico;
  }

  public function config_usuario(IEfspax_xestor $e = null) {
    $this->config_rs( $e->config() );
  }

  public function config_rs(Config_obd $c) {
    $a_link = $this->obxetos("rs_");

    if (count($a_link) == 0) return;

    foreach ($a_link as $k=>$l) {
      if ($k == "rs_whatsapp_url") {
        $l->visible = $c->atr("tipo")->valor == "mobil";
      }

      $l->post( self::__rs_link_imx_html($k, $c, $this->css_ico) );
    }
  }

  public function pon_disparador(Control $c) {
    //* non funciona se o usamos dende o constructor.
    //* engadimos ao disparador $c, os eventos necesarios para mostrar/ocultar o CRS

    $this->disparador = $c;


    //~ $this->obxeto("illa")->style("default", "display: none;");

    $this->obxeto("illa")->pon_eventos("onmouseover", "crs_onmouseover(this)");
    $this->obxeto("illa")->pon_eventos("onmouseout" , "crs_onmouseout(this)");

    $src = $this->obxeto("illa")->nome_completo();

    $c->pon_eventos("onmouseover", "crs_disparador_onmouseover('{$src}')");
    $c->pon_eventos("onmouseout" , "crs_disparador_onmouseout('{$src}')" );
  }

  public function crs_obd(CRS_obd $crs_obd = null) {
    if ($crs_obd != null) {
      $this->id_crs      = $crs_obd->atr("id_crs")->valor;

      $this->rs_activado = $crs_obd->atr("rs_activado")->valor;

      $this->rs_facebook = $crs_obd->atr("rs_facebook")->valor;
      $this->rs_twitter  = $crs_obd->atr("rs_twitter" )->valor;
      $this->rs_linkedin = $crs_obd->atr("rs_linkedin")->valor;
      $this->rs_whatsapp = $crs_obd->atr("rs_whatsapp")->valor;
   }

    $crs_obd = new CRS_obd($this->id_crs);

    $crs_obd->atr("rs_activado")->valor = $this->rs_activado;

    $crs_obd->atr("rs_facebook")->valor = $this->rs_facebook;
    $crs_obd->atr("rs_twitter" )->valor = $this->rs_twitter;
    $crs_obd->atr("rs_linkedin")->valor = $this->rs_linkedin;
    $crs_obd->atr("rs_whatsapp")->valor = $this->rs_whatsapp;


    return $crs_obd;
  }

  public function preparar_saida(Ajax $a = null) {
    $html = null;

    if ($this->rs_activado) {
      if ($this->rs_facebook) $html .= $this->obxeto("rs_facebook_url")->html();
      if ($this->rs_twitter ) $html .= $this->obxeto("rs_twitter_url" )->html();
      if ($this->rs_linkedin) $html .= $this->obxeto("rs_linkedin_url")->html();
      if ($this->rs_whatsapp) $html .= $this->obxeto("rs_whatsapp_url")->html();

      $html = "<div class='{$this->css_marco}'>{$html}</div>";
    }

    $this->obxeto("illa")->post($html);

    if ($a == null) return;


    $a->pon($this->obxeto("illa"));

    $this->obxeto("illa")->post(null);
  }

  private static function __rs_link_imx_html($id, Config_obd $c, $css_ico) {
    //* para que xurta efecto a operacion POST, debemos chamar a esta funci&oacute;n antes de config_usuario();
    if ($c->atr("rs_tipo")->valor <  100) return self::__rs_link_png_html($id, $c, $css_ico);


    return self::__rs_link_svg_html($id, $c, $css_ico);
  }

  private static function __rs_link_png_html($id, Config_obd $c, $css_ico) {
    $css = ($css_ico != "0")?"CRS_novas_ico":"CRS_pax_ico";

    $imx = new Image("{$id}_imx", Refs::url($c->rs($id)), false);

    $imx->clase_css("default" , $css);
    $imx->clase_css("readonly", $css);

    return $imx->html();
  }

  private static function __rs_link_svg_html($id, Config_obd $c, $css_ico) {
    $css = ($css_ico != "0")?"CRS_svg_novas":"CRS_svg";


    $svg = file_get_contents( Refs::url($c->rs($id)) );

    $svg = str_replace("[--class--]", "class='{$css}'", $svg);


    return $svg;
  }
}

//************************************

final class CRS_0 extends Componente {
  public function __construct(Site_config_obd $sc, Config_obd $c) {
    parent::__construct("crs_0");

    $this->pon_rs_link($sc, $c, "rs_facebook_url");
    $this->pon_rs_link($sc, $c, "rs_bluesky_url");
    $this->pon_rs_link($sc, $c, "rs_twitter_url");
    $this->pon_rs_link($sc, $c, "rs_linkedin_url");
    $this->pon_rs_link($sc, $c, "rs_vimeo_url");
    $this->pon_rs_link($sc, $c, "rs_flickr_url");
    $this->pon_rs_link($sc, $c, "rs_tumblr_url");
    $this->pon_rs_link($sc, $c, "rs_youtube_url");
    $this->pon_rs_link($sc, $c, "rs_instagram_url");
    $this->pon_rs_link($sc, $c, "rs_tripadvisor_url");
    $this->pon_rs_link($sc, $c, "rs_pinterest_url");
    $this->pon_rs_link($sc, $c, "rs_blogger_url");
    $this->pon_rs_link($sc, $c, "rs_telegram_url");
    $this->pon_rs_link($sc, $c, "rs_tiktok_url");
    $this->pon_rs_link($sc, $c, "rs_spotify_url");
    $this->pon_rs_link($sc, $c, "rs_applemusic_url");


    $this->obxeto("illa")->clase_css("default", "CRS_0_marco");


    //~ $a_rs = $this->obxetos("rs_");
    //~ if (!is_array($a_rs)) return;
    //~ foreach($a_rs as $o) $html .= $o->style("default", "float: left;");
  }

  public function preparar_saida(Ajax $a = null) {
    $a_rs = $this->obxetos("rs_");

    if (count($a_rs) == 0) return parent::preparar_saida($a);


    $html = null; foreach($a_rs as $o) $html .= $o->html();


    $this->obxeto("illa")->post($html);

    if ($a == null) return;


    $a->pon($this->obxeto("illa"));

    $this->obxeto("illa")->post(null);
  }

  private function pon_rs_link(Site_config_obd $sc, Config_obd $c, $id) {
    if ($sc->atr($id)->valor == null) return;

    if ($c->atr("rs_tipo")->valor <  100) return $this->pon_rs_link_png($sc, $c, $id);


    return $this->pon_rs_link_svg($sc, $c, $id);
  }

  private function pon_rs_link_png(Site_config_obd $sc, Config_obd $c, $id) {
    $url_imx = Refs::url($c->rs($id));

    $l = new Link($id, "<img src='{$url_imx}' alt='{$sc->atr($id)->valor}' class='CRS_0_ico'/>", "_blank", $sc->atr($id)->valor);

    $l->title = $sc->atr($id)->valor;

    $this->pon_obxeto( $l );
  }

  private function pon_rs_link_svg(Site_config_obd $sc, Config_obd $c, $id) {
    $svg = str_replace( "[--class--]", "class='CRS_svg'", file_get_contents(Refs::url($c->rs($id))) );

    $l = new Link($id, $svg, "_blank", $sc->atr($id)->valor);

    $l->title = $sc->atr($id)->valor;

    $this->pon_obxeto( $l );
  }
}


//************************************


final class CRS_editor extends Componente {
  const ptw_1 = "ptw/paragrafo/elmt/crs_editor_1.html";
  const ptw_2 = "ptw/paragrafo/elmt/crs_editor_2.html";


  public $crs_obd;

  public  $ptw_detalle; //* para integracion con botonera oeste
  private $ptw_aux;     //* para integracion con botonera oeste

  public function __construct(CRS_obd $crs_obd) {
    parent::__construct("crs_editor", self::ptw_1);


    $this->crs_obd = $crs_obd;

    $this->pon_obxeto(new Select("rs_activado",  array("0"=>"Non", "1"=>"Si")));

    $this->obxeto("rs_activado")->post($crs_obd->atr("rs_activado")->valor);

    //~ $this->obxeto("rs_activado")->envia_submit("onchange");
    $this->obxeto("rs_activado")->envia_ajax("onchange");


    $this->pon_obxeto(new Checkbox("rs_facebook", $crs_obd->atr("rs_facebook")->valor != 0));
    $this->pon_obxeto(new Checkbox("rs_twitter" , $crs_obd->atr("rs_twitter" )->valor != 0));
    $this->pon_obxeto(new Checkbox("rs_linkedin", $crs_obd->atr("rs_linkedin")->valor != 0));
    $this->pon_obxeto(new Checkbox("rs_whatsapp", $crs_obd->atr("rs_whatsapp")->valor != 0));


    $this->pon_obxeto(Panel_fs::__bapagado("bEditar", null, "Editar Compartir en Redes Sociales", true, "Editar"));

    $this->obxeto("bEditar")->visible = $crs_obd->atr("rs_activado")->valor != 0;

    $this->obxeto("bEditar")->envia_ajax("onclick");
  }

  public function operacion(EstadoHTTP $e) {
    if ($this->obxeto("bEditar"    )->control_evento()) return $this->operacion_bEditar($e);

    if ($this->obxeto("rs_activado")->control_evento()) return $this->operacion_rs_activado($e);

    return null;
  }

  public function operacion_aceptar(Epaxina_edit $e) {
    //* para integracion con botonera oeste
    if ($this->ptw_aux == null) return null;

    $this->crs_obd->atr("rs_activado")->valor = $this->obxeto("rs_activado")->valor();

    $this->crs_obd->atr("rs_facebook")->valor = ($this->obxeto("rs_facebook")->valor())?"1":"0";
    $this->crs_obd->atr("rs_twitter" )->valor = ($this->obxeto("rs_twitter" )->valor())?"1":"0";
    $this->crs_obd->atr("rs_linkedin")->valor = ($this->obxeto("rs_linkedin")->valor())?"1":"0";
    $this->crs_obd->atr("rs_whatsapp")->valor = ($this->obxeto("rs_whatsapp")->valor())?"1":"0";

    $this->pai->ptw = $this->ptw_aux;

    $this->ptw_aux  = null;

    $this->ptw      = self::ptw_1;


    return $e;
  }

  public function modo($resumo = true, $ptw_detalle = null) {
    if ($ptw_detalle       != null) $this->ptw_detalle = $ptw_detalle;
    if ($this->ptw_detalle == null) $this->ptw_detalle = Edita_elmt::ptw_crs;

    if ($resumo) {
      if ($this->ptw_aux == null) return;

      $this->pai->ptw = $this->ptw_aux;

      $this->ptw_aux  = null;

      $this->ptw      = self::ptw_1;
    }
    else {
      $this->ptw_aux  = $this->pai->ptw;

      $this->pai->ptw = $this->ptw_detalle;

      $this->ptw      = self::ptw_2;
    }
  }

  public function operacion_cancelar(Epaxina_edit $e) {
    //* para integracion con botonera oeste
    if ($this->ptw_aux == null) return null;

    $this->obxeto("rs_facebook")->valor = $this->crs_obd->atr("rs_facebook")->valor != 0;
    $this->obxeto("rs_twitter" )->valor = $this->crs_obd->atr("rs_twitter" )->valor != 0;
    $this->obxeto("rs_linkedin")->valor = $this->crs_obd->atr("rs_linkedin")->valor != 0;
    $this->obxeto("rs_whatsapp")->valor = $this->crs_obd->atr("rs_whatsapp")->valor != 0;

    $this->modo(true);

    $this->pai->preparar_saida($e->ajax());


    return $e;
  }

  public function operacion_rs_activado(Efs_admin $e) {
    $this->crs_obd->atr("rs_activado")->valor = $this->obxeto("rs_activado")->valor();

    if ($this->obxeto("rs_activado")->valor() == "1") {
      $this->obxeto("bEditar")->visible = true;


      return $this->operacion_bEditar($e);
    }

    $this->obxeto("bEditar")->visible = false;

    $this->preparar_saida($e->ajax());

    return $e;
  }

  public function operacion_bEditar(Efs_admin $e) {
    $this->modo(false);

    $this->pai->preparar_saida($e->ajax());


    return $e;
  }
}

