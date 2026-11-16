<?php

/** Devolve unha consulta de BD en formato HTML.
 *
 * @package tilia\modelo\escritorResultado
 */


class EscritorResultado_html extends EscritorResultado {
  public $align        = "left";
  public $width        = null;
  public $border       = 0;
  public $cellspacing  = 0;
  public $cellpadding  = 3;
                       
  public $class_table  = "";
  public $style_table  = "border: #333333 1px solid; margin:3px; font-family: sans-serif; font-size: 11px; font-weight: normal;";
  public $style_cab    = "font-weight: bold; background-color: #FFB366;";
  public $style_tr     = "background-color: #FFFFFF;";
  public $style_tr_alt = "background-color: #FFD9B2;";

  public function __construct(Iterador_bd $rbd = null) {
    parent::__construct($rbd);
  }

  protected function inicia_saida() {
    $width = ($this->width       == null)?"":" width={$this->width}";
    $class = ($this->class_table == null)?"":" class='{$this->class_table}'";
    $style = ($this->style_table == null)?"":" style='{$this->style_table}'";

    return "
      <table{$width}{$class} align={$this->align} border={$this->border} cellpadding={$this->cellpadding} cellspacing={$this->cellspacing}{$style}>\n";
  }

  protected function finaliza_saida() {
    return "
      </table>";
  }

  protected function cabeceira($df = null) {
    if ($df == null    ) return "";
    if (!is_array($df) ) return "";
    if (count($df) == 0) return "";


    $s = "
      <tr style=\"{$this->style_cab}\">";

    foreach ($df as $k_dc=>$dc) {
      $k_dc = ucfirst($k_dc);

      switch ($dc['tipo']) {
        case "cadena":
          $s .= "
          <td align=left>{$k_dc}</td>";
          break;
        case "numero":
        case "fecha":
          $s .= "
          <td align=center>{$k_dc}</td>";
      }
    }
    $s .= "
      </tr>";

    return $s;
  }

  protected function salto_paxina() {
    return "\n<span style='page-break-before: always; '></span>\n";
  }

  protected function linha_detalle($df, $f) {
    $estilo = (($this->ct_lineas % 2) == 0)?$this->style_tr:$this->style_tr_alt;

    $s = "
      <tr style=\"{$estilo}\">";
    foreach ($f as $k_dato=>$dato)
      if (!is_numeric($k_dato)) $s .= self::formato_dato_html($dato, $df[$k_dato]['tipo']);

    $s .= "
      </tr>";

    return $s;
  }

  protected static function formato_dato_html($valor, $tipo) {
    if ($valor == "") $valor = "null";

    //**  tipo IN {"cadena", "numero", "fecha"}
    switch ($tipo) {
      case "cadena":
        return "
          <td align=left>
            {$valor}
          </td>";
      case "numero":
        return "
          <td align=right>
            {$valor}
          </td>";
      case "fecha":
        return "
          <td align=center>
            {$valor}
          </td>";
      default:
        return "
          <td align=center>
            {$valor}
          </td>";
    }
  }
}


/** Devolve unha consulta de BD en formato HTML grid.
 *
 * @package tilia\modelo\escritorResultado
 */

class ER_grid extends EscritorResultado_html {

  public function __construct(Iterador_bd $rbd = null) {
    parent::__construct($rbd);
  }

  protected function inicia_saida() {
    $class = ($this->class_table == null)?"":" class='{$this->class_table}'";
    $style = ($this->style_table == null)?"":" style='{$this->style_table}'";

    return "
      <div{$class}{$style}>\n";
  }

  protected function finaliza_saida() {
    return "
      </div>";
  }

  protected function linha_detalle($df, $f) {
    $estilo = (($this->ct_lineas % 2) == 0)?$this->style_tr:$this->style_tr_alt;

    $s = "
      <div style=\"{$estilo}\">";
    foreach ($f as $k_dato=>$dato)
      if (!is_numeric($k_dato)) $s .= self::formato_dato_html($dato, $df[$k_dato]['tipo']);

    $s .= "
      </div>";

    return $s;
  }
}
