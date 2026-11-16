<?php

final class CLoginWeb extends    Componente
                      implements I_fs       {

  const ptw_0 = "ptw/clogin/web/cloginweb_0.html";
  const ptw_1 = "ptw/clogin/web/cloginweb_1.html";

  public $id_site;
  public $ru_diferido;

  public function __construct(Site_obd $s, $id_idioma) {
    parent::__construct("cloginweb");

    $this->id_site = $s->atr("id_site")->valor;

    $sc = Site_config_obd::inicia(new FS_cbd(), $this->id_site);

    $this->visible     = $sc->atr("ru_visible")->valor == 1;

    $this->ru_diferido = $sc->atr("ru_diferido")->valor;

      $this->pon_obxeto(self::__link_r("lrexistro", "Reg&iacute;strate", "login.php"));
      $this->pon_obxeto(self::__link_r("lclientes", "Login", "login.php"));
      $this->pon_obxeto(self::__link_r("lxclientes", "Editar perfil", "perfil.php"));
      $this->pon_obxeto(self::__lpechar());
  }

  public function config_lectura() {}
  public function config_edicion() {}

  public function config_usuario(IEfspax_xestor $e = null) {
    if ($e->usuario()->validado())
      $this->ptw = Refs::url(self::ptw_1);
    else
      $this->ptw = Refs::url(self::ptw_0);
  }

  public function operacion(EstadoHTTP $e) {
    if ($this->obxeto("lpechar")->control_evento()) return $this->operacion_lpechar($e);

    return null;
  }

  public function operacion_lpechar(IEfspax_xestor $e) {
    $u = new UsuarioWeb_obd();

    $e->usuario($u);

    $this->config_usuario($e);

    $e->redirect("login.php");


    return $e;
  }

  private static function __link($id, $txt, $k="test") {
    $l = new Link($id, $txt);

    //~ $l->href = "?k={$k}";

    $l->envia_submit("onclick");
    //~ $l->envia_ajax  ("onclick");


    return $l;
  }

  private static function __link_r($id, $txt, $href) {
    $l = new Span($id, $txt);

    $l->pon_eventos("onclick", "window.location.href = '{$href}'");


    return $l;
  }

  private static function __lpechar() {
    $l = new Link("lpechar", "Cerrar Sesión");

    $l->envia_submit("onclick", "¿ Desea cerrar la sesión ?");


    return $l;
  }
}

//****************************

final class CLoginWeb_form extends Componente {
  const ptw_0 = "ptw/clogin/web/cloginweb_form_0.html";
  //~ const ptw_1 = "ptw/clogin/web/cloginweb_form_1.html";
  const ptw_2 = "ptw/clogin/web/cloginweb_form_2.html";

  private $permiso           = null;

  private $redirect          = null;
  private $redirect_bloquear = false;


  public function __construct(FGS_usuario $u, $id_site, $t = 0, $ru_registro_visible = 1, $ru_difierido = 0, $id_idioma = "es") {
    parent::__construct("clwf");

    $this->__tipo($t);

    $this->pon_obxeto(new Param("pdisplay", "initial"));


    $this->pon_obxeto(new CAccesoWeb  ($id_site, $id_idioma, $u           ));
    $this->pon_obxeto(new CCambiakWeb ($id_site, $id_idioma, $ru_difierido));
    $this->pon_obxeto(new CRexistroWeb($id_site, $id_idioma, $ru_difierido));

    if (!$ru_registro_visible) {
      $this->obxeto("pdisplay")->post( "none" );
      
      $this->obxeto("crexistroweb")->visible = false;
    }


  }

  public function declara_css() {
    return array( Refs::url("css/cpagar.css") );
  }


  public function permiso(?string $permiso = null):?string {
    if ($permiso != "admin") $this->permiso = $permiso;
    
    return $this->permiso;
  }
  

  public function redirect():?string {
    return $this->redirect;
  }

  public function redirect_asignar(?string $r):void {
    if ($this->redirect_bloquear) return;
    
    $this->redirect = $r;
  }

  public function redirect_bloqueo(bool $b):void {
    $this->redirect_bloquear = $b;
  }

  public function permiso_asignar(?string $p):void {   
    $this->permiso = $p;
  }

