<?php

/*************************************************

    Tilia Framework v.0

    Navegador_obd.php

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

final class Navegador_obd extends Obxeto_bd {

  public function __construct() {
    parent::__construct();
  }

  public function mapa_bd() {
    return new Navegador_mbd();
  }

  public static function inicia($id_site) {
    $cbd = new FS_cbd();

    $n = new Navegador_obd();

    $n->atr("id_site"  )->valor = $id_site;
    $n->atr("navegador")->valor = "";
    $n->atr("ct"       )->valor = 0;

    $w0 = $n->atr("id_site"  )->sql_where($cbd);
    $w1 = $n->atr("navegador")->sql_where($cbd);

    $n->select($cbd, "({$w0}) and ({$w1})");

    return $n;
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

  //~ private static function __navegador() {
    //~ return Mobile_Detect::__userAgent(true);
  //~ }
}

//------------------------------------------

final class Navegador_mbd extends Mapa_bd {
  public function __construct() {
    $t = new Taboa_dbd("stats_navegador");

    $t->pon_campo("id_site", new Numero(), true);
    $t->pon_campo("navegador", new Varchar(), true);
    $t->pon_campo("ct", new Numero());

    $this->pon_taboa($t);
  }
}

?>
