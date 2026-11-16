<?php

abstract class Edita_elmt extends Editor_btnr_oeste {
  const ptw_crs = "ptw/paragrafo/elmt/editor_elmt_rs.html";

  protected $elmt;

  protected $iw;
  protected $ih;

  protected $mw;
  protected $mh;

  public function __construct($icono, $titulo, $ptw, AElemento $elmt = null) {
    parent::__construct($icono, $titulo, $ptw);

    $this->elmt = $elmt;

    $this->pon_obxeto(self::__text());
    $this->pon_obxeto(self::__tadesc());
    $this->pon_obxeto($this->__editor_style());

    if ($elmt == null) return;

    $this->ancla_baceptar = $elmt->__ancla();

    $this->obxeto("titulo")->post($elmt->enome);
    $this->obxeto("tadesc")->post($elmt->enotas);
  }

  abstract protected function __editor_style();

  public function operacion(EstadoHTTP $e) {
    if (($e_aux = $this->obxeto("cstyle")->operacion($e)) != null) return $e_aux;

    if (($crs = $this->obxeto("crs_editor")) != null) if (($eaux = $crs->operacion($e)) != null) return $eaux;


    return parent::operacion($e);
  }

  public function operacion_aceptar(Efs_admin $e) {
    $this->elmt->enome  = $this->obxeto("titulo")->valor();
    $this->elmt->enotas = trim($this->obxeto("tadesc")->valor());

    $this->elmt->modificado = true;

    if (($cstyle = $this->obxeto("cstyle")) != null) {
      if (($s_obd = $cstyle->style_obd()) != null) $this->elmt->ecss = $s_obd->atr("css")->valor;
    }

    $this->elmt->preparar_saida($e->ajax());

    return $e;
  }

  public function validar():?string {
    return null;
  }

  private static function __text() {
    $t = Panel_fs::__text("titulo", 33, 333);

    return $t;
  }

  private static function __tadesc() {
    $t = Panel_fs::__text("tadesc", 33, 333);

    return $t;
  }
}

//------------------------------------

final class Edita_elmt_nulo extends Edita_elmt {

  public function __construct() {
    parent::__construct(null, null, null, null);
  }

  public function open() {
    $this->close();
  }

  protected function __editor_style() {
    return new Editor_style_nulo();
  }
}


