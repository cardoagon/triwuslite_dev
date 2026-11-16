<?php

/** Cliente SMTP. Permite enviar correos electrónicos.
 *
 * @package tilia\controlador\util
 */


//Import PHPMailer classes into the global namespace
//These must be at the top of your script, not inside a function

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

//Load Composer's autoloader

//require 'vendor/autoload.php';


require "src/PHPMailer.php";
require "src/SMTP.php";
require "src/Exception.php";



class Mail3 extends PHPMailer {
  private ?string $nome    = null;
  private ?string $l       = null;
  private array   $_config = [];

  public function __construct(array $_config = [], string $l = null) {
    parent::__construct(true);  //passing `true` enables exceptions
    
    $this->_config = $_config;
    $this->l       = $l;

    $this->reset();
  }

  public function _config():array {
    return $this->_config;
  }

  public function reset(int $debug = null):void {
    if ($debug != null) $this->SMTPDebug = $debug; //Enable verbose debug output
    
    $this->isHTML(true);

    $this->CharSet  = 'UTF-8';

    /* 
     //To load the spanish version
     $mail->setLanguage('es', '/optional/path/to/language/directory/');  */
    $this->setLanguage($this->l);


    $this->Priority = null;

    $this->clearAddresses    ();
    $this->clearCCs          ();
    $this->clearBCCs         ();
    $this->clearReplyTos     ();
    $this->clearAllRecipients();
    $this->clearAttachments  ();

    
  // si non hai configuración SMTP en site_config
    if ($this->_config == []) {
      $this->isMail();

      $this->Host = "triwus.com";
      
      return;
    }
    
    $this->isSMTP();                             //Send using SMTP
   
    $this->Host     = $this->_config["url"  ];   //Set the SMTP server to send through
    $this->SMTPAuth = true;                      //Enable SMTP authentication
    $this->Username = $this->_config["email"];   //SMTP username
    $this->Password = $this->_config["k"    ];   //SMTP password
    $this->Port     = $this->_config["porto"];   //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`
  
        if ($this->_config["seg"] == "ssl") $this->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;    //Enable implicit TLS encryption
    elseif ($this->_config["seg"] == "tls") $this->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; //* tls
    else                                    $this->SMTPSecure = null;                           //* non
    
  }

  public function pon_direccion($addr, $limpar = false) {
    if ($limpar) $this->clearAddresses();

    $this->AddAddress( strtolower($addr) );
  }

  public function pon_cc($cc, $limpar = false) {
    if ($limpar) $this->clearCCs();

    $this->AddCC(strtolower($cc));
  }

  public function pon_cco($cco, $limpar = false) {
    if ($limpar) $this->clearBCCs();

    $this->AddBCC(strtolower($cco));
  }

  public function pon_rmtnt($rmtnt) {
    $rmtnt = strtolower($rmtnt);
    
    $n = null;
    if ( isset($this->_config["nome"]) ) $n = $this->_config["nome"];

    $this->SetFrom($rmtnt, $n);
  }

  public function pon_asunto($subj) {
    $this->Subject = $subj;
  }

  public function pon_corpo($body) {
    $this->Body = $body;
  }

  public function pon_prioridade($p = null) {  // Se o email é urxente, pódese indicar.
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
      throw $e;
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
}
