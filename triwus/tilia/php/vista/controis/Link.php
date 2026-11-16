<?php


/** Representa ao elemento html &lt;a ... &gt;
 *
 * @package tilia\vista\controis
 */

class Link extends Control implements IAJAX {
  public $href = null;
  public $target = null;

  public function  __construct($nome, $a = "", $target = "_self", $href = null, $readonly = false) {
    parent::__construct($nome);

    $this->tipo_AJAX     = "LINK";
    $this->tipo_htmljson = "LINK";

    $this->href = $href;

    if ($a == "") $a = $nome;

    $this->post($a);

    $this->target = $target;
    $this->readonly = $readonly;
  }

  public function limpar($l = null) {} //* SOBREESCRIBE: ObxetoFormulario.limpar()

  public function html():string {
    if ($this->readonly) return "<a  " . $this->html_clase() . " " . $this->html_atr() .  " " . $this->html_estilo() . " " . $this->html_title() .  ">" . $this->valor() . "</a>";

    $href = "";
    if ($this->href != null) $href = "href='{$this->href}'";

    return $this->html_ancla() . "<a {$href} " . $this->html_nome() .  "  " . $this->html_clase() .  " " . $this->html_estilo() .  " " . $this->html_eventos() . " " . $this->html_title() .  " target='{$this->target}'>" . $this->valor() . "</a>";
  }


  public function atributosAJAX() { //* plantilla para implementar IAJAX.
    $a = parent::atributosAJAX();

    $a["href"] = $this->href;

    return $a;
  }

  public function fillosAJAX() { //* plantilla para implementar IAJAX.
    return $this->valor;
  }
}
