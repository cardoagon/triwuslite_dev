<?php



final class Cuenta_obd {
  public  FGS_usuario $u;
  public  Site_obd    $s;
  public  bool        $venta;
  
  public function __construct(FGS_usuario $u, FS_cbd $cbd = null) {
    if ($cbd == null) $cbd = new FS_cbd();

    $this->u = $u;
    
    $this->s = $u->site_obd($cbd);
    
    $this->venta = $this->s->venta($cbd);
  }

  public function edita_web() {
    return $this->u->atr("id_almacen")->valor == 1;
  }
  
  public function _subs(FS_cbd $cbd = null):array {
    if (($u = $this->u->atr("id_usuario")->valor) == null) return [];

    if ($cbd == null) $cbd = new FS_cbd();

    $sql = "select a.nome, a.version
              from servizo a
             where (a.id_usuario = {$u}) and (id_sb = '121000')";

    $r = $cbd->consulta($sql);

    $_subs = [];
    while ($_r = $r->next()) $_subs[] = $_r;

    return $_subs;
  }
}


/*

class Cuenta_obd extends Obxeto_bd  {
  public function __construct() {
    parent::__construct();
  }

  public function mapa_bd() {
    return new Cuenta_mbd();
  }

  public function edita_web() {
    switch ($this->atr("id_sb")->valor) {
      case 1:
      case 2:
      case 3:
      case 4:
      case 5:
      case 6:
      case 7:
      case 8:
      case 9:
        return true;
    }

    return false;
  }

  public static function inicia(FS_cbd $cbd, int $id_servizo) {
    $u = new Cuenta_obd();

    $u->select($cbd, "v_servizo_plan.id_servizo = {$id_servizo}");

    return $u;
  }

  public static function inicia_usuario(FS_cbd $cbd, int $id_usuario) {
    $u = new Cuenta_obd();

    $u->select($cbd, "v_servizo_plan.id_usuario = {$id_usuario}");

    return $u;
  }

  public function select(FS_cbd $cbd, $where = null, $orderby = null) {
    $r = $cbd->consulta($this->sql_select($cbd, $where, $orderby));

    if (!$a = $r->next()) return;

    $this->post($a);
  }
  

  public function _subs(FS_cbd $cbd = null):array {
    if (($u = $this->atr("id_usuario")->valor) == null) return [];

    if ($cbd == null) $cbd = new FS_cbd();

    $sql = "select a.nome, a.version
              from servizo a
             where (a.id_usuario = {$u}) and (id_sb = '121000')";

    $r = $cbd->consulta($sql);

    $_subs = [];
    while ($_r = $r->next()) $_subs[] = $_r;

    return $_subs;
  }
}

//-----------------------------------------------

final class Cuenta_mbd extends Mapa_bd {
  public function __construct() {
    $t = new Taboa_dbd("v_servizo_plan");

    $t->pon_campo("id_site"      , new Numero());
    $t->pon_campo("id_servizo"   , new Numero());
    $t->pon_campo("id_usuario"   , new Numero());
    $t->pon_campo("id_sb"        , new Numero());
    $t->pon_campo("nome");
    $t->pon_campo("max_GB"       , new Numero());
    $t->pon_campo("multilinguaxe", new Numero());
    $t->pon_campo("venta"        , new Numero());
    $t->pon_campo("email"        , new Numero());
    $t->pon_campo("pvp_base"     , new Numero());
    $t->pon_campo("pvp_frac"     , new Numero());
    $t->pon_campo("caduca"       , new Numero());
    $t->pon_campo("custodia"     , new Numero()); //* tiempo de custodia m&iacute;nimo segun LOPD

    $this->pon_taboa($t);


    $t2 = new Taboa_dbd("site_plus");

    $t2->pon_campo("email", new Numero(), false, "plus_email");
    $t2->pon_campo("gigas", new Numero(), false, "plus_gigas");


    $this->relacion_fk($t2, array("id_site"), array("id_site"), "left");


    $t2 = new Taboa_dbd("site_plesk");

    $t2->pon_campo("dominio"                 );


    $this->relacion_fk($t2, array("id_site"), array("id_site"), "left");
  }
}
*/
