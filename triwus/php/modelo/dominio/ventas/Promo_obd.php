<?php

final class Promo_obd extends Obxeto_bd {
  public function __construct($id_site, $id_promo = null) {
    parent::__construct();

    $this->atr("id_site" )->valor = $id_site;
    $this->atr("id_promo")->valor = $id_promo;
    
    $this->atr("tipo"    )->valor = "g";
  }

  public function mapa_bd() {
    return new Promo_mbd();
  }

  public static function inicia(FS_cbd $cbd, $id_site, $id_promo = null) {
    $p = new Promo_obd($id_site, $id_promo);

    if ($id_promo == null) return $p;

    $wh_s = $p->atr("id_site" )->sql_where($cbd);
    $wh_p = $p->atr("id_promo")->sql_where($cbd);

    $p->select($cbd, "{$wh_s} and {$wh_p}");

    return $p;
  }

/*
 *
 * name: Promo_obd::busca_articulo
 * @param
 * @return devolve as id_promo non caducadas asociadas a un artículo.
 *
 */
  public static function busca_articulo(FS_cbd $cbd, $id_site, $id_articulo, $promo_regalo = false) {
    if ($id_site     == null) return null;
    if ($id_articulo == null) return null;

    $d = date("Ymd") . "000000";

    $wh_s = "(a.id_site = {$id_site})";
    $wh_a = "((b.id_articulo = {$id_articulo}) or (a.tipo = 'g'))";
    $wh_d = "(a.data_ini <= '{$d}' and a.data_fin > '{$d}')";
    $wh_r = ($promo_regalo)?"(a.tipo_dto = 'r')":"(a.tipo_dto <> 'r')"; //* ($promo_regalo) => busca promociones de tipo regalo.

    $sql = "select distinct a.id_promo, a.tipo_dto
              from promo a left join promo_producto b on (a.id_site = b.id_site and a.id_promo = b.id_promo)
             where {$wh_s} and {$wh_a} and {$wh_d} and {$wh_r} and ((b.regalo = '0') or (b.regalo is null))";

    $r = $cbd->consulta($sql);

    $_p = null;
    while ($_a = $r->next()) $_p[] = array( $_a['id_promo'], $_a['tipo_dto']);


    return $_p;
  }

/*
 *
 * name: Promo_obd::inicia_articulo
 * @param
 * @return devolve a primeira promoción non caducada asociada a un artículo.
 *
 */
  public static function inicia_articulo(FS_cbd $cbd, $id_site, $id_articulo, $promo_regalo = false) {
    $_id = self::busca_articulo($cbd, $id_site, $id_articulo, $promo_regalo);

//~ echo "11111111111111111111111\n";

    if (!isset($_id[0])) return null;

//~ echo "222222222222222222222222\n";
    
    return Promo_obd::inicia($cbd, $id_site, $_id[0][0]);
  }
  
/*
 *
 * name: Promo_obd::busca_codpromocional
 * @param
 * @return devolve a promoción non caducada asociada a un codigo promocional.
 *
 */
  public static function busca_codpromocional(FS_cbd $cbd, $id_site, $codigo) {
    if ($id_site == null) return null;
    if ($codigo  == null) return null;

    $d = date("Ymd") . "000000";

    $wh_s = "(id_site = {$id_site})";
    $wh_c = "(codigo = '{$codigo}')";
    $wh_d = "(data_ini <= '{$d}' and data_fin > '{$d}')";

    $sql = "select id_promo from promo where {$wh_s} and {$wh_c} and {$wh_d}";

    $r = $cbd->consulta($sql);

    if (!$_a = $r->next()) return null;


    return $_a['id_promo'];
  }
  
/*
 *
 * name: Promo_obd::inicia_codpromocional
 * @param
 * @return devolve a primeira promoción non caducada asociada a un codigo promocional.
 *
 */
  public static function inicia_codpromocional(FS_cbd $cbd, $id_site, $codigo) {
    $id_promo = self::busca_codpromocional($cbd, $id_site, $codigo);

    if ($id_promo == null) return null;
    

    return Promo_obd::inicia($cbd, $id_site, $id_promo);
  }

/*
 *
 * name: Promo_obd::aplicar
 *
 * @param pvp_0         => unidade para a cal se aplicará o desconto
 * @param $total_0 = -1 => daremos un prezo promcional, sen ter en conta o total da compra
 * @param $codigo IN (null, -1, alfa). Ver Promo_obd::verificar
 *
 * @return real
 *
 */
  public function aplicar($pvp_0, $total_0 = -1, $codigo = -1) {
    if (!$this->verificar($total_0, $codigo)) return $pvp_0;


    return $pvp_0 - $this->calcula_dto($pvp_0);
  }

