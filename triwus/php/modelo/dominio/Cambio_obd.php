<?php

/** Definicion abstracta para diferentes tipos de controis para cambio seguros.
 *
 */

abstract class Cambio_obd extends Obxeto_bd {
  public $l_id = 7; //* lonxitude do código 'id_cambio'

  protected $k  = null;
  protected $k2 = null;

  public function __construct($htl, $id_cambio = null) {
    parent::__construct();

    $this->atr("htl")->valor = $htl;

    if ($id_cambio == null) return null;

    $this->select(new FS_cbd(), "cambio.id_cambio = '{$id_cambio}'");
  }

  abstract public function usuario_obd();
  abstract public function operacion_click(FS_cbd $cbd);

  public static function inicia($id_cambio, FS_cbd $cbd = null) {
    $sql = "select email, tipo from v_cambio_pdte_email where id_cambio = '{$id_cambio}'";

    if ($cbd == null) $cbd = new FS_cbd();

    $r = $cbd->consulta($sql);

    if (!$a = $r->next()) return null;

    return self::factory($a['tipo'], $id_cambio);
  }

  public function select(FS_cbd $cbd, $where = null, $orderby = null) {
    if ($where != null) $where .= " and ";
    $where .= "date_add(creado, interval htl hour) > now()";

    $r = $cbd->consulta($this->sql_select($cbd, $where, $orderby));

    if (!$a = $r->next()) return;

    $this->post($a);
  }

  public function insert(FS_cbd $cbd) {
    if ($this->atr("id_cambio")->valor != null) return $cbd->executa($this->sql_update($cbd));


    if (!$this->calcula_id($cbd)) return false;

    if (!$cbd->executa($this->sql_insert($cbd))) return false;

    $this->atr("creado")->valor = date("YmdHis");

    return $cbd->executa($this->sql_insert($cbd, "cambio"));
  }

  public function delete(FS_cbd $cbd) {
    if (!$cbd->executa($this->sql_delete($cbd))) return false;

    return $cbd->executa($this->sql_delete($cbd, null, "cambio"));
  }

  public function click(FS_cbd $cbd) {
    if (!$this->operacion_click($cbd)) return false;

    $this->atr("click")->valor = date("YmdHis");

    return $cbd->executa($this->sql_update($cbd, null, "cambio"));
  }

  public function valida_click() {
        if ($this->atr("id_cambio")->valor == null) return "(id_cambio == null)";
    elseif ($this->atr("click")->valor     != null) return "(click != null)";

    return null;
  }

  public function pdte() {
    if ($this->atr("id_cambio")->valor == null) return false;

    return $this->atr("click")->valor == null;
  }

  final public function post_k($k) {
    $this->k  = $k;
    $this->k2 = FGS_usuario::calcula_k2($k);
  }

  final public static function factory($tipo, $id_cambio = null) {
    switch ($tipo) {
      case "altaweb":      return new Cambio_altaweb_obd  ($id_cambio);
      case "invitaweb":    return new Cambio_invitaweb_obd($id_cambio);
      case "email":        return new Cambio_email_obd    ($id_cambio);
      case "emailweb":     return new Cambio_emailweb_obd ($id_cambio);
      case "k":            return new Cambio_k_obd        ($id_cambio);
      case "kweb":         return new Cambio_kweb_obd     ($id_cambio);
      case "ns":           return new Cambio_novosite_obd ($id_cambio);
      case "retomar-pago": return new Cambio_pago_obd     ($id_cambio);
    }

    die("Cambio_obd.factory(), tipo desconhecido");
  }

  final public static function __codigo($l_id) {
    return substr(md5(uniqid(rand(), true)), 1, $l_id);
  }

  private function calcula_id(FS_cbd $cbd) {
    if ($this->atr("id_cambio")->valor != null) return true;

    $this->atr("id_cambio")->valor = self::__codigo($this->l_id);

    return true;
  }
}

