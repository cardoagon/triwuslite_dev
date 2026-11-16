<?php


class Elmt_carrusel extends AElemento {

  public $site_obd       = null;
                       
  public $_elmt          = null;

  public $_elmt_sup      = null;
                         
  public $columnas       = 1;
                         
  public $i              = 0;
                         
  public $u              = 0;
  public $id_idioma      = 0;
                         
  public $url_arquivos   = 0;
                         
  public $url_limpa      = false;

  public $frecuencia     = 0;
  public $ciclo          = 0;
  public $imx_proporcion = 0.6;
  public $puntos         = 0;

  public $mobil          = false;
                         
  public $editando       = -1;  //* -2 (Non), -1 (no editor), edit>0 (editando elmt 'edit')


  public function __construct(Elmt_carrusel_obd $pe_obd = null, $iditen_tipo = -1) {
    parent::__construct($pe_obd);

    $this->pon_obxeto(new Hidden("hrd"));
    $this->pon_obxeto(new Hidden("msx"));
    
    $this->pon_obxeto(self::__marco());
    
    if ($pe_obd == null) return;

    $this->frecuencia     = $pe_obd->atr("frecuencia"    )->valor;
    $this->ciclo          = $pe_obd->atr("ciclo"         )->valor;
    $this->imx_proporcion = $pe_obd->atr("imx_proporcion")->valor;
    $this->columnas       = $pe_obd->atr("columnas"      )->valor;
    $this->puntos         = $pe_obd->atr("puntos"        )->valor;

    $this->_elmt          = $pe_obd->a_elmt_obd($iditen_tipo);
  }
/*
  public function declara_css() {
    return array(Refs::url("css/owlcarousel/owl.theme.default.min.css"),
                 Refs::url("css/owlcarousel/owl.carousel.min.css")
                );
  }

  public function declara_js() {
    return array(Refs::url("js/owlcarousel/owl.carousel.js"),
                 Refs::url("js/carrusel-2.js")
                );
  }
*/
  public function js_callback() {
    return "trw_carrusel_ini(" . $this->json_carrusel() . ")";
  }

  public function config_usuario(IEfspax_xestor $e = null) {
    AElemento::config_usuario($e);

    $this->site_obd     = $e->__site_obd(null, new FS_cbd());
        
    $this->u            = $e->usuario();
    $this->id_idioma    = $e->id_idioma();

    $this->mobil        = $e->config()->atr("tipo")->valor == "mobil";

    $this->url_arquivos = $e->url_arquivos();
    $this->url_limpa    = $e->url_limpa;
  }

  public function elmt_obd() {
    $ec = $this->prototipo_obd();

    $ec->atr("frecuencia"    )->valor = $this->frecuencia;
    $ec->atr("ciclo"         )->valor = $this->ciclo;
    $ec->atr("imx_proporcion")->valor = $this->imx_proporcion;
    $ec->atr("columnas"      )->valor = $this->columnas;
    $ec->atr("puntos"        )->valor = $this->puntos;

    $_elmt_aux1 = $this->_elmt;
    $_elmt_aux2 = array();
    for ($i = 0; $i < count($_elmt_aux1); $i++) {
          if ( ($id_elmt = $_elmt_aux1[$i]->atr("id_elmt")->valor) == null );
      elseif ( isset($this->_elmt_sup[$id_elmt]) ) {
        $_elmt_aux1[$i]->modificado = true;
        
        $_elmt_aux1[$i]->atr("id_elmt")->valor = -1 * $id_elmt;
      }


      $_elmt_aux2[] = $_elmt_aux1[$i];
    }

    $ec->a_elmt_obd( $_elmt_aux2 );

    $ec = $this->obd_base($ec);
    
    return $ec;
  }

  public function prototipo_obd() {
    return new Elmt_carrusel_obd();
  }

  public function prototipo_iten_obd() {
    return new Elmt_carrusel_iten_obd();
  }

  protected function pon_btnr() {
    $this->pon_obxeto( new Btnr_elmt_carrusel() );
  }

  public function editor() {
    return new Editor_ecarrusel($this);
  }

  public function num_cols($n = 0) {
    if ($n == 0) return $this->columnas;

    $this->columnas = $n;


    return $n;
  }

  public function numitens($eliminados = true) {
    $n = (is_array($this->_elmt)) ? count( $this->_elmt ) : 0;

    if ($eliminados) return $n; //* contamos os eliminados

    $ct_sup = is_array($this->_elmt_sup)?count( $this->_elmt_sup ):0;

    $n -= $ct_sup;


    if ($n <= 0) $n = 0;

    return $n;
  }

