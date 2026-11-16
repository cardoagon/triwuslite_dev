<?php

final class Elmt_galeria extends AElemento {
  public $src        = null;
  public $bdfotos    = null;
  public $columnas   = 7;
  public $frecuencia = null;
  public $formato    = null;
  public $mobil      = false;
  public $tgaleria   = "m";

  private $id_json   = null;


  public function __construct(Galeria_obd $pe_obd) {
    parent::__construct($pe_obd, Refs::url("ptw/paragrafo/elmt/elmt_galeria.html"));

    $this->frecuencia = $pe_obd->atr("frecuencia")->valor;
    $this->formato    = $pe_obd->atr("formato"   )->valor;
    $this->tgaleria   = $pe_obd->atr("tgaleria"  )->valor;

    if (($this->columnas = $pe_obd->atr("numcols")->valor) < 1) $this->columnas = 5;

    $this->id_json = "trw-galeria" . Escritor_html::csubnome . $this->enome;

    if ($this->frecuencia == 0) $this->frecuencia = "0";
    if ($this->formato    == 0) $this->formato    = "1";
    if ($this->columnas   == 0) $this->columnas   = "7";

    $this->pon_obxeto(new Hidden("clogo_prgfialbum"));
  }

  public function js_callback() {
    return "galeria2_iniciar(" . $this->json() . ")";
  }

  public function config_usuario(IEfspax_xestor $e = null) {
    parent::config_usuario($e);

    //~ $this->src     = $e->url_galerias("{$this->enome}");
    $this->src     = Refs::url_arquivos( Refs::url_galerias . "/{$this->enome}/");

    $this->bdfotos = new BD_fotos( $this->src );

    $this->mobil   = $e->config()->atr("tipo")->valor == "mobil";
  }

  protected function pon_btnr() {
    $btnr = new Btnr_elmt();

    $btnr->config_visibles(true, false, false, true);

    //~ $btnr->obxeto("bEditar")->envia_submit("onclick");

    $this->pon_obxeto($btnr);
  }

  public function elmt_obd() {
    $elmt = $this->obd_base( new Galeria_obd() );

    $elmt->post_fotos($this->bdfotos);

    $elmt->atr("numcols"   )->valor = $this->columnas;
    $elmt->atr("frecuencia")->valor = $this->frecuencia;
    $elmt->atr("tgaleria"  )->valor = $this->tgaleria;

    return $elmt;
  }

  public function editor() {
    return new Editor_egaleria($this);
  }

  public function operacion(EstadoHTTP $e) {
    if ($this->obxeto("brecargar")->control_evento()) {
      $this->preparar_saida( $e->ajax() );

      return $e;
    }


    return parent::operacion($e);
  }

  protected function html_propio() {
    if (!$this->pcontrol) if ($this->bdfotos->total() < 1) return "";

    return "<div id='{$this->id_json}'>" . $this->html_msx_0() . "</div>
            <script>" . $this->js_callback() . ";</script>";
  }

  public function preparar_saida(Ajax $a = null) {
    parent::preparar_saida( $a );

    if ($a == null) return;

    $a->pon_ok(true, $this->js_callback());
  }

  protected function html_msx_0() {
    return "
          <div style='color: #333333; line-height: 161%; padding: 11px 7px;'>
            Por algún motivo no se a completado la carga de la galeria, pincha el <b>botón recargar</b> para intentarlo de nuevo.
            <br /><br />
            <center>" . $this->obxeto("brecargar")->html() . "</center>
          </div>";
  }

  private function json() {
    $nome     = $this->enome;
    $frec     = $this->frecuencia;
    $editor   = ($this->pcontrol)?"1":"0";
    $bd       = $this->bdfotos->__json();
    $tgaleria = $this->tgaleria;
    $numcols  = $this->columnas;

    if ($this->mobil) $numcols = ($numcols >=4)?2:1;


    return "['{$this->id_json}', '{$numcols}', {$bd}, '{$nome}', {$frec}, {$editor}, '{$tgaleria}']";
  }

}

