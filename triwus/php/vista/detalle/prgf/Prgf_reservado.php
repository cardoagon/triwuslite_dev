<?php


abstract class Prgf_reservado extends Paragrafo {

  public function __construct(Paxina_reservada_obd $p) {
    parent::__construct($p->prgf_indice(), "prgf");

    $this->alg_html_elmts = 5;
    $this->usa_bpos       = true;
    $this->excluir        = 1;
  }

  protected function pon_btnr() {
    $this->pon_obxeto(new Btnr_prgf(false));
  }

  abstract public function html_reservado();

  public static function factory(Paxina_reservada_obd $p) {
    switch ($p->atr("tipo")->valor) {
      case "almacen"    : return new Prgf_reservado_almacen   ($p);
      case "buscador"   : return new Prgf_reservado_buscador  ($p);
      case "confirmar"  : return new Prgf_reservado_confirmar ($p);
      case "login"      : return new Prgf_reservado_login     ($p);
      case "pago"       : return new Prgf_reservado_pago      ($p);
      case "pago_gpay"  : return new Prgf_reservado_pagoGpay  ($p);
      case "pago_paypal": return new Prgf_reservado_pagoPaypal($p);
      case "perfil"     : return new Prgf_reservado_perfil    ($p);
      case "producto"   : return new Prgf_reservado_producto  ($p);
    }

    return null;
  }
}

//*************************************

final class Prgf_reservado_almacen extends Prgf_reservado {
  const ptw_0 = "ptw/paragrafo/prgf_almacen.html";
  const ptw_1 = "ptw/paragrafo/prgf_marcas_0.html";

  private ?int $id_a     = null;

  private      $a        = null;
  private      $json_alm = null;

  public function __construct(Paxina_reservada_obd $p) {
    parent::__construct($p);
  }

  public function operacion(EstadoHTTP $e) {
    if (($catp = $this->obxeto("cat")) != null) {
      if (($e_aux = $catp->operacion($e)) != null) return $e_aux;
    }

    $ev = $e->evento();

//~ echo "Prgf_reservado_almacen::" .  $ev->html();

    if ($ev->tipo()  != "op-reservada") return null;
    if ($ev->nome(0) != "almacen"     ) return null;

    if ($ev->nome(1) == "t"           ) return $this->operacion_inicia_t($e);


    return $this->operacion_inicia_x($e);
  }

  public function html_reservado() {
    if ($this->a == null) return $this->html_reservado_t();

    return $this->html_reservado_x();
  }

  private function operacion_inicia_t(EstadoHTTP $e) {
    $this->a    = null;
    $this->id_a = null;

    $this->json_alm = json_encode( Almacen_obd::_almacen_2($e->id_site) );

    return $e;
  }

  private function operacion_inicia_x(EstadoHTTP $e) {
    $ev = $e->evento();

    $_param = $ev->nome(1);

    if ($this->id_a == $_param[0]) {
      if (($lpr = $this->obxeto("cat")) != null) {
        $lpr->control_url($_param);

        return $e;
      }
    }


    //* inicia almacen.
    $cbd = new FS_cbd();
    
    $this->id_a = $_param[0];

    $this->a = Almacen_obd::inicia($cbd, $e->id_site, $this->id_a);
    $this->c = $this->a->contacto_obd($cbd);

    //* actualiza a información de cabeceira da paxina para compartir en RS.
    $smt = Site_metatags_obd::inicia($cbd, $e->id_site);

    $smt->__publicar($e->paxina(), $this->a);


    //* inicia Catalogo_p.
    $lpr = new Catalogo_p( $this->a, $e->edicion(), $e->config(), "almacen.php?a={$this->id_a}");
    
    $lpr->control_url($_param);
    
//~ echo $evr->sql_vista();

    $this->pon_obxeto( $lpr ); //* engadimos primeiro o obxeto ao parágrafo.


    return $e;
  }

  private function html_reservado_t() {
    $html = LectorPlantilla::plantilla( Refs::url( self::ptw_1 ) );

    $html = str_replace("[vista]"      , 1              , $html);
    $html = str_replace("[json_marcas]", $this->json_alm, $html);
    $html = str_replace("[m]"          , "a"            , $html);

    return $html;
  }

