<?php


$fs_home    = "../../triwus/"; 
$tilia_home = "{$fs_home}tilia/";
$subs_home  = "{$fs_home}php/trw-subs/";


require "{$tilia_home}manifesto.php";
require "{$fs_home}manifesto.php";


require "{$subs_home}php/control/ESubs.php";

require "{$subs_home}php/vista/paneis/SubsPcab.php";
require "{$subs_home}php/vista/paneis/SubsPpe.php";
require "{$subs_home}php/vista/paneis/SubsPcentro.php";

require "{$subs_home}php/vista/componentes/SubsClista.php";
require "{$subs_home}php/vista/componentes/SubsBotonera.php";
require "{$subs_home}php/vista/componentes/SubsBusca.php";
require "{$subs_home}php/vista/componentes/SubsCSV.php";
require "{$subs_home}php/vista/componentes/SubsFicha.php";
require "{$subs_home}php/vista/componentes/SubsAManten.php";

