<?php

/** Componente Tilia Framework, que permite definir táboas de datos editables.
 *
 * @package tilia\vista\componentes\taboa
 */

class Taboa extends Componente {
/*
  Variable: $ordenable

 Habilita a ordenación alfabética dos datos da táboa por calquera das columnas.

  > public boolean $ordenable = true;
*/
  public $ordenable = true;
/*
  Variable: $hai_bmais

 Habilita a inserción filas na táboa.

  > public boolean $hai_bmais = true;
*/
  public $hai_bmais = true;
/*
  Variable: $hai_bmenos

 Habilita a eliminación filas na táboa.

  > public boolean $hai_bmenos = true;
*/
  public $hai_bmenos = true;
/*
  Variable: $hai_check

 Establece se mostramos un columna de tipo check.

  > public boolean $hai_check = false;
*/
  public $hai_check = false;
/*
  Variable: $check_multiple

 Cando está habilitada a columna de Checker's, indicamos que dita función actuará como un radio button.

  > public boolean $hai_check = true;
*/
  public $check_multiple = true;
/*
  Variable: $ultimo_check

 índice da última fila chequeada, so ten sentido si $check_multiple == false.

  > protected int ultimo_check = -1;
*/
  protected $ultimo_check = -1;
/*
  Variable: $hai_cab

 Establece se debe mostrar a cabeceira da táboa

  > public boolean $check_multiple = true;
*/
  public $hai_cab = true;
/*
  Variable: $hai_pe

 Establece se debe mostrar o pe da táboa

  > public boolean $hai_pe = true;
*/
  public $hai_pe = true;
/*
  Variable: $filas

 Array numérico de <Fila>s da <Taboa>.

  > protected Fila[] $filas = null.
*/
  protected $filas = null;
/*
  Variable: $columnas

 Array numérico de <Columna>s da <Taboa>.

  > protected Columna[] $columnas = null.
*/
  protected $columnas = null;

/*
  Variable: $columna_orden

 Almacena a columna pola cal están ordenadas as filas da táboa.

  > protected int $columna_orden = -1.
*/
  protected $columna_orden = -1;

/*
  Variable: $filas_paxina

 Establece o número de filas por páxina. Cando $filas_paxina == -1, se mostrarán tódals filas nunha única páxina.

  > protected int $filas_paxina = -1.
*/
  public $filas_paxina = -1;
/*
  Variable: $paxina

 Almacena a páxina que se está mostrando actualmente.

  > protected int $paxina = 0.
*/
  protected $paxina = 0;
/*
  Variable: $ct_pag

 Almacena o número total de páxinas. Este valor calcúlase dinámicamente en función de *count($filas)* e *$filas_paxina*.

  > protected int $ct_pag = null.
*/
  protected $ct_pag = null;

/*
   Constructor: __construct

     Initializes the object.

   Parameters:

     o String $nome - Nome da táboa.
     o <Columna> [] $columnas - Array numérico de columnas da táboa.
     o int $filas_paxina = -1 - indica o número máximo de filas que serán mostradas en cada páxina da táboa.

   See Also:

   <Fila>, <Columna>
*/
  public function __construct($nome, $columnas, $filas_paxina = -1) {
    parent::__construct($nome);

    $this->columnas = $columnas;
    $this->filas_paxina = $filas_paxina;

    Taboa_html::inicia_clasesCSS($this);
  }

/*
   Function: numColumnas

     Devolve o número total de columnas da táboa

   Return:

   int
*/
  public function numColumnas() {
    return count($this->columnas);
  }

/*
   Function: numFilas

     Devolve o número total de filas da táboa

   Return:

   int
*/
  public function numFilas() {
    return count($this->filas);
  }

/*
  Function: readonly

  Permite establecer o atributo *sololectura* dun componente .

  Parameters:

      o boolean $readonly = true

  Return:
    void
*/