  private function html_reservado_x() {
    if (($f = $this->c->iclogo_src(new FS_cbd())) == null) $f = Imaxe_obd::noimx;

    $pechado = ($this->a->pechado())?"initial":"none";


    $html = LectorPlantilla::plantilla( Refs::url( self::ptw_0 ) );

    $html = str_replace("[cimaxe]"       , $f                                  , $html);
    $html = str_replace("[pechado]"      , $pechado                            , $html);
    $html = str_replace("[razon_social]" , $this->c->atr("razon_social")->valor, $html);
    $html = str_replace("[notas]"        , $this->c->atr("notas")->valor       , $html);
    $html = str_replace("[email]"        , $this->c->atr("email")->valor       , $html);
    $html = str_replace("[mobil]"        , $this->c->atr("mobil")->valor       , $html);
    $html = str_replace("[web]"          , $this->c->atr("web")->valor         , $html);
    $html = str_replace("[enderezo]"     , $this->c->enderezo_obd()->resumo()  , $html);
    $html = str_replace("[num_anucios]"  , $this->a->contar()                  , $html);
    $html = str_replace("[elmtventasrel]", $this->obxeto("cat")->html()      , $html);

    return $html;
  }
}

//*************************************

final class Prgf_reservado_buscador extends Prgf_reservado {
  public function __construct(Paxina_reservada_obd $p) {
    parent::__construct($p);

    $this->pon_obxeto(new FSL_buscador());
  }

  public function operacion(EstadoHTTP $e) {
    if (($e_aux = $this->obxeto("lrb")->operacion($e)) != null) return $e_aux;

    $ev = $e->evento();

    if ($ev->tipo() != "op-reservada") return null;

    if ($ev->nome(0) != "buscador") return null;



    $a_upermisos = $e->usuario()->a_permisos();

    $this->obxeto("lrb")->__where($e->id_site, $ev->nome(1), $a_upermisos, $e->edicion(), $e->id_idioma());

//~ echo $this->obxeto("lrb")->sql_vista();


    return $e;
  }

  public function html_reservado() {
    return $this->obxeto("lrb")->html();
  }
}

//*************************************

final class Prgf_reservado_confirmar extends Prgf_reservado {
  public function __construct(Paxina_reservada_obd $p) {
    parent::__construct($p);

    $this->pon_obxeto(new CValidar_Conta( $p->atr("id_idioma")->valor ));
  }

  public function operacion(EstadoHTTP $e) {
    if ($e->edicion()) return null;
    
    return $this->obxeto("cvalidar")->operacion($e);
  }

  public function html_reservado() {
    return $this->obxeto("cvalidar")->html();
  }
}

//*************************************

final class Prgf_reservado_login extends Prgf_reservado {
  const ptw = "ptw/paragrafo/prgf_login.html";
  
  private ?string $redirect = null;
  private ?string $permiso  = null;

  public function __construct(Paxina_reservada_obd $p) {
    parent::__construct($p);
  }

  public function declara_css() {
    //~ return array(Refs::url("css/cxcli3.css"));
  }

  public function declara_js() {
    return array(Refs::url("js/cxcli3.js"));
  }

  public function config_usuario(IEfspax_xestor $e = null) {
    if (!$e->edicion()) {
      if ($e->usuario()->validado()) {
        $this->redirect = (isset($_REQUEST["redirect"]))?$_REQUEST["redirect"]:"perfil.php";
        
        return;
      }
    }


    parent::config_usuario($e);


    $sc = Site_config_obd::inicia(new FS_cbd(), $e->id_site);

    if ($sc->atr("ru_visible")->valor != 1) {
      $this->ptw = Refs::url(self::ptw);
    }
    else {
      $clw = new CLoginWeb_form($e->usuario(), $e->id_site, 0, $sc->atr("ru_registro_visible")->valor, $sc->atr("ru_diferido")->valor, $e->id_idioma());

      if (isset($_REQUEST["redirect"])) $clw->redirect_asignar( $_REQUEST["redirect"] );

      $this->pon_obxeto($clw);
    }


    $this->cambiar_idioma( Idioma::factory( $e->id_idioma() ) );
  }

