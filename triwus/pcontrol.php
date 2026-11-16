<?php

error_reporting(E_ALL ^ E_WARNING ^ E_NOTICE);


//~ if (!isset($_SERVER['HTTPS'])) {
  //~ $qs = ($_SERVER['QUERY_STRING'] == null)?"":"?{$_SERVER['QUERY_STRING']}";

  //~ header("Location: https://triwus.com/pcontrol.php{$qs}");
//~ }



$nome_sesion = "triwus.pcontrol.php";

putenv("pcontrol=1");

$fs_home    = "";
$tilia_home = "tilia/";

$ehttp_inicio = "Econsulta_site";


require "{$tilia_home}manifesto.php";
require "manifesto.php";
require "manifesto_adminsite.php";


if (!session_start()) die ("script action '{$nome_sesion}', !session_start()");


unset($_SESSION['triwus.editor.php']);

if (isset($_GET['k']) && ($_GET['k'] == "0")) {
  unset($_SESSION['triwus.pcontrol.php']);
}



$tilia = new Tilia($nome_sesion, $tilia_home, $ehttp_inicio);

$tilia->transitar();

