<?php

error_reporting(E_ALL ^ E_NOTICE);

require "config.php";


chdir('../triwus');


$nome_sesion = "triwus.editor.php";

$tilia_home = "tilia/";
$fs_home    = "";

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

  //~ $ehttp = unserialize($_SESSION['triwus.pcontrol.php']);
  $ehttp = $_SESSION['triwus.pcontrol.php'];

  if (($u = $ehttp->usuario()) == null) return false;

  if (!$u->validado()) return false;

  return true;
}
