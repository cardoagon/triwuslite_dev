<?php


final class Site_obd extends Obxeto_bd {
  private $config       = null;
  private $config_mobil = null;

  public function __construct() {
    parent::__construct();
  }

  public function mapa_bd() {
    return new Site_mbd();
  }

  public function baleiro():bool {
    return ($this->atr("id_site")->valor == null);
  }

  public function config_obd($tipo = "escritorio", FS_cbd $cbd = null) {
    if ($cbd == null) $cbd = new FS_cbd();

    $id_config = $this->atr("id_config")->valor;

    if ($tipo == "escritorio") {
      if ($this->config == null) $this->config = new Config_obd($id_config, $tipo);

      return $this->config;
    }

    //* solicita version mobil

    if ($this->config_mobil != null) return $this->config_mobil;

    $this->config_mobil = new Config_obd($id_config, $tipo);

    if ( $this->config_mobil->__nula() ) $this->config_mobil = new Config_obd($id_config, "escritorio");

    return $this->config_mobil;
  }

  public function venta(FS_cbd $cbd = null) {
    $c = $this->config_obd("escritorio", $cbd);
    
    return $c->__venta_online();
  }

  public function usuario_obd(FS_cbd $cbd = null, $pcontrol = true) {
    if ($cbd == null) $cbd = new FS_cbd();

    $u = ($pcontrol)?new FGS_usuario():new UsuarioWeb_obd();

    $wh_a = $this->atr("id_site")->sql_where($cbd);
    //~ $wh_b = "(id_servizo is not null)";

    $u->select($cbd, "{$wh_a}");

    return $u;
  }

  public function admin(FS_cbd $cbd = null):?FGS_usuario {
    if (($id_site = $this->atr("id_site")->valor) == null) return null;
    
    if ($cbd == null) $cbd = new FS_cbd();

    $sql = "select id_usuario from usuario where id_site = {$id_site} and permisos = 'admin'";
    
    $r = $cbd->consulta($sql);
    
    if (!$_r = $r->next()) return null;
    
    
    return FGS_usuario::inicia($cbd, $_r["id_usuario"]);
  }

  public static function inicia(FS_cbd $cbd, int $id_site, bool $baixa = false) {
    $s = new Site_obd();

    $s->select($cbd, "site.id_site = {$id_site}", null, $baixa);

    return $s;
  }

  public function select(FS_cbd $cbd, $where = null, $orderby = null, $baixa = false) {
    $this->config = null;

    $r = $cbd->consulta($this->sql_select($cbd, $where, $orderby));

    if (!$a = $r->next()) return null;

    $this->post($a);
  }

  public function update(FS_cbd $cbd, $a_oms_obd = null) {
    if (($ms = $this->menu_site($cbd)) != null) if (!$ms->update($cbd, $a_oms_obd)) return false;

    $this->atr("actualizado")->valor = date("YmdHis");

    return $cbd->executa($this->sql_update($cbd));
  }

  public function update_baixa(FS_cbd $cbd) {
    $this->atr("baixa")->valor = date("YmdHis");

    return $cbd->executa($this->sql_update($cbd));
  }

  public function insert(FS_cbd $cbd) {
    //* inserta prgf_cabeceira.
    $cab = new Cab_obd( $this->atr("nome")->valor );

    if (!$cab->insert($cbd)) return false;

    //* inserta prgf_pé.
    $pe = new Pe_obd();

    if (!$pe->insert($cbd)) return false;

    //* inserta un site.
    $this->atr("id_cab"     )->valor = $cab->atr("id_paragrafo")->valor;
    $this->atr("id_pe"      )->valor = $pe->atr("id_paragrafo")->valor;
    $this->atr("creado"     )->valor = date("YmdHis");
    $this->atr("actualizado")->valor = $this->atr("creado")->valor;

    if (!$cbd->executa($this->sql_insert($cbd))) return false;

    $this->calcula_id($cbd);

    $id_site = $this->atr("id_site")->valor;
    $dominio = TrwlConfig::dominio;

    //* inserta un site_config.
    if (!$cbd->executa("insert into site_config (id_site, url_limpa) values ({$id_site}, 1)")) return false;

    //* inserta a categoría raíz.
    if (!$cbd->executa("insert into categoria (id_site, id_categoria, nome, id_pai) values ({$id_site}, 1, 'Categoría raíz', 0)")) return false;
    if (!$cbd->executa("insert into categoria_seq (id_site, id_categoria) values ({$id_site}, 1)")) return false;

    //* inserta un site_plesk.
    if (!$cbd->executa("insert into site_plesk (id_site, dominio) values ({$id_site}, '{$dominio}')")) return false;


    //~ if (!$this->calcula_nome($cbd)) return false;


    return true;
  }

