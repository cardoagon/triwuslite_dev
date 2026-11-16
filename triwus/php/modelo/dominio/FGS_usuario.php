<?php

class FGS_usuario extends    Obxeto_bd
                  implements ICambio_obd {

  const lonx_k2          = 3;
  const log_max_intentos = 5;

  protected $nav = ["cpago"=>null];

  public function __construct() {
    parent::__construct();

    $this->nav['ct'] = 0;

    $this->atr("permisos")->valor = "pub";
  }

  public function mapa_bd() {
    return new Usuario_mbd();
  }

  public function baleiro() {
    return ($this->atr("id_usuario")->valor == null);
  }

  public static function inicia(FS_cbd $cbd, $id_usuario) {
    $u = new FGS_usuario();

    $u->select($cbd, "usuario.id_usuario = '{$id_usuario}'");

    return $u;
  }

  public static function inicia_contacto(FS_cbd $cbd, $id_contacto) {
    $u = new FGS_usuario();

    $this->atr("id_contacto")->valor = $id_contacto;

    $u->select($cbd, $this->atr("id_contacto")->sql_where($cbd));

    return $u;
  }

  public static function inicia_email(FS_cbd $cbd, $email, $id_site = null) {
    $u = new FGS_usuario();

    $u->select_email($cbd, $email, $id_site);

    return $u;
  }

  public function login_pcontrol(FS_cbd $cbd, $email, $contrasinal, $dominio = null) {
    $this->atr("login"      )->valor = $email;

    $wh_e = $this->atr("login")->sql_where($cbd);
    $wh_c = "((id_almacen > 1) or (permisos = 'admin'))";


	  return $this->__loginbase($cbd, "{$wh_c} and {$wh_e}", $contrasinal); //* NON comprobamos acceso peradmin.
  }

  public function login_web(FS_cbd $cbd, $id_site, $email, $contrasinal) {
    $this->atr("id_site")->valor = $id_site;
    $this->atr("login"  )->valor = $email;

    $wh_s = $this->atr("id_site")->sql_where($cbd);
    $wh_e = $this->atr("login"  )->sql_where($cbd);

	return $this->__loginbase($cbd, "{$wh_s} and {$wh_e}", $contrasinal);
  }

  public function login_cookie(FS_cbd $cbd, $cookie) {
    $this->atr("kcookie")->valor = $cookie;

    $wh_k = $this->atr("kcookie")->sql_where($cbd);

    return $this->__loginbase($cbd, $wh_k);
  }

  public function login_intentos() {
    return $this->nav['ct'];
  }

  public function permiso($p) {
    return (array_search($p, $this->a_permisos()) !== FALSE);
  }

  public function pon_permiso($p) {
	if ($this->permiso("admin")) return;
	
    $this->atr("permisos")->valor .= ", {$p}";
  }

  public function sup_permiso($p) {
    $this->atr("permisos")->valor = str_replace(", {$p}", "", $this->atr("permisos")->valor); //* non elimina 'pub' ou 'admin'
  }

  public function a_permisos() {
    //~ return explode(", ", $this->atr("permisos")->valor);
    return self::explode_permisos($this->atr("permisos")->valor);
  }
  
  public static function valida_permisos($p) {
    $p = Valida::comas( strtolower($p) );
    
    if ($p == null) return "pub";
    
    if (strpos($p, "pub") === FALSE) $p = "pub, {$p}";
    
    //~ $_p = explode(", ", $p);
    $_p = self::explode_permisos($p);
    
    $paux = "";
    foreach($_p as $pi) {
      if ($pi == "") return "pub";
      
      if ($pi == "admin") continue;
      
      if ($paux != "") $paux .= ", ";
      
      $paux .= $pi;
    }
    
    if ($paux == "") $paux = "pub";


    return $paux;
  }

  public function update_k(FS_cbd $cbd, $k) {
    $this->atr("contrasinal")->cifrar(true);
    $this->atr("k2"         )->cifrar(true);
    $this->atr("k3"         )->cifrar(true);
    $this->atr("k4"         )->cifrar(true);


    $k2 = self::calcula_k2($k);

    $this->atr("k_renovar"  )->valor = null;
    $this->atr("contrasinal")->valor = $k;
    $this->atr("k2"         )->valor = $k2;
    $this->atr("k3"         )->valor = $k;
    $this->atr("k4"         )->valor = $k2;

    return $cbd->executa($this->sql_update($cbd));
  }
/*
  private function update_k3(FS_cbd $cbd, $k) {
    $this->atr("contrasinal")->cifrar(false);
    $this->atr("k2"         )->cifrar(false);
    $this->atr("k3"         )->cifrar(true);
    $this->atr("k4"         )->cifrar(true);

    $this->atr("k3")->valor = $k;
    $this->atr("k4")->valor = self::calcula_k2($k);


    return $cbd->executa($this->sql_update($cbd));
  }
*/
  public function update_kcookie(FS_cbd $cbd, $k) {
    $this->atr("kcookie")->valor = $k;

    $sql = "update usuario set kcookie = '{$k}' where id_usuario = " . $this->atr("id_usuario")->valor;

    return $cbd->executa($sql);
  }

  public function update_permisos(FS_cbd $cbd) {
    $u = $this->atr("id_usuario")->valor;
    $p = $this->atr("permisos"  )->valor;

    return $cbd->executa("update usuario set permisos = '{$p}' where id_usuario = {$u}");
  }

  public function admin() {
    //* refírese a admin a nivel global, non é o admin dun site.

    return $this->atr("id_sb")->valor == "0";
  }

  public function venta(FS_cbd $cbd = null):bool {
    $s = $this->site_obd($cbd);
    
    return $s->venta($cbd);
  }

  public function bloqueado(FS_cbd $cbd = null) {
//~ echo "<pre>" . print_r($this->a_resumo(), true) . "</pre>";
    if ($this->atr('u_baixa')->valor == null) $this->atr('u_baixa')->valor = 0;

    return $this->atr('u_baixa')->valor > 0;
  }

  public function bloquear(FS_cbd $cbd) {
    $b = ($this->bloqueado($cbd))?"0":"1";

    $sql = "update usuario set baixa = '{$b}' where id_usuario = " . $this->atr("id_usuario")->valor;

    return $cbd->executa($sql);
  }

  public function validado() {
    return $this->atr("id_usuario")->valor != null;
  }

  public function select(FS_cbd $cbd, $where = null, $orderby = null) {
    $r = $cbd->consulta($this->sql_select($cbd, $where, $orderby));

    if (!$a = $r->next()) return;

    $this->post($a);
  }

  public function select_email(FS_cbd $cbd, $email, $id_site = null) {
    //~ $this->atr("email")->valor = $email;
    $this->atr("login")->valor = $email;

    //~ $wh_e = $this->atr("email")->sql_where($cbd);
    $wh_e = $this->atr("login")->sql_where($cbd);

    if ($id_site == null) {
      //~ $wh_c = "(not id_servizo is null)"; //* buscamos un usuario do panel de control
      $wh_s = "";
    }
    else {
      $this->atr("id_site")->valor = $id_site;

      //~ $wh_c = "(id_servizo is null)";     //* buscamos un usuario dun site triwus
      $wh_s = " and " . $this->atr("id_site")->sql_where($cbd);
    }

    //~ $this->select($cbd, "{$wh_e} and {$wh_c}{$wh_s}");
    $this->select($cbd, "{$wh_e}{$wh_s}");
  }

  public function insert(FS_cbd $cbd) {
    if ($this->atr("alta"   )->valor == null) $this->atr("alta"   )->valor = date("YmdHis");
    
    if ($this->atr("u_baixa")->valor == null) $this->atr("u_baixa")->valor = "0";

    if (!$cbd->executa($this->sql_insert($cbd))) return false;

    $this->calcula_id($cbd);

    return true;
  }

  public function update(FS_cbd $cbd) {
    $this->atr("contrasinal")->cifrar(false);
    $this->atr("k2"         )->cifrar(false);
    $this->atr("k3"         )->cifrar(false);
    $this->atr("k4"         )->cifrar(false);

    if ($this->atr("baixa"  )->valor == null) $this->atr("baixa"  )->valor = "0";
    if ($this->atr("u_baixa")->valor == null) $this->atr("u_baixa")->valor = "0";

    return $cbd->executa($this->sql_update($cbd));
  }

  public function update_contacto(FS_cbd $cbd, Contacto_obd $c) {
    if ($this->atr("id_contacto")->valor == null) return false;

    $c2 = Contacto_obd::inicia($cbd, $this->atr("id_contacto")->valor, "p");

    $idc = $c2->atr("id_contacto")->valor;
    $idt = $c2->atr("tipo"       )->valor;
    $ide = $c2->atr("id_enderezo")->valor;

    $c2->post($c->a_resumo());

    $c2->atr("id_contacto")->valor = $idc;
    $c2->atr("tipo"       )->valor = $idt;
    $c2->atr("id_enderezo")->valor = $ide;

    $c2->atr("endz_c"     )->valor = $idc;
    $c2->atr("endz_t"     )->valor = $idt;

    return $c2->update($cbd);
  }

  public function delete(FS_cbd $cbd) {
    //* elimina pedidos asociados.
    if (($_p = $this->_pedido(5, $cbd)) != null) {
      $s = $this->atr("id_site")->valor;

      foreach ($_p as $_p2) {
        $p = Pedido_obd::inicia($cbd, $s, $_p2["ano"], $_p2["numero"]);

        if (!$p->delete($cbd)) return false;
      }
    }

    //* elimina contactos.
    if (($c = $this->contacto_obd($cbd)) != null) if (!$c->delete($cbd, true)) return false;

    //* elimina usuario.
    return $cbd->executa($this->sql_delete($cbd));
  }

  public function delete_valida(FS_cbd $cbd = null) {
    $u = $this->atr("id_usuario")->valor;
    //* Non se pode borrar a un usuario asociado a un almacen.
    if (($a = $this->atr("id_almacen")->valor) != null) {
      return "ERROR. no se puede borrar el usuario ({$u}) porque esta relacionado con el almacén ({$a}).<br>";
    }


    //* Non se pode borrar a un usurio con pedidos que teñan menos de 5 anos, porque os pedidos asociados ao usuario tamen seran eliminados.
    if (($_p = $this->_pedido(5, $cbd, 1)) == null) return null;
//~ echo "<pre>" . print_r($_p, 1) . "</pre>";

    $u = $this->atr("id_usuario")->valor;
    $p = "{$_p[0]["numero"]}-{$_p[0]["ano"]}";

    return "ERROR. no se puede borrar el usuario ({$u}) porque al menos existe un pedido relaccionado ({$p}) inferior a 5 años.<br>";
  }

  public function contacto_obd(FS_cbd $cbd = null, $tipo = "p") {
    if (($id_contacto = $this->atr("id_contacto")->valor) == null) {
      $c = new Contacto_obd($tipo);

      $c->atr("email")->valor = $this->atr("login")->valor;

      return $c;
    }

    //~ if ($cbd == null) $cbd = ($this->permiso("admin")) ? new Xesta_cbd() : new FS_cbd();
    if ($cbd == null) $cbd = new FS_cbd();

    return Contacto_obd::inicia($cbd, $this->atr("id_contacto")->valor, $tipo);
  }

  public function contacto_obd_2($tipo = "p", FS_cbd $cbd = null) {
    if (($id_contacto = $this->atr("id_contacto")->valor) == null) return new Contacto_obd($tipo);

    //~ if ($cbd == null) $cbd = ($this->permiso("admin")) ? new Xesta_cbd() : new FS_cbd();
    if ($cbd == null) $cbd = new FS_cbd();

    return Contacto_obd::inicia($cbd, $id_contacto, $tipo);
  }

  public function en_custodia() {
    //~ return ($this->atr("id_servizo")->valor == null) || ($this->atr("baixa")->valor != null);
    return ($this->atr("baixa")->valor != null); //*** Sistemas multivendedor. Vendedores non teñen servizo. Imos entender en custodia, como aqueles sites dados de baixa.
  }

  public function site_obd(FS_cbd $cbd = null):?Site_obd {
    if ($cbd == null) $cbd = new FS_cbd();

    if (($s = $this->atr("id_site")->valor) == null) return null;

    return Site_obd::inicia($cbd, $s);
  }

  public function siteConfig_obd(FS_cbd $cbd = null):?Site_config_obd {
    if ($cbd == null) $cbd = new FS_cbd();

    if (($s = $this->atr("id_site")->valor) == null) return null;

    return Site_config_obd::inicia($cbd, $s);
  }

  public function cuenta_obd(FS_cbd $cbd = null):?Cuenta_obd {
    if ($cbd == null) $cbd = new FS_cbd();
    
    return new Cuenta_obd($this, $cbd);
  }

  public function multilinguaxe(FS_cbd $cbd = null) {
    return true;
  }

  public function icambio_obd() { //* IMPLEMENTA ICambio_obd::icambio_obd()
    $where = "click is null and id_usuario = " . $this->atr("id_usuario")->valor;

    $c = new Cambio_k_obd();

    $c->select(new FS_cbd(), $where);

//~ echo "<pre>" . print_r($c->a_resumo(), 1) . "</pre>";

    $c->atr("id_usuario")->valor = $this->atr("id_usuario")->valor; //* se $c baleiro, enton e preciso.
    $c->atr("id_site"   )->valor = $this->atr("id_site"   )->valor; 

    return $c;
  }

  public function id_site() { //* IMPLEMENTA ICambio_obd::id_site()
    return $this->atr("id_site")->valor;
  }

  final public static function explode_permisos(string $s):array {
    return explode(", ", $s);
  }
  
  final public function _pedido($anos = null, FS_cbd $cbd = null, $limit = 0) {
    //* devolve array de referencias a pedidos relacionados co usuario no site.
    if (($s = $this->atr("id_site"   )->valor) == null) return null;
    if (($u = $this->atr("id_usuario")->valor) == null) return null;
    if (($m = $this->atr("login"     )->valor) == null) return null;

    $wh_a = "(1)"; if (($anos = abs($anos)) > 0) $wh_a = "momento > date_sub(now(), interval {$anos} year)";

    $l = ""; if ($limit > 0) $l = " limit 0, {$limit}";

    $sql = "select numero, ano
              from pedido
             where (id_site = {$s}) and ({$wh_a})
               and ((id_cliente = {$u}) or (cliente_email = '{$m}'))
               {$l}";

    if ($cbd == null) $cbd = new FS_cbd();

    $r = $cbd->consulta($sql);

    $_p = null;
    while ($_r = $r->next()) {
      $_p[] = ["ano" => $_r["ano"], "numero" => $_r["numero"]];
    }

    return $_p;
  }

  final public function copia_historia(FGS_usuario $u) {
    $this->nav = $u->nav;
  }

  final public function resposta($id_elmt, $id_eer) {
    if (!Enquisa_resposta_obd::resposta($id_elmt, $id_eer)) return false;

    $this->nav['enquisa'][$id_elmt] = $id_eer;

    return true;
  }

  final public function enquisa_resposta($id_elmt) {
    return $this->nav['enquisa'][$id_elmt];
  }

  final public function cpago(Ususario_cpago $c = null) {
    if ($c != null) $this->nav['cpago'] = $c;

    if ($this->nav['cpago'] == null) $this->nav['cpago'] = new Ususario_cpago($this);

    return $this->nav['cpago'];
  }


  final public function pcontrol_tipo() {
    if ($this->atr("id_almacen")->valor > 1) return 30;

    if ($this->atr("id_sb")->valor == 1000)  return 20;
    if ($this->atr("id_sb")->valor == 1001)  return 20;

    return 10;
  }

  public static function calcula_k2($k) {
    if ($k == null) return null;

    return substr($k, -3);
  }


  private function __loginbase(FS_cbd $cbd, $where, $contrasinal = null):int {
    /*
      Function: __loginbase

      Return: n:integer

              0: validación OK
             -1: superado el número máximo de fracasos permitidos
          n > 0: nº de fracasos
    */
    
    $this->atr("id_usuario")->valor = null; //* VER $this->validado()

    if ($where != null) $this->select($cbd, "site.baixa is null and usuario.baixa < 1 and ({$where}) and contacto.tipo = 'p'");

    if (!$this->validado()) {
      if (($this->nav['ct']++) >= self::log_max_intentos) return -1;

      return $this->nav['ct'];
    }

//~ echo $contrasinal. "::" . $this->atr("k3")->valor . "<br>";

	
    if ($contrasinal === null) {
      if (($this->nav['ct']++) >= self::log_max_intentos) return -1;

      return $this->nav['ct'];
    }
	

	  if ( !password_verify($contrasinal, $this->atr("k3")->valor) ) {
	    if (($this->nav['ct']++) >= self::log_max_intentos) return -1;
	    
	    return $this->nav['ct'];
	  }


    //* usuario validado.
    $this->nav['ct'] = 0;

    return 0;
  }
/*
  private function login_pcontrol_peradmin(FS_cbd $cbd, $email, $contrasinal, $dominio = null):int {
	//* Validamos config global para peradmin.
	//* PRECOND:: Refs::s_trwadm:int = -1.
	if (($s = Refs::s_trwadm) < 0) return -1;

	
	//* Validamos dominio.
	if ($dominio == null) return -1;

	
	//* Comprobamos se está habilitado o acceso peradmin para o dominio.
	$_p = Site_config_obd::peradmin($dominio, $cbd);
	
	if ( $_p    == [] ) return -1;
	if ( $_p[0] != 1  ) return -1;
	
    
    //* Realiza consulta __loginbase().
    $wh_e = $this->atr("login")->sql_where($cbd);
    $wh_c = "id_site = {$s} and permisos = 'trwadm'";


    return $this->__loginbase($cbd, "{$wh_c} and {$wh_e}", $contrasinal);
  }
*/
  private function calcula_id(FS_cbd $cbd) {
    $r = $cbd->consulta("select LAST_INSERT_ID() as id");

    if (!$a = $r->next()) die("FGS_usuario.calcula_id(), (!a = r.next())");

    $this->atr("id_usuario")->valor = $a['id'];
  }
}