//--------------------------------------------------------

class Cambio_email_obd extends Cambio_obd {
  public function __construct($id_cambio = null) {
    parent::__construct(24, $id_cambio);
  }

  public function mapa_bd() {
    return new Cambio_email_mbd();
  }

  public function usuario_obd() {
    return FGS_usuario::inicia_contacto(new FS_cbd(), $this->atr("id_contacto")->valor);
  }

  public function operacion_click(FS_cbd $cbd) {
    $c = Contacto_obd::inicia($cbd, $this->atr("id_contacto")->valor);

    $c->atr("email")->valor = $this->atr("email")->valor;

    if (!$c->update($cbd)) return false;


    $u = FGS_usuario::inicia_contacto($cbd, $this->atr("id_contacto")->valor);

    $u->atr("login")->valor = $this->atr("email")->valor;

    return $u->update($cbd);
  }
}

//--------------------------------------------------------

final class Cambio_emailweb_obd extends Cambio_email_obd {
  public function __construct($id_cambio = null) {
    parent::__construct($id_cambio);
  }

  public function mapa_bd() {
    return new Cambio_emailweb_mbd();
  }

  public function usuario_obd() {
    $u = new UsuarioWeb_obd();

    $u->select(new FS_cbd(), "contacto.id_contacto = " . $this->atr("id_contacto")->valor);

    return $u;
  }
}

//--------------------------------------------------------

final class Cambio_factor2_obd extends Cambio_obd {
  public function __construct($id_cambio = null) {
    parent::__construct(1, $id_cambio);
    
    if ($id_cambio != null) return;
    
    $this->atr("kf2")->valor = Cambio_obd::__codigo(6);
  }

  public function mapa_bd() {
    return new Cambio_factor2_mbd();
  }

  public function usuario_obd() {
    return null;
  }

  public function operacion_click(FS_cbd $cbd) {
    return true;
  }
}

//--------------------------------------------------------

class Cambio_k_obd extends Cambio_obd {
  public function __construct($id_cambio = null) {
    parent::__construct(24, $id_cambio);
  }

  public function mapa_bd() {
    return new Cambio_k_mbd();
  }

  public function usuario_obd() {
    return FGS_usuario::inicia(new FS_cbd(), $this->atr("id_usuario")->valor);
  }

  public function operacion_click(FS_cbd $cbd) {
    $u = FGS_usuario::inicia($cbd, $this->atr("id_usuario")->valor);

    //~ $u->atr("contrasinal")->cifrar(false);
    //~ $u->atr("k2"         )->cifrar(false);
    //~ $u->atr("k3"         )->cifrar(false);
    //~ $u->atr("k4"         )->cifrar(false);

    $u->atr("k_renovar"  )->valor = null;
    $u->atr("contrasinal")->valor = $this->atr("k" )->valor;
    $u->atr("k2"         )->valor = $this->atr("k2")->valor;
    $u->atr("k3"         )->valor = $this->atr("k3")->valor;
    $u->atr("k4"         )->valor = $this->atr("k4")->valor;

    return $u->update($cbd);
  }
}

//--------------------------------------------------------

class Cambio_kweb_obd extends Cambio_k_obd {
  public function __construct($id_cambio = null) {
    parent::__construct($id_cambio);
  }

  public function mapa_bd() {
    return new Cambio_kweb_mbd();
  }

  public function usuario_obd() {
    return UsuarioWeb_obd::inicia(new FS_cbd(), $this->atr("id_usuario")->valor);
  }

  public function operacion_click(FS_cbd $cbd) {
    $u = UsuarioWeb_obd::inicia($cbd, $this->atr("id_usuario")->valor);

    $u->atr("contrasinal")->cifrar(true);
    $u->atr("k2"         )->cifrar(true);
    $u->atr("k3"         )->cifrar(true);
    $u->atr("k4"         )->cifrar(true);

    $u->atr("k_renovar"  )->valor = null;
    $u->atr("contrasinal")->valor = $this->k;
    $u->atr("k2"         )->valor = $this->k2;
    $u->atr("k3"         )->valor = $this->k;
    $u->atr("k4"         )->valor = $this->k2;

    return $u->update($cbd);
  }
}

