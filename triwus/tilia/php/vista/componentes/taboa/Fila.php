<?php

/** Esta clase será usada por un Componente Taboa, para almacenar información de cada fila.
 *
 * @package tilia\vista\componentes\taboa
 */

class Fila {
/*
  Variable: $celdas

  Array de valores que representa unha fila tupla.

  > public String[] celda
*/
  public $celdas = null;
/*
  Variable: $checked

  Indica se unha fila foi selecionada por un usuario.

  > public boolean $checked = false;
*/
  public $checked = false;
/*
  Variable: $estado

  Representa o estado de actualización de cada fila.

  > public String estado IN {"", "S", "N", "NA", "B", "BA", "A"}

*/
  public $estado = null;
/*
   Constructor: __construct

     Initializes the object.

   See Also:

   <Fila>, <Taboa>
*/
  public function __construct() {}

/*
   Function: pon_dato

  Permite engadir un dato *$d*, á celda de posición *$posicion*. Se omitimos o parámetro *$posición*, entón engade unha nova celda á <Fila>.


   Parameters:

     o mixed $d.
     o int $posicion = -1.

   Return:

   void
*/
  final public function pon_dato($d, $posicion = -1) {
    if ($posicion == -1) $posicion = count($this->celdas);

    $this->celdas[$posicion] = $d;
  }

  public function html(Taboa $t, $i, $i_cor_fila) {
    $ancla_fila = $t->nome_completo() . "_af{$i}";

    $columnas = $t->columna();

    if (($this->estado == "B") || ($this->estado == "BA"))
      $str_f .= $this->html_brec($t, $i, $ancla_fila);
    else {
      $str_f .= $this->html_bmais($t, $i, $ancla_fila);
      $str_f .= $this->html_bmenos($t, $i, $ancla_fila);
      $str_f .= $this->html_check($t, $i);
    }

    $ct_c = count($this->celdas);
    for($j = 0; $j < $ct_c; $j++)
      $str_f .= $this->html_celda($t, $columnas[$j], $i, $j);

    $str_f .= $this->html_estado($i, $t);


    $css = (($i % 2) == $i_cor_fila)?$t->clase_css("fila"):$t->clase_css("fila_alt");
    if (($this->estado == "N") || ($this->estado == "NA")) $css = $t->clase_css("fila_nova");
    elseif ($this->checked) $css = $t->clase_css("fila_check");

    return "
      <tr class='{$css}'>
        {$str_f}
      </tr>";
  }

  final public function html_estado($numFila, Taboa $t)  {
    $o = new Hidden("estado" . Escritor_html::csubnome . "{$numFila}");

    $o->post($this->estado);
    $o->pai = $t;

    return $o->html();
  }

  protected function html_celda(Taboa $t, Columna $c, $i, $j) {
    if (!$c->visible) return "";

    $ojs_celda = $c->obxeto($t, $i, $j);

    $ojs_celda->post($this->celdas[$j]);

    if (($this->estado == "B") || ($this->estado == "BA")) {
      $ojs_celda->readonly = true;
      $ojs_celda->style("readonly", "text-decoration: line-through;");
    }
    elseif (($this->estado == "N") || ($this->estado == "NA"))
      $ojs_celda->readonly = $c->readonly;

    $width = ($c->width != null)?" width={$c->width}":"";

    return "
      <td align={$c->align}{$width}>" . $ojs_celda->html() . "</td>";
  }

  protected function html_bmais(Taboa $t, $numFila, $ancla_fila)  {
    if (!$t->hai_bmais) return "<td></td>";

    $subnome = Escritor_html::csubnome . $numFila;

    $b = self::boton("bmas{$subnome}", $ancla_fila, "list-add.png", "list-add.png", "list-add.png", $t->readonly);

    $b->pai = $t;

    return "<td align=center>" . $b->html() . "</td>";
  }

  protected function html_bmenos(Taboa $t, $numFila, $ancla_fila)  {
    if (!$t->hai_bmenos) return "<td></td>";

    $subnome = Escritor_html::csubnome . $numFila;

    $b = self::boton("bmenos{$subnome}", $ancla_fila, "list-remove.png", "list-remove.png", "list-remove.png", $t->readonly);

    $b->pai = $t;

    $b->readonly = $t->readonly;

    return "
      <td align=center>" . $b->html() . "</td>";
  }

  protected function html_brec(Taboa $t, $numFila, $ancla_fila)  {
    $subnome = Escritor_html::csubnome . $numFila;

    $b = self::boton("brec{$subnome}", $ancla_fila, "system-restart-panel.png", "system-restart-panel.png", "system-restart-panel.png", $t->readonly);

    $b->pai = $t;

    return "
      <td></td><td align=center>" . $b->html() . "</td><td></td>";
  }

  protected function html_check(Taboa $t, $numFila)  {
    if (!$t->hai_check) return "<td width='16px'>&nbsp;</td>";

    $ch = self::check($t, $numFila, $this->checked);

    $ch->pai = $t;


    return "
      <td align=center>" . $ch->html() . "</td>";
  }

  final protected static function boton($nome, $ancla_fila, $src, $src_over, $src_click, $readonly)  {
    $carpeta = Tilia::home() . "imaxes/taboa";

    $b = new Image($nome, "{$carpeta}/{$src}", false);

    $b->ancla = $ancla_fila;

    $b->readonly = $readonly;

    //$b->pon_src("{$carpeta}/{$src}", "{$carpeta}/{$src_click}", "{$carpeta}/{$src_over}", "{$carpeta}/{$src}");

    $b->style("default", "cursor: pointer;");

    $b->envia_SUBMIT("onClick");

    return $b;
  }

  final protected static function check(Taboa $t, $numFila, $checked) {
    $subnome = Escritor_html::csubnome . $numFila;

    $ch = new Checkbox("check{$subnome}", $checked);

    $ch->pon_eventos("onchange", "taboa_check('" . $t->nome_completo() . "', {$numFila})");

    $ch->readonly = $t->readonly;

    return $ch;
  }
}

?>
