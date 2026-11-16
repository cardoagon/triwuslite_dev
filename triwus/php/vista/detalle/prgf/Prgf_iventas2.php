<?php

final class Prgf_iventas2 extends    Paragrafo
                          implements IPrgf_iventas {

  const ptw_0 = "ptw/paragrafo/elmt/elmt_ventas_post-mobil.html";

  public $id_site    = null;
  public $id_paxina  = null;
  public $config     = null;

  private bool $vpa       = true;

  private $pax_i     = 0;
  private $pax_t     = 30;

  private $_al       = null; //* Almacen_obd[]. caché de almacén.

  private $where_aux = null; //* Where auxiliar.

  private $sql_vista = 0;    //* vble. aux. para depuración. Almacena a derradeira sql executada en post_hb().

  public function __construct(Paxina_ventas2_obd $p, $editor) {
    parent::__construct($p->prgf_indice(), "prgf", false);

    $this->id_site        = $p->atr("id_site"  )->valor;
    $this->id_paxina      = $p->atr("id_paxina")->valor;

    $this->alg_html_elmts = 8;
    $this->usa_bpos       = false;
    $this->excluir        = 1;

    $this->vpa            = (Site_config_obd::inicia(new FS_cbd(), $this->id_site)->atr("ventas_producto_agotados")->valor == 1);

    $this->pon_obxeto(new Hidden("_hb", 1)); //* canal E/S. solicitude de artículos (cxcli.js).

    $this->pon_obxeto(new Hidden("_h0")); //* canal E. lista id_articulos.

    $this->pon_obxeto(new Div   ("_h1")); //* canal S. resposta html.

    $this->pon_obxeto(new Hidden("_h2")); //* canal E. solicitude agregar artículo ao carriño.


    $this->pon_obxeto(new Ediventas_buscador($this->id_site, $this->id_paxina, $this->id_prgf,
                                             $this->iventas_categoria, $p->atr("tipo")->valor
                                            )
                     );


    $this->obxeto("_h1")->style("default", "display: none;");
  }

  public function declara_js() {
    return array(Refs::url("js/ventas2.js"));
  }

  public function inicia_ptw(Config_obd $c) {
    $this->config = $c;
  }

  public function where_aux($where = -1) {
    //* GETTER/SETTER

    if ($where != -1) $this->where_aux = $where;

    if ($this->where_aux != null) return $this->where_aux;

    if (($where2 = $this->obxeto("ccatalogo")->sql_where()) == "(1)") return null;


    return $where2;
  }

  public function operacion(EstadoHTTP $e) {
//~ echo $e->evento()->html();

    if (($e_aux = $this->operacion_url_filtrado($e))         != null) return $e_aux;

    if (($e_aux = $this->obxeto("ccatalogo")->operacion($e)) != null) return $e_aux;

    if ($this->obxeto("_hb")->control_evento()) return $this->operacion_hb($e);
    if ($this->obxeto("_h0")->control_evento()) return $this->operacion_h0($e);
    if ($this->obxeto("_h2")->control_evento()) return $this->operacion_h2($e);


    return parent::operacion($e);
  }

  public function operacion_editar(Epaxina_edit $e) {
    $e->obxeto("detalle_btnr_oeste")->pon_editor($e, new Editor_prgf_ventas2($this));

    return $e;
  }

  public function operacion_reset(IEfspax_xestor $e, $where = null) {
    //* carga articulos segundo a seleción do buscador de triwus.

    $this->obxeto("_h1")->post(null);

    $this->post_hb($where, true);

    $e->ajax()->pon($this->obxeto("_hb"), true, "ventas2_b0_resposta(" . $this->json_o(true) . ")");


    return $e;
  }

  public function html_ventas2() {
    return
$this->obxeto("_hb")->html() .
$this->obxeto("_h0")->html() .
$this->obxeto("_h1")->html() .
$this->obxeto("_h2")->html() . "
<input type='hidden' id='trw-catalogo-id' value='" . $this->nome_completo() . "' />" .
$this->html_buscador() . "
<div id='cxcli3-lanuncios-0'>
  <div id='cxcli3-lanuncios-0-panel'></div>
</div>

<script>
  ventas2_ini(" . $this->json_o(true) . ");
</script>";
  }

  protected function pon_btnr() {
    $this->pon_obxeto(new Btnr_prgf(false));
  }

  private function operacion_hb(EstadoHTTP $e) {
    //* inicia artículos na páxina de resumo.


    $reinicia = ($this->obxeto("_hb")->valor() == 1);

    $this->post_hb($this->where_aux(), $reinicia);


    $e->ajax()->pon( $this->obxeto("_hb") );


    return $e;
  }

  private function operacion_h0(EstadoHTTP $e) {
    $_h0 = $this->obxeto("_h0");
    $_h1 = $this->obxeto("_h1");

    $_a = explode(",", $_h0->valor());

    $cbd = new FS_cbd();

    $html = ""; for ($i = 0; $i < count($_a); $i++) $html .= $this->html_elmt($cbd, $_a[$i]);

//~ echo "<pre>" . print_r($_a, true) . "</pre>";

    $_h1->post($html);

    $e->ajax()->pon( $_h1 );

    return $e;
  }

  private function operacion_h2(EstadoHTTP $e) {
    $_h2 = $this->obxeto("_h2");


    $cbd = new FS_cbd();

    $a = Articulo_obd::inicia($cbd, $this->id_site, $_h2->valor());

    $u_carro = $e->usuario()->cpago()->carro();

    $i_carro = $u_carro->post($a, -1, $a->atr("pedido_min")->valor());    //~ $html = "<pre>" . print_r($_a, true) . "</pre>";

    //~ $_h1->post($html);

    $html = LectorPlantilla::plantilla( Refs::url(self::ptw_0) );

    $json = Elmt_ventas::__bcarrito_resposta_json($u_carro, $i_carro, $html, false, $e->url_limpa);

    $e->ajax()->pon_ok( true, "elmt_ventas_postResposta({$json})" );

    return $e;
  }

  private function operacion_url_filtrado(EstadoHTTP $e) {
//~ echo $e->evento()->html();

    //* 1.- control de evento de filtrado.
    if ($e->evento()->tipo() != "op-reservada") return null;

    if ($e->evento()->nome(0) != "filtrado") return null;

    if (!is_array($e->evento()->nome(1))) return null;

    $_p = $e->evento()->nome(1);

//~ echo "Prgf_iventas2:: $this->iventas_buscador<br>";

    if ($this->iventas_buscador == 1) {
      //* executa filtrado con buscador trw (buscador predeterminado).
      $this->obxeto("ccatalogo")->operacion_txt($_p);

      $this->obxeto("ccatalogo")->operacion_f1($_p);
      $this->obxeto("ccatalogo")->operacion_f2($_p);

      return $e;
    }



    return $e;
  }

  private function html_elmt(FS_cbd $cbd, $id_articulo) {
    //* inicia $elmt.
    $e = new Elmt_ventas_clr_resumo($this->id_site, $id_articulo, $this->vpa, $this->config, $cbd);

    if (!$e->visible) return "";


    //* configura $elmt.
    $id_c = $this->obxeto("_h2")->nome_completo();

    $e->config_urls($id_c, $this->pai->__editor);

    
    //* devolve html
    return "<div class='ventas_div_c'>" . $e->html() . "</div>";
  }

/*
  private function html_elmt(FS_cbd $cbd, $id_articulo) {
    list($_gcc, $ar) = self::inicia_gcc($cbd, $this->id_site, $id_articulo, $this->vpa);
    
//~ print_r($_gcc);
//~ print_r($ar->a_resumo());
   
    //* inicia $e.
    $e = new Elmt_ventas_clr($ar, $this->config, false, false, $cbd);

    if (!$e->visible) return "";


    //* configura $e.
    $id_c = $this->obxeto("_h2")->nome_completo();

    $e->config_ventas2($id_c, $ar, null, $this->pai->__editor);


    //* escribe html_cc en $e.
    $e->obxeto("elmt_cc")->post( self::html_gcc($_gcc) );

    
    //* devolve html
    return "<div class='ventas_div_c'>" . $e->html() . "</div>";
  }
*/
  private function html_buscador() {
    if ($this->iventas_buscador != 1) return "";

    $html = LectorPlantilla::plantilla( Refs::url("ptw/paragrafo/prgf_cbuscador.html") );

    $html = str_replace("[ccatalogo]", $this->obxeto("ccatalogo")->html(), $html);

    return $html;
  }

  private function json_o($limpar = false) {
    $id_js     = $this->nome_completo();
    $limpar2   = ($limpar)?"1":"0";
    $b_visible = ($this->iventas_buscador == 1)?"1":"0";

    $_o = array("s"      => $this->id_site,
                "busca"  => array("_p"        => null,
                                  "visible"   => $b_visible,
                                  "categoria" => $this->iventas_categoria,
                                  "a"         => $this->iventas_buscador == 2
                                 ),
                "id_js"  => "{$id_js}",
                "limpar" => "{$limpar2}",
                "pax_t"  => "{$this->pax_t}",
                "a"      => "{$this->action}"
               );

    if ($this->sql_vista != null) $_o["sql_vista"] = $this->sql_vista;

    return json_encode($_o);
  }


  private function post_hb($where = null, $reinicia = true) {
    //* 1.- Calcula sql.
    //~ $w = "(id_site = {$this->id_site} and id_paxina = {$this->id_paxina})"; //*** SEN control de categoría do prgf.

    //*** CON control de categoría do prgf.
    $w = "(a.id_site = {$this->id_site} and h.id_paxina = {$this->id_paxina})";
    if ($this->iventas_categoria > 1) {
      $w .= " and (a.id_categoria = {$this->iventas_categoria} or c.id_pai = {$this->iventas_categoria} or c.id_avo = {$this->iventas_categoria})";
    }

    if (!$this->vpa) {
      $w .= " and (unidades >= pedido_min)";
    }

    if ($where != null) $w .= " and ({$where})";


    $sql = "
select distinct id_articulo
  from (
    select distinct a.id_articulo, a.nome, h.momento
      from articulo a inner join articulo_publicacions h on (a.id_site = h.id_site and a.id_articulo = h.id_articulo)
                      left  join v_categoria           c on (a.id_site = c.id_site and a.id_categoria = c.id_categoria)
    where ({$w}) and (a.id_grupo_cc is null)
    union
    select id_articulo, nome, momento
    from (
        select id_grupo_cc, a.id_articulo, a.nome, desc_p, pvp, h.momento
          from articulo a inner join articulo_publicacions h on (a.id_site = h.id_site and a.id_articulo = h.id_articulo)
                          left  join v_categoria           c on (a.id_site = c.id_site and a.id_categoria = c.id_categoria)
        where ({$w}) and (not id_grupo_cc is null)
        order by desc_p desc, pvp asc
         ) a1
    group by a1.id_grupo_cc
) a2
" . $this->orderby() . $this->limit($reinicia);

//~ echo $sql;

    //* 2.- Lee id_articulo[] na BD.

    $cbd = new FS_cbd();

    $r = $cbd->consulta($sql);

    $_a = array(); while ($_r = $r->next()) $_a[] = $_r['id_articulo'];


//~ echo "<pre>" . print_r($_a, true) . "</pre>";


    //* 3.- Asigna id_articulo[] ao buffer _hb.

    $this->obxeto("_hb")->post( json_encode($_a) );
  }


  private function orderby() {
    $o = " order by ";

    if ($this->orderby == 1) return "{$o}momento desc";

    if ($this->orderby == 2) return "{$o}momento asc";

    if ($this->orderby == 3) return "{$o}nome asc";

    return "{$o}rand()"; //* opción ($this->orderby == 9) predeterminada.
  }

  private function limit($reinicia = false) {
    //* 1.- actualiza contadores.
    $this->pax_i = ($reinicia)?"0":$this->pax_i + $this->pax_t;

    //* 2.- crea a instrucción sql limit, ($l).
    if (($i = $this->pax_i) == 0) $i = "0";

    $l = " limit {$i}, {$this->pax_t}";

    //* 3.- devolve a instrucción sql limit.
    return $l;
  }

  private static function inicia_gcc(FS_cbd $cbd, int $id_site, int $id_articulo, bool $vpa):array {
    /* Inicia o grupo_cc, e selecciona o primeiro artículo con stock dispoñible.
     */

    $ar = Articulo_obd::inicia($cbd, $id_site, $id_articulo);

//~ print_r($ar->a_resumo());

    $_gcc = Articulo_cc_obd::_grupo_resumo($id_site, $ar->atr("id_grupo_cc")->valor, $vpa, $cbd); //* so buscamos artículos NON vpa.

//~ print_r($_gcc);

    if ($ar->atr("unidades")->valor >= $ar->atr("pedido_min")->valor) return [$_gcc, $ar]; 

    foreach($_gcc as $n => $_x) {
      if ($_x["a"] == $id_articulo) continue; //* xa foi evaluado e xa sabemos que non vale.
      
      if ($_x["u"] < $_x["m"]) continue;
      
      $ar = Articulo_obd::inicia($cbd, $id_site, $_x["a"]);
            
      return [$_gcc, $ar];
    }


    return [$_gcc, $ar]; //* se non atopamos ningún con unidades
  }

  private static function html_gcc(array $_gcc):string {
    if ($_gcc == []) return "";

//~ print_r($_gcc, 1);
    
    $html_cc = "";
    foreach($_gcc as $n => $_x) {
      $html_cc .= ($_x["u"] > $_x["m"]) ? "<div unid='{$_x["u"]}'>{$n}</div>" : "<div class='elmt_cc__sin-stock'>{$n}</div>";
    }

    return "<div class='elmt_cc'>
              <div class='elmt_cc__btn'></div>
              <div class='elmt_cc__lista'>{$html_cc}</div>
            </div>";
  }
}


