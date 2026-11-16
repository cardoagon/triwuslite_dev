<?php


final class EInstalar extends EstadoHTTP {
  const config_dist = "dist/Config.php";
  const config_dest = "../triwus/Config.php";
  
 
  private bool $instalar;

  public function __construct() {
    parent::__construct(self::__paxina());

    $this->pon_obxeto( self::_dmsx() );
    
    $this->inicia_instalar();

    $this->pon_obxeto( new CInstalar ($this->instalar) );
    $this->pon_obxeto( new CActualiza($this->instalar) );
  }

  public function post_msx($m = null, $t = "e", $ajax = null):void {
    //* $t IN {"a", "e", "m"}

    $o = $this->obxeto("msx");

    if ($m == null) {
        $o->post(null);

        $o->clase_css("default", "txt_apagado");

      return;
    }


    $x = "<div onclick=\"trw_post_msx()\">✕</div>";

    $o->post( "{$x}<div>{$m}</div>" );

        if ($t == "a") $o->clase_css("default", "trw_msx txt_adv");
    elseif ($t == "e") $o->clase_css("default", "trw_msx txt_err");
    elseif ($t == "m") $o->clase_css("default", "trw_msx txt_msx");


    if ($ajax === null) $ajax = ($this->evento()->ajax() == 1);

    if ($ajax) $this->ajax()->pon( $o );
  }


  public function operacion() { //* :EstadoHTTP
    $this->post_msx();
    
    if (($e_aux = $this->operacion_actualizar()) != null) return $e_aux;


    return $this;
  }

  public function operacion_actualizar():?EstadoHTTP { 
    $this->inicia_instalar();
    
    if ($this->instalar) return $this->obxeto("cinstalar")->operacion($this);
    

    return $this->obxeto("cactualiza")->operacion($this);
  }


  private function inicia_instalar():void {
    $this->instalar = Config::mysql_b == "";
  }

  private static function _dmsx():Div {
    $d = new Div("msx");

    $d->ajax_css_display = "flex";

    return $d;
  }

  private static function __paxina():Paxina {
    $p = new Paxina("trw.instalar.paxina");

    $p->head->links_ver(1);

    $p->head->pon_meta("viewport", "width=device-width, initial-scale=1");


    $p->head->pon_js ("js/basico.js");

    $p->head->pon_css("css/trwform.css");
    $p->head->pon_css("css/basico.css");

    $p->formulario()->action = "index.php";
    $p->formulario()->ptw    = "ptw/form/0.html";

    //~ $p->pon_panel(new Panel("centro"));

    return $p;
  }
}
