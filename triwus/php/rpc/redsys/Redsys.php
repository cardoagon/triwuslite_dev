<?php

final class Redsys {
  const url_test = "https://sis-t.redsys.es:25443/sis/realizarPago"; //* PROBAS
  const url = "https://sis.redsys.es/sis/realizarPago"; //* REAL

  const log = "redsys-log.txt";

  public static function log($m) {
    $fp = fopen(Redsys::log, "a");

    $m = "\n" . date("YmdHis") . "\n{$m}\n\n";

    fwrite($fp, $m);

    fclose($fp);
  }


  //****   FUNCIONES AUXILIARES   ***//


  /******  3DES Function  ******/
/*
  public static function encrypt_3DES($message, $key) {
    // Se establece un IV por defecto
    $bytes = array(0,0,0,0,0,0,0,0); //byte [] IV = {0, 0, 0, 0, 0, 0, 0, 0}

    $iv = implode(array_map("chr", $bytes)); //PHP 4 >= 4.0.2

    // Se cifra
    $ciphertext = mcrypt_encrypt(MCRYPT_3DES, $key, $message, MCRYPT_MODE_CBC, $iv); //PHP 4 >= 4.0.2

    return $ciphertext;
  }
*/

  public static function encrypt_3DES($message, $key) {
    $l = ceil(strlen($message) / 8) * 8;
    return substr(openssl_encrypt($message . str_repeat("\0", $l - strlen($message)), 'des-ede3-cbc', $key, OPENSSL_RAW_DATA, "\0\0\0\0\0\0\0\0"), 0, $l);
  }

  /******  Base64 Functions  ******/
  public static function base64_url_encode($input) {
    return strtr(base64_encode($input), '+/', '-_');
  }

  public static function encodeBase64($data) {
    return base64_encode($data);
  }

  public static function base64_url_decode($input) {
    return base64_decode(strtr($input, '-_', '+/'));
  }

  public static function decodeBase64($data){
    return base64_decode($data);
  }

  /******  MAC Function ******/
  public static function mac256($ent, $key) {
    return hash_hmac('sha256', $ent, $key, true); //(PHP 5 >= 5.1.2)
  }


  //*** FUNCIONES PARA LA GENERACIÓN DEL FORMULARIO DE PAGO

  public static function getOrder($a_param){
    if(empty($a_param['DS_MERCHANT_ORDER'])) return $a_param['Ds_Merchant_Order'];

    return $a_param['DS_MERCHANT_ORDER'];
  }

  public static function arrayToJson($a_param){
    return json_encode($a_param); //(PHP 5 >= 5.2.0)
  }

  public static function createMerchantParameters($a_param){
    $json = self::arrayToJson($a_param);

    return self::encodeBase64($json);
  }

  public static function createMerchantSignature($key, $a_param){
    $key = self::decodeBase64($key);

    $ent = self::createMerchantParameters($a_param);

    $key = self::encrypt_3DES(self::getOrder($a_param), $key);

    $res = self::mac256($ent, $key);


    return self::encodeBase64($res);
  }


  //**** FUNCIONES PARA LA RECEPCIÓN DE DATOS DE PAGO (Notif, URLOK y URLKO): ////////////

  public static function getOrderNotif($a_param) {
    if (empty($a_param['Ds_Order'])) return $a_param['DS_ORDER'];

    return $a_param['Ds_Order'];
  }

  public static function stringToArray($datosDecod) {
    return json_decode($datosDecod, true); //(PHP 5 >= 5.2.0)
  }

  public static function decodeMerchantParameters($datos) {
    return self::base64_url_decode($datos);
  }

  public static function createMerchantSignatureNotif($key, $datos){
    $key     = self::decodeBase64($key);

    $a_param = self::stringToArray( self::base64_url_decode($datos) );

    $key     = self::encrypt_3DES(self::getOrderNotif($a_param), $key);

    $res     = self::mac256($datos, $key);


    return self::base64_url_encode($res);
  }
}

//************************************************

final class Redsys_request {
  private $test = false;

