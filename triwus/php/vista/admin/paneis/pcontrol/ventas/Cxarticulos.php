<?php

final class Cxarticulos extends Componente implements ICPC {
  const ptw_1 = "ptw/paneis/pcontrol/ventas/articulo/cxarticulos_1.html";
  const ptw_2 = "ptw/paneis/pcontrol/ventas/articulo/cxarticulos_2.html";

  public $_cheq  = [];

  public $id_site    = null;
  public $id_almacen = null;

  public function __construct($id_site, $id_almacen) {
    parent::__construct("cxarticulos");

    $this->id_site    = $id_site;
    $this->id_almacen = $id_almacen;


    $this->pon_obxeto(Panel_fs::__bmais    ("bEngadir" , "Crear un producto", "A&ntilde;adir un nuevo producto al cat&aacute;logo" ));
    $this->pon_obxeto(Panel_fs::__bexportar("bExportar", "Exportar cat&aacute;logo", "Descarga los productos seleccionados"));
    $this->pon_obxeto(Panel_fs::__bimportar("bImportar", "Importar productos", "Importar un archivo CSV"));

    $this->pon_obxeto(new CXA_ficha($id_site, $id_almacen));
    $this->pon_obxeto(new CXA_lista($id_site, $id_almacen, new CXA_ehtml()));

    $this->pon_obxeto(new Cxarticulos_busca($id_site, $id_almacen));

    $this->pon_obxeto(new Cxarticulos_importar($id_site, $id_almacen));

    $this->pon_obxeto( self::_smasivo() );
    $this->pon_obxeto( Cxarticulos_masivo::inicia() );

    $this->pon_obxeto(new Param("msx"));

    $this->pon_obxeto( new Checkbox("cheq_0", false) );

    $this->configurar();


    $this->obxeto("cheq_0")->pon_eventos("onclick", "pcontrol_cxarquivo_check0_click(this)");

    //~ $this->obxeto("bEngadir" )->envia_ajax("onclick");
    $this->obxeto("bExportar")->envia_MIME("onclick");
    $this->obxeto("bImportar")->envia_ajax("onclick");
  }

  public function configurar($c = 0) {
    if ($c == 0) {
      $this->ptw = self::ptw_1;

      $this->obxeto("cxal")->mini(false);

      return;
    }

    $this->ptw = self::ptw_2;

    $this->obxeto("cxal")->mini(true);
  }

  public function buscar_promo() {
    return $this->obxeto("cxab")->buscar_promo();
  }

  public function cpc_miga() {
    return "productos";
  }

  public function seleccionado() {
    return $this->obxeto("cxaf")->seleccionado();
  }

  public function id_grupo() {
    return $this->obxeto("cxaf")->id_grupo();
  }

  public function operacion(EstadoHTTP $e) {
//~ echo $e->evento()->html();

    if ($this->obxeto("smasivo" )->control_evento()) return $this->operacion_smasivo ($e);

    if ($this->obxeto("bEngadir" )->control_evento()) return $this->operacion_articulo ($e);
    if ($this->obxeto("bExportar")->control_evento()) return $this->operacion_bExportar($e);
    if ($this->obxeto("bImportar")->control_evento()) return $this->operacion_bImportar($e);

    if (($e_aux = $this->obxeto("cxaf")->operacion($e)) != null) return $e_aux;
    if (($e_aux = $this->obxeto("cxab")->operacion($e)) != null) return $e_aux;
    if (($e_aux = $this->obxeto("cxal")->operacion($e)) != null) return $e_aux;
    if (($e_aux = $this->obxeto("cxai")->operacion($e)) != null) return $e_aux;
    if (($e_aux = $this->obxeto("cxam")->operacion($e)) != null) return $e_aux;


    return null;
  }

  public function recarga_articulo(Epcontrol $e, $id_articulo = null) {
    $this->obxeto("cxaf")->reinicia();
  }

  public function operacion_articulo(Epcontrol $e, $id_articulo = null) {
    $this->configurar(1);

    $a    = Articulo_obd::inicia(new FS_cbd(), $this->id_site, $id_articulo);

    $cxaf = $this->obxeto("cxaf");

    $cxaf->pon_articulo($a, $id_articulo == null);


    //~ if ($id_articulo != null) {
      $this->preparar_saida($e->ajax());

      $cxaf->obxeto("notas")->preparar_saida($e->ajax());

      $e->ajax()->pon_ok(true, "pcontrol_cab_0()");
    //~ }

    return $e;
  }

  public function operacion_cheq(Epcontrol $e, $id_articulo = null) {
    $cbd = new FS_cbd();

    $a = Articulo_obd::inicia($cbd, $this->id_site, $id_articulo);

    $id_grupo_cc = $a->atr("id_grupo_cc")->valor;

//~ echo "$id_articulo,  $id_grupo_cc<pre>" . print_r($this->_cheq, 1) . "</pre>";

    if (( $id_grupo_cc == null ) || ( $id_articulo != $id_grupo_cc )) {
      $this->operacion_cheq_swtich( $id_articulo );

//~ echo "1111111111111<pre>" . print_r($this->_cheq, 1) . "</pre>";


      $e->ajax()->pon_ok();

      return $e;
    }


    $r = $cbd->consulta("select distinct id_articulo from v_grupo_cc where id_site = {$this->id_site} and id_grupo_cc = {$id_grupo_cc}");

    while($_r = $r->next()) $this->operacion_cheq_swtich( $_r["id_articulo"] );

//~ echo "2222222222222<pre>" . print_r($this->_cheq, 1) . "</pre>";


    $this->obxeto("cxal")->preparar_saida($e->ajax());

    return $e;
  }