  public function operacion(EstadoHTTP $e) {
    if (($e_aux = $this->obxeto("cacceso"     )->operacion($e)) != null) return $e_aux;
    if (($e_aux = $this->obxeto("ccambiak"    )->operacion($e)) != null) return $e_aux;
    if (($e_aux = $this->obxeto("crexistroweb")->operacion($e)) != null) return $e_aux;

    return null;
  }

  public function __tipo($t = 0) {
    $ptw = ($t == 2)?self::ptw_2:self::ptw_0;

    $this->ptw = Refs::url($ptw);
    
        //~ if ($t == 1) $this->ptw = Refs::url(self::ptw_1);
    //~ elseif ($t == 2) $this->ptw = Refs::url(self::ptw_2);
    //~ else             $this->ptw = Refs::url(self::ptw_0);
  }

  public static function email_invitacion(Cambio_obd $c, $id_idioma, $d = "triwus/", $redirect = "") {
    $id_site = $c->atr("id_site"  )->valor;
    $email   = $c->atr("email"    )->valor;
    $codigo  = $c->atr("id_cambio")->valor;


    $cbd = new FS_cbd();
    
    $s = Site_obd::inicia($cbd, $id_site);
    
    $a = $s->admin($cbd);

    $tenda  = self::__tenda($s);
    $legal  = self::__ptw_legal($d, $s->atr("nome")->valor);
    
    if ( $redirect != null ) $redirect = "&redirect={$redirect}";


    $html = self::__ptw($d, CPagar2_email::ptw_5, $id_idioma);
    $html = str_replace("[nome]", "", $html);
    $html = str_replace("[link_enlace]", "<a href='https://{$tenda}/confirmar.php?c={$codigo}{$redirect}'>https://{$tenda}/confirmar.php?c={$codigo}{$redirect}</a>", $html);
    $html = str_replace("[dominio]", "{$tenda}", $html);
    $html = str_replace("[legal]", $legal, $html);

   // $mail_inv = new XMail2();
    $mail_inv = new XMail3($id_site);

    (($_x = $mail_inv->_config()) != [])
     ? $mail_inv->pon_rmtnt($_x['email'])
     : $mail_inv->pon_rmtnt("no-reply@{$tenda}"); 

    $mail_inv->pon_asunto   (Msx::$clogin[$id_idioma][8] . $tenda);
    $mail_inv->pon_direccion($email );
    $mail_inv->pon_cc       ($a->atr("login")->valor );

    $mail_inv->pon_corpo($html);


    try {
      $mail_inv->enviar();
    }
    catch (Exception $exc) {
      throw $exc;
    }
    
    //~ echo $mail_inv->html();
  }


  private static function __tenda(Site_obd $s) {
    if ($s->atr("dominio")->valor != null) return $s->atr("dominio")->valor;

    return $s->atr("nome")->valor;
  }

  private static function __ptw($d, $src, $id_idioma = "es") {
    return LectorPlantilla::plantilla("{$d}{$src}", Idioma::factory($id_idioma));
  }

  private static function __ptw_legal($d, $nome_site) {
    $src = Refs::url_sites . "/{$nome_site}/" . Refs::url_legais . "/lopd_email.html";

    $ptw = self::__ptw($d, $src);

    return $ptw;
  }
}

//****************************

class CCambiakWeb extends CCambiak {
  const ptw_0 = "ptw/clogin/web/ccambiakweb_0.html";
  const ptw_2 = "ptw/clogin/web/ccambiakweb_1.html";

  private $id_site     = null;
  private $ru_diferido = null;

  public function __construct($id_site, $id_idioma, $ru_diferido = 0) {
    parent::__construct($id_idioma);

    $this->id_site     = $id_site;
    $this->ru_diferido = $ru_diferido;

    $this->obxeto("email")->clase_css("default", "text cpagar3_text");
  }

  protected function ptw_0() {
    return Refs::url(self::ptw_0);
  }

  protected function ptw_2() {
    return Refs::url(self::ptw_2);
  }

