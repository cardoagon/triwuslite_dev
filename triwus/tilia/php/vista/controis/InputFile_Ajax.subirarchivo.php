<?php

//~ echo "<pre>" . print_r($_SERVER, 1) . "</pre>";

if (array_key_exists('HTTP_X_FILE_NAME', $_SERVER) && array_key_exists('CONTENT_LENGTH', $_SERVER)) {
  $contentLength = $_SERVER['CONTENT_LENGTH'];
}
else {
  throw new Exception("Error retrieving headers");
}

if (!$contentLength > 0) {
  throw new Exception('No file uploaded!');
}

$ruta_destino = base64_decode($_GET["path"]) . "-" . $_GET['i'];

//~ echo $ruta_destino . "<br>";

file_put_contents($ruta_destino, file_get_contents("php://input"));
