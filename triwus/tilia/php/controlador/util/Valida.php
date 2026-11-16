<?php

/** Colección de algoritmos de validación comunes a muchas aplicaciones web
 *
 * @package tilia\controlador\util
 */


final class Valida {

  public static function url($url) {
    return filter_var($url, FILTER_VALIDATE_URL);
  }

  public static function mkdir($ruta) {
    //* Recibe unha ruta en $ruta, e comproba que existan as carpetas.

    $_r = explode("/", $ruta);

    $r = ($_r[0] == "/")?"/":"";
    foreach ($_r as $n) {
      if ($n == null) continue;

      if (($r != "") || ($r != "/")) $r .= "/";

      $r .= $n;

//~ echo "{$r}<br>";

      if (!is_dir($r)) mkdir( $r );
   }

    return $ruta;
  }

  public static function split($s, $l, $plus = " &hellip;") {
    if ($s == null) return "";

    $s = strip_tags(html_entity_decode($s));

    if (strlen($s) <= $l) return $s;

    $_s = explode(" ", $s);

    $s2 = $_s[0] . " ";
    for ($i = 1; $i < count($_s); $i++) {
      if (strlen($s2 . $_s[$i]) >= $l) return $s2 . $plus;

      $s2 .= $_s[$i] . " ";
    }


    return $s2 . $plus;
  }

  public static function email($m, $domcheck = true):bool {
    // Primero, checamos que solo haya un símbolo @, y que los largos sean correctos
    if (!preg_match("{^[^@]{1,64}@[^@]{1,255}$}", $m)) {
      // correo inválido por número incorrecto de caracteres en una parte, o número incorrecto de símbolos @
      return false;
    }

    // se divide en partes para hacerlo más sencillo
    $email_array = explode("@", $m);
    $local_array = explode(".", $email_array[0]);
    for ($i = 0; $i < sizeof($local_array); $i++) {
      if (!preg_match("{^(([A-Za-z0-9!#$%&'*+/=?^_`{|}~-][A-Za-z0-9!#$%&'*+/=?^_`{|}~\.-]{0,63})|(\"[^(\\|\")]{0,62}\"))$}", $local_array[$i])) {
        return false;
      }
    }

    // se revisa si el dominio es una IP. Si no, debe ser un nombre de dominio válido
    if (!preg_match("{^\[?[0-9\.]+\]?$}", $email_array[1])) {
       $domain_array = explode(".", $email_array[1]);
       if (sizeof($domain_array) < 2) {
          return false; // No son suficientes partes o secciones para se un dominio
       }
       for ($i = 0; $i < sizeof($domain_array); $i++) {
          if (!preg_match("{^(([A-Za-z0-9][A-Za-z0-9-]{0,61}[A-Za-z0-9])|([A-Za-z0-9]+))$}", $domain_array[$i])) {
             return false;
          }
       }
    }

    // se consulta al DNS del dominio si existe el email.
    if (!$domcheck) return true;

    if ( checkdnsrr($email_array[1], "MX") ) return true; 
    
    return checkdnsrr($email_array[1], "A");
  }

  public static function emails($emails) {
    $_p = preg_split("/[\s\n\r\t,;:]+/", $emails );

    $tp = ""; $erros = "";
    foreach($_p as $p) {
      if (trim($p) == "") continue;

      if (!Valida::email($p)) {
        if ($erros != "") $erros .= ", ";

        $erros .= "<b>{$p}</b>";

        continue;
      }

      if ($tp != "") $tp .= "\n";

      $tp .= $p;
    }

//~ echo "|{$tp}::{$erros}|<br>";


    return array($tp, $erros);
  }

  public static function tlfn($t, $p = 34) {
    /**
     * @desc - si es un telefono correcto(españa, móvil).
     */

    if (($t = trim($t)) == null) return null;


    if ($p != 34) return $t; //* se prefixo non é España (34), non se valida o formato.


    $t = preg_replace( array('/[-.]/', '/\s/'), array('', ''), $t );


    if (!preg_match("/^[0-9]{9}$/", $t)) return null;


    return substr($t, 0, 3) . "-" . substr($t, 3, 3) . "-" . substr($t, 6, 3);
  }

  public static function IPv4($ip) {
    $long = ip2long($ip);

    return !($long == -1 || $long === FALSE);
  }

  public static function IMO($imo) {
    $imo = trim($imo);

    if ($imo == null) return -1;

    $aux = "";
    for($i = 0; $i < strlen($imo); $i++) if ( is_numeric($imo[$i]) ) $aux .= $imo[$i];

    if ( strlen($aux) != 7 ) return -2;


    $s = 0;
    for($i = 0; $i < 6; $i++) $s += ( $aux[ $i ] * ( 7 - $i ) );

    $s = (String)$s;
    if ( $s[ strlen($s) - 1 ] != $aux[6] ) return -3;


    return 1;
  }