  protected function operacion_cambio(Efs $e, $pcontrol = true) {
    $e = parent::operacion_cambio($e, false);

    $e->obxeto("cloginweb")->config_usuario($e);

    $this->obxeto("tk")->post(null);

    return $this->operacion_bCancelar($e);
  }

  protected function operacion_bCancelar(Efs $e) {
    if ($this->obxeto("tk")->valor() != null) return $this->operacion_cambio($e);

    $this->pai->__tipo();
    
    $this->pai->preparar_saida($e->ajax());


    return $e;
  }

  protected function operacion_cambiak(Efs $e, $renovar = false) {
    $this->pai->__tipo(1);

    $this->pai->preparar_saida($e->ajax());


    return $e;
  }

  protected function operacion_recuperar(Efs $e) {
//~ echo "111111111111111111111<br>";
    if ($this->ptw == $this->ptw_2()) return $this->operacion_bCancelar($e);

    $this->validar_email();

    if (($m = $this->obxeto("msx_1")->valor()) != null) {
      $e->post_msx($m);
      
      return $e;
    }

//~ echo "2222222222222222222222222<br>";


    $email = $this->obxeto("email")->valor();

    $this->validar_email_site($email);


    if (($m = $this->obxeto("msx_0")->valor()) != null) {
      $e->post_msx($m);
      
      return $e;
    }

//~ echo "333333333333333333333333<br>";

    try {
      CLoginWeb_form::email_invitacion($this->cambio_obd(), $this->idioma->codigo);

      $this->config_recuperar_ok($email);
    
      $this->pai->preparar_saida($e->ajax());
    }
    catch (Exception $exc) {
      $e->post_msx($exc->getMessage());
    }


    return $e;
  }

  protected function operacion_lreenviar(Efs $e) {
    try {
      $c_obd = $this->cambio_obd();
      
      CLoginWeb_form::email_invitacion($c_obd, $this->idioma->codigo);
      
      $m = $c_obd->atr("email")->valor;

      $e->post_msx(Msx::$clogin[$this->idioma->codigo][117]. " <b>{$m}</b>.", "m");
    }
    catch (Exception $exc) {
      $e->post_msx($exc->getMessage());
    }


    return $e;
  }
  
  protected function config_recuperar_ok($email) {
   // $this->pon_obxeto(Panel_fs::__baceptar());
    $this->pon_obxeto(Panel_fs::__baceptar(Msx::$clogin[$this->idioma->codigo][10], Msx::$clogin[$this->idioma->codigo][10]));

    $this->ptw = $this->ptw_2();

    $this->pon_obxeto(new Param("email_activar", $email));
  }

  private function validar_email_site($email, FS_cbd $cbd = null) {
    $u = Site_obd::u_login($this->id_site, $email, $cbd);

//~ echo "<pre>" . print_r($u->a_resumo(), 1) . "</pre>";

    $erro = "";
    
        if ( $u == null                            ) $erro = Msx::$clogin[$this->idioma->codigo][101];
    elseif ( $u->atr("permisos")->valor == "admin" ) $erro = Msx::$clogin[$this->idioma->codigo][101];
    elseif ( !$u->validado()                       ) $erro = Msx::$clogin[$this->idioma->codigo][101];
    elseif ( $u->atr("u_baixa" )->valor > 0        ) $erro = Msx::$clogin[$this->idioma->codigo][101];

//~ echo "$erro<br>";

    $this->obxeto("msx_0")->post($erro);
  }

  private function cambio_obd() {
    if ($this->cambio != null) return $this->cambio;
    

    $c = new Cambio_invitaweb_obd();

    $c->atr("id_site" )->valor = $this->id_site;
    $c->atr("email"   )->valor = $this->obxeto("email")->valor();

    $cbd = new FS_cbd();

    $cbd->transaccion();

    if (!$c->insert($cbd)) {
      $cbd->rollback();

      return null;
    }

    $cbd->commit();

    $this->cambio = $c;

//~ echo "<pre>" . print_r($this->cambio, 1) . "</pre>";
    
    return $c;
  }
}

//****************************

class CAccesoWeb extends CAcceso {
  const ptw_0 = "ptw/clogin/web/caccesoweb_0.html";
  const ptw_2 = "ptw/clogin/web/caccesoweb_1.html";

  protected $id_site;

