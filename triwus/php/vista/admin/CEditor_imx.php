<?php

final class CEditor_imx extends Componente implements IVM_pcontrol_ficha {    
  private $k = null;
  
  private $url_arquivos = null;
  private $url_nula     = null;
  
  public function __construct($url_nula = null, ?string $k = null) {
    parent::__construct("ceditor_imx", "ptw/ceditor_imx.html");
    
    $this->k = $k;

    $this->pon_obxeto( new Hidden("m") );
    $this->pon_obxeto( self::__imx()   );
    $this->pon_obxeto( self::__limx()  );
    
    $this->pon_src_nula($url_nula); //* importante. PRECOND. this.url_nula != null
  }

  public function pon_src($url = null) {
    if ($url == null) {
      $this->obxeto("imx")->pon_src($this->url_nula);
      
      return $this->url_nula;
    }
      
    
    $this->obxeto("imx")->pon_src($url);
    
    if (($fimx = $this->obxeto("fimx")) != null) $fimx->vincular_f($url);
    
    
    return $url;
  }

  public function pon_src_nula($url = null) {
    if ($url == null) $url = Imaxe_obd::noimx;
    
    $this->url_nula = $url;
    
    if (($src = $this->obxeto("imx")->src()) != null) if (is_file($src)) return $src;
    
    return $this->pon_src();
  }
  
  public function config_fimaxe(Epcontrol $e, $url = null) {
    if ($this->url_arquivos != null) return;
    
    $id_js  = $this->nome_completo();

    $this->obxeto("limx")->pon_eventos("onclick", "pcontrol_xpax_limx_click('{$id_js}')");
    

    //~ $this->url_arquivos = $e->url_arquivos($e->id_site);
    $this->url_arquivos = Refs::url_arquivos() . "/";
      
    $this->pon_obxeto( self::__fimx(  Efs::url_tmp( $this->url_arquivos ) ) );
  }

  public function validar() {
    return true;
  }

/*
  public function aceptar_imx() {
    if (($fimx = $this->obxeto("fimx")) == null) return null;
        
    return $fimx->aceptar_f( $this->url_arquivos );
  }
*/

  public function aceptar_imx() {
    if (($fimx = $this->obxeto("fimx")) == null) return null;
        
    return $fimx->f(); //* Imaxe_obd. encargarase da xestión tmp.
  }

  public function operacion(EstadoHTTP $e) {
    if (($fimx = $this->obxeto("fimx")) != null) {
      if ($fimx->control_evento()) {
        $fimx->post_f();
        
        $this->pon_src( $fimx->f() );


        return $this->operacion_logo($e); 
      }
    }

    return parent::operacion($e);
  }
  
  public function operacion_bCancelar(Epcontrol $e) {
    //~ $this->hai_cambios = false;
    
    $this->pon_src( $this->pai->url($this->k) );

    $e->ajax()->pon_ok();

    return $e;
  }
  
  public function operacion_bAceptar (Epcontrol $e) {
    $src_aux = $this->obxeto("imx")->src();
    
    list($x, $y, $w, $h) = explode(Escritor_html::csubnome, $this->obxeto("m")->valor());

    $fimx    = $this->obxeto("fimx");
    
//~ echo "(x, y, w, h)::($x, $y, $w, $h)<br>\n";
//~ echo "src::" . $this->obxeto("imx")->src() . "<br>\n";

    if ($fimx == null) {
      $this->config_fimaxe($e);
      
      $fimx->vincular_f( $src_aux );
    }
    elseif($fimx->f() == null) {
      $fimx->vincular_f( $src_aux );
    }
    
//~ echo "fimx->f()::" . $fimx->f() . "\n";


    if ( $fimx->recortar($x, $y, $w, $h) ) {
      $this->pai->post_url( $fimx->f(), $this->k );
      
      //~ $this->hai_cambios = true;  
    }
    else {
      $e->post_msx("ERROR, al guardar la imagen.");
    }

    $this->pai->preparar_saida( $e->ajax() );


    return $e;
  }

  public function operacion_logo(EstadoHTTP $e, $imx_proporcion = "NaN", $limx_visible = true) {
    if ($limx_visible) {
      $this->obxeto("limx")->visible = true; 
      
      $this->config_fimaxe($e);
    }
    else {
      $this->obxeto("limx")->visible = false; 
    }
    
    
    
    $v = $e->obxeto("vm_pcontrol_aux");
    
    $t = "Editar foto";

    $id_js = $this->nome_completo();
    
    $f_ini = "pcontrol_xpax_logo_click('{$id_js}', {$imx_proporcion})";
    $f_bac = "pcontrol_xpax_vm_aceptar('{$id_js}')";
 

    return $v->operacion_ficha($e, $this, $t, $f_ini, $f_bac);
  }

  public static function __logo($id = "logo") {
    $l = new Div($id);

    //~ $l->envia_SUBMIT("onclick");
    $l->envia_AJAX("onclick");


    return $l;
  }

  public static function __style_logo($url, $readonly = false, $halign = "center") {
    $sr = ($readonly)?"":"cursor: pointer;";
    
    return "height:111px;
            {$sr}
            background-image: url(\"{$url}\");
            background-position: center {$halign};
            background-repeat: no-repeat;
            background-size: contain;";
          
  }

  private static function __imx() {
    $l = new Image("imx");

    $l->style("default" , "max-width: 88%;");


    return $l;
  }

  private static function __limx() {
    $l = new Div("limx", "Cambiar Imaxe");

    $l->clase_css("default", "texto2_a");
    $l->style    ("default", "cursor: pointer; padding: 9px 0;text-align: center;");


    return $l;
  }

  private static function __fimx($src_tmp) {
    $f = new File("fimx", $src_tmp, 1024 * 1024 * 3);

    $f->clase_css("default", "file_apagado");

    $f->accept      = "image/gif, image/jpg, image/jpeg, image/png, image/webp";


    return $f;
  }
}
