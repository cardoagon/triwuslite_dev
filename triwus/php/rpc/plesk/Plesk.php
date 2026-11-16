<?php

abstract class Plesk {
  public $status  = null;
  public $errcode = null;
  public $errtext = null;

  protected $curl = false;

  public function __construct() {
    $this->curl = $this->curlInit("46.231.5.121", "admin", '2a:ngW44C?5LCw');
  }

  public function close() {
    curl_close($this->curl);
  }

  protected function sendRequest($packet) {
    try {
      $this->status  = null;
      $this->errcode = null;
      $this->errtext = null;

      if (!$this->curl) { throw new Exception( 'Curl Not Initalised'); }
      curl_setopt($this->curl, CURLOPT_POSTFIELDS, $packet);
      $result = curl_exec($this->curl);
      
      if (curl_errno($this->curl)) {
        $errmsg  = curl_error($this->curl);
        $errcode = curl_errno($this->curl);

        throw new Exception($errmsg." : ".$errcode);
      }

      return $result;
    }
    catch (Exception $e){
      echo $e;

      return false;
    }
  }

  protected function parseResponse($response_string) {
    try {
      $xml = new SimpleXMLElement($response_string);
      if (!is_a($xml, 'SimpleXMLElement')) {  throw new Exception( 'Malformed SimpleXML Element');  }

        try {

          $resultNode = $xml->xpath("//*[text()[contains(., 'error')]]") ;
          if($resultNode){
            $resultNode = $xml->xpath("//*/result") ;

            $this->status  = $resultNode[0]->status;
            $this->errcode = $resultNode[0]->errcode;
            $this->errtext = $resultNode[0]->errtext;

            return -1;
          }
        }
        catch (Exception $e){
          exit($e);
        }

        return $xml;
     }
    catch (Exception $e){
      exit($e);
    }
  }

  protected function checkError($response_string) {
    try {
      $xml = new SimpleXMLElement($response_string);
      if (!is_a($xml, 'SimpleXMLElement'))
        throw new Exception( 'Malformed SimpleXML Element');
          return $xml;
     }
    catch (Exception $e){
        echo $e;
        return false;
    }
  }

  private function curlInit($host, $login, $password) {
    try {
      $curl = curl_init();
      if(!curl_setopt($curl, CURLOPT_URL, "https://{$host}:8443/enterprise/control/agent.php")){ throw new Exception( 'Problem With CURLOPT_URL');  };
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($curl, CURLOPT_POST,           true);
      curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
      curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
      if(!curl_setopt($curl, CURLOPT_HTTPHEADER,
         array("HTTP_AUTH_LOGIN: {$login}",
            "HTTP_AUTH_PASSWD: {$password}",
            "HTTP_PRETTY_PRINT: TRUE",
            "Content-Type: text/xml")
      )){ throw new Exception( 'Problem With CURLOPT_HTTPHEADER');  }

      return $curl;
    }
    catch (Exception $e) {
      return false;
    }
  }
}
