<?php

error_reporting(0);
//~ error_reporting(E_ALL ^ E_NOTICE ^ E_STRICT);

//------------------------------------------------

$site_home  = "../../";
$trw_home   = "{$site_home}triwus/";
$tilia_home = "{$trw_home}tilia/";

//------------------------------------------------

require "{$tilia_home}manifesto.php";
require "{$trw_home}manifesto.php";
require "{$trw_home}php/rpc/googlePay/GooglePay.php";

//------------------------------------------------

GPay::log("_SERVER:\n" . print_r($_SERVER, true));
GPay::log("_POST:\n"   . print_r($_POST  , true));

$listener = new GPay_ipn();

try {
  $listener->requirePostMethod();

  $listener->processIpn($_POST);
}
catch (Exception $e) {
  PayPal::log( $e->getMessage() );

  exit(0);
}

