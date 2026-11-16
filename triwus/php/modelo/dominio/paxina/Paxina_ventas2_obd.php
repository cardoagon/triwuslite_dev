<?php

class Paxina_ventas2_obd extends  Paxina_obd
                         implements IPax_indice, ICatalogo_fonte {

  public $id_categoria    = null;
  public $id_marca        = null;
  
  public $icatF__orderby = "momento desc"; //* tmp

  public function __construct($id_paxina = null) {
    parent::__construct("ventas2", $id_paxina);
  }

  public function mapa_bd() {
    return new Paxina_mbd();
  }

  public function __indice() {
    return true;
  }

  public function insert(FS_cbd $cbd, $id_idioma, Prgf_iventas2b_obd $iprgf = null) {
    if ($iprgf == null) {
      $iprgf = new Prgf_iventas2b_obd();

      $iprgf->atr("nome")->valor = "Catálogo";
    }

    if (!$this->insert_base($cbd, $id_idioma, $iprgf)) return false;

    $this->atr("id_prgf_indice")->valor = $iprgf->atr("id_paragrafo")->valor;

    if (!$cbd->executa($this->sql_update($cbd))) return false;


    return true;
  }

  public function duplicar(FS_cbd $cbd, $id_idioma, ?string $refsite = null) {
    //* Lemos o posición do prgf_indice.
    $iprgf_0 = $this->prgf_indice($cbd);

    //* duplica páxina
    $id_dupli = $this->atr("id_paxina")->valor;

    $this->atr("id_paxina"     )->valor = null;
    $this->atr("id_comentarios")->valor = null;

        if ($this->atr("id_idioma")->valor == $id_idioma) $this->atr("id_sincro")->valor = null;
    elseif ($this->atr("id_sincro")->valor == null      ) $this->atr("id_sincro")->valor = $id_dupli;
    //~ if ($this->atr("id_sincro")->valor == null) $this->atr("id_sincro")->valor = $id_dupli;

    if (!$this->insert_base($cbd, $id_idioma)) return false;

    $id_paxina = $this->atr("id_paxina")->valor;

    //* duplica detalle
    $d = new Detalle_obd($id_paxina);

    if (!$d->duplicar($cbd, $id_dupli, $refsite)) return false;

    //* actualizamos prgf_indice. Buscamos o novo prgf_indice, pola posición do prgf_indice orixinal.
    $_prgf = $d->a_prgf($cbd);

    if (!is_array($_prgf)) return false;

    foreach($_prgf as $pos=>$prgf) {
      if ($pos != $iprgf_0->atr("posicion")->valor) continue;

      $this->atr("id_prgf_indice")->valor = $prgf->atr("id_paragrafo")->valor;

      if (!$this->update($cbd)) return false;

      break;
    }

    //* Publicamos os productos que están (publicados) na páxina duplicada.
    $sql = "insert into articulo_publicacions (id_site, id_articulo, id_paxina, momento)
            select id_site, id_articulo, {$id_paxina}, momento
              from articulo_publicacions
             where id_paxina = {$id_dupli}";


    return $cbd->executa($sql);
  }

  public function prgf_indice(FS_cbd $cbd = null) {
    if ($cbd == null) $cbd = new FS_cbd();

    return Prgf_iventas2b_obd::inicia($cbd, $this->atr("id_prgf_indice")->valor);
    //~ return Prgf_iventas2_obd::inicia($cbd, $this->atr("id_prgf_indice")->valor);
  }

  public function action_2($limpa = false, $completa = false) { //* implementa IMenuSite_paxina->action_2()
    $a = $this->atr("action")->valor;
    $c = ""; //$this->id_categoria;
    $m = $this->id_marca;
    $i = $this->atr("id_idioma")->valor;

    if (!$limpa   ) return "{$a}?ctlg={$i}&f1={$c}&f2={$m}";

    if (!$completa) return "{$a}?ctlg={$i}&f1={$c}&f2={$m}";


    return $this->action_site() . "?ctlg={$i}&f1={$c}&f2={$m}";
  }

  public function delete(FS_cbd $cbd) {
    //* borramos publicacions.
    $s = $this->atr("id_site"  )->valor;
    $p = $this->atr("id_paxina")->valor;
    
    $sql = "delete from articulo_publicacions where id_site = {$s} and id_paxina = {$p}";

    if (!$cbd->executa($sql)) return false;
    
    //* borramos paxcat.
    $sql = "delete from paxcat where id_paxina = {$p}";

    if (!$cbd->executa($sql)) return false;

    //* borramos o resto da paxina.    
    return parent::delete($cbd);
  }


  public function icatF__site():int {  //* IMPLEMENTA ICatalogo_fonte.icatF__site()
    return $this->atr("id_site")->valor;
  }

  public function icatF__catRaiz():int {  //* IMPLEMENTA ICatalogo_fonte.icatF__catRaiz()
    if ($this->id_categoria == null) return 1;
    
    return $this->id_categoria;
  }
  
  public function icatF__titulo():string {  //* IMPLEMENTA ICatalogo_fonte.icatF__titulo()
    return "";
  }
  
  public function icatF__selectfrom(bool $editor, bool $vpa):string {  //* IMPLEMENTA ICatalogo_fonte.icatF__selectfrom()
    $id_cat = $this->icatF__catRaiz();
        
    $wh_s   = "a0.id_site  = " . $this->icatF__site();
    $wh_p   = "b.id_paxina = " . $this->atr("id_paxina")->valor;
    $wh_c   = ($id_cat > 1)?"":" and (a.id_categoria = {$id_cat} or c.id_pai = {$id_cat} or c.id_avo = {$id_cat})";
    $wh_u   = ($vpa       )?"":" and (a0.unidades >= a0.pedido_min)";


    return "
select distinct a.id_articulo, a.nome, a.notas, a.id_categoria, a.id_marca, b.momento
  from (
select id_site, min(id_articulo) as id_articulo, nome, notas, id_categoria, id_marca from articulo a0 where (not id_grupo_cc is null) and ({$wh_s}){$wh_u} group by id_grupo_cc
union
select id_site, id_articulo, nome, notas, id_categoria, id_marca from articulo a0 where (id_grupo_cc is null) and ({$wh_s}){$wh_u}
       ) a inner join articulo_publicacions b on (a.id_site = b.id_site and a.id_articulo  = b.id_articulo and {$wh_p})
           left  join v_categoria           c on (a.id_site = c.id_site and a.id_categoria = c.id_categoria{$wh_c})";
  }
  
  public function icatF__orderby(int $orderby = -1):string {  //* IMPLEMENTA ICatalogo_fonte.icatF__orderby()
    return $this->icatF__orderby;
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
    $id_cat = $this->icatF__catRaiz();
        
    $wh_s   = "a.id_site  = " . $this->icatF__site();
    $wh_p   = "b.id_paxina = " . $this->atr("id_paxina")->valor;
    //~ $wh_c   = " and (a.id_categoria = {$id_cat} or c.id_pai = {$id_cat} or c.id_avo = {$id_cat})";
    $wh_c   = " and (a.id_categoria > 1) and (a.id_categoria = {$id_cat} or c.id_pai = {$id_cat})";
    $wh_u   = ($vpa)?"":" and (a.unidades >= a.pedido_min)";


    return "
select distinct c.id_categoria as id_etq, c.nome as etq
  from articulo a inner join articulo_publicacions b on (a.id_site = b.id_site and a.id_articulo  = b.id_articulo and {$wh_p})
                  inner join v_categoria           c on (a.id_site = c.id_site and a.id_categoria = c.id_categoria)
 where ({$wh_s}){$wh_u}{$wh_c}
 order by c.nome";
  }
  
  private function icatF__sql_marcas(bool $vpa):?string { 
    $wh_s = "a.id_site   = " . $this->atr("id_site"  )->valor;
    $wh_p = "c.id_paxina = " . $this->atr("id_paxina")->valor;
    
    $wh_u = ($vpa)?"":" and (unidades >= pedido_min)";
    
    return "select distinct a.id_marca as id_etq, b.nome as etq
              from articulo a inner join articulo_marca        b on (a.id_site = b.id_site and a.id_marca    = b.id_marca   )
                              inner join articulo_publicacions c on (a.id_site = c.id_site and a.id_articulo = c.id_articulo)
             where {$wh_s} and {$wh_p}{$wh_u}
            order by b.nome";
  }
}