  public function delete(FS_cbd $cbd) {
    if (($id_site = $this->atr("id_site")->valor) == null) return true;

    //* multilinguaxe
    $r = $cbd->consulta("select id_ml from v_ml_site where id_site = {$id_site}");
    while ($_r = $r->next()) {
      $ml = MLsite_obd::inicia_ml($cbd, $_r["id_ml"]);

      if (!$ml->delete($cbd)) return false;
    }


    if (($cab = $this->cab($cbd)) != null) if (!$cab->delete($cbd)) return false;

    //~ if (($difucom = $this->elmt_difucom($cbd)) != null) if (!$difucom->delete($cbd)) return false;

    if (($pe  = $this->pe($cbd)) != null) if (!$pe->delete($cbd)) return false;

    if (($ms  = $this->menu_site($cbd)) != null) if (!$ms->delete($cbd)) return false;

    if (($a_oms = $ms->a_oms($cbd, -1, true)) == null) return true;

    foreach ($a_oms as $oms) if (!$oms->delete($cbd, -1, false)) return false;


    if (($mtgs  = $this->metatags_obd($cbd)) != null) if (!$mtgs->delete($cbd)) return false;

    //* if (($u = $this->usuario_obd($cbd)) != null) if (!$u->delete($cbd)) return false; //* non borramos o usuario, para manter as facturas

    if (!$cbd->executa("delete from stats_navegador where id_site = {$id_site}")) return false;
    if (!$cbd->executa("delete from site_plesk      where id_site = {$id_site}")) return false;
    if (!$cbd->executa("delete from site_config where id_site = {$id_site}")) return false;

    //~ if (!$cbd->executa("delete from servizo where id_usuario = " . $u->atr("id_usuario")->valor)) return false;

    if (!$cbd->executa($this->sql_delete($cbd))) return false;


    //* borra ml_site
    $_mli = MLsite_obd::__a_idiomas($id_site, 0, null, $cbd);
    
    if ( !is_array($_mli) ) return false; //* ao menos hai un idioma.

    foreach ($_mli as $id_idioma => $v) {
      $ml = MLsite_obd::inicia($cbd, $id_site, $id_idioma);
      
      if (!$ml->delete($cbd)) return false;
    }


    //* triwus negocio

    //* almacens
    $r = $cbd->consulta("select id_almacen from almacen where id_site = {$id_site}");
    while ($_r = $r->next()) {
      $al = Almacen_obd::inicia($cbd, $id_site, $_r["id_almacen"]);

      if (!$al->delete($cbd, false)) return false;
    }
    
    if (!$cbd->executa("delete from acceso  where id_site = {$id_site}")) return false;
    
    if (!$cbd->executa("delete from categoria  where id_site = {$id_site}")) return false;
    if (!$cbd->executa("delete from site_fpago where id_site = {$id_site}")) return false;
    if (!$cbd->executa("delete from almacen    where id_site = {$id_site}")) return false;

    if (!$cbd->executa("delete from articulo where id_site = {$id_site}")) return false;
    if (!$cbd->executa("delete from articulo_seq where id_site = {$id_site}")) return false;

    if (!$cbd->executa("delete from articulo_cc where id_site = {$id_site}")) return false;

    if (!$cbd->executa("delete from campocustom_0 where id_site = {$id_site}")) return false;
    if (!$cbd->executa("delete from campocustom_1 where id_site = {$id_site}")) return false;


    if (!$cbd->executa("delete from contacto where id_contacto in (select id_destino from pedido where id_site = {$id_site})")) return false;
    if (!$cbd->executa("delete from enderezo where not id_contacto in (select id_contacto from contacto)")) return false;

    if (!$cbd->executa("delete from pedido where id_site = {$id_site}")) return false;
    if (!$cbd->executa("delete from pedido_seq where id_site = {$id_site}")) return false;
    if (!$cbd->executa("delete from pedido_articulo where id_site = {$id_site}")) return false;
    if (!$cbd->executa("delete from pedido_fpago where id_site = {$id_site}")) return false;


    return true;
  }

  public function cab(FS_cbd $cbd = null) {
    if ($cbd == null) $cbd = new FS_cbd();

    if ($this->atr("id_cab")->valor == null) return null;

    return Cab_obd::inicia($cbd, $this->atr("id_cab")->valor);
  }

  public function pe(FS_cbd $cbd = null) {
    if ($cbd == null) $cbd = new FS_cbd();

    if ($this->atr("id_pe")->valor == null) return null;

    return Pe_obd::inicia($cbd, $this->atr("id_pe")->valor);
  }

