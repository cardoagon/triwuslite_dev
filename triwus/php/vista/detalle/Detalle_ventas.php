<?php

final class Detalle_ventas extends Detalle {
  public $dprgf = null;

  public function __construct(Paxina_ventas_obd $p, $editor = true) {
    parent::__construct($p, $editor);
  }

  public function declara_botoneras(Epaxina_edit $e, Paxina_obd $p) {
    $e->pon_obxeto(new Btnr_detalle_norte_ventas($e, $p)); //* norte

    $e->pon_obxeto(new Btnr_detalle_este());

    $e->pon_obxeto(new Btnr_detalle_oeste());
  }

  public function config_usuario(IEfspax_xestor $e = null) {
    if ($this->iprgf != null) $this->iprgf->inicia_ptw($e->config());
    if ($this->dprgf != null) $this->dprgf->inicia_ptw($e->config());

    parent::config_usuario($e);
  }

  public function ini_prgf(Paxina_obd $pax, Paragrafo_obd $prgf) {
    if ($pax->atr("id_prgf_indice")->valor == $prgf->atr("id_paragrafo")->valor) {
      $this->iprgf = new Prgf_iventas($pax, $this->__editor);

      return $this->iprgf;
    }

    if ($pax->atr("id_prgf_detalle")->valor == $prgf->atr("id_paragrafo")->valor) {
      $this->dprgf = new Prgf_ventas_detalle($pax);

      return $this->dprgf;
    }

    return new Paragrafo($prgf);
  }

}