//**********************************

class Paxina_v2cat_obd extends Paxina_ventas2_obd {
  private PaxCat_obd $paxcat;

  public function __construct(int $id_paxina) {
    parent::__construct($id_paxina);
    
    $this->atr("tipo")->valor = "ventas2cat";
  }

  public static function inicia_cat(FS_cbd $cbd, int $id_paxina, int $id_categoria) {
    $p = new Paxina_v2cat_obd($id_paxina);

    $p->select($cbd, $p->atr("id_paxina")->sql_where($cbd));
    
    $p->atr("tipo")->valor = "ventas2cat";
   
    
    $p->paxcat = PaxCat_obd::inicia($cbd, $p, $id_categoria);
    
    $p->post_paxcat($cbd);
    
    return $p;
  }

  public function categoria():int {
    return $this->paxcat->atr("id_categoria")->valor;
  }

  public function __indice() {
    return false;
  }

  public function update(FS_cbd $cbd, $dactu = true) {
    $this->paxcat->atr("h1"    )->valor = $this->atr("cat_h1"    )->valor;
    $this->paxcat->atr("titulo")->valor = $this->atr("titulo"    )->valor;
    $this->paxcat->atr("descri")->valor = $this->atr("descricion")->valor;
    
    return $this->paxcat->update($cbd);
  }

