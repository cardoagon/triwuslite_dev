<?php

/*************************************************

    Triwus Framework v.0

    VM-editor-confirmar.php

    Author: Carlos Domingo Arias González

    Copyright (C) 2011

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation version 3.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.

*************************************************/


final class VM_editor_confirmar extends Confirm_2 {
  protected $eseg = null;

  public function __construct(Efs $eseg = null) {
    parent::__construct("vm_confirm");

    $this->eseg = $eseg;

    $this->titulo  = "Control de Cambios";
    $this->mensaxe = self::mensaxe();


    $brexeitar = Panel_fs::__bimage("bRexeitar", Ico::battention, "&laquo;Perder&raquo; los cambios y continuar la operaci&oacute;n" , true, "&nbsp;Perder los cambios");
    $bAceptar  = Panel_fs::__bimage("bAceptar" , Ico::bsave3    , "&laquo;Guardar&raquo; los cambios y continuar la operaci&oacute;n", true, "&nbsp;Guardar");
    $bCancelar = Panel_fs::__bcancelar();

    $this->pon_boton($brexeitar);
    $this->pon_boton($bAceptar);
    $this->pon_boton($bCancelar, "onclick");


    $this->obxeto("display")->post("inline");

    $this->css_capa_2 = "trw-pcontrol-backdrop";
  }

  public function operacion(EstadoHTTP $e) {
    if ($this->obxeto("bRexeitar")->control_evento()) return $this->operacion_rexeitar($e);

    return parent::operacion($e);
  }

  public function operacion_rexeitar(Epaxina_edit $e) {
     //~ parent::operacion_aceptar($e);
     return $this->eseg;
  }

  public function operacion_aceptar(EstadoHTTP $e) {
    $e->op_gardar();

    return $this->operacion_rexeitar($e);
  }

  protected function html_propio() {
    /* return "<div style='fon-weight: bold; padding: 3px;'>{$this->titulo}</div>
            <div style='text-align: center; padding: 27px 0 27px 0;'>{$this->mensaxe}</div>
            <div style='text-align: center; padding: 27px 0 3px 0;'>" . $this->obxeto("bCancelar")->html() . "&nbsp;&nbsp;&nbsp;" . $this->obxeto("bRexeitar")->html() . "&nbsp;&nbsp;&nbsp;" . $this->obxeto("bAceptar")->html() . "</div>"; */

       return "<div class='trw-pcontrol-modal'>
                <div class='trw-pcontrol-modal__header'>
                <span class='pcontrol_vm_bpechar'
                      bpecha_id='findex;vm_confirm;bCancelar'
                      onclick='pcontrol_vm_bpechar(this)'>✕</span>{$this->titulo}
                </div>
                <div class='trw-pcontrol-modal__content'>
                  {$this->mensaxe}
                </div>
                <div class='trw-pcontrol-modal__footer'>
                  <div class='trw-pcontrol-modal__buttons'>
                    <div>" . $this->obxeto("bCancelar")->html() . "</div>
                    <div>" . $this->obxeto("bRexeitar")->html() . "</div>
                    <div>" . $this->obxeto("bAceptar")->html() . "</div>
                  </div>
                </div>
              </div>";
  }


  private static function mensaxe() {
    return "Atenci&oacute;n, cambios no confirmados en la p&aacute;gina.";

  }

  private static function titulo() {
    return "<h2>&nbsp;Control de Cambios</h2>";

  }
}
