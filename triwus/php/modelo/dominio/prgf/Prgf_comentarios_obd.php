<?php

/*************************************************

    Triwus Framework v.0

    Prgf_comentarios_obd.php

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

final class Prgf_comentarios_obd extends Paragrafo_obd {
  public function __construct() {
    parent::__construct();

    $this->atr("nome")->valor = "Comentarios de internautas";
  }

  public static function inicia(FS_cbd $cbd, $id_paragrafo) {
    $p = new Prgf_comentarios_obd();

    if ($id_paragrafo == null) return $p;

    $p->atr("id_paragrafo")->valor = $id_paragrafo;

    $p->select($cbd, $p->atr("id_paragrafo")->sql_where($cbd));

    return $p;
  }

  public function elmt_intro() {
    $where = "id_paragrafo = " . $this->atr("id_paragrafo")->valor . " and posicion = 1";

    $pe_obd = new Paragrafo_elmt_obd();

    $pe_obd->select(new FS_cbd(), $where);

    return $pe_obd->elmt();
  }

  public function elmt_comentarios() {
    $where = "id_paragrafo = " . $this->atr("id_paragrafo")->valor . " and posicion = 2";

    $pe_obd = new Paragrafo_elmt_obd();

    $pe_obd->select(new FS_cbd(), $where);

    return $pe_obd->elmt();
  }

  public function insert(FS_cbd $cbd) {
    if (!parent::insert($cbd)) return false;

    if (!self::insert_intro($cbd, $this->atr("id_paragrafo")->valor)) return false;
    if (!self::insert_elmt($cbd, $this->atr("id_paragrafo")->valor)) return false;

    return true;
  }

  private static function insert_intro(FS_cbd $cbd, $id_prgf) {
    $pe_obd = new Paragrafo_elmt_obd();

    $pe_obd->atr("posicion")->valor = 1;
    $pe_obd->atr("id_paragrafo")->valor = $id_prgf;

    return $pe_obd->insert($cbd, new Texto_obd("<font size=5>Puedes escribir tu opinión, muchas gracias :-)</font>", 1));
  }

  private static function insert_elmt(FS_cbd $cbd, $id_prgf) {
    $pe_obd = new Paragrafo_elmt_obd();

    $pe_obd->atr("posicion")->valor = 2;
    $pe_obd->atr("id_paragrafo")->valor = $id_prgf;

    return $pe_obd->insert($cbd, new Comentarios_obd());
  }
}

?>