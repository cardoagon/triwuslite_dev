<?php


final class Elmt_ventas_lprto1 extends FS_lista {
  public $id_site;
  public $id_articulo;

  public $tipo;

  public $erro;

  public $iniciando = true;

  public function __construct(Articulo_obd $a) {
    parent::__construct("lprsto1", new Elmt_ventas_lprto_ehtml1());

    $this->id_site     = $a->atr("id_site")->valor;
    $this->id_articulo = $a->atr("id_articulo")->valor;

    $this->selectfrom  = "select * from prsto_paso";
    $this->where       = "id_site = {$this->id_site} and id_articulo = {$this->id_articulo}";
    $this->orderby     = "posicion";

    $this->obxeto("lista")->clase_css("default", "ventas_pp");
  }

  public function validar() {
    if (function_exists("prsto_validar")) return prsto_validar($this);

    return $this->validar_base();
  }

  public function validar_base() {
    $validar = true;

    if (!$this->visible) return true; //* se non esta activo => validacion TRUE;

    $a_lopt = $this->obxetos("sublista");

    if (count($a_lopt) == 0) {
      $this->erro = "Error: count(this.a_opcions) == 0.<br>";

      $validar = false;
    }

    if (count($a_lopt) != $this->__count()) {
      $this->erro = "Faltan opciones por seleccionar<br>";

      $validar = false;
    }

    foreach($a_lopt as $k=>$lopt) {
      $lopt->erro = null;

      list($k0, $id_rama) = explode(Escritor_html::csubnome, $k);
      list($id_paso, $tipo) = explode("@", $id_rama);

//~ echo "$k0, $id_rama, $id_paso, $tipo\n";

      if (($tipo == "d") || ($tipo[0] == "t")) {
        $a_topcion = $lopt->obxetos("topcion");

        if (count($a_topcion) == 0) continue;
        foreach($a_topcion as $k=>$t) {
          list($k0, $id_opcion) = explode(Escritor_html::csubnome, $k);

          if (!isset($lopt->a_tobligado[$id_opcion])) continue;

          if (($v = trim($t->valor())) != "") continue;

          $lopt->erro .= "Debes teclear los datos obligatorios que est&aacute;n marcados con (*).<br>";

          $validar = false;
        }
      }
      elseif ($tipo == "m") {
      }
      else {
//~ echo "....{$id_paso}\n";
        $opcion = $lopt->obxeto("opt", $id_paso)->valor();

//~ echo "::::{$opcion}\n";

        if (is_numeric($opcion) && ($opcion != "")) continue;

        $lopt->erro .= "Falta por escoger una opci&oacute;n.<br>";

        $validar = false;
      }
    }


    return $validar;
  }

  public function pon_opcions($a_ops) {
    $a_lopt = $this->obxetos("sublista");

//~ echo "ct::" . count($a_lopt) . "<br>";

    if (count($a_lopt) == 0) return;

    foreach($a_lopt as $lopt) $lopt->pon_opcions( $a_ops[$lopt->id_paso] );
  }

  public function a_opcions() {
    $a_opcions = null;

    $a_lopt = $this->obxetos("sublista");

    if (count($a_lopt) == 0) return $a_opcions;

    foreach($a_lopt as $lopt) $a_opcions[$lopt->id_paso] = $lopt->selecion();


    return $a_opcions;
  }

  public function sublista_prototipo($id_rama) {
    list($id_paso, $tipo) = explode("@", $id_rama);

    return new Elmt_ventas_lprto2($this->id_site, $this->id_articulo, $id_paso, $tipo);
  }
/*
  public function operacion(EstadoHTTP $e) {
    $evento = $e->evento();

    //~ echo $evento->html();

    if ($this->control_fslevento($evento, "lnome")) return parent::op_rama($e, $evento->subnome(0));

    return parent::operacion($e);
  }
*/
}

//---------------------------------

final class Elmt_ventas_lprto_ehtml1 extends FS_ehtml {
  public function __construct() {
    parent::__construct();
  }

  protected function inicia_saida() {
    return "";
  }

