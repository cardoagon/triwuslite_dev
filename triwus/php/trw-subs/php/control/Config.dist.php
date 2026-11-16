<?php

final class Config {
  /*** local ***/
  const trw_site  = 18;

  const titulo    = "Triwus - Subs";
  const favicon   = "imx/favicon.png";

  const logo_i    = "imx/logo.svg";
  const logo_t    = "Descrición do logo, (html title)";

  const url_login = "http://127.0.0.1/triwus/sites/test/login.php?redirect=subs.admin/admin/index.php";

  const bd_h      = "localhost"; //* mariadb host.
  const bd_b      = ""; //* mariadb db.
  const bd_u      = ""; //* mariadb user.
  const bd_k      = ""; //* mariadb password.


  /*** global ***/
  const msx_login = "Error de credenciais";


  private function __construct() {}

  public static function trw_u(FGS_usuario $u = null) {
    if (($s = self::trw_s()) == null) return null;

    if ($u == null) {
      if (($u = $s->usuario()) == null) $u = new UsuarioWeb_obd();

      return $u;
    }

    $s->usuario($u);
  }

  public static function trw_admin(FS_cbd $cbd = null) {
    if ($cbd == null) $cbd = new FS_cbd();


    $s = Site_obd::inicia( $cbd, self::trw_site );
    
    $u = $s->usuario_obd($cbd);


    if ( $u->baleiro() ) return null;


    return $u;
  }

  public static function trw_s(EstadoHTTP $e = null) {
    $s = "fs_" . self::trw_site;

    if ($e != null) $_SESSION[$s] = $e;

    if (!isset($_SESSION[$s])) return null;

    return $_SESSION[$s];
  }

  public static function trw_msx(EstadoHTTP $e, $m, $t = "e") {
    $e->post_msx($m, $t);

    $e->msx_persiste = true;
  }

  public static function mkdir($src):void {
    $src_priv = Config::priv . "/";


    $_src_0   = explode($src_priv, $src);
    $_src_1   = explode("/", $_src_0[1]);

    $src2 = "{$_src_0[0]}{$src_priv}";
    foreach($_src_1 as $v) {
      if (trim($v) == null) continue;

      $src2 .= "{$v}/";

      if (is_dir($src2)) continue;

      mkdir($src2);
    }
  }

  public static function strval(?array $_erro):?string {
    if ($_erro == null) return null;
    
    $erro = null;
    foreach ($_erro as $k=>$v) {
      if ($erro != null) $erro .= "\n<br>";
      
      $erro .= $v;
    }


    return $erro;
  }

  public static function __colle_ticket() {
    $t = Elmt_contacto_obd::__colle_ticket(self::trw_site, new FS_cbd());

    return str_replace("/", "-", $t);
  }
 
  private static function tilia_idioma() {
    if (($s = self::trw_s()) == null) return null;

    if (($id_idioma = $s->id_idioma()) == null) $id_idioma = "gl";

    return Idioma::factory( $id_idioma );
  }
}
