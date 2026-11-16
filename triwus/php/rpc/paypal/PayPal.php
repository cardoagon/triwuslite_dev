<?php

final class PayPal {
  const url = 'https://www.paypal.com/cgi-bin/webscr';
  const url_test = 'https://www.sandbox.paypal.com/cgi-bin/webscr';

  const log = "paypal-log.txt";

  public static function log($m) {
    $fp = fopen(PayPal::log, "a");

    $m = "\n" . date("YmdHis") . "\n{$m}\n\n";

    fwrite($fp, $m);

    fclose($fp);
  }
}

//**********************************

final class PayPal_request {
  private $_k = array();

  private $test = false;

  public function __construct($request) {
    if (!isset($_REQUEST['k'])) die('paypal.request.php, !isset($_REQUEST["k"]) ');

    $k = base64_decode($request['k']);

    list($p, $r1, $r2) = explode("::", $k); //* datos do pedido

    list($s, $a , $n ) = explode("-" , $p); //* datos do pedido


    $cbd = new FS_cbd();


    $sfp = Site_fpago_obd::inicia($cbd, $s, 4);

    $a_info = $sfp->info();


    $this->_k("business", $a_info['config']['id_paypal']);

    $this->test = $a_info['test'] == 1;


    //* insertamos na BD a parte do pedido con informacion relacionada coa transccion de paypal
    $paypal_obd = new PayPal_obd($s, $a, $n);

    $paypal_obd->insert($cbd);


    //* marcamos a id_pago do pedido baleira.

    $p_obd = Pedido_obd::inicia($cbd, $s, $a, $n);
/*
    $p_obd->atr("id_pago")->valor = Pedido_obd::pago_ko;

    $p_obd->update($cbd);
*/

    //* damos valor aos parametros da mensaxe.
    $r_ok = "{$r1}response.php?k=" . base64_encode("ok::{$p}::{$r2}");
    $r_ko = "{$r1}response.php?k=" . base64_encode("ko::{$p}::{$r2}");


    $this->_k("item_name"    , "Total de su pedido");
    $this->_k("cmd"          , "_xclick");
    $this->_k("paymentaction", "sale");
    $this->_k("amount"       , $p_obd->calcula_total(-1, $cbd, true));
    $this->_k("currency_code", "EUR");
    $this->_k("custom"       , "{$s}");
    $this->_k("invoice"      , "{$n}-{$a}");
    $this->_k("return"       , "{$r_ok}");
    $this->_k("cancel_return", "{$r_ko}");
    $this->_k("notify_url"   , "{$r1}ipn.php");
    $this->_k("cbt"          , "Volver al Comercio");
    $this->_k("no_shipping"  , "1");
    $this->_k("rm"           , "2");
  }

  public function _k($k, $v = "@BAL#") {
    if ($v == "@BAL#") return $this->_k[$k];

    $this->_k[$k] = $v;
  }

  public function submit() {
    $url = ($this->test)?PayPal::url_test:PayPal::url;

    echo "<html>
          <head>
            <meta http-equiv=\"content-type\" content=\"text/html\" charset=\"utf-8\" />
            <title>TPV PayPal</title>
          </head>
          <body onLoad=\"document.forms['fpaypal'].submit();\">
          <form method=\"post\" name=\"fpaypal\" action=\"{$url}\">\n";

    foreach ($this->_k as $k=>$v) echo "<input type=\"hidden\" name=\"{$k}\" value=\"{$v}\"/>\n";

    echo "</form>\n</body>\n</html>\n";
  }

  public function dump() {
    $url = ($this->test)?PayPal::url_test:PayPal::url;

    echo "url::{$url}<br>";

    ksort($this->_k);

    echo "<pre>" . print_r($this->_k, true) . "</pre>";
  }
}

//**********************************

final class PayPal_response {
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

    list($s, $a , $n ) = explode("-" , $p); //* datos do pedido

    $p_obd = Pedido_obd::inicia($cbd, $s, $a, $n);

    if (($kok == "ok") && ($p_obd->atr("id_pago")->valor != Pedido_obd::pago_ko)) return; //* pode ser que se escribira unha notificación correcta

    $p_obd->atr("id_pago")->valor = $id_pago_aux;

    $p_obd->update($cbd);
  }

  private static function redirect($action, $kok) {
echo "
<html>
  <head>
    <meta http-equiv='content-type' content='text/html; charset=UTF-8'>
  </head>

  <body style='background-color: transparent;'>
    <form id='fpaypal_response' method='post' action='{$action}?kok={$kok}'>
      <input type='hidden' id='findex;evento' name='findex;evento' value='findex;detalle;cmsxdet;c;paypal_request*{$kok}*tipo=tpvResponse*ajax=0' />
    </form>

    <script>
      document.getElementById('fpaypal_response').submit();
    </script>
  </body>
</html>";
  }
}

//**********************************

final class PayPal_ipn {
  public $use_ssl         = true;

  public $use_sandbox     = false;

  public $timeout = 30;

  private $post_data = array();
  private $post_uri = '';
  private $response_status = '';
  private $response = '';

  const PAYPAL_HOST  = 'www.paypal.com';
  const SANDBOX_HOST = 'www.sandbox.paypal.com';

  public function getPostUri() {
      return $this->post_uri;
  }

  public function getResponse() {
      return $this->response;
  }

  public function getResponseStatus() {
      return $this->response_status;
  }