//*********************************************


//* DEBEMOS definir unha interface, IEdiventas_buscador, que deberán implementar Prgf_iventas2 e Prgf_imarcas.

final class Ediventas_buscador extends Componente {
  const ptw_0 = "ptw/paragrafo/prgf_iventas_busca.html";

  public $id_site;
  public $id_paxina;
  public $id_prgf;
  public $id_categoria;
  public $tipo;

/*
 *
 * name: Ediventas_buscador::__construct
 * @param $id_site
 * @param $id_prgf
 * @param $id_categoria
 * @param $tipo
 * @return
 *
 */

  public function __construct($id_site, $id_paxina, $id_prgf, $id_categoria, $tipo) {
    parent::__construct("ccatalogo", Refs::url(self::ptw_0));

    $this->id_site      = $id_site;
    $this->id_paxina    = $id_paxina;
    $this->id_prgf      = $id_prgf;
    $this->id_categoria = $id_categoria;
    $this->tipo         = $tipo;

    $this->pon_obxeto(new Param("pmarcas" ));
    $this->pon_obxeto(new Param("psubcats"));

    $this->pon_obxeto(new Hidden("hevento"));


    $this->inicia_buscador();
  }

  public function operacion(EstadoHTTP $e) {
//~ echo $e->evento()->html();

    if ($this->obxeto("hevento")->control_evento()) return $this->operacion_hevento($e);


    return null;
  }

