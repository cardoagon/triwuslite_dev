<?php

final class Categoria_obd extends    Obxeto_bd
                          implements IElmt_ventas_rel {

  const nivel_max = 2;

  public function __construct($id_site, $id_categoria = null) {
    parent::__construct();

    $this->atr("id_site"     )->valor = $id_site;
    $this->atr("id_categoria")->valor = $id_categoria;
  }

  public function mapa_bd() {
    return new Categoria_mbd();
  }

  public static function inicia(FS_cbd $cbd, $id_site, $id_categoria = null) {
    $a = new Categoria_obd($id_site, $id_categoria);

    if ($id_categoria == null) return $a;

    $wh_s = $a->atr("id_site"     )->sql_where($cbd);
    $wh_a = $a->atr("id_categoria")->sql_where($cbd);

    $a->select($cbd, "{$wh_s} and {$wh_a}");


    return $a;
  }

  public static function inicia_articulo(FS_cbd $cbd, $id_site, $id_articulo) {
    $sql = "select id_categoria from articulo where id_site = {$id_site} and id_articulo = {$id_articulo}";

    $r = $cbd->consulta($sql);

    if (!$a = $r->next()) return null;


    return self::inicia($cbd, $id_site, $a['id_categoria']);
  }

  public function select(FS_cbd $cbd, $where = null, $orderby = null) {
    $r = $cbd->consulta($this->sql_select($cbd, $where, $orderby));

    if (!$a = $r->next()) return;

    $this->post($a);
  }

  //~ public function update(FS_cbd $cbd, Contacto_obd $c = null) {
  public function update(FS_cbd $cbd) {
    if ($this->atr("id_pai")->valor == 0) $this->atr("id_pai")->valor = "0";

    //*** update
    if ($this->atr("id_categoria")->valor != null) return $cbd->executa($this->sql_update($cbd));


    //*** insert
    if (!$this->calcula_id($cbd)) return false;

    return $cbd->executa($this->sql_insert($cbd));
  }

  public function delete(FS_cbd $cbd) {
    $wh_s = $this->atr("id_site"     )->valor;
    $wh_a = $this->atr("id_categoria")->valor;
    $wh_p = $this->atr("id_pai"      )->valor;

    //* actualiza artículos.
    $sql = "update articulo set id_categoria={$wh_p} where id_site = {$wh_s} and id_categoria = {$wh_a}";

    if (!$cbd->executa($sql)) return false;

    //* actualiza categorias descendentes.
    $sql = "update categoria set id_pai={$wh_p} where id_site = {$wh_s} and id_pai = {$wh_a}";

    if (!$cbd->executa($sql)) return false;


    //* elimina categoría.
    return $cbd->executa($this->sql_delete($cbd));
  }

  public function titulo() { //* IMPLEMENTA IElmt_ventas_rel.titulo()
    return "Otros artículos relacionados";
  }

  public function orderby($orderby = -1) { //* IMPLEMENTA IElmt_ventas_rel.orderby()
    return "rand()";
  }

  public function selectfrom(FS_cbd $cbd = null):string { //* IMPLEMENTA IElmt_ventas_rel.selectfrom():string
    return "";
  }

  public function _articulo(FS_cbd $cbd = null, $max = -1) { //* IMPLEMENTA IElmt_ventas_rel._articulo()
    if (($id_cat = $this->atr("id_categoria")->valor) == 1) return array();


    $wh_u = "";
    $wh_s = "a.id_site = " . $this->atr("id_site")->valor;
    $wh_c = "(c.id_categoria = {$id_cat})";


    if ($cbd == null) $cbd = new FS_cbd();


    $sc = Site_config_obd::inicia($cbd, $this->atr("id_site")->valor);

    if ($sc->atr("ventas_producto_agotados")->valor != 1) $wh_u = " and (a.unidades >= a.pedido_min)";


    //~ if (($id_cat_pai = $this->atr("id_pai")->valor) > 1) $wh_c .= " or (c.id_pai = {$id_cat_pai})";

    $orderby = $this->orderby();

    $limit = ""; if ($max > 0) $limit = " limit 0, {$max}";

    $sql = "
select id_articulo
  from (
    select a.id_articulo
      from articulo a inner join articulo_publicacions h on (a.id_site = h.id_site and a.id_articulo = h.id_articulo)
                      left  join categoria             c on (a.id_site = c.id_site and a.id_categoria = c.id_categoria)
    where ({$wh_s}) and (a.id_grupo_cc is null) and ({$wh_c}){$wh_u}
    union
    select id_articulo
    from (
        select  id_grupo_cc, a.id_articulo, desc_p, pvp, momento
          from articulo a inner join articulo_publicacions h on (a.id_site = h.id_site and a.id_articulo = h.id_articulo)
                          left  join categoria             c on (a.id_site = c.id_site and a.id_categoria = c.id_categoria)
        where ({$wh_s}) and ({$wh_c}){$wh_u} and (not id_grupo_cc is null)
        order by desc_p desc, pvp asc
         ) a1
    group by a1.id_grupo_cc
) a2
order by {$orderby} {$limit}";

/*
    $sql = "
select id_articulo
  from (
    select id_articulo from v_articulo_ventas2 where ({$wh_s}) and (id_grupo_cc is null) and ({$wh_c}){$wh_u}
    union
    select id_articulo
    from (
      select id_grupo_cc, id_articulo, desc_p, pvp
        from v_articulo_ventas2
        where ({$wh_s}) and ({$wh_c}){$wh_u} and (not id_grupo_cc is null)
        order by desc_p desc, pvp asc
         ) a1
    group by a1.id_grupo_cc
) a2
order by {$orderby} {$limit}";
*/

    $r = $cbd->consulta($sql);

    $_a = null; while ($_r= $r->next()) $_a[ $_r['id_articulo'] ] = 1;

    return $_a;
  }


  public static function _categoria(int $id_site, ?int $nivel = null, ?int $id_pai = null, ?string $predet = "···", FS_cbd $cbd = null):array {
    if ($cbd == null) $cbd = new FS_cbd();

    if (($nivel  !== null) && ($nivel  < 2)) $nivel  = "0,1";
    if (($id_pai !== null) && ($id_pai < 2)) $id_pai = "0,1";
    
    $wh_s = "id_site = {$id_site}";
    $wh_n = ($nivel  === null)?"1":"nivel  IN ({$nivel} )";
    $wh_p = ($id_pai === null)?"1":"id_pai IN ({$id_pai})";

    $sql = "select id_categoria, nome from categoria where ({$wh_s}) and ({$wh_n}) and ({$wh_p}) order by nome";

    $r = $cbd->consulta($sql);

    $_c = ($predet == null) ? [] : ["**" => $predet];

    while ($_a = $r->next())  {
      $k = str_pad($_a["id_categoria"], 4, "0", STR_PAD_LEFT);
      
      $_c[ $_a['id_categoria'] ] = "{$k} - {$_a['nome']}";
    }


    return $_c;
  }

  public static function _anteriores(int $id_site, int $id_categoria, ?string $predet = "···", FS_cbd $cbd = null):array {
    if ($cbd == null) $cbd = new FS_cbd();
    
    //* calcula o pai da caegoría anterior.
    $sql = "select id_avo 
              from v_categoria a
             where (a.id_site = {$id_site}) and (a.id_categoria = {$id_categoria})";

    $r = $cbd->consulta($sql);
    
    if ( !$_r = $r->next() ) return [];

    
    //* devolve caegoríaa anteriores.
    return self::_categoria($id_site, null, $_r["id_avo"], $predet, $cbd);
  }

  public static function _migas(int $id_site, ?int $nivel = null, $predet = null, FS_cbd $cbd = null):array {
    if ($cbd == null) $cbd = new FS_cbd();

    if (($nivel != null) && ($nivel < 2)) $nivel = "0,1";

    $wh_s = "id_site = {$id_site}";
    $wh_n = ($nivel  == null)?"1":"nivel IN ({$nivel})";

    $sql = "select id_categoria, miga from v_categoria_migas where ({$wh_s}) and ({$wh_n})";

    $r = $cbd->consulta($sql);

    $_c = ($predet == null) ? [] : ["**" => $predet];

    while ($_a = $r->next()) {
      $k = str_pad($_a["id_categoria"], 4, "0", STR_PAD_LEFT);
      
      $_c[ $_a['id_categoria'] ] = "{$k} - {$_a['miga']}";
    }


    return $_c;
  }

  public static function _miga(int $id_site, int $id_categoria, FS_cbd $cbd = null):array {
    if ($cbd == null) $cbd = new FS_cbd();

    $_m = array();
    $ct = self::nivel_max + 1;
    do {
      $ct--;
      
      $sql = "select nome, id_pai from categoria where id_site = {$id_site} and id_categoria = {$id_categoria}";

      $r = $cbd->consulta($sql);

      if ($_r = $r->next()) {
        array_unshift( $_m, array($id_categoria, $_r["nome"], $_r["id_pai"]) );

        $id_categoria = $_r["id_pai"];
      }
      else
        $id_categoria = 0;
    }
    while (($ct > 0) && ($id_categoria > 1));


    return $_m;
  }

  private function calcula_id(FS_cbd $cbd) {
    $id_site = $this->atr("id_site")->valor;

    if (!$cbd->executa("insert into categoria_seq (id_site) values ({$id_site})")) return false;

    $r = $cbd->consulta("select LAST_INSERT_ID() as id");

    if (!$a = $r->next()) return false;

    $cbd->executa("delete from categoria_seq where id_site = {$id_site} and id_categoria < {$a['id']}");

    $this->atr("id_categoria")->valor = $a['id'];

    return true;
  }
}

//-----------------------------------------------

final class Categoria_mbd extends Mapa_bd {
  public function __construct() {
    $t = new Taboa_dbd("categoria");

    $t->pon_campo("id_site"     , new Numero(), true);
    $t->pon_campo("id_categoria", new Numero(), true);
    $t->pon_campo("id_pai"      , new Numero()      );
    $t->pon_campo("nivel"       , new Numero()      );
    $t->pon_campo("nome"                            );
    $t->pon_campo("notas"                           );

    $this->pon_taboa($t);
  }
}
