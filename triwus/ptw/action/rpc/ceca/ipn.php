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

require "{$trw_home}php/rpc/ceca/CECA.php";

//~ require "{$site_home}manifesto.php";


//------------------------------------------------


CECA_ipn::__exec($_REQUEST);
