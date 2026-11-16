<?php

class SColor extends Componente {
  const transparente = "transparent";
  
  const ptw_0 = "ptw/style/scolor_0.html";
  const ptw_1 = "ptw/style/scolor_1.html";

  private $hex    = null;
  private $_color = null;

  public function __construct($hex = null, $nome = "scolor") {
    parent::__construct($nome, self::ptw_0);

    if ($hex == null) $hex = self::transparente;

    $this->post_hex($hex);

    $this->pon_obxeto(new Param("pcolor_config"));
    $this->pon_obxeto(self::__bcolor("color_t", self::transparente));
 }

  public function valor() {
    return $this->hex;
  }

  public function post_hex($hex) {
    $this->hex = $hex;

    $this->pon_obxeto(self::__selector($hex));
    $this->pon_obxeto(self::__bcolor("color_0", $hex, false));
  }

  public function operacion(EstadoHTTP $e) {
    if ($this->obxeto("color_0")->control_evento()) return $this->operacion_color_0($e);
    
    if ($this->obxeto("selector")->control_evento()) {
      return $this->operacion_cambia_color($e, $this->obxeto("selector")->valor());
    }
    
    if ($this->obxeto("color_t")->control_evento()) {
      return $this->operacion_cambia_color($e, self::transparente);
    }


    if (!is_array($this->_color)) return;

    foreach($this->_color as $k => $hex) {
      if (($b = $this->obxeto("color_config", $k)) == null) continue;
      
      if (!$b->control_evento()) continue;
      
      return $this->operacion_cambia_color($e, $hex);
    }


    return null;
  }

  private function operacion_color_0(EstadoHTTP $e) {
    $this->ptw = ($this->ptw != self::ptw_1)?self::ptw_1:self::ptw_0;

    if ($this->_color == null) {
      $this->inicia_colores_config($e->config());
    }
    
    $this->preparar_saida($e->ajax());
    
    return $e;
  }

  private function operacion_cambia_color(EstadoHTTP $e, $hex) {
    $this->post_hex($hex);
        
    $this->preparar_saida($e->ajax());
    
    return $e;
  }

  private function inicia_colores_config(Config_obd $c) {
    if ($this->_color != null) return;

    //* 1.- Crea array de colores de la plantilla.
    $_c = $c->a_resumo();

    $this->_color = array_flip(array("#{$_c["color"]}"        => 1,
                                     "#{$_c["color2"]}"       => 2,
                                     "#{$_c["color_fondo"]}"  => 3,
                                     "#{$_c["color_fondo2"]}" => 4,
                                    )
                              );

//~ $this->obxeto("pcolor_config")->post( print_r($this->_color, true) );


    if (!is_array($this->_color)) return;


    //* 2.- Crea botones relacionados.
    $s = "";
    foreach($this->_color as $k => $hex) {
      $this->pon_obxeto(self::__bcolor("color_config", $hex), $k);

      $s .= $this->obxeto("color_config", $k)->html();
    }

    
    $this->obxeto("pcolor_config")->post( $s );
  }

  private static function __bcolor($id, $hex, $mini = true) {
    if ($hex != self::transparente) {
      $bg  = "background-color: {$hex};";

      $hex = strtoupper($hex);
    }
    else
      $bg = "background-image: url(imx/elmt/imx/fondo_imaxes.png);";

    $d = "<div class='trw_scolor_00' style='{$bg}'></div>";


    if ($mini) {
      $css = "trw_scolor_01b";
    }
    else {
      $css = "trw_scolor_01a";
      
      $d .= "&nbsp;&nbsp;{$hex}"; 
    }
    

    $b = new Button($id, $d);

    $b->title = $hex;
    
    $b->clase_css("default", $css);

    $b->envia_ajax("onclick");

    return $b;
  }

  private static function __selector($hex) {
    $b = new Color("selector");

    $b->envia_ajax("onchange");
    //~ $b->envia_submit("onchange");

    $b->clase_css("default", "trw_scolor_02");

    if ($hex != self::transparente) $b->post($hex);

    return $b;
  }
}
