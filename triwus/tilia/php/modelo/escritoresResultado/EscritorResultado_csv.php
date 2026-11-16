<?php

/** Devolve unha consulta de BD en formato CSV.
 *
 * @package tilia\modelo\escritorResultado
 */


class EscritorResultado_csv extends EscritorResultado {

  protected $s  = null;
  protected $ct = null;

  public function __construct(Iterador_bd $rbd = null, $separador = ";", $cualificadorTexto = "\"") {
    parent::__construct($rbd);

    $this->s = $separador;
    $this->ct = $cualificadorTexto;
  }

  protected function cabeceira($df = null) {
    if (count($df) == 0) return "";

    $s = null;
    foreach ($df as $k_dc=>$dc) $s .= ($s == null)?$k_dc:"{$this->s}{$k_dc}";

    return "{$s}\r\n";
  }

  protected function linha_detalle($df, $f) {
    $s = null;
    foreach ($f as $k_dato=>$dato) {
      if (is_numeric($k_dato)) continue;

      if ($s != null) $s .= $this->s;
      
      $s .= self::formato_dato_csv($dato, $this->ct);
    }

    if ($s == null) return null;

    return "{$s}\r\n";
  }

  private static function formato_dato_csv($valor, $ct) {
        if (!isset($valor)    ) $valor = "null";
    elseif (is_numeric($valor)) return $valor;
        
    return "{$ct}{$valor}{$ct}";
  }
}
