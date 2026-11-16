<?php

abstract class Subms extends    Componente
                     implements IXestor_ms {

  public $tipo;
  public $mobil;
  public $prof;
  public $ct_oms;
  
  public $op_cero;

  private $__disparador = false;

  protected function __construct(FGS_usuario $u, IMenuSite_opcion $oms, IMenuSite_paxina $pax, Config_obd $c, $edicion, $prof) {
    parent::__construct("subms");

    $this->tipo  = $c->atr("ms_tipo")->valor;
    $this->mobil = $c->mobil();
    $this->prof  = $prof;

    $id_idioma = $pax->atr("id_idioma")->valor;

    $_suboms = $this->_suboms($oms, $id_idioma);

    Xestor_ms::inicia($this, $_suboms, $u, $c, $id_idioma, $edicion, $prof);
  }

  abstract protected function __disparador(Control $c);

  public function id_capa() {
    if (($c = $this->obxeto("capa")) == null) return null;

    return $this->obxeto("capa")->nome_completo();
  }

  public function config_disparador(Control $c) {
    if ($this->__disparador) return;

    $this->__disparador = $this->__disparador($c);
  }

  public static function inicia(FGS_usuario $u, IMenuSite_opcion $oms, IMenuSite_paxina $pax, Config_obd $c, $edicion, $prof) {

    switch ($c->atr("ms_tipo")->valor) {
      case "h" : return new Subms_h ($u, $oms, $pax, $c, $edicion, $prof);
      case "h2": return new Subms_h2($u, $oms, $pax, $c, $edicion, $prof);
      case "3" : return new Subms_3 ($u, $oms, $pax, $c, $edicion, $prof);
      case "4" : return new Subms_4 ($u, $oms, $pax, $c, $edicion, $prof);
      case "5" : return new Subms_5 ($u, $oms, $pax, $c, $edicion, $prof);
      case "6" : return new Subms_6 ($u, $oms, $pax, $c, $edicion, $prof);
      case "v" : return new Subms_v ($u, $oms, $pax, $c, $edicion, $prof);
    }

    die("Subms.inicia(), ms_tipo desconhecido.");
  }

  public function selecionar($b = true) {
    //* este método propaga a operacion selecionar() ate a raiz.

    return $this->pai->selecionar($b);
  }

  public function buscar($id_oms) {
    return Xestor_ms::buscar($this, $id_oms);
  }

  public function operacion(EstadoHTTP $e) {
    return Xestor_ms::__operacion($this, $e);
  }

  public function html():string {
    //~ if (!$this->visible) return "";

    $obxetos = $this->obxetos("oms");

    if (count($obxetos) == 0) return "";

    $html = null;

    foreach($obxetos as $o) $html .= $o->html();

    if ($html == null) return "";

    return "<div class='trw_ms'>{$html}</div>";
  }

  protected function _suboms(IMenuSite_opcion $oms, $id_idioma) {
    return $oms->a_suboms(null, $id_idioma);
  }
}

//********************************************

final class Subms_h extends Subms {
  protected function __construct(FGS_usuario $u, IMenuSite_opcion $oms, IMenuSite_paxina $pax, Config_obd $c, $edicion, $prof) {
    parent::__construct($u, $oms, $pax, $c, $edicion, $prof);

    $this->pon_obxeto(new Capa("capa", null, "subms_capa"));

    $this->obxeto("capa")->visible     = false;
    $this->obxeto("capa")->onmouseout  = "trw_menu_aberto.pechar = true";
    $this->obxeto("capa")->onmouseover = "trw_menu_aberto.pechar = false";

  }

  public function html():string {
    if (($html = parent::html()) == "") return "";

    $this->obxeto("capa")->post($html);

    return $this->obxeto("capa")->html();
  }

  protected function __disparador(Control $c) {
    if ( $this->prof > 0 ) return true;

    if (($id_capa = $this->id_capa()) == null) return true;

    $c->pon_eventos("onmouseover", "trw_menu_abrir(\"{$id_capa}\")");

    return true;
  }
}

//********************************************

final class Subms_h2 extends Subms {
  protected function __construct(FGS_usuario $u, IMenuSite_opcion $oms, IMenuSite_paxina $pax, Config_obd $c, $edicion, $prof) {
    parent::__construct($u, $oms, $pax, $c, $edicion, $prof);

    $this->pon_obxeto(new Div("capa", "subms_capa_h2"));

    //~ $this->obxeto("capa")->visible     = false;
    $this->obxeto("capa")->clase_css  ("default"    , "subms_capa_h2");

    $this->obxeto("capa")->onmouseout  = "trw_menu_aberto_h2[{$prof}].pechar = true";
    $this->obxeto("capa")->onmouseover = "trw_menu_aberto_h2[{$prof}].pechar = false";
  }

  public function html():string {
    if (($html = parent::html()) == "") return "";

    if ($this->prof > 2) return $html;

    $this->obxeto("capa")->post($html);

    return $this->obxeto("capa")->html();
  }

