<?php

class Iframe_obd extends Elmt_obd {
  const tipo_bd = "IFRAME";
  
  public function __construct($url = null) {
    parent::__construct();
    
    $this->atr("url_pre")->valor = $url;
  }
 
  public function mapa_bd() {   
    return new Iframe_mbd();
  }
 
  protected function tipo_bd() {
    return self::tipo_bd;
  }
}

//-----------------------------

final class Iframe_mbd extends Elmt_mbd {
  public function __construct() {
    parent::__construct();
    
    $t = new Taboa_dbd("elmt_iframe");

    $t->pon_campo("id_elmt" , new Numero(), true);
    $t->pon_campo("url"     , new VarChar(true));
    $t->pon_campo("url_pre" , new VarChar(true));
    $t->pon_campo("url_post", new VarChar(true));
    
    $this->relacion_fk($t, array("id_elmt"), array("id_elmt"));
  }
}

//****************************


final class ElmtCbuscador_obd extends Iframe_obd {
  public function __construct() {
    parent::__construct( self::url() );
  }

  private static function url() {
    return "
<div class=\"trw_elmt_cbuscador_marco\">
  <input  id          = \"trw_elmt_cbuscador_t\"
          onkeypress  = \"trw_elmt_cbuscador_keypress()\"
          type        = \"text\"
          maxlength   = \"99\"
          placeholder = \"\"
          title       = \"hola\"/>
          
  <button onclick     = \"trw_elmt_cbuscador_click()\"
          type        = \"button\"
          title       = \"\">Buscar</button>
</div>";
  }
}
