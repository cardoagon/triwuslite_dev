<?php

class Paxinador_obd extends Elmt_obd {

  const tipo_bd = "PAXINADOR";

  public function __construct() {
    parent::__construct();
  }

  public static function crea_migaspan() {
    return new Paxinador_obd();
  }

  public function mapa_bd() {
    return new Elmt_mbd();
  }

  protected function tipo_bd() {
    return self::tipo_bd;
  }
  
  public static function a_migas($id_paxina, $url_limpa = false, FS_cbd $cbd = null):array {
    if ($id_paxina == null) return [];
    
    if ($cbd == null) $cbd = new FS_cbd();

    $p = Paxina_obd::inicia($cbd, $id_paxina);


    return $p->a_migas($url_limpa, $cbd);
  }
}
