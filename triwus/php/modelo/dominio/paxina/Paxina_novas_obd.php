<?php


final class Paxina_novas_obd extends    Paxina_obd
                             implements IPax_indice {

  public function __construct($id_paxina = null) {
    parent::__construct("novas", $id_paxina);

  }

  public function mapa_bd() {
    return new Paxina_novas_mbd();
  }

  public function __indice() {
    return $this->atr("id_prgf_indice")->valor > 0;
  }

  public function select_pai(FS_cbd $cbd = null) {
    if ($cbd == null) $cbd = new FS_cbd();

    $id_prgf_indice = $this->atr("id_prgf_indice")->valor;

    if ($id_prgf_indice > 0) return parent::select_pai($cbd);

    $id_opcion = $this->atr("id_opcion")->valor;

    $sql_inner_2 = "";
    if (($id_idioma = $this->atr("id_idioma")->valor) != null) $sql_inner_2 = "inner join v_ml_paxina c on (a.id_paxina = c.id_paxina and c.id_idioma = '{$id_idioma}')";

    $sql = "select a.id_paxina as id_paxina
              from paxina a inner join paxina_novas b on (a.id_paxina = b.id_paxina)
                            {$sql_inner_2}
                      where id_opcion = {$id_opcion} and a.id_prgf_indice > 0";

    $r = $cbd->consulta($sql);

    if (!$a = $r->next()) return null;


    return Paxina_obd::inicia($cbd, $a['id_paxina'], "novas");
  }

  public function select_ant(FS_cbd $cbd = null) {
    if ($cbd == null) $cbd = new FS_cbd();

    $id_prgf_indice = $this->atr("id_prgf_indice")->valor;

    if ($id_prgf_indice > 0) return parent::select_ant($cbd);

    $sql = "select id_paragrafo, posicion
              from paragrafo_elmt a inner join elmt_novas b on (a.id_elmt = b.id_elmt)
             where id_paxina = " . $this->atr("id_paxina")->valor;

    $r = $cbd->consulta($sql);

    if (!$a = $r->next()) return null;

//* as paxinas de novas se msostran en orde inversa.
    $sql = "select id_paxina
              from paragrafo_elmt a inner join elmt_novas b on (a.id_elmt = b.id_elmt)
             where id_paragrafo = {$a['id_paragrafo']} and posicion > {$a['posicion']}
          order by posicion";

    $r = $cbd->consulta($sql);

    if (!$a = $r->next()) return null;



    return self::inicia($cbd, $a['id_paxina']);
  }

  public function select_seg(FS_cbd $cbd = null) {
    if ($cbd == null) $cbd = new FS_cbd();

    $id_prgf_indice = $this->atr("id_prgf_indice")->valor;

    if ($id_prgf_indice > 0) return parent::select_seg($cbd);

    $sql = "select id_paragrafo, posicion
              from paragrafo_elmt a inner join elmt_novas b on (a.id_elmt = b.id_elmt)
             where id_paxina = " . $this->atr("id_paxina")->valor;

    $r = $cbd->consulta($sql);

    if (!$a = $r->next()) return null;

//* as paxinas de novas se mostran en orde inversa.
    $sql = "select id_paxina
              from paragrafo_elmt a inner join elmt_novas b on (a.id_elmt = b.id_elmt)
             where id_paragrafo = {$a['id_paragrafo']} and posicion < {$a['posicion']}
          order by posicion desc";

    $r = $cbd->consulta($sql);

    if (!$a = $r->next()) return null;



    return self::inicia($cbd, $a['id_paxina']);
  }

  public function iclogo_obd(FS_cbd $cbd, ?string $k = null) {
    if (($l = $this->atr("logo")->valor) != null) return Elmt_obd::inicia($cbd, $l);

    if ($this->__indice()) return Elmt_obd::inicia($cbd, $l);

    return $this->elmt_novas($cbd);
  }

  public function update(FS_cbd $cbd, $dactu = truee) {
    if ( !parent::update($cbd, true) ) return false;

    return $this->insert_detalles($cbd);
  }

  public function insert(FS_cbd $cbd, $id_idioma) {
    $prgf = new Prgf_inovas_obd();

    $prgf->atr("nome")->valor = "Noticiero";

    if (!$this->insert_base($cbd, $id_idioma, $prgf)) return false;

    $this->atr("id_prgf_indice")->valor = $prgf->atr("id_paragrafo")->valor;

    if (!$cbd->executa($this->sql_update($cbd))) return false;

    if (!$cbd->executa($this->sql_insert($cbd, "paxina_novas"))) return false;


    return true;
  }

  public function duplicar(FS_cbd $cbd, $id_idioma, ?string $refsite = null) {
    $id_dupli = $this->atr("id_paxina")->valor;


        if ($this->atr("id_idioma")->valor == $id_idioma) $this->atr("id_sincro")->valor = null;
    elseif ($this->atr("id_sincro")->valor == null      ) $this->atr("id_sincro")->valor = $id_dupli;
     

    //* duplica páxina detalle
    if ($this->atr("id_prgf_indice")->valor == 0) {
      //~ echo "<b>1.- localizar a paxina_novas_indice(id_idioma) P1.</b><br>";

      $pax_aux = $this->inicia_idioma($cbd, $id_idioma, "paxina.id_prgf_indice > 0");

      if ($pax_aux                          == null) return false;
      if ($pax_aux->atr("id_paxina")->valor == null) return false;

      $prgf_indice_aux = $pax_aux->prgf_indice($cbd);

      //~ echo "<b>2.- localizar a elmt_novas asociado a esta paxina de detalle E1.</b><br>";

      $elmt_aux = $this->elmt_novas($cbd);


      //~ echo "<b>3.- duplicar_paxina_detalle</b><br>";

      if (!$this->duplicar_paxina_detalle($cbd, $id_idioma, $refsite)) return false;


      //~ echo "<b>4.- insertar E1 en P1->prgf_indice</b><br>";

      $elmt_aux->atr("id_elmt"  )->valor = null;
      $elmt_aux->atr("nome"     )->valor = $this->atr("nome")->valor;
      $elmt_aux->atr("notas"    )->valor = $this->atr("descricion")->valor;
      $elmt_aux->atr("id_paxina")->valor = $this->atr("id_paxina")->valor;
      $elmt_aux->atr("id_style" )->valor = null;
      $elmt_aux->atr("link_href")->valor = null;


      return $prgf_indice_aux->update_elmt($cbd, $elmt_aux);
    }

    //* duplica páxina índice
    $this->atr("id_paxina"     )->valor = null;
    $this->atr("id_idioma"     )->valor = $id_idioma;
    //~ $this->atr("id_comentarios")->valor = null;

    if (!$this->insert_base($cbd, $id_idioma)) return false;

    $prgf = $this->prgf_indice($cbd);

    if (!$prgf->duplicar_indice($cbd, $this, $refsite)) return false;


    $this->atr("id_prgf_indice")->valor = $prgf->atr("id_paragrafo")->valor;

    if (!$cbd->executa($this->sql_update($cbd))) return false;


    return $cbd->executa($this->sql_insert($cbd, "paxina_novas"));
  }

  public function insert_paxina_detalle(FS_cbd $cbd, Elmt_novas_obd $ea) {
//~ echo "<pre>" . print_r($ea->a_resumo(), true) . "</pre>";
    $p = new Paxina_novas_obd();

    $p->atr("id_opcion"     )->valor = $this->atr("id_opcion")->valor;
    $p->atr("id_idioma"     )->valor = $this->atr("id_idioma")->valor;
    $p->atr("action"        )->valor = $this->atr("action")->valor;
    $p->atr("id_prgf_indice")->valor = "0";
    $p->atr("nome"          )->valor = $ea->atr("nome")->valor;
    $p->atr("descricion"    )->valor = $ea->atr("notas")->valor;


    if (!$p->insert_base($cbd, $this->atr("id_idioma")->valor)) return null;

    if (!$cbd->executa($p->sql_insert($cbd, "paxina_novas"))) return null;

    $prgf1 = new Paragrafo_obd();
    $prgf2 = new Paragrafo_obd();
    $prgf3 = new Paragrafo_obd();
    //~ $prgf4 = new Paragrafo_obd();

    if (!$p->insert_detalle($cbd, $prgf1, 1)) return null;
    if (!$p->insert_detalle($cbd, $prgf2, 2)) return null;
    if (!$p->insert_detalle($cbd, $prgf3, 3)) return null;
    //~ if (!$p->insert_detalle($cbd, $prgf4, 4)) return false;

    if (!self::insert_migaspan($cbd, $prgf1     )) return null;
    if (!self::insert_enovas  ($cbd, $prgf2, $ea)) return null;
    if (!self::insert_rs      ($cbd, $prgf3     )) return null;
    //~ if (!self::insert_carrusel($cbd, $prgf4, $this->atr("id_prgf_indice")->valor)) return null;


    $sql = "update elmt_novas set id_paxina = " . $p->atr("id_paxina")->valor . " where id_elmt = " . $ea->atr("id_elmt")->valor;

    if (!$cbd->executa($sql)) return null;
    //~ $cbd->executa($sql); //* non comprobar resultado

    return $p;
  }

  public function duplicar_paxina_detalle(FS_cbd $cbd, $id_idioma, ?string $refsite = null) {
    $id_dupli = $this->atr("id_paxina")->valor;

    //* duplica p&aacute;xina
    $this->atr("id_paxina"     )->valor = null;
    $this->atr("id_prgf_indice")->valor = "0";
    $this->atr("id_comentarios")->valor = null;


    if (!$this->insert_base($cbd, $id_idioma)) return false;

    if (!$cbd->executa($this->sql_insert($cbd, "paxina_novas"))) return null;


    //* duplica detalle
    $d = new Detalle_obd($this->atr("id_paxina")->valor);


    return $d->duplicar($cbd, $id_dupli, $refsite);
  }

  public function delete(FS_cbd $cbd) {
    if (!parent::delete($cbd)) return false;

    return $cbd->executa($this->sql_delete($cbd, null, "paxina_novas"));
  }

  public function prgf_indice(FS_cbd $cbd = null) {
    if ($cbd == null) $cbd = new FS_cbd();

    return Prgf_inovas_obd::inicia($cbd, $this->atr("id_prgf_indice")->valor);
  }

  public function elmt_novas(FS_cbd $cbd = null) {
    if ($cbd == null) $cbd = new FS_cbd();

    $r = $cbd->consulta("select id_elmt from elmt_novas where id_paxina = " . $this->atr("id_paxina")->valor);

    if (!$a = $r->next()) return null;

    return Elmt_obd::inicia($cbd, $a["id_elmt"]);
  }

  public function insert_detalles(FS_cbd $cbd) {
    $sql = "select a.id_elmt
              from elmt_novas a inner join paragrafo_elmt b on (a.id_elmt = b.id_elmt)
             where (id_paxina is null) and b.id_paragrafo = " . $this->atr("id_prgf_indice")->valor;

    $r = $cbd->consulta($sql);

    while ($a = $r->next()) {
      $elmt = Elmt_obd::inicia($cbd, $a['id_elmt']);

      if ($this->insert_paxina_detalle($cbd, $elmt) == null) return false;
    }

    return true;
  }

  private static function insert_migaspan(FS_cbd $cbd, Paragrafo_obd $p) {
    $pe_obd = new Paragrafo_elmt_obd();

    $pe_obd->atr("posicion"    )->valor = 1;
    $pe_obd->atr("id_paragrafo")->valor = $p->atr("id_paragrafo")->valor;


    return $pe_obd->insert($cbd, Paxinador_obd::crea_migaspan());
  }

  private static function insert_rs(FS_cbd $cbd, Paragrafo_obd $p) {
    $pe_obd = new Paragrafo_elmt_obd();

    $pe_obd->atr("posicion"    )->valor = 1;
    $pe_obd->atr("id_paragrafo")->valor = $p->atr("id_paragrafo")->valor;


    return $pe_obd->insert($cbd, Difucom2_obd::crea_rs());
  }

  private static function insert_carrusel(FS_cbd $cbd, Paragrafo_obd $p, $id_prgf_indice) {
    $pe_obd = new Paragrafo_elmt_obd();

    $pe_obd->atr("posicion"    )->valor = 1;
    $pe_obd->atr("id_paragrafo")->valor = $p->atr("id_paragrafo")->valor;

    //* inicia carrusel_obd
    $c = new Elmt_carrusel_obd(4, 8);


    $sql = "select a.id_paxina, c.inovas_imx_proporcion
              from paragrafo_elmt b inner join elmt_novas a on (b.id_elmt      = a.id_elmt)
                                    inner join paragrafo  c on (b.id_paragrafo = c.id_paragrafo)
             where b.id_paragrafo = {$id_prgf_indice} and (not id_paxina is null)
          order by a.id_elmt desc
             limit 0, 5";


    $bd    = $cbd->consulta($sql);
    $imx_r = null;
    while ($_a = $bd->next()) {
//~ echo "<pre>" . print_r($_a, true) . "</pre>";
      $ci = new Elmt_carrusel_iten_obd();

      $ci->post_idpaxina($_a['id_paxina'], $cbd);


      $c->post_iten($ci);


      if ($imx_r == null) $imx_r = $_a['inovas_imx_proporcion'];
    }

    if ($ci == null) return true;


    if ($imx_r == null) $imx_r = Elmt_carrusel_obd::imx_r;


    $c->atr("imx_proporcion")->valor = $imx_r;


    return $pe_obd->insert($cbd, $c);
  }

  private static function insert_enovas(FS_cbd $cbd, Paragrafo_obd $p, Elmt_novas_obd $ea) {
    $pe_obd = new Paragrafo_elmt_obd();

    $pe_obd->atr("posicion"    )->valor = 1;
    $pe_obd->atr("id_paragrafo")->valor = $p->atr("id_paragrafo")->valor;

    $txt = "<h2>" . $ea->atr("nome")->valor . "</h2>";

    if ( !$pe_obd->insert($cbd, new Texto_obd($txt, 1)) ) return false;


    $pe_obd->atr("posicion"    )->valor = 2;
    $pe_obd->atr("id_paragrafo")->valor = $p->atr("id_paragrafo")->valor;

    if ( !$pe_obd->insert($cbd, $ea->imaxe_obd()) ) return false;


    $pe_obd->atr("posicion"    )->valor = 3;
    $pe_obd->atr("id_paragrafo")->valor = $p->atr("id_paragrafo")->valor;

    $txt = $ea->atr("notas")->valor;

    return $pe_obd->insert($cbd, new Texto_obd($txt, 1));
  }
}

//-------------------------------------

final class Paxina_novas_mbd extends Paxina_mbd {
  public function __construct() {
    parent::__construct();

    $t = new Taboa_dbd("paxina_novas");

    $t->pon_campo("id_paxina", new Numero(), true);

    //* compatibilidade con parche: paxina/novasAnovas2
    $t->pon_campo("etq_1");
    $t->pon_campo("etq_2");
    $t->pon_campo("etq_3");
    $t->pon_campo("data", new Data("YmdHis"));


    $this->relacion_fk($t, array("id_paxina"), array("id_paxina"));
  }
}