//--------------------------------------------------------

class Cambio_altaweb_obd extends Cambio_obd {
  protected $contacto = null;
  protected $usuario  = null;

  public function __construct($id_cambio = null, $htl = 48) {
    parent::__construct($htl, $id_cambio);
  }

  public function mapa_bd() {
    return new Cambio_altaweb_mbd();
  }

  public function contacto_obd() {
    return $this->contacto;
  }

  public function usuario_obd() {
    return $this->usuario;
  }

  public function select_email(FS_cbd $cbd, $email) {
    $this->atr("email")->valor = $email;

    $this->select($cbd, $this->atr("email")->sql_where($cbd));
  }

  public function operacion_click(FS_cbd $cbd) {
    if (!$this->insert_contacto($cbd)) return false;
    if (!$this->insert_usuario ($cbd)) return false;

    $this->usuario->atr("email")->valor = $this->contacto->atr("email")->valor;

    return Pedido_obd::pon_cliente($cbd, $this->usuario);
  }

  protected function insert_contacto(FS_cbd $cbd) {
    $this->contacto = new Contacto_obd();

    $this->contacto->atr("email"       )->valor = $this->atr("email"       )->valor;
    //~ $this->contacto->atr("cnif_d"      )->valor = $this->atr("cnif_d"      )->valor;
    $this->contacto->atr("nome"        )->valor = $this->atr("nome"        )->valor;
    //~ $this->contacto->atr("razon_social")->valor = $this->atr("razon_social")->valor;
    //~ $this->contacto->atr("mobil"       )->valor = $this->atr("mobil"       )->valor;


    return $this->contacto->insert($cbd);
  }

  protected function insert_usuario(FS_cbd $cbd) {
    //~ $ru_diferido = $this->atr("baixa")->valor;

    $this->usuario = new UsuarioWeb_obd();

    $this->usuario->atr("id_contacto")->valor = $this->contacto->atr("id_contacto")->valor;

    $this->usuario->atr("id_site"    )->valor = $this->atr("id_site"    )->valor;
    $this->usuario->atr("u_baixa"    )->valor = null;
    $this->usuario->atr("permisos"   )->valor = $this->atr("permisos"   )->valor;
    $this->usuario->atr("login"      )->valor = $this->atr("email"      )->valor;


    if ($this->atr("permisos")->valor == null) $this->atr("permisos")->valor = "pub";

    $this->usuario->atr("permisos")->valor = $this->atr("permisos")->valor;


    if ($this->k == null) $this->post_k( substr(md5(uniqid(rand())), 1, 9) );

    $this->usuario->atr("k_renovar"  )->valor = null;
    $this->usuario->atr("contrasinal")->valor = $this->k;
    $this->usuario->atr("k2"         )->valor = $this->k2;
    $this->usuario->atr("k3"         )->valor = $this->k;
    $this->usuario->atr("k4"         )->valor = $this->k2;


    if (!$this->usuario->insert($cbd)) return false;


    return true;
  }
}

//--------------------------------------------------------

class Cambio_invitaweb_obd extends Cambio_altaweb_obd {

  public function __construct($id_cambio = null, $htl = 168, $l_id = 16) {
    parent::__construct($id_cambio, $htl);

    $this->l_id = $l_id;
  }

  public function mapa_bd() {
    return new Cambio_invitaweb_mbd();
  }

  public static function valida_email($cbd, $email, $id_site) {
    $sql = "select a.id_cambio from v_cambio_pdte_email a inner join cambio_invitaweb b on (a.id_cambio = b.id_cambio) where b.id_site = {$id_site} and b.email = '{$email}'";

    $r = $cbd->consulta($sql);

    $a = $r->next();
    
    if (!is_array( $a )          ) return true;
    if (!isset( $a['id_cambio'] )) return true;

    return $a['id_cambio'] == null;
  }