//*******************************

final class Editor_egaleria extends Edita_elmt {
  const ptw_0 = "ptw/paragrafo/elmt/editor_galeria_0.html";
  const ptw_1 = "ptw/paragrafo/elmt/editor_galeria_1.html";
  const ptw_9 = "ptw/paragrafo/elmt/editor_galeria_foto.html";

  public static $_cols    = array("1"=>"1", "2"=>"2", "3"=>"3", "4"=>"4", "5"=>"5", "6"=>"6", "7"=>"7", "8"=>"8");

  private $i_imx_edit = -1;

  public function __construct(Elmt_galeria $elmt) {
    parent::__construct(Ico::bgaleria, "Galería", "", $elmt);

$this->pon_obxeto(new Param("t"));

    $this->pon_obxeto(new Select("tgaleria"   , Galeria_obd::$_tgaleria ));
    $this->pon_obxeto(new Select("tcolumnas"  , self::$_cols            ));

    $this->pon_obxeto(self::__bmais());
    $this->pon_obxeto(self::__file($elmt->src));


    $this->pon_obxeto( new CLIMX_galeria( $elmt->bdfotos ) );

    $this->pon_obxeto( new Image("imxedit", null, false) );
    $this->pon_obxeto( new Text("tetqedit") );

    $this->pon_obxeto(self::__bimxedit());
    $this->pon_obxeto(self::__fimxedit($elmt->src));


    $this->obxeto("tgaleria")->post( $elmt->tgaleria );
    $this->obxeto("tgaleria")->envia_ajax("onchange");

    $this->obxeto("tcolumnas")->post( ($elmt->columnas == null)?"5":$elmt->columnas );


    //~ $this->obxeto("imxedit")->style("default", "max-height: 232px; max-width: 77%;");

    //~ $this->obxeto("tetqedit")->clase_css("default", "egaleria_txt_r");
    
    
    $this->ptw = $this->__ptw_0(); //* depende do obxeto "tgalería".
  }

  public function operacion(EstadoHTTP $e) {
$this->obxeto("t")->post(null);

    if ($this->obxeto("tgaleria")->control_evento()) {
      $this->ptw = $this->__ptw_0();
       
      $this->preparar_saida( $e->ajax() );

      return $e;
    }


    if ($this->obxeto("ifile")->control_evento()) return $this->operacion_ifile($e);
    
    if ($this->obxeto("f_imxedit")->control_evento()) return $this->operacion_fimxedit($e);

    if (($e_aux = $this->obxeto("climxgal")->operacion($e)) != null) return $e_aux;


    return parent::operacion($e);
  }
 
  public function operacion_ifile(Epaxina_edit $e) {
    $f = $this->obxeto("ifile")->post_f(true, -1);
    
    $_bd_url = $this->obxeto("climxgal")->bd->_url();

//~ $t .= "111111111111111::{$f} == " . BD_fotos::url_json . "\n";
    if ( ($b = basename($f)) == BD_fotos::url_json ) return null;
    

//~ $t .= "222222222222222\n";
    if ( isset($_bd_url[$f])                       ) return null;
    
    
    $this->obxeto("climxgal")->bd->pon($f);
//~ $t .= "333333333333333\n";



//~ $t .= $this->obxeto("climxgal")->bd->clonar()->__json();

//~ $this->obxeto("t")->post($t);



    $this->preparar_saida( $e->ajax() );

    return $e;
  }

  public function operacion_fimxedit(Efs_admin $e) {
    $f = $this->obxeto("f_imxedit")->post_f();
    
    $this->obxeto("imxedit")->post( $f );


    $this->preparar_saida( $e->ajax() );
    

    return $e;
  }