  public function __construct($id_site, $id_idioma, FGS_usuario $u) {
    parent::__construct($id_idioma);

    //~ $this->ptw = ($u->login_intentos() >= 5)?$this->ptw_2():$this->ptw_0();
    $this->ptw = $this->ptw_0();

    $this->id_site = $id_site;

    $this->obxeto("cambiak")->visible = true;
    $this->obxeto("cambiak")->envia_AJAX("onclick");

    $this->obxeto("contrasinal")->sup_eventos();

    $this->obxeto("email"      )->clase_css("default", "text cpagar3_text");
    $this->obxeto("contrasinal")->clase_css("default", "text cpagar3_text");

    $this->obxeto("bAceptar")->envia_AJAX("onclick");
  }

  protected function ptw_0() {
    return Refs::url(self::ptw_0);
  }

  protected function ptw_2() {
    return Refs::url(self::ptw_2);
  }

  protected function operacion_bCancelar(Efs $e) {
    return $e;
  }

  protected function operacion_clogin(Efs $e) {
    $this->pai->redirect_bloqueo(false);

    $this->validar();
    
    $m = null;
    if ( ($m_0 = $this->obxeto("msx_1")->valor()) != null ) $m .= "<div>&bull;&nbsp;{$m_0}</div>"; 
    if ( ($m_0 = $this->obxeto("msx_2")->valor()) != null ) $m .= "<div>&bull;&nbsp;{$m_0}</div>"; 

    if ( $m != null ) {
      $this->pai->redirect_bloqueo(true);
      
      $e->post_msx($m);
      
      return $e;
    }
    

    $u   = new UsuarioWeb_obd();

    $cbd = new FS_cbd();

    $r_login = $u->login_web($cbd, $this->id_site, $this->obxeto("email")->valor(), $this->obxeto("contrasinal")->valor());

    switch ($r_login) {
      case -1:
        $this->ptw = $this->ptw_2();

        $this->pon_obxeto(Panel_fs::__baceptar());

        break;

      case  0:
        if ( $this->obxeto("chkcookie")->valor() ) $this->kcookie_pon($e, $u, $cbd);

        $this->obxeto("contrasinal")->post("");
        
        $u = Pedido_obd::asignar_permisos($u, $cbd);

        $e->usuario($u);

        $e->obxeto("cloginweb")->config_usuario($e);

        $e->pon_obxeto( new Menu_site($e->__site_obd($this->id_site, $cbd), $u, $e->config(), $e->id_idioma(), $e->edicion()) );

//~ echo "CLoginWeb. redirect::" . $this->pai->redirect() . "<br>";

        if (($redirect = $this->pai->redirect()) == null) $redirect =  "perfil.php";

        Acceso::rexistrar($cbd, 
                          $this->id_site, 
                          $_SERVER["REMOTE_ADDR"], 
                          $this->obxeto("email")->valor(), 
                          $_SERVER["HTTP_REFERER"], 
                          1
                          );        

        //~ $e->redirect( $redirect );
        
        $e->ajax()->pon_ok( true, "window.history.replaceState(null, '', '{$redirect}');document.location = '{$redirect}'" );


        return $e;

      default:
        $this->pai->redirect_bloqueo(true);
        
        $intentos = 1 + FGS_usuario::log_max_intentos - $r_login;

        $erro = Msx::$clogin[$this->idioma->codigo][101];
        if ($intentos <= 3) $erro .= "<br />Te quedan {$intentos} intentos.";

        $this->obxeto("msx_0")->post($erro);

        $e->post_msx($erro);

        Acceso::rexistrar($cbd, 
                          $this->id_site, 
                          $_SERVER["REMOTE_ADDR"], 
                          $this->obxeto("email")->valor(), 
                          $_SERVER["HTTP_REFERER"], 
                          0
                          );        
    }


    if ($e->evento()->ajax()) $this->preparar_saida($e->ajax());


    return $e;
  }

  protected function operacion_cambiak(Efs $e, $renovar = false) {
    $this->pai->__tipo(2);
    
    $this->pai->obxeto("ccambiak")->inicia($renovar);

    $this->pai->preparar_saida($e->ajax());

    return $e;
  }