  protected function finaliza_saida() {
    return "";

  }

  protected function cabeceira($df = null) {
    if ($this->xestor->erro != null) return "<div class='ventas_pp__erro'>{$this->xestor->erro}</div>";

    return "";
  }
  
  protected function linha_detalle($df, $f) {
    $id_rama = "{$f['id_paso']}@{$f['tipo']}";

    if ($this->xestor->iniciando) $this->rama_click($id_rama);
    
    return "
    <div class='ventas_pp__paso'>
      <div class=ventas_pp_marco >
        <div class='ventas_pp_titulo'>" . self::__lnome($f) . "</div>
        <div class='ventas_pp_notas'>{$f['notas']}</div>
      </div>
      " . $this->rama_html($id_rama) . "
    </div>";
  }
  
  protected function totais() {
    $this->xestor->iniciando = false;
    
    return "";
  }

  private static function __lnome($f) {
    return $f['nome'];
  }
}

//*****************************************************

final class Elmt_ventas_lprto2 extends FS_lista {
  public $id_paso;

  public $tipo;

  public $erro;

  public $a_tobligado = null;

  public function __construct($id_site, $id_articulo, $id_paso, $tipo) {
    parent::__construct("lprsto2", new Elmt_ventas_lprto_ehtml2());

    $this->id_paso = $id_paso;
    $this->tipo    = $tipo;

    $this->selectfrom = "select * from prsto_paso_opcion";
    $this->where      = "id_site = {$id_site} and id_articulo = {$id_articulo} and id_paso = {$id_paso}";
    $this->orderby    = "grupo, id_opcion";

    $this->pon_obxeto(new Hidden("opt"), $id_paso);

    $this->obxeto("lista")->clase_css("default", "ventas_pp__opcions");
  }

  public function pon_opcions($a_ops) {
    if ($this->tipo[0] == "m") return $this->pon_opcions_m($a_ops);
    if ($this->tipo[0] == "t") return $this->pon_opcions_t($a_ops);
    if ($this->tipo    == "d") return $this->pon_opcions_t($a_ops);
    //~ print_r($a_ops);

    $this->obxeto("opt", $this->id_paso)->post( $a_ops[0] );
  }

  public function pon_opcions_t($a_ops) {
    if (($ct = count($a_ops)) == 0) return;

    for($i = 0; $i < $ct; $i++) {
      if (($o = $this->obxeto("topcion", $i + 1)) == null) break;
      
      $o->post($a_ops[$i]);
    }
  }

  public function pon_opcions_m($a_ops) {
    if (($ct = (count($a_ops) - 1)) == 0) return;

    for($i = 0; $i < $ct; $i++) {
      if (($o = $this->obxeto("chopcion", $i + 1)) == null) continue;

      $o->post($a_ops[$i] == "1");
    }
  }

  public function selecion() {
    if ($this->tipo    == "m") return $this->selecion_m();
    if ($this->tipo[0] == "t") return $this->selecion_t();
    if ($this->tipo    == "d") return $this->selecion_t();

    if (($id_o = $this->obxeto("opt", $this->id_paso)->valor()) == null) return null;

    $id_s = $this->pai->id_site;
    $id_a = $this->pai->id_articulo;
    $id_p = $this->id_paso;

    $ppo = Presuposto_paso_opcion_obd::inicia(new FS_cbd(), $id_s, $id_a, $id_p, $id_o);

    $a_po["id_o"    ] = $id_o;
    $a_po["grupo_o" ] = $ppo->atr("grupo"   )->valor;
    $a_po["nome_o"  ] = $ppo->atr("nome"    )->valor;
    $a_po["notas_o" ] = $ppo->atr("notas"   )->valor;
    $a_po["pvp_suma"] = $ppo->atr("pvp_suma")->valor;
    $a_po["obligado"] = $ppo->atr("obligado")->valor;

    $pp = $ppo->paso_obd();

    $a_po["id_p"     ] = $id_p;
    $a_po["nome_p"   ] = $pp->atr("nome"     )->valor;
    $a_po["notas_p"  ] = $pp->atr("notas"    )->valor;
    $a_po["pos_p"    ] = $pp->atr("posicion" )->valor;
    $a_po["tipo"     ] = $pp->atr("tipo"     )->valor;
    $a_po["cantidade"] = $pp->atr("cantidade")->valor;

    //~ echo "<pre>" . print_r($a_po, true) . "</pre>";

    return $a_po;
  }

