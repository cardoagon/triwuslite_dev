<?php

/*************************************************

    Triwus Framework v.0

    Difucom2_obd.php

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


final class Difucom2_obd extends Elmt_obd {
  const tipo_bd = "DIFUCOM2";

  public function __construct() {
    parent::__construct();

    $this->usa_crs = true;
  }

  public static function crea_rs() {
    $df2 = new Difucom2_obd();

    $df2->crs_obd( new CRS_obd(null, true) );



    return $df2;

  }

  public function mapa_bd() {
    return new Difucom2_mbd();
  }

  protected function tipo_bd() {
    return self::tipo_bd;
  }
}

//--------------------------------------------------------

final class Difucom2_mbd extends Elmt_mbd {
  public function __construct() {
    parent::__construct();

    $t = new Taboa_dbd("elmt_difucom2");

    $t->pon_campo("id_elmt", new Numero(), true);

    $this->relacion_fk($t, array("id_elmt"), array("id_elmt"));
  }
}
