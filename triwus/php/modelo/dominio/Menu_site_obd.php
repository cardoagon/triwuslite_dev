<?php


final class Menu_site_obd extends Obxeto_bd {

  public function __construct() {
    parent::__construct();
  }

  public function mapa_bd() {
    return new Menu_site_mbd();
  }

  public static function inicia(FS_cbd $cbd, $id_ms) {
    $m = new Menu_site_obd();

    $m->select($cbd, "id_ms = {$id_ms}");

    return $m;
  }

  public function select(FS_cbd $cbd, $where = null, $orderby = null) {
    $r = $cbd->consulta($this->sql_select($cbd, $where, $orderby));

    if (!$a = $r->next()) return null;

    $this->post($a);
  }

  public function insert(FS_cbd $cbd) {
    return $cbd->executa($this->sql_insert($cbd));
  }

  public function update(FS_cbd $cbd, $a_oms_obd = null) {
    if (!$cbd->executa($this->sql_update($cbd))) return false;

    if ($a_oms_obd == null) return true;

    foreach($a_oms_obd as $oms_obd) {
      $oms_obd->atr("id_ms")->valor = $this->atr("id_ms")->valor;

      if (!$oms_obd->update($cbd)) return false;
    }

    return true;
  }

  public function intercambia_opciones(FS_cbd $cbd, $pos0, $pai, $suma) {
    $id_ms = $this->atr("id_ms")->valor;

    if (($pos_swap = self::__posswap($cbd, $id_ms, $pos0, $pai, $suma)) == null) return false;

    if (!self::sql_cambiaPosicion($cbd, $id_ms, "0"      , $pos_swap, $pai)) return false;
    if (!self::sql_cambiaPosicion($cbd, $id_ms, $pos_swap, $pos0    , $pai)) return false;
    if (!self::sql_cambiaPosicion($cbd, $id_ms, $pos0    , "0"      , $pai)) return false;

    //* control indexar site.

    if ($pai != null) return true;

    $oms1 = Opcion_ms_obd::inicia_primeira($cbd, $id_ms);
    $pos1 = $oms1->atr("posicion")->valor;

    //~ echo "intercambia_opciones::$pos0, $pai, $suma<br/>";

    if (($pos_swap == $pos1) || ($pos0 == $pos1)) Opcion_ms_obd::indexar_site($id_ms, $oms1, $cbd);

    return true;
  }

  public function delete(FS_cbd $cbd) {
    return $cbd->executa($this->sql_delete($cbd));
  }

  public function delete_opcions(FS_cbd $cbd, $a_oms_obd) {
    if ($a_oms_obd == null) return true;

    foreach($a_oms_obd as $oms_obd) {
      $oms_obd->atr("id_ms")->valor = $this->atr("id_ms")->valor;

      if (!$oms_obd->delete($cbd)) return false;
    }

    return true;
  }

  public function a_oms(FS_cbd $cbd = null, $id_idioma = null, $filtrar_reservadas = true) {
    if ($cbd == null) $cbd = new FS_cbd();

    $id_ms = $this->atr("id_ms")->valor;

    $w_idioma = Opcion_ms_obd::where_idioma($id_idioma);
    $w_reserv = ($filtrar_reservadas)?" and tipo IN ('libre', 'novas', 'novas2', 'ventas', 'ventas2', 'marcas', 'cxcli')":"";

    $sql = "select id_site as id_ms, id_opcion as id_oms, tipo, grupo, action
              from v_site_opcion
             where id_site = {$id_ms}{$w_idioma} and (pai is null){$w_reserv}
          order by posicion";

    $r = $cbd->consulta($sql);

    $a_elmt = null;
    while ($a = $r->next()) {
      if ($a['tipo'] == "ventas2") {
        $a['id_idioma'   ] = $id_idioma;
        $a['id_categoria'] = null;
        $a['categoria'   ] = null;

        $oms = new Opcion_ms_venta2($a);
      }
      //~ elseif ($a['tipo'] == "marcas") {
        //~ $a['id_idioma'] = $id_idioma;

        //~ $oms = new Opcion_ms_marca($a);
      //~ }
      else {
        $oms = new Opcion_ms_obd();

        $oms->post($a);
      }

      $a_elmt[] = $oms;
    }

    return $a_elmt;
  }

