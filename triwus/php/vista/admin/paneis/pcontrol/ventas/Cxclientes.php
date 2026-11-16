<?php

final class Cxclientes extends CURexistrados implements ICPC {
  const ptw_1 = "ptw/paneis/pcontrol/ventas/cxclientes_1.html";

  public function __construct($id_site) {
    parent::__construct($id_site, "cxclientes");

    $this->obxeto("cxclil")->__where(true);

    $this->ptw = self::ptw_1;
  }
}
