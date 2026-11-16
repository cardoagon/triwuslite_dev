<?php

final class CMetatags extends    Componente
                      implements ICPC, IXestorCEditor_imx {
                         
  private $url     = null;
  private $id_site = null;
  private $ico_obd = null;
  private $src_tmp = null;
  //~ private $self::url_site($id_site) . Refs::url_arquivos . "/"; = null;

  public function __construct($id_site) {
    parent::__construct("cmetatags", "ptw/paneis/pcontrol/xeral/cmetatags.html");

    $cbd = new FS_cbd();

    $m = Site_obd::inicia($cbd, $id_site)->metatags_obd($cbd);

    $this->id_site = $id_site;
    $this->ico_obd = $m->__ico($cbd);

    $this->pon_obxeto(self::__text    ("author"     , $m->atr("author"     )->valor));
    $this->pon_obxeto(self::__text    ("robots"     , $m->atr("robots"     )->valor));
    $this->pon_obxeto(self::__text    ("copyright"  , $m->atr("copyright"  )->valor));
    $this->pon_obxeto(self::__text    ("titulo"     , $m->atr("titulo_txt" )->valor));
    $this->pon_obxeto(self::__textarea("description", $m->atr("description")->valor));
    $this->pon_obxeto(self::__textarea("ganalitics" , $m->atr("ganalitics" )->valor));
    //~ $this->pon_obxeto(self::__text("keywords", $m->atr("keywords")->valor, "por ejemplo: verano, vacaciones, playa"));

    $this->pon_obxeto(self::__bico($this->ico_obd->url()));
    $this->pon_obxeto(self::__fico($this->id_site));
    $this->pon_obxeto(self::__mico());


    $this->pon_obxeto(new CEditor_imx());
    $this->pon_obxeto(self::metatags_logo());


    $this->pon_obxeto(new CSiteMap($id_site));

    
    $this->pon_obxeto(Panel_fs::__baceptar());


    $this->obxeto("ganalitics")->style("default", "width: 99%;height: 17.3em;");

    $this->obxeto("copyright")->envia_AJAX("onblur");
    $this->obxeto("robots"   )->envia_AJAX("onblur");


    $url = null; if (($l = $m->iclogo_obd($cbd)) != null) $url = $l->url();

    $this->post_url($url);
  }

  public function cpc_miga() {
    return "configuración básica";
  }


  public function url(?string $k = null) { //* IMPLEMENTA IXestorCEditor_imx
    return $this->url;
  }

  public function post_url($url = null, ?string $k = null) { //* IMPLEMENTA IXestorCEditor_imx
    $this->url = $this->obxeto("ceditor_imx")->pon_src($url);

    $this->obxeto("logo")->style("default" , "background-image: url({$this->url});cursor: pointer;");
    $this->obxeto("logo")->style("readonly", "background-image: url({$this->url});");
  }

  
  public function validar() {
    $erro = null;

    if (trim($this->obxeto("titulo")->valor()) == "") $erro .= "Debe teclear o nome do site.";

    return $erro;
  }

  public function update(FS_cbd $cbd) {
    $m = Site_metatags_obd::inicia($cbd, $this->id_site);

    $m->atr("titulo_ico")->valor  = $this->ico_obd->atr("id_elmt")->valor;
    $m->atr("titulo_txt")->valor  = $this->obxeto("titulo")->valor();
    $m->atr("author")->valor      = $this->obxeto("author")->valor();
    $m->atr("copyright")->valor   = $this->obxeto("copyright")->valor();
    $m->atr("description")->valor = $this->obxeto("description")->valor();
    //~ $m->atr("keywords")->valor    = $this->obxeto("keywords")->valor();
    $m->atr("robots")->valor      = Erro::__comas($this->obxeto("robots")->valor());
    $m->atr("ganalitics")->valor  = trim($this->obxeto("ganalitics")->valor());
    

    if ( !$m->update($cbd) ) return false;
    
    //~ return true;

    if ( !$this->update_ico($cbd) ) return false;

    return $this->update_logo($cbd, $m);
  }

  public function operacion(EstadoHTTP $e) {
    $this->obxeto("mico")->visible = false;

    if (($e_aux = $this->operacion_fico($e)) != null) return $e_aux;

    if (($e_aux = $this->obxeto("csitemap"      )->operacion($e)) != null) return $e_aux;
    //~ if (($e_aux = $this->obxeto("manifesto-wpt")->operacion($e)) != null) return $e_aux;

    if ($this->obxeto("copyright")->control_evento()) return $this->operacion_copy($e);

    if ($this->obxeto("robots")->control_evento()) return $this->operacion_comas($e, $this->obxeto("robots"));


    if ($this->obxeto("logo")->control_evento()) return $this->obxeto("ceditor_imx")->operacion_logo($e);
    if (($e_aux = $this->obxeto("ceditor_imx")->operacion($e)) != null) return $e_aux;



    if ($this->obxeto("bAceptar")->control_evento()) return $this->operacion_aceptar($e);



    return null;
  }

  public static function __bico(?string $url) {
    if ($url == null) $url = Ico::bico;

    $b = Panel_fs::__bimage("bico", $url, "Cambiar el icono del t&iacute;tulo");

    $b->clase_css("default", "pcontrol_bico");
 
    $b->pon_eventos("onclick", "document.getElementById('findex;cmetatags;fico').click()");

    return $b;
  }

  public static function __fico($id_site) {
    //~ $url_tmp = Efs::url_tmp( Efs::url_site($id_site) . Refs::url_arquivos . "/" );
    $url_tmp = Refs::url_arquivos(Refs::url_tmp . "/");
    
    $f = new File("fico", $url_tmp, 1024 * 1024 * 1.5); //* 1.5Mb+-

    $f->clase_css("default", "file_apagado");

    $f->accept = "image/gif, image/jpg, image/jpeg, image/png, image/webp";

    return $f;
  }


  public static function __mico() {
    $d = new Div("mico");

    $d->clase_css("default", "texto3_erro");

    $d->visible = false;

    return $d;
  }

  public static function __text($nome, $valor, $placeholder = "") {
    $t = Panel_fs::__text($nome, null, null, $placeholder);

    //~ $t->style("default", "width: 99%;");
    //~ $t->style("readonly", "width: 99%;");

    $t->post($valor);

    return $t;
  }

  public static function __textarea($nome, $valor, $placeholder = "") {
    $t = Panel_fs::__textarea($nome, null, false, $placeholder);

    $t->filas = 2;

    $t->style("default", "width: 99%;");
    $t->style("readonly", "width: 99%;");

    $t->post($valor);

    return $t;
  }

  private function operacion_comas(Epcontrol $e, Control $c) {
    $t = Erro::__comas($c->valor());

    $c->post($t);

    $e->ajax()->pon($c);

    return $e;
  }

  private function operacion_copy(Epcontrol $e) {
    $txt = str_replace(array("©", "&copy;"), array("", ""), $this->obxeto("copyright")->valor());

    $this->obxeto("copyright")->post($txt);

    $e->ajax()->pon($this->obxeto("copyright"));

    return $e;
  }

  private function operacion_fico(Efs_admin $e) {
    if (!$this->obxeto("fico")->control_evento()) return null;

    $url_tmp = $this->obxeto("fico")->post_f();
    
    
    $this->pon_obxeto( self::__bico($url_tmp) );
    
    $this->preparar_saida( $e->ajax() );


    return $e;
  }

  private function operacion_aceptar(Efs_admin $e) {
    //* Valida nome
    if (($nome = trim($this->obxeto("titulo")->valor())) == "") {
      $e->post_msx("ERROR, debes teclear el título.");
      
      return $e; 
    }
    
    
    //* Actualiza na BD.
    $cbd = new FS_cbd();

    $cbd->transaccion();

    if ( $this->update($cbd) ) {
      $cbd->commit();      

      $e->post_msx("Ficha actualizada.", "m");
    }
    else {
      $cbd->rollback();

      $e->post_msx("Sucedió un error al actualizar la BD.");
    }
    
    return $e;  
  }

  private function update_ico(FS_cbd $cbd) {
    $this->ico_obd->post_url( $this->obxeto("fico")->f() );

//~ print_r($this->obxeto("fico")->f());
//~ print_r($this->ico_obd->a_resumo());
    

    return $this->ico_obd->update($cbd);
  }

  private function update_logo(FS_cbd $cbd, Site_metatags_obd $m) {
    if (($url = $this->obxeto("ceditor_imx")->aceptar_imx()) == null) return true;


    return $m->iclogo_update($cbd, $url);
  }

  private static function metatags_logo() {
    $l = new Div("logo");

    $l->envia_AJAX("onclick");

    $l->clase_css("default", "paxinfo_logo");


    return $l;
  }

}