//-----------------------------------------------

class UsuarioWeb_obd extends FGS_usuario {

  public function __construct() {
    parent::__construct();
  }

  public static function inicia(FS_cbd $cbd, $id_usuario) {
    $u = new UsuarioWeb_obd();

    $u->select($cbd, "usuario.id_usuario = {$id_usuario}");

    return $u;
  }

  public function contacto_obd(FS_cbd $cbd = null, $tipo = 'p') {
    $c = parent::contacto_obd($cbd);

    $c->pcontrol = false;

    return $c;
  }

  public function icambio_obd() {  //* IMPLEMENTA ICambio_obd::icambio_obd()
    $where = "click is null and id_usuario = " . $this->atr("id_usuario")->valor;

    $c = new Cambio_kweb_obd();

    $c->select(new FS_cbd(), $where);

    $c->atr("id_usuario")->valor = $this->atr("id_usuario")->valor;
    $c->atr("id_site"   )->valor = $this->atr("id_site"   )->valor;

    return $c;
  }
}

//********************************************

final class Usuario_mbd extends Mapa_bd {
  public function __construct() {
    $this->pon_taboa(self::tusuario());

    $t2 = new Taboa_dbd("contacto");

    $t2->pon_campo("cnif_d");
    $t2->pon_campo("nome");
    $t2->pon_campo("razon_social");
    $t2->pon_campo("email");

    $this->relacion_fk($t2, array("id_contacto"), array("id_contacto"), "left");


    $t3 = new Taboa_dbd("site");

    $t3->pon_campo("baixa");

    $this->relacion_fk($t3, array("id_site"), array("id_site"), "left");

/*
    $myxesta_b = TrwlConfig::mysql_b;

    $t4 = new Taboa_dbd("{$myxesta_b}.v_servizo_plan");

    $t4->pon_campo("id_servizo"   , new Numero());
    $t4->pon_campo("id_sb"        , new Numero());
    $t4->pon_campo("max_GB"       , new Numero());
    $t4->pon_campo("multilinguaxe", new Numero());
    //~ $t4->pon_campo("venta"        , new Numero());
    $t4->pon_campo("email"        , new Numero(), false, "maxmail");

    $this->relacion_fk($t4, array("id_usuario"), array("id_usuario"), "left");
*/

    $t5 = new Taboa_dbd("almacen");

    $t5->pon_campo("id_almacen"   , new Numero());

    $this->relacion_fk($t5, array("id_contacto"), array("id_contacto"), "left"); //* OLLO !!!, contacto tipo "a".
  }