  public function calcula_dto($pvp_0) {
    if ($this->atr("tipo_dto")->valor == "r") return 0; //* promos tipo 'r' restan 0 ao importe da compra.
    
    if ($this->atr("tipo_dto")->valor == "a") return $this->atr("dto")->valor;


    return ($pvp_0 * ($this->atr("dto")->valor / 100));
  }

/*
 *
 * name: Promo_obd::verificar
 *
 * controla se podemos aplicar unha promoción (non fai a verificación por artículo e datas)
 *
 * @param $total_0 = -1  => daremos un prezo promcional, sen ter en conta o total da compra
 * @param $codigo = null => necesario se é unha promoción con código promocional
 *        $codigo = -1   => sempre omite a comprobación de código.
 *
 * @return boolean
 *
 */
  public function verificar($total_0 = -1, $codigo = null) {
    //~ if ($this->atr("tipo_dto")->valor == "r") return false;

    
    $tok = (($total_0 == -1) || ($total_0 > $this->atr("compra_min")->valor));


    if ($codigo == -1) return $tok;
    
    if ($this->atr("codigo")->valor == null) return $tok;

    if ($codigo == null) return false;
    
    
    if (!$tok) return false;


    return ($this->atr("codigo")->valor == $codigo);
  }

/*
 *
 * name: Promo_obd::verificar_articulo
 *
 * verifica se un artículo pertence a unha promación
 *
 * @param $id_articulo
 *
 * @return boolean
 *
 */
  public function verificar_articulo($id_articulo, FS_cbd $cbd = null) {
    if ($this->atr("tipo")->valor == "g") return true;
    
    $_a = $this->a_articulos($cbd);

    return isset( $_a[$id_articulo] );
  }

  public function html_ventas($detallado = false) {
    return $this->atr("nome")->valor;
  }

  public function html_carro() {
    $d = $this->atr("dto")->valor;
    
    if ($this->atr("tipo_dto")->valor == "a") {
      $td = "€";

      $d *= -1;
    }
    else {
      $td = "%";
    }
          
    return "OFERTA&nbsp;{$d}{$td}";
  }

  public static function _a(int $id_site, ?string $tipo = null, ?bool $activa = true, ?string $predet = "···", FS_cbd $cbd = null):array {
    if ($cbd == null) $cbd = new FS_cbd();

    
    $wh_s = "id_site = {$id_site}";
    $wh_t = ($tipo === null)?"1":"tipo = '{$tipo}'";
    $wh_a = (!$activa      )?"1":"activa = 1";

    $sql = "select id_promo, nome, activa from v_promo where ({$wh_s}) and ({$wh_t}) and ({$wh_a}) order by nome";

    $r = $cbd->consulta($sql);

    $_p = ($predet == null) ? [] : ["**" => $predet];
    while ($_r = $r->next())  {
      $k = str_pad($_r["id_promo"], 4, "0", STR_PAD_LEFT);
      $a = ($_r["activa"] == 1)?" - ACTIVA":"";
      
      $_p[ $_r['id_promo'] ] = "{$k} - {$_r['nome']}{$a}";
    }


    return $_p;
  }

  public static function a_tipo($nulo = false) {
    $a = null;

    if ($nulo) $a['**'] = "...";

    $a['g'] = "Promo global";
    $a['p'] = "Promo x producto";

    return $a;
  }

