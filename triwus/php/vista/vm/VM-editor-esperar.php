<?php


class VM_editor_esperar extends Alert_2 {
  public function __construct() {
    parent::__construct("vm_esperar");

    $this->titulo  = "";
    $this->mensaxe = "<img src='imx/espera.gif' style='background-color: #ffffff; border-radius: 11%;' />";

    $this->css_capa_2 = "editor_vmesperar_capa_2";
  }

  protected function html_propio() {
    return "<div style='fon-weight: bold; padding: 3px;'>{$this->titulo}</div>
            <div style='text-align: center; padding: 27px 0 27px 0;'>{$this->mensaxe}</div>";
  }
}