  public function metatags_obd(FS_cbd $cbd = null) {
    if ($cbd == null) $cbd = new FS_cbd();

    return Site_metatags_obd::inicia($cbd, $this->atr("id_site")->valor);
  }

  public function menu_site(FS_cbd $cbd = null) {
    $id_site = $this->atr("id_site")->valor;

    if ($cbd == null) $cbd = new FS_cbd();

    $r = $cbd->consulta("select id_ms from menu_site where id_site = {$id_site}");

    if (!$a = $r->next()) return null;

    return Menu_site_obd::inicia($cbd, $a['id_ms']);
  }

  public static function u_login($id_site, $login, FS_cbd $cbd = null) {
    if ($cbd == null) $cbd = new FS_cbd();


    $sql = "select id_usuario from usuario where id_site = {$id_site} and login = '{$login}'";
    
    $r = $cbd->consulta($sql);
    
    if (!$_r = $r->next()) return null;
    
    
    return FGS_usuario::inicia($cbd, $_r["id_usuario"]);
  }

  public static function id_logo(FS_cbd $cbd, $id_site) {
    $sql = "select c.id_elmt
              from site a,
                   paragrafo_elmt b inner join elmt_imaxe c on (b.id_elmt = c.id_elmt)
             where a.id_cab  = b.id_paragrafo
               and a.id_site = {$id_site}";

    $r = $cbd->consulta($sql);

    if (!$a = $r->next()) return null;

    return $a['id_elmt'];
  }

  public static function valida_nome($ns0, $reserva) {
    //*** 0.- validación básica do nome de entrada.
    $ns0 = strtolower(trim($ns0));

    if (strlen($ns0) < 2) return array(-1, Erro::nome_site_corto, $ns0);

    if (($erro = Refs::valida_url_protexida($ns0)) != null) return array(-1, $erro, $ns0);


    //*** 1.- validación caracter a caracter.
    $cval = "abcdefghijklmnopqrstuvwxyz0123456789-_";

    $ns2 = "";
    for ($i = 0; $i < strlen($ns0); $i++) {
      if (strpos($cval, $ns0[$i]) === FALSE) continue;

      $ns2 .= $ns0[$i];
    }

    if ($ns2 == null) return array(-1, Erro::baleiro_titulo, $ns2);

    if (strlen($ns2) < 2) return array(-1, Erro::nome_site_corto, $ns2);

    if (($erro = Refs::valida_url_protexida($ns2)) != null) return array(-1, $erro, $ns2);

    if ($ns0 != $ns2) return array(-2, null, $ns2);


    //*** 2.- comproba/reserva directorio.
/*
    $dir_aux = Refs::url_sites . "/{$ns2}";

    if (is_dir($dir_aux)) return array(-1, Erro::nome_site_ocupado2, $ns2);

    if ($reserva) {
      if (!mkdir($dir_aux)) return array(-1, Erro::nome_site_ocupado2, $ns2);


    }
*/
    return array(1, null, $ns2); //* validación OK
  }

  private function calcula_id(FS_cbd $cbd) {
    $r = $cbd->consulta("select LAST_INSERT_ID() as id");

    if (!$a = $r->next()) die("Site_obd.calcula_id(), (!a = r.next())");

    $this->atr("id_site")->valor = $a['id'];
  }
}

//-------------------------------------------

final class Site_mbd extends Mapa_bd {
  public function __construct() {
    $t = new Taboa_dbd("site");

    $t->pon_campo("id_site", new Numero(), true);
    $t->pon_campo("nome");
    $t->pon_campo("id_config", new Numero());
    $t->pon_campo("id_cab", new Numero());
    $t->pon_campo("id_pe", new Numero());
    $t->pon_campo("creado", new Data("YmdHis"));
    $t->pon_campo("actualizado", new Data("YmdHis"));
    $t->pon_campo("baixa", new Data("YmdHis"));

    $this->pon_taboa($t);


    $t2 = new Taboa_dbd("site_config");

    $t2->pon_campo("https");
    $t2->pon_campo("url_limpa");

    $this->relacion_fk($t2, array("id_site"), array("id_site"), "left");


    //~ $t3 = new Taboa_dbd("site_plesk");

    //~ $t3->pon_campo("dominio");

    //~ $this->relacion_fk($t3, array("id_site"), array("id_site"), "left");
  }
}

//********************************************

