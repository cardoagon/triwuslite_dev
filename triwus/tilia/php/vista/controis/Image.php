<?php


/** Representa ao elemento html &lt;img ... &gt;
 *
 * @package tilia\vista\controis
 */

class Image extends Control implements IAJAX {

  public    $rollover = true;
  public    $height   = null;
  public    $width    = null;

  protected $src          = "";
  protected $src_click    = "";
  protected $src_over     = "";
  protected $src_readonly = "";

  public function  __construct($nome, $src = null, $rollover = false) {
    parent::__construct($nome);

    $this->pon_src($src);
    $this->rollover = $rollover;

    $this->tipo_AJAX     = "SRC";
    $this->tipo_htmljson = "SRC";
  }

  public function src() {
    return $this->src;
  }
  public function src_click() {
    return $this->src_click;
  }
  public function src_over() {
    return $this->src_over;
  }
  public function src_readonly() {
    return $this->src_readonly;
  }

  public function post($post) {
    $this->pon_src($post);
  }

  public function pon_src($src, $src_click = null, $src_over = null, $src_readonly = null) {
    $this->src = $src;
    $this->src_click = ($src_click == null)?$src:$src_click;
    $this->src_over = ($src_over == null)?$src:$src_over;
    $this->src_readonly = ($src_readonly == null)?$src:$src_readonly;
  }

  public function html():string {
    if (!$this->visible) return "";

    //~ if (($_dato = $this->_htmljson_dato()) == null) return "";

    if ($this->readonly) {
      $src       = $this->src_readonly;
      $src_click = $this->src_readonly;
      $src_over  = $this->src_readonly;
    }
    else {
      $src       = $this->src;
      $src_click = $this->src_click;
      $src_over  = $this->src_over;
    }

    $height = ($this->height == null)?"":"height=\"{$this->height}\"";
    $width  = ($this->width == null)?"":"width=\"{$this->width}\"";
    $ancla  = $this->html_ancla();

    $str_rollover = "";
    if ($this->rollover)
      $str_rollover = "
           onMouseout=\"this.src = '{$src}'\"
           onMouseover=\"this.src = '{$src_over}'\"
           onMousedown=\"this.src = '{$src_click}'\"";

    return "{$ancla}<img " . $this->html_nome() . " {$height} {$width}
           " . $this->html_clase() .  "
           " . $this->html_estilo() .  "
           " . $this->html_eventos() .  "
           " . $this->html_title() .  "
           " . $this->html_alt() .  "
           src=\"{$src}\" {$str_rollover} />";
  }

  public function fillosAJAX() { //* plantilla para implementar IAJAX.
    return $this->src;
  }

  protected function html_alt() {
    if ($this->title != null) return "alt='{$this->title}'";
  }

/*
  protected function _htmljson_dato($_subdato = null) {
    if (!$this->visible) return null;
    
    $_dato = parent::_htmljson_dato($_subdato);
    
    if ($this->readonly) {
      $src       = $this->src_readonly;
    }
    else {
      $src       = $this->src;
    }


    $_dato["h"  ] = ($this->height == null)?"":"height=\"{$this->height}\"";
    $_dato["w"  ] = ($this->width  == null)?"":"width=\"{$this->width}\""  ;
    
    $_dato["src"] = $src;

    
    return $_dato;
  }
*/
}
