<?php

final class CXAF_imaxes extends    Componente {
                          
  const ptw_0  = "ptw/paneis/pcontrol/ventas/articulo/cxaf_imaxe_1.html";

  public $src_base = null;
  //~ public $src      = null;
  
  public $id_s = null;
  public $id_a = null;
  
  public function __construct() {
    parent::__construct("cxafimaxe", self::ptw_0);

    $this->visible = false;

    $this->pon_obxeto(self::__bmais());
    
    $this->pon_obxeto(new CXAFimx_lista());
  }

  public function pon_articulo(Articulo_obd $a) {
//~ echo "<pre>" . print_r($a->a_resumo(), true) . "</pre>";

    $this->id_a = $a->atr("id_articulo")->valor;

    $this->obxeto("cxafimxl")->pon_articulo($a);

    if ($this->src_base != null) return;


    $this->id_s = $a->atr("id_site")->valor;

    $this->src_base = Efs::url_site($this->id_s) . Refs::url_arquivos . "/";
    
    
    $this->pon_obxeto( self::__file( $this->src_base ) );
  }

  public function operacion(EstadoHTTP $e) {
    if (($e_aux = $this->operacion_ifile($e)) != null) return $e_aux;

    if (($e_aux = $this->obxeto("cxafimxl")->operacion($e)) != null) return $e_aux;

    return parent::operacion($e);
  }

  public function operacion_ifile(Epcontrol $e) {
    if (($ifile = $this->obxeto("ifile")) == null) return null;
    
    if (!$ifile->control_evento()) return null;

    $f  = $ifile->post_f(true, -1);
    
    
    
    $cbd = new FS_cbd();

    $cbd->transaccion();
    
    $a = Articulo_obd::inicia($cbd, $this->id_s, $this->id_a);
    
    if (!$a->iclogo_update($cbd, $f)) {
      $cbd->rollback();
      
      $e->post_msx("Sucedió un error al tratar de actualizar la BD");
    
      return $e;
    }
  
    $cbd->commit();

    $this->obxeto("cxafimxl")->pon_articulo($a);


    $this->preparar_saida( $e->ajax() );

    return $e;
  }

  private static function __file($src_tmp) {
    $f = new File("ifile", $src_tmp, 1024 * 1024 * 3);
    
    $f->multiple = true;

    $f->clase_css("default", "file_apagado");

    $f->accept      = "image/avif, image/gif, image/jpg, image/jpeg, image/png, image/webp";


    return $f;
  }

  private static function __bmais() {
    $b = Panel_fs::__bmais("bmais", "Añadir imágenes", "Añadir imágenes del producto");

    $b->pon_eventos("onclick", "tilia_file_click('findex;cxarticulos;cxaf;cxafimaxe;ifile')");


    return $b;
  }
}

//*******************************

