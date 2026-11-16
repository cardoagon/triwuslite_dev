<?php

abstract class SubsClista extends CLista {
  public function __construct(SubsEhtml $ehtml, ?string $id, string $cbd_nome, string $ptw = null) {
    if ($id == null) $id = "clista";
    
    parent::__construct($id, $cbd_nome, $ehtml, $ptw);
  }

  public function declara_css() {
    return null;
  }

  public function operacion(EstadoHTTP $e) {
    $evento = $e->evento();

    if ($this->control_fslevento($evento, "k")) return $this->pai->op_ficha($e, $evento->subnome(0));

    return parent::operacion($e);

  }

  protected function html_baleiro() {
    if (!$this->visible) return "";

    return "<div class='lista_baleira txt_adv'>Non se atoparon coincidencias</div>";
  }
}

//*************************************

abstract class SubsEhtml extends CLista_ehtml {
  const style_tr   = "";
  const style_tr_a = "style='background-color: #ffeb92;'";
  
  protected static $ajax = true;

  public function __construct(Iterador_bd $rbd = null) {
    parent::__construct($rbd);

    $this->class_table = "lista_00";

    $this->width = "100%";
  }

  protected function style_tr($k) {
    return ($k == $this->xestor->pai->k())?self::style_tr_a:self::style_tr;
  }

  final public static function _momento(?string $m, string $f = "d-m-Y H:i:s", string $nulo = "--"):string {
    if ($m == null) return $nulo;

    return date($f, strtotime($m));
  }

  final public static function _data($d, $hhmm = false, $nulo = "--") {
    if ($d == null) return $nulo;

  //  list($d1, $d2) = explode(" ", $d);

    $dSeparado = explode(" ", $d);

    $d1 = isset($dSeparado[0]) ? $dSeparado[0] : null;
    $d2 = isset($dSeparado[1]) ? $dSeparado[1] : null;

    list($Y, $m, $d) = explode("-", $d1);

    if (!$hhmm) return "{$d}-{$m}-{$Y}";

    return "{$d}-{$m}-{$Y} " . substr($d2, 0, 5);
  }

  final public static function _hora($d) {
    list($d1, $d2) = explode(" ", $d);

    return substr($d2, 0, 5);
  }

  final public static function _check($b) {
    $t =  ($b)?"☑":"☐";

    return "<div class='lista_check_0'>{$t}</div>";
  }

  final public static function _unidades($s) {
    return "<span class='lista_unidades'>{$s}</span>";
  }

  final public static function _semaforo($tipo) {
    //* $tipo IN (0,1). 0: bermello, 1: verde.

    $color = ($tipo == 1)?"#00ba00":"#ba0000";

    return "<span style='color:{$color}; font-size: 1.5em;'>&bull;</span>";
  }

  final protected static function _lid(CLista_ehtml $ehtml, $v, ?int $l = 7, $msx = null) {
    if ($l !== null) $v = str_pad($v, $l, "0", STR_PAD_LEFT);

    $l = new Div("k", $v);

    $l->clase_css("default", "adm_enlace");

    $l->title = "Ir a {$v}";

    if (self::$ajax)
      $l->envia_ajax  ("onclick", $msx);
    else
      $l->envia_submit("onclick", $msx);

    return $ehtml->__fslControl($l, $v)->html();
  }
}

