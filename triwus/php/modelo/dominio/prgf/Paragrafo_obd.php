<?php

class Paragrafo_obd extends    Obxeto_bd
                    implements ICLogo, ILista_xeobd, IStyle_obd {

  public $modificado = true;
  
  public ?string $tmp_fondo = null;

  public function __construct() {
    parent::__construct();

    $this->atr("columnas"      )->valor = 1;
    $this->atr("columnas_mobil")->valor = "2";
    $this->atr("orde"          )->valor = "1";
  }

  public function mapa_bd() {
    return new Paragrafo_mbd();
  }

  public static function inicia(FS_cbd $cbd, $id_paragrafo) {
    $p = new Paragrafo_obd();

    $p->atr("id_paragrafo")->valor = $id_paragrafo;

    if ($id_paragrafo == null) return $p;

    $p->select($cbd, $p->atr("id_paragrafo")->sql_where($cbd));

    return $p;
  }

  public function iclogo_obd(FS_cbd $cbd, ?string $k = null) {
    if (($l = $this->iclogo_id($k)) == null) return null;

    return Elmt_obd::inicia($cbd, $l, Imaxe_obd::tipo_bd);
  }

  public function iclogo_id(?string $k = null) {
    return $this->atr("id_fondo")->valor;
  }

  public function iclogo_src(FS_cbd $cbd, ?string $k = null) {
    if ($this->atr("id_paragrafo")->valor == null) return null;

    if (($f = $this->iclogo_id()) == null) return null;


    $f_obd = Elmt_obd::inicia($cbd, $f, Imaxe_obd::tipo_bd);

    return $f_obd->url();
  }

  public function iclogo_update(FS_cbd $cbd) {
    //* control borrado.
    if ($this->iclogo_id() < 0) {
      $this->atr("id_fondo")->valor *= -1;
      
      return $this->iclogo_delete($cbd);
    }

    //* actualiza rexistro imx_obd.
    if ($this->tmp_fondo == null) return true;

    if (($l = $this->iclogo_obd($cbd)) == null) {
      $l = new Imaxe_obd($this->tmp_fondo);

      if (!$l->insert($cbd)) return false;
    }
    else {
      $l->post_url($this->tmp_fondo);

      if (!$l->update($cbd)) return false;
    }

    //* actualiza o fondo no prgf.
    $this->atr("id_fondo")->valor = $l->atr("id_elmt")->valor; //* actualiza obd na RAM.

    return true;
  }

  public function iclogo_delete(FS_cbd $cbd) {
    if (($l = $this->iclogo_obd($cbd)) != null) {
      if (!$l->delete($cbd)) return false;
    }
    
    $this->atr("id_fondo")->valor = null;

    return true;
  }

  public function select(FS_cbd $cbd, $where = null, $orderby = null) {
    $r = $cbd->consulta($this->sql_select($cbd, $where, $orderby));

    if (!$a = $r->next()) return;

    $this->post($a);
  }

  public function update(FS_cbd $cbd, $a_elmt_obd = null) {
    
    if ($this->modificado) {
      //* garda crs
      //~ if (!$this->update_crs($cbd)) return false;

      //* garda estilo
      if (!$this->update_style($cbd)) return false;
      
      //* garda imx fondo.
      if (!$this->iclogo_update($cbd)) return false;

      //* garda paragrafo
      if (!$cbd->executa($this->sql_update($cbd))) return false;
    }

    //* garda paragrafo_elmt
    if ($a_elmt_obd == null) return true;

    foreach($a_elmt_obd as $posicion=>$elmt) {
      if ($elmt == null) continue;

      if (!$this->update_elmt($cbd, $elmt, $posicion)) return false;
    }


    return true;
  }

  public function update_elmt(FS_cbd $cbd, Elmt_obd $elmt, $posicion = null) {
    $pe = new Paragrafo_elmt_obd();

    $pe->atr("id_paragrafo")->valor = $this->atr("id_paragrafo")->valor;
    $pe->atr("posicion"    )->valor = $posicion;

    if ($elmt->atr("id_elmt")->valor < 0) {
      $pe->atr("id_elmt")->valor = -1 * $elmt->atr("id_elmt")->valor;

      return $pe->delete($cbd, $elmt);
    }

    if ($elmt->atr("id_elmt")->valor == null) return $pe->insert($cbd, $elmt);

    $pe->atr("id_elmt")->valor = $elmt->atr("id_elmt")->valor;

    $ok = $pe->update($cbd, $elmt, $this->modificado);


    return $ok;
  }

  public function insert(FS_cbd $cbd) {
    //~ if (!$this->update_crs($cbd)) return false;
    if (!$this->update_style($cbd)) return false;

    if (!$cbd->executa($this->sql_insert($cbd))) return false;

    $this->calcula_id($cbd);

    return $this->iclogo_update($cbd);
  }

  public function duplicar(FS_cbd $cbd, $id_dupli, ?string $refsite = null) {
    $prgf = Paragrafo_obd::inicia($cbd, $id_dupli);

    $a_pelmt = $prgf->a_elmt("id_elmt", $cbd);

    $this->atr("id_style")->valor = null;
    //~ $this->atr("id_crs"  )->valor = null;

    if (!$this->insert($cbd)) return false;

    if (!is_array($a_pelmt)) return true;

    if (count($a_pelmt) == 0) return true;

    foreach ($a_pelmt as $pelmt) {
      $pelmt->atr("id_paragrafo")->valor = $this->atr("id_paragrafo")->valor;

      if (!$pelmt->duplicar($cbd, $refsite)) return false;
    }

    return true;
  }

  public function duplicar_indice(FS_cbd $cbd, Paxina_obd $pax_dupli, ?string $refsite = null) {
    return true;
  }

  public function delete(FS_cbd $cbd) {
    if (($a_pe = $this->a_elmt()) != null) foreach($a_pe as $pe) if (!$pe->delete($cbd)) return false;

    //~ if (!$this->crs_obd()->delete($cbd)) return false;
    if (!$this->style_obd()->delete($cbd)) return false;

    return $cbd->executa($this->sql_delete($cbd));
  }
/*
  final public function crs_obd(CRS_obd $crs = null) {
    if ($crs == null) {
      $crs = new CRS_obd($this->atr("id_crs")->valor);

      $crs->atr("id_crs"     )->valor = $this->atr("id_crs"     )->valor;
      $crs->atr("rs_activado")->valor = $this->atr("rs_activado")->valor;
      $crs->atr("rs_facebook")->valor = $this->atr("rs_facebook")->valor;
      $crs->atr("rs_twitter" )->valor = $this->atr("rs_twitter" )->valor;
      //~ $crs->atr("rs_gplus"   )->valor = $this->atr("rs_gplus"   )->valor;
      $crs->atr("rs_linkedin")->valor = $this->atr("rs_linkedin")->valor;
      $crs->atr("rs_whatsapp")->valor = $this->atr("rs_whatsapp")->valor;

      return $crs;
    }

    $this->atr("id_crs"     )->valor = $crs->atr("id_crs"     )->valor;
    $this->atr("rs_activado")->valor = $crs->atr("rs_activado")->valor;
    $this->atr("rs_facebook")->valor = $crs->atr("rs_facebook")->valor;
    $this->atr("rs_twitter" )->valor = $crs->atr("rs_twitter" )->valor;
    //~ $this->atr("rs_gplus"   )->valor = $crs->atr("rs_gplus"   )->valor;
    $this->atr("rs_linkedin")->valor = $crs->atr("rs_linkedin")->valor;
    $this->atr("rs_whatsapp")->valor = $crs->atr("rs_whatsapp")->valor;
  }
*/
  public function style_obd(Style_obd $s = null) {
    if ($s == null) 
      return new Style_obd($this->atr("id_style")->valor, $this->atr("css")->valor, $this->atr("css_fondo")->valor);

    $this->atr("id_style" )->valor = $s->atr("id_style" )->valor;
    $this->atr("css"      )->valor = $s->atr("css"      )->valor;
    $this->atr("css_fondo")->valor = $s->atr("css_fondo")->valor;
  }

  public function a_elmt($k_asoc = "id_elmt", FS_cbd $cbd = null, $where = null, $limit = null, $orderby = "posicion desc") {
    if ($this->atr("id_paragrafo")->valor == null) return null;

    if ($cbd == null) $cbd = new FS_cbd();

    //~ $cbd->verbose = true;

    $wh = "id_paragrafo = {$this->atr("id_paragrafo")->valor}";
    if ($where != null) $wh .= " and {$where}";

    $sql = "select *
              from v_paragrafo_elmt
             where {$wh}
             order by {$orderby} {$limit}";

    $r = $cbd->consulta($sql);

    $a_elmt = null;
    while ($a = $r->next()) {
      $pe = new Paragrafo_elmt_obd();

      $pe->atr("id_elmt"     )->valor = $a['id_elmt'];
      $pe->atr("id_paragrafo")->valor = $a['id_paragrafo'];
      $pe->atr("posicion"    )->valor = $a['posicion'];

      if ($k_asoc == null)
        $a_elmt[] = $pe;
      else
        $a_elmt[$a[$k_asoc]] = $pe;
    }

    return $a_elmt;
  }

  protected function calcula_id(FS_cbd $cbd) {
    $r = $cbd->consulta("select LAST_INSERT_ID() as id");

    if (!$a = $r->next()) die("Paragrafo_obd.calcula_id(), (!a = r.next())");

    $this->atr("id_paragrafo")->valor = $a['id'];
  }

/*
  final public function update_crs(FS_cbd $cbd) {
    $crs = $this->crs_obd();

    if (!$crs->update($cbd)) return false;

    $this->atr("id_crs")->valor = $crs->atr("id_crs")->valor;

    return true;
  }
*/

  final public function update_style(FS_cbd $cbd) {
    $s = $this->style_obd();

    if (!$s->update($cbd)) return false;

    $this->atr("id_style")->valor = $s->atr("id_style")->valor;

    return true;
  }
}

