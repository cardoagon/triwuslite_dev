<?php

/** Partindo dun <Obxeto_bd>, esta clase será capaz de escribir sentencias SQL select, update, insert e delete.
 *
 * @package tilia\modelo\escritoresSQL
 */

class Escritor_sql {

  protected $obd = null;

  private $cbd = null;

  public function __construct(Conexion_bd $cbd, Obxeto_bd $obd) {
    if ($obd == null)  die ("Escritor_sql.__construct(), obd == null");
    if ($cbd == null)  die ("Escritor_sql.__construct(), cbd == null");

    $this->obd = $obd;
    $this->cbd = $cbd;
  }

  public function select($where = null, $orderby = null) {
    $dbd = $this->obd->mapa_bd();
    $sintaxis = $this->cbd->sintaxis();

    $select = null;
    foreach ($dbd->taboas as $nome_t=>$t) {
      foreach ($t->campos as $nome_c=>$c) {
        if (($campo_sql = $c->nome_sql($sintaxis, "select")) == null) continue;

        if ($select == null)
          $select = "SELECT {$campo_sql}";
        else
          $select .= ", {$campo_sql}";
      }
    }

    $taboa_principal = $dbd->taboa_principal;

    $from = $taboa_principal;
    $wr  = $sintaxis->whereRelacion($dbd);
    if ($wr != "") $from .= $wr;

    $where = ($where == null)?"":"WHERE {$where}";
    $orderby = ($orderby == null)?"":"ORDER BY {$orderby}";

    return "{$select} FROM {$from} {$where} {$orderby}";
  }

  public function update($where = null, $taboa_detalle = null) {
    $dbd = $this->obd->mapa_bd();
    $sintaxis = $this->cbd->sintaxis();

    $nome_t = ($taboa_detalle == null)?$dbd->taboa_principal:$taboa_detalle;
    $updates = "";
    $where_cp = null;

    foreach ($dbd->taboas[$nome_t]->campos as $nome_c=>$c)
      if (!$c->cp) {
        if (($str_c = $c->nome_sql($sintaxis, "update")) != null) {
          $str_v = $this->obd->atr($c->alias)->formato_sql($sintaxis);

          if ($updates != "") $updates .= ", ";

          $updates .= "{$str_c} = {$str_v}";
        }
      }
      elseif ($where != null);
      else {
        $w_aux = "{$c->tbd->nome}.{$c->nome} = " . $this->obd->atr($c->alias)->formato_sql($sintaxis);

        if ($where_cp == null)
          $where_cp = $w_aux;
        else
          $where_cp .= " AND {$w_aux}";
      }

    if ($where == null) $where = $where_cp;

    $where = ($where == null)?"":"WHERE {$where}";

    return "UPDATE {$nome_t} SET {$updates} {$where}";
  }

  public function insert($taboa_detalle = null) {
    $dbd = $this->obd->mapa_bd();
    $sintaxis = $this->cbd->sintaxis();

    $campos = null;
    $valores = null;

    $nome_t = ($taboa_detalle == null)?$dbd->taboa_principal:$taboa_detalle;

    foreach ($dbd->taboas[$nome_t]->campos as $nome_c=>$c) {
      if (($campo = $c->nome_sql($sintaxis, "insert")) != null) {
        if ($campos != null) $campos .= ", ";
        if ($valores != null) $valores .= ", ";

        $campos .= $campo;
        $valores .= $this->obd->atr($c->alias)->formato_sql($sintaxis);
      }
    }

    return "INSERT INTO {$nome_t} ({$campos}) VALUES ({$valores})";
  }

  public function delete($where = null, $taboa_detalle = null) {
    $dbd = $this->obd->mapa_bd();
    $sintaxis = $this->cbd->sintaxis();

    $nome_t = ($taboa_detalle == null)?$dbd->taboa_principal:$taboa_detalle;

    if ( !isset($dbd->taboas[$nome_t]) ) die ("Escritor_sql.delete(), !isset(dbd.taboas[\"{$nome_t}\"])");

    if ($where == null) {
      if (count($dbd->taboas[$nome_t]->campos) == 0) die ("Escritor_sql.delete(), count(dbd.taboas[\"{$nome_t}\"].campos) == 0)");

      foreach ($dbd->taboas[$nome_t]->campos as $nome_c=>$c)
        if ($c->cp) {
          $w_aux = "{$c->tbd->nome}.{$c->nome} = " . $this->obd->atr($c->alias)->formato_sql($sintaxis);

          if ($where == null)
            $where = $w_aux;
          else
            $where .= " AND {$w_aux}";
        }
    }

    $d = "DELETE FROM {$nome_t}";
    if ($where != null) $d .= " WHERE {$where}";

    return $d;
  }

  final public function select_blob(Atributo_obd $atr) {
    $dbd = $this->obd->mapa_bd();
    $sintaxis = $this->cbd->sintaxis();

    $nome_t = $atr->campo->tbd->nome;

    $where = "";
    foreach ($dbd->taboas[$nome_t]->campos as $nome_c=>$c)
      if ($c->cp) {
        $w_aux = "{$c->nome} = " . $this->obd->atr($c->alias)->formato_sql($sintaxis);

        if ($where == null)
          $where = $w_aux;
        else
          $where .= " AND {$w_aux}";
      }

    if ($where != null) $where = "WHERE {$where}";

    return "SELECT {$atr->campo->nome} FROM {$nome_t} {$where}";
  }

  final public function update_blob(Atributo_obd $atr) {
    $dbd = $this->obd->mapa_bd();
    $sintaxis = $this->cbd->sintaxis();

    $nome_t = $atr->campo->tbd->nome;

    $updates = $atr->campo->nome . " = " . $sintaxis->blob_encode($atr->valor);

    $where = "";
    foreach ($dbd->taboas[$nome_t]->campos as $nome_c=>$c)
      if ($c->cp) {
        $w_aux = "{$c->nome} = " . $this->obd->atr($c->alias)->formato_sql($sintaxis);

        if ($where == null)
          $where = $w_aux;
        else
          $where .= " AND {$w_aux}";
      }

    if ($where != null) $where = "WHERE {$where}";

    return "UPDATE {$nome_t} SET {$updates} {$where}";
  }
}

