<?php

/** Devolve unha consulta de BD en formato XML.
 *
 * @package tilia\modelo\escritorResultado
 */


class EscritorResultado_xml extends EscritorResultado {
/*
  Variable: $nome_raiz

    String $nome_raiz - Nome da etiqueta raíz do arquivo XML devolto.
*/
  public $nome_raiz = null;
/*
  Variable: $nome_rexistro

    String $nome_rexistro - Nome do elemento XML que caontén cada unha das tuplas devoltas pola consulta SQL.
*/
  public $nome_rexistro = null;

/*
  Variable: $version

    String $version = "1.0" -  Describe o tipo de versión de arquivo XML.
*/
  public $version = "1.0";
/*
  Variable: $encoding:

  String $encoding = "iso-8859-1" - Conxunto de caracteres do arquivo XML.
*/
  public $encoding = "iso-8859-1";

  public function __construct($nome_raiz, $nome_rexistro, Iterador_bd $rbd = null) {
    parent::__construct($rbd);

    $this->nome_raiz = $nome_raiz;
    $this->nome_rexistro = $nome_rexistro;
  }

  protected function inicia_saida() {
    return "<?xml version='{$this->version}' encoding='{$this->encoding}'?>";
  }

  protected function finaliza_saida() {
    return "</{$this->nome_raiz}>";
  }

  protected function cabeceira($df = null) {
    return  "\n<{$this->nome_raiz}>\n";
  }

  protected function linha_detalle($df, $f) {
    $s = "";
    foreach ($f as $k_dato=>$dato) {
      if (is_numeric($k_dato)) continue;

      if ($dato == null) $s .= "<{$k_dato}></{$k_dato}>\n";
      elseif ($df[$k_dato]['tipo'] == "cadena") $s .= "<{$k_dato}><![CDATA[{$dato}]]></{$k_dato}>\n";
      else $s .= "<{$k_dato}>{$dato}</{$k_dato}>\n";
    }

    return "<{$this->nome_rexistro}>\n{$s}</{$this->nome_rexistro}>\n";
  }
}
