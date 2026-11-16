<?php

/** Permite definir ventanas modales.
 *
 * @package tilia\vista\componentes\ventanaModal
 */

abstract class VentanaModal extends Componente {
  public $modal = true;

  public $mensaxe = "";

  public $titulo = "Tilia";
  public $titulo_height = 39;

  public $height = null;
  public $width = 555;

  protected $modal_color     = "#ffffff";
  protected $modal_opacidade = ".55";

  protected function __construct($nome, $ptw, $modal = true) {
    parent::__construct($nome, $ptw);

    $this->modal = $modal;

    $this->visible = false;

    $this->clase_css("capa", VM_html::css_capa);
    $this->clase_css("titulo", VM_html::css_titulo);
    $this->clase_css("mensaxe", VM_html::css_mensaxe);
    $this->clase_css("botonera", VM_html::css_botonera);

    $this->inicia_capa();
  }

  public function declara_css() {
    return array(Tilia::home(). "css/ventanaModal.css");
  }

  public function declara_js() {
    return array(Tilia::home() . "js/ventanaModal.js");
  }

  public function open() {
    $this->visible = true;
  }

  public function close() {
    $this->visible = false;
  }

  public function readonly($b = true) {}

  public function html():string {
    if (!$this->visible) return "";

    if (($ptw = $this->html00()) == null) $ptw = $this->mensaxe;

$html = "
<table width=100% height=100% border=0 cellspacing=0 cellpadding=0>
  <tr height='10%'><td class='" . $this->clase_css("titulo") . "'>" . VM_html::titulo($this) . "</td></tr>
  <tr height='*' valign='top'><td class='" . $this->clase_css("mensaxe") . "'>" . VM_html::mensaxe($this, $ptw) . "</td></tr>
  <tr height='1%'><td class='" . $this->clase_css("botonera") . "'>" . VM_html::botonera($this) . "</td></tr>
</table>";

    $this->obxeto("capa")->width = $this->width;
    $this->obxeto("capa")->height = $this->height;
    $this->obxeto("capa")->clase_css("default", $this->clase_css("capa"));

    $this->obxeto("capa")->post($html);

    return VM_html::modal($this) . $this->obxeto("capa")->html() . VM_html::script($this);
  }

  protected function inicia_capa() {
    $c = new Capa("capa", $this->width, $this->clase_css("capa"));

    $c->sempre_visible = true;

    $c->height = $this->height;

    $this->pon_obxeto($c);
  }
}

/** Escribe a saida HTML dunha VentanaModal
 *
 * @package tilia\vista\componentes\ventanaModal
 */

final class VM_html extends VentanaModal {
  const css_capa = "vm_capa";
  const css_titulo = "vm_titulo";
  const css_mensaxe = "vm_mensaxe";
  const css_botonera = "vm_botonera";

  public static function titulo(VentanaModal $v) {
    $id_titulo = $v->obxeto("capa")->nome_completo() . self::csubnome . "titulo";

    return "<div id='{$id_titulo}'
                  style='width:100%; height: 100%; position:relative; cursor: move;'
                  onmousedown = 'vm_mousedown(event, this, {$v->titulo_height}, {$v->width})'>{$v->titulo}</div>";
  }

  public static function mensaxe(VentanaModal $v, $mensaxe) {
    return $mensaxe;
  }

  public static function modal(VentanaModal $v) {
    if (!$v->modal) return "";

    return "<div id='" . $v->obxeto("capa")->nome_completo() . "modal' style='opacity: {$v->modal_opacidade}; background-color: {$v->modal_color};'></div>";
  }

  public static function botonera(VentanaModal $v) {
    return $v->html_botonera();
  }

  public static function script(VentanaModal $v) {
    $nc = $v->obxeto("capa")->nome_completo();

    return "
      <script>
        vm_modal('{$nc}modal');
        vm_posicionar('" . $v->obxeto("capa")->nome_completo() . "');
      </script>";
  }
}


