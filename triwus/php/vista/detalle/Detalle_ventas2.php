<?php


final class Detalle_ventas2 extends Detalle {
  public function __construct(Paxina_ventas2_obd $p, $editor = true) {
    parent::__construct($p, $editor);
  }

  public function declara_botoneras(Epaxina_edit $e, Paxina_obd $p) {
    $e->pon_obxeto(new Btnr_detalle_norte($e, $p));

    $e->pon_obxeto(new Btnr_detalle_este($this));

    $e->pon_obxeto(new Btnr_detalle_oeste());
  }

  public function config_usuario(IEfspax_xestor $e = null) {
    if ($this->iprgf != null) $this->iprgf->inicia_ptw($e->config());

    parent::config_usuario($e);
  }

  public function ini_prgf(Paxina_obd $pax, Paragrafo_obd $prgf) {
    if ($pax->atr("id_prgf_indice")->valor == $prgf->atr("id_paragrafo")->valor) {
      $this->iprgf = new Prgf_iventas2($pax, $this->__editor);

      return $this->iprgf;
    }

    return new Paragrafo($prgf);
  }

}