  public static function a_tipoDesc($nulo = false) {
    $a = null;

    if ($nulo) $a['**'] = "...";

    $a['p'] = "% - porcentaje";
    $a['a'] = "€ - euros";
    //~ $a['r'] = "★ - regalo(s)";


    return $a;
  }


/*
 *
 * name: Promo_obd::a_promos
 * @param $t IN (-1, 0, 1). -1 == todas, 0 == xproducto, 1 == global.
 * @param $id_articulo. 
 * @return devolve promocións non caducadas asociadas a un site.
 *
 */

  public static function a_promos($id_site, $tdto, $t = -1, FS_cbd $cbd = null) {
    if ($cbd == null) $cbd = new FS_cbd();

    $d = date("Ymd") . "000000";

    $wh_s    = "(id_site = {$id_site})";
    $wh_d    = "(data_ini <= '{$d}' and data_fin > '{$d}')";
    $wh_tdto = "(tipo_dto = '{$tdto}')";
    $wh_t    = "1=1";
    
        if ($t == 0) $wh_t = "tipo = 'p'";
    elseif ($t == 1) $wh_t = "tipo = 'g'";

    $sql = "select id_promo from promo where {$wh_s} and {$wh_d} and {$wh_t} and {$wh_tdto}";


  
    $r  = $cbd->consulta($sql);

    
    $_p = null;
    while ($_a = $r->next()) {
      $_p[ $_a["id_promo"] ] = Promo_obd::inicia($cbd, $id_site, $_a["id_promo"]);
    }
      
    return $_p;
  }

  public function a_articulos(FS_cbd $cbd = null, $regalo = false, $inicia_obd = false) {
    if ($this->atr("id_promo")->valor == null) return null;

    if ($cbd == null) $cbd = new FS_cbd();

    $wh = "id_site  = " . $this->atr("id_site")->valor  . " and
           id_promo = " . $this->atr("id_promo")->valor . " and
           regalo   = " . (($regalo)?"1":"0");

    $sql = "select id_articulo from promo_producto where {$wh}";

    $r = $cbd->consulta($sql);

    $a = null;
    while ($a2 = $r->next()) {
      if ($inicia_obd) {
        //* ollo, non inicia presupostos.
        $arti = Articulo_obd::inicia($cbd, $this->atr("id_site")->valor, $a2['id_articulo']);
      }
      else
        $arti = true;
      
      $a[ $a2['id_articulo'] ] = $arti;
    }

    return $a;
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
    if ($this->atr("id_promo")->valor == null) return $this->insert($cbd);


    return $cbd->executa($this->sql_update($cbd));
  }

  public function delete(FS_cbd $cbd) {
    $sql = "delete from promo_producto where id_site = " . $this->atr("id_site")->valor . " and id_promo = " . $this->atr("id_promo")->valor;

    if (!$cbd->executa($sql)) return false;

    return $cbd->executa($this->sql_delete($cbd));
  }

  private function calcula_id(FS_cbd $cbd) {
    $id_site = $this->atr("id_site")->valor;

    if (!$cbd->executa("insert into promo_seq (id_site) values ({$id_site})")) return false;

    $r = $cbd->consulta("select LAST_INSERT_ID() as id");

    if (!$a = $r->next()) return false;

    $cbd->executa("delete from promo_seq where id_site = {$id_site} and id_promo < {$a['id']}");

    $this->atr("id_promo")->valor = $a['id'];

    return true;
  }
}

//-----------------------------------------------

final class Promo_mbd extends Mapa_bd {
  public function __construct() {
    $t = new Taboa_dbd("promo");

    $t->pon_campo("id_site"   , new Numero(), true);
    $t->pon_campo("id_promo"  , new Numero(), true);
    $t->pon_campo("tipo");
    $t->pon_campo("tipo_dto");
    $t->pon_campo("nome");
    $t->pon_campo("dto"       , new Numero());
    $t->pon_campo("compra_min", new Numero());
    $t->pon_campo("codigo");
    $t->pon_campo("data_ini"  , new Data("Y-m-d"));
    $t->pon_campo("data_fin"  , new Data("Y-m-d"));

    $this->pon_taboa($t);
  }
}