  public function operacion_f(EstadoHTTP $e, $_p) {
//~ echo "<pre>" . print_r($_p, true) . "</pre>";

    //* asigna categorias.
    $ok = $this->operacion_f1($_p);
    $ok = $this->operacion_f2($_p);

    //~ if (!$ok) return $e;

    return $this->operacion_buscar($e);
  }

  public function sql_where() {
    //* crea un where para a vista 'v_articulo'.

    //~ return $this->where_categoria() . " and " . $this->where_marca() . " and " . $this->where_pvp();

    return $this->where_txt() . " and " . $this->where_categoria() . " and " . $this->where_marca();
  }

  public function preparar_saida(Ajax $a = null) {
    $this->obxeto("hevento" )->post( null                  );
    $this->obxeto("psubcats")->post( $this->html_subcats() );
    $this->obxeto("pmarcas" )->post( $this->html_marcas()  );

    parent::preparar_saida($a);
  }

  private function operacion_hevento(EstadoHTTP $e) {
    $hevento = $this->obxeto("hevento");

    switch ($hevento->valor()) {
      case "f":
        return $this->operacion_buscar($e);

      case "l":
        $this->inicia_buscador();

        return $this->operacion_buscar($e);
    }


    $e->ajax()->pon_ok();

    return $e;
  }

