<?php

final class CampoCustom_0_obd extends  Obxeto_bd {

  public static $_tipo = array("0" => "Base", "color" => "Color");

  public function __construct($id_site, $id_cc_0 = null, $id_almacen = null) {
    parent::__construct();

    $this->atr("id_site"   )->valor = $id_site;
    $this->atr("id_cc_0"   )->valor = $id_cc_0;
    $this->atr("id_almacen")->valor = $id_almacen;
    $this->atr("tipo"      )->valor = "0";
  }

  public function mapa_bd() {
    return new CampoCustom_0_mbd();
  }

  public static function inicia(FS_cbd $cbd, $id_site, $id_cc_0 = null, $id_almacen = null) {
    $p = new CampoCustom_0_obd($id_site, $id_cc_0, $id_almacen);

    if ($id_cc_0 == null) return $p;

    $wh_s = $p->atr("id_site")->sql_where($cbd);
    $wh_1 = $p->atr("id_cc_0")->sql_where($cbd);

    $p->select($cbd, "{$wh_s} and {$wh_1}");

    return $p;
  }

  public function select(FS_cbd $cbd, $where = null, $orderby = null) {
    $r = $cbd->consulta($this->sql_select($cbd, $where, $orderby));

    if (!$a = $r->next()) return;

    $this->post($a);
  }

  public function insert(FS_cbd $cbd) {
    if (!$this->calcula_id($cbd)) return false;

    return $cbd->executa($this->sql_insert($cbd));
  }

  public function update(FS_cbd $cbd) {
    if ($this->atr("id_cc_0")->valor == null) return $this->insert($cbd);

    return $cbd->executa($this->sql_update($cbd));
  }

  public function delete(FS_cbd $cbd) {
    $s   = $this->atr("id_site")->valor;
    $cc0 = $this->atr("id_cc_0")->valor;

    $sql = "delete from campocustom_1 where id_site = {$s} and id_cc_0 = {$cc0}";

    if (!$cbd->executa($sql)) return false;

    return $cbd->executa($this->sql_delete($cbd));
  }

  public static function __crea_0($id_site, FS_cbd $cbd) {
    if ($cbd == null) $cbd = new FS_cbd();

    //* 1.- comprobamos se existen cc asociados ao site.
    $r = $cbd->consulta("select count(*) as ct from campocustom_0 where id_site = {$id_site}");

    $_r = $r->next();

    if ($_r['ct'] > 0) return true;


    //* 2.- engadimos ao site cc's exemplo.

if (!$cbd->executa("INSERT INTO campocustom_0 (id_site, id_cc_0, tipo, nome, id_almacen) VALUES ({$id_site}, 1, '0', 'Tallas', 1)")) return false;
if (!$cbd->executa("INSERT INTO campocustom_1 (id_site, id_cc_0, id_cc_1, nome, notas) VALUES ({$id_site}, 1, 1, 'XS' , '')"      )) return false;
if (!$cbd->executa("INSERT INTO campocustom_1 (id_site, id_cc_0, id_cc_1, nome, notas) VALUES ({$id_site}, 1, 2, 'S'  , '')"      )) return false;
if (!$cbd->executa("INSERT INTO campocustom_1 (id_site, id_cc_0, id_cc_1, nome, notas) VALUES ({$id_site}, 1, 3, 'L'  , '')"      )) return false;
if (!$cbd->executa("INSERT INTO campocustom_1 (id_site, id_cc_0, id_cc_1, nome, notas) VALUES ({$id_site}, 1, 4, 'XL' , '')"      )) return false;
if (!$cbd->executa("INSERT INTO campocustom_1 (id_site, id_cc_0, id_cc_1, nome, notas) VALUES ({$id_site}, 1, 5, 'XXL', '')"      )) return false;

if (!$cbd->executa("INSERT INTO campocustom_0 (id_site, id_cc_0, tipo, nome, id_almacen) VALUES ({$id_site}, 2, 'color', 'Color', 1)" )) return false;
if (!$cbd->executa("INSERT INTO campocustom_1 (id_site, id_cc_0, id_cc_1, nome, notas) VALUES ({$id_site}, 2, 1, '#FFFFFF', 'Blanco')"  )) return false;
if (!$cbd->executa("INSERT INTO campocustom_1 (id_site, id_cc_0, id_cc_1, nome, notas) VALUES ({$id_site}, 2, 2, '#FF0000', 'Rojo'  )"  )) return false;
if (!$cbd->executa("INSERT INTO campocustom_1 (id_site, id_cc_0, id_cc_1, nome, notas) VALUES ({$id_site}, 2, 3, '#00FF00', 'Verde' )"  )) return false;
if (!$cbd->executa("INSERT INTO campocustom_1 (id_site, id_cc_0, id_cc_1, nome, notas) VALUES ({$id_site}, 2, 4, '#0000FF', 'Azul'  )"  )) return false;
if (!$cbd->executa("INSERT INTO campocustom_1 (id_site, id_cc_0, id_cc_1, nome, notas) VALUES ({$id_site}, 2, 5, '#000000', 'Negro' )"  )) return false;

if (!$cbd->executa("INSERT into campocustom_0_seq (id_site, id_cc_0) VALUES ({$id_site}, 2)"                           )) return false;
if (!$cbd->executa("INSERT into campocustom_1_seq (id_site, id_cc_0, id_cc_1) VALUES ({$id_site}, 1, 5)"               )) return false;
if (!$cbd->executa("INSERT into campocustom_1_seq (id_site, id_cc_0, id_cc_1) VALUES ({$id_site}, 2, 5)"               )) return false;


  return true;
}

  private function calcula_id(FS_cbd $cbd) {
    $id_site = $this->atr("id_site")->valor;

    if (!$cbd->executa("insert into campocustom_0_seq (id_site) values ({$id_site})")) return false;

    $r = $cbd->consulta("select LAST_INSERT_ID() as id");

    if (!$a = $r->next()) return false;

    $cbd->executa("delete from campocustom_0_seq where id_site = {$id_site} and id_cc_0 < {$a['id']}");

    $this->atr("id_cc_0")->valor = $a['id'];

    return true;
  }
}

//-----------------------------------------------

final class CampoCustom_0_mbd extends Mapa_bd {
  public function __construct() {
    $t = new Taboa_dbd("campocustom_0");

    $t->pon_campo("id_site", new Numero(), true);
    $t->pon_campo("id_cc_0", new Numero(), true);
    $t->pon_campo("tipo");
    $t->pon_campo("nome");
    $t->pon_campo("id_almacen", new Numero());

    $this->pon_taboa($t);
 }
}


//*************************************************


final class CampoCustom_1_obd extends  Obxeto_bd {

  public function __construct($id_site, $id_cc_0, $id_cc_1 = null) {
    parent::__construct();

    $this->atr("id_site")->valor = $id_site;
    $this->atr("id_cc_0")->valor = $id_cc_0;
    $this->atr("id_cc_1")->valor = $id_cc_1;
  }

  public function mapa_bd() {
    return new CampoCustom_1_mbd();
  }

  public static function inicia(FS_cbd $cbd, $id_site, $id_cc_0, $id_cc_1 = null) {
    $p = new CampoCustom_1_obd($id_site, $id_cc_0, $id_cc_1);

    if ($id_cc_1 == null) return $p;

    $wh_s = $p->atr("id_site")->sql_where($cbd);
    $wh_0 = $p->atr("id_cc_0")->sql_where($cbd);
    $wh_1 = $p->atr("id_cc_1")->sql_where($cbd);

    $p->select($cbd, "{$wh_s} and {$wh_0} and {$wh_1}");

    return $p;
  }

  public function select(FS_cbd $cbd, $where = null, $orderby = null) {
    $r = $cbd->consulta($this->sql_select($cbd, $where, $orderby));

    if (!$a = $r->next()) return;

    $this->post($a);
  }

  public function insert(FS_cbd $cbd) {
    if (!$this->calcula_id  ($cbd)) return false;
    if (!$this->calcula_orde($cbd)) return false;

    return $cbd->executa($this->sql_insert($cbd));
  }

  public function update(FS_cbd $cbd) {
    if ($this->atr("id_cc_1")->valor == null) return $this->insert($cbd);

    return $cbd->executa($this->sql_update($cbd));
  }

  public function delete(FS_cbd $cbd) {
    return $cbd->executa($this->sql_delete($cbd));
  }
/*
  public function tipo(FS_cbd $cbd = null) {
    if ($cbd == null) $cbd = new FS_cbd();

    $wh_s = $this->atr("id_site")->sql_where($cbd);
    $wh_0 = $this->atr("id_cc_0")->sql_where($cbd);

    $r = $cbd->consulta("select tipo from campocustom_0 where {$wh_s} and {$wh_0}");

    if ($_r = $r->next()) return null;

    return $_r["tipo"];
  }
*/

  public static function _orde(int $id_site, int $id_cc_0, FS_cbd $cbd = null) {
    if ($cbd == null) $cbd = new FS_cbd();

    $r = $cbd->consulta("select id_cc_1, orde from campocustom_1 where id_site={$id_site} and id_cc_0={$id_cc_0} order by orde");

    $a2 = null;
    while ($a = $r->next()) $a2[ $a['id_cc_1'] ] = $a['orde'];


    return $a2;
  }


  private function calcula_orde(FS_cbd $cbd):bool {
    $k1 = $this->atr("id_site")->valor;
    $k2 = $this->atr("id_cc_0")->valor;
    
    $r = $cbd->consulta("select ifnull(max(orde), 0) as orde from campocustom_1 where id_site = {$k1} and id_cc_0 = {$k2}");
    
    if (!$_r = $r->next()) return false;
    
    
    $this->atr("orde")->valor = $_r["orde"] + 1;

    return true;
  }

  private function calcula_id(FS_cbd $cbd) {
    $id_site = $this->atr("id_site")->valor;
    $id_cc_0 = $this->atr("id_cc_0")->valor;

    if (!$cbd->executa("insert into campocustom_1_seq (id_site, id_cc_0) values ({$id_site}, {$id_cc_0})")) return false;

    $r = $cbd->consulta("select LAST_INSERT_ID() as id");

    if (!$a = $r->next()) return false;

    $cbd->executa("delete from campocustom_1_seq where id_site = {$id_site} and id_cc_0 = {$id_cc_0} and id_cc_1 < {$a['id']}");

    $this->atr("id_cc_1")->valor = $a['id'];

    return true;
  }
}

//-----------------------------------------------

final class CampoCustom_1_mbd extends Mapa_bd {
  public function __construct() {
    $t = new Taboa_dbd("campocustom_1");

    $t->pon_campo("id_site", new Numero(), true);
    $t->pon_campo("id_cc_0", new Numero(), true);
    $t->pon_campo("id_cc_1", new Numero(), true);
    $t->pon_campo("orde"   , new Numero());
    $t->pon_campo("nome");
    $t->pon_campo("notas");

    $this->pon_taboa($t);

    $t2 = new Taboa_dbd("campocustom_0");

    $t2->pon_campo("tipo");

    $this->relacion_fk($t2, array("id_site", "id_cc_0"), array("id_site", "id_cc_0"));
 }
}


//*************************************************


final class Articulo_cc_obd {
  public static function html_articulo(FS_cbd $cbd, $id_site, $id_articulo) {
    $sql = "select *
              from v_grupo_cc
             where id_site = {$id_site} and id_articulo = {$id_articulo}
             order by id_cc_0";


    $r = $cbd->consulta($sql);

    $html = null;
    while ($_r = $r->next()) {
      if ($_r["tipo"] == "color")
        $v = $_r["notas"];
      else
        $v = $_r["valor"];

      $html .= "<div>&bull;&nbsp;<b>{$_r['campo']}</b>:&nbsp;{$v}</div>";
    }

    return "<div style='font-size: 90%; text-align: left;'>{$html}</div>";
  }

  public static function update(FS_cbd $cbd, $id_site, $id_articulo, $id_cc_0, $id_cc_1) {
    $sql = "update articulo_cc
               set id_cc_1 = {$id_cc_1}
             where id_site = {$id_site} and id_articulo = {$id_articulo} and id_cc_0 = {$id_cc_0}";

    if (!$cbd->executa($sql)) return false;


    return self::update_tupla($cbd, $id_site, $id_articulo);
  }

  public static function update_tupla(FS_cbd $cbd, $id_site, $id_articulo) {
    $t = self::tupla_html($id_site, $id_articulo, null, $cbd);


    $sql = "update articulo set tupla_cc = '{$t}' where id_site = {$id_site} and id_articulo = {$id_articulo}";


    return $cbd->executa($sql);
  }

  public static function cc_enUso(FS_cbd $cbd, $id_site, $id_cc_0, $id_cc_1 = null) {
    $wh_cc_1 = ($id_cc_1 == null)?"":" and a.id_cc_1 = {$id_cc_1}";

    //~ $sql = "select count(*) as ct
              //~ from articulo_cc
             //~ where id_site = {$id_site} and id_cc_0 = {$id_cc_0}{$wh_cc_1}";

    $sql = "select b.id_articulo, b.nome
              from articulo_cc a inner join articulo b on (a.id_site = b.id_site and a.id_articulo = b.id_articulo)
             where a.id_site = {$id_site} and a.id_cc_0 = {$id_cc_0}{$wh_cc_1}";

    $r = $cbd->consulta($sql);

    $_ = array("a" => null, "n" => null, "t" => 0);

    while ($_r = $r->next()) {
      if ($_["a"] == null) {
        $_['a'] = $_r['id_articulo'];
        $_['n'] = $_r['nome'];
      }

      $_["t"]++;
    }

    if ($_["t"] == 0) return null;


    return $_;
  }

  public static function _campo_disponible($id_site, $id_almacen, $id_grupo_cc, $predet = null, FS_cbd $cbd = null) {
    if ($cbd == null) $cbd = new FS_cbd();


    $_n = array();

    if ($predet != null) $_n["**"] = $predet;

    //* 1.- Busca campos disponibles
    $sql = "select id_cc_0, nome
              from campocustom_0
             where (id_site = {$id_site}) and (id_almacen in (1, {$id_almacen}))
          order by id_cc_0";

    $r = $cbd->consulta($sql);

    while ($_r = $r->next()) $_n[ $_r['id_cc_0'] ] = $_r['nome'];


    if ($id_grupo_cc == null) return $_n;


    //* 2.- Busca valores del campo no usados
    $sql = "select id_cc_0
              from v_grupo_cc
             where id_site = {$id_site} and id_grupo_cc = {$id_grupo_cc}";

    $r = $cbd->consulta($sql);

    while ($_r = $r->next()) unset( $_n[ $_r['id_cc_0'] ] ); //* Elimina valores usados.



    return $_n;
  }

  public static function hai_tupla($id_site, $id_grupo_cc, $_t, FS_cbd $cbd = null) {
    if ( !is_array($_t) ) return false;

    if (($ct_t = count($_t)) == 0) return false;

    if ($cbd == null) $cbd = new FS_cbd();


    $w_t = null;
    foreach ($_t as $id_cc_0=>$id_cc_1) {
      if ($w_t != null) $w_t .= " or ";

      $w_t .= "(id_cc_0 = {$id_cc_0} and id_cc_1 = {$id_cc_1})";
    }

    $sql = "select id_articulo, count(id_articulo) as ct_t
              from v_grupo_cc
             where (id_site = {$id_site}) and (id_grupo_cc = {$id_grupo_cc}) and ({$w_t})
             group by id_articulo
             having ct_t = {$ct_t}";

    $r = $cbd->consulta($sql);

    if ( $_r = $r->next() ) return $_r["id_articulo"];


    return null;
  }

  public static function _valor($id_site, $id_cc_0, $predet = null, FS_cbd $cbd = null) {
    $_ = null;
    if ($predet != null) $_["**"] = $predet;

    if ($id_cc_0 == null) return $_;

    if ($cbd == null) $cbd = new FS_cbd();

    $sql = "select a.id_cc_0, b.tipo, a.id_cc_1, a.nome, a.notas
              from campocustom_1 a inner join campocustom_0 b on (a.id_site = b.id_site and a.id_cc_0 = b.id_cc_0)
             where a.id_site = {$id_site} and a.id_cc_0 = {$id_cc_0}
             order by a.orde";

    $r = $cbd->consulta($sql);

    while ($_r = $r->next()) {
      $_[ $_r['id_cc_1'] ] = ($_r['tipo'] == "color")?$_r['notas']:$_r['nome' ];
    }

//~ echo "<pre>" . print_r($_, true) . "</pre>";

    return $_;
  }

  public static function _tupla($id_site, $id_articulo, $id_cc_0 = null, FS_cbd $cbd = null) {
    if ($cbd == null) $cbd = new FS_cbd();

    $wh_0 = ""; if ($id_cc_0     != null) $wh_0 = " and id_cc_0 = {$id_cc_0}";
    $wh_1 = ""; if ($id_articulo != null) $wh_1 = " and id_articulo = {$id_articulo}";

    $sql = "select id_cc_0, tipo, campo, id_cc_1, valor, notas
              from v_grupo_cc
             where id_site = {$id_site} {$wh_1} {$wh_0}
             order by orde";

    $r = $cbd->consulta($sql);

    $_ = null;
    while ($_r = $r->next()) {
      $v = ($_r['tipo'] == 'color')?$_r['notas']:$_r['valor'];

      $_[ $_r['id_cc_0'] ] = array($_r['id_cc_1'], $v, $_r['campo']);
    }


    return $_;
  }

  public static function tupla_html($id_site, $id_articulo, $id_cc_0 = null, FS_cbd $cbd = null) {
    $_t = self::_tupla($id_site, $id_articulo, $id_cc_0, $cbd);

    if (!is_array($_t))  return "";

    if (count($_t) == 0) return "";

    $t0 = "";
    foreach ($_t as $id_cc_0 => $_cc_1) {
      if ($t0 != "") $t0 .= ", ";

      $t0 .= $_cc_1[1];
    }


    return "{{$t0}}";
  }

  public static function insert(FS_cbd $cbd, $id_site, $id_articulo, $id_cc_0, $id_cc_1, $id_grupo_cc) {
    $sql = "insert into articulo_cc (id_site, id_articulo, id_cc_0, id_cc_1)
                 values ({$id_site}, {$id_articulo}, {$id_cc_0}, {$id_cc_1})";

    if (!$cbd->executa($sql)) return false;


    if ($id_grupo_cc != null) return true; //* o artículo xa ten o un grupo_cc


    $sql = "update articulo set id_grupo_cc = {$id_articulo} where id_site = {$id_site} and id_articulo = {$id_articulo}";


    return $cbd->executa($sql);
  }

