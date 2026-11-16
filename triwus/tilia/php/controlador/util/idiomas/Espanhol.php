<?php

/** Idioma Espa&ntilde;ol.
 *
 * @package tilia\controlador\util\idioma
 */


final class Espanhol extends Idioma {

  public function __construct($bandeira = null) {
    parent::__construct("es", "Espa&ntilde;ol", $bandeira);
  }

  protected function formato_fecha() {
    return "d-m-Y";
  }

  protected function dias() {
    return array("Domingo", "Lunes", "Martes", "Miercores", "Jueves", "Viernes", "Sabado");
  }

  protected function primerDiaSemana() {
    return 1;
  }

  protected function meses() {
    return array("Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");
  }
}