  private function operacion_buscar(EstadoHTTP $e) {
    //* realiza búsquedas de artículo en 'v_articulo'.
    $wh = $this->sql_where();

    return $this->pai->operacion_reset($e, $wh);
  }

  private function where_txt() {
    $txt = $this->obxeto("txt")->valor();

    $wh  = Valida::buscador_txt2sql( $txt, array("a.nome", "a.notas") );

    if ($wh == "") return "(1)";

    return "({$wh})";
  }

  private function where_categoria() {
    $_ch = $this->obxetos("chSubcat");

    if (count($_ch) == 0) return "(1)";

    $wh = null;
    foreach($_ch as $ch) {
      if (!$ch->valor()) continue;

      list($x, $id_subcat) = explode(Escritor_html::csubnome, $ch->nome());

      if ($wh != null) $wh .= ", ";

      $wh .= $id_subcat;
    }

    if ($wh == null) return "(1)";


    return "((a.id_categoria IN ({$wh})) or (c.id_pai IN ({$wh})))";
  }

  private function where_marca() {
    $_ch = $this->obxetos("chMarca");

    if (count($_ch) == 0) return "(1)";

    $wh = null;
    foreach($_ch as $ch) {
      if (!$ch->valor()) continue;

      list($x, $id_marca) = explode(Escritor_html::csubnome, $ch->nome());

      if ($wh != null) $wh .= ", ";

      $wh .= "{$id_marca}";
    }

    if ($wh == null) return "(1)";


    return "(id_marca in ({$wh}))";
  }

