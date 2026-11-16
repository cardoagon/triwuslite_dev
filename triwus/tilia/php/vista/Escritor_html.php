<?php

/** Clase abstracta da que parte calquera obxeto que devolva unha saida html.
 *
 * @package tilia\vista
 */


abstract class Escritor_html {
  const cnome    = ";";
  const csubnome = "*";

  public $nome      = null;
  public $html_name = null; //* 20170816. Para poder dar valores ditintos aos atributos html: id e name.
  public $ptw       = null;
  public $idioma    = null;
  
  //~ public $htmljson  = false;
  
  protected $clases_css = array("default" => null, "readonly" => null);
  protected $style      = array("default" => null, "readonly" => null);

  protected function __construct($nome) {
      $this->nome = $nome;
  }


  abstract public function formulario();

  public function cambiar_idioma(Idioma $idioma = null) {
    $this->idioma = $idioma;
  }

  final public function nome($nome = null) {
    if ($nome == null) return $this->nome;

    $this->nome = $nome;
  }

  final public function clase_css($k, $v = -1) {
    if ($v === null) {
      unset( $this->clases_css[$k] );
      
      return;
    }
    
    if ($v == -1) {
      if (isset($this->clases_css[$k])) return $this->clases_css[$k];
      
      return "";
    }

    $this->clases_css[$k] = $v;
  }

  final public function style($k, $v = -1) {
    if ($v == -1) return $this->style[$k];

    $this->style[$k] = $v;
  }

  public function declara_css() {}
  public function declara_js() {}


  public function html():string {
    if ($this->ptw == null) return "";

    return LectorPlantilla::plantilla($this->ptw, $this->idioma);
  }
}
