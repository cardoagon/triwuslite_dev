<?php

class Ico_obd extends Imaxe_obd {
  const tipo_bd = "ICO";

  public function __construct($url = null) {
    parent::__construct($url);

    $this->atr("nome")->valor = "ico";
  }

  //~ public function url($mini = false, FS_cbd $cbd = null) {
    //~ return parent::url(17);
  //~ }

  public static function url_nula() {
    return Refs::url(Ico::bico);
  }

  protected function tipo_bd() {
    return self::tipo_bd;
  }
}


