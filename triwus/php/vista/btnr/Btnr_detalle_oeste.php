<?php


final class Btnr_detalle_oeste extends    Componente
                               implements I_fs       {

  public function __construct() {
    parent::__construct("detalle_btnr_oeste", "ptw/detalle/det_bot_elmt.html");

    $this->pon_obxeto(self::__illa());
  }

  public function config_lectura() {
    $this->obxeto("det_bot_elmt")->visible = false;
  }

  public function config_edicion() {
    $this->obxeto("det_bot_elmt")->visible = true;
  }

  public function readonly($b = true) {
    $this->obxeto("bAceptar")->visible  = !$b;
    $this->obxeto("bAceptar")->readonly =  $b;
  }

  public function config_usuario(IEfspax_xestor $e = null) {}

  public function pon_editor(Efs_admin $e, Editor_btnr_oeste $ee) {
    $this->obxeto("det_bot_elmt")->visible = true;
    $this->obxeto("det_bot_elmt")->clase_css("default", $ee->css_illa_btnr);

    $this->pon_obxeto( new Param( "titulo", self::__titulo($ee->titulo, $ee->icono) ) );
    $this->pon_obxeto($ee);

    $this->pon_obxeto($ee->declara_baceptar());
    $this->pon_obxeto($ee->declara_bcancelar());


    $this->preparar_saida($e->ajax());
  }

  public function operacion(EstadoHTTP $e) {
    if (!$this->obxeto("det_bot_elmt")->visible) return null;

    if ($this->obxeto("bCancelar")->control_evento()) return $this->operacion_bCancelar($e);

    if ($this->obxeto("bAceptar")->control_evento()) return $this->operacion_bAceptar($e);


    if (($ee = $this->obxeto("editor")) == null) return;

    if (($e_aux = $ee->operacion($e)) != null) return $e_aux;

    return null;
  }

  public function html():string {
    $this->preparar_saida();

    $html = $this->obxeto("det_bot_elmt")->html();

    $this->obxeto("det_bot_elmt")->post(null);

    return $html;
  }

  public function preparar_saida(Ajax $a = null) {
    //~ $nc =  $this->nome_completo();
    //~ $ip =  ($this->imx_proporcion > 0)?round(1 / $this->imx_proporcion, 3):"null";

    $m = ($this->visible)?$this->html00():"";

    $this->obxeto("det_bot_elmt")->post($m);

    if ($a == null) return;


    $a->pon($this->obxeto("det_bot_elmt"));

    $this->obxeto("det_bot_elmt")->post(null);
  }

  public function operacion_bAceptar(Epaxina_edit $e) {
    if (($e_aux = $this->obxeto("editor")->operacion_aceptar($e)) == null) return $e;

    $ajax = null;
    if ($e_aux->evento()->ajax()) $ajax = $e_aux->ajax();

    if ($this->obxeto("editor")->cc) {
      $e_aux->cc_pon($this->obxeto("bAceptar"));

      $e_aux->obxeto("detalle_btnr_este")->preparar_saida($ajax);
    }

    $this->obxeto("det_bot_elmt")->visible = false;

    $this->preparar_saida($ajax);

    return $e_aux;
  }

  public function operacion_bCancelar(Epaxina_edit $e) {
    if (($e_aux = $this->obxeto("editor")->operacion_cancelar($e)) != null) return $e;

    $this->obxeto("det_bot_elmt")->visible = false;

    $this->preparar_saida($e->ajax());

    return $e;
  }

  protected static function __titulo($titulo, $icono) {
/* TODO eliminar atributo $icono de la clase
 * 
    $html_icono = "";
    if ($icono != null) $html_icono = "<img src='{$icono}' />&nbsp;";

    return "<span style='color: #333333; line-height: 33px; font-size: 23px;'>
              {$html_icono}{$titulo}
            </span>";
*/
    /* return "<span style='color: #333333; line-height: 33px; font-size: 23px;'>{$titulo}</span>"; */

    return $titulo;
  }

  private static function __illa() {
    $i = new Div("det_bot_elmt");

    $i->clase_css("default", "etq_btnr_elmt_edit");

    $i->visible = false;

    return $i;
  }
}

//--------------------------------------------

abstract class Editor_btnr_oeste extends    Componente
                                 implements I_fs       {

  public $titulo;
  public $icono;
 /*  public $css_illa_btnr = "etq_btnr_elmt_edit"; */ //* para indicar a clase
  public $css_illa_btnr = "trw-editor-plateral";

  public $cc = true;

  protected $width          = 444;
  protected $ancla_baceptar = null;

  public function __construct($icono, $titulo, $ptw) {
    parent::__construct("editor", $ptw);

    $this->titulo = $titulo;
    $this->icono  = $icono;
  }

  abstract public function operacion_aceptar(Efs_admin $e);

  public function config_lectura() {}
  public function config_edicion() {}
  public function config_usuario(IEfspax_xestor $e = null) {}

  public function readonly($b = true) {
    $this->pai->readonly($b);
  }

  public function operacion_cancelar(Epaxina_edit $e) {
    return null;
  }

  public function declara_baceptar() {
    $b = Panel_fs::__baceptar();

    //~ $b->envia_submit("onclick");
    $b->pon_eventos("onclick", "editor_vm_esperar_abrir(this, 'vm_esperar', '', null)");

    return $b;
  }

  public function declara_bcancelar() {
    $b = Panel_fs::__bcancelar();

    //~ $b->envia_submit("onclick");
    $b->envia_ajax("onclick");

    return $b;
  }
}
