<?php


/** Esta clase abstracta representa unha conexión cun SGBD.
 *
 * @package tilia\modelo\clientes
 */
abstract class Conexion_bd {

  public $verbose;

  protected $cbd;

  protected function __construct() {}

  public function isnull() {
    return $this->cbd == null;
  }

  abstract public function sintaxis();

  abstract public function consulta($sql);

  abstract public function executa($sql);

  abstract public function transaccion($booleano = true);

  abstract public function commit();

  abstract public function rollback();

  abstract public function close();

  public function csv($sql, $separador = ";", $cualificadorTexto = "\"") {
    $escritor = new EscritorResultado_csv($this->consulta($sql), $separador, $cualificadorTexto);

    return $escritor->escribe();
  }

  public function json($sql) {
    $escritor = new EscritorResultado_json( $this->consulta($sql) );

    return $escritor->escribe();
  }

  public function html($sql, EscritorResultado_html $e = null) {
    if ($e == null) $e = new EscritorResultado_html();

    $e->pon_iterador($this->consulta($sql));

    return $e->escribe();
  }

  public function xml($sql, $nome_raiz, $nome_rexistro) {
    $escritor = new EscritorResultado_xml($nome_raiz, $nome_rexistro, $this->consulta($sql));

    return $escritor->escribe();
  }

  public function select_blob(Atrobd_blob $atr) {
    $eSQL = new Escritor_sql($this, $atr->obd);

    $r = $this->consulta($eSQL->select_blob($atr));

    $a = $r->next();

    $atr->valor = $this->sintaxis()->blob_decode($a[$atr->campo->nome]);
  }

  public function update_blob(Atrobd_blob $atr) {
    $eSQL = new Escritor_sql($this, $atr->obd);

    return $this->executa($eSQL->update_blob($atr));
  }
/*
  final protected static function verbose($sql) {
    error_log( "{$sql};\n" );
  }
*/
  final protected static function verbose($sql) {
    if (($log = Tilia::log()) == null)
      echo "{$sql};<br>\n";
    else
      $log->anotar("{$sql};\n");
  }

}



/** Representa o resultado dunha consulta SQL SELECT.
 *
 * @package tilia\modelo\clientes
 */
abstract class Resultado_bd implements Iterador_bd {
  protected $result = null;  //* es el resultado de una consulta.

  protected $iCprimero = null;

  public function __construct($result, $iCprimero = 0) {
    $this->result = $result;
    $this->iCprimero = (int)$iCprimero;
  }

  public function descFila() {
    return [];
/*
    if ($this->iCprimero == 0) $nc = $this->numCampos() - 1;
    elseif ($this->iCprimero == 1) $nc = $this->numCampos();
    else echo "<font color=#000000><b>ERROR: Resultado_bd->descFila() 'this->iCprimero' no pertenece a {0, 1}.</b></font><br>";

    for ($i = $this->iCprimero; $i <= $nc; $i++) {
      $dc = $this->descCampo($i);

      $df[$dc['nombre']]['tipo'    ] = $dc['tipo'];
      $df[$dc['nombre']]['esClave' ] = $dc['esClave'];
      $df[$dc['nombre']]['longitud'] = $dc['longitud'];
    }

    return $df;
*/
  }

  abstract public function numFilas();

  abstract public function numCampos();

  abstract public function descCampo($numCampo);

  public function next() {}
}



/** Podemos crear clases que implementen esta Interface, para construir un iterador compatible con clases EscritorResultado.
 *
 * @package tilia\modelo\clientes
 */
interface Iterador_bd {

  public function descFila();

  public function next();
}
