<?php

/** Idioma Francés.
 *
 * @package tilia\controlador\util\idioma
 */

final class Frances extends Idioma {

  public function __construct($bandeira = null) {
    parent::__construct("fr", "Francés", $bandeira);
  }


  protected function primerDiaSemana() {
    return 1;
  }


  protected function formato_fecha() {
    return "d-m-Y";
  }

  protected function dias() {
    return array("Dimanche", "Luns", "Mardi", "Mercredi", "Jeudi", "Vendredi", "Samedi");
  }

  protected function meses() {
    return array("Janvier", "Février", "Mars", "Avril", "Mai", "Juin", "Juillet", "Août", "Septembre", "Octobre", "Novembre", "Décembre");
  }
}
