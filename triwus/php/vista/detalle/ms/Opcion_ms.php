<?php

class Opcion_ms extends Componente {
  public $a_grupo = null;
  public $tipo    = null;
  public $prof    = 0;

  public function __construct(FGS_usuario $u, IMenuSite_opcion $oms, IMenuSite_paxina $pax, Config_obd $c, $edicion, $prof = 0) {
    parent::__construct("oms");

    //~ $this->a_grupo = $oms->a_grupo();
    $this->a_grupo = Opcion_ms_obd::explode_grupo( $oms->atr("grupo")->valor );

    $this->tipo = $c->atr("ms_tipo")->valor;
    $this->prof = $prof;

    $this->pon_obxeto(new Param("css", "ms_opcion"));

    $this->pon_obxeto(new Link_pax($pax, $edicion));
    $this->pon_obxeto(Subms::inicia($u, $oms, $pax, $c, $edicion, $prof));

    $this->clase_css("default"    , "ms_opcion");
    $this->clase_css("subms_marco", "subms_marco");
    $this->clase_css("selec"      , "ms_opcion_selec");

    if ($edicion != 2) $this->visible = Paxina_obd::publicada_imspax($pax);

//~ echo $pax->atr("estado")->valor. "::{$this->visible}::$edicion<br>";
  }

  public function html():string {
    if (!$this->visible) return "";

    $css    = ($this->readonly)?$this->clase_css("selec"):$this->clase_css("default");

    $subms  = $this->obxeto("subms")->html();

    $oloms  = $this->obxeto("loms");


    if ($subms != "") {
      $this->obxeto("subms")->config_disparador($oloms);

      $v = $this->__v();
    }
    else
      $v = $this->__v("&nbsp;");

    return $this->__ptw($css, $oloms, $subms, $v);
  }

  public function buscar($id_oms) {
    if ($this->id_oms() == $id_oms) return $this;

    return $this->obxeto("subms")->buscar($id_oms);
  }

  public function id_oms() {
    return $this->obxeto("loms")->id_oms;
  }

  public function selecionar($b = true) {
    $this->readonly = $b;

    //~ $this->obxeto("css")->post($css); //* purga php8.

    $this->obxeto("subms")->visible = $b;
  }

  public function operacion(EstadoHTTP $e) {
    if (($e_aux = $this->obxeto("subms")->operacion($e)) != null) return $e_aux;

    if (($e_aux = $this->obxeto("loms")->operacion($e)) != null) return $e_aux;

    return null;
  }

  protected function __ptw($css, Link_pax $oloms, $subms, $v = "") {
    $href = $oloms->href;
    $loms = $oloms->html();

    if ($this->tipo == "3") {
      $html = "<span class='{$css}'>{$loms}</span>";

      if ($href != null) $html = "<a href='{$href}'>{$html}</a>";

      return array($html, $subms);
    }

    if ($this->tipo == "4") {
      $html = "<div class='{$css}'>{$loms}</div><div>{$subms}</div>";

      if ($href != null) $html = "<a href='{$href}'>{$html}</a>";

      return "<div class='trw_ms_td'>{$html}</div>";
    }

    if ($this->tipo == "v") {
      $html = "<div class='{$css}'>{$loms}</div>";

      if ($href != null) $html = "<a href='{$href}'>{$html}</a>";

      return "<div class='trw_ms_td'>{$v}{$html}<div><center>{$subms}</center></div></div>";
    }

    if ($this->tipo == "h2") {
      $html = "<div class='{$css}'>{$loms}</div>";

      if ($href != null) $html = "<a href='{$href}'>{$html}</a>";

      return "<div>{$v}{$html}<div>{$subms}</div></div>";
    }


    $html = "<div class='{$css}'>{$loms}</div>";

    if ($href != null) $html = "<a href='{$href}'>{$html}</a>";

    return "<div class='trw_ms_td'><div>{$v}{$html}</div><div>{$subms}</div></div>";
  }

  protected function __v($c = "▶") {
    if (($id_capa = $this->obxeto("subms")->id_capa()) == null) {
      $c =  "&nbsp;";
      $js = "";
    }
    elseif ($this->tipo == "5") {
      $js = "onclick=\"trw_ms5_abrir('{$id_capa}')\"";
    }
    elseif ($this->tipo == "6") {
      $js = "onclick=\"trw_ms6_abrir('{$id_capa}')\"";
    }
    elseif ($this->tipo == "h2") {
      $js = "onclick=\"trw_submenu_h2_toggle('{$id_capa}', {$this->prof})\"";
    }
    else {
      $js = "onclick=\"trw_menu_abrir('{$id_capa}')\"";
    }

    //~ return "";
    return "<div class='ms_v' {$js}>{$c}</div>";
  }
}

//********************************************

