<?php


final class CFscripts extends Componente implements ICPC {

  private $id_site = null;


  public function __construct($id_site) {
    parent::__construct("capis", "ptw/paneis/pcontrol/apis/fscripts.html");

    $this->id_site = $id_site;

    $this->pon_obxeto(self::__idIdioma($id_site));

    $this->pon_obxeto( new Checkbox("chApi", true, "Activar / Desactivar conexión con FacturaScripts.") );

    $this->pon_obxeto( new Text("fs_u") );
  //  $this->pon_obxeto( self::__txt("fs_k", true) );
    $this->pon_obxeto( Panel_fs::__password("fs_k") );

    $this->pon_obxeto( Panel_fs::__baceptar()  );
    $this->pon_obxeto( Panel_fs::__bcancelar() );

    $this->post_obd();

  }

  public function cpc_miga() {
    return "Descarga plugin Triwus para Facturascripts";
  }

  public function operacion(EstadoHTTP $e) {
    if ($this->obxeto("bAceptar" )->control_evento()) return $this->operacion_baceptar ($e);
    if ($this->obxeto("bCancelar")->control_evento()) return $this->operacion_bcancelar($e);
    return null;
  }

  private function operacion_bcancelar(Epcontrol $e) {
    $this->post_obd();

    return $e;
  }

  private function operacion_baceptar(Epcontrol $e) {
    $cbd = new FS_cbd();
    
    $cbd->transaccion();

    if (!$this->update($cbd)) { //* realiza un update de logo e resto dos campos
      $cbd->rollback();
    }
    else {
      $cbd->commit();
    }

    return $e;
  }

  private function update(FS_cbd $cbd) {
  
    $sc_obd = $this->ccv_obd(); 
    
    return $sc_obd->update($cbd);
  }

  private function __validar() {
    return "Descarga plugin triwus para Facturascripts";
  }

  private function ccv_obd() {
    $sc_obd = Site_config_obd::inicia(new FS_cbd(), $this->id_site);

    $sc_obd->atr("api" )->valor = ($this->obxeto("chApi")->valor())?"1":null;
    
    $sc_obd->atr("fs_u")->valor = $this->obxeto("fs_u")->valor();
    $sc_obd->atr("fs_k")->valor = $this->obxeto("fs_k")->valor();
    
    return $sc_obd;
  }

  private function post_obd() {
    $cbd = new FS_cbd();

    $sc_obd = Site_config_obd::inicia($cbd, $this->id_site);

    $this->obxeto("chApi")->post( ($sc_obd->atr("api")->valor == 1)?true:false );

    $this->obxeto("fs_u" )->post( $sc_obd->atr("fs_u")->valor );
    $this->obxeto("fs_k" )->post( $sc_obd->atr("fs_k")->valor );
  }

  public static function __txt($id, $pass = false, $title = null) {
    $t = ($pass)?new Password($id):new Text($id);

    $t->size        = 20;
    $t->title       = $title;
    $t->placeholder = " ";

    $t->clase_css("default", null);

    return $t;
  }


  private static function __idIdioma($id_site) {
    $l = MLsite_obd::idioma_predeterminado($id_site);

    //~ if ($l ==)
    
    return new Param("id_idioma", $l);
  }
}
