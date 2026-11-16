<?php

final class VM_pcontrol_aux extends VentanaModal_2 {
  public  $pechar = true;
  public  $titulo = null;
  
  private $ficha  = null;

  public function __construct($id = "vm_pcontrol_aux") {
    parent::__construct($id, null, false, true);

    $this->css_capa_1 = "";
    $this->css_capa_2 = "trw-pcontrol-backdrop"; 

    $this->pon_boton(Panel_fs::__baceptar() , "onclick", "null", "null", false);
    $this->pon_boton(Panel_fs::__bcancelar(), null);

    $this->obxeto("bCancelar")->envia_ajax("onclick");
  }

  public function operacion_ficha(Epcontrol $e,
                                  IVM_pcontrol_ficha $ficha = null,
                                  $titulo = "",
                                  $js_ini = null,
                                  $js_baceptar_pre = "null") {
    $this->ficha  = $ficha;
    $this->titulo = $titulo;

    $this->pon_boton(Panel_fs::__baceptar() , "onclick", $js_baceptar_pre, "null", false);

    return $this->operacion_abrir($e, $js_ini);
  }

  public function operacion(EstadoHTTP $e) {
    if ($this->obxeto("bCancelar")->control_evento()) return $this->operacion_bCancelar($e);


    if ($this->obxeto("bAceptar" )->control_evento()) return $this->operacion_bAceptar ($e);


    return null;
  }

  public function operacion_bCancelar(Epcontrol $e) {
    $this->ficha->operacion_bCancelar($e);

    if (!$this->pechar) return $e;

    return $this->operacion_pechar($e);
  }

  public function operacion_bAceptar(Epcontrol $e) {
    if ( !$this->ficha->validar() ) {
      $this->ficha->preparar_saida($e->ajax());

      return $e;
    }

    $e = $this->ficha->operacion_bAceptar($e);

    if (!$this->pechar) return $e;

    return $this->operacion_pechar($e);
  }

  protected function html_propio() {
    if ($this->ficha == null) return "";

 /*    return "
  <fieldset>
    <legend><span class     ='pcontrol_vm_bpechar'
                  bpecha_id = 'findex;vm_pcontrol_aux;bCancelar'
                  onclick   = 'pcontrol_vm_bpechar(this)'>✕</span>{$this->titulo}</legend>
    <div class='trw_pcontrol_form_0'>

      " . $this->ficha->html() . "

      <div class='trw_pcontrol_form_1 trw_pcontrol_form_sep_0'>
        <div>" . $this->obxeto("bCancelar")->html() . "</div>
        <div>" . $this->obxeto("bAceptar")->html() . "</div>
      </div>
    </div>
  </fieldset>";
 */

  return "<div class='trw-pcontrol-modal'>
            <div class='trw-pcontrol-modal__header'>
              <span class= 'pcontrol_vm_bpechar'
                    bpecha_id = 'findex;vm_pcontrol_aux;bCancelar'
                    onclick   = 'pcontrol_vm_bpechar(this)'>✕</span>{$this->titulo}
              </div>
            <div class='trw-pcontrol-modal__content'>
                  " . $this->ficha->html() . "
            </div>
            <div class='trw-pcontrol-modal__footer'>
              <div class='trw-pcontrol-modal__buttons'>
                <div>" . $this->obxeto("bCancelar")->html() . "</div>
                <div>" . $this->obxeto("bAceptar")->html() . "</div>
              </div>
            </div>
          </div>
        ";
  }
}