final class Site_metatags_obd extends    Obxeto_bd
                              implements ICLogo  {

  public function __construct($id_site = null) {
    parent::__construct();

	$this->atr("id_site")->valor = $id_site;
    $this->atr("robots" )->valor = "noindex, nofollow";
  }

  public function mapa_bd() {
    return new Site_metatags_mbd();
  }

  public static function inicia(FS_cbd $cbd, $id_site = null) {
    $sm = new Site_metatags_obd($id_site);
    
    if ($id_site == null) return $sm;

    $sm->select($cbd, $sm->atr("id_site")->sql_where($cbd));

    return $sm;
  }

  public function baleiro() {
    return $this->atr("id_site")->valor == null;
  }

  public function iclogo_obd(FS_cbd $cbd, ?string $k = null) {
    if ( ($l = $this->iclogo_id($k)) == null) return null;

    return Elmt_obd::inicia($cbd, $l, Imaxe_obd::tipo_bd);
  }

  public function iclogo_id(?string $k = null) {
    return $this->atr("id_logo")->valor;
  }

  public function iclogo_src(FS_cbd $cbd, ?string $k = null) {
    return null;
  }

  public function iclogo_update(FS_cbd $cbd, $src) {
    //* solo se usa desde CMetatags.
    //* o logo do site será un Imaxe_obd dedicado.

//~ echo "src::{$src}<br>";

    if (($l = $this->iclogo_obd($cbd)) == null) {
      $l = new Imaxe_obd($src);

      if (!$l->insert($cbd)) return false;
    }
    else {
      $l->post_url($src);

      if (!$l->update($cbd)) return false;
    }

    $id_s = $this->atr("id_site")->valor;
    $id_l = $l   ->atr("id_elmt")->valor;


    $this->atr("id_logo")->valor = $id_l;


    return $cbd->executa( "update site_metatags set id_logo={$id_l} where id_site = {$id_s}" );
  }

  public function select(FS_cbd $cbd, $where = null, $orderby = null) {
    $r = $cbd->consulta($this->sql_select($cbd, $where, $orderby));

    if (!$a = $r->next()) return;

    $this->post($a);
  }

  public function update(FS_cbd $cbd) {
    return $cbd->executa($this->sql_update($cbd));
  }

  public function insert(FS_cbd $cbd) {
    return $cbd->executa($this->sql_insert($cbd));
  }

  public function delete(FS_cbd $cbd) {
    if (!$cbd->executa($this->sql_delete($cbd))) return false;

    if ($this->atr("titulo_ico")->valor == null) return true;

    if (($ico = $this->__ico($cbd)) == null) return true;

    return $ico->delete($cbd);
  }

  public function __ico(FS_cbd $cbd = null) {
    if ($cbd == null) $cbd = new FS_cbd();

    if ($this->atr("titulo_ico")->valor == null) return new Ico_obd();

    return Elmt_obd::inicia($cbd, $this->atr("titulo_ico")->valor);
  }

  public function __publicar(Paxina $p, IMetatags $imeta = null) {
    $cbd = new FS_cbd();

    $p->head->base = Efs::url_base($this->atr("id_site")->valor, $cbd);

    $_meta = [];
    
    if ($imeta != null) $_meta = $imeta->_metatags($this->atr("url_limpa")->valor == 1, $cbd); 
    
// echo "<pre>" . print_r($_meta, 1) . "</pre>";

    if ($p->head->icono == null) {
      if (($__ico = $this->__ico($cbd)) != null) $p->head->icono  = $__ico->url();
    }

    if ($_meta[6] != null) {
      $p->head->canonical = $p->head->base . $_meta[6];
      
      $p->head->pon_og("og:url"     , $p->head->canonical);
      $p->head->pon_og("twitter:url", $p->head->canonical);
    }
    else {
      $p->head->canonical = null;
      
      $p->head->pon_og("og:url"     , "");
      $p->head->pon_og("twitter:url", "");
    }

    if ($_meta[5] != null) {
      $p->id_idioma = $_meta[5];
    }

    if ($_meta[2] == null) {
	  if ( ($logo_obd = $this->iclogo_obd($cbd)) != null ) $_meta[2] = $logo_obd->atr("url")->valor;
	}

    if ($_meta[2] != null) {
      $logo_url = $this->__publicar_url($_meta[2], $p->head->base);

      if ($logo_url != null) {
        $p->head->image_src  = $logo_url;

        $p->head->pon_og("og:image"     , $logo_url);

        $p->head->pon_og("twitter:image", $logo_url);
        $p->head->pon_og("twitter:card" , "photo");
      }
      else {
        $p->head->image_src  = null;

        $p->head->pon_og("og:image"     , ""       );
        $p->head->pon_og("twitter:image", ""       );
        $p->head->pon_og("twitter:card" , "summary");
      }
    }

    $titulo   = $_meta[0];
    $describe = self::__combina($this->atr("description")->valor, $_meta[1]);
    $robots   = self::__combina($this->atr("robots"     )->valor, $_meta[3]);


    $p->head->titulo = $titulo;
    
    $p->head->pon_og("og:title"      , $titulo);
    $p->head->pon_og("og:description", $describe);
    
    $p->head->pon_og("twitter:title"      , $titulo);
    $p->head->pon_og("twitter:description", $describe);

    $p->head->pon_meta("author"          , $this->atr("author")->valor);
    $p->head->pon_meta("description"     , $describe);
    $p->head->pon_meta("copyright"       , "&copy;" . $this->atr("copyright")->valor);
    $p->head->pon_meta("robots"          , $robots);

    
    $p->head->javascript = [];

    $this->pon_script_pwa($cbd, $p);

    $p->head->pon_script($this->atr("ganalitics")->valor);
    $p->head->pon_script($this->atr("piwik"     )->valor);

    $p->head->pon_script($_meta[7]);

  }

  public static function __titulo($id_site, FS_cbd $cbd = null) {
    if ($cbd == null) $cbd = new FS_cbd();

    return self::inicia($cbd, $id_site)->atr("titulo_txt")->valor;
  }

  private function __publicar_url($url, $url_base) {
    if ($url            == null) return null;
    if (is_array($url)         ) return null;
    if (TrwlConfig::dominio == null) return null;
	
    if (strpos($url, "arquivos/") === false) $url = "arquivos/{$url}";

	  if (pathinfo($url, PATHINFO_EXTENSION) != null) return $url_base . $url;

    $urljpg = "{$url}.jpg"; 

    if (!is_file($urljpg)) link($url, $urljpg);


    return $url_base . $urljpg;
  }

  private function pon_script_pwa(FS_cbd $cbd, Paxina $p):void {
    if ($this->atr("pwa_activo")->valor != 1) return;
    
    $o = Site_pwa_obd::inicia($cbd, $this->atr("id_site")->valor);
    
    if ($o->baleiro()) return;
  
    $cp = $o->atr("color_p")->valor;
    
    $s = 
"<link rel=\"manifest\" href=\"app.webmanifest\">
<meta mame=\"theme-color\" content=\"{$cp}\">";
	
    $p->head->pon_script( $s );
  }

  private static function __combina($s1, $s2) {
    if ($s2 == null) {
      if ($s1 == null) $s1 = "";
      
      return addslashes($s1);
    }

    return addslashes($s2);
  }
}

