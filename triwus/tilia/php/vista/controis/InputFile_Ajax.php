<?php

class InputFile_Ajax extends Control {

  public $id = null;
  public $rutadestino = null;
  public $rutaloadphp = null;
  public $maxFileSize = null;

  public $accept   = null;
  public $multiple = false;

  public $callback = null;

  public function  __construct($id, $maxFileSize = 1000000, $rutadestino = null, $multiple = false) {
    parent::__construct($id);

    $this->id = $id;
    $this->etq = $etq;
    
    $this->rutadestino( $rutadestino );
    $this->rutaloadphp( Tilia::home()."/php/vista/controis/InputFile_Ajax.subirarchivo.php" );
    
    $this->maxFileSize = $maxFileSize;
    
    $this->multiple    = $multiple;
  }

  public function html():string {
    $nc = $this->nome_completo();

    $id_b = "id='{$nc}_b'";
    $id_d = "id='{$nc}_d'";
    $id_p = "id='{$nc}_p'";
    $id_s = "id='{$nc}_s'";

    $multiple = ($this->multiple)?"multiple":"";

    $accept   = ($this->accept   == null)?"":" accept = \"{$this->accept}\"";
    
    $callback = ($this->callback == null)?"null":$this->callback;

    return "
            <progress {$id_b} value='0' max='100' style='display: none;'></progress>
            <input type='hidden' {$id_s} value='{$this->maxFileSize}' />
            <input type='hidden' {$id_p} value='{$this->rutaloadphp}' />
            <input type='hidden' {$id_d} value='{$this->rutadestino}' />
            <input type='file' " . $this->html_nome() . " {$accept} {$multiple}
                   onchange='tilia_uploadFile_0(this, {$callback})'
                   " . $this->html_title() . $this->html_clase() . $this->html_estilo() . "/>";
  }

  public function html_etq() {
    if ($this->n_etq == null) return "";

    return "<label for='" . $this->nome_completo() . "' id='label_{$this->id}'>{$this->n_etq}</label>";
  }

  public function callback($c = null) {
    if ($c == null) return $this->callback;

    $this->callback = $c;
  }

  public function rutadestino($r = null) {
    if ($r == null) return base64_decode($this->rutadestino);

    $this->rutadestino = base64_encode(uniqid( $r, true));
  }

  public function rutaloadphp($r = null) {
    if ($r == null) return base64_decode($this->rutaloadphp);

    $this->rutaloadphp = base64_encode($r);
  }
}
