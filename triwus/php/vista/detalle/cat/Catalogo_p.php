<?php

final class Catalogo_p extends Catalogo {  

  const ptw_0 = "ptw/paragrafo/elmt/elmt_ventas_post-mobil.html"; //* MODAL resposta "bComprar". Para plantillas q amosan o botón comprar na vista de resumo.

  public function __construct(ICatalogo_fonte $icp, bool $editor, Config_obd $c, string $action, bool $hai_busca = true) {
    parent::__construct($icp, $editor, $c, $action, $hai_busca);

    $this->pon_obxeto(new Hidden("_h2")); //* canal E. solicitude agregar artículo ao carriño.
  }


  public function operacion(EstadoHTTP $e) {
    if ($this->obxeto("_h2")->control_evento()) return $this->operacion_h2($e);

    return parent::operacion($e);
  }
  
  public function html():string {
    return $this->obxeto("_h2")->html() . parent::html();
  }

  public function _busca(ICatalogo_fonte $icp, bool $vpa, string $action, bool $hai_busca):Cat_busca { //* IMPLEMENTA Catalogo._busca()
    return new CatP_busca($icp, $vpa, $action, $hai_busca);
  }
  
  public function _lista(ICatalogo_fonte $icp, bool $editor, Config_obd $c_obd, Site_config_obd $sc_obd, string $action):Cat_lista { //* IMPLEMENTA Catalogo._lista()
    return new CatP_lista($icp, $editor, $c_obd, $sc_obd, $action);
  }

  protected function operacion_h2(EstadoHTTP $e) {
    $_h2 = $this->obxeto("_h2");


    $cbd = new FS_cbd();

    $a = Articulo_obd::inicia($cbd, $this->id_site, $_h2->valor());

    $u_carro = $e->usuario()->cpago()->carro();

    $i_carro = $u_carro->post($a, -1, $a->atr("pedido_min")->valor());

    $html = LectorPlantilla::plantilla( Refs::url(self::ptw_0) );

    $json = Elmt_ventas::__bcarrito_resposta_json($u_carro, $i_carro, $html, false, $e->url_limpa);

    $e->ajax()->pon_ok( true, "elmt_ventas_postResposta({$json})" );

    return $e;
  }
}


//*********************************************


final class CatP_lista extends Cat_lista {
  public function __construct(ICatalogo_fonte $icp, bool $editor, Config_obd $c_obd, Site_config_obd $sc_obd, string $action) {
    parent::__construct($icp, $editor, $c_obd, $sc_obd, $action);
    
    $this->pon_obxeto(new Hidden("hlistar"));
    
    $this->obxeto("hlistar")->pon_atr("hlistar", 1);
  }

  public function operacion(EstadoHTTP $e) {
    if ($this->obxeto("hlistar")->control_evento()) return $this->operacion_hlistar($e);
    

    return parent::operacion($e);
  }


  protected function _ehtml():Cat_ehtml { //* IMPLEMENTA Cat_lista._ehtml()
    return new CatPlista_ehtml();
  }

  private function operacion_hlistar(EstadoHTTP $e):EstadoHTTP {
    $s  = $this->id_site;
    
    //~ if ($s != 124) return $this->operacion_hlistar_elmt($e);
    
    $a  = $this->obxeto("hlistar")->valor();
    
    if ($a == null) {
      $e->ajax()->pon_ok(true, json_encode(""));      

      return $e;
    }


    $elmt = new CatProducto_html($s, $a, true, $this->c_obd, $this->sc_obd);
    
    $e->ajax()->pon_ok(true, json_encode($elmt->html()));
    

    return $e;
  }

  //~ private function operacion_hlistar_elmt(EstadoHTTP $e):EstadoHTTP { //* en deshuso
    //~ $s  = $this->id_site;
    //~ $a  = $this->obxeto("hlistar")->valor();
    //~ $c  = $this->c_obd;
    //~ $sc = $this->sc_obd;
    //~ $ed = $this->editor;

    //~ $elmt = new Elmt_ventas_FSLproducto($s, $a, $ed, true, $c, $sc);
    
