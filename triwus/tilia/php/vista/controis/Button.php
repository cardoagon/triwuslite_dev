<?php

/** Representa ao elemento html <button>
 *
 * @package tilia\vista\controis
 */


class Button extends Control implements IAJAX {
  protected $type = "button";

  public function  __construct($nome, $value = null) {
    parent::__construct($nome);

    $this->tipo_AJAX     = "TIPO";
    $this->tipo_htmljson = $this->type;

    if ($value == null) $value = $nome;

    $this->post($value);

    $this->clase_css("default", "button");
    $this->clase_css("readonly", "button_r");

    $this->envia_SUBMIT("onclick");
  }

  public function limpar($l = null) {}
  
  public function html():string {
    return $this->html_ancla() .
           "<button type={$this->type}
                    " . $this->html_nome() . "
                    " . $this->html_atr() . "
                    " . $this->html_eventos() . "
                    " . $this->html_title() . "
                    " . $this->html_clase() .  "
                    " . $this->html_estilo() .  ">{$this->valor}</button>";

    $html = str_replace(array("\n", "\r", "    "), array("", "", " "), $html);
    $html = str_replace("   ", " ", $html);

    return str_replace("  ", " ", $html);
  }
}
