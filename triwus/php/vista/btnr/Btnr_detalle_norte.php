<?php


class Btnr_detalle_norte extends Btnr {

  private $p          = null;

  public function __construct(Epaxina_edit $e, Paxina_obd $p) {
    parent::__construct("d_btnr_n", "ptw/btnr/norte.html");

    $this->p = $p;
    //~ $this->c = $e->config();

    $this->pon_obxeto(self::__iten("bpaxina", "P&aacute;gina"));

    $this->pon_obxeto(self::__iten("binsertar", "Insertar"));
    $this->pon_obxeto(new Btnr_insertar( $p ));

    $this->pon_obxeto(self::__iten("bpanoramix", "Panorámix"));


    $this->obxeto("bpaxina"   )->pon_eventos("onclick", "editor_vm_esperar_abrir(this, 'vm_esperar', '', null)");
    $this->obxeto("binsertar" )->pon_eventos("onclick", "detalle_btnr_norte_bins(this)");
    //~ $this->obxeto("bpanoramix")->envia_submit("onclick");
    $this->obxeto("bpanoramix")->pon_eventos("onclick", "editor_vm_esperar_abrir(this, 'vm_esperar', '', null)");
  }

  public function config_lectura() {
    $this->obxeto("binsertar"    )->visible = false;
    $this->obxeto("btnr_insertar")->visible = false;
  }

  public function config_edicion() {
    $this->obxeto("binsertar"    )->visible = true;
    $this->obxeto("btnr_insertar")->visible = true;
  }

  public function config_usuario(IEfspax_xestor $e = null) {
    $this->visible = !$e->usuario()->en_custodia();
  }

  public function operacion(EstadoHTTP $e) {
//~ echo $e->evento()->html();

    if ($this->obxeto("bpanoramix")->control_evento()) {
      //~ $path = $e->url_panoramix($this->p->atr("id_site")->valor, $this->p->atr("id_paxina")->valor);
      
      $this->pon_obxeto(new Editor_panoramix($this->p->atr("id_site")->valor, $this->p->atr("id_paxina")->valor));

      $e->obxeto("detalle_btnr_oeste")->pon_editor($e, $this->obxeto("editor"));

      return $e;
    }

    if ($this->obxeto("bpaxina")->control_evento()) {
      $this->pon_obxeto(new Editor_paxina());
    
      $this->obxeto("editor")->pon_paxinfo($e);

      $e->obxeto("detalle_btnr_oeste")->pon_editor($e, $this->obxeto("editor"));

      return $e;
    }

    
    if (($editor = $this->obxeto("editor")) != null)
      if (($e_aux = $editor->operacion($e)) != null) return $e_aux;
      

    //~ if (($e_aux = $this->obxeto("cvisormobil")->operacion($e)) != null) return $e_aux;

    

    return null;
  }

  public static function __iten($nome, $txt, $title = null) {
    $b = new Link($nome, $txt);

    $b->title = $title;

    $b->clase_css("default", "texto_cab0");
    $b->clase_css("readonly", "texto_cab0");

    $b->style("default", "padding: 0 11px;");
    $b->style("readonly", "padding: 0 55px;");

    $b->pon_eventos("onmouseover", "this.style.textDecoration = 'underline'");
    $b->pon_eventos("onmouseout", "this.style.textDecoration = 'initial'");

    return $b;
  }
}

//***************************************
/*
final class Btnr_detalle_norte_ventas extends Btnr_detalle_norte {

  public function __construct(Epaxina_edit $e, Paxina_ventas_obd $p) {
    parent::__construct($e, $p);

    $this->ptw = "ptw/btnr/norte_ventas.html";

    $this->pon_obxeto(self::__iten("bAdd", "[+] Art&iacute;culos"));

    $this->obxeto("bAdd")->pon_eventos("onclick", "editor_vm_esperar_abrir(this, 'vm_esperar', '', null)");

    $this->obxeto("bAdd")->visible = $p->atr("id_prgf_indice")->valor > 0;
  }

  public function operacion(EstadoHTTP $e) {
    if ($this->obxeto("bAdd")->control_evento()) return $this->operacion_bAdd($e);

    return parent::operacion($e);
  }

  private function operacion_bAdd(Epaxina_edit $e) {
    $prgf = $e->obxeto("detalle")->iprgf;

    $e->obxeto("detalle_btnr_oeste")->pon_editor($e, new Ediventas_catalogo($prgf));

    $prgf->preparar_saida($e->ajax());

    return $e;
  }
}
*/
//***************************************