  public function ct_oms() {
    $cbd = new FS_cbd();

    $id_ms = $this->atr("id_ms")->valor;

    $r = $cbd->consulta("select count(*) as ct from menu_site_opcion where id_ms = {$id_ms}");

    if (!$a = $r->next()) return 0;

    return $a['ct'];
  }

  private static function sql_cambiaPosicion(FS_cbd $cbd, $id_ms, $pos_u, $pos_w, $pai) {
    $w_pai = ($pai == null)?"pai is null":"pai = {$pai}";

    $sql = "update menu_site_opcion set posicion = {$pos_u} where id_ms = {$id_ms} and posicion = {$pos_w} and {$w_pai}";

    return $cbd->executa($sql);
  }

  private static function __posswap(FS_cbd $cbd, $id_ms, $pos0, $pai, $suma) {
    $w_pai = ($pai == null)?"pai is null":"pai = {$pai}";

    if ($suma > 0) {
      $op = ">=";
      $orderby = "posicion ASC";
      $pos0++;
    }
    else {
      $op = "<=";
      $orderby = "posicion DESC";
      $pos0--;
    }

    $sql = "select posicion
              from v_site_opcion
             where (id_site = {$id_ms}) and (tipo IN ('libre','novas','ventas','novas2','ventas2','marcas')) and (posicion {$op} {$pos0}) and ({$w_pai})
          order by {$orderby}
             limit 0, 1";

    $r = $cbd->consulta($sql);

    if (!$a = $r->next()) return null;

    return $a['posicion'];
  }
}

//**************************************