  public function a_elmt($eliminados = true) {
    if ($eliminados) return $this->_elmt;


    if ($this->numitens(false) == 0) return null;

    if ($eliminados) return $this->_elmt;

    $_elmt     = null;
    $_elmt_aux = $this->_elmt;
    foreach($_elmt_aux as $k=>$elmt) {
      $id_elmt = $elmt->atr("id_elmt")->valor;
      
      if ( isset($this->_elmt_sup[$id_elmt]) ) continue;

      $_elmt[$k] = $elmt;
    }

    return $_elmt;
  }

  public function operacion(EstadoHTTP $e) {
//~ echo $e->evento()->html();

    if ($this->obxeto("hrd")->control_evento()) return $this->operacion_hrd( $e );
 

    return parent::operacion($e);
  }

  public function preparar_saida(Ajax $a = null) {
    if ($a == null) {
      parent::preparar_saida();

      return;
    }

    $illa  = $this->obxeto("illa");
    $msx   = $this->obxeto("msx");
    $marco = $this->obxeto("marco");
    $js    = $this->js_callback();

    $marco->post($this->html_msx_0());

    $html_pcontrol = "";

    if ($this->pcontrol) {
      $html_pcontrol = $this->obxeto("hrd")->html() . 
                       $this->obxeto("msx")->html();
    }


    $illa ->post( $this->html_illa($html_pcontrol . $marco->html()) );
    

    $a->pon($illa, true, $js);

    $this->obxeto("illa")->post(null); //* liberamos buffer.
  }

  protected function html_propio() {
    //~ if (!$this->visible) return "";

    
    $id    = $this->nome_completo();
    $nc    = ($this->mobil)?1:(int)$this->num_cols();
    $ni    = $this->numitens(false);
    $f     = $this->frecuencia * 1000;
    $_elmt = $this->json_elmts();

    $this->obxeto("marco")->post($this->html_msx_0());


    $html_pcontrol = "";
    if ($this->pcontrol) {
      $html_pcontrol = $this->obxeto("hrd")->html() . 
                       $this->obxeto("msx")->html();
    }
    elseif ($ni == 0) {
      return "";
    }

    return
      $html_pcontrol . 
      $this->obxeto("marco")->html() . "
      <script type='text/javascript'>
        var oc = " . $this->json_carrusel() . ";
        
        trw_carrusel_ini(oc);
      </script>";
  }

  protected function html_msx_0() {
    return "
          <div style='color: #333333; line-height: 161%; padding: 11px 7px;'>
            Por algún motivo no se a completado la carga del carrusel, pincha el <b>botón recargar</b> para intentarlo de nuevo.
            <br /><br />
            <center>" . $this->obxeto("brecargar")->html() . "</center>
          </div>";
  }

  private function operacion_hrd(EstadoHTTP $e) {
    $id_paxina = $this->obxeto("hrd")->valor();

    return Efspax_xestor::operacion_redirect($e, $id_paxina);
  }

  private function json_carrusel() {
    $id    = $this->nome_completo();
    $nc    = ($this->mobil)?1:(int)$this->num_cols();
    $ni    = $this->numitens(false);
    $prop  = ($this->imx_proporcion <= 0)?"null":round($this->imx_proporcion, 3);
    $f     = $this->frecuencia * 1000;
    $c     = ($this->ciclo)?"1":"0";
    $p     = ($this->puntos)?"1":"0";
    $_elmt = $this->json_elmts();

    if ($this->pcontrol) {
      $edit = $this->editando;

      $this->editando = "-1"; //* reseteamos this.editando
    }
    else
      $edit  = "-2";
    

    return "{id:'{$id}', numcols:{$nc}, numitens:{$ni}, freq:{$f}, ciclo:{$c}, puntos:{$p}, prop:{$prop}, editando:{$edit}, td1_height:-1, _elmt:{$_elmt}}";
  }