final class Btnr_detalle_norte_novas extends Btnr_detalle_norte {

  public function __construct(Epaxina_edit $e, Paxina_novas_obd $p) {
    parent::__construct($e, $p);

    $this->ptw = "ptw/btnr/norte_novas.html";

    $this->pon_obxeto(self::__iten("bAdd", "[+] Nueva"));

    $this->pon_obxeto(new Param("bFil"));

    $this->obxeto("bAdd")->envia_ajax("onclick");
    //~ $this->obxeto("bAdd")->envia_submit("onclick");
    
    $this->obxeto("bAdd")->visible = ($p->atr("id_prgf_indice")->valor > 0);
  }

  public function operacion(EstadoHTTP $e) {
    if ($this->obxeto("bAdd")->control_evento()) return $this->operacion_bAdd($e);

    return parent::operacion($e);
  }

  private function operacion_bAdd(Epaxina_edit $e) {
    $iprgf = $e->obxeto("detalle")->iprgf;

    $e_aux = $iprgf->operacion_bAdd($e);

    $e_aux->cc_pon($this->obxeto("bAdd"));

    $e_aux->obxeto("detalle_btnr_este")->preparar_saida($e_aux->ajax());

    $iprgf->preparar_saida($e_aux->ajax());

    return $e_aux;
  }

}

//***************************************
/*
final class Btnr_detalle_norte_novas2 extends Btnr_detalle_norte {

  public function __construct(Epaxina_edit $e, Paxina_novas2_obd $p) {
    parent::__construct($e, $p);

    $this->ptw = "ptw/btnr/norte_novas.html";

    $this->pon_obxeto(self::__iten("bAdd", "[+] Nueva"));
    $this->pon_obxeto(self::__iten("bFil", "Filtrar"));

    $this->obxeto("bAdd")->envia_ajax("onclick");
    //~ $this->obxeto("bAdd")->envia_submit("onclick");

    $this->obxeto("bFil")->envia_ajax("onclick");
    //~ $this->obxeto("bFil")->envia_submit("onclick");
    
    $this->obxeto("bAdd")->visible = ($p->atr("id_prgf_indice")->valor > 0);
    $this->obxeto("bFil")->visible = ($p->atr("id_prgf_indice")->valor > 0) && ($p->atr("tipo")->valor == "novas2");
  }

  public function operacion(EstadoHTTP $e) {
    if ($this->obxeto("bAdd")->control_evento()) return $this->operacion_bAdd($e);
    if ($this->obxeto("bFil")->control_evento()) return $this->operacion_bFil($e);

    return parent::operacion($e);
  }

  private function operacion_bAdd(Epaxina_edit $e) {
    return $e->obxeto("detalle")->iprgf->operacion_editar_nova($e, null);
  }

  private function operacion_bFil(Epaxina_edit $e) {
    return $e->obxeto("detalle")->iprgf->operacion_filtrar($e);
  }

}
*/
//***************************************

