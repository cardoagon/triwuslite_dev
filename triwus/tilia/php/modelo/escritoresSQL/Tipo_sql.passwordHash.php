<?php

/** Serve para definir e unificar os diferentes tipos de datos SQL de diferentes SXBD.
 *
 * @package tilia\modelo\escritoresSQL\tipo
 */

abstract class Tipo_sql {
  protected function __construct() {}

  public function strsqlselect(Campo_dbd $c, Sintaxis_sql $s) {
    return "{$c->tbd->nome}.{$c->nome} as {$c->alias}";
  }

  public function strsqlinsertupdate(Campo_dbd $c, Sintaxis_sql $s) {
    return $c->nome;
  }

  abstract public function ini_atr(Obxeto_bd $obd, Campo_dbd $c, $valor = null);
  abstract public function formato_sql($valor, Sintaxis_sql $s);
}


/** Tipo de dato SQL: VarChar.
 *
 * @package tilia\modelo\escritoresSQL\tipo
 */

final class VarChar extends Tipo_sql {
  public $base64 = false;
  public $trim = false;

  public function __construct($base64 = false) {
    parent::__construct();

    $this->base64 = $base64;
  }

  public function ini_atr(Obxeto_bd $obd, Campo_dbd $c, $valor = null) {
    return new Atrobd_varchar($obd, $c, $valor);
  }

  public function formato_sql($valor, Sintaxis_sql $s) {
    if ($valor == "") return "null";

    if ($this->trim) $valor = trim($valor);

    $valor = ($this->base64)?base64_encode($valor):str_replace("'", "\'", $valor);

    return "'{$valor}'";
  }
}


/** Tipo de dato SQL: Psswd.
 *
 * @package tilia\modelo\escritoresSQL\tipo
 */

final class Psswd extends Tipo_sql {
  public $cifrar = true;

  public function __construct($cifrar = true) {
    parent::__construct();

    $this->cifrar = $cifrar;
  }

  public function ini_atr(Obxeto_bd $obd, Campo_dbd $c, $valor = null) {
    return new Atrobd_psswd($obd, $c, $valor);
  }

  public function formato_sql($valor, Sintaxis_sql $s) {
    if ($valor == "") return "null";

    $valor = str_replace("'", "\"", $valor);

    if ($this->cifrar) $valor = self::__crypt($valor);

    return "'{$valor}'";
  }

  public static function __crypt($k) {
    if ($k == null) die("Psswd.__crypt(), k == null");
    
    $kc = password_hash($k, PASSWORD_DEFAULT);

    return $kc;
  }

  public static function __analiza($k) {
    /*
     * Analiza un contrasinal $k:
     *
     * (pex.) RETURN
     *
     *    Array (
     *           [k]         => 1_contrasinal
     *           [l]         => 13
     *           [alfabetos] => 3
     *           [entropia]  => 61.57
     *          )
     *
     */


    if (($ct = strlen($k)) == 0) return array("k"=>"", "l"=>0, "alfabetos"=>0, "entropia"=>0);

    // Conta maiusculas, minusculas, numeros e simbolos
    $M = 0; $m = 0; $n = 0; $o = 0;

    for ($i = 0; $i < $ct; $i++)
      if     (preg_match('/^[[:upper:]]$/', $k[$i])) $M++;
      elseif (preg_match('/^[[:lower:]]$/', $k[$i])) $m++;
      elseif (preg_match('/^[[:digit:]]$/', $k[$i])) $n++;
      else                                           $o++;


    //* Calcula entropia
    $e  ($M * 4.7) + ($m * 4.7) + ($n * 3.32) + ($o * 6.55);


    return array("k"=>$k, "l"=>$ct, "alfabetos"=>$a, "entropia"=>$e);
  }
}


/** Tipo de dato SQL: Blob.
 *
 * @package tilia\modelo\escritoresSQL\tipo
 */

final class Blob extends Tipo_sql {
  public function __construct() {
    parent::__construct();
  }

  public function ini_atr(Obxeto_bd $obd, Campo_dbd $c, $valor = null) {
    return new Atrobd_blob($obd, $c, $valor);
  }

  public function strsqlselect(Campo_dbd $c, Sintaxis_sql $s) {
    return null;
  }

  public function strsqlinsertupdate(Campo_dbd $c, Sintaxis_sql $s) {
    return null;
  }

  public function formato_sql($valor, Sintaxis_sql $s) {
    return $s->blob_encode($valor);
  }
}


/** Tipo de dato SQL: Numero.
 *
 * @package tilia\modelo\escritoresSQL\tipo
 */

class Numero extends Tipo_sql {
  public function __construct() {
    parent::__construct();
  }

  public function ini_atr(Obxeto_bd $obd, Campo_dbd $c, $valor = null) {
    return new Atributo_obd($obd, $c, $valor);
  }

  public function formato_sql($valor, Sintaxis_sql $s) {
    if ($valor == "") return "null";

    return str_replace(",", ".", $valor);
  }

}


/** Tipo de dato SQL: AutoIncrement.
 *
 * @package tilia\modelo\escritoresSQL\tipo
 */

final class AutoIncrement extends Numero {
  public function __construct() {
    parent::__construct();
  }

  public function strsqlinsertupdate(Campo_dbd $c, Sintaxis_sql $s) {
    return null;
  }
}



/** Tipo de dato SQL: Data (fecha).
 *
 * @package tilia\modelo\escritoresSQL\tipo
 */

class Data extends Tipo_sql {

  public $formato = null;

  public function __construct($formato = "Y-m-d H:i:s") {
    parent::__construct();

    $this->formato = $formato;
  }

  public function ini_atr(Obxeto_bd $obd, Campo_dbd $c, $valor = null) {
    return new Atrobd_data($obd, $c, $valor);
  }

  public function strsqlselect(Campo_dbd $c, Sintaxis_sql $s) {
    return $s->select_data($c);
  }

  public function formato_sql($valor, Sintaxis_sql $s) {
    if ($valor == null) return "null";

    return $s->str_data($valor, $this);
  }

  public function a_data($str_data) {
    //* creamos el patron de la fecha.
    $patron = str_replace("Y", "yyyy", $this->formato);
    $patron = str_replace("m", "mm", $patron);
    $patron = str_replace("d", "dd", $patron);
    $patron = str_replace("H", "hh", $patron);
    $patron = str_replace("i", "ii", $patron);
    $patron = str_replace("s", "ss", $patron);

    //* creamos o array a_data.
    $a_data = array("Y"=>null, "m"=>null, "d"=>null, "H"=>null, "i"=>null, "s"=>null);
    for($i = 0; $i < strlen($patron); $i++) {
          if ($patron[$i] == "y") $a_data["Y"] .= $str_data[$i];
      elseif ($patron[$i] == "m") $a_data["m"] .= $str_data[$i];
      elseif ($patron[$i] == "d") $a_data["d"] .= $str_data[$i];
      elseif ($patron[$i] == "h") $a_data["H"] .= $str_data[$i];
      elseif ($patron[$i] == "i") $a_data["i"] .= $str_data[$i];
      elseif ($patron[$i] == "s") $a_data["s"] .= $str_data[$i];
    }

    return $a_data;
  }
}


/** Tipo de dato SQL: Timestamp.
 *
 * @package tilia\modelo\escritoresSQL\tipo
 */

final class Timestamp extends Data {
  public function __construct($formato = "Y-m-d H:i:s") {
    parent::__construct($formato);
  }

  public function formato_sql($valor, Sintaxis_sql $s) {
    if ($valor != "") return parent::formato_sql($valor, $s);

    return $s->str_timestamp();
  }
}