  private function kcookie_pon(Efs $e, FGS_usuario $u, FS_cbd $cbd):void {
    //* trw-kcookie_0.
    if (($kcookie = $u->atr("kcookie")->valor) == null) {
      $kcookie = hash('sha256', uniqid("", true) . $u->atr("k4")->valor );

      $u->update_kcookie($cbd, $kcookie);
    }

    $e->pon_cookie("trw-kcookie_0", 
                   $kcookie, 
                   time() + (3600 * 24 * 30), 
                   "/", "", TRUE, TRUE
                  );


    //* trw-kcookie_1.
    $e->pon_cookie("trw-kcookie_1", 
                   Efs::cookie_firma_calcula($u, "trw-kcookie_1", $kcookie), 
                   time() + (3600 * 24 * 30), 
                   "/", "", TRUE, TRUE
                  );

  }

}

//****************************

class CRexistroWeb extends Componente {
  const ptw_0 = "ptw/clogin/web/crexistroweb_0.html";
  const ptw_2 = "ptw/clogin/web/crexistroweb_1.html";
  const ptw_3 = "ptw/clogin/web/crexistroweb_3.html";
  const ptw_4 = "ptw/clogin/web/crexistroweb_4.html";

  private $id_site     = null;
  private $ru_diferido = null;
  private $cambio      = null;

  private $ru_registro_visible = null;

  public function __construct($id_site, $id_idioma, $ru_diferido = 0) {
    parent::__construct("crexistroweb", ($ru_diferido == 0)?Refs::url(self::ptw_0):Refs::url(self::ptw_3));

    $this->id_site     = $id_site;
    $this->ru_diferido = $ru_diferido;

    $this->pon_obxeto(new Param("pemail"));
    
    $this->pon_obxeto(Panel_fs::__text("email", 27, 255, " "));

    $this->obxeto("email")->style("default", "width: 91%;");
    
    $this->pon_obxeto(Panel_fs::__link("lreenviar", Msx::$clogin[$id_idioma][11]));

    $this->pon_obxeto(Panel_fs::__text("tk", 35, 255, Msx::$clogin[$id_idioma][116]));

    $this->pon_obxeto(new Checkbox("ch_lic", false));


    $this->pon_obxeto(new Span("erro_0"));
    $this->pon_obxeto(new Span("erro_email"));
    $this->pon_obxeto(new Span("erro_lic"));
/*
    $this->obxeto("erro_0"    )->style("default", "color: #ba0000;");
    $this->obxeto("erro_email")->style("default", "color: #ba0000;");
    $this->obxeto("erro_lic"  )->style("default", "color: #ba0000;");
*/

    $this->pon_obxeto(new Param("href_aviso", "legal.php"));
    $this->pon_obxeto(new Param("href_lopd" , "legal.php"));

    $this->pon_obxeto(Panel_fs::__bcancelar());
    $this->pon_obxeto(Panel_fs::__baceptar(Msx::$clogin[$id_idioma][5]));

    $this->obxeto("email")->clase_css("default", "text cpagar3_text");

    $this->obxeto("bAceptar" )->envia_AJAX("onclick");
    $this->obxeto("lreenviar")->envia_AJAX("onclick");

    if ($ru_diferido != 1) return;

    $this->pon_obxeto(Panel_fs::__text("nome", 20, 255, " "));
    $this->pon_obxeto(Panel_fs::__text("razon_social", 20, 255, " "));
    $this->pon_obxeto(Panel_fs::__text("mobil_p0", 4,  3, " "));
    $this->pon_obxeto(Panel_fs::__text("mobil", 15,  12, " "));

    $this->pon_obxeto(new Span("erro_rs"));
    $this->pon_obxeto(new Span("erro_mobil"));
    $this->pon_obxeto(new Span("erro_cnif"));

    //~ $this->obxeto("erro_rs"   )->style("default", "color: #ba0000;");
    //~ $this->obxeto("erro_mobil")->style("default", "color: #ba0000;");

    $this->obxeto("nome"        )->clase_css("default", "text cpagar3_text");
    $this->obxeto("razon_social")->clase_css("default", "text cpagar3_text");
    $this->obxeto("mobil"       )->clase_css("default", "text cpagar3_text");
    $this->obxeto("mobil_p0"    )->clase_css("default", "text cpagar3_text");
  }

