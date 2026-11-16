<?php

class Articulo_obd extends    Obxeto_bd
                   implements ICLogo, IMetatags {

  const logo_width  = 111;
  const logo_height = 111;

  public static $a_moeda = array("euro"=>"&euro;", "euro_mes"=>"&euro;/mes", "euro_ano"=>"&euro;/año", "dolar_a"=>"$", "libra"=>"&pound;");

  private $baleiro = true;

  public function __construct($id_site, $id_articulo = null) {
    parent::__construct();

    $this->atr("id_site"     )->valor = $id_site;
    $this->atr("id_articulo" )->valor = $id_articulo;
    
    $this->atr("id_categoria")->valor = 1;
    
    $this->atr("id_almacen"  )->valor = 1;

    $this->atr("moeda"       )->valor = "euro"; 
                                 
    $this->atr("peso"        )->valor = "0";
                             
    $this->atr("desc_u"      )->valor = 1;
    $this->atr("desc_p"      )->valor = "0";
                             
    $this->atr("pedido_min"  )->valor = 1;
    $this->atr("pedido_max"  )->valor = "0";
  }

  public function baleiro() {
    return $this->baleiro;
  }

  public function mapa_bd() {
    return new Articulo_mbd();
  }

  public static function inicia(FS_cbd $cbd, $id_site, $id_articulo = null) {
    $a = new Articulo_obd($id_site, $id_articulo);

    if ($id_articulo == null) return $a;

    $wh_s = $a->atr("id_site"    )->sql_where($cbd);
    $wh_a = $a->atr("id_articulo")->sql_where($cbd);

    $a->select($cbd, "{$wh_s} and {$wh_a}");

    return $a;
  }

  public function iclogo_obd(FS_cbd $cbd, ?string $k = null) {
    if ($cbd == null) $cbd = new FS_cbd();

    $id_logo = $this->iclogo_id($k);

    if ($id_logo != null) return Elmt_obd::inicia($cbd, $id_logo);


    return new Articulo_imaxe_obd();
  }

  public function iclogo_id(?string $k = null) {
    $where = "id_site = " . $this->atr("id_site")->valor . " and id_articulo = " . $this->atr("id_articulo")->valor;

    $sql = "select id_logo from articulo_imx where {$where} order by posicion limit 0, 1";

    $cbd = new FS_cbd();

    $r = $cbd->consulta($sql);

    if ($a = $r->next()) return $a['id_logo'];

    return null;
  }

  public function iclogo_src(FS_cbd $cbd, ?string $k = null) {
        //~ if (is_numeric($mini));
    //~ elseif ($mini            ) $mini = self::logo_width;
    
    $mini = self::logo_width;
    
    if ($this->atr("id_articulo")->valor == null) return null;


    $where = "id_site = " . $this->atr("id_site")->valor . " and id_articulo = " . $this->atr("id_articulo")->valor;

    $sql = "select id_logo from articulo_imx where {$where} order by posicion";

    if ($cbd == null) $cbd = new FS_cbd();

    $r = $cbd->consulta($sql);

    $a_src = null;
    while ( $a = $r->next() ) {
      $logo_obd = Elmt_obd::inicia($cbd, $a['id_logo']);

      $a_src[$a['id_logo']] = $logo_obd->url($mini);
    }

//~ echo "<pre>" . print_r($a_src, true) . "</pre>";

    return $a_src;
  }

  public function iclogo_update(FS_cbd $cbd, $id_file, $id_logo = null) {
    $logo_obd = ($id_logo == null)?null:Elmt_obd::inicia($cbd, $id_logo);

    if ($logo_obd == null) $logo_obd = new Articulo_imaxe_obd();

    $a_url = Imaxe_obd::url_split($id_file);

//~ echo "<pre>" . print_r($a_url, true) . "</pre>";

    $logo_obd->atr("url"     )->valor = $a_url["url"     ];
    $logo_obd->atr("ref_site")->valor = $a_url["ref_site"];

    if ($id_logo != null) return $logo_obd->update($cbd);


    if (!$logo_obd->insert($cbd)) return false;

    $id_site     = $this    ->atr("id_site"    )->valor;
    $id_articulo = $this    ->atr("id_articulo")->valor;
    $id_logo     = $logo_obd->atr("id_elmt"    )->valor;

    $aii_obd = AImx_indice_obd::inicia($cbd, $id_site, $id_articulo, $id_logo);


    return $aii_obd->insert($cbd);
  }

  public function iclogo_delete(FS_cbd $cbd, $id_logo) {
    $s = $this->atr("id_site"    )->valor;
    $a = $this->atr("id_articulo")->valor;
    
    $aimx = new AImx_indice_obd($s, $a, $id_logo);

    return $aimx->delete($cbd);
  }

  public function _metatags($url_limpa, FS_cbd $cbd = null) {  //* IMPLEMENTA IMetatags._metatags()
    if ($cbd == null) $cbd = new FS_cbd();

    $id_site     = $this->atr("id_site"    )->valor;
    $id_articulo = $this->atr("id_articulo")->valor;

    $tit = $this->atr("nome_seo" )->valor ?: $this->atr("nome")->valor;
    $des = $this->atr("notas_seo")->valor ?: Valida::split($this->atr("notas")->valor, 111, "");

    $log = $this->iclogo_src($cbd);
    $rob = "index, nofollow";
    $url = null;
    $idi = null;
    $can = $this->action($url_limpa);

    $promo = Promo_obd::inicia_articulo($cbd, $id_site, $id_articulo);

    $precio = $this->pvp(1, true, true, $promo);

    $url_base = Efs::url_base($id_site, $cbd);
    $dominio  = parse_url($url_base, PHP_URL_HOST);

    $ean = $this->atr("ean")->valor;  
    $sku = self::ref($this->atr("id_articulo")->valor);

    if (is_array($log)) $log = array_shift($log);

    $disponible = ($this->atr("unidades")->valor >= 1) ? "https://schema.org/InStock" : "https://schema.org/OutOfStock";
   
    $script = '<script type="application/ld+json">{
                    "@context": "https://schema.org/",
                    "@type": "Product",
                    "name": ' . json_encode($tit) . ',
                    "image": [
                      "' . $url_base . $log . '"
                    ],
                    "description": ' . json_encode($des) . ',
                    "sku": "' . $sku . '", 
                    "gtin13": "' . $ean . '",  
                    "offers": {
                      "@type": "Offer",
                      "url": "' . $url_base . $can . '",
                      "priceCurrency": "EUR",
                      "price": "' . $precio . '",
                      "availability": "' . $disponible . '",
                      "seller": { "@type": "Organization", "name": "'. $dominio .'" }
                    }
                }</script>';


    return array($tit, $des, $log, $rob, $url, $idi, $can, $script);
  }

  public function crea_grupo(FS_cbd $cbd = null) {
    if ($cbd == null) $cbd = new FS_cbd();

    $this->atr("id_grupo")->valor = $this->atr("id_articulo")->valor;

    return $cbd->executa($this->sql_update($cbd));
  }

  public function select(FS_cbd $cbd, $where = null, $orderby = null) {
    $r = $cbd->consulta($this->sql_select($cbd, $where, $orderby));

    if (!$a = $r->next()) return;

    $this->baleiro = false;

    $this->post($a);
  }

  public function duplicar(FS_cbd $cbd, $_cc, ?string $refsite = null) {
    $a_prsto_pasos = $this->a_presuposto_pasos($cbd);
    $_pubs         = APubs_obd::_paxina($this, $cbd);

    $s   = $this->atr("id_site"    )->valor;
    $a_0 = $this->atr("id_articulo")->valor;

    $this->atr("id_grupo"   )->valor = null; //* grupo de relacións
    $this->atr("id_articulo")->valor = null;

    if (!$this->insert($cbd, $_cc, $_pubs)) return false;


    $a_1 = $this->atr("id_articulo")->valor;

    if (!AImx_indice_obd::duplica_articulo($cbd, $s, $a_0, $a_1)) return false;
    

    //* duplicamos o metapresuposto.
    if ($a_prsto_pasos == null) return true; //* => $this->atr("tipo")->valor != 'p'

    foreach ($a_prsto_pasos as $pp)
      if (!$pp->duplicar($cbd, $a_1, $refsite)) return false;


    return true;
  }

  public function insert(FS_cbd $cbd, $_cc, $_pubs) {
    if (!$this->calcula_id($cbd)) return false;


    if ($this->atr("unidades"    )->valor == null) $this->atr("unidades"    )->valor = "0";
    if ($this->atr("servizo_dias")->valor == null) $this->atr("servizo_dias")->valor = "0";
    if ($this->atr("pvp"         )->valor == null) $this->atr("pvp"         )->valor = "0";
    if ($this->atr("desc_p"      )->valor == null) $this->atr("desc_p"      )->valor = "0";
  

    if (!$cbd->executa($this->sql_insert($cbd))) return false;


    //* inserta publicacións.
    if (!APubs_obd::update($cbd, $this, $_pubs)) return false;

    //* inserta campos cústom.
    if (!is_array($_cc)) return true;

    $id_s = $this->atr("id_site"    )->valor;
    $id_a = $this->atr("id_articulo")->valor;
    $id_g = $this->atr("id_grupo_cc")->valor;
      
    foreach($_cc as $id_cc_0=>$id_cc_1) {
      if (!Articulo_cc_obd::insert($cbd, $id_s, $id_a, $id_cc_0, $id_cc_1, $id_g)) return false;
    }

    //* actualiza tupla_cc.
    return Articulo_cc_obd::update_tupla($cbd, $id_s, $id_a);
  }

  public function update(FS_cbd $cbd, $_pubs = null) {
    if (!APubs_obd::update($cbd, $this, $_pubs)) return false;


    if ($this->atr("unidades"    )->valor == null) $this->atr("unidades"    )->valor = "0";
    if ($this->atr("servizo_dias")->valor == null) $this->atr("servizo_dias")->valor = "0";
    if ($this->atr("pvp"         )->valor == null) $this->atr("pvp"         )->valor = "0";
    if ($this->atr("desc_p"      )->valor == null) $this->atr("desc_p"      )->valor = "0";

    if (!$cbd->executa($this->sql_update($cbd))) return false;

    return true;
  }

  public function post_cc(FS_cbd $cbd, $id_cc_0, $id_cc_1) {
    $s = $this->atr("id_site"    )->valor;
    $a = $this->atr("id_articulo")->valor;
    $c = $this->atr("id_cc_0"    )->valor;

    $sql = "delete from articulo_cc where id_site = {$s} and id_articulo = {$a} and id_cc_0 = {$c}";

    if (!$cbd->executa($sql)) return false;

    $sql = "insert into articulo_cc (id_site, id_articulo, id_cc_0, id_cc_1)
                 values ({$s}, {$a}, {$c}, {$id_cc_1})";
    
    return $cbd->executa($sql);
  }
  
  public function resta_unidades(FS_cbd $cbd, $unidades) {
    $this->atr("unidades")->valor -= $unidades;

    if ($this->atr("unidades")->valor == 0) $this->atr("unidades")->valor = "0";

    return $cbd->executa($this->sql_update($cbd));
  }

  public static function a_tipo($nulo = false, $nulo_txt = "...") {
    $a = null;

    if ($nulo) $a['**'] = $nulo_txt;

    $a['a'] = 'Artículo';
    $a['s'] = 'Servicio';

    return $a;
  }

  public static function a_tipoive($predet = null) {
    $cbd = new FS_cbd();

    $r = $cbd->consulta("select * from tipos_ive order by id_ive");

    $o = ($predet == null)?array():array("**"=>$predet);
    
    while ($a = $r->next()) $o[$a['id_ive']] = $a['pive'];

    return $o;
  }

  public function destacado(FS_cbd $cbd = null) {
    return ($this->atr("momento_novo")->valor >= date("Y-m-d"));
  }

  public function publicado(FS_cbd $cbd = null) {
    if ($cbd == null) $cbd = new FS_cbd();
    
    $_pubs = APubs_obd::_paxina($this, $cbd, "1");
    
    
    return count($_pubs) > 0;
  }

  public function hai_presuposto(FS_cbd $cbd = null) {
    if ($cbd == null) $cbd = new FS_cbd();

    $s = $this->atr("id_site"    )->valor;
    $a = $this->atr("id_articulo")->valor;

    $r = $cbd->consulta("select id_paso from prsto_paso where id_site = {$s} and id_articulo = {$a} limit 0, 1");

    return $r->next();
  }

  public function a_presuposto_pasos(FS_cbd $cbd = null) {
    if ($cbd == null) $cbd = new FS_cbd();

    $s = $this->atr("id_site"    )->valor;
    $a = $this->atr("id_articulo")->valor;

    $r = $cbd->consulta("select id_paso from prsto_paso where id_site = {$s} and id_articulo = {$a}");

    $a_pp = null;
    while ($a2 = $r->next()) $a_pp[] = Presuposto_paso_obd::inicia($cbd, $s, $a, $a2['id_paso']);

    return $a_pp;
  }

  public function describe_html(FS_cbd $cbd = null) {
    if ($cbd == null) $cbd = new FS_cbd();

    return Articulo_cc_obd::html_articulo($cbd, $this->atr("id_site")->valor, $this->atr("id_articulo")->valor);
  }

  public function delete(FS_cbd $cbd) {
    if (!$cbd->executa($this->sql_delete($cbd))) return false;

    $s = $this->atr("id_site"    )->valor;
    $a = $this->atr("id_articulo")->valor;
    $g = $this->atr("id_grupo_cc")->valor;
      
    //* elimina vínculos do producto en articulo_cc.
    $sql = "delete from articulo_cc where id_site = {$s} and id_articulo = {$a}";

    if (!$cbd->executa($sql)) return false;
      
    //* elimina vínculos do producto con un grupo de relacions.
    $sql = "delete from articulo_grupo_2 where id_site = {$s} and id_articulo = {$a}";

    if (!$cbd->executa($sql)) return false;


    //* elimina publicacións do producto en paxinas de tipo: venta2
    if (!APubs_obd::delete($cbd, $this)) return false;


    //* elimina as imaxes asociadas ao artículo.
    if (!AImx_indice_obd::delete_articulo($cbd, $s, $a)) return false;


    //* eliminamos artículo en promoción.
    $sql = "delete from promo_producto where id_site = {$s} and id_articulo = {$a}";

    if (!$cbd->executa($sql)) return false;


    //* eliminamos o metapresuposto.
    $a_prsto_pasos = $this->a_presuposto_pasos($cbd);

    if ($a_prsto_pasos == null) return true; //* => $this->atr("tipo")->valor != 'p'

    foreach ($a_prsto_pasos as $pp) if (!$pp->delete($cbd)) return false;


    return true;
  }

  public function pvp($unidades = 1, $ive = true, $des = false, Promo_obd $p = null, $pvp_plus = 0) {
    $pvp = $this->atr("pvp")->valor + $pvp_plus;

    if ($ive) $pvp += ($pvp * ($this->atr("pive")->valor / 100));

        if ($p != null) $pvp =  $p->aplicar($pvp);
    elseif ($des      ) $pvp -= ($pvp * ($this->atr("desc_p")->valor / 100));


    return round($unidades * $pvp, 2);
  }

  public function almacen_obd(FS_cbd $cbd = null) {
    if (($id_almacen = $this->atr("id_almacen")->valor) == null) return null;

    if ($cbd == null) $cbd = new FS_cbd();

    return Almacen_obd::inicia($cbd, $this->atr("id_site")->valor, $id_almacen);
  }

  public function categoria_obd(FS_cbd $cbd = null) {
    if (($id_categoria = $this->atr("id_categoria")->valor) == null) return null;

    if ($cbd == null) $cbd = new FS_cbd();

    return Categoria_obd::inicia($cbd, $this->atr("id_site")->valor, $id_categoria);
  }

  public function perfil_obd(FS_cbd $cbd = null) {
    if (($id_perfil = $this->atr("id_perfil")->valor) == null) return null;

    if ($cbd == null) $cbd = new FS_cbd();

    return Articulo_perfil_obd::inicia($cbd, $id_perfil);
  }

  public function a_elmt_obd(FS_cbd $cbd = null) {
    if ($cbd == null) $cbd = new FS_cbd();

    $id_s = $this->atr("id_site")->valor;
    $id_a = $this->atr("id_articulo")->valor;

    //* devolve articulos que estan asociados a algun Elmt_obd nunha paxina do site.
    $sql = "select id_elmt from elmt_ventas where id_site = {$id_s} and id_articulo = {$id_a}

            UNION

            select id_elmt from elmt_carrusel_iten where id_site = {$id_s} and id_articulo = {$id_a}";


    $r = $cbd->consulta($sql);

    $a_elmt = null;
    while ($a = $r->next()) $a_elmt[$a['id_elmt']] = Elmt_obd::inicia($cbd, $a['id_elmt']);


    return $a_elmt;
  }

  public function action($url_limpa = true) {
    if ($this->baleiro()) return null;
    
    return Paxina_reservada_obd::action_producto($this->atr("id_articulo")->valor, $this->atr("nome")->valor, $url_limpa);

  }

  public static function ref($id_articulo, $l = 6) {
    if ($id_articulo == null) return "";


    return str_pad($id_articulo, $l, "0", STR_PAD_LEFT);
  }

  public static function ean_dupli(int $id_site, string $ean, FS_cbd $cbd = null) {
    //* DEVOLVE: true  => ean duplicado.
    //*          false => ean NON duplicado.
    
    if (trim($ean) == "") return false;
     
    if ($cbd == null) $cbd = new FS_cbd();
    
    $sql = "select count(ean) as ct from articulo where id_site={$id_site} and ean = '{$ean}'";
    
    $r = $cbd->consulta($sql);
    
    $_r = $r->next();


    return $_r["ct"] > 0;
 }

  private function calcula_id(FS_cbd $cbd) {
    $id_site = $this->atr("id_site")->valor;

    if (!$cbd->executa("insert into articulo_seq (id_site) values ({$id_site})")) return false;

    $r = $cbd->consulta("select LAST_INSERT_ID() as id");

    if (!$a = $r->next()) return false;

    $cbd->executa("delete from articulo_seq where id_site = {$id_site} and id_articulo < {$a['id']}");

    $this->atr("id_articulo")->valor = $a['id'];

    return true;
  }
}

