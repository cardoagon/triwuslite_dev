<?php

final class CPwa extends Componente implements ICPC {

  const ptw_1 = "ptw/paneis/pcontrol/apis/pwa.html";

  private $id_site  = null;
  
  //~ private $src_site = null;
  private $src_icos = null;
 
  public function __construct(int $id_site) {
    parent::__construct("cpwa", self::ptw_1);
    
    $this->id_site  = $id_site;
    

    $this->pon_obxeto( new Checkbox("activo", true, "Activar / Desactivar PWA.") );
    
    $this->pon_obxeto(new Text    ("nome_l"));
    $this->pon_obxeto(new Text    ("nome_c"));
    $this->pon_obxeto(new Textarea("descri"));

    $this->pon_obxeto(new Color   ("color_p"));
    $this->pon_obxeto(new Color   ("color_f"));

    $this->pon_obxeto(new CPwa_ico("logo_0" , "Principal"));
    $this->pon_obxeto(new CPwa_ico("logo_m" , "Adaptable", "maskable"));
    
    //~ $this->pon_obxeto(new CPwa_ico("logo_0" , "Icono Estándar (512x512px)", "Este es el icono principal de tu App. Se usará en la mayoría de los dispositivos y se mostrará en la lista de aplicaciones y en los avisos de instalación."));
    //~ $this->pon_obxeto(new CPwa_ico("logo_m" , "Es un icono cuadrado pensado para que los sistemas modernos (como Android 8+ o algunos navegadores) puedan recortarlo automáticamente y mostrarlo en distintas formas: redondo, cuadrado con bordes redondeados, gota, etc."));


    $this->pon_obxeto( Panel_fs::__bcancelar() );      
    $this->pon_obxeto( Panel_fs::__baceptar () );      // [bAceptar]


    $this->obxeto("nome_l")->maxlength = 99;
    $this->obxeto("nome_c")->maxlength = 12;

    $this->obxeto("descri")->maxlength = 255;
    $this->obxeto("descri")->style("default", "");
    
    
    $this->obxeto("activo"   )->envia_AJAX("onclick");
    
    $this->obxeto("bAceptar" )->envia_AJAX("onclick");
    $this->obxeto("bCancelar")->envia_AJAX("onclick");
    
    $this->inicia();
  }

  public function cpc_miga() {
    return "PWA";
  }

  public function __obd(FS_cbd $cbd = null):Site_pwa_obd {
    if ($cbd == null) $cbd = new FS_cbd();
    
    $o = Site_pwa_obd::inicia($cbd, $this->id_site);
    
    $o->atr("nome_l" )->valor = trim( $this->obxeto("nome_l" )->valor() );
    $o->atr("nome_c" )->valor = trim( $this->obxeto("nome_c" )->valor() );
    $o->atr("descri" )->valor = trim( $this->obxeto("descri" )->valor() );
    $o->atr("color_p")->valor = trim( $this->obxeto("color_p")->valor() );
    $o->atr("color_f")->valor = trim( $this->obxeto("color_f")->valor() );
    
    $o->atr("activo" )->valor = ( $this->obxeto("activo" )->valor() ) ? 1 : null;


//~ echo "<pre>" . print_r($o->a_resumo(), 1) . "</pre>";

    return $o;
  }

  public function operacion(EstadoHTTP $e) {
    //echo $e->evento()->html();
    
    if ($this->obxeto("activo"   )->control_evento()) return $this->operacion_activo   ($e);
    
    if ($this->obxeto("bAceptar" )->control_evento()) return $this->operacion_baceptar ($e);
    if ($this->obxeto("bCancelar")->control_evento()) return $this->operacion_bcancelar($e);


    if (($e_aux = $this->obxeto("logo_0")->operacion($e)) != null) return $e_aux;
    if (($e_aux = $this->obxeto("logo_m")->operacion($e)) != null) return $e_aux;


    return null;
  }



