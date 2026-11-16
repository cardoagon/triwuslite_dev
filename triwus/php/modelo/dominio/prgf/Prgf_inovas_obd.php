<?php

final class Prgf_inovas_obd extends Paragrafo_obd {

  public function __construct() {
    parent::__construct();

    $this->atr("i_entradas"           )->valor = 10;
    //~ $this->atr("inovas_imx_proporcion")->valor = 0.66;
    $this->atr("inovas_imx_proporcion")->valor = 0.6;
  }

  public static function inicia(FS_cbd $cbd, $id_paragrafo) {
    $p = new Prgf_inovas_obd();

    if ($id_paragrafo == null) return $p;

    $p->atr("id_paragrafo")->valor = $id_paragrafo;

    $p->select($cbd, $p->atr("id_paragrafo")->sql_where($cbd));

    return $p;
  }

  public function duplicar_indice(FS_cbd $cbd, Paxina_obd $pax_dupli, ?string $refsite = null) {
    $id_dupli  = $pax_dupli->atr("id_prgf_indice")->valor;
    $id_paxina = $pax_dupli->atr("id_paxina"     )->valor;
    $id_opcion = $pax_dupli->atr("id_opcion"     )->valor;
    $id_idioma = $pax_dupli->atr("id_idioma"     )->valor;


    $d = new Detalle_obd($id_paxina);

    $this->atr("id_paragrafo")->valor = null;

    $prgf = Prgf_inovas_obd::inicia($cbd, $id_dupli);

    $a_pelmt = $prgf->a_elmt("id_elmt", $cbd);

    if (!$d->insert($cbd, $this)) return false;

    if (!is_array($a_pelmt))  return true;
    if (count($a_pelmt) == 0) return true;

    foreach ($a_pelmt as $pelmt) {
      $pelmt->atr("id_paragrafo")->valor = $this->atr("id_paragrafo")->valor;

      $elmt = $pelmt->elmt($cbd);
      $paxd = $elmt->paxina_obd($cbd);

      $elmt->atr("id_elmt")->valor   = null;
      $elmt->atr("id_paxina")->valor = null;

      if ($paxd != null) {
        $paxd->atr("id_opcion")->valor = $id_opcion;

        if (!$paxd->duplicar_paxina_detalle($cbd, $id_idioma)) return false;

        $elmt->atr("id_paxina")->valor = $paxd->atr("id_paxina")->valor;
      }


      if (!$pelmt->insert($cbd, $elmt)) return false;
    }

    return true;
  }
}

