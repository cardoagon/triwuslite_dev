<?php

abstract class Multilinguaxe_obd extends Obxeto_bd  {

  public function __construct() {
    parent::__construct();
  }

  public function mapa_bd() {
    return new Multilinguaxe_mbd();
  }

  public function select(FS_cbd $cbd, $where = null, $orderby = null) {
    $r = $cbd->consulta($this->sql_select($cbd, $where, $orderby));

    if (!$a = $r->next()) return null;

    $this->post($a);
  }

  public function update(FS_cbd $cbd) {
    return $cbd->executa($this->sql_update($cbd));
  }

  public function insert(FS_cbd $cbd) {
    if (!$cbd->executa($this->sql_insert($cbd))) return false;

    $this->calcula_id($cbd);

    return true;
  }

  final protected function calcula_id(FS_cbd $cbd) {
    $r = $cbd->consulta("select LAST_INSERT_ID() as id");

    if (!$a = $r->next()) die("Multilinguaxe_obd.calcula_id(), (!a = r.next())");

    $this->atr("id_ml")->valor = $a['id'];
  }

  public function delete(FS_cbd $cbd) {
    return $cbd->executa($this->sql_delete($cbd));
  }
}

//-------------------------------------------

abstract class Multilinguaxe_mbd extends Mapa_bd {
  public function __construct() {
    $t = new Taboa_dbd("multilinguaxe");

    $t->pon_campo("id_ml", new Numero(), true);
    $t->pon_campo("id_idioma", new Varchar());

    $this->pon_taboa($t);


    $t2 = new Taboa_dbd("idioma");

    $t2->pon_campo("nome");

    $this->relacion_fk($t2, array("id_idioma"), array("id_idioma"));
  }
}


//*************************************************


final class MLsite_obd extends Multilinguaxe_obd  {

  public function __construct() {
    parent::__construct();
  }

  public function mapa_bd() {
    return new MLsite_mbd();
  }

  public static function inicia(FS_cbd $cbd, $id_site, $id_idioma = null) {
    $s = new MLsite_obd();

    $s->atr("id_site"  )->valor = $id_site;
    $s->atr("id_idioma")->valor = $id_idioma;

    $where = $s->atr("id_site")->sql_where($cbd);

    if ($id_idioma != null) $where .= " and " . $s->atr("id_idioma")->sql_where($cbd);

    $s->select($cbd, $where, "pos asc");

    return $s;
  }

  public static function inicia_ml(FS_cbd $cbd, $id_ml) {
    $s = new MLsite_obd();

    $s->atr("id_ml")->valor = $id_ml;

    $s->select($cbd, $s->atr("id_ml")->sql_where($cbd));

    return $s;
  }

  public function insert(FS_cbd $cbd) {
    if (!parent::insert($cbd)) return false;

    $this->atr("pos")->valor = self::__count($this->atr("id_site")->valor, $cbd) + 1;

    return $cbd->executa($this->sql_insert($cbd, "ml_site"));
  }

  public function update(FS_cbd $cbd) {
    if (!parent::update($cbd)) return false;

    return $cbd->executa($this->sql_update($cbd, null, "ml_site"));
  }

  public function delete(FS_cbd $cbd) {
    if (!parent::delete($cbd)) return false;

    return $cbd->executa($this->sql_delete($cbd, null, "ml_site"));
  }

  public static function calcula_idioma_activo(int $id_site, string $id_idioma, FS_cbd $cbd = null) {
    if ($cbd == null) $cbd = new FS_cbd();

    $sql = "select activo from v_ml_site where id_site={$id_site} and id_idioma = '{$id_idioma}'";


    $r = $cbd->consulta($sql);

    if (!$_r = $r->next()) return false;
    
    return $_r["activo"] == 1;
  }

/*
  public static function activado($id_site, FS_cbd $cbd = null) {
    if ($cbd == null) $cbd = new FS_cbd();

    $ml = new MLsite_obd();

    $r = $cbd->consulta($ml->sql_select($cbd, "id_site = {$id_site}"));

    return $r->next();
  }
*/
  public static function activado($id_site, FS_cbd $cbd = null) {
    return true; //* todos os sites teñen a lo menos un idioma (compatibilidade hacia atrás).
  }
  
  public static function idioma_predeterminado($id_site, FS_cbd $cbd = null) {
    if ($cbd == null) $cbd = new FS_cbd();

    $ml = self::inicia($cbd, $id_site);

    return $ml->atr("id_idioma")->valor;
  }