//-----------------------------------------------

class Articulo_mbd extends Mapa_bd {
  public function __construct() {
    $this->pon_taboa(self::__t());


    $t2 = new Taboa_dbd("tipos_ive");

    $t2->pon_campo("pive", new Numero());

    $this->relacion_fk($t2, array("id_ive"), array("id_ive"), "left");


    $t3 = new Taboa_dbd("categoria");

    $t3->pon_campo("id_pai", new Numero(), false, "id_categoria_pai");

    $this->relacion_fk($t3, array("id_site", "id_categoria"), array("id_site", "id_categoria"), "left");


    $t4 = new Taboa_dbd("almacen");

    $t4->pon_campo("id_almacen_portes", new Numero());

    $this->relacion_fk($t4, array("id_site", "id_almacen"), array("id_site", "id_almacen"), "left");
  }

  public static function __t($n = "articulo") {
    $t = new Taboa_dbd($n);

    $t->pon_campo("id_site"         , new Numero()      , true);
    $t->pon_campo("id_articulo"     , new Numero()      , true);
    $t->pon_campo("tipo"                                      );                              
    $t->pon_campo("pvp"             , new Numero()            );
    $t->pon_campo("id_ive"          , new Numero()            );
    $t->pon_campo("moeda"                                     );                                   
    $t->pon_campo("unidades"        , new Numero()            );
    $t->pon_campo("desc_u"          , new Numero()            );
    $t->pon_campo("desc_p"          , new Numero()            );
    $t->pon_campo("servizo_data"    , new Data("Y-m-d")        );
    $t->pon_campo("servizo_dias"    , new Numero()            );
    $t->pon_campo("servizo_permisos"                          );
    $t->pon_campo("peso"            , new Numero()            );
    $t->pon_campo("pedido_min"      , new Numero()            );
    $t->pon_campo("pedido_max"      , new Numero()            );
    $t->pon_campo("nome"                                      );
    $t->pon_campo("nome_seo"                                  );                                    
    $t->pon_campo("aux_0"                                     );                                   
    $t->pon_campo("aux_1"                                     );                                   
    $t->pon_campo("aux_2"                                     );                                   
    $t->pon_campo("ref_externa"                               );                             
    $t->pon_campo("id_marca"        , new Numero()            );
    $t->pon_campo("ean"                                       );                                     
    $t->pon_campo("cep"                                       );                                     
    $t->pon_campo("notas"                                     );
    $t->pon_campo("notas_seo"                                 );
    $t->pon_campo("id_almacen"      , new Numero()            );
    $t->pon_campo("id_categoria"    , new Numero()            );
    $t->pon_campo("id_grupo_cc"     , new Numero()            );
    $t->pon_campo("tupla_cc"                                  );                                
    $t->pon_campo("id_grupo"        , new Numero()            );
    $t->pon_campo("reserva"         , new Numero()            );
    $t->pon_campo("reserva_sinal"   , new Numero()            );
    $t->pon_campo("momento_novo"    , new Data("Y-m-d")       );
    $t->pon_campo("momento_fs"      , new Data("YmdHis")      );


    return $t;
  }
}

