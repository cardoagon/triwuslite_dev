<?php


class Elmt_carrusel_obd extends Elmt_obd {
  const tipo_bd = "CARRUSEL";
  const imx_r   = 0.66;
  
  protected $a_elmt_obd    = null;

  public function __construct($cols = 3, $freq = "0", $imx_r = null) {
    parent::__construct();

    $this->atr("columnas"      )->valor = $cols;
    $this->atr("frecuencia"    )->valor = $freq;
    $this->atr("ciclo"         )->valor = "0";
    $this->atr("imx_proporcion")->valor = ($imx_r == null)?self::imx_r:$imx_r;
  }

  public function mapa_bd() {
    return new Elmt_carrusel_mbd();
  }

  public function a_elmt_obd($a_elmt_obd = -1, FS_cbd $cbd = null) {
    if ($a_elmt_obd == -1) {
      $this->a_elmt_obd = array();
      
      if ($cbd == null) $cbd = new FS_cbd();
      
      if (($p = $this->atr("id_elmt")->valor) != null) {
        $sql = "select id_elmt from elmt_carrusel_iten where id_pai = {$p}";
        
        $r = $cbd->consulta($sql);

        while ($_a = $r->next()) array_unshift($this->a_elmt_obd, Elmt_obd::inicia($cbd, $_a['id_elmt']));
      }
    }
    elseif ($a_elmt_obd == -2);
    else
      $this->a_elmt_obd = $a_elmt_obd;


    return $this->a_elmt_obd;
  }

  public function post_iten(Elmt_carrusel_iten_obd $i) {
    return $this->a_elmt_obd[] = $i;
  }

  public function insert(FS_cbd $cbd) {
   // return false;

    if (!parent::insert($cbd)) return false;


    if (count($this->a_elmt_obd) == 0) return true;


    foreach ($this->a_elmt_obd as $elmt_i) {
      $elmt_i->atr("id_pai")->valor = $this->atr("id_elmt")->valor;

      if (!$elmt_i->insert($cbd)) return false;
    }


    return true;
  }

  public function duplicar(FS_cbd $cbd, ?string $refsite = null) {
    return false; //* non se inserta. Falta implementar o método duplicar.
  }

  public function update(FS_cbd $cbd) {
    if (count($this->a_elmt_obd) > 0)
      foreach ($this->a_elmt_obd as $elmt_i)
        if (!$elmt_i->update($cbd)) return false;

    if (!$this->modificado) return true;
    
    if (!parent::update($cbd)) return false;


    return true;
  }

  public function delete(FS_cbd $cbd, $delete_base = true) {
    
    $sql = "select id_elmt from elmt_carrusel_iten where id_pai = " . $this->atr("id_elmt")->valor;
    
    $r = $cbd->consulta($sql);

    while ($_a = $r->next()) {
      $elmt_i = Elmt_obd::inicia($cbd, $_a['id_elmt']);

      if ($elmt_i != null) if (!$elmt_i->delete($cbd)) return false;
    }

    return parent::delete($cbd);
  }

  protected function tipo_bd() {
    return self::tipo_bd;
  }
}

//--------------------------------------------------------

final class Elmt_carrusel_mbd extends Elmt_mbd {
  public function __construct() {
    parent::__construct();

    $t = new Taboa_dbd("elmt_carrusel");

    $t->pon_campo("id_elmt"       , new Numero(), true);
    $t->pon_campo("frecuencia"    , new Numero());
    $t->pon_campo("ciclo"         , new Numero());
    $t->pon_campo("imx_proporcion", new Numero());
    $t->pon_campo("columnas"      , new Numero());
    $t->pon_campo("puntos"        , new Numero());

    $this->relacion_fk($t, array("id_elmt"), array("id_elmt"));
  }
}

//****************************************

class Elmt_carrusel_iten_obd extends Imaxe_obd {
  const tipo_bd = "CARRUSEL_ITEN";

  public function __construct($id_paxina = null, $url = null, $id_aux = 0) {
    parent::__construct($url);

    $this->atr("id_paxina")->valor = $id_paxina;
    $this->atr("id_aux"   )->valor = $id_aux;
    $this->atr("baixa"    )->valor = "0";
  }

  public function mapa_bd() {
    return new Elmt_carrusel_iten_mbd();
  }

