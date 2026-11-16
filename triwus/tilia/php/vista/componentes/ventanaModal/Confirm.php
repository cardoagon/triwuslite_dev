<?php

/** Ventana modal de confirmacion.
 *
 * @package tilia\vista\componentes\ventanaModal
 */

class Confirm extends VentanaModal {
  protected $aceptado = null;

  public function __construct($nome, $mensaxe = null, $titulo = "Tilia.Confirm") {
    parent::__construct($nome, null);

    $this->titulo = $titulo;
    $this->mensaxe = $mensaxe;

    $this->pon_obxeto(new Button("bAceptar", "Aceptar"));
    $this->pon_obxeto(new Button("bCancelar", "Cancelar"));
  }

  public function aceptado() {
    return $this->aceptado === true;
  }

  public function open() {
    $this->aceptado = null;

    $this->visible = true;
  }

  public function html_botonera() {
    return $this->obxeto("bCancelar")->html() . "&nbsp;&nbsp;&nbsp;" . $this->obxeto("bAceptar")->html();
  }

  public function operacion(EstadoHTTP $e) {
    if ($this->obxeto("bAceptar")->control_evento()) return $this->operacion_aceptar($e);

    if ($this->obxeto("bCancelar")->control_evento()) return $this->operacion_cancelar($e);

    return null;
  }

  protected function operacion_aceptar(EstadoHTTP $e) {
    $this->aceptado = true;


    $this->close();

    return $e;
  }

  protected function operacion_cancelar(EstadoHTTP $e) {
    $this->close();

    return $e;
  }
}

?>
