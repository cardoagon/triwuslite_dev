<?php

final class Paxina_legal_obd extends Paxina_obd {
  const urldocs_0 = "ptw/paneis/pcontrol/ceditor_legais/";

  public function __construct($id_paxina = null) {
    parent::__construct("legal", $id_paxina);

    $this->atr("visible")->valor = "0";
  }

  public function mapa_bd() {
    return new Paxina_mbd();
  }

  public function insert(FS_cbd $cbd, $id_idioma) {
    //* inserta prgf
    $prgf_cgs  = $this->__prgf("Condiciones Generales"              , "legal-1");
    $prgf_lopd = $this->__prgf("Ley Orgánica de Protección de Datos", "legal-2");
    $prgf_coo  = $this->__prgf("Aviso de Cookies"                   , "legal-6");
    $prgf_vta  = $this->__prgf("Condiciones de venta"               , "legal-3");

    if (!$this->insert_base($cbd, $id_idioma, $prgf_cgs)) return false; //* inserta o primeiro prgf


    $d = $this->detalle_obd($cbd);

    if (!$d->insert($cbd, $prgf_lopd, 2)) return false;
    if (!$d->insert($cbd, $prgf_coo , 3)) return false;
    if (!$d->insert($cbd, $prgf_vta , 4)) return false;


    //* inserta textos legais

    if (!$this->insert_prgfelmt($cbd, $prgf_cgs , "aviso_legal.html"  )) return false;
    if (!$this->insert_prgfelmt($cbd, $prgf_lopd, "lopd.html"         )) return false;
    if (!$this->insert_prgfelmt($cbd, $prgf_coo , "cookies-texto.html")) return false;
    if (!$this->insert_prgfelmt($cbd, $prgf_vta , "cgc.html"          )) return false;


    return true;
  }

  public function duplicar(FS_cbd $cbd, $id_idioma, ?string $refsite = null) {
    return Paxina_dl_obd::duplicar($cbd, $id_idioma, $refsite);
  }

/*
 *
 * name: Paxina_legal_obd::_enlace
 *
 * @param tipo IN {1, 2, 3, 6}
 *
 *  .- Condiciones Generales               ---> 1
 *  .- Ley Orgánica de Protección de Datos ---> 2
 *  .- Aviso de Cookies                    ---> 6
 *  .- Condiciones de venta                ---> 3
 *
 *
 * @return String. enlace a un texto legal.
 *
 */
  public function enlace($tipo) {
    return $this->action(true) . "#" . md5("legal-{$tipo}");
  }

  private function insert_prgfelmt(FS_cbd $cbd, Paragrafo_obd $prgf, $id_ptw) {
    $prgfelmt = new Paragrafo_elmt_obd();

    $prgfelmt->atr("posicion"    )->valor = 1;
    $prgfelmt->atr("id_paragrafo")->valor = $prgf->atr("id_paragrafo")->valor;


    $txt = $this->__txt($id_ptw);


    return $prgfelmt->insert($cbd, new Texto_obd($txt, 1));
  }

  private function __txt($id_ptw) {
    $f = $id_ptw;
    $d = Efs::url_legais( $this->atr("id_site")->valor );

    if (!is_dir($d)) mkdir($d);

    if (!is_file("{$d}{$f}")) copy(self::urldocs_0 . $f, "{$d}{$f}");


    return file_get_contents("{$d}{$f}");
  }

  private static function __prgf($nome, $ancla) {
    $prgf = new Paragrafo_obd();

    $prgf->atr("nome"    )->valor = $nome;
    $prgf->atr("ancla"   )->valor = $ancla;


    return $prgf;
  }
}

