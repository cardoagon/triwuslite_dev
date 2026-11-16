<?php

final class XCCS extends Componente {
/*
  public static function aceptar_cemail($email, Cambio_email_obd $c, $id_site = null) {
    if (!Valida::email($email)) return Erro::email;

    $c->atr("email")->valor = $email; //* se ven de CCS_editor, esta operación non é necesaria.

    if (!self::cambio_insert($c)) return Erro::ccs_lconfirm;

    if ($id_site == null)
      self::__mail_cemail($c, $email)->enviar();
    else
      self::__mail_cemailweb($c, $email)->enviar();


    return null; //* tudo bem !!
  }
*/
  public static function aceptar_factor2(Cambio_factor2_obd $c, FGS_usuario $u):?string {
    if ($c == null) return "Usuario no registrado";

    if (!self::cambio_insert($c)) return Erro::ccs_lconfirm;

    self::__mail_factor2($c, $u->atr("email")->valor)->enviar();


    return null;  //* tudo bem !!
  }

  public static function aceptar_ck($email, $k, Cambio_k_obd $c = null, $id_site = null) {
      /*
    if ($c == null) {
      $href = Refs::url_home();
      $ptw  = Refs::url("ptw/ccs/email/confirma_k_2.html");

      $asunto = "usuario no registrado";

      if ($id_site != null) $asunto = "{$asunto} ". self::__nome_site($id_site);

      self::__mail_confirm(self::__lconfirm($href), "usuario no registrado", null, $href, $email,  $ptw)->enviar();

      return;
    }
    */

    if ($c == null) return "Usuario no registrado";

    if (!self::cambio_insert($c)) return Erro::ccs_lconfirm;

    if ($id_site == null)
      self::__mail_ck($c, $email)->enviar();
    else
      self::__mail_ckweb($c, $email)->enviar();


    return null;  //* tudo bem !!
  }

  public static function aceptar_alta(Cambio_altaweb_obd $c, $id_site) {
    $email_r  = $c->atr("email")->valor;

    if (self::control_mail_duplicado($email_r, $id_site)) {
      self::__mail_altaweb_dupli($c)->enviar();

      return "Error, email duplicado.";
    }

    if (!self::cambio_insert($c)) return Erro::ccs_lconfirm;


    if ($id_site == null)
      self::__mail_altaweb($c)->enviar();


    return null; //* tudo bem !!
  }

  public static function aceptar_ru_diferida(Cambio_altaweb_obd $c, $id_site, $id_idioma = "es") { //sólo sites triwus
    $email_r  = $c->atr("email")->valor;

    if (!self::cambio_click($c)) return Erro::ccs_ru_diferido;


    $m = self::__mail_ru_diferida($c, $id_idioma);

    $m->enviar();
    //~ echo $m->html();



    return null; //* tudo bem !!
  }

  public static function inicia_ck_obd($email, $k, $id_site = null) {
    $u = ($id_site == null)?new FGS_usuario():new UsuarioWeb_obd();

    $u->select_email(new FS_cbd(), $email, $id_site);

    if (!$u->validado()) return null;

    $ca = $u->icambio_obd();

    $ca->atr("k" )->valor = $k;
    $ca->atr("k2")->valor = FGS_usuario::calcula_k2($k);
    $ca->atr("k3")->valor = $k;
    $ca->atr("k4")->valor = $ca->atr("k2")->valor;

    return $ca;
  }

  public static function inicia_factor2_obd() {
    return new Cambio_factor2_obd();
  }

  public static function control_mail_duplicado($email, $id_site = null) {
    $u = ($id_site == null)?new FGS_usuario():new UsuarioWeb_obd();

    $u->select_email(new FS_cbd(), $email, $id_site);

    return $u->validado();
  }

  private static function cambio_insert(Cambio_obd $c) {
    if ($c == null) return false;

    $cbd = new FS_cbd();

    $cbd->transaccion(true);

    if (!$c->insert($cbd)) {
      $cbd->rollback();

      return false;
    }

    $cbd->commit();

    return true;
  }

  private static function cambio_click(Cambio_obd $c) {
    if ($c == null) return false;

    $cbd = new FS_cbd();

    $cbd->transaccion(true);

    if (!$c->operacion_click($cbd)) {
      $cbd->rollback();

      return false;
    }

    $cbd->commit();

    return true;
  }

