<?php

final class Paxina_dl_obd extends Paxina_obd {
  public function __construct($id_paxina = null) {
    parent::__construct("libre", $id_paxina);
  }

  public function mapa_bd() {
    return new Paxina_mbd();
  }

  public function insert(FS_cbd $cbd, $id_idioma) {
    return $this->insert_base($cbd, $id_idioma, new Paragrafo_obd());
  }

  public function duplicar(FS_cbd $cbd, $id_idioma, ?string $refsite = null) {

    $id_dupli = $this->atr("id_paxina")->valor;

    //* duplica páxina
    $this->atr("id_paxina")->valor = null;
    $this->atr("id_comentarios")->valor = null;

    if ($this->atr("id_sincro")->valor == null) $this->atr("id_sincro")->valor = $id_dupli;

    if (!$this->insert_base($cbd, $id_idioma)) return false;


    //* duplica detalle
    $d = new Detalle_obd($this->atr("id_paxina")->valor);


    if (!$d->duplicar($cbd, $id_dupli, $refsite)) return false;
    
    return true;
  }
}