final class Btnr_insertar extends Btnr {
  public function __construct(Paxina_obd $p) {
    parent::__construct("btnr_insertar", "ptw/btnr/triangulo_norte.html");

    $this->pon_obxeto(self::__capa(false, "elmt_btnr_capa2"));

    $this->pon_obxeto(self::bAdd());

    $this->pon_obxeto(new Hidden("evento"));

    $this->pon_obxeto(self::boton("bDifucom2", Ico::bdifucom , "Pinchar y arrastrar para añadir un componente de compartir en Redes Sociales", 15, "&nbsp;&nbsp;R.&nbsp;Sociales"));
    $this->pon_obxeto(self::boton("bTexto"   , Ico::btxt     , Msx::titulo_btexto    , 15, "&nbsp;&nbsp;Texto"));
    $this->pon_obxeto(self::boton("bImaxe"   , Ico::bimaxe   , Msx::titulo_bimaxe    , 15, "&nbsp;&nbsp;Imagen"));
    $this->pon_obxeto(self::boton("bGaleria" , Ico::bgaleria , Msx::titulo_bgaleria  , 15, "&nbsp;&nbsp;Galería"));
    $this->pon_obxeto(self::boton("bCarrusel", Ico::bcarrusel, "Pinchar y arrastrar para añadir un carrusel de p&aacute;ginas", 15, "&nbsp;&nbsp;Carrusel"));
    $this->pon_obxeto(self::boton("bLink"    , Ico::blink    , Msx::titulo_blink     , 15, "&nbsp;&nbsp;V&iacute;nculo"));
    $this->pon_obxeto(self::boton("bBusca"   , Ico::blink    , Msx::titulo_blink     , 15, "&nbsp;&nbsp;Buscador"));
    $this->pon_obxeto(self::boton("bHR"      , Ico::bhr      , Msx::titulo_bhr       , 15, "&nbsp;&nbsp;Separador"));
    $this->pon_obxeto(self::boton("bcontacto", Ico::bcontacto, Msx::titulo_bcontacto , 15, "&nbsp;&nbsp;Contacto"));
    $this->pon_obxeto(self::boton("bvcard"   , Ico::bcontacto, Msx::titulo_bvcard    , 15, "&nbsp;&nbsp;VCard"));
    $this->pon_obxeto(self::boton("bpax"     , Ico::bmove    , Msx::titulo_bpaxinador, 15, "&nbsp;&nbsp;Migas"));
    $this->pon_obxeto(self::boton("bindice"  , Ico::bindice  , Msx::titulo_bindice   , 15, "&nbsp;&nbsp;&Iacute;ndice"));
  }

  public function factory(Epaxina_edit $e) {
    $evento = $e->btnr_evento();

    if ($evento->control_ohttp($this->obxeto("bDifucom2"))) return new Difucom2_obd(null, true);
    if ($evento->control_ohttp($this->obxeto("bTexto"   ))) return new Texto_obd(null, true);
    if ($evento->control_ohttp($this->obxeto("bImaxe"   ))) return new Imaxe_obd();
    if ($evento->control_ohttp($this->obxeto("bGaleria" ))) return new Galeria_obd();
    if ($evento->control_ohttp($this->obxeto("bLink"    ))) return new Iframe_obd();
    if ($evento->control_ohttp($this->obxeto("bHR"      ))) return new HR_obd();
    if ($evento->control_ohttp($this->obxeto("bCarrusel"))) return new Elmt_carrusel_obd();
    if ($evento->control_ohttp($this->obxeto("bpax"     ))) return new Paxinador_obd();
    if ($evento->control_ohttp($this->obxeto("bvcard"   ))) return new Elmt_vcard_obd();

    if ($evento->control_ohttp($this->obxeto("bcontacto"))) {
      $o = new Elmt_contacto_obd();

      $o->inicia_asunto($e->usuario());
      $o->inicia_campos();

      return $o;
    }

    if ($evento->control_ohttp($this->obxeto("bindice")))  {
      $o = new Indice_obd();

      $o->actualizar($e->__site_obd(), $e->id_idioma());

      return $o;
    }


    if (($bcarr_arti = $this->obxeto("bCarrusel_arti")) == null) return null;

    if ($evento->control_ohttp($bcarr_arti)) return new Elmt_carrusel_arti_obd();

    return null;
  }

  public function html():string {
    if (!$this->visible) return "";


    $this->obxeto("capa")->post($this->html00());

    $html = $this->obxeto("capa")->html();

    $this->obxeto("capa")->post(null);


    return $html;
  }

  public static function boton($nome, $src, $titulo, $mini = true, $txt = " ") {
    $b = self::__belmt($nome, $src, $titulo, $mini, $txt);
    
    $style_0 = $b->style("default");

    $b->style("default", "{$style_0} font-size: 11px; width: 111px; text-align: left; padding-left: 14px; background-position: left center;");

    $b->pon_eventos("onmousedown", "detalle_btnr_bElmt_mousedown(this, 'celda_elmt_marcado')");


    return $b;
  }

