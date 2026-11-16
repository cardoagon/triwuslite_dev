<?php

abstract class Catalogo extends Componente {
  
  public function __construct(ICatalogo_fonte $icp, bool $editor, Config_obd $c, string $action, bool $hai_busca = true) {
    parent::__construct("cat");


    $sc_obd = Site_config_obd::inicia(new FS_cbd(), $icp->icatF__site());

    $vpa    = ($sc_obd->atr("ventas_producto_agotados")->valor == 1);
    
    $this->pon_obxeto($this->_busca($icp, $vpa, $action, $hai_busca));
    $this->pon_obxeto($this->_lista($icp, $editor, $c, $sc_obd, $action));
    
//~ echo $this->obxeto("clista")->sql_vista();

  }
  
  abstract public function _busca(ICatalogo_fonte $icp, bool $vpa, string $action, bool $hai_busca):Cat_busca;
  abstract public function _lista(ICatalogo_fonte $icp, bool $editor, Config_obd $c_obd, Site_config_obd $sc_obd, string $action):Cat_lista;


  public function control_url(array $_p):void {
    if (!is_array($_p) ) return; 

    
    if ( is_array($_p["_f"]) ) {
      $this->filtrar( $_p["_f"] ); //* primeiro filtrar, porque post_where(), reinicia limit_0.
    }

    $this->paxinar( $_p[1] );
    
 
//~ echo $this->obxeto("clista")->sql_vista();
  }

  public function operacion(EstadoHTTP $e) {
    if (($e_aux = $this->obxeto("cbusca")->operacion($e)) != null) return $e_aux;
    if (($e_aux = $this->obxeto("clista")->operacion($e)) != null) return $e_aux;

    return null;
  }
  
  public function html():string {
    return $this->obxeto("cbusca")->html() . 
           $this->obxeto("clista")->html();
  }


  protected function filtrar(array $_f):void {
    //* .- realiza filtrado.
    $b = $this->obxeto("cbusca");

    if (!$b->visible) return;
    
    
    $b->op_txt( $_f    );
    $b->op_etq( 1, $_f );
    $b->op_etq( 2, $_f );
    $b->op_etq( 3, $_f );

    //* .- actualiza lista.
    $this->obxeto("clista")->post_where( $b->sql_where() );
  }

  protected function paxinar(?int $pax = null):void {
    $this->obxeto("clista")->paxinar($pax);
  }
}


//*********************************************


abstract class Cat_lista extends FS_lista {
  public string          $titulo;
  public string          $migas;
  public string          $action;
  public int             $id_site;
  
  public string          $css_cols = "";

  public bool            $editor;

  public Config_obd      $c_obd;
  public Site_config_obd $sc_obd;

  public function __construct(ICatalogo_fonte $icp, bool $editor, Config_obd $c_obd, Site_config_obd $sc_obd, string $action) {
    parent::__construct("clista", $this->_ehtml());

    $this->titulo     = $icp->icatF__titulo();
    $this->migas      = "";
    $this->action     = $action;
    $this->editor     = $editor;
                      
    $this->id_site    = $icp->icatF__site();
                      
    $this->c_obd      = $c_obd;
    $this->sc_obd     = $sc_obd;
                      
                      
    $vpa              = ($sc_obd->atr("ventas_producto_agotados")->valor == 1);

    $this->selectfrom = $icp->icatF__selectfrom($editor, $vpa);
    $this->where      = "1";
    $this->orderby    = $icp->icatF__orderby();
    $this->limit_f    = 20;


//~ echo $this->sql_vista();
  }

  abstract protected function _ehtml():Cat_ehtml;

  public function operacion_lpax(EstadoHTTP $e, $suma = 1) {
    $e = parent::operacion_lpax($e, $suma);
    
    if ( $e->evento()->ajax() ) {
      $e->ajax()->pon_ok(true, "trwCat_listar()");
    }

    return $e;
  }

  //~ public function preparar_saida(Ajax $a = null) {
    //~ parent::preparar_saida($a);

    //~ if ($a == null) return;

    //~ $a->pon_ok(true, "trwCat_listar()");
  //~ }
  
  protected function html_baleiro() {
    $lreset = new Span("trwCat_lreset", "Resetear búsqueda");
    
    $lreset->style("default", "cursor: pointer; text-decoration: underline;");
    
    $lreset->pon_eventos("onclick", "trwCat_reset('{$this->action}')");
    
    return "<div>
              <div class='txt_adv'>No se encontraron resultados. " . $lreset->html() . "</div>
            </div>";
  }
}