//********************************************

class AImx_indice_obd extends Obxeto_bd {

  public function __construct($id_site, $id_articulo, $id_logo = null) {
    parent::__construct();

    $this->atr("id_site"    )->valor = $id_site;
    $this->atr("id_articulo")->valor = $id_articulo;
    $this->atr("id_logo"    )->valor = $id_logo;
  }

  public function mapa_bd() {
    return new AImx_indice_mbd();
  }

  public static function inicia(FS_cbd $cbd, $id_site, $id_articulo, $id_logo = null) {
    $ai = new AImx_indice_obd($id_site, $id_articulo, $id_logo);

    if ($id_logo == null) return $ai;

    $wh_s = $ai->atr("id_site"    )->sql_where($cbd);
    $wh_a = $ai->atr("id_articulo")->sql_where($cbd);
    $wh_l = $ai->atr("id_logo"    )->sql_where($cbd);

    $ai->select($cbd, "{$wh_s} and {$wh_a} and {$wh_l}");


    return $ai;
  }

  public function select(FS_cbd $cbd, $where = null, $orderby = null) {
    $r = $cbd->consulta($this->sql_select($cbd, $where, $orderby));

    if (!$a = $r->next()) return;

    $this->post($a);
  }

  public function insert(FS_cbd $cbd) {
    //* 1.- calcula posicion.
    $wh_s = "id_site = "     . $this->atr("id_site"    )->valor;
    $wh_a = "id_articulo = " . $this->atr("id_articulo")->valor;

    $sql = "select max(posicion) + 1 as ct from articulo_imx where {$wh_s} and {$wh_a}";

    $r = $cbd->consulta($sql);

    $_r = $r->next();

    if ($_r['ct'] == null) $_r['ct'] = 1;

    $this->atr("posicion")->valor = $_r['ct'];

    //* 2.- inserta.

    return $cbd->executa($this->sql_insert($cbd));
  }

