<?php

final class HR_obd extends Elmt_obd {
                     
  const tipo_bd = "HR";
  
  public function __construct() {
    parent::__construct();
  }
 
  public function mapa_bd() {   
    return new HR_mbd();
  }
 
  protected function tipo_bd() {
    return self::tipo_bd;
  }
}

//--------------------------------------------------------

final class HR_mbd extends Elmt_mbd {
  public function __construct() {
    parent::__construct();
    
    $t = new Taboa_dbd("elmt_hr");

    $t->pon_campo("id_elmt", new Numero(), true);
    
    $this->relacion_fk($t, array("id_elmt"), array("id_elmt"));
  }
}
