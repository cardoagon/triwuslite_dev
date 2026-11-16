<?php

/*************************************************

    Triwus Framework v.0

    Color_obd.php

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

final class Color_obd extends Obxeto_bd {
  const transparente = "transp";
  const transp_css   = "transparent";

  public function __construct($hex = null, $nome = null, $paleta = null) {
    parent::__construct();

    $this->atr("hex"   )->valor = trim($hex, "#");
    $this->atr("nome"  )->valor = $nome;
    $this->atr("paleta")->valor = $paleta;
  }

  public function mapa_bd() {
    return new Color_mbd();
  }

  public static function inicia($hex, $nome = null, $paleta = null) {
    if ($hex == null            ) return self::__transp();
    if ($hex == self::transp_css) return self::__transp();

    $c = new Color_obd($hex, $nome, $paleta);

    $cbd = new FS_cbd();

    $c->select($cbd, $c->atr("hex")->sql_where($cbd));

    return $c;
  }

  public function select(FS_cbd $cbd, $where = null, $orderby = null) {
    $r = $cbd->consulta($this->sql_select($cbd, $where, $orderby));

    if (!$a = $r->next()) return null;

    $this->post($a);
  }

  public function lenda() {
    if ($this->atr("nome")->valor == null) return "#" . $this->atr("hex")->valor;

    return $this->atr("nome")->valor . "&nbsp;&middot;&nbsp;#" . $this->atr("hex")->valor;
  }

  public static function __transp() {
    return new Color_obd(self::transp_css, "Transparente");
  }

  public static function a_pantones() {
    $cobd = new Color_obd();

    $cbd = new FS_cbd();

    $ap = null;

    $r = $cbd->consulta($cobd->sql_select($cbd, null, "paleta, hex"));

    while ($a = $r->next()) $ap[$a['paleta']][$a['nome']] = $a['hex'];

    return $ap;
  }
}

//**************************************

final class Color_mbd extends Mapa_bd {
  public function __construct() {
    $t = new Taboa_dbd("color");

    $t->pon_campo("hex", new VarChar(), true);
    $t->pon_campo("nome");
    $t->pon_campo("paleta");

    $this->pon_taboa($t);
  }
}

?>