  public function operacion_himxedit(Epaxina_edit $e, $i_imx) {
    $this->i_imx_edit = $i_imx;
    $this->ptw        = self::ptw_9;

    $bd = $this->obxeto("climxgal")->bd;

    $this->obxeto("imxedit" )->pon_src($bd->url($i_imx));

    $this->obxeto("tetqedit")->post($bd->nome($i_imx));

    $this->preparar_saida($e->ajax());


    return $e;
  }

  public function operacion_cancelar(Epaxina_edit $e) {
    if ($this->i_imx_edit > -1) return $this->operacion_cancelar_1($e);


    //* borra fotos recien engadidas.
    $bd = $this->obxeto("climxgal")->bd->clonar();
    for ($i = 0; $i < $bd->total(); $i++) {
      if ($bd->marca($i) != "+") continue;

      $url = $bd->url($i);

      if (is_file($url)) unlink($url);
    }

    return parent::operacion_cancelar($e);
  }

  public function operacion_cancelar_1(Epaxina_edit $e) {
    $this->i_imx_edit = -1;
    $this->ptw        = $this->__ptw_0();

    $this->preparar_saida($e->ajax());

    return $e;
  }

  public function operacion_aceptar(Efs_admin $e) {
    if ($this->i_imx_edit > -1) return $this->operacion_aceptar_1($e);

    $e = parent::operacion_aceptar($e);


    //* confirma fotos recien engadidas, non é de todo compatible con recargar.
    $bd = $this->obxeto("climxgal")->bd->clonar();
    for ($i = 0; $i < $bd->total(); $i++) {
      if ($bd->marca($i) == "+") $bd->marcar($i, "0");
    }

    //* actualiza elmt

    $this->elmt->modificado = true;

    $this->elmt->bdfotos    = $bd->clonar();
    $this->elmt->tgaleria   = $this->obxeto("tgaleria" )->valor();
    $this->elmt->columnas   = $this->obxeto("tcolumnas")->valor();

    $this->elmt->preparar_saida($e->ajax());

    return $e;
  }

  public function operacion_aceptar_1(Efs_admin $e) {
    $this->obxeto("climxgal")->bd->url ($this->i_imx_edit, $this->obxeto("imxedit" )->src  ());
    $this->obxeto("climxgal")->bd->nome($this->i_imx_edit, $this->obxeto("tetqedit")->valor());

    $this->operacion_cancelar_1($e);

    return null;
  }

  protected function __editor_style() {
    return new Editor_style_nulo();
  }

  private function __ptw_0():string {
    if ($this->obxeto("tgaleria")->valor() == "m") return self::ptw_0;
    
    return self::ptw_1;
  }

  private static function __file($src_tmp) {
    $f = new File("ifile", $src_tmp, 1024 * 1024 * 3);
    
    $f->multiple = true;

    $f->clase_css("default", "file_apagado");

    $f->accept      = "image/gif, image/jpg, image/jpeg, image/png, image/webp";


    return $f;
  }

  private static function __bmais() {
    $b = Panel_fs::__bmais("bmais", "Añadir fotos", "Añadir fotos a la galería");

    $b->pon_eventos("onclick", "tilia_file_click('findex;detalle_btnr_oeste;editor;ifile')");


    return $b;
  }

  private static function __fimxedit($src_tmp) {
    $f = new File("f_imxedit", $src_tmp, 1024 * 1024 * 3);
    
    $f->multiple = true;

    $f->clase_css("default", "file_apagado");

    $f->accept      = "image/gif, image/jpg, image/jpeg, image/png, image/webp";


    return $f;
  }

  private static function __bimxedit() {
    $l = Panel_fs::__link("imxedit_cambiar", "Cambiar la imagen", "trw-btn", "trw-btn over");

    $l->title = "Pincha aqu&iacute; para &laquo;cambiar&raquo; la imagen";

    $l->pon_eventos("onclick", "tilia_file_click('findex;detalle_btnr_oeste;editor;f_imxedit')");

    return $l;
  }
}

//*******************************

