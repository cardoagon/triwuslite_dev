<?php


final class Log_eventos {
  private $l    = null;
  private $undo = null;

  public function __construct() {
    $this->limpar();
  }

  public function hai_eventos() {
    if ( !is_array($this->l) ) return false;

    return count($this->l) > 0;
  }

  public function pon(ObxetoFormulario $c, ObxetoFormulario $undo = null) {
    $kc = self::__k($c);

    if ( isset($this->undo[$kc])) {
      unset($this->l[$this->undo[$kc]]);

      return;
    }

    $this->l[$kc] = $kc;

    if ($undo == null) return;

    $this->undo[self::__k($undo)] = $kc;
  }

  public function limpar() {
    $this->l    = null;
    $this->undo = null;
  }

  public function html():string {
    if ($this->l == null) return "";

    $html = null;
    foreach ($this->l as $s) $html .= "<li>{$s}</li>";

    return "<ol>{$html}</ol>";
  }

  private static function __k(ObxetoFormulario $c) {
    return $c->nome_completo();
  }
}

