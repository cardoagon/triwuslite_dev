<?php

/** Representa unha conexión cun SGBD Sqlit3.
 *
 * @package tilia\modelo\clientes
 */

class Sqlit3 extends Conexion_bd {

  public function __construct($url) {
    parent::__construct();

    $this->cbd = new SQLite3($url);
  }

  public function sintaxis() {
    return new Sintaxis_Sqlite();
  }

  public function transaccion($booleano = true) {
    if (!$booleano) return;

    $this->executa("BEGIN") or die("ERROR: Sqlite->transaccion().");
  }

  public function commit() {
    return $this->executa("COMMIT");
  }

  public function rollback() {
    return $this->executa("ROLLBACK");
  }

  public function consulta($sql) {
    if ($this->verbose) self::verbose($sql);

    return new Sqlit3_result($this->cbd->query($sql));
  }

  public function executa($sql) {
    if ($this->verbose) self::verbose($sql);

    return $this->cbd->exec($sql);
  }


  public function close() {
    $this->cbd->close();
  }
}



/** Representa o resultado dunha consulta SQL SELECT de Sqlite.
 *
 * @package tilia\modelo\clientes
 */

class Sqlit3_result extends Resultado_bd {

  public function __construct(SQLite3Result $result) {
    parent::__construct($result, 0);
  }

  public function numFilas() {
    return null;//sqlite_num_rows($this->result);
  }

  public function numCampos() {
    return $this->result->numColumns();
  }

  public function descCampo($numCampo) {
    if (($numCampo >= 0) && ($numCampo < $this->numCampos())) {

      $dc['nombre'] = $this->result->columnName($numCampo);
      $dc['tipo'] = "cadena";

      return $dc;
    }

    return null;
  }

  public function next() {
    return $this->result->fetchArray(SQLITE3_ASSOC);
  }
}

