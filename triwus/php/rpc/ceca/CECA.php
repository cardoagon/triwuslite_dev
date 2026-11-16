<?php

final class CECA {
  const url_test = 'https://tpv.ceca.es/tpvweb/tpv/compra.action'; //* PROBAS
  //~ const url      = 'https://pgw.ceca.es/tpvweb/tpv/compra.action'; //* REAL

  //~ const url_test = 'http://tpv.ceca.es:8000/cgi-bin/tpv'; //* PROBAS
  const url      = 'https://pgw.ceca.es/cgi-bin/tpv'; //* REAL

  const log = "ceca-log.txt";

  public static function log($m) {
    $fp = fopen(CECA::log, "a");

    $m = "\n" . date("YmdHis") . "\n{$m}\n\n";

    fwrite($fp, $m);

    fclose($fp);
  }

  public static function ptw_email($id_site, $ptw_0) {
    $a_path = pathinfo($ptw_0);

    $ptw_2 = self::ref_triwus . Efs::url_legais($id_site) . $a_path['basename'];

    if (!is_file($ptw_2)) return self::ref_triwus . $ptw_0;


    return $ptw_2;
  }

  public static function firma($fp_info, $id_pedido, $importe, $moeda, $exponente, $url_ok, $url_ko) {
    $k = $fp_info['config']['firma'];
    $m = $fp_info['config']['MerchantID'];
    $a = $fp_info['config']['AcquirerBIN'];
    $t = $fp_info['config']['TerminalID'];

    return hash("sha256", "{$k}{$m}{$a}{$t}{$id_pedido}{$importe}{$moeda}{$exponente}SHA2{$url_ok}{$url_ko}");
  }
}

//************************************************

final class CECA_request {
  private $test = false;

  private $_k   = array();

  public function __construct($request) {
    if (!isset($_REQUEST['k'])) die('ceca.request.php, !isset($_REQUEST["k"]) ');


    $k = base64_decode($request['k']);

    list($p, $r1, $r2, $z) = explode("::", $k); //* datos do pedido

    list($s, $a , $n) = explode("-" , $p); //* datos do pedido



    $cbd = new FS_cbd();

    $fp_info = Site_fpago_obd::inicia($cbd, $s, 5)->info();

    //~ echo "<pre>" . print_r($fp_info, true) . "</pre>";

    $this->test = $fp_info['test'] == 1;

    //* marcamos id_pago do pedido baleira.

    $p_obd = Pedido_obd::inicia($cbd, $s, $a, $n);


    //* insertamos na BD a parte do pedido con informacion relacionada coa transaccion de paypal
    $ceca_obd = CECA_obd::inicia($cbd, $s, $a, $n);

    $ceca_obd->insert($cbd);


    //* damos valor aos parametros da mensaxe.

    $order  = $p;
    $amount = round($p_obd->calcula_total(-1, $cbd, true) * 100);
    $moeda  = "978";
    $exp    = "2";

    $r_ok   = "{$r1}response.php?k=" . base64_encode("ok::{$p}::{$r2}");
    $r_ko   = "{$r1}response.php?k=" . base64_encode("ko::{$p}::{$r2}");


    $firma  = CECA::firma($fp_info, $order, $amount, $moeda, $exp, $r_ok, $r_ko);

    $this->_k("MerchantID"    , $fp_info['config']['MerchantID']);
    $this->_k("AcquirerBIN"   , $fp_info['config']['AcquirerBIN']);
    $this->_k("TerminalID"    , $fp_info['config']['TerminalID']);
    $this->_k("Firma"         , $firma);
    $this->_k("URL_OK"        , $r_ok);
    $this->_k("URL_NOK"       , $r_ko);
    $this->_k("Num_operacion" , $order);
    $this->_k("Importe"       , $amount);
    $this->_k("TipoMoneda"    , $moeda);
    $this->_k("Exponente"     , $exp);
    $this->_k("Pago_soportado", "SSL");
    $this->_k("Cifrado"       , "SHA2");
    
    if ($z == "1") $this->_k("inicioBizum", "1");
  }

  public function _k($k, $v = "@BAL#") {
    if ($v == "@BAL#") return $this->_k[$k];

    $this->_k[$k] = $v;
  }

  public function submit() {
    $url = ($this->test)?CECA::url_test:CECA::url;

    echo "<html>
          <head>
            <meta http-equiv=\"content-type\" content=\"text/html\" charset=\"utf-8\" />
            <title>TPV CECA</title>
          </head>
          <body onLoad=\"document.forms['fceca'].submit();\">
          <form method=\"post\" name=\"fceca\" action=\"{$url}\" ENCTYPE=\"application/x-www-form-urlencoded\">\n";

    foreach ($this->_k as $k=>$v) echo "<input type=\"hidden\" name=\"{$k}\" value=\"{$v}\"/>\n";

    echo "</form>\n</body>\n</html>\n";
  }

