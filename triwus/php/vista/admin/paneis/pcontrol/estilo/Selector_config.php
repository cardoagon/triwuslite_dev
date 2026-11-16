<?php

final class CPlantilla extends    Componente
                       implements ICPC       {

  public $id_usuario;
  public $id_site;
  public $id_config;

  public function __construct(FGS_usuario $u) {
    parent::__construct("cplantilla", "ptw/paneis/pcontrol/estilo/cplantilla.html");

    $this->pon_obxeto(new Param("pinfo_0"));

    $this->pon_obxeto(Panel_fs::__bExportar("bexp", "Descargar"));
    
    $dtmp = IEC::__tmp(Efs::url_tmp());
        
    $this->pon_obxeto(new File( "fimp", $dtmp ));

    $this->pon_usuario($u);
  }

  public function pon_usuario(FGS_usuario $u) {
    $this->id_usuario = $u->atr("id_usuario")->valor;
    $this->id_site    = $u->atr("id_site"   )->valor;

    $cbd = new FS_cbd();

    $s = $u->site_obd($cbd);

    $this->id_config = $s->atr("id_config")->valor;

    $p = $s->config_obd()->plantilla_obd($cbd);

    $this->obxeto("pinfo_0")->post( self::html_1($p) );
  }

  public function cpc_miga() {
    return "tu diseño";
  }

  public function operacion(EstadoHTTP $e) {
    if ($this->obxeto("bexp")->control_evento()) return $this->operacion_bexp($e);
    if ($this->obxeto("fimp")->control_evento()) return $this->operacion_bimp($e);

    return null;
  }

  private function operacion_bexp(EstadoHTTP $e) {
    try {
      $cbd = new FS_cbd();
      
      $id_config = $this->id_config;
      
      if (($pl = IEC::id_plantilla($cbd, $id_config)) == -1){
        $e->post_msx( "ERROR. CPlantilla.operacion_bexp(). pl == -1." );
        
        return $e;
      }
      
//~ echo "#Plantilla::{$pl}<br>";
      
      $url_trw = TrwlConfig::pwd . "/plantilla/";
      $url_tmp = Efs::url_tmp();
//~ echo "$url_trw" . "<br>";
   
      if (($src = IEC_exp::executa($cbd, $url_trw, $url_tmp, $pl)) == null) {
        $e->post_msx( "ERROR. CPlantilla.operacion_bexp(). src == null." );
        
        return $e;
      }
//~ echo "$url_trw::$src" . "<br>";

      $e->redirect($src);
    }
    catch (Exception $ex) {
      $e->post_msx( $ex->getMessage() );
    }
    
    return $e;
  }

  private function operacion_bimp(EstadoHTTP $e) {
    try {

      $url_zip = $this->obxeto("fimp")->post_f(false);
      $url_tmp = Efs::url_tmp();

//~ echo "URL (zip, tmp)::(" . $url_zip . ", " . $url_tmp . ")\n";



      $cbd = new FS_cbd();
      
      $cbd->transaccion();
      
      if (($_info = IEC_imp::executa($cbd, $url_zip, $url_tmp)) == []) {
        $e->post_msx( "ERROR. CXSite_f.operacion_bPlantilla_imp(). _info == []." );
        
        $cbd->rollback();
        
        return $e;
      }

//~ print_r($_info); 


      $id_config = array_key_first( $_info["_p"] );
       
      if ( !$this->update_config($cbd, $id_config) ) {
        $e->post_msx( "ERROR. NON se pudo actualizar #Config." );
        
        $cbd->rollback();

        return $e;
      }
        
      $cbd->commit();
    
      $this->preparar_saida($e->ajax());
    
      $e->post_msx( "Plantilla importada correctamente.<br><pre>" . print_r($_info, 1) . "</pre>", "m" );
    
      return $e;
    }
    catch (Exception $ex) {
      $e->post_msx( $ex->getMessage() );
        
      $cbd->rollback();
      
      return $e;
    }
  }

  private function update_config(FS_cbd $tcbd, int $id_config):bool {
    //~ if ($id_config == $this->id_config_0) return true;
    
    $s = Site_obd::inicia($tcbd, $this->id_site);
    
    $s->atr("id_config")->valor = $id_config;
    
    return $s->update($tcbd);
  }

  private static function html_1(Plantilla_obd $p) {
    $a_info = $p->a_info();

    $p = (isset($a_info['escritorio']) )?"S&iacute;":"No";
    $m = (isset($a_info['mobil'])      )?"S&iacute;":"No";
    $c = ($a_info['id_usuario'] == null)?"S&iacute;":"No";

    // echo "<pre>" . print_r($a_info) . "</pre>";
    //$fontSize = trim(array_pop(explode(', ', $a_info['tipo'])));

    return "<div style='width:70%;display:grid; grid-template-columns: max-content 1fr;gap: 1em;'>
                <div><b>Nombre:</b></div>
                <div>" . ucfirst(strtolower($a_info['nome'])) . ", &nbsp;{$a_info['creado']}</div>
                <div><b>Tipografía:</b></div>
                <div>{$a_info['tipo']}</div>
                <div><b>Colores:</b></div>
                <div style='display:flex; flex-direction:column; gap: 0.5em;' >". self::html_bCores($a_info['cores']) . "</div>
            </div>";
  }

  private static function html_bCores($a_cores) {
    $html = "";

    foreach($a_cores as $id_config=>$a_cor) {
       $html .= " <div style='display:flex;align-items:center;gap: 1em;text-transform: uppercase;'>
                    <span style='display:inline-block;border-radius:100vmax; width:17px;height:17px;border: 1px #ccc solid;background-color: #{$a_cor[0]};'></span> #{$a_cor[0]}
                  </div>
                  <div style='display:flex;align-items:center;gap: 1em;text-transform: uppercase;'>
                    <span style='display:inline-block;border-radius:100vmax; width:17px;height:17px;border: 1px #ccc solid;background-color: #{$a_cor[1]};'></span> #{$a_cor[1]}
                  </div>
                  <div style='display:flex;align-items:center;gap: 1em;text-transform: uppercase;'>
                    <span style='display:inline-block;border-radius:100vmax; width:17px;height:17px;border: 1px #ccc solid;background-color: #{$a_cor[2]};'></span> #{$a_cor[2]}
                  </div>
                  <div style='display:flex;align-items:center;gap: 1em;text-transform: uppercase;'>
                    <span style='display:inline-block;border-radius:100vmax; width:17px;height:17px;border: 1px #ccc solid;background-color: #{$a_cor[3]};'></span> #{$a_cor[3]}
                  </div>";
    }

    return $html;
  }
}
