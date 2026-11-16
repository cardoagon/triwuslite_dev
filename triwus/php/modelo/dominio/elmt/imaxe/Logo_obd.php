<?php


class Logo_obd extends Imaxe_obd {
  const tipo_bd = "LOGO";

  public function __construct($url = null) {
    parent::__construct($url);

    $this->atr("nome")->valor = "logo";
  }

  protected function tipo_bd() {
    return self::tipo_bd;
  }
}
