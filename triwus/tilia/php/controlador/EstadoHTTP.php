<?php

/** Clase que encapsula un estado dun aplicativo web. Máquina de Estados.
 *
 * @package tilia\controlador
 */

abstract class EstadoHTTP implements ITilia {

  protected $request = null;
  protected $nocache = false;

  private $pai       = null;
  private $anterior  = null;
  private $paxina    = null;
  private $ajax      = null;
  private $cookies   = null; //* para escritura de cookies (submit).
  private $mime      = null;


  protected function __construct(Paxina $paxina = null) {
    if ($paxina == null) $paxina = new Paxina("Tilia Framework");

    $this->paxina = $paxina;

    $this->inicia_obxetosHttp();
  }

  public function evento(EventoHTTP $ev = null) {
    return $this->paxina()->evento($ev);
  }

  public function operacion() { //* IMPLEMENTA ITilia
    if (($e = $this->paxina()->formulario()->operacion($this)) != null) return $e;

    if ($this->obxeto("location")->control_evento()) {
      $this->ajax()->pon_ok();

      return $e;
    }

    return $this;
  }

  public function pai(EstadoHTTP $pai = null) {
    if ($pai == null) return $this->pai;

    $this->pai = $pai;
  }

  public function anterior(EstadoHTTP $anterior = null) {
    if ($anterior == null) return $this->anterior;

    $this->anterior = $anterior;
  }

  public function location() {
    return explode("@", $this->obxeto("location")->valor());
  }

  public function obxeto($nome) {
    return $this->paxina()->obxeto($nome);
  }

  public function pon_obxeto(Escritor_html $o) {
    $this->paxina()->pon_obxeto($o);
  }

  final public function postHTTP($http_request, $http_files = null) { //* IMPLEMENTA ITilia
    $this->cookies = null;
    $this->mime    = null;

    $this->ajax()->limpar();

    if ($http_files != null) foreach($http_files as $k_file=>$a_file) $http_request[$k_file] = $a_file;

    //~ echo "<pre style='color: #ba0000;'><b>http_request</b>::" . print_r($http_request, true) . "</pre>";

    $this->request = $this->paxina()->post($http_request);
  }

  public function cambiar_idioma(Idioma $idioma = null, Panel $p = null) {
    $this->paxina()->cambiar_idioma($idioma, $p);
  }

  public function readonly($b = true) {
    $this->paxina()->readonly($b);
  }

  final public function cookies():?array {
    return $this->cookies;
  }

  final public function pon_cookie($nome, $value = "",$expires = 0, $path = "/", $domain = "", $secure = TRUE, $httponly = TRUE) {
    $this->cookies[$nome]['value'   ] = $value;
    $this->cookies[$nome]['expires' ] = $expires;
    $this->cookies[$nome]['path'    ] = $path;
    $this->cookies[$nome]['domain'  ] = $domain;
    $this->cookies[$nome]['secure'  ] = $secure;
    $this->cookies[$nome]['httponly'] = $httponly;
  }

  final public function sup_cookie($nome) {
    unset($this->cookies[$nome]); 
//~ setcookie($nome, '', 1, '/'); 
    $this->pon_cookie($nome, "", 1, "/");
  }

  final public function pon_mime($nome, $mime, $datos = null, $size = null, $titulo = null, $codif = "UTF-8") {
    if ($titulo == null) $titulo = basename($nome);

    $this->mime['nome' ] = $nome;
    $this->mime['mime' ] = $mime;
    $this->mime['datos'] = $datos;
    $this->mime['size' ] = $size;
    $this->mime['titu' ] = $titulo;
    $this->mime['codif'] = $codif;
  }

  final public function redirect($url) {
    $this->pon_mime($url, null, null, null);
  }

  final public function inicia_obxetosHttp(Panel $p = null) {
    $this->pon_obxeto(new Hidden("location"));

    $this->paxina()->declara_obxetos($p);
  }

  final public function colle_obxetosHttp(Panel $p = null) {
    if ($p == null) return $this->paxina()->formulario()->obxetos();

    return $p->declara_obxetos();
  }

  final public function panel($nome) {
    return $this->paxina()->formulario()->panel($nome);
  }

  public function ajax(Ajax $a = null) {
    if ($a != null) $this->ajax = $a;

    if ($this->ajax == null) $this->ajax = new AJAX($this->paxina()->head->charset);

    return $this->ajax;
  }

  public function paxina(Paxina $p = null) {
    if ($p != null) $this->paxina = $p;

    return $this->paxina;
  }

  final public function devolver() { //* IMPLEMENTA ITilia
    $this->request = null;

    if ($this->mime != null) return $this->devolver_MIME();

    $this->devolver_cookies();

    if ($this->evento()->ajax()) return $this->devolver_AJAX();

    return $this->devolver_SUBMIT();
  }

  private function devolver_SUBMIT() {
    if ($this->nocache) header("Cache-Control: no-cache, must-revalidate");


    return $this->paxina()->html();
  }

  private function devolver_AJAX() {
    $json = $this->ajax()->json();

    header("Content-Type: application/json");

    return $json;
  }

  private function devolver_MIME() {
    $fs = $this->mime;

    $this->mime = null;

    if ($fs['datos'] == null)
      header("Location: {$fs['nome']}");
    else {
      header("Pragma: no-cache");
      header("Content-type: {$fs['mime']}; charset={$fs['codif']}");
      header("Content-Type: application/force-download");
      header("Content-Type: application/download");
      header("Content-Description: File Transfer");
      header("Content-Disposition: attachment; filename=\"{$fs['titu']}\"");
      if ($fs['size'] !== null) header("Content-Length: {$fs['size']}");

      return $fs['datos'];
    }
  }

  private function devolver_cookies() {
    if ($this->cookies == null) return;


    foreach($this->cookies as $k => $v) {
      $_cookie_options = array(
                  'expires'  => $v['expires'],
                  'domain'   => $v['domain'],
                  'path'     => $v['path'],
                  'secure'   => $v['secure'],     // or false
                  'httponly' => $v['httponly'],    // or false
                  'samesite' => "Lax", // None || Lax  || Strict
                  );

      setcookie($k, $v['value'], $_cookie_options);
    }
    
    $this->cookies = null;
  }
}
