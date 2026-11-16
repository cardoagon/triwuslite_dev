<?php

/** Encapsula eventos recibidos dende un cliente ou navegador web.
 *
 * @package tilia\controlador
 */

class EventoHTTP {
  protected $valor = null;

  public function __construct($valor) {
    $this->valor = self::parse($valor);
  }

  public function nome_completo() {
    $n0 = "";
    $t = count( $this->valor['nome'] );
    for ($i = 0; $i < $t; $i++) {
      if ($n0 != "") $n0 .= Escritor_html::cnome;

      $n0 .= $this->valor['nome'][$i];
    }

    $n1 = "";
    $t = count( $this->valor['subnome'] );
    for ($i = 0; $i < $t; $i++) {
      $n1 .= Escritor_html::csubnome . $this->valor['subnome'][$i];
    }


    return $n0 . $n1;
  }

  public function nome($i = -1) {
    if (!isset($this->valor['nome'])) return null;

    if ($i == -1) return $this->valor['nome'];

    if (!isset($this->valor['nome'][$i])) return null;

    return $this->valor['nome'][$i];
  }

  public function subnome($i = -1) {
    if ( !isset($this->valor['subnome']) ) return null;

    if ($i == -1) return $this->valor['subnome'];

    if ( !isset($this->valor['subnome'][$i]) ) return null;

    return $this->valor['subnome'][$i];
  }

  public function tipo() {
    if (!isset($this->valor['tipo'])) return null;

    return strtolower($this->valor['tipo']);
  }

  public function ajax($ajax = -1) {
    if ($ajax != -1) $this->valor['ajax'] = $ajax;

    if (!isset($this->valor['ajax'])) return false;

    return $this->valor['ajax'] == 1;
  }

  public function control_str($str) {
    $a_str = self::parse($str);

    if (count($this->valor['nome']) != count($a_str['nome'])) return false;

    for($i = 0; $i < count($this->valor['nome']); $i++) {
      if ($this->valor['nome'][$i] != $a_str['nome'][$i]) return false;
    }

    if (!isset($a_str['subnome'])) return true;
    if (!is_array($a_str['subnome'])) return true;

    for($i = 0; $i < count($this->valor['subnome']); $i++)
      if ($this->valor['subnome'][$i] != $a_str['subnome'][$i]) return false;

    if ($i == 0) return false;


    return 2;
  }

  public function control_ohttp(ObxetoFormulario $o) {
    return $this->control_str($o->nome_completo());
  }

  private function parse($str) {
    if ($str === null) return null;

    $a_valor = null; $str2 = null; $str3 = null;

    if (strpos($str, Escritor_html::csubnome . "tipo=") === FALSE) {
      $str2 = $str;
      $str3 = null;
    }
    else
      list($str2, $str3) = explode(Escritor_html::csubnome . "tipo=", $str);

    if ($str3 == null);
    elseif (strpos($str3, Escritor_html::csubnome . "ajax=") === FALSE) {
      $a_valor['tipo'] = $str3;
      $a_valor['ajax'] = null;
    }
    else
      list($a_valor['tipo'], $a_valor['ajax']) = explode(Escritor_html::csubnome . "ajax=", $str3);

    $a_valor['nome'] = explode(Escritor_html::cnome, $str2);

    $a_subnome = explode(Escritor_html::csubnome, $a_valor['nome'][count($a_valor['nome']) - 1]);

    if (count($a_subnome) < 2) return $a_valor;

    for($i = 1; $i < count($a_subnome); $i++) $a_valor['subnome'][] = $a_subnome[$i];

    $a_valor['nome'][count($a_valor['nome']) - 1] = $a_subnome[0];


    return $a_valor;
  }

  final public function html($etq = "pre") {
    if ($etq == null) return print_r($this, true);

    return "<{$etq}>" . print_r($this, true) . "</{$etq}>";
  }
}


/*****************
 *
 * @package tilia\controlador
 */

class EventoHTTP_nulo extends EventoHTTP {

  public function __construct() {
    parent::__construct(null);
  }

  public function nome_completo() {
    return null;
  }

  public function control_str($str) {
    return false;
  }

  public function control_ohttp(ObxetoFormulario $o) {
    return $this->control_str(null);
  }
}

