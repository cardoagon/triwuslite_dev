<?php

final class CCambiak_pcontrol extends CCambiak {
  const ptw_0 = "ptw/clogin/pcontrol/ccambiakweb_0.html";
  const ptw_1 = "ptw/clogin/pcontrol/ccambiakweb_1.html";
  const ptw_2 = "ptw/clogin/pcontrol/crenovarkweb_0.html";
  const ptw_3 = "ptw/clogin/pcontrol/crenovarkweb_1.html";
  
  public function __construct() {
    parent::__construct();

    $this->visible = false;

    $this->cambiar_idioma( Idioma::factory("es") );
    
    $this->pon_obxeto(CAcceso_pcontrol::__boton("bAceptar" , "Cambiar la contraseña"));
    $this->pon_obxeto(CAcceso_pcontrol::__boton("bAceptar2", "Corfirmar el código"  ));
    
    $this->pon_obxeto(CAcceso_pcontrol::__txt("email"));
    $this->pon_obxeto(CAcceso_pcontrol::__txt("tk"   ));

    $this->pon_obxeto(CAcceso_pcontrol::__link("cambiak"  , "Acceso clientes"));
    $this->pon_obxeto(CAcceso_pcontrol::__link("lreenviar", "Volver a enviar"));

$this->obxeto("lreenviar")->envia_submit("onclick");
  }

  protected function ptw_0() {
    if ($this->renovar) return Refs::url(self::ptw_2);

    return Refs::url(self::ptw_0);
  }

  protected function ptw_2() {
    if ($this->renovar) return Refs::url(self::ptw_3);

    return Refs::url(self::ptw_1);
  }

  public function operacion(EstadoHTTP $e) {
    $this->obxeto("msx_0")->post(null);
    $this->obxeto("msx_1")->post(null);
    $this->obxeto("msx_2")->post(null);

    if ($this->obxeto("bAceptar2")->control_evento()) return $this->operacion_recuperar($e);

    return parent::operacion($e);
  }

  protected function operacion_cambio(Efs $e, $pcontrol = true) {
    $e_aux = parent::operacion_cambio($e);

    if (($erro = $this->obxeto("msx_0")->valor()) != null) {
      $e->post_msx($erro, "e", false);
      
      return $e;
    }

    $this->ptw = $this->ptw_0();

    return new Epcontrol( $e_aux->usuario() );
  }

  protected function operacion_bCancelar(Efs $e) {
    //* 20170529 operacion cancelar xa non exite, non se usa boton.

    if ($this->obxeto("tk")->valor() != null) return $this->operacion_cambio($e);


    $e->post_msx("Debes teclear el código de verificación.", "e", false);


    return $e;
  }

  protected function operacion_cambiak(Efs $e, $renovar = false) {
    $e->cambiar_panel();

    return $e;
  }

  protected function operacion_recuperar(Efs $e) {
    if ($this->ptw == $this->ptw_2()) {
      return $this->operacion_bCancelar($e);
    }

    $this->validar();

    if (($m = $this->obxeto("msx_1")->valor()) != null) {
      $e->post_msx($m, "e", false);
      
      return $e;
    }
    if (($m = $this->obxeto("msx_2")->valor()) != null) {
      $e->post_msx($m, "e", false);
      
      return $e;
    }

    $email = $this->obxeto("email")->valor();
    $k     = $this->obxeto("cknovo")->__k();

    $c = XCCS::inicia_ck_obd($email, $k);
    if (($erro = XCCS::aceptar_ck($email, $k, $c) ) != null) {
      $e->post_msx($erro, "e", false);
    }
    else {
      $this->cambio = null;

      $this->pon_obxeto(new Param("email_activar", $email));

      $this->ptw = $this->ptw_2();
      

      $e->post_msx("El código de verificación ha sido enviado a <b>{$email}</b>.", "m", false);
    }


    return $e;
  }

