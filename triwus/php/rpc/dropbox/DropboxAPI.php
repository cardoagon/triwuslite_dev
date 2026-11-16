<?php



abstract class DropboxAPI {
  public ?object $otoken = null;

  public string $kpub  = "";
  public string $kpri  = "";

  public function __construct(string $kpub, string $kpri) {
    $this->kpub  = $kpub;
    $this->kpri  = $kpri;
  }

  public function oauth_code():string {
    $url = "https://www.dropbox.com/oauth2/authorize?client_id={$this->kpub}&response_type=code&token_access_type=online&redirect_uri=" . $this->oauth_urlRedirect;


    return $url;
  }

  abstract public function oauth_urlRedirect():string;

  public function post_token($code) {
    $_arg = ["code"          => $code,
             "client_id"     => $this->kpub,
             "client_secret" => $this->kpri,
             "redirect_uri"  => $this->oauth_urlRedirect(),
             "grant_type"    => "authorization_code"
            ];

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, "https://api.dropbox.com/oauth2/token");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $_arg);

    $response = curl_exec($ch);

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

//~ echo  $httpCode . "<br>";

    if ($httpCode == 200) {
      $this->otoken = json_decode( $response );
    }
    else {
      //~ echo "Se ha producido un error al descargar el archivo: " . $httpCode . "<br><br>". $response;
      throw new Exception("Se ha producido un error al descargar el archivo: " . $httpCode . "<br><br>". $response);
    }

    curl_close($ch);
  }

  public function listFiles($folder = null):?array {
       $token = $this->otoken->access_token;


      $headers = array(
          "Authorization: Bearer " . $token,
          "Content-Type: application/json",
      );

      $params = array(
          'path' => '/crcc'     );

      $url = 'https://api.dropboxapi.com/2/files/list_folder';

      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_POST, true);
      curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
      curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

      curl_close($ch);

      $response = curl_exec($ch);

      if ($response === false) {
          throw new Exception( 'Error de cURL: ' . curl_error($ch) );
          
          return null;
      } 
//~ echo $response;
      
      return json_decode($response, true);
  }
/*
  public function downloadFile($filePath) {
      $headers = array(
          "Authorization: Bearer " . $this->token,
          "Dropbox-API-Arg: {\"path\":\"" . $filePath . "\"}",
          "Content-Type: application/octet-stream",
          "Accept: application/octet-stream"
      );

      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, "https://content.dropboxapi.com/2/files/download");
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_POST, true);
      curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);


      $response = curl_exec($ch);

      $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);


      if ($httpCode == 200) {
          return $response;
      } else {
        throw new Exception("Se ha producido un error al descargar el archivo: " . $httpCode . "<br><br>". $response);
      }

      curl_close($ch);
  }

  public function uploadFile($localFilePath, $uploadPath) {
      $fileContent = file_get_contents($localFilePath);

      $headers = array(
          "Authorization: Bearer " . $this->token,
          "Content-Type: application/octet-stream",
          "Dropbox-API-Arg: {\"path\":\"" . $uploadPath . "\"}"
      );

      $ch = curl_init("https://content.dropboxapi.com/2/files/upload");
      curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
      curl_setopt($ch, CURLOPT_POST, true);
      curl_setopt($ch, CURLOPT_POSTFIELDS, $fileContent);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

      $response = curl_exec($ch);

      $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

      if ($httpCode == 200) {
          $info = json_decode($response, true);
          return $info;

      } else {
          throw new Exception("Error al subir el archivo: " . $httpCode);
      }

      curl_close($ch);
  }
*/
}

//*********************************

final class DropboxTRW extends DropboxAPI {
  private int $id_site = -1;

  private function __construct(int $id_site, array $_dropbox) {
    parent::__construct($_dropbox["dropbox_u"], $_dropbox["dropbox_k"]);

    $this->id_site = $id_site;
  }

  public static function inicia(string $id_site, FS_cbd $cbd = null):?DropboxTRW {
    $_dropbox = Site_config_obd::_dropbox($id_site, $cbd);

    if ($_dropbox == []) return null;


    $o = new DropboxTRW($id_site, $_dropbox);

    return $o;
  }

  public function oauth_urlRedirect():string {
    return "http://localhost/triwus/php/rpc/dropbox/code-recibe.php";
  }
}