//-------------------------------------------

final class Site_metatags_mbd extends Mapa_bd {
  public function __construct() {
    $t = new Taboa_dbd("site_metatags");

    $t->pon_campo("id_site", new Numero(), true);
    $t->pon_campo("id_logo", new Numero());
    $t->pon_campo("titulo_ico", new Numero());
    $t->pon_campo("titulo_txt");
    //~ $t->pon_campo("keywords");
    $t->pon_campo("author");
    $t->pon_campo("description");
    $t->pon_campo("robots");
    $t->pon_campo("copyright");
    $t->pon_campo("ganalitics", new Varchar(true));
    $t->pon_campo("piwik", new Varchar(true));

    $this->pon_taboa($t);

/*
    $t3 = new Taboa_dbd("site_plesk");

    $t3->pon_campo("dominio");

    $this->relacion_fk($t3, array("id_site"), array("id_site"), "left");
*/

    $t4 = new Taboa_dbd("site_config");

    $t4->pon_campo("url_limpa");

    $this->relacion_fk($t4, array("id_site"), array("id_site"), "left");


    $t5 = new Taboa_dbd("site_pwa");

    $t5->pon_campo("activo", new Numero(), false, "pwa_activo");

    $this->relacion_fk($t5, array("id_site"), array("id_site"), "left");
  }
}


//********************************************

final class Site_plesk_obd extends Obxeto_bd  {

  public function __construct() {
    parent::__construct();
  }

  public function mapa_bd() {
    return new Site_plesk_mbd();
  }

  public static function inicia(FS_cbd $cbd, FGS_usuario $u) {
    if ($u->atr("id_site")->valor > 0)
      return self::inicia_s($cbd, $u->atr("id_site")->valor);


    return self::inicia_u($cbd, $u->atr("id_usuario")->valor);
  }

  public static function inicia_s(FS_cbd $cbd, $id_site) {
    $s = new Site_plesk_obd();

    $s->select($cbd, "id_site = {$id_site}");

    return $s;
  }

  public static function inicia_u(FS_cbd $cbd, $id_usuario) {
    $s = new Site_plesk_obd();

    $s->select($cbd, "id_usuario = {$id_usuario}");

    return $s;
  }