  public function update(FS_cbd $cbd) {
    return $cbd->executa($this->sql_update($cbd));
  }

  public function delete(FS_cbd $cbd) {
    //* 1.- Borramos a entrada do índice.
    if (!$cbd->executa($this->sql_delete($cbd))) return false;
    
    //* 2.- Borramos a imx da BD, se non está sendo empregada por outro artículo.
    $s = $this->atr("id_site")->valor;
    $l = $this->atr("id_logo")->valor;
    
    $sql = "select count(id_logo) as ct from articulo_imx where id_site = {$s} and id_logo = {$l}";

    $r = $cbd->consulta($sql);

    if (!$_r = $r->next()) return true;

    if ($_r['ct'] > 0) return true; //* hay máis productos compartindo o logo, non se borra.


    if (($logo_obd = Elmt_obd::inicia($cbd, $l)) == null) return true;
    

    return $logo_obd->delete($cbd);
  }

  public static function delete_articulo(FS_cbd $cbd, $id_site, $id_articulo) {
    $sql = "select id_logo from articulo_imx where id_site = {$id_site} and id_articulo = {$id_articulo}";

    $r = $cbd->consulta($sql);

    while ($_r = $r->next()) {
      $aimx = new AImx_indice_obd($id_site, $id_articulo, $_r["id_logo"]);

      if (!$aimx->delete($cbd)) return false;
    }


    return true;
  }

