<?php


class Texto_obd extends Elmt_obd {
  const tipo_bd = "TEXT";
  
  public function __construct($txt = null, $wysiwyg = "0") {
    parent::__construct();
    
    $this->atr("txt"    )->valor = $txt;
    $this->atr("wysiwyg")->valor = $wysiwyg;
  }
 
  public function mapa_bd() {   
    return new Texto_mbd();
  }
 
  protected function tipo_bd() {
    return self::tipo_bd;
  }
/*
  final public static function actualiza_refsite(FS_cbd $cbd, $n0, $n2) {
    $t = new Texto_obd();
    
    $r = $cbd->consulta($t->sql_select($cbd, "tipo = '" . self::tipo_bd . "' and ref_site = '{$n0}'"));
    
    
    while ($a = $r->next()) {
      $t->post($a);
      
      $ref0 = Refs::url_sites . "/{$n0}/" . Refs::url_arquivos;
      $ref2 = Refs::url_sites . "/{$n2}/" . Refs::url_arquivos;
      
      $t->atr("txt")     ->valor = str_replace($ref0, $ref2, $t->atr("txt")->valor);
      $t->atr("ref_site")->valor = $n2;
      
      if (!$t->update($cbd)) return false;
    }
    
    return true;
  }
*/
}

//-----------------------------

final class Indice_obd extends Texto_obd {
  const tipo_bd = "INDICE";
  
  public function __construct() {
    parent::__construct(null, 1);
  }
 
  protected function tipo_bd() {
    return self::tipo_bd;
  }
  
  public function actualizar(Site_obd $s, $id_idioma) {
    $this->atr("txt")->valor = Elmt_indice::__indice($s, $id_idioma);
  }
}

//-----------------------------

class Texto_mbd extends Elmt_mbd {
  public function __construct() {
    parent::__construct();
    
    $t = new Taboa_dbd("elmt_texto");

    $t->pon_campo("id_elmt", new Numero(), true);
    $t->pon_campo("ref_site", new VarChar());
    $t->pon_campo("txt", new Varchar(true));
    $t->pon_campo("wysiwyg", new Numero());
    
    $this->relacion_fk($t, array("id_elmt"), array("id_elmt"));
  }
}

