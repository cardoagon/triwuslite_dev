<?php

abstract class CAcceso extends Componente {
  public function __construct($id_idioma = "es") {
    parent::__construct("cacceso");

    $this->pon_obxeto(new Div("msx_0"));
    $this->pon_obxeto(new Div("msx_1"));
    $this->pon_obxeto(new Div("msx_2"));

    $this->pon_obxeto(Panel_fs::__email("email", 23, 255, " "));
    $this->pon_obxeto(Panel_fs::__password("contrasinal", true, false,23, 255, " "));

    $this->pon_obxeto(new Checkbox("chkcookie", false, Msx::$clogin[$id_idioma][1]));

    $this->pon_obxeto(Panel_fs::__bcancelar());
    $this->pon_obxeto(Panel_fs::__baceptar(Msx::$clogin[$id_idioma][3]));
    $this->pon_obxeto(Panel_fs::__link("cambiak", Msx::$clogin[$id_idioma][2]));

    $this->obxeto("email"      )->style("default", "width: 99%;");
    $this->obxeto("contrasinal")->style("default", "width: 99%;");

    $this->obxeto("msx_0")->clase_css("default", "texto3_erro");
    $this->obxeto("msx_1")->clase_css("default", "texto3_erro");
    $this->obxeto("msx_2")->clase_css("default", "texto3_erro");

    $this->obxeto("contrasinal")->pon_eventos("onkeypress", "pcontrol_contrasinal_onkeypress(event, this)");
    $this->obxeto("cambiak"    )->envia_ajax("onclick");
  }

  abstract protected function ptw_0();
  abstract protected function ptw_2();

  abstract protected function operacion_bCancelar(Efs $e);
  abstract protected function operacion_clogin(Efs $e);
  abstract protected function operacion_cambiak(Efs $e, $renovar = false);

  public function operacion(EstadoHTTP $e) {
//~ echo "<pre>" . print_r($_REQUEST, 1) . "</pre>";
//~ echo "<pre>" . print_r($_SERVER , 1) . "</pre>";

    $this->obxeto("msx_0")->post(null);
    $this->obxeto("msx_1")->post(null);
    $this->obxeto("msx_2")->post(null);

    if ($this->obxeto("cambiak")->control_evento()) return $this->operacion_cambiak($e);

    if ($this->obxeto("contrasinal")->control_evento()) return $this->operacion_clogin($e);

    if ($this->obxeto("bAceptar")->control_evento()) return $this->operacion_clogin($e);

    if ($this->obxeto("bCancelar")->control_evento()) return $this->operacion_bCancelar($e);

    return null;
  }

  protected function validar() {
    $this->validar_email();
    $this->validar_k();
  }

  protected function validar_email() {
    $email = $this->obxeto("email")->valor();

    $erro = "";
    if (!Valida::email($email)) $erro = Msx::$clogin[$this->idioma->codigo][100];

    $this->obxeto("msx_1")->post($erro);
  }

  protected function validar_k() {
    $k = $this->obxeto("contrasinal")->valor();

    $erro = "";
    if ($k == "") $erro .= Msx::$clogin[$this->idioma->codigo][103];

    $this->obxeto("msx_2")->post($erro);
  }
}

//****************************

abstract class CCambiak extends Componente {

  protected $cambio  = null;
  
  protected $renovar = false;

