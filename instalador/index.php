<?php

error_reporting(E_ALL ^ E_NOTICE);

$trw_home     = "../triwus/";
$tilia_home   = "{$trw_home}/tilia/";

$nome_sesion  = "trw.instalar";

$ehttp_inicio = "EInstalar";


require "{$tilia_home}manifesto.php";

if (is_file("{$trw_home}Config.php")) {
   require "{$trw_home}Config.php";
}

require "{$trw_home}php/control/Refs.php";

require "php/EInstalar.php";
require "php/CInstalar.php";
require "php/CActualiza.php";


if (!session_start()) die ("script action '{$nome_sesion}', !session_start()");


$tilia = new Tilia($nome_sesion, $tilia_home, $ehttp_inicio);

$tilia->transitar();
