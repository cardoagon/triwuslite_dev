<?php

interface IXestor_ms {}

//************************************************

final class Menu_site extends    Componente
                      implements IXestor_ms {

  const ancla = "menu_app";

  public $tipo      = null;
  public $ct_oms    = 0;
  public $op_cero   = null;
  public $id_idioma = null;

  private $op_selec = null;
  private $html_3   = null;

  public function __construct(Site_obd $s, FGS_usuario $u, Config_obd $c, $id_idioma, $edicion = true) {
    parent::__construct("menu_app");

    $this->id_idioma = $id_idioma;
    $this->tipo      = $c->atr("ms_tipo")->valor;

    $ms = $s->menu_site();

    $this->ct_oms = 0;

        if ($edicion)                         $tipo = 2;
    elseif ($s->atr("url_limpa")->valor == 1) $tipo = 1;
    else                                      $tipo = 0;

    Xestor_ms::inicia($this, $ms->a_oms(null, $id_idioma), $u, $c, $id_idioma, $tipo, 0);

    //~ $this->sup_obxeto("illa");
  }

  public function operacion(EstadoHTTP $e) {
    return Xestor_ms::__operacion($this, $e);
  }

  public function control_permisos(FGS_usuario $u) {
    return !Erro::__parse_permisos($this->op_selec->a_grupo, $u->a_permisos());
  }

  public function operacion_primeira(IEfspax_xestor $e) {
    $p = $this->op_cero->obxeto("loms")->id_pax;

    return Efspax_xestor::operacion_redirect($e, $p);
  }

  //~ public function preparar_saida(Ajax $a = null) {
    //~ if ($a == null) return;
    
    //~ $json = json_encode([1,2,3,4,5]);
    
    //~ $a->pon_ok(true, "trw_menu_ajax_resposta({$json})");
  //~ }

  public function selecionar($id_oms) {
    if (($op = $this->buscar($id_oms)) == null) return false;

    if ($this->op_selec != null) $this->op_selec->selecionar(false);

    $op->selecionar(true);

    $this->op_selec = $op;

    return true;
  }

  public function buscar($id_oms) {
    return Xestor_ms::buscar($this, $id_oms);
  }

  public function html():string {
    if ($this->ct_oms < 2) return "";

    if (($obxetos = $this->obxetos()) == null) return "";

    $html         = null;
    $this->html_3 = null;

    foreach($obxetos as $k=>$o) {
      $html_o = $o->html();

          if ($this->tipo == "v") $html_o = "<div class='trw_ms_tr'>{$html_o}</div>";
      elseif ($this->tipo == "3") $html_o = $this->html_3($k, $html_o);

      $html .= $html_o;
    }


    if ($this->tipo == "3") {
      $html_3 = "";
      if (is_array($this->html_3)) {
        foreach ($this->html_3 as $k=>$submenu) {
          $display = ($submenu['visible'])?"flex":"none";

          $html_3 .= "<span id='trw-subms3-{$k}' name='trw-subms3' class='subms_capa_3' style='display: {$display};'>{$submenu['html']}</span>";
        }
      }

      return "<div class='ms_marco'>
                <div class='trw_ms_tr subms_capa' style='position: initial;'>{$html}</div>
                <div class='trw_ms_tr subms_capa' style='position: initial;'>{$html_3}</div>
              </div>
              <script>trw_ms3_swap();</script>";
    }

    if ($this->tipo == "4") $html = "<div class='trw_ms_tr'>{$html}</div>";

    if ($this->tipo == "5") $html = "<div class='trw_ms_tr'>{$html}</div>";

    if ($this->tipo == "6") $html = "<div class='trw_ms_tr'>{$html}</div>";

    if ($this->tipo == "h") $html = "<div class='trw_ms_tr'>{$html}</div>";

    if ($this->tipo == "h2") {
      //~ $html = "<div class='trw_ms_tr'>{$html}</div>";

      return "<div id='trw_ms' class='trw_ms ms_marco'>{$html}</div>";
    }


    return "<div id='trw_ms' class='trw_ms ms_marco'>{$html}</div>";
  }

  private function html_3($p, $a_html) {
    if (!is_array($a_html)) return "";

    if (is_array($a_html[1])) {
      foreach ($a_html[1] as $a_html_1) {
        list($p0, $p1) = explode(Escritor_html::csubnome, $p);

        $this->html_3[$p]['html'] .= $this->html_3($p1 + 1, $a_html_1);
      }

      $this->html_3[$p]['visible'] .= $a_html[1]['visible'];
    }

    return $a_html[0];
  }
}

//************************************************

final class Xestor_ms extends Componente {

  public static function inicia(IXestor_ms $ms, $a_oms, FGS_usuario $u, Config_obd $c, $id_idioma, $edicion, $prof) {
//~ echo "<pre>" . print_r($a_oms[1], true) . "</pre>";

    if (!is_array($a_oms)) return;

    for($i = 0; $i < count($a_oms); $i++) {
      $oms = self::inicia_opcion($a_oms[$i], $u, $c, $id_idioma, $edicion, $prof);

      if ($oms == null) continue;

      if ($ms->ct_oms == 0) $ms->op_cero = $oms;

      $ms->ct_oms++;

      $ms->pon_obxeto($oms, ($i + 1));
    }
  }

  public static function buscar(IXestor_ms $ms, $id_oms) {
    $obxetos = $ms->obxetos("oms");

    if (count($obxetos) == 0) return null;

    foreach($obxetos as $obx)
      if (($opcion = $obx->buscar($id_oms)) != null) return $opcion;

    return null;
  }

  public static function __operacion(IXestor_ms $ms, Efs $e) {
    $obxetos = $ms->obxetos("oms");

    if (count($obxetos) == 0) return null;

    foreach($obxetos as $o) if (($e_aux = $o->operacion($e)) != null) return $e_aux;

    return null;
  }

  private static function inicia_opcion(IMenuSite_opcion $oms, FGS_usuario $u, Config_obd $c, $id_idioma, $edicion, $prof) {
    if (($pax = $oms->paxina_obd(null, $id_idioma)) == null) return null;

    //~ echo $pax->atr("nome")->valor . "::" . $pax->atr("visible")->valor . "::" . $pax->atr("grupo")->valor . "::" . $u->atr("permisos")->valor . "<br />";

    if ( $pax->atr("visible")->valor != 1 ) return null;
 

    if ( !Erro::__parse_permisos( $pax->a_grupo(), $u->a_permisos()) ) return null;

    if ($oms->atr("pai")->valor == null) return new Opcion_ms($u, $oms, $pax, $c, $edicion, $prof);

    return new Suboms($u, $oms, $pax, $c, $edicion, $prof + 1);
  }
}

