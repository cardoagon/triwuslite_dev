<?php

/** Esta clase permite encapsular parte das reglas de sintaxis SQL de Sqlite.
 *
 * @package tilia\modelo\escritoresSQL\sintaxis
 */

class Sintaxis_Sqlite extends Sintaxis_sql {

  public function __construct() {
    parent::__construct();
  }

  public function select_data(Campo_dbd  $c) {
    $formato = self::formatoData_sqlite($c->tipo->formato);

    return "strftime('{$formato}', {$c->tbd->nome}.{$c->nome}) as {$c->alias}";
  }

  public function str_data($valor, Tipo_sql $t) {
    $a_data = $t->a_data($valor);

    $Y = (strlen($a_data['Y']) == 4)?"{$a_data['Y']}-":"";
    $m = (strlen($a_data['m']) == 2)?"{$a_data['m']}-":"";
    $d = (strlen($a_data['d']) == 2)?$a_data['d']:"";
    $H = (strlen($a_data['H']) == 2)?" {$a_data['H']}:":"";
    $i = (strlen($a_data['i']) == 2)?"{$a_data['i']}:":"";
    $s = (strlen($a_data['s']) == 2)?$a_data['s']:"";

    return "'{$Y}{$m}{$d}{$H}{$i}{$s}'";
  }

  public function str_timestamp() {
    return "datetime('now')";
  }

  private static function formatoData_sqlite($formato_php) {
    $f = str_replace("Y", "%Y", $formato_php);
    $f = str_replace("m", "%m", $f);
    $f = str_replace("d", "%d", $f);
    $f = str_replace("H", "%H", $f);
    $f = str_replace("i", "%M", $f);
    $f = str_replace("s", "%S", $f);

    return $f;
  }

  public function blob_encode($valor) {
    return "'" . base64_encode($valor) . "'";
  }

  public static function blob_decode($valor = null) {
    if ($valor == null) return null;

    return base64_decode($valor);
  }
}
