<?php

final class XMail2 extends Mail2 {
  const de       = "no-reply@triwus.com";
  const respaldo = "log@triwus.com";

  private static $a_class = array("class='imx_paxlogo'",
                                  "class='texto2'",
                                  "class='texto3'",
                                  "class='texto4'",
                                  "class='texto5'");

  private static $a_style = array("style='margin: 0; padding: 0; height: 61px; width: 61px; border: 2px solid transparent; border-radius: 4px; -webkit-border-radius: 4px; -khtml-border-radius: 4px;'",
                                   "style='font-size: 12px; color: #555555;'",
                                   "style='font-size: 11px; color: #333333;'",
                                   "style='font-size: 11px; font-style: italic; color: #444444;'",
                                   "style='font-size: 11px; text-transform: uppercase; color: #404040;'");

  public $respaldar = false;

  public function __construct($respaldar = false, $html = true) {
    parent::__construct($html);

    $this->isHTML(true);

    $this->respaldar = $respaldar;
  }

  public function enviar($verbose = false){
    $this->Timeout = 30;

    //~ $this->prepara_enviar_1();

      if ($this->SMTPAuth) $this->prepara_enviar_1();
    else                   $this->prepara_enviar_2();

    if ($this->respaldar) $this->pon_cco(self::respaldo);


    parent::enviar($verbose);
  }

  private function prepara_enviar_1(){
    $this->Mailer = "smtp";
    $this->IsSMTP();

    //~ $host = explode("@", $this->Username);

    $this->Host = "triwus.com";
    $this->Port = 25;
  }

  private function prepara_enviar_2(){
    $this->IsMail();                 // Usamos el metodo mail de la clase PHPMailer SMTPAuth false

    $this->Host = "triwus.com";
  }

  private static function __parsehtml($txt) {
    $txt = self::__refs($txt);

    //* estilos
    $txt = str_replace(self::$a_class, self::$a_style, $txt);

    return $txt;
  }

  public static function __link($href, $id = "lmail") {
    return new Link($id, $href, "_blank", $href);
  }
}
