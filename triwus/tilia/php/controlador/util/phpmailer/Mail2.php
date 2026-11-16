<?php

/** Cliente SMTP. Permite enviar correos electrónicos.
 *
 * @package tilia\controlador\util
 */

require 'PHPMailerAutoload.php';

class Mail2 extends PHPMailer {
  protected $m = null;

  public function __construct($html = true){
    parent::__construct(false);

    $this->reset($html);
  }

  public function reset($html = true) {
    $this->CharSet  = 'UTF-8';
    $this->isHTML(true);
    $this->Priority = null;

    $this->clearAddresses();
    $this->clearCCs();
    $this->clearBCCs();
    $this->clearReplyTos();
    $this->clearAllRecipients();
    $this->clearAttachments();
  }

  public function pon_direccion($addr, $limpar = false) {
    if ($limpar) $this->clearAddresses();

    $this->AddAddress( strtolower($addr) );
  }

  public function pon_cc($cc, $limpar = false){
    if ($limpar) $this->clearCCs();

    $this->AddCC(strtolower($cc));
  }

  public function pon_cco($cco, $limpar = false){
    if ($limpar) $this->clearBCCs();

    $this->AddBCC(strtolower($cco));
  }

  public function pon_rmtnt($rmtnt, $pass = null, $name = null){
    $rmtnt = strtolower($rmtnt);

    if ($pass != null) {
      $this->SMTPAuth = true;
      $this->Username = $rmtnt;
      $this->Password = $pass;
    }

    if (is_null($name)) $name = $rmtnt;


    $this->AddReplyTo($rmtnt, $name);


    $this->SetFrom($rmtnt, $name);
  }

  public function pon_asunto($subj){
    $this->Subject = $subj;
  }

  public function pon_corpo($body){
    $this->Body = $body;
  }

  public function pon_prioridade($p = null){  // Se o email é urxente, pódese indicar.
    $this->Priority = $p;
  }

  public function pon_adxunto($path, $name = '', $encoding = 'base64', $type = '') {
    $this->AddAttachment($path, $name, $encoding, $type);
  }

  public function pon_adxunto_s($s, $name = '', $encoding = 'base64', $type = '') {
    $this->addStringAttachment($s, $name, $encoding, $type);
  }

  public function enviar($verbose = false) {
    if ($verbose) echo "<pre>" . print_r($this, true) . "</pre>";

try {
    $this->send();
}
catch (Exception $e) {
  echo $e->errorMessage();
  echo $e->getMessage();
}
  }

  public function html():string {
    //* vista html do obxeto (usar so para validación).
    $s = "
    From: <b>{$this->From}</b><br />
    To: <b>" . print_r($this->to, true) . "</b><br />
    CC: <b>" . print_r($this->cc, true) . "</b><br />
    BCC: <b>" . print_r($this->bcc, true) . "</b><br />
    Subject: <b>{$this->Subject}</b><br />
    Body:<br/> {$this->Body}<br />
    ";


    return $s;
  }


/*
   Function: check

    Realiza unha validacion dunha direccion de correo electronico

    Visitar: <http://www.linuxtotal.com.mx/index.php?cont=info_otros_002 at http://www.linuxtotal.com.mx/index.php?cont=info_otros_002>


   Parameters:

     o string $email

   Return:

     boolean
*/
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

}