  protected function operacion_lreenviar(Efs $e) {
    $c = $this->cambio;
    $m = $this->obxeto("email_activar")->valor();

//~ echo "<pre>$m\n" . print_r($c->a_resumo(), 1) .  "</pre>";


    if ($c != null) XCCS::__mail_ck($c, $m)->enviar();


    $e->post_msx("El código de verificación ha sido enviado a <b>{$m}</b>.", "m", false);


    return $e;
  }
}

//****************************

final class CAcceso_pcontrol extends CAcceso {
  const ptw_0 = "ptw/clogin/pcontrol/caccesoweb_0.html";
  const ptw_2 = "ptw/clogin/pcontrol/caccesoweb_1.html";
  const ptw_3 = "ptw/clogin/pcontrol/caccesoweb_2.html";
  const ptw_4 = "ptw/clogin/pcontrol/caccesoweb_4.html";


  //~ private bool $factor2 = false;

  public function __construct() {
    parent::__construct();

    $this->cambiar_idioma( Idioma::factory("es") );

    $this->ptw = $this->ptw_0();
    
    $this->pon_obxeto( new Param("pcontrol2_url", "") );

    $this->pon_obxeto(self::__link ("cambiak", "Olvidaste tu contraseña"));
    
    $this->pon_obxeto(self::__boton("bAceptar", "Iniciar sesión"));
    
    $this->pon_obxeto(new Param("mf2") );
    $this->pon_obxeto(self::__txt("kf2"));
    
    $this->pon_obxeto(self::__txt("email"));
    $this->pon_obxeto(Panel_fs::__password("contrasinal"));

    
    //~ $this->obxeto("email")->envia_submit("onchange");
    
    $this->obxeto("contrasinal")->pon_eventos("onkeypress", "pcontrol_contrasinal_onkeypress(event, this)");
  }

  protected function ptw_0() {
    return Refs::url(self::ptw_0);
  }

  protected function ptw_2() {
    return Refs::url(self::ptw_2);
  }

  protected function ptw_3() {
    return Refs::url(self::ptw_3);
  }

  protected function ptw_4() {
    return Refs::url(self::ptw_4);
  }

  protected function operacion_bCancelar(Efs $e) {
    //~ $e->redirect("http://triwus.com");

    return $e;
  }

  protected function operacion_clogin(Efs $e) {
//~ echo $e->evento()->html();

    if ($this->ptw == $this->ptw_2()) return $this->operacion_bCancelar($e);
    if ($this->ptw == $this->ptw_3()) return $e;
    //~ if ($this->ptw == $this->ptw_4()) return $this->factor2_operacion($e);

    $this->validar();

    if (($m = $this->obxeto("msx_1")->valor()) != null) {
      $e->post_msx($m, "e", false);
      
      return $e;
    }
    if (($m = $this->obxeto("msx_2")->valor()) != null) {
      $e->post_msx($m, "e", false);
      
      return $e;
    }

    $u = $e->usuario();

    $cbd = new FS_cbd();

    $r_login = $u->login_pcontrol($cbd, $this->obxeto("email")->valor(), $this->obxeto("contrasinal")->valor());

//~ echo "<pre>" . print_r($r_login, 1) . "</pre>";

    switch ($r_login) {
      case -1:
        $this->ptw = Refs::url(self::ptw_2);

        break;

      case  0:
        //~ echo "<pre>" . print_r($u->a_resumo(), true) . "</pre>";
        
        if (($_aux = $this->validar_pcontrol2($e, $u, $cbd)) != null) return $e_aux;
        
        //~ if (($_aux = $this->factor2_validar  ($e, $u, $cbd)) != null) return $e_aux;

        
        if ($u->atr("k_renovar")->valor != null) return $this->operacion_cambiak($e, true);


        Acceso::rexistrar($cbd, 
                          $u->atr("id_site")->valor, 
                          $_SERVER["REMOTE_ADDR"], 
                          $this->obxeto("email")->valor(), 
                          "pcontrol", 
                          1
                          );
        
        return new Epcontrol($u);
        
      default:
        $intentos = 1 + FGS_usuario::log_max_intentos - $r_login;

        $erro = "Error de credenciales.";
        if ($intentos <= 3) $erro .= " Te quedan {$intentos} intentos.";

        $e->post_msx($erro, "e", false);

        break;
    }


    return $e;
  }

