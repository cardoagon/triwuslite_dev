<?php


abstract class Elmt_obd extends    Obxeto_bd
                        implements IStyle_obd {

  public $modificado = false; //* permite/impide a actualización do elmt_obd. CONTROL no editor de páxinas.

  protected $usa_crs = false;

  public function __construct() {
    parent::__construct();

    $this->atr("tipo")->valor = $this->tipo_bd();
  }

  abstract protected function tipo_bd();

  final public static function inicia(FS_cbd $cbd, $id_elmt, $tipo = null) {
    if ($id_elmt == null) return null;

    if ($tipo == null) {
      $r = $cbd->consulta("select tipo from elmt where id_elmt = {$id_elmt}");

      if (!$_a = $r->next()) return null;

      $tipo = $_a['tipo'];
    }

    if (($elmt = Elmt_obd::factory($tipo)) == null) return null;

    $elmt->select($cbd, "elmt.id_elmt = {$id_elmt}");


    return $elmt;
  }

  public function select(FS_cbd $cbd, $where = null, $orderby = null) {
    $r = $cbd->consulta($this->sql_select($cbd, $where, $orderby));

    if (!$a = $r->next()) return;

    $this->post($a);
  }

  public function update(FS_cbd $cbd) {
    if (!$this->modificado) return true;

    //* garda crs
    if (!$this->update_crs($cbd)) return false;

    //* garda estilo
    if (!$this->update_style($cbd)) return false;

    if (!$cbd->executa($this->sql_update($cbd))) return false;

    $mbd = $this->mapa_bd();
    foreach($mbd->taboas as $k=>$t) {
      if ($t->nome == $mbd->taboa_principal) continue;
      if ($t->nome == "style")               continue;
      if ($t->nome == "crs")                 continue;
      if ($t->nome == "elmt_hr")             continue;
      //~ if ($t->nome == "elmt_galeria")        continue;
      if ($t->nome == "elmt_difucom2")       continue;

      if (!$cbd->executa($this->sql_update($cbd, null, $t->nome))) return false;
    }

    return true;
  }

  final public function update_style(FS_cbd $cbd) {
    switch ($this->tipo_bd()) {
      case "IMXARTI": return true;
      case "CARRUSEL_ITEN": return true;
      case "CARR_ARTI_ITEN": return true;
    }

    $s = $this->style_obd();

    if (!$s->update($cbd)) return false;

    $this->atr("id_style")->valor = $s->atr("id_style")->valor;

    return true;
  }

  final public function update_crs(FS_cbd $cbd) {
    if (!$this->usa_crs) return true;

    $crs = $this->crs_obd();

    if (!$crs->update($cbd)) return false;

    $this->atr("id_crs")->valor = $crs->atr("id_crs")->valor;

    return true;
  }

  public function insert(FS_cbd $cbd) {
    if (!$this->update_crs($cbd)) return false;
    if (!$this->update_style($cbd)) return false;
    if (!$cbd->executa($this->sql_insert($cbd))) return false;

    $this->calcula_id($cbd);

    $mbd = $this->mapa_bd();
    foreach($mbd->taboas as $k=>$t) {
      if ($t->nome == $mbd->taboa_principal) continue;
      if ($t->nome == "style")               continue;
      if ($t->nome == "crs")                 continue;

      if ( !$cbd->executa($this->sql_insert($cbd, $t->nome)) ) return false;
    }

    return true;
  }

  public function duplicar(FS_cbd $cbd, ?string $refsite = null) {
    $this->atr("id_elmt" )->valor = null;
    $this->atr("id_style")->valor = null;
    $this->atr("id_crs"  )->valor = null;

    return $this->insert($cbd);
  }

  public function delete(FS_cbd $cbd, $delete_base = true) {
    if ($this->atr("id_elmt")->valor < 0) $this->atr("id_elmt")->valor = -1 * $this->atr("id_elmt")->valor;

    if ($delete_base) if (!$cbd->executa($this->sql_delete($cbd))) return false;

    if ($this->usa_crs) if (!$this->crs_obd()->delete($cbd)) return false;

    $mbd = $this->mapa_bd();
    foreach($mbd->taboas as $k=>$t) {
      if ($t->nome == $mbd->taboa_principal) continue;

      if (!$cbd->executa($this->sql_delete($cbd, null, $t->nome))) return false;
    }

    return true;
  }

  final public function crs_obd(CRS_obd $crs = null) {
    if ($crs == null) {
      $crs = new CRS_obd($this->atr("id_crs")->valor);

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
/*
  final public function paxina_obd(FS_cbd $cbd = null) {
    if ($this->atr("id_elmt")->valor == null) return null;
    
    if ($cbd == null) $cbd = new FS_cbd();

    $r = $cbd->consulta("select id_paxina from v_site_elmt where id_elmt = " . $this->atr("id_elmt")->valor);

    if (!$a = $r->next()) return null;

    return Paxina_obd::inicia($cbd, $a['id_paxina']);
  }
*/
  final public function style_obd(Style_obd $s = null) {
    if ($s == null) return new Style_obd($this->atr("id_style")->valor, $this->atr("css")->valor);

    $this->atr("id_style")->valor = $s->atr("id_style")->valor;
    $this->atr("css")->valor      = $s->atr("css")->valor;
  }

  final public static function factory($tipo) {
    switch (strtoupper($tipo)) {
      case Articulo_imaxe_obd::tipo_bd:          return new Articulo_imaxe_obd();
      //~ case Comentarios_obd::tipo_bd:             return new Comentarios_obd();
      //~ case Difucom_obd::tipo_bd:                 return new Difucom_obd();
      case Difucom2_obd::tipo_bd:                return new Difucom2_obd();
      //~ case Elmt_album_obd::tipo_bd:              return new Elmt_album_obd();
      case Elmt_carrusel_obd::tipo_bd:           return new Elmt_carrusel_obd();
      case Elmt_carrusel_iten_obd::tipo_bd:      return new Elmt_carrusel_iten_obd();
      //~ case Elmt_carrusel_arti_obd::tipo_bd:      return new Elmt_carrusel_obd();
      //~ case Elmt_carrusel_arti_iten_obd::tipo_bd: return new Elmt_carrusel_iten_obd();
      case Elmt_contacto_obd::tipo_bd:           return new Elmt_contacto_obd();
      case Elmt_vcard_obd::tipo_bd:              return new Elmt_vcard_obd();
      case Elmt_novas_obd::tipo_bd:              return new Elmt_novas_obd();
      //~ case Elmt_suscripcion_obd::tipo_bd:        return new Elmt_suscripcion_obd();
      case Elmt_ventas_obd::tipo_bd:             return new Elmt_ventas_obd();
      case Galeria_obd::tipo_bd:                 return new Galeria_obd();
      //~ case Enquisa_obd::tipo_bd:                 return new Enquisa_obd();
      case HR_obd::tipo_bd:                      return new HR_obd();
      case Ico_obd::tipo_bd:                     return new Ico_obd();
      case Iframe_obd::tipo_bd:                  return new Iframe_obd();
      case Indice_obd::tipo_bd:                  return new Indice_obd();
      case Imaxe_obd::tipo_bd:                   return new Imaxe_obd();
      case Logo_obd::tipo_bd:                    return new Logo_obd();
      case Texto_obd::tipo_bd:                   return new Texto_obd();
      case Paxinador_obd::tipo_bd:               return new Paxinador_obd();
    }

    return null;
  }

  protected function calcula_id(FS_cbd $cbd) {
    $r = $cbd->consulta("select LAST_INSERT_ID() as id");

    if (!$a = $r->next()) die("Elmt_obd.calcula_id(), (!a = r.next())");


    $this->atr("id_elmt")->valor = $a['id'];
  }
}

//--------------------------------------------------------

class Elmt_mbd extends Mapa_bd {
  public function __construct() {
    parent::__construct();

    $t = new Taboa_dbd("elmt");

    $t->pon_campo("id_elmt", new Numero(), true);
    $t->pon_campo("tipo");
    $t->pon_campo("nome");
    $t->pon_campo("etiquetas");
    $t->pon_campo("etiquetas_1");
    $t->pon_campo("etiquetas_2");
    $t->pon_campo("id_style", new Numero());
    $t->pon_campo("id_crs"  , new Numero());
    $t->pon_campo("notas");

    $this->pon_taboa($t);

    $t2 = new Taboa_dbd("style");

    $t2->pon_campo("id_style", new Numero(), true);
    $t2->pon_campo("css");

    $this->relacion_fk($t2, array("id_style"), array("id_style"), "left");

    $t3 = CRS_mbd::__taboa_csr();

    $this->relacion_fk($t3, array("id_crs"), array("id_crs"), "left");

  }
}

