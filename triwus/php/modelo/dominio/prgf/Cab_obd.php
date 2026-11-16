<?php

final class Cab_obd extends    Paragrafo_obd
                    implements ILista_xeobd {

  private $refsite = null;

  public function __construct($refsite = null) {
    parent::__construct();
    
    $this->refsite = $refsite;
  }

  public static function inicia(FS_cbd $cbd, $id_paragrafo) {
    $p = new Cab_obd();

    if ($id_paragrafo == null) return $p;

    $p->atr("id_paragrafo")->valor = $id_paragrafo;

    $p->select($cbd, $p->atr("id_paragrafo")->sql_where($cbd));

    return $p;
  }

  public function logo_obd() {
    $where = "id_paragrafo = " . $this->atr("id_paragrafo")->valor . " and posicion = 1";

    $pe_obd = new Paragrafo_elmt_obd();

    $pe_obd->select(new FS_cbd(), $where);

    return $pe_obd->elmt();
  }

  public function insert(FS_cbd $cbd) {
    if (!parent::insert($cbd)) return false;

    $id_prgf = $this->atr("id_paragrafo")->valor;

    if (!self::insert_logo($cbd, $id_prgf, $this->refsite)) return false;

    if (!self::insert_titulo($cbd, $id_prgf)) return false;

    return true;
  }

  private static function insert_logo(FS_cbd $cbd, $id_prgf, $refsite) {
    if ($refsite == null) return false;
    
    
    $pe_obd = new Paragrafo_elmt_obd();

    $pe_obd->atr("posicion"    )->valor = 1;
    $pe_obd->atr("id_paragrafo")->valor = $id_prgf;
    
    $lo_obd = new Logo_obd();
    
    $lo_obd->atr("ref_site")->valor = $refsite;

    return $pe_obd->insert($cbd, $lo_obd);
  }

  private static function insert_titulo(FS_cbd $cbd, $id_prgf) {
    $pe_obd = new Paragrafo_elmt_obd();

    $pe_obd->atr("posicion"    )->valor = 2;
    $pe_obd->atr("id_paragrafo")->valor = $id_prgf;

    return $pe_obd->insert($cbd, new Texto_obd("&nbsp;"));
  }
}

