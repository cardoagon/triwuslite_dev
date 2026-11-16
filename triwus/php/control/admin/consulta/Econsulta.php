<?php


abstract class Econsulta extends Efs_admin {
  public function __construct(Paxina $p, FGS_usuario $u = null) {
    parent::__construct($p, $u);
  }

  public function __site_obd($id_site = null, FS_cbd $cbd = null) {
    if ($id_site == null) return null;

    if ($cbd == null) $cbd = new FS_cbd();

    return Site_obd::inicia($cbd, $id_site);
  }
}