  public function post_paxina(Paxina_obd $p, FS_cbd $cbd = null) {
    if ($cbd == null) $cbd = new FS_cbd();
 
    $this->atr("id_site"  )->valor = $p->atr("id_site"  )->valor;
    $this->atr("id_paxina")->valor = $p->atr("id_paxina")->valor;

    //* control para páxinas de venta online
/*    
    if ($p->atr("tipo"          )->valor != "ventas") return;
    if ($p->atr("id_prgf_indice")->valor > 1        ) return;
    

    $r = $cbd->consulta("select id_articulo from elmt_ventas where id_paxina = " . $p->atr("id_paxina")->valor);

    if (!$a = $r->next()) return;

    $this->atr("id_articulo")->valor = $a['id_articulo'];
*/
  }

  public function post_idpaxina($id_paxina, FS_cbd $cbd = null) {
    if ($cbd == null) $cbd = new FS_cbd();

    if (($p = Paxina_obd::inicia($cbd, $id_paxina)) == null) return;

    $this->post_paxina($p, $cbd);
  }

  public function href($url_limpa = false, FS_cbd $cbd = null) {
    if ($cbd == null) $cbd = new FS_cbd();
 
    if (($p = $this->atr("id_paxina"  )->valor) > 0) return Paxina_obd::inicia($cbd, $p)->action_2($url_limpa, true);

    if (($url = $a->action($url_limpa)) == null) return $url;
      

    return $this->atr("href")->valor;
  }
  
  public function imaxe_obd() {
    $i = new Imaxe_obd();

    $i->atr("id_elmt")->valor  = null;

    $i->atr("id_style")->valor = null;
    $i->atr("css"     )->valor = $this->atr("css")->valor;

    $i->atr("notas"   )->valor = $this->atr("alt")->valor;

    $i->atr("ref_site")->valor = $this->atr("ref_site")->valor;
    $i->atr("url"     )->valor = $this->atr("url")->valor;


    return $i;
  }

  public function data(FS_cbd $cbd = null) {
    if (($p = $this->paxina_obd($cbd)) == null) return "";

    $a = $p->atr("creado")->a_data();

    return "{$a['d']}-{$a['m']}-{$a['Y']} {$a['H']}:{$a['i']}:{$a['s']}";
  }

  public function info_arti(FS_cbd $cbd = null) {
    if (($id_a = $this->atr("id_articulo")->valor) == null) return null;
    
    if ($cbd == null) $cbd = new FS_cbd();
    
    $a = Articulo_obd::inicia($cbd, $this->atr("id_site")->valor, $id_a);

    $p = Promo_obd::inicia_articulo($cbd, $this->atr("id_site")->valor, $id_a);


    return array("pvp"        => $a->pvp(1, true, false, null),
                 "pvpd"       => $a->pvp(1, true, true, $p),
                 "pedido_min" => $a->atr("pedido_min")->valor
                );
  }

  public function update(FS_cbd $cbd) {
    if ($this->atr("id_elmt")->valor < 0) {
      $this->atr("id_elmt")->valor *= -1;
      
      return parent::delete($cbd);
    }
    
    if ($this->atr("id_elmt")->valor == null) return $this->insert($cbd);


    return parent::update($cbd);
  }

  public function insert(FS_cbd $cbd) {
    $this->atr("id_aux")->valor = $this->calcula_id_aux($cbd, $this->atr("id_pai")->valor);
    
    return parent::insert($cbd);
  }

  protected function tipo_bd() {
    return self::tipo_bd;
  }

  private function calcula_id_aux(FS_cbd $cbd, $id_pai) {
    //* PRECOND. ($id_pai == null) => $id_aux = 1

    if ($id_pai == null) return 1;
    
    
    $sql = "select max(id_aux) + 1 as id from elmt_carrusel_iten where id_pai = {$id_pai}";

    $r = $cbd->consulta($sql);
    

    if (!$_a = $r->next()) return 1;
    
    if ($_a["id"] == null) return 1;
    
    
    return $_a["id"];
  }
}

//--------------------------------------------------------