  public function selecion_t() {
    $id_s = $this->pai->id_site;
    $id_a = $this->pai->id_articulo;
    $id_p = $this->id_paso;

    $cbd = new FS_cbd();

    $pp = Presuposto_paso_obd::inicia($cbd, $id_s, $id_a, $id_p);

    $a_po["id_p"     ] = $id_p;
    $a_po["nome_p"   ] = $pp->atr("nome"     )->valor;
    $a_po["notas_p"  ] = $pp->atr("notas"    )->valor;
    $a_po["pos_p"    ] = $pp->atr("posicion" )->valor;
    $a_po["tipo"     ] = $pp->atr("tipo"     )->valor;
    $a_po["cantidade"] = $pp->atr("cantidade")->valor;

    $a_ppo = $pp->a_opcions($cbd);

    if (count($a_ppo) == 0) return $a_po;

    foreach ($a_ppo as $ppo) {
      $id_o = $ppo->atr("id_opcion")->valor;

      $a_po["ops"][$id_o]["id_o"    ] = $id_o;
      $a_po["ops"][$id_o]["nome_o"  ] = $ppo->atr("nome"    )->valor;
      $a_po["ops"][$id_o]["notas_o" ] = $ppo->atr("notas"   )->valor;
      $a_po["ops"][$id_o]["pvp_suma"] = $ppo->atr("pvp_suma")->valor;
      $a_po["ops"][$id_o]["obligado"] = $ppo->atr("obligado")->valor;
      
      $a_po["ops"][$id_o]["valor"   ] = $this->obxeto("topcion", $id_o)->valor();
    }

    //~ echo "<pre>" . print_r($a_po, true) . "</pre>";

    return $a_po;
  }

  public function selecion_m() {
    $id_s = $this->pai->id_site;
    $id_a = $this->pai->id_articulo;
    $id_p = $this->id_paso;

    $cbd = new FS_cbd();

    $pp = Presuposto_paso_obd::inicia($cbd, $id_s, $id_a, $id_p);

    $a_po["id_p"     ] = $id_p;
    $a_po["nome_p"   ] = $pp->atr("nome"     )->valor;
    $a_po["notas_p"  ] = $pp->atr("notas"    )->valor;
    $a_po["pos_p"    ] = $pp->atr("posicion" )->valor;
    $a_po["tipo"     ] = $pp->atr("tipo"     )->valor;
    $a_po["cantidade"] = $pp->atr("cantidade")->valor;

    $a_ppo = $pp->a_opcions($cbd);

    if (count($a_ppo) == 0) return $a_po;

    foreach ($a_ppo as $ppo) {
      $id_o = $ppo->atr("id_opcion")->valor;

      $a_po["ops"][$id_o]["id_o"    ] = $id_o;
      $a_po["ops"][$id_o]["nome_o"  ] = $ppo->atr("nome"    )->valor;
      $a_po["ops"][$id_o]["notas_o" ] = $ppo->atr("notas"   )->valor;
      $a_po["ops"][$id_o]["pvp_suma"] = $ppo->atr("pvp_suma")->valor;
      $a_po["ops"][$id_o]["obligado"] = $ppo->atr("obligado")->valor;

      $a_po["ops"][$id_o]["valor"   ] = $this->obxeto("chopcion", $id_o)->valor();
    }

    //~ echo "<pre>" . print_r($a_po, true) . "</pre>";

    return $a_po;
  }

  public function operacion(EstadoHTTP $e) {    
    if ($this->control_fslevento($e->evento(), "bopcion")) {
      return $this->pai->pai->operacion_bpvp_prsto($e, false);
    }

    if ($this->obxeto("opt", $this->id_paso)->control_evento()) {
      $this->preparar_saida($e->ajax());

      return $this->pai->pai->operacion_bpvp_prsto($e, false);
    }


    return null;
  }
}

