<?php


abstract class Editor_selmt extends Editor_style {
  public function __construct(Elmt_obd $elmt, ?string $ptw = null) {
    parent::__construct($elmt->style_obd(), $ptw);
  }
}

//---------------------------------------------------

final class Editor_shr extends Editor_selmt {

  public function __construct(HR_obd $elmt) {
    parent::__construct($elmt, "ptw/style/editor_shr.html");

  }

  public function operacion(EstadoHTTP $e) {
    if (($e_aux = $this->obxeto("scolor")->operacion($e)) != null) {
      $this->pai->pai->preparar_saida($e_aux->ajax());

      return $e_aux;
    }

    return parent::operacion($e);
  }

  protected function a_props():?array {
    if (($_o = $this->obxetos()) == null) return null;

    $_a = null;

    if (($color  = $_o["scolor"]->valor()) != null) $_a["background-color"] = $color;
    if (($height = $_o["height"]->valor()) != null) $_a["height"          ] = "{$height}px";
    if (($width  = $_o["width" ]->valor()) != null) $_a["width"           ] = "{$width}%";
    if (($align  = $_o["align" ]->valor()) != null) $_a["align"           ] = Editor_style::$a_eah[$align];
    if (($mtop   = $_o["mtop"  ]->valor()) != null) $_a["mtop"            ] = "{$mtop}px";

    $_a["border-radius"] = ( ($_o["bordes"]->valor())?round($height / 2):"0" ) . "px";

    return $_a;
  }

  protected function post_style(Style_obd $s):void {
    $a_props = $s->a_props();

    //~ echo "<pre>" . print_r($a_props, true) . "</pre>";

    if ($a_props["align"           ] == null) $a_props["align"            ] = "center";
    if ($a_props["mtop"            ] == null) $a_props["mtop"             ] = 3;
    if ($a_props["height"          ] == null) $a_props["height"           ] = 3;
    if ($a_props["width"           ] == null) $a_props["width"            ] = 97;
    if ($a_props["background-color"] == null) $a_props["background-color" ] = "#333333";
    if ($a_props["border-radius"   ] == null) $a_props["border-radius"    ] = "0px";

    $this->pon_obxeto(self::__ralign_h("align" , $a_props["align"]));
    $this->pon_obxeto(self::__number  ("height",  Style_obd::__parseInt($a_props["height"]), 1, 15 , 1));
    $this->pon_obxeto(self::__number  ("mtop"  ,  Style_obd::__parseInt($a_props["mtop"]  ), 1, 100, 1));
    $this->pon_obxeto(self::__number  ("width" ,  Style_obd::__parseInt($a_props["width"] ), 1, 100, 1, "%"));
    $this->pon_obxeto(self::__scolor  ("scolor",  $a_props["background-color"]));
    $this->pon_obxeto(new Checkbox    ("bordes",  $a_props["border-radius"] != "0px"));


    //~ $id_elmt = $this->pai->elmt->nome_completo();

    //~ $this->obxeto("height")->pon_eventos("onblur", "elmt_hr_previa('{$id_elmt}')");
    //~ $this->obxeto("width" )->pon_eventos("onblur", "elmt_hr_previa('{$id_elmt}')");
  }
}

//---------------------------------------------------

final class Editor_simx extends Editor_selmt {
  public $escala_h = null;
  public $escala_w = null;

  public function __construct(Imaxe_obd $elmt) {
    parent::__construct($elmt, "ptw/style/editor_simx.html");
  }

  public function operacion(EstadoHTTP $e) {
    if ($this->obxeto("align")->control_evento()) {
      $this->pai->obxeto("tdvp_align")->post($this->align_h());

      $this->pai->pai->preparar_saida($e->ajax());

      return $e;
    }

    return parent::operacion($e);
  }

  protected function a_props():?array {
    if (($a_obx = $this->obxetos()) == null) return null;

    $a = null;

    if (($align  = $a_obx['align']->valor())  != null) $a["align"] = Editor_style::$a_eah[$align];
    
    if (($width = $a_obx['width']->valor()) != null) {
      $width = Erro::valida_numero($width);


      $a["width"] = "{$width}%";
    }

    return $a;
  }

