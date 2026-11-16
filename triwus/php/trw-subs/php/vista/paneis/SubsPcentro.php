<?php

abstract class SubsPcentro extends Panel_pestanhas {
  protected array $_mapa = [];
  
  protected string $l = "gl";
  
  private static array $_l = ["gl" => "Pechar sesión",
                              "es" => "Cerrar sesión"
                             ];
                               
  public function __construct(string $l = "gl") {
    parent::__construct("centro");
    
    $this->l = $l;
  }

  public function declara_css() {
    return null;
  }

  public function pon_SubsPestanha(SubsPestanha $p) {
    $this->_mapa[ $p->grupo() ][ $p->nome ] = $p->titulo();
    
    $this->pon($p);
  }

  public function html():string {
    if (!$this->visible) return "";

    $pa = $this->pestanha_activa;

    $html_p = "";
    foreach ($this->_mapa as $g => $_pg) {
//~ echo "$g <pre>" . print_r($_pg, 1) . "</pre><br>";

      $html_g = "<nav class=\"menu__opciones\"><div class=\"adm_menu--titulo\">{$g}</div>";
      foreach ($_pg as $kp => $t) {
        $css = ""; $js  = "";
        if ($kp == $pa->nome) {
          $css = " activa";
        }
        else {
          $js  = " onclick=\"__ptnh_onclick(this, '')\"";
        }
        
        $html_o = "
              <div class=\"adm-menu__opcion\" id=\"findex;{$kp}_etq;td\"{$js}>
                <input type=\"hidden\" id=\"findex;{$kp}_etq\" />
                <span class=\"adm_menu_3{$css}\">&bull;</span>
                <span class=\"adm-menu__txt\">{$t}</span>
              </div>";
        
        
        $html_g .= $html_o;
      }
      $html_g .= "</nav>";
      
      $html_p .= $html_g;
    }

    $bp_txt = self::$_l[$this->l];

    return "
  <div class=\"adm_menu_0\">
      <div>
        <div class=\"adm-logo\">
          <img src=\"" . Config::logo_i ."\" alt=\"" . Config::logo_t ."\"/>
        </div>
        <div class=\"adm-menu\">
          <div class=\"adm-menu__bloque\">
            {$html_p}
          </div>
        </div>
        <div class=\"adm-salir\">
          <div class=\"adm_menu_1 pecharsesion\" onclick=\"enviaEventoSubmit_str('findex;bpechar', 'onclick')\">[bpechar]{$bp_txt}</div>
        </div>
      </div>
  </div>
  [{$pa->aManten}]";
  }
}

//******************************

abstract class SubsPestanha extends Pestanha {
  public $aManten = null;
  
  public function __construct($id) {
    parent::__construct($id);
    
    $this->etq($this->declara_etq($id));
  }
  
  abstract public function titulo():string;
  
  abstract public function declara_AManten():SubsAManten;

  public function grupo():string {
    return "Contidos";
  }

  public function declara_obxetos() {
    $o = $this->declara_AManten();
    
    $this->aManten = $o->nome;
    
    return array( $o );
  }
}
