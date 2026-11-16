<?php

class Elmt_imx extends AElemento {

  const min_width  = "33";
  const min_height = "33";

  public $link_href            = null;
  public $link_target          = null;

  public $detalle_w            = null;

  public $imx_proporcion       = 0; //* non se recorta en funcion de imx_proporcion

  //~ public $recalcula_dimensions = true;

  public $site_obd             = null;

  public $url_arquivos = null;

  public $file_tmp = null;

  protected $id_oms  = null;
  protected $id_pax  = null;
  protected $edicion = null;

  protected $mini     = 0;

  public function __construct(Imaxe_obd $pe_obd) {
    parent::__construct($pe_obd);

    $this->link_href   = $pe_obd->atr("link_href")->valor;
    $this->link_target = $pe_obd->atr("link_target")->valor;

    $this->pon_imaxe($pe_obd);

    $this->pon_obxeto(new Hidden("brecortar_info"));
  }

  public function config_usuario(IEfspax_xestor $e = null) {
    parent::config_usuario($e);

    if ($this->site_obd != null) return;

    $c = $e->config();

    $this->detalle_w = $c->atr("w")->valor;

    $this->site_obd = $e->__site_obd();
    $this->edicion  = $e->edicion();

    $this->url_arquivos = $e->url_arquivos();

    $this->pon_linkhref();
  }

  public function elmt_obd() {
    $src_0    = $this->obxeto("imaxe")->src();
    $src_nula = Imaxe_obd::url_nula();
    
    $imx = new Imaxe_obd();
    
    if ( $src_0 != $src_nula ) $imx->post_url($src_0);

    $imx->atr("link_href"  )->valor = $this->link_href;
    $imx->atr("link_target")->valor = $this->link_target;

    return $this->obd_base($imx);
  }

  public function editor() {
    return new Editor_eimx($this);
  }

  public function pon_linkhref() {
    if ($this->link_href == null) {
      if ($this->obxeto("link") != null) $this->sup_obxeto("link");

      return;
    }

    if (!$this->pcontrol) { //* se non estamos no editor, tomamos sempre esta opción
      $this->pon_obxeto(new Link("link", "", $this->link_target, $this->link_href));

      return;
    }

    if ($this->site_obd == null) {
      $this->pon_obxeto(new Link("link", "", $this->link_target, $this->link_href));

      return;
    }

    $pax_obd = Paxina_obd::inicia_url(new FS_cbd(), $this->link_href, $this->site_obd);

    if ($pax_obd == null) {
      $this->pon_obxeto(new Link("link", "", $this->link_target, $this->link_href));

      return;
    }

    $this->id_oms = $pax_obd->atr("id_opcion")->valor;
    $this->id_pax = $pax_obd->atr("id_paxina")->valor;

    $this->pon_obxeto(self::__linkpaxobd($this->link_target));
  }

  public function operacion(EstadoHTTP $e) {
    if ($this->obxeto("illa")->control_evento()) {
      if ($e->evento()->tipo() == "brecortar-cancela") return $this->preparar_saida( $e->ajax() );

      return $e;
    }

    if (($e_aux = $this->operacion_brecortar_info($e)) != null) return $e_aux;

    if (($l = $this->obxeto("link")) != null) {
      if ($l->control_evento()) return Efspax_xestor::operacion_redirect($e, $this->id_pax, 1);
    }

    return parent::operacion($e);
  }

  public function num_cols($real = false) {
    return $this->prgf()->num_cols(null, $real);
  }

  public function pon_src($src) {
    if ($this->obxeto("imaxe") == null) return;

    $this->obxeto("imaxe")->pon_src($src);
 }

  public function src($mini = -1) {
    return $this->obxeto("imaxe")->src();
  }

  protected function html_propio() {
    $a_css    = Style_obd::__props($this->ecss);

    $align    = $a_css['align'];
    $cimaxe   = $this->obxeto("imaxe");
    $etq      = self::__etq($this, $align);

    $src      = $this->src($this->mini);

    $link     = $this->obxeto("link");

    $hinfo    = $this->obxeto("brecortar_info");


    //~ if (!is_file($src)) return self::__ptw($align, "", $etq, $link, $hinfo);


    if ($cimaxe->src() != Imaxe_obd::noimx) {
      $cimaxe->style("default", "max-width: {$a_css['width']};");
    }

    return self::__ptw($align, $cimaxe->html(), $etq, $link, $hinfo);
  }

