<?php

/** Idioma Catalán.
 *
 * @package tilia\controlador\util\idioma
 */


final class Catalan extends Idioma {

  public function __construct($bandeira = null) {
    parent::__construct("ca", "Catalán", $bandeira);
  }

  protected function formato_fecha() {
    return "d-m-Y";
  }

  protected function dias() {
    return array("Diumenge", "Dilluns", "Dimarts", "Dimecres", "Dijous", "Divendres", "Dissabte");
  }

  protected function primerDiaSemana() {
    return 1;
  }

  protected function meses() {
    return array("Gener", "Febrer", "Març", "Abril", "Maig", "Juny", "Juliol", "Agost", "Setembre", "Octubre", "Novembre", "Desembre");
  }
}
