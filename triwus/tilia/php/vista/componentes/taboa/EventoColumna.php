<?php

/** Permite engadir un evento a unha Columna dun componente Taboa.
 *
 * @package tilia\vista\componentes\taboa
 */

class EventoColumna {
/*
  Variable: $evento

  Evento esperado, p.ex. onclick, onchange ...

  > public String $evento = null;

  Esta clase permite engadir un evento personalizado en tempo de execución, no momento de creación do ObjetoJS asociado a unha cela
  dun <Componente> de tipo <Taboa>

  P.Ex.:

  >
  >
  >


*/
  public $evento = null;
/*
  Variable: $accion

  Operación javascript que debe ser executada cando aparece o evento.

  > public String $accion = null;

  Podemos usar os seguintes tipos de acción, para que se chamen ás operacións javascript
  básicas de <ObjetoJS>''s, como <envia_AJAX>, <envia_SUBMIT>, <modifica_formulario>. Os
  valores de acción predeterminados son:


  p.ex:

  > $ev = new EventoColumna("onclick", "alert(this.value)");

  (start code)

  private static function col_operacion($nome, $operacion = null) {
    $col = new Columna(new Enlace($nome, $operacion), "");

    $col->width = "99px";

    $col->pon_eventos(new EventoColumna("onclick", "envia_SUBMIT"));

    return $col;
  }

  (end)
*/
  public $accion = null;
  public $ancla_enviar = "";
  public $msx_confirmar = "";


/*
  Constructor: __construct

  Initilizes a object

  p.ex:

  > $ev = new EventoColumna("onclick", "alert(this.value)");


  Parameters:

      o String $evento - evento esperado, p.ex. onclick, onchange ...
      o String $accion - operación javascript que debe ser executada cando aparece o evento.

*/

  public function __construct($evento, $accion) {
    $this->evento = $evento;
    $this->accion = $accion;
  }

/*
  Function: pon_accion

  Engade un evento ao <ObjetoJS> pasado como paramétro.


  Parameters:

      o <ObjetoJS> $ojs.

  Return:

  <ObjetoJS>

*/

  public function pon_accion(Control $ojs) {
    switch ($this->accion) {
      case "envia_AJAX":
        $ojs->envia_AJAX($this->evento, $this->msx_confirmar);
        break;
      case "envia_SUBMIT":
        $ojs->envia_SUBMIT($this->evento, $this->msx_confirmar, $this->ancla_enviar);
        break;
      default:
        $ojs->pon_eventos($this->evento, $this->accion);
    }

    return $ojs;
  }
}

?>
