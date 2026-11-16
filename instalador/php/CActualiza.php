<?php

final class CActualiza extends Componente {  
  const ptw_00 = "ptw/cactualiza/00.html";
  const ptw_01 = "ptw/cactualiza/01.html";
  const ptw_02 = "ptw/cactualiza/02.html";
  
  const tmp = "tmp";
  
  private  array  $_info;
  private ?string $dspg = null;

  public function __construct(bool $instalar) {
    parent::__construct("cactualiza", self::ptw_02);

    if ( $instalar ) {
      $this->visible = false;
      
      return;
    }

    $this->pon_obxeto( new Text("info_ver", true) );
    $this->pon_obxeto( new Text("info_tit", true) );
    
    
    $this->pon_obxeto( new Hidden("hInstal") );
    
    $this->pon_obxeto( new Button("bVolver", "< Volver") );
    $this->pon_obxeto( new Button("bInstal", "✓ Actualizar") );
    
    $this->obxeto("bVolver")->pon_eventos("onclick", "window.history.back()");
    $this->obxeto("bInstal")->pon_eventos("onclick", "trwInst__bInstal()"   );
    
    $this->info_actualizar();
  }

  public function operacion(EstadoHTTP $e) { //* RETURN: ?EstadoHTTP.
//~ echo $e->evento()->html();

    if ( !$this->visible ) return null;

    if ($this->obxeto("hInstal")->control_evento()) return $this->operacion_hInstal($e);

    return null;
  }

  private function operacion_hInstal(EstadoHTTP $e):EstadoHTTP {
    try {
      switch ( $this->obxeto("hInstal")->valor() ) {
        case 1: return $this->operacion_hInstal_1($e); //* prepara
        case 2: return $this->operacion_hInstal_2($e); //* dspg
        case 3: return $this->operacion_hInstal_3($e); //* bd
        case 4: return $this->operacion_hInstal_4($e); //* parche
        case 5: return $this->operacion_hInstal_5($e); //* finaliza
      }
    }
    catch (Exception $ex) {
      $this->post_msx("CActualiza.operacion_hInstal(). hInstal descoñecido.");
      
      return $e;
    }
    
    $this->post_msx("CActualiza.operacion_hInstal(). hInstal descoñecido.");
    
    return $e;
  }

  private function operacion_hInstal_1(EstadoHTTP $e):EstadoHTTP {
    $_r = CActualiza_prepara::exec( $this->_info["seg"] );

    if ($_r[0] == false) {
      $this->dspg = null;
      
      $e->post_msx( $_r[1] );
      
      $e->ajax()->pon_ok(true, "trwInst__bInstal_i_resposta([1, false])");
      
      return $e;
    }
    
    
    $this->dspg = $_r[1];
    
    $e->ajax()->pon_ok(true, "trwInst__bInstal_i_resposta([1, true])"); //* msx ok.
    
    return $e;
  }

  private function operacion_hInstal_2(EstadoHTTP $e):EstadoHTTP {
    $_r = CActualiza_dspg::exec( $this->_info["seg"]["version"], $this->dspg );

    if ($_r[0] == false) {
      $this->dspg = null;
      
      $e->post_msx( $_r[1] );
      
      $e->ajax()->pon_ok(true, "trwInst__bInstal_i_resposta([2, false])");
      
      return $e;
    }

    //~ $e->post_msx("dspg: {$this->dspg}");
    //~ $e->post_msx("error al actualizar el código fuente");
    
    $e->ajax()->pon_ok(true, "trwInst__bInstal_i_resposta([2, true])");
    
    return $e;
  }

  private function operacion_hInstal_3(EstadoHTTP $e):EstadoHTTP {
    sleep(2);
    
    $e->ajax()->pon_ok(true, "trwInst__bInstal_i_resposta([3, true])");
    
    return $e;
  }

  private function operacion_hInstal_4(EstadoHTTP $e):EstadoHTTP {
    
    //~ $e->post_msx("Versión actualizada correctamente");
    
    $e->ajax()->pon_ok(true, "trwInst__bInstal_i_resposta([4, true])");
    
    return $e;
  }