  public function readonly($b = true) {
    parent::readonly($b);

    if ($this->numColumnas() == 0) return;

    foreach($this->columnas as $c) $c->readonly = $b;
  }

/*
   Function: columna

     Devolve a <Columna> da posición $i, ou o array <Columna> []

  Parameters:

      o int $i = null

   Return:
   o ($i === null) => <Columna> []
   o ($i !== null) => <Columna>

 (start code)
  public function columna($i = null) {
    if ($i === null) return $this->columnas;

    return $this->columnas[$i];
  }
 (end)
*/
  public function columna($i = null) {
    if ($i === null) return $this->columnas;

    return $this->columnas[$i];
  }

/*
   Function: fila

     Devolve a <Fila> da posición $i, ou o array <Fila> []

  Parameters:

      o int $i = null

   Return:
   o ($i === null) => <Fila> []
   o ($i !== null) => <Fila>

 (start code)
  public function fila($i = null) {
    if ($i === null) return $this->filas;

    return $this->filas[$i];
  }
 (end)
*/
  public function fila($i = null) {
    if ($i === null) return $this->filas;

    return $this->filas[$i];
  }

/*
   Function: colleFilas

     Devolve a matriz de datos da táboa.

   Return:

   mixed[][]
*/
  final public function colleFilas() {
    return Taboa_loxica::coller_filas($this, null, false);
  }

/*
   Function: colleFilas_selecionadas

     Devolve a matriz de datos da táboa, que veñen de filas que foron selecionadas por un usuario.

     O primeiro índice da matriz de datos devolta é a posición de cada fila dentro da táboa.

   Parameters:

     o boolean $inversa = false - Cando $inversa == true, devolve as filas que non selecionadas.

   Return:

   mixed[][]
*/
  final public function colleFilas_selecionadas($inversa = false) {
    return Taboa_loxica::coller_filas($this, "S", $inversa);
  }

/*
   Function: colleFilas_novas

     Devolve a matriz de datos da táboa, que veñen de filas novas engadidas por un usuario.

     O primeiro índice da matriz de datos devolta é a posición de cada fila dentro da táboa.

   Parameters:

     o boolean $inversa = false - Cando $inversa == true, devolve as filas que non son novas.

   Return:

   mixed[][]
*/
  final public function colleFilas_novas($inversa = false) {
    return Taboa_loxica::coller_filas($this, "NA", $inversa);
  }

/*
   Function: colleFilas_actualizadas

     Devolve a matriz de datos da táboa, que veñen de filas que foron selecionadas por un usuario.

     O primeiro índice da matriz de datos devolta é a posición de cada fila dentro da táboa.

   Parameters:

     o boolean $inversa = false - Cando $inversa == true, devolve as filas que non foron selecionadas.

   Return:

   mixed[][]
*/
  final public function colleFilas_actualizadas($inversa = false) {
    return Taboa_loxica::coller_filas($this, "A", $inversa);
  }

/*
   Function: colleFilas_borradas

     Devolve a matriz de datos da táboa, que veñen de filas que foron borradas por un usuario.

     O primeiro índice da matriz de datos devolta é a posición de cada fila dentro da táboa.

   Parameters:

     o boolean $inversa = false - Cando $inversa == true, devolve as filas que non foron booradas.

   Return:

   mixed[][]
*/
  final public function colleFilas_borradas($inversa = false) {
    return Taboa_loxica::coller_filas($this, "B", $inversa);
  }

/*
   Function: pon_rbd

     Permite engadir un <Resultado_bd> como filas da táboa.

     Engade as tuplas do <Resultado_bd> cando os nomes de campos de tuplas coincidan cos nomes das columnas asociadas á táboa.

   Parameters:

     o <Resultado_bd> $r_bd.

   Return:

   void
*/
  public function pon_rbd(Resultado_bd $r_bd) {
    $this->filas = null;

    $this->ultimo_check = -1;

    $this->paxina = 0;

    for($i = 0; $fdatos = $r_bd->next(); $i++) $this->pon_fila($i, $fdatos);
  }

/*
   Function: pon_fila

     Actualiza ou engade unha nova <Fila>, cos datos do array asociativo *$fdatos*

     Engade os elementos do array asociativo cuxos nomes de clave coinciden cos nomes de columnas asociadas á táboa.

   Parameters:

     o int $posicion = -1. cando *$posicion* == -1, entón engade unha fila ao final da táboa.
     o mixed $fdatos = null.

   Return:

   void
*/
  public function pon_fila($posicion = -1, $fdatos = null) {
    if ($this->numColumnas() == 0) return;

    $nf = $this->numFilas();

    if ($posicion == -1) $posicion = $nf;

    for ($i = $nf; $i > $posicion; $i--) {
      $this->filas[$i] = $this->filas[$i - 1];

      if ((!$this->check_multiple) && ($this->filas[$i]->checked)) $this->ultimo_check = $i;
    }

    $f = new Fila();
    foreach($this->columnas as $c) $f->pon_dato($fdatos[$c->nome()]);

    $this->filas[$posicion] = $f;

    if ($fdatos == null) $this->filas[$posicion]->estado = "N";
  }

/*
   Function: sup_fila

     Elimina a <Fila> da tabla da posicion *$posicion*.

     Non se permite a eliminacion de filas cando count($filas) == 1.

   Parameters:

     o int $posicion. Posición na táboa da fila a eliminar.

   Return:

   void
*/
  final public function sup_fila($posicion) {
    if ($this->numFilas() < 1) return;
    if ($posicion < 0) return;

    $fin = $this->numFilas() - 1;

    if ((!$this->check_multiple) && ($this->filas[$posicion]->checked)) $this->ultimo_check = -1;

    for($i = $posicion; $i < $fin; $i++) {
      $this->filas[$i] = $this->filas[$i + 1];

      if ((!$this->check_multiple) && ($this->filas[$i]->checked)) $this->ultimo_check = $i;
    }

    unset($this->filas[$fin]);
  }

/*
 Function: declara_css

 Declara o(s) arquivo(s) CSS asociados a <Componente> de tipo <Taboa>.

  Return:
    o String[]
    o null - podemos devolver *null* no caso de que non sexa necesario vincular arquivos CSS.

 Exemplo:

(start code)
  public function declara_css() {
    $a = parent::declara_css();

    $a[] = array(Tilia::home() . "css/taboa.css");

    return $a;
  }
(end)
*/

