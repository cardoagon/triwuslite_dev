<?php

final class CAndroid extends Componente implements ICPC {
  const ptw_0 = "ptw/paneis/pcontrol/andoidAPK/android.html";

  private $id_site  = null;


  public function __construct(Epcontol $e) {
    parent::__construct("capk", self::ptw_0);

    $this->id_site = id_site;

    // novos campos en táboa site_config
   
    /* $this->pon_obxeto( new Text("cm_url"));
    $this->pon_obxeto( self::_sSeguridad("cm_seguridad", "SSL/TLS"));
    $this->pon_obxeto( new Text("cm_puerto"));
    $this->pon_obxeto( new Text("cm_email"));
    $this->pon_obxeto( Panel_fs::__password("cm_k"));

    $this->pon_obxeto( new Text("cm_nombre")); 

    $this->pon_obxeto( Panel_fs::__baceptar());      // [bAceptar]
    $this->pon_obxeto( Panel_fs::__bcancelar() );    // [bCancelar]
    //~ $this->pon_obxeto( Panel_fs::__benviar("bProbar", "Probar", $title = "Probar la configuración SMTP") );  */

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

}
