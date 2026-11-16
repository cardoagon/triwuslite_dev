<?php

/** Clase abstracta que serve como plantilla para o deseño de novas clases de escritores.
 *
 * @package tilia\modelo\escritorResultado
 */

abstract class EscritorResultado {

  protected $rbd = null;

  protected $ct_lineas = 0;

  public function __construct(Iterador_bd $rbd = null) {
    if ($rbd != null) $this->pon_iterador($rbd);
  }

  public function pon_iterador(Iterador_bd $rbd) {
    $this->rbd = $rbd;
  }

  public function escribe($cabeceira = true, $totais = true, $paxina = 0) {
    $df = $this->rbd->descFila();

    $is = $this->inicia_saida();

    if ($cabeceira) $c = $this->cabeceira($df);

    $s = null;
    for ($this->ct_lineas = 1; $f = $this->rbd->next(); $this->ct_lineas++) {
      if (($cabeceira) && ($paxina > 0))
        if ((0 == ($this->ct_lineas % $paxina)) && ($this->ct_lineas > 0)) {
          $s .= $this->salto_paxina();
          $s .= $this->cabeceira();
        }

      $s .= $this->linha_detalle($df, $f);
    }
    
    
    if ($s == null) return null;

    if ($totais) $t = $this->totais();

    $fs = $this->finaliza_saida();

    return $is . $c . $s . $t . $fs;
  }

  protected function inicia_saida() {}

  protected function finaliza_saida() {}

  protected function cabeceira($df = null) {}

  protected function totais() {}

  protected function salto_paxina() {}

  abstract protected function linha_detalle($df, $f);
}
