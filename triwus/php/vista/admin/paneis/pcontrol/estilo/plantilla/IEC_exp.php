<?php

final class IEC_exp {
  public static function executa(FS_cbd $cbd, string $url_trw, string $url_tmp_0, int $p):?string {
    //* Crea o arquivo de exportación dunha plantilla Triwus
    
    
    //* valida parám.
    if ($p == null) return null;

    if (!is_dir($url_trw)) {
      throw new Exception("ERROR. IEC_exp.executa(). '$url_trw' NON é un directorio.");
      
      return null;
    }


    //* prepara
    list($_p, $url_tmp) = self::__e_prepara($cbd, $url_tmp_0, $p);

//~ echo "::$url_trw::$url_tmp::<pre>" . print_r($_p, 1) . "</pre>"; 

    if ($_p == null) {
      throw new Exception("ERROR. IEC_exp.executa(). '_p' == null.");
      
      return null;
    }

    IEC::__system("mkdir {$url_tmp}/plantilla");
    
    //* exportamos json
    self::__e_json($cbd, $p, $_p, $url_tmp);
    
    //* exportamos sql
    self::__e_sql($cbd, $p, $_p, $url_tmp);
    
    //* exportamos css
    self::__e_css($cbd, $p, $url_trw, $url_tmp);
    
    //* exportamos css_patron
    //~ self::__e_css_patron($cbd, $p, $url_trw, $url_tmp);  //* trwlite non usa patróns.
    
 
    //* exportamos ptw
    self::__e_ptw($cbd, $p, $url_trw, $url_tmp);
    

    //* zipeamos carpeta deexportación e eliminamos a carpeta.
//~ echo "*****" . IEC::__tmp() . "<br>"; 
    $dir_0 = getcwd() . "/";
//~ echo "000::{$dir_0}::{$url_tmp_0}<br>";

    //~ IEC::__chdir($url_tmp);
    IEC::__chdir( IEC::__tmp($url_tmp_0) );
//~ echo "111::" . getcwd() . "<br>";


    IEC::__system("zip -qq -r {$p}.zip {$p}");

    IEC::__chdir($dir_0);

    return "{$url_tmp}.zip";
  }

  public static function __l() {
    return IEC::__lplantilla();
  }

/*  
  public static function __axuda() {
    return "


Usar: exportar.php opcion parametros

------------------
-- Opcions
------------------

 ?  : devolve este menú
 
-e  : crea o arquivo de exportación dunha plantilla Triwus

      Usar: exportar.php -e id_plantilla
                          
      Pex.: exportar.php -e 1
      
-l  : devolve en formato csv, a lista de plantillas instaladas no servidor

      Usar: exportar.php -l 

      Pex.: exportar.php -l
      Pex.: exportar.php -l > plantillas.csv

";
  }
*/

  private static function __e_prepara(FS_cbd $cbd, string $url_tmp_0, $p) {
    //* calcula plantilla.
    $_p = null;
    
    $r = $cbd->consulta("select * from config where id_plantilla = {$p}");
    
    while ($_r = $r->next()) $_p [ $_r['id_config'] ] = $_r;
    
    //* calcula carpeta temporal (tmp).
    $url_tmp = IEC::__tmp($url_tmp_0, $p);
        
    
    //* 
    return array($_p, $url_tmp);
  }

  private static function __e_json(FS_cbd $cbd, $p, $_p, $url_tmp) {
    $json = array("ver"=>0,
                  "_p" =>$_p
                 );

    file_put_contents("{$url_tmp}/info.json", json_encode($json));
  }

  private static function __e_sql(FS_cbd $cbd, $p, $_p, $url_tmp) {
    $sql = "";
    
    //* xera sql plantilla.  
    $sql .= self::__e_sql_aux($cbd, "plantilla", "id_plantilla = '{$p}'");
    
    //* xera sql de configuracións da plantilla.
    foreach($_p as $id_config=>$_pi) {
      $sql .= self::__e_sql_aux($cbd, "config"    , "id_config = '{$id_config}' and tipo = 'escritorio'");
      $sql .= self::__e_sql_aux($cbd, "config"    , "id_config = '{$id_config}' and tipo = 'mobil'");
      $sql .= self::__e_sql_aux($cbd, "config_css", "id_css = '{$_pi['id_css']}'");
      $sql .= self::__e_sql_aux($cbd, "config_ptw", "id_ptw = '{$_pi['id_ptw']}'");
    }

    file_put_contents("{$url_tmp}/bd.sql", $sql);
  }

  private static function __e_sql_aux(FS_cbd $cbd, $from, $where) {
    //* xera sql delete.
    $sql_delete = "delete from {$from} where {$where};";

    //* xera sql insert (aux).

    $r = $cbd->consulta("select * from {$from} where {$where}");
    
    $c = null;
    $v = null;
    while ($_r = $r->next()) {
      foreach($_r as $k => $vi) {
        if (is_numeric($k)) continue;
        
        if ($c != null) $c .= ",";
        if ($v != null) $v .= ",";
        
        $c .= $k;
        
        if ($vi == "") {
          $v .= "null";
        }
        else {
          $vi = addslashes($vi);
          
          $v .= "'{$vi}'";
        }
      } 
    }

    if ($c == null) return "";

    //* xera sql insert.
    $sql_insert = "insert into {$from} ({$c}) values ({$v});";

    //*
    return "\n{$sql_delete}\n{$sql_insert}\n";
  }

  private static function __e_css(FS_cbd $cbd, $p, $url_trw, $url_tmp) {
    //* Crear carpetas temporais.
    
    IEC::__system("mkdir {$url_tmp}/plantilla/css");


    //* Exporta ficheiros da plantilla css.
    $r = $cbd->consulta("select distinct id_css from config where id_plantilla = {$p}");
    
    while ($_r = $r->next()) {
      IEC::__system("cp -r {$url_trw}/css/{$_r['id_css']} {$url_tmp}/plantilla/css");
    }
  }

  private static function __e_css_patron(FS_cbd $cbd, $p, $url_trw, $url_tmp) {
    //* Crear carpetas temporais.
    IEC::__system("mkdir {$url_tmp}/util");
    IEC::__system("mkdir {$url_tmp}/util/css.2");
    IEC::__system("mkdir {$url_tmp}/util/css.2/patrons");


    //* Exporta ficheiros da plantilla css_patrón.
    IEC::__system("cp -r {$url_trw}/util/css.2/patrons/{$p} {$url_tmp}/util/css.2/patrons");
  }

  private static function __e_ptw(FS_cbd $cbd, $p, $url_trw, $url_tmp) {
    //* Crear carpetas temporais.
    
    IEC::__system("mkdir {$url_tmp}/plantilla/ptw");
    IEC::__system("mkdir {$url_tmp}/plantilla/ptw/pax");
    IEC::__system("mkdir {$url_tmp}/plantilla/ptw/elmt");
    IEC::__system("mkdir {$url_tmp}/plantilla/ptw/prgf");

    //* Exporta ficheiros da plantilla ptw.
    $r = $cbd->consulta("select distinct id_ptw from config where id_plantilla = {$p}");
    
    while ($_r = $r->next()) {
      IEC::__system("cp    {$url_trw}/ptw/pax/{$_r['id_ptw']}.html {$url_tmp}/plantilla/ptw/pax" );
      IEC::__system("cp -r {$url_trw}/ptw/elmt/{$_r['id_ptw']}     {$url_tmp}/plantilla/ptw/elmt");
      IEC::__system("cp -r {$url_trw}/ptw/prgf/{$_r['id_ptw']}     {$url_tmp}/plantilla/ptw/prgf");
    }
  }
}
