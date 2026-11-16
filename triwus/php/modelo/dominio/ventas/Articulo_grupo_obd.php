<?php

final class Articulo_grupo_obd extends    Obxeto_bd
                               implements IElmt_ventas_rel {
                                 
  public function __construct($id_site, $id_grupo = null) {
    parent::__construct();

    $this->atr("id_site" )->valor = $id_site;
    $this->atr("id_grupo")->valor = $id_grupo;
  }

  public function mapa_bd() {
    return new Articulo_grupo_mbd();
  }

  public static function inicia(FS_cbd $cbd, $id_site, $id_grupo = null) {
    $o = new Articulo_grupo_obd($id_site, $id_grupo);

    if ($id_grupo == null) return $o;

    $o->select($cbd, $o->atr("id_grupo")->sql_where($cbd));

    return $o;
  }

  public static function inicia_articulo(FS_cbd $cbd, $id_site, $id_articulo) {
    $sql = "select id_grupo from articulo_grupo_2 where id_site = {$id_site} and id_articulo = {$id_articulo}";

    $r = $cbd->consulta($sql);

    if (!$a = $r->next()) return null;


    return self::inicia($cbd, $id_site, $a['id_grupo']);
  }

  public function select(FS_cbd $cbd, $where = null, $orderby = null) {
    $r = $cbd->consulta($this->sql_select($cbd, $where, $orderby));

    if (!$a = $r->next()) return;

    $this->post($a);
  }

  public function duplicar(FS_cbd $cbd, $id_articulo_novo) {
    return true;
  }

  public function insert(FS_cbd $cbd, $id_articulo) {
    if (!$cbd->executa($this->sql_insert($cbd))) return false;

    $this->calcula_id($cbd);

    if ( !$this->update_articulos($cbd, array($id_articulo=>true)) ) return false;


    return true;
  }

  public function update(FS_cbd $cbd, $a_articulos) {
    if (!$this->update_articulos($cbd, $a_articulos)) return false;

    if ($this->__contar($cbd) == 0) return $cbd->executa($this->sql_delete($cbd));

    return $cbd->executa($this->sql_update($cbd));
  }

  public function asociar(FS_cbd $cbd, $id_articulo, $b = true) {
    return $this->update_articulos($cbd, array($id_articulo=>$b));
  }

  public function delete(FS_cbd $cbd) {
    $sql = "delete from articulo_grupo_2 where id_grupo = " . $this->atr("id_grupo")->valor;

    if (!$cbd->executa($sql)) return false;

    return $cbd->executa($this->sql_delete($cbd));
  }

  public function __contar(FS_cbd $cbd = null) {
    if ($this->atr("id_grupo")->valor == null) return 0;

    if ($cbd == null) $cbd = new FS_cbd();

    $sql = "select count(*) as c from articulo_grupo_2 where id_grupo = " . $this->atr("id_grupo")->valor;

    $r = $cbd->consulta($sql);

    if (!$a = $r->next()) return 0;

    return $a['c'];
  }

  public function a_grupos($excluir = false, $id_almacen = 1, FS_cbd $cbd = null, $orderby = "nome asc") {
    if (($id_site = $this->atr("id_site")->valor) == null) return null;

    if ($cbd == null) $cbd = new FS_cbd();

    $wh_relacion = "";
    if ($excluir) if ($this->atr("id_grupo")->valor != null) $wh_relacion = " and id_grupo <> " . $this->atr("id_grupo")->valor;

    $wh_almacen = "";
    if ($id_almacen > 1) $wh_almacen = " and id_almacen = {$id_almacen}";

    $sql = "select id_grupo, nome
              from articulo_grupo
             where id_site = {$id_site}{$wh_almacen}{$wh_relacion} order by {$orderby}";

    $r = $cbd->consulta($sql);

    $a = null;
    while ($a2 = $r->next()) $a[$a2['id_grupo']] = $a2['nome'];

    return $a;
  }

  public function titulo() { //* IMPLEMENTA IElmt_ventas_rel.titulo()
    if (($t = $this->atr("nome_pub")->valor) != null) return $t;

    return $t = $this->atr("nome")->valor;
  }

  public function orderby($orderby = -1) { //* IMPLEMENTA IElmt_ventas_rel.orderby()
  }

  public function selectfrom(FS_cbd $cbd = null):string { //* IMPLEMENTA IElmt_ventas_rel.selectfrom():string
    return "";
  }

  public function _articulo(FS_cbd $cbd = null, $max = -1) { //* IMPLEMENTA IElmt_ventas_rel._articulo()
    if ($this->atr("id_grupo")->valor == null) return null;

    if ($cbd == null) $cbd = new FS_cbd();

    $where = "id_grupo = " . $this->atr("id_grupo")->valor;

    $limit = ""; if ($max > 0) $limit = " limit 0, {$max}";

    $sql = "select id_articulo from articulo_grupo_2 where {$where}{$limit}";


    $r = $cbd->consulta($sql);

    $_a = null; while ($_r= $r->next()) $_a[ $_r['id_articulo'] ] = 1;

    return $_a;
  }

  private function update_articulos(FS_cbd $cbd, $a_articulos) {
    if (count($a_articulos) == 0) return true;

    $id_site  = $this->atr("id_site" )->valor;
    $id_grupo = $this->atr("id_grupo")->valor;

    foreach ($a_articulos as $id_articulo=>$b) {
      if ($b)
        $sql = "insert into articulo_grupo_2 (id_site, id_grupo, id_articulo)
                values ('{$id_site}', '{$id_grupo}', '{$id_articulo}')
                    on duplicate key update id_articulo = id_articulo";
      else
        $sql = "delete from articulo_grupo_2 where id_site = {$id_site} and id_grupo = {$id_grupo} and id_articulo = {$id_articulo}";

      if (!$cbd->executa($sql)) return false;
    }

    return true;
  }

  private function calcula_id(FS_cbd $cbd) {
    $r = $cbd->consulta("select LAST_INSERT_ID() as id");

    if (!$a = $r->next()) die("Articulo_grupo_obd.calcula_id(), (!a = r.next())");

    $this->atr("id_grupo")->valor = $a['id'];
  }
}

//-----------------------------------------------

final class Articulo_grupo_mbd extends Mapa_bd {
  public function __construct() {
    $t = new Taboa_dbd("articulo_grupo");

    $t->pon_campo("id_grupo"  , new Numero(), true);
    $t->pon_campo("id_site"   , new Numero(), true);
    $t->pon_campo("id_almacen", new Numero());
    $t->pon_campo("nome");
    $t->pon_campo("nome_pub");

    $this->pon_taboa($t);
  }
}