  public function declara_css() {
    $a = parent::declara_css();

    $a[] = Tilia::home() . "css/taboa.css";

    return $a;
  }

/*
 Function: declara_js

 Declara o(s) arquivo(s) JS asociados a <Componente> de tipo <Taboa>.

  Return:
    o String[]
    o null - podemos devolver *null* no caso de que non sexa necesario vincular arquivos JS.

 Exemplo:

(start code)
  public function declara_js() {
    $a = parent::declara_js();

    $a[] = array(Tilia::home() . "js/taboa.js");

    return $a;
  }
(end)
*/
  public function declara_js() {
    $a = parent::declara_js();

    $a[] = Tilia::home() . "js/taboa.js";

    return $a;
  }

/*
 Function: html

 Debvolve a representación HTML da <Taboa>.

  Return:
    String.
*/
  public function html():string {
    return Taboa_html::taboa($this);
  }

/*
  Function: post

  Recolle a información envía por cliente. Redefine ou sobrescribe o método <post> da clase superior <Componente>.

  Parameters:

      o $post - array asociativo similar á variable global de PHP $_REQUEST

  Return:
    void

  See Also:

   <post>
*/
  public function post($post) {
    if (!is_array($post)) return;

    foreach($post as $k=>$v) {
      //* Comprobamos se é un 'post' dalgún ObxetoFormulario asociado a este componente Taboa.
      $a_k = explode(self::cnome, $k);
      if (count($a_k) > 1) {
        if ($this->obxeto($a_k[0]) != null) $this->obxeto($a_k[0])->post(array($a_k[1]=>$v));

        continue;
      }

      //* Supoñemos e probamos se é un 'post' propio do componente Taboa.
      list($k0, $i, $j) = explode(self::csubnome, $k);

      if ($k0 == "celda") $this->filas[$i]->celdas[$j] = $v;
      elseif ($k0 == "ctpag") $this->ct_pag = $v;
      elseif ($k0 == "estado") $this->filas[$i]->estado = $v;
      elseif ($k0 == "estado") $this->filas[$i]->estado = $v;
      elseif ($k0 == "check") {
        $this->filas[$i]->checked = $v;

        if ($this->check_multiple) continue;

        if ($v == 0) {
          if ($this->ultimo_check == $i) $this->ultimo_check = -1;

          continue;
        }

        if ($this->ultimo_check == $i) continue;

        if (($this->ultimo_check != -1) && ($this->numFilas() > 1)) $this->filas[$this->ultimo_check]->checked = false;

        $this->ultimo_check = $i;
      }
    }
  }

/*
 Function: operacion

   Executa os eventos asociados a unha instancia dunha <Taboa>

   Parameters:

     o <EstadoHTTP> $e - Estado que conten a esta instancia dunha <Taboa>.

  Return:

  o <EstadoHTTP>
  o null, se o evento asociado ao prametro *$e*, non se corresponde con esta obxeto, ou instancia da clase <Calendario>.

*/
  public function operacion(EstadoHTTP $e) {
    $evento = $e->evento();

    $coor = $evento->subnome();

    if ($evento->control_str($this->nome_completo() . self::cnome . "columna") == 1) {
        $this->columna_orden = $coor[0];
        Taboa_loxica::ordenar($this);
        $this->columnas[$this->columna_orden]->cambiar_tipo_orden();

        return $e;
    }

    if ($evento->control_str($this->nome_completo() . self::cnome . "bini") == 1) {
        Taboa_loxica::cambia_paxina($this, 0);

        return $e;
    }

    if ($evento->control_str($this->nome_completo() . self::cnome . "bant") == 1) {
        Taboa_loxica::cambia_paxina($this, $this->paxina - 1);

        return $e;
    }

    if ($evento->control_str($this->nome_completo() . self::cnome . "bsig") == 1) {
        Taboa_loxica::cambia_paxina($this, $this->paxina + 1);

        return $e;
    }

    if ($evento->control_str($this->nome_completo() . self::cnome . "bfin") == 1) {
        Taboa_loxica::cambia_paxina($this, Taboa_loxica::num_paxinas($this) - 1);


        return $e;
    }

    if ($evento->control_str($this->nome_completo() . self::cnome . "ctpag") == 1) {
        Taboa_loxica::cambia_paxina($this);

        return $e;
    }

    if ($evento->control_str($this->nome_completo() . self::cnome . "bmas") == 1) {
        $this->pon_fila($coor[0]);

        return $e;
    }

    if ($evento->control_str($this->nome_completo() . self::cnome . "bmenos") == 1) {
        if (($this->filas[$coor[0]]->estado == "NA") || ($this->filas[$coor[0]]->estado == "N")) {
          if ($this->numFilas() > 1) $this->sup_fila($coor[0]);
        }
        elseif ($this->filas[$coor[0]]->estado == "A")
          $this->filas[$coor[0]]->estado = "BA";
        else
          $this->filas[$coor[0]]->estado = "B";

        return $e;
    }

    if ($evento->control_str($this->nome_completo() . self::cnome . "brec") == 1) {
        if ($this->filas[$coor[0]]->estado == "B")
          $this->filas[$coor[0]]->estado = "";
        elseif ($this->filas[$coor[0]]->estado == "BA")
          $this->filas[$coor[0]]->estado = "A";

        return $e;
    }

    return null;
  }
}