  public static function tusuario() {
    $t = new Taboa_dbd("usuario");

    $t->pon_campo("id_usuario" , new Numero()      , true            );
    $t->pon_campo("id_contacto", new Numero()                        );
    $t->pon_campo("id_site"    , new Numero()                        );
    $t->pon_campo("login"      , new Varchar()                       );
    $t->pon_campo("contrasinal", new Psswd()                         );
    $t->pon_campo("k2"         , new Psswd()                         );
    $t->pon_campo("k3"         , new Psswd2()                        ); //*** esta é a boa.
    $t->pon_campo("k4"         , new Psswd2()                        );
    $t->pon_campo("k_renovar"  , new Numero()                        );
    $t->pon_campo("kcookie"    , new Varchar()                       );
    $t->pon_campo("permisos"                                         );
    $t->pon_campo("pcontrol2"  , new Numero()                        );
    //~ $t->pon_campo("f2_m"       , new Data("YmdHis")                  );
    //~ $t->pon_campo("f2_k"       , new Varchar()                       );
    $t->pon_campo("alta"       , new Data("YmdHis")                  );
    $t->pon_campo("baixa"      , new Varchar()     , false, "u_baixa");

    return $t;
  }
}

//***********************************************

final class Ususario_cpago extends FGS_usuario {

