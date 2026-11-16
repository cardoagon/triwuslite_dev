<?php

final class Paxinfo_ref extends Paxinfo_nulo {
  private $p = null;

  public function __construct(Paxina_ref_obd $p) {
    parent::__construct(null, "ptw/xpax/paxinfo/paxinfo_ref.html");

    $this->p = $p;

    $this->ptw_0 = Refs::url(self::ptw_0);

    $this->__reset($p);

    $this->pon_obxeto(self::__lpaxref());
  }

  public function operacion(EstadoHTTP $e) {
    if ($this->obxeto("lpaxref")->control_evento()) {
      return $e->obxeto("xpax")->operacion_idoms($e, null, null, $this->p->atr("id_ref")->valor);
    }

    return Paxinfo::operacion($e);
  }

  public function titulo_ficha() {
    return "P&aacute;gina Referencia";
  }

  public function __reset(Paxina_obd $p = null) {
    if ($p == null) return;

    $this->obxeto("nome")->post($p->atr("nome")->valor);
    $this->obxeto("descricion")->post($p->atr("descricion")->valor);

    $p_ref = Paxina_obd::inicia(new FS_cbd(), $p->atr("id_ref")->valor);

    $this->obxeto("id_oms")->post($p_ref->atr("id_opcion")->valor);
    $this->obxeto("id_pax")->post($p_ref->atr("id_paxina")->valor);
    $this->obxeto("id_idioma")->post($p_ref->atr("id_idioma")->valor);
  }

  protected function __update(FS_cbd $cbd, Paxina_obd $p = null) {
    $this->p->atr("nome")->valor = $this->obxeto("nome")->valor();
    $this->p->atr("descricion")->valor = $this->obxeto("descricion")->valor();

    return $this->p->update($cbd, true, false);
  }

  private static function __lpaxref() {
    $etq = "<span class=texto3_a>Ver propiedades de la p&aacute;gina referenciada</span>";

    $b = Panel_fs::__bapagado("lpaxref", null, null , false, $etq);

    $b->envia_ajax("onclick");

    return $b;
  }
}