//-----------------------------------------

abstract class Cat_ehtml extends FS_ehtml {
  public function __construct() {
    parent::__construct();

    $this->class_table = "";
    $this->style_table = "margin-top: 55px;";
    $this->width       = "100%";
    $this->cellspacing = 0;
    $this->cellpadding = 3;
    $this->border      = 0;
  }

  abstract protected function iten_html($df, $f):string;


  protected function trw_paxinador(string $action):string { //* deshuso
    if ($this->xestor->limit_f == null) return "";

    $i = $this->xestor->__i();

    if (($t = $this->xestor->__t()) < 2) return "";


    $a = self::__boton("lpax_a", "", "&#9668;", $i == 1 );
    $s = self::__boton("lpax_s", "", "&#9658;", $i == $t);

    if ($i > 1) {
      $p = $i - 1;
      
      //~ $a->pon_eventos("onclick", "document.location = '{$action}&pax={$p}'");
      $a->pon_eventos("onclick", "trwCat_paxinar(this, '{$action}', {$p})");
    }

    if ($i < $t) {
      $p = $i + 1;
      
      //~ $s->pon_eventos("onclick", "document.location = '{$action}&pax={$p}'");
      $s->pon_eventos("onclick", "trwCat_paxinar(this, '{$action}', {$p})");
    }
    
    $i2 = self::__text($this, "ti", $i      );
    $t2 = self::__text($this, "tt", $t, true);

    $i2->pon_eventos("onmouseover", "this.style.borderColor = '#ccc'"             );
    $i2->pon_eventos("onfocus"    , "this.style.borderColor = '#ccc'"             );
    $i2->pon_eventos("onblur"     , "this.style.borderColor = 'transparent'"      );
    $i2->pon_eventos("onchange"   , "trwCat_paxinar_txt(this, '{$action}', {$t})" );

    return $this->__fslControl($a)->html() . "&nbsp;" . $i2->html() . "&nbsp;de&nbsp;" . $t2->html() . "&nbsp;" . $this->__fslControl($s)->html();
  }
  
  protected function inicia_saida() {
    return "";
  }

  protected function finaliza_saida() {
    return "";
  }

  protected function cabeceira($df = NULL) {
    return "<div class='catalogo-migas'>{$this->xestor->migas}</div>
            <div class='catalogo-titulo'>{$this->xestor->titulo}</div>
            <div id='cxcli3-lanuncios-0-panel' class='catalogo-lista {$this->xestor->css_cols}'>";
  }

  protected function linha_detalle($df, $f) {
    return "<div class='ventas_div_c'>" . $this->iten_html($df, $f) . "</div>";
  }

  protected function totais() {
    //~ if ($this->xestor->editor) return "</div><div class='txt_adv'>Muestra para el editor.</div>";
    
    return "</div><div class='catalogo-paginador'>" . $this->trw_paxinador( $this->xestor->action ) . "</div>";
  }
}


//*********************************************


abstract class Cat_busca extends Componente {
  const ptw_0 = "ptw/detalle/cat/b.html";
  
  protected array $_titetq = []; 

  public function __construct(ICatalogo_fonte $icp, bool $vpa, string $action, bool $hai_busca) {
    parent::__construct("cbusca", Refs::url(self::ptw_0));
    
    if (!$hai_busca) {
      $this->visible = false;
      
      return;
    }

    $this->_titetq[1] = $icp->icatF__etq_tit(1); 
    $this->_titetq[2] = $icp->icatF__etq_tit(2); 
    $this->_titetq[3] = $icp->icatF__etq_tit(3); 
    
    $this->pon_obxeto(new Param("action"  , $action));

    $this->pon_obxeto(new Param("petq_1"));
    $this->pon_obxeto(new Param("petq_2"));
    $this->pon_obxeto(new Param("petq_3"));

    $this->inicia($icp, $vpa);
  }
  
  abstract protected function sql_where_txt(      ):string;
  abstract protected function sql_where_etq(int $i):string;
  abstract protected function inicia_etq(int $i, FS_cbd $cbd, ICatalogo_fonte $icp, bool $vpa):void;

  public function sql_where():string {
    if (!$this->visible) return "(1)";
    
    return $this->sql_where_txt() . " and " . $this->sql_where_etq(1) . " and " . $this->sql_where_etq(2) . " and " . $this->sql_where_etq(3);
  }

  public function operacion(EstadoHTTP $e) {
    if (($e_aux = $this->operacion_hfiltrar($e)) != null) return $e_aux;

    return null;
  }