final class CLIMX_galeria extends FS_lista
                       implements Iterador_bd {

  public $bd = null;

  private $i = -1;

  public function __construct(BD_fotos $bd) {
    parent::__construct("climxgal", new FSehtml_egaleria(), null);

    $this->bd = $bd->clonar();

    $this->pon_obxeto(new Hidden("himxedit"));
  }

  protected function __iterador() {
    return $this;
  }

  public function numcols() {
    return $this->pai->obxeto("tcolumnas")->valor();
  }

  public function bd_reset($src) {
    //~ $this->bd->pechar();

    BD_fotos::__reset_bd($src);

    $this->bd = new BD_fotos($src);
  }

  public function operacion(EstadoHTTP $e) {
    $evento = $e->evento();


    if ($this->control_fslevento($evento, "bsup")) {
      $i = $evento->subnome(0);

      if ( $this->bd->marca($i) == "+" ) {
        $url = $this->bd->url($i);

        if (is_file($url)) unlink($url);
      }

      $this->bd->marcar($i);

      $this->pai->preparar_saida( $e->ajax() );

      return $e;
    }

    if ($this->control_fslevento($evento, "beditar")) {
      $i = $evento->subnome(0);

      return $this->pai->operacion_himxedit($e, $i);
    }

    return parent::operacion($e);
  }

  public function __count() {
    return $this->bd->total();
  }

  protected function html_baleiro() {
    if (!$this->visible) return "";

    return "<div class='lista_baleira txt_adv'>No hay imágenes</div>";
  }

  public function numFilas() {
    return $this->__count();
  }

  public function descFila() {}

  public function next() {
    $this->i++;

    if ($this->i >= $this->__count()) {
      $this->i = -1;

      return null;
    }

    if ( $this->bd->marca($this->i) == "-" ) return $this->next();


    return array( $this->i, $this->bd->url($this->i), $this->bd->nome($this->i) , $this->bd->marca($this->i) );
  }
}

//---------------------------------------------

final class FSehtml_egaleria extends FS_ehtml {

  public function __construct() {
    parent::__construct();
    
    $this->width = "97%";

    $this->style_table = "border: 1px solid #333; padding-left: 3px;";
  }


  protected function inicia_saida() {
    return "<div>";
  }

  protected function finaliza_saida() {
    return "</div>";
  }


  protected function cabeceira($df = null) {
    return "";
  }

  protected function linha_detalle($df, $f) {
    //* NON pinta borrados.
    if ($f[3] == "-") return "";
    
    //* pintamos a foto.
//~ return "<pre>" . print_r($f, true) . "</pre>";

    return "
      <div class='epanoramix_tr'>
        <div>" . self::__chActivo($this, $f) . "</div>
        " . self::__imx($this, $f)      . "
        <div>" . self::__txt($this, $f) . "</div>
        <div>" . self::__bSup($this, $f)  . "</div>
      </div>";
  }


  private static function __chActivo(FSehtml_egaleria $ehtml, $f):string {
    return "";
    
    $b = new Checkbox("chActivo", $f[1]->activo == 1);
    
    $b->readonly = $ehtml->xestor->readonly;

    $b->envia_ajax("onclick");
    //~ $b->envia_submit("onclick");

    return $ehtml->__fslControl($b, strval($f[0]))->html();

  }

  private static function __imx(FSehtml_egaleria $ehtml, $f):string {
    //~ return "";
    
    $imx = $f[1];

    $s = "<div class='epanoramix_tr_2' style='background-image: url(\"{$imx}\");'></div>";

    return $s;
  }