  //~ public static function valida_permisos($p) {
    //~ return FGS_usuario::valida_permisos($p);
  //~ }

  public function operacion_click(FS_cbd $cbd) {
    $this->usuario = new UsuarioWeb_obd();

    //* busca usuario por email, evita (emails,id_site) duplicado
    $this->usuario->select_email($cbd, $this->atr("email")->valor, $this->atr("id_site")->valor);


    if ( !$this->usuario->validado() ) return parent::operacion_click($cbd);


    if ( $this->atr("permisos")->valor != null )
      $this->usuario->atr("permisos")->valor = $this->atr("permisos")->valor;


    return $this->usuario->update_k($cbd, $this->k);
  }
}

//--------------------------------------------------------

final class Cambio_novosite_obd extends Cambio_obd {
  const pre_idioma  = "es"; //* español internacional
  const pre_config  = 215;  //* triwus-piloto
  
  private $contacto  = null;
  private $site      = null;
  private $usuario   = null;
  private $servizo   = null;

  public function __construct($id_cambio = null) {
    parent::__construct(48, $id_cambio);
  }

  public function mapa_bd() {
    return new Cambio_novosite_mbd();
  }

  public function usuario_obd() {
    return $this->usuario;
  }

  public function servizo_obd() {
    return $this->servizo;
  }

  public function select_email(FS_cbd $cbd, $email) {
    $this->atr("email")->valor = $email;

    $this->select($cbd, $this->atr("email")->sql_where($cbd));
  }

  public function operacion_click(FS_cbd $cbd) {
    if ($this->atr("id_config")->valor == null) $this->atr("id_config")->valor = self::pre_config;
    if ($this->atr("id_idioma")->valor == null) $this->atr("id_idioma")->valor = self::pre_idioma;

//*

    if (!$this->insert_site     ($cbd)) return false;
    if (!$this->insert_contacto ($cbd)) return false;
    if (!$this->insert_usuario  ($cbd)) return false;
    if (!$this->insert_servizo  ($cbd)) return false;
    if (!$this->insert_almacen_1($cbd)) return false; //* Manter a orde das instrucción insert.

    $this->usuario->atr("email")->valor = $this->contacto->atr("email")->valor;

    return true;
  }

  private function insert_site(FS_cbd $cbd) {
    $this->site = new Site_obd();

    $this->site->atr("nome"     )->valor = $this->atr("nome_site")->valor;
    $this->site->atr("dominio"  )->valor = $this->atr("dominio"  )->valor;
    $this->site->atr("id_config")->valor = $this->atr("id_config")->valor;

    if (!$this->site->insert   ($cbd)) return false;
    if (!$this->insert_metatags($cbd)) return false;
    if (!$this->insert_idioma  ($cbd)) return false;
    if (!$this->insert_ms      ($cbd)) return false;

    return $this->insert_oms($cbd);
  }

  private function insert_contacto(FS_cbd $cbd) {
    $this->contacto = new Contacto_obd();

    $this->contacto->atr("nome")->valor = $this->atr("nome_usuario")->valor;
    $this->contacto->atr("email")->valor = $this->atr("email")->valor;
    //~ $this->contacto->atr("enderezo")->valor = $this->atr("enderezo")->valor;
    $this->contacto->atr("fax")->valor = $this->atr("fax")->valor;
    $this->contacto->atr("tlfn")->valor = $this->atr("tlfn")->valor;

    return $this->contacto->insert($cbd);
  }

  private function insert_almacen_1(FS_cbd $cbd) {
    $_sb_venta = array(3, 6, 9);

    if ( !in_array($this->servizo->atr("id_sb")->valor, $_sb_venta) ) return true;


    $c_al = new Contacto_obd();

    $c_al->clonar( $this->contacto );


    $al = new Almacen_obd( $this->site->atr("id_site")->valor );

    return $al->update($cbd, $c_al);
  }

