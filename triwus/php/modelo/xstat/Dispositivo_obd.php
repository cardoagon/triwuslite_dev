<?php

/*************************************************

    Tilia Framework v.0

    Dispositivo_obd.php

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

final class Dispositivo_obd extends Obxeto_bd {

  public function __construct() {
    parent::__construct();
  }

  public function mapa_bd() {
    return new Dispositivo_mbd();
  }

  public static function inicia($id_site) {
    $cbd = new FS_cbd();

    $d = new Dispositivo_obd();

    $d->atr("id_site"    )->valor = $id_site;
    $d->atr("dispositivo")->valor = self::__dispositivo();
    $d->atr("ct"         )->valor = 0;

    $w0 = $d->atr("id_site"    )->sql_where($cbd);
    $w1 = $d->atr("dispositivo")->sql_where($cbd);

    $d->select($cbd, "({$w0}) and ({$w1})");

    return $d;
  }

  public function select(FS_cbd $cbd, $where = null, $orderby = null) {
    $r = $cbd->consulta($this->sql_select($cbd, $where, $orderby));

    if ($a = $r->next()) $this->post($a);
  }

  public function anotar(FS_cbd $cbd) {
    $this->atr("ct")->valor++;

    if ($this->atr("ct")->valor == 1)
      return $cbd->executa($this->sql_insert($cbd));
    else
      return $cbd->executa($this->sql_update($cbd));
  }

  private static function __dispositivo() {
    if (Mobile_Detect::__exec() == 3) return "m";

    return "pc";
  }
}

//------------------------------------------

final class Dispositivo_mbd extends Mapa_bd {
  public function __construct() {
    $t = new Taboa_dbd("stats_dispositivo");

    $t->pon_campo("id_site", new Numero(), true);
    $t->pon_campo("dispositivo", new Varchar(), true);
    $t->pon_campo("ct", new Numero());

    $this->pon_taboa($t);
  }
}

?>