<?php

final class Elmt_enlace extends AElemento {
  public $enlace = null;
  
  public function __construct(Iframe_obd $pe_obd) {
    parent::__construct($pe_obd);

    $this->enlace = $pe_obd->atr("url_pre" )->valor;
  }

  public function elmt_obd() {
    $o = new Iframe_obd();

    $o->atr("url"     )->valor = null;
    $o->atr("url_pre" )->valor = $this->enlace;

    return $this->obd_base($o);
  }

  public function editor() {
    return new Editor_eenlace($this);
  }

  protected function html_propio() {
    if (($html = $this->enlace) == null) if ($this->pcontrol) $html = $this->html_baleiro();
    
    
    return $html;
  }
  
  private function html_baleiro() {
    return "<h2 style='color:rgb(204, 204, 204)'><center>Enlace vacío</center></h2>";
  }
}

//-------------------------------

class Editor_eenlace extends Edita_elmt {
  const ptw = "ptw/paragrafo/elmt/editor_enlace.html";

  public function __construct(Elmt_enlace $elmt) {
    parent::__construct(Ico::blink, "Editar enlace", self::ptw, $elmt);

    $this->pon_obxeto( self::__txt_paste($elmt->enlace) );
  }

  public function operacion_aceptar(Efs_admin $e) {
    $this->elmt->enlace = trim($this->obxeto("txt_paste")->valor());


    return parent::operacion_aceptar($e);
  }

  protected function __editor_style() {
    return new Editor_style_nulo();
  }

  private static function __txt_paste( $enlace ) {
    $ta = Panel_fs::__textarea("txt_paste");

    $ta->post( $enlace );

    $ta->style("default", "width: 100%; min-width: 39em; min-height: 23em; font-size: 1em;");

    $ta->title       = Msx::elmt_enlace_txtpaste;
    /* $ta->placeholder = Msx::elmt_enlace_txtpaste; */

    return $ta;
  }
}