  public function operacion_bborrar(Epcontrol $e, $id_articulo = null) {
    if ($id_articulo == null) return $e;

    $cbd = new FS_cbd();

    $cbd->transaccion();

    $a = Articulo_obd::inicia($cbd, $this->id_site, $id_articulo);

    if (!$a->delete($cbd)) {
      $cbd->rollback();

      return $e;
    }

    //~ $cbd->rollback();
    $cbd->commit();


    return $this->obxeto("cxaf")->operacion_bCancelar($e);
  }

  public function operacion_bExportar(Epcontrol $e, $where = -1, $limit = -1) {
    if ($where == -1) $where = $this->obxeto("cxab")->__where_exportar();

    $eCSV = new Cxarticulos_exportar( $where, $limit );

    $titulo = "trw-productos-" . date("YmdHis") . ".csv";

    $e->pon_mime(null, "text/csv", $eCSV->escribe(), null, $titulo);

    return $e;
  }

  public function operacion_bImportar(Epcontrol $e) {
    $this->obxeto("cxai")->visible = true;

    $this->obxeto("cxai")->preparar_saida($e->ajax());

    return $e;
  }

  public function operacion_buscador(Epcontrol $e, $where = null) {
    $this->obxeto("cxal")->limit_0 = 0;

    $this->obxeto("cxal")->__where($where);

//~ echo $this->obxeto("cxal")->sql_vista() . "<br>";

    $this->preparar_saida($e->ajax());

    return $e;
  }

  public function operacion_smasivo(Epcontrol $e) {
    $this->pon_obxeto( Cxarticulos_masivo::inicia( $this->obxeto("smasivo")->valor(), $this->id_site ) );


    $this->preparar_saida($e->ajax());

    return $e;
  }

  public function update(FS_cbd $cbd, Articulo_obd $a, $_campos = null, $_pubs = null) {
    //~ $a->atr("id_almacen")->valor = $this->id_almacen;

    if ($a->atr("id_articulo")->valor == null) return $a->insert($cbd, $_campos, $_pubs);


    return $a->update($cbd, $_pubs);
  }

  private function operacion_cheq_swtich( $id_articulo ) {
//~ echo $id_articulo . "::";
    if ( isset( $this->_cheq[$id_articulo] ) )
      unset( $this->_cheq[$id_articulo] );
    else
      $this->_cheq[$id_articulo] = 1;
  }

  private static function _smasivo() {
    $_o_00 = ["**"   => "··· Selecciona la operación masiva",
             ];

    $_o_10 = ["del"  => "Eliminar"
             ];
    $_o_20 = ["cat"  => "Cambiar"
             ];
    $_o_30 = ["nov"  => "Destacar",
              "dnov" => "Quitar destacados"
             ];
    $_o_40 = ["pro"  => "Añadir",
              "dpro" => "Quitar promociones"
             ];
    $_o_50 = ["pub"  => "Publicar",
              "dpub" => "Quitar publicados"
             ];

    $s = new Select("smasivo");

    $s->pon_grupo(1, "Borrar productos");
    $s->pon_grupo(2, "Categorías");
    $s->pon_grupo(3, "Destacar productos");
    $s->pon_grupo(4, "Promociones");
    $s->pon_grupo(5, "Publicar productos");

    $s->options($_o_00   );
    $s->options($_o_10, 1);
    $s->options($_o_20, 2);
    $s->options($_o_30, 3);
    $s->options($_o_40, 4);
    $s->options($_o_50, 5);

    //~ $s->envia_submit("onchange");
    $s->envia_ajax("onchange");

    return $s;
  }
}

//******************************************

final class Cxarticulos_exportar extends EscritorResultado_csv {
  private $url_base_imx = null;

  public function __construct($where, $limit = -1) {
    parent::__construct( self::iterador($where, $limit) );
  }

  protected function cabeceira($df = null) {
    $this->url_base_imx = null;

    return "\"ref_triwus\";\"ref_externa\";\"ean\";\"tipo\";\"id_almacen\";\"nome\";\"pvp\";\"id_ive\";\"pive\";\"moeda\";\"unidades\";\"desc_u\";\"desc_p\";\"servizo_dias\";\"servizo_permisos\";\"pedido_min\";\"pedido_max\";\"peso\";\"id_marca\";\"marca\";\"id_categoria\";\"categoria\";\"id_grupo_cc\";\"tupla_cc\";\"imx\";\"notas\"\r\n";
  }

