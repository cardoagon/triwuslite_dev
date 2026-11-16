<?php

/** Un Componente Tilia Framework, permite agrupar conxunto ObxetoFormularios, deinindo unha lóxica interna e operativide propia.
 * - As veces, podemos agrupar de forma lóxica, conxuntos de ObxetoFormularios que colaboran na realización dalgunha tarefa.
 * - Por exemplo, podemos ver un Calendario, como unha conxunto de Etiquetas, Botons, Selectores, ... etc,
 *   que permiten a un usuario seleccionar unha data dunha forma máis 'natural'.
 *
 * @package tilia\vista\componentes
 */

abstract class Componente extends ObxetoFormulario {

  private $obxetos = null;

  protected function __construct($nome, $ptw = null) {
    parent::__construct($nome);

    $this->ptw = $ptw;

    $this->pon_obxeto(new IllaAJAX("illa"));
  }

  public function declara_css() {
    $a = parent::declara_css();

    if ($this->obxetos == null) return $a;

    foreach ($this->obxetos as $o) {
      if (($a_o = $o->declara_css()) == null) continue;

      foreach($a_o as $css) $a[] = $css;
    }

    return $a;
  }

  public function declara_js() {
    $a = parent::declara_js();

    if ($this->obxetos == null) return $a;

    foreach ($this->obxetos as $o) {
      if (($a_o = $o->declara_js()) == null) continue;

      foreach($a_o as $js) $a[] = $js;
    }

    return $a;
  }

  public function post($post) {
//*PRECOND:: count($post) == 1;

//~ echo "<pre>" . print_r($post, true) . "</pre>";

    if (!is_array($post)) return;

    foreach ($post as $k=>$v) {
      $a_k = explode(self::cnome, $k);

      if ($this->obxeto($a_k[0]) == null) continue;

      if ($a_k[0] == $k)
        $this->obxeto($a_k[0])->post($v);
      else
        $this->obxeto($a_k[0])->post( array(substr($k, strlen($a_k[0] . self::cnome))=>$v) );
    }
  }

  public function control_evento() {
    if ($this->obxetos == null) return 0;

    foreach ($this->obxetos as $o)
      if (($r = $o->control_evento()) != 0) return $r;

    return 0;
  }

  public function readonly($b = true) {
    parent::readonly($b);

    if (count($this->obxetos) == 0) return;

    foreach($this->obxetos as $k=>$v) $this->obxetos[$k]->readonly($b);
  }

  public function limpar($l = null) {
    if (count($this->obxetos) == 0) return;

    foreach($this->obxetos as $k=>$v) $this->obxetos[$k]->limpar($l);
  }

  public function visible($visible = true) {
    $this->visible = $visible;
  }

  public function cambiar_idioma(Idioma $idioma = null) {
    parent::cambiar_idioma($idioma);

    if ($this->obxetos == null) return;

    foreach($this->obxetos as $k=>$o) $this->obxetos[$k]->cambiar_idioma($idioma);
  }

  public function html():string {
    $this->preparar_saida();

    $html = $this->obxeto("illa")->html();

    $this->obxeto("illa")->post(null);

    return $html;
  }

  public function preparar_saida(Ajax $a = null) {
    $this->obxeto("illa")->post($this->html00());

    if ($a == null) return;

    $a->pon($this->obxeto("illa"));

    $this->obxeto("illa")->post(null);
  }

  protected function html00() {
    if (($ptw = parent::html()) == null) return "";

    if ($this->obxetos == null) return $ptw;

    $saida = $ptw;
    foreach($this->obxetos as $k=>$o) {
      $k = "[" . $o->nome() . "]";

      if (strpos($saida, $k) === FALSE) continue;

      $saida = str_replace($k, $o->html(), $saida);
    }

    return $saida;
  }

  final public function pon_obxeto(ObxetoFormulario $o, $subnome = null) {
    if ($subnome != null) $o->nome($o->nome() . self::csubnome . $subnome);

    $this->obxetos[$o->nome()] = $o;

    $o->pai = $this;
  }

  final public function sup_obxeto($nome, $subnome = null) {
    if (!is_array($this->obxetos)) return;

    if ($subnome != null) {
      unset($this->obxetos[$nome . self::csubnome . $subnome]);

      return;
    }

    foreach($this->obxetos as $k=>$o)
      if (strpos($k, $nome) === 0) unset($this->obxetos[$k]);
  }

  final public function obxeto($nome, $subnome = null) {
    if ($subnome == null) {
      if (!isset($this->obxetos[$nome])) return null;

      return $this->obxetos[$nome];
    }

    if (!isset($this->obxetos[$nome. self::csubnome . $subnome])) return null;

    return $this->obxetos[$nome . self::csubnome . $subnome];
  }

  final public function obxetos($knome = null) {
    if (($obxetos = $this->obxetos) == null) return array();

    if ($knome == null) return $obxetos;

    $obxetos_2 = array();
    foreach ($obxetos as $k=>$o)
      if (strpos($k, $knome) !== FALSE) $obxetos_2[$k] = $o;

    return $obxetos_2;
  }
}
