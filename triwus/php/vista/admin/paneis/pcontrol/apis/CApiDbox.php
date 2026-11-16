<?php


final class CApiDbox extends Componente implements ICPC {

  private $id_site = null;

  public $dropbox = null;

  public function __construct($id_site) {
    parent::__construct("capidbox", "ptw/paneis/pcontrol/apis/dbox.html");

    $this->id_site = $id_site;

    $this->dropbox = DropboxTRW::inicia($this->id_site);

    $this->pon_obxeto( new Hidden("htest") );

    $this->pon_obxeto( new Div("dtest") );

    $this->pon_obxeto( new Text("dropbox_u") );
    $this->pon_obxeto( Panel_fs::__password("dropbox_k") );

    $this->pon_obxeto( Panel_fs::__baceptar()  );
    $this->pon_obxeto( Panel_fs::__bcancelar() );
    $this->pon_obxeto( Panel_fs::__benviar("btest", "Test") );


    $this->obxeto("dtest")->style("default", "white-space: prewrap; max-width: 100%; overflow-x: scroll;");

    //~ $this->obxeto("bCancelar")->envia_ajax("onclick");
    
    
    
    $kpub = $this->dropbox->kpub;
    $ruri = $this->dropbox->oauth_urlRedirect();
    
    $this->obxeto("btest")->pon_eventos("onclick", "pcontrol_cdropbox_request_code('{$kpub}', '{$ruri}')");



    $this->post_obd();

//~ echo "site::" . $this->id_site . "<br>";
  }

  public function cpc_miga() {
    return "Conexión Dropbox";
  }

  public function operacion(EstadoHTTP $e) {
//~ echo $e->evento()->html();
    if ($this->obxeto("bAceptar" )->control_evento()) return $this->operacion_baceptar ($e);
    if ($this->obxeto("bCancelar")->control_evento()) return $this->operacion_bcancelar($e);

    if ($this->obxeto("htest"    )->control_evento()) return $this->operacion_htest    ($e);
    //~ if ($this->obxeto("btest"    )->control_evento()) return $this->operacion_btest    ($e);

    return null;
  }

  //~ private function operacion_code(Epcontrol $e) {
//~ //* echo "<pre>" . print_r($_REQUEST, 1) . "</pre>";

    //~ if (!isset($_REQUEST["code"])) return null;


    //~ $this->obxeto("ptest")->post($_REQUEST["code"]);

    //~ return $e;
  //~ }

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
/*
  private function operacion_btest(Epcontrol $e) {
    if ( $this->dropbox->otoken != null ) return $this->operacion_htest( $e );
    
    
    $url = $this->dropbox->oauth_code();

    $e->redirect($url);


    return $e;
  }
*/
  private function operacion_htest(Epcontrol $e) {
    if ( $this->dropbox->otoken != null ) {
      $this->obxeto("btest")->envia_AJAX("onclik");
    }
    

    $this->obxeto("dtest")->post( "<pre>" . print_r($this->dropbox->listFiles(), 1) . "</pre>" );
    
    
    $this->preparar_saida($e->ajax());


    return $e;
  }

  private function update(FS_cbd $cbd) {
    $sc_obd = $this->ccv_obd();

    return $sc_obd->update($cbd);
  }

  private function __validar() {
    return null;
  }

  private function ccv_obd() {
    $sc_obd = Site_config_obd::inicia(new FS_cbd(), $this->id_site);

    $sc_obd->atr("dropbox_u")->valor = $this->obxeto("dropbox_u")->valor();
    $sc_obd->atr("dropbox_k")->valor = $this->obxeto("dropbox_k")->valor();

    return $sc_obd;
  }

  private function post_obd() {
    $cbd = new FS_cbd();

    $sc_obd = Site_config_obd::inicia($cbd, $this->id_site);


    $this->obxeto("dropbox_u" )->post( $sc_obd->atr("dropbox_u")->valor );
    $this->obxeto("dropbox_k" )->post( $sc_obd->atr("dropbox_k")->valor );
  }
}