  public static function duplica_articulo(FS_cbd $cbd, $id_site, $id_articulo_0, $id_articulo_1) {
    $sql = "select id_logo from articulo_imx where id_site = {$id_site} and id_articulo = {$id_articulo_0} order by posicion";

    $r = $cbd->consulta($sql);

    while ($_r = $r->next()) {
      $aimx = new AImx_indice_obd($id_site, $id_articulo_1, $_r["id_logo"]);

      if (!$aimx->insert($cbd)) return false;
    }


    return true;
  }

  public static function swap(FS_cbd $cbd, $id_site, $id_articulo, $id_logo_a, $id_logo_b) {
    $wh_s = "id_site = {$id_site}";
    $wh_a = "id_articulo = {$id_articulo}";

    //* 1.- calcula $pos_a
    $sql = "select posicion from articulo_imx where {$wh_s} and {$wh_a} and id_logo = {$id_logo_a}";

    $r = $cbd->consulta($sql);

    if (!$_r = $r->next()) return false;

    $pos_a = $_r["posicion"];

    //* 2.- calcula $pos_b
    $sql = "select posicion from articulo_imx where {$wh_s} and {$wh_a} and id_logo = {$id_logo_b}";

    $r = $cbd->consulta($sql);

    if (!$_r = $r->next()) return false;

    $pos_b = $_r["posicion"];


    //* 3.- swap $pos_a
    $sql = "update articulo_imx set posicion = {$pos_a} where {$wh_s} and {$wh_a} and id_logo = {$id_logo_b}";

    if (!$cbd->executa($sql)) return false;


    //* 4.- swap $pos_b
    $sql = "update articulo_imx set posicion = {$pos_b} where {$wh_s} and {$wh_a} and id_logo = {$id_logo_a}";

    if (!$cbd->executa($sql)) return false;


    return true;
  }