  protected function linha_detalle($df, $f) {
    $not = ($f['notas'] == null)?"":strip_tags($f['notas']);
    
    $pvp = number_format(floatval($f['pvp'] ), 2, ",", "");
    $pes = number_format(floatval($f['peso']), 2, ",", "");
    
    $url = $this->url_base($f["id_site"]) . $f["imx"];

    return "{$f['ref_triwus']};\"{$f['ref_externa']}\";\"{$f['ean']}\";\"{$f['tipo']}\";\"{$f['id_almacen']}\";\"{$f['nome']}\";{$pvp};\"{$f['id_ive']}\";{$f['pive']};\"{$f['moeda']}\";{$f['unidades']};{$f['desc_u']};{$f['desc_p']};{$f['servizo_dias']};\"{$f['servizo_permisos']}\";{$f['pedido_min']};{$f['pedido_max']};{$pes};\"{$f['id_marca']}\";\"{$f['marca']}\";\"{$f['id_categoria']}\";\"{$f['categoria']}\";\"{$f['id_grupo_cc']}\";\"{$f['tupla_cc']}\";\"{$url}\";\"{$not}\"\r\n";
  }

  private static function iterador($where, $limit) {
    $l = ($limit != -1)?" limit {$limit}":"";

    $sql = "select * from v_articulo_exportar a where {$where}{$l}";

//~ echo "$sql<br>";

    $cbd = new FS_cbd();

    return $cbd->consulta($sql);
  }

  private function url_base($id_site) {
    if ($this->url_base_imx != null) return $this->url_base_imx;

    $this->url_base_imx = Efs::url_base( $id_site ) . Refs::url_arquivos . "/";

    return $this->url_base_imx;
  }
}

//******************************************

final class Cxarticulos_busca extends Componente {
  const ptw_1 = "ptw/paneis/pcontrol/ventas/articulo/cxarticulos_busca.html";

  private $id_site    = null;
  private $id_almacen = null;

  public function __construct($id_site, $id_almacen) {
    parent::__construct("cxab", self::ptw_1);


    $this->id_site    = $id_site;
    $this->id_almacen = $id_almacen;


    $this->pon_obxeto(new Span("msx_erro"));

    $this->pon_obxeto(Panel_fs::__bbusca   ("bBuscar", null, null));
    $this->pon_obxeto(Panel_fs::__bcancelar(null, null, "bLimpa"));

    $this->pon_obxeto( self::__stipo     ()         );
    $this->pon_obxeto( self::__sive      ()         );
    $this->pon_obxeto( self::__salmacen  ($id_site, $id_almacen) );
    $this->pon_obxeto( self::__smarca    ($id_site, $id_almacen) );
    $this->pon_obxeto( self::__spromo    ($id_site, false) );
    $this->pon_obxeto( self::__spaxina   ($id_site) );
    $this->pon_obxeto( self::__scategoria($id_site) );
    $this->pon_obxeto( self::__sdesc     ()         );

    $this->pon_obxeto(Panel_fs::__text("ct_buscador", 11, 111, " "));

    $this->obxeto("ct_buscador")->clase_css("default", "tbuscador"  );

    $this->obxeto("bBuscar")->pon_eventos("onclick", "cxaf_gardando(this)");

    $this->obxeto("bLimpa" )->pon_eventos("onclick", "cxaf_gardando(this)");
    $this->obxeto("bLimpa" )->visible = false;
  }

  public function operacion(EstadoHTTP $e) {
    $this->obxeto("msx_erro")->post(null);

    if ($this->obxeto("bBuscar")->control_evento()) return $this->operacion_buscador($e);
    if ($this->obxeto("bLimpa" )->control_evento()) return $this->operacion_limpa   ($e);

    return null;
  }


  public function buscar_promo() {
    $p = $this->obxeto("spromo")->valor();

    if ($p == "**") return false;
    if ($p == ""  ) return false;


    return true;
  }

  public function operacion_busca_txt(Epcontrol $e, $txt) {
    $e = $this->operacion_limpa($e);

    $this->obxeto("ct_buscador")->post($txt);


    return $this->operacion_buscador($e);
  }

  private function operacion_buscador(Epcontrol $e, $vblimpa = true) {
    if (($erro = $this->__validar()) != null) {
      $this->obxeto("msx_erro")->post($erro);

      return $e;
    }

    $this->obxeto("bLimpa")->visible = $vblimpa;

    return $this->pai->operacion_buscador($e, $this->__where());
  }

  private function operacion_limpa(Epcontrol $e) {
    $this->obxeto("bLimpa")->visible = false;

    if (!$this->obxeto("salmacen")->readonly) $this->obxeto("salmacen")->post("");

    $this->obxeto("ct_buscador")->post("");
    $this->obxeto("smarca"     )->post("");
    $this->obxeto("scategoria" )->post("");
    $this->obxeto("spaxina"    )->post("");
    $this->obxeto("stipo"      )->post("");
    $this->obxeto("spromo"     )->post("");
    $this->obxeto("sive"       )->post("");


    return $this->operacion_buscador($e, false);
  }