  public function __construct($id_idioma = "es") {
    parent::__construct("ccambiak", $this->ptw_0());

    $this->pon_obxeto(new Div("msx_0"));
    $this->pon_obxeto(new Div("msx_1"));
    $this->pon_obxeto(new Div("msx_2"));

    $this->pon_obxeto(Panel_fs::__email("email", 23, 255, " "));
    $this->pon_obxeto(new Cknovo("cknovo", "h"));

    $this->pon_obxeto(Panel_fs::__text("tk", 35, 255, Msx::$clogin[$id_idioma][116]));

    $this->pon_obxeto(Panel_fs::__bcancelar());
    $this->pon_obxeto(Panel_fs::__baceptar(Msx::$clogin[$id_idioma][6], Msx::$clogin[$id_idioma][7]));


    $this->pon_obxeto(Panel_fs::__link("cambiak", Msx::$clogin[$id_idioma][4]));
    
    $this->pon_obxeto(Panel_fs::__link("lreenviar", Msx::$clogin[$id_idioma][11]));

    $this->obxeto("tk")->style("default", "width: 77%; text-align: center;");

    $this->obxeto("email")->style("default", "width: 99%;");

    //~ $this->obxeto("msx_0")->clase_css("default", "texto3_erro");
    //~ $this->obxeto("msx_1")->clase_css("default", "texto3_erro");
    //~ $this->obxeto("msx_2")->clase_css("default", "texto3_erro");

    $this->obxeto("cambiak"  )->envia_ajax("onclick");
    $this->obxeto("lreenviar")->envia_ajax("onclick");
    $this->obxeto("bAceptar" )->envia_ajax("onclick");
  }

  abstract protected function ptw_0();
  abstract protected function ptw_2();

  abstract protected function operacion_bCancelar(Efs $e);
  abstract protected function operacion_recuperar(Efs $e);
  abstract protected function operacion_lreenviar(Efs $e);
  abstract protected function operacion_cambiak(Efs $e, $renovar = false);

  public function operacion(EstadoHTTP $e) {
    $this->obxeto("msx_0")->post(null);
    $this->obxeto("msx_1")->post(null);
    $this->obxeto("msx_2")->post(null);

    if ($this->obxeto("lreenviar")->control_evento()) return $this->operacion_lreenviar($e);

    if ($this->obxeto("cambiak"  )->control_evento()) return $this->operacion_cambiak  ($e);

    if ($this->obxeto("bAceptar" )->control_evento()) return $this->operacion_recuperar($e);

    if ($this->obxeto("bCancelar")->control_evento()) return $this->operacion_bCancelar($e);

    return null;
  }


  final public function inicia($renovar_k = false) {
    $this->renovar = $renovar_k;

    $this->cambio = null;
    
    $this->ptw = $this->ptw_0();

    $this->obxeto("cknovo")->__limpar();
  }

  protected function operacion_cambio(Efs $e, $pcontrol = true) {
    $cbd = new FS_cbd();

    //~ $tk = $this->obxeto("tk")->valor();

    $c = Cambio_obd::inicia($this->obxeto("tk")->valor(), $cbd);

    if ($c == null) {
      $this->obxeto("msx_0")->post("Error, el código de verificación introducido no es válido.");

      return $e;
    }

    if (($erro = $c->valida_click()) != null) {
      $this->obxeto("msx_0")->post("Error, no se pudo completar la operación de confirmación. {$erro}");

      return $e;
    }

    $cbd->transaccion(true);

    if (!$c->click($cbd)) {
      $this->obxeto("msx_0")->post("Error, al actualizar la BD.");

      $cbd->rollback();

      return $e;
    }

    $cbd->commit();

    $u = $c->usuario_obd();

    $e->usuario($u);
/*
    if ($pcontrol)
      XCCS::__mail_confirma_op($u->atr("email")->valor, "cambio de contraseña");
    else
*/
      XCCS::__mail_confirma_opweb($u->atr("email")->valor, "cambio de contraseña", $c->atr("id_site")->valor);



    return $e;
  }


  protected function validar() {
    $this->validar_email();
    $this->validar_k();
  }

  protected function validar_email() {
    $email = $this->obxeto("email")->valor();

    $erro = "";
    if (!Valida::email($email)) $erro = Msx::$clogin[$this->idioma->codigo][100];

    $this->obxeto("msx_1")->post($erro);
  }

  protected function validar_k() {
    $erro = $this->obxeto("cknovo")->validar();

    $this->obxeto("msx_2")->post($erro);
  }

}