    //~ $html = ($elmt->visible) ? $elmt->html() : "";
    
    
    //~ $e->ajax()->pon_ok(true, json_encode($html));
    

    //~ return $e;
  //~ }
}

//-----------------------------------------

final class CatPlista_ehtml extends Cat_ehtml {
  private string $ptw_0;
  
  public function __construct() {
    parent::__construct();
  }
  
  protected function inicia_saida() {
    $this->ptw_0 = "";
    
    return $this->xestor->obxeto("hlistar")->html();
  }

  protected function finaliza_saida() {
    $this->ptw_0 = "";
    
    return "<script>trwCat_listar()</script>";
  }

  protected function iten_html($df, $f):string { //* IMPLEMENTA Cat_ehtml.iten_html()  
    return "<div k='{$f["id_articulo"]}'>{$this->ptw_0}</div>";
  }

/*
  protected function iten_html($df, $f):string { //* IMPLEMENTA Cat_ehtml.iten_html()
    $s  = $this->xestor->id_site;
    
    //~ if ($s != 124) return $this->iten_html_0($df, $f);
    
    $a  = $f["id_articulo"];
    $c  = $this->xestor->c_obd;
    $sc = $this->xestor->sc_obd;

    $elmt = new CatProducto_html($s, $a, true, $c, $sc);
    
    //~ if (!$elmt->visible) return "";
    
    return $elmt->html();
  }

  protected function iten_html_0($df, $f):string {
//~ return "<div><pre>" . print_r($f, 1) . "</pre></div>";
    $s  = $this->xestor->id_site;
    $a  = $f["id_articulo"];
    $c  = $this->xestor->c_obd;
    $sc = $this->xestor->sc_obd;
    $ed = $this->xestor->editor;

    $elmt = new Elmt_ventas_FSLproducto($s, $a, $ed, true, $c, $sc);
    
    if (!$elmt->visible) return "";
    
    return $elmt->html();
  }
*/
}


//*********************************************


final class CatP_busca extends Cat_busca {
  public function __construct(ICatalogo_fonte $icp, bool $vpa, string $action, bool $hai_busca) {
    parent::__construct($icp, $vpa, $action, $hai_busca);
  }

  protected function sql_where_txt():string { //* IMPLEMENTA Cat_busca.sql_where_txt()
    $txt = $this->obxeto("txt")->valor();

    $wh  = Valida::buscador_txt2sql( $txt, array("a.nome", "a.notas") );

    if ($wh == "") return "(1)";

    return "({$wh})";
  }

  protected function sql_where_etq(int $i):string { //* IMPLEMENTA Cat_busca.sql_where_etq()
    if (($wh = $this->chEtq_comas($i)) == null) return "(1)";
    

    switch ($i) {
      case 1: return "((a.id_categoria IN ({$wh})) or (c.id_pai IN ({$wh})) or (c.id_avo IN ({$wh})))";
      case 2: return "(a.id_marca in ({$wh}))";
    }
    
    return "(1)";
  }


  protected function inicia_etq(int $i, FS_cbd $cbd, ICatalogo_fonte $icp, bool $vpa):void {//* IMPLEMENTA Cat_busca.inicia_etq()
    if (($sql = $icp->icatF__etq_sql($i, $vpa)) == null) return;
    
    $r = $cbd->consulta( $sql );

    while ($_a = $r->next()) $this->pon_obxeto(self::__checker($i, $_a["etq"]), $_a["id_etq"]);
  }
}


//*****************************************************


final class CatProducto_html {
  private ?Articulo_obd $ar     = null;
  private ?Promo_obd    $p      = null;
  private ?array        $_gcc   = null;
  private ?Config_obd   $c      = null;
  private ?bool         $ive    = null;
  private ?string       $imx    = null;
  private ?string       $action = null;
  