  public static function badd() {
    $b = Btnr_detalle_este::__bAdd(15, "&nbsp;Párrafo");
    
    $style_0 = $b->style("default");

    $b->style("default", "{$style_0} font-size: 11px; width: 111px; text-align: left; padding-left: 14px; background-position: left center;background-size: 15px 15px;");


    return $b;
  }
}

//***************************************

final class Editor_paxina extends Editor_btnr_oeste {
  public function __construct() {
    parent::__construct(null, "Propiedades de la p&aacute;gina", "ptw/btnr/editor_paxina.html");

    $this->cc = false;
  }

  public function pon_paxinfo(Epaxina_edit $e) {
    $p  = Paxina_obd::inicia(new FS_cbd(), $e->id_paxina());

    $pi = Paxinfo::inicia($p, true);

    $pi->ptw_0 = null;

    //~ $pi->readonly(true);

    $this->pon_obxeto($pi);
  }

  public function operacion_aceptar(Efs_admin $e) {
    return $e;
  }
/*
  public function declara_bcancelar() {
    $b = parent::declara_bcancelar();

    $b->visible = false;

    return $b;
  }
*/
  public function declara_baceptar() {
    $b = parent::declara_baceptar();

    $b->visible = false;

    return $b;
  }
}

//***************************************

final class Editor_panoramix extends Editor_btnr_oeste {
  const ptw_0 = "ptw/btnr/editor_panoramix_0.html";
  const ptw_1 = "ptw/btnr/editor_panoramix_1.html";

  public $s;
  public $p;
  
  public $bd;
  
  public $path;
  public $path_tmp;

  private $i_editando = -2;
  
  //~ private $url_upload = null;
  
  public function __construct($id_site, $id_paxina) {
    parent::__construct(null, "Panorámix", self::ptw_0);

$this->pon_obxeto(new Param("paux"));

    $this->s        = $id_site;
    $this->p        = $id_paxina;

    $this->inicia_tmp($id_site);




    $this->bd = new BD_panoramix( $this->path_tmp() );

    $this->pon_obxeto(self::__imx());
 
    $this->pon_obxeto(self::__lcambiar());
    $this->pon_obxeto(new Div("msx_cambiar"));


    $this->pon_obxeto(self::__f_imaxe( $this->path_tmp() . "/" ));

       
    $this->pon_obxeto(self::__smov());
    
    $this->pon_obxeto(new Checkbox("chmov", true, "Movimiento en bucle"));

       
    $this->pon_obxeto(self::__txt());
    
    $this->pon_obxeto(new Checkbox("chH1" , true, "Asignar etiqueta <b>&lt;H1&gt;</b> al texto"));
    
    $this->pon_obxeto(new SColor("#ffffff"));
    
    $this->pon_obxeto(new Panoramix_liten());
    
    $this->pon_obxeto(self::__bmais());


    $this->pon_obxeto(Panel_fs::__text("link_href", 10, 255));
    $this->pon_obxeto(new Select("link_target", array("_self"=>"Misma ventana", "_blank"=>"Nueva ventana")));

    //~ $this->obxeto("link_href"  )->style("default", "width: 66%;");
    //~ $this->obxeto("link_target")->style("default", "width: 22%; cursor:pointer;");

    //* inicia datos globales "g".
    list($v, $b) = $this->bd->_mov();
    
    $this->obxeto("smov" )->post( $v                     );
    $this->obxeto("chmov")->post( ($b == "1")?true:false );

//~ $this->_informe_0();
  }
  
  public function path($basename = null) {
    $p = "{$this->path}/{$this->p}";
    
    if ($basename == null) return $p;
    
    return "{$p}/{$basename}";
  }
  
  public function path_tmp($basename = null) {
    $p = "{$this->path_tmp}/{$this->p}";
    
    if ($basename == null) return $p;
    
    return "{$p}/{$basename}";
  }
  