final class Opcion_ms_obd extends    Obxeto_bd
                          implements IMenuSite_opcion {

  public function __construct($id_oms = null) {
    parent::__construct();

    $this->atr("id_oms")->valor = $id_oms;
    $this->atr("grupo" )->valor = "pub";
  }

  public function mapa_bd() {
    return new Opcion_ms_mbd();
  }

  public static function inicia(FS_cbd $cbd, $id_oms) {
    if ($id_oms == null) return null;

    $oms = new Opcion_ms_obd();

    $oms->select($cbd, "id_oms = {$id_oms}");

    return $oms;
  }

  public function control_primeira(FS_cbd $cbd, $id_idioma = -1) {
    if ($this->atr("pai")->valor != null) return false;
    
    return $this->atr("id_oms")->valor == self::primeira($cbd, $this->atr("id_ms")->valor, $id_idioma);
  }

  public static function inicia_primeira(FS_cbd $cbd, $id_site, $id_idioma = -1) {
    return self::inicia($cbd, self::primeira($cbd, $id_site, $id_idioma));
  }

  public static function primeira(FS_cbd $cbd, $id_site, $id_idioma = -1) {
    $w_idioma = Opcion_ms_obd::where_idioma($id_idioma);

    $sql = "select id_site as id_ms, id_opcion as id_oms
              from v_site_opcion
             where id_site = {$id_site}{$w_idioma} and pai is null
          order by posicion
             limit 0, 1";

    $r = $cbd->consulta($sql);


    if (!$a = $r->next()) die("Opcion_ms_obd::inicia_primeira(), (!a = r.next())");


    return $a['id_oms'];
  }

  public function a_grupo() {
    return self::explode_grupo($this->atr("grupo")->valor);
  }

  public function select(FS_cbd $cbd, $where = null, $orderby = null) {
    $r = $cbd->consulta($this->sql_select($cbd, $where, $orderby));

    if (!$a = $r->next()) return;

    $this->post($a);
  }

  public function insert_base(FS_cbd $cbd) {
    if ($this->atr("posicion")->valor == null) $this->calcula_posicion($cbd);

    if (!$cbd->executa($this->sql_insert($cbd))) return false;

    $this->calcula_id($cbd);

    return true;
  }

  public function insert(FS_cbd $cbd, Paxina_obd $p = null) {
    $this->atr("action")->valor = self::calcula_action($p->atr("nome")->valor, $this->atr("id_ms")->valor);

    if (!$this->insert_base($cbd)) return false;

    if ($p == null) $p = Paxina_obd::inicia($cbd);


    $p->atr("id_opcion")->valor = $this->atr("id_oms")->valor;

    $id_idioma = $p->atr("id_idioma")->valor;

    if (!$p->insert($cbd, $id_idioma)) return false;

    $this->escribe_exemplar($cbd);

    if ($this->atr("pai"     )->valor != null) return true; // non precisa indexado
    if ($this->atr("posicion")->valor != 1   ) return true; // non precisa indexado

    self::indexar_site($this->atr("id_ms")->valor, $this, $cbd);

    return true;
  }

  public function escribe_exemplar(FS_cbd $cbd = null, string $url_site = null) {
    try {
      $action = $this->atr("action")->valor;

      $ptw = LectorPlantilla::plantilla(Refs::url("ptw/action/fs_action.php"));

      $ptw = str_replace("[nomepaxina]", $action                    , $ptw);
      $ptw = str_replace("[idsite]"    , $this->atr("id_ms" )->valor, $ptw);
      $ptw = str_replace("[idoms]"     , $this->atr("id_oms")->valor, $ptw);

      if ($url_site == null) $url_site = Efs::url_site($this->atr("id_ms")->valor, $cbd);

      if (file_put_contents("{$url_site}{$action}", $ptw) === false) throw new Exception("( file_put_contents('{$url_site}{$action}', ...) === false )");
    }
    catch (Exception $ex) {
      throw $ex;
    }
  }

  public function update(FS_cbd $cbd) {
    return $cbd->executa($this->sql_update($cbd));
  }

  public function subir_nivel(FS_cbd $cbd) {
    $pai_0 = $this->atr("pai")->valor;


    $this->atr("pai")->valor = $this->id_avo($cbd);

    $this->calcula_posicion($cbd);
    
    if (!$this->update($cbd)) return false;

    $ms = Menu_site_obd::inicia($cbd, $this->atr("id_ms")->valor);

    $i   = 0;
    $ant = $this->id_anterior($cbd);
    while (($i < 55) && ($ant != $pai_0)) {

//~ echo "i, id_anterior, pai_0: $i, $ant, {$pai_0}<br>";

      $ms->intercambia_opciones($cbd, $this->atr("posicion")->valor, $this->atr("pai")->valor, -1);

      $this->select($cbd, $this->atr("id_oms")->sql_where($cbd));

      $i++;
      $ant = $this->id_anterior($cbd);
    }

//~ echo "i, id_anterior, pai_0: $i, $ant, {$pai_0}<br>";

    return true;
  }

  public function baixar_nivel(FS_cbd $cbd) {
    $id_anterior = $this->id_anterior($cbd);

    $sql = "select distinct id_oms, tipo
              from v_site_paxina
             where id_oms in (" . $this->atr("id_oms")->valor . ", {$id_anterior})";

    $r = $cbd->consulta($sql);
    $a = null;
    while ($a_i = $r->next()) $a[$a_i['id_oms']] = $a_i['tipo'];

    if ($a == null) return false;

    if ((count($a) == 2) && ($a[$id_anterior] != 'libre')) return false;

    $this->calcula_posicion($cbd, $id_anterior);

    return $this->update($cbd);
  }

  public function delete(FS_cbd $cbd, $id_idioma = -1, $control_reindexar = true, $borra_script = -1) {
    $id_site   = $this->atr("id_ms")->valor;
    $id_opcion = $this->atr("id_oms")->valor;
    //~ $action    = Efs::url_site($id_site, $cbd) . $this->action(false, $cbd);
    $action    = Efs::url_site($id_site, $cbd) . $this->atr("action")->valor;

    //* control reindexar.
    $oms1      = null;
    $reindexar = false;
    if ($control_reindexar) {
      $oms1      = self::inicia_primeira($cbd, $id_site);

      $reindexar = ($id_opcion == $oms1->atr('id_oms')->valor);
    }


    //* borra suboms.
    if (($a_suboms = $this->a_suboms($cbd, $id_idioma)) != null) {
      foreach ($a_suboms as $suboms)
        if (!$suboms->delete($cbd, $id_idioma, false)) return false;
    }

    //* borra paxinas e opcion segundo idioma.
    $wh_idioma = Opcion_ms_obd::where_idioma($id_idioma);

    $r = $cbd->consulta("select id_paxina, tipo from v_site_paxina where id_oms = {$id_opcion}{$wh_idioma}");

    while ($a = $r->next()) {
      if ($a['tipo'] == "ref")
        $p = Paxina_ref_obd::inicia($cbd, $a['id_paxina']);
      else
        $p = Paxina_obd::inicia($cbd, $a['id_paxina'], $a['tipo']);

      if (!$p->delete($cbd)) return false;
    }

    //* borra oms
    $sql = "select count(*) as ct from v_site_paxina where id_oms = " . $this->atr("id_oms")->valor;

    $r = $cbd->consulta($sql);

    $a =  $r->next();

    if ($a['ct'] > 0) return true;


    if (!$cbd->executa($this->sql_delete($cbd))) return false;

    //* Reindexar app.
    if ($reindexar) self::indexar_site($id_site, null, $cbd);

    //* borra action
    if ($borra_script === FALSE) return true;

    //~ if ($borra_script === -1) if ($p != null) $borra_script = $p->atr("tipo")->valor != "ref";

    if ($borra_script) if (is_file($action)) unlink($action);

    return true;
  }

  public function a_suboms(FS_cbd $cbd = null, $id_idioma = null) {  //* implementa IMenuSite_opcion->a_suboms(FS_cbd $cbd = null, $id_idioma = -1)
    if ($cbd == null) $cbd = new FS_cbd();

    $id_oms = $this->atr("id_oms")->valor;

    $w_idioma = Opcion_ms_obd::where_idioma($id_idioma);

    $sql = "select id_site as id_ms, id_opcion as id_oms, posicion, tipo, pai, grupo
              from v_site_opcion
             where pai = {$id_oms}{$w_idioma}
          order by posicion";

    $r = $cbd->consulta($sql);

    $a_elmt = null;
    while ($a = $r->next()) {
      if ($a['tipo'] == "ventas2") {
        $a['id_idioma'   ] = $id_idioma;
        $a['id_categoria'] = null;
        $a['categoria'   ] = null;

        $oms = new Opcion_ms_venta2($a);
      }
      else {
        $oms = new Opcion_ms_obd();

        $oms->post($a);
      }

      $a_elmt[] = $oms;
    }

    return $a_elmt;
  }

  public function site_obd(FS_cbd $cbd = null) {
    if ($cbd == null) $cbd = new FS_cbd();

//~ echo "<pre>" . print_r($this->a_resumo(),1) . "</pre>";

    return Site_obd::inicia($cbd, $this->atr("id_ms")->valor);
  }

  public function paxina_obd(FS_cbd $cbd = null, $id_idioma = -1) { //* implementa IMenuSite_opcion->paxina_obd(FS_cbd $cbd = null, $id_idioma = -1)
    if ($cbd == null) $cbd = new FS_cbd();

    $w_idioma = Opcion_ms_obd::where_idioma($id_idioma);

    $sql = "select id_paxina, tipo
              from v_site_opcion
             where id_opcion = " . $this->atr("id_oms")->valor . $w_idioma . "
             order by id_paxina";

    $r = $cbd->consulta($sql);

    if (!$a = $r->next()) return null;

    if ($a['tipo'] == "ref") return Paxina_ref_obd::inicia($cbd, $a['id_paxina']);

    return Paxina_obd::inicia($cbd, $a['id_paxina'], $a['tipo']);
  }

  public function a_paxina_obd(FS_cbd $cbd = null, $id_idioma = -1) {
    if ($cbd == null) $cbd = new FS_cbd();

    $w_idioma = Opcion_ms_obd::where_idioma($id_idioma);

    $sql = "select id_paxina, tipo
              from v_site_paxina
             where id_oms = " . $this->atr("id_oms")->valor . $w_idioma;

    $r = $cbd->consulta($sql);

    $a_pax = null;
    while ($a = $r->next()) $a_pax[] = Paxina_obd::inicia($cbd, $a['id_paxina'], $a['tipo']);

    return $a_pax;
  }

  public function action($completo = false, FS_cbd $cbd = null) {
    if (!$completo) return $this->atr("action")->valor;

    if ($cbd == null) $cbd = new FS_cbd();

    //~ return $this->paxina_obd($cbd)->action(true);
    return $this->paxina_obd($cbd)->action_2(false, true);
  }

  public static function hai_action(int $id_site, string $urlcus, FS_cbd $cbd = null):bool {
    if ($cbd == null) $cbd = new FS_cbd();
    
    $action = substr($urlcus, 0, -1) . ".php"; //* para calcular posible action, eliminamos o derradeiro '/' e engadimos ".php".

    $sql = "select a.* 
              from v_site_paxina a 
             where a.id_site = {$id_site} 
               and (a.action = '{$action}' or a.url_custom = '{$urlcus}')";
    
    $r = $cbd->consulta($sql);
    

    if ($r->next() == null) return false;
    
    return true;
  }

  public static function explode_grupo($s_grupo) {
    //~ return explode(", ", $s_grupo);
    
    return FGS_usuario::explode_permisos($s_grupo);
  }

  public static function indexar_site($id_site, Opcion_ms_obd $oms = null, FS_cbd $cbd = null) {
    if ($cbd == null) $cbd = new FS_cbd();

    if ($oms == null) $oms = Opcion_ms_obd::inicia_primeira($cbd, $id_site);

    
    $xht = new Xhtaccess($id_site, $cbd);
    
    $xht->escribe($oms->action(false, $cbd), $cbd);
  }
  
  public static function where_idioma($id_idioma = -1) {
    if ($id_idioma == -1  ) return "";
    if ($id_idioma == null) return " and id_idioma is null";

    return " and id_idioma = '{$id_idioma}'";
  }

  private function calcula_id(FS_cbd $cbd) {
    $r = $cbd->consulta("select LAST_INSERT_ID() as id");

    if (!$a = $r->next()) die("Opcion_ms_obd.calcula_id(), (!a = r.next())");

    $this->atr("id_oms")->valor = $a['id'];
  }

  private function calcula_posicion(FS_cbd $cbd, $pai = null) {
    if ($pai != null) $this->atr("pai")->valor = $pai;

    $w_pai = ($this->atr("pai")->valor == null)?"pai is null":"pai = " . $this->atr("pai")->valor;

    $r = $cbd->consulta("select if (max(posicion), max(posicion) + 1, 1) as pos from menu_site_opcion where posicion < 100000 and id_ms = {$this->atr("id_ms")->valor} and {$w_pai}");

    $a = $r->next();

    if ($a['pos'] == null) $a['pos'] = 1;

    $this->atr("posicion")->valor = $a['pos'];
  }

  public static function calcula_action($action_0, $id_site, $control_duplicado = true) {
    //* valida caracteres de $action_0

    //~ $url_carpeta = Efs::url_site($id_site);
    $url_carpeta = "";

    $action      = null;

    $action_0 = str_replace(array("Á", "É", "Í", "Ó", "Ú", "á", "é", "í", "ó", "ú", " "),
                            array("A", "E", "I", "O", "U", "a", "e", "i", "o", "u", "-"),
                            $action_0);

    $val = "ABCDEFGHIJKLMNÑOPQRSTUVWXYZabcdefghijklmnñopqrstuvwxyz0123456789_-";

    for ($i = 0; $i < strlen($action_0); $i++)
      if (strpos($val, $action_0[$i]) !== FALSE) $action .= $action_0[$i];


    //* control de action reservado
    if (($action_aux = self::calcula_action_reservado($action, $url_carpeta)) != null) return $action_aux;


    //* comprobamos que non existe outro action neste site co memsmo nome.

    if (!$control_duplicado) return "{$action}.php";

    $action_aux  = $action;

    for($i = 1; file_exists("{$url_carpeta}{$action_aux}.php"); $i++) $action_aux = "{$action}{$i}";


    return "{$action_aux}.php";
  }

  public static function calcula_action_reservado($action_0, $url_carpeta) {
    if ( !isset(Erro::$a_nome_pax_reservado[$action_0]) ) return null;
    if ( Erro::$a_nome_pax_reservado[$action_0] == null ) return null;


    $action = "{$action_0}.php";

    if (file_exists("{$url_carpeta}{$action}")) unlink("{$url_carpeta}{$action}");


    return $action;
  }

  private function id_avo(FS_cbd $cbd) {
    $r = $cbd->consulta("select avo from v_mso_xerarquia where id_oms = " . $this->atr("id_oms")->valor);

    $a = $r->next();

    return $a['avo'];
  }

  private function id_anterior(FS_cbd $cbd) {
    $w_pai = ($this->atr("pai")->valor == null)?"pai is null":"pai = " . $this->atr("pai")->valor;

    $r = $cbd->consulta("select id_oms as anterior from v_mso_xerarquia where id_ms = " . $this->atr("id_ms")->valor . " and ({$w_pai}) and posicion < " . $this->atr("posicion")->valor . " order by posicion desc limit 0,1");

    $a = $r->next();

    return $a['anterior'];
  }
}

