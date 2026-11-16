<?php


final class Idioma_obd extends Obxeto_bd {
  
  public function __construct() {
    parent::__construct();
  }
 
  public function mapa_bd() {   
    return new Idioma_mbd();
  }

  public static function inicia(FS_cbd $cbd, $id_idioma) {
    $s = new Idioma_obd();

    $s->select($cbd, "id_idioma = '{$id_idioma}'", null);
    
    return $s;
  }

  public function select(FS_cbd $cbd, $where = null, $orderby = null) {
    $r = $cbd->consulta($this->sql_select($cbd, $where, $orderby));
    
    if (!$a = $r->next()) return null;
    
    $this->post($a);
  }
}

//-------------------------------------------

final class Idioma_mbd extends Mapa_bd {
  public function __construct() {
    $t = new Taboa_dbd("idioma");
    
    $t->pon_campo("id_idioma", new Varchar(), true);
    $t->pon_campo("nome");
    
    $this->pon_taboa($t);
  }
}

