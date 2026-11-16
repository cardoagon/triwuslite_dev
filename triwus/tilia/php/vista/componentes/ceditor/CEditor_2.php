<?php

class CEditor_2 extends Componente {
  public $body_class     = null;
  public $content_css    = null;

  public $menubar = false;
  public $plugins = array("advlist autolink lists link image charmap print anchor, searchreplace visualblocks code fullscreen textcolor, insertdatetime media paste");
  public $toolbar = "fullscreen | removeformat | undo redo | cut copy paste | searchreplace | bold italic underline strikethrough superscript subscript | alignleft aligncenter alignright alignjustify | forecolor backcolor | bullist numlist | hr | link image | charmap | visualblocks code";

  private $txt      = "";
  private $selector = null;
  private $_css     = null;
  
  private static $hai_js   = false;

  public function __construct($id, $txt = "") {
    parent::__construct($id);

    $this->ptw = Tilia::home() . "php/vista/componentes/ceditor/ceditor_2.html";

    $this->selector = str_replace("=", "", base64_encode(uniqid()));

    $this->pon_obxeto(new Hidden("href", $this->selector));
  }

  public function declara_js() {
    $th = Tilia::home();
    
    return array("{$th}/php/vista/componentes/ceditor/tinymce/tinymce.dev.js",
                 "{$th}/js/ceditor_2.js"
                );
  }

  public function pon_config_css($css) {
    $this->_css[$css] = 1;
  }

  public function post($post) {
    parent::post($post);

    if ( !isset($_REQUEST[$this->selector]) ) return;

    $this->post_txt( $_REQUEST[$this->selector] );
  }

  public function post_txt($txt) {
    if ($txt == null) $txt = "";
    
    $this->txt = $txt;
  }

  public function valor() {
    return $this->txt;
  }

  public function html():string {
    if (!$this->visible) return "";
    
    if ($this->readonly) return $this->txt;


    $html = parent::html();

    $html = str_replace("[selector]"   , $this->selector      , $html);
    $html = str_replace("[txt]"        , $this->txt           , $html);
    $html = str_replace("[body_class]" , $this->__body_class(), $html);
    $html = str_replace("[js_callback]", $this->js_callback() , $html);

    return $html;
  }

  public function preparar_saida(Ajax $a = null) {
    parent::preparar_saida($a);

    if ($a == null) return;
    

    $illa = $this->obxeto("illa");

    $illa ->post( $this->html() );

    $a->pon($illa, true, $this->js_callback());

    $this->obxeto("illa")->post(null); //* liberamos buffer.
  }

  public function js_callback() {
    return "tilia_ceditor2_oninit(" . $this->json_config() . ")";
  }

  public function json_config() {
    $_config = array("selector"    => $this->selector,
                     "menubar"     => $this->menubar,
                     "content_css" => $this->__content_css(),
                     "body_class"  => $this->__body_class(),
                     "plugins"     => $this->plugins,
                     "toolbar"     => $this->toolbar
                    );
    
    return json_encode($_config);
  }

  protected function __content_css() {
    if ( !is_array($this->_css) ) return "";


    $_ccss = array();
    
    foreach($this->_css as $css=>$x) $_ccss[] = $css;


    return $_ccss;
  }

  protected function __body_class() {
    if ($this->body_class == null) return "";

    return $this->body_class;
  }
}

