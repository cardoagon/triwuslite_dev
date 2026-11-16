<?php

/** Esta clase define unha plantilla que servirá encapsular parte das reglas de sintaxis SQL especializadas
 * segundo un SXBD concreto. Ainda que a maioria de SGBD emplean un SQL standard, as diferenzas de sintaxe aparecen
 * cando se manexan tipos como blob ou date.

 *
 * @package tilia\modelo\escritoresSQL\sintaxis
 */


abstract class Sintaxis_sql {

  protected function __construct() {}

  abstract public function select_data(Campo_dbd  $campo);

  abstract public function str_data($valor, Tipo_sql $t);

  abstract public function str_timestamp();

  abstract public function blob_encode($valor);

  //~ abstract public static function blob_decode($valor = null);

  public function whereRelacion(Mapa_bd $d_bbdd) {
    if (!$d_bbdd->hai_relacions()) return "";

    $nome_tp = $d_bbdd->taboa_principal;

    $wr = "";
    foreach($d_bbdd->taboas as $nome_td=>$td) {
      if ($nome_tp != $nome_td) {
        $union_i = "{$td->relacion_fk->tipo_relacion} join";
        $wr_i = "";
        for ($i = 0; $i < count($td->relacion_fk->a_cp); $i++) {
          $cp_i = $td->relacion_fk->a_cp[$i];
          $cd_i = $td->relacion_fk->a_cd[$i];
          if ($wr_i == "")
            $wr_i = "{$nome_tp}.{$cp_i} = {$nome_td}.{$cd_i}";
          else
            $wr_i .= " and {$nome_tp}.{$cp_i} = {$nome_td}.{$cd_i}";
        }


        $wr .= " {$union_i} {$nome_td} on ({$wr_i})";
      }
    }

    return $wr;
  }

}

