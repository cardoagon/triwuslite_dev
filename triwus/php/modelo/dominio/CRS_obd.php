<?php

final class CRS_obd extends Obxeto_bd {

  public function __construct($id_crs, $activar = false) {
    parent::__construct();

    $this->atr("id_crs")->valor = $id_crs;

    $this->activar($activar);
  }

  public function mapa_bd() {
    return new CRS_mbd();
  }

  public function activar($b = true) {
    $v = ($b)?"1":"0";

    $this->atr("rs_activado")->valor = $v;
    $this->atr("rs_facebook")->valor = $v;
    $this->atr("rs_twitter" )->valor = $v;
    $this->atr("rs_linkedin")->valor = $v;
    $this->atr("rs_whatsapp")->valor = $v;
  }

  public static function inicia(FS_cbd $cbd, $id_crs) {
    $s = new CRS_obd($id_crs);

    if ($id_crs == null) return $s;

    $s->select($cbd, "crs.id_crs = {$id_crs}");

    return $s;
  }

  public function select(FS_cbd $cbd, $where = null, $orderby = null) {
    $r = $cbd->consulta($this->sql_select($cbd, $where, $orderby));

    if (!$a = $r->next()) return;

    $this->post($a);
  }

  public function update(FS_cbd $cbd) {
    if ($this->atr("id_crs")->valor != null) return $cbd->executa($this->sql_update($cbd));

    return $this->insert($cbd);
  }

  public function insert(FS_cbd $cbd) {
    if (!$cbd->executa($this->sql_insert($cbd))) return false;

    $this->calcula_id($cbd);

    return true;
  }

  public function delete(FS_cbd $cbd) {
    if ($this->atr("id_crs")->valor == null) return true;

    return $cbd->executa($this->sql_delete($cbd));
  }

  private function calcula_id(FS_cbd $cbd) {
    $r = $cbd->consulta("select LAST_INSERT_ID() as id");

    if (!$a = $r->next()) die("CRS_obd.calcula_id(), (!a = r.next())");

    $this->atr("id_crs")->valor = $a['id'];
  }
}

//-----------------------------------------

final class CRS_mbd extends Mapa_bd {
  public function __construct() {
    $this->pon_taboa( self::__taboa_csr() );
  }

  public static function __taboa_csr() {
    $t = new Taboa_dbd("crs");

    $t->pon_campo("id_crs"     , new Numero(), true);
    $t->pon_campo("rs_activado", new Numero());
    $t->pon_campo("rs_facebook", new Numero());
    $t->pon_campo("rs_twitter" , new Numero());
    $t->pon_campo("rs_linkedin", new Numero());
    $t->pon_campo("rs_whatsapp", new Numero());


    return $t;
  }
}