  public static function valida_proporcion(Articulo_obd $a, $imx_proporcion = null) {
    $cbd = new FS_cbd();

    //* Lemos _src das imaxes
    if (($src_aux = $a->iclogo_src($cbd, false)) == null) return -1;

    
     //* Lemos imx_proporcion.
    if ($imx_proporcion == null) {
      $c = Site_obd::inicia( $cbd, $a->atr("id_site")->valor )->config_obd();

      $imx_proporcion = 1 / $c->atr("factor_h")->valor;
    }

    //* Validamos imaxes.
    foreach ($src_aux as $id_logo => $url)
      if (!self::valida_proporcion_url($url, $imx_proporcion)) return -2;


    return 1;
  }

  public static function valida_proporcion_url($url, $imx_proporcion, $max_error = 0.01) {
    if (!is_file($url)) return false;
    
    list($ancho, $alto) = getimagesize($url);

//~ echo "{$imx_proporcion} - ({$ancho} / {$alto}) <= {$max_error}<br>";

    return abs($imx_proporcion - ($ancho / $alto)) <= $max_error;
  }
}

//-----------------------------------------

final class AImx_indice_mbd extends Mapa_bd {
  public function __construct() {
    $t = new Taboa_dbd("articulo_imx");

    $t->pon_campo("id_site"    , new Numero(), true);
    $t->pon_campo("id_articulo", new Numero(), true);
    $t->pon_campo("id_logo"    , new Numero(), true);
    $t->pon_campo("posicion"   , new Numero());

    $this->pon_taboa($t);
  }
}