final class Suboms extends Opcion_ms {
  public function __construct(FGS_usuario $u, IMenuSite_opcion $oms, IMenuSite_paxina $pax, Config_obd $c, $edicion, $prof = 1) {
    parent::__construct($u, $oms, $pax, $c, $edicion, $prof);

    $this->clase_css("default", "ms_subopcion");
    $this->clase_css("selec"  , "ms_subopcion_selec");
  }

  public function selecionar($b = true) {
    parent::selecionar($b);

    return $this->pai->selecionar($b); //* propagación selecionar() até a raiz. ($this->pai:Subms)
  }

  protected function __ptw($css, Link_pax $oloms, $subms, $v = "") {
    $href = $oloms->href;
    $loms = $oloms->html();

    if ($this->tipo == "3") {
      $html = "<span class='{$css}'>{$loms}</span>";

      if ($href != null) $html = "<a href='{$href}'>{$html}</a>";

      return array($html, $subms);
    }

    if ($this->tipo == "4") {
      if ($subms != null) $subms = "<div>{$subms}</div>";

      $html = "
            <div class       = '{$css}'
                 onmouseover = 'this.className = \"{$css}_over\"'
                 onmouseout  = 'this.className = \"{$css}\"'>{$loms}</div>"; //* so 2 niveis.

      if ($href != null) $html = "<a href='{$href}'>{$html}</a>";

      return $html;
    }

    if ($this->tipo == "5") {
      if ($subms != null) $subms = "<div>{$subms}</div>";

      $float    = ""; $float_aux = "class='ms5_subgrupo'";
      $css_over = "{$css}_over";
      if ($this->prof == 1) {
        $css      .= " ms_subopcion_1";
        $css_over .= " ms_subopcion_1";

        $float = $float_aux;
      }
      else {
        $css      .= " ms_subopcion_n";
        $css_over .= " ms_subopcion_n";
      }

      $html = "
            <div class       = '{$css}'
                 onmouseover = 'this.className = \"{$css_over}\"'
                 onmouseout  = 'this.className = \"{$css}\"'><div onclick='trw_ms5_submenu_abrir(this); return false;'>▶</div>{$loms}</div>";

      if ($href != null) $html = "<a href='{$href}'>{$html}</a>";

      $float = ($this->prof == 1)?$float_aux:"";


      return "<div {$float}>
                <div>{$html}</div>
                <div class='subms_capa_2'>{$subms}</div>
              </div>";
    }

    if ($this->tipo == "6") {
      if ($subms != null) $subms = "<div>{$subms}</div>";

      $float    = ""; $float_aux = "class='ms6_subgrupo'";
      $css_over = "{$css}_over";
      if ($this->prof == 1) {
        $css      .= " ms_subopcion_1";
        $css_over .= " ms_subopcion_1";

        $float = $float_aux;
      }
      else {
        $css      .= " ms_subopcion_n";
        $css_over .= " ms_subopcion_n";
      }

      $html = "
            <div class       = '{$css}'
                 onmouseover = 'this.className = \"{$css_over}\"'
                 onmouseout  = 'this.className = \"{$css}\"'><div onclick='trw_ms6_submenu_abrir(this); return false;'>▶</div>{$loms}</div>";

      if ($href != null) $html = "<a href='{$href}'>{$html}</a>";

      $float = ($this->prof == 1)?$float_aux:"";


      return "<div {$float}>
                <div>{$html}</div>
                <div class='subms_capa_2'>{$subms}</div>
              </div>";
    }

    if ($this->tipo == "v") {
      $css_over = "{$css}_over";
      if ($this->prof > 1) {
        $css      .= " ms_subopcion_n";
        $css_over .= " ms_subopcion_n";
      }

      $html = "
            <div class       = '{$css}'
                 onmouseover = 'this.className = \"{$css_over}\"'
                 onmouseout  = 'this.className = \"{$css}\"'>{$loms}</div>";

      if ($href != null) $html = "<a href='{$href}'>{$html}</a>";


      return "<div class='trw_ms_tr'>
                <div class='trw_ms_td'>
                  {$html}
                  <div>{$subms}</div>
                </div>
              </div>";
    }

    if ($this->tipo == "h2") {
      $html = "<div class       = '{$css}'
                    onmouseover = 'this.className = \"{$css}_over\"'
                    onmouseout  = 'this.className = \"{$css}\"'>{$loms}</div>";

      if ($href != null) $html = "<a href='{$href}'>{$html}</a>";


      return "<div>
                {$html}
                <div>{$subms}</div>
              </div>";
    }

    $html = "<div class       = '{$css}'
                  onmouseover = 'this.className = \"{$css}_over\"'
                  onmouseout  = 'this.className = \"{$css}\"'>{$loms}</div>";

    if ($href != null) $html = "<a href='{$href}'>{$html}</a>";


    return "<div class='trw_ms_tr'>
              <div class='trw_ms_td'>
                {$html}
                <div>{$subms}</div>
              </div>
            </div>";
  }
}

