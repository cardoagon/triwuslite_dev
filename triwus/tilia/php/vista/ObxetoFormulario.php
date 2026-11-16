<?php

/** Os obxetos da clase ObxetoFormulario, serán empregados na composición GUI's HTML.
 *
 * @package tilia\vista
 */

abstract class ObxetoFormulario extends Escritor_html {

  public $pai      = null;
  public $readonly = false;
  public $valor    = null;
  public $visible  = true;
  
  protected $tipo_htmljson = "0";


  protected function __construct($nome) {
    parent::__construct($nome);
  }


  final public function nome_completo() {
    $nome_html = $this->nome();

    $o_pai = $this->pai;
    while($o_pai != null) {
      $nome_html = $o_pai->nome() . Escritor_html::cnome . $nome_html;

      $o_pai = $o_pai->pai;
    }

    return $nome_html;
  }


  public function formulario() {
    if ($this->pai != null) return $this->pai->formulario();

    if (is_a($this, "Formulario")) return $this;

    return null;
  }
  
  public function control_evento() {
//~ echo "{$this->nome}::" . gettype($this) . "::\n";
    if ( $this->pai == null) return false;
    if ( $this->readonly   ) return false;
    if (!$this->visible    ) return false;

    $evento = $this->formulario()->evento();

    return $evento->control_ohttp($this); //* RETURN: int(0, 1, 2).
  }

  public function post($post) {
    $this->valor = $post;
  }

  public function limpar($l = null) {
    $this->post($l);
  }

  public function valor() {
    return $this->valor;
  }

  public function readonly($b = true) {
    $this->readonly = $b;
  }

  public function visible($b = true) {
    $this->visible = $b;
  }

  public function operacion(EstadoHTTP $e){
    return null;
  }

  public function html():string {
    if (!$this->visible) return "";

    if (($html = parent::html()) != "") return $html;
    
    if (($html = $this->valor()) == null) return "";

    return $html;
  }

}

