<?php

class Elmt_difucom2 extends AElemento {
  public function __construct(Difucom2_obd $pe_obd) {
    parent::__construct($pe_obd, Refs::url("ptw/paragrafo/elmt/elmt_difucom2.html"));

    $crs_obd = $pe_obd->crs_obd();

    if (!$crs_obd->atr("rs_activado")->valor) $crs_obd->activar();

    $this->pon_obxeto( new CRS($crs_obd) );
  }

  public function config_usuario(IEfspax_xestor $e = null) {
    AElemento::config_usuario($e);

    $this->obxeto("crs")->config_usuario($e);

    //~ $this->obxeto("crs")->pon_paxina( $e->id_paxina() );
  }

  public function elmt_obd() {
    $df2 = new Difucom2_obd();

    $df2->crs_obd( $this->obxeto("crs")->crs_obd() );

    return $this->obd_base($df2);
  }

  public function editor() {
    return new Editor_edifucom2($this);
  }

  public function html_propio() {
    $html = trim( $this->obxeto("crs")->html() );

    if ($html == null) return "CRS Baleiro";

    return $html;
  }
}

//*******************************


final class Elmt_difucom2_reservada extends Elmt_difucom2 {
  private $url = null;

  public function __construct($url) {
    parent::__construct(self::inicia_eobd(), Refs::url("ptw/paragrafo/elmt/elmt_difucom2.html"));

    $this->url = $url;
  }

  public function config_usuario(IEfspax_xestor $e = null) {
    AElemento::config_usuario($e);

    $this->obxeto("crs")->config_usuario($e);

    //~ $this->obxeto("crs")->pon_url( Efs::url_paxina($e->id_site) . "/{$this->url}" );
  }

  private static function inicia_eobd() {
    $df2 = new Difucom2_obd();

    $df2->atr("id_crs"     )->valor = null;
    $df2->atr("rs_activado")->valor = 1;
    $df2->atr("rs_facebook")->valor = 1;
    $df2->atr("rs_twitter" )->valor = 1;
    $df2->atr("rs_linkedin")->valor = 1;
    $df2->atr("rs_whatsapp")->valor = 1;

    return $df2;
  }
}

//*******************************

final class Editor_edifucom2 extends Edita_elmt {
  const ptw = "ptw/paragrafo/elmt/editor_difucom2.html";

  public function __construct(Elmt_difucom2 $elmt) {
    parent::__construct(Ico::bdifucom, "Redes Sociales", self::ptw, $elmt);

    $crs_obd = $elmt->obxeto("crs")->crs_obd();

    $this->pon_obxeto( new CRS_editor($crs_obd) );

    $this->obxeto("crs_editor")->modo(false, self::ptw);
  }

  public function operacion_aceptar(Efs_admin $e) {
    $e = parent::operacion_aceptar($e);

    $this->obxeto("crs_editor")->operacion_aceptar($e);

    $this->elmt->obxeto("crs")->crs_obd( $this->obxeto("crs_editor")->crs_obd );

    $this->elmt->preparar_saida($e->ajax());

    return $e;
  }

  protected function __editor_style() {
    return new Editor_sdifucom2( $this->elmt->elmt_obd() );
  }
}