  private function insert_idioma(FS_cbd $cbd) {
    $ml = new MLsite_obd();

    $ml->atr("id_site"  )->valor = $this->site->atr("id_site")->valor;
    $ml->atr("id_idioma")->valor = $this->atr("id_idioma")->valor;
    $ml->atr("pos"      )->valor = 1;
    $ml->atr("activo"   )->valor = 1;

    return $ml->insert($cbd);
  }

  private function insert_usuario(FS_cbd $cbd) {
    $this->usuario = new FGS_usuario();

    $this->usuario->atr("id_site"    )->valor = $this->site->atr("id_site")->valor;
    $this->usuario->atr("id_contacto")->valor = $this->contacto->atr("id_contacto")->valor;

    $this->usuario->atr("login"      )->valor = $this->contacto->atr("email")->valor;

    $this->usuario->atr("k3"         )->valor = $this->atr("k" )->valor;
    $this->usuario->atr("k4"         )->valor = $this->atr("k2")->valor;

    $this->usuario->atr("permisos"   )->valor = "admin";

    $this->usuario->atr("k3"         )->cifrar(false);
    $this->usuario->atr("k4"         )->cifrar(false);

    return $this->usuario->insert($cbd);
  }

  private function insert_servizo(FS_cbd $cbd) {
    $this->servizo = new Servizo_obd();

    $this->servizo->atr("id_usuario")->valor = $this->usuario->atr("id_usuario")->valor;
    $this->servizo->atr("id_sb")->valor      = $this->atr("id_sb")->valor;

    return $this->servizo->insert($cbd);
  }

  private function insert_metatags(FS_cbd $cbd) {
    $smt = new Site_metatags_obd();

    $smt->atr("id_site")->valor    = $this->site->atr("id_site")->valor;
    $smt->atr("titulo_txt")->valor = $this->atr("titulo_txt")->valor;
    //~ $smt->atr("keywords")->valor   = $this->atr("keywords")->valor;
    $smt->atr("author")->valor     = $this->atr("author")->valor;

    return $smt->insert($cbd);
  }

  private function insert_ms(FS_cbd $cbd) {
    $ms = new Menu_site_obd();

    $ms->atr("id_ms"  )->valor   = $this->site->atr("id_site")->valor;
    $ms->atr("id_site")->valor   = $this->site->atr("id_site")->valor;

    return $ms->insert($cbd);
  }

  private function insert_oms(FS_cbd $cbd, $id_idioma = "es") {
    $id_idioma = $this->atr("id_idioma")->valor;


    if ( !$this->insert_oms_2($cbd, new Paxina_dl_obd(), "inicio"  , $id_idioma) ) return false;
    if ( !$this->insert_oms_2($cbd, new Paxina_dl_obd(), "contacto", $id_idioma) ) return false;

    //~ if ( !$this->insert_oms_2($cbd, new Paxina_legal_obd(), "Legal", $id_idioma) ) return false;

    if ( !$this->insert_oms_2($cbd, new Paxina_reservada_obd("login"    ), "login"    , $id_idioma) ) return false;
    if ( !$this->insert_oms_2($cbd, new Paxina_reservada_obd("perfil"   ), "perfil"   , $id_idioma) ) return false;
    if ( !$this->insert_oms_2($cbd, new Paxina_reservada_obd("buscador" ), "buscador" , $id_idioma) ) return false;
    if ( !$this->insert_oms_2($cbd, new Paxina_reservada_obd("confirmar"), "confirmar", $id_idioma) ) return false;

    if ( ($this->atr("id_sb")->valor % 3) == 0 ) return true;

    if ( !$this->insert_oms_2($cbd, new Paxina_reservada_obd("pago")    , "pago"    , $id_idioma) ) return false;
    if ( !$this->insert_oms_2($cbd, new Paxina_reservada_obd("producto"), "producto", $id_idioma) ) return false;


    return true;
  }

