<?php

/*************************************************

    Tilia Framework v.0

    CEditor.php

    Author: Carlos Domingo Arias González

    Copyright (C) 2015

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation version 3.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.

*************************************************/


class CEditor extends Componente {
  public $ptw_0 = null;
  public $ptw_1 = null;

  public $wysiwyg        = true;
  public $onload_hide    = true;
  public $onblur_hide    = true;
  public $body_class     = null;
                         
  private $a_config_css  = null;

  private static $hai_js = false;


  public function __construct($id, $txt = "", $onload_hide = true, $onblur_hide = true, $wysiwyg = true) {
    parent::__construct($id);

    $this->ptw_0 = Tilia::home() . "php/vista/componentes/ceditor/wysiwyg.html";
    $this->ptw_1 = Tilia::home() . "php/vista/componentes/ceditor/limpo.html";

    $this->wysiwyg     = $wysiwyg;
    $this->onload_hide = $onload_hide;
    $this->onblur_hide = $onblur_hide;

    $this->pon_obxeto(new Param("declara_js"));
    $this->pon_obxeto(new Param("body_class"));
    $this->pon_obxeto(new Param("content_css"));
    $this->pon_obxeto(new Param("onload_hide"));
    $this->pon_obxeto(new Param("onblur_hide"));
    $this->pon_obxeto(new Param("selector"));
    $this->pon_obxeto(new Param("texto", $txt));

    $this->pon_obxeto(new Hidden("href"));
  }

  public function pon_config_css($css) {
    $this->a_config_css[$css] = 1;
  }

  public function post($post) {
    parent::post($post);

    if ( !isset($_REQUEST[$this->__selector()]) ) return;

    $this->post_txt( $_REQUEST[$this->__selector()] );
  }

  public function post_txt($txt) {
    $this->obxeto("texto")->post($txt);
  }

  public function valor() {
    return $this->obxeto("texto")->valor();
  }

  public function html():string {
    if (!$this->visible) return "";
    
    if ($this->readonly) return strval($this->obxeto("texto")->valor());

    $this->pon_js();
    

    $this->__ptw();
    $this->__selector();
    $this->__onload_hide();
    $this->__onblur_hide();
    $this->__content_css();
    $this->__body_class();

    return parent::html();
  }

  final public function __selector() {
    if ($this->obxeto("selector")->valor() == null) {
      $s = str_replace("=", "", base64_encode(uniqid()));

      $this->obxeto("href"    )->post( $s );
      $this->obxeto("selector")->post( $s );
    }

    return $this->obxeto("selector")->valor();
  }

  protected function pon_js() {
    if (self::$hai_js) return;

    self::$hai_js = true;

    $paxhead = $this->formulario()->paxina->head;
   
    $paxhead->pon_js(Tilia::home() . "php/vista/componentes/ceditor/tinymce/tinymce.dev.js");
  }

  protected function __ptw() {
    $this->ptw = ($this->wysiwyg)?$this->ptw_0:$this->ptw_1;
  }

  protected function __onload_hide() {
    $h = ($this->onload_hide)?"true":"false";

    $this->obxeto("onload_hide")->post($h);
  }

  protected function __onblur_hide() {
    $h = ($this->onblur_hide)?"true":"false";

    $this->obxeto("onblur_hide")->post($h);
  }

  protected function __content_css() {
    $ccss = null;

    $this->obxeto("content_css")->post(null);

    if (!is_array($this->a_config_css)) return;

    foreach($this->a_config_css as $css=>$x) {
      if ($ccss != null) $ccss .= ",";

      $ccss .= "\"{$css}\"";
    }

    $ccss = "[{$ccss}]";

    $this->obxeto("content_css")->post($ccss);
  }

  protected function __body_class() {
    $bc = ($this->body_class == null)?null:"{$this->body_class}";

    $this->obxeto("body_class")->post($bc);
  }
}