  private function operacion_hInstal_5(EstadoHTTP $e):EstadoHTTP {
    sleep(2);
    
    $_SESSION = null;
    
    $e->ajax()->pon_ok(true, "trwInst__bInstal_i_resposta([5, true])");
    
    return $e;
  }

  private function info_actualizar():void {
    $this->_info = Refs::sActu_info();
    
echo "<pre>" . print_r($this->_info, 1) . "</pre>";
    
    if ( isset($this->_info["err"]) ) {
      $this->ptw = self::ptw_02;
      
      return;
    }
    
    
    if ( $this->_info["actualizar"] != 1 ) {
      $this->ptw = self::ptw_00;
      
      return;
    }
    
    
    $this->ptw = self::ptw_01;
    
    $this->obxeto("info_ver")->post( $this->_info["seg"]["version"] );
    $this->obxeto("info_tit")->post( $this->_info["seg"]["titulo" ] );
  }
}

//**************************

final class CActualiza_prepara {
  public static function exec(array $_info):array {
    sleep(1);
    
    $tmp = CActualiza::tmp;
    
    XestorDescargas::borra_antigos($tmp, 0);
    
    try {
      //* descarga arquivo de despliege.
      $dspg = Refs::sActu_dspg($_info["dspg"]);
            
      if ( $dspg == null ) 
        return [false, "CActualiza_prepara.exec(). dspg == null."];
        

      //* crea arquivo tmp de despliegue.
      $f_dspg = "{$tmp}/" . basename($_info["dspg"]);
      
      if ( @file_put_contents($f_dspg, $dspg) === false) 
        return [false, "CActualiza_prepara.exec(). !file_put_contents()."];
        

      //* prepara arquivo zip de despliegue.      
      return  self::dspg_unzip($f_dspg);
    }
    catch ( Exception $ex ) {
      return [false, "CActualiza_prepara.exec().</br>" . $ex->getMessage()];
    }
  }
  
  private static function dspg_unzip(string $url_zip):array {
    $tmp   = dirname($url_zip);
    $dir_0 = getcwd();
    
    if ( @chdir($tmp) === false ) {
      return [false, "CActualiza_prepara.dspg_unzip(). chdir(tmp) === false."];
    }


    $f_zip = basename($url_zip);
    $d_zip = basename($url_zip, ".zip");

    passthru("unzip -qq {$f_zip}", $result_code);
    if ( $result_code != null ) {
      return [false, "CActualiza_prepara.dspg_unzip(). result_code == {$result_code}."];
    }

    
    if ( @chdir($dir_0) === false ) {
      return [false, "CActualiza_prepara.dspg_unzip(). chdir(dir_0) === false."];
    }


    return [true, "{$tmp}/{$d_zip}"];
  }
}
  

//**************************

final class CActualiza_dspg {
  const ptw_config = "dist/Config.php";

  public static function exec(string $version, string $src_pack):array {

    $src_pack = $src_pack . "/fonte/triwus";
    $src_dspg = TrwlConfig::pwd . "/triwus";

   
    //~ return [false, "$src_pack::$src_dspg"];

    if (!is_dir($src_pack)) return [true];
    
    
    if (!is_dir($src_dspg)) return [false, "CActualiza_dspg::__exec(), !is_dir({$src_dspg})"];

    try {
      self::__dspg_f($src_pack, $src_dspg, "editor.php");
      self::__dspg_f($src_pack, $src_dspg, "pcontrol.php");
      self::__dspg_f($src_pack, $src_dspg, "manifesto.php");
      self::__dspg_f($src_pack, $src_dspg, "manifesto_adminsite.php");
      
      self::__dspg_dir($src_pack, $src_dspg, "php"  );
      self::__dspg_dir($src_pack, $src_dspg, "ptw"  );
      self::__dspg_dir($src_pack, $src_dspg, "css"  );
      self::__dspg_dir($src_pack, $src_dspg, "js"   );
      self::__dspg_dir($src_pack, $src_dspg, "imx"  );
      self::__dspg_dir($src_pack, $src_dspg, "tilia");
      
      self::__dspg_config($version, $src_dspg);
    }
    catch(Exception $ex) {
      return [false, $ex->getMessage()];
    }
    

    return [true];
  }

