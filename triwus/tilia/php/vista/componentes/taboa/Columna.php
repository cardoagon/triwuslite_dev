<?php

/** Esta clase servirá para definir as columnas dun Componente Taboa.
 *
 * @package tilia\vista\componentes\taboa
 */

class Columna {
/*
  Variable: $ojs

  <Control> prototipo asociado a cada <Columna>.
*/
  protected $ojs = null;
/*
  Variable: $tipo_orden

  Define o tipo de orde predeterminado para a columna.

  Este valor será manexado directamente por o usuario final, cando interactúa cunha <Taboa>.

  > protected String $tipo_orden IN {'ASC', 'DESC'}
*/
  protected $tipo_orden = "ASC";
/*
  Variable: $eventos

  Array de obxetos <EventoColumna> asociados.

  > protected <EventoColumna> [] $evento = null;
*/
  protected $eventos = null;
/*
  Variable: $etiqueta

  Valor que será mostrado na cabeceira dunha <Taboa>.

  > public String $etiqueta = null.
*/
  public $etiqueta = null;
/*
  Variable: $visible

  Indica se debemos mostrar unha columna, este valor poderá ser cambiado en tempo de execución.

  > public boolean $visible = true.
*/
  public $visible = true;
/*
  Variable: $readonly

  Indica se debemos mostrar o Control asociado en modo consulta ou edición, este valor poderá ser cambiado en tempo de execución.

  > public boolean $readonly = false.
*/
  public $readonly = false;
/*
  Variable: $ordenable

  Indica se permitimos ordenar as <Fila>s dunha <Taboa>, este valor poderá ser cambiado en tempo de execución.

  > public boolean $ordenable = true.
*/
  public $ordenable = true;
/*
  Variable: $width

  Permite asignar un valor ao atributo width do elemento <td> representado.

  > public String $width = null.
*/
  public $width = null;
/*
  Variable: $align

  Permite asignar un valor ao atributo align do elemento <td> representado.

  > public String $align = "left".
*/
  public $align = "left";


/*
   Constructor: __construct

     Initializes the object.

   Parameters:

     o <ObjetoIU> $ojs = null - Asigna <ObjetoIU> prototipo asociado a cada <Columna>.
     o String $etiqueta = null - Cadea de texto a mostrar na cabeceira da columna.
     o boolean $readonly = false - Indica se debemos mostrar o <Control> asociado en modo consulta ou edición.

   See Also:

   <Fila>, <Taboa>
*/
  public function __construct(Control $ojs = null, $etiqueta = null, $readonly = false) {
    if ($ojs == null) {
      $nome_ojs = str_replace(" ", "", $etiqueta);

      if ($nome_ojs == null) die("Columna.Columna(), $nome_ojs == null");

      $ojs = new Text($nome_ojs);
    }

    $this->ojs = $ojs;
    $this->etiqueta = ($etiqueta == null)?$ojs->nome:$etiqueta;
    $this->readonly = $readonly;
  }

/*
   Function: nome

     Devolve nome asociado a unha <Columna>.

   Return:

   string
*/
  final public function nome() {
    return $this->ojs->nome;
  }

/*
   Function: sup_eventos

     Elimina os <EventoColumna>s asociados

   Return:

   void
*/
  final public function sup_eventos() {
    $this->eventos == null;
  }

/*
   Function: poner_eventos

     Engade un <EventoColumna>

   Parameters:

     o <EventoColumna> $ec

   Return:

   void
*/
  final public function pon_eventos(EventoColumna $ec) {
    $this->eventos[count($this->eventos)] = $ec;
  }

/*
   Function: obxeto

     Devolve un <Control> asociado a unha celda de posicion ($fila, $columna) dunha <Taboa>.

     Esta función será usada polo obxetos de tipo <Taboa>, e non debera ser trascendente para un programador.

   Parameters:

     o String $nometabla - nome do obxeto <Taboa>.
     o int $fila - número de fila.
     o int $columna - número de columna.

   Return:

   <Control>
*/
  public function obxeto(Taboa $t, $fila, $columna) {
    $clon = clone $this->ojs;

    $clon->nome = "celda" . Escritor_html::csubnome . $fila . Escritor_html::csubnome . $columna;
    $clon->pon_eventos("onChange", "taboa_filaActualizada(\"" . $t->nome_completo() . "\", {$fila})");

    $clon->pai = $t;

    if ($this->readonly)
      $clon->readonly = true;
    //elseif ($this->eventos == null)
    //  $clon->pon_eventos("onChange", "FilaActualizada(\"{$nometabla}\", {$fila})");
    else
      for($i = 0; $i < count($this->eventos); $i++) $this->eventos[$i]->pon_accion($clon);

    return $clon;
  }

/*
   Function: cambiar_tipo_orden

     Cambia o valor do atributo <$tipo_orden> dun obxeto <Columna>

     Esta función será usada polo obxetos de tipo <Taboa>, e non debera ser trascendente para un programador.

   Return:

   void
*/

  final public function cambiar_tipo_orden() {
    if ($this->tipo_orden == "ASC") $this->tipo_orden = "DESC";
    elseif ($this->tipo_orden == "DESC") $this->tipo_orden = "ASC";
  }

  final public function tipo_orden() {
    return $this->tipo_orden;
  }

/*
   Function: html_cabecera

     Devolve o elemento html <td> asociado á cabeceira da <Taboa>

     Esta función será usada polo obxetos de tipo <Taboa>, e non debera ser trascendente para un programador.

   Return:

   string
*/

  public function html_cabecera(Taboa $t, $columna, $ordenable, $clase_css) {
    if (!$this->visible) return "";

    $width = ($this->width != null)?" width={$this->width}":"";

    $etq = new Etiqueta("columna@{$columna}", $this->etiqueta);
    if ($ordenable && $this->ordenable) {
      $etq->envia_SUBMIT("onClick", "", "");
      $etq->style("default", "cursor: pointer;");
      $etq->title = "Ordenar({$this->etiqueta}, {$this->tipo_orden})";
    }

    $etq->pai = $t;

    return
      "<th class='{$clase_css}' {$width}>" . $etq->html() . "</th>\n";
  }
}

?>