  public function getTextReport() {
    $r = str_repeat('-', 80);

    // date and POST url
    $r .= "\n" . $this->getPostUri() . " (fsockopen)\n";

    // HTTP Response
    $r .= str_repeat('-', 80);
    $r .= "\n" . $this->getResponse(). "\n";

    // POST vars
    $r .= str_repeat('-', 80) . "\n";

    foreach ($this->post_data as $key => $value) $r .= str_pad($key, 25)."$value\n";

    $r .= "\n\n";

    return $r;
  }

  public function processIpn($post_data) {
    $this->post_data = $post_data;

    $this->check_sandbox();

    $encoded_data = 'cmd=_notify-validate';

    foreach ($this->post_data as $key => $value) $encoded_data .= "&{$key}=".urlencode($value);


    $this->fsockPost($encoded_data);

    if (strpos($this->response_status, '200') === false) throw new Exception("Invalid response status: ".$this->response_status);

    if (strpos($this->response, "VERIFIED") !== false) return $this->check_bd(true);

    if (strpos($this->response, "INVALID") !== false) {
      PayPal::log( $this->getTextReport() );

      return $this->check_bd(false);
    }

    throw new Exception("Unexpected response from PayPal.");
  }

  public function requirePostMethod() {
    // require POST requests
    if ($_SERVER['REQUEST_METHOD'] && $_SERVER['REQUEST_METHOD'] != 'POST') {
      header('Allow: POST', true, 405);
      throw new Exception("Invalid HTTP request method.");
    }
  }

  protected function fsockPost($encoded_data) {
      if ($this->use_ssl) {
          $uri = 'ssl://'.$this->getPaypalHost();
          $port = '443';
          $this->post_uri = $uri.'/cgi-bin/webscr';
      } else {
          $uri = $this->getPaypalHost(); // no "http://" in call to fsockopen()
          $port = '80';
          $this->post_uri = 'http://'.$uri.'/cgi-bin/webscr';
      }

      $fp = fsockopen($uri, $port, $errno, $errstr, $this->timeout);

      if (!$fp) {
          // fsockopen error
          throw new Exception("fsockopen error: [$errno] $errstr");
      }

      $header = "POST /cgi-bin/webscr HTTP/1.1\r\n";
      $header .= "Host: ".$this->getPaypalHost()."\r\n";
      $header .= "Content-Type: application/x-www-form-urlencoded\r\n";
      $header .= "Content-Length: ".strlen($encoded_data)."\r\n";
      $header .= "Connection: Close\r\n\r\n";

      fputs($fp, $header.$encoded_data."\r\n\r\n");

      while(!feof($fp)) {
        if (empty($this->response)) {
          // extract HTTP status from first line
          $status = fgets($fp, 1024);

          $this->response .= $status;
          $this->response_status = trim(substr($status, 9, 4));
        } else {
          $this->response .= fgets($fp, 1024);
        }
      }

      fclose($fp);
  }

  private function getPaypalHost() {
      if ($this->use_sandbox) return self::SANDBOX_HOST;

      return self::PAYPAL_HOST;
  }

  private function check_bd($verified) {
    $cbd = new FS_cbd();

    $s = $this->post_data['custom'];list($n, $a) = explode("-", $this->post_data['invoice']);

    $paypal_obd = PayPal_obd::inicia($cbd, $s, $a, $n);

    if ($paypal_obd->atr("IPN_response")->valor == "V") {
      //* xa foi notificado anteriormente
      PayPal::log("PayPal_ipn::check_bd(), paypal_obd.atr('IPN_response').valor == 'V'");

      return;
    }


    if (!$verified) {
      $p = Pedido_obd::inicia($cbd, $s, $a, $n);

      CPagar2_email::mail_pago_ko ($p, "../../triwus/");

      $paypal_obd->atr("IPN_response")->valor = "I";

      if (!$paypal_obd->insert($cbd)) PayPal::log("PayPal_ipn::check_bd()::00, !paypal_obd.insert()");

      return;
    }


    $cbd->transaccion();

    $paypal_obd->atr("tx"          )->valor = $this->post_data['txn_id'];
    $paypal_obd->atr("status"      )->valor = strtolower( $this->post_data['payment_status'] );
    $paypal_obd->atr("IPN_response")->valor = "V";

    if (!$paypal_obd->insert($cbd)) {
      $cbd->rollback();

      PayPal::log("PayPal_ipn::check_bd()::11, !paypal_obd.insert()");

      return;
    }

    if ($paypal_obd->atr("status")->valor != "completed") {
      $cbd->commit();

      $p = Pedido_obd::inicia($cbd, $s, $a, $n);

      CPagar2_email::mail_pago_ko ($p, "../../triwus/");

      PayPal::log("PayPal_ipn::check_bd()::11, paypal_obd.atr(\"status\").valor != \"completed\"");

      return;
    }

    if (!$paypal_obd->pagar_pedido($cbd)) {
      $cbd->rollback();

      PayPal::log("PayPal_ipn::check_bd(), !paypal_obd.pagar_pedido()");

      return;
    }


    $cbd->commit();

    $p = Pedido_obd::inicia($cbd, $s, $a, $n);

    CPagar2_email::mail_pago_ok($p, "../../triwus/");
  }

  private function check_sandbox() {
    if (!isset($this->post_data['custom'])) return;

    $cbd = new FS_cbd();


    $sfp = Site_fpago_obd::inicia($cbd, $this->post_data['custom'], 4);

    $a_info = $sfp->info();

    $this->use_sandbox = $a_info['test'] == 1;
  }

}

