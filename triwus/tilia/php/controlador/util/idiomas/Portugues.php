<?php


/** Idioma Portugu&ecirc;s.
 *
 * @package tilia\controlador\util\idioma
 */


final class Portugues extends Idioma {

  public function __construct($bandeira = null) {
    parent::__construct("pt", "Português", $bandeira);
  }

  protected function primerDiaSemana() {
    return 0;
  }

  protected function formato_fecha() {
    return "d-m-Y";
  }

  protected function dias() {
    return array("Domingo", "2&ordf;", "3&ordf;", "4&ordf;", "5&ordf;", "6&ordf;", "S&aacute;bado");
  }

  protected function meses() {
    return array("Janeiro", "Fevereiro", "Março", "Abril", "Maio", "Junho", "Julho", "Agosto", "Setembro", "Outubro", "Novembro", "Dezembro");
  }
}

