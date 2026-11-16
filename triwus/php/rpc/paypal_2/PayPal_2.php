<?php

final class PayPal_2 {
  const log        = "paypal_2-log.txt";
                   
  const APIauth    = 'https://api-m.paypal.com/v1/oauth2/token';
  const API        = 'https://api-m.paypal.com/v2/checkout';
  
  const sb_APIauth = 'https://api-m.sandbox.paypal.com/v1/oauth2/token';
  const sb_API     = 'https://api-m.sandbox.paypal.com/v2/checkout';


  public bool   $sandbox = true;
  
  public string $kpub;
  public string $kpri;
 
  public function __construct(Site_fpago_obd $sfp) {
    $_sfp = $sfp->info();
    
    $this->sandbox = $_sfp["test"] == 1;
    
    $this->kpub = $_sfp["config"]["kpub"];
    $this->kpri = $_sfp["config"]["kpri"];
  }

  public function generateAccessToken(){ 
    $ch = curl_init();  
    curl_setopt($ch, CURLOPT_URL           , ($this->sandbox) ? self::sb_APIauth : self::APIauth );  
    curl_setopt($ch, CURLOPT_HEADER        , false);  
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);  
    curl_setopt($ch, CURLOPT_POST          , true);  
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);  
    curl_setopt($ch, CURLOPT_USERPWD       , $this->kpub.":".$this->kpri);  
    curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials");  
    $auth_response = json_decode(curl_exec($ch)); 
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE); 
    curl_close($ch);  

    if ($http_code != 200 && !empty($auth_response->error)) {  
      throw new Exception('Failed to generate Access Token: '.$auth_response->error.' >>> '.$auth_response->error_description);  
    } 
      
    if(!empty($auth_response)) return $auth_response->access_token;  
    
    throw new Exception('Failed to generate Access Token. empty(auth_response): '.$auth_response->error.' >>> '.$auth_response->error_description);

    return false;
  } 

  public function createOrder(Pedido_obd $ped, $paymentSource) { 
    $accessToken = $this->generateAccessToken(); 
    
    if (empty($accessToken)) return false;  
    
    $postParams = array( 
        "intent" => "CAPTURE", 
        "purchase_units" => array( 
            array( 
                "custom_id"   => $ped->atr("id_site")->valor . "-" . $ped->atr("ano")->valor . "-" . $ped->atr("numero")->valor, 
                "description" => "Carrito de la compra", 
                "amount" => array( 
                    "currency_code" => "EUR", 
                    "value" => floatval($ped->atr("tpvpive")->valor) + floatval($ped->atr("tpvp_plus_ive")->valor) + floatval($ped->atr("portes")->valor)
                ) 
            ) 
        ) 
    ); 
    
    
    $api = ($this->sandbox) ? self::sb_API : self::API;

    $ch = curl_init(); 
    curl_setopt($ch, CURLOPT_URL, $api.'/orders/'); 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);  
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Authorization: Bearer '. $accessToken));  
    curl_setopt($ch, CURLOPT_POST, true); 
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postParams));  
    
    $api_resp = curl_exec($ch); 
    $api_data = json_decode($api_resp, true); 
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);  
    curl_close($ch); 

    if ($http_code != 200 && $http_code != 201) {  
      throw new Exception('Failed to create Order ('.$http_code.'): '.$api_resp);  
    } 

    return !empty($api_data) && ($http_code == 200 || $http_code == 201)?$api_data:false; 
  } 
 
  public function captureOrder($orderId){ 
    $accessToken = $this->generateAccessToken(); 
    
    if(empty($accessToken)) return false;  
    

    $api = ($this->sandbox) ? self::sb_API : self::API;

    $ch = curl_init(); 
    curl_setopt($ch, CURLOPT_URL, $api.'/orders/'.$orderId.'/capture'); 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);  
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Authorization: Bearer '. $accessToken));  
    curl_setopt($ch, CURLOPT_POST, true); 
    
    $api_resp = curl_exec($ch); 
    $api_data = json_decode($api_resp, true); 
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);  
    curl_close($ch); 

    if ($http_code != 200 && $http_code != 201) {  
      throw new Exception('Failed to create Order ('.$http_code.'): '.$api_resp);  
    } 

    return !empty($api_data) && ($http_code == 200 || $http_code == 201)?$api_data:false; 
  } 


  private static function log($m) {
    $fp = fopen(self::log, "a");

    $m = "\n" . date("YmdHis") . "\n{$m}\n\n";

    fwrite($fp, $m);

    fclose($fp);
  }
}