  public function preparar_saida(Ajax $a = null) {
    if ($this->visible) {
      $this->obxeto("hfiltrar")->post( null               );
      $this->obxeto("petq_1"  )->post( $this->html_etq(1) );
      $this->obxeto("petq_2"  )->post( $this->html_etq(2) );
      $this->obxeto("petq_3"  )->post( $this->html_etq(3) );
    }

    parent::preparar_saida($a);
  }

  public function op_txt(array $_p):void {
    $t = ( isset($_p["f0"]) )? trim($_p["f0"]) : "";

    $this->obxeto("txt")->post( $t );
  }

  public function op_etq(int $i, array $_p):void {
    $this->reset("chEtq_{$i}");

    if (!isset($_p["f{$i}"])) return;

    $_ch = $this->obxetos("chEtq_{$i}");

    if (count($_ch) == 0) return;


    $_p1 = explode(",", $_p["f{$i}"]);


    for ($j = 0; $j < count($_p1); $j++) {
      $k = $_p1[$j];
      //~ if (!is_numeric($k)) continue;
      
      
      if (($o = $this->obxeto("chEtq_{$i}", $k)) == null) continue;

      $o->post(true);
    }
  }

  public function reset($ch_tipo) {
    $_ch = $this->obxetos($ch_tipo);

    if (count($_ch) == 0) return true;

    foreach($_ch as $k=>$ch) $ch->post(false);


    return true;
  }

  protected function chEtq_comas(int $i):?string {
    $_ch = $this->obxetos("chEtq_{$i}");

    if (count($_ch) == 0) return null;


    $s = null;
    foreach($_ch as $ch) {
      if (!$ch->valor()) continue;

      list($x, $k) = explode(Escritor_html::csubnome, $ch->nome());

      if ($s != null) $s .= ", ";

      $s .= $k;
    }

    return $s;
  }

  private function html_etq(int $i) {
    if (!$this->visible) return "";
    
    $_ch = $this->obxetos("chEtq_{$i}");

    if (count($_ch) == 0) return "";

    $html = null;

    foreach($_ch as $ch) $html .= "<div>" . $ch->html() . "</div>";

    if ($html == null) return "";

    return "<div class='cbusca-filtro__titulo'>{$this->_titetq[$i]}</div>" . $html;
  }

  private function inicia(ICatalogo_fonte $icp, bool $vpa) {
    $this->inicia_hfiltrar();
    $this->inicia_txt     ();

    $cbd = new FS_cbd();

    $this->inicia_etq(1, $cbd, $icp, $vpa);
    $this->inicia_etq(2, $cbd, $icp, $vpa);
    $this->inicia_etq(3, $cbd, $icp, $vpa);
  }

  private function inicia_hfiltrar() {
    $t = new Hidden("hfiltrar");

    $t->pon_atr("ctlg_busca_hfiltrar", "1");

    $this->pon_obxeto( $t );
  }

  private function inicia_txt() {
    $t = new Text("txt");

    $t->pon_atr("ctlg_busca_etq_0", "1");

    $t->placeholder = "Buscar";

    $this->pon_obxeto( $t );
  }

  private function operacion_hfiltrar(EstadoHTTP $e) {
    if ( ($hfiltrar = $this->obxeto("hfiltrar")) == null )  return null;
    
    if ( !$hfiltrar->control_evento() )  return null;
    
    
    //* parsea hfiltrar.
    $_hfiltrar = parse_url($hfiltrar->valor());
    
    parse_str($_hfiltrar['query'], $_hfiltrar);
    
    $_f = [];
    if (isset($_hfiltrar["f0"])) $_f["f0"] = $_hfiltrar["f0"];
    if (isset($_hfiltrar["f1"])) $_f["f1"] = $_hfiltrar["f1"];
    if (isset($_hfiltrar["f2"])) $_f["f2"] = $_hfiltrar["f2"];
    if (isset($_hfiltrar["f3"])) $_f["f3"] = $_hfiltrar["f3"];
    
    $this->pai->control_url( [$_hfiltrar["ctlg"], 1, "_f" => $_f] );
    
    
    $this->pai->obxeto("clista")->preparar_saida($e->ajax());
    
    $e->ajax()->pon_ok(true, "trwCat_listar()" );
    $e->ajax()->pon_ok(true, "trwCat_colorea()");
    
      
    return $e;
  }


  final protected static function __checker(int $i, string $etq):Checkbox {
    $ch = new Checkbox("chEtq_{$i}", false, $etq);

    return $ch;
  }
}