  private static function __txt(FSehtml_egaleria $ehtml, $f):string {
    if (($t = $f[2]) == null) {
      $t = "Sin título";
    }
    else {
      $t = Valida::split($t, 37);
    }

    //~ if ($f[1]->a->href)
      //~ $a = "<div class='epanoramix_tr_3_2_marca' style='background-color: #cae0fb;'><a title='Ir a: {$f[1]->a->href}' href='{$f[1]->a->href}' target='_blank' style='text-decoration: none;'>&lt;a&gt;</a></div>";

    if ( file_exists($f[1]) ) {
      $peso = File::html_bytes( filesize($f[1]) );
      $tipo = mime_content_type($f[1]);
    }
    else {
      $peso = "--";
      $tipo = "--";
    }


    $l = new Link("beditar", $t);

    $l->envia_AJAX("onclick");
    //~ $l->envia_submit("onclick");

    $ehtml->__fslControl($l, strval($f[0]));
    
    //~ return "<div class='epanoramix_tr_3'>
              //~ <div>" . $l->html() . "</div>
              //~ <div>{$peso}&nbsp;&nbsp;&nbsp;{$tipo}</div>
            //~ </div>";
    
    return "<div class='epanoramix_tr_3' style='flex-direction: column;align-items: flex-start;gap: 0.3em;'>
              <div class='lista_elemento_a'>" . $l->html() . "</div>
              <div style='color: #555;'>{$peso}&nbsp;&nbsp;&nbsp;{$tipo}</div>
            </div>";
  }

  private static function __bSup(FSehtml_egaleria $ehtml, $f):string {
    if ($ehtml->xestor->readonly) return "";
    
    $b = Panel_fs::__bsup("bsup", null);

    $b->envia_ajax("onclick");
    //~ $b->envia_submit("onclick");

    return $ehtml->__fslControl($b, strval($f[0]))->html();
  }
}


/*
final class FSehtml_egaleria extends FS_ehtml {
  private $i = 0;
  private $c = 0;

  private $_td = null;

  public function __construct() {
    parent::__construct();

    $this->style_table = "";
    $this->align       = "center";
    $this->width       = "100%";
    $this->cellspacing = 0;
    $this->cellpadding = 0;
    $this->border      = 0;
  }

  protected function cabeceira($df = null) {
    $this->_td = null;
    $this->c   = $this->xestor->numcols();
    $this->i   = -1;

    return $this->xestor->obxeto("himxedit")->html() . "<tr valign='top'>";
  }

  protected function linha_detalle($df, $f) {
    $this->i++;

    if (($i = $f['0']) == 0) $i = "0";

    $a = ""; $t = "";
    if ($f[3] == 1) {
      $a = "alt='{$f[2]}'";
      $t = "title='{$f[2]}'";
    }

    $this->_td[$this->i % $this->c] .=
      self::bmove($this, $i) . self::bsup($this, $i) . "
      <img id='trwg2mini-{$i}'
        class='trw-g2-marco-mini'
        style='max-width: 100%; min-width: 88%; margin-top: -21px; position: relative;'
          src='{$f[1]}' {$a} {$t}
      onclick='galeria2_editor_foto_click({$i})' />";


    return " ";
  }

  protected function totais() {
    $w = 100 / $this->c;

    $std = "";

    for ($i = 0; $i < $this->c; $i++) {
      $this->_td[$i] = "<td align='right' width='{$w}%'>{$this->_td[$i]}</td>";

      $std .= $this->_td[$i];
    }

    return "{$std}</tr>";
  }

  private static function bmove(FSehtml_egaleria $ehtml, $i) {
    $b = new Button("bmove", "<img src='imx/accions/bMove.png' />");

    $b->title = "Pinchar y arrastra para mover la foto.";

    $b->clase_css("default", "trw-g2-bsup-01");

    $b->sup_eventos("onclick");
    $b->pon_eventos("onmousedown", "galeria2_bmove_mousedown(this)");

    //~ if ($i == 0) $i = "0";

    return $ehtml->xestor->adopta($b, $i)->html();
  }

  private static function bsup(FSehtml_egaleria $ehtml, $i) {
    $b = new Button("bsup", "<img src='imx/accions/bEliminar.png' />");

    $b->clase_css("default", "trw-g2-bsup-01");

    $b->envia_ajax("onclick", "Quieres eliminar la foto.");

    //~ if ($i == 0) $i = "0";

    return $ehtml->xestor->adopta($b, $i)->html();
  }
}
*/