  public function operacion(EstadoHTTP $e) {
    if ($this->ptw != null) return null;

    if (($clwf = $this->obxeto("clwf")) != null)
      if (($e_aux = $clwf->operacion($e)) != null) return $e_aux;

    return null;
  }

  public function html_reservado() {
    if ($this->redirect != null) {
      return "<script>document.location = '{$this->redirect}'</script>";
    }
//~ echo "_request-2::{$_REQUEST['redirect']}<br>";

    if ($this->ptw == null) {
      if ($this->obxeto("clwf") == null) {
        return "";
      }

      //* revisamos parámetros url (redirect, permiso).
      if (isset($_REQUEST["permiso"])) $this->obxeto("clwf")->permiso( $_REQUEST["permiso"] );
      
      $redirect = (isset($_REQUEST["redirect"]))?$_REQUEST["redirect"]:"perfil.php";
      
      $this->obxeto("clwf")->redirect_asignar( $redirect );


      return $this->obxeto("clwf")->html();
    }

    return LectorPlantilla::plantilla($this->ptw, $this->idioma);
  }
}

//*************************************

final class Prgf_reservado_perfil extends Prgf_reservado {
  
  private ?string $redirect = null;

  public function __construct(Paxina_reservada_obd $p) {
    parent::__construct($p);
  }

  public function config_usuario(IEfspax_xestor $e = null) {
    if (!$e->edicion()) {
      if (!$e->usuario()->validado()) {
        $this->redirect = "login.php";
        
        return;
      }
    }

    parent::config_usuario($e);

    $cbd = new FS_cbd();

    $u_site = $e->__site_obd($e->id_site, $cbd)->admin($cbd);

    $cxcli2 = new CXCli2($e->usuario(), $e->id_site, $e->id_idioma(), $u_site->venta());


    $this->pon_obxeto( $cxcli2 );
  }

  public function declara_css() {
    //~ return array(Refs::url("css/cxcli3.css"));
  }

  public function declara_js() {
    return array(Refs::url("js/cxcli3.js"));
  }

  public function operacion(EstadoHTTP $e) {
    if (!$e->usuario()->validado()) {
      $e->redirect("login.php");

      return;
    }

    if (($cxcli2 = $this->obxeto("cxcli2")) != null)
      if (($e_aux = $cxcli2->operacion($e)) != null) return $e_aux;


    return null;
  }

  public function html_reservado() {
    if ($this->redirect != null) {
      return "<script>document.location = '{$this->redirect}'</script>";
    }
    
    if (($cxcli2 = $this->obxeto("cxcli2")) == null) return "";

    return $cxcli2->html();
  }
}

//*************************************

final class Prgf_reservado_pago extends Prgf_reservado {

  private $cpagar2;

  public function __construct(Paxina_reservada_obd $p) {
    parent::__construct($p);
  }

  public function config_usuario(IEfspax_xestor $e = null) {
    parent::config_usuario($e);

    $this->cpagar2 = $e->obxeto("cpagar2");

    $this->cpagar2->cambiar_idioma( Idioma::factory( $e->id_idioma() ) );
  }

  public function operacion(EstadoHTTP $e) {
    if ($e->evento()->tipo() != "op-reservada") return null;

//~ echo "<pre>" . print_r($this->cpagar2->pedido_obd->a_resumo(), 1) . "</pre>";

    if ($e->evento()->nome(0) == "retomar-pago") return $this->operacion_pedido($e);

    return null;
  }

  public function html_reservado() {
    return $this->cpagar2->html();
  }

  private function operacion_pedido(EstadoHTTP $e) {
//~ echo "operacion_pedido<br>";

    $cbd = new FS_cbd();


    $c = Cambio_obd::inicia($e->evento()->nome(1), $cbd); //* Cambio_pago_obd


    if ($c == null) return $e;

    if (!$c->pdte()) return $e;

    $c->post_u( $e->usuario() );


    $cbd->transaccion();

    if (!$c->click($cbd)) {
      $cbd->rollback();

      return $e;
    }


    $this->cpagar2->config_usuario( $e );


    return $e;
  }
}

//*************************************

final class Prgf_reservado_pagoPaypal extends Prgf_reservado {

  const ptw_0 = "ptw/paragrafo/prgf_paypal2_0.html";
  
  //~ private bool $valido = false;

