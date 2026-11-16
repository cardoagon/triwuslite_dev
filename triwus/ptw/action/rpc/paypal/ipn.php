<?php

error_reporting(0);
//~ error_reporting(E_ALL ^ E_NOTICE ^ E_STRICT);

//------------------------------------------------

$site_home  = "../../";
$trw_home   = "{$site_home}triwus/";
$tilia_home = "{$trw_home}tilia/";

//------------------------------------------------

if (is_file("{$site_home}admin/config.php")) require "{$site_home}admin/config.php";

require "{$tilia_home}manifesto.php";

require "{$trw_home}manifesto.php";

require "{$trw_home}php/rpc/paypal/PayPal.php";

//~ require "{$site_home}manifesto.php";


//------------------------------------------------

PayPal::log("_SERVER:\n" . print_r($_SERVER, true));
PayPal::log("_POST:\n"   . print_r($_POST  , true));

$listener = new PayPal_ipn();

try {
  $listener->requirePostMethod();

  $listener->processIpn($_POST);
}
catch (Exception $e) {
  PayPal::log( $e->getMessage() );

  exit(0);
}