  private static function __dspg_f($src_pack, $src_dspg, $f):void {
    if (!is_file("{$src_pack}/{$f}")) return;
    
    if (($s = self::__exec("rm -r {$src_dspg}/{$f}")) > 0) {
      throw new Exception("ERROR({$s}). rm -r {$src_dspg}/{$f}");
      
      return;
    }
    
    if (($s = self::__exec("cp -r {$src_pack}/{$f}  {$src_dspg}")) > 0) {
      throw new Exception("ERROR({$s}). cp -r {$src_pack}/{$f}  {$src_dspg}");
      
      return;
    }
  }

  private static function __dspg_dir($src_pack, $src_dspg, $dir):void {
    if (!is_dir("{$src_pack}/{$dir}")) return;

    if (($s = self::__exec("rm -r {$src_dspg}/{$dir}")) > 0) {
      throw new Exception("ERROR({$s}). rm -r {$src_dspg}/{$dir}");
      
      return;
    }
    
    if (($s = self::__exec("cp -r {$src_pack}/{$dir}  {$src_dspg}")) > 0) {
      throw new Exception("ERROR({$s}). cp -r {$src_pack}/{$dir}  {$src_dspg}");
      
      return;
    }
  }

  private static function __dspg_config($version, $src_dspg):void {
    $_c = [];
    
    $_c["version"] = $version;
    $_c["dominio"] = TrwlConfig::dominio;
    $_c["mysql_h"] = TrwlConfig::mysql_h;
    $_c["mysql_b"] = TrwlConfig::mysql_b;
    $_c["mysql_u"] = TrwlConfig::mysql_u;
    $_c["mysql_p"] = TrwlConfig::mysql_p;
    $_c["pwd"    ] = TrwlConfig::pwd    ;
    $_c["priv"   ] = TrwlConfig::priv   ;
    
        
    $f = @file_get_contents(self::ptw_config);

    if ($f === false) {
      throw new Exception("CActualiza_dspg::__dspg_config(), f === false");
      
      return;
    }

    foreach ($_c as $k=>$v) $f = str_replace("[{$k}]", $v, $f);
    

    if ( !@file_put_contents("{$src_dspg}/Config.php", $f) ) {
      throw new Exception("CActualiza_dspg::__dspg_config(), !file_put_contents()");
      
      return;
    }
  }

  private static function __exec($sh):int {
    $result_code = null;

    //~ echo "$sh\n\n";

    //~ exec($sh, $output, $return_var);
    passthru($sh, $result_code);
    
    return $result_code;
  }
}
  
//**************************

final class CActualiza_bd {
  public static function exec(string $src_pack):array {
    //~ return [false, "$src_pack::$src_dspg"];


    $src_pack = $src_pack . "/fonte/sql";

    if (!is_dir($src_pack)) return [true];
    
    
    $f_sql = "{$src_pack}/bd.sql";
    
    if (!is_file($f_sql)) return [false, "CActualiza_bd::__exec(), !is_file($f_sql)"];
    
    
    $_sql = explode( ";", file_get_contents("{$tmp}bd.sql") );

//~ print_r($_sql);

    if (!is_array($_sql)) return [false, "CActualiza_bd::__exec(), !is_array(_sql)"];

    
    $cbd = new FS_cbd();
    
    $cbd->transaccion();
    
    foreach ($_sql as $sql) {
      if (trim($sql) == "") continue;
      
      if (!$cbd->executa($sql)) {
        $cbd->rollback();
        
        return [false, "CActualiza_bd::__exec(), !cbd->executa($sql)"];
      }
    }
    
    $cbd->commit();
    

    return [true];
  }
}