  private $_k   = array();

  public function __construct($request) {
    if (!isset($_REQUEST['k'])) die('redsys.request.php, !isset($_REQUEST["k"]) ');

    $k = base64_decode($request['k']);

    list($p, $r1, $r2, $z) = explode("::", $k); //* datos do pedido

    list($s, $a , $n) = explode("-" , $p); //* datos do pedido



    $cbd = new FS_cbd();

    $fp_info = Site_fpago_obd::inicia($cbd, $s, 5)->info();

    $this->test = $fp_info['test'] == 1;

    //* marcamos id_pago do pedido baleira.

    $p_obd = Pedido_obd::inicia($cbd, $s, $a, $n);

    //* insertamos na BD a parte do pedido con informacion relacionada coa transaccion de redsys
    $redsys_obd = new Redsys_obd($s, $a, $n);

    $redsys_obd->insert($cbd);


    //* damos valor aos parametros da mensaxe.
    $r_ok = "{$r1}response.php?k=" . base64_encode("ok::{$p}::{$r2}");
    $r_ko = "{$r1}response.php?k=" . base64_encode("ko::{$p}::{$r2}");

    //~ $order  = "{$s}-{$a}-{$n}";
    $order  = "{$a}-{$n}";
    $amount = round($p_obd->calcula_total(-1, $cbd, true) * 100);

    //~ $firma  = Redsys::firma_request($fp_info['config']["firma"], $amount, $order, $fp_info['config']['comercio'], 978, Redsys::ipn);

    $a_k["DS_MERCHANT_MERCHANTCODE"   ] = $fp_info['config']['comercio'];
    $a_k["DS_MERCHANT_TERMINAL"       ] = $fp_info['config']['terminal'];
    $a_k["DS_MERCHANT_AMOUNT"         ] = $amount;
    $a_k["DS_MERCHANT_ORDER"          ] = $order;
    $a_k["DS_MERCHANT_MERCHANTDATA"   ] = $s;
    $a_k["DS_MERCHANT_CURRENCY"       ] = 978;
    $a_k["DS_MERCHANT_TRANSACTIONTYPE"] = "0";
    $a_k["DS_MERCHANT_MERCHANTURL"    ] = "{$r1}ipn.php";
    $a_k["DS_MERCHANT_URLOK"          ] = $r_ok;
    $a_k["DS_MERCHANT_URLKO"          ] = $r_ko;
    $a_k["DS_MERCHANT_PAYMETHODS"     ] = ($z == "1")?"z":"C";

//~ echo "<pre>" . print_r($a_k, true) . "</pre>";

    $this->_k("Ds_SignatureVersion"  , "HMAC_SHA256_V1");
    $this->_k("Ds_MerchantParameters", Redsys::createMerchantParameters($a_k));
    $this->_k("Ds_Signature"         , Redsys::createMerchantSignature($fp_info['config']['firma'], $a_k));
  }

  public function _k($k, $v = "@BAL#") {
    if ($v == "@BAL#") return $this->_k[$k];

    $this->_k[$k] = $v;
  }

  public function submit() {
    $url = ($this->test)?Redsys::url_test:Redsys::url;

    echo "<html>
          <head>
            <meta http-equiv=\"content-type\" content=\"text/html\" charset=\"utf-8\" />
            <title>TPV Redsys</title>
          </head>
          <body onLoad=\"document.forms['fredsys'].submit();\">
          <form method=\"post\" name=\"fredsys\" action=\"{$url}\">\n";

    foreach ($this->_k as $k=>$v) echo "<input type=\"hidden\" name=\"{$k}\" value=\"{$v}\"/>\n";

    echo "</form>\n</body>\n</html>\n";
  }

  public function dump() {
    ksort($this->_k);

    echo "<pre>" . print_r($this->_k, true) . "</pre>";
  }
}

//**********************************

