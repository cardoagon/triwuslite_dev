<?php


final class CBuscador extends    Componente
                      implements I_fs       {

  public function __construct(Site_obd $s, $editor) {
    parent::__construct("cbuscador", Refs::url("ptw/cbuscador.html"));

    $this->pon_obxeto(Panel_fs::__text("tbusca", 1, 99, " "));

    //~ $this->obxeto("tbusca")->type = "search";
    $this->obxeto("tbusca")->clase_css("default", "cbuscador_input");

    $this->pon_obxeto(self::__bbusca());
  }

  public function config_lectura() {}
  public function config_edicion() {}
  public function config_usuario(IEfspax_xestor $e = null) {
    //* cambiamos o método de operación se non estamos non editor
    if ($e->edicion()) return;

    $this->obxeto("tbusca")->pon_eventos("onkeypress", "triwus_tbuscador_keypress()");

    $this->obxeto("bbusca")->pon_eventos("onclick"   , "triwus_cbuscador_click()");
  }

  public function __b($b = null) {
    if ($b !== null) $this->obxeto("tbusca")->post($b);


    return $this->obxeto("tbusca")->valor();
  }

  public function operacion(EstadoHTTP $e) {
    if ($this->obxeto("bbusca")->control_evento()) return $this->operacion_bbuscar($e);

    return null;
  }

  public function operacion_bbuscar(IEfspax_xestor $e) {
    //* para editor.php

    $id_paxb = Paxina_reservada_obd::id_paxina(new FS_cbd(), $e->id_site, "buscador");


    return Efspax_xestor::operacion_redirect($e, $id_paxb);
  }

  public function __bbusca() {
    $b = new Button("bbusca");

    $b->clase_css("default" , "cbuscador_boton");
    $b->clase_css("readonly" , "cbuscador_boton");

    $b->sup_eventos();

    return $b;
  }
}

//***************************************

final class FSL_buscador extends FS_lista {
  public $editor;
  
  public $a_buscar;
  public $t_buscar;

  private $id_site;
  private $id_logo;
  
  public function __construct() {
    parent::__construct("lrb", new FSehtml_buscador());

    $this->selectfrom = "select * from v_cbuscador";

    $this->where      = "1=0";

    $this->orderby    = "id_prgf_indice asc, nome asc";

    $this->limit_f    = 60;

    $this->obxeto("lista")->style("default", "display: flex;flex-direction: column;justify-content: center;align-items: center;");
  }

  public function operacion(EstadoHTTP $e) {
    $evento = $e->evento();

    //~ echo $evento->html();

    if ($this->control_fslevento($evento, "n")) return Efspax_xestor::operacion_redirect($e, $evento->subnome(0));

    return parent::operacion($e);
  }

  public function __where($id_site, $_param, $_permisos, $editor, $id_idioma) {
    $this->id_site  = $id_site;
    $this->editor   = $editor;
    
    //* paxinacion.
    $this->paxinar( $_param[1] );


    //* where.
    $this->a_buscar = preg_split("/[\s,;:]+/", $_param[0]);
    $this->t_buscar = $_param[0];


    $w1 = "(id_site = {$id_site})";

    if (!$editor) $w1 .= " and (estado = 'publicada')";


    $w2 = Valida::buscador_txt2sql($this->t_buscar, array("nome", "descricion", "action", "nubeb"), false);


    $ct_p = count($_permisos);

        //~ if ($ct_p == 0)   $w3 = "(1=1)";
        if ($ct_p == 0)   $w3 = "(grupo == 'pub')";
    elseif ($_permisos[0] == "admin") $w3 = "(1=1)";
    elseif (($ct_p == 1) && ($_permisos[1] == "pub")) $w3 = "(grupo == 'pub')";
    else {
      $w3 = null;
      foreach ($_permisos as $p) {
        if ($w3 != null) $w3 .= " or ";

        $w3 .= "(grupo like ('{$p}'))";
      }
    }

    $w_idioma = ($id_idioma == null)?"":" and (id_idioma = '@@' or id_idioma='{$id_idioma}')";

    $this->where = "{$w1} and ({$w2}) and ({$w3}) {$w_idioma}";
  }