//*---------------------------

final class Opcion_ms_venta2 implements IMenuSite_opcion {
//* falso Opcion_ms_obd. Na BD, son subcategorías da categoría principal asociada á páxina.

  private $oms  = null;

  private $tipo         = null; //* $tipo IN (null, "cxcli", "cxcli-cat")
  private $id_categoria = null; //* ($tipo == "cxcli-cat") <=> ($id_categoria != null)
  private $categoria    = null;
  private $action       = null;

  public function __construct($_c) {
    $this->tipo         = $_c["tipo"];
    $this->id_categoria = $_c["id_categoria"];
    $this->categoria    = $_c["categoria"];
  //  $this->action       = $_c["action"];
    $this->action = isset($_c["action"]) ? $_c["action"] : null; 

    $this->oms = new Opcion_ms_obd();

    $this->oms->post($_c);
  }

  public function atr($nome = null) { //* implementa IMenuSite_opcion->atr($nome = null)
    return $this->oms->atr($nome);
  }

  public function paxina_obd(FS_cbd $cbd = null, $id_idioma = -1) { //* implementa IMenuSite_opcion->paxina_obd(FS_cbd $cbd = null, $id_idioma = -1)
    if ($this->tipo == "ventas2") return $this->oms->paxina_obd($cbd, $id_idioma);


//~ echo "<pre style='color: #00ba00;'>" . print_r($this->oms->a_resumo(), true)  . "</pre>";


    $_c = array("id_site"      => $this->oms->atr("id_ms")->valor,
                "id_opcion"    => $this->oms->atr("id_oms")->valor,
                "id_idioma"    => $id_idioma,
                "grupo"        => "pub",
                "action"       => $this->action,
                "nome"         => $this->categoria,
                "id_categoria" => $this->id_categoria
               );

//~ echo "<pre style='color: #ba0000;'>" . print_r($_c, true)  . "</pre>";

    //* Busca subcategorías asociadas
    return new PaxVenta_ms($_c);
  }

