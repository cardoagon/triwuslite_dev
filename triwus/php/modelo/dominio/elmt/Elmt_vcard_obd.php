<?php

final class Elmt_vcard_obd extends Imaxe_obd {
  const tipo_bd = "VCARD";

  public function __construct() {
    parent::__construct();

    $this->atr("visible")->valor = "0";
    $this->atr("pais"   )->valor = "es";
  }

  public function mapa_bd() {
    return new Elmt_vcard_mbd();
  }

  protected function tipo_bd() {
    return self::tipo_bd;
  }
}


//--------------------------------------------------------


final class Elmt_vcard_mbd extends Elmt_mbd {
  public function __construct() {
    parent::__construct();

    $t = new Taboa_dbd("elmt_vcard");

    $t->pon_campo("id_elmt", new Numero(), true);
    $t->pon_campo("visible", new Numero());
    $t->pon_campo("email");
    $t->pon_campo("tlfn");
    $t->pon_campo("localidade");
    $t->pon_campo("dirpostal");
    $t->pon_campo("cp");
    $t->pon_campo("pais");
    $t->pon_campo("ref_site");
    $t->pon_campo("url"     );
    $t->pon_campo("www"     );

    $this->relacion_fk($t, array("id_elmt"), array("id_elmt"));
  }
}