  public function select(FS_cbd $cbd, $where = null, $orderby = null) {
    $r = $cbd->consulta($this->sql_select($cbd, $where, $orderby));

    if (!$a = $r->next()) return null;

    $this->post($a);
  }
}

//-------------------------------------------

final class Site_plesk_mbd extends Mapa_bd {
  public function __construct() {
    $t = new Taboa_dbd("site_plesk");

    $t->pon_campo("id_site", new Numero(), true);
    $t->pon_campo("id_usuario", new Numero(), true);
    $t->pon_campo("id_plesk", new Numero());
    $t->pon_campo("dominio");
    $t->pon_campo("id_piwik", new Numero());

    $this->pon_taboa($t);
  }
}


//********************************************

final class Site_plus_obd extends Obxeto_bd  {

  public function __construct() {
    parent::__construct();
  }

  public function mapa_bd() {
    return new Site_plus_mbd();
  }

  public static function inicia(FS_cbd $cbd, $id_site) {
    $s = new Site_plus_obd();

    $s->select($cbd, "id_site = {$id_site}");

    return $s;
  }

  public function select(FS_cbd $cbd, $where = null, $orderby = null) {
    $r = $cbd->consulta($this->sql_select($cbd, $where, $orderby));

    if (!$a = $r->next()) return null;

    $this->post($a);
  }
}

//-------------------------------------------

final class Site_plus_mbd extends Mapa_bd {
  public function __construct() {
    $t = new Taboa_dbd("site_plus");

    $t->pon_campo("id_site", new Numero(), true);
    $t->pon_campo("email"  , new Numero()      );
    $t->pon_campo("gigas"  , new Numero()      );
    $t->pon_campo("subs_n"                     );
    $t->pon_campo("subs_v"                     );

    $this->pon_taboa($t);
  }
}

//********************************************
/*
final class Site_info {  
  public  int    $k       = -1;
  public  string $refsite = "";
  public ?string $dominio = "";
  public  array  $_idioma = [];
  
  
  public function __construct(FS_cbd $cbd, int $id_site) {
    if ( !$this->calcula_site($cbd, $id_site) ) return;

    $this->calcula_idioma($cbd, $id_site);
  }
  
  public function validar():?string {
    $erro = null;
    
    if ($this->refsite == null) $erro .= "SiteInfo.validar(). (this->refsite == null).\n";
    
    if ($erro == null) return null;
    
    return "\n** Site '{$this->k}':\n{$erro}";
  }       
  
  private function calcula_site(FS_cbd $cbd, int $id_site):bool {
    $this->k = $id_site;
    
    
    $sql = "select a.nome, b.dominio
              from site a left join site_plesk b on (a.id_site = b.id_site)
             where a.id_site = {$id_site}";
             
    
    $r = $cbd->consulta($sql);
    
    if (!$_r = $r->next()) return false;
    
    $this->refsite = $_r["nome"];
    $this->dominio = $_r["dominio"];
    
    
    return true;
  }
  
  private function calcula_idioma(FS_cbd $cbd, int $id_site):bool {
    $sql = "select id_idioma
              from v_ml_site
             where id_site = {$id_site}
          order by id_idioma";
             
    
    $r = $cbd->consulta($sql);
    
    $this->_idioma = [];
    //~ while ($_r = $r->next()) $this->_idioma[] = $_r["id_idioma"];
    while ($_r = $r->next()) $this->_idioma[ $_r["id_idioma"] ] = 1;
    
    
    return $this->_idioma != [];
  }
}
*/
//********************************************

final class Site_config_obd extends Obxeto_bd  {

  private static $k = "";


  public function __construct($id_site) {
    parent::__construct();

    $this->atr("id_site")->valor = $id_site;
  }

  public function mapa_bd() {
    return new Site_config_mbd();
  }

  public static function inicia(FS_cbd $cbd, $id_site) {
    $s = new Site_config_obd($id_site);

    $s->select($cbd, "id_site = {$id_site}");

    return $s;
  }

  //~ public static function __serie($id_site, FS_cbd $cbd = null) {
    //~ if ($cbd == null) $cbd = new FS_cbd();

    //~ $sc = self::inicia($cbd, $id_site);

    //~ return $sc->atr("ventas_factura_serie")->valor;
  //~ }