  private function json_elmts() {
    $_a = null;
    
    if ($this->numitens(false) == 0) return json_encode($_a);


    $_elmt = $this->a_elmt(false);

    $cbd   = new FS_cbd(); 
    $i     = 0;
    
    foreach($_elmt as $elmt) {
      if ($elmt->atr("baixa")->valor == 1) continue;
      
      $_a[$i]['id_elmt'  ] = $elmt->atr("id_elmt")->valor;
      $_a[$i]['id_paxina'] = $elmt->atr("id_paxina")->valor;
      $_a[$i]['nome'     ] = $elmt->atr("nome" )->valor;
      $_a[$i]['notas'    ] = $elmt->atr("notas")->valor;
      $_a[$i]['imx'      ] = $elmt->url();
      $_a[$i]['arti'     ] = $elmt->info_arti($cbd);
      $_a[$i]['url'      ] = $elmt->atr("href")->valor;
      $_a[$i]['target'   ] = $elmt->atr("target")->valor;
      $i++;
    }


    return json_encode($_a);
  }

  private static function __marco() {
    $d = new Div("marco");

    $d->clase_css("default", "carrusel_marco");

    return $d;
  }
}

//******************************

class Editor_ecarrusel extends Edita_elmt {
  const ptw_0   = "ptw/paragrafo/elmt/editor_ecarrusel.html";
  const ptw_1   = "ptw/paragrafo/elmt/editor_ecarrusel_1.html";
  const ptw_2   = "ptw/paragrafo/elmt/editor_ecarrusel_2.html";

  const interna = "<span class='ecarrusel_interna'>interna</span>";

  
  public static $_freq = array("0"=>"No", "5"=>"Rápido", "9"=>"Normal", "15"=>"Lento");
  public static $_cols = array("1"=>"1", "2"=>"2", "3"=>"3", "4"=>"4", "5"=>"5");

  private $editando   = -4;   //* >= -4
  private $edita_elmt = null;
  
  private $scan_imx   = null;
  
  public function __construct(Elmt_carrusel $elmt) {
    parent::__construct(Ico::bcarrusel, "Carrusel", self::ptw_0, $elmt);


    $this->pon_obxeto( new Elmt_carrusel_lentradas($elmt) );


$this->pon_obxeto( new Param("paux") );


    $this->pon_obxeto( new Select("tfrecuencia", self::$_freq)   );
    $this->pon_obxeto( new Checkbox("chciclo", false, "Movimiento en bucle")   );
    $this->pon_obxeto( new Checkbox("chpuntos", false, "Mostrar puntos de navegación")   );
    $this->pon_obxeto( new Select("tcolumnas"  , self::$_cols)   );
    $this->pon_obxeto( Panel_fs::__text("tetq", 22, 99) );
    
    //~ $this->pon_obxeto( Panel_fs::__text("timx_proporcion", 1, 4) );
    $this->pon_obxeto( Panel_fs::__dlImxForma("timx_proporcion") );
    

    $this->pon_obxeto( self::__lmais() );
    $this->pon_obxeto( self::__lscan() );
    $this->pon_obxeto( self::__lurl () );

    $this->pon_obxeto( new Param("scan_url" ) );
    $this->pon_obxeto( new Param("tipo_url" ) );
    
    $this->pon_obxeto( self::__fimaxe( null ) );
    $this->pon_obxeto( new Hidden("msx") );
    $this->pon_obxeto( new Hidden("acepta_scan") );
    $this->pon_obxeto( Panel_fs::__text("edita_titulo", 33, 255) );
    $this->pon_obxeto( Panel_fs::__text("edita_url"   , 33, 255) );
    $this->pon_obxeto( new Textarea("edita_notas", 1, 1) );

    $this->pon_obxeto(new Select("target", array("_self"=>"Misma ventana", "_blank"=>"Nueva ventana")));



    $this->obxeto("timx_proporcion")->post( $elmt->imx_proporcion );
    $this->obxeto("tcolumnas"      )->post( $elmt->num_cols() );
    $this->obxeto("chciclo"        )->post( ($elmt->ciclo  == 1)?true:false );
    $this->obxeto("chpuntos"       )->post( ($elmt->puntos == 1)?true:false );
    $this->obxeto("tfrecuencia"    )->post( ($elmt->frecuencia == null)?"0":$elmt->frecuencia );
    $this->obxeto("tetq"           )->post( $elmt->enome) ;


    $this->obxeto("edita_titulo"   )->style("default", "");
    $this->obxeto("edita_url"      )->style("default", "");
    $this->obxeto("edita_notas"    )->style("default", "");
  }

  public function declara_baceptar() {
    $b = Panel_fs::__baceptar();

    $b->pon_eventos("onclick", "trw_carrusel_editor_bAceptar_click(this)");

    return $b;
  }

