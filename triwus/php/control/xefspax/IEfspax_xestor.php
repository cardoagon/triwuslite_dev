<?php

interface IEfspax_xestor {
  public function id_oms($id_oms = null);
  public function id_paxina($id_paxina = null);
  public function id_idioma($id_idioma = null);
  public function config(Config_obd $c = null);
  public function config_mobil(Config_obd $c = null);
  public function configurar();
  public function usuario();
  public function edicion();
}