  public function operacion(EstadoHTTP $e) {
    $this->obxeto("erro_0"    )->post(null);
    $this->obxeto("erro_email")->post(null);
    $this->obxeto("erro_lic"  )->post(null);

    if ($this->ru_diferido == 1) {
      $this->obxeto("erro_rs"   )->post(null);
      $this->obxeto("erro_mobil")->post(null);
    }

    //~ echo $e->evento()->html();

    if ($this->obxeto("lreenviar")->control_evento()) return $this->operacion_lreenviar($e);

    if ($this->obxeto("bAceptar" )->control_evento()) return $this->operacion_rexistroweb($e);

    if ($this->obxeto("bCancelar")->control_evento()) return $this->operacion_bCancelar($e);

    return null;
  }

  protected function operacion_lreenviar(Efs $e) {
    try {
      $c_obd = $this->cambio_obd();
      
      CLoginWeb_form::email_invitacion($c_obd, $this->idioma->codigo);
      
      $m = $c_obd->atr("email")->valor;

      $e->post_msx(Msx::$clogin[$this->idioma->codigo][117] . " <b>{$m}</b>.", "m");
    }
    catch (Exception $exc) {
      $e->post_msx($exc->getMessage());
    }


    return $e;
  }
  
  protected function operacion_rexistroweb(IEfspax_xestor $e) {
    if ($this->ru_diferido == 1) return $this->operacion_ru_diferida($e);

    if ($this->ptw == Refs::url(self::ptw_2)) return $this->operacion_bCancelar($e);


    $this->validar();
    
    $m = null;
    if (($m_0 = $this->obxeto("erro_email")->valor()) != null) $m .= "<div>&bull;&nbsp;{$m_0}</div>";
    if (($m_0 = $this->obxeto("erro_lic"  )->valor()) != null) $m .= "<div>&bull;&nbsp;{$m_0}</div>";

    if ($m != null) {
      $e->post_msx($m);

      return $e;
    }


    $this->validar_email_site();
    
    $m = null;
    if (($m_0 = $this->obxeto("erro_0")->valor()) != null) $m .= "<div>&bull;&nbsp;{$m_0}</div>";

    if ($m != null) {
      $e->post_msx($m);

      return $e;
    }


    try {
      CLoginWeb_form::email_invitacion($this->cambio_obd(), $this->idioma->codigo);


      $this->obxeto("pemail")->post( $this->obxeto("email")->valor() );

      $this->ptw = Refs::url(self::ptw_2);

      $this->obxeto("bAceptar")->title = null;
      
      $this->preparar_saida($e->ajax());

      $msx = Msx::$clogin[$this->idioma->codigo][117] . $this->obxeto("email")->valor();

      $e->post_msx($msx, "m");
    }
    catch (Exception $exc) {
      $e->post_msx($exc->getMessage());
    }


    return $e;
  }

  protected function operacion_ru_diferida(IEfspax_xestor $e) {
    if ($this->ptw == Refs::url(self::ptw_4)) return $this->operacion_bCancelar($e);

    $this->validar();

    $err = "";
    foreach( $this->obxetos("erro_") as $oerr ) {
      if (($oerr_i = $oerr->valor()) == null) continue;
      
      $err .= "<div>&bull;&nbsp;{$oerr_i}</div>";
    }

    if ($err != "") {
      $e->post_msx($err);
      
      return $e;
    }


    $this->validar_email_site();
    
    $m = null;
    if (($m_0 = $this->obxeto("erro_0")->valor()) != null) $m .= "<div>&bull;&nbsp;{$m_0}</div>";

    if ($m != null) {
      $e->post_msx($m);

      return $e;
    }


    try {
      $c = $this->cambio_obd();


      if (($err = XCCS::aceptar_ru_diferida($c, $this->id_site, $this->idioma->codigo) ) != null) {
        //~ $this->obxeto("erro_0")->post($err);

        $e->post_msx($err);

        return $e;
      }

      //* enviamos un mail ao admin do site informando do novo rexistro.
      $u = $c->usuario_obd();
      
      $cbd = new FS_cbd();

      $u_admin = $e->__site_obd($this->id_site, $cbd)->usuario_obd($cbd);


      $this->email_aviso_alta($u_admin, $u);



      //* preparamos a saida por pantalla
      $this->ptw = Refs::url(self::ptw_4);

      $this->obxeto("bAceptar")->title = null;

      $this->pon_obxeto(new Param("pemail", $this->obxeto("email")->valor()));
      
      $this->preparar_saida($e->ajax());
      
      $e->post_msx( Msx::$clogin[$this->idioma->codigo][700], "m" );
    }
    catch (Exception $exc) {
      $e->post_msx($exc->getMessage());
    }


    return $e;
  }

