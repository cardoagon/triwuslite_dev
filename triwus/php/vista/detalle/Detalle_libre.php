<?php

/*************************************************

    Tilia Framework v.0

    Detalle_libre.php

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


final class Detalle_libre extends Detalle {
  public function __construct(Paxina_dl_obd $p, $editor = true) {
    parent::__construct($p, $editor);
  }

  public function declara_botoneras(Epaxina_edit $e, Paxina_obd $p) {
    $e->pon_obxeto(new Btnr_detalle_norte($e, $p));

    $e->pon_obxeto(new Btnr_detalle_este($this));

    $e->pon_obxeto(new Btnr_detalle_oeste());
  }

  public function ini_prgf(Paxina_obd $pax, Paragrafo_obd $prgf) {
    return new Paragrafo($prgf);
  }
}

//*************************************

final class Detalle_legal extends Detalle {
  public function __construct(Paxina_legal_obd $p, $editor = true) {
    parent::__construct($p, $editor);
  }

  public function declara_botoneras(Epaxina_edit $e, Paxina_obd $p) {
    $e->pon_obxeto(new Btnr_detalle_norte($e, $p));

    $e->pon_obxeto(new Btnr_detalle_este($this));

    $e->pon_obxeto(new Btnr_detalle_oeste());
  }

  public function ini_prgf(Paxina_obd $pax, Paragrafo_obd $prgf) {
    return new Paragrafo($prgf);
  }
}