  private ?string    $kped = null;
  private Pedido_obd $ped;
  private PayPal_2   $paypal;

  public function __construct(Paxina_reservada_obd $p) {
    parent::__construct($p);
    
    $this->pon_obxeto(new Hidden("h0")); //* canal de comunicación.
    
    $this->obxeto("h0")->pon_atr("paypal_evento", "1");
  }
  
  public function declara_js() {
    if ( !$this->pai->__editor ) return array( Refs::url("js/paypal.js?v=122") );
  }
  
  public function config_usuario(IEfspax_xestor $e = null) {
    parent::config_usuario($e);
    
    $this->kped = null;
    
    if (!isset( $_REQUEST["kped"] )) return;
    
    $this->kped = $_REQUEST["kped"];

    $s = $e->id_site; $_p = explode("-", AES_256_CBC::descifrar($this->kped));
    
//~ echo "<pre>" . print_r($_p, 1) . "</pre>";
    
    $cbd = new FS_cbd();
    
    $this->ped = Pedido_obd::inicia( $cbd, $s, $_p[0], $_p[1] );
    
    if ( $this->ped->baleiro() ) return;
    
    
    $this->paypal = new PayPal_2( Site_fpago_obd::inicia($cbd, $s, 4) );
    
//~ echo "<pre>" . print_r($this->ped->a_resumo(), 1) . "</pre>";


    //~ $this->valido = true;
  }

  public function operacion(EstadoHTTP $e) {

//~ echo $e->evento()->html();

    if (($e_aux = $this->operacion_h0($e)) != null) return $e_aux;


    return null;
  }

  public function html_reservado() {
    if ( $this->kped == null ) return "";
    
    
    if ( $this->pai->__editor ) return "<h1>Espacio reservado para el formulario de pago de Paypal Advanced Checkout.</h1>";
    
    
    $_html = $this->_html_parse();
    
    $html = LectorPlantilla::plantilla( Refs::url( self::ptw_0 ) );
    
    $html = str_replace("[h0]"         , $this->obxeto("h0")->html()       , $html);
    $html = str_replace("[pvp]"        , $_html["pvp"]                     , $html);
    $html = str_replace("[pedido]"     , print_r($this->ped->a_resumo(), 1), $html);
    $html = str_replace("[paypal_kpub]", $this->paypal->kpub               , $html);
        
    return $html;
  }

  private function operacion_h0(EstadoHTTP $e) {
    if ( !$this->obxeto("h0")->control_evento() ) return null;

//~ echo "<pre>" . print_r($_REQUEST, 1) . "</pre>";

    if (empty($_REQUEST["payment_op"])) {
      $e->ajax()->pon_ok(true); //* deberiamos mostrar un erro ?
      
      return $e;
    }
    
    
    if ($_REQUEST["payment_op"] == "create_order" ) return $this->operacion_h0_create ($e);
    if ($_REQUEST["payment_op"] == "capture_order") return $this->operacion_h0_capture($e);

    $e->ajax()->pon_ok(true); //* deberiamos mostrar un erro ?

    return $e;
  }

  private function operacion_h0_create(EstadoHTTP $e) {
    $payment_source = $_REQUEST['payment_source']; 

    // Create order with PayPal Orders API 
    try { 
        $order = $this->paypal->createOrder($this->ped, $payment_source); 
    } 
    catch(Exception $exc) {  
        $api_error = $exc->getMessage();  
    } 
     
    if(!empty($order)){ 
        $response = array( 
            'status' => 1,  
            'data' => $order 
        ); 
    }
    else{ 
        $response['msg'] = $api_error; 
    } 

    $e->ajax()->pon_ok(true, $response);

    return $e;
  }