  private function where_pvp() {
    return 1;
/*
    list($m, $M) = explode(",", $this->obxeto("pvp")->valor());

    return "(pvp >= {$m} and pvp <= {$M})";
*/
  }

  private function html_subcats() {
    $_ch = $this->obxetos("chSubcat");

    if (count($_ch) == 0) return "";

    $html = null;

    foreach($_ch as $ch) $html .= "<div>" . $ch->html() . "</div>";

    if ($html == null) return "";

    return "<div class='elmt_ventas_post_4'>CATEGORÍAS</div>" . $html;
  }

  private function html_marcas() {
    //~ return "";


    $html = null;

    $_ch = $this->obxetos("chMarca");

    if (count($_ch) == 0) return "";

    foreach($_ch as $ch) $html .= "<div>" . $ch->html() . "</div>";

    if ($html == null) return "";

    return "<div class='elmt_ventas_post_4'>MARCAS</div>" . $html;

  }

  private function inicia_buscador() {
    $this->inicia_txt();

    $cbd = new FS_cbd();

    $ok0 = $this->inicia_categorias($cbd, $this->id_site, $this->id_prgf, $this->id_categoria);
    $ok1 = $this->inicia_marcas    ($cbd, $this->id_site, $this->id_prgf);
    //~ $this->inicia_pvp       ($cbd);


    $this->visible = ($ok0 || $ok1);
  }

  private function inicia_txt() {
    $t = new Text("txt");

    $t->pon_atr("trw_novas2_busca_etq_0", "1");

    $t->placeholder = "Buscar";

    $t->pon_eventos("oninput", "prgf_inovas_clear(this)");

    $this->pon_obxeto( $t );
  }

  private function inicia_categorias(FS_cbd $cbd) {
    //* 1.- Conta artículos por categoria
    
    if ($this->id_categoria != 1) {
      $sql = "  select distinct b.id_categoria, b.nome as categoria
                  from articulo a inner join categoria             b on (a.id_site = b.id_site and a.id_categoria = b.id_categoria)
                                  inner join articulo_publicacions c on (a.id_site = c.id_site and a.id_articulo  = c.id_articulo )
                 where a.id_site = {$this->id_site} and c.id_paxina = {$this->id_paxina}
                   and (a.id_categoria = {$this->id_categoria} or b.id_pai = {$this->id_categoria})
              order by b.nome";
    }
    else {
      $sql = "select distinct b.id_categoria, b.nome as categoria
                from v_articulo_ventas2 a inner join categoria b on (a.id_site = b.id_site and (a.id_categoria = b.id_categoria or a.id_categoria_pai = b.id_categoria) and b.id_pai = 1)
               where (a.id_site = {$this->id_site}) and (a.id_paxina = {$this->id_paxina})
            order by b.nome";
    }


    $r = $cbd->consulta($sql);

    $_ct = null;
    while ($_a = $r->next()) $_ct[ $_a['id_categoria'] ] = $_a['categoria'];

    if ($_ct == null) return false;


    foreach($_ct as $k=>$v) {
      $this->pon_obxeto(self::__checker("chSubcat", $v, ""), $k);
    }


    return true;
  }