//***************************** 

final class CSiteMap extends Componente {
  private $s = null;
  private $n = null;
  private $d = null;

  private $url_0;
  private $url_1;

  public function __construct($id_site) {
    parent::__construct("csitemap", "ptw/paneis/pcontrol/xeral/csitemap.html");

    $s = Site_obd::inicia(new FS_cbd(), $id_site);

    $this->s     = $id_site;
    $this->n     = "sitemap.xml";
    $this->d     = TrwlConfig::dominio;
    
    //~ $this->url_0 = Refs::url(Refs::url_sites . "/" . $s->atr("nome")->valor . "/");
    $this->url_0 = "";
    $this->url_1 = $this->url_0 . $this->n;


    $this->pon_obxeto( self::bsubir  ($this->n) );
    $this->pon_obxeto( self::bxerar () );

    $this->inicia();                       
  }

  public function operacion(EstadoHTTP $e) {
    if ($this->obxeto("benviar" )->control_evento()) return $this->operacion_benviar ($e);
    if ($this->obxeto("bsuprime")->control_evento()) return $this->operacion_bsuprime($e);
    if ($this->obxeto("bxerar"  )->control_evento()) return $this->operacion_bxerar  ($e);
    if ($this->obxeto("fsubir"  )->control_evento()) return $this->operacion_fsubir  ($e);


    return null;
  }