  private function operacion_h0_capture(EstadoHTTP $e) {
    $response  = $this->_response(0);  //* response erro (status == 0).
    
    $api_error = '';
    $order_id  = $_REQUEST["order_id"]; 
 
    //* Capture order with PayPal Orders API 
    try { 
      $order = $this->paypal->captureOrder($order_id); 
    } 
    catch(Exception $exc) {  
      $api_error = $exc->getMessage();  
    } 

    if (empty($order)) {
      //* envía respuesta KO.
      $response = $this->_response(0, "ERROR. operacion_h0_capture().<br>{$api_error}"); 
      
      $e->ajax()->pon_ok(true, $response);

      return $e;
    }

//~ print_r($order);

    
    //* control order status. 
    $order_id     = $order["id"    ]; 
    $order_status = $order["status"]; 

    if ($order_status != "COMPLETED") {
      //* envía respuesta KO.
      $response = $this->_response(0, "ERROR. operacion_h0_capture(). order_status != 'COMPLETED'.");
      
      $e->ajax()->pon_ok(true, $response);

      return $e;
    }

    $payment_source = $payment_source_card_name = $payment_source_card_last_digits = $payment_source_card_expiry = $payment_source_card_brand = $payment_source_card_type = $payment_source_paypal_email = $payment_source_paypal_id = $payment_source_paypal_status = null; 
    if(!empty($order["payment_source"])){ 
      foreach($order["payment_source"] as $key=>$value){ 
        $payment_source = $key; 

        if($payment_source == "card"){ 
          $payment_source_card_name        = $value["name"]; 
          $payment_source_card_last_digits = $value["last_digits"]; 
          $payment_source_card_expiry      = $value["expiry"]; 
          $payment_source_card_brand       = $value["brand"]; 
          $payment_source_card_type        = $value["type"]; 
        } 
        elseif($payment_source == "paypal"){ 
          $payment_source_paypal_email   = $value["email_address"]; 
          $payment_source_paypal_id      = $value["account_id"]; 
          $payment_source_paypal_status  = $value["account_status"]; 
        } 
      } 
    } 
    

    $transaction_id = $payment_status = $custom_id = $amount_value = $currency_code = $create_time = null; 
    if(!empty($order["purchase_units"][0])){ 
      $purchase_unit = $order["purchase_units"][0]; 
      if(!empty($purchase_unit["payments"])){ 
        $payments = $purchase_unit["payments"]; 
        if(!empty($payments["captures"])){ 
          $captures = $payments["captures"]; 
          if(!empty($captures[0])){ 
            $transaction_id = $captures[0]["id"]; 
            $payment_status = $captures[0]["status"]; 
            $custom_id      = $captures[0]["custom_id"]; 
            $amount_value   = $captures[0]["amount"]["value"]; 
            $currency_code  = $captures[0]["amount"]["currency_code"]; 
            $create_time    = date("Y-m-d H:i:s", strtotime($captures[0]["create_time"])); 
            $update_time    = date("Y-m-d H:i:s", strtotime($captures[0]["update_time"])); 
          } 
        } 
      } 
    } 
    
    //* actualiza BD.
    $cbd = new FS_cbd();
    
    $rpc_obd = PayPal_2_obd::inicia($cbd, $transaction_id);

    if (!$rpc_obd->baleiro()) {
      //* envía respuesta OK. ¿¿¿ actualizar ???
      $e->ajax()->pon_ok(true, $this->_response(1));


      return $e;
    }

    $rpc_obd->atr("id_site"                        )->valor = $this->ped->atr("id_site")->valor;
    $rpc_obd->atr("ano"                            )->valor = $this->ped->atr("ano"    )->valor;
    $rpc_obd->atr("numero"                         )->valor = $this->ped->atr("numero" )->valor;
    $rpc_obd->atr("order_id"                       )->valor = $order_id                        ;    
    $rpc_obd->atr("payment_source"                 )->valor = $payment_source                  ;
    $rpc_obd->atr("payment_source_card_name"       )->valor = $payment_source_card_name        ;
    $rpc_obd->atr("payment_source_card_last_digits")->valor = $payment_source_card_last_digits ;
    $rpc_obd->atr("payment_source_card_expiry"     )->valor = $payment_source_card_expiry      ;
    $rpc_obd->atr("payment_source_card_brand"      )->valor = $payment_source_card_brand       ;
    $rpc_obd->atr("payment_source_card_type"       )->valor = $payment_source_card_type        ;
    $rpc_obd->atr("payment_source_paypal_email"    )->valor = $payment_source_paypal_email     ;
    $rpc_obd->atr("payment_source_paypal_id"       )->valor = $payment_source_paypal_id        ;
    $rpc_obd->atr("payment_source_paypal_status"   )->valor = $payment_source_paypal_status    ;
    $rpc_obd->atr("transaction_id"                 )->valor = $transaction_id                  ;
    $rpc_obd->atr("payment_status"                 )->valor = $payment_status                  ;
    $rpc_obd->atr("custom_id"                      )->valor = $custom_id                       ;
    $rpc_obd->atr("amount_value"                   )->valor = $amount_value                    ;
    $rpc_obd->atr("currency_code"                  )->valor = $currency_code                   ;
    $rpc_obd->atr("created"                        )->valor = $create_time                     ; 
    $rpc_obd->atr("modified"                       )->valor = $update_time                     ; 

//~ print_r($rpc_obd->a_resumo());

    $cbd->transaccion();

    try {
      if (!$rpc_obd->insert($cbd)) {
        //* envía respuesta KO.
        $cbd->rollback();
        
        $response = $this->_response(0, "ERROR. operacion_h0_capture(). !rpc_obd->insert().");
      
        $e->ajax()->pon_ok(true, $response);
        

        return $e;
      }
    }
    catch (Exception $exc) {
      //* envía respuesta KO.
      $cbd->rollback();
        
      $response = $this->_response(0, "ERROR. " . $exc->getMessage());
      
      $e->ajax()->pon_ok(true, $response);

      return $e;
    }

    //* envía respuesta OK.
    $cbd->commit();

    $e->ajax()->pon_ok(true, $this->_response(1));
      
    return $e;
  }