  private function _param() {
    $_param = [];

    $_param['ct_buscador'] = $this->obxeto("ct_buscador")->valor();
    $_param['salmacen'   ] = $this->obxeto("salmacen"   )->valor();
    $_param['smarca'     ] = $this->obxeto("smarca"     )->valor();
    $_param['scategoria' ] = $this->obxeto("scategoria" )->valor();
    $_param['spaxina'    ] = $this->obxeto("spaxina"    )->valor();
    $_param['stipo'      ] = $this->obxeto("stipo"      )->valor();
    $_param['spromo'     ] = $this->obxeto("spromo"     )->valor();
    $_param['sive'       ] = $this->obxeto("sive"       )->valor();
    $_param['sdesc'      ] = $this->obxeto("sdesc"      )->valor();


    return $_param;
  }

  private function __validar() {
    return null;
  }

  public function __where() {
    $a_param = $this->_param();

    //~ echo "<pre>" . print_r($a_param, true) . "</pre>";

    $wh_busca = Valida::buscador_txt2sql($a_param['ct_buscador'], array("a.id_articulo", "a.nome", "a.ean", "a.ref_externa", "a.notas"));

    if ($wh_busca == null) $wh_busca = "(1=1)";

    $id_ive = $a_param['sive'];
        if ($id_ive == "**"  ) $wh_ive = "(1=1)";
    elseif ($id_ive == ""    ) $wh_ive = "(1=1)";
    else                       $wh_ive = "(a.id_ive = {$id_ive})";

    $id_alm = $a_param['salmacen'];
        if ($id_alm == "**"  ) $wh_alm = "(1=1)";
    elseif ($id_alm == ""    ) $wh_alm = "(1=1)";
    else                       $wh_alm = "(a.id_almacen = {$id_alm})";

    $id_mar = $a_param['smarca'];
        if ($id_mar == "**"  ) $wh_mar = "(1=1)";
    elseif ($id_mar == ""    ) $wh_mar = "(1=1)";
    else                       $wh_mar = "(a.id_marca = {$id_mar})";

    $id_cat = $a_param['scategoria'];
        if ( $id_cat == "**"         ) $wh_cat = "(1=1)";
    elseif ( $id_cat == null         ) $wh_cat = "(1=1)";
    elseif ( !is_numeric($id_cat)    ) $wh_cat = "(1=1)";
    elseif ( $a_param['sdesc'] == "2") $wh_cat = "(a.id_categoria = {$id_cat})";
    else                               $wh_cat = "(a.id_categoria = {$id_cat} or c.id_pai = {$id_cat} or c.id_avo = {$id_cat})";

    $id_paxina = $a_param['spaxina'];
        if ($id_paxina == "**"  ) $wh_paxina = "(1=1)";
    elseif ($id_paxina == ""    ) $wh_paxina = "(1=1)";
    else                          $wh_paxina = "(b.id_paxina = {$id_paxina})";


    $id_promo = $a_param['spromo'];
    $wh_promo = ($this->buscar_promo()) ? "(d.id_promo = {$id_promo})":"(1=1)";

    $id_tipo = $a_param['stipo'];
        if ($id_tipo == "**" ) $wh_tipo = "(1=1)";
    elseif ($id_tipo == ""   ) $wh_tipo = "(1=1)";
    else                       $wh_tipo = "(a.tipo = '{$id_tipo}')";



    $where = "{$wh_busca} and {$wh_alm} and {$wh_mar} and {$wh_promo} and {$wh_ive} and {$wh_cat} and {$wh_paxina} and {$wh_tipo}";


    return str_replace(" and (1=1)", "", $where);
  }

  public function __where_exportar() {
    $a_param = $this->_param();

    //~ echo "<pre>" . print_r($a_param, true) . "</pre>";

    $wh_busca = Valida::buscador_txt2sql($a_param['ct_buscador'], array("a.id_articulo", "a.nome", "a.ean", "a.ref_externa", "a.notas"));

    if ($wh_busca == null) $wh_busca = "(1=1)";

    $id_ive = $a_param['sive'];
        if ($id_ive == "**"  ) $wh_ive = "(1=1)";
    elseif ($id_ive == ""    ) $wh_ive = "(1=1)";
    else                       $wh_ive = "(a.id_ive = {$id_ive})";

    $id_alm = $a_param['salmacen'];
        if ($id_alm == "**"  ) $wh_alm = "(1=1)";
    elseif ($id_alm == ""    ) $wh_alm = "(1=1)";
    else                       $wh_alm = "(a.id_almacen = {$id_alm})";

    $id_mar = $a_param['smarca'];
        if ($id_mar == "**"  ) $wh_mar = "(1=1)";
    elseif ($id_mar == ""    ) $wh_mar = "(1=1)";
    else                       $wh_mar = "(a.id_marca = {$id_mar})";

    $id_cat = $a_param['scategoria'];
        if ( $id_cat == "**"      ) $wh_cat = "(1=1)";
    elseif ( $id_cat == null      ) $wh_cat = "(1=1)";
    elseif ( !is_numeric($id_cat) ) $wh_cat = "(1=1)";
    elseif ( $id_cat == "1"       ) $wh_cat = "(a.id_categoria = {$id_cat})";
    else                            $wh_cat = "(a.id_categoria = {$id_cat} or c.id_pai = {$id_cat})";

    $id_tipo = $a_param['stipo'];
        if ($id_tipo == "**" ) $wh_tipo = "(1=1)";
    elseif ($id_tipo == ""   ) $wh_tipo = "(1=1)";
    else                       $wh_tipo = "(a.tipo = '{$id_tipo}')";


    $where = "(a.id_site = {$this->id_site}) and {$wh_busca} and {$wh_alm} and {$wh_mar} and {$wh_ive} and {$wh_cat} and {$wh_tipo}";


    return str_replace(" and (1=1)", "", $where);
  }

