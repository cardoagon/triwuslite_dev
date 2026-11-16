<?php

/*************************************************

    Triwus Framework v.0

    PayPal_obd.php

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

final class PayPal_obd extends Obxeto_bd {
  public function __construct($id_site, $ano, $numero = null) {
    parent::__construct();

    $this->atr("id_site")->post($id_site);
    $this->atr("ano"    )->post($ano);
    $this->atr("numero" )->post($numero);
  }

  public function mapa_bd() {
    return new PayPal_mbd();
  }

  public static function inicia(FS_cbd $cbd, $id_site, $ano, $numero = null) {
    $o = new PayPal_obd($id_site, $ano, $numero);

    if ($numero == null) return $o;

    $wh_s = $o->atr("id_site")->sql_where($cbd);
    $wh_a = $o->atr("ano"    )->sql_where($cbd);
    $wh_n = $o->atr("numero" )->sql_where($cbd);

    $o->select($cbd, "{$wh_s} and {$wh_a} and {$wh_n}");


    return $o;
  }
/*
  public function pagar_pedido(FS_cbd $cbd = null) {
    if ($cbd == null) $cbd = new FS_cbd();

    $id_site = $this->atr("id_site")->valor;
    $ano     = $this->atr("ano")->valor;
    $numero  = $this->atr("numero")->valor;

    $p = Pedido_obd::inicia($cbd, $id_site, $ano, $numero);

    $p->atr("pago_momento")->valor = date("YmdHis");
    $p->atr("id_pago"     )->valor = $this->atr("tx")->valor;

    return $p->update($cbd);
  }
*/
  public function pagar_pedido(FS_cbd $cbd = null) {
    if ($cbd == null) $cbd = new FS_cbd();

    $id_site = $this->atr("id_site")->valor;
    $ano     = $this->atr("ano")->valor;
    $numero  = $this->atr("numero")->valor;

    $p = Pedido_obd::inicia($cbd, $id_site, $ano, $numero);

    return $p->update_pago($cbd, date("Y-m-d"), $this->atr("tx")->valor, true);
  }

  public function select(FS_cbd $cbd, $where = null, $orderby = null) {
    $r = $cbd->consulta($this->sql_select($cbd, $where, $orderby));

    if (!$a = $r->next()) return;

    $this->post($a);
  }

  public function insert(FS_cbd $cbd) {
    if ($this->atr("IPN_response")->valor != null) return $this->update($cbd);

    $this->atr("IPN_response")->valor = "0";
    $this->atr("momento"     )->valor = date("YmdHis");

    return $cbd->executa($this->sql_insert($cbd));
  }

  public function update(FS_cbd $cbd) {
    $this->atr("momento")->valor = date("YmdHis");

    return $cbd->executa($this->sql_update($cbd));
  }

  public function delete(FS_cbd $cbd) {
    return $cbd->executa($this->sql_delete($cbd));
  }
}

//------------------------------------------

final class PayPal_mbd extends Mapa_bd {
  public function __construct() {
    parent::__construct();

    $t = new Taboa_dbd("rpc_paypal");

    $t->pon_campo("id_site"     , new Numero(), true);
    $t->pon_campo("ano"         , new Numero(), true);
    $t->pon_campo("numero"      , new Numero(), true);
    $t->pon_campo("momento"     , new Data("YmdHis"));
    $t->pon_campo("tx"          , new Varchar());
    $t->pon_campo("CSCMATCH"    , new Varchar());
    $t->pon_campo("IPN_response", new Varchar());
    $t->pon_campo("status"      , new Varchar());


    $this->pon_taboa($t);
  }
}

