<?php

final class CXCli2_pechar extends Componente
                       implements IPanel_cxcli2 {

  private $id_site;

  public function __construct() {
    parent::__construct("cpechar", Refs::url("ptw/clogin/web/cxcli2_pechar.html"));
  }

  public function id() { //* IMPLEMENTS IPanel_cxcli2.id();
    return $this->nome;
  }

  public function titulo() { //* IMPLEMENTS IPanel_cxcli2.titulo();
    return Msx::$cxcli[$this->pai->idioma->codigo]["pechar"]["t"];
  }

  public function notas() {  //* IMPLEMENTS IPanel_cxcli2.notas();
    return Msx::$cxcli[$this->pai->idioma->codigo]["pechar"]["n"];
  }

  public function post_u(FGS_usuario $u = null) { //* IMPLEMENTS IPanel_cxcli2.post_u();
    $this->obxeto("btnr")->obxeto("bAceptar")->visible = false;
    $this->obxeto("btnr")->obxeto("bCancelar")->visible = false;

    $this->cambiar_idioma($this->pai->idioma);


    $t = Msx::$cxcli[$this->pai->idioma->codigo]["pechar"]["t"];

    $this->pon_obxeto(Panel_fs::__bcancelar($t, $t, "bPechar"));
  }

  public function tipo_btnr() {  //* IMPLEMENTS IPanel_cxcli2.tipo_btnr();
    return 0;
  }

  public function operacion(EstadoHTTP $e) {
    $evento = $e->evento();

    //~ echo $evento->html();

    if ($this->obxeto("bPechar")->control_evento()) {
/* 20250902 Non furrula. REVISAR.
      $e->sup_cookie("trw-kcookie-0");
      $e->sup_cookie("trw-kcookie-1");
*/
//~ echo "<pre>" . print_r($e->cookies(), true) . "</pre>";      



      $e->usuario( new UsuarioWeb_obd() );

      $e->redirect("login.php");

      return $e;
    }

    return parent::operacion($e);
  }
  
  public function operacion_bCancelar(IEfspax_xestor $e) {} //* IMPLEMENTS IPanel_cxcli2.operacion_bCancelar();

  public function operacion_bAceptar(IEfspax_xestor $e) {} //* IMPLEMENTS IPanel_cxcli2.operacion_bAceptar();
}

