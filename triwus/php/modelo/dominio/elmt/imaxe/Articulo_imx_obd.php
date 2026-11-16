<?php

class Articulo_imaxe_obd extends Imaxe_obd {
  const tipo_bd = "IMXARTI";

  public function __construct($url = null) {
    parent::__construct($url);
  }

  protected function tipo_bd() {
    return self::tipo_bd;
  }
}
