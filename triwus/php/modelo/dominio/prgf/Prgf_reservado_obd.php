<?php

final class Prgf_reservado_obd extends Paragrafo_obd {

  public static function inicia(FS_cbd $cbd, $id_paragrafo) {
    $p = new Prgf_reservado_obd();

    if ($id_paragrafo == null) return $p;

    $p->atr("id_paragrafo")->valor = $id_paragrafo;

    $p->select($cbd, $p->atr("id_paragrafo")->sql_where($cbd));

    return $p;
  }

  public function duplicar(FS_cbd $cbd, $id_dupli, ?string $refsite = null) {
    return true;
  }

  public function duplicar_indice(FS_cbd $cbd, Paxina_obd $pax_dupli, ?string $refsite = null) {
    return true;
  }
}
