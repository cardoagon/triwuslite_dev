<?php

abstract class Efs extends EstadoHTTP {
  const httptriwus = "http://*-triwus-*/";

  public $url_limpa;
  public $venta     = false;

  public $msx_persiste = false;

  private $usuario = null;

  public function __construct(Paxina $p = null, FGS_usuario $u = null) {
    parent::__construct($p);

    $p->head->links_ver(TrwlConfig::version);

    $p->head->pon_meta("viewport", "width=device-width, initial-scale=1");

    $p->head->pon_js( Tilia::home() . "js/six.js"              );
    $p->head->pon_js( Tilia::home() . "js/ventanaModal-2.js"   );
    $p->head->pon_js( Refs::url("js/catalogo.js")              );
    $p->head->pon_js( Refs::url("js/jquery-3.1.1.slim.min.js") );
    $p->head->pon_js( Refs::url("js/owlcarousel/owl.carousel.js") );
    $p->head->pon_js( Refs::url("js/carrusel-2.js"              ) );


    $p->head->pon_css(Tilia::home() . "css/ventanaModal-2.css");
    $p->head->pon_css(Refs::url("css/owlcarousel/owl.theme.default.min.css"));
    $p->head->pon_css(Refs::url("css/owlcarousel/owl.carousel.min.css"     ));


    $this->pon_obxeto( self::_dmsx() );


    $this->inicia_usuario_anterior($u);
  }


  abstract public function __site_obd($id_site = null, FS_cbd $cbd = null);


  public function usuario(FGS_usuario $u = null) {
    if ($u == null) return $this->usuario;

    if ($this->usuario != null) $u->copia_historia( $this->usuario );

    $this->usuario = $u;
  }


  public function post_msx($m = null, $t = "e", $ajax = null) {
    //* $t IN {"a", "e", "m"}

    $o = $this->obxeto("msx");

    if ($m == null) {
      if ($this->msx_persiste) {
        $this->msx_persiste = false; //* a seguinte vez borramos a mensaxe, seguindo a operativa habitual.
      }
      else {
        $o->post(null);

        $o->clase_css("default", "txt_apagado");
      }

      return;
    }


    $x = "<div onclick=\"trw_post_msx()\">✕</div>";

    $o->post( "{$x}<div>{$m}</div>" );

        if ($t == "a") $o->clase_css("default", "trw_msx txt_adv");
    elseif ($t == "e") $o->clase_css("default", "trw_msx txt_err");
    elseif ($t == "m") $o->clase_css("default", "trw_msx txt_msx");


    if ($ajax === null) $ajax = ($this->evento()->ajax() == 1);

    if ($ajax) $this->ajax()->pon( $o );
  }
/*
  final public function url_httptriwus($url, $nome_site = null) {
    if (!self::__httptriwus($url)) return null;


    $snome    = Refs::snome(-1);

    $url_0 = $snome;
    $url_1 = str_replace(self::httptriwus, "", $url);

    if ($snome == Refs::url_home(-1)) {
      if ($nome_site == null) $nome_site = $this->__site_obd()->atr("nome")->valor;

      $url_0 .= "/" . Refs::url_sites . "/" . $nome_site;
    }

    return "{$url_0}/{$url_1}";
  }
*/

  final public static function destino_t2lf( $codpostal, $codpais ) {
    if ($codpais != 203   ) return false;
    if ($codpostal == null) return false;
    
    $prov = substr($codpostal, 0, 2);

    return (($prov == "35") || ($prov == "38"));
  }

  final public static function url_base($id_site, FS_cbd $cbd = null) {
    return Efs::url_paxina($id_site, null, null, null, $cbd) . "/";
  }

  final public static function url_paxina($id_site, $action = null, $id_pax = null, $l = null, FS_cbd $cbd = null, $https_navegacion = true) {
    if ($cbd == null) $cbd = new FS_cbd();

    $s = Site_obd::inicia($cbd, $id_site);

    $https = ($https_navegacion)?-1:$s->atr("https")->valor;

    //~ if ($s->atr("dominio")->valor == null)
      //~ $url = Refs::snome($https) . "/" . Refs::url_sites . "/" . $s->atr("nome")->valor;
    //~ else
      $url = Refs::protocolo($https) . TrwlConfig::dominio;


    //* a seguinte liña serve so para o site trw-envolve (triwus.com)
    //~ if ($url == Refs::url_home($https)) $url .= "/" . Refs::url_sites . "/" . $s->atr("nome")->valor;



    if ($action == null) return $url;

    $url .= "/{$action}" . self::url_paxina_param($id_pax, $l);


    return $url;
  }

