<?php

final class CConfig_ventas extends    Componente
                           implements ICPC,
                                      IXestorCEditor_imx {
                             
  const ptw_0 = "ptw/paneis/pcontrol/ventas/cconfig_ventas.html";

  private $id_site = null;

  private $pedfra_url  = null;
  private $pedfra_logo = null; //* Imaxe_obd

  public function __construct($id_site) {
    parent::__construct("cconfig_ventas", self::ptw_0);

    $this->id_site = $id_site;

    $this->pon_obxeto( new Select("ccv_producto_agotados", array("1"=>"Mostrar agotado", "2"=>"No mostrar")) );
    $this->pon_obxeto( new Select("s_cnife"              , array("1"=>" Sí", "2"=>" No")) );
    $this->pon_obxeto( new Select("s_tlfn"               , array("1"=>" Sí", "2"=>" No")) );
    $this->pon_obxeto( new Select("ccv_producto_ive"     , array("1"=>" con IVA", "2"=>" sin IVA")) );
    
    //~ $this->pon_obxeto( new Text("ccv_fra_serie") );
    //~ $this->pon_obxeto( new Text("ccv_ped_serie") );

    //~ $this->pon_obxeto( new Text("fs_k") );
    //~ $this->pon_obxeto( new Text("fs_u") );

    $this->pon_obxeto( new CEditor_imx() );
    
    $this->pon_obxeto( CEditor_imx::__logo() );

    $this->pon_obxeto( Panel_fs::__baceptar()  );
    $this->pon_obxeto( Panel_fs::__bcancelar() );

    //~ $this->obxeto("ccv_fra_serie")->style("default", "width: 55px;");
    //~ $this->obxeto("ccv_fra_serie")->style("readonly", "width: 55px;");

    $this->post_obd();
  }

  public function cpc_miga() {
    return "miscelánea";
  }

  public function operacion(EstadoHTTP $e) {
    if ($this->obxeto("logo")->control_evento()) return $this->obxeto("ceditor_imx")->operacion_logo($e);

    if (($e_aux = $this->obxeto("ceditor_imx")->operacion($e)) != null) return $e_aux;

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
    if (!$this->update_logo($cbd)) return false;
    
    $sc_obd = $this->ccv_obd(); 
    
    
    return $sc_obd->update($cbd);
  }

  private function update_logo(FS_cbd $cbd) {
    if (($url = $this->obxeto("ceditor_imx")->aceptar_imx()) == null) return true;

    $this->pedfra_logo->post_url($url);
    
    
    if ($this->pedfra_logo->atr("id_elmt")->valor == null) return $this->pedfra_logo->insert($cbd);
    
    
    return $this->pedfra_logo->update($cbd);
  }

  private function __validar() {
    return null;
  }

  private function ccv_obd() {
    $sc_obd = Site_config_obd::inicia(new FS_cbd(), $this->id_site);

    $sc_obd->atr("ventas_producto_agotados")->valor = $this->obxeto("ccv_producto_agotados")->valor();
    $sc_obd->atr("ventas_producto_ive"     )->valor = $this->obxeto("ccv_producto_ive")->valor();
    //~ $sc_obd->atr("ventas_pedido_serie"     )->valor = $this->obxeto("ccv_ped_serie")->valor();
    //~ $sc_obd->atr("ventas_factura_serie"    )->valor = $this->obxeto("ccv_fra_serie")->valor();
    $sc_obd->atr("ventas_cpagar2_cnife"    )->valor = $this->obxeto("s_cnife")->valor();
    $sc_obd->atr("ventas_cpagar2_tlfn"     )->valor = $this->obxeto("s_tlfn" )->valor();
    
    //~ $sc_obd->atr("fs_u"                    )->valor = $this->obxeto("fs_u")->valor();
    //~ $sc_obd->atr("fs_k"                    )->valor = $this->obxeto("fs_k")->valor();
    
    $sc_obd->atr("ventas_pedfra_logo"      )->valor = $this->pedfra_logo->atr("id_elmt")->valor;

    return $sc_obd;
  }

  private function post_obd() {
    $cbd = new FS_cbd();
    
    $sc_obd = Site_config_obd::inicia($cbd, $this->id_site);
    
    $this->obxeto("ccv_producto_agotados")->post( $sc_obd->atr("ventas_producto_agotados")->valor );
    $this->obxeto("ccv_producto_ive"     )->post( $sc_obd->atr("ventas_producto_ive"     )->valor );
    //~ $this->obxeto("ccv_ped_serie"        )->post( $sc_obd->atr("ventas_pedido_serie"     )->valor );
    //~ $this->obxeto("ccv_fra_serie"        )->post( $sc_obd->atr("ventas_factura_serie"    )->valor );
    $this->obxeto("s_cnife"              )->post( $sc_obd->atr("ventas_cpagar2_cnife"    )->valor );
    $this->obxeto("s_tlfn"               )->post( $sc_obd->atr("ventas_cpagar2_tlfn"     )->valor );


    //~ $this->obxeto("fs_u"                 )->post( $sc_obd->atr("fs_u"                    )->valor );
    //~ $this->obxeto("fs_k"                 )->post( $sc_obd->atr("fs_k"                    )->valor );

    $this->pedfra_logo = $sc_obd->logo("ventas_pedfra_logo", $cbd);
    
    $this->post_url( $this->pedfra_logo->url(false, $cbd) );
  }

  public function url(?string $k = null) { //* IMPLEMENTS IXestorCEditor_imx
    return $this->pedfra_url;
  }

  public function post_url($url = null, ?string $k = null) { //* IMPLEMENTA IXestorCEditor_imx
    $url = $this->obxeto("ceditor_imx")->pon_src($url);
    
    $this->obxeto("logo")->style("default" , CEditor_imx::__style_logo($url, false, "left"));
    //~ $this->obxeto("logo")->style("readonly", CEditor_imx::__style_logo($url, true , "left"));
  }
}
