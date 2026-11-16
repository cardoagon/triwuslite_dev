<?php

class Elmt_logo extends Elmt_imx {
  public $editor_titulo = "Editar Logo";

  protected $max_w;
  protected $max_h;

  public function __construct(Imaxe_obd $pe_obd) {
    parent::__construct($pe_obd);

    if (($btnr = $this->obxeto("btnr")) != null) $btnr->config_e();
  }

  public function pon_maxwh(Config_obd $c) {
    $this->max_w = $c->atr("logo_w")->valor;
    $this->max_h = $c->atr("logo_h")->valor;
  }

  public function elmt_obd() {
    $src_0    = $this->obxeto("imaxe")->src();
    $src_nula = Imaxe_obd::url_nula();
    
    $imx = new Logo_obd();
    
    if ( $src_0 != $src_nula ) $imx->post_url($src_0);

    $imx->atr("link_href"  )->valor = $this->link_href;
    $imx->atr("link_target")->valor = $this->link_target;

    return $this->obd_base($imx);
  }

  public function editor() {
    return new Editor_elogo($this, $this->max_w, $this->max_h, $this->editor_titulo);
  }

  protected function html_propio() {
    $src = $this->src();

    //~ if ($this->readonly) if ($src == Imaxe_obd::noimx) return "";

    if (is_file($src)) {
      $a_css = Style_obd::__props($this->ecss);

      if (($escala = Editor_style::__parseN($a_css['width'])) == null) $escala = 100;

      list($w, $h, $x) = getimagesize($src);

      $w *= ($escala / 100);
      $h *= ($escala / 100);
    }
    else {
      $w = $this->max_w;
      $h = $this->max_h;
    }

    $w = round($w);
    $h = round($h);

    $this->obxeto("imaxe")->style("default", "width: {$w}px;");

    if (($l = $this->obxeto("link")) == null) return $this->obxeto("imaxe")->html();

    $l->post($this->obxeto("imaxe")->html());

    return $l->html();
  }

  protected function pon_btnr() {
    $btnr = new Btnr_elmt();

    $btnr->config_visibles(true, false, false, false);

    $this->pon_obxeto( $btnr );
  }
}

//*******************************

class Editor_elogo extends Editor_eimx {
  protected $max_w;
  protected $max_h;

  public function __construct(Elmt_imx $elmt, $max_w, $max_h, $titulo) {
    parent::__construct($elmt, Ico::bimaxe, $titulo);

    $this->max_w = $max_w;
    $this->max_h = $max_h;
  }

  protected function recibir_arquivo(Efs_admin $e) {
    parent::recibir_arquivo($e);

    $escala = self::__escala_ideal($this->obxeto("imaxe")->src(), $this->max_w, $this->max_h);

    $this->obxeto("cstyle")->obxeto("width")->post($escala);
  }

  public static function __escala_ideal($src, $max_w, $max_h, $maximizar = true) {
    list($w, $h, $x) = getimagesize($src);

    //~ echo "(w, h)=($w, $h)<br />";


    $escala_w = 1;
    if ( ($w < ($max_w * .5)) || ($w > $max_w) ) $escala_w = $max_w / $w;

    //~ echo "escala_w($escala_w)=(" . $w * $escala_w . ", " . $h * $escala_w . ")<br />";


    $escala_h = 1;
    if ( ($h < ($max_h * .5)) || ($h > $max_h) ) $escala_h = $max_h / $h;

    //~ echo "escala_h($escala_h)=(" . $w * $escala_h . ", " . $h * $escala_h . ")<br />";


    $escala = round(min($escala_w, $escala_h) * 100);

    if ((!$maximizar) && ($escala > 100)) return 100;

    return $escala;
  }
}