  private function inicia_marcas(FS_cbd $cbd) {
    //* 1.- Conta artículos por marca

    $sql = "select distinct a.id_marca, b.nome as marca 
              from articulo a inner join articulo_marca        b on (a.id_site = b.id_site and a.id_marca    = b.id_marca   )
                              inner join articulo_publicacions c on (a.id_site = c.id_site and a.id_articulo = c.id_articulo)
                              
             where (a.id_site = {$this->id_site}) and (c.id_paxina = {$this->id_paxina}) and (a.unidades > 0)
            order by b.nome";


    $r = $cbd->consulta($sql);

    $_ct = null;
    while ($_a = $r->next()) $_ct[ $_a['id_marca'] ] = $_a['marca'];


    //* 2.- Crea Controis.
    if (!is_array($_ct)) return;

    foreach($_ct as $k=>$v) $this->pon_obxeto(self::__checker("chMarca", $v, ""), $k);
  }

  private function inicia_pvp(FS_cbd $cbd) {
    //* 1.- Calculamos límites mín e máx.

    if ($this->tipo == "ventas") {
      $sql = "select TRUNCATE(min(pvp), 0) as m, round(max(pvp), 0) as M
                from v_paragrafo_elmtventas
               where id_paragrafo = {$this->id_prgf} and unidades > 0
            group by id_categoria";
    }
    elseif ($this->tipo == "cxcli") {
      $sql = "select TRUNCATE(min(pvp), 0) as m, round(max(pvp), 0) as M
                from v_paragrafo_cxcli
               where (id_site = {$this->id_site})
                 and (id_categoria = {$this->id_categoria} or id_categoria_pai = {$this->id_categoria})
            group by id_categoria";
    }
    elseif ($this->tipo == "ventas2") {
      $sql = "select TRUNCATE(min(pvp), 0) as m, round(max(pvp), 0) as M
                from v_paragrafo_cxcli
               where (id_site = {$this->id_site})
                 and (id_categoria = {$this->id_categoria} or id_categoria_pai = {$this->id_categoria})
            group by id_categoria";
    }
    else
      return;


    $r = $cbd->consulta($sql);

    if ($_a = $r->next()) {
      $m = $_a['m'];
      $M = $_a['M'];
    }
    else {
      $m = 0;
      $M = 1000;
    }

    //* 2.- Crea Control.
    $t = new MultiRange("pvp", $m, $M, 1, "€");


    $this->pon_obxeto($t);
  }

  public function operacion_txt($_p) {
    $t = ( isset($_p["f0"]) )? $_p["f0"] : "";
    
    $this->obxeto("txt")->post( $t );

    return true;
  }

  public function operacion_f1($_p) {
    $this->reset("chSubcat");

    if (!isset($_p['f1'])) return false;

    $_ch = $this->obxetos("chSubcat");

    if (count($_ch) == 0) return false;

    $ok = false;

    $_p1 = explode(",", $_p['f1']);

    for ($i = 0; $i < count($_p1); $i++) {
      if (($o = $this->obxeto("chSubcat", $_p1[$i])) == null) continue;

      $ok = true;

      $o->post(true);
    }

    return $ok;
  }

  public function operacion_f2($_p) {
    $this->reset("chMarca");

    if (!isset($_p['f2'])) return false;

    $_ch = $this->obxetos("chMarca");

    $ok = false;

    $_p2 = explode(",", $_p['f2']);

    for ($i = 0; $i < count($_p2); $i++) {
      if (($o = $this->obxeto("chMarca", $_p2[$i])) == null) continue;

      $ok = true;

      $o->post(true);
    }

    return $ok;
  }

  private function reset($ch_tipo) {
    $_ch = $this->obxetos($ch_tipo);

    if (count($_ch) == 0) return true;

    foreach($_ch as $k=>$ch) $ch->post(false);


    return true;
  }

  private static function __checker($id, $etq, $ct) {
    //~ $ch = new Checkbox($id, false, "{$etq} ({$ct})");
    $ch = new Checkbox($id, false, $etq);

    return $ch;
  }
}


