<?php

final class IEC_imp {
  const tmp = "imp";
  
  public static function executa(FS_cbd $cbd, string $url_zip, string $url_tmp_0):array {
    //* valida parám.
    if ($url_zip == null) {
      throw new Exception( "IEC_imp::executa(). url_zip == null" );
      
      return [];
    }
    
    if (!is_file($url_zip)) {
      throw new Exception( "\nErro, !is_file('{$url_zip}').");
      
      return [];
    }
    
    //* prepara.
    try {
      $tmp = self::__i_prepara($url_zip, $url_tmp_0);

//~ echo "carpeta-zip-descomprimido::$tmp\n";
      
      if ($tmp == null) {
        throw new Exception("IEC_imp::executa(). tmp == null");
        
        return [];
      }
    }
    catch (Exception $ex) { 
      throw $ex;
      
      return [];
    }

    //* calcula info json.
    try {
      $_info = self::__i_json($tmp);
      
//~ print_r($_info);
      
      if ($_info == []) {
        throw new Exception("IEC_imp::executa(). _info == []");
        
        return [];
      }
    }
    catch (Exception $ex) { 
      throw $ex;
      
      return [];
    }

    //* importamos arquivos.
    try {
      self::__i_arquivos($tmp);
    }
    catch (Exception $ex) {
      throw $ex;
      
      return [];
    }
    
    //~ //* importamos sql
    if ( !self::__i_sql($cbd, $tmp) ) {
      throw new Exception( "Erro, erro importar SQL." );
      
      return [];
    }

    //* FIN.
    return $_info;
  }


  private static function __i_prepara(string $url_zip, string $url_tmp_0):?string {
    //* resetea carpeta temporal para importar unha plantilla.
    
    $tmp = dirname($url_zip);
    
    //* descomprime plantilla.
    $dir_0 = getcwd();

    IEC::__chdir( $tmp );
    IEC::__system("unzip -qq " . basename($url_zip));
    IEC::__chdir($dir_0);
    
    //* calcula carpeta temporal da plantilla descomprimida.
    if (($tmp = self::__i_prepara_pd($tmp)) == null) {
      throw new Exception("ERROR: calcula carpeta temporal da plantilla descomprimida.");
      
      return null;
    }

    return $tmp;
  }

  private static function __i_prepara_pd(string $tmp):?string {
    $_d  = scandir($tmp);
//~ print_r($_d);    
    foreach ($_d as $x) {
      if ($x == "." ) continue;
      if ($x == "..") continue;
      
      if (is_dir("{$tmp}/{$x}")) return "{$tmp}/{$x}/";
    }
    
        
    return null;
  }

  private static function __i_json(string $tmp):array {
    $_json = [];
    
//~ echo "URL-json::{$tmp}info.json\n";
    
    try {
      $_json = json_decode( file_get_contents("{$tmp}info.json"), true );
    }
    catch (Exception $ex) {
      throw $ex;
    }

    
    return $_json; 
  }

  private static function __i_arquivos(string $tmp):void {
    try {
      $src_trw = TrwlConfig::pwd . "/plantilla";
      
      if (is_dir("{$src_trw}")) IEC::__system("rm -rf {$src_trw}");
      
      mkdir("{$src_trw}"    );
      mkdir("{$src_trw}/css");
      mkdir("{$src_trw}/ptw");
      
      IEC::__system("cp -rf {$tmp}css/configs/*  {$src_trw}/css");
      IEC::__system("cp -rf {$tmp}ptw/configs/*  {$src_trw}/ptw");
      //~ IEC::__system("cp -r {$tmp}util {$src_trw}"); //* trwlite non usa patróns.
    }
    catch (Exception $ex) {
      throw $ex;
    }
  }

  private static function __i_sql(FS_cbd $cbd, $tmp):bool {
    $_sql = explode( "\n", file_get_contents("{$tmp}bd.sql") );

//~ print_r($_sql);

    if (!is_array($_sql)) return false;

    
    foreach ($_sql as $sql) {
      if (trim($sql) == "") continue;
      
      if (!$cbd->executa($sql)) return false;
    }
    
    return true;
  }
}
