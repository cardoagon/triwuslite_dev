<?php

/** Ventana modal de confirmacion.
 *
 * @package tilia\vista\componentes\ventanaModal
 */

class Confirm_2 extends VentanaModal_2 {
  public $titulo;
  public $mensaxe;

  protected $aceptado = null;

  public function __construct($nome, $mensaxe = null, $titulo = "Tilia.Confirmar") {
    parent::__construct($nome, null);

    $this->titulo  = $titulo;
    $this->mensaxe = $mensaxe;

    $this->pon_boton(new Button("bAceptar" , "Aceptar") , "onclick");
    $this->pon_boton(new Button("bCancelar", "Cancelar"), "onclick");
  }

  public function aceptado() {
    return $this->aceptado === true;
  }

  public function operacion_abrir(EstadoHTTP $e, $js_ini = null) {
    $this->aceptado = null;

    return parent::operacion_abrir($e, $callback);
  }

  public function operacion(EstadoHTTP $e) {
    if ($this->obxeto("bAceptar" )->control_evento()) return $this->operacion_aceptar($e);

    if ($this->obxeto("bCancelar")->control_evento()) return $this->operacion_pechar($e);

    return null;
  }

  protected function operacion_aceptar(EstadoHTTP $e) {
    $this->aceptado = true;

    return $this->operacion_pechar($e);
  }

  protected function html_propio() {
    return "<div style='fon-weight: bold; padding: 3px;'>{$this->titulo}</div>
            <div style='text-align: center; padding: 27px 0 27px 0;'>{$this->mensaxe}</div>
            <div style='text-align: center; padding: 27px 0 3px 0;'>" . $this->obxeto("bCancelar")->html() . "&nbsp;&nbsp;&nbsp;" . $this->obxeto("bAceptar")->html() . "</div>";
  }
}
