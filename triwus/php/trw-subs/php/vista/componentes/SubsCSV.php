<?php


abstract class SubsCSV extends EscritorResultado_csv {
  protected string $t = "";

  public function __construct(string $titulo, string $where) {
    parent::__construct( $this->iterador($where) );
    
    $this->t = $titulo;
  }

  abstract protected function iterador(string $where):Iterador_bd;
  
  public function devolver(ESubs $e, string $codigo = "ISO-8859-1", string $tipo = "application/vnd.ms-excel"):void {
    $t = $this->t . "_" . date("YmdHis") . ".csv";
    
    $e->pon_mime(null, $tipo, utf8_decode($this->escribe()), null, $t, $codigo);
  }
  
  public static function sanar(?string $s):string {
    if ($s == null) return "";
    
    return str_replace(["'", ";"], ["`", ".,"], $s);
  }
}