  public function operacion(EstadoHTTP $e) {
    $this->obxeto("paux")->post(null);
    
    if ($this->obxeto("bmais"  )->control_evento()) return $this->operacion_editar($e, -1);
    if ($this->obxeto("f_imaxe")->control_evento()) return $this->operacion_fimaxe($e);
    

    if (($e_aux = $this->obxeto("scolor"    )->operacion($e)) != null) return $e_aux;
    if (($e_aux = $this->obxeto("lpanoramix")->operacion($e)) != null) return $e_aux;

    return parent::operacion($e);
  }

  public function operacion_editar(Efs_admin $e, $i) {
    $this->ptw        = self::ptw_1;

    $this->i_editando = $i;

    if ($i == -1) 
      $pi = BD_panoramix::__rexistrar(null, false, "Lorem ipsum", "#fff", null, null);

    else {
      $pi = $this->bd->pi($i);
    }


//~ error_log( print_r($pi, 1) );
    $url = ($pi->url == null)?Imaxe_obd::noimx:$this->path_tmp($pi->url);

    $this->obxeto("imaxe"      )->pon_src ( $url               );
    $this->obxeto("chH1"       )->post    ( $pi->_txt->H1      );
    $this->obxeto("ttxt"       )->post    ( $pi->_txt->txt     );
    $this->obxeto("scolor"     )->post_hex( $pi->_txt->color   );
    $this->obxeto("link_href"  )->post    ( $pi->a->href       );
    $this->obxeto("link_target")->post    ( $pi->a->target     );


    $this->preparar_saida($e->ajax());


    return $e;
  }

  public function operacion_fimaxe(Efs_admin $e) {
    $f = $this->obxeto("f_imaxe")->post_f();
    
    $this->obxeto("imaxe")->post( $f );


    $this->preparar_saida( $e->ajax() );
    

    return $e;
  }

  public function operacion_cancelar(Epaxina_edit $e) {
    if ($this->i_editando > -2) {
      $this->ptw = self::ptw_0;

      $this->i_editando = -2;

      $this->preparar_saida($e->ajax());

      return $e; //* Indica a Btnr_detalle_oeste q debe mantener abierto el editor
    }
    
    if (is_dir($this->path_tmp())) passthru("rm -r " . $this->path_tmp());


    return parent::operacion_cancelar($e);
  }

  public function operacion_aceptar(Efs_admin $e) {
    if ($this->i_editando > -2) return $this->operacion_aceptar_1($e);
    
    
    return $this->operacion_aceptar_2($e);
  }

  public function operacion_chActivo(Efs_admin $e) {
    $i = $e->evento()->subnome(0);

    $this->bd->activar($i);


    $this->preparar_saida($e->ajax());

    return $e;
  }

  public function operacion_bsup(Efs_admin $e) {
    $i = $e->evento()->subnome(0);

    $u = $this->path_tmp( $this->bd->url($i) );

    if ( is_file($u) ) unlink($u);
    
    $this->bd->sup($i);


    $this->preparar_saida($e->ajax());

    return $e;
  }

  private function operacion_aceptar_1(Efs_admin $e) {
    $i        = $this->i_editando;
    
    $activo   = -1;
    $url      = $this->obxeto("imaxe"      )->src();

    $h1       = $this->obxeto("chH1"       )->valor();
    $txt      = $this->obxeto("ttxt"       )->valor();
    $color    = $this->obxeto("scolor"     )->valor();
    $href     = $this->obxeto("link_href"  )->valor();
    $target   = $this->obxeto("link_target")->valor();

    $this->bd->pon($i, $activo, $url, $h1, $txt, $color, $href, $target);
    

//~ $this->_informe_0();

    
    $this->operacion_cancelar($e);

    return null; //* Indica a Btnr_detalle_oeste q debe mantener abierto el editor
  }

  private function operacion_aceptar_2(Epaxina_edit $e) {
    //* completa informacion ptw_0.
    $_mov = [ $this->obxeto("smov" )->valor(),
              $this->obxeto("chmov")->valor()
            ];
            
    $this->bd->_mov( $_mov );

    //* pechamos a bd e limpamos tmp's.
    $this->bd->pecha();

    $d0 = $this->path    ();
    $d1 = $this->path_tmp();

//~ echo "<br><br><br><br><br><br>cp {$d1}/* {$d0}";

    if ( is_dir($d0) ) passthru("rm -r {$d0}");

    mkdir($d0);

    passthru("cp {$d1}/* {$d0}");
    

    //* prepara saida ajax.
    $callback = "panoramix2_inicia(" . $e->id_paxina() . ")";

    $e->ajax()->pon_ok(true, $callback);
    
    return $e;
  }