  public static function __salmacen($id_site, $id_almacen, $predet = "...") {
    $s = new Select("salmacen", Almacen_obd::_almacen($id_site, $predet));

    if ($id_almacen < 2) return $s;

    $s->readonly = true;

    $s->post($id_almacen);


    return $s;
  }

  public static function __smarca($id_site, $id_almacen = null) {
    $_o = Articulo_marca_obd::_marca($id_site, null);

    $s = new Select("smarca", $_o);


    return $s;
  }

  public static function __scategoria($id_site) {
    $s = new DataList("scategoria", Categoria_obd::_migas($id_site));

    $s->style("default", "width: 23em;");

    return $s;
  }

  public static function __spaxina($id_site, $predet = "...") {
    $sql = "select id_paxina, id_idioma, if(titulo is null, nome_paxina, titulo) as nome_paxina from v_site_paxina where id_site = {$id_site} and tipo = 'ventas2' order by nome_paxina";

    $cbd = new FS_cbd();

    $r = $cbd->consulta($sql);

    if ($predet != null) $a2['**'] = $predet;
    while ($a = $r->next()) $a2[$a['id_paxina']] = "{$a['id_paxina']} {$a['nome_paxina']} ({$a['id_idioma']})";


    $s = new Select("spaxina", $a2);

    $s->style("default", "width: 99%;");

    return $s;
  }

  public static function __spromo($id_site, $activa = true) {
    $s = new Select("spromo", Promo_obd::_a($id_site, "p", $activa));

    $s->post("**");

    return $s;
  }

  public static function __sive() {
    $s = new Select("sive", Articulo_obd::a_tipoive("..."));


    return $s;
  }

  public static function __stipo() {
    $s = new Select("stipo", Articulo_obd::a_tipo(true, "..."));


    return $s;
  }

  public static function __sdesc() {
    $options = [
      "1"=> "Sí",
      "2"=> "No"
    ];

    $s = new Select("sdesc", $options);

    return $s;
  }
}

//******************************************

class CXA_lista extends  FS_lista {
  public    $where_aux      = null;

  protected $id_site        = null;

  private   $id_almacen     = null;
  private   $mini           = false;

  public function __construct($id_site, $id_almacen, CXA_ehtml $ehtml) {
    parent::__construct("cxal", $ehtml);


    $this->id_site    = $id_site;
    $this->id_almacen = $id_almacen;

    $this->__where();
    $this->orderby = "a.id_articulo desc";

    $this->limit_f = 10;
  }

  public function __where($where = null) {
    //*
    $this->borra_sublistas();

    $this->where     = null;
    //*
    $where2  = "a.id_site = {$this->id_site}";

    if ($this->id_almacen > 1) $where2 .= " and a.id_almacen = {$this->id_almacen}";

    if ($where != null) $where2 = "({$where2}) and ({$where})";

    //*
    $this->selectfrom = str_replace( "[where]", " and ({$where2})", $this->selectfrom_aux($where != null) );

    $this->where_aux  = $where2;

//~ echo $this->sql_vista() . "<br>";
  }

  public function _cheq() {
    return $this->pai->_cheq;
  }

  public function mini($mini = -1) {
    if ($mini === -1) return $this->mini;

    $this->mini = $mini;

    return $this->mini;
  }

  public function seleccionado() {
    return $this->pai->seleccionado();
  }

  public function operacion(EstadoHTTP $e) {
    $evento = $e->evento();


    if ($this->control_fslevento($evento, "borrar")) {
      $e_aux = $this->pai->operacion_bborrar($e, $evento->subnome(0));

      $this->control_limits_sup();

      return $e_aux;
    }

    if ($this->control_fslevento($evento, "id_articulo")) {
      return $this->pai->operacion_articulo($e, $evento->subnome(0));
    }

    if ($this->control_fslevento($evento, "cheq")) {
      return $this->pai->operacion_cheq($e, $evento->subnome(0));
    }

    return parent::operacion($e);
  }

  public function sublista_prototipo($id_rama) {
//~ echo  "<br/>$id_rama::<br/>";
    list($id_grupo_cc, $id_articulo) = explode("@", $id_rama);

    if ($id_grupo_cc == null) return null;


    return new CXA_lista_2($this->id_site, $id_grupo_cc, $id_articulo, new CXA_ehtml_2());
  }