  protected function __disparador(Control $c) {
    if (($id_capa = $this->id_capa()) == null) return true;

    $c->pon_eventos("onmouseover", "trw_submenu_h2_toggle(\"{$id_capa}\", {$this->prof})");

    return true;
  }
}

//********************************************

final class Subms_v extends Subms {
  protected function __construct(FGS_usuario $u, IMenuSite_opcion $oms, IMenuSite_paxina $pax, Config_obd $c, $edicion, $prof) {
    parent::__construct($u, $oms, $pax, $c, $edicion, $prof);

    $this->pon_obxeto(new Capa("capa", null, "subms_capa_v"));

    $this->obxeto("capa")->autopechar = 0;

    $this->obxeto("capa")->zindex     = "initial";
    $this->obxeto("capa")->position   = "initial";
  }

  public function html():string {
    if (($html = parent::html()) == "") return "";

    $this->obxeto("capa")->visible = $this->pai->readonly; //* abrimos a capa se o pai está seleccionado.

    $this->obxeto("capa")->post($html);

    return $this->obxeto("capa")->html();
  }


  protected function __disparador(Control $c) {
    return true;
  }

}

//********************************************

final class Subms_3 extends Subms {

  protected function __construct(FGS_usuario $u, IMenuSite_opcion $oms, IMenuSite_paxina $pax, Config_obd $c, $edicion, $prof) {
    parent::__construct($u, $oms, $pax, $c, $edicion, $prof);

    $this->visible = false;
  }

  public function html():string {
    //~ if (!$this->visible) return "";

    $obxetos = $this->obxetos("oms");

    if (count($obxetos) == 0) return "";

    $html = null;
    foreach($obxetos as $o) $html[] = $o->html();

    $html['visible'] = $this->visible;

    return $html;
  }

  protected function __disparador(Control $c) {
    $c->pon_eventos("onmouseover", "trw_ms3_over(this)");

    return true;
  }
}

//********************************************

final class Subms_4 extends Subms {

  protected function __construct(FGS_usuario $u, IMenuSite_opcion $oms, IMenuSite_paxina $pax, Config_obd $c, $edicion, $prof) {
    parent::__construct($u, $oms, $pax, $c, $edicion, $prof);
  }

  protected function __disparador(Control $c) {
    return true;
  }
}

//********************************************

final class Subms_5 extends Subms {
  protected function __construct(FGS_usuario $u, IMenuSite_opcion $oms, IMenuSite_paxina $pax, Config_obd $c, $edicion, $prof) {
    parent::__construct($u, $oms, $pax, $c, $edicion, $prof);

    //~ echo "<pre>" . print_r($oms->a_resumo(), true) . "</pre>";

    if ($oms->atr("pai")->valor != null) return;

    $this->pon_obxeto(new Capa("capa", null, "subms_capa"));

    $this->obxeto("capa")->position = "fixed";
    $this->obxeto("capa")->visible = false;
  }

  public function html():string {
    if (($html = $this->html_aux()) == "") return "";

    if (($capa = $this->obxeto("capa")) == null) return $html;

    $id_capa = $this->obxeto("capa")->nome_completo();

    $capa->post($html);

    return $capa->html();
  }

  protected function __disparador(Control $c) {
    if (($id_capa = $this->id_capa()) == null) return true;

    $c->pon_eventos("onmouseover", "trw_ms5_abrir(\"{$id_capa}\")");

    return true;
  }

  private function html_aux() {
    if (!$this->visible) return "";

    $obxetos = $this->obxetos("oms");

    if (count($obxetos) == 0) return "";

    $html = "";

    foreach($obxetos as $o) $html .= $o->html();

    if ($html == null) return "";

    return $html;
  }
}

//********************************************

final class Subms_6 extends Subms {
  protected function __construct(FGS_usuario $u, IMenuSite_opcion $oms, IMenuSite_paxina $pax, Config_obd $c, $edicion, $prof) {
    parent::__construct($u, $oms, $pax, $c, $edicion, $prof);

    //~ echo "<pre>" . print_r($oms->a_resumo(), true) . "</pre>";

    if ($oms->atr("pai")->valor != null) return;

    $this->pon_obxeto(new Capa("capa", null, "subms_capa"));

    $this->obxeto("capa")->position = "fixed";
    $this->obxeto("capa")->visible = false;
  }

  public function html():string {
    if (($html = $this->html_aux()) == "") return "";

    if (($capa = $this->obxeto("capa")) == null) return $html;

    $id_capa = $this->obxeto("capa")->nome_completo();

    $capa->post($html);

    return $capa->html();
  }

  protected function __disparador(Control $c) {
    if (($id_capa = $this->id_capa()) == null) return true;

    $c->pon_eventos("onmouseover", "trw_ms6_abrir(\"{$id_capa}\")");

    return true;
  }

  private function html_aux() {
    if (!$this->visible) return "";

    $obxetos = $this->obxetos("oms");

    if (count($obxetos) == 0) return "";

    $html = "";

    foreach($obxetos as $o) $html .= $o->html();

    if ($html == null) return "";

    return $html;
  }
}