//****************************************

final class Paragrafo_elmt_obd extends    Obxeto_bd
                               implements IXestor_eobd {

  public function __construct() {
    parent::__construct();
  }

  public function mapa_bd() {
    return new Paragrafo_elmt_mbd();
  }

  public static function inicia(FS_cbd $cbd, $id_elmt) {
    $p = new Paragrafo_elmt_obd();

    $p->select($cbd, "id_elmt = {$id_elmt}");

    return $p;
  }

  public function select(FS_cbd $cbd, $where = null, $orderby = null) {
    $r = $cbd->consulta($this->sql_select($cbd, $where, $orderby));

    if (!$a = $r->next()) return;

    $this->post($a);
  }

  public function update(FS_cbd $cbd, Elmt_obd $elmt, $modificado = true) {
    if (!$elmt->update($cbd)) return false;

    if (!$modificado) return true;

    return $cbd->executa($this->sql_update($cbd));
  }

  public function insert(FS_cbd $cbd, Elmt_obd $elmt = null) {
    //~ if ($elmt == null) return $cbd->executa($this->sql_insert($cbd));
    if ($elmt == null) return false;

    if (!$elmt->insert($cbd)) return false;

    $this->atr("id_elmt")->valor = $elmt->atr("id_elmt")->valor;

    if ($this->atr("posicion")->valor == null) $this->calcula_posicion($cbd);


    return $cbd->executa($this->sql_insert($cbd));
  }

  public function duplicar(FS_cbd $cbd, ?string $refsite = null) {
    $elmt = $this->elmt($cbd);

    if ($elmt == null) return $cbd->executa($this->sql_insert($cbd));

    if (!$elmt->duplicar($cbd, $refsite)) return true; //* máis flexible, simplemente non inserta as estructuras relativas ao elemento fallido.

    $this->atr("id_elmt")->valor = $elmt->atr("id_elmt")->valor;

    return $cbd->executa($this->sql_insert($cbd));
  }

  public function delete(FS_cbd $cbd, Elmt_obd $elmt = null) {
    if ($elmt == null) $elmt = $this->elmt($cbd);

    if ($elmt != null) if (!$elmt->delete($cbd)) return false;

    return $cbd->executa($this->sql_delete($cbd));
  }

  public function elmt(FS_cbd $cbd = null) {
    if ($cbd == null) $cbd = new FS_cbd();

    return Elmt_obd::inicia($cbd, $this->atr("id_elmt")->valor);
  }

  public function posicion() {
    return $this->atr("posicion")->valor;
  }

  protected function calcula_posicion(FS_cbd $cbd) {
    $where = $this->atr("id_paragrafo")->sql_where($cbd);

    $r = $cbd->consulta("select max(posicion) as posicion from paragrafo_elmt where {$where}");

    if (!$a = $r->next()) die("Paragrafo_elmt_obd.calcula_posicion(), (!a = r.next())");

    $this->atr("posicion")->valor = $a['posicion'] + 1;
  }
}