final class CXAFimx_lista extends FS_lista
                       implements Iterador_bd, IXestorCEditor_imx {

  public $imx_proporcion = null;
                  
  private $url  = null;
  private $i_imx  = null;
  private $id_imx = null;
  
  private $i      = -1;
  private $_src   = null;
  
  public function __construct() {
    parent::__construct("cxafimxl", new CXAFimx_ehtml(), null);

    $this->pon_obxeto(new Hidden("h"));
    $this->pon_obxeto(new CEditor_imx());
  }

  protected function __iterador() {
    return $this;
  }

  public function pon_articulo(Articulo_obd $a) {
    $cbd = new FS_cbd();
    
    //* Lemos proporción.
    $this->imx_proporcion = $this->pai->pai->imx_proporcion;

    //* Lemos imaxes.
    $this->_src   = null;

    if (($src_aux = $a->iclogo_src($cbd, false)) == null) return;
    
    foreach ($src_aux as $id_logo => $url) $this->_src[] = array($url, $id_logo);
  }

  public function operacion(EstadoHTTP $e) {
    $evento = $e->evento();

//~ echo $evento->html();
 
    if ( $this->obxeto("h")->control_evento() ) {
      switch ($evento->tipo()) {
        case "onclick" : return $this->operacion_h_0($e);
        case "imx-move": return $this->operacion_h_1($e);
      }
    }
    
    if ($this->control_fslevento($evento, "bsup")) return $this->operacion_bsup($e, $evento->subnome(0));


    if (($e_aux = $this->obxeto("ceditor_imx")->operacion($e)) != null) return $e_aux;
    
    

    return parent::operacion($e);
  }

  public function __count() {
    if ( !is_array($this->_src) ) return 0;
    
    return count($this->_src);
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
    

    return array($this->i, $this->_src[$this->i]);
  }

  public function url(?string $k = null) { //* IMPLEMENTA IXestorCEditor_imx
    return $this->url;
  }

  public function post_url($url = null, ?string $k = null) { //* IMPLEMENTA IXestorCEditor_imx
    if ($this->id_imx == null) {
      $this->url = $url;
       
      $this->obxeto("ceditor_imx")->pon_src($this->url);

      return;
    }
    
    $url = $this->obxeto("ceditor_imx")->aceptar_imx();

    $cbd = new FS_cbd();

    $cbd->transaccion();

    $imx_obd = Elmt_obd::inicia($cbd, $this->id_imx, Articulo_imaxe_obd::tipo_bd);

    $imx_obd->post_url( $url );

    if (!$imx_obd->update($cbd)) {
      $cbd->rollback();

      return;
    }
    
    $cbd->commit();

    if (is_file($this->url)) unlink($this->url);

    $this->_src[$this->i_imx][0] = $url;
  }

  private function operacion_h_0(Epcontrol $e) {
    $this->obxeto("ceditor_imx")->config_fimaxe($e);
    
    $this->id_imx = null;
    $this->i_imx  = null;
    
    $_h = json_decode($this->obxeto("h")->valor());

//~ echo "<pre>" . print_r($_h, true) . "</pre>";

    $this->post_url($_h[0]);  //* IMPORTANTE facelo aquí 0.

    $this->id_imx = $_h[1];   //* IMPORTANTE facelo aquí 1.
    $this->i_imx  = $_h[2];   //* IMPORTANTE facelo aquí 2.
     
    $this->obxeto("ceditor_imx")->config_fimaxe( $e );

    return $this->obxeto("ceditor_imx")->operacion_logo($e, $this->imx_proporcion, false);
  }

  private function operacion_h_1(Epcontrol $e) {
    $_h = json_decode( $this->obxeto("h")->valor() );

//~ print_r($_h);
    
    //* 1.- intercambia posición das imaxes na BD, (para próximas lecturas).

    $cbd   = new FS_cbd();
    
    $id_s  = $this->pai->id_s;
    $id_a  = $this->pai->id_a;
    $id_la = $this->_src[ $_h[0] ][1];
    $id_lb = $this->_src[ $_h[1] ][1];


    $cbd->transaccion();

    if (!AImx_indice_obd::swap($cbd, $id_s, $id_a, $id_la, $id_lb)) {
      $cbd->rollback();

      $this->preparar_saida($e->ajax());

      return $e;
    }

    $cbd->commit();
    

    //* 2.- intercambia posición das imaxes na RAM, (non temos que volver a ler na BD).

    $_src_swap = $this->_src[ $_h[0] ];

    $this->_src[ $_h[0] ] = $this->_src[ $_h[1] ];
    $this->_src[ $_h[1] ] = $_src_swap;

    $this->preparar_saida($e->ajax());
    
    $e->obxeto("cxarticulos")->obxeto("cxal")->preparar_saida($e->ajax()); //* actualiza foto da lista

    return $e;
  }

  private function operacion_bsup(Epcontrol $e, $id_logo) {
    //~ echo $e->evento()->html();

    if ($id_logo == null) return $e;


    $cbd = new FS_cbd();

    $cbd->transaccion();

    $a = Articulo_obd::inicia($cbd, $this->pai->id_s, $this->pai->id_a);

    if (!$a->iclogo_delete($cbd, $id_logo)) {
      $cbd->rollback();

      $e->ajax()->pon_ok();
      

      return $e;
    }

    $cbd->commit();


    $this->pon_articulo($a);


    $this->preparar_saida($e->ajax());
    
    $e->obxeto("cxarticulos")->obxeto("cxal")->preparar_saida($e->ajax()); //* actualiza foto da lista


    return $e;
  }
}