  public function validar():?string {
    if ($this->obxeto("timx_proporcion")->validar()) return null;
    
    $p = trim($this->obxeto("timx_proporcion")->valor());
    
    if (($p = Imaxe_obd::valida_proporcion($p)) === null) return "La proporción de la imagen no es válida.";
    
    $this->obxeto("timx_proporcion")->post($p);
    
    return null;
  }

  public function operacion(EstadoHTTP $e) {
    //$this->post_paux("", true);

    $this->obxeto("scan_url")->post(null);

    if ( $this->obxeto("bmais"      )->control_evento() ) return $this->operacion_editar($e, -1);
    if ( $this->obxeto("lscan"      )->control_evento() ) return $this->operacion_editar($e, -2);
    if ( $this->obxeto("acepta_scan")->control_evento() ) return $this->operacion_editar($e, -3);

    if ( $this->obxeto("edita_url"  )->control_evento() ) return $this->operacion_edita_url($e);

    if ($this->obxeto("fimaxe")->control_evento()) {
      $url_upload = $this->obxeto("fimaxe")->post_f();
      
      $this->edita_elmt->post_url($url_upload);
      

      $this->preparar_saida_edita( $e->ajax() );
      
      return $e;
    }

    if ($this->obxeto("msx")->control_evento()) return $this->operacion_msx($e);

    if (($e_aux = $this->obxeto("lentradas")->operacion($e)) != null) return $e_aux;


    return parent::operacion($e);
  }

  public function operacion_aceptar(Efs_admin $e) {
    if (($erro = $this->validar()) != null) {
      $e->post_msx($erro);
      
      return null;
    }
    
    $this->elmt->_elmt          = $this->obxeto("lentradas")->_elmt;
    $this->elmt->_elmt_sup      = $this->obxeto("lentradas")->_elmt_sup;

    $this->elmt->enome          = $this->obxeto("tetq"           )->valor();
    $this->elmt->frecuencia     = $this->obxeto("tfrecuencia"    )->valor();
    $this->elmt->ciclo          = ($this->obxeto("chciclo"       )->valor())?"1":"0";
    $this->elmt->puntos         = ($this->obxeto("chpuntos"      )->valor())?"1":"0";
    $this->elmt->imx_proporcion = $this->obxeto("timx_proporcion")->valor();

    $this->elmt->num_cols( $this->obxeto("tcolumnas")->valor() );

    return parent::operacion_aceptar($e);
  }

  public function operacion_cancelar(Epaxina_edit $e) {
    if ($this->editando == -4) return parent::operacion_cancelar($e);
    
    $this->ptw      = self::ptw_0;
    $this->editando = -4;

    $this->readonly(false);

    $this->pai->preparar_saida( $e->ajax() );
    

    return $e;
  }

  public function operacion_editar(Epaxina_edit $e, $editando = -1) {
//~ $this->post_paux("inicia-editar", true);
//~ $this->post_paux("editando::$editando", true);

    $this->editando   = $editando;
    
    //~ $this->pon_obxeto( self::__fimaxe( Efs::url_tmp($this->elmt->url_arquivos) ) ); //* reiniciamos File compartido.
    $this->pon_obxeto( self::__fimaxe( Refs::url_arquivos(Refs::url_tmp . "/") ) ); //* reiniciamos File compartido.

    if ($editando > -1) {
      $this->ptw        = self::ptw_1;
      
      $this->edita_elmt = $this->elmt->prototipo_iten_obd();
      
      $this->edita_elmt->clonar( $this->obxeto("lentradas")->_elmt[$editando] );
    }
    elseif ($editando == -1) {
      $this->ptw        = self::ptw_1;
      
      $this->edita_elmt = $this->inicia_iten_obd($e->id_site);
    }
    elseif ($editando == -2) {
      $this->ptw        = self::ptw_2;
      
      $this->edita_elmt = $this->inicia_iten_obd($e->id_site);

      $this->readonly(true);
    }
    else { //* ($editando == -3)
      $this->ptw        = self::ptw_1;
    }
    
                                 
    $this->obxeto("edita_titulo" )->post( $this->edita_elmt->atr("nome"  )->valor );
    $this->obxeto("edita_url"    )->post( $this->edita_elmt->atr("href"  )->valor );
    $this->obxeto("target"       )->post( $this->edita_elmt->atr("target")->valor );
    $this->obxeto("edita_notas"  )->post( $this->edita_elmt->atr("notas" )->valor );


    if ($this->edita_elmt->atr("id_paxina")->valor > 0) {
      $this->obxeto("edita_url")->readonly = true;
      $this->obxeto("tipo_url" )->post(self::interna);
    }
    else {
      $this->obxeto("edita_url")->readonly = false;
      $this->obxeto("tipo_url" )->post(null);
    }
  
    $this->preparar_saida_edita( $e->ajax() );


    return $e;
  }

