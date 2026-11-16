<?php


final class Efs_paxina extends    Efs
                       implements IEfspax_xestor {

  public $id_site       = null;
  public $action        = null;
  public $url_limpa     = null;
  public $mobil         = true; //* a plantilla ten configuracións para versións

  private $a_ts         = null;
  private $id_oms       = null;
  private $id_paxina    = null;
  private $config       = null;
  private $config_mobil = null;
  
  private bool $b_kcookie = true;

  public function __construct() {
    parent::__construct(self::__paxina());

    $this->id_oms   (getenv("get.id_oms"));
    $this->id_paxina(getenv("get.id_pax"));


//~ echo "id-oms::" . getenv("get.id_pax") . "<br>";
//~ echo "id-oms::{$this->id_paxina}<br>";

    $this->control_kcookie();

    Efspax_xestor::construir($this, getenv("get.id_idioma"), false);


    $this->configurar();

    XStats_visita::anotar($this->id_paxina());
  }

  public function __site_obd($id_site = null, FS_cbd $cbd = null) {
    if ($cbd == null) $cbd = new FS_cbd();

    if (($oms = Opcion_ms_obd::inicia($cbd, $this->id_oms)) == null) return null;

    return $oms->site_obd($cbd);
  }

  public function id_oms($id_oms = null) {
    if ($id_oms == null) return $this->id_oms;

    $this->id_oms = $id_oms;
  }

  public function id_paxina($id_paxina = null) {
    if ($id_paxina == null) return $this->id_paxina;

    $this->id_paxina = $id_paxina;
  }

  public function id_idioma($id_idioma = null) {
    return $this->obxeto("cidioma")->id_idioma($id_idioma);
  }

  public function edicion() {
    return false;
  }

  public function config(Config_obd $c = null) {
    if ($c == null) {
      if (Efspax_xestor::__plataforma() != 3) return $this->config;

      if ($this->mobil) if ($this->config_mobil != null) return $this->config_mobil();

      return $this->config;
    }

    $this->config = $c;
  }

  public function config_mobil(Config_obd $c = null) {
    if ($c == null) return $this->config_mobil;

    $this->config_mobil = $c;
  }

  final public function configurar(Paxina_obd $pax_obd = null) {
    $cbd = new FS_cbd();

    if ($pax_obd == null) $pax_obd = Paxina_obd::inicia($cbd, $this->id_paxina);

    if (!MLsite_obd::calcula_idioma_activo($this->id_site, $pax_obd->atr("id_idioma")->valor, $cbd)) {
      die(Efspax_xestor::__erro("1"));
    }


    Efspax_xestor::config_lectura($this, false);

    //* config. pax. html e formulario
    $site_obd = $pax_obd->site($cbd);

    $this->paxina()->body        ->onload = "trw_body_onload({$this->id_paxina})";
    $this->paxina()->formulario()->action = $pax_obd->action();

    $smt_obd = $site_obd->metatags_obd($cbd);

    $smt_obd->__publicar($this->paxina(), $pax_obd);

    //* activa usuario
    if ($this->usuario()) return;

    $this->usuario($site_obd->usuario_obd($cbd));
  }

  public function operacion() {
    $this->post_msx();

    $this->control_kcookie(); //* control de la cookie de acceso.

    $this->operacion_get(); //* establece a interface para realizar operacións en componentes, seguindo a ruta parent::operacion()

//~ echo $this->evento()->html();

    if ($this->evento()->nome(1) == "redirect") {
      $id_paxina = $this->evento()->subnome(0);
      $sincro    = $this->evento()->subnome(1);

      return Efspax_xestor::operacion_redirect($this, $id_paxina, $sincro);
    }

    //* 20170129 manter compatibilidade con modrewrite de apache (.htaccess)
    //* 20170201 debemos cambiar a base en cada nova petición (por se hai un cambio de protocolo)
    $this->paxina()->head->base = Efs::url_base($this->id_site);
    //*---------------------------

    $this->control_panoramix();


    if (($e_aux = parent::operacion()) != null) return $e_aux;


    return $this;
  }

  final public function control_kcookie():void {
//~ echo "<pre>" . print_r($_COOKIE, 1) . "</pre>";

    //* Unha vez.
    if ( !$this->b_kcookie ) return;
    
    $this->b_kcookie = false;

    //* trw-kcookie_0.
    if (!isset($_COOKIE["trw-kcookie_0"])) return;


    $u = $this->usuario();

    if ($u->validado()) return;


    $cbd = new FS_cbd();

    if ($u->login_cookie($cbd, $_COOKIE["trw-kcookie_0"]) != 0) return;
    

    //* trw-kcookie_1.
    if ( !Efs::cookie_firma_valida($u, "trw-kcookie_1", $_COOKIE["trw-kcookie_0"]) ) return;
    
    
    //* loguea al usuario.
    $u = Pedido_obd::asignar_permisos($u, $cbd);
    
    Acceso::rexistrar(new FS_cbd(), 
                      $u->atr("id_site")->valor, 
                      $_SERVER["REMOTE_ADDR"], 
                      $u->atr("login")->valor, 
                      "pcontrol", 
                      1
                     );

    $this->usuario($u);
  }

  final public function control_panoramix() {
    $url = $this->url_arquivo($this->id_site) . "panoramix/{$this->id_paxina}/bd.json";
    
    $this->obxeto("haipanoramix")->post( is_file($url) ? "1" : "0" );
  }

  private function operacion_get() {
    $this->operacion_get_tpvRsponse3();

    $this->operacion_get_ctlg();

    $this->operacion_get_f();

    $this->operacion_get_p(); //*  !!! mantener orde

    $this->operacion_get_a();

    $this->operacion_get_b();

    $this->operacion_get_c();

    $this->operacion_get_r();
    
    $this->operacion_get_m();
   
    $this->operacion_get_n();

    $this->operacion_get_k(); //* !!! mantener orde
   
    $this->operacion_get_kp(); //* !!! mantener orde
  }

  private function operacion_get_a() {
    //~ echo "<pre>" . print_r($_GET, true) . "</pre>";

    if (!isset($_GET["a"]  )) return null;
    
        if (!isset($_GET["pax"])     ) $_GET["pax"] = 1;
    elseif (!is_numeric($_GET["pax"])) $_GET["pax"] = 1;
   
    
    $_f = [];
    if (isset($_GET["f0"])) $_f["f0"] = $_GET["f0"];
    if (isset($_GET["f1"])) $_f["f1"] = $_GET["f1"];
    if (isset($_GET["f2"])) $_f["f2"] = $_GET["f2"];

    $this->evento( new Ev_reservado("almacen", [$_GET["a"], $_GET["pax"], "_f" => $_f]) );
  }

  private function operacion_get_f() {
    if (!isset($_GET['f'])) return null;

    $this->evento( new Ev_reservado("filtrado", $_GET) );
  }

  private function operacion_get_ctlg() {
//~ echo "<pre>" . print_r($_SERVER, 1) . "</pre>";
	  
    if (!isset($_GET["ctlg"])) return null;
   
  
        if (!isset($_GET["pax"])     ) $_GET["pax"] = 1;
    elseif (!is_numeric($_GET["pax"])) $_GET["pax"] = 1;
    
    $_f = [];
    if (isset($_GET["f0"])) $_f["f0"] = $_GET["f0"];
    if (isset($_GET["f1"])) $_f["f1"] = $_GET["f1"];
    if (isset($_GET["f2"])) $_f["f2"] = $_GET["f2"];
    if (isset($_GET["f3"])) $_f["f3"] = $_GET["f3"];

    $this->evento( new Ev_reservado("catalogo", [$_GET["ctlg"], $_GET["pax"], "_f" => $_f]) );
  }

  private function operacion_get_tpvRsponse3() {
//~ echo "<pre>" . print_r($_SERVER, 1) . "</pre>";
	  
    if (!isset($_GET["tpvResponse3"])) return null;
    
    if (!isset($_GET["kok"])         ) return null;


    $this->evento( new Ev_reservado( "tpvResponse3", [$_GET["tpvResponse3"], $_GET["kok"]] ) );
  }

  private function operacion_get_p() {
    //~ echo "<pre>" . print_r($_GET, true) . "</pre>";

    if (!isset($_GET['p'])) return null;

    $this->evento( new Ev_reservado("producto", $_GET['p']) );
  }

  private function operacion_get_b() {
    if (!isset($_GET['b'])) return null;
    
    if (!isset($_GET['pax'])) $_GET['pax'] = 1;

    $this->evento( new Ev_reservado("buscador", [$_GET['b'], $_GET['pax']]) );
  }

  private function operacion_get_c() {
//~ echo "<pre>" . print_r($_SERVER, true) . "</pre>";

    if (!isset($_GET['c'])) return null;
    
    $r = (isset($_GET['redirect'])) ? $_GET['redirect'] : null ;

    $this->evento( new Ev_reservado("confirmar", [ $_GET['c'], $r ]) );
  }

  private function operacion_get_r() {
//~ echo "<pre>" . print_r($_SERVER, true) . "</pre>";

    if (!isset($_GET['r'])) return null;

    $this->evento( new Ev_reservado("retomar-pago", $_GET['r']) );
  }

  private function operacion_get_k() {
    
    $id_oms    = Efspax_xestor::parse_idoms(getenv("get.id_oms"));
    $id_pax    = isset($_GET['k'])?Efspax_xestor::parse_idpax($_GET['k']):null;

    $id_idioma = getenv("get.id_idioma");

//~ echo "[{$this->id_oms}::{$id_oms}]::[{$this->id_idioma}]::[{$id_idioma}]::[{$this->id_paxina}::{$id_pax}]<br>";

        if ($this->id_oms != $id_oms) {
          if ($id_idioma == null) $id_idioma = $this->id_idioma();
        }
    elseif ($id_idioma    != null   );
    elseif ($id_pax       == null   ) return null;  //* Non  precisa recarga de páxina.


    if ($this->id_paxina != $id_pax) {
//~ echo "1111111111111111111111111111<br>";
      $p = Efspax_xestor::__paxobd(new FS_cbd(), $id_oms, $id_pax, $id_idioma, false);

      if ($p == null) {
        die(Efspax_xestor::__erro("1"));

        return;
      }

      if (!$p->publicada()) {
        die(Efspax_xestor::__erro("1"));

        return;
      }


      $this->recarga($p);
    }
  }

  private function operacion_get_m() {
    //~ echo "<pre>" . print_r($_SERVER, true) . "</pre>";
    //~ echo "<pre>" . print_r($_GET, true) . "</pre>";

    if (!isset($_GET["m"])) return null;
    
        if (!isset($_GET["pax"])     ) $_GET["pax"] = 1;
    elseif (!is_numeric($_GET["pax"])) $_GET["pax"] = 1;
    
    $_f = [];
    if (isset($_GET["f0"])) $_f["f0"] = $_GET["f0"];
    if (isset($_GET["f1"])) $_f["f1"] = $_GET["f1"];
    if (isset($_GET["f2"])) $_f["f2"] = $_GET["f2"];
    
    $_p = [ $_GET["m"], 
            $_GET["pax"],
            basename($_SERVER["SCRIPT_FILENAME"]),
            "_f" => $_f
          ];

    $this->evento( new Ev_reservado("marcas", $_p) );
  }

  private function operacion_get_n() {
    if (!isset($_GET['n'])) return;

    $id_oms   = Efspax_xestor::parse_idoms(getenv("get.id_oms"));
    $nome_pax = $_GET['n'];

    $p = Paxina_obd::inicia_md5(new FS_cbd(), $this->id_site, $id_oms, $this->id_idioma(), $nome_pax);

    if ($p == null) {
      die(Efspax_xestor::__erro("1"));

      return;
    }


    $this->recarga($p);
  }

  private function operacion_get_kp() {
    if (!isset($_GET['p'])) return;

    $this->obxeto("detalle")->operacion_kp($this, $_GET['p']);
  }

  private function recarga(Paxina_obd $p) {
    $u = $this->usuario();

    if ( !Erro::__parse_permisos($p->a_grupo(), $u->a_permisos()) ) {
//~ echo "<pre>" . print_r($_SERVER, 1) . "</pre>";

      $base     = $this->url_base( $this->id_site );
      //~ $redirect = base64_encode( urldecode($_SERVER["REQUEST_URI"]) );
      $redirect = $_SERVER["REQUEST_URI"];

      $this->redirect("{$base}login.php?redirect={$redirect}");

      return;
    }

    Efspax_xestor::pon_paxina_obd($this, $p, false);

    $this->configurar($p);
  }

  private static function __paxina() {
    $p = new Paxina("triwus.paxina");

    //~ $p->head->pon_meta("Content-Security-Policy" , "default-src https://cdn.example.net; child-src 'none'; object-src 'none'", "Content-Security-Policy");

    $p->head->pon_css(Refs::url("css/triwus.css"));
    $p->head->pon_css(Refs::url("css/basico.css"));
    $p->head->pon_css(Refs::url("css/trwform.css"));
    $p->head->pon_css(Refs::url("css/elmt.css"));
    $p->head->pon_css(Refs::url("css/carrusel.css"));
    $p->head->pon_css(Refs::url("css/venta.css"));
    $p->head->pon_css(Refs::url("css/cpagar.css"));
    $p->head->pon_css(Refs::url("css/catalogo.css"));

    $p->head->pon_js(Refs::url("js/basico.js"));
    $p->head->pon_js(Refs::url("js/prgf.js"));
    $p->head->pon_js(Refs::url("js/elmt.js"));

    $p->head->pon_js(Tilia::home() . "js/cknovo.js");
    $p->head->pon_js(Tilia::home() . "js/reloxio.js");


    $p->formulario()->ptw = Refs::url("ptw/form/ffs_paxina.html");

    $p->pon_panel(new Panel("centro"));

    return $p;
  }
}


/********************************/


class Ev_reservado extends EventoHTTP {

  public function __construct($tipo, $v) {
    parent::__construct(null);

    $this->valor = array("ajax"=>0, "tipo"=>"op-reservada", "nome"=>array($tipo, $v));
  }

  public function control_str($str) {
    return false;
  }

  public function control_ohttp(ObxetoFormulario $o) {
    return false;
  }
}