  public function __construct(FGS_usuario $u) {
    $this->copia_historia($u);
  }

  public function limpar() {
    $this->nav['cpago'] = null;
  }

  public function carro(Ususario_carro $c = null) {
    if ($c != null) $this->nav['cpago']['carro'] = $c;

    //~ if ($this->nav['cpago']['carro'] == null) $this->nav['cpago']['carro'] = new Ususario_carro();
    if (!isset($this->nav['cpago']['carro'])) $this->nav['cpago']['carro'] = new Ususario_carro();

    return $this->nav['cpago']['carro'];
  }

  public function destino(Contacto_obd $destino = null) {
    if ($destino != null) $this->nav['cpago']['destino'] = $destino;

    if (!isset($this->nav['cpago']['destino'])) return null;

    return $this->nav['cpago']['destino'];
  }

  public function _prefs($_prefs = null) {
    if ($_prefs != null) $this->nav['cpago']['_prefs'] = $_prefs;
    
    if (!isset($this->nav['cpago']['_prefs'])) return [null, null, null];

    return $this->nav['cpago']['_prefs'];
  }

  public function mrw_config($mrw_config = null) {
    if ($mrw_config != null) $this->nav['cpago']['mrw_config'] = $mrw_config;

    if ($this->nav['cpago']['mrw_config']['recolle'] == null) $this->nav['cpago']['mrw_config']['recolle'] = 1;

    return $this->nav['cpago']['mrw_config'];
  }

  public function fpago(Pedido_fpago_obd $fpago = null) {
    if ($fpago != null) $this->nav['cpago']['fpago'] = $fpago;

    if (!isset($this->nav['cpago']['fpago'])) $this->nav['cpago']['fpago'] = new Pedido_fpago_obd();

    return $this->nav['cpago']['fpago'];
  }
}

//***********************************************

final class Ususario_carro implements Iterador_bd {
  private $xpromos = null; //* xestor de promos.

  private $a;

  //~ private $a_promos;       //* promocións da compra.

  private $cargofpago = null;

  private $i = -1;

  public $id_destino  = null; //* será el codigo_postal  (permite calcular impuestos, portes y tipo de facturación)
  public $id_country  = null; //* será el codigo_country (permite calcular impuestos, portes y tipo de facturación)

  public $finde       = false;

  private $_alma      = null;

  public function a_arti($i = -1) {
//~ echo "articulo_i:{$i}<br>";

    if ($i == -1) return $this->a;

    return $this->a[$i];
  }

  public function xpromos() {
    return $this->xpromos;
  }