  private function inicia():void {
    $cbd = new FS_cbd();

    //~ $this->src_site = Efs::url_site($this->id_site, $cbd);
    //~ $this->src_icos = $this->src_site . Refs::url_arquivos . "/";
    $this->src_icos = Refs::url_arquivos();


    $pwa = Site_pwa_obd::inicia($cbd, $this->id_site);
    
    $this->obxeto("logo_0")->inicia($cbd, $pwa);
    $this->obxeto("logo_m")->inicia($cbd, $pwa);
    
    if ( $pwa->baleiro() ) {
      $this->inicia_baleiro($cbd);
      
      return;
    }
    
    
    $this->obxeto("nome_l" )->post( $pwa->atr("nome_l" )->valor );
    $this->obxeto("nome_c" )->post( $pwa->atr("nome_c" )->valor );
    $this->obxeto("descri" )->post( $pwa->atr("descri" )->valor );
    $this->obxeto("color_p")->post( $pwa->atr("color_p")->valor );
    $this->obxeto("color_f")->post( $pwa->atr("color_f")->valor );
    $this->obxeto("color_f")->post( $pwa->atr("color_f")->valor );
    $this->obxeto("activo" )->post( $pwa->atr("activo" )->valor == 1 );
  }  

  private function inicia_baleiro(FS_cbd $cbd):void {
    $s  = Site_obd::inicia($cbd, $this->id_site);
    $sm = $s->metatags_obd($cbd);
    $sc = $s->config_obd("escritorio", $cbd);

//~ echo "<pre>" . print_r($s ->a_resumo(), 1) . "</pre>";
//~ echo "<pre>" . print_r($sm->a_resumo(), 1) . "</pre>";
//~ echo "<pre>" . print_r($sc->a_resumo(), 1) . "</pre>";
    
    $this->obxeto("nome_l" )->post($sm->atr("titulo_txt" )->valor);
    $this->obxeto("nome_c" )->post(TrwlConfig::dominio);
    $this->obxeto("descri" )->post($sm->atr("description")->valor);
    $this->obxeto("color_p")->post("#" . $sc->atr("color"      )->valor);
    $this->obxeto("color_f")->post("#" . $sc->atr("color_fondo")->valor);
    $this->obxeto("activo" )->post( true );
  }  
 

  private function operacion_activo(Epcontrol $e):Epcontrol {
    $b = $this->obxeto("activo")->valor();
    
    $this->obxeto("logo_0")->actualizar($b);
    $this->obxeto("logo_m")->actualizar($b);
    
    $e->ajax()->pon_ok();


    return $e;
  }

  private function operacion_baceptar(Epcontrol $e):Epcontrol {
    //echo "procesamos el evento: bAceptar.<br>";
    try {
      $cbd = new FS_cbd();
      
      $o = $this->__obd($cbd);
      
      if (($erro = $this->validar($o)) != null) {
        $e->post_msx($erro);

        return $e;
      }
      
      
      $cbd->transaccion();
      
      if ( !$this->update($cbd, $o) ) {
        $e->post_msx("Sucedió un error al actualizar la BD.");

        $cbd->rollback();
        
        return $e;
      }


      $cbd->commit();

      
      $m = ($this->obxeto("activo" )->valor()) ? "La configuración PWA está ACTIVA." : "La configuración PWA está DESACTIVADA." ;
      
      $e->post_msx($m, "m");

      $this->preparar_saida($e->ajax());
    }
    catch (Exception $ex) {
      $e->post_msx($ex->getMessage());

      $cbd->rollback();
      
      return $e;
    }


    return $e;
  }

  private function operacion_bcancelar(Epcontrol $e):Epcontrol {
    $this->inicia();
    
    
    $e->post_msx("La configuración PWA ha sido reestablecida al estado anterior.", "m");
    
    $this->preparar_saida($e->ajax());

    return $e;
  }

  private function validar(Site_pwa_obd $o):?string {
    if ( !$this->obxeto("activo" )->valor() ) return null;

    $erro = null;
    
    if ( ($erro = $o->__validar()                   ) != null) return $erro;
    
    if ( ($erro = $this->obxeto("logo_0")->validar()) != null) return $erro;
    if ( ($erro = $this->obxeto("logo_m")->validar()) != null) return $erro;
    
    
    return $erro;
  }