  public function selectfrom_aux(bool $promo = false) {
    $j_promo = "";
    if ( $promo ) {
      if ( $this->pai->buscar_promo() ) {
        $j_promo = "inner join promo_producto d on (a.id_site = c.id_site and a.id_articulo = d.id_articulo)";
      }
    }

    return "
select distinct a.id_site, a.id_articulo, a.unidades, a.nome, a.ean, a.desc_p, a.notas, a.id_grupo_cc, a.tupla_cc, momento_novo
  from (
     select a.*
       from articulo a left join articulo_publicacions b on (a.id_site = b.id_site and a.id_articulo  = b.id_articulo)
                       left join v_categoria           c on (a.id_site = c.id_site and a.id_categoria = c.id_categoria)
                       {$j_promo}
      where (not a.id_grupo_cc is null) [where]
      group by a.id_site, a.id_grupo_cc
          union
     select a.*
       from articulo a left join articulo_publicacions b on (a.id_site = b.id_site and a.id_articulo  = b.id_articulo)
                       left join v_categoria           c on (a.id_site = c.id_site and a.id_categoria = c.id_categoria)
                       {$j_promo}
      where (a.id_grupo_cc is null) [where]
  ) a";

  }
}

//---------------------------------

class CXA_ehtml extends FS_ehtml {
  const style_td_2 = "style='color: #707070;'";
  const style_td_3 = "style='color: #707070;'";

  protected $nivel = 1;
  protected $ct    = 1;

  private static $_pive = null;

  public function __construct() {
    parent::__construct();

    $this->class_table = "lista_arti_0";
    $this->style_table = "";
    $this->align       = "center";
    $this->width       = "100%";
    $this->cellspacing = 0;
    $this->cellpadding = 0;
    $this->border      = 0;
  }

  protected function cabeceira($df = null) {
    $this->ct = -1;

    if ($this->xestor->mini()) {
      $h = "<th>
              <div class='larti_cab_x'>
                <div>" . $this->__lOrdenar_html("id_articulo", "#Ref") . "</div>
                <div>" . $this->__lOrdenar_html("nome,id_articulo", "Nombre") . "</div>
                <div>" . $this->__lOrdenar_html("unidades", "Uds") . "</div>
                <div>" . $this->__lOrdenar_html("pvpive", "PVP") . "</div>
              </div>
            </th>";
    }
    else {
      $cheq_0 = ""; if ($this->xestor->pai->obxeto("cheq_0") != null) $cheq_0 = $this->xestor->pai->obxeto("cheq_0")->html();

      $h = "<th>
              <div class='larti_cab_x'>
                <div>{$cheq_0}</div>
                <div>ORDENAR: </div>
                <div>" . $this->__lOrdenar_html("id_articulo", "#Ref") . "</div>
                <div>" . $this->__lOrdenar_html("nome,id_articulo", "Nombre") . "</div>
                <div>" . $this->__lOrdenar_html("tupla_cc,id_articulo", "Custom") . "</div>
                <div>" . $this->__lOrdenar_html("unidades", "Uds") . "</div>
                <div>" . $this->__lOrdenar_html("pvpive", "PVP") . "</div>
                <div>" . $this->__lOrdenar_html("ean", "EAN") . "</div>
              </div>
            </th>";

      //~ $h = "<th>ORDENAR: </th>";
    }

    return "<thead>
            <tr>
              {$h}
            </tr>
            </thead>
            <tbody id='tartiBody'>";
  }

  protected function linha_detalle($df, $f) {
    $this->ct++;

    $id_rama = "{$f['id_grupo_cc']}@{$f['id_articulo']}";

    if ($this->nivel == 1) if ($f['id_grupo_cc'] != null) $this->rama_check($id_rama);

    if ($this->xestor->mini()) return $this->linha_detalle_1($df, $f);

    return $this->linha_detalle_2($df, $f);
  }

  protected function linha_detalle_1($df, $f) {
    $id_rama     = "{$f['id_grupo_cc']}@{$f['id_articulo']}";
    $id_articulo = $f['id_articulo'];


    $a_obd = Articulo_obd::inicia(new FS_cbd(), $f['id_site'], $id_articulo);

    $style_tr   = ($this->xestor->seleccionado() != $id_articulo)?FS_ehtml::style_tr:FS_ehtml::style_tr_a;

    $style_td_2 = self::style_td_2;

    $pvp  = number_format($a_obd->pvp(1, true, true), 2, ",", ".") . "<small>&euro;</small>";

    if (isset($f['desc_p'])) if ($f['desc_p'] > 0) $pvp = "<span style='color: #0b0;' title='Descuento directo = {$f['desc_p']}%'>{$pvp}</span>";

    $html = "<tr {$style_tr}>
               <td>
                 <div class='larti_grid_1'>
                   <div>" . $this->__bmais_html($id_rama) . "</div>
                   <div>" . self::__imx($this, $a_obd) . "</div>
                   <div class='larti_grid_1_div3'>
                     <div>
                       <div>" . self::__larticulo($this, $f) . "</div>
                     </div>
                     <div>
                       <div>"  . self::__pub($a_obd) . "</div>
                       <div>"  . self::__destacado($a_obd) . "</div>
                       <div>" . Articulo_obd::ref($id_articulo) . "</div>
                       <div>{$f['ean']}</div>
                       <div>{$f['unidades']}</div>
                       <div>{$pvp}</div>
                     </div>
                   </div>
                   <div>" . self::__bborrar($this, $id_articulo) . "</div>
                 </div>
               </td>
            </tr>";


    if ($this->nivel != 1) return $html;

    if ($f['id_grupo_cc'] == null) return $html;

    if (($rama_html = $this->rama_html($id_rama)) == null) return $html;


    return "{$html}
            <tr style='background-color: transparent;'>
              <td>{$rama_html}</td>
            </tr>";

/*
    if ($this->nivel != 1) return $html;

    if ($f['id_grupo_cc'] == null) return $html;

    if (($rama_html = $this->rama_html($id_rama)) == null) return $html;


    return "{$html}
            <tr style='background-color: #transparent;'>
              <td>{$rama_html}</td>
            </tr>";
*/
  }