  public function post(Articulo_obd $a, $i = -1, $unidades_pedir = 1, $regalo = false) {
    $id_site = $a->atr("id_site")->valor;

    //* 1.- Inicia promos sii ($this->xpromos == null).
    if ($this->xpromos == null) $this->xpromos = new XUCarro_promos($id_site);


    //* 2.- Engade artículo ao carriño.
    if ($i > -1) {
      $this->post_unidades($i, $this->a[$i][3] + $unidades_pedir);

      return $i;
    }

    if (($i = $this->busca($a)) > -1) {
      if ($regalo) return -1; //* ERRO, deberemos tratalo posteriormente.

      $this->post_unidades($i, $this->a[$i][3] + $unidades_pedir);

      return $i;
    }


    //* engadimos un novo producto.

    $i = $this->ct();

    if ($regalo) {
      //* Non se aplicarán promocións a un regalo

      $promo_d = -1;
      $promo_r = -1;
    }
    else {
      $promo_d = $this->xpromos->post_articulo( $a->atr("id_articulo")->valor, false );
      $promo_r = $this->xpromos->post_articulo( $a->atr("id_articulo")->valor, true  );
    }


    $this->a[$i] = $this->__a_post($a, $i, $unidades_pedir, $promo_d, $promo_r);

//~ print_r($this->a);

    return $i;
  }

  public function post_regalo(Articulo_obd $a, $unidades_pedir = 1) {
    $a->atr("pvp"   )->valor = 0;
    $a->atr("desc_p")->valor = 100;

    return $this->post($a, -1, $unidades_pedir, true);
  }

  public function post_pedido(Pedido_obd $p) {
    //* 0.- Para unha operación retomar-pago, primeiro eliminamos o contido previo do carriño.
    $this->sup();

    //* 1.- Inicia promos sii ($this->xpromos == null).
    if ($this->xpromos == null) $this->xpromos = new XUCarro_promos( $p->atr("id_site")->valor );

    //* 2.- Engade pedido ao cariño.
    $_pa = $p->_pedido_articulo();

    if (count($_pa) == 0) return -1;

    foreach ($_pa as $pa) {
      $i = $this->ct();

      $promo_d = $this->xpromos->post_articulo( $pa->atr("id_articulo")->valor, false );
      $promo_r = $this->xpromos->post_articulo( $pa->atr("id_articulo")->valor, true  );

      $this->a[$i] = $this->__a_post_pa($pa, $i);
    }

//~ echo "<pre>" . print_r($this->a, true) . "</pre>";

    return $i;
  }

  public function esRegalo($i) {
    if ($this->a[$i]["promo_d"] != -1) return false;
    if ($this->a[$i]["promo_r"] != -1) return false;


    return true;
  }

  public function busca(Articulo_obd $a, $k = "k") {
    $ct          = $this->ct();
    $id_articulo = $a->atr("id_articulo")->valor;

    for ($i = 0; $i < $ct; $i++) if ($this->a[$i][$k] == $id_articulo) return $i;


    return -1;
  }

  public function almacen($a = null) {
//~ echo "id_almacen: $a <br>";
    if ($a == null) return $this->_alma;

    return $this->_alma[$a];
  }

  public function _info() {
    return array("u" => $this->ct(),
                 "t" => number_format($this->total(), 2, ",", ".")
                );
  }

  public function ct() {
    if (!is_array($this->a)) return 0;

    return count($this->a);
  }

  public function ct_articulos() {
    return $this->ct();
  }

  public function ct_alma() {
    if (!is_array($this->_alma)) return 0;

    return count($this->_alma);
  }

  public function precisa_envio() {
    for ($i = 0; $i < $this->ct(); $i++) {

      if ($this->a[$i]['tipo'] != 's') if ($this->a[$i]['reserva'] != 1) return true;
    }

    return false;
  }

  public function hai_reservas() {
    for ($i = 0; $i < $this->ct(); $i++) if ($this->a[$i]['reserva'] == 1) return true;

    return false;
  }

  public function simular_compra(float $portes):bool {
    if ($portes > 0) return false;
    
    for ($i = 0; $i < $this->ct(); $i++) {
      if ($this->a[$i]['pvp_d3'] > 0) return false;
    }

    return true;
  }


  public function pvp($i, $ive = false, $des = false, $u = -1, $reserva = true) {
    if ($u == -1) $u = $this->a[$i][3];

    $pvp = $u * $this->a[$i]["pvp_d3"];

    if ($ive) {
      //* un pvp_plus súmase ao prezo sen ive, pero se mostra por separado no carrito.
      $pvp += ($u * $this->a[$i]["pvp_plus"]);

      $pvp += ($pvp * ($this->iva_aplicable($i) / 100));
    }

    if ($reserva) if ($this->a[$i]["reserva"] == 1) $pvp = $pvp * ($this->a[$i]["reserva_sinal"] / 100);

    if (!$des) return $pvp; //* Se non se aplican descontos, devolvemos o importe


    $promo = $this->xpromos->promo($this->a[$i]['promo_d'], "d");

    if ($promo != null) return $promo->aplicar($pvp, $this->total());

    return $pvp - ($pvp * ($this->a[$i]["desc_p"] / 100));
  }

  public function peso($i, $u = -1) {
    if (isset($this->a[$i]['servizo'])) if ($this->a[$i]['servizo'] == 1) return 0;

    if ($u == -1) $u = $this->a[$i][3];

    return $u * $this->a[$i]["peso"];
  }

  public function verificar_descontos_directos($p, $i) {
    $promo = $this->xpromos->promo($this->a[$i]['promo_d'], "d");

    if ( ($promo == null) && ($p > 0) ) return true;


    if ( $promo != null )               return $promo->verificar($this->total(), -1); //* OMITIMOS comprobación por código por xa se encarga UCarro.XPromos

    return false;
  }

  public function verificar_descontos_totais($t) {
    if ($this->xpromos == null) return null;

    $_p = $this->xpromos->_promo("t");

    if ( !is_array($_p) ) return null;

    $p = null;
    foreach ($_p as $k => $pi) {
      if ($pi->verificar($t, -1)) { //* o codigo xa voi validado anteriormente.
        $p = $pi;

        break;
      }
    }

    return $p;
  }

  public function oferta_html($p, $i) {
    $promo = $this->xpromos->promo($this->a[$i]['promo_d'], "d");

    if ($promo != null) return $promo->html_carro();

    return "OFERTA&nbsp;{$p}%"; //* ofertas directas - cxarticulos - (sempre en porcentaxe)
  }