  private function inicia_tmp($id_site) {
    $url_site       = Efs::url_site($id_site);
    
    //~ $this->path     = $url_site . Refs::url_panoramix;
    //~ $this->path_tmp = $url_site . Refs::url_arquivos . "/" . Refs::url_tmp;

    $this->path     = Refs::url_arquivo ( Refs::url_panoramix );
    $this->path_tmp = Refs::url_arquivos( Refs::url_tmp       );

    //* control de carpeta panoramix para o site.
    if (!is_dir($this->path)) mkdir($this->path);

    //* criamos unha carpeta tmp baleira ($d0).
    if (is_dir($this->path_tmp))
      XestorDescargas::borra_antigos($this->path_tmp, 2880); //* 2 dias.
    else
      mkdir($this->path_tmp);

    $d0 = $this->path_tmp();
    if (is_dir($d0)) passthru("rm -r {$d0}"); 
    
    mkdir($d0);

    //* copiamos a carpeta tmp, a informacion do panoramix (sii existe).
    $d1 = $this->path();

    if ( !is_dir($d1) ) return;
    
    passthru("cp {$d1}/* {$d0}");

  }

  
  private static function __smov() {
    $_o = [ "-1"    => "No",
            "5555"  => "Rápido",      
            "9999"  => "Normal",      
            "15551" => "Lento",      
          ];
          
    
    $s = new Select("smov", $_o);

    $s->post("-1");


    return $s;
  }
  
  private static function __bmais() {
    $b = Panel_fs::__bmais("bmais", "Añadir foto", "Añadir una foto panorámica");

    $b->envia_AJAX("onclick");


    return $b;
  }

  private static function __bpan() {
    $i = new Image("bpan", "");

    $i->style("default", "width: 100%;");

    $i->pon_eventos("onclick", "tilia_file_click('findex;detalle_btnr_oeste;editor;ifile')");


    return $i;
  }

  private static function __f_imaxe($src_tmp) {
    $f = new File("f_imaxe", $src_tmp, 1024 * 1024 * 3);

    $f->clase_css("default", "file_apagado");


    $f->accept      = "image/gif, image/jpg, image/jpeg, image/png, image/webp";


    return $f;
  }

  private static function __txt() {
    $t = new Textarea("ttxt");
    
//    $t->maxlength = 222;
    
    //~ $t->style("default", "width: 66%; min-width: 66%; max-width: 88%; height: 44px; min-height: 44px;");

    return $t;
  }

  private static function __lcambiar() {
    $l = Panel_fs::__link("imx_cambiar", "Cambiar la imagen", "trw-btn", "trw-btn over");

    $l->title = "Pincha aqu&iacute; para &laquo;cambiar&raquo; la imagen";

    $l->pon_eventos("onclick", "elmt_imx_cambiar(this)");

    return $l;
  }

  private static function __imx() {
    $imx = new Image("imaxe", null, false);

    $imx->style("default", "max-height: 77px;");


    return $imx;
  }

  private function _informe_0() {
    $s = "
path_0: {$this->path}<br>
temp_0: {$this->path_tmp}<br>
path_1: " . $this->path() . "<br>
temp_1: " . $this->path_tmp() . "<br>
" . $this->bd->html();

    $this->obxeto("paux")->post($s);
  }
}

//---------------------------------

