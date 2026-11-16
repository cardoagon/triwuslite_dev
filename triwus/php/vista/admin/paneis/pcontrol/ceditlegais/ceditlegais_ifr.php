<?php

$nome_sesion = "triwus.pcontrol.php";

$fs_home = "../../../../../../";
$tilia_home = "{$fs_home}tilia/";


echo "
<script src=\"{$tilia_home}/js/basico.js\"></script>
<script src=\"{$fs_home}/js/nicEdit.js\"></script>
";

require "{$tilia_home}manifesto.php";
require "{$fs_home}manifesto.php";
require "{$fs_home}manifesto_adminsite.php";

if (!session_start()) die ("index.php, !session_start()");


$ehttp = $_SESSION[$nome_sesion];

$ceditor = $ehttp->obxeto("cel")->obxeto("ceditor");

echo $ceditor->html();
?>

<script>
  function ceditlegais_pecha() {
    nicedit_pecha("findex;cel;ceditor;texto");
  }
</script>