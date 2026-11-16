<?php

final class Paxina_ventas_obd extends    Paxina_obd
                              implements IPax_indice {

  public function __construct($id_paxina = null) {
    parent::__construct("ventas", $id_paxina);
  }

  public function mapa_bd() {
    return new Paxina_ventas_mbd();
  }

  public function __indice() {
    return $this->atr("id_prgf_indice")->valor > 0;
  }

  public function elmt_ventas(FS_cbd $cbd = null) {
    if ($cbd == null) $cbd = new FS_cbd();

    $sql = "select a.id_elmt as id_elmt
              from paragrafo_elmt a inner join paxina_ventas b on (a.id_paragrafo = b.id_prgf_detalle)
                                    inner join elmt_ventas c on (a.id_elmt = c.id_elmt)
             where b.id_paxina = " . $this->atr("id_paxina")->valor;

    $r = $cbd->consulta($sql);

    if (!$a = $r->next()) return null;

    return Elmt_obd::inicia($cbd, $a["id_elmt"]);
  }

  public function select_pai(FS_cbd $cbd = null) {
    if ($cbd == null) $cbd = new FS_cbd();

    $id_prgf_indice = $this->atr("id_prgf_indice")->valor;

    if ($id_prgf_indice > 0) return parent::select_pai($cbd);

    $id_opcion = $this->atr("id_opcion")->valor;

    $sql_inner_2 = "";
    if (($id_idioma = $this->atr("id_idioma")->valor) != null) $sql_inner_2 = "inner join v_ml_paxina c on (a.id_paxina = c.id_paxina and c.id_idioma = '{$id_idioma}')";

    $sql = "select a.id_paxina as id_paxina
              from paxina a inner join paxina_ventas b on (a.id_paxina = b.id_paxina)
                            {$sql_inner_2}
                      where a.id_opcion = {$id_opcion} and a.id_prgf_indice > 0";

    $r = $cbd->consulta($sql);


    //~ $r = $cbd->consulta("select a.id_paxina as id_paxina from paxina a inner join paxina_ventas b on (a.id_paxina = b.id_paxina) where id_opcion = {$id_opcion} and id_prgf_indice > 0;");

    if (!$a = $r->next()) return null;


    return Paxina_obd::inicia($cbd, $a['id_paxina'], "ventas");
  }

  public function insert(FS_cbd $cbd, $id_idioma) {
    $prgf = new Prgf_iventas_obd();

    $prgf->atr("nome")->valor = "Cat&aacute;logo";

    if (!$this->insert_base($cbd, $id_idioma, $prgf)) return false;

    $this->atr("id_prgf_indice")->valor = $prgf->atr("id_paragrafo")->valor;

    if (!$cbd->executa($this->sql_update($cbd))) return false;

    if (!$cbd->executa($this->sql_insert($cbd, "paxina_ventas"))) return false;


    return true;
  }

  public function duplicar(FS_cbd $cbd, $id_idioma, ?string $refsite = null) {
    if ($this->atr("id_prgf_indice")->valor == 0) return true;

    $id_dupli = $this->atr("id_paxina")->valor;

    //* duplica páxina
    $this->atr("id_paxina"     )->valor = null;
    $this->atr("id_idioma"     )->valor = $id_idioma;
    $this->atr("id_comentarios")->valor = null;

    if ($this->atr("id_sincro")->valor == null) $this->atr("id_sincro")->valor = $id_dupli;


    if (!$this->insert_base($cbd, $id_idioma)) return false;

    $prgf = $this->prgf_indice($cbd);

    if (!$prgf->duplicar_indice($cbd, $this, $refsite)) return false;


    $this->atr("id_prgf_indice")->valor = $prgf->atr("id_paragrafo")->valor;

    if (!$cbd->executa($this->sql_update($cbd))) return false;


    return $cbd->executa($this->sql_insert($cbd, "paxina_ventas"));
  }

  public function insert_paxina_detalle(FS_cbd $cbd, Elmt_ventas_obd $ea) {
    $a_obd = Articulo_obd::inicia($cbd, $ea->atr("id_site")->valor, $ea->atr("id_articulo")->valor);

    $p = new Paxina_ventas_obd();

    $p->atr("id_opcion"     )->valor = $this->atr("id_opcion")->valor;
    $p->atr("id_idioma"     )->valor = $this->atr("id_idioma")->valor;
    $p->atr("action"        )->valor = $this->atr("action")->valor;
    $p->atr("nome"          )->valor = $ea->atr("nome")->valor;
    $p->atr("descricion"    )->valor = $ea->atr("notas")->valor;
    $p->atr("id_prgf_indice")->valor = "0";
    $p->atr("logo"          )->valor = $a_obd->iclogo_id();


    if (!$p->insert_base($cbd, $this->atr("id_idioma")->valor)) return null;

    if ( !$cbd->executa($p->sql_insert($cbd, "paxina_ventas")) ) return null;


    $ea->atr("id_paxina")->valor = $p->atr("id_paxina")->valor;

    if (!$cbd->executa($ea->sql_update($cbd, null, "elmt_ventas"))) return null;


    $prgf1 = new Paragrafo_obd();

    if (!$p->insert_detalle($cbd, $prgf1, 1)) return false;

    if (!self::insert_migaspan($cbd, $prgf1)) return null;



    $prgf2 = new Paragrafo_obd();

    if (!$p->insert_detalle($cbd, $prgf2, 2)) return false;

    if (($imx_obd = self::insert_elmt_ventas($cbd, $prgf2, $ea)) == null) return null;

    $p->atr("id_prgf_detalle")->valor = $prgf2->atr("id_paragrafo")->valor;

    if (!$cbd->executa($p->sql_update($cbd, null, "paxina_ventas"))) return null;



    $prgf3 = new Paragrafo_obd();

    if (!$p->insert_detalle($cbd, $prgf3, 3)) return false;

    if (!self::insert_rs($cbd, $prgf3)) return null;



    return $p;
  }

  public function clonar_paxina_detalle(FS_cbd $cbd, Elmt_ventas_obd $e_obd, Paxina_ventas_obd $pvi) {
    if (($p_aux = $e_obd->paxina_obd()) != null) {
      //* xa existe un clon de este artículo na páxina un índice.

      if ($p_aux->atr("pai")->valor == $pvi->atr("pai")->valor) return true;
    }

    $id_articulo = $e_obd->atr("id_articulo")->valor;
    $id_dupli    = $this->atr("id_paxina")->valor;


//~ echo "\n*********************** Clonando páxina ventas ***********************\n\n";
    //* duplica p&aacute;xina
    $this->atr("id_paxina"     )->valor = null;
    $this->atr("id_sincro"     )->valor = null;
    $this->atr("id_prgf_indice")->valor = "0";
    $this->atr("id_comentarios")->valor = null;

    if (!$this->insert_base($cbd, $id_idioma)) return false;

    if (!$cbd->executa($this->sql_insert($cbd, "paxina_ventas"))) return null;

    $id_paxina = $this->atr("id_paxina")->valor;

    //* duplica detalle
    $d = new Detalle_obd($id_paxina);


    if (!$d->duplicar($cbd, $id_dupli)) return false;

    //* buscamos o prgf_detalle o elmt_ventas asociado.
    $sql = "select a.id_paragrafo, a.id_elmt
              from v_paragrafo_elmt a inner join detalle b on (a.tipo = 'VENTAS' and b.id_paxina = {$id_paxina} and a.id_paragrafo = b.id_paragrafo)";

    $r = $cbd->consulta($sql);

    if (!$a = $r->next()) return false;

    //* actualizamos a paxina clonada para albergar a id_articulo

    $sql = "update elmt_ventas set id_articulo = {$id_articulo} where id_elmt = {$a['id_elmt']}";

    if (!$cbd->executa($sql)) return false;

    $sql = "update elmt set nome = '" . $e_obd->atr("nome")->valor . "', notas = '" . $e_obd->atr("notas")->valor . "' where id_elmt = {$a['id_elmt']}";

    if (!$cbd->executa($sql)) return false;

    $this->atr("id_prgf_detalle")->valor = $a['id_paragrafo']; //* so a efectos de manter o obxeto actualizado

    $sql = "update paxina_ventas set id_prgf_detalle = {$a['id_paragrafo']} where id_paxina = {$id_paxina}";

    if (!$cbd->executa($sql)) return false;


    $sql = "update paxina set id_opcion = " . $pvi->atr("id_opcion")->valor . " where id_paxina = {$id_paxina}";

    if (!$cbd->executa($sql)) return false;


    $e_obd->atr("id_paxina")->valor = $id_paxina;

    return $e_obd->update($cbd); //* tamén actualiza metatags na páxina detalle.
  }

  public function delete(FS_cbd $cbd) {
    if (!parent::delete($cbd)) return false;

    return $cbd->executa($this->sql_delete($cbd, null, "paxina_ventas"));
  }

  public function prgf_indice(FS_cbd $cbd = null) {
    if ($cbd == null) $cbd = new FS_cbd();

    return Prgf_iventas_obd::inicia($cbd, $this->atr("id_prgf_indice")->valor);
  }

  public function prgf_detalle(FS_cbd $cbd = null) {
    if ($cbd == null) $cbd = new FS_cbd();

    return Prgf_ventasdet_obd::inicia($cbd, $this->atr("id_prgf_detalle")->valor);
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

  private static function insert_elmt_ventas(FS_cbd $cbd, Paragrafo_obd $p, Elmt_ventas_obd $ea) {
    $pe_obd = new Paragrafo_elmt_obd();

    $pe_obd->atr("posicion"    )->valor = 1;
    $pe_obd->atr("id_paragrafo")->valor = $p->atr("id_paragrafo")->valor;

    $ea->atr("id_elmt")->valor = null;
    $ea->atr("id_paxina")->valor = null;

    if (!$pe_obd->insert($cbd, $ea)) return null;


    return $ea;
  }

  private static function a_pos(FS_cbd $cbd, $id_paxina) {
    $r = $cbd->consulta(self::sql_paxinador("b.id_paxina = {$id_paxina}"));

    return $r->next();
  }

  private static function sql_paxinador($where, $orderby = null) {
    if ($orderby == null) $orderby = "posicion";

    return "select b.id_site, a.id_paxina, c.posicion
              from elmt_ventas a inner join v_site_paxina  b on (a.id_paxina = b.id_paxina)
                                 inner join paragrafo_elmt c on (a.id_elmt = c.id_elmt)
             where {$where} order by {$orderby}";
  }
}

//-------------------------------------

final class Paxina_ventas_mbd extends Paxina_mbd {
  public function __construct() {
    parent::__construct();

    $t = new Taboa_dbd("paxina_ventas");

    $t->pon_campo("id_paxina"      , new Numero(), true);
    $t->pon_campo("id_prgf_detalle", new Numero());

    $this->relacion_fk($t, array("id_paxina"), array("id_paxina"));
  }
}