  public function __construct(int $s, int $a, bool $vpa, Config_obd $c, Site_config_obd $sc, FS_cbd $cbd = null) {
    if ($cbd == null) $cbd = new FS_cbd();

    list($this->_gcc, $this->ar) = self::inicia_gcc($cbd, $s, $a, $vpa);
    
    $this->ive    = $sc->atr("ventas_producto_ive")->valor == 1;
    $this->c      = $c;
    $this->p      = Promo_obd::inicia_articulo($cbd, $s, $this->ar->atr("id_articulo")->valor);
    $this->imx    = $this->ar->iclogo_obd($cbd)->url(false, $cbd);
    $this->action = $this->ar->action(true);
  }

  public function html():string {
    //~ return "<pre>" . print_r($this->_gcc, 1) . print_r($this->ar->a_resumo(), 1) . "</pre>";
    $html = LectorPlantilla::plantilla( $this->c->ptw_elmt("elmt_ventas") );
    
    $html = str_replace("[ldetalle]" , ""                      , $html);
    $html = str_replace("[clogoarti]", $this->html_clogoarti (), $html);
    $html = str_replace("[enome]"    , $this->html_nome      (), $html);
    $html = str_replace("[pvp]"      , $this->html_pvp       (), $html);
    $html = str_replace("[pvpoferta]", $this->html_pvp_oferta(), $html);
    
    if (strpos($html, "[elmt_cc]") >= 0) $html = str_replace("[elmt_cc]"  , $this->html_gcc   (), $html);
    if (strpos($html, "[doferta]") >= 0) $html = str_replace("[doferta]"  , $this->html_oferta(), $html);

    $html = $this->html_aux    ($html);
    $html = $this->html_cat    ($html);
    $html = $this->html_almacen($html);

    
    return $html;
  }

  private function html_clogoarti():string {
    $t = $this->ar->atr("nome")->valor;
    
    return "<a href='{$this->action}'><img class='celda_imaxe_ventas' style='cursor:pointer;' title='{$t}' alt='{$t}' src='{$this->imx}'></a>";
  }

  private function html_nome():string {
    $d = new Div("enome", $this->ar->atr("nome")->valor);

    $css = "ventas_titulo";
    if ($this->ar->destacado()) $css .= " ventas_destacado";

    $d->clase_css("default", $css);
    
    $d->pon_eventos("onclick", "document.location.href = '{$this->action}'");
    

    return $d->html();
  }

  private function html_oferta():string {
    $ok_p = false; if ($this->p != null) if ($this->p->atr("tipo_dto")->valor == "p") $ok_p = true;

    $txt = ($ok_p)?$this->p->html_ventas():"OFERTA";


    $d = new Div("doferta", $txt);

    $d->clase_css("default", "ventas_oferta");

    if ($ok_p)
      $d->visible = (($this->p->atr("compra_min")->valor == 0) && ($this->p->atr("codigo")->valor == null));
    else
      $d->visible = ($this->ar->atr("desc_p")->valor > 0);


    return $d->html();
  }

  private function html_pvp():string {
    $ok_p = false; if ($this->p != null) if ($this->p->atr("tipo_dto")->valor == "p") $ok_p = true;

    $pvp = $this->ar->pvp(1, $this->ive, false, null);

    $txt = number_format($pvp, 2, ",", ".") . "&nbsp;" . Articulo_obd::$a_moeda[$this->ar->atr("moeda")->valor];

    if ($ok_p) {
      if (($this->p->atr("compra_min")->valor == 0) && ($this->p->atr("codigo")->valor == null)) $txt = "<s>{$txt}</s>";
    }
    elseif ($this->ar->atr("desc_p")->valor > 0) $txt = "<s>{$txt}</s>";


    $d = new Div("pvp", $txt);

    $d->clase_css("default", "ventas_pvp");


    return $d->html();
  }

  private function html_pvp_oferta():string {
    $ok_p = false; if ($this->p != null) if ($this->p->atr("tipo_dto")->valor == "p") $ok_p = true;

    $pvp = ($ok_p)?$this->ar->pvp(1, $this->ive, true, $this->p):$this->ar->pvp(1, $this->ive, true, null);

    $txt = number_format($pvp, 2, ",", ".") . "&nbsp;" . Articulo_obd::$a_moeda[$this->ar->atr("moeda")->valor];


    $d = new Div("pvpoferta", $txt);

    $d->clase_css("default", "ventas_pvp_oferta");

    if ($ok_p)
      $d->visible = (($this->p->atr("compra_min")->valor == 0) && ($this->p->atr("codigo")->valor == null));
    else
      $d->visible = ($this->ar->atr("desc_p")->valor > 0);


    return $d->html();
  }