/**
 *
 * @package tilia\vista\componentes\taboa
 */


final class Taboa_loxica extends Taboa {
  protected static function cambia_paxina(Taboa $t, $paxina = -1) {
    if ($paxina == -1) {
      list($p_nova, $total_p) = explode("@", $t->ct_pag);

      $paxina = $p_nova - 1;
    }

    if (($paxina < 0) || ($paxina >= Taboa_loxica::num_paxinas($t))) return;

    $t->paxina = $paxina;
  }

  protected static function ordenar(Taboa $t) {
    if (($nf = $t->numFilas()) == 0) return;

    $array_ordenar = null;
    for($i = 0; $i < $nf; $i++) $array_ordenar[$i] = $t->filas[$i]->celdas[$t->columna_orden];

    if ($t->columnas[$t->columna_orden]->tipo_orden() == "ASC") asort($array_ordenar);
    elseif ($t->columnas[$t->columna_orden]->tipo_orden() == "DESC") arsort($array_ordenar);

    $filas_aux = null;
    $i = 0;
    foreach($array_ordenar as $k=>$v) {
      $filas_aux[$i] = $t->filas[$k];

      if ((!$t->check_multiple) && ($filas_aux[$i]->checked)) $t->ultimo_check = $i;

      $i++;
    }

    $t->filas = $filas_aux;
  }