  public function operacion_edita_url(Epaxina_edit $e) {
    $this->scan_imx =null;
    
    //* 0.- valida $href
    $href = $this->obxeto("edita_url")->valor();

    if (!Valida::url($href)) {
      $e->post_msx("ERROR, la URL no es válida.");
      
      $this->preparar_saida($e->ajax());

      return $e;
    }

    //* 1.- intenta ler os atributos meta da href

    $phm = Parse_Html_meta::parse_url($href);
    
    if ($phm == null) {
      $e->post_msx("ERROR, la URL no es válida.");
      
      $this->preparar_saida($e->ajax());
      
      return $e;
    }

//~ echo "<pre>" . print_r($phm, 1) . "</pre>";

    $_meta = $phm->_content("property");

//~ echo "<pre>" . print_r($_meta, 1) . "</pre>";


    $this->scan_imx                            = $_meta["og:image"];
    $this->edita_elmt->atr("id_paxina")->valor = "0";
    $this->edita_elmt->atr("nome"     )->valor = $_meta["og:title"];
    $this->edita_elmt->atr("href"     )->valor = $href;
    $this->edita_elmt->atr("notas"    )->valor = $_meta["og:description"];
    
//~ echo Refs::url_arquivos( Refs::url_tmp . "/" . File::calcula_nome($_meta["og:image"]) ); . "<br>";

    $url_tmp = Refs::url_arquivos( Refs::url_tmp . "/" . File::calcula_nome($_meta["og:image"]) );

    
    if (Parse_Html_meta::imxscan($_meta["og:image"], $url_tmp)) 
      $this->edita_elmt->post_url($url_tmp);
    else
      $this->edita_elmt->post_url(null);
    
    

    $cbd  = new FS_cbd();

    //* 2.- busca páxina triwus
    $p = Paxina_obd::inicia_url($cbd, $href, $this->elmt->site_obd);

    if ($p != null) {
      $this->edita_elmt->post_paxina($p, $cbd);
    }
    else {
      $this->edita_elmt->atr("id_paxina")->valor = "0";
    }

    $this->preparar_saida_scan( $e->ajax() );

    
    return $e;
  }

  public function operacion_msx(Epaxina_edit $e) {
    $fimx = $this->obxeto("fimaxe");

//~ $this->post_paux( "fimx->f()::" . $fimx->f() );
//~ $this->post_paux( "msx::" . $this->obxeto("msx")->valor() );
//~ $this->post_paux( "edita_elmt->url()::" . $this->edita_elmt->url() );
//~ $this->post_paux( "this.editando::" . $this->editando );
     

    if (($m = $this->obxeto("msx")->valor()) != -1) {
      $fimx->vincular_f( $this->edita_elmt->url() );
      
      if ( ($f = $fimx->f()) != null ) {
        list($x, $y, $w, $h) = explode(Escritor_html::csubnome, $m);
        
        $fimx->recortar($x, $y, $w, $h); 
        
        $this->edita_elmt->post_url( $fimx->f() );
      }
    }
    
    $this->edita_elmt->atr("nome"   )->valor = $this->obxeto("edita_titulo")->valor();
    $this->edita_elmt->atr("href"   )->valor = $this->obxeto("edita_url"   )->valor();
    $this->edita_elmt->atr("target" )->valor = $this->obxeto("target"      )->valor();
    $this->edita_elmt->atr("notas"  )->valor = $this->obxeto("edita_notas" )->valor();

    $this->edita_elmt->modificado = true;


    $this->obxeto("lentradas")->post_elmt($this->edita_elmt, $this->editando, $fimx);


    return $this->operacion_cancelar($e);
  }

  protected function inicia_iten_obd($id_site) {
    $elmt = $this->elmt->prototipo_iten_obd();

    $elmt->atr("id_pai"   )->valor = $this->elmt->id_elmt;
    $elmt->atr("id_site"  )->valor = $id_site;
    $elmt->atr("ref_site" )->valor = $this->elmt->site_obd->atr("nome")->valor;
    $elmt->atr("id_paxina")->valor = "0";


    return $elmt;
  }

