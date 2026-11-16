<?php

class CXCli2panel_2 extends    CContacto
                    implements IPanel_cxcli2 {

  public function __construct() {
    parent::__construct();
  }

  public function id() { //* IMPLEMENTS IPanel_cxcli2.id();
    return $this->nome;
  }

  public function titulo() { //* IMPLEMENTS IPanel_cxcli2.titulo();
    return Msx::$cxcli[$this->pai->idioma->codigo]["panel_2"]["t"];
  }

  public function notas() {  //* IMPLEMENTS IPanel_cxcli2.notas();
    return Msx::$cxcli[$this->pai->idioma->codigo]["panel_2"]["n"];
  }

  public function post_u(FGS_usuario $u = null) { //* IMPLEMENTS IPanel_cxcli2.post_u();
    $this->cambiar_idioma( $this->pai->idioma );
    
    $this->post_contacto($u->contacto_obd(), true);

    $this->id_contacto = $u->atr("id_contacto")->valor;

    //~ echo "<pre>" . print_r($u->a_resumo(), true) . "</pre>";
  }

  public function tipo_btnr() {  //* IMPLEMENTS IPanel_cxcli2.tipo_btnr();
    return 2;
  }

  //~ protected function ptw_0() {
    //~ return Refs::url("ptw/clogin/web/cxcli2_panel_2.html");
  //~ }

  public function operacion_bCancelar(IEfspax_xestor $e) {} //* IMPLEMENTS IPanel_cxcli2.operacion_bCancelar();

  public function operacion_bAceptar(IEfspax_xestor $e) { //* IMPLEMENTS IPanel_cxcli2.operacion_bAceptar();
    $btnr = $this->obxeto("btnr");

    if (($erro = $this->validar()) != null) {
      $btnr->post_erro($erro, $e);

      return $e;
    }

    $cbd = new FS_cbd();

    $cbd->transaccion();

    if (!$this->update_contacto($cbd)) {
      $btnr->post_erro("Error, sucedió un error al guardar tu información de contacto.", $e);

      $cbd->rollback();


      return $e;
    }

    $cbd->commit();

    $this->pai->panel_preparar_saida($this, $e->ajax());


    return $e;
  }
}