  public static function num_paxinas(Taboa $t) {
    $np = (double)($t->numFilas() / $t->filas_paxina);

    if ((int)$np < $np) $np = $np + 1;

    return (int)$np;
  }

  public static function ini_paxina(Taboa $t) {
    if ($t->filas_paxina == -1) return 0;

    return $t->paxina * $t->filas_paxina;
  }

  public static function fin_paxina(Taboa $t) {
    $nf = $t->numFilas();

    if ($t->filas_paxina == -1) return $nf;

    $fp = ($t->paxina * $t->filas_paxina);

    if (($t->paxina + 1) == self::num_paxinas($t)) {
      if (($mod_fp = $nf % $t->filas_paxina) == 0)
        return $fp + $t->filas_paxina;
      else
        return $fp + $mod_fp;
    }
    else
      return $fp + $t->filas_paxina;
  }

  protected static function coller_filas(Taboa $t, $estado, $inversa) {
    $nf = $t->numFilas();
    $nc = $t->numColumnas();

    $f = null;
    for($i = 0; $i < $nf; $i++) {
      $estado_f = $t->filas[$i]->estado;
      if ($estado == null) $seleccionada = true;
      elseif ($estado == "B") $seleccionada = ("BA" == $estado_f) || ("B" == $estado_f);
      elseif ($estado == "S") $seleccionada = $t->filas[$i]->checked;
      else $seleccionada = $estado == $estado_f;

      if ($inversa) $seleccionada = !$seleccionada;

      if ($seleccionada) {
        $fdatos = null;
        for ($j = 0; $j < $nc; $j++)
          $fdatos[$t->columnas[$j]->nome()] = $t->filas[$i]->celdas[$j];

        $f[$i] = $fdatos;
      }
    }

    return $f;
  }
}

/**
 *
 * @package tilia\vista\componentes\taboa
 */

final class Taboa_html extends Taboa {
  const css_taboa = "taboa";
  const css_cabeceira = "taboa_cab";
  const css_th = "taboa_th";
  const css_fila = "taboa_linea";
  const css_fila_alt = "taboa_linea_alt";
  const css_fila_nova = "taboa_linea_nova";
  const css_fila_check = "taboa_linea_check";
  const css_pe = "taboa_pe";
  const css_boton_fila = "boton";

  protected static function inicia_clasesCSS(Taboa $t) {
    $t->clase_css("taboa", self::css_taboa);
    $t->clase_css("cabeceira", self::css_cabeceira);
    $t->clase_css("th", self::css_th);
    $t->clase_css("fila", self::css_fila);
    $t->clase_css("fila_alt", self::css_fila_alt);
    $t->clase_css("fila_nova", self::css_fila_nova);
    $t->clase_css("fila_check", self::css_fila_check);
    $t->clase_css("pe", self::css_pe);
    $t->clase_css("boton_fila", self::css_boton_fila);
  }

  protected static function taboa(Taboa $t) {
    if (!$t->visible) return "";

    return "
        <table border=0 cellspacing=0 cellpadding=2 class='" . $t->clase_css("taboa") . "'>
          " . Taboa_html::cabeceira($t) . "
          " . Taboa_html::detalle($t) . "
          " . Taboa_html::pe($t) . "
        </table>";
  }

