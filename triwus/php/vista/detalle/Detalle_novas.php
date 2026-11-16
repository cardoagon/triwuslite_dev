<?php

/*************************************************

    Triwus Framework v.0

    Detalle_novas.php

    Author: Carlos Domingo Arias González

    Copyright (C) 2011

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation version 3.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.

*************************************************/



final class Detalle_novas extends Detalle {
  public function __construct(Paxina_novas_obd $p, $editor = true) {
    parent::__construct($p, $editor);
  }

  public function declara_botoneras(Epaxina_edit $e, Paxina_obd $p) {
    $e->pon_obxeto(new Btnr_detalle_norte_novas($e, $p)); //* norte

    $e->pon_obxeto(new Btnr_detalle_este());

    $e->pon_obxeto(new Btnr_detalle_oeste());
  }

  public function config_usuario(IEfspax_xestor $e = null) {
    if ($this->iprgf != null) $this->iprgf->inicia_ptw($e->config());

    parent::config_usuario($e);
  }

  public function ini_prgf(Paxina_obd $pax, Paragrafo_obd $prgf) {
    if ($pax->atr("id_prgf_indice")->valor == $prgf->atr("id_paragrafo")->valor) {
      $this->iprgf = new Prgf_inovas($pax, $this->__editor);

      return $this->iprgf;
    }

    return new Paragrafo($prgf);
  }
}