final class Redsys_response {
  public static function __exec($_request) {
    //~ echo "<pre>" . print_r($_request, true) . "</pre>";

    list($kok, $p, $r) = explode("::", base64_decode($_request['k']));

    //~ echo "{$kok}, {$r}";

    self::pedido_kok($kok, $p);

    self::redirect($r, $kok);
  }

  private static function pedido_kok($kok, $p) {
    $id_pago_aux = ($kok == "ok")?null:Pedido_obd::pago_ko;

    $cbd = new FS_cbd();

    list($s, $a , $n) = explode("-" , $p); //* datos do pedido

    $p_obd = Pedido_obd::inicia($cbd, $s, $a, $n);

    if (($kok == "ok") && ($p_obd->atr("id_pago")->valor != Pedido_obd::pago_ko)) return; //* pode ser que se escribira unha notificacion correcta

    $p_obd->atr("id_pago")->valor = $id_pago_aux;

    $p_obd->update($cbd);
  }

  private static function redirect($action, $kok) {
echo "<html>
  <head>
    <meta http-equiv='content-type' content='text/html; charset=UTF-8'>
  </head>

  <body style='background-color: transparent;'>
    <form id='fredsys_response' method='post' action='{$action}?kok={$kok}'>
      <input type='hidden' id='findex;evento' name='findex;evento' value='findex;detalle;cmsxdet;c;redsys_request*{$kok}*tipo=tpvResponse*ajax=0' />
    </form>

    <script>
      document.getElementById('fredsys_response').submit();
    </script>
  </body>
</html>";
  }
}

//**********************************

final class Redsys_ipn {

  public static function __exec($_request_0) {

Redsys::log(print_r($_request_0, true));

    $version      = $_request_0["Ds_SignatureVersion"];
    $datos        = $_request_0["Ds_MerchantParameters"];
    $Ds_Signature = $_request_0["Ds_Signature"];

    $_request = Redsys::stringToArray( Redsys::decodeMerchantParameters($datos) );

Redsys::log(print_r($_request, true));

    $s = $_request['Ds_MerchantData']; list($a, $n) = explode("-", $_request['Ds_Order']);

Redsys::log("::$s, $a, $n::{$_request['Ds_Order']}\n");


    $cbd = new FS_cbd();

    //~ $fp_info = Site_fpago_obd::inicia($cbd, $s, 5)->info();
    $fp_info = Site_fpago_obd::inicia($cbd, $s, 5)->info();

Redsys::log(print_r($fp_info, true));


    //* Validar firma

Redsys::log("Validar firma\n");

    $firma = Redsys::createMerchantSignatureNotif($fp_info['config']["firma"], $datos);


Redsys::log("{$firma}::{$Ds_Signature}\n");

    if ($firma != $Ds_Signature) return;


    //* Control da autorizacion

Redsys::log("Control da autorizacion\n");


    //~ if ( trim($_request['Ds_AuthorisationCode']) == null ) {
    if ( !self::validaAutorizacion($_request) ) {
      $p = Pedido_obd::inicia($cbd, $s, $a, $n);

      CPagar2_email::mail_pago_ko ($p, "../../triwus/");

      return;
    }

    //* Actualizar BD cos datos do pago

Redsys::log("Actualizar BD cos datos do pago\n");

    $redsys_obd = Redsys_obd::inicia($cbd, $s, $a, $n);

    $redsys_obd->atr("referencia")->valor = trim( $_request['Ds_AuthorisationCode'] );

    $redsys_obd->pagar_pedido($cbd);

    $p = Pedido_obd::inicia($cbd, $s, $a, $n);


    CPagar2_email::mail_pago_ok($p, "../../triwus/");
  }

  private static function validaAutorizacion($_request) {
    if (isset($_request['Ds_ErrorCode'])) if ($_request['Ds_ErrorCode'] != null) return false;


    if (!isset($_request['Ds_Response'])) return false;

    $err = (int)trim($_request['Ds_Response']);

    if ($err > 99) return false;


    if (!isset($_request['Ds_AuthorisationCode'])) return false;

    $ath = trim($_request['Ds_AuthorisationCode']);


    return ($ath != null);
  }
}
