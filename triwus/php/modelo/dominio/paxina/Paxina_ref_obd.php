<?php

final class Paxina_ref_obd extends Paxina_obd {
  public function __construct($id_paxina = null) {
    parent::__construct("ref", $id_paxina);
  }

  public static function inicia(FS_cbd $cbd, $id_paxina = null, $tipo = null) {
    $p = new Paxina_ref_obd($id_paxina);

    if ($id_paxina == null) return $p;

    $p->select($cbd, $p->atr("id_paxina")->sql_where($cbd));

    return $p;
  }

  public function paxref_obd(FS_cbd $cbd = null) {
    if ($cbd == null) $cbd = new FS_cbd();

    return Paxina_obd::inicia($cbd, $this->atr("id_ref")->valor);
  }

  public function a_grupo(FS_cbd $cbd = null) {
    return $this->paxref_obd($cbd)->a_grupo();
  }

  public function mapa_bd() {
    return new Paxina_ref_mbd();
  }

  public function insert(FS_cbd $cbd, $id_idioma) {
    //* inserta unha páxina.
    $this->atr("logo")->valor        = null;
    $this->atr("id_puntos")->valor   = null;
    $this->atr("actualizado")->valor = date("YmdHis");
    $this->atr("creado")->valor      = date("YmdHis");
    $this->atr("id_ref")->valor      = $this->atr('id_sincro')->valor;

    if (!$cbd->executa($this->sql_insert($cbd))) return false;

    $this->calcula_id($cbd);

    //* inserta multilinguaxe
    if ($id_idioma == null) $id_idioma = $this->atr("id_idioma")->valor;

    if ($id_idioma != null) {
      $ml = new MLpaxina_obd($id_idioma, $this->atr('id_paxina')->valor, $this->atr('id_sincro')->valor);

      if (!$ml->insert($cbd)) return false;
    }

    //* inserta a referencia;
    return $cbd->executa($this->sql_insert($cbd, "paxina_ref"));
  }

  public function duplicar(FS_cbd $cbd, $id_idioma, ?string $refsite = null) {
    $id_dupli = $this->atr("id_paxina")->valor;

    //* duplica p&aacute;xina
    $this->atr("id_paxina")->valor = null;
    $this->atr("id_comentarios")->valor = null;

    if ($this->atr("id_sincro")->valor == null) $this->atr("id_sincro")->valor = $id_dupli;


    return $this->insert($cbd, $id_idioma);
  }

  public static function id_ref(FS_cbd $cbd, $id_paxina = null) {
    $r = $cbd->consulta("select id_ref from paxina_ref where id_paxina = {$id_paxina}");

    $a = $r->next();

    return $a['id_ref'];
  }

  public function delete(FS_cbd $cbd) {
    if (!parent::delete($cbd)) return false;

    return $cbd->executa($this->sql_delete($cbd, null, "paxina_ref"));
  }

  public function action_2($limpa = false, $completa = false) {
    if ($this->atr("refexterna")->valor != null) return $this->atr("refexterna")->valor;

    return parent::action_2($limpa, $completa);
  }
}

//-------------------------------------

final class Paxina_ref_mbd extends Paxina_mbd {
  public function __construct() {
    parent::__construct();

    $t = new Taboa_dbd("paxina_ref");

    $t->pon_campo("id_paxina", new Numero(), true);
    $t->pon_campo("id_ref"   , new Numero());

    $this->relacion_fk($t, array("id_paxina"), array("id_paxina"));
  }
}