  protected function pon_imaxe(Imaxe_obd $iobd, $mini = -1) {
    $this->mini = $mini;

    if ($mini == -1) $mini = false;

    $imx = new Image("imaxe", $iobd->url($mini), false);

    $imx->title = $iobd->atr("notas")->valor;

    $this->pon_obxeto($imx);
  }


  private function operacion_brecortar_info(EstadoHTTP $e) {
    if ( !$this->obxeto("brecortar_info")->control_evento() ) return null;


    //~ $url = $this->obxeto("imaxe")->src();

    list($x, $y, $w, $h) = explode(Escritor_html::csubnome, $this->obxeto("brecortar_info")->valor());

    $file_tmp = new File("x", Refs::url_arquivos(Refs::url_tmp . "/") );

    $file_tmp->vincular_f( $this->obxeto("imaxe")->src() );

    if (!$file_tmp->recortar($x, $y, $w, $h)) {
      $e->post_msx("ERROR: no se pudo recortar la imagen.");

      return $e;
    }


    $this->pon_src( $file_tmp->f() );

    $this->modificado = true;


    $this->preparar_saida( $e->ajax() );


    $e->cc_pon($this->obxeto("imaxe"));

    $e->obxeto("detalle_btnr_este")->preparar_saida($e->ajax());


    return $e;
  }


  private static function __etq(Elmt_imx $eimx, $align) {
    if (($eimx->enome == null) && ($eimx->enotas == null)) return null;

    $etq = new Div("etq", $eimx->enome);

    $etq->pai = $eimx;

    $etq->clase_css("default", "elmt_imx_titulo");
    $etq->style    ("default", "align: {$align};");

    return $etq->html();
  }

  private static function __ptw($align, $html_imx, $html_etq, Control $l = null, Control $hinfo = null) {

    $imx = $hinfo->html() .
           "<div class='celda_imaxe' style='min-width:" . Elmt_imx::min_width . "px; min-height: " . Elmt_imx::min_height . "px; '>{$html_imx}{$html_etq}</div>";

    if ($l == null) return $imx;

    $l->post($imx);

    return $l->html();
  }

  private static function __linkpaxobd($target) {
    $l = new Link("link", "", $target);

    $l->style("default", "cursor: pointer;");

    $l->envia_SUBMIT("onclick");

    return $l;
  }

  protected function pon_btnr() {
    $this->pon_obxeto(new Btnr_elmt_imx());
  }
}

//*******************************

class Editor_eimx extends Edita_elmt {
  public $ptw_0 = "ptw/paragrafo/elmt/editor_imx.html";

  //~ public $imx_proporcion = null;

  public function __construct(Elmt_imx $elmt, $icono = Ico::bimaxe, $titulo = "Editar imagen", $ptw = null) {
    if ($ptw != null) $this->ptw_0 = $ptw;

    parent::__construct($icono, $titulo, $this->ptw_0, $elmt);


    $this->pon_obxeto(self::__lcambiar());

    $this->pon_obxeto($this->__fimaxe( Refs::url_arquivos("tmp/") ));

    $this->pon_obxeto(self::__imx($elmt));

    $this->pon_obxeto(new Param("imx_prop"));

    $this->pon_obxeto(Panel_fs::__text("link_href", 10, 255));
    $this->pon_obxeto(new Select("link_target", array("_self"=>"Misma ventana", "_blank"=>"Nueva ventana")));

    $this->pon_obxeto(new Param("tdvp_align"));


    $this->obxeto("link_href"  )->post($elmt->link_href  );
    $this->obxeto("link_target")->post($elmt->link_target);

    $this->config_props();


    self::__imxprop($this);
  }

  public function operacion(EstadoHTTP $e) {
    if ($this->obxeto("f_imaxe")->control_evento()) {
      $this->recibir_arquivo($e);

      $this->preparar_saida($e->ajax());

      return $e;
    }

    return parent::operacion($e);
  }

