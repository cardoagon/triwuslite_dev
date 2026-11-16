<?php

abstract class SubsFicha extends Componente {
  protected $k = null;

  public function __construct(string $ptw) {
    parent::__construct("cficha", $ptw);

    $this->visible = false;
  }
  
  abstract public function obd(Conexion_bd $cbd):Obxeto_bd;
  abstract public function post_obd(Obxeto_bd $o):void; 

  public function k() { //* mixed
    return $this->k;
  }

  public function abrir($k):void {
    $this->visible = true;
    $this->k       = $k;    
  }

  public function pechar():void {
    $this->visible = false;
    $this->k       = null;
  }
}
