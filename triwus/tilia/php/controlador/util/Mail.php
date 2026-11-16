<?php

/** Permite enviar correos electrónicos.
 *
 * @package tilia\controlador\util
 */



class Mail {
  private $html    = true;

  private $cc      = null;
  private $cco     = null;

  private $adxunto = null;


  public $charset  = "iso-8859-1";

  public $de       = null;
  public $para     = null;
  public $asunto   = null;

  public $texto    = null;


  public function __construct($html = true) {
    $this->html = $html;
  }

  public function reset() {
    $this->cc       = null;
    $this->cco      = null;

    $this->adxunto  = null;

    $this->de       = null;
    $this->para     = null;
    $this->asunto   = null;

    $this->texto    = null;
  }

  public function pon_adxunto($src) {
    $this->adxunto[$src] = 1;
  }

  public function pon_adxunto_2($nome, $datos, $k = null) {
    $this->adxunto[$k] = array($nome, chunk_split(base64_encode($datos)));
  }

  public function pon_cc($cc) {
    $this->cc[strtolower($cc)] = 1;
  }

  public function pon_cco($cco) {
    $this->cco[strtolower($cco)] = 1;
  }

  public function enviar($verbose = false) {
    $boundary = md5(uniqid(time()));

    $h  = $this->__header_0($boundary);
    $h .= $this->__header_t($boundary);
    $h .= $this->__header_a($boundary);

    if ($verbose) echo "<pre>" . print_r($this, true) . print_r($h, true) . "</pre>";


    return mail($this->para, $this->asunto, "", $h);
  }

  public static function check($email) {
    // Primero, checamos que solo haya un símbolo @, y que los largos sean correctos

    if (!preg_match("{^[^@]{1,64}@[^@]{1,255}$}", $email)) {
      // correo inválido por número incorrecto de caracteres en una parte, o número incorrecto de símbolos @
      return false;
    }

    // se divide en partes para hacerlo más sencillo
    $email_array = explode("@", $email);
    $local_array = explode(".", $email_array[0]);
    for ($i = 0; $i < sizeof($local_array); $i++) {
      if (!preg_match("{^(([A-Za-z0-9!#$%&'*+/=?^_`{|}~-][A-Za-z0-9!#$%&'*+/=?^_`{|}~\.-]{0,63})|(\"[^(\\|\")]{0,62}\"))$}", $local_array[$i])) {
        return false;
      }
    }

    // se revisa si el dominio es una IP. Si no, debe ser un nombre de dominio válido
    if (!preg_match("{^\[?[0-9\.]+\]?$}", $email_array[1])) {
       $domain_array = explode(".", $email_array[1]);
       if (sizeof($domain_array) < 2) {
          return false; // No son suficientes partes o secciones para se un dominio
       }
       for ($i = 0; $i < sizeof($domain_array); $i++) {
          if (!preg_match("{^(([A-Za-z0-9][A-Za-z0-9-]{0,61}[A-Za-z0-9])|([A-Za-z0-9]+))$}", $domain_array[$i])) {
             return false;
          }
       }
    }

    return true;
  }

  private function __header_0($boundary) {
    $h  = "";

    if ($this->de != null) $h .= "From: {$this->de}\r\n";

    if ($this->cc != null) {
      $hi = "";
      foreach($this->cc as $cc=>$x) $hi .= ($hi == null)?"Cc: {$cc}":", {$cc}";

      $h .= "{$hi}\r\n";
    }

    if ($this->cco != null) {
      $hi = null;
      foreach($this->cco as $cco=>$x) $hi .= ($hi == null)?"Bcc: {$cco}":", {$cco}";

      $h .= "{$hi}\r\n";
    }

    $h .= "MIME-Version: 1.0\r\n";
    $h .= "Content-Type: multipart/mixed; boundary=\"{$boundary}\"\r\n\r\n";
    $h .= "This is a multi-part message in MIME format.\r\n";
    $h .= "--{$boundary}\r\n";


    return $h;
  }

  private function __header_t($boundary) {
    $mime = ($this->html)?"html":"plain";

    $h .= "Content-type: text/{$mime}; charset={$this->charset}\r\n";
    $h .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
    $h .= "{$this->texto}\r\n\r\n";
    $h .= "--{$boundary}\r\n";


    return $h;
  }

  private function __header_a($boundary) {
    if (count($this->adxunto) == 0) return "";

    $h = "";
    foreach ($this->adxunto as $src=>$a) {
      if (is_array($a)) {
        $n = $a[0];
        $d = $a[1];
      }
      elseif (is_file($src)) {
        $n = basename($src);
        $d = chunk_split( base64_encode(file_get_contents($src)) );
      }
      else
        continue;

      $h .= "Content-Type: application/octet-stream; name=\"{$n}\"\r\n"; // use different content types here
      $h .= "Content-Transfer-Encoding: base64\r\n";
      $h .= "Content-Disposition: attachment; filename=\"{$n}\"\r\n\r\n";
      $h .= "{$d}\r\n\r\n";
      $h .= "--{$boundary}\r\n";
    }


    return $h;
  }
}

