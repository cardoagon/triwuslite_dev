<?php

/** Clase que encapsula un comunicación cliente-servidor dun Componente AJAX.
 *
 * @package tilia\controlador
 */

interface IComponente_aa {
  public function aa_exec($tilia_op);
  public function aa_json($tilia_op);
}


abstract class ActionAJAX implements ITilia {
  public  $tilia_op = null;

  protected $c        = null;
  protected $_request = null;

  public function __construct(IComponente_aa $c) {
    $this->c = $c;
  }

  public function postHTTP($http_request, $http_files = null) { //* IMPLEMENTA ITilia
//~ print_r($http_request);

    $this->_request = array(); unset( $this->tilia_op );

    foreach($http_request as $k=>$v) {
      if ($k == "tilia_op") {
        $this->tilia_op = $v;

        continue;
      }

      $_k = explode(Escritor_html::cnome, $k);

      if (!isset($_k[1]))
        $this->_request[$_k[0]] = $v;
      else
        $this->_request[ substr($k, strlen($_k[0]) + 1) ] = $v;
    }

//~ print_r($this->_request);

    $this->c->post( $this->_request );
  }

  public function operacion() { //* IMPLEMENTA ITilia
//~ echo "0::tilia_op::{$this->tilia_op}";

    $this->c->aa_exec($this->tilia_op);

    return $this;
  }

  final public function devolver() { //* IMPLEMENTA ITilia
    $this->_request = null;

    return $this->c->aa_json($this->tilia_op);
  }

  public function readonly($b = true) {
    $this->c->readonly($b);
  }

  public function icaa(IComponente_aa $c = null) {
    if ($c != null) $this->c = $c;

    return $this->c;
  }
}
