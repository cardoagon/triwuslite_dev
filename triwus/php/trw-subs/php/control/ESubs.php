<?php

abstract class ESubs extends EstadoHTTP {
  protected  bool $u_control  = false;
  
  private   ?int  $id_usuario = null;

  public function __construct() {
    parent::__construct($this->__paxina());

    $this->pon_obxeto(self::bpechar()         );
    $this->pon_obxeto(self::_derro()          );
  }

  abstract public function declara_pcab   ():SubsPcab;
  abstract public function declara_pcentro():SubsPCentro;
  abstract public function declara_ppe    ():SubsPpe;

  public function derro_post($m = null, $t = "e", $ajax = null) {
    //* $t IN {"a", "e", "m"}

    $o = $this->obxeto("derro");

    if ($m == null) {
      $o->post(null);

      $o->clase_css("default", "adm-txt_apagado");

      return;
    }


    $x = "<div onclick=\"document.getElementById('findex;derro').className = 'adm-txt_apagado';\">✕</div>";

    $o->post( "<div>{$m}</div>{$x}" );

        if ($t == "a") $o->clase_css("default", "adm-msx adm-txt_adv");
    elseif ($t == "e") $o->clase_css("default", "adm-msx adm-txt_err");
    elseif ($t == "m") $o->clase_css("default", "adm-msx adm-txt_msx");


    if ($ajax === null) $ajax = ($this->evento()->ajax() == 1);

    if ($ajax) $this->ajax()->pon( $o );
  }

  public function operacion() {

    $this->derro_post();

    $this->post_u();
    
    if (($e_aux = parent::operacion()) == null) return $e_aux;

    if ($this->obxeto("bpechar")->control_evento()) return $this->operacion_bpechar();

    if (($e_aux = $this->u_control() ) != null) return $e_aux;

    if (($e_aux = $this->operacion_pth()) != null) return $e_aux;


    return null;
  }

  protected function __paxina():Paxina {
    $v = "Wfys";

    $p = new Paxina(Config::titulo);

    $p->head->icono = Config::favicon;

    $p->head->pon_meta("viewport", "width=device-width, initial-scale=1, maximum-scale=1");
    $p->head->pon_css ("../../triwus/css/basico.css?v={$v}");
    $p->head->pon_css ("../../triwus/css/trwform.css?v={$v}");
    $p->head->pon_css ("css/basico.css?v={$v}");
    $p->head->pon_css ("css/clista.css?v={$v}");
    $p->head->pon_css ("css/xestor_pestanhas.css?v={$v}");
    $p->head->pon_js  ("js/basico.js?v={$v}");


    $p->formulario()->action = "index.php";
    $p->formulario()->ptw    = "ptw/formulario.html";

    $p->pon_panel($this->declara_pcab   ());
    $p->pon_panel($this->declara_pcentro());
    $p->pon_panel($this->declara_ppe    ());


    return $p;
  }

  protected function u_control_permiso() {
    return "admin";
  }

  protected function post_u() {
/*
    if ($this->id_usuario == null) return;

    if ($this->obxeto("caxenda"  ) != null) $this->obxeto("caxenda"  )->post_u( $this->id_usuario );
    if ($this->obxeto("cescola"  ) != null) $this->obxeto("cescola"  )->post_u( $this->id_usuario );
    if ($this->obxeto("cmaterial") != null) $this->obxeto("cmaterial")->post_u( $this->id_usuario );
*/
  }

  private function u_control() {
    if ($this->id_usuario != null) return null;

    $trw = Config::trw_s();

    if ($trw == null) {
      $this->redirect( Config::url_login );


      return $this;
    }

    $u = $trw->usuario();

    if (!$u->validado())  return $this->u_control_redirect( $trw );

    if (!$u->permiso( $this->u_control_permiso() )) return $this->u_control_redirect( $trw );

    $this->id_usuario = $u->atr("id_usuario")->valor;
    
    $this->post_u();

    return null;
  }

  private function u_control_redirect(EstadoHTTP $e_trw) {
    $this->u_control = true;
    
    $e_trw->usuario( new UsuarioWeb_obd() );
    
    Config::trw_msx( $e_trw, Config::msx_login );
    Config::trw_s($e_trw);
    
    $this->redirect( Config::url_login );

    return $this;
  }

  private function operacion_bpechar() {
    $this->u_control = true;

    $trw = Config::trw_s();

    if ($trw != null) {
      $trw->usuario( new UsuarioWeb_obd() );

      Config::trw_s($trw);
    }

    session_destroy();

    $this->redirect( Config::url_login );

    return $this;
  }

  private function operacion_pth() {
    if (!isset($_GET["pth"])) return null;

    $this->panel("centro")->activar_pestanha($this, $_GET["pth"]);

    return $this;
  }

  private static function _derro($id = "derro") {
    $d = new Div($id);

    $d->ajax_css_display = "flex";


    return $d;
  }

  private static function bpechar() {
    $i = new Image("bpechar", "imx/apaga.svg");

    $i->style("default", "width: 1em;margin-right: 0.7em;");

    $i->title = "Pechar sesión.";

    return $i;
  }
}