  private function insert_oms_2(FS_cbd $cbd, Paxina_obd $pax_proto, $nome, $id_idioma) {
    $id_idioma = $this->atr("id_idioma")->valor;


    $oms = new Opcion_ms_obd();

    $oms->atr("id_ms" )->valor = $this->site->atr("id_site")->valor;


    $pax_proto->atr("nome"     )->valor = $nome;
    $pax_proto->atr("id_idioma")->valor = $id_idioma;


    return $oms->insert($cbd, $pax_proto);
  }
}

//--------------------------------------------------------

final class Cambio_pago_obd extends Cambio_obd {
  private $u;

  public function __construct($id_cambio = null) {
    parent::__construct(168, $id_cambio);

    $this->l_id = 11;
  }

  public function mapa_bd() {
    return new Cambio_pago_mbd();
  }

  public function usuario_obd() {
    return $this->u;
  }

  public function operacion_click(FS_cbd $cbd) {
    $p = $this->pedido_obd();

    $this->u->cpago()->destino( $p->destino($cbd) ); //* engade os datos de destino do pedido, ao formulario de pago.

//~ echo "<pre>" . print_r($this->u->a_resumo(), true) . "</pre>";

    $ccarro = $this->u->cpago()->carro();

    if ($ccarro->post_pedido($p) == -1) return false;


    return true;
  }

  public function post_u(FGS_usuario $u) {
    $this->u = $u;
  }

  private function pedido_obd(FS_cbd $cbd = null) {
    if ($cbd == null) $cbd = new FS_cbd();

    $s = $this->atr("id_site")->valor;
    $a = $this->atr("ano"    )->valor;
    $n = $this->atr("numero" )->valor;

    return Pedido_obd::inicia($cbd, $s, $a, $n);
  }
}


//********************************************************


abstract class Cambio_mbd extends Mapa_bd {
  public function __construct(Taboa_dbd $t0) {
    parent::__construct();

    $this->pon_taboa($t0);

    $t = new Taboa_dbd("cambio");

    $t->pon_campo("id_cambio", new Varchar(), true);
    $t->pon_campo("creado", new Data("YmdHis"));
    $t->pon_campo("htl", new Numero());
    $t->pon_campo("click", new Data("YmdHis"));

    $this->relacion_fk($t, array("id_cambio"), array("id_cambio"));
  }
}

//--------------------------------------------------------

final class Cambio_email_mbd extends Cambio_mbd {
  public function __construct() {
    parent::__construct(self::__t0());
  }

  private static function __t0() {
    $t = new Taboa_dbd("cambio_email");

    $t->pon_campo("id_cambio", new Varchar(), true);
    $t->pon_campo("id_contacto", new Numero());
    $t->pon_campo("id_site", new Numero());
    $t->pon_campo("email");

    return $t;
  }
}

//--------------------------------------------------------

final class Cambio_emailweb_mbd extends Cambio_mbd {
  public function __construct() {
    parent::__construct(self::__t0());
  }

  private static function __t0() {
    $t = new Taboa_dbd("cambio_emailweb");

    $t->pon_campo("id_cambio", new Varchar(), true);
    $t->pon_campo("id_contacto", new Numero());
    $t->pon_campo("id_site", new Numero());
    $t->pon_campo("email");

    return $t;
  }
}

//--------------------------------------------------------

final class Cambio_factor2_mbd extends Cambio_mbd {
  public function __construct() {
    parent::__construct(self::__t0());
  }

  private static function __t0() {
    $t = new Taboa_dbd("cambio_factor2");

    $t->pon_campo("id_cambio", new Varchar(), true);
    $t->pon_campo("kf2"      , new Varchar()      );

    return $t;
  }
}

//--------------------------------------------------------