  protected function linha_detalle_2($df, $f) {
    $id_rama     = "{$f['id_grupo_cc']}@{$f['id_articulo']}";
    $id_articulo = $f['id_articulo'];


    $a_obd      = Articulo_obd::inicia(new FS_cbd(), $f['id_site'], $id_articulo);

    $style_tr   = ($this->xestor->seleccionado() != $id_articulo)?FS_ehtml::style_tr:FS_ehtml::style_tr_a;

    $style_td_2 = self::style_td_2;

    $pvp  = number_format($a_obd->pvp(1, true, true), 2, ",", ".") . "<small>&euro;</small>";

    if (isset($f['desc_p'])) if ($f['desc_p'] > 0) $pvp = "<span style='color: #0b0;' title='Descuento directo = {$f['desc_p']}%'>{$pvp}</span>";

    $html = "<tr {$style_tr}>
               <td>
                 <div class='larti_grid_2'>
                   <div>" . self::__cheq($this, $f) . "</div>
                   <div>" . $this->__bmais_html($id_rama) . "</div>
                   <div>" . self::__imx($this, $a_obd) . "</div>
                   <div class='larti_grid_2_div3'>
                     <div>
                       <div>"  . self::__pub($a_obd) . "</div>
                       <div>"  . self::__destacado($a_obd) . "</div>
                       <div>" . Articulo_obd::ref($id_articulo) . "</div>
                       <div>" . self::__larticulo($this, $f) . "</div>
                       <div>{$f['unidades']}<small>uds</small></div>
                       <div>{$pvp}</div>
                     </div>
                     <div>
                       <div>" . self::__describe($f) . "</div>
                     </div>
                   </div>
                   <div>" . self::__bborrar($this, $id_articulo) . "</div>
                 </div>
               </td>
            </tr>";


    if ($this->nivel != 1) return $html;

    if ($f['id_grupo_cc'] == null) return $html;

    if (($rama_html = $this->rama_html($id_rama)) == null) return $html;


    return "{$html}
            <tr cxarticulos_grupo='{$id_rama}' style='background-color: transparent;'>
              <td>{$rama_html}</td>
            </tr>";
  }

  protected function totais() {
    $ct = "";
    if (!$this->xestor->mini()) $ct = $this->xestor->__count() . "&nbsp;productos&nbsp;";

    return "</tbody>
            <tfoot>
            <tr style='color: #707070; background-color: #fff;'>
              <td align=center style='white-space: nowrap;padding-top: 11px;'>{$ct}" . $this->__paxinador_html() . "</td>
            </tr>
            </tfoot>";
  }

  protected function rama_html($id_rama) { //* REDEFINE CLista_ehtml.rama_html()
    if ($id_rama == null) return "";

    $sublista = $this->xestor->sublista($id_rama);

    if ($sublista == null) return "";

    $sublista->__where( $this->xestor->where_aux );

//~ echo $sublista->sql_vista();

    return $sublista->html();
  }

  protected static function __imx(CXA_ehtml $ehtml, Articulo_obd $a) {
    $cbd = new FS_cbd();

    $wh    = ($ehtml->xestor->mini())?"3em":"4em";

    $style = "width: {$wh}; height: {$wh};
              background-image:url(" . $a->iclogo_obd($cbd)->url(false, $cbd) . ");
              background-position: center center;
              background-size: cover;";


    $dimx = new Div("logo");

    $dimx->style("default", $style);


    return $ehtml->__fslControl($dimx, $a->atr("id_articulo")->valor)->html();
  }

  protected static function __pub(Articulo_obd $a) {
    $css = ""; if ($a->publicado()) $css = "color: #00ba00;";

    return "<span style='{$css}'>P</span>";
  }

  protected static function __destacado(Articulo_obd $a) {
    if (!$a->destacado()) return "";


    return "<span title='" . $a->atr("momento_novo")->valor . "' style='color: #1E90FF;'>&#9733;</span>";
  }

  protected static function __cheq(CXA_ehtml $ehtml, $f) {
    if ($ehtml->xestor->mini()) return "";

//~ echo "<pre>" . print_r() . "</pre>";

    //~ $cheq_0 = $ehtml->xestor->pai->obxeto("cheq_0")->valor();
    $_cheq = $ehtml->xestor->_cheq();

    //* inicia obxeto e engade á lista.
    $b = new Checkbox( "cheq", isset( $_cheq[$f['id_articulo']] )  );

    $b->title = "Selecciona producto";

    $b->pon_eventos("onclick", "cxaf_gardando(this)");
    //~ $b->envia_submit("onclick");

    return $ehtml->__fslControl($b, $f['id_articulo'])->html();
  }

