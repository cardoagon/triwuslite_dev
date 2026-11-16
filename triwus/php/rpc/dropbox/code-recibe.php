<?php

error_reporting(E_ALL ^ E_NOTICE);


$site_home  = "../../..";
$trw_home   = "{$site_home}/";
$tilia_home = "{$trw_home}tilia/";


require "{$tilia_home}manifesto.php";
require "{$trw_home}manifesto.php";
require "{$trw_home}manifesto_adminsite.php";



$nome_sesion = "triwus.pcontrol.php";

if (!session_start()) die ("script action 'php/rpc/dropbox/code-recibe.php', !session_start()");



//------------------------------------------------

$response = -1;

$epcontrol = $_SESSION[$nome_sesion];

if ($epcontrol != null) {
  $capidbox = $epcontrol->obxeto("capidbox");
  
  if ($capidbox != null) {  
    $capidbox->dropbox->post_token( $_REQUEST["code"] );

    $response = 1;
    
    $_SESSION[$nome_sesion] = $epcontrol;
  }
}



echo
"
<script>
//~ console.log( window.opener );
//~ console.log( window );
  window.opener.location = 'http://localhost/triwus/pcontrol.php?r=bapis_dbox&dropbox_token={$response}';
  
  window.close();
</script>";


/*
echo
"
<script>
  document.location = 'http://localhost/triwus/pcontrol.php?r=bapis_dbox&dropbox_token={$response}';
</script>";
*/
