<?php


abstract class Paxina_obd extends    Obxeto_bd
                          implements ICLogo, IMenuSite_paxina, IMetatags {

  const logo_width = 61;

  public function __construct($tipo, $id_paxina = null) {
    parent::__construct();

    $this->atr("id_paxina"     )->valor = $id_paxina;

    $this->atr("tipo"          )->valor = $tipo;
    $this->atr("visible"       )->valor = 1;
    $this->atr("reservada"     )->valor = "0";
    $this->atr("logo"          )->valor = null;
    $this->atr("estado"        )->valor = "publicada";
    $this->atr("id_prgf_indice")->valor = -1;
    $this->atr("id_comentarios")->valor = 0;
  }

  abstract public function insert(FS_cbd $cbd, $id_idioma);
  abstract public function duplicar(FS_cbd $cbd, $id_idioma, ?string $refsite = null);

  public static function inicia(FS_cbd $cbd, $id_paxina = null, $tipo = null) {
    if ($tipo == null) $tipo = self::tipo($cbd, $id_paxina);

    $p = self::factory($tipo, $id_paxina);

    if ($id_paxina == null) return $p;

    $p->select($cbd, $p->atr("id_paxina")->sql_where($cbd));

    return $p;
  }

  public static function inicia_md5(FS_cbd $cbd, $id_site, $id_oms, $id_idioma, $s = null) {
    $r = $cbd->consulta("select tipo, id_paxina from v_site_paxina where id_site = {$id_site} and id_oms = {$id_oms} and id_idioma = '{$id_idioma}' and md5 = '{$s}'");

    if (!$_a = $r->next()) {
      $r = $cbd->consulta("select tipo, id_paxina from v_site_paxina where id_site = {$id_site} and id_oms = {$id_oms} and md5 = '{$s}'");

      if (!$_a = $r->next()) return null;
    }


    return Paxina_obd::inicia($cbd, $_a['id_paxina'], $_a['tipo']);
  }

  public static function inicia_url(FS_cbd $cbd, $url, Site_obd $s) {
    $a_url = parse_url($url);

    if ( !isset($a_url['host']) ) return null;

    $a_url['host'] = str_replace("www.", "", $a_url['host']);

    if ( isset($a_url['query']) ) if ($a_url['query'] != null) $a_query = explode("=", $a_url['query']);

    if ( isset($a_url['path']) ) if ($a_url['path'] != null) $action = end(explode("/", trim($a_url['path'], "/")));

    //* comprobamos se temos un posible link a unha paxina do site.

    if (TrwlConfig::dominio != $a_url['host']) return null;
        //~ if (TrwlConfig::dominio == "{$a_url['host']}/triwus"); //* control local
    //~ elseif ($s->atr("dominio")->valor != $a_url['host']) return null;


    //* buscamos na BD a paxina apuntada no link.
    
    $id_site = $s->atr("id_site")->valor;

    $l = ($a_query[0] != "l")?null:$a_query[1];
    $k = ($a_query[0] != "k")?null:$a_query[1];

    //* buscamos a paxina segundo o parametro k
    if ($k != null) {
      $p = Paxina_obd::inicia($cbd, $k);

      if ($p->atr("id_site")->valor == $id_site) return $p;
    }

    if ($action == null) {
      $l2 = ($l == null)?-1:$l;

      $id_oms = Opcion_ms_obd::primeira($cbd, $id_site, $l2);

      return Efspax_xestor::__paxobd($cbd, $id_oms, null, $l);
    }

    //* action != null. control de url's limpias.
    if (substr($action, -4) != ".php") {
      if (substr($action, -1) == "/") $action = substr($action, 0, -1);

      $action .= ".php";
    }

    //* buscamos na BD cruzando id_site e action
    $where = "where id_site = {$id_site} and action = '{$action}'";

    if ($l != null) $where .= " and id_idioma = '{$l}'";

    $r = $cbd->consulta("select id_paxina from v_site_paxina {$where}");

    if (!$a = $r->next()) return null;

    return Paxina_obd::inicia($cbd, $a['id_paxina']);
  }

  public function inicia_idioma(FS_cbd $cbd, $id_idioma, $where = null) {
    if ($id_idioma == null) return null;

    $p = self::factory($this->atr("tipo")->valor);

    $p->atr("id_opcion")->valor = $this->atr("id_opcion")->valor;
    $p->atr("id_idioma")->valor = $id_idioma;


    if ($cbd == null) $cbd = new FS_cbd();

    $wh_o = $p->atr("id_opcion")->sql_where($cbd);
    $wh_i = $p->atr("id_idioma")->sql_where($cbd);

    if ($where != null) $where .= " and {$wh_o} and {$wh_i}";

    $p->select($cbd, $where);

    return $p;
  }

  public function select(FS_cbd $cbd, $where = null, $orderby = null) {
    $r = $cbd->consulta($this->sql_select($cbd, $where, $orderby));

    if (!$a = $r->next()) return;

    $this->post($a);
  }

  public function select_pai(FS_cbd $cbd = null) {
    if ($cbd == null) $cbd = new FS_cbd();

    if (($id_pai = $this->atr("pai")->valor) == null) return null;

    $sql_inner_2 = "";
    if (($id_idioma = $this->atr("id_idioma")->valor) != null) $sql_inner_2 = "inner join v_ml_paxina c on (a.id_paxina = c.id_paxina and c.id_idioma = '{$id_idioma}')";

    $sql = "select a.id_paxina as id_paxina
              from paxina a {$sql_inner_2}
             where id_opcion = {$id_pai}";

    $r = $cbd->consulta($sql);

    if (!$a = $r->next()) return null;

    return self::inicia($cbd, $a['id_paxina']);
  }

  public function select_ant(FS_cbd $cbd = null) {
    if ($cbd == null) $cbd = new FS_cbd();

    $id_pai = $this->atr("pai")->valor();

    $wh_s   = "id_site = " . $this->atr("id_site")->valor();
    $wh_pai = ($id_pai == null)?"pai is null":"pai = {$id_pai}";
    $wh_pos = "posicion < " . $this->atr("posicion")->valor();


    $r = $cbd->consulta("select id_paxina from v_site_opcion where {$wh_s} and {$wh_pai} and {$wh_pos} order by posicion desc");

    if (!$a = $r->next()) return null;

    return self::inicia($cbd, $a['id_paxina']);
  }

  public function select_seg(FS_cbd $cbd = null) {
    if ($cbd == null) $cbd = new FS_cbd();

    $id_pai = $this->atr("pai")->valor();

    $wh_s   = "id_site = " . $this->atr("id_site")->valor();
    $wh_pai = ($id_pai == null)?"pai is null":"pai = {$id_pai}";
    $wh_pos = "posicion > " . $this->atr("posicion")->valor();


    $r = $cbd->consulta("select id_paxina from v_site_opcion where {$wh_s} and {$wh_pai} and {$wh_pos} order by posicion");

    if (!$a = $r->next()) return null;

    return self::inicia($cbd, $a['id_paxina']);
  }

  public function update(FS_cbd $cbd, $dactu = true) {
    if ($this->atr("id_opcion")->valor == 0) die("Paxina_obd.update(), (this.atr('id_opcion').valor == 0)");

    if ($dactu) $this->atr("actualizado")->valor = date("YmdHis");

    if (!$cbd->executa($this->sql_update($cbd))) return false;

    return $this->update_md5($cbd);
  }

  public function a_grupo() {
    return Opcion_ms_obd::explode_grupo($this->atr("grupo")->valor);
  }

  public function delete(FS_cbd $cbd) {
    $id_paxina = $this->atr("id_paxina")->valor;

    //* multilinguaxe
    if ($this->atr("id_ml")->valor != null) {
      $ml = MLpaxina_obd::inicia($cbd, $this->atr("id_ml")->valor);

      if (!$ml->delete($cbd)) return false;
    }

    //* detalle
    if (($d = $this->detalle_obd($cbd)) != null) { if (!$d->delete($cbd)) return false; }


    //* stats
    if (!$cbd->executa("delete from stats_visita where id_paxina = {$id_paxina}")) return false;
    if (!Nube_obd::delete($id_paxina, $cbd)) return false;

    //* carrusel_iten
    if (!$cbd->executa("delete from elmt_carrusel_iten where id_paxina = {$id_paxina}")) return false;


    //* borra logo, (se fora preciso).

    //* paxina
    return $cbd->executa($this->sql_delete($cbd));
  }

  public function publicada():bool {
    return self::calcula_publicada($this->atr("estado"  )->valor, 
                                   $this->atr("publicar")->fvalor("YmdHis")
                                  );
  }

  public static function publicada_imspax(IMenuSite_paxina $imsp):bool {
    return self::calcula_publicada($imsp->atr("estado"  )->valor, 
                                   $imsp->atr("publicar")->fvalor("YmdHis")
                                  );
  }

  public function detalle_obd(FS_cbd $cbd = null) {
    if ($this->atr("id_paxina")->valor == null) return null;

    if ($cbd == null) $cbd = new FS_cbd();

    return Detalle_obd::inicia($cbd, $this->atr("id_paxina")->valor);
  }

  public function ml_obd() {
    if ($this->atr('id_idioma')->valor == null) return null;

    $ml =  new MLpaxina_obd($this->atr('id_idioma')->valor,
                            $this->atr('id_paxina')->valor,
                            $this->atr('id_sincro')->valor);

    $ml->atr("id_ml")->valor = $this->atr('id_ml')->valor;

    return $ml;
  }

  public function site(FS_cbd $cbd = null) {
    if ($cbd == null) $cbd = new FS_cbd();

    return Site_obd::inicia($cbd, $this->atr("id_site")->valor);
  }

  public function action($completo = false) {
    $a = $this->atr("action")->valor;

    if (!$completo) return $a;

    $s = $this->atr("id_site"  )->valor;
    $p = $this->atr("id_paxina")->valor;
    $l = $this->atr("id_idioma")->valor;


    return Efs::url_paxina($s, $a, $p, $l);
  }

  public function action_2($limpa = false, $completa = false) {
    $s = $this->atr("id_site"  )->valor;
    $a = $this->atr("action"   )->valor;
    $k = $this->atr("id_paxina")->valor;
    $l = $this->atr("id_idioma")->valor;

    //* url base.
    if (!$limpa) {
      if (!$completa) return "{$a}?k={$k}";

      $url_0 = $this->action_site();

      return "{$url_0}{$a}?k={$k}";
    }
    
    //* url custom.
    //~ if (($url = $this->action_custom($completa)) != null) return $url;
    
    
    //* url limpa.
    $a = str_replace(".php", "", $a);

    $i = $this->atr("id_prgf_indice")->valor;

    $href = null;

    if ($i > 0) {
      $href = "{$a}/{$l}/";
    }
    elseif ($i == -1) {
      $href = "{$a}/{$l}/";
    }
    else {
      $n =  $this->atr("md5")->valor;

      $href = "{$a}/{$n}/";
    }

    if (!$completa) return $href;

    $url_0 = $this->action_site();

    return "{$url_0}{$href}";
  }

  public function action_custom($completa = false):?string {
    if (($href = $this->atr("url_custom")->valor) == null) return null;
    
    if (!$completa) return $href;

    $url_0 = $this->action_site();

    return "{$url_0}{$href}";
  }

  public function action_site():?string {
    //~ return Efs::url_paxina($this->atr("id_site")->valor, null, null, null, null, false);
    return Efs::url_base($this->atr("id_site")->valor);
  }
  
  public function iclogo_obd(FS_cbd $cbd, ?string $k = null) {
    if (($l = $this->iclogo_id($k)) == null) return null;

    return Elmt_obd::inicia($cbd, $l, Imaxe_obd::tipo_bd);
  }

  public function iclogo_id(?string $k = null) {
    return $this->atr("logo")->valor;
  }

  public function iclogo_src(FS_cbd $cbd, ?string $k = null) {
    $id_paxina = $this->atr("id_paxina")->valor;


    $a_src = null;

    $sql = "select c.id_elmt
              from detalle a inner join paragrafo_elmt b on (a.id_paragrafo = b.id_paragrafo),
                   elmt_imaxe c
             where b.id_elmt = c.id_elmt and a.id_paxina = {$id_paxina}
            union
            select d.id_logo as id_elmt
              from detalle a inner join paragrafo_elmt b on (a.id_paragrafo = b.id_paragrafo),
                   elmt_ventas c inner join articulo_imx d on (c.id_site = d.id_site and c.id_articulo = d.id_articulo)
             where b.id_elmt = c.id_elmt and a.id_paxina = {$id_paxina} and (not d.id_logo is null)
            union
            select c.id_elmt
              from detalle a inner join paragrafo_elmt b ON (a.id_paragrafo = b.id_paragrafo),
                   elmt_carrusel_iten c
             where b.id_elmt = c.id_elmt and a.id_paxina = {$id_paxina}";

    $r = $cbd->consulta($sql);

    //~ if ($mini) $mini = self::logo_width;

    $a_src = null;
    while ($a = $r->next()) $a_src[$a['id_elmt']] = Elmt_obd::inicia($cbd, $a['id_elmt']);


    return $a_src;
  }

  public function iclogo_update(FS_cbd $cbd, $src) {
    //* solo se usa desde Paxinfo.
    //* o logo da paxina será un Imaxe_obd dedicado.



    if (($l = $this->iclogo_obd($cbd)) == null) {
      $l = new Imaxe_obd($src);

      if (!$l->insert($cbd)) return false;
    }
    elseif ($l->atr("tipo")->valor != Imaxe_obd::tipo_bd) {
      $l = new Imaxe_obd($src);

      if (!$l->insert($cbd)) return false;
    }
    else {
      $sql = "select id_elmt from paragrafo_elmt where id_elmt = " . $this->iclogo_id();

      $r = $cbd->consulta($sql);

      if (!$_a = $r->next()) {
        //* o logo é un Imaxe_obd asciado a un Elmt_imaxe. Creamos unha instancia propia para a páxina.
  
        $l = new Imaxe_obd($src);

        if (!$l->insert($cbd)) return false;
      }
      else {
        $l->post_url($src);

        if (!$l->update($cbd)) return false;
      }
    }

    //* actualiza o logo na paxina_obd.

    $id_p = $this->atr("id_paxina")->valor;
    $id_l = $l   ->atr("id_elmt"  )->valor;


    $this->atr("logo")->valor = $id_l;


    return $cbd->executa( "update paxina set logo={$id_l} where id_paxina = {$id_p}" );
  }

  public function iclogo_delete(FS_cbd $cbd) {
    //* borra o logo na paxina_obd.

    $id_p = $this->atr("id_paxina")->valor;


    $this->atr("logo")->valor = null;


    return $cbd->executa( "update paxina set logo=null where id_paxina = {$id_p}" );
  }

  public function _metatags($url_limpa, FS_cbd $cbd = null) {  //* IMPLEMENTA IMetatags._metatags()
    if ($cbd == null) $cbd = new FS_cbd();

    $tit = $this->atr("titulo")->valor;
    $des = $this->atr("descricion")->valor;
    $log = null;
    $rob = $this->atr("robots")->valor;
    $url = null;
    $idi = $this->atr("id_idioma")->valor;
    $can = $this->action_2($url_limpa);
    $script = $this->atr("script")->valor;

    if (($logo = $this->iclogo_obd($cbd)) != null) $log = $logo->atr("url")->valor;


    return array($tit, $des, $log, $rob, $url, $idi, $can, $script);
  }

    
  public function a_migas($url_limpa = false, FS_cbd $cbd = null):array {
    if ($cbd == null) $cbd = new FS_cbd();
    
    $paux = $this;

    if ($paux->atr("id_paxina")->valor == null) return [];

    $_a0 = [];
    do {
//~ echo "<pre>" . print_r($paux->a_resumo(), 1) . "</pre>";

      $action = ($paux->atr("estado")->valor == "publicada")?$paux->action_2($url_limpa, true):null;
      
      $_a1 = array($paux->atr("id_paxina")->valor, $paux->atr("nome")->valor, $action);

      array_unshift($_a0, $_a1);

      $paux = $paux->select_pai($cbd);
    }
    while ($paux != null);


    return $_a0;
  }



  final public static function factory($tipo, $id_paxina = null) {
//~ echo "($tipo, $id_paxina)<br>";
    switch ($tipo) {
      case "novas"  : return new Paxina_novas_obd  ($id_paxina);
      case "novas2" : return new Paxina_novas2_obd ($id_paxina);
      case "marcas" : return new Paxina_marcas_obd ($id_paxina);
      case "ventas" : return new Paxina_ventas_obd ($id_paxina);
      case "ventas2": return new Paxina_ventas2_obd($id_paxina);
      case "libre"  : return new Paxina_dl_obd     ($id_paxina);
      case "legal"  : return new Paxina_legal_obd  ($id_paxina);

      case "ref"  : {
        $cbd = new FS_cbd();

        $id_ref   = Paxina_ref_obd::id_ref($cbd, $id_paxina);
        $tipo_ref = self::tipo($cbd, $id_ref);

        if ($tipo_ref == "ref") die("Paxina_obd.factory() , tipo_ref == 'ref'");

        return self::factory($tipo_ref, $id_ref);
      }
    }
    
    
    if (Paxina_reservada_obd::test($tipo)) return new Paxina_reservada_obd($tipo, $id_paxina);
    
    

    die("Paxina_obd.factory() , tipo '{$tipo}' descoñecido.\n");
  }

  final protected function insert_base(FS_cbd $cbd, $id_idioma, Paragrafo_obd $prgf = null) {
    //* inserta unha páxina.
    $this->atr("id_paxina"  )->valor = null;
    $this->atr("id_puntos"  )->valor = null; //* en deshuso
    $this->atr("actualizado")->valor = date("YmdHis");
    $this->atr("creado"     )->valor = date("YmdHis");

    if ($this->atr("titulo")->valor == null) $this->atr("titulo")->valor = $this->atr("nome")->valor;

    if (!$cbd->executa($this->sql_insert($cbd))) return false;

    $this->calcula_id($cbd);

    if (!$this->update_md5($cbd)) return false;


    //* inserta multilinguaxe
    if ($id_idioma == null) $id_idioma = $this->atr("id_idioma")->valor;

    if ($id_idioma != null) {
      $ml = new MLpaxina_obd($id_idioma, $this->atr('id_paxina')->valor, $this->atr('id_sincro')->valor);

      if (!$ml->insert($cbd)) return false;

      $this->atr("id_ml")->valor = $ml->atr("id_ml")->valor;
    }

    //* inserta detalle
    if ($prgf == null) return true;


    return $this->insert_detalle($cbd, $prgf);
  }

  final protected function insert_comentarios(FS_cbd $cbd) {
    $prgf = new Prgf_comentarios_obd();

    if (!$prgf->insert($cbd)) return false;

    $this->atr("id_comentarios")->valor = $prgf->atr("id_paragrafo")->valor;

    return $cbd->executa($this->sql_update($cbd));
  }

  final protected function insert_detalle(FS_cbd $cbd, Paragrafo_obd $prgf = null, $posicion = null) {
    $d = new Detalle_obd();

    $d->atr("id_paxina")->valor = $this->atr("id_paxina")->valor;
    $d->atr("posicion") ->valor = $posicion;

    return $d->insert($cbd, $prgf);
  }

  final protected function calcula_id(FS_cbd $cbd) {
    $r = $cbd->consulta("select LAST_INSERT_ID() as id");

    if (!$a = $r->next()) die("Paxina_obd.calcula_id(), (!a = r.next())");

    $this->atr("id_paxina")->valor = $a['id'];
  }

  final public static function tipo(FS_cbd $cbd, $id_paxina = null) {
    $tipo = "libre";

    if ($id_paxina == null) return $tipo;

    $r = $cbd->consulta("select tipo from paxina where id_paxina = {$id_paxina}");

    if ($a = $r->next()) return $a['tipo'];

    return $tipo;
  }

  final public static function __md5($s) {
    return str_replace(array("#", "+", "%", "?", chr(38), chr(47), "'", " "), array("", "", "", "", "", "", "`", "-"), $s);
  }

  final public function update_md5(FS_cbd $cbd) {
    //* debemos usar esta función despois de actualizar o nome dunha páxina.
    $o = $this->atr("id_opcion")->valor;
    $p = $this->atr("id_paxina")->valor;

    $md5 = self::__md5($this->atr("nome")->valor);

    $sql = "select id_paxina
              from paxina
             where id_opcion={$o} and id_paxina<>{$p} and md5 = '{$md5}'
             limit 0, 1";

    $r = $cbd->consulta($sql);

    if ($r->next()) $md5 .= "-{$p}";

    $this->atr("md5")->valor = $md5;

    return $cbd->executa("update paxina set md5='{$md5}' where id_paxina={$p}");
  }

  final protected static function calcula_publicada(string $estado, ?string $dpub):bool {
    if ($estado == "borrador") return false;

    if ($dpub == null) return true;
    
     
    return (date("YmdHis") > $dpub);
  } 
}

//--------------------------------------

class Paxina_mbd extends Mapa_bd {
  public function __construct() {
    parent::__construct();

    $t = new Taboa_dbd("paxina");

    $t->pon_campo("id_paxina"     , new Numero()     , true);
    $t->pon_campo("id_opcion"     , new Numero()           );
    $t->pon_campo("titulo"                                 );
    $t->pon_campo("cat_h1"                                 ); //* En principio so o usarán páxinas que usen uun catálogo.
    $t->pon_campo("nome"                                   );
    $t->pon_campo("md5"                                    );
    $t->pon_campo("robots"                                 );
    $t->pon_campo("descricion"                             );
    $t->pon_campo("estado"                                 );
    $t->pon_campo("tipo"                                   );
    $t->pon_campo("url_custom"                             );
    $t->pon_campo("visible"       , new Numero()           );
    $t->pon_campo("reservada"     , new Numero()           );
    $t->pon_campo("id_puntos"     , new Numero()           );
    $t->pon_campo("logo"          , new Numero()           );
    $t->pon_campo("script"        , new Varchar(true)      );
    $t->pon_campo("creado"        , new Data("YmdHis")     );
    $t->pon_campo("actualizado"   , new Data("YmdHis")     );
    $t->pon_campo("publicar"      , new Data("Y-m-d H:i:s"));
    $t->pon_campo("id_prgf_indice", new Numero()           );
    $t->pon_campo("id_comentarios", new Numero()           );

    $this->pon_taboa($t);


    $t1 = new Taboa_dbd("menu_site_opcion");

    $t1->pon_campo("id_ms", new Numero(), false, "id_site");
    $t1->pon_campo("action");
    $t1->pon_campo("grupo");
    $t1->pon_campo("posicion", new Numero());
    $t1->pon_campo("pai", new Numero());

    $this->relacion_fk($t1, array("id_opcion"), array("id_oms"), "inner");


    $t3 = new Taboa_dbd("v_ml_paxina");

    $t3->pon_campo("id_ml");
    $t3->pon_campo("id_idioma");
    $t3->pon_campo("id_sincro");

    $this->relacion_fk($t3, array("id_paxina"), array("id_paxina"), "left");
  }
}