  public function a_suboms(FS_cbd $cbd = null, $id_idioma = null) { //* implementa IMenuSite_opcion->a_suboms(FS_cbd $cbd = null, $id_idioma = -1)
    if (($id_site  = $this->oms->atr("id_ms")->valor) != 124) return $this->a_suboms_prgf($cbd, $id_idioma);
    
    return $this->a_suboms_prgf($cbd, $id_idioma);
  }

  public function a_suboms_prgf(FS_cbd $cbd = null, $id_idioma = null) { //* implementa IMenuSite_opcion->a_suboms(FS_cbd $cbd = null, $id_idioma = -1)
    if ($this->tipo == "ventas2-cat") return null; //* neste caso temos unha subcategoría actuando como un oms => _suboms == null.

    if ($cbd == null) $cbd = new FS_cbd();

    $id_site  = $this->oms->atr("id_ms")->valor;
    $id_oms   = $this->atr("id_oms")->valor;
    $w_idioma = Opcion_ms_obd::where_idioma($id_idioma);

    //* 1.- Busca a categoría asociada ao id_oms segundo id_prgf_indice
    $sql = "select a.id_paxina, a.action, b.iventas_categoria as id_categoria
              from v_site_paxina a inner join paragrafo b on (b.iventas_buscador <> 9 and a.id_prgf_indice = b.id_paragrafo)
             where id_oms = {$id_oms}{$w_idioma}";

    $r = $cbd->consulta($sql);

    if (!$_r = $r->next()) return null;

    $id_pax   = $_r['id_paxina'   ];
    $action   = $_r['action'      ];
    $prgf_cat = $_r['id_categoria'];


    //* 2.- Busca subcategorías de $prgf_cat con artículos asociados, que se poden publicar segundo as regras 'ventas2'.
    if ($prgf_cat != 1)
      $sql = "select distinct a.id_categoria, b.nome as categoria
                from v_articulo_ventas2 a inner join categoria b on (a.id_site = b.id_site and a.id_categoria = b.id_categoria)
               where a.id_site = {$id_site} and a.id_paxina = {$id_pax}
                 and (a.id_categoria_pai = {$prgf_cat})
            order by b.nome";
    else
      $sql = "select distinct b.id_categoria, b.nome as categoria
                from v_articulo_ventas2 a inner join categoria b on (a.id_site = b.id_site and (a.id_categoria = b.id_categoria or a.id_categoria_pai = b.id_categoria) and b.id_pai = 1)
               where a.id_site = {$id_site} and a.id_paxina = {$id_pax}
            order by b.nome";

    $r = $cbd->consulta($sql);

    $a_elmt = null;
    while ($_r = $r->next()) {
      $_r['id_site'     ] = $id_site;
      $_r['id_oms'      ] = $id_oms;
      $_r['pai'         ] = $id_oms;
      $_r['tipo'        ] = "ventas2-cat";
      $_r['action'      ] = $action;
      //~ $_r['id_categoria'] = $_r['id_categoria'];
      $_r['grupo'       ] = "pub";

      $a_elmt[] = new Opcion_ms_venta2($_r);
    }
    
    //~ if (is_array($a_elmt)) if (count($a_elmt) == 1) return null;

    return $a_elmt;
  }

  public function delete(FS_cbd $cbd, $id_idioma = -1, $control_reindexar = true, $borra_script = -1) {
    return true;
  }
}


//***************************************


final class Menu_site_mbd extends Mapa_bd {
  public function __construct() {
    $t = new Taboa_dbd("menu_site");

    $t->pon_campo("id_ms", new Numero(), true);
    $t->pon_campo("id_site", new Numero());

    $this->pon_taboa($t);
  }
}


final class Opcion_ms_mbd extends Mapa_bd {
  public function __construct() {
    $t = new Taboa_dbd("menu_site_opcion");

    $t->pon_campo("id_oms", new Numero(), true);
    $t->pon_campo("id_ms" , new Numero());
    $t->pon_campo("action");
    $t->pon_campo("grupo");
    $t->pon_campo("posicion", new Numero());
    //~ $t->pon_campo("seguinte", new Numero());
    $t->pon_campo("pai"     , new Numero());

    $this->pon_taboa($t);
  }
}