//---------------------------------

final class Elmt_ventas_lprto_ehtml2 extends FS_ehtml {
  private $grupo;

  private $s_describe;

  public function __construct() {
    parent::__construct();


    /* $this->style_table = "";
    $this->align       = "center";
    $this->width       = "100%";
    $this->cellspacing = 0;
    $this->cellpadding = 0;
    $this->border      = 0; */
  }

  protected function inicia_saida() {
    return "";
//    return "<div class='ventas_pp_paso_2'>";
  }

  protected function finaliza_saida() {
    return "";
//    return "</div>";
  }

  protected function cabeceira($df = null) {
    $this->grupo = null;

    if ($this->xestor->tipo    == "s") return $this->cabeceira_s($df);  //select
    if ($this->xestor->tipo    == "r") return $this->cabeceira_r($df);  //radio buttons
    if ($this->xestor->tipo    == "m") return $this->cabeceira_m($df);  //checkers
    if ($this->xestor->tipo[0] == "t") return $this->cabeceira_t($df);  //text

    $id_paso = $this->xestor->id_paso;

    $h = $this->xestor->obxeto("opt", $id_paso)->html();

    /* return "<tr style='background-color: #fff;'>
              <td>
                " . self::__erro($this) . "
                <div id='dopt{$id_paso}'>{$h}</div>
              </td>
            </tr>
            <tr style='background-color: #fff;'>
              <td id='bopt{$id_paso}'>"; */
      return "<div class='ventas_pp__error'>".self::__erro($this)."
                <div id='dopt{$id_paso}'>{$h}</div>
              </div>
              <div>
                <div id='bopt{$id_paso}'></div>
              </div>";
  }

  protected function cabeceira_t($df = null) {
    /* return "<tr style='background-color: #fff;'>
              <td>" . self::__erro($this) . "</td>
            </tr>
            <tr style='background-color: #fff;'>
              <td>"; */
    return "<div class='ventas_pp__error'>".self::__erro($this)."</div>";
  }

  protected function cabeceira_m($df = null) {
    return $this->cabeceira_t($df);
  }

  protected function cabeceira_r($df = null) {
    $this->s_describe = null;

    $id_paso = $this->xestor->id_paso;

    $input = $this->xestor->obxeto("opt", $id_paso);

    if ($input->valor() == null) $input->post(1);
    
    return "<div id='dopt{$id_paso}'>" . $input->html() . "</div>";
  }

  protected function cabeceira_s($df = null) {
    $this->s_describe = null;

    $id_paso = $this->xestor->id_paso;

    $input = $this->xestor->obxeto("opt", $id_paso);

    if ($input->valor() == null) $input->post(1);

    $onchange = "onchange='elmt_prsto_select_opcion_2(this, {$id_paso})'";

     return "<div class='ventas_pp__opcion--select' id='dopt{$id_paso}'>"
               . $input->html() ."<select class='ventas_ppo_selector' {$onchange}>";  //pechan en método totais_s()
  }

  protected function linha_detalle($df, $f) {
    if ($this->xestor->tipo    == "r") return $this->linha_detalle_r($df, $f);
    if ($this->xestor->tipo    == "s") return $this->linha_detalle_s($df, $f);
    if ($this->xestor->tipo    == "m") return $this->linha_detalle_m($df, $f);
    if ($this->xestor->tipo    == "d") return $this->linha_detalle_t($df, $f);
    if ($this->xestor->tipo[0] == "t") return $this->linha_detalle_t($df, $f);

    $div_g = "";
    if ($this->grupo != $f['grupo']) {
      $this->grupo = $f['grupo'];

      $div_g = "<div class='ventas_ppo_grupo'>{$this->grupo}</div>";
    }

    return "{$div_g}<div style='float: left; padding: 3px;'>" . self::__bopcion($this, $f) . "</div>";
  }

