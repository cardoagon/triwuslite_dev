<?php

/**
 *
 * @package tilia\vista\controis
 */


class Capa extends Control implements IAJAX {

  public $sempre_visible = false;
  public $width          = null;
  public $height         = null;
  public $zindex         = "100";
  public $position       = "absolute";
  public $autopechar     = 1;
  public $onmouseout     = null;
  public $onmouseover    = null;


  public function  __construct($nome, $width, $clase_css = "capa") {
    parent::__construct($nome);

    $this->tipo_AJAX     = "TIPO";
    $this->tipo_htmljson = "CAPA";

    $this->width = $width;
    $this->clase_css("default", $clase_css);
  }

  public function html():string {
    $visible = ($this->visible)?"block":"none";

    if (!$this->sempre_visible) {
//~ echo "onmouseover:" . $this->onmouseover . "<br>";
//~ echo "onmouseout :" . $this->onmouseout  . "<br>";

      if ($this->onmouseover == null)
        $this->onmouseover = "mostrar_CAPA(this, 'inline-block', '', {$this->autopechar})";
        
      if ($this->onmouseout == null)
        $this->onmouseout = "mostrar_CAPA(this, 'none', '', {$this->autopechar})";
      
      $operacions = "onmouseover=\"{$this->onmouseover}\" onmouseout=\"{$this->onmouseout}\"";

//~ echo $operacions;
    }

    $height = ""; $width = "";
    if ($this->height != null) $height = " height:{$this->height};";
    if ($this->width  != null) $width  = " width:{$this->width};";


    $estilo_0 = "style=\"display:{$visible}; {$width} {$height}\"";

    $ancla = $this->html_ancla();

    return "{$ancla}
            <div id='" . $this->nome_completo() . "' {$operacions} " . $this->html_clase() . " {$estilo_0} " . $this->html_estilo() . " >{$this->valor}</div>";
  }
}