class Elmt_carrusel_iten_mbd extends Elmt_mbd {
  public function __construct() {
    parent::__construct();

    $t = new Taboa_dbd("elmt_carrusel_iten");

    $t->pon_campo("id_elmt"    , new Numero(), true);
    $t->pon_campo("id_paxina"  , new Numero(), true);
    $t->pon_campo("id_aux"     , new Numero(), true);
    $t->pon_campo("id_site"    , new Numero());
    $t->pon_campo("ref_site"   , new VarChar());
    $t->pon_campo("href"       , new VarChar());
    $t->pon_campo("url"        , new VarChar());
    $t->pon_campo("alt"        , new VarChar());
    $t->pon_campo("target"     , new VarChar());
    $t->pon_campo("id_articulo", new Numero());
    $t->pon_campo("id_pai"     , new Numero());
    $t->pon_campo("baixa"      , new Numero());

    $this->relacion_fk($t, array("id_elmt"), array("id_elmt"), "inner");
  }
}



//******************************************



final class ElmtCarrusel_novas2_obd extends Elmt_carrusel_obd {
  public function __construct(Paxina_novas2_obd $p, array $u_permisos, ?float $inovas_imx_proporcion, FS_cbd $cbd = null) {
    parent::__construct(4, "0", $inovas_imx_proporcion);

    $this->atr("puntos")->valor = 1;
 
    if ($cbd == null) $cbd = new FS_cbd();
   
    $this->inicia_novas2($cbd, $p, $u_permisos);
  }

  public function a_elmt_obd($a_elmt_obd = -1, FS_cbd $cbd = null) {  //* SOBRESCRIBE: Elmt_carrusel_obd.a_elmt_obd()
    return parent::a_elmt_obd(-2, $cbd);
  }

  public function insert(FS_cbd $cbd) {  //* SOBRESCRIBE: Elmt_carrusel_obd.insert()
    return true;
  }

  public function duplicar(FS_cbd $cbd, ?string $refsite = null) {  //* SOBRESCRIBE: Elmt_carrusel_obd.duplicar()
    return true;
  }

  public function update(FS_cbd $cbd) {  //* SOBRESCRIBE: Elmt_carrusel_obd.update()
    return true;
  }

  public function delete(FS_cbd $cbd, $delete_base = true) {  //* SOBRESCRIBE: Elmt_carrusel_obd.delete()
    return true;
  }


  private function inicia_novas2(FS_cbd $cbd, Paxina_novas2_obd $p, array $u_permisos):void {
    $id_paxina = $p->atr("id_paxina")->valor;
    $id_opcion = $p->atr("id_opcion")->valor;
    $id_idioma = $p->atr("id_idioma")->valor;
    $pos       = $p->atr("pos"      )->valor;

    
    $sql = "
select a.id_paxina, b.grupo_aux
  from paxina a inner join paxina_novas b on (a.id_paxina = b.id_paxina)
                inner join v_ml_paxina  c on (a.id_paxina = c.id_paxina)
  where (a.id_paxina <> {$id_paxina}) 
    and (a.id_opcion = {$id_opcion} and c.id_idioma = '{$id_idioma}' and a.id_prgf_indice = 0)
    and ((b.pos <= ({$pos} + 1)) or (b.pos <= ({$pos} + 4)))
    and ((a.publicar is null) or (now() > a.publicar)) 
  order by b.pos desc
  limit 0, 7";
  
      
    $r = $cbd->consulta($sql);
    
    while ($_r = $r->next()) {
//~ echo $_r["grupo_aux"]. "<br>";
      if ( !Erro::__parse_permisos( Opcion_ms_obd::explode_grupo($_r["grupo_aux"]), $u_permisos) ) continue;

      $this->inicia_novas2_iten($cbd, $_r["id_paxina"]);
    }
  }

  private function inicia_novas2_iten(FS_cbd $cbd, int $id_paxina):void {
    $pax  = Paxina_obd::inicia($cbd, $id_paxina, "novas2");

    if ($pax  == null) return;


    $logo = $pax->iclogo_obd($cbd);

    if ($logo == null) return;

    
    $iten = new Elmt_carrusel_iten_obd();
    
    $iten->atr("id_paxina")->valor = $id_paxina;
    $iten->atr("target"   )->valor = "_self";
    
    $iten->atr("nome"     )->valor = $pax ->atr("titulo"    )->valor;
    $iten->atr("notas"    )->valor = $pax ->atr("descricion")->valor;
                          
    $iten->atr("href"     )->valor = $pax ->action_2(true,true);
                          
    $iten->atr("ref_site" )->valor = $logo->atr("ref_site")->valor;
    $iten->atr("url"      )->valor = $logo->atr("url"     )->valor;
    
    
    $this->post_iten($iten);
  }
}
