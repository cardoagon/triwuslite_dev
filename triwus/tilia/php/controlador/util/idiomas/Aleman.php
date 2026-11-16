<?php

/** Idioma Catalán.
 *
 * @package tilia\controlador\util\idioma
 */


final class Aleman extends Idioma {

  public function __construct($bandeira = null) {
    parent::__construct("de", "Alemán", $bandeira);
  }

  protected function formato_fecha() {
    return "d.m.Y";
  }

  protected function dias() {
    return array("Sonntag", "Montag", "Dienstag", "Mittwoch", "Donnerstag", "Freitag", "Samstag");
  }

  protected function primerDiaSemana() {
    return 1;
  }

  protected function meses() {
    return array("Januar", "Februar", "März", "April", "Mai", "Juni", "Juli", "August", "September", "Oktober", "November", "	Dezember");
  }
}
