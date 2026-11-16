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

$tpv = new GPay_request($_REQUEST);

//~ $tpv->dump();
$tpv->submit();