  public static function ptw_buscador($v = "") {
    return "<div class=\"trw_elmt_cbuscador_marco\">
              <input type=\"text\" value=\"{$v}\" id=\"trw_elmt_cbuscador_t\" onkeypress=\"trw_elmt_cbuscador_keypress()\" maxlength=\"99\" placeholder=\" \">
              <button type=\"button\" id=\"findex;cbuscador;bbusca\" onclick=\"trw_elmt_cbuscador_click(this)\"></button>
            </div>";
  }

  public function html_b() {
    if (!is_array($this->a_buscar)) return "";

    $b = "";
    for ($i = 0; $i < count($this->a_buscar); $i++) {
      if ($b != "") $b .= " ";

      $b .= "<b>{$this->a_buscar[$i]}</b>";
    }

    return $b;
  }

  public function id_logo_aux(FS_cbd $cbd = null) {
    if ($this->logo_id != null) return $this->logo_id;
    
    if ($this->id_site == null) return null;
    
    
    if ($cbd == null) $cbd = new FS_cbd();
    
        
    $this->logo_id = Site_metatags_obd::inicia($cbd, $this->id_site)->iclogo_id();
    
    
    return $this->logo_id;
  }

  protected function html_baleiro() {
    if (!$this->visible) return "";

    return FSL_buscador::ptw_buscador($this->t_buscar) . "<div class='lista_baleira txt_adv'>No se encontraron coincidencias para tu consulta: " . $this->html_b() . ".</div>";
  }
}

//-----------------------------------------

final class FSehtml_buscador extends FS_ehtml {
  public function __construct() {
    parent::__construct();

    $this->class_table = "";
    $this->style_table = "margin-top: 55px;";
    $this->width       = "100%";
    $this->cellspacing = 0;
    $this->cellpadding = 3;
    $this->border      = 0;
  }


  protected function inicia_saida() {
    return "<div class='cbuscador_00'>";
  }

  protected function cabeceira($df = NULL) {
    return FSL_buscador::ptw_buscador( $this->xestor->t_buscar ) . "<div class='cbuscador_det'>";
  }

  protected function linha_detalle($df, $f) {
    //~ return "<div><pre>" . print_r($f, 1) . "</pre></div>";

    return "
    <div class='cbuscador_det_a' " . self::__o($this, $f) . ">
      " . self::__i($this, $f) . "
      " . self::__n($f) . "
    </div>";
  }

  protected function totais() {
    $action = "buscador.php?b={$this->xestor->t_buscar}";
    
    return "</div><div class='cbuscador_cab'>" . $this->trw_paxinador($action) . "</div>";
  }

  protected function finaliza_saida() {
    return "</div>";
  }

  private static function __n($f) {
    return "<div>{$f['nome']}</div>";
  }

  //~ private static function __d($f) {
        //~ if ($f['tipo'] == "producto"     ) $d = $f['notas'];
    //~ elseif (($d = $f['descricion']) == "") $d = $f['nube'];

    //~ return "<div>" . Valida::split(strip_tags($d), 77) . "</div>";
  //~ }

  private static function __i(FSehtml_buscador $ehtml, $f) {
    if ($f['logo'] == null) $f['logo'] = $ehtml->xestor->id_logo_aux();
    
    if ($f['logo'] == null)
      $logo_src = Refs::url(Imaxe_obd::noimx);
    elseif (($elmt_obd = Elmt_obd::inicia(new FS_cbd(), $f['logo'])) != null)
      $logo_src = $elmt_obd->url(0);
    else
      $logo_src = Refs::url(Imaxe_obd::noimx);

    return "<div alt='{$f['nome']}' title='{$f['nome']}' style='background-image: url({$logo_src})'></div>";
  }

  private static function __o(FSehtml_buscador $ehtml, $f) {
    $p = $f['id_paxina'];
    $o = "onclick=";

    if ($f['tipo'] != "producto") {
      $k = $ehtml->xestor->nome_completo() . Escritor_html::cnome . "n" . Escritor_html::csubnome . $p;

      return "{$o}\"enviaEventoSubmit_str('{$k}', 'onclick', null, null)\"";
    }


    return "{$o}\"document.location='{$f['action']}?p={$p}'\"";
  }
}


