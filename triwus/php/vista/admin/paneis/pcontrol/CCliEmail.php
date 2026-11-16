<?php

final class CCliEmail extends Componente implements ICPC {
  const ptw_0 = "ptw/paneis/pcontrol/cliemail/ccliemail_0.html";

  private $id_site  = null;
  private $cm_k     = null;


  public function __construct(FGS_usuario $u) {
    parent::__construct("ccliemail", self::ptw_0);

    $this->id_site = $u->atr("id_site")->valor;

    // novos campos en táboa site_config
    $this->pon_obxeto( new Text("cm_url"));
    $this->pon_obxeto( self::_sSeguridad("cm_seguridad", "SSL/TLS"));
    $this->pon_obxeto( new Text("cm_puerto"));
    $this->pon_obxeto( new Text("cm_email"));
    $this->pon_obxeto( Panel_fs::__password("cm_k"));

    $this->pon_obxeto( new Text("cm_nombre")); 

    $this->pon_obxeto( Panel_fs::__baceptar());      // [bAceptar]
    $this->pon_obxeto( Panel_fs::__bcancelar() );    // [bCancelar]
    //~ $this->pon_obxeto( Panel_fs::__benviar("bProbar", "Probar", $title = "Probar la configuración SMTP") ); 

    $this->post_obd();

  }

  public function cpc_miga() {
    return "Cliente SMTP";
  }

  public function operacion(EstadoHTTP $e) {
    if ($this->obxeto("bAceptar" )->control_evento()) return $this->operacion_baceptar ($e);
    if ($this->obxeto("bCancelar")->control_evento()) return $this->operacion_bcancelar($e);
    //~ if ($this->obxeto("bProbar"  )->control_evento()) return $this->operacion_bprobar  ($e);
  
    return null;
  }

  private function operacion_bcancelar(Epcontrol $e) {
    $this->post_obd();

    return $e;
  }

  private function operacion_baceptar(Epcontrol $e) {
    //~ $this->obxeto("bProbar")->visible = false;
    
    if (($erro = $this->__validar()) != null) {
      $e->post_msx($erro);

      return $e;
    }


    $cbd = new FS_cbd();
    
    $cbd->transaccion();

    if ($this->update($cbd)) { 
      if ( ($erro = self::__bprobar($cbd)) == null ) {
        $cbd->commit();

        $e->post_msx("Cliente SMTP OK", "m");        
     }
      else {
        $cbd->rollback();

        $e->post_msx($erro);        
      }
      
    }
    else {
      $cbd->rollback();

      $e->post_msx("ERROR, sucedeu un erro ao actualizar a BD.");
    }

    
    return $e;
  }

  private function __bprobar(FS_cbd $cbd):?string {
    $m = new XMail3($this->id_site, null, $cbd);
    
    $_x = $m->_config();
   
    try {
      $m->pon_rmtnt($_x["email"]);
      
      $m->pon_direccion($_x["email"]);
      
      $m->pon_asunto("test");
      
      $m->pon_corpo("Test triwus cliente SMTP a " . date("Y-m-d H:i:s") . ".");

      $m->enviar();
    }
    catch (Exception $exc) {
      $msx = "Cliente SMTP KO:<br><br>" . $exc->getMessage();

      return $msx;
    }
    
    return null;
  }

  private function update(FS_cbd $cbd) {
  
    $sc_obd = $this->obd(); 
    
    return $sc_obd->update($cbd);
  }

  private function __validar() {

    $erro = null;

    $email = $this->obxeto("cm_email")->valor();
    $puerto= trim($this->obxeto("cm_puerto")->valor());
    
    if (($cm_k = trim($this->obxeto("cm_k")->valor())) == ""  ) $cm_k = $this->cm_k;

    if (trim($this->obxeto("cm_url")->valor()) == "") $erro .= "Error. Servidor requerido<br>";
    if ($cm_k                                  == "") $erro .= "Error. Contraseña requerida<br>";
    if (!Valida::email($email)                      ) $erro .= "Error. Email no válido<br>";
     
    if (!is_numeric($puerto) || $puerto < 1 || $puerto > 65535 || $puerto[0] === '0') 
      $erro .= "Error. Puerto incorrecto<br>";
    
    return $erro;
  
  }

  private function obd() {
    $sc_obd = Site_config_obd::inicia(new FS_cbd(), $this->id_site);

    $sc_obd->atr("cm_url"      )->valor = trim( $this->obxeto("cm_url"      )->valor() );
    $sc_obd->atr("cm_seguridad")->valor = trim( $this->obxeto("cm_seguridad")->valor() );
    $sc_obd->atr("cm_puerto"   )->valor = trim( $this->obxeto("cm_puerto"   )->valor() );
    $sc_obd->atr("cm_email"    )->valor = trim( $this->obxeto("cm_email"    )->valor() );
    /* $sc_obd->atr("cm_k"        )->valor = trim( $this->obxeto("cm_k"        )->valor() ); */

    if (($cm_k = trim($this->obxeto("cm_k")->valor())) == ""  ) $cm_k = $this->cm_k;

    $sc_obd->cm_k($cm_k);

    $sc_obd->atr("cm_nombre")->valor = trim( $this->obxeto("cm_nombre")->valor() );
    
    return $sc_obd;
  }

  private function post_obd() {
    $cbd = new FS_cbd();

    $sc_obd = Site_config_obd::inicia($cbd, $this->id_site);

    $this->cm_k = $sc_obd->cm_k();

    $this->obxeto("cm_url"      )->post( $sc_obd->atr("cm_url"      )->valor );
    $this->obxeto("cm_seguridad")->post( $sc_obd->atr("cm_seguridad")->valor );
    $this->obxeto("cm_puerto"   )->post( $sc_obd->atr("cm_puerto"   )->valor );
    $this->obxeto("cm_email"    )->post( $sc_obd->atr("cm_email"    )->valor );
    $this->obxeto("cm_nombre"   )->post( $sc_obd->atr("cm_nombre"   )->valor );
    $this->obxeto("cm_k"        )->post( ""                                  );
  /*   $this->obxeto("cm_k"        )->post( $sc_obd->atr("cm_k"        )->valor );   */

    //~ $this->obxeto("cm_k"         )->post($sc_obd->cm_k());

    //~ $this->obxeto("bProbar")->visible = ($sc_obd->atr("cm_seguridad")->valor != null);
  }

  public static function _sSeguridad($k) {
    $_o = array();

    $_o["ssl"] = "SSL/TLS";     //465
    $_o["tls"] = "STARTTLS";    //587
    $_o["non"] = "Ninguno";

    $s = new Select($k, $_o);

    $s->post("ssl");

    return $s;
  }

}
