<?php

/*************************************************

    Triwus Framework v.0

    Elmt_ventas_obd.php

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


class Elmt_ventas_obd extends Elmt_obd {
  const tipo_bd = "VENTAS";

  private $a_articulo = null;

  public function __construct($id_paxina = null, $url = null) {
    parent::__construct($url);

    $this->atr("id_paxina")->valor = $id_paxina;
  }

  public function mapa_bd() {
    return new Elmt_ventas_mbd();
  }

  protected function tipo_bd() {
    return self::tipo_bd;
  }

  public function update(FS_cbd $cbd) {
    if (!$this->modificado) return true;

    if (!parent::update($cbd)) return false;

    if ($this->atr("id_paxina")->valor == null) return true;

    $pax_md5 = Paxina_obd::__md5( $this->atr("nome")->valor );

    $sql = "update elmt_ventas a inner join paxina       b on (a.id_paxina = b.id_paxina)
                                 inner join articulo     c on (a.id_site = c.id_site and a.id_articulo = c.id_articulo)
                                 inner join articulo_imx d on (a.id_site = d.id_site and a.id_articulo = d.id_articulo)
               set b.logo = d.id_logo, b.nome = c.nome, b.descricion = c.notas, b.md5 = '{$pax_md5}'
             where a.id_elmt = " . $this->atr("id_elmt")->valor;

    if (!$cbd->executa($sql)) return false;


    return true;
  }

  public function delete(FS_cbd $cbd, $delete_base = true) {
    //* 1.- se temos unha paxina detalle asociada, eliminamola.
    if (($id_paxina = $this->atr("id_paxina")->valor) != null) {
      $p = Paxina_obd::inicia($cbd, $id_paxina, "ventas");

      if (!$p->delete($cbd)) return false;
    }

    //* 2.- eliminamos a parte de elmt_obd
    return parent::delete($cbd);
  }

  public function paxina_obd(FS_cbd $cbd = null) {
    if ($this->atr("id_paxina")->valor == null) return null;

    if ($cbd == null) $cbd = new FS_cbd();


    return Paxina_obd::inicia($cbd, $this->atr("id_paxina")->valor, "ventas");
  }

  public function articulo_obd(FS_cbd $cbd = null, $i = 0) {
    if ($cbd == null) $cbd = new FS_cbd();

    return Articulo_obd::inicia($cbd, $this->atr("id_site")->valor, $this->atr("id_articulo")->valor);
  }
}

//**********************

class Elmt_ventas_mbd extends Elmt_mbd {
  public function __construct() {
    parent::__construct();

    $t = new Taboa_dbd("elmt_ventas");

    $t->pon_campo("id_elmt"    , new Numero(), true);
    $t->pon_campo("id_paxina"  , new Numero());
    $t->pon_campo("id_site"    , new Numero());
    $t->pon_campo("id_articulo", new Numero());

    $this->relacion_fk($t, array("id_elmt"), array("id_elmt"));
  }
}