  public static function cc($cc) {
    if ($cc == null) return "Debes escribir el n&ordm; de cuenta";

    $valores = array(1, 2, 4, 8, 5, 10, 9, 7, 3, 6);

    $controlCS = 0; $controlCC = 0;
    for ($i = 0; $i <= 7; $i++) $controlCS += (int)$cc[$i] * $valores[$i + 2];

    $controlCS = 11 - ($controlCS % 11);

        if ($controlCS == 11) $controlCS = 0;
    elseif ($controlCS == 10) $controlCS = 1;

    for ($i = 10; $i <= 19; $i++) $controlCC += (int)$cc[$i] * $valores[$i - 10];

    $controlCC = 11 - ($controlCC % 11);

        if ($controlCC == 11) $controlCC = 0;
    elseif ($controlCC == 10) $controlCC = 1;

    if (($cc[8] == $controlCS) && ($cc[9] == $controlCC )) return null;

    return "El número de cuenta proporcionado no es válido";
  }

  public static function iban($iban):?string {
    $iban = str_replace([" ", "-"], ["", ""], $iban);

    $_letras = array("A"=>10, "B"=>11, "C"=>12, "D"=>13, "E"=>14, "F"=>15, "G"=>16,"H"=>17, "I"=>18, "J"=>19, "K"=>20, "L"=>21, "M"=>22, "N"=>23, "O"=>24, "P"=>25, "Q"=>26, "R"=>27, "S"=>28, "T"=>29, "U"=>30, "V"=>31, "W"=>32, "X"=>33, "Y"=>34, "Z"=>35);

    $_pais = str_pad("{$_letras[ $iban[0] ]}{$_letras[ $iban[1] ]}", 6, "0", STR_PAD_RIGHT);


    $cc = substr($iban, 4);

    if (($erro = self::cc($cc)) != null) return $erro;

    //* E=14, S=28, 00 se concatena al numero de cuenta y se resta a 98 el modulo 97 resultante
    $iban_cc = sprintf("%02d", 98 - self::bcmod("{$cc}{$_pais}", 97));

    if ($iban_cc == substr($iban, 2, 2)) return null;

    return "El IBAN proporcionado no es válido";
  }

  public static function comas($txt) {
    $a_txt = preg_split("/[\s,;:]+/", trim($txt));

    if (!is_array($a_txt)) return null;

    $txt = null;
    foreach ($a_txt as $p) {
      if (trim($p) == "") continue;

      if ($txt != null) $txt .= ", ";

      $txt .= $p;
    }

    return $txt;
  }

  public static function _imxprops($src) {
    $_a = getimagesize($src);

    return array("w" => $_a[0],
                 "h" => $_a[1],
                 "m" => $_a["mime"],
                 "t" => File::html_bytes(filesize($src)),
                 "d" => date("d-m-Y H:i:s", filemtime($src))
                );
  }

  public static function numero($n, $d = 0) {
    //* valida numeros reales

    if ($n       == null) return "0";
    if (trim($n) == ""  ) return "0";

    $n = str_replace(",", ".", $n);

        if ( !is_numeric($n) ) $n = "0";
    elseif ( $d > -1         ) $n = round($n, $d);


    return $n;
  }

  public static function datahora(string $dh, string $f = "Y-m-d H:i:s") {
    if (date_create_from_format($f, $dh) === false) return false;

    return true;
  }

  public static function upload($a_file, $so_imx = true) {
    if ($a_file['error'] > 0) return $a_file['error'];

    if (!$so_imx) return null;

    if (exif_imagetype($a_file['tmp_name']) === FALSE) return 5;

    return null;
  }

