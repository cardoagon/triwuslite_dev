<?php

error_reporting(E_ALL ^ E_WARNING ^ E_NOTICE);

//~ if (!isset($_SERVER['HTTPS'])) {
  //~ $qs = ($_SERVER['QUERY_STRING'] == null)?"":"?{$_SERVER['QUERY_STRING']}";

  //~ header("Location: https://triwus.com/editor.php{$qs}");
//~ }



$nome_sesion = "triwus.editor.php";

putenv("pcontrol=1");

$fs_home    = "";
$tilia_home = "tilia/";

$ehttp_inicio = "Epaxina_edit";

require "{$tilia_home}manifesto.php";
require "manifesto.php";
require "manifesto_adminsite.php";



if (!session_start()) die ("script action '{$nome_sesion}', !session_start()");


if (!control_sesion()) {
  header("Location: pcontrol.php?k=0");

  die();
}


$tilia = new Tilia($nome_sesion, $tilia_home, $ehttp_inicio);

$tilia->transitar();


//******************************

function control_sesion() {
  if (!isset($_SESSION['triwus.pcontrol.php'])) return false;
  
  if ($_SESSION['triwus.pcontrol.php'] == null) return false;

  if (($u = $_SESSION['triwus.pcontrol.php']->usuario()) == null) return false;

  if (!$u->validado()) return false;


  return true;
}