  public static function _cliemail($id_site, FS_cbd $cbd = null):array {
	  if ($id_site == null) return [];
	
    if ($cbd == null) $cbd = new FS_cbd();

    $sc = self::inicia($cbd, $id_site);

    if ($sc->atr("cm_url")->valor == null) return [];

    return [ "url"   => $sc->atr("cm_url"      )->valor,
             "email" => $sc->atr("cm_email"    )->valor,
             "k"     => $sc->cm_k(),
             "seg"   => $sc->atr("cm_seguridad")->valor,
             "porto" => $sc->atr("cm_puerto"   )->valor,
             "nome"  => $sc->atr("cm_nombre"   )->valor
           ];
  }

  public static function _dropbox($id_site, FS_cbd $cbd = null):array {
	  if ($id_site == null) return [];
	
    if ($cbd == null) $cbd = new FS_cbd();

    $sc = self::inicia($cbd, $id_site);

    if ($sc->atr("dropbox_u")->valor == null) return [];

    return [ "dropbox_u" => $sc->atr("dropbox_u")->valor,
             "dropbox_k" => $sc->atr("dropbox_k")->valor
           ];
  }

  public static function peradmin(string $dominio, FS_cbd $cbd = null):array {
    if ($cbd == null) $cbd = new FS_cbd();
    
    //* Calcula id_site.
    $r = $cbd->consulta("select id_site from site_plesk where dominio = '{$dominio}'");
    
    
    if (!$_r = $r->next()) return [];


	//* calcula peradmin
    $sc = self::inicia($cbd, $_r["id_site"]);
    

    return [ $sc->atr("at_0")->valor, $sc->atr("at_1")->valor ];
  }

  //~ public static function fs_credenciais($id_site, FS_cbd $cbd = null) {
    //~ if ($cbd == null) $cbd = new FS_cbd();

    //~ $sc = self::inicia($cbd, $id_site);

    //~ return [$sc->atr("api")->valor, $sc->atr("fs_u")->valor, $sc->atr("fs_k")->valor];
  //~ }

  public function cm_k($k = null) {
    if ($k === null) {
      return AES_256_CBC::descifrar($this->atr("cm_k")->valor);
    }
    
    $this->atr("cm_k")->valor = AES_256_CBC::cifrar($k);
    
    return $this->atr("cm_k")->valor;

  }

  public function logo($tlogo, FS_cbd $cbd = null) {
    if ($cbd == null) $cbd = new FS_cbd();

    if (($id_logo = $this->atr($tlogo)->valor) == null) return new Imaxe_obd();

    return Elmt_obd::inicia($cbd, $id_logo, Imaxe_obd::tipo_bd);
  }

  public function select(FS_cbd $cbd, $where = null, $orderby = null) {
    $r = $cbd->consulta($this->sql_select($cbd, $where, $orderby));

    if (!$a = $r->next()) return;

    $this->post($a);
  }

  public function update(FS_cbd $cbd) {
    
    return $cbd->executa( $this->sql_update($cbd) );
  }
}

//-------------------------------------------

final class Site_config_mbd extends Mapa_bd {
  public function __construct() {
    $t = new Taboa_dbd("site_config");

    $t->pon_campo("id_site"                 , new Numero(), true);
    $t->pon_campo("https"                   , new Numero()      );
    $t->pon_campo("url_limpa"               , new Numero()      );
    $t->pon_campo("cookies"                 , new Numero()      );
    $t->pon_campo("ru_visible"                                  );
    $t->pon_campo("ru_registro_visible"                         );
    $t->pon_campo("ru_diferido"                                 );
    $t->pon_campo("rs_facebook_url"                             );
    $t->pon_campo("rs_twitter_url"                              );
    $t->pon_campo("rs_linkedin_url"                             );
    $t->pon_campo("rs_vimeo_url"                                );
    $t->pon_campo("rs_flickr_url"                               );
    $t->pon_campo("rs_tumblr_url"                               );
    $t->pon_campo("rs_youtube_url"                              );
    $t->pon_campo("rs_instagram_url"                            );
    $t->pon_campo("rs_tripadvisor_url"                          );
    $t->pon_campo("rs_pinterest_url"                            );
    $t->pon_campo("rs_blogger_url"                              );
    $t->pon_campo("rs_telegram_url"                             );
    $t->pon_campo("rs_tiktok_url"                               );
    $t->pon_campo("rs_applemusic_url"                           );
    $t->pon_campo("rs_spotify_url"                              );
    $t->pon_campo("rs_bluesky_url"                              );
    $t->pon_campo("ventas_producto_agotados", new Numero()      );
    $t->pon_campo("ventas_producto_ive"     , new Numero()      );
    $t->pon_campo("ventas_pedfra_logo"      , new Numero()      );
    $t->pon_campo("ventas_cpagar2_cnife"    , new Numero()      );
    $t->pon_campo("ventas_cpagar2_tlfn"     , new Numero()      );
    $t->pon_campo("dropbox_u"                                   );
    $t->pon_campo("dropbox_k"                                   );
    $t->pon_campo("api"                     , new Numero()      );
    $t->pon_campo("fs_u"                                        );
    $t->pon_campo("fs_k"                                        );
    $t->pon_campo("at_0"                    , new Numero()      );
    $t->pon_campo("at_1"                    , new Numero()      );
    $t->pon_campo("cm_url"                                      );
    $t->pon_campo("cm_email"                                    );
    $t->pon_campo("cm_k"                                        );
    $t->pon_campo("cm_seguridad"                                );
    $t->pon_campo("cm_puerto"                                   );
    $t->pon_campo("cm_nombre"                                   );

    $this->pon_taboa($t);
  }
}