final class Panoramix_liten extends FS_lista
                         implements Iterador_bd {
  
  private $i = -2;

  public function __construct() {
    parent::__construct("lpanoramix", new Panoramix_liten_ehtml());
  }

  protected function html_baleiro() {
    if (!$this->visible) return "";

    return "<div class='lista_baleira txt_adv'>No hay fotos panorámicas asociadas a la página.</div>";
  }

  public function numFilas() {
    return $this->pai->bd->total();
  }

  public function __count() {
    return $this->numFilas();
  }
  
  public function descFila() {}

  public function next() {
    if ($this->i == -2) 
      $this->i = $this->numFilas() - 1;
    else
      $this->i--;
    
    
    if ($this->i == -1) {
      $this->i = -2;

      return false;
    }

    $pi = $this->pai->bd->pi($this->i);


    if (($i = $this->i) == 0) $i = "0";

    return array($i, $pi);
  }

  public function operacion(EstadoHTTP $e) {
    $evento = $e->evento();

//~ echo $evento->html();

    if ($this->control_fslevento($evento, "beditar" )) return $this->pai->operacion_editar($e, $evento->subnome(0));
    if ($this->control_fslevento($evento, "chActivo")) return $this->pai->operacion_chActivo($e);
    if ($this->control_fslevento($evento, "bsup"    )) return $this->pai->operacion_bsup($e);

    
    return parent::operacion($e);
  }

  protected function __iterador() {
    return $this;
  }
}

//---------------------------------

final class Panoramix_liten_ehtml extends FS_ehtml {

  public function __construct() {
    parent::__construct();
    
    $this->width = "97%";

    $this->style_table = "border: 1px solid #333; padding-left: 3px;";
  }


  protected function inicia_saida() {
    return "<div>";
  }

  protected function finaliza_saida() {
    return "</div>";
  }


  protected function cabeceira($df = null) {
    return "";
  }

  protected function linha_detalle($df, $f) {
//~ return "<pre>" . print_r($f, true) . "</pre>";

    return "
      <div class='epanoramix_tr'>
        <div>" . self::__chActivo($this, $f) . "</div>
        " . self::__imx($this, $f)      . "
        <div>" . self::__txt($this, $f) . "</div>
        <div>" . self::__bSup($this, $f)  . "</div>
      </div>";
  }


  private static function __chActivo(Panoramix_liten_ehtml $ehtml, $f) {
    
    $b = new Checkbox("chActivo", $f[1]->activo == 1);
    
    $b->readonly = $ehtml->xestor->readonly;

    $b->envia_ajax("onclick");
    //~ $b->envia_submit("onclick");

    return $ehtml->__fslControl($b, $f[0])->html();
  }

  private static function __imx(Panoramix_liten_ehtml $ehtml, $f) {
    $imx = $ehtml->xestor->pai->path_tmp( $f[1]->url );

    $s = "<div class='epanoramix_tr_2' style='background-image: url(\"{$imx}\");'></div>";

    return $s;
  }

  private static function __txt(Panoramix_liten_ehtml $ehtml, $f) {
    $color = ""; $a = ""; $h1 = ""; 
    if (($t = $f[1]->_txt->txt) == null) {
      $t = "Sin título";
    }
    else {
      $t = Valida::split($t, 22);

      $color = "<div class='epanoramix_tr_3_2_color' style='background-color:{$f[1]->_txt->color};' title='{$f[1]->_txt->color}'></div>";


      if ($f[1]->_txt->H1)
        $h1 = "<div class='epanoramix_tr_3_2_marca' style='background-color: #fcfc00;'>&lt;H1&gt;</div>";
    }

    if ($f[1]->a->href)
      $a = "<div class='epanoramix_tr_3_2_marca' style='background-color: #cae0fb;'><a title='Ir a: {$f[1]->a->href}' href='{$f[1]->a->href}' target='_blank' style='text-decoration: none;'>&lt;a&gt;</a></div>";


    $url = $ehtml->xestor->pai->path_tmp( $f[1]->url );
    if ( file_exists( $url ) ) {
      $peso = File::html_bytes( filesize($url) );
      $tipo = mime_content_type($url);
    }
    else {
      $peso = "--";
      $tipo = "--";
    }


    $l = new Link("beditar", $t);

    $l->envia_ajax("onclick");
    //~ $l->envia_submit("onclick");

    $ehtml->__fslControl($l, $f[0]);
    
    return "<div class='epanoramix_tr_3'>
              <div>
                <div class='lista_elemento_a'>" . $l->html() . "</div>
                <div style='margin-top: 0.3em;color: #555;'>{$peso}&nbsp;&nbsp;&nbsp;{$tipo}</div>
              </div>
              <div>{$color}{$a}{$h1}</div>
            </div>";
  }