  public function operacion_aceptar(Efs_admin $e) {
    $this->elmt->link_href   = trim($this->obxeto("link_href")->valor());
    $this->elmt->link_target = $this->obxeto("link_target")->valor();

    $this->elmt->pon_src($this->obxeto("imaxe")->src());

    //~ $this->elmt->file_tmp = $this->obxeto("f_imaxe");

    $this->elmt->obxeto("imaxe")->title = trim($this->obxeto("tadesc")->valor());

    $this->elmt->pon_linkhref();

    return parent::operacion_aceptar($e);
  }

  public function operacion_cancelar(Epaxina_edit $e) {
    if ($this->ptw == $this->ptw_0) return parent::operacion_cancelar($e);

    if ($this->ptw == $this->ptw_2) return $this->operacion_brecortar($e, false);

    $this->obxeto("imaxe2")->post( $this->obxeto("imaxe")->src() );

    $this->config_props();

    $this->preparar_saida($e->ajax());

    return $e;
  }

  protected function __editor_style() {
    return new Editor_simx($this->elmt->elmt_obd());
  }

  protected function recibir_arquivo(Efs_admin $e) {
    $url_tmp = $this->obxeto("f_imaxe")->post_f();

    $this->obxeto("imaxe")->post( $url_tmp );

    self::__imxprop($this);
  }

  private function config_props() {
    $this->ptw = $this->ptw_0;

    $this->mw = round($this->width * .49);
    $this->mh = 128;

    /* $this->obxeto("imaxe")->style("default", "max-width: 100%; max-height: 272px; cursor: pointer;"); */
    $this->obxeto("imaxe")->style("default", "");

    self::__config($this);
  }

  private static function __imxprop(Editor_eimx $eeimx) {
    $src = $eeimx->obxeto("imaxe")->src();

    $a_props = Valida::_imxprops($src);

    $eeimx->obxeto("cstyle")->escala_w = $a_props["w"];
    $eeimx->obxeto("cstyle")->escala_h = $a_props["h"];


    $s = "<ul class='img-thumb__props'>
            <li><span class='img-thumb__prop-name'>Ancho:</span>
                <span class='img-thumb__prop-value'>{$a_props["w"]} px</span></li>
            <li><span class='img-thumb__prop-name'>Altura:</span>
                <span class='img-thumb__prop-value'>{$a_props["h"]} px</span></li>
            <li><span class='img-thumb__prop-name'>Mime:</span>
                <span class='img-thumb__prop-value'>{$a_props["m"]}</span></li>
            <li><span class='img-thumb__prop-name'>Tamaño:</span>
                <span class='img-thumb__prop-value'>{$a_props["t"]}</span></li>
          </ul>";

    $eeimx->obxeto("imx_prop")->post($s);
  }


  private static function __fimaxe($src_tmp) {
    $f = new File("f_imaxe", $src_tmp, 1024 * 1024 * 3);

    $f->clase_css("default", "file_apagado");


    $f->accept      = "image/gif, image/jpg, image/jpeg, image/png, image/webp";


    return $f;
  }


  private static function __imx(Elmt_imx $elmt) {
    $url = $elmt->obxeto("imaxe")->src();

    if ($url == null) $url = Imaxe_obd::noimx;


    return new Image("imaxe", Refs::url($url), false);
  }

  private static function __config(Editor_eimx $eeimx) {
    $eeimx->obxeto("tdvp_align")->post($eeimx->obxeto("cstyle")->align_h());

    //~ $eeimx->obxeto("msx_cambiar")->visible = false;
    //~ $eeimx->obxeto("msx_cambiar")->clase_css("default", "fgs_erro");

    $eeimx->obxeto("titulo")->title = Msx::elmt_imaxe_pefoto;
   /*  $eeimx->obxeto("titulo")->placeholder = Msx::elmt_imaxe_pefoto; */
  }

  private static function __lcambiar() {
    /* $l = Panel_fs::__link("imx_cambiar", "Cambiar la imagen", "texto2", "texto2_a"); */
    $l = Panel_fs::__link("imx_cambiar", "Cambiar la imagen", "trw-btn", "trw-btn over");

    /* $l->style("default", "padding: 3px 3px 3px 11px;"); */

    $l->title = "Pincha aqu&iacute; para &laquo;cambiar&raquo; la imagen";

    $l->pon_eventos("onclick", "elmt_imx_cambiar(this)");

    return $l;
  }

}