//********************************************

final class APubs_obd {
  public static $_err = array("0" => "&bull; El producto no tiene foto.<br>",
                              "1" => "&bull; Al menos alguna de las fotos del producto no tiene la proporción adecuada.<br>",
                              "2" => "&bull; Debes completar el campo del servicio: 'Permisos' y 'Duración (días)'.<br>",
                              "3" => "&bull; Duración (días), debe ser mayor que cero ( > 0 ).<br>"
                             );

  public static $_adv = array("0" => "&bull; El producto no tiene asignada una categoría.<br>",
                              "1" => "&bull; El producto no tiene nombre.<br>",
                              "2" => "&bull; La descripción del producto debe tener al menos 33 palabras.<br>"
                             );

  public static $_msx = array("0" => "&bull; El producto está publicado.<br>",
                              "1" => "&bull; <b>El producto está publicado con advertencias.</b><br>"
                             );

  private function __construct() {}

  public static function _paxina(Articulo_obd $a, FS_cbd $cbd = null, $limit = "") {
    $id_s = $a->atr("id_site"    )->valor;
    $id_a = $a->atr("id_articulo")->valor;

    if ($id_a == null) return [];
    
    if ($cbd == null) $cbd = new FS_cbd();

    if ($limit != "") $limit = " limit {$limit}";

    $wh_s = "a.id_site     = {$id_s}";
    $wh_a = "a.id_articulo = {$id_a}";

    $sql = "select a.id_paxina, DATE_FORMAT(a.momento, '%Y%m%d%H%i%s') as momento
              from articulo_publicacions a inner join paxina b on (a.id_paxina = b.id_paxina and b.estado = 'publicada')
             where {$wh_s} and {$wh_a} {$limit}";

    $r = $cbd->consulta($sql);

    $_p = array();
    while ($_r = $r->next()) $_p[ $_r["id_paxina"] ] = $_r["momento"];


    return $_p;
  }