  private static function __bSup(Panoramix_liten_ehtml $ehtml, $f) {
    if ($ehtml->xestor->readonly) return "";
    
    $b = Panel_fs::__bsup("bsup", null);

    $b->envia_ajax("onclick", "¿ Quieres eliminar la foto ?");
    //~ $b->envia_submit("onclick", "¿ Quieres eliminar la foto ?");

    return $ehtml->__fslControl($b, $f[0])->html();
  }
}

//---------------------------------

final class BD_panoramix {
  
  const url_json = "bd.json";
  const bd_0     = ["g" => ["_mov" => [-1, null]], "l" => []];

  private $_bd   = null;
  private $src   = null;


  public function __construct($src = null) {
    if ($src == null) return;
    
    $this->src = "{$src}/";

    //~ echo "<br><br><br><br><br>" . $this->src . self::url_json . "<br>";

    if (!is_file($this->src . self::url_json)) self::__reset_bd($this->src);
    //~ self::__reset_bd($this->src);

    $this->abre();
  }
  
  public static function __reset_bd($src) {
    $_bd = self::bd_0;

    foreach (glob("{$src}*") as $url) {
      if (($b = basename($url)) == self::url_json) continue;
      
      $_bd["l"][] = self::__rexistrar($b, false, "", "#ffffff", null, null);
    }
    
    file_put_contents( $src . self::url_json, json_encode($_bd) );
  }

  public function total() {
    if ( $this->_bd == null       ) return 0;
    
    if ( !is_array($this->_bd->l) ) return 0;
    
    return count($this->_bd->l);
  }

  public function pon($i, $activo, $url, $h1, $txt, $color, $a_href, $a_target) {
    //* PRECOND: $i:int, $i > -2

    $_p = self::__rexistrar($url, $h1, $txt, $color, $a_href, $a_target);


    if ($i == -1) {
      $this->_bd->l[] = $_p;

      return;
    }

    if ($activo == -1) $_p->activo = $this->_bd->l[$i]->activo;


    $this->_bd->l[$i] = $_p;
  }

  public function sup($i) {
    unset($this->_bd->l[$i]);

    $this->_bd->l = array_values($this->_bd->l); //* reindexamos
  }
  
  public function pi($i, $pi = -1) {
    if ($pi != -1) $this->_bd->l[$i] = $pi;

    return $this->_bd->l[$i];
  }
  
  public function activar($i) {
    $this->_bd->l[$i]->activo = ($this->_bd->l[$i]->activo == 1)?0:1;

    return $this->_bd->l[$i]->activo;
  }
  
  public function _mov($_mov = -1) {
    if ($this->_bd == null) $this->_bd = self::_bd_0;
    
    if ($_mov != -1) $this->_bd->g->_mov = $_mov;
    
    return $this->_bd->g->_mov;
  }
  
  public function url($i, $url = -1) {
    if ($url != -1) $this->_bd->l[$i]->url   = basename($url);

    return $this->_bd->l[$i]->url;
  }


  public function html( $pre = true ) {
    $s = print_r($this, true);

    if (!$pre) return $s;
    
    return "<pre>{$s}</pre>";
  }

  public static function __rexistrar($url, $h1, $txt, $color, $a_href, $a_target) {
    $_r = array("activo"   => 1,
                "url"      => basename(strval($url)),
                "_txt"     => array("H1"       => $h1,
                                    "txt"      => $txt,
                                    "color"    => $color
                                  ),
                "a"        => array("href" => $a_href ,"target" => $a_target)
               );

    return json_decode(json_encode($_r));
  }
  
  private function abre() {
    $this->_bd = self::bd_0;


    $ubd = $this->src . self::url_json;

    if (!is_file($ubd)) return;

    $this->_bd = json_decode( file_get_contents($ubd) );
  }

  public function pecha(  ) {
    file_put_contents( $this->src . self::url_json, json_encode($this->_bd) );
  }
}