  private function update(FS_cbd $cbd, Site_pwa_obd $o):bool {
    try {
      if ( !$o->update($cbd) ) return false;

      //* icos.
      $ico_0 = $this->obxeto("logo_0");
      $ico_m = $this->obxeto("logo_m");
      
      if ( !$o->iclogo_update($cbd, $ico_0->url($this->src_icos), "logo_0") ) return false;
      if ( !$o->iclogo_update($cbd, $ico_m->url($this->src_icos), "logo_m") ) return false;

//~ echo "1111111111111111111111111\n";

      //* web.manifest.
      $this->update_manifest($o);


//~ echo "2222222222222222222222\n";


      //* logos.
      if ($ico_0->aceptar_logo($this->src_icos) == null) return false;
      if ($ico_m->aceptar_logo($this->src_icos) == null) return false;

      
      return true;
    }
    catch (Exception $ex) {
      throw $ex;
      
      return false;
    }
  }

  private function update_manifest(Site_pwa_obd $o):void {
    //~ if (!is_dir($this->src_site)) {
      //~ throw new Exception("!is_dir('{$this->src_site}')");
      
      //~ return;
    //~ }
    
    try {
      $f = "../app.webmanifest";
      
      if ( is_file($f) ) unlink($f);
      
      if ( $o->atr("activo")->valor != 1) return;
      
      $ico_0 = $this->obxeto("logo_0");
      $ico_m = $this->obxeto("logo_m");
      
      
      $_m = [
          "id"               => "/",
          "name"             => $o->atr("nome_l")->valor,
          "short_name"       => $o->atr("nome_c")->valor,
          "description"      => $o->atr("descri")->valor,
          "start_url"        => "/",
          "display"          => "standalone",
          "orientation"      => "any",
          "background_color" => $o->atr("color_f")->valor,
          "theme_color"      => $o->atr("color_p")->valor,
          "icons"            => [
              [
              "src"   => Refs::url_arquivos(basename($ico_0->url())),
              "type"  => $ico_0->tip,
              "sizes" => $ico_0->dim
              ],
              [
              "src"     => Refs::url_arquivos(basename($ico_m->url())),
              "type"    => $ico_m->tip,
              "sizes"   => $ico_m->dim,
              "purpose" => "maskable"
              ]
          ]
      ];
      
      if ( !file_put_contents($f, json_encode($_m, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)) ) {
        throw new Exception("CPwa.update_manifest(), !file_put_contents(). ");
        
        return;
      }
    }
    catch (Exception $ex) {
      throw $ex;
    }
  }

  private static function apk_logo(string $k):string {
    $l = new Div($k);
  
    $l->envia_AJAX("onclick");
  
    $l->clase_css("default", "paxinfo_logo");
  
    return $l;
  }
}


//********************************************


final class CPwa_ico extends Componente {
  const ptw_0 = "ptw/paneis/pcontrol/apis/pwa_ico.html";
  
  public ?string $dim = null; //* dimensión (px x px).
  public  int    $lad = 0;    //* lado (px).
  public  float  $pro = 0;    //* proporción.
  public ?string $tip = null; //* tipo.
  
  private $url = null;

  private bool $actualizar = false;
  
 
  public function __construct(string $k, string $etq, string $axu = "") {
    parent::__construct($k, self::ptw_0);
    
    $this->pon_obxeto( new Param("etq"  , $etq ) );
    $this->pon_obxeto( new Param("axu"  , $axu ) );
    $this->pon_obxeto( new Param("props", ""   ) );
    $this->pon_obxeto( self::_logo ()            );
    $this->pon_obxeto( self::_flogo()            );
  }

  public function inicia(FS_cbd $cbd, Site_pwa_obd $pwa):void {
    $url = ($pwa->baleiro()) ? null : $pwa->iclogo_src($cbd, $this->nome);
    
    $this->post_url($url);
  }  

  public function operacion(EstadoHTTP $e) {
    //echo $e->evento()->html();
    
    if ($this->obxeto("flogo")->control_evento()) return $this->operacion_flogo($e);
    
    
    return null;
  }


