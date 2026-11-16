<?php


/** Representa ao elemento html <textarea>Hola mundo cruel !!!</textarea>
 *
 * @package tilia\vista\controis
 */

class Textarea extends Control implements IAJAX {

  public $maxlength = null;

  public $filas = null;

  public $columnas = null;

  public $posicion = null; // 19/04/2016

  public function  __construct($nome, $filas = 3, $columnas = 20, $readonly = false, $posicion = 1) { // <-----
    parent::__construct($nome);

    $this->filas    = $filas;
    $this->columnas = $columnas;

    $this->clase_css("default", "text");
    $this->clase_css("readonly", "text_r");

    $this->readonly = $readonly;

    $this->tipo_AJAX     = "TIPO";
    $this->tipo_htmljson = "TIPO";

    $this->posicion = $posicion; // -------
  }

  public function post($post) {
        if ($post == null);
    elseif (is_numeric($this->maxlength)) $post = substr($post, 0, $this->maxlength);

    parent::post($post);
  }

  public function valor() {
    $v = strval(parent::valor());

    if (is_numeric($this->maxlength)) $v = substr($v, 0, $this->maxlength);

    return $v;
  }

  public function html():string {
    $readonly = ($this->readonly)?"readonly":"";
    $ancla = $this->html_ancla();

    $html ="
      {$ancla}
      <textarea " . $this->html_nome() . "{$readonly}
                " . $this->html_clase() . "
                " . $this->html_placeholder() . "
                " . $this->html_estilo() . "
                " . $this->html_title() . "
                " . $this->html_eventos() . "
                rows={$this->filas} cols={$this->columnas}
                maxlength='{$this->maxlength}'>{$this->valor}</textarea>";

// 19/04/2016
    if($this->posicion != null) {
      //~ $this->pon_etq($this->nome, $this->posicion);
      switch($this->posicion) {
        case 1: // arriba > pos_arriba
          $html = $this->html_etq() . $html;
          break;
        case 2: // derecha > pos_dereita
          $html = $html . $this->html_etq();
          break;
        case 3: // abajo > pos_ abaixo
          $html = $html . $this->html_etq();
          break;
        case 4: // izquierda > pos_esquerda
          $html = $this->html_etq() . $html;
          break;
      }
    }
// ---------------------------------------------------------

    return $html;
  }
}
