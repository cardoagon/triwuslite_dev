<?php


/** Representa unha conexión cun SGBD Oracle.
 *
 * @package tilia\modelo\clientes
 */

class Oracle extends Conexion_bd {
  private $modo_execute = OCI_COMMIT_ON_SUCCESS;

  public function __construct($usuario, $clave, $cadenaTNS) {
    parent::__construct();
    $this->cbd = OCINLogon($usuario, $clave, $cadenaTNS);
  }

  public function sintaxis() {
    return new Sintaxis_ORACLE();
  }

  public function transaccion($booleano = true) {
    if ($booleano) {
      $this->modo_execute = OCI_DEFAULT;

      if ($this->verbose) self::verbose("BEGIN");
    }
    else
      $this->modo_execute = OCI_COMMIT_ON_SUCCESS;
  }

  public function commit() {
    ocicommit($this->cbd);

    if ($this->verbose) self::verbose("COMMIT");
  }

  public function rollback() {
    ocirollback($this->cbd);

    if ($this->verbose) self::verbose("ROLLBACK");
  }

  public function consulta($sql) {
    if ($this->verbose) self::verbose($sql);

    $sql_parse = OCIParse($this->cbd, $sql);

    $resultado = new OracleResult($sql_parse);

    OCIExecute($sql_parse, $this->modo_execute);

    return $resultado;
  }

  public function executa($sql) {
    if ($this->verbose) self::verbose($sql);

    $sql_parse = OCIParse($this->cbd, $sql);

    return OCIExecute($sql_parse, $this->modo_execute);
  }

  public function close() {
    OCILogOff($this->cbd);
  }

  public function select_blob(Atrobd_blob $atr) {
    $eSQL = new Escritor_sql($this, $atr->obd);

    $r = $this->consulta($eSQL->select_blob($atr));

    $a = $r->next();

    //echo "select_blob::{$atr->campo->nome}<br>";

    $atr->valor = $this->sintaxis()->blob_decode($a[$atr->campo->nome]->load());
  }

  public function update_blob(Atrobd_blob $atr) {
    $eSQL = new Escritor_sql($this, $atr->obd);

    $sql = $eSQL->update_blob($atr) . "RETURNING {$atr->campo->nome} INTO :mylob";

    if ($this->verbose) self::verbose($sql);

    $sql_parse = OCIParse($this->cbd, $sql);

    $mylob = OCINewDescriptor($this->cbd, OCI_D_LOB);

    OCIBindByName($sql_parse, ':mylob', $mylob, -1, OCI_B_CLOB);

    if (!OCIExecute($sql_parse, $this->modo_execute)) return false;

    $ok = $mylob->save($atr->valor);

    $mylob->free();
    oci_free_statement($sql_parse);


    return $ok;
  }
}



/** Representa o resultado dunha consulta SQL SELECT de ORACLE.
 *
 * @package tilia\modelo\clientes
 */

class OracleResult extends Resultado_bd{

  public function __construct($result) {
    parent::__construct($result, 1);
  }


  public function numFilas() {
    return OCIRowCount($this->result);
  }

  public function numCampos() {
    return ocinumcols($this->result);
  }

  public function descCampo($numCampo) {
     if (($numCampo > 0) && ($numCampo <= $this->numCampos())) {

      $dc['nombre'] = ocicolumnname($this->result, $numCampo);
      $dc['tipo'] = ocicolumntype($this->result, $numCampo);
      $dc['longitud'] = ocicolumnsize($this->result, $numCampo);

      switch ($dc['tipo']) {
        case "FLOAT":
        case "LONG":
        case "NUMBER":
          $dc['tipo'] = "numero";
          break;
        case "CHAR":
        case "VARCHAR":
        case "VARCHAR2":
        case "NCHAR":
        case "NCHAR2":
          $dc['tipo'] = "cadena";
          break;
        case "DATE":
          $dc['tipo'] = "fecha";
      }

      return $dc;
    }
    else
      return null;
  }

  public function next() {
    $x = oci_fetch_assoc($this->result);
    if (!is_array($x)) return $x;

    $i = $this->iCprimero;
    foreach($x as $k=>$v) {
      $dc = $this->descCampo($i);
      if ($dc['tipo'] == "numero") $v = str_replace(",", ".", $v);

      $x2[$k] = $v;

      $i++;
    }

    return $x2;
  }
}

