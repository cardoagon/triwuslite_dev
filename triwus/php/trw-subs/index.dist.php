<?php

//~ error_reporting(0);
//~ error_reporting(E_ALL ^ E_NOTICE);
error_reporting(E_ALL ^ E_NOTICE ^ E_STRICT);


$nome_sesion  = "xesta-admin";
$ehttp_inicio = "EXesta_admin";

require "manifesto.php";

$trw_home   = "../../triwus/";
$tilia_home = "{$trw_home}tilia/";


if (!session_start()) die ("script action '{$nome_sesion}', !session_start()");



$tilia = new Tilia($nome_sesion, $tilia_home, $ehttp_inicio);

$tilia->transitar();