  public static function _grupo($id_site, $id_grupo_cc, $vpa, FS_cbd $cbd = null) {
    if ($id_grupo_cc == null) return array();


    if ($cbd == null) $cbd = new FS_cbd();


    $wh_u = "1"; if ($vpa != 1) $wh_u = "a.unidades >= a.pedido_min";

    $sql = "select a.id_cc_0, a.tipo, a.id_articulo, a.id_cc_1, a.campo, a.valor, a.notas, sum(a.unidades) as unidades 
              from v_grupo_cc a inner join articulo_publicacions e on (a.id_site = e.id_site and a.id_articulo = e.id_articulo)
             where (a.id_site = {$id_site}) and (a.id_grupo_cc = {$id_grupo_cc}) and ({$wh_u})
          group by a.id_cc_0, a.id_cc_1
          order by a.id_cc_0, a.orde";

    $r = $cbd->consulta($sql);
    
    
    $_0 = array();
    while ($_r = $r->next()) {
      $_0[ $_r["id_cc_0"] ][ "c" ] = $_r["campo"];
      $_0[ $_r["id_cc_0"] ][ "t" ] = $_r["tipo" ];
      
      $_0[ $_r["id_cc_0"] ][ "v" ][ $_r["id_cc_1"] ] = [ $_r["valor"], $_r["notas"], $_r["unidades"], $_r["id_articulo"] ];
    }


    return $_0;
  }
/*
  public static function _grupo($id_site, $id_grupo_cc, $vpa, $_t, FS_cbd $cbd = null) {
    if ($id_grupo_cc == null) return array();


    if ($cbd == null) $cbd = new FS_cbd();


    $wh_u = "1"; if ($vpa != 1) $wh_u = "a.unidades >= a.pedido_min";

    $sql = "select a.id_cc_0, a.tipo, a.id_articulo, a.id_cc_1, a.campo, a.valor, a.notas
              from v_grupo_cc a inner join articulo_publicacions e on (a.id_site = e.id_site and a.id_articulo = e.id_articulo)
             where (a.id_site = {$id_site}) and (a.id_grupo_cc = {$id_grupo_cc}) and ({$wh_u})
          order by a.id_cc_0, a.id_articulo";

    $r = $cbd->consulta($sql);

    if (is_array($_t)) array_pop($_t); //* eliminamos o derradeiro par {$id_cc_0 => $id_cc_1}

    $id_cc_aux = -1; //* id_cc_aux IN {-2, -1, id_cc_0}

    $_0 = array(); $_a_non = array();
    while ($_r = $r->next()) {
      if (($id_cc_aux != -2) && ($id_cc_aux != $_r["id_cc_0"])) {
        list($id_cc_0, $id_cc_1) = self::_grupo_tupla_shift($_t);

        $id_cc_aux = $id_cc_0;

        //~ echo "<pre style='color: #0a0;'>_t\n" . print_r($_t, true) . "</pre>";
        //~ echo "<pre style='color: #1a1;'>($id_cc_aux, id_cc_0, id_cc_1) = ($id_cc_aux, $id_cc_0, $id_cc_1)</pre>";
      }

//~ echo "<pre style='color: #0a0;'>_a_non\n" . print_r($_a_non, true) . "</pre>";
      if ( isset( $_a_non[$_r["id_articulo"]] ) ) continue;

//~ echo "<pre style='color: #0a0;'>_r\n" . print_r($_r, true) . "</pre>";
      if ($id_cc_aux > 0)
        if ( ($_r["id_cc_0"] == $id_cc_0) && ($_r["id_cc_1"] != $id_cc_1) ) $_a_non[$_r["id_articulo"]] = 1; //* marcamos id_articulo

      $_0[ $_r["id_cc_0"] ]["c"] = $_r["campo"];

      $_0[ $_r["id_cc_0"] ]["t"] = $_r["tipo"];

      $_0[ $_r["id_cc_0"] ]["v"][ $_r["id_cc_1"] ][0] = $_r["valor"];
      $_0[ $_r["id_cc_0"] ]["v"][ $_r["id_cc_1"] ][1] = $_r["notas"];
    }


    return $_0;
  }
*/
  public static function _grupo_resumo($id_site, $id_grupo_cc, bool $vpa, FS_cbd $cbd = null) {
    if ($id_grupo_cc == null) return [];


    if ($cbd == null) $cbd = new FS_cbd();


    //~ $wh_u = "1"; if ($vpa != 1) $wh_u = "a.unidades >= a.pedido_min";
    $wh_u = ($vpa)?"1":"a.unidades >= a.pedido_min";

    $sql = "select distinct a.valor, a.unidades, a.pedido_min, a.id_articulo
              from v_grupo_cc a inner join articulo_publicacions e on (a.id_site = e.id_site and a.id_articulo = e.id_articulo)
             where (a.id_site = {$id_site}) and (a.id_grupo_cc = {$id_grupo_cc}) and (a.tipo = '0') and ({$wh_u})
          order by a.orde";

    $r = $cbd->consulta($sql);

    $_0 = []; while ($_r = $r->next()) $_0[ $_r["valor"] ] = ["u" => $_r["unidades"], "m" => $_r["pedido_min"], "a" => $_r["id_articulo"]];


    return $_0;
  }

  private static function _grupo_tupla_shift(&$_t) {
    if (count($_t) == 0) return array(-2, -2);

    foreach($_t as $id_cc_0 => $id_cc_1) break;

    array_shift($_t);

    return array($id_cc_0, $id_cc_1);
  }
}
