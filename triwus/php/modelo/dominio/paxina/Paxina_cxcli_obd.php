<?php

final class Paxina_cxcli_obd extends    Paxina_obd
                             implements IPax_indice {

  public function __construct($id_paxina = null) {
    parent::__construct("cxcli", $id_paxina);
  }

  public function mapa_bd() {
    return new Paxina_mbd();
  }

  public function __indice() {
    return true;
  }

  public function insert(FS_cbd $cbd, $id_idioma) {
    $prgf = new Prgf_cxcli_obd();

    $prgf->atr("nome")->valor = "Cat&aacute;logo";

    if (!$this->insert_base($cbd, $id_idioma, $prgf)) return false;

    $this->atr("id_prgf_indice")->valor = $prgf->atr("id_paragrafo")->valor;

    if (!$cbd->executa($this->sql_update($cbd))) return false;


    return true;
  }

  public function duplicar(FS_cbd $cbd, $id_idioma, ?string $refsite = null) {
    return true;
  }

  public function prgf_indice(FS_cbd $cbd = null) {
    if ($cbd == null) $cbd = new FS_cbd();

    return Prgf_cxcli_obd::inicia($cbd, $this->atr("id_prgf_indice")->valor);
  }
}

