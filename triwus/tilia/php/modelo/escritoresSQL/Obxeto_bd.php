<?php

/** Esta clase representa obxetos que saberán escribir sentencias SQL como select, insert, update e delete,
 * en función dos datos almacenados en Atributo_obd's, e un Mapa_bd asociado, facilitando así a comunicación con SXBD.
 *
 * @package tilia\modelo\escritoresSQL
 */


abstract class Obxeto_bd {

  public $atributos = null;

  public function __construct() {
    Mapa_bd::inicia_obd($this);
  }

  abstract public function mapa_bd();

  public function reset() {
    Mapa_bd::inicia_obd($this);
  }

  public function clonar(Obxeto_bd $o) {
    $_atr = $o->atr();

    if ( !is_array($_atr)  ) return;
    if ( count($_atr) == 0 ) return;

    foreach($_atr as $k=>$atr) {
      if (!isset($this->atributos[$k])) continue;

      $this->atributos[$k]->valor = $atr->valor;
    }
  }

  public function ini_atr(Campo_dbd $c, $valor = null) {
    $this->atributos[$c->alias] = $c->ini_atr($this, $valor);
  }

  public function sup_atr($nombre) {
    unset($this->atributos[$nome]);
  }

  public function atr($nome = null) {
    if ($nome == null) return $this->atributos;

    if ( !isset($this->atributos[$nome]) ) return null;

    return $this->atributos[$nome];
  }

  public function a_resumo() {
    if (!is_array($this->atributos)) return null;

    $r = null;
    foreach ($this->atributos as $k=>$v) $r[$k] = $v->valor;

    return $r;
  }

  public function _nomes() {
    if (!is_array($this->atributos)) return null;

    $r = null;
    foreach ($this->atributos as $k=>$v) $r[] = $k;

    return $r;
  }

  public function tojson() {
    return json_encode($this->a_resumo());
  }

  public function post($post) {
    if (count($this->atributos) == 0) return;

    foreach($this->atributos as $k=>$v)
      if (isset($post[$k]))
        $this->atributos[$k]->post($post[$k]);
  }

  public function coje_k() {
    $dbd = $this->mapa_bd();

    if (($k_alias = $dbd->colle_clave()) == null) return null;

    $k = null;
    foreach ($k_alias as $v) $k[$v] = $this->atributos[$v];

    return $k;
  }

  final public function sql_select(Conexion_bd $cbd, $where = null, $orderby = null) {
    $eSQL = new Escritor_sql($cbd, $this);

    return $eSQL->select($where, $orderby);
  }

  final public function sql_update(Conexion_bd $cbd, $where = null, $tabla_detalle = null) {
    $eSQL = new Escritor_sql($cbd, $this);

    return $eSQL->update($where, $tabla_detalle);
  }

  final public function sql_insert(Conexion_bd $cbd, $tabla_detalle = null) {
    $eSQL = new Escritor_sql($cbd, $this);

    return $eSQL->insert($tabla_detalle);
  }

  final public function sql_delete(Conexion_bd $cbd, $where = null, $tabla_detalle = null) {
    $eSQL = new Escritor_sql($cbd, $this);

    return $eSQL->delete($where, $tabla_detalle);
  }
}



/** Representa atributos dun Obxeto_bd.
 *
 * @package tilia\modelo\escritoresSQL
 */

class Atributo_obd {

  public $obd = null;

  public $campo = null;

  public $valor = null;


  public function __construct(Obxeto_bd $obd, Campo_dbd $campo, $valor = null) {
    $this->obd = $obd;

    $this->campo = $campo;
    $this->valor = $valor;
  }

  public function tipo_sql() {
    return $this->campo->tipo;
  }

  public function valor() {
    return $this->valor;
  }

  public function post($post) {
    $this->valor = $post;
  }

  public function sql_where(Conexion_bd $cbd, $operador = "=") {
    $n = "{$this->campo->tbd->nome}.{$this->campo->nome}";
    $v = $this->tipo_sql()->formato_sql($this->valor, $cbd->sintaxis());

    return "{$n} {$operador} {$v}";
  }

  public function formato_sql(Sintaxis_sql $sintaxis) {
    return $this->campo->formato_sql($sintaxis, $this->valor);
  }

  public function __toString() {
    return $this->valor;
  }
}

//-----------------------------------------------------------


/** Representa atributos blob dun Obxeto_bd.
 *
 * @package tilia\modelo\escritoresSQL
 */

final class Atrobd_blob extends Atributo_obd {
  public function __construct(Obxeto_bd $obd, Campo_dbd $campo, $valor = null) {
    parent::__construct($obd, $campo, $valor);
  }
}

//-----------------------------------------------------------


/** Representa atributos password dun Obxeto_bd.
 *
 * @package tilia\modelo\escritoresSQL
 */

final class Atrobd_psswd extends Atributo_obd {
  public function __construct(Obxeto_bd $obd, Campo_dbd $campo, $valor = null) {
    parent::__construct($obd, $campo, $valor);
  }

  public function cifrar($b = true) {
    $this->tipo_sql()->cifrar = $b;
  }
}

//-----------------------------------------------------------


/** Representa atributos varchar dun Obxeto_bd.
 *
 * @package tilia\modelo\escritoresSQL
 */

final class Atrobd_varchar extends Atributo_obd {
  public function __construct(Obxeto_bd $obd, Campo_dbd $campo, $valor = null) {
    parent::__construct($obd, $campo, $valor);
  }

  public function base64($b = true) {
    $this->tipo_sql()->base64 = $b;
  }

  public function trim($b = true) {
    $this->tipo_sql()->trim = $b;
  }

  public function post($post) {
    if ($this->tipo_sql()->base64) $post = base64_decode($post);

    parent::post($post);
  }
}

//-----------------------------------------------------------


/** Representa atributos date dun Obxeto_bd.
 *
 * @package tilia\modelo\escritoresSQL
 */

final class Atrobd_data extends Atributo_obd {
  public function __construct(Obxeto_bd $obd, Campo_dbd $campo, $valor = null) {
    parent::__construct($obd, $campo, $valor);
  }

  public function a_data() {
    return $this->tipo_sql()->a_data($this->valor);
  }

  public function fvalor(string $formato, ?string $modificador = null):string {
    if ($this->valor == null) return "";
    

    if ($modificador == null) return date($formato, strtotime($this->valor));
    
    
    return date( $formato, strtotime( date("Y-m-d", strtotime($this->valor) ) . $modificador) );
  }
}

