<?php


class Elmt_texto extends AElemento {
  const txt0 ="<p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. <b>Aenean commodo ligula eget dolor</b>. Aenean massa. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus.<p>";

  public $editable = false;
  public $wysiwyg  = false;
  public $ref_site = null;

  public function __construct(Texto_obd $pe_obd) {
    parent::__construct($pe_obd);

    $this->ref_site = $pe_obd->atr("ref_site")->valor;
    $this->wysiwyg  = $pe_obd->atr("wysiwyg" )->valor == 1;

    $this->pon_btnr();

    $this->clase_css("default", "celda_elmt_texto");
    $this->clase_css("edit"   , "celda_elmt_texto_over");

    if (($txt = $pe_obd->atr("txt")->valor) == null)
      if ($pe_obd->atr("id_elmt")->valor == null) $txt = self::txt0;


    $this->pon_obxeto(new Elmt_texto_ceditor("texto", $txt, true, $this->wysiwyg));
  }

  public function config_usuario(IEfspax_xestor $e = null) {
    $c    = $e->config();
    
    $ctxt = $this->obxeto("texto");
    
    $ctxt->pon_config_css( Refs::url("css/basico.css") );
    $ctxt->pon_config_css( $c->css("basico") );

    $this->obxeto("btnr")->obxeto("bEditar")->pon_eventos("onclick", "elmt_texto2_onclick(this, '" . $ctxt->__selector() . "')");
  }

  public function config_lectura() {
    parent::config_lectura();

    $this->obxeto("texto")->readonly = true;
  }

  public function config_edicion() {
    parent::config_edicion();

    $this->obxeto("texto")->readonly = false;
  }

  public function declara_js() {
    return $this->obxeto("texto")->declara_js();
  }

  public function elmt_obd() {
    $e = new Texto_obd();

    $e->atr("ref_site")->valor = $this->ref_site;
    $e->atr("txt"     )->valor = $this->obxeto("texto")->valor();
    $e->atr("wysiwyg" )->valor = ($this->wysiwyg)?"1":"0";

    return $this->obd_base($e);
  }

  public function operacion(EstadoHTTP $e) {
//~ echo $e->evento()->html();

    if ($this->obxeto("texto")->obxeto("illa")->control_evento()) return $this->operacion_editar($e);


    return parent::operacion($e);
  }

  public function operacion_editar(Efs_admin $e) {
//~ echo $e->evento()->html();

    $this->modificado = true;

    if ($e->hai_cambios()) {
      $e->ajax()->pon_ok();

      return $e;
    }

    $e->cc_pon($this->obxeto("texto")->obxeto("illa"));

    $e->obxeto("detalle_btnr_este")->preparar_saida($e->ajax());


    return $e;
  }

  public function html_propio() {
    return $this->obxeto("texto")->html();
  }

  public function editor() {
    return null;
  }

  protected function pon_btnr() {
    if ($this->obxeto("btnr") != null) return;

    $btnr = new Btnr_elmt();

    //~ $btnr->visible = false;

    if (($ctxt = $this->obxeto("texto")) != null) {
      $f = "elmt_texto2_onclick(this, '" . $ctxt->__selector() . "')";

      $btnr->obxeto("bEditar")->pon_eventos("onclick", $f);
    }

    $this->pon_obxeto($btnr);
  }
}

//*******************************

final class Elmt_texto_ceditor extends CEditor {
  public function __construct($id, $txt = "", $onload_hide = true, $wysiwyg = true) {
    parent::__construct($id, $txt, $onload_hide, true, $wysiwyg);

    $this->ptw_0 = Refs::url("ptw/paragrafo/elmt/elmt_texto_ceditor.html"      );
    $this->ptw_1 = Refs::url("ptw/paragrafo/elmt/elmt_texto_ceditor_limpo.html");

    $this->body_class = "celda_body celda_elmt_texto";
  }
}