  private function html_gcc():string {
    if ($this->_gcc == []) return "";

//~ print_r($this->_gcc, 1);
    
    $html_cc = "";
    foreach($this->_gcc as $n => $_x) {
      $html_cc .= ($_x["u"] >= $_x["m"]) ? "<div unid='{$_x["u"]}'>{$n}</div>" : "<div class='elmt_cc__sin-stock'>{$n}</div>";
    }

    return "<div class='elmt_cc'>
              <div class='elmt_cc__btn'></div>
              <div class='elmt_cc__lista'>{$html_cc}</div>
            </div>";
  }

  private function html_aux(string $html):string {
    $html = str_replace("[aux_0]", $this->ar->atr("aux_0")->valor, $html);
    $html = str_replace("[aux_1]", $this->ar->atr("aux_1")->valor, $html);
    $html = str_replace("[aux_2]", $this->ar->atr("aux_2")->valor, $html);
  
    return $html;
  }

  private function html_cat(string $html):string {
    if (strpos($html, "[cat") == false) return $html;
    
    $_cats = Categoria_obd::_miga($this->ar->atr("id_site")->valor, $this->ar->atr("id_categoria")->valor);
    
    for($i = 0; $i < 3; $i++) {
      if (strpos($html, "[cat{$i}]") === false) continue;
      
      if ( !isset($_cats[$i]) ) {
        $html = str_replace("[cat{$i}]", "", $html);
        
        continue;
      }
      
      $div_i  = "<div class='ventas_etq-cat{$i} ventas_etq-cat{$i}_{$_cats[$i][0]}'>{$_cats[$i][1]}</div>";
            
      $html = str_replace("[cat{$i}]", $div_i, $html);
    }


    return $html;
  }

  private function html_almacen(string $html):string {
    if (strpos($html, "[vendedor_rs]") === false) return $html; //* RS, etq + básica para unha plantilla con almacén.
    
    $al = $this->ar->almacen_obd();
    $co = $al->contacto_obd();

    $html = str_replace("[vendedor_rs]" , self::html_almacen_rs ($al, $co), $html);
    $html = str_replace("[vendedor_imx]", self::html_almacen_imx($al, $co), $html);
    $html = str_replace("[vendedor_na]" , self::html_almacen_na ($al     ), $html);

//~ [vendedor_imx]

    return $html;
  }

  private static function html_almacen_rs(Almacen_obd $a, Contacto_obd $c):string {
    if (($rs = $c->atr("razon_social")->valor) == null) $rs = $c->atr("nome")->valor;
    
    $rs_l = Paxina_reservada_obd::action_almacen($a->atr("id_almacen")->valor, $rs);

    return "<a href='{$rs_l}' target='_self' class='ventas_vendedor-02'>{$rs}</a>";
  }

  private static function html_almacen_na(Almacen_obd $a):string {
    $na = $a->contar() . " productos";

    return "<div class='entas_vendedor-03'>{$na}</div>";
  }

  private static function html_almacen_imx(Almacen_obd $a, Contacto_obd $c):string {
    if (($rs = $c->atr("razon_social")->valor) == null) $rs = $c->atr("nome")->valor;
    
    $rs_l = Paxina_reservada_obd::action_almacen($a->atr("id_almacen")->valor, $rs);


    $cbd = new FS_cbd();

    if (($f = $c->iclogo_src($cbd)) == null) $f = Refs::url(Imaxe_obd::noimx);

    return "<a href='{$rs_l}' target='_self'>
              <img src='{$f}' alt='{$rs}'  title='{$rs}'  class='ventas_vendedor-01' />
            </a>";
  }

  private static function inicia_gcc(FS_cbd $cbd, int $id_site, int $id_articulo, bool $vpa):array {
    //* Inicia o grupo_cc, e selecciona o primeiro artículo con stock dispoñible.


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
}