  public static function __mail_confirma_opweb($email, $op, $id_site, $ptw = null) {
    $href  = Efs::url_paxina($id_site);
    $legal = self::__legal($id_site);

        if ($ptw == null)   $ptw = Refs::url("ptw/ccs/email/confirma_operacionweb.html");
    elseif (!is_file($ptw)) $ptw = Refs::url("ptw/ccs/email/confirma_operacionweb.html");

    $txt = LectorPlantilla::plantilla( $ptw );

    $txt = str_replace("[op]"      , $op   , $txt);
    $txt = str_replace("[legal]"   , $legal, $txt);
    $txt = str_replace("[url_home]", $href , $txt);


    $m = self::__smtp($id_site);

    $m->pon_direccion($email);
    $m->pon_asunto("{$op} " . self::__nome_site($id_site));
    $m->pon_corpo($txt);

    try {
      $m->enviar();
    }
    catch (Exception $e) {
      throw $e;
    }
  }

  private static function __mail(int $id_site, $to, $txt, $asunto, $de = null) {
    $m = self::__smtp($id_site);

    $m->pon_direccion($to);
    $m->pon_asunto($asunto);
    $m->pon_corpo($txt);

//~ echo $m->html();

    return $m;
  }

  private static function __mail_confirm(int $id_site, Link $lconfirm, $asunto, $de, $url_home, $to, $ptw, $ccs_op = null, $k = null, $k_confirm = "", $legal = null, $id_idioma = "es") {
    $txt = LectorPlantilla::plantilla( $ptw, Idioma::factory($id_idioma) );

    $txt = str_replace("[u]", $to  , $txt);
    $txt = str_replace("[k]", ""  , $txt);
    $txt = str_replace("[k_confirm]", $k_confirm, $txt);
    //~ $txt = str_replace("[link_confirm]", $lconfirm->html(), $txt);
    $txt = str_replace("[ccs_op]"  , $ccs_op, $txt);
    $txt = str_replace("[url_home]", "<a href='{$url_home}' target='_blank'>{$url_home}</a>", $txt);
    $txt = str_replace("[legal]"   , $legal, $txt);

    return self::__mail($id_site, $to, $txt, $asunto, $de);
  }

  public static function __mail_factor2(Cambio_obd $c, $email) { //* asunto OK
    $id_site   = $c->atr("id_site")->valor;

    $link      = self::__lconfirm(Refs::url_home() . "/pcontrol.php", $c);
    $ptw       = Refs::url("ptw/ccs/email/confirma_factor2.html");
    $ccs_op    = "Triwus. Control de doble factor.";
    $k         = $c->atr("kf2")->valor;
    $k_confirm = $c->atr("id_cambio")->valor;

    return self::__mail_confirm(null, $link, "Control de doble factor Triwus", "info@triwus.com", Refs::url_home(), $email, $ptw, $ccs_op, $k, $k_confirm);
  }

  public static function __mail_ck(Cambio_obd $c, $email) { //* asunto OK
    $id_site   = $c->atr("id_site")->valor;

    $link      = self::__lconfirm(Refs::url_home() . "/pcontrol.php", $c);
    $ptw       = Refs::url("ptw/ccs/email/confirma_k.html");
    $ccs_op    = "cambio de contraseña";
    $k         = $c->atr("k")->valor;
    $k_confirm = $c->atr("id_cambio")->valor;

    return self::__mail_confirm($id_site, $link, "cambio de contraseña Triwus", "info@triwus.com", Refs::url_home(), $email, $ptw, $ccs_op, $k, $k_confirm);
  }

  public static function __mail_ckweb(Cambio_obd $c, $email) { //* asunto OK
    $id_site   = $c->atr("id_site")->valor;

    $href      = Efs::url_paxina($id_site);
    $de        = self::__de($id_site);
    $link      = self::__lconfirm($href . "/index.php", $c, "kw");
    $ptw       = Refs::url("ptw/ccs/email/confirma_kweb.html");
    $ccs_op    = "cambio de contraseña";
    $k         = $c->atr("k")->valor;
    $k_confirm = $c->atr("id_cambio")->valor;
    $legal     = self::__legal($id_site);
    $asunto    = "información cambio contraseña " . self::__nome_site($id_site);

    return self::__mail_confirm($id_site, $link, $asunto, $de, $href, $email, $ptw, $ccs_op, $k, $k_confirm, $legal);
  }

