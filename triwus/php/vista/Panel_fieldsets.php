<?php

/*************************************************

    Apiweb Framework v.0

    Panel_fieldsets.php
    
    Author: Carlos Domingo Arias González
    
    Copyright (C) 2011 

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation version 3.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/ at http://www.gnu.org/licenses/>.
    
*************************************************/



/*
   File: Panel_fieldsets.php
*/

/*
   Class: Panel_fieldsets

   Este tipo de panel permite crear un panel composto de outros paneis que serán visualizados nun fieldset.

   See Also:
    <Panel>
*/

abstract class Panel_fieldsets extends Panel {
  private $a_pfs = null;

  public function __construct($nome, $ptw = null) {
    parent::__construct($nome, $ptw);
  }

  public function declara_css() {
    if ($this->decorator() != null) return $this->decorator()->declara_css();
    
    $a = array(Tilia::home() . "css/panel_fieldsets.css");
    
    foreach($this->a_pfs as $pfs) $a[] = $pfs->panel->declara_css();
    
    return $a; 
  }

  public function pon(Panel $p, $legend, $open = true) {
    if (count($this->a_pfs) == 0) $open = true;

    $this->a_pfs[$p->nome]->panel = $p;
    
    $this->a_pfs[$p->nome]->fs = new Fieldset_panel($p, $legend, $open); 
  }

  public function declara_obxetos() {
    if ($this->decorator() != null) return $this->decorator()->declara_obxetos();
    
    foreach ($this->a_pfs as $k=>$pfs) {
      $d[] = $pfs->fs;
      
      $d = array_merge($d, $pfs->panel->declara_obxetos());
    }

    return $d;
  }

  public function html():string {
    if ($this->decorator() != null) return $this->decorator()->declara_obxetos();
    
    if (!$this->visible) return "";

    $html = Escritor_html::html();

    $hai_ptw = $html != null;

    foreach ($this->a_pfs as $k=>$pfs) 
      if ($hai_ptw) 
        $html = str_replace("[{$k}]", $pfs->fs->html(), $html);
      else
        $html .= $pfs->fs->html();
    
    return $html;
  }
}



final class Fieldset_panel extends Componente {
  private $open = true;
  
  private $legend = null;
  
  public function  __construct(Panel $p, $legend, $open = true) {
    parent::__construct("pfs_" . $p->nome(), $p->ptw);

    $this->legend = $legend;
    $this->open = $open;
    
    $this->clase_css("fieldset", "fieldset");
    $this->clase_css("legend", "fieldset_legend");
    $this->clase_css("contido", "fieldset_contido");
  }

  public function html():string {
    if (!$this->visible) return "";

    if ($this->legend != null) $legend = "<legend class='" . $this->clase_css("legend") . "'>{$this->legend}</legend>";


    return "
      <fieldset class='" . $this->clase_css("fieldset") . "'> 
        {$legend}
        <a name='{$this->nome}'></a>
        
        <div id='" . $this->nome_completo() . "_contido' class='" . $this->clase_css("contido") . "'>
        " . Escritor_html::html() . "
        </div>
      </fieldset>";
  }
}

?>