  final public static function email_noreply($id_site, FS_cbd $cbd = null) {
    $s = self::url_paxina($id_site, $cbd);

    list($p, $d) = explode("://", $s);


    return "no-reply@{$d}";
  }

  final public static function url_site($id_site, FS_cbd $cbd = null) {
    if ($cbd == null) $cbd = new FS_cbd();

    $s = Site_obd::inicia($cbd, $id_site);

    return Refs::url($s->atr("nome")->valor . "/");
  }


  final public static function url_logs($uid_site, FS_cbd $cbd = null):string {
    // /var/www/vhosts/zeta.triwus.com/logs/[dominio]/access*
    // /var/www/vhosts/zeta.triwus.com/logs/pilotoz.triwus.com

    if ($cbd == null) $cbd = new FS_cbd();

    $nomeDominio = TrwlConfig::dominio;

    $url = ($nomeDominio) ?
        "/{$nomeDominio}/access*" :
        "/access*";

    return TrwlConfig::url_logs . $url;
  }

  final public function url_arquivos($id_site = null) {
    if ($id_site == null) $id_site = $this->__site_obd()->atr("id_site")->valor;

    return self::url_site($id_site) . Refs::url_arquivos . "/";
  }

  //~ final public function url_galerias($nome, $id_site = null) {
    //~ if ($id_site == null) $id_site = $this->__site_obd()->atr("id_site")->valor;


    //~ return self::url_site($id_site) . Refs::url_galerias . "/{$nome}/";
  //~ }

  final public function url_arquivo($id_site = null) {
    if ($id_site == null) $id_site = $this->__site_obd()->atr("id_site")->valor;

    return self::url_site($id_site) . Refs::url_arquivo . "/";
  }

  final static public function url_legais($id_site) {
    if ($id_site == 58) return "legais/";

    return self::url_site($id_site) . Refs::url_legais . "/";
  }

  final static public function url_arquivo_priv($id_site) {
    $d = TrwlConfig::priv . "/{$id_site}/";

    if (!is_dir($d)) mkdir($d);

    return $d;
  }

  final static public function url_tmp($url_0 = null) {
    $d = Refs::url_tmp . "/";

    if ($url_0 != null) $d = $url_0 . $d;

    if (!is_dir($d)) mkdir($d);

    return $d;
  }

  final static public function __httptriwus($url) {
    return (strpos($url, self::httptriwus) === 0);
  }



  final public static function cookie_firma_calcula(FGS_usuario $u, string $k, string $v = "", string $Yn = null):string {
    if ($Yn == null) $Yn = date("Yn");
    
    return  hash('sha256', $k . $v . $Yn . $u->atr("k4")->valor . $_SERVER["HTTP_USER_AGENT"] );
  }

  final public static function cookie_firma_valida(FGS_usuario $u, string $k, string $v = ""):bool {
    if ( !isset($_COOKIE[$k])           ) return false;
    if ( ($firma = $_COOKIE[$k]) == null) return false;
    
    $Y = intval( date("Y") ); 
    $n = intval( date("n") );
    
    for ($i = 0; $i < 3; $i++) {
      $n = $n - $i; 
      
      if ($n < 1) {
        $n = 12;
        $Y--;
      }
      
      if ( $firma == self::cookie_firma_calcula($u, $k, $v, $Y . $n) ) return true;
    }

    
    return false;
  }


  protected function inicia_usuario_anterior(FGS_usuario $u = null) {
    if ($u == null) $u = new FGS_usuario();

    $this->usuario($u);
  }

  private static function _dmsx() {
    $d = new Div("msx");

    $d->ajax_css_display = "flex";

    return $d;
  }

  private static function url_paxina_param($k, $l) {
    if ($k != null) return "?k={$k}";

    if ($l != null) return "?l={$l}";

    return "";
  }
}
