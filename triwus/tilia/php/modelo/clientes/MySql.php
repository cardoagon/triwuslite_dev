<?php


/** Representa unha conexión cun SGBD MySql.
 *
 * @package tilia\modelo\clientes
 */
class MySql extends Conexion_bd {
  public string $bd;
  
  public function __construct($servidor, $usuario, $clave, $bd) {
    parent::__construct();
    
    $this->bd  = $bd;

    $this->cbd = mysqli_connect($servidor, $usuario, $clave, $bd) or self::verbose(mysqli_error($this->cbd));

    mysqli_set_charset($this->cbd, "utf8");
  }

  public function transaccion($booleano = true) {
    if ($booleano)
      $this->executa("BEGIN");
  }

  public function sintaxis() {
    return new Sintaxis_MySql();
  }

 public function commit() {
    return $this->executa("COMMIT");
  }

  public function rollback() {
    return $this->executa("ROLLBACK");
  }

  public function consulta($sql) {
    if ($this->verbose) self::verbose($sql);
//~ self::verbose($sql);
    return new MySqlResult(mysqli_query($this->cbd, $sql));
  }

  public function executa($sql) {
    if ($this->verbose) self::verbose($sql);

    return mysqli_query($this->cbd, $sql) or self::verbose(mysqli_error($this->cbd));
  }

  public function close() {
    mysqli_close($this->cbd);
  }
}



/** Representa o resultado dunha consulta SQL SELECT de MySql.
 *
 * @package tilia\modelo\clientes
 */
class MySqlResult extends Resultado_bd {

  public function __construct($result) {
    parent::__construct($result, 0);
  }

  public function numFilas() {
    if (!$this->result) return 0;
    return mysqli_num_rows($this->result);
  }


  public function numCampos() {
    if (!$this->result) return 0;
    return mysqli_num_fields($this->result);
  }


  public function descCampo($numCampo) {
     $campo = mysqli_fetch_field($this->result, $numCampo);

//~ echo "{$campo->name}::{$campo->type}::{$campo->primary_key}<br>";

     $a['nombre'] = $campo->name;
     switch (strtolower($campo->type)) {
         case "string":
            $a['tipo'] = "cadena";
            break;
         case "int":
         case "real":
            $a['tipo'] = "numero";
            break;
         case "datetime":
            $a['tipo'] = "fecha";
            break;
         default:
            $a['tipo'] = "cadena";
            break;
     }
     $a['longitud'] = 0;
     $a['esClave'] = $campo->primary_key;

    return $a;
  }


  public function next() {
    if ($this->result == null) return false;

    return mysqli_fetch_array($this->result);
  }

}
