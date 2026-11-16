<?php

/** Esta clase permite encapsular parte das reglas de sintaxis SQL de MySql.
 *
 * @package tilia\modelo\escritoresSQL\sintaxis
 */

class Sintaxis_MySql extends Sintaxis_sql {

  public function __construct() {
    parent::__construct();
  }

  public function select_data(Campo_dbd  $c) {
    $formato = self::formatoData_mysql($c->tipo->formato);

    return "DATE_FORMAT({$c->tbd->nome}.{$c->nome}, '{$formato}') as {$c->alias}";
  }

  public function str_data($valor, Tipo_sql $t) {
    $a_data = $t->a_data($valor);

    return "'{$a_data['Y']}{$a_data['m']}{$a_data['d']}{$a_data['H']}{$a_data['i']}{$a_data['s']}'";
  }

  public function str_timestamp() {
    return "now()";
  }

  private static function formatoData_mysql($formato_php) {
    $f = str_replace("Y", "%Y", $formato_php);
    $f = str_replace("m", "%m", $f);
    $f = str_replace("d", "%d", $f);
    $f = str_replace("H", "%H", $f);
    $f = str_replace("i", "%i", $f);
    $f = str_replace("s", "%S", $f);

    return $f;
  }

  public function blob_encode($valor) {
    return "'" . addslashes($valor) . "'";
  }

  public static function blob_decode($valor = null) {
    if ($valor == null) return null;

    return $valor;
  }
}