//****************************************

final class Paragrafo_mbd extends Mapa_bd {
  public function __construct() {
    $t = new Taboa_dbd("paragrafo");

    $t->pon_campo("id_paragrafo"         , new Numero(), true);
    $t->pon_campo("nome"                                     );
    $t->pon_campo("columnas"             , new Numero()      );
    $t->pon_campo("columnas_mobil"       , new Numero()      );
    $t->pon_campo("id_style"             , new Numero()      );
    $t->pon_campo("id_fondo"             , new Numero()      );
    $t->pon_campo("orde"                 , new Numero()      );
    $t->pon_campo("i_entradas"           , new Numero()      );
    $t->pon_campo("inovas_etqs"                              );
    $t->pon_campo("inovas_imx_proporcion", new Numero()      );
    $t->pon_campo("inovas_rel_b"         , new Numero()      );
    $t->pon_campo("inovas_rel_t"                             );
    $t->pon_campo("inovas_rel_s"         , new Numero()      );
    $t->pon_campo("iventas_buscador"     , new Numero()      );
    $t->pon_campo("imarcas_vista"        , new Numero()      );
    $t->pon_campo("iventas_categoria"                        );
    $t->pon_campo("ancla"                                    );

    $this->pon_taboa($t);

    $t2 = new Taboa_dbd("detalle");

    $t2->pon_campo("posicion", new Numero());

    $this->relacion_fk($t2, array("id_paragrafo"), array("id_paragrafo"), "left");


    $t3 = new Taboa_dbd("style");

    $t3->pon_campo("id_style", new Numero(), true);
    $t3->pon_campo("css");
    $t3->pon_campo("css_fondo");

    $this->relacion_fk($t3, array("id_style"), array("id_style"), "left");

/*
    $t4 = CRS_mbd::__taboa_csr();

    $this->relacion_fk($t4, array("id_crs"), array("id_crs"), "left");
*/
  }
}

//****************************************

final class Paragrafo_elmt_mbd extends Mapa_bd {
  public function __construct() {
    $t = new Taboa_dbd("paragrafo_elmt");

    $t->pon_campo("id_elmt", new Numero(), true);
    $t->pon_campo("id_paragrafo", new Numero());
    $t->pon_campo("posicion", new Numero());

    $this->pon_taboa($t);
  }
}

