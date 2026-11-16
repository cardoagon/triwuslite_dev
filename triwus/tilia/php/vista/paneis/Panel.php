<?php

/** Podemos dividir un formulario en paneis.  Esta técnica permite definir Páxinas web mediante composición de Paneis.
 *
 * @package tilia\vista\paneis
 */


class Panel extends ObxetoFormulario {

  private $decorator = null;

  public function __construct($nome = "centro", $ptw = null) {
    parent::__construct($nome);

    $this->ptw = $ptw;
  }

  public function declara_obxetos() {
    if ($this->decorator != null) return $this->decorator->declara_obxetos();

    return null;
  }

  public function decorator(Panel $p = null) {
    if ($p == null) return $this->decorator;

    $this->decorator = $p;
  }

  public function declara_css() {
    if ($this->decorator == null) return parent::declara_css();

    return $this->decorator->declara_css();
  }

  public function declara_js() {
    if ($this->decorator == null) return parent::declara_js();

    return $this->decorator->declara_js();
  }

  public function cambiar_idioma(Idioma $idioma = NULL) {
    if ($this->decorator != null) $this->decorator->cambiar_idioma($idioma);

    parent::cambiar_idioma($idioma);
  }

  public function readonly($b = true) {
    if ($this->decorator != null) $this->decorator->readonly($b);

    parent::readonly($b);
  }

  public function html():string {
    if ($this->decorator != null) return $this->decorator->html();

    return parent::html();
  }

  final public function post($post) {}
}
