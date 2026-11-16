<?php


class Elmt_novas_obd extends Imaxe_obd {
  const tipo_bd = "NOVAS";

  public function __construct($id_paxina = null, $url = null) {
    parent::__construct($url);

    $this->atr("id_paxina")->valor = $id_paxina;
  }

  public function imaxe_obd() {
    $i = new Imaxe_obd();

    $i->atr("id_elmt")->valor  = null;

    $i->atr("id_style")->valor = null;
    $i->atr("css")->valor      = $this->atr("css")->valor;

    $i->atr("ref_site")->valor = $this->atr("ref_site")->valor;
    $i->atr("url")->valor      = $this->atr("url")->valor;

    $i->atr("link_href")->valor   = $this->atr("link_href")->valor;
    $i->atr("link_target")->valor = $this->atr("link_target")->valor;


    return $i;
  }

  public function txt_obd() {
    $t = new Texto_obd();

    $t->atr("id_elmt")->valor  = null;
    $t->atr("nome")->valor     = $this->atr("nome")->valor;
    $t->atr("notas")->valor    = $this->atr("notas")->valor;
    $t->atr("id_style")->valor = null;
    $t->atr("css")->valor      = "";

    $t->atr("ref_site")->valor = $this->atr("ref_site")->valor;
    $t->atr("txt")->valor      = $this->atr("txt")->valor;
    $t->atr("wysiwyg")->valor  = 1;


    return $t;
  }

  public function mapa_bd() {
    return new Elmt_novas_mbd();
  }

  protected function tipo_bd() {
    return self::tipo_bd;
  }

  public function update(FS_cbd $cbd) {
    if (!$this->modificado) return true;

    if (!parent::update($cbd)) return false;

    if ($this->atr("id_paxina")->valor == null) return true;

    $p= $this->paxina_obd($cbd);

    $p->atr("logo"      )->valor = $this->atr("id_elmt")->valor;
    $p->atr("nome"      )->valor = $this->atr("nome"   )->valor;
    $p->atr("titulo"    )->valor = $this->atr("nome"   )->valor;
    $p->atr("descricion")->valor = $this->atr("notas"  )->valor;


    return $p->update($cbd, true, false);
  }

  public function duplicar(FS_cbd $cbd, ?string $refsite = null) {
    $this->atr("link_href")->valor = null;

    return parent::duplicar($cbd, $refsite);
  }

  public function delete(FS_cbd $cbd, $delete_base = true, $delete_paxina = true) {
    if ($delete_paxina) {
      if ($this->atr("id_paxina")->valor != null) {
        $p = Paxina_obd::inicia($cbd, $this->atr("id_paxina")->valor, "novas");

        if (!$p->delete($cbd)) return false;
      }
    }

    return parent::delete($cbd, true);
  }

  public function paxina_obd(FS_cbd $cbd = null) {
    if ($this->atr("id_paxina")->valor == null) return null;

    if ($cbd == null) $cbd = new FS_cbd();


    return Paxina_obd::inicia($cbd, $this->atr("id_paxina")->valor, "novas");
  }

  public function data(FS_cbd $cbd = null) {
    if (($p = $this->paxina_obd($cbd)) == null) return "";

    $a = $p->atr("creado")->a_data();

    return "{$a['d']}-{$a['m']}-{$a['Y']} {$a['H']}:{$a['i']}:{$a['s']}";
  }
/*
  final public static function actualiza_refsite(FS_cbd $cbd, $n0, $n2) {
    $en_obd = new Elmt_novas_obd();

    $r = $cbd->consulta($en_obd->sql_select($cbd, "tipo = '" . self::tipo_bd . "' and ref_site = '{$n0}'"));


    while ($a = $r->next()) {
      $en_obd->post($a);

      $ref0 = Refs::url_sites . "/{$n0}/" . Refs::url_arquivos;
      $ref2 = Refs::url_sites . "/{$n2}/" . Refs::url_arquivos;

      $en_obd->atr("txt")     ->valor = str_replace($ref0, $ref2, $en_obd->atr("txt")->valor);
      $en_obd->atr("ref_site")->valor = $n2;

      if (!$en_obd->update($cbd)) return false;
    }

    return true;
  }
*/
}

//**********************

class Elmt_novas_mbd extends Elmt_mbd {
  public function __construct() {
    parent::__construct();

    $t = new Taboa_dbd("elmt_novas");

    $t->pon_campo("id_elmt"       , new Numero(), true);
    $t->pon_campo("id_paxina"     , new Numero());
    $t->pon_campo("ref_site"      , new VarChar());
    $t->pon_campo("url"           , new VarChar());
    $t->pon_campo("link_href"     , new VarChar());
    $t->pon_campo("link_target"   , new VarChar());
    $t->pon_campo("txt"           , new Varchar(true));

    $this->relacion_fk($t, array("id_elmt"), array("id_elmt"));
  }
}

