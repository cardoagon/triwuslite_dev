<?php

/** Serve para realizar a descrición de obxetos de base de datos empregados por Obxeto_bd.
 *
 * @package tilia\modelo\escritoresSQL\mapa
 */


class Mapa_bd {

  public $taboas = null;

  public $taboa_principal = null;

  public function __construct() {}

  final public function colle_clave() {
    $k = null;

    foreach ($this->taboas[$this->taboa_principal]->campos as $nome=>$campo)
      if ($campo->cp) $k[count($k)] = $campo->alias;

    return $k;
  }

  final function hai_relacions() {
    return count($this->taboas) > 1;
  }

  final public static function inicia_obd(Obxeto_bd $obd) {
    $dbd = $obd->mapa_bd();

    if (!is_array($dbd->taboas)) return null;

    $obd->atributos = null;

    foreach($dbd->taboas as $nome_t=>$t) {
      if (!is_array($t->campos)) continue;

      foreach ($t->campos as $nome_c=>$c) $obd->ini_atr($c);
    }
  }

  final public function pon_taboa(Taboa_dbd $t) {
    $this->engadir_taboa($t, true);
  }

  final public function relacion_fk(Taboa_dbd $taboa_detalle, $clave_principal, $clave_detalle, $tipo_relacion = "inner") {
    $taboa_detalle->relacion_fk($clave_principal, $clave_detalle, $tipo_relacion);

    $this->engadir_taboa($taboa_detalle, false);
  }

  private function engadir_taboa(Taboa_dbd $t, $principal = false) {
    $t->dbd = $this;

    $this->taboas[$t->nome] = $t;

    if ($principal) {
      if ($this->taboa_principal != null) die("Mapa_bd.pon_taboa(), this.taboa_principal != null");

      $this->taboa_principal = $t->nome;
    }
  }
}


/** Describe unha táboa de base de datos.
 *
 * @package tilia\modelo\escritoresSQL\mapa
 */

class Taboa_dbd {

  public $dbd;

  public $nome;

  public $campos;

  public $relacion_fk;

  public function __construct($nome) {
    $this->nome = $nome;
  }

  public function pon_campo($nome, Tipo_sql $tipo = null, $cp = false, $alias = null) {
    $c = new Campo_dbd($nome, $tipo, $cp, $alias);

    $c->tbd = $this;

    $this->campos[$c->nome] = $c;
  }


  public function relacion_fk($clave_principal, $clave_detalle, $tipo_relacion = "inner") {
    $this->relacion_fk = new Relacion($clave_principal, $clave_detalle, $tipo_relacion);
  }
}



/** Describe un campo dunha táboa de base de datos.
 *
 * @package tilia\modelo\escritoresSQL\mapa
 */

final class Campo_dbd {

  public $tbd;

  public $nome;

  public $alias;

  public $tipo;

  public $cp;

  public function __construct($nome, Tipo_sql $tipo = null, $cp = false, $alias = null) {
    if ($tipo === null) $tipo = new VarChar();
    if ($alias === null) $alias = $nome;

    $this->nome = $nome;
    $this->alias = $alias;
    $this->tipo = $tipo;
    $this->cp = $cp;
  }

  public function ini_atr(Obxeto_bd $obd, $valor = null) {
    return $this->tipo->ini_atr($obd, $this, $valor);
  }

  public function nome_sql(Sintaxis_sql $sintaxis, $sentencia) {
    if ($sentencia == "select") return $this->tipo->strsqlselect($this, $sintaxis);
    elseif (($sentencia == "update") || ($sentencia == "insert")) return $this->tipo->strsqlinsertupdate($this, $sintaxis);
    elseif ($sentencia == "orderby") return $this->alias;
  }

  public function formato_sql(Sintaxis_sql $sintaxis, $valor) {
    return $this->tipo->formato_sql($valor, $sintaxis);
  }


}


/** Describe unha relación entre duas táboas de base de datos.
 *
 * @package tilia\modelo\escritoresSQL\mapa
 */

final class Relacion {

  public $a_cp;

  public $a_cd;

  public $tipo_relacion;

  public function __construct($clave_principal, $clave_detalle, $tipo_relacion = "inner") {
    $this->a_cp = $clave_principal;
    $this->a_cd = $clave_detalle;
    $this->tipo_relacion = $tipo_relacion;
  }
}