  protected function __editor_style() { 
    return new Editor_style_nulo();
  }

  private function preparar_saida_edita(Ajax $a) {
    $this->pai->preparar_saida( $a );
    
    $p = $this->obxeto("timx_proporcion")->valor();

    $a->pon_ok( true, "trw_carrusel_editor_inicia('" . $this->edita_elmt->url() . "', {$p})" );
  }

  private function preparar_saida_scan(Ajax $a) {
    $this->obxeto("scan_url" )->post( $this->scan2html() );
    $this->obxeto("edita_url")->post( null );
    
    $this->readonly(false);
    
    $this->pai->preparar_saida( $a );
  }

  private function scan2html() {
    $url      = $this->edita_elmt->url();
    $url_tipo = "";
    $imx_info = "";

    $href     = $this->edita_elmt->atr("href")->valor;
    $href     = "<a href='{$href}' title='Abrir' target='_blank' style='color: #0000ba;'>{$href}</a>";

    if ($this->edita_elmt->atr("id_paxina")->valor > 1) $url_tipo = self::interna;

    if ($this->edita_elmt->atr("url")->valor != null) $imx_info = self::html_imxprop($url, $this->scan_imx);
    
    $tabla = "<div>
              <table class='ecarrusel_0' border=0 cellspacing=0 cellpadding=0>
                <tr>
                  <td><b>URL</b>{$url_tipo}</td>
                  <td colspan=2>{$href}</td>
                </tr>
                <tr>
                  <td style='width: 5em;' valign='top'><b>Foto</b></td>
                  <td style='width: 7em;' align='center'><a href='{$url}' title='Abrir' target='_blank'><img src='{$url}'/></a></td>
                  <td width=41% align='center'>{$imx_info}</td>
                </tr>
                <tr>
                  <td><b>Titular</b></td>
                  <td colspan=2>" . $this->edita_elmt->atr("nome")->valor . "</td>
                </tr>
                <tr>
                  <td valign='top'><b>Entradilla</b></td>
                  <td colspan=2>" . $this->edita_elmt->atr("notas")->valor . "</td>
                </tr>
              </table>
            </div>";
           
    return "<fieldset class='fset-editor'>
              <legend class='editor_legend'>Resultado</legend>
              <div class='trw-form-1'>{$tabla}</div>
            </fieldset>";
    //~ return "<div class='trw-form-1'>{$tabla}</div>";
  }

  private function informe_scanURL($url_tmp_0, $url_tmp_1) {
    $m = $this->elmt->url_arquivos . "<br>" .
         $this->elmt->url_tmp_0 . "<br>" .
         $this->elmt->url_tmp_1 . "<br>";
         
    $this->obxeto("paux")->post($m);
  }

  private function post_paux($m, $limpar = false) {
    $paux = "";
    if (!$limpar) {
      $paux = $this->obxeto("paux")->valor() . "<br>";
    }
    
    $this->obxeto("paux")->post( $paux . $m );
  }

  private static function __fimaxe($src_tmp) {
    $f = new File("fimaxe", $src_tmp, 1024 * 1024 * 3);

    $f->clase_css("default", "file_apagado");


    $f->accept = "image/gif, image/jpg, image/jpeg, image/png, image/webp";


    return $f;
  }

  private static function __lmais() {
    $o = Panel_fs::__bmais("bmais", "Añadir");
    
    //~ $o->envia_submit("onclick");
    $o->envia_ajax("onclick");

    return $o;
  }

  private static function __lscan() {
    $o = Panel_fs::__bmais("lscan", "Escanear");
    
    //~ $o->envia_submit("onclick");
    $o->envia_ajax("onclick");

    return $o;
  }

  private static function __lurl() {
    $o = Panel_fs::__bmais("lurl", "Escanear");
    
    $o->pon_eventos("onclick", "trw_carrusel_editor_lurl_click(this)");

    return $o;
  }