  protected function linha_detalle_r($df, $f) {
    if ($f['pvp_suma'] == null) $f['pvp_suma'] = "0";

    $id_paso = $this->xestor->id_paso;

    $checked  = "";
    if ($this->xestor->obxeto("opt", $id_paso)->valor() == $f['id_opcion']) {
      $checked = " checked";
    }

    $id = "prsto_r_{$id_paso}_{$f['id_opcion']}";

    return "<div class='ventas_ppo_radio_marco' title='{$f['notas']}'>
              <input class='ventas_ppo_radio' type='radio' id='{$id}' name='prsto_r_{$id_paso}' value='{$f['id_opcion']}' onchange='elmt_prsto_select_opcion_2(this, {$id_paso})' {$checked} />
              <label class='ventas_ppo_radio_label' for='{$id}'>{$f['nome']}" . self::__pvp_suma($f['pvp_suma']) . "</label>
            </div>";
  }

  protected function linha_detalle_s($df, $f) {
    if ($f['pvp_suma'] == null) $f['pvp_suma'] = "0";

    $id_paso = $this->xestor->id_paso;

    $selected = "";
    if ($this->xestor->obxeto("opt", $id_paso)->valor() == $f['id_opcion']) {
      $selected = " selected";

      $this->s_describe = "<span class='ventas_pp_notas_2'>{$f['notas']}</span>";
    }

    return "<option value='{$f['id_opcion']}'{$selected}>{$f['nome']}" . self::__pvp_suma($f['pvp_suma']) . "</option>";
  }

  protected function linha_detalle_t($df, $f) {
    $n = $f['nome'];
    if ($f['obligado'] == 1) {
      $this->xestor->a_tobligado[$f['id_opcion']] = 1;

      $n = "(*) {$n}";
    }

    return "<div class='ventas_pp__opcion'>
              <div class='ventas_ppo_boton_texto'>{$n}</div>
              <div class='ventas_pp__input'>" . self::__topcion($this, $f) . "<span class='ventas_pp_notas_2'>{$f['notas']}</span></div>
            </div>";
  }

  protected function linha_detalle_m($df, $f) {
    return "<div class='ventas_pp__opcion--check' title='{$f['notas']}'>
              <div>
                " . self::__chopcion($this, $f) . "
                <span class='ventas_pp_notas_2'>{$f['notas']}</span>
              </div>
            </div>";
  }

  protected function totais() {
    if ($this->xestor->tipo    == "s") return $this->totais_s();
    if ($this->xestor->tipo    == "m") return $this->totais_m();
    if ($this->xestor->tipo[0] == "t") return $this->totais_t();

    /* return "</td></tr>"; */
    return "";
  }

  protected function totais_t() {
    /* return "</td></tr>"; */
    return "";
  }

  protected function totais_m() {
    return $this->totais_t();
  }

  protected function totais_s() {
    /* return "</select>{$this->s_describe}</td></tr>"; */
    return "</select>{$this->s_describe}</div>";
  }

  private static function __erro(Elmt_ventas_lprto_ehtml2 $ehtml) {
    if ($ehtml->xestor->erro == null) return "";

    return "<div class='ventas_pp__erro'>{$ehtml->xestor->erro}</div>";
  }

  private static function __bopcion(Elmt_ventas_lprto_ehtml2 $ehtml, $f) {
    if ($ehtml->xestor->tipo == "b4") return self::__bopcion4($ehtml, $f);

    if (($g = $f['grupo']) != null) $g .= ".";

    $title = "{$g}{$f['nome']}" . self::__pvp_suma($f['pvp_suma']);

    $txt = ($ehtml->xestor->tipo != "b1")?"<div class='ventas_ppo_boton_texto'>{$title}</div>":"";
    $txt .= self::__bopcion_imx($f['id_logo'], $txt);

    $b = new Button("bopcion", $txt);

    $b->title = $title;

    $b->pon_eventos("onclick", "elmt_prsto_select_opcion(this, {$f['id_paso']}, {$f['id_opcion']})");

    $b->clase_css("default", self::__bopcion_css($ehtml->xestor->tipo,
                                                 $ehtml->xestor->obxeto("opt", $f['id_paso'])->valor(),
                                                 $f['id_opcion']));


    return $ehtml->__fslControl($b, $f['id_opcion'])->html();
  }

