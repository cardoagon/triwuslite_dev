<?php


abstract class AElemento extends    Componente
                         implements I_fs,
                                    I_ElmtAjaxBRecargable {

  public $id_elmt      = null;
  public $enome        = null;
  public $enotas       = null;
  public $eid_style    = null;
  public $ecss         = null;
  public $ecss_marcado = null;
  public $pcontrol     = false;
  public $modificado   = false;

  protected $html_btnr = true;

  public function __construct(Elmt_obd $elmt = null, $ptw = null, $ecss_marcado = "celda_elmt_marcado") {
    //* PRECOND: se queremos incluir a $elmt nun prgf ordinario entón ($nome == "elmt").
    parent::__construct("elmt", $ptw);

    $this->obxeto("illa")->clase_css("default", $ecss_marcado); //* permite un acceso directo a los elementos para operaciones de posicionamiento.

    $this->pon_obxeto( self::__brecargar() );

    $this->clase_css("default", "celda_elmt");
    $this->clase_css("edit"   , "celda_elmt_edit");

    if ($elmt == null) return;

    $this->id_elmt      = $elmt->atr("id_elmt")->valor;
    $this->enome        = $elmt->atr("nome")->valor;
    $this->enotas       = $elmt->atr("notas")->valor;
    $this->eid_style    = $elmt->atr("id_style")->valor;
    $this->ecss         = $elmt->atr("css")->valor;
    $this->ecss_marcado = $ecss_marcado;
  }

  public function config_lectura() {
    $this->readonly = true;
  }

  public function config_edicion() {
    $this->readonly = false;

    $this->pon_btnr();
  }

  public function config_usuario(IEfspax_xestor $e = null) {
    $this->pcontrol = $e->edicion();
  }

  public function id_elmt() {
    return $this->id_elmt;
  }

  public function prgf() {
    return $this->pai;
  }

  abstract public function elmt_obd();
  abstract public function editor();
  abstract protected function html_propio();

  final public function __ancla() {
    return Btnr::__ancla($this);
  }

  public static function factory(Elmt_obd $pe_obd) {
    switch (strtoupper($pe_obd->atr("tipo")->valor)) {
      case Texto_obd::tipo_bd:                   return new Elmt_texto($pe_obd);
      case Difucom2_obd::tipo_bd:                return new Elmt_difucom2($pe_obd);
      case Indice_obd::tipo_bd:                  return new Elmt_indice($pe_obd);
      case Imaxe_obd::tipo_bd:                   return new Elmt_imx($pe_obd);
      case Logo_obd::tipo_bd:                    return new Elmt_logo($pe_obd);
      case Iframe_obd::tipo_bd:                  return new Elmt_enlace($pe_obd);
      case HR_obd::tipo_bd:                      return new Elmt_hr($pe_obd);
      case Elmt_carrusel_obd::tipo_bd:           return new Elmt_carrusel($pe_obd);
      //~ case Elmt_carrusel_iten_obd::tipo_bd:      return new Elmt_carrusel_iten($pe_obd);
      //~ case Elmt_carrusel_arti_obd::tipo_bd:      return new Elmt_carrusel_arti($pe_obd);
      //~ case Elmt_carrusel_arti_iten_obd::tipo_bd: return new Elmt_carrusel_arti_iten($pe_obd);
      //~ case Elmt_album_obd::tipo_bd:              return new Elmt_album($pe_obd);
      case Galeria_obd::tipo_bd:                 return new Elmt_galeria($pe_obd);
      case Elmt_novas_obd::tipo_bd:              return new Elmt_novas($pe_obd);
      case Elmt_ventas_obd::tipo_bd:             return new Elmt_ventas($pe_obd);
      case Elmt_contacto_obd::tipo_bd:           return new Elmt_contacto($pe_obd);
      case Elmt_vcard_obd::tipo_bd:              return new Elmt_vcard($pe_obd);
      //~ case Elmt_suscripcion_obd::tipo_bd:        return new Elmt_suscripcion($pe_obd);
      //~ case Comentarios_obd::tipo_bd:             return new Elmt_comentarios($pe_obd);
      //~ case Enquisa_obd::tipo_bd:                 return new Elmt_enquisa($pe_obd);
      case Paxinador_obd::tipo_bd:               return new Elmt_paxinador($pe_obd);
    }

    die("AElemento.factory(), tipo descoñecido.<br>");
  }

  public function operacion(EstadoHTTP $e) {
    if (($btnr = $this->obxeto("btnr")) != null) if (($eaux = $btnr->operacion($e)) != null) return $eaux;

    //~ if (($crs = $this->obxeto("crs")) != null) if (($eaux = $crs->operacion($e, $this->elmt)) != null) return $eaux;


    if (($e_aux = $this->operacion_bRecargable($e)) != null) return $e_aux;


    return null;
  }

  public function operacion_bRecargable(IEfspax_xestor $e = null) {
    if (!$this->obxeto("brecargar")->control_evento()) return null;

    $this->preparar_saida( $e->ajax() );

    return $e;
  }

  public function operacion_editar(Efs_admin $e) {
    if (($editor = $this->editor()) == null) return $e;

    $editor->config_usuario($e);

    $e->obxeto("detalle_btnr_oeste")->pon_editor($e, $editor);

    $e->obxeto("detalle_btnr_oeste")->preparar_saida($e->ajax());

    return $e;
  }

  public function operacion_eliminar(Efs_admin $e) {
    $e->cc_pon($this->obxeto("btnr")->obxeto("bEliminar"));

    $this->prgf()->sup_elmt($this);

    if ($e->evento()->ajax()) {
      $this->prgf()->preparar_saida($e->ajax());

      $e->obxeto("detalle_btnr_este")->preparar_saida($e->ajax());
    }

    return $e;
  }

  public function html_align($tipo = "h") {
    $a_css = Style_obd::__props($this->ecss);

    if ($tipo == "h") {
      if (!isset($a_css["align"])) return null;
      
      return $a_css["align"];
    }
    
    if (!isset($a_css["valign"])) return null;

    return $a_css["valign"];
  }

  public function preparar_saida(Ajax $a = null) {
    $this->obxeto("illa")->post($this->html_illa());


    if ($a == null) return;


    $a->pon($this->obxeto("illa"));

    $this->obxeto("illa")->post(null);
  }

  public function js_callback() {
    return null;
  }

  protected function pon_btnr() {
    $this->pon_obxeto(new Btnr_elmt());
  }

  final protected function obd_base(Elmt_obd $eobd) {
    $eobd->atr("id_elmt" )->valor = $this->id_elmt;
    $eobd->atr("nome"    )->valor = $this->enome;
    $eobd->atr("notas"   )->valor = $this->enotas;
    $eobd->atr("id_style")->valor = $this->eid_style;
    $eobd->atr("css"     )->valor = $this->ecss;

    $eobd->modificado = $this->modificado;

    return $eobd;
  }

  public function html_illa($html_propio = null) {
    if (!$this->visible) return "";

    $style = (($alignh = $this->html_align()) != null)?" style='text-align:{$alignh};'":"";

    if ($html_propio == null) $html_propio = $this->html_propio();

    $ancla = "<a name='" . $this->__ancla() . "'></a>";
    $css   = $this->clase_css("default");

    $id_div       = "";
    $html_btnr    = "";
    $html_btnr_ev = "";

    if (!$this->readonly) {
      if (($btnr = $this->obxeto("btnr")) != null) {
        $html_btnr_ev = $btnr->html_eventos_mostrar($this);

        if ($this->html_btnr) $html_btnr = $btnr->html();
      }

      $id_div  = $this->nome_completo() . "_div";
    }

    return "{$ancla}
            <div id='{$id_div}' class='{$css}' {$html_btnr_ev}>{$html_btnr}<div {$style}>{$html_propio}</div></div>";
  }

  private static function __brecargar() {
    $b = new Div("brecargar", "Recargar");

    $b->style("default", "cursor: pointer; text-decoration: underline;");

    $b->html_name = "belmt_recargar";

    $b->envia_ajax("onclick");


    return $b;
  }
}
