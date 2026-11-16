<?php

class Paxinfo_libre extends Paxinfo {
  public function __construct(Paxina_obd $p) {
    parent::__construct($p);
  }

  public function titulo_ficha() {
    return "Propiedades&nbsp;de&nbsp;la&nbsp;p&aacute;gina";
  }

  public function paxina_obd(FS_cbd $cbd = null) {
    return $this->paxina_obd_base("libre", $cbd);
  }
}

//*******************************************

class Paxinfo_reservado extends Paxinfo {
  public function __construct(Paxina_reservada_obd $p) {
    parent::__construct($p);
  }

  public function readonly($b = false) {
    parent::readonly($b);

    $this->obxeto("nome" )->readonly = true;
    $this->obxeto("grupo")->readonly = true;

    $this->obxeto("puente" )->readonly = true;
    $this->obxeto("estado" )->readonly = true;
    $this->obxeto("visible")->readonly = true;
  }

  public function titulo_ficha() {
    return "Propiedades&nbsp;de&nbsp;la&nbsp;p&aacute;gina reservada";
  }

  public function paxina_obd(FS_cbd $cbd = null) {
    return $this->paxina_obd_base(null, $cbd);
  }
}

//*******************************************

class Paxinfo_legal extends Paxinfo {
  public function __construct(Paxina_legal_obd $p) {
    parent::__construct($p);
  }

  public function readonly($b = false) {
    parent::readonly($b);

    $this->obxeto("nome"   )->readonly = true;
  }

  public function titulo_ficha() {
    return "Propiedades&nbsp;de&nbsp;la&nbsp;p&aacute;gina legal";
  }

  public function paxina_obd(FS_cbd $cbd = null) {
    return $this->paxina_obd_base("libre", $cbd);
  }
}

