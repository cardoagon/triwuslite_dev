<?php

/** Representa unha etiqueta calquera de html, <???> ... </???>
 *
 * @package tilia\vista\controis
 */


class Etiqueta extends Control implements IAJAX {

  public $element = "span";

  public function  __construct($nome, $txt = "", $visible = true) {
    parent::__construct($nome);

    $this->visible($visible);

    $this->tipo_AJAX     = "TIPO";
    $this->tipo_htmljson = "TIPO";

    $this->post($txt);
  }

  public function limpar($l = null) {} //* SOBREESCRIBE: ObxetoFormulario.limpar()

  public function html():string {
    $ancla = $this->html_ancla();

    $eventos = $this->html_eventos();
    return "{$ancla}<{$this->element} " . $this->html_nome() . " " . $this->html_title() . " {$eventos}
           " . $this->html_atr() . "
           " . $this->html_clase() .  "
           " . $this->html_estilo() .  ">" . $this->valor() . "</{$this->element}>";
  }
}


/** Representa ao elemento html <span>
 *
 * @package tilia\vista\controis
 */

class Span extends Etiqueta {}


/** Representa ao elemento html <div>
 *
 * @package tilia\vista\controis
 */

class Div extends Etiqueta {

  public function __construct($nome, $valor = null) {
    parent::__construct($nome, $valor);

    $this->element = "div";
  }
}


/** Representa ao elemento html <pre>
 *
 * @package tilia\vista\controis
 */

class Pre extends Etiqueta {

  public function __construct($nome, $valor = null) {
    parent::__construct($nome, $valor);

    $this->element = "pre";
  }
}


/** Representa ao elemento html <fieldset>
 *
 * @package tilia\vista\controis
 */

class Fieldset extends Etiqueta {
  protected $legend = null;

  public function __construct($nome, $valor = null, Etiqueta $legend = null) {
    parent::__construct($nome, $valor);

    $this->element = "fieldset";

    if ($legend != null) $this->post_legend($legend);
  }

  public function post_legend(Etiqueta $legend) {
    $this->legend = $legend;
    $this->legend->element = "legend";
  }

  public function html():string {
    if ($this->legend->valor != null) $legend = $this->legend->html();

    $ancla = $this->html_ancla();

    $eventos = $this->html_eventos();
    return "{$ancla}<{$this->element} " . $this->html_nome() . " " . $this->html_title() . " {$eventos}
              " . $this->html_clase() .  "
              " . $this->html_estilo() .  ">{$legend}" . $this->valor() . "</{$this->element}>";
  }
}


/**
 *
 * @package tilia\vista\controis
 */

class IllaAJAX extends Div {


  public $liberar = true;

  public function __construct($nome, $valor = null, $liberar = true) {
    parent::__construct($nome, $valor);

    $this->liberar = $liberar;
  }

  public function html():string {
    $html = parent::html();


    if ($this->liberar)  $this->post(null);


    return $html;
  }
}