  protected function operacion_cambiak(Efs $e, $renovar = false) {
    if ($renovar) $e->post_msx("Es necesario que renueves tu contraseña", "a", false);
    
    $e->obxeto("ccambiak")->inicia($renovar);

    $e->cambiar_panel();


    return $e;
  }

  private function validar_pcontrol2(Efs $e, FGS_usuario $u, FS_cbd $cbd):?Efs {
    if ($u->atr("pcontrol2")->valor <> 1) return null;
    
    if (parse_url($_SERVER["HTTP_ORIGIN"], PHP_URL_HOST) != "triwus.com") return null;

    $this->ptw = Refs::url(self::ptw_3);
    
    if (($s = $u->site_obd()) == null) {
      $this->obxeto("pcontrol2_url")->post( "ERROR. CAcceso_pcontrol.validar_pcontrol2(). ((s = u.site_obd()) == null)." );
      
      return $e;
    }
    
    if (($dom = TrwlConfig::dominio) == null) $dom = "---";
              
    $this->obxeto("pcontrol2_url")->post( "https://{$dom}/admin/pcontrol.php" );
    
    return $e;
  }

  private function factor2_validar(Efs $e, FGS_usuario $u, FS_cbd $cbd):?Efs {
//~ echo "1111111111111<pre>" . print_r($_COOKIE, 1) . "</pre>";
//~ echo "1111111111111<pre>" . print_r($u->a_resumo(), 1) . "</pre>";

    //* validar cookie factor2.
    if ( Efs::cookie_firma_valida($u, "trw-kf2cookie") ) return null;


    //* envío código factor2.
/*
    $c = XCCS::inicia_factor2_obd();
    if (($erro = XCCS::aceptar_factor2($c, $u->atr("email")->valor )) != null) {
      $e->post_msx($erro, "e", false);
    
      return $e;
    }
*/
    
    //* plantilla factor2 e resposta.
    $this->ptw = $this->ptw_4();
    
    $this->obxeto("mf2")->post( $this->obxeto("email")->valor() );

    $e->post_msx("Es necesario realizar una validación de doble factor.", "a");
    
    return $e;
  }

  public static function __link($id, $txt, $title = null) {
    $l = new Link($id, $txt);

    $l->title = $title;

    //~ $l->pon_eventos("onclick", "pcontrol_login_link_onclick(this)");
    //~ $l->envia_SUBMIT("onclick");
    $l->envia_ajax("onclick");


    return $l;
  }

  public static function __boton($id, $txt, $title = null) {
    $b = new Button($id, $txt);

    $b->title = $title;

    return $b;
  }

  public static function __txt($id, $pass = false, $title = null) {
    $t = ($pass)?new Password($id):new Text($id);

    $t->size        = 255;
    $t->title       = $title;
    $t->placeholder = " ";

    $t->clase_css("default", null);

    return $t;
  }

  public static function rlogin_valida($rlogin) {
    $t = ($pass)?new Password($id):new Text($id);

    $t->size        = 255;
    $t->title       = $title;
    $t->placeholder = " ";

    $t->clase_css("default", null);

    return $t;
  }

  private function factor2_operacion(Efs $e) {
    //* Validamos kf2.
    
    
    //* Acceso correcto.
    $u = $e->usuario();
    
    Acceso::rexistrar(new FS_cbd(), 
                      $u->atr("id_site")->valor, 
                      $_SERVER["REMOTE_ADDR"], 
                      $this->obxeto("email")->valor(), 
                      "pcontrol", 
                      1
                      );
    
    $e_aux = new Epcontrol($u);
    
    $e_aux->pon_cookie("trw-kf2cookie", 
                       Efs::cookie_firma_calcula($u, "trw-kf2cookie"), 
                       time() + (3600 * 24 * 1), 
                       "/", "", TRUE, TRUE
                      );
    
    
    return $e_aux;
  }
}