  protected static function cabeceira(Taboa $t) {
    $str_c = self::oculto_ultimoCheck($t);

    if (!$t->hai_cab) return $str_c;

    $ct_c = $t->numColumnas();

    $str_c .= "
      <tr class='" . $t->clase_css("cabeceira") . "'>
        <th class='" . $t->clase_css("th") . "' width=0px></th>
        <th class='" . $t->clase_css("th") . "' width=0px></th>
        <th class='" . $t->clase_css("th") . "' width=0px></th>";

    for($j = 0; $j < $ct_c; $j++)
      $str_c .= $t->columnas[$j]->html_cabecera($t, $j, $t->ordenable, $t->clase_css("th"));

    $str_c .= "
      </tr>";

    return $str_c;
  }

  protected static function detalle(Taboa $t) {
    if ($t->numFilas() == 0) return "";

    $ip = Taboa_loxica::ini_paxina($t);
    $fp = Taboa_loxica::fin_paxina($t);

    for($i = $ip; $i < $fp; $i++) $str_f .= $t->filas[$i]->html($t, $i, ($ip % 2));

    return $str_f;
  }

  protected static function pe(Taboa $t) {
    if (!$t->hai_pe) return "";

    $ancla_pe = $t->nome_completo() . "ape";

    $str_botonera = "";
    $str_ct = "";
    if (($t->numFilas($t) > 0) && ($t->filas_paxina != -1)) {
      $str_botonera .= self::botonPe($t, $ancla_pe, "bini", "go-first.png");;
      $str_botonera .= self::botonPe($t, $ancla_pe, "bant", "go-previous.png");
      $str_botonera .= self::botonPe($t, $ancla_pe, "bsig", "go-next.png");
      $str_botonera .= self::botonPe($t, $ancla_pe, "bfin", "go-last.png");

      $str_ct = self::ctPe($t, $ancla_pe);
    }

    return "
      <tr>
        <td colspan = " . (3 + $t->numColumnas()) . ">
          <table width='100%' border=0 cellspacing=0 cellpadding=2 class='" . $t->clase_css("taboa") . "'>
            <tr>
              <td>
                {$str_botonera}
                <a name='{$ancla_pe}'/a>
              </td>
              <td align=right>
                {$str_ct}" . self::etqPe($t) . "
              </td>
            </tr>
          </table>
        </td>
      </tr>";
  }

  private static function oculto_ultimoCheck(Taboa $t) {
    if ($t->check_multiple) return "";

    $o = new Hidden("ultimoCheck", $t->ultimo_check);

    $o->pai = $t;

    return $o->html();
  }

  private static function etqPe(Taboa $t) {
    $etq = new Etiqueta("totalfilas", "&nbsp;" . $t->numFilas() . "&nbsp;&nbsp;");

    $etq->pai = $t;

    return "<span>" . $etq->html() . "</span>";
  }

  private static function ctPe(Taboa $t, $ancla_pe) {
    $ct = new Text("ctpag");

    $ct->size = 11;
    $ct->maxlength = 50;

    $ct->clase_css("default", "number");
    $ct->style("default", "width: 100px; height: 20px; font-size:12px; ");

    $ct->valor = ($t->paxina + 1) . "/" . Taboa_loxica::num_paxinas($t);

    $ct->envia_SUBMIT("onChange", "", $ancla_pe);

    $ct->pai = $t;

    return "<span>" . $ct->html() . "</span>";
  }

  private static function botonPe(Taboa $t, $ancla_pe, $nome, $etq) {
    $carpeta = Tilia::home() . "imaxes/taboa";

    $b = new Image($nome, "{$carpeta}/{$etq}", false);

    $b->style("default", "cursor: pointer; ");

    $b->envia_SUBMIT("onClick", "", $ancla_pe);

    $b->pai = $t;

    return "<span>" . $b->html() . "</span>";
  }
}

?>
