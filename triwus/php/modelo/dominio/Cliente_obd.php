<?php

class Cliente_obd extends    Obxeto_bd {
  public function __construct() {
    parent::__construct();
  }

  public function mapa_bd() {
    return new Cliente_mbd();
  }

  public function baleiro() {
    return ($this->atr("id_usuario")->valor == null);
  }

  public static function inicia(Xesta_cbd $cbd, $id_usuario) {
    $u = new Cliente_obd();

    $u->select($cbd, "usuario.id_usuario = '{$id_usuario}'");

    return $u;
  }

  public static function inicia_contacto(Xesta_cbd $cbd, $id_contacto) {
    $u = new FGS_usuario();

    $this->atr("id_contacto")->valor = $id_contacto;

    $u->select($cbd, $this->atr("id_contacto")->sql_where($cbd));

    return $u;
  }

  public static function inicia_xsite(Xesta_cbd $cbd, int $id_site) {
    $u = new Cliente_obd();

    $u->select($cbd, "usuario.id_site = '{$id_site}'");

    return $u;
  }

  public function select(Xesta_cbd $cbd, $where = null, $orderby = null) {
    $r = $cbd->consulta($this->sql_select($cbd, $where, $orderby));

    if (!$a = $r->next()) return;

    $this->post($a);
  }

  public function insert(Xesta_cbd $cbd) {
    if ($this->atr("alta"   )->valor == null) $this->atr("alta"   )->valor = date("YmdHis");
    
    if (!$cbd->executa($this->sql_insert($cbd))) return false;

    $this->calcula_id($cbd);

    return true;
  }

  public function update(Xesta_cbd $cbd) {
    if ($this->atr("baixa"  )->valor == null) $this->atr("baixa"  )->valor = "0";

    return $cbd->executa($this->sql_update($cbd));
  }

  public function update_contacto(Xesta_cbd $cbd, Contacto_obd $c) {
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

  public function delete(Xesta_cbd $cbd) {
    //* elimina contactos.
    if (($c = $this->contacto_obd($cbd)) != null) if (!$c->delete($cbd, true)) return false;

    //* elimina usuario.
    return $cbd->executa($this->sql_delete($cbd));
  }

  public function contacto_obd(FS_cbd $cbd = null, $tipo = "p") {
    if (($id_contacto = $this->atr("id_contacto")->valor) == null) return  new Contacto_obd($tipo);


    if ($cbd == null) $cbd = new FS_cbd();

    return Contacto_obd::inicia($cbd, $this->atr("id_contacto")->valor, $tipo);
  }

  public function cuenta_obd(FS_cbd $cbd = null):?Cuenta_obd {
    if ($cbd == null) $cbd = new FS_cbd();
    
    if (($u = $this->atr("id_usuario")->valor) == null) return null;

    return Cuenta_obd::inicia_usuario($cbd, $u);
  }

  private function calcula_id(FS_cbd $cbd) {
    $r = $cbd->consulta("select LAST_INSERT_ID() as id");

    if (!$a = $r->next()) die("Cliente_obd.calcula_id(), (!a = r.next())");

    $this->atr("id_usuario")->valor = $a['id'];
  }
}

//********************************************

final class Cliente_mbd extends Mapa_bd {
  public function __construct() {
    $t = new Taboa_dbd("usuario");

    $t->pon_campo("id_usuario" , new Numero()      , true);
    $t->pon_campo("id_contacto", new Numero()            );
    $t->pon_campo("id_site"    , new Numero()            );
    $t->pon_campo("alta"       , new Data("YmdHis")      );
    $t->pon_campo("baixa"      , new Numero()            );

    $this->pon_taboa($t);

    $t2 = new Taboa_dbd("contacto");

    $t2->pon_campo("cnif_d");
    $t2->pon_campo("nome");
    $t2->pon_campo("razon_social");
    $t2->pon_campo("email");

    $this->relacion_fk($t2, array("id_contacto"), array("id_contacto"), "left");
  }
}