final class Cambio_k_mbd extends Cambio_mbd {
  public function __construct() {
    parent::__construct(self::__t0());
  }

  private static function __t0() {
    $t = new Taboa_dbd("cambio_k");

    $t->pon_campo("id_cambio", new Varchar(), true);
    $t->pon_campo("id_site", new Numero());
    $t->pon_campo("id_usuario", new Numero());
    $t->pon_campo("k" , new Psswd ());
    $t->pon_campo("k2", new Psswd ());
    $t->pon_campo("k3", new Psswd2());
    $t->pon_campo("k4", new Psswd2());

    return $t;
  }
}

//--------------------------------------------------------

final class Cambio_kweb_mbd extends Cambio_mbd {
  public function __construct() {
    parent::__construct(self::__t0());
  }

  private static function __t0() {
    $t = new Taboa_dbd("cambio_kweb");

    $t->pon_campo("id_cambio", new Varchar(), true);
    $t->pon_campo("id_usuario", new Numero());
    $t->pon_campo("id_site", new Numero());

    return $t;
  }
}

//--------------------------------------------------------

final class Cambio_novosite_mbd extends Cambio_mbd {
  public function __construct() {
    parent::__construct(self::__t0());
  }

  private static function __t0() {
    $t = new Taboa_dbd("site_novo");

    $t->pon_campo("id_cambio"   , new Varchar(), true);
    $t->pon_campo("k"           , new Psswd2()       );
    $t->pon_campo("k2"          , new Psswd2()       );
    $t->pon_campo("id_sb"       , new Numero()       );
    $t->pon_campo("id_config"   , new Numero()       );
    $t->pon_campo("id_idioma"                        );
    $t->pon_campo("dominio"                          );
    $t->pon_campo("nome_site"                        );
    $t->pon_campo("email"                            );
    $t->pon_campo("nome_usuario"                     );
    $t->pon_campo("codtipovia"                       );
    $t->pon_campo("via"                              );
    $t->pon_campo("num"                              );
    $t->pon_campo("resto"                            );
    $t->pon_campo("author"                           );
    $t->pon_campo("tlfn"                             );
    $t->pon_campo("fax"                              );
    $t->pon_campo("titulo_txt"                       );
    $t->pon_campo("keywords"                         );

    return $t;
  }
}

//--------------------------------------------------------

final class Cambio_altaweb_mbd extends Cambio_mbd {
  public function __construct() {
    parent::__construct(self::__t0());
  }

  private static function __t0() {
    $t = new Taboa_dbd("cambio_altaweb");

    $t->pon_campo("id_cambio", new Varchar(), true);
    $t->pon_campo("id_site", new Numero());
    $t->pon_campo("email");
    $t->pon_campo("cnif_d");
    $t->pon_campo("nome");
    $t->pon_campo("razon_social");
    $t->pon_campo("mobil");
    $t->pon_campo("permisos");
    $t->pon_campo("baixa");


    return $t;
  }
}

//--------------------------------------------------------

final class Cambio_invitaweb_mbd extends Cambio_mbd {
  public function __construct() {
    parent::__construct(self::__t0());
  }

  private static function __t0() {
    $t = new Taboa_dbd("cambio_invitaweb");

    $t->pon_campo("id_cambio", new Varchar(), true);
    $t->pon_campo("id_site", new Numero());
    $t->pon_campo("email");
    $t->pon_campo("nome");
    $t->pon_campo("permisos");


    return $t;
  }
}

//--------------------------------------------------------

final class Cambio_pago_mbd extends Cambio_mbd {
  public function __construct() {
    parent::__construct(self::__t0());
  }

  private static function __t0() {
    $t = new Taboa_dbd("cambio_pago");

    $t->pon_campo("id_cambio", new Varchar(), true);
    $t->pon_campo("id_site"  , new Numero());
    $t->pon_campo("ano"      , new Numero());
    $t->pon_campo("numero"   , new Numero());


    return $t;
  }
}