  private static function __mail_altaweb(Cambio_altaweb_obd $c, $ru_diferido = false) { //* asunto OK
    $id_site   = $c->atr("id_site")->valor;

    $ptw       = Refs::url("ptw/ccs/email/confirma_altaweb.html");

    $de        = self::__de($id_site);
    $href      = Efs::url_paxina($id_site);
    $link      = new Link("x");  //* deshabilitado
    $email     = $c->atr("email")->valor;
    $k         = $c->atr("k")->valor;
    $k_confirm = $c->atr("id_cambio")->valor;
    $ccs_op    = "registro";
    $legal     = self::__legal($id_site);
    $asunto    = "información registro " . self::__nome_site($id_site);

    return self::__mail_confirm($id_site, $link, $asunto, $de, $href, $email, $ptw, $ccs_op, $k, $k_confirm, $legal, $titulo);
  }

  private static function __mail_ru_diferida(Cambio_altaweb_obd $c, $id_idioma = "es") { //* asunto OK
    $id_site   = $c->atr("id_site")->valor;

    $ptw       = Refs::url("ptw/ccs/email/confirma_rudiferido.html");

    $de        = self::__de($id_site);
    $href      = Efs::url_paxina($id_site);
    $link      = new Link("x");  //* deshabilitado
    $email     = $c->atr("email")->valor;
    $k         = null;
    $k_confirm = null;
    $ccs_op    = Msx::$clogin[$id_idioma][5];
    $legal     = self::__legal($id_site);
    $asunto    = Msx::$clogin[$id_idioma][5] . " " . self::__nome_site($id_site);

    return self::__mail_confirm($id_site, $link, $asunto, $de, $href, $email, $ptw, $ccs_op, $k, $k_confirm, $legal, $id_idioma);
  }

  public static function __mail_altaweb_dupli(Cambio_altaweb_obd $c) { //* asunto OK
    $id_site = $c->atr("id_site")->valor;
    $de      = self::__de($id_site);
    $site    = Efs::url_paxina($id_site);
    $email   = $c->atr("email")->valor;
    $href    = "{$site}/index.php";
    $asunto  = "info: {$site}";

    $txt = LectorPlantilla::plantilla( Refs::url("ptw/ccs/email/novosite_dupli.html") );
    $txt = str_replace("[ccs_op]", "registro", $txt);
    $txt = str_replace("[email]", $email, $txt);
    $txt = str_replace("[url_home]", "<a href='{$href}' target='_blank'>{$href}</a>", $txt);

    return self::__mail($email, $txt, $asunto, $de);
  }

  private static function __lconfirm($href, Cambio_obd $c = null, $k = "k") {
    if ($c != null) $href .= "?{$k}=" . $c->atr("id_cambio")->valor;

    return new Link("fgslc", $href, "_blank", $href);
  }

  public static function __nome_site($id_site) {
    return Site_metatags_obd::__titulo($id_site);
  }

  public static function __legal($id_site) {
    $src = Efs::url_legais($id_site) . "/lopd_email.html";

    if (!is_file($src)) return "";

    return LectorPlantilla::plantilla($src);
  }

  private static function __smtp(?int $id_site):Mail3 {
    $cbd = new FS_cbd();
    
    //* Probamos si el site tiene configurado un cliente SMTP.
    if ($id_site != null) {
      $m = new XMail3($id_site); 

      if (($_x = $m->_config()) != []) {
        $m->pon_rmtnt($_x['email']);
        
        return $m;
      }
    }
    
    //* Usamos un email de triwus.
    $_c = [ "url"   => "triwus.com",
            "email" => "log@triwus.com",
            "k"     => "",
            "seg"   => "ssl",
            "porto" => 465,
            "nome"  => "Triwus. Non reponder."
          ]; 
    
    $m = new Mail3($_c, "es");
    
    $m->pon_rmtnt($_c['email']);

    return $m;
  }


  public static function __de($id_site) {
    $cbd = new FS_cbd();

    $u_admin = Site_obd::inicia($cbd, $id_site)->usuario_obd($cbd);

    list($x, $dominio) = explode("@", $u_admin->atr("email")->valor);

    return "no-reply@{$dominio}";
  }
}
