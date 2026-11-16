<?php

/** Ventana modal de advertencia.
 *
 * @package tilia\vista\componentes\ventanaModal
 */

class Alert_2 extends VentanaModal_2 {
  public $titulo;
  public $mensaxe;

  public function __construct($nome, $mensaxe = null, $titulo = "Tilia.Alerta") {
    parent::__construct($nome, null);

    $this->mensaxe = $mensaxe;
    $this->titulo  = $titulo;

    $this->pon_boton(new Button("bAceptar", "Aceptar"), "onclick");
  }

  public function operacion(EstadoHTTP $e) {
    if ($this->obxeto("bAceptar")->control_evento()) return $this->operacion_pechar($e);

    return null;
  }

  protected function html_propio() {
    return "<div style='fon-weight: bold; padding: 3px;'>{$this->titulo}</div>
            <div style='text-align: center; padding: 27px 0 27px 0;'>{$this->mensaxe}</div>
            <div style='text-align: center; padding: 27px 0 3px 0;'>" . $this->obxeto("bAceptar")->html() . "</div>";
  }
}