  protected static function __larticulo(CXA_ehtml $ehtml, $f) {
    //* calcula 'n'.
    $split = ($ehtml->xestor->mini())?27:77;

    $n = Valida::split($f['nome'], $split) . " {$f['tupla_cc']}";

    //* inicia obxeto e engade á lista.
    $b = new Div("id_articulo", $n);

    $b->clase_css("default", "lista_elemento_a");

    $b->title = "Editar producto";

    $b->pon_eventos("onclick", "cxaf_gardando(this)");
    //~ $b->envia_submit("onclick");

    return $ehtml->__fslControl($b, $f['id_articulo'])->html();
  }

  protected static function __describe($f) {
    if ($f['notas']              == null) return "";
    if (($n = trim($f['notas'])) == ""  ) return "";

    return Valida::split( strip_tags($n), 77);
  }

  protected static function __notas($f) {
    return Valida::split($f['notas'], 99);
  }

  protected static function __ive($f) {
    if (self::$_pive == null) self::$_pive = Articulo_obd::a_tipoive();


    return self::$_pive[ $f['id_ive'] ] . "<small>%</small>";
  }

  protected static function __bborrar(CXA_ehtml $ehtml, $id) {
    if ($ehtml->xestor->readonly) return "";

    $t = "<span style='color: #ba0000;font-size: 1.3em;'>♻</span>";

    if ($ehtml->xestor->mini()) {
      $b = new Div("borrar", $t);

      $b->style("default", "cursor:pointer;");
    }
    else
      $b = Panel_fs::__button("borrar", $t);

    $b->title = "Eliminar el producto";

    $b->pon_eventos("onclick", "bborra_carticulo(this)");

    return $ehtml->__fslControl($b, $id)->html();
  }

}

//******************************************

class CXA_lista_2 extends FS_lista {
  //~ public $mini = false;

  private $id_site     = null;
  private $id_articulo = null;
  private $id_grupo_cc = null;

  public function __construct($id_site, $id_grupo_cc, $id_articulo, CXA_ehtml_2 $ehtml) {
    parent::__construct("cxal2", $ehtml);

    $this->id_site     = $id_site;
    $this->id_articulo = $id_articulo;
    $this->id_grupo_cc = $id_grupo_cc;

    $this->selectfrom = $this->selectfrom_aux();

    $this->where = null;
    $this->orderby = "a.id_articulo desc";

    $this->limit_f = 10;
  }

  public function __where($where = null) {

    $this->where     = null;
    //*
    $where2  = "a.id_site = {$this->id_site} and a.id_grupo_cc = {$this->id_grupo_cc} and a.id_articulo <> {$this->id_articulo}";


    if ($where != null) $where2 = "({$where2}) and ({$where})";

    //*
    $this->selectfrom = str_replace( "[where]", "({$where2})", $this->selectfrom_aux($where != null) );
//~ echo $this->sql_vista() . "<br>";
  }

  public function _cheq() {
    return $this->pai->_cheq();
  }

  public function mini($mini = -1) {
    return $this->pai->mini();
  }

  public function seleccionado() {
    return $this->pai->pai->seleccionado();
  }

  public function operacion(EstadoHTTP $e) {
    $evento = $e->evento();


    if ($this->control_fslevento($evento, "borrar")) {
      $e_aux = $this->pai->pai->operacion_bborrar($e, $evento->subnome(0));

      //~ $this->control_limits_sup();

      return $e_aux;
    }

    if ($this->control_fslevento($evento, "id_articulo")) {
      return $this->pai->pai->operacion_articulo($e, $evento->subnome(0));
    }

    if ($this->control_fslevento($evento, "cheq")) {
      return $this->pai->pai->operacion_cheq($e, $evento->subnome(0));
    }

    return FS_lista::operacion($e);
  }


  public function selectfrom_aux(bool $promo = false) {
    $j_promo = "";
    if ( $promo ) {
      if ( $this->pai->pai->buscar_promo() ) {
        $j_promo = "inner join promo_producto d on (a.id_site = d.id_site and a.id_articulo = d.id_articulo)";
      }
    }

    return "
  select distinct a.id_site, a.id_articulo, a.unidades, a.nome, a.ean, a.notas, a.id_grupo_cc, a.tupla_cc
    from articulo a left join articulo_publicacions b on (a.id_site = b.id_site and a.id_articulo = b.id_articulo)
                    left join v_categoria c on (a.id_site = c.id_site and a.id_categoria = c.id_categoria)
                    {$j_promo}
   where [where]";

  }

  protected function html_baleiro() {
    if (!$this->visible) return "";

    return "<div style='margin: 0.5em 0 0.5em 27px;color: #538953;font-weight: bold;'>No hay más artículos en el grupo {$this->id_grupo_cc}.</div>";
  }
}

//---------------------------------

class CXA_ehtml_2 extends CXA_ehtml {
  public function __construct() {
    parent::__construct();

    $this->nivel = 2;

    $this->class_table = "lista_arti_1";
  }
}
