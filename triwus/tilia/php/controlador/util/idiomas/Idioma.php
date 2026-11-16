<?php

/** Esta clase abstracta representa características propias de cada Idioma.
 *
 * @package tilia\controlador\util\idioma
 */


abstract class Idioma {

  public $codigo = null;

  public $bandeira = null;

  public $nome = null;

  public $formato_fecha = null;

  public $dias = null;

  public $primerDiaSemana = null;

  public $meses = null;

  protected function __construct($codigo, $nome, $bandeira) {
    $this->codigo          = $codigo;
    $this->nome            = $nome;
    $this->bandeira        = ($bandeira == null)?Tilia::home() . "/imaxes/bandeiras/{$codigo}.png":$bandeira;

    $this->formato_fecha   = $this->formato_fecha();
    $this->dias            = $this->dias();
    $this->primerDiaSemana = $this->primerDiaSemana();
    $this->meses           = $this->meses();
  }

  abstract protected function formato_fecha();

  abstract protected function dias();

  abstract protected function primerDiaSemana();

  abstract protected function meses();

  final public static function igual(Idioma $i1 = null, Idioma $i2 =  null) {
    if ($i1 == null) return false;
    if ($i2 == null) return false;
    
    return ($i1->cod == $i2->cod);
  }

  final public static function factory($id_idioma) {
    switch ($id_idioma) {
      case "ca": return new Catalan();
      case "de": return new Aleman();
      case "en": return new English();
      case "es": return new Espanhol();
      case "fr": return new Frances();
      case "gl": return new Galego();
      case "pt": return new Portugues();
    }

    die ("Idioma.factory(), id_idioma <b>{$id_dioma}</b> desconhecido");
  }
}
