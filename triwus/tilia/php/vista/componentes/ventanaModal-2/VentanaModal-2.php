<?php

/** Permite definir ventanas modales.
 *
 * @package tilia\vista\componentes\ventanaModal
 */

abstract class VentanaModal_2 extends Componente {
  public $css_capa_1 = "tilia_vm_capa_1";
  public $css_capa_2 = "tilia_vm_capa_2";

  public $modal = true;

  protected function __construct($id, $ptw, $estatica = true, $modal = true) {
    parent::__construct($id, $ptw);

    $this->modal = $modal;

    $this->pon_obxeto(new Hidden("display" , "none"));
    $this->pon_obxeto(new Hidden("estatica", ($estatica)?"1":"0"));
  }

  public function declara_css() {
    return array(Tilia::home(). "css/ventanaModal-2.css");
  }

  public function declara_js() {
    return array(Tilia::home() . "js/ventanaModal-2.js");
  }

  public function aberta() {
    return $this->obxeto("display")->valor() != "none";
  }

  public function estatica() {
    return $this->obxeto("estatica")->valor() != "0";
  }

  public function operacion(EstadoHTTP $e) {
    if (!$this->obxeto("illa")->control_evento()) return null;

    if ($e->evento()->tipo() != "tilia-vm-abrir") return null;

    return $this->operacion_abrir($e);
  }

  public function operacion_abrir(EstadoHTTP $e, $js_ini = null) {
    $this->obxeto("display")->post("inline");

    if ($e->evento()->ajax()) {
      $this->preparar_saida();

      $e->ajax()->pon($this->obxeto("illa"), true, $js_ini);
    }


    return $e;
  }

  public function operacion_pechar(EstadoHTTP $e) {
    if (!$this->aberta()) return $e;

    $this->obxeto("display")->post("none");

    if ($e->evento()->ajax()) $this->preparar_saida( $e->ajax() );


    return $e;
  }

  protected function pon_boton(Control $c, $evento = null, $js_pre_callback = "null", $js_post_callback = "null", $pechar = true) {
    if ($evento != null) {
      $pechar = ($pechar)?"1":"0";

      $c->pon_eventos($evento, "tilia_vm_evento('{$this->nome}', this, {$js_pre_callback}, {$js_post_callback}, {$pechar})");
      //~ $c->envia_SUBMIT($evento);
    }

    $this->pon_obxeto($c);
  }

  public function readonly($b = true) {}

  public function preparar_saida(Ajax $a = null) {
    //* xeramos a saida.
    $display = $this->obxeto("display")->valor();

    $illa = $this->obxeto("illa");


    $illa->visible = $this->aberta();

   //~ $css_modal = ($this->modal)?"height: 100%; width: 100%;":"";
   $css_modal = "";


    $html = "<div name='tilia-vm-div-1' class='{$this->css_capa_1}' style='{$css_modal}'>" .
              $this->obxeto("display")->html() .
              $this->obxeto("estatica")->html() . "
             <div name='tilia-vm-div-2' class='{$this->css_capa_2}'>";

        if ($this->estatica()) $html .= $this->html_propio();
    elseif ($this->aberta()  ) $html .= $this->html_propio();

    $html .= "</div></div>" . $this->html_script();

    $illa->post( $html );


    if ($a == null) return;

    $a->pon( $illa );

    $illa->post(null);
  }

  protected function html_propio() {
    return $this->html00();
  }

  protected function html_script() {
    $s = $this->nome;
    $i = $this->nome_completo();

    return "<script> tilia_vm_inicia('{$s}', '{$i}'); </script>";
  }
}