//---------------------------------------------

final class CXAFimx_ehtml extends FS_ehtml {
  private $h_nome = null;

  private $ok_imx = true;
  
  public function __construct() {
    parent::__construct();

    $this->style_table = "padding: 11px 0;";
    $this->align       = "left";
    $this->width       = "100%";
    $this->cellspacing = 0;
    $this->cellpadding = 0;
    $this->border      = 0;
  }

  protected function cabeceira($df = null) {
    $this->ok_imx = true;
    
    if ($this->h_nome == null) $this->h_nome = $this->xestor->obxeto("h")->nome_completo();
    
    return "<tr><td style='background-color: #fff;'>" . $this->xestor->obxeto("h")->html() . "</td></tr>
            <tr><td style='background-color: #fff;'>";
  }

  protected function linha_detalle($df, $f) {
    $d = new Div( "dimx", "" );

    if (self::valida_proporcion($this, $f[1][0])) {
      $style_border = "border: 5px solid #aaa;";
    }
    else {
      $style_border = "border: 5px solid #ba0000;";

      $this->ok_imx = false;
    }

    $d->clase_css("default", "pcontrol_cxaf_imx_div");

    $d->style    ("default", "background-image: url({$f[1][0]}); {$style_border}");


    $d->post( self::bmove($this, $f) . self::brecortar($this, $f) . self::bsup($this, $f[1][1]) );


    $h = "";

    return "<div class='pcontrol_cxaf_imx'>" . $d->html() . "{$h}</div>";
  }

  protected function totais() {
    return "</td></tr>
            <tr><td style='background-color: #fff;'>" . self::erro_proporcion($this) . "</td></tr>";
  }

  private static function erro_proporcion(CXAFimx_ehtml $ehtml) {
    if ($ehtml->ok_imx) return "";

  
    return "<div class='txt_adv'>
              Las imágenes con borde rojo no tienen la proporción adecuada para la plantilla.
            </div>";
  }

  private static function valida_proporcion(CXAFimx_ehtml $ehtml, $url) {
    return AImx_indice_obd::valida_proporcion_url($url, $ehtml->xestor->imx_proporcion);
  }

  private static function bsup(CXAFimx_ehtml $ehtml, $id_logo) {
    $b = new Button("bsup", "&#10005;");

    $b->clase_css("default", "trw-g2-bsup-00");

    $b->title = "Pincha para borrar la foto";

    $b->envia_ajax("onclick", "Quieres eliminar la foto.");


    return $ehtml->xestor->adopta($b, $id_logo)->html();
  }

  private static function brecortar(CXAFimx_ehtml $ehtml, $f) {
    if ($f[0] == 0) $f[0] = "0";

    $b = new Button("brecortar{$f[0]}", "✂");

    $b->title = "Pincha para recortar la foto";

    $b->clase_css("default", "trw-g2-bsup-00");


    $_p = json_encode( array($ehtml->h_nome, $f[1][0], $f[1][1], $f[0]) );

    $b->pon_eventos("onclick", "cxaf_imx_click({$_p})");
    


    return $ehtml->xestor->adopta($b, $f[0])->html();
  }

  private static function bmove(CXAFimx_ehtml $ehtml, $f) {
    if ($f[0] == 0) $f[0] = "0";

    $b = new Button("bmove", "&#10012;");

    $b->title = "Pincha y arrastra para mover la foto";

    $b->clase_css("default", "trw-g2-bsup-00");
    //~ $b->style    ("default", "margin: 7px -57px;");

    $_p = json_encode( array($ehtml->h_nome, $f[0]) );

    $b->pon_eventos("onmousedown", "cxaf_imx_bmove_mousedown({$_p})");
    
    $b->sup_eventos("onclick");


    return $ehtml->xestor->adopta($b, $f[0])->html();
  }
}
