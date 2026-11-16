<?php

class Articulo_marca_obd extends Obxeto_bd
                      implements ICLogo, IMetatags, IElmt_ventas_rel, ICatalogo_fonte {

  private $orderby = "asc()";

  public function __construct($id_site, $id_marca = null) {
    parent::__construct();

    $this->atr("id_site" )->valor = $id_site;
    $this->atr("id_marca")->valor = $id_marca;
  }

  public function mapa_bd() {
    return new Articulo_marca_mbd();
  }

  public static function inicia(FS_cbd $cbd, $id_site, $id_marca = null) {
    $a = new Articulo_marca_obd($id_site, $id_marca);

    if ($id_marca == null) return $a;

    $wh_s = $a->atr("id_site" )->sql_where($cbd);
    $wh_a = $a->atr("id_marca")->sql_where($cbd);

    $a->select($cbd, "{$wh_s} and {$wh_a}");

    return $a;
  }

  public function iclogo_obd(FS_cbd $cbd, ?string $k = null) { //* IMPLEMENTA ICLogo.iclogo_obd()
    if ($cbd == null) $cbd = new FS_cbd();

    $id_logo = $this->iclogo_id($k);

    if ($id_logo != null) return Elmt_obd::inicia($cbd, $id_logo);


    return null;
  }

  public function iclogo_id(?string $k = null) { //* IMPLEMENTA ICLogo.iclogo_id()
    return $this->atr("id_logo")->valor;
  }

  public function iclogo_src(FS_cbd $cbd, ?string $k = null) { //* IMPLEMENTA ICLogo.iclogo_src()
    if (($logo_obd = $this->iclogo_obd($cbd, $k)) == null) return null;

    return $logo_obd->url(false, $cbd);
  }

  public function iclogo_update(FS_cbd $cbd, $src) { //* IMPLEMENTA ICLogo.iclogo_update()
    if (($l = $this->iclogo_obd($cbd)) != null) {
      $l->post_url($src);

      return $l->update($cbd);
    }

    if ($src == null) return true;


    $l = new Imaxe_obd($src);

    if (!$l->insert($cbd)) return false;

    //* actualiza a foto no contacto.

    $k1 = $this->atr("id_site" )->valor;
    $k2 = $this->atr("id_marca")->valor;

    $id_logo = $l->atr("id_elmt")->valor;


    $this->atr("id_logo")->valor = $id_logo; //* actualizamos o valor en RAM


    return $cbd->executa( "update articulo_marca set id_logo='{$id_logo}' where id_site='{$k1}' and id_marca='{$k2}'" );
  }

  public function _metatags($url_limpa, FS_cbd $cbd = null) { //* IMPLEMENTA IMetatags._metatags()
    if ($cbd == null) $cbd = new FS_cbd();

    $tit = $this->atr("nome")->valor;
    $des = Valida::split($this->atr("notas")->valor, 222, "");
    $log = $this->iclogo_src($cbd);
    $rob = "index, follow";
    $script = null;

    $url = null;
    $idi = null;
    $can = null;

    //~ if (($_fimx = $this->iclogo_src($cbd)) == null) $log = array_shift($_fimx);

    return array($tit, $des, $log, $rob, $url, $idi, $can, $script);
  }

  public function select(FS_cbd $cbd, $where = null, $orderby = null) {
    $r = $cbd->consulta($this->sql_select($cbd, $where, $orderby));

    if (!$a = $r->next()) return;

    $this->post($a);
  }

  public function insert(FS_cbd $cbd, $url_foto) {
    if ( !$this->calcula_id($cbd)                ) return false;

    if ( !$cbd->executa($this->sql_insert($cbd)) ) return false;


    return $this->iclogo_update($cbd, $url_foto);
  }

  public function update(FS_cbd $cbd, $url_foto) {
    if ($this->atr("id_marca")->valor == null) return $this->insert($cbd, $url_foto);


    if (!$this->iclogo_update($cbd, $url_foto)) return false;

    return $cbd->executa($this->sql_update($cbd));
  }

  public function delete(FS_cbd $cbd) {
    $wh_s = "id_site  = " . $this->atr("id_site" )->valor;
    $wh_m = "id_marca = " . $this->atr("id_marca")->valor;
    $sql  = "update articulo set id_marca = null where {$wh_s} and {$wh_m}";

    if (!$cbd->executa($sql)) return false;


    if (!$cbd->executa($this->sql_delete($cbd))) return false;

    if (($logo_obd = $this->iclogo_obd($cbd)) == null) return true;


    return $logo_obd->delete($cbd);
  }

  public static function _marca($id_site, $id_almacen = null, $predet = "···", FS_cbd $cbd = null) {
    //* devolve un array de marcas para un selector.

    if ($cbd == null) $cbd = new FS_cbd();

    $_m = array();

    if ($predet != null) $_m['**'] = $predet;

    $wh_alm = ""; if ($id_almacen != null) $wh_alm = " and id_almacen = {$id_almacen}";

    $r = $cbd->consulta("select id_marca, nome from articulo_marca where id_site = {$id_site}{$wh_alm} order by nome");

    while ($_r = $r->next()) $_m[ $_r['id_marca'] ] = $_r['nome'];


    return $_m;
  }

  public static function _marca_2($id_site, FS_cbd $cbd = null, $logo = true, $limit = null, $orderby = "c.nome asc") {
    if ($cbd == null) $cbd = new FS_cbd();

    $oby   = ($orderby == null)?"a.nome asc":$orderby            ;
    $limit = ($limit   == null)?""          :" limit 0, {$limit}";

    $wh_u = "(1)";


    $sc = Site_config_obd::inicia($cbd, $id_site);

    if ($sc->atr("ventas_producto_agotados")->valor != 1) $wh_u = "a.unidades >= a.pedido_min";

    $sql = "select distinct c.*
             from articulo a inner join articulo_publicacions b on (a.id_site = b.id_site and a.id_articulo = b.id_articulo)
                             inner join articulo_marca        c on (a.id_site = c.id_site and a.id_marca    = c.id_marca)
            where (a.id_site = {$id_site}) and ({$wh_u})
         order by {$oby} {$limit}";

    $r = $cbd->consulta( $sql );


    $_m = array();
    while ($_r = $r->next()) {
      $l = ($logo)?self::logo_src($cbd, $_r['id_logo']):null;
      
      $_m[] = array('id_marca' => $_r['id_marca'],
                    'id_logo'  => $l,
                    'nome'     => $_r['nome'],
                    'notas'    => Valida::split($_r['notas'], 99, "")
                   );
    }


    return $_m;
  }

  public function titulo() { //* IMPLEMENTA IElmt_ventas_rel.titulo()
    return "<h1>" . $this->atr("nome")->valor . "</h1>"; //* no implementaremos esta operación
  }

  public function orderby($orderby = -1) { //* IMPLEMENTA IElmt_ventas_rel.orderby()
    if ($orderby != -1) $this->orderby = $orderby;

    return $this->orderby;
  }

  public function selectfrom(FS_cbd $cbd = null):string { //* IMPLEMENTA IElmt_ventas_rel.selectfrom():string
    $wh_s = "id_site  = " . $this->atr("id_site" )->valor;
    $wh_m = "id_marca = " . $this->atr("id_marca")->valor;

    return "
select distinct a.id_articulo, a.nome
  from (
select id_site, min(id_articulo) as id_articulo, nome from articulo where (not id_grupo_cc is null) and ({$wh_s}) and ({$wh_m}) group by id_grupo_cc
union
select id_site, id_articulo, nome from articulo where (id_grupo_cc is null) and ({$wh_s}) and ({$wh_m})
       ) a inner join articulo_publicacions b on (a.id_site = b.id_site and a.id_articulo = b.id_articulo)
           inner join paxina c                on (b.id_paxina = c.id_paxina and c.estado = 'publicada')";
  }

  public function _articulo(FS_cbd $cbd = null, $max = -1) { //* IMPLEMENTA IElmt_ventas_rel._articulo()
    $o = $this->orderby();
    $l = ""; if ($max > 0) $l = " limit 0, {$max}";

    $sql =  $this->selectfrom($cbd) . " order by {$orderby}{$limit}";

    if ($cbd == null) $cbd = new FS_cbd();

    $r = $cbd->consulta($sql);

    $_a = null; while ($_r= $r->next()) $_a[ $_r['id_articulo'] ] = 1;

    return $_a;
  }



  public function icatF__site():int {  //* IMPLEMENTA ICatalogo_fonte.icatF__site()
    return $this->atr("id_site")->valor;
  }

  public function icatF__catRaiz():int {  //* IMPLEMENTA ICatalogo_fonte.icatF__catRaiz()
    return 1;
  }
  
  public function icatF__titulo():string {  //* IMPLEMENTA ICatalogo_fonte.icatF__titulo()
    return strval($this->atr("nome")->valor);
  }
  
  public function icatF__selectfrom(bool $editor, bool $vpa):string {  //* IMPLEMENTA ICatalogo_fonte.icatF__selectfrom()
    $wh_s = "a0.id_site  = " . $this->atr("id_site" )->valor;
    $wh_m = "a0.id_marca = " . $this->atr("id_marca")->valor;
    
    $wh_u = ($vpa)?"":" and (unidades >= pedido_min)";

    return "
select distinct a.id_articulo, a.nome, a.notas, a.id_categoria, a.id_marca, a.momento_fs as momento
  from (
select id_site, min(id_articulo) as id_articulo, nome, notas, id_categoria, id_marca, momento_fs from articulo a0 where (not id_grupo_cc is null) and ({$wh_s}) and ({$wh_m}){$wh_u} group by id_grupo_cc
union
select id_site, id_articulo, nome, notas, id_categoria, id_marca, momento_fs from articulo a0 where (id_grupo_cc is null) and ({$wh_s}) and ({$wh_m}){$wh_u}
       ) a inner join articulo_publicacions b on (a.id_site = b.id_site and a.id_articulo  = b.id_articulo)
           left  join v_categoria           c on (a.id_site = c.id_site and a.id_categoria = c.id_categoria)";

  }
  
  public function icatF__orderby(int $orderby = -1):string {  //* IMPLEMENTA ICatalogo_fonte.icatF__orderby()
    return "momento desc";
  }
  
  public function icatF__etq_tit(int $i, FS_cbd $cbd = null):?string {  //* IMPLEMENTA ICatalogo_fonte.icatF__etq_tit()
    switch ($i) {
      case 1: return "Categorías";
    }

    return null;
  }
  
  public function icatF__etq_sql(int $i, bool $vpa, FS_cbd $cbd = null):?string {  //* IMPLEMENTA ICatalogo_fonte.icatF__etq_sql()
    switch ($i) {
      case 1: return $this->icatF__sql_categorias($vpa, $cbd);
      case 2: return $this->icatF__sql_marcas($vpa);
    }

    return null;
  }
  
  private function icatF__sql_categorias(bool $vpa, FS_cbd $cbd = null):?string {
    if ($cbd == null) $cbd = new FS_cbd();


    $wh_s = "a.id_site  = " . $this->atr("id_site" )->valor;
    $wh_a = "a.id_marca = " . $this->atr("id_marca")->valor;
    
    $wh_u = ($vpa)?"":" and (unidades >= pedido_min)";
    
    $sql = "select distinct 
              CASE
                WHEN b.nivel = 3 THEN b.id_avo
                WHEN b.nivel = 2 THEN b.id_pai
                ELSE a.id_categoria
              END as id_categoria
              from articulo a inner join v_categoria           b on (a.id_site = b.id_site and a.id_categoria = b.id_categoria)
                              inner join articulo_publicacions c on (a.id_site = c.id_site and a.id_articulo  = c.id_articulo )
             where {$wh_s} and {$wh_a}{$wh_u} and b.id_categoria > 1";
             
             
    $sk = "";
    $r = $cbd->consulta($sql);
    while( $_r = $r->next() ) {
      if ($sk != "") $sk .= ",";
      
      $sk .= $_r["id_categoria"];
    }
    
    if ($sk == "") $sk = "0";
    
    return "select a.id_categoria as id_etq, a.nome as etq from categoria a where {$wh_s} and a.id_categoria IN ({$sk}) order by a.nome";
  }
  
  private function icatF__sql_marcas(bool $vpa):?string {  
    return null;
  }



  private function calcula_id(FS_cbd $cbd) {
    $id_site = $this->atr("id_site")->valor;

    if (!$cbd->executa("insert into articulo_marca_seq (id_site) values ({$id_site})")) return false;

    $r = $cbd->consulta("select LAST_INSERT_ID() as id");

    if (!$a = $r->next()) return false;

    $cbd->executa("delete from articulo_marca_seq where id_site = {$id_site} and id_marca < {$a['id']}");

    $this->atr("id_marca")->valor = $a['id'];

    return true;
  }

  private static function logo_src(FS_cbd $cbd, $id_logo) {
    $am = new Articulo_marca_obd(null);

    $am->atr("id_logo")->valor = $id_logo;

    return $am->iclogo_src($cbd);
  }
}

//-----------------------------------------------

class Articulo_marca_mbd extends Mapa_bd {
  public function __construct() {
    $t = new Taboa_dbd("articulo_marca");

    $t->pon_campo("id_site"   , new Numero(), true);
    $t->pon_campo("id_marca"  , new Numero(), true);
    $t->pon_campo("id_almacen", new Numero());
    $t->pon_campo("id_logo"   , new Numero());
    $t->pon_campo("nome");
    $t->pon_campo("notas");

    $this->pon_taboa($t);
  }
}
