<?php

/** Permite manexar plantillas web a outros obxetos de tilia.
 *
 * @package tilia\controlador\util
 */



final class LectorPlantilla {
  private function __construct() {}
 
  public static function json($url, Idioma $idioma = null) {
    $json = self::plantilla($url, $idioma);


    return json_decode($json);
  }
 
  public static function plantilla($url, Idioma $idioma = null):string {
    $l = self::l($url, $idioma);

    if (!file_exists($l)) {
      throw new Exception("ERROR: LectorPlantilla.plantilla(), !file_exists('{$url}')");
      
      return "";
    }

    return file_get_contents($l);
  }

  private static function l($url, Idioma $idioma = null) {
    if ($idioma == null) return $url;

    $_path = pathinfo($url);

    $nome = $_path['filename'];
    if ($_path['dirname'] != null) $nome = "{$_path['dirname']}/{$nome}";

    $ext = $_path['extension'];

    if (!file_exists("{$nome}.{$idioma->codigo}.{$ext}")) return $url;

    return "{$nome}.{$idioma->codigo}.{$ext}";
  }
}
