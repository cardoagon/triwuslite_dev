<?php

final class CXCli2panel_1 extends    Componente
                          implements IPanel_cxcli2, IControladorCCS {

  public function __construct() {
    parent::__construct("credenciales", Refs::url("ptw/clogin/web/cxcli2_panel_1.html"));

    $this->obxeto("illa")->clase_css("default", "cxcli2_credenciais");
  }

  public function id() {     //* IMPLEMENTS IPanel_cxcli2.id();
    return $this->nome;
  }

  public function titulo() { //* IMPLEMENTS IPanel_cxcli2.titulo();
    return Msx::$cxcli[$this->pai->idioma->codigo]["panel_1"]["t"];
  }

  public function notas() {  //* IMPLEMENTS IPanel_cxcli2.notas();
    return Msx::$cxcli[$this->pai->idioma->codigo]["panel_1"]["n"];
  }

  public function tipo_btnr() {  //* IMPLEMENTS IPanel_cxcli2.tipo_btnr();
    return 1;
  }

  public function post_u(FGS_usuario $u = null) { //* IMPLEMENTS IPanel_cxcli2.post_u();
    $this->pon_obxeto( new CCS_email($u, false) );

    $this->pon_obxeto( new CCS_k($u, false) );
  }

  public function ccsk_actualiza_email($email) { //* IMPLEMENTS IControladorCCS.ccsk_actualiza_email($email)
    $this->obxeto("ccs_k")->email_activar = $email;
  }

  public function operacion(EstadoHTTP $e) {
    if (($e_aux = $this->obxeto("ccs_email")->operacion($e)) != null) return $e_aux;

    if (($e_aux = $this->obxeto("ccs_k"    )->operacion($e)) != null) return $e_aux;


    return null;
  }

  public function operacion_bCancelar(IEfspax_xestor $e) {} //* IMPLEMENTS IPanel_cxcli2.operacion_bCancelar();

  public function operacion_bAceptar(IEfspax_xestor $e) {} //* IMPLEMENTS IPanel_cxcli2.operacion_bAceptar();
}

