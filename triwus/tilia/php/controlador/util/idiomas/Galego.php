<?php

/** Idioma Galego.
 *
 * @package tilia\controlador\util\idioma
 */

final class Galego extends Idioma {

  public function __construct($bandeira = null) {
    parent::__construct("gl", "Galego", $bandeira);
  }


  protected function primerDiaSemana() {
    return 1;
  }


  protected function formato_fecha() {
    return "d-m-Y";
  }

  protected function dias() {
    return array("Domingo", "Luns", "Martes", "Mercores", "Xoves", "Venres", "Sabado");
  }

  protected function meses() {
    return array("Xaneiro", "Febreiro", "Marzo", "Abril", "Maio", "Xu&ntilde;o", "Xullo", "Agosto", "Setembro", "Outubro", "Novembro", "Decembro");
  }
}