  private function inicia() {
    $this->pon_obxeto( $this->fsubir () );
    $this->pon_obxeto( $this->benlace() );                       
    $this->pon_obxeto( self::benviar($this->url_1) );
    $this->pon_obxeto( self::bsuprime($this->n, $this->url_1) );
  }

  private function operacion_benviar(Efs_admin $e) {
//~ echo $e->evento()->html();

    $_r = XSiteMap::enviar( $this->url_sitemap() );

//~ echo "<pre>" . print_r($_r, 1) . "</pre>";


    $this->preparar_saida($e->ajax());

    $m = "
<div style='
  margin-bottom: 0.7em;'>El archivo sitemap.xml ha sido enviado.</div>
<div style='
  min-width: 44em;
	max-height: 17em;
	overflow: scroll;
	border: 1px solid #aaa;
  border-radius: 3px;
	padding: 0.7em;
	background-color: #fff;
	color: #333;'>{$_r[0]}</div>";

    $e->post_msx($m, "m", true);

    return $e;
  }

  private function operacion_bsuprime(Efs_admin $e) {
    unlink("{$this->url_0}sitemap.xml");
    unlink("{$this->url_0}robots.txt");
    

    $this->inicia();


    $this->preparar_saida($e->ajax());

    $e->post_msx("Arquivo sitemap.xml eliminado correctamente", "m", true);


    return $e;
  }

  private function operacion_bxerar(Efs_admin $e) {
//~ echo $e->evento()->html();

    $_r = XSiteMap::xerar($this->s, $this->url_tmp());

//~ echo "<pre>" . print_r($_r, 1) . "</pre>";

    copy("{$_r[0]}sitemap.xml", "{$this->url_0}sitemap.xml");
    copy("{$_r[0]}robots.txt" , "{$this->url_0}robots.txt" );

    $this->inicia();


    $this->preparar_saida($e->ajax());

    $e->post_msx("Arquivo sitemap.xml generado correctamente con {$_r[1]} url`s.", "m", true);

    return $e;
  }

  private function operacion_fsubir(Efs_admin $e) {
    $url_tmp = $this->obxeto("fsubir")->post_f();

    $this->obxeto("fsubir")->aceptar_f($this->url_0, "sitemap");
    

    $this->inicia();


    $this->preparar_saida($e->ajax());

    $e->post_msx("Arquivo sitemap.xml subido correctamente", "m", true);


    return $e;
  }
  
  private function benlace() {
    if ( is_file($this->url_1) ) {
      $l = new Link("benlace", "Ver archivo: &laquo;{$this->n}&raquo;", "_blank", $this->url_sitemap());
      $l->style("default", "color: #00f; text-decoration: underline;");
      
      return $l;
    }
    
    $l = new Link("benlace", "No hay un &laquo;{$this->n}&raquo; asociado");
    $l->style("default", "color: #00f; cursor: initial;");
    
    $l->readonly = false;
    
    
    return $l;
  }

  private function fsubir() {
    $tmp = $this->url_tmp();
    $mfs = 1024 * 1024 * 5;

    $f = new File("fsubir", $tmp, $mfs);

    $f->clase_css("default" , "file_apagado");
    $f->clase_css("readonly", "file_apagado");

    $f->accept = ".xml";

    //~ $f->envia_submit("onchange");


    return $f;
  }

  private function url_tmp() {
    return $this->url_0 . Refs::url_arquivos . "/" . Refs::url_tmp . "/";
  }

  private function url_sitemap() {
    return "https://{$this->d}/{$this->n}";
  }
  
  private static function bsubir($n) {
    $b = Panel_fs::__bimportar("bsubir", "", "Añadir archivo {$n}");

    $b->pon_eventos("onclick", "document.getElementById('findex;cmetatags;csitemap;fsubir').click()");

    return $b;
  }

  private static function bsuprime($n, $f) {
    $b = Panel_fs::__bsup("bsuprime", null, "Borrar {$n}");
    
    $b->visible = is_file($f);

    $b->envia_SUBMIT("onclick");
    //~ $b->envia_AJAX  ("onclick");

    return $b;
  }

  private static function benviar($f) {
    $b = Panel_fs::__benviar("benviar", null, "Solicita la actualización del sitemap a Bing y Google.");
    
    $b->visible = is_file($f);

    $b->envia_SUBMIT("onclick");
    //~ $b->envia_AJAX  ("onclick");
    
    return $b;
  }

  private static function bxerar() {
    $b = Panel_fs::__beditar("bxerar", null, "Genera el sitemap.xml de tu sitio web");

    $b->envia_SUBMIT("onclick");
    //~ $b->envia_AJAX  ("onclick");
    
    return $b;
  }
}

