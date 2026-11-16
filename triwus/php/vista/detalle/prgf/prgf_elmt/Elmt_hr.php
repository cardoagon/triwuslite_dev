<?php

final class Elmt_hr extends AElemento {
  public $align  = "center";
  public $borde  = false;
  public $color  = "#333333";
  public $margin = 11;
  public $pwidth = 97;
  public $size   = 3;

  public function __construct(HR_obd $pe_obd) {
    parent::__construct($pe_obd);

    $this->post_style( $pe_obd->style_obd() );
  }

  public function elmt_obd() {
    $e = new HR_obd();

    $e->atr("id_elmt")->valor = $this->id_elmt;

    return $this->obd_base($e);
  }

  public function editor() {
    return new Editor_ehr($this);
  }

  public function post_style(Style_obd $s) {
    if ($s->atr("css")->valor == null) return;

    $a_props = $s->a_props();

    //~ echo "<pre>" . print_r($a_props, true) . "</pre>";

    $this->align  = $a_props["align"] ?? null;
    $this->size   = $a_props["height"] ?? null;
    $this->pwidth = $a_props["width"] ?? null;
    $this->color  = $a_props["background-color"] ?? null;
    $this->margin = $a_props["mtop"] ?? null;
    $this->borde  = $a_props["border-radius"] ?? null;
  }
  
  public function html_illa($html_propio = null) { //* sobreescribe método de AElemento
    if (!$this->visible) return "";
    

    return $this->html_propio();
  }

  public function html_propio() {
    $illa = $this->obxeto("illa");

    $btnr_json = "null";
    if ($btnr = $this->obxeto("btnr") != null) $btnr_json = $this->obxeto("btnr")->html_json();
    
    $ohr = array("i" => $illa->nome_completo(), 
                 "a" => $this->align,
                 "b" => $this->borde,
                 "m" => $this->margin,
                 "h" => $this->size,
                 "w" => $this->pwidth,
                 "c" => $this->color
                );
    
    return $this->html_msx_0() . "
<script type='text/javascript'>
  var ohr = " . json_encode( $ohr ) . ";
  var ob  = {$btnr_json};
  
  elmt_hr_ini(ohr, ob);
</script>";
  }

  public function preparar_saida(Ajax $a = null) {
    parent::preparar_saida($a);

    if ($a == null) return;

    $a->pon_ok(true, $this->js_callback());
  }

  public function js_callback() {
    $btnr_json = "null";
    if ($btnr = $this->obxeto("btnr") != null) $btnr_json = $this->obxeto("btnr")->html_json();
    
    $ohr = array("i" => $this->obxeto("illa")->nome_completo(), 
                 "a" => $this->align,
                 "b" => $this->borde,
                 "m" => $this->margin,
                 "h" => $this->size,
                 "w" => $this->pwidth,
                 "c" => $this->color
                );

    return "elmt_hr_ini(" . json_encode( $ohr ) . ", {$btnr_json})";
  }

  protected function html_msx_0() {
    return "
          <div style='color: #333333; line-height: 161%; padding: 11px 7px;'>
            Por algún motivo no se a completado la carga del separador, pincha el <b>botón recargar</b> para intentarlo de nuevo.
            <br /><br />
            <center>" . $this->obxeto("brecargar")->html() . "</center>
          </div>";
  }
}

//-------------------------------

final class Editor_ehr extends Edita_elmt {

  public function __construct(Elmt_hr $elmt) {
    parent::__construct(Ico::bhr, "Editar separador", "ptw/paragrafo/elmt/editor_hr.html", $elmt);
  }

  public function operacion_aceptar(Efs_admin $e) {
    $style = $this->obxeto("cstyle")->style_obd();

    $this->elmt->post_style($style);

    return parent::operacion_aceptar($e);
  }

  protected function __editor_style() {
    return new Editor_shr($this->elmt->elmt_obd());
  }
}
