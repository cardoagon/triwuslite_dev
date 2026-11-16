<?php

/** Permite escribir valores nunha vista HTML.
 *
 * @package tilia\vista
 */


class Param extends ObxetoFormulario {
  
  protected $tipo_htmljson = "param";

  public function __construct($nome, $valor = null) {
    parent::__construct($nome);

    $this->post($valor);
  }
}

