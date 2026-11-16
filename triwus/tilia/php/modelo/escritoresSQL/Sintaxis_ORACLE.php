<?php

/** Esta clase permite encapsular parte das reglas de sintaxis SQL de ORACLE.
 *
 * @package tilia\modelo\escritoresSQL\sintaxis
 */

class Sintaxis_ORACLE extends Sintaxis_sql {

  public function __construct()  {
    parent::__construct();
  }

  public function select_data(Campo_dbd  $c) {
    $formato = self::formatoData_oracle($c->tipo->formato);

    return "to_char({$c->tbd->nome}.{$c->nome}, '{$formato}') as {$c->alias}";
  }

  public function str_data($valor, Tipo_sql $t) {
    $a_data = $t->a_data($valor);

    $YmdHis = "{$a_data['Y']}{$a_data['m']}{$a_data['d']}{$a_data['H']}{$a_data['i']}{$a_data['s']}";

    return "to_date('{$YmdHis}', 'yyyymmddHH24MISS')";
  }

  public function str_timestamp() {
    return "sysdate";
  }

  public function blob_encode($valor) {
    return "EMPTY_BLOB()";
  }

  public static function blob_decode($valor = null) {
    if ($valor == null) return null;

    return $valor;
  }

  private static function formatoData_oracle($formato_php) {
    $f = str_replace("Y", "YYYY", $formato_php);
    $f = str_replace("m", "MM", $f);
    $f = str_replace("d", "DD", $f);
    $f = str_replace("H", "HH24", $f);
    $f = str_replace("i", "MI", $f);
    $f = str_replace("s", "SS", $f);

    return $f;
  }
}