  public static function nif_cif_nie($d) {
    /*
     * Fonte: http://bulma.net/impresion.phtml?nIdNoticia=2248
     *
     * Copyright ©2005-2011 David Vidal Serra. Bajo licencia GNU GPL.
     * Este software viene SIN NINGUN TIPO DE GARANTIA; para saber mas detalles
     * puede consultar la licencia en http://www.gnu.org/licenses/gpl.txt(1).
     *
     * Esto es software libre, y puede ser usado y redistribuirdo de acuerdo
     * con la condicion de que el autor jamas sera responsable de su uso.
     *
     * Returns: 1 = NIF ok, 2 = CIF ok, 3 = NIE ok, -1 = NIF ko, -2 = CIF ko, -3 = NIE ko, 0 = ??? ko
     *
     */
    $d = strtoupper($d);

    for ($i = 0; $i < 9; $i ++) $num[$i] = substr($d, $i, 1);

    //* si no tiene un formato valido devuelve error
    if (!preg_match('/((^[A-Z]{1}[0-9]{7}[A-Z0-9]{1}$|^[T]{1}[A-Z0-9]{8}$)|^[0-9]{8}[A-Z]{1}$)/', $d)) return 0;

    //* NIFs estandar
    if (preg_match('/(^[0-9]{8}[A-Z]{1}$)/', $d)) return ($num[8] == substr('TRWAGMYFPDXBNJZSQVHLCKE', substr($d, 0, 8) % 23, 1))?1:-1;

    //* Codigos tipo CIF
    $suma = $num[2] + $num[4] + $num[6];
    for ($i = 1; $i < 8; $i += 2) {
      if ( ($a = substr((2 * $num[$i]), 0, 1) ) == "") $a = 0;
      if ( ($b = substr((2 * $num[$i]), 1, 1) ) == "") $b = 0;

      $suma += ($a + $b);
    }

    $n = 10 - substr($suma, strlen($suma) - 1, 1);

    //* NIFs especiales (se calculan como CIFs o como NIFs)
    if (preg_match('/^[KLM]{1}/', $d))
      return ($num[8] == chr(64 + $n) || $num[8] == substr('TRWAGMYFPDXBNJZSQVHLCKE', substr($d, 1, 8) % 23, 1))?1:-1;

    //* CIFs
    if (preg_match('/^[ABCDEFGHJNPQRSUVW]{1}/', $d))
      return ($num[8] == chr(64 + $n) || $num[8] == substr($n, strlen($n) - 1, 1))?2:-2;

    //* NIEs
    if (preg_match('/^[XYZ]{1}/', $d))
      return ($num[8] == substr('TRWAGMYFPDXBNJZSQVHLCKE', substr(str_replace(array('X','Y','Z'), array('0','1','2'), $d), 0, 8) % 23, 1))?3:-3;


    //* error, no se ha verificado
    return 0;
  }

  public static function nass($nass) {
    $nass = preg_replace("[^0-9]", "", $nass);  // elimina todo o que non son números

    if (strlen($nass) != 12) return false;

    $a = substr($nass,  0, 2);
    $b = substr($nass,  2, 8);
    $c = substr($nass, 10, 2);

    $d = ($b < 10000000)?$a * 10000000 + $b:"{$a}{$b}";


    return ($c == bcmod($d, 97));
  }

  public static function suma_dias($dias = 1, $Ymd = null, $f = "d-m-Y") {
    if ($Ymd == null) $Ymd = date("Ymd");

    $d = date_create($Ymd);

        if ($dias > 0) date_add($d, date_interval_create_from_date_string( "{$dias} days" ));
    elseif ($dias < 0) date_sub($d, date_interval_create_from_date_string( abs($dias) . " days" ));

    return date_format($d, $f);
  }

  public static function xml2array($xml) {
    if (!function_exists("normalizeSimpleXML")) {
      function normalizeSimpleXML($obj, &$result) {
        $data = $obj;
        if (is_object($data)) {
          $data = get_object_vars($data);
        }
        if (is_array($data)) {
          foreach ($data as $key => $value) {
            $res = null;
            normalizeSimpleXML($value, $res);

            if (($key == '@attributes') && ($key)) {
              $result = $res;
            }
            else {
              $result[$key] = $res;
            }
          }
        }
        else {
          $result = $data;
        }

        return $result;
      }
    }

    $xml = str_replace(array("<![CDATA[", "]]>"), array("", ""), $xml);

    normalizeSimpleXML(simplexml_load_string($xml), $result);


    return $result;
  }

  public static function html_utf8_entity_decode($html) {
    $html = preg_replace_callback("/(&#[0-9]+;)/", function($m) { return mb_convert_encoding($m[1], "UTF-8", "HTML-ENTITIES"); }, $html);




    return $html;
  }

  public static function buscador_txt2sql($txt, $a_campos, $estricto = false) {
    if (count($a_campos) == 0) return "";

    $a_txt = preg_split("/[\s,;:]+/", $txt);

    if (count($a_txt) == 0) return "";

    $w = null;
    foreach ($a_txt as $p) {
      if ($p == null) continue;

      if (strlen($p) < 3) continue;

      if ($w != null) $w .= " and ";

      if (!$estricto) {
        if (is_numeric($p));
        elseif (strlen($p) > 3) $p = substr($p, 0, strlen($p) - 1); //* heurística mingo, coido que funciona mellor en galego, castelan ... elimina plurais e xenero
      }

      $w2 = null;
      foreach ($a_campos as $c) {
        if ($w2 != null) $w2 .= " or ";

        if ($estricto)
          $w2 .= "{$c} regexp '[ ,.]{$p}' or {$c} regexp '{$p}[ ,.]'";
        else
          $w2 .= "{$c} like ('%{$p}%')";
      }

      $w .= "({$w2})";
    }

    return $w;
  }