  private function _response(int $status, string $msx = null):array {
    return ["status" => $status, "kped" => $this->kped, "msx" => $msx];
  }

  private function _html_parse():array {
    $_html = [];
    
    $_html["pvp"] = number_format($this->ped->atr("tpvpive")->valor, 2, ",", ".") . "€";
    
    
    return $_html;
  }
}

//*************************************

final class Prgf_reservado_pagoGpay extends Prgf_reservado {

  const ptw_0 = "";

  private $p;

  public function __construct(Paxina_reservada_obd $p) {
    parent::__construct($p);
  }
  
  public function declara_js() {
    if ( !$this->pai->__editor ) return array( Refs::url("js/googlePay.js") );
  }
  
  public function config_usuario(IEfspax_xestor $e = null) {
    parent::config_usuario($e);

    $s = $e->id_site; list($a, $n) = explode("-", AES_256_CBC::descifrar($_REQUEST["gpay"]));
    
    $this->p = Pedido_obd::inicia(new FS_cbd(), $s, $a, $n);

//~ echo "111111111111::{$this->a}, {$this->n}::<br>";
  }

  public function operacion(EstadoHTTP $e) {
    if ($e->evento()->tipo() != "op-reservada") return null;

    if ($e->evento()->nome(0) != "pagoGpay"   ) return null;


    return null;
  }

  public function html_reservado() {
    if ( $this->pai->__editor ) return "<h1>Espacio reservado para el formulario de pago de Google Pay</h1>";
    
    return "
<div style=' min-height: 11em;'>
  <div style='margin: 3em 0;text-align: left;'><pre>" . print_r($this->p->a_resumo(), 1) . "</pre></div>
  <div id='container'></div>
  <script async
          src    = \"https://pay.google.com/gp/p/js/pay.js\"
          onload = \"onGooglePayLoaded()\"></script>
</div>
";
  }

}

//*************************************

final class Prgf_reservado_producto extends Prgf_reservado {
  private $id_articulo;

  public function __construct(Paxina_reservada_obd $p) {
    parent::__construct($p);
    
    $this->pon_obxeto( self::hproducto() );
  }

