<?php

final class Almacen_obd extends Obxeto_bd
                     implements IPortes_obd, IMetatags, IElmt_ventas_rel, ICatalogo_fonte {

  public static $_entregau = array("d", "h");
  public static $_semana   = array("2" => "Martes",
                                   "3" => "Miércoles",
                                   "4" => "Jueves",
                                   "5" => "Viernes"
                                  );

  private $contacto_obd = null; //* caché de contacto.

  public function __construct($id_site, $id_almacen = null) {
    parent::__construct();

    $this->atr("id_site"            )->valor = $id_site;
    $this->atr("id_almacen"         )->valor = $id_almacen;
    $this->atr("local"              )->valor = "1";
    $this->atr("finde"              )->valor = "1";
    $this->atr("peninsula"          )->valor = "1";
    $this->atr("baleares"           )->valor = "1";
    $this->atr("canarias"           )->valor = "1";
    $this->atr("cee"                )->valor = "1";
    $this->atr("portes_l"           )->valor = "0";
    $this->atr("portes_b"           )->valor = "0";
    $this->atr("portes_c35"         )->valor = "0";
    $this->atr("portes_c38"         )->valor = "0";
    $this->atr("portes_cee"         )->valor = "0";
    $this->atr("portes_p"           )->valor = "0";
    $this->atr("portes_finde"       )->valor = "0";
    $this->atr("portes_gratis_l"    )->valor = "0";
    $this->atr("portes_gratis_b"    )->valor = "0";
    $this->atr("portes_gratis_c35"  )->valor = "0";
    $this->atr("portes_gratis_c38"  )->valor = "0";
    $this->atr("portes_gratis_cee"  )->valor = "0";
    $this->atr("portes_gratis_p"    )->valor = "0";
    $this->atr("portes_gratis_finde")->valor = "0";
    $this->atr("capacidade"         )->valor = "0";
    $this->atr("entrega_l"          )->valor = "48";
    $this->atr("entrega_p"          )->valor = "48";
    $this->atr("entrega_b"          )->valor = "48";
    $this->atr("entrega_c35"        )->valor = "48";
    $this->atr("entrega_c38"        )->valor = "48";
    $this->atr("entrega_cee"        )->valor = "5";
    $this->atr("entrega_finde"      )->valor = "5";
    $this->atr("entrega_u_p"        )->valor = "h";
    $this->atr("entrega_u_b"        )->valor = "h";
    $this->atr("entrega_u_c35"      )->valor = "h";
    $this->atr("entrega_u_c38"      )->valor = "h";
    $this->atr("entrega_u_cee"      )->valor = "d";
    $this->atr("entrega_u_finde"    )->valor = "d";
    $this->atr("limite_finde_d"     )->valor = "5";
    $this->atr("limite_finde_h"     )->valor = "14:00";
    $this->atr("comision_venta"     )->valor = "0";
    $this->atr("id_contacto"        )->valor = null;
    $this->atr("ces"                )->valor = null;
    $this->atr("ch_1"               )->valor = "1";
    $this->atr("ch_2"               )->valor = "1";
    $this->atr("ch_3"               )->valor = "1";
    $this->atr("ch_4"               )->valor = "1";
    $this->atr("ch_5"               )->valor = "1";
    $this->atr("ch_6"               )->valor = "1";
    $this->atr("ch_0"               )->valor = "1";
  }

  public function mapa_bd() {
    return new Almacen_mbd();
  }

  public static function inicia(FS_cbd $cbd, $id_site, $id_almacen = null) {
    $a = new Almacen_obd($id_site, $id_almacen);

    if ($id_almacen == null) return $a;

    $wh_s = $a->atr("id_site"   )->sql_where($cbd);
    $wh_a = $a->atr("id_almacen")->sql_where($cbd);

    $a->select($cbd, "{$wh_s} and {$wh_a}");


    return $a;
  }

  public function titulo() { //* IMPLEMENTA IElmt_ventas_rel.titulo()
    return "";
  }

  public function orderby($orderby = -1) { //* IMPLEMENTA IElmt_ventas_rel.orderby()
    return "momento desc";
  }

  public function selectfrom(FS_cbd $cbd = null):string { //* IMPLEMENTA IElmt_ventas_rel.selectfrom():string
    if ($cbd == null) $cbd = new FS_cbd();

    $wh_u = "";
    $wh_s = "a.id_site  = " . $this->atr("id_site"   )->valor;
    $wh_a = "id_almacen = " . $this->atr("id_almacen")->valor;


    $sc = Site_config_obd::inicia($cbd, $this->atr("id_site")->valor);

    if ($sc->atr("ventas_producto_agotados")->valor != 1) $wh_u = " and (unidades >= pedido_min)";

    return "
select distinct id_articulo
  from (
    select distinct a.id_articulo, a.nome, h.momento
      from articulo a inner join articulo_publicacions h on (a.id_site = h.id_site and a.id_articulo = h.id_articulo)
    where {$wh_s} and {$wh_a}{$wh_u} and (a.id_grupo_cc is null)
    union
    select id_articulo, nome, momento
    from (
        select id_grupo_cc, a.id_articulo, a.nome, desc_p, pvp, h.momento
          from articulo a inner join articulo_publicacions h on (a.id_site = h.id_site and a.id_articulo = h.id_articulo)
        where {$wh_s} and {$wh_a}{$wh_u} and (not id_grupo_cc is null)
        order by desc_p desc, pvp asc
         ) a1
    group by a1.id_grupo_cc
) a";
  }

  public function _articulo(FS_cbd $cbd = null, $max = -1) { //* IMPLEMENTA IElmt_ventas_rel._articulo()
    $limit = ""; if ($max > 0) $limit = " limit 0, {$max}";
    $orderby = $this->orderby();


    if ($cbd == null) $cbd = new FS_cbd();

    $sql =  $this->selectfrom($cbd) . " order by {$orderby}{$limit}";

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
    return "";
  }
  
  public function icatF__selectfrom(bool $editor, bool $vpa):string {  //* IMPLEMENTA ICatalogo_fonte.icatF__selectfrom()
    $wh_s = "a0.id_site  = " . $this->atr("id_site"   )->valor;
    $wh_a = "a0.id_almacen = " . $this->atr("id_almacen")->valor;
    
    $wh_u = ($vpa)?"":" and (a0.unidades >= a0.pedido_min)";

    return "
select distinct a.id_articulo, a.nome, a.notas, a.id_categoria, a.id_marca, a.momento_fs as momento
  from (
select id_site, min(id_articulo) as id_articulo, nome, notas, id_categoria, id_marca, momento_fs from articulo a0 where (not id_grupo_cc is null) and ({$wh_s}) and ({$wh_a}){$wh_u} group by id_grupo_cc
union
select id_site, id_articulo, nome, notas, id_categoria, id_marca, momento_fs from articulo a0 where (id_grupo_cc is null) and ({$wh_s}) and ({$wh_a}){$wh_u}
       ) a inner join articulo_publicacions b on (a.id_site = b.id_site and a.id_articulo  = b.id_articulo)
           left  join v_categoria           c on (a.id_site = c.id_site and a.id_categoria = c.id_categoria)";
  }
  
  public function icatF__orderby(int $orderby = -1):string {  //* IMPLEMENTA ICatalogo_fonte.icatF__orderby()
    return "momento desc";
  }
  
  public function icatF__etq_tit(int $i, FS_cbd $cbd = null):?string {  //* IMPLEMENTA ICatalogo_fonte.icatF__etq_tit()
    switch ($i) {
      case 1: return "Categorías";
      case 2: return "Marcas";
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


    $wh_s = "a.id_site  = " . $this->atr("id_site"   )->valor;
    $wh_a = "id_almacen = " . $this->atr("id_almacen")->valor;
    
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
    $wh_s = "a.id_site    = " . $this->atr("id_site"   )->valor;
    $wh_a = "a.id_almacen = " . $this->atr("id_almacen")->valor;
    
    $wh_u = ($vpa)?"":" and (unidades >= pedido_min)";
    
    return "select distinct a.id_marca as id_etq, b.nome as etq
              from articulo a inner join articulo_marca        b on (a.id_site = b.id_site and a.id_marca    = b.id_marca   )
                              inner join articulo_publicacions c on (a.id_site = c.id_site and a.id_articulo = c.id_articulo)
             where {$wh_s} and {$wh_a}{$wh_u}
            order by b.nome";
  }
  

  public function validado() {
    return $this->atr("id_contacto")->valor != null;
  }

  public function contacto_obd(FS_cbd $cbd = null) {
    if ($this->contacto_obd != null) return $this->contacto_obd;

    if ($cbd == null) $cbd = new FS_cbd();

    $this->contacto_obd = Contacto_obd::inicia($cbd, $this->atr("id_contacto")->valor, "a");

    return $this->contacto_obd;
  }

  public function _metatags($url_limpa, FS_cbd $cbd = null) {  //* IMPLEMENTA IMetatags._metatags()
    if ($cbd == null) $cbd = new FS_cbd();

    $c = $this->contacto_obd($cbd);


    $tit = $c->atr("razon_social")->valor;
    $des = Valida::split($c->atr("notas")->valor, 160, "");
    $log = $c->iclogo_src($cbd);
    $rob = "index, follow";
    $script = null;

    $url = null;
    $idi = null;
    $can = null;
    

    return array($tit, $des, $log, $rob, $url, $idi, $can, $script);
  }

  public function select(FS_cbd $cbd, $where = null, $orderby = null) {
    $r = $cbd->consulta($this->sql_select($cbd, $where, $orderby));

    if (!$a = $r->next()) return;

    $this->post($a);
  }

  public function update(FS_cbd $cbd, Contacto_obd $c = null, $src_foto = null) {
    if ($this->atr("id_almacen")->valor == null) return $this->insert($cbd, $c, $src_foto);

    if (!$c->update($cbd, $src_foto)) return false;

    if ( !$cbd->executa($this->sql_update($cbd, null, "almacen_portes_importe")) ) return false;
    if ( !$cbd->executa($this->sql_update($cbd, null, "almacen_portes_peso"   )) ) return false;

    return $cbd->executa($this->sql_update($cbd));
  }

  public function insert(FS_cbd $cbd, Contacto_obd $c, $src_foto) {
    $c->atr("tipo")->valor = "a"; //* forzamos tipo de contacto.

    if (!$c->insert($cbd, $src_foto)) return false;

    $this->atr("id_contacto")->valor = $c->atr("id_contacto")->valor;


    if (!$this->calcula_id($cbd)) return false;

    $this->atr("id_almacen_portes")->valor = $this->atr("id_almacen")->valor;

    if ( !$cbd->executa($this->sql_insert($cbd, "almacen_portes_importe")) ) return false;
    if ( !$cbd->executa($this->sql_insert($cbd, "almacen_portes_peso"   )) ) return false;


    return $cbd->executa($this->sql_insert($cbd));
  }

  public function delete(FS_cbd $cbd, $articulos = true) {
    //* elimina contacto
    if (($c = $this->contacto_obd()) != null) if (!$c->delete($cbd)) return false;

    //* elimina almacén

    if (!$cbd->executa($this->sql_delete($cbd, null, "almacen_portes_importe"))) return false;
    if (!$cbd->executa($this->sql_delete($cbd, null, "almacen_portes_peso"   ))) return false;

    if (!$cbd->executa($this->sql_delete($cbd))) return false;


    //* borra artículos asociados ao almacen.
    if (!$articulos) return true;

    $site = $this->atr("id_site"   )->valor;
    $alma = $this->atr("id_almacen")->valor;

    $r = $cbd->consulta("select id_articulo from articulo where id_site = {$site} and id_almacen = {$alma}");

    while ($_r = $r->next()) {
      $a = Articulo_obd::inicia($cbd, $site, $_r["id_articulo"]);

      if ( !$a->delete($cbd) ) return false;
    }


    return true;
  }

  public function pechado() {
    $k = "ch_" . date("w");

    return $this->atr($k)->valor != 1;
  }

  public function validar_destino($id_country, $codpostal) {
//~ echo "$id_country, $codpostal<br>";


    //* destino local.
    if ($codpostal != "") if ( $this->destino_local($codpostal) ) return ($this->atr("local")->valor == 1);

    //* destino extranxeiro.
    if (self::destino_extranxeiro($id_country)) return $this->atr("cee")->valor == 1;


    if ($id_country == 177) {
      //* O enderezo é Portugal, non se fai control de destino insular.

      return ($this->atr("peninsula")->valor == 1);
    }


    if ($codpostal == "") return true;

    //* Control de destino insular.
    $d = substr($codpostal, 0, 2);

    if ($d == 7) return ($this->atr("baleares")->valor == 1);

    if (($d == 35) || ($d == 38)) return ($this->atr("canarias")->valor == 1);


    //* destino peninsular.
    return ($this->atr("peninsula")->valor == 1);
  }

  public function calcula($a_param) {  //* IMPLEMENTA IPortes_obd.calcula()
    if ($this->atr("portes_tipo")->valor == "i") return $this->calcula_portes_importe($a_param);

    return $this->calcula_portes_peso($a_param);
  }

  public function contar(FS_cbd $cbd = null) {
    if ($cbd == null) $cbd = new FS_cbd();

    $s = $this->atr("id_site"   )->valor;
    $a = $this->atr("id_almacen")->valor;

    $wh_u = "";

    $sc = Site_config_obd::inicia($cbd, $s);

    if ($sc->atr("ventas_producto_agotados")->valor != 1) $wh_u = " and (unidades >= pedido_min)";

    $r = $cbd->consulta("select count(*) as c from articulo where id_site = {$s} and id_almacen = {$a}{$wh_u}");

    if ($_r = $r->next()) return $_r["c"];

    return 0;
  }

  public static function destino_extranxeiro($id_country) {
    //* Fora da península, (España e Portugal).

    if ($id_country == 177) return false; //* portugal
    if ($id_country == 203) return false; //* españa

    return true;
  }

  public function destino_local($id_destino) {
    if (trim($id_destino) == "") return true;

    return ( strpos(strval($this->atr("portes_postais")->valor), $id_destino) !== false);
  }

  public static function destino_insular($id_country, $id_destino) {
    if ($id_country != 203) return false; //* españa

    $d = substr($id_destino, 0, 2);

    return (($d == 7) || ($d == 35) || ($d == 38));
  }

  public static function destino_peninsular($id_country, $id_destino) {
    if (self::destino_extranxeiro($id_country             )) return false;
    if (self::destino_insular    ($id_country, $id_destino)) return false;
    //~ if (self::destino_local      ($id_destino             )) return false;

    return true;
  }

  public static function _almacen($id_site, $predet = null, FS_cbd $cbd = null) {
    if ($cbd == null) $cbd = new FS_cbd();


    $sql = "select id_almacen, nome from v_almacen where id_site = '{$id_site}' order by nome";

    $r = $cbd->consulta($sql);

    $_al = ($predet == null)?array():array("**"=>$predet);

    while ($_a = $r->next()) $_al[$_a['id_almacen']] = $_a['nome'];


    return $_al;
  }

  public static function _almacen_2($id_site, $limit = null, FS_cbd $cbd = null) {
    if ($cbd == null) $cbd = new FS_cbd();

    $limit = ($limit != null)?" limit 0, {$limit}":"";

    $sql = "select distinct id_almacen, nome, id_contacto
              from v_almacen a
             where a.id_site = {$id_site}
          order by a.nome {$limit}";

    $r = $cbd->consulta( $sql );

    $_m = array();
    while ($_r = $r->next()) {
      $c = Contacto_obd::inicia($cbd, $_r['id_contacto'], "p");

      $_m[] = array('id_marca' => $_r['id_almacen'],
                    'id_logo'  => $c->iclogo_src($cbd),
                    'nome'     => $_r['nome'],
                    'notas'    => Valida::split( $c->atr("notas")->valor, 99, "" ),
                    'url'      => Paxina_reservada_obd::action_almacen($_r['id_almacen'], $_r['nome'])
                   );
    }


    return $_m;
  }

  private function calcula_id(FS_cbd $cbd) {
    $id_site = $this->atr("id_site")->valor;

    if (!$cbd->executa("insert into almacen_seq (id_site) values ({$id_site})")) return false;

    $r = $cbd->consulta("select LAST_INSERT_ID() as id");

    if (!$a = $r->next()) return false;

    $cbd->executa("delete from almacen_seq where id_site = {$id_site} and id_almacen < {$a['id']}");

    $this->atr("id_almacen")->valor = $a['id'];

    return true;
  }

  public function calcula_chrecollida() {
    //* $_alma:Almacen_obd[]
    //* return data_vindeiro_sábado ou null.

    //* OPCIÓN RESTRICTIVA.

    if ($this->atr("ch_recollida")->valor != 1) return null;

    $c = $this->contacto_obd();


    return $c->enderezo_obd()->resumo();
  }

  public static function calcula_chsabado($_alma) {
    //* $_alma:Almacen_obd[]
    //* return data_vindeiro_sábado ou null.

    //* OPCIÓN RESTRICTIVA.

    if ($_alma == null) return null;

//~ foreach ($_alma as $a) echo "<pre>" . print_r($a->a_resumo(), true) . "</pre>";


    //* 0.- calcula visibilidade e límite.
    $ld = "6";
    $lh = "23:59";
    foreach ($_alma as $a) {
      //* 0.1.- visivilidade, (OPCIÓN RESTRICTIVA).
      if ($a->atr("finde")->valor != 1) return null;

      //* 0.3.- límite, (OPCIÓN RESTRICTIVA).
      if ($a->atr("limite_finde_d")->valor <= $ld) {
        $ld = $a->atr("limite_finde_d")->valor;

        if ($a->atr("limite_finde_h")->valor <= $lh) $lh = $a->atr("limite_finde_h")->valor;
      }
    }


    //* 10.- calcula data_vindeiro_sábado.
    /*
     * $limite_d IN {2,3,4,5}
     * $hoxe_d   IN {0,1, ... , 6}
     * $hoxe_t   {H:i}
     */
    $hoxe_d        = date("w");
    $hoxe_h        = date("H:i");

    $dias_a_sabado = 6 - $hoxe_d;

    if ($hoxe_d > $ld) {
      $dias_a_sabado += 7;
    }
    elseif (($hoxe_d == $ld) && ($hoxe_h > $lh)) {
      $dias_a_sabado += 7;
    }


    return Valida::suma_dias($dias_a_sabado);
  }


  private function calcula_portes_importe($a_param) {  //* IMPLEMENTA IPortes_obd.calcula()
    $t = $a_param['total']; //* total de pvp de articulos de un pedido que pertenecen a este almacen.


    //* portes locais
    if ( $this->destino_local( $a_param['id_destino'] ) ) {
      if ($this->atr("portes_gratis_l_2")->valor > 0)
        if ($t >= $this->atr("portes_gratis_l_2")->valor) return $this->atr("portes_l_3")->valor;
      if ($t >= $this->atr("portes_gratis_l"  )->valor) return $this->atr("portes_l_2")->valor;

      return $this->atr("portes_l")->valor;
    }


    if ($a_param['id_country'] == null) $a_param['id_country'] = 203; //* se non hai un páis establecido, tomamos España como predeterminado.

    if (self::destino_extranxeiro($a_param['id_country'])) {
      if ($this->atr("portes_gratis_cee_2")->valor > 0)
        if ($t >= $this->atr("portes_gratis_cee_2")->valor) return $this->atr("portes_cee_3")->valor;
      if ($t >= $this->atr("portes_gratis_cee"  )->valor) return $this->atr("portes_cee_2")->valor;

      return $this->atr("portes_cee")->valor;
    }



    $d = (int)substr($a_param['id_destino'], 0, 2);

    if ($d == 7) {
      if ($this->atr("portes_gratis_b_2")->valor > 0)
        if ($t >= $this->atr("portes_gratis_b_2")->valor) return $this->atr("portes_b_3")->valor;
      if ($t >= $this->atr("portes_gratis_b"  )->valor) return $this->atr("portes_b_2")->valor;

      return $this->atr("portes_b")->valor;
    }

    if ($d == 35) {
      if ($this->atr("portes_gratis_c35_2")->valor > 0)
        if ($t >= $this->atr("portes_gratis_c35_2")->valor) return $this->atr("portes_c35_3")->valor;
      if ($t >= $this->atr("portes_gratis_c35"  )->valor) return $this->atr("portes_c35_2")->valor;

      return $this->atr("portes_c35")->valor;
    }

    if ($d == 38) {
      if ($this->atr("portes_gratis_c38_2")->valor > 0)
        if ($t >= $this->atr("portes_gratis_c38_2")->valor) return $this->atr("portes_c38_3")->valor;
      if ($t >= $this->atr("portes_gratis_c38"  )->valor) return $this->atr("portes_c38_2")->valor;

      return $this->atr("portes_c38")->valor;
    }


    //* portes península en sábado (España e Portugal)
    if ($a_param['chsabado']) {
      if ($t >= $this->atr("portes_gratis_finde")->valor) return $this->atr("portes_finde_2")->valor;

      return $this->atr("portes_finde")->valor;
    }

    //* portes península (España e Portugal)
    if ($this->atr("portes_gratis_p_2")->valor > 0)
      if ($t >= $this->atr("portes_gratis_p_2")->valor) return $this->atr("portes_p_3")->valor;

    if ($t >= $this->atr("portes_gratis_p"  )->valor) return $this->atr("portes_p_2")->valor;

    return $this->atr("portes_p")->valor;
  }

  private function calcula_portes_peso($a_param) {  //* IMPLEMENTA IPortes_obd.calcula()
//~ print_r( $a_param );

    if ($a_param['id_country'] == null) $a_param['id_country'] = 203; //* se non hai un páis establecido, tomamos España como predeterminado.

    $t = $a_param['pesos']; //* total de peso de articulos de un pedido que pertenecen a este almacen.

    $d = (int)substr($a_param['id_destino'], 0, 2);

    if ($this->destino_local($a_param['id_destino'])) {
      $_peso = unserialize($this->atr("pesos_l")->valor);
    }
    elseif (self::destino_extranxeiro($a_param['id_country'])) {
      $_peso = unserialize($this->atr("pesos_cee")->valor);
    }
    elseif ($d == 7) {
      $_peso = unserialize($this->atr("pesos_b")->valor);
    }
    elseif ($d == 35) {
      $_peso = unserialize($this->atr("pesos_c35")->valor);
    }
    elseif ($d == 38) {
      $_peso = unserialize($this->atr("pesos_c38")->valor);
    }
    else {
      $_peso = unserialize($this->atr("pesos_p")->valor);
    }
//~ print_r( $_peso );


    //* portes península (España e Portugal)
    /*
    if ($a_param['chsabado']) {
      if ($t >= $this->atr("portes_gratis_finde")->valor) return $this->atr("portes_finde_2")->valor;

      return $this->atr("portes_finde")->valor;
    }
    */

//~ print_r("peso = {$t}\n");

//~ print_r($a_param);
//~ print_r($this->a_resumo());


    for ($i_max = 20; $i_max > 0; $i_max--) if ($_peso[$i_max] > 0) break;         //* calcula o peso mais alto do array de pesos.

    if ( $t >= $i_max ) return $_peso[$i_max] + ( ceil($t - $i_max) * $_peso[21] ); //* se o peso > $i_max, aplica $i_max sumando o incremento por cada kilo que sobrepasa $i_max.



    return $_peso[ ceil($t) ];
  }
}

//-----------------------------------------------

final class Almacen_mbd extends Mapa_bd {
  public function __construct() {
    $t = new Taboa_dbd("almacen");

    $t->pon_campo("id_site"              , new Numero(), true);
    $t->pon_campo("id_almacen"           , new Numero(), true);
    $t->pon_campo("finde"                , new Numero()      );
    $t->pon_campo("peninsula"            , new Numero()      );
    $t->pon_campo("baleares"             , new Numero()      );
    $t->pon_campo("canarias"             , new Numero()      );
    $t->pon_campo("cee"                  , new Numero()      );
    $t->pon_campo("local"                , new Numero()      );
    $t->pon_campo("portes_tipo"                              );
    $t->pon_campo("portes_postais"                           );
    $t->pon_campo("comision_venta"       , new Numero()      );
    $t->pon_campo("entrega_finde"        , new Numero()      );
    $t->pon_campo("entrega_l"            , new Numero()      );
    $t->pon_campo("entrega_p"            , new Numero()      );
    $t->pon_campo("entrega_b"            , new Numero()      );
    $t->pon_campo("entrega_c35"          , new Numero()      );
    $t->pon_campo("entrega_c38"          , new Numero()      );
    $t->pon_campo("entrega_cee"          , new Numero()      );
    $t->pon_campo("entrega_u_finde"                          );
    $t->pon_campo("entrega_u_l"                              );
    $t->pon_campo("entrega_u_p"                              );
    $t->pon_campo("entrega_u_b"                              );
    $t->pon_campo("entrega_u_c35"                            );
    $t->pon_campo("entrega_u_c38"                            );
    $t->pon_campo("entrega_u_cee"                            );
    $t->pon_campo("limite_finde_d"       , new Numero()      );
    $t->pon_campo("limite_finde_h"                           );
    $t->pon_campo("id_almacen_portes"    , new Numero()      );
    $t->pon_campo("capacidade"           , new Numero()      );
    $t->pon_campo("id_contacto"          , new Numero()      );
    $t->pon_campo("ces"                  , new Varchar(true) );
    $t->pon_campo("ch_0"                 , new Numero()      );
    $t->pon_campo("ch_1"                 , new Numero()      );
    $t->pon_campo("ch_2"                 , new Numero()      );
    $t->pon_campo("ch_3"                 , new Numero()      );
    $t->pon_campo("ch_4"                 , new Numero()      );
    $t->pon_campo("ch_5"                 , new Numero()      );
    $t->pon_campo("ch_6"                 , new Numero()      );
    $t->pon_campo("ch_recollida"         , new Numero()      );

    $this->pon_taboa($t);


    $t3 = new Taboa_dbd("v_almacen");

    $t3->pon_campo("nome" );
    $t3->pon_campo("email");

    $this->relacion_fk($t3, array("id_site", "id_almacen"),  array("id_site", "id_almacen"), "left");


    $t4 = new Taboa_dbd("almacen_portes_importe");

    $t4->pon_campo("id_site"              , new Numero(), true);
    $t4->pon_campo("id_almacen"           , new Numero(), true);
    $t4->pon_campo("portes_finde"         , new Numero());
    $t4->pon_campo("portes_l"             , new Numero());
    $t4->pon_campo("portes_p"             , new Numero());
    $t4->pon_campo("portes_b"             , new Numero());
    $t4->pon_campo("portes_c35"           , new Numero());
    $t4->pon_campo("portes_c38"           , new Numero());
    $t4->pon_campo("portes_cee"           , new Numero());
    $t4->pon_campo("portes_finde_2"       , new Numero());
    $t4->pon_campo("portes_l_2"           , new Numero());
    $t4->pon_campo("portes_p_2"           , new Numero());
    $t4->pon_campo("portes_b_2"           , new Numero());
    $t4->pon_campo("portes_c35_2"         , new Numero());
    $t4->pon_campo("portes_c38_2"         , new Numero());
    $t4->pon_campo("portes_cee_2"         , new Numero());
    $t4->pon_campo("portes_finde_3"       , new Numero());
    $t4->pon_campo("portes_l_3"           , new Numero());
    $t4->pon_campo("portes_p_3"           , new Numero());
    $t4->pon_campo("portes_b_3"           , new Numero());
    $t4->pon_campo("portes_c35_3"         , new Numero());
    $t4->pon_campo("portes_c38_3"         , new Numero());
    $t4->pon_campo("portes_cee_3"         , new Numero());
    $t4->pon_campo("portes_gratis_finde"  , new Numero());
    $t4->pon_campo("portes_gratis_l"      , new Numero());
    $t4->pon_campo("portes_gratis_p"      , new Numero());
    $t4->pon_campo("portes_gratis_b"      , new Numero());
    $t4->pon_campo("portes_gratis_c35"    , new Numero());
    $t4->pon_campo("portes_gratis_c38"    , new Numero());
    $t4->pon_campo("portes_gratis_cee"    , new Numero());
    $t4->pon_campo("portes_gratis_finde_2", new Numero());
    $t4->pon_campo("portes_gratis_l_2"    , new Numero());
    $t4->pon_campo("portes_gratis_p_2"    , new Numero());
    $t4->pon_campo("portes_gratis_b_2"    , new Numero());
    $t4->pon_campo("portes_gratis_c35_2"  , new Numero());
    $t4->pon_campo("portes_gratis_c38_2"  , new Numero());
    $t4->pon_campo("portes_gratis_cee_2"  , new Numero());

    $this->relacion_fk($t4, array("id_site", "id_almacen"),  array("id_site", "id_almacen"), "left");


    $t5 = new Taboa_dbd("almacen_portes_peso");

    $t5->pon_campo("id_site"              , new Numero(), true);
    $t5->pon_campo("id_almacen"           , new Numero(), true);
    $t5->pon_campo("pesos_b"  );
    $t5->pon_campo("pesos_c35");
    $t5->pon_campo("pesos_c38");
    $t5->pon_campo("pesos_cee");
    $t5->pon_campo("pesos_l"  );
    $t5->pon_campo("pesos_p"  );

    $this->relacion_fk($t5, array("id_site", "id_almacen"),  array("id_site", "id_almacen"), "left");
  }
}