  public static function update(FS_cbd $cbd, Articulo_obd $a, $_p) {
    if (!self::delete($cbd, $a)) return false;

    if (!is_array($_p)) return true;

    $id_s = $a->atr("id_site"    )->valor;
    $id_a = $a->atr("id_articulo")->valor;
    
    foreach($_p as $p => $m) {
      if ($m == -1) continue;
      
      $sql = "insert into articulo_publicacions (id_site, id_articulo, id_paxina, momento)
                   values ({$id_s}, {$id_a}, {$p}, {$m})";
      
      if (!$cbd->executa($sql)) return false;
    }
    

    return true;
  }

  public static function delete(FS_cbd $cbd, Articulo_obd $a) {
    $id_s = $a->atr("id_site"    )->valor;
    $id_a = $a->atr("id_articulo")->valor;
    
    $wh_s = "id_site     = {$id_s}";
    $wh_a = "id_articulo = {$id_a}";
    
    $sql = "delete from articulo_publicacions where {$wh_s} and {$wh_a}";

    return $cbd->executa($sql);
  }


  public static function valida($id_s, $id_a, $id_p, $imx_proporcion, FS_cbd $cbd = null):array {
    if ($cbd == null) $cbd = new FS_cbd();

    //* Inicia artículo.
    $a = Articulo_obd::inicia($cbd, $id_s, $id_a);
    
    //* Inicia rexistros.
    $err = null;
    $adv = null;


    //*  Valida advertencias.
    if ($a->atr("nome")->valor                  == null) $adv .= self::$_adv["1"];
    if ($a->atr("id_categoria")->valor          <= 1   ) $adv .= self::$_adv["0"];
    if (str_word_count($a->atr("notas")->valor) < 33   ) $adv .= self::$_adv["2"];



    //*  Valida erros.
    //** Valida imaxe(s).
    $b = AImx_indice_obd::valida_proporcion($a, $imx_proporcion);
    
        if ($b == -1) $err .= self::$_err["0"];
    elseif ($b == -2) $err .= self::$_err["1"];

    //** Valida servizo.

    //~ if ($a->atr("tipo")->valor == "s") {
          //~ if ( $a->atr("servizo_dias"    )->valor == null  ) $_err[] = self::$_err["2"];
      //~ elseif ( $a->atr("servizo_dias"    )->valor <  1     ) $_err[] = self::$_err["3"];
      //~ elseif ( $a->atr("servizo_permisos")->valor == "pub" ) $_err[] = self::$_err["2"];
    //~ }


    
    //* Devolve saída.
    if ($err != null) return ["e", $err];

    if ($adv != null) return ["a", $adv . self::$_msx["1"]];

    return ["m", self::$_msx["0"]];
  }
}
