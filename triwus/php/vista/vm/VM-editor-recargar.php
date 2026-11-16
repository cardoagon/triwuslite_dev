<?php


class VM_editor_recargar extends Confirm_2 {
  public function __construct() {
    parent::__construct("vm_recargar");

    $this->titulo  = "Atención !!";
    $this->mensaxe = "Si pulsas [Aceptar] perderás los cambios no guardados.<br /><br />\n\n<b>¿ Deseas continuar ?</b><br />\n";

    $this->pon_boton(Panel_fs::__baceptar());
    $this->pon_boton(Panel_fs::__bcancelar(), "onclick");

    $this->css_capa_2 = "trw-pcontrol-backdrop";
  }

  protected function operacion_aceptar(EstadoHTTP $e) {
    return $e->__crear_instancia($e->id_oms(), $e->id_paxina());
  }

  protected function html_propio() {
    /* return "<div style='fon-weight: bold; padding: 3px;'>{$this->titulo}</div>
            <div style='text-align: center; padding: 27px 0 27px 0;'>{$this->mensaxe}</div>
            <div style='text-align: center; padding: 27px 0 3px 0;'>" . $this->obxeto("bCancelar")->html() . "&nbsp;&nbsp;&nbsp;" . $this->obxeto("bAceptar")->html() . "</div>"; */


      return "<div class='trw-pcontrol-modal'>
                <div class='trw-pcontrol-modal__header'>
                  <span class= 'pcontrol_vm_bpechar'
                        bpecha_id = 'findex;vm_recargar;bCancelar'
                        onclick= 'pcontrol_vm_bpechar(this)'>✕</span>{$this->titulo}
                  </div>
                <div class='trw-pcontrol-modal__content'>
                    {$this->mensaxe}
                </div>
                <div class='trw-pcontrol-modal__footer'>
                  <div class='trw-pcontrol-modal__buttons'>
                    <div>" . $this->obxeto("bCancelar")->html() . "</div>
                    <div>" . $this->obxeto("bAceptar")->html() . "</div>
                  </div>
                </div>
              </div>";
  }

}
