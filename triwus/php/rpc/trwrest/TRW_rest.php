<?php

abstract class TRW_rest  {
  protected $s;
  protected $_r;
  
  public function __construct($id_site, $_request) {
    $this->s  = $id_site;
    $this->_r = $_request;

    $this->anotar( print_r($_REQUEST, 1), "INFO" );
  }
  
  abstract protected function _get();    //* RETURN array(int, String)
  abstract protected function _delete(); //* RETURN array(int, String)
  abstract protected function _put();    //* RETURN array(int, String)
  abstract protected function _post();   //* RETURN array(int, String)
  
  public function exec() {
    if (!$this->login()) {
      header("WWW-Authenticate: Basic realm=\"" . $this->dominio() ."\"");
      header("HTTP/1.0 401 Unauthorized");

      echo "HTTP/1.0 401 Unauthorized";

      return;
    }

        if ($this->_r["op"] == "get"   ) list($c, $r) = $this->_get();
    elseif ($this->_r["op"] == "post"  ) list($c, $r) = $this->_post();
    elseif ($this->_r["op"] == "put"   ) list($c, $r) = $this->_put();
    elseif ($this->_r["op"] == "delete") list($c, $r) = $this->_delete();
    else                                 list($c, $r) = array(405, "[]");
          
    header( $this->header_msx($c) );
    header("Content-Type: application/json");
    
    return $r;
  }

  public function log(string $m, string $t = "INFO"):void {
    $this->anotar($m, $t);
  }

  protected function sql_limit() {
    if (!isset($this->_r["limit"])) return "";

    return "limit {$this->_r["limit"]}";
  }
  
  protected function login() {
    if (!isset($_SERVER['PHP_AUTH_USER'])) return false;

    list($api, $u, $k) = self::_credenciais($this->s);

    if ($api != 1) return false;

    if ($_SERVER['PHP_AUTH_USER'] != $u) return false;
    if ($_SERVER['PHP_AUTH_PW'  ] != $k) return false;


    return true;
  }
  
  private function header_msx($codigo) {
    switch ($codigo) {
      case 200: return "HTTP/1.1 200 ok";
      case 201: return "HTTP/1.1 201 Created";
      case 400: return "HTTP/1.1 400";
      case 401: return "HTTP/1.1 401";
      case 404: return "HTTP/1.1 404 Not Found";
      case 405: return "HTTP/1.1 405 Method not allowed";
      case 409: return "HTTP/1.1 409";
      case 500: return "HTTP/1.1 500 Internal Server Error";
    }
  }
  
  private function dominio() {
    return TrwlConfig::dominio;
    /*
    $cbd = new FS_cbd();

    $r = $cbd->consulta("select dominio from site_plesk where id_site = {$this->s}");
    
    if (!$_r = $r->next()) return "triwus.com";


    return $_r["dominio"];
    */
  }

  private function anotar(string $m, string $t):void {
    $_php = pathinfo( $_SERVER["PHP_SELF"] );

    $id_log = "{$_php["filename"]}.log";

    file_put_contents($id_log, date("Y-m-d H:i:s") . ": {$t}: {$m}\r\n",  FILE_APPEND | LOCK_EX);
  }


  private static function _credenciais($id_site, FS_cbd $cbd = null) {
    if ($cbd == null) $cbd = new FS_cbd();

    $sc = Site_config_obd::inicia($cbd, $id_site);

    return [$sc->atr("api")->valor, $sc->atr("fs_u")->valor, $sc->atr("fs_k")->valor];
  }
}
