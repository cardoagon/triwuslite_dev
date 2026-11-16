<?php

final class Editor_sprgf extends Editor_style {
  //~ private static array $_obx = ["","","",""];
  

  public function __construct(Paragrafo $p) {
    parent::__construct($p->prgf_obd()->style_obd(), "ptw/style/editor_sprgf.html");

    $this->pon_obxeto( self::__ffondo("ffondo"));


    $this->pon_obxeto( self::__ffondo_bsup("ffondo_sup") );
    
    $this->ffondo_borrar( $p->sup_fondo );
  }

  public function operacion(EstadoHTTP $e) {
    if (($e_aux = $this->obxeto("scolor")->operacion($e)) != null) {

      //~ $this->pai->pai->preparar_saida($e_aux->ajax());
      $this->preparar_saida($e_aux->ajax());

      return $e_aux;
    }

    if ($this->obxeto("ffondo_sup")->control_evento()) {
      $this->ffondo_borrar( true );

      $e->ajax()->pon_ok();

      return $e;
    }

    if ($this->obxeto("ffondo")->control_evento()) {
      $this->obxeto("ffondo")->post_f();

      $e->ajax()->pon_ok();

      return $e;
    }


    return parent::operacion($e);
  }

  protected function a_props():?array {
    if (($a_obx = $this->obxetos()) == null) return null;

    $a = null;

    if (($borde   = $a_obx['cborde']->valor()) != null) $a["border"] = $borde;
    if (($marxe   = $a_obx['cmarxe']->valor()) != null) $a["margin"] = $marxe;
    if (($padding = $a_obx['cpadding']->valor()) != null) $a["padding"] = $padding;
    if (($width   = $a_obx['width']->valor()) != null) {
      $width = Erro::valida_numero($width);

      $a["max-width"] = "{$width}%";
    }

    if (($color   = $a_obx['scolor']->valor()) != null) $a["background-color"] = $color;
    //~ if (($bgimx = $this->ffondo()) != null) {
      //~ $a["background-image"] = "1"; //* esta propiedad será reescrita en tempo de execución, según prgf.id_fondo.
    //~ }

    return $a;
  }

  protected function post_style(Style_obd $s):void {
    $a_props = $s->a_props();
    
//~ echo "<pre>" . print_r($a_props, true) . "</pre>";

    $maxwidth = ($a_props['max-width'] == null)?100:str_replace("%", "", $a_props['max-width']);

    $this->pon_obxeto(self::__number("width"   , $a_props['max-width'       ], 1, 100, 1, "%"));
    $this->pon_obxeto(self::__cborde("cborde"  , $a_props['border'          ]));
    $this->pon_obxeto(self::__cmarxe("cmarxe"  , $a_props['margin'          ]));
    $this->pon_obxeto(self::__cmarxe("cpadding", $a_props['padding'         ]));
    $this->pon_obxeto(self::__scolor("scolor"  , $a_props['background-color']));
    

    $this->obxeto("width")->style("default", "width: 77px;");
  }
}