  public function total() {
    /* calcula o total da compra, sen ter en conta aqueles descontos directos que dependen do montante da compra.
     *
     * este total servirá para contrastar se podemos aplicar ditos descontos.
     */

    if ( !is_array($this->a) ) return 0;

    $t = 0;
    for ($i = 0; $i < count($this->a); $i++) {
      $d = $this->a[$i]["desc_p"];
      $p = $this->xpromos->promo($this->a[$i]['promo_d'], "d");

      $pvp_i = $this->pvp($i, true);

          if ( ($p != null) && ($p->atr("compra_min")->valor == 0) ) $pvp_i = $p->aplicar($pvp_i);
      elseif ( ($p == null) && ($d > 0) )                            $pvp_i -= ($pvp_i * ( $d /100 ));

      $t += $pvp_i;
    }

    return $t;
  }

  public function iva_aplicable($i) {
    if ( Efs::destino_t2lf($this->id_destino, $this->id_country) ) return "0";

    return $this->a[$i][5];
  }

  public function post_promo_codigo($codigo = null) {
    if ($this->xpromos == null) return null;

    return $this->xpromos->post_codigo($codigo);
  }
/*
  public function promo_codigo() {
    return $this->a_promos['promo_codigo'];
  }
*/

/* 20221215. non se pode modificar un orzamento. BORRAR.
  public function post_prsto_modificado($i, Articulo_obd $a) {
    if (!$a->hai_presuposto()) return;

    $this->a[$i]["prsto_html"] = $a->prsto_html();
    $this->a[$i]["prsto_ops" ] = $a->a_ops_resumo();
    $this->a[$i]["pvp_plus"  ] = $a->pvp_plus();
  }
*/
  public function post_unidades($i, $u) {
    if ($u < $this->a[$i]["pedido_min"]) $u = $this->a[$i]["pedido_min"];
    if ($this->a[$i]["pedido_max"] > 0) if ($u > $this->a[$i]["pedido_max"]) $u = $this->a[$i]["pedido_max"];

    if ( $this->a[$i]["stock"] != -999  ) { //* stock == -999, non podemos validar stock.
      if ($u > $this->a[$i]["stock"]) {

        return [$u, true, $this->a[$i][1]]; //* ERRO, unidades solicitadas > stock.
      }
    }


    $this->a[$i][3] = $u;


    return [$u, false, null]; //* devolve as unidades validadas
  }

  public function post_cargofpago($t, $v, $tipo = "%") {
    $this->cargofpago = array("txt"=>$t, "v"=>$v, "tipo"=>$tipo);
  }

  public function sup_subtotal() {
    $this->cargofpago = null;
  }

  public function cargofpago() {
    return $this->cargofpago;
  }

  public function sup($i = -1) {
    if ($i == -1) {
      $this->a = null;

      return;
    }

    if ($this->ct() == 1) {
      $this->a = null;

      return;
    }

    //* elimina a o producto
    unset($this->a[$i]);

    //* renumera this.a
    $a = null; $i2 = 0;
    foreach($this->a as $a_i2) {
      $a_i2["i"] = $i2;

      $a[$i2] = $a_i2;

      $i2++;
    }

    $this->a = $a;
  }

  public function descFila() {}

  public function next() {
    $this->i++;

    $ct = (is_array($this->a))?count($this->a):0;

    if ($this->i >= $ct) {
      $this->i = -1;

      return null;
    }


    return $this->a[ $this->i ];
  }

  public function __a_pedido_arti(Pedido_obd $p) {
    if ($this->ct_articulos() == 0) return null;

    $a     = null;
    $k_aux = 1;
    foreach($this->a as $a_arti) {
      $describe_html = "";

      $pa = new Pedido_articulo_obd($p);

      $sinal_p = ($a_arti['reserva'] != 1)?null:$a_arti['reserva_sinal'];


      if ($a_arti["describe_html"] != null) $describe_html = "{$a_arti["describe_html"]}";

      if ($a_arti["prsto_html"] != null) {
        if ($describe_html != null) $describe_html = "<div>{$describe_html}</div><div>---</div>";

        $describe_html .= "<div>{$a_arti["prsto_html"]}</div>";
      }


      $pa->atr("id_articulo"     )->valor = $a_arti[0];
      $pa->atr("k_aux"           )->valor = $k_aux;
      $pa->atr("sinal_p"         )->valor = $sinal_p;
      $pa->atr("tipo"            )->valor = $a_arti["tipo"];
      $pa->atr("desc_p"          )->valor = $this->pedido_arti_dto($a_arti);
      $pa->atr("pvp"             )->valor = $a_arti["pvp_d3"];
      $pa->atr("pvp_plus"        )->valor = $a_arti["pvp_plus"];
      $pa->atr("pive"            )->valor = $this->iva_aplicable($a_arti["i"]);
      $pa->atr("moeda"           )->valor = $a_arti["moeda"];
      $pa->atr("unidades"        )->valor = $a_arti[3];
      $pa->atr("peso"            )->valor = $a_arti["peso"];
      $pa->atr("servizo_dias"    )->valor = $a_arti["servizo_dias"    ];
      $pa->atr("servizo_data"    )->valor = $a_arti["servizo_data"    ];
      $pa->atr("servizo_permisos")->valor = $a_arti["servizo_permisos"];
      $pa->atr("describe_html"   )->valor = $describe_html;
      $pa->atr("nome"            )->valor = $a_arti[1];
      $pa->atr("notas"           )->valor = $a_arti[2];
      $pa->atr("id_almacen"      )->valor = $a_arti["id_almacen"];

//~ echo "<pre>" . print_r($pa->a_resumo(), true) . "</pre>";

      $k_aux++;

      $a[] = $pa;
    }

    return $a;
  }

  private function pedido_arti_dto($a_arti) {
    if ( $this->esRegalo($a_arti['i']) ) return "100";

    $promo = $this->xpromos->promo($a_arti['promo_d'], "d");

    if ($promo != null) {
      $t = $this->total();

      if ( $promo->verificar($t, -1) ) return $promo->atr("dto")->valor; //* a promo con código xa foi validada.
    }

    return $a_arti["desc_p"];
  }