  public function actualizar(bool $b):void {
    $this->actualizar = $b;
  }

  public function aceptar_logo(string $src):?string {
//~ echo "CPWA->url. $this->url\n";

    if ( !$this->actualizar ) return $this->url;
    
    $url = $this->obxeto("flogo")->aceptar_f("{$src}/");

//~ echo "CPWA.aceptar_logo($src). $url\n";

    if ($url == null) return $this->url; //* nos vale se existe unha URL anterior.
    
    $this->post_url($url);
    
    $this->actualizar = false;
    
    return $url;
  }

  public function validar():?string {
    if ( !$this->actualizar ) return null;
    
    
    $erro = null;
    $etq  = $this->obxeto("etq"  )->valor();
    $acc  = $this->obxeto("flogo")->accept;
    
    if ($this->url == null            ) return "Error, {$etq}. Debes subir una imagen cuadrada [192px ... 1024px].<br>";
    if ($this->url == Imaxe_obd::noimx) return "Error, {$etq}. Debes subir una imagen cuadrada [192px ... 1024px].<br>";

//~ echo "{$etq}: Pro: {$this->pro}<br>";

    if ( (strpos($acc, $this->tip) === false) ) $erro .= "Error, {$etq}. tipo de imagen '{$this->tip}' no válido. Tipos válidos: '{$acc}'.<br>";
    
    if ($this->pro != 1 ) return "Error, {$etq}. Debe ser una imagen cuadrada [192px ... 1024px].<br>";
    
    if (($this->lad < 192) || ($this->lad > 1024)) $erro .= "Error, {$etq}. Ancho {$this->lad}px, fuera del rango [192px ... 1024px].<br>";
    
    
    return $erro;
  }


  public function url(string $src = null):string {
//~ echo "CPwa_ico::url($src)::{$this->url}\n";

    if ($src == null) return $this->url;

    return $src . "/" . basename($this->url);
  }

  private function post_url(string $url = null, ?string $k = null):void {
    $this->post_props($url);

    if ($url == null) $url = Imaxe_obd::noimx;
    
    $this->url = $url;

    $this->obxeto("logo")->style("default" , "background-image: url({$this->url});cursor: pointer;");
    $this->obxeto("logo")->style("readonly", "background-image: url({$this->url});"                );
  }

  private function operacion_flogo(EstadoHTTP $e):EstadoHTTP {
    $this->actualizar = true;
    
    $f = $this->obxeto("flogo")->post_f();
    
    $this->post_url($f);

    $this->preparar_saida( $e->ajax() );
    
    return $e;
  }
  
  private function post_props(?string $url):void {
    if ( !is_file( strval($url) ) ) {
      $this->dim = null;
      $this->lad = 0;
      $this->pro = 0;
      $this->tip = null;
      
      $this->obxeto("props")->post("");
      
      return;
    }
    
    $_p = getimagesize($url);

    $this->dim = "{$_p[0]}x{$_p[1]}";
    $this->lad = $_p[0];
    $this->pro = $_p[0] / $_p[1];
    $this->tip = $_p["mime"];
    
    $s  = "
<ul style='margin: 0;padding: 1em;'>
  <li>({$this->dim})px</li>
  <li>{$this->tip}</li>
</ul>";
    
    
    $this->obxeto("props")->post($s);
  }
  
  private static function _logo():Div {
    $l = new Div("logo");
  
    $l->pon_eventos("onclick", "pcontrol_pwa_logo_click(this)");
  
    $l->clase_css("default" , "paxinfo_logo");
    $l->clase_css("readonly", "paxinfo_logo");
  
    return $l;
  }
  
  private static function _flogo():File {
    //~ $f = new File("flogo", Efs::url_tmp(), ((1024 * 1024) / 4));
    $f = new File("flogo", Refs::url_arquivos(Refs::url_tmp . "/"), ((1024 * 1024) / 4));

    $f->accept = "image/png, image/webp";
    
    $f->clase_css("default" , "file_apagado");
    $f->clase_css("readonly", "file_apagado");
  
    return $f;
  }
}
