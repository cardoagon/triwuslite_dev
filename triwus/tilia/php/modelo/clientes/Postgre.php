<?php

/** Representa unha conexión cun SGBD Postgre.
 *
 * @package tilia\modelo\clientes
 */

class Postgre extends Conexion_bd {

  public function __construct($servidor, $usuario, $clave, $bd) {
    parent::__construct();

    $this->cbd = pg_connect("host={$servidor} dbname={$bd} user={$usuario} password={$clave}");
  }

  public function sintaxis() {
    return new Sintaxis_Postgre();
  }

  public function transaccion($booleano = true) {
    if ($booleano) {
      $this->executa("begin") or die("ERROR: Postgre->transaccion()."); //non se inicia a transaccion.
    }
  }

  public function commit() {
    return $this->executa("commit");
  }

  public function rollback() {
    return $this->executa("rollback");
  }

  public function consulta($sql) {
    if ($this->verbose) self::verbose($sql);

    return new PostgreResult(pg_query($this->cbd, $sql));
  }

  public function executa($sql) {
    if ($this->verbose) self::verbose($sql);

    return pg_query($this->cbd, $sql);
  }

  public function close() {
    pg_close($this->cbd);
  }
}


/** Representa o resultado dunha consulta SQL SELECT de Postgre.
 *
 * @package tilia\modelo\clientes
 */

class PostgreResult extends Resultado_bd {

  public function __construct($result) {
    parent::__construct($result, 0);
  }

  public function numFilas() {
    if (!$this->result) return 0;
    return pg_num_rows($this->result);
  }

  public function numCampos() {
    if (!$this->result) return 0;
    return pg_num_fields($this->result);
  }

  public function descCampo($numCampo) {
     if (($numCampo < 0) || ($numCampo >= $this->numCampos())) return null;

     $dc['nombre'] = pg_field_name($this->result, $numCampo);
     $dc['tipo'] = pg_field_type($this->result, $numCampo);
     $dc['longitud'] = pg_field_size($this->result, $numCampo);

     switch (strtolower($dc['tipo'])) {
         case "string":
            $dc['tipo'] = "cadena";
            break;
         case "int":
         case "real":
            $dc['tipo'] = "numero";
            break;
         case "datetime":
            $dc['tipo'] = "fecha";
            break;
         default:
            $dc['tipo'] = "cadena";
            break;
     }

    return $dc;
  }

  public function next() {
    return pg_fetch_assoc($this->result);
  }

}
