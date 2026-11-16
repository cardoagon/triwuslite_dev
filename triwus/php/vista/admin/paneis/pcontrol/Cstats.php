<?php


final class Cstats extends Componente implements ICPC {
  public function __construct($id_site) {
    parent::__construct("cstats", "ptw/paneis/pcontrol/stats/stats.html");

    

    $this->pon_obxeto(self::__idPiwik($id_site));
    
    $this->pon_obxeto(self::__idIdioma($id_site));
    
    $this->pon_obxeto(new Param("ta_piwik", "5b4b80dba2d2592db9a84d4cbb073e01")); //* token usuario matomo (trwPcontrol).
  }

  public function cpc_miga() {
    return null;
  }

  private static function __idPiwik($id_site) {
    $sp = Site_plesk_obd::inicia_s(new FS_cbd(), $id_site);

    return new Param("id_piwik", $sp->atr("id_piwik")->valor);
  }

  private static function __idIdioma($id_site) {
    $l = MLsite_obd::idioma_predeterminado($id_site);

    //~ if ($l ==)
    
    return new Param("id_idioma", $l);
  }
}