  private static function __bopcion4(Elmt_ventas_lprto_ehtml2 $ehtml, $f) {
    $title = "<b>{$f['nome']}</b><br />{$f['notas']}" . self::__pvp_suma($f['pvp_suma']);

    $txt = self::__bopcion_imx($f['id_logo'], $txt);

    $txt = "<div class='ventas_ppo_boton4_marco'>
              <div class='ventas_ppo_boton4_imx'>{$txt}</div>
              <div class='ventas_ppo_boton4_texto'>{$title}</div>
            </div>";

    $b = new Button("bopcion", $txt);

    $b->title = strip_tags($title);

    $b->pon_eventos("onclick", "elmt_prsto_select_opcion(this, {$f['id_paso']}, {$f['id_opcion']})");

    $b->clase_css("default", self::__bopcion_css($ehtml->xestor->tipo,
                                                 $ehtml->xestor->obxeto("opt", $f['id_paso'])->valor(),
                                                 $f['id_opcion']));


    return $ehtml->__fslControl($b, $f['id_opcion'])->html();
  }

  private static function __pvp_suma($pvp_suma) {
    if ($pvp_suma == null) return "";
    if ($pvp_suma == "0")  return "";

    $s = ($pvp_suma > 0)?"+":"";

    return "&nbsp;(&nbsp;{$s}{$pvp_suma}&nbsp;&euro;&nbsp;+IVA)";
  }

  private static function __bopcion_css($tipo, $id_opt, $id_opt_s) {
    switch ($tipo) {
      case "b1":
        if ($id_opt == $id_opt_s) return "ventas_ppo_boton_1_selecionado";

        return "ventas_ppo_boton_1";

      case "b2":
        if ($id_opt == $id_opt_s) return "ventas_ppo_boton_2_selecionado";

        return "ventas_ppo_boton_2";

      case "b3":
        if ($id_opt == $id_opt_s) return "ventas_ppo_boton_3_selecionado";

        return "ventas_ppo_boton_3";

      case "b4":
        if ($id_opt == $id_opt_s) return "ventas_ppo_boton_4_selecionado";

        return "ventas_ppo_boton_4";

    }

    die("Elmt_ventas.__bopcion_css(), tipo desco&ntilde;ecido");
  }

  private static function __bopcion_imx($id_logo) {
    if ($id_logo == null) return "";

    $imx_obd = Elmt_obd::inicia(new FS_cbd(), $id_logo);

    $url = $imx_obd->url(171);

    return "<img src='{$url}' width=91% />";
  }

  private static function __topcion(Elmt_ventas_lprto_ehtml2 $ehtml, $f) {
    if (($t = $ehtml->xestor->obxeto("topcion", $f['id_opcion'])) != null) return $t->html();

    if ($ehtml->xestor->tipo == "t") {
      $t = new Text("topcion");

      //~ $t->style("default", "width: 41%;");
    }
    elseif ($ehtml->xestor->tipo == "d") {
      $t = new DataInput("topcion");

      //~ $t->style("default", "width: 41%;");
    }
    else {
      $t = new Textarea("topcion", 4, 20);

      //~ $t->style("default", "width: 99%;");
    }

    $ehtml->xestor->pon_obxeto($t, $f['id_opcion']);


    return $t->html();
  }

  private static function __chopcion(Elmt_ventas_lprto_ehtml2 $ehtml, $f) {
    if (($ch = $ehtml->xestor->obxeto("chopcion", $f['id_opcion'])) != null) return $ch->html();

    $obr = ($f['obligado'] == 1);
    
    $nom = $f['nome'] . self::__pvp_suma($f['pvp_suma']);   
    
    if ($obr) $nom = "(*) {$nom}";
    

    $ch = new Checkbox("chopcion", $obr, $nom);
    
    $ch->readonly = $obr;
    
    //~ $ch->envia_AJAX("onchange");

    $ehtml->xestor->pon_obxeto($ch, $f['id_opcion']);


    return $ch->html();
  }
}