  public static function __count($id_site, FS_cbd $cbd = null) {
    if ($cbd == null) $cbd = new FS_cbd();

    $r = $cbd->consulta("select count(*) as t from v_ml_site where id_site = {$id_site}");

    $a = $r->next();

    return $a['t'];
  }

  public static function __a_idiomas($id_site, $tipo = 0, $orderby = null, FS_cbd $cbd = null) {
    if ($orderby == null) $orderby = "nome";

    if     ($tipo == 2) //* outros idiomas
      $sql = "select id_idioma, nome
                from idioma
               where not id_idioma in (select id_idioma from v_ml_site where id_site = {$id_site})
              order by {$orderby}";
    elseif ($tipo == 1) //* propios
      $sql = "select a.id_idioma, a.nome, b.pos, b.activo
                from idioma a inner join v_ml_site b on (a.id_idioma = b.id_idioma and b.id_site = {$id_site})
              order by {$orderby}";

    elseif ($tipo == 3) //* propios && activos
      $sql = "select a.id_idioma, a.nome
                from idioma a inner join v_ml_site b on (a.id_idioma = b.id_idioma and b.id_site = {$id_site} and b.activo = 1)
              order by pos";

    else                //* todos
      $sql = "select id_idioma, nome from idioma order by {$orderby}";


    if ($cbd == null) $cbd = new FS_cbd();

    $r = $cbd->consulta($sql);

    $a2 = null;
    while ($a = $r->next()) $a2[$a['id_idioma']] = $a['nome'];


    return $a2;
  }

  public static function _pos($id_site, FS_cbd $cbd = null) {
    if ($cbd == null) $cbd = new FS_cbd();

    $r = $cbd->consulta("select id_idioma, pos from v_ml_site where id_site={$id_site} order by pos");

    $a2 = null;
    while ($a = $r->next()) $a2[ $a['id_idioma'] ] = $a['pos'];


    return $a2;
  }
}

//-------------------------------------------

final class MLsite_mbd extends Multilinguaxe_mbd {
  public function __construct() {
    parent::__construct();

    $t = new Taboa_dbd("ml_site");

    $t->pon_campo("id_ml"  , new Numero(), true);
    $t->pon_campo("id_site", new Numero()      );
    $t->pon_campo("pos"    , new Numero()      );
    $t->pon_campo("activo" , new Numero()      );

    $this->relacion_fk($t, array("id_ml"), array("id_ml"));
  }
}


//*************************************************


final class MLpaxina_obd extends Multilinguaxe_obd  {
  public function __construct($id_idioma = null, $id_paxina = null, $id_sincro = null) {
    parent::__construct();

    if ($id_sincro == null) $id_sincro = $id_paxina;

    $this->atr("id_idioma")->valor = $id_idioma;
    $this->atr("id_paxina")->valor = $id_paxina;
    $this->atr("id_sincro")->valor = $id_sincro;
  }

  public function mapa_bd() {
    return new MLpaxina_mbd();
  }

  public static function inicia(FS_cbd $cbd, $id_ml) {
    $ml = new MLpaxina_obd();

    $ml->select($cbd, "multilinguaxe.id_ml = {$id_ml}", null);

    return $ml;
  }

  public static function inicia_paxina(FS_cbd $cbd, $id_paxina) {
    $ml = new MLpaxina_obd(null, $id_paxina);

    $ml->select($cbd, $ml->atr("id_paxina")->sql_where($cbd), null);

    return $ml;
  }

  public function update(FS_cbd $cbd) {
    if (!parent::update($cbd)) return false;

    return $cbd->executa($this->sql_update($cbd, null, "ml_paxina"));
  }

  public function insert(FS_cbd $cbd) {
    if (!parent::insert($cbd)) return false;

    return $cbd->executa($this->sql_insert($cbd, "ml_paxina"));
  }

  public function delete(FS_cbd $cbd) {
    if (!parent::delete($cbd)) return false;

    return $cbd->executa($this->sql_delete($cbd, null, "ml_paxina"));
  }
}

//-------------------------------------------

final class MLpaxina_mbd extends Multilinguaxe_mbd {
  public function __construct() {
    parent::__construct();

    $t = new Taboa_dbd("ml_paxina");

    $t->pon_campo("id_ml", new Numero(), true);
    $t->pon_campo("id_paxina", new Numero());
    $t->pon_campo("id_sincro", new Numero());

    $this->relacion_fk($t, array("id_ml"), array("id_ml"));
  }
}