  public function validar_stock($id_site) {
    if ($this->ct_articulos() == 0) return null;

    $cbd = new FS_cbd();

    $erro = null;
    foreach($this->a as $a_arti) {
      if ($a_arti["tipo"] == "s") continue;

      $a_aux = Articulo_obd::inicia($cbd, $id_site, $a_arti[0]);

      $stock = $a_aux->atr("unidades")->valor;

      if ($a_arti[3] > $stock) {
        $erro .= "<div>&bull;&nbsp;El art&iacute;culo <b>{$a_arti[1]}</b>, sólo hay <b>{$stock} unidade(s) disponibles</b>.</div>";
      }
    }

    if ($erro == null) return null;

    return "Lo sentimos, no tenemos stock suficiente para atender tu pedido, debes modificar las unidades en el carrito de la compra, para los art&iacute;culos siguientes:<br />{$erro}";
  }

  private function rexistra_almacen($s, $a) {
    if (isset($this->_alma[$a])) return;

    $this->_alma[$a] = Almacen_obd::inicia(new FS_cbd(), $s, $a);
  }

  private function __a_post(Articulo_obd $a, $i, $unidades_pedir = 1, $promo_d = null, $promo_r = null) {
    $this->rexistra_almacen($a->atr("id_site")->valor, $a->atr("id_almacen_portes")->valor);

    if (isset($a->a_ops) && $a->a_ops != null) { //* a_ops: opcións de prsto.
      $k = $i * -1; //* Un metapresupuesto se convierte en un presupuesto, no vale el id_articulo.

      $pvp_plus   = $a->pvp_plus();
      $prsto_ops  = $a->a_ops_resumo();
      $prsto_html = $a->prsto_html();
    }
    else {
      $k = $a->atr("id_articulo")->valor;

      $pvp_plus   = 0;
      $prsto_ops  = null;
      $prsto_html = null;
    }

    if ($unidades_pedir < $a->atr("pedido_min")->valor) $unidades_pedir = $a->atr("pedido_min")->valor;

    if ($a->atr("pedido_max")->valor > 0)
      if ($unidades_pedir > $a->atr("pedido_max")->valor)
        $unidades_pedir = $a->atr("pedido_max")->valor;

    return array(0                   => $a->atr("id_articulo")->valor,
                 1                   => $a->atr("nome")->valor,
                 2                   => $a->atr("notas")->valor,
                 3                   => $unidades_pedir,
                 4                   => null,
                 5                   => $a->atr("pive")->valor,
                 6                   => null,
                 "k"                 => $k,
                 "i"                 => $i,
                 "url"               => self::__imx_url($a),
                 "moeda"             => $a->atr("moeda")->valor,
                 "peso"              => $a->atr("peso")->valor,
                 "tipo"              => $a->atr("tipo")->valor,
                 "desc_u"            => $a->atr("desc_u")->valor,
                 "desc_p"            => $a->atr("desc_p")->valor,
                 "precio_0"          => null,
                 "pvp_0"             => null,
                 "servizo_dias"      => $a->atr("servizo_dias"    )->valor,
                 "servizo_data"      => $a->atr("servizo_data"    )->valor,
                 "servizo_permisos"  => $a->atr("servizo_permisos")->valor,
                 "stock"             => $a->atr("unidades")->valor,
                 "pedido_min"        => $a->atr("pedido_min")->valor,
                 "pedido_max"        => $a->atr("pedido_max")->valor,
                 "describe_html"     => $a->describe_html(),
                 "prsto_html"        => $prsto_html,
                 "prsto_ops"         => $prsto_ops,
                 "pvp_d3"            => $a->atr("pvp")->valor,
                 "pvp_plus"          => $pvp_plus,
                 "promo_r"           => $promo_r,
                 "promo_d"           => $promo_d,
                 "id_almacen"        => $a->atr("id_almacen")->valor,
                 "id_almacen_portes" => $a->atr("id_almacen_portes")->valor,
                 "id_site"           => $a->atr("id_site")->valor,
                 "reserva"           => $a->atr("reserva")->valor,
                 "reserva_sinal"     => $a->atr("reserva_sinal")->valor
                );
  }

  private function __a_post_pa(Pedido_articulo_obd $pa, $i, $promo_d = null, $promo_r = null) {
    $this->rexistra_almacen($pa->atr("id_site"          )->valor,
                            $pa->atr("id_almacen_portes")->valor
                           );

    return array(0                   => $pa->atr("id_articulo")->valor,
                 1                   => $pa->atr("nome")->valor,
                 2                   => $pa->atr("notas")->valor,
                 3                   => $pa->atr("unidades")->valor,
                 4                   => null,
                 5                   => $pa->atr("pive")->valor,
                 6                   => null,
                 "k"                 => $pa->atr("id_articulo")->valor,
                 "i"                 => $i,
                 "url"               => self::__imx_url($pa),             //***  !!!
                 "moeda"             => $pa->atr("moeda")->valor,
                 "peso"              => $pa->atr("peso")->valor,
                 "tipo"              => $pa->atr("tipo")->valor,
                 "desc_u"            => 1, //* !!!
                 "desc_p"            => $pa->atr("desc_p")->valor,
                 "precio_0"          => null,
                 "pvp_0"             => null,
                 "servizo_dias"      => $pa->atr("servizo_dias"    )->valor,
                 "servizo_data"      => $pa->atr("servizo_data"    )->valor,
                 "servizo_permisos"  => $pa->atr("servizo_permisos")->valor,
                 "stock"             => -999,
                 "pedido_min"        => null,  //* !!!
                 "pedido_max"        => null,  //* !!!
                 "describe_html"     => $pa->atr("describe_html")->valor,
                 "prsto_html"        => null,
                 "prsto_ops"         => null,
                 "pvp_d3"            => $pa->atr("pvp")->valor,
                 "pvp_plus"          => $pa->atr("pvp_plus")->valor,
                 "promo_r"           => $promo_r,
                 "promo_d"           => $promo_d,
                 "id_almacen"        => $pa->atr("id_almacen")->valor,
                 "id_almacen_portes" => $pa->atr("id_almacen_portes")->valor,
                 "id_site"           => $pa->atr("id_site")->valor,
                 "reserva"           => null,  //* !!!
                 "reserva_sinal"     => null   //* !!!
                );
  }