  public function dump() {
    ksort($this->_k);

    echo "<pre>" . print_r($this->_k, true) . "</pre>";
  }
}

//**********************************

final class CECA_response {
  public static function __exec($_request) {
    //~ echo "<pre>" . print_r($_request, true) . "</pre>";

    list($kok, $p, $r) = explode("::", base64_decode($_request['k']));

    //~ echo "{$kok}, {$r}";

    self::pedido_kok($kok, $p);

    self::redirect($r, $kok, $p);
  }

  private static function pedido_kok($kok, $p) {
    $id_pago_aux = ($kok == "ok")?null:Pedido_obd::pago_ko;

    $cbd = new FS_cbd();

    list($s, $a , $n ) = explode("-" , $p); //* datos do pedido

    $p_obd = Pedido_obd::inicia($cbd, $s, $a, $n);

    if (($kok == "ok") && ($p_obd->atr("id_pago")->valor != Pedido_obd::pago_ko)) return; //* pode ser que se escribira unha notificacion correcta

    $p_obd->atr("id_pago")->valor = $id_pago_aux;

    $p_obd->update($cbd);
  }

  private static function redirect($action, $kok, $p) {
echo "<html>
  <head>
    <meta http-equiv='content-type' content='text/html; charset=UTF-8'>
  </head>

  <body style='background-color: transparent;'>
    <form id='fceca_response' method='post' action='{$action}?kok={$kok}'>
      <input type='hidden' id='findex;evento' name='findex;evento' value='findex;detalle;cmsxdet;c;ceca_request*{$kok}*tipo=tpvResponse*ajax=0' />
      <input type='hidden' id='tpvResponse-p' name='tpvResponse-p' value='{$p}' />
    </form>

    <script>
      document.getElementById('fceca_response').submit();
    </script>
  </body>
</html>";
  }
}

//**********************************

final class CECA_ipn {
  public static function __exec($_request) {
CECA::log(print_r($_request, true));

    list($s, $a, $n) = explode("-", $_request['Num_operacion']);

CECA::log("::$s, $a, $n::{$_request['Num_operacion']}\n");

    $cbd = new FS_cbd();

    $fp_info = Site_fpago_obd::inicia($cbd, $s, 5)->info();


    //* Validar firma

CECA::log("Validar firma\n");

    $ok = self::firmaIPN_validar($_request['Firma'], $fp_info, $_request['Num_operacion'], $_request['Importe'], $_request['TipoMoneda'], $_request['Exponente'], $_request['Referencia']);

    if (!$ok) return self::__fin_ko($cbd, $s, $a, $n);


    //* Actualiza pedido cos datos de pago

CECA::log("Actualiza pedido cos datos de pago\n");

    $ceca_obd = CECA_obd::inicia($cbd, $s, $a, $n);

    $ceca_obd->atr("referencia")->valor = $_request['Referencia'];

    $ceca_obd->pagar_pedido($cbd);


    $p = Pedido_obd::inicia($cbd, $s, $a, $n);


    CPagar2_email::mail_pago_ok($p, "../../triwus/");


    //* Escribimos resposta $*$OKY$*$

CECA::log('$*$OKY$*$');

    echo '<HTML><HEAD></HEAD><BODY>$*$OKY$*$</BODY></HTML>';
  }

  private static function __fin_ko(FS_cbd $cbd, $s, $a, $n) {
    $p = Pedido_obd::inicia($cbd, $s, $a, $n);

    CPagar2_email::mail_pago_ko ($p, "../../triwus/");

    return;
  }

  private static function firmaIPN_validar($firma_0, $fp_info, $num_operacion, $importe, $moeda, $exponente, $referencia) {
    $k = $fp_info['config']['firma'];
    $m = $fp_info['config']['MerchantID'];
    $a = $fp_info['config']['AcquirerBIN'];
    $t = $fp_info['config']['TerminalID'];

    //~ echo "firma::{$k}|{$m}|{$a}|{$t}|{$num_operacion}|{$importe}|{$moeda}|{$exponente}|{$referencia}<br>";

    return ($firma_0 == hash("sha256", "{$k}{$m}{$a}{$t}{$num_operacion}{$importe}{$moeda}{$exponente}{$referencia}"));
  }
}