  protected function operacion_bCancelar(IEfspax_xestor $e) {
    return $e;
  }

  protected function operacion_cambio(IEfspax_xestor $e) {
    return $this->operacion_bCancelar($e);
  }

  protected function validar() {
    $this->validar_email();
    $this->validar_lic();

    if ($this->ru_diferido != 1) return null;

    if ($this->obxeto("mobil")->valor() == null)
      $this->obxeto("erro_mobil")->post( Msx::$clogin[$this->idioma->codigo][111] );
  }

  protected function validar_email() {
    $email = $this->obxeto("email")->valor();

    $erro = "";
    if (!Valida::email($email)) $erro = Msx::$clogin[$this->idioma->codigo][100];

    $this->obxeto("erro_email")->post($erro);
  }

  protected function validar_lic() {
    if ($this->obxeto("ch_lic")->valor()) return;

    $this->obxeto("erro_lic")->post(Msx::$clogin[$this->idioma->codigo][104]);
  }

  private function cambio_obd() {
    if ($this->cambio != null) return $this->cambio;
    
    //* para rexistro.

    $c = ($this->ru_diferido == 1)?new Cambio_altaweb_obd():new Cambio_invitaweb_obd();

    $c->atr("id_site")->valor = $this->id_site;
    $c->atr("email"  )->valor = $this->obxeto("email")->valor();

    if ($this->ru_diferido == 1) {
      $c->atr("nome"        )->valor = $this->obxeto("nome")->valor();
      $c->atr("razon_social")->valor = $this->obxeto("razon_social")->valor();
      $c->atr("mobil"       )->valor = $this->obxeto("mobil")->valor();
      $c->atr("baixa"       )->valor = 2; //* baixa por ru_diferido.
    }
    
    $c->atr("permisos")->valor = "pub";
    if (($p2 = $this->pai->permiso()) != null) $c->atr("permisos")->valor .= ", {$p2}"; 


    $cbd = new FS_cbd();

    $cbd->transaccion();

    if (!$c->insert($cbd)) {
      $cbd->rollback();

      return null;
    }

//~ echo "<pre>" . print_r($c->a_resumo(), 1) . "</pre>";

    $cbd->commit();

    $this->cambio = $c;
      

    return $c;
  }

  private function email_aviso_alta(FGS_usuario $u_admin, FGS_usuario $u_rexitro) {

    $corpo = "Alta de usuario pediente de validación.<br /><br />
              Email del usuario registrado: " . $u_rexitro->atr("email")->valor . "<br />";

//    $m = new XMail2(false);
    $m = new XMail3($this->id_site);

    list($x, $dominio) = explode("@", $u_admin->atr("login")->valor);

    (($_x = $m->_config()) != [])
    ? $m->pon_rmtnt($_x['email'])
    : $m->pon_rmtnt("altas@{$dominio}");

    $m->pon_direccion($u_admin->atr("login")->valor);
    $m->pon_asunto("Alta pendiente de validar");
    $m->pon_corpo($corpo);

    try {
      $m->enviar();
    }

    catch (Exception $e) {
      throw $e;
    }
  }

  private function validar_email_site(FS_cbd $cbd = null) {
    $u = Site_obd::u_login($this->id_site, $this->obxeto("email")->valor(), $cbd);
    
    
    if ( $u == null ) return;
    

    $erro = Msx::$clogin[$this->idioma->codigo][101];
    

    $this->obxeto("erro_0")->post($erro);
  }
}

