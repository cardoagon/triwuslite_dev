<?php


/*************************************************

    Triwus Framework v.0

    Pe_obd.php

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

final class Pe_obd extends Paragrafo_obd
                   implements ILista_xeobd {

  public function __construct() {
    parent::__construct();
  }

  public static function inicia(FS_cbd $cbd, $id_paragrafo) {
    $p = new Pe_obd();

    if ($id_paragrafo == null) return $p;

    $p->atr("id_paragrafo")->valor = $id_paragrafo;

    $p->select($cbd, $p->atr("id_paragrafo")->sql_where($cbd));

    return $p;
  }

  public function insert(FS_cbd $cbd) {
    if (!parent::insert($cbd)) return false;

    $pe_obd = new Paragrafo_elmt_obd();

    $pe_obd->atr("posicion")->valor = 1;
    $pe_obd->atr("id_paragrafo")->valor = $this->atr("id_paragrafo")->valor;


    return $pe_obd->insert($cbd, new Texto_obd("&nbsp;", 1));
  }
}

?>
