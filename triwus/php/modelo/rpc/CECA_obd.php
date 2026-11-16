<?php

final class CECA_obd extends Obxeto_bd {
  public function __construct($id_site, $ano, $numero = null) {
    parent::__construct();

    $this->atr("id_site")->post($id_site);
    $this->atr("ano"    )->post($ano);
    $this->atr("numero" )->post($numero);
  }

  public function mapa_bd() {
    return new CECA_mbd();
  }

  public static function inicia(FS_cbd $cbd, $id_site, $ano, $numero = null) {
    $o = new CECA_obd($id_site, $ano, $numero);

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
    $p->atr("id_pago"     )->valor = $this->atr("referencia")->valor;

    return $p->update($cbd);
  }
*/
  public function pagar_pedido(FS_cbd $cbd = null) {
    if ($cbd == null) $cbd = new FS_cbd();

    $id_site = $this->atr("id_site")->valor;
    $ano     = $this->atr("ano")->valor;
    $numero  = $this->atr("numero")->valor;

    $p = Pedido_obd::inicia($cbd, $id_site, $ano, $numero);

    return $p->update_pago($cbd, date("Y-m-d"), $this->atr("referencia")->valor, true);
  }
  
  public function select(FS_cbd $cbd, $where = null, $orderby = null) {
    //~ if ($where != null) $where = "({$where}) and ";

    //~ $where .= "(date_add(momento, interval 12 hour) > now())";

    $r = $cbd->consulta($this->sql_select($cbd, $where, $orderby));

    if (!$a = $r->next()) return;

    $this->post($a);
  }

  public function insert(FS_cbd $cbd) {
    if ($this->atr("momento")->valor != null) return $this->update($cbd);

    $this->atr("momento")->valor = date("YmdHis");

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

final class CECA_mbd extends Mapa_bd {
  public function __construct() {
    parent::__construct();


    $t = new Taboa_dbd("rpc_ceca");

    $t->pon_campo("id_site"   , new Numero(), true);
    $t->pon_campo("ano"       , new Numero(), true);
    $t->pon_campo("numero"    , new Numero(), true);
    $t->pon_campo("momento"   , new Data("YmdHis"));
    $t->pon_campo("referencia", new Varchar());
    $t->pon_campo("erro"      , new Varchar());


    $this->pon_taboa($t);
  }
}