  public function operacion(EstadoHTTP $e) {
    $ev = $e->evento();

//~ echo $ev->html();

//~ echo $this->id_articulo . "::" . $this->obxeto("elmt", "p")->id_articulo . "::" . $_REQUEST[ "trw_prgfProducto_0" ] . "\n";
    
    //*** comprobamos evento .
    if (($e_aux = $this->operacion_hproducto($e)) != null) return $e_aux;

    if (($e_aux = $this->operacion_producto ($e)) != null) return $e_aux;



    //*** inicia prgf (1ª vez).
    
    //* valida evento: "solicitude de inicio de prgf.
    if ($ev->tipo() != "op-reservada") return null;

    if ($ev->nome(0) != "producto") {
      $e->redirect("index.php");

      return $e;
    }

    if ($ev->nome(1) == null) {
      $e->redirect("index.php");

      return $e;
    }
	
	
    //* inicia prgf_producto.
    $cbd = new FS_cbd();

    $a = $this->inicia_producto($e->id_site, $e->config(), $ev->nome(1), $cbd);

    if ($a == null) {
      $e->redirect("index.php");

      return $e;
    }
//~ echo "<pre>" . print_r($a->a_resumo(), 1) . "</pre>";

    //* actualiza a información de cabeceira da paxina (head).
    $smt = Site_metatags_obd::inicia($cbd, $e->id_site);

    $smt->__publicar($e->paxina(), $a);


    return $e;
  }

  public function html_reservado() {
    if (($h = $this->obxeto("hproducto")) == null) return "";
    
    if (($p = $this->obxeto("elmt", "p")) == null) return "";
    //~ if (($d = $this->obxeto("elmt", "d")) == null) return "";

    //~ return "
//~ <input type='hidden' id='trw_prgfProducto_0' value='{$this->id_articulo}' />
//~ <div style='display: grid; grid-template-columns: 100%; margin-bottom:3em;'>
//~ " . $p->html() . $d->html() . "
//~ </div>";

    return "
" . $h->html() . "
<input type='hidden' id='trw_prgfProducto_0' value='{$this->id_articulo}' />
<div style='display: grid; grid-template-columns: 100%; margin-bottom:3em;'>" . $p->html() . "</div>";
  }



  private function operacion_hproducto(EstadoHTTP $e) {
    if (!$this->obxeto("hproducto")->control_evento()) return null;
    
    $id_articulo = $this->obxeto("hproducto")->valor();

    $cbd = new FS_cbd();

    $a = Articulo_obd::inicia($cbd, $e->id_site, $id_articulo);


    $url = ( !$a->publicado($cbd) )?"index.php":$a->action(true);
    

    $e->redirect($url);


    return $e;
  }



  private function operacion_producto(EstadoHTTP $e) {
//~ echo "<br><br><br><br><br><br>{$_REQUEST["trw_prgfProducto_0"]} == $this->id_articulo<br";
    if (($elmt_p = $this->obxeto("elmt", "p")) == null) return null;

    //* control lapela duplicada.
    if (!isset($_REQUEST["trw_prgfProducto_0"])) return $elmt_p->operacion($e);

//~ echo "11111111111111111111111111111111<br>";
        
    if ($_REQUEST["trw_prgfProducto_0"] == $this->id_articulo) return $elmt_p->operacion($e);
      
    
    //* lapela duplicada. reinicia elmt_p.
    $a = $this->inicia_producto($e->id_site, $e->config(), $_REQUEST["trw_prgfProducto_0"]);

    if ($a == null) $e->redirect("index.php");
    
    
    $e->ajax()->pon_ok();

    return $e; 
  }

  private function inicia_producto(int $id_site, Config_obd $c, ?int $id_articulo, FS_cbd $cbd = null):?Articulo_obd {
    $this->id_articulo = $id_articulo;

    if ($cbd == null) $cbd = new FS_cbd();

    $a = Articulo_obd::inicia($cbd, $id_site, $this->id_articulo);


    if (($n = $a->atr("nome")->valor) == null) return null;
    if (!$a->publicado($cbd)                 ) return null;


    $d = true;
    //~ $m = $ev->nome(0) != "producto";
    $m = true;


    //* inicia elmt_ventas
    $elmt_p = new Elmt_ventas_clr  ($a, $c, $d, $m, $cbd);

    $this->pon_obxeto($elmt_p, "p");


    //* inicia difucon_2
    /*
    $r = Paxina_reservada_obd::action_producto($this->id_articulo, $n, $e->url_limpa);

    $elmt_d = new Elmt_difucom2_reservada( $r );

    $elmt_d->config_usuario($e);

    $this->pon_obxeto($elmt_d, "d");
    */


    return $a;
  }
  
  private static function hproducto():Hidden {
    $o = new Hidden("hproducto");
    
    $o->pon_atr("trw_prgfProducto_p", "1");
    
    
    return $o;
  }
}
