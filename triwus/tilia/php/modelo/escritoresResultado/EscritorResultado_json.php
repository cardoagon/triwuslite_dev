<?php

/** Devolve unha consulta de BD en formato JSON.
 *
 * @package tilia\modelo\escritorResultado
 */


class EscritorResultado_json extends EscritorResultado {
  private $_r = array();
  
  public function __construct(Iterador_bd $rbd = null) {
    parent::__construct($rbd);
  }

  public function escribe($cabeceira = true, $totais = true, $paxina = 0) {
    $df = $this->rbd->descFila();

    $this->_r = array();

    for ($i = 1; $f = $this->rbd->next(); $i++) $this->linha_detalle($df, $f);


    return json_encode($this->_r);
  }

  protected function linha_detalle($df, $f) {
    $_r = array();
    
    foreach ($f as $k => $v) {
      if (is_numeric($k)) continue;

      $_r[$k] = $v;
    }

    $this->_r[] = $_r;
  }
}
