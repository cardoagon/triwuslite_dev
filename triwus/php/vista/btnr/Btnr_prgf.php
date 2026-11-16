<?php

class Btnr_prgf extends Btnr {
  public $usa_bEliminar = true;

  private $ancla;

  public function __construct($usa_bEliminar = true) {
    parent::__construct("btnr");

    $this->usa_bEliminar = $usa_bEliminar;

    $this->pon_obxeto(self::__bedit("Editar las propiedades del p&aacute;rrafo", true));
    $this->pon_obxeto(self::__beliminar("Borrar el p&aacute;rrafo", true));

    $this->obxeto("bEditar"  )->pon_eventos("onclick", "editor_vm_esperar_abrir(this, 'vm_esperar', '', null)");
    //~ $this->obxeto("bEditar"  )->envia_submit("onclick");

    $this->obxeto("bEliminar")->pon_eventos("onclick", "editor_vm_esperar_abrir(this, 'vm_esperar', '', null)");
  }

  public function operacion(EstadoHTTP $e) {
    if ($this->obxeto("bEliminar")->control_evento()) return $this->operacion_bSup($e);

    if ($this->obxeto("bEditar")->control_evento()) {
//~ echo $this->pai->nome_completo() . "</br>";
      return $this->pai->operacion_editar($e);
    }

    return null;
  }

  public function html():string {
    if (!$this->visible) return "";

    $html = "<a name='{$this->ancla}'></a>" . $this->obxeto("bEditar")->html();

    if (!$this->usa_bEliminar         ) return $html;
    
    if ($this->pai->pai->ct_prgf() < 2) return $html;
    

    return "{$html}&nbsp;" . $this->obxeto("bEliminar")->html();
  }

  private function operacion_bSup(Epaxina_edit $e) {
    $e->cc_pon($this->obxeto("bEliminar"));

    return $this->pai->operacion_bSup($e);
  }
}

//*********************************************

class Boton_pos extends Button {
  public function __construct($etq, $css) {
    parent::__construct("bPosicionar", $etq);

    $this->clase_css("default", $css);

    $this->pon_eventos("onmouseover", "this.style.color = '#0000ba'");
    $this->pon_eventos("onmouseout", "this.style.color = null");

    //~ $this->visible = false;
  }
}

//-----------------------------------

final class Bpos_elmt extends Boton_pos {
  public function __construct() {
    parent::__construct("Puedes a&ntilde;adir contenido aqu&iacute;", "detalle_bPosicionar_elmt");

    //~ $this->envia_submit("onclick");
    $this->pon_eventos("onclick", "editor_vm_esperar_abrir(this, 'vm_esperar', '', null)");
  }
}

//-----------------------------------

final class Bpos_prgf extends Boton_pos {
  public function __construct() {
    parent::__construct("Puedes a&ntilde;adir un p&aacute;rrafo aqu&iacute;", "detalle_bPosicionar_prgf");

    //~ $this->envia_ajax("onclick", null, "editor_enviaAJAX_resposta");
    $this->pon_eventos("onclick", "editor_vm_esperar_abrir(this, 'vm_esperar', '', null)");
  }
}