  protected function post_style(Style_obd $s):void {
    $a_props = $s->a_props();

    if ($a_props['align'] == null) $a_props['align'] = "center";
    
    $a_props['width'] = ($a_props['width'] == null)?100:str_replace("%", "", $a_props['width']);

    $this->pon_obxeto(self::__ralign_h("align", $a_props['align']));
    $this->pon_obxeto(self::__number("width",  $a_props['width'], 1, 100, 1, "%"));

    $this->obxeto("align")->envia_AJAX("onclick");
  }
}

//---------------------------------------------------

final class Editor_svcard extends Editor_selmt {
  public function __construct(Imaxe_obd $elmt) {
    parent::__construct($elmt, "ptw/style/editor_svcard.html");
  }

  protected function a_props():?array {
    if (($_obx = $this->obxetos()) == null) return null;

    if (($align = $_obx['align']->valor()) == null)

    $_ = null;

    $_["align"] = Editor_style::$a_eah[$align];

    return $_;
  }

  protected function post_style(Style_obd $s):void {
    $_p = $s->a_props();

    if ($_p['align'] == null) $_p['align'] = "center";

    
    $this->pon_obxeto( self::__ralign_h("align", $_p['align']) );
  }
}

//---------------------------------------------------

final class Editor_senlace extends Editor_selmt {

  public function __construct(Iframe_obd $elmt) {
    parent::__construct($elmt, "ptw/style/editor_senlace.html");
  }

  public function operacion(EstadoHTTP $e) {
    if ($this->obxeto("align")->control_evento("onchange")) {
      $this->pai->obxeto("tdvp_align")->post($this->align_h());

      $this->pai->pai->preparar_saida($e->ajax());

      return $e;
    }

    return parent::operacion($e);
  }

  protected function a_props():?array {
    if (($a_obx = $this->obxetos()) == null) return null;

    $a = null;

    $uds_h = $a_obx['uds_h']->valor();
    $uds_w = $a_obx['uds_w']->valor();

    if (($height = round( $a_obx['height']->valor()) ) != null) $a["height"]  = "{$height}{$uds_h}";
    if (($width  = round( $a_obx['width']->valor()) )  != null) $a["width"]   = "{$width}{$uds_w}";
    if (($align  = $a_obx['align']->valor())           != null) $a["align"]   = Editor_style::$a_eah[$align];

    return $a;
  }

  protected function post_style(Style_obd $s):void {
    $a_props = $s->a_props();

    $uds_h = "px";
    $uds_w = "px";

    $h = $a_props['height'];
    $w = $a_props['width'];

    if (!isset($a_props['align'])) $a_props['align'] = "center";

    if ($w == null)
      $w = IFrame_enlace::min_w;
    else {
      $w = str_replace("px", "", $w);

      $uds_w = ($w != $a_props['width'])?"px":"%";
    }

    if ($h == null)
      $h = IFrame_enlace::min_h;
    else {
      $h = str_replace("px", "", $h);

      $uds_h = ($h != $a_props['height'])?"px":"%";
    }

    $this->pon_obxeto(self::__ralign_h("align", $a_props['align']));
    $this->pon_obxeto(self::__number("width", $w, 1, 9999, 10));
    $this->pon_obxeto(self::__number("height", $h, 1, 9999, 10));

    $this->pon_obxeto( self::__sunidades("uds_h", $uds_h) );
    $this->pon_obxeto( self::__sunidades("uds_w", $uds_w) );

    $this->obxeto("align")->envia_AJAX("onclick");
  }
}

//---------------------------------------------------

final class Editor_sdifucom2 extends Editor_selmt {

  public function __construct(Difucom2_obd $elmt) {
    parent::__construct($elmt, "ptw/style/editor_sdifucom2.html");
  }

  protected function a_props():?array {
    if (($a_obx = $this->obxetos()) == null) return null;

    $a = null;

    if (($align = $a_obx['align']->valor()) != null) $a["align"] = Editor_style::$a_eah[$align];

    return $a;
  }

  protected function post_style(Style_obd $s):void {
    $a_props = $s->a_props();

    if (!isset($a_props['align'])) $a_props['align'] = "left";


    $this->pon_obxeto(self::__ralign_h("align", $a_props['align']));
  }
}
