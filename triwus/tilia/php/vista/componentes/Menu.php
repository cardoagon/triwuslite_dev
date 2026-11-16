<?php

/** Componente Tilia Framework, que permite definir menus, como os menus dunha barra de ferramentas.
 *
 * @package tilia\vista\componentes
 */

class Menu extends Componente {

  public $evento_mostraropt = "onclick";

  public function __construct($nome, $width = null) {
    parent::__construct($nome);

    $this->clase_css("capa", "menu_capa");
    $this->clase_css("opcion_onmouseover", "menu_opcion_onmouseover");
    $this->clase_css("opcion_onmouseout", "menu_opcion_onmouseout");
    $this->clase_css("sep", "menu_separador");

    $capa = new Capa("capa", $width, $this->clase_css("capa"));

    $capa->visible = false;

    $this->pon_obxeto($capa);
  }

  public function ponEtiqueta(Control $etq) {
    $etq->nome = "etq";

    $this->pon_obxeto($etq);
  }

  public function ponOpcion(Control $opcionMenu, $msxConfirma = null) {
    $opcionMenu->msxConfirma = $msxConfirma;

    $opcionMenu->envia_SUBMIT("onclick", $msxConfirma);

    $opcionMenu->clase_css("default", $this->clase_css("opcion_onmouseout"));

    $this->pon_obxeto($opcionMenu);
  }

  public function ponSubmenu(Menu $menu) {
    $this->pon_obxeto($menu);
  }

  public function ponSeparador($txt = null) {
    $subnome = count($this->obxetos("sep"));

    if ($txt == null) $txt = "<hr class='" . $this->clase_css("sep") . "'>";

    $etq = new Etiqueta("sep", $txt);
    $etq->readonly = true;

    $this->pon_obxeto($etq, $subnome);
  }

  public function declara_css() {
    return array(Tilia::home() . "css/menu.css");
  }

  public function html():string {
    if (!$this->visible) return "";

    if (($e = $this->obxeto("etq")) == null) die ('Menu.html(), ($e = $this->obxeto("etq")) == null');

    $k = $this->obxeto("capa");

    $k->clase_css("default", $this->clase_css("capa"));

    $k->post($this->html_opcions());


    $id_capa = $k->nome_completo();

    $e->pon_eventos($this->evento_mostraropt, "capa_disparador_abrir(\"{$id_capa}\")");
    $e->pon_eventos("onmouseout", "capa_disparador_onmouseout(\"{$id_capa}\")");

    return self::__ptw($e->html(), $k->html());
  }

  protected function html_opcions() {
    if (($obxetos = $this->obxetos()) == null) return "";

    $s = "\n<table width=100%>";

    foreach ($obxetos as $k_o=>$o) {
      if ($o->nome == "etq") continue;
      if ($o->nome == "capa") continue;

      if (!$o->readonly) {
        $idtd = $o->nome_completo() . Escritor_html::cnome . "td";

        $css_onmouseover = $this->clase_css("opcion_onmouseover");
        $css_onmouseout = $this->clase_css("opcion_onmouseout");

        $actions = " onclick='__menu_onclick(this, \"{$o->msxConfirma}\")' onmouseover='__menu_onmouseoverout(this, \"{$css_onmouseover}\")' onmouseout='__menu_onmouseoverout(this, \"{$css_onmouseout}\")'";
      }

      $s .= "\n<tr><td id='{$idtd}' width=99%{$actions}>" . $o->html() . "</td></tr>";
    }

    return "{$s}\n</table>";
  }

  private static function __ptw($etq, $k) {
    return "<div>{$etq}</div><div>{$k}</div>";
  }
}

