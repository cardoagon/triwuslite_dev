<?php

error_reporting(E_ALL ^ E_NOTICE ^ E_STRICT);

$fs_home     = "../triwus/";
$tilia_home  = "{$fs_home}tilia/";

require "{$tilia_home}manifesto.php";
require "{$fs_home}php/modelo/FS_cbd.php";
require "CNotas.php";


//**************************************

final class CNotas_aa extends ActionAJAX {
  public function __construct() {
    parent::__construct( new CNotas($_GET['n1'], $_GET['n2']) );
  }
}

//*********************************


$nome_sesion  = "trw.notifica.{$_GET['n1']}";
$aa           = "CNotas_aa";

if (!session_start()) die ("script action '{$nome_sesion}', !session_start()");


$tilia = new Tilia($nome_sesion, $tilia_home, $aa);

$tilia->transitar();