  public static function __imx_url(ICLogo $l, FS_cbd $cbd = null) {
    if ($cbd == null) $cbd = new FS_cbd();

    $logo_obd = $l->iclogo_obd($cbd);

    $url = $logo_obd->url(null, $cbd);

    $_url = explode("/", $url);

    return "arquivos/" . $_url[count($_url) - 1];
  }
}

//----------------------------------------

final class XUCarro_promos {
  private $id_site;

  private $_pcodigo = null;

  private $_pp = array("d"=>null, "r"=>null, "t"=>null); //* promocións xproducto directas (%), total (€) e regalo.
  private $_pg = array("d"=>null, "r"=>null, "t"=>null); //* promocións globais directas (%), total (€) e regalo.

  public function __construct($id_site) {
    $this->id_site = $id_site;

    $this->_pg["d"] = Promo_obd::a_promos($id_site, "p", 1, new FS_cbd());
    $this->_pg["t"] = Promo_obd::a_promos($id_site, "a", 1, new FS_cbd());
    $this->_pg["r"] = Promo_obd::a_promos($id_site, "r", 1, new FS_cbd());
  }

  public function post_articulo($id_articulo, $regalo = false) {
    $cbd = new FS_cbd();

    $_p = Promo_obd::busca_articulo($cbd, $this->id_site, $id_articulo, $regalo);

    if ($_p == null) return null;

    $id_promo = $_p[0][0]; //* tomamos a primeira id_promo.

        if ($_p[0][1] == "a") $k = "t"; //* aplicable ao total
    elseif ($_p[0][1] == "p") $k = "d"; //* dto directo
    elseif ($_p[0][1] == "r") $k = "r"; //* regalo

//~ print_r($_p);
//~ print_r($this->_pp);

    if (isset($this->_pp[$k][$id_promo])) return $id_promo;

//~ echo "$k::$id_promo\n";

    $this->_pp[$k][$id_promo] = Promo_obd::inicia($cbd, $this->id_site, $id_promo);



    return $id_promo;
  }

  public function promos_codigo() {
    //* Devolve as promos con código validadas.
    return $this->_pcodigo;
  }

  public function post_codigo($codigo) {
    if ( isset($this->_pcodigo[$codigo]) ) return $this->_pcodigo[$codigo];

    $p = Promo_obd::inicia_codpromocional(new FS_cbd(), $this->id_site, $codigo);

    if ($p == null) return null;

    $this->_pcodigo[$codigo] = array("id_promo"   => $p->atr("id_promo")->valor,
                                     "nome"       => $p->atr("nome")->valor,
                                     "codigo"     => $codigo,
                                     "dto"        => $p->atr("dto")->valor,
                                     "compra_min" => $p->atr("compra_min")->valor,
                                     "regalo"     => ($p->atr("tipo_dto")->valor == 'r'),
                                     "total"      => ($p->atr("tipo_dto")->valor == '€'),
                                     "_arti_r"    => array()
                                    );

    return $this->_pcodigo[$codigo];
  }

  public function promo($id_promo, $tipo) {
    //* Busca promo global, (pg ten prioridade sobre unha pp).
    if ( is_array($this->_pg[$tipo]) ) {
      foreach ($this->_pg[$tipo] as $id_promo => $p) if ($this->valida_promo($p)) return $p;
    }

    //* buscamos unha promo x producto.
    if ($id_promo <= 0) return null;

    $p = null;
    if (isset($this->_pp[$tipo][$id_promo])) {
      $p = $this->_pp[$tipo][$id_promo];

      if ($this->valida_promo($p)) return $p;
    }

    return null;
  }

  public function _promo($tipo) {
    //* PRECOND:: $tipo IN (d, r, t)

    $_p = null;

    if (isset($this->_pp[$tipo])) {
	  if (is_array($this->_pp[$tipo])) {
	    foreach ($this->_pp[$tipo] as $id_promo => $p) {
	  	  if (!$this->valida_promo($p)) continue;
		  
	  	  $_p[$id_promo] = $p;
	    }
	  }
    }

    if (isset($this->_pg[$tipo])) {
      if (is_array($this->_pg[$tipo])) {
        foreach ($this->_pg[$tipo] as $id_promo => $p) {
          if (!$this->valida_promo($p)) continue;
	  
          $_p[$id_promo] = $p;
        }
      }
    }


    return $_p;
  }

  public function sup_promo($id_promo, $tipo) {
    //* PRECOND:: $tipo IN (d, r, t)

    unset($this->_pp[$tipo][$id_promo]);
    unset($this->_pg[$tipo][$id_promo]);
  }

  public function valida_promo(Promo_obd $p) {
    if ($p == null) return false;

    if (($codigo = $p->atr("codigo")->valor) == null) return true; //* promoción sen codigo


    return isset($this->_pcodigo[$codigo]); //* se temos un codigo de promoción, xa está validado.
  }

  public function html():string {
    $html = "";

    $html .= self::html_promos($this->_pp['d'], "Promoción xProducto Directa (PPD)");
    $html .= self::html_promos($this->_pp['r'], "Promoción xProducto Regalo  (PPR)");
    $html .= self::html_promos($this->_pg['d'], "Promoción Global    Directa (PGD)");
    $html .= self::html_promos($this->_pg['r'], "Promoción Global    Regalo  (PGR)");


    return $html;
  }

  public static function html_promos($_p, $t) {
    $html = "*** {$t}:</br>";

    if (count($_p) == 0) return null;

    foreach($_p as $k => $p)
      $html .= "promo_({$k})<pre>" . print_r($p->a_resumo(), true) . "</pre>";


    return $html;
  }

/*
  public function html_codigos_validados() {
    if (!is_array($this->_pcodigo)) return null;

    $html = "";
    foreach ($this->_pcodigo as $codigo => $_promo) {
      if ($_promo['regalo'])
        $d = "Escoje tu regalo";
      else
        $d = "Descuento aplicado a los productos promocionados";

//~ print_r($_promo);

      $html .= "<div class='lista_carro_codigo_0000'>CÓDIGO VALIDADO: <b>{$codigo}</b>. Promoción: {$_promo['nome']}.<br /><b>{$d}</b>.</div>";
    }

    return $html;
  }
*/
}
