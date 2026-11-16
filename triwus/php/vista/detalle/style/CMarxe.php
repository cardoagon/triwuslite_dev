<?php

class CMarxe extends Componente {
  const ptw_0 = "ptw/style/cmarxe_0.html";

  public $min  = 0;
  public $max  = 999;
  public $step = 1;

  public function __construct($nome = "cmarxe", $css = "0 0 0 0") {
    parent::__construct($nome, Refs::url(self::ptw_0));

    self::__number("t", $this);
    self::__number("r", $this);
    self::__number("b", $this);
    self::__number("l", $this);

    $this->post_css($css);
  }

  public function post_css($css) {
    if ($css == null) $css = "0 0 0 0";

    list($t, $r, $b, $l) = explode(" ", $css);

    $this->obxeto("t")->post( $this->__parseInt($t) );
    $this->obxeto("r")->post( $this->__parseInt($r) );
    $this->obxeto("b")->post( $this->__parseInt($b) );
    $this->obxeto("l")->post( $this->__parseInt($l) );
  }

  public function valor() {
    $t = $this->__parseInt( $this->obxeto("t")->valor() );
    $r = $this->__parseInt( $this->obxeto("r")->valor() );
    $b = $this->__parseInt( $this->obxeto("b")->valor() );
    $l = $this->__parseInt( $this->obxeto("l")->valor() );

    if (($t + $r + $b + $l) == 0) return null;

    return "{$t}px {$r}px {$b}px {$l}px";
  }

  public function html():string {
    return $this->html00();
  }

  public function preparar_saida(Ajax $a = null) {
    if ($a == null) return;

    $a->pon($this->obxeto("t"));
    $a->pon($this->obxeto("r"));
    $a->pon($this->obxeto("b"));
    $a->pon($this->obxeto("l"));
  }

  private static function __number($id, CMarxe $cm) {
    $n = Panel_fs::__number($id, $cm->min, $cm->max, $cm->step);

    $n->maxlength = 3;

    $n->style("default", "font-size: 11px; width:59px;");

    $cm->pon_obxeto($n);
  }

  private function __parseInt($a) {
    $a = Style_obd::__parseInt($a);

    if ($a < $this->min) return "{$this->min}";
    if ($a > $this->max) return "{$this->max}";


    return $a;
  }
}

