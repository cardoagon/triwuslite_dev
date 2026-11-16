<?php

final class Style_obd extends Obxeto_bd {

  public function __construct($id_style, $css = null) {
    parent::__construct();

    $this->atr("id_style")->valor = $id_style;
    $this->atr("css")->valor      = $css;
  }

  public function mapa_bd() {
    return new Style_mbd();
  }

  public static function inicia(FS_cbd $cbd, $id_style) {
    $s = new Style_obd($id_css);

    if ($id_style == null) return $s;

    $s->select($cbd, $s->atr("id_style")->where_sql($cbd));

    return $s;
  }

  public function a_props($a_props = -1) {
    if ($a_props == -1) return self::__props($this->atr("css")->valor);

    $this->atr("css")->valor = self::__css($a_props);
  }

  public function select(FS_cbd $cbd, $where = null, $orderby = null) {
    $r = $cbd->consulta($this->sql_select($cbd, $where, $orderby));

    if (!$a = $r->next()) return;

    $this->post($a);
  }

  public function update(FS_cbd $cbd) {
    if ($this->atr("id_style")->valor != null) return $cbd->executa($this->sql_update($cbd));

    if ($this->atr("css")->valor == null) return true;

    return $this->insert($cbd);
  }

  public function insert(FS_cbd $cbd) {
    if (!$cbd->executa($this->sql_insert($cbd))) return false;

    $this->calcula_id($cbd);

    return true;
  }

  public function delete(FS_cbd $cbd) {
    if ($this->atr("id_style")->valor == null) return true;

    return $cbd->executa($this->sql_delete($cbd));
  }

  public static function __props($css):array {
    $a1 = ["align" => "left", "width" => "", "max-width" => "", "border" => "", "margin" => "", "padding" => "", "background-color" => "", "background-image" => ""];


    if ($css == null) return $a1;
    
    $a0 = explode(";", $css);

    if (!is_array($a0)) return $a1;

    foreach($a0 as $v0) {
      if ($v0 == null) continue;

      if (($p = strpos($v0, ":")) === false) continue;

      $k1 = substr($v0, 0, $p);
      $v1 = substr($v0, $p + 1);

      $a1[$k1] = $v1;
    }

    return $a1;
  }

  public static function __css($a_props):string {
    if ($a_props == null)    return "";

    if (!is_array($a_props)) return "";

    $css = "";
    foreach ($a_props as $k=>$v) $css .= "{$k}:{$v};";

    //~ return str_replace(" ", "", $css);
    return $css;
  }

  public static function __parseInt($a) {
    $a = str_replace("px", "", $a);
    $a = str_replace("%", "", $a);

    if (!is_numeric($a)) return "0";

    return round($a);
  }

  private function calcula_id(FS_cbd $cbd) {
    $r = $cbd->consulta("select LAST_INSERT_ID() as id");

    if (!$a = $r->next()) die("Style_obd.calcula_id(), (!a = r.next())");

    $this->atr("id_style")->valor = $a['id'];
  }
}

//-----------------------------------------

final class Style_mbd extends Mapa_bd {
  public function __construct() {
    $t = new Taboa_dbd("style");

    $t->pon_campo("id_style", new Numero(), true);
    $t->pon_campo("css");
    //~ $t->pon_campo("css_fondo");

    $this->pon_taboa($t);
  }
}