  public function insert(FS_cbd $cbd, $id_idioma, Prgf_iventas2b_obd $iprgf = null) {
    $this->paxcat->atr("h1"    )->valor = $this->atr("cat_h1"    )->valor;
    $this->paxcat->atr("titulo")->valor = $this->atr("titulo"    )->valor;
    $this->paxcat->atr("descri")->valor = $this->atr("descricion")->valor;
    
    return $this->paxcat->insert($cbd);
  }

  public function duplicar(FS_cbd $cbd, $id_idioma, ?string $refsite = null) {
    return true;
  }

  public function prgf_indice(FS_cbd $cbd = null) {
    return null;
  }

  public function delete(FS_cbd $cbd) {
    return $this->paxcat->delete($cbd);
  }

  public function action_2($limpa = false, $completa = false) { //* implementa IMenuSite_paxina->action_2()
    $a = $this->atr("action")->valor;
    $c = $this->paxcat->atr("id_categoria")->valor; 
    $i = $this->atr("id_idioma")->valor;

    if (!$limpa   ) return "{$a}?ctlg={$i}&f1={$c}";

    if (!$completa) return "{$a}?ctlg={$i}&f1={$c}";


    return $this->action_site() . "?ctlg={$i}&f1={$c}";
  }


  public function a_migas($url_limpa = false, FS_cbd $cbd = null):array {
    if ($cbd == null) $cbd = new FS_cbd();
    
    $_a = parent::a_migas($url_limpa, $cbd);
    
    $cat = $this->paxcat->categoria_obd($cbd);
    
    $_a[] = [null,
             $cat->atr("nome")->valor,
             null
            ];

//~ echo "<pre>" . print_r($_a, 1) . "</pre>";

    return $_a;
  }



  private function post_paxcat(FS_cbd $cbd):void {
    if ( !$this->paxcat->baleiro() ) {
      $this->atr("cat_h1"    )->valor = $this->paxcat->atr("h1"    )->valor;
      $this->atr("titulo"    )->valor = $this->paxcat->atr("titulo")->valor;
      $this->atr("descricion")->valor = $this->paxcat->atr("descri")->valor;
      
      return;
    }
    
    $c = $this->paxcat->categoria_obd($cbd); 
    
    $this->atr("cat_h1"    )->valor .= " - " . $c->atr("nome")->valor;
    $this->atr("titulo"    )->valor .= " - " . $c->atr("nome")->valor;
    $this->atr("descricion")->valor .= " - " . $c->atr("nome")->valor;
  } 
}   

//**********************************

final class PaxCat_obd extends Obxeto_bd {
  const noimx = "imx/noimx.png";
  
  private int  $id_site;
  private bool $baleiro = true;
    