//********************************************

final class Site_pwa_obd extends    Obxeto_bd 
                         implements ICLogo {
                           
  private bool $baleiro = true;

  private function __construct(int $id_site = null) {
    parent::__construct();
    
    $this->atr("id_site")->valor = $id_site;
  }

  public function mapa_bd() {
    return new Site_pwa_mbd();
  }

  public function baleiro():bool {
    return $this->baleiro;
  }
  
  public static function inicia(FS_cbd $cbd, int $id_site) {
    $s = new Site_pwa_obd($id_site);
    
    $s->select($cbd, "id_site = {$id_site}");

    return $s;
  }


  public function iclogo_obd(FS_cbd $cbd, ?string $k = null) {
    if ( ($l = $this->iclogo_id($k)) == null) return null;

    return Elmt_obd::inicia($cbd, $l, Imaxe_obd::tipo_bd);
  }

  public function iclogo_id(?string $k = null) {
    return $this->atr($k)->valor;
  }

  public function iclogo_src(FS_cbd $cbd, ?string $k = null) {
    $l = $this->iclogo_obd($cbd, $k);
    
    return $l->url(0, $cbd);
  }

  public function iclogo_update(FS_cbd $cbd, $src, ?string $k = null) {
    //* os logos pwa serán un Imaxe_obd dedicado.

//~ echo "(src, k)::({$src}, $k)\n";

    if (($l = $this->iclogo_obd($cbd, $k)) == null) {
      $l = new Imaxe_obd($src);

      if (!$l->insert($cbd)) return false;

      $id_s = $this->atr("id_site")->valor;
      $id_l = $l   ->atr("id_elmt")->valor;

      $this->atr($k)->valor = $id_l;

      return $cbd->executa( "update site_pwa set {$k}={$id_l} where id_site = {$id_s}" );
    }

    //* update src.
    $l->post_url($src);

    return $l->update($cbd);
  }

  public function select(FS_cbd $cbd, $where = null, $orderby = null) {
    $this->baleiro = true;
    
    $r = $cbd->consulta($this->sql_select($cbd, $where, $orderby));

    if (!$a = $r->next()) return null;

    $this->baleiro = false;

    $this->post($a);
  }

  public function update(FS_cbd $cbd):bool {
    if ($this->baleiro()) return $this->insert($cbd);
    
    return $cbd->executa($this->sql_update($cbd));
  }

  public function __validar():?string {
    $erro = null;

    if ( $this->atr("nome_l" )->valor == null ) $erro .= "Debes teclear un nombre largo.<br>";
    if ( $this->atr("nome_c" )->valor == null ) $erro .= "Debes teclear un nombre corto.<br>";
    if ( $this->atr("descri" )->valor == null ) $erro .= "Debes teclear una descripción.<br>";
    if ( $this->atr("color_p")->valor == null ) $erro .= "Debes seleccionar el color principal.<br>";
    if ( $this->atr("color_f")->valor == null ) $erro .= "Debes seleccionar el color de fondo.<br>";


    return $erro;    
  }

  private function insert(FS_cbd $cbd) {
    if  ( !$cbd->executa($this->sql_insert($cbd)) ) return false;
    
    $this->baleiro = false;
    
    return true;
  }

}

//-------------------------------------------

final class Site_pwa_mbd extends Mapa_bd {
  public function __construct() {
    $t = new Taboa_dbd("site_pwa");

    $t->pon_campo("id_site", new Numero(), true);
    $t->pon_campo("nome_c"                     );
    $t->pon_campo("nome_l"                     );
    $t->pon_campo("descri"                     );
    $t->pon_campo("color_p"                    );
    $t->pon_campo("color_f"                    );
    $t->pon_campo("activo" , new Numero()      );
    $t->pon_campo("logo_0"                     );
    $t->pon_campo("logo_m"                     );

    $this->pon_taboa($t);
  }
}
