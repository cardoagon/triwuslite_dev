<?php


abstract class Efs_admin extends Efs {
  public function __construct(Paxina $p = null, FGS_usuario $u = null) {
    parent::__construct($p, $u);


    $p->head->icono = Ico::bico;

    $p->head->pon_css("css/triwus.css");
    $p->head->pon_css("css/basico.css");
    $p->head->pon_css("css/trwform.css");
    /*  $p->head->pon_css("css/pcontrol.css");*/
    $p->head->pon_css("css/pcontrol_2.css");
    $p->head->pon_css("css/carrusel.css");
    $p->head->pon_css("css/elmt.css");
    $p->head->pon_css("css/venta.css");
    $p->head->pon_css("css/cropper.min.css");
    $p->head->pon_css("css/catalogo.css");


    $p->head->pon_js("js/basico.js");
    $p->head->pon_js("js/prgf.js");
    $p->head->pon_js("js/elmt.js");
    $p->head->pon_js("js/cropper.min.js");

    $p->head->pon_js("js/admin.js");
    $p->head->pon_js("js/xpax.js");


    $u = $this->usuario();

    $this->pon_obxeto(new Param("pdominio"));

    $this->pon_obxeto(new VM_editor_esperar());

    $this->pon_obxeto(self::__baxuda());
    $this->pon_obxeto(self::__blogo());
    $this->pon_obxeto(self::__blogout());
    $this->pon_obxeto(self::__bversion());
    $this->pon_obxeto(self::__bversion_ico());
    $this->pon_obxeto(self::__beditarweb());
    $this->pon_obxeto(self::__bpanelcontrol($u));

    $this->pon_obxeto(new Clopd_cancela($u));
    
  }

  public function operacion() {
    if (($e_aux = $this->operacion_k()) != null) return $e_aux;

    //~ if ($this->obxeto("blogo"        )->control_evento()) return $this->operacion_binicio();
    if ($this->obxeto("blogout"      )->control_evento()) return $this->operacion_bpecharses();
    if ($this->obxeto("bpanelcontrol")->control_evento()) return $this->operacion_bpanelcontrol();

    $this->obxeto("vm_esperar")->operacion_pechar($this);

    if (($e_aux = parent::operacion()) != null) return $e_aux;

    return $this;
  }

  //~ protected function operacion_binicio() {
    //~ $this->redirect("index.php");

    //~ return $this;
  //~ }

  protected function operacion_bpanelcontrol() {
    return $this;
  }

  protected function operacion_bpecharses() {

    return new Econsulta_site(null, null);

  }

  protected function operacion_k() {
    if (!isset($this->request["k"])) return null;

    if (($k = $this->request["k"]) == null) return null;

    $cbd = new FS_cbd();

    $r = $cbd->consulta("select * from v_cambio_pdte_email where id_cambio = '{$k}'");

    if (!$a = $r->next()) return null;

    $c = Cambio_obd::inicia($k);

    switch ($a['tipo']) {
      case "ns":      return new Efs_ns($c);
      case "k":       return new Efs_ck($c);
      //~ case "kweb":    return new Efs_ckweb($c);
      case "email":   return new Efs_cemail($c);
      //~ case "altaweb": return new Efs_altaweb($c);
    }

    return null;
  }

  private static function __baxuda() {
    $l = Panel_fs::__link("baxuda", "Ayuda", "texto_cab0");

    $l->href   = "manual.pdf";
    $l->target = "_blank";

    $l->sup_eventos("onclick");
    $l->pon_eventos("onmouseover", "this.style.textDecoration='underline'");
    $l->pon_eventos("onmouseout" , "this.style.textDecoration=null");

    $l->title = Msx::title_axuda;

    return $l;
  }

  private static function __bversion() {
    $l = Panel_fs::__link("bversion", "Versión", "texto_cab0");

    $l->pon_eventos("onmouseover", "this.style.textDecoration='underline'");
    $l->pon_eventos("onmouseout" , "this.style.textDecoration=null");
    $l->pon_eventos("onclick"    , "document.location.href = '?r=bversion'");

    $l->title = "Mantén tu site actualizado.";

    return $l;
  }

  private static function __bversion_ico():Param {
    $_info = Refs::sActu_info();

    $col = "#00ff00";
    if ( isset($_info["err"]) ) {
      $col = "#ff0000";
    }
    elseif ( $_info["actualizar"] == 1 ) {
      $col = "orange";
    }

    return new Param("bversion_ico", "<div style='color:{$col};font-size:1.7em;'>&bull;</div>");
  }

  private static function __beditarweb() {
    $l = Panel_fs::__link("beditarweb", "Editar&nbsp;Web", "texto_cab0");

    $l->pon_eventos("onmouseover", "this.style.textDecoration='underline'");
    $l->pon_eventos("onmouseout" , "this.style.textDecoration=null");

    $l->title = Msx::title_editar_web;

    return $l;
  }

  private static function __bpanelcontrol() {
    $l = Panel_fs::__link("bpanelcontrol", "Panel&nbsp;de&nbsp;control", "texto_cab0");

    $l->pon_eventos("onmouseover", "this.style.textDecoration='underline'");
    $l->pon_eventos("onmouseout" , "this.style.textDecoration=null");

    $l->title = Msx::title_bpanelcontrol;

    $l->pon_eventos("onclick", "editor_enviaEvento(this, true)");

    return $l;
  }

  private static function __blogout() {
    $b = Panel_fs::__bimage("blogout", null, "Cerrar la sesión triwus", false, "Cerrar Sesión");

    $b->clase_css("default" , "boton_cab_login texto_cab0");

    $b->pon_eventos("onmouseover", "this.className = 'boton_cab_login2 texto_cab0'");
    $b->pon_eventos("onmouseout", "this.className = 'boton_cab_login texto_cab0'");

    $b->envia_SUBMIT("onclick", "¿ Desea salir del panel de control ?");

    return $b;
  }

  private static function __blogo() {
    $i = new Image("blogo", "imx/pcontrol/logo.svg", false);

    $i->style("default", "width: 100%; max-width:183px;");
    //~ $i->envia_submit("onclick");

    return $i;
  }

}