  public static function buscador_data2sql($campo, $op, $data) {
    if ($data == null) return "(1=1)";

    if ($op == "<=") $data .= " 23:59:59";

    return "({$campo} {$op} '{$data}')";
  }

  public static function json($s) {
   json_decode($s);

   return json_last_error() === JSON_ERROR_NONE;
  }

  public static function bcmod( $x, $y ) {
      // how many numbers to take at once? carefull not to exceed (int)
      $take = 5;
      $mod = '';

      do
      {
          $a = (int)$mod.substr( $x, 0, $take );
          $x = substr( $x, $take );
          $mod = $a % $y;
      }
      while ( strlen($x) );

      return (int)$mod;
  }
}


//************************************


final class Parse_Html_meta {
  private $url   = null;
  private $_meta = null;

  private function __construct($_meta) {
    $this->_meta = $_meta;
  }

  public static function parse_html($html) {
    if ($html == null) return null;

    return new Parse_Html_meta( self::html2_meta($html) );
  }

  public static function parse_url($url) {
    if (!Valida::url($url)) return null;

    $o = self::parse_html( self::urlscan($url) );

    if ($o == null) return null;

    $o->url = $url;


    return $o;
  }

  public static function imxscan($url, $fdescarga = null) {
//~ echo "imxscan($url, $fdescarga)<br>";

    if (!Valida::url($url)) return false;


//~ echo "imxscan. URL OK<br>";

    $opts = [
      'http' => [
        'method' => "GET",
        'header' => "User-Agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/50.0.2661.102 Safari/537.36",
      ]
    ];

    $context = stream_context_create($opts);

    try {
      $imx = file_get_contents($url, false, $context);
    }
    catch (Exception $ex) {
      echo $ex->getMessageText();
      throw $ex;
      
      return false;
    }

    if ($fdescarga == null) return $imx;

    if (!file_put_contents($fdescarga, $imx)) return false;

//~ echo "imxscan. 2222222222222222222222222222<br>";


    return true;
  }

  public function baleiro() {
    return (count($this->_meta) == 0);
  }

  public function url() {
    return $this->url;
  }

  public function _meta() {
    return $this->_meta;
  }

  public function _content($k = null) {
    if (count($this->_meta) == 0) return null;

    $_c = null;
    foreach ($this->_meta as $_m) {
      if ( !isset($_m["content"]) ) continue;

      if ( $k == null ) {
        $_c[] = $_m["content"];

        continue;
      }

      if ( !isset($_m[$k]) ) continue;

      $_c[ $_m[$k] ] = $_m["content"];
    }

    return $_c;
  }

  private static function html2_meta($html, $i0 = 0) {
    $html      = self::html_head($html);

    $html_len  = strlen    ($html);
    $html_mini = strtolower($html);

    $_meta = array();
    while ($i0 < $html_len) {
      if (($i0 = strpos($html_mini, "<meta " , $i0    )) === false) {
        $i0 = $html_len;

        continue;
      }

      if (($in = strpos($html_mini, ">"      , $i0 + 1)) === false) {
        $i0 = $html_len;

        continue;
      }

      $m = substr($html, $i0, $in - $i0 + 1);

      $_meta[] = self::parse_meta($m);

      $i0 = $in + 1;
    }


    return $_meta;
  }

  private static function html_head($html) {
    $html_mini = strtolower($html);

    if (($i0 = strpos($html_mini, "<head>" , 0)      ) === false) return null;
    if (($in = strpos($html_mini, "</head>", $i0 + 6)) === false) return null;

    $html = substr($html, $i0, $in - $i0 + 7);

    $html = strip_tags($html, "<head><meta>");

    return $html;
  }

  private static function parse_meta($html) {
    if (strpos($html, "/>") === false) $html .= "</meta>";

    $html = html_entity_decode($html);
    $html = Valida::html_utf8_entity_decode($html);

    $html = str_replace(array("&",";"), array("",""), $html);


    //~ if (!mb_detect_encoding($html, 'UTF-8', true)) $html = utf8_encode( $html );
    if (!mb_detect_encoding($html, 'UTF-8', true)) $html = mb_convert_encoding($html, "UTF-8", mb_detect_encoding($html)); 


//*** echo $html . "\n";

    return Valida::xml2array($html);
  }

  private static function urlscan($url, $bmax = 512000) {
    $f = @fopen($url, 'r');

    if ( !$f ) return null;


    $b       = true;
    $html    = null;
    $ct_bmax = 0;
    while ( ($b) && (!feof($f)) ) {
      $ct_bmax += 4096;

      $s = @fgets($f, 4096);

      $html .= $s;

      $b = ( (strpos( strtolower($s), "</head>" ) === false) || ($ct_bmax >= $bmax) );
    }

    fclose($f);


    return $html;
  }
}