  private static function html_imxprop($src, $scan_imx) {
    $a_props = getimagesize($src);
    
    $scan_imx = ($scan_imx == null) ? "--" : "<a href='{$scan_imx}' title='Abrir' target='_blank' style='color: #0000ba;'>abrir</a>";

    $s = "<div class='ecarrusel_1'>
              <div>Ancho</div>
              <div>{$a_props[0]}&nbsp;px</div>
              <div>Altura</div>
              <div>{$a_props[1]}&nbsp;px</div>
              <div>Mime</div>
              <div>{$a_props['mime']}</div>
              <div>Tamaño</div>
              <div>" . File::html_bytes(filesize($src)) . "</div>
              <div>URL</div>
              <div>{$scan_imx}</div>
         </div>";
    //~ $s = "<table class='ecarrusel_1' cellspacing=0 cellpadding=2>
            //~ <tr>
              //~ <td width=44px align='right'>Ancho:</td>
              //~ <td style='font-weight: bold;'>{$a_props[0]}&nbsp;px</td>
            //~ </tr>
            //~ <tr>
              //~ <td align='right'>Altura:</td>
              //~ <td style='font-weight: bold;'>{$a_props[1]}&nbsp;px</td>
            //~ </tr>
            //~ <tr>
              //~ <td align='right'>Mime:</td>
              //~ <td style='font-weight: bold;'>{$a_props['mime']}</td>
            //~ </tr>
            //~ <tr>
              //~ <td align='right'>Tama&ntilde;o:</td>
              //~ <td style='font-weight: bold;'>" . File::html_bytes(filesize($src)) . "</td>
            //~ </tr>
            //~ <tr>
              //~ <td align='right'>URL</td>
              //~ <td style='font-weight: bold;'>{$scan_imx}</td>
            //~ </tr>
         //~ </table>";



    return $s;
  }
}




/** Lista de itens.
 * 
 *****************************/

class Elmt_carrusel_lentradas extends    FS_lista
                              implements Iterador_bd {
  public $_elmt      = null;
  public $_elmt_sup  = [];
                     
  public $i          = -1;

  public function __construct(Elmt_carrusel $elmt) {
    parent::__construct("lentradas", new Elmt_carrusel_lentradas_ehtml());

    $this->_elmt      = $elmt->_elmt;
    $this->_elmt_sup  = $elmt->_elmt_sup;
  }

  protected function html_baleiro() {
    if (!$this->visible) return "";

    return "<div class='lista_baleira txt_adv'>Carrusel sin entradas.</div>";
  }

  public function numFilas() {
    $ct_a = ( is_array($this->_elmt    ) )?count($this->_elmt    ):0;
    $ct_b = ( is_array($this->_elmt_sup) )?count($this->_elmt_sup):0;
    
    return $ct_a - $ct_b;
  }

  public function __count() {
    return $this->numFilas();
  }
  
  public function descFila() {}
  
  public function post_elmt(Elmt_carrusel_iten_obd $elmt, int $i) {    
    if ($i > -1) {
      $this->_elmt[$i] = $elmt;
      
      return;
    }
    
    if (count($this->_elmt) == 0) {
      $this->_elmt     [0] = $elmt;

      return;
    }
    
    array_unshift($this->_elmt     , $elmt);
  }

  public function next() {
    $this->i++;
    
    if ($this->i == count($this->_elmt)) {
      $this->i = -1;

      return false;
    }

    $elmt = $this->_elmt[$this->i];

    if (isset( $this->_elmt_sup[$elmt->atr("id_elmt")->valor] )) return $this->next();

    if (($i = $this->i) == 0) $i = "0";

    return array($i, $elmt);
  }

  public function operacion(EstadoHTTP $e) {
    $evento = $e->evento();

    //~ echo $evento->html();

    if ($this->control_fslevento($evento, "beditar")) return $this->pai->operacion_editar($e, $evento->subnome(0));

    if ($this->control_fslevento($evento, "chBaixa")) return $this->operacion_chbaixa($e);

    if ($this->control_fslevento($evento, "bsup"   )) return $this->operacion_bsup($e);

    
    return parent::operacion($e);
  }

  protected function __iterador() {
    return $this;
  }

  private function operacion_chbaixa(Efs_admin $e) {
    $id_l = $e->evento()->subnome(0);
    
    $b = ($this->_elmt[ $id_l ]->atr("baixa")->valor == 1)?"0":1;

    $this->_elmt[ $id_l ]->atr("baixa")->valor = $b;
    
    $this->_elmt[ $id_l ]->modificado = true;


    $e->ajax()->pon_ok();

    return $e;
  }

  private function operacion_bsup(Efs_admin $e) {
    $id_l = $e->evento()->subnome(0);
    
    //* (id_elmt != null) => asigna elmt ao $this->_elmt_sup.
    if (($id_elmt = $this->_elmt[ $id_l ]->atr("id_elmt")->valor) != null) {
      $this->_elmt_sup[ $id_elmt ] = 1;

      $this->preparar_saida($e->ajax());

      return $e;
    }

    $t = count($this->_elmt) - 1;
    for ($i = $id_l; $i < $t; $i++) {
      $this->_elmt     [$i] = $this->_elmt     [$i + 1];
    }
    
    unset($this->_elmt     [$t]);    


    $this->preparar_saida($e->ajax());

    return $e;
  }
}

