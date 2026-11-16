<?php

/** Ventana modal de advertencia.
 *
 * @package tilia\vista\componentes\ventanaModal
 */

class Alert extends VentanaModal {
  public function __construct($nome, $mensaxe = null, $titulo = "Tilia.Alert") {
    parent::__construct($nome, null);

    $this->mensaxe = $mensaxe;
    $this->titulo = $titulo;

    $this->pon_obxeto(new Button("bAceptar", "Aceptar"));
  }

  public function html_botonera() {
    return $this->obxeto("bAceptar")->html();
  }

  public function operacion(EstadoHTTP $e) {
    if ($this->obxeto("bAceptar")->control_evento()) {
      $this->close($e);

      return $e;
    }

    return null;
  }
}

?>
