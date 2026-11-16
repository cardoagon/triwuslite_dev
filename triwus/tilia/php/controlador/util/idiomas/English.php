<?php

/** Idioma English.
 *
 * @package tilia\controlador\util\idioma
 */


final class English extends Idioma {

  public function __construct($bandeira = null) {
    parent::__construct("en", "English", $bandeira);
  }

  protected function formato_fecha() {
    return "m-d-Y";
  }

  protected function dias() {
    return array("Sunday", "Monday", "Tuesday", "Wendsday", "Thursday", "Friday", "Saturday");
  }

  protected function primerDiaSemana() {
    return 0;
  }

  protected function meses() {
    return array("January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");
  }
}