//---------------------------------

class Elmt_carrusel_lentradas_ehtml extends FS_ehtml {
  private $i;
  
  public function __construct() {
    parent::__construct();
    
    $this->style_table = "display: flex; flex-direction: column; margin-top: 0.3em; border: 1px solid #333;";
  }

  protected function inicia_saida() {
    return "
      <div style='{$this->style_table}'>\n";
  }

  protected function cabeceira($df = null) {
    $this->i = -1; 
    return self::__sep();
  }

  protected function linha_detalle($df, $f) {
    //~ return "<pre>" . print_r($f->a_resumo(), true) . "</pre>";
    
    $this->i++;

    $ef = "";


    return "<div style='display: flex; align-items: center; 
                        padding: 3px 0.5em 3px 0;border-bottom: 1px solid #ccc; margin-bottom: 3px;'>
              <div style='width: 2em; margin-left: 1em;'>" . self::__chBaixa($this, $f) . "</div>
              <div " . self::__imx($f) . "></div>
              <div style='flex-grow: 1;'>
                <div style='margin-bottom: 0.2em;'>" . self::__nome($f) . "</div>
                <div style='margin-top: 0.2em;'>" . $ef . "</div>
              </div>
              <div style='width: 4em; text-align:center;'>" . self::__bEditar($this, $f) . "</div>
              <div style='width: 4em; text-align:center;'>" . self::__bSup   ($this, $f) . "</div>
            </div>" . self::__sep();
  }

  private static function __sep() {
    //~ return "<tr height=3px><td colspan=5 style='font-size: 0;'>&nbsp;</td></tr>";
    return "";
  }

  protected function finaliza_saida() {
    return "
      </div>";
  }

  private static function __imx($f) {
    $imx = $f[1]->url();

    $s = "style='height: 55px; width: 55px; min-width: 55px;
                 margin-right: 0.7em;
                 border: 1px solid transparent;
                 background-image: url(\"{$imx}\"); background-position: center center; background-size: cover; background-repeat: no-repeat;'";


    return $s;
  }

  private static function __nome($f) {
    if (($n = $f[1]->atr("nome")->valor) == null) $n = "Sin título";
    
    return "<b>" . Valida::split($n, 91) . "</b>";
  }

  private static function __href($f) {
    if (($href = $f[1]->atr("href")->valor) == null) return "Sin enlace";
    
    //~ $href_1 = Valida::split($href, 91);
    $href_1 = "enlace";

    return "<a href='{$href}' target='_blank' onmouseover='this.style.textDecoration=\"underline\"' onmouseout='this.style.textDecoration=\"initial\"'>{$href_1}</a>";
  }

  private static function __chBaixa(Elmt_carrusel_lentradas_ehtml $ehtml, $f) {
    
    $b = new Checkbox("chBaixa", $f[1]->atr("baixa")->valor != 1);
    
    $b->readonly = $ehtml->xestor->readonly;
    //~ $b->readonly = true;

    $b->envia_ajax("onclick");
    //~ $b->envia_submit("onclick");

    return $ehtml->__fslControl($b, $f[0])->html();
  }

  private static function __bEditar(Elmt_carrusel_lentradas_ehtml $ehtml, $f) {
    if ($ehtml->xestor->readonly) return "";
    
    $b = Panel_fs::__beditar("beditar", null);

    $b->envia_ajax("onclick");
    //~ $b->envia_submit("onclick");

    return $ehtml->__fslControl($b, $f[0])->html();
  }

  private static function __bSup(Elmt_carrusel_lentradas_ehtml $ehtml, $f) {
    if ($ehtml->xestor->readonly) return "";
    
    $b = Panel_fs::__bsup("bsup", null);

    $b->envia_ajax("onclick");
    //~ $b->envia_submit("onclick");

    return $ehtml->__fslControl($b, $f[0])->html();
  }
}

