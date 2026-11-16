<?php

/** Calquera Obxeto que implemente esta interface, sera compatible co obxeto AJAX de Tilia Framework.
 *
 * @package tilia\vista
 */


interface IAJAX {

  public function tipoAJAX();
  public function atributosAJAX();
  public function fillosAJAX();
}


/*
   Class: AJAX

   Clase que manexa obxetos IAJAX.
   Esta clase será capaz de xerar arquivos JSON necesarios para a comunicación asíncrona AJAX de Tilia framework.

 * @package tilia\vista
 */

class AJAX {

  protected $json = null;

  public function __construct() {}

  public function pon(IAJAX $iajax, $liberar = false, $js_callback = null) {
    if ($iajax == null) return;

    $this->json[] = self::plantilla($iajax->tipoAJAX(), $iajax->atributosAJAX(), $iajax->fillosAJAX(), $js_callback);

    if ($liberar) $iajax->post(null);
  }

  public function pon_ok($b = true, $js_callback = null) {
    $ok = ($b)?"1":"0";

    $this->json[] = self::plantilla("OK", $ok, null, $js_callback);
  }

  public function ok($b = true, $js_callback = null) {
    $this->pon_ok($b, $js_callback);
  }

  public function limpar() {
    $this->json = null;
  }

  public static function plantilla($tipoAJAX, $atributosAJAX, $fillosAJAX, $js_callback) {
    return array("ti"=>$tipoAJAX, "at"=>$atributosAJAX, "fi"=>$fillosAJAX, "cb"=>$js_callback);
  }

  public function json() {
    $json = $this->json;

    $this->limpar();


    return json_encode($json);
  }
}
