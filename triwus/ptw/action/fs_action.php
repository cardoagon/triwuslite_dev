<?php

error_reporting(E_ALL ^ E_NOTICE);


$nome_sesion  = "fs_[idsite]";

$tilia_home   = "triwus/tilia/";
$fs_home      = "triwus/";

$ehttp_inicio = "Efs_paxina";

//------------------------------------------------

if (is_file("admin/config.php")) require "admin/config.php";

require "{$tilia_home}manifesto.php";
require "{$fs_home}manifesto.php";

if (is_file("manifesto.php")) require "manifesto.php";

//------------------------------------------------

$k = (isset($_GET['k']))?$_GET['k']:null;
$l = (isset($_GET['l']))?$_GET['l']:null;

putenv("get.id_oms=[idoms]");
putenv("get.id_pax={$k}");
putenv("get.id_idioma={$l}");


//------------------------------------------------

if (!session_start()) die ("fs_action, !session_start()");

$tilia = new Tilia($nome_sesion, $tilia_home, $ehttp_inicio);

$tilia->transicion($_SESSION, $_REQUEST, $_FILES);
