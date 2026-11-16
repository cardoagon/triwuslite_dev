<?php

class Btnr_detalle_este extends Btnr {
  const ptw_0 = "ptw/btnr/este_0.html";
  const ptw_1 = "ptw/btnr/este_1.html";
  
  private $mobil = false;

  public function __construct() {
    parent::__construct("detalle_btnr_este");

    $this->pon_obxeto(self::__bvolver());
    $this->pon_obxeto(self::__bvprev());
    $this->pon_obxeto(self::__bmobil($this->mobil));
    $this->pon_obxeto(self::__brecargar());
    $this->pon_obxeto(self::__bsave());

    $this->pon_obxeto(self::__bAdd());

    $this->pon_obxeto(new Hidden("evento"));

    $this->pon_obxeto(self::__belmt("bTexto", Ico::btxt  , Msx::titulo_btexto, false));
    $this->pon_obxeto(self::__belmt("bImaxe", Ico::bimaxe, Msx::titulo_bimaxe, false));
    $this->pon_obxeto(self::__belmt("bLink" , Ico::blink , Msx::titulo_blink , false));

    $this->pon_obxeto(new IllaAjax("illa"));
    
    $this->obxeto("bTexto")->pon_eventos("onmousedown", "detalle_btnr_bElmt_mousedown(this, 'celda_elmt_marcado')");
    $this->obxeto("bImaxe")->pon_eventos("onmousedown", "detalle_btnr_bElmt_mousedown(this, 'celda_elmt_marcado')");
    $this->obxeto("bLink" )->pon_eventos("onmousedown", "detalle_btnr_bElmt_mousedown(this, 'celda_elmt_marcado')");
  }

  public function config_lectura() {
    $this->pon_obxeto(Btnr_detalle_este::__bvprev(false));

    parent::config_lectura();
  }

  public function config_edicion() {
    $this->pon_obxeto(Btnr_detalle_este::__bvprev(true));

    parent::config_edicion();
  }

  public function config_usuario(IEfspax_xestor $e = null) {
    $custodia = $e->usuario()->en_custodia();

    $this->obxeto("bPE"      )->readonly = $custodia;
    $this->obxeto("bCancelar")->readonly = $custodia;
    $this->obxeto("bAceptar" )->readonly = $custodia;
  }

  public function operacion(EstadoHTTP $e) {
    if ($this->obxeto("bCancelar")->control_evento()) return $e->__crear_instancia($e->id_oms(), $e->id_paxina());

    if ($this->obxeto("bAceptar")->control_evento()) {
      $e->op_gardar();


      return $e;
    }

    if ($this->obxeto("bPE")->control_evento()) {
      $e->configurar_vp();

      return $e;
    }

    if ($this->obxeto("bPM")->control_evento()) {
      $e->mobil = !$e->mobil;

      $e->configurar();
      
      return $e;
    }

    if ($this->obxeto("bVolver")->control_evento()) {
      $e->mobil = false;
      
      $e->redirect("pcontrol.php");

      return $e;
    }

    return null;
  }

  public function hai_cambios($b = true) {
    $this->pon_obxeto(self::__bsave($b));
  }

  public function preparar_saida(Ajax $a = null) {
    $this->obxeto("evento")->post(null);

    $this->ptw = ($this->visible)?self::ptw_1:self::ptw_0;


    $b = $this->visible; //* $this->html00()

    $this->visible = true;

    $this->obxeto("illa")->post($this->html00());

    $this->visible = $b;

    if ($a == null) return;


    $a->pon($this->obxeto("illa"));

    $this->obxeto("illa")->post(null);
  }

  public static function __bvolver($hai_cambios = false) {
    $b = Btnr::__boton("bVolver", Ico::bprev, "Salir del editor", false);

    $b->pon_eventos("onclick", "editor_enviaEvento(this, true)");

    return $b;
  }

  public static function __bsave($hai_cambios = false) {
    if ($hai_cambios) {
      $ico = Ico::bsave2;
      $tit = "Guardar p&aacute;gina, (hay cambios no confirmados)";
    }
    else {
      $ico = Ico::bsave;
      $tit = "Guardar p&aacute;gina";
    }


    $b = Btnr::__boton("bAceptar", $ico, $tit, false);

    $b->pon_eventos("onclick", "editor_enviaEvento(this, false, true)");

    return $b;
  }

  public static function __brecargar() {
    $b = Btnr::__boton("bCancelar", Ico::brevert, "Actualizar", false);

    $b->pon_eventos("onclick", "detalle_btnr_este_brecargar(this)");

    return $b;
  }

  public static function __bvprev($vp = true) {
    if ($vp) {
      $ico = Ico::bpreview;
      $tit = "Vista previa";
    }
    else {
      $ico = Ico::bpreview2;
      $tit = "Cancelar vista previa";
    }

    $b = Btnr::__boton("bPE", $ico, $tit, false);

    $b->pon_eventos("onclick", "editor_enviaEvento(this, false)");

    return $b;
  }

  public static function __bmobil($vm = false) {
    if ($vm) {
      $ico = Ico::bmobil2;
      $tit = "Cancelar vista móvil (Beta)";
    }
    else {
      $ico = Ico::bmobil;
      $tit = "Vista móvil (Beta)";
    }

    $b = Btnr::__boton("bPM", $ico, $tit, false);

    $b->pon_eventos("onclick", "editor_enviaEvento(this, false)");

    return $b;
  }

  public static function __badd($mini = false, $txt = " ") {
    $b = self::__boton("bAdd", Ico::badd, Msx::titulo_badd, false, $txt);

    $b->sup_eventos("onclick");
    $b->pon_eventos("onmousedown", "detalle_btnr_bmas_mousedown(this)");

    return $b;
  }

  public function factory(Epaxina_edit $e) {
    $evento = $e->btnr_evento();

    if ($evento->control_ohttp($this->obxeto("bTexto")))   return new Texto_obd(null, true);
    if ($evento->control_ohttp($this->obxeto("bImaxe")))   return new Imaxe_obd();
    if ($evento->control_ohttp($this->obxeto("bLink")))    return new Iframe_obd();

    return null;
  }
}