  public function __construct(Paxina_ventas2_obd $p, int $id_categoria) {
    parent::__construct();

    $this->id_site                    = $p->atr("id_site"  )->valor;
    
    $this->atr("id_paxina"   )->valor = $p->atr("id_paxina")->valor;
    $this->atr("id_categoria")->valor = $id_categoria;
  }

  public function mapa_bd() {
    return new PaxCat_mbd();
  }

  public function baleiro():bool {    
    return $this->baleiro;
  }

  public static function inicia(FS_cbd $cbd, Paxina_ventas2_obd $p, int $id_categoria):PaxCat_obd {
    $o = new PaxCat_obd($p, $id_categoria);

    $wh_p = $o->atr("id_paxina"   )->sql_where($cbd);
    $wh_c = $o->atr("id_categoria")->sql_where($cbd);

    $o->select( $cbd, "{$wh_p} and {$wh_c}" );

    return $o;
  }

  public function categoria_obd(FS_cbd $cbd = null):Categoria_obd {
    if ($cbd == null) $cbd = new FS_cbd();
    
    return Categoria_obd::inicia($cbd, $this->id_site, $this->atr("id_categoria")->valor); 
  }

  public function select(FS_cbd $cbd, $where = null, $orderby = null) {
    $this->baleiro = true;
    
    $r = $cbd->consulta( $this->sql_select($cbd, $where, $orderby) );

    if (!$a = $r->next()) return;
    

    $this->baleiro = false;
    
    $this->post($a);
  }

  public function update(FS_cbd $cbd) {
    if ($this->baleiro()) return $this->insert($cbd);
    
    return $cbd->executa($this->sql_update($cbd));
  }

  public function insert(FS_cbd $cbd) {
    return $cbd->executa($this->sql_insert($cbd));
  }

  public function delete(FS_cbd $cbd, String $src_0) {
    return $cbd->executa( $this->sql_delete($cbd) );
  }
}

//-----------------------------------------------

final class PaxCat_mbd extends Mapa_bd {
  public function __construct() {
    parent::__construct();

    $t = new Taboa_dbd("paxcat");

    $t->pon_campo("id_paxina"   , new Numero()           , true);
    $t->pon_campo("id_categoria", new Numero()           , true);
    $t->pon_campo("h1"                                         );
    $t->pon_campo("titulo"                                     );
    $t->pon_campo("descri"                                     );

    $this->pon_taboa($t);
  }
}

//**********************************

final class PaxVenta_ms implements IMenuSite_paxina {
//* falso Opcion_ms_obd. Na BD, son subcategorías da categoría principal asociada á páxina.

  private $pax          = null;
  private $id_marca     = null;
  private $id_categoria = null;

  public function __construct($_c) {
    $this->id_marca     = (isset($_c['id_marca'    ]))?$_c['id_marca'    ]:null;
    $this->id_categoria = (isset($_c['id_categoria']))?$_c['id_categoria']:null;

    $this->pax = new Paxina_ventas2_obd(-1);

//~ echo "<pre>" . print_r($_c, 1) . "</pre>";

    $this->pax->post($_c);
  }

  public function atr($nome = null) {
  //* implementa IMenuSite_paxina->atr($nome = null)

    return $this->pax->atr($nome);
  }

  public function a_grupo() {
  //* implementa IMenuSite_paxina->a_grupo()

    return $this->pax->a_grupo();
  }
/*
  public function action_2($limpa = false, $completa = false) {
  //* implementa IMenuSite_paxina->action_2($limpa = false, $completa = false)

    $a = $this->atr("action"   )->valor;
    $c = $this->id_categoria;
    $m = $this->id_marca;
    $i = $this->atr("id_idioma")->valor;

    if ($m == null) return "{$a}?f={$i}&f1={$c}";

    return "{$a}?m={$m}";
  }
*/
  public function action_2($limpa = false, $completa = false) {
  //* implementa IMenuSite_paxina->action_2($limpa = false, $completa = false)

    $a = $this->atr("action")->valor;
    $c = $this->id_categoria;
    $m = $this->id_marca;
    $i = $this->atr("id_idioma")->valor;

    if ($m == null) return "{$a}?ctlg={$i}&f1={$c}";

    return "{$a}?m={$m}";
  }

  public function action_custom($completa = false):?string {
    return null;
  }
}
