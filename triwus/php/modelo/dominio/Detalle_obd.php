<?php

final class Detalle_obd extends Obxeto_bd  {

  public function __construct($id_paxina = null) {
    parent::__construct();

    $this->atr("id_paxina")->valor = $id_paxina;
  }

  public function mapa_bd() {
    return new Detalle_mbd();
  }

  public static function inicia(FS_cbd $cbd, $id_paxina) {
    $d = new Detalle_obd($id_paxina);

    if ($id_paxina == null) return $d;

    $d->select($cbd, $d->atr("id_paxina")->sql_where($cbd));

    return $d;
  }

  public function select(FS_cbd $cbd, $where = null, $orderby = null) {
    $r = $cbd->consulta($this->sql_select($cbd, $where, $orderby));

    if (!$a = $r->next()) return;

    $this->post($a);
  }

  public function update(FS_cbd $cbd, Paragrafo $p) {
    $p_obd = $p->prgf_obd();

    $this->atr("posicion")->valor = $p->pai->pos_prgf($p);

    $this->atr("id_paragrafo")->valor = $p_obd->atr("id_paragrafo")->valor;

    if ($p_obd->atr("id_paragrafo")->valor == null)
      $ok = $this->insert($cbd, $p_obd);
    else
      $ok = $cbd->executa($this->sql_update($cbd));

    if (!$ok) return false;

    return $p_obd->update($cbd, $p->a_elmt_obd());
  }

  public function insert(FS_cbd $cbd, Paragrafo_obd $p_obd = null, $posicion = null) {
    if ($p_obd == null) $p_obd = new Paragrafo_obd();

    if (!$p_obd->insert($cbd)) return false;

    $this->atr("id_paragrafo")->valor = $p_obd->atr("id_paragrafo")->valor;

    //~ $this->atr("posicion")->valor = ($posicion != null)?$posicion:"1";

    if ($this->atr("posicion")->valor == null) {
      $this->atr("posicion")->valor = ($posicion != null)?$posicion:"1";
    }

    return $cbd->executa($this->sql_insert($cbd));
  }

  public function duplicar(FS_cbd $cbd, $id_dupli, ?string $refsite = null) {
    $d = new Detalle_obd($id_dupli);

    $a_prgf = $d->a_prgf($cbd);

    if (count($a_prgf) == 0) return true;

    $ct_prgf = 1;
    foreach ($a_prgf as $prgf) {
      $id_dupli2 = $prgf->atr("id_paragrafo")->valor;

      $prgf->atr("id_paragrafo")->valor = null;

      if (!$prgf->duplicar($cbd, $id_dupli2, $refsite)) return false;

      $this->atr("id_paragrafo")->valor = $prgf->atr("id_paragrafo")->valor;
      $this->atr("posicion"    )->valor = $ct_prgf;

      if (!$cbd->executa($this->sql_insert($cbd))) return false;


      $ct_prgf++;
    }

    return true;
  }

  public function delete(FS_cbd $cbd, Paragrafo $p = null) {
    $a_prgf = ($p == null)?$this->a_prgf($cbd):array($p->prgf_obd());

    if ($a_prgf == null) return true;
    //~ if ($a_prgf == null) die("Detalle_obd.delete(), (a_prgf = this.a_prgf()) == null");

    foreach($a_prgf as $prgf) {
      if (!$prgf->delete($cbd)) return false;

      $this->atr("id_paragrafo")->valor = $prgf->atr("id_paragrafo")->valor;

      if (!$cbd->executa($this->sql_delete($cbd))) return false;
    }

    return true;
  }

  public function paxina_obd() {
    return Paxina_obd::inicia(new FS_cbd(), $this->atr("id_paxina")->valor);
  }

  public function a_prgf(FS_cbd $cbd = null) {
    if ($cbd == null) $cbd = new FS_cbd();

    $sql = "select *
              from detalle
             where id_paxina = {$this->atr("id_paxina")->valor}
          order by posicion";

    $r = $cbd->consulta($sql);

    $a_prgf = null;
    while ($a = $r->next()) $a_prgf[$a['posicion']] = Paragrafo_obd::inicia($cbd, $a['id_paragrafo']);

    return $a_prgf;
  }

  public function a_prgf_ids(FS_cbd $cbd = null) {
    if ($cbd == null) $cbd = new FS_cbd();

    $r = $cbd->consulta("select id_paragrafo from detalle where id_paxina = {$this->atr("id_paxina")->valor} order by posicion");

    $a_id = null;
    while ($a = $r->next()) $a_id[] = $a['id_paragrafo'];

    return $a_id;
  }
}

//*************************************

final class Detalle_mbd extends Mapa_bd {
  public function __construct() {
    $t = new Taboa_dbd("detalle");

    $t->pon_campo("id_paxina", new Numero(), true);
    $t->pon_campo("id_paragrafo", new Numero(), true);
    $t->pon_campo("posicion", new Numero());

    $this->pon_taboa($t);
  }
}

