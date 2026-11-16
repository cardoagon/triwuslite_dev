<?php

abstract class Btnr extends    Componente
                    implements I_fs {

  protected function __construct($nome = "btnr", $ptw = null) {
    parent::__construct($nome, $ptw);
  }

  public function config_lectura() {
    $this->visible(false);
  }

  public function config_edicion() {
    $this->visible(true);
  }

  public function config_usuario(IEfspax_xestor $e = null) {}

  public static function __ancla(ObxetoFormulario $o) {
    return md5($o->nome_completo());
  }

  public static function __boton($nome, $src, $title = null, $mini = true, $txt = " ") {
    if ($mini) {
      $style = "background-image: url({$src});
                background-position: center center;
                background-repeat: no-repeat;
                background-size: 15px 15px;
                height: 25px; width: 27px;";
    }
    else {
      $style = "background-image: url({$src});
                background-position: center center;
                background-repeat: no-repeat;
                height: 29px; width: 29px;";
    }

    $b = new Button($nome, $txt);

    $b->title = $title;
    
    $b->clase_css("default" , "detalle_btnr_boton");
    $b->clase_css("readonly", "detalle_btnr_boton");

    $b->style("default" , $style);
    $b->style("readonly", $style);

    $b->pon_eventos("onmouseover", "detalle_btnr_boton_onmouseover(this)");
    $b->pon_eventos("onmouseout" , "detalle_btnr_boton_onmouseout(this)" );

    return $b;
  }

  public static function __belmt($nome, $src, $titulo, $mini = true, $txt = " ") {
    $b = self::__boton($nome, $src, $titulo, $mini, $txt);

    $b->sup_eventos("onclick");

    return $b;
  }

  public static function __beliminar($title = "Eliminar") {
    return self::__boton("bEliminar", Ico::bsup, $title);
  }

  protected static function __bedit($titulo) {
    return self::__boton("bEditar", Ico::bedit, $titulo);
  }

  protected static function __capa($visible = false, $css = "elmt_btnr_capa", $position = "absolute", $zindex = 100) {
    $c = new Capa("capa", null, $css);

    $c->visible  = $visible;
    $c->position = $position;
    $c->zindex   = $zindex;

    return $c;
  }
}

//********************************************

final class Btnr_nula extends    Param
                      implements I_fs {

  public function __construct() {
    parent::__construct("btnr_nula");
  }

  public function config_lectura() {}

  public function config_edicion() {}

  public function config_usuario(IEfspax_xestor $e = null) {}
}

