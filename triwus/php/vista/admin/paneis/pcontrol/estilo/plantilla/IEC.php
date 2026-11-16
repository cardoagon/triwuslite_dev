<?php


final class IEC {
  const tmp = "plantillas/";

  public static function __tmp(string $url_0, $p = null) {
    //* calcula carpeta temporal (tmp).
    
    $tmp = $url_0 .  "/" . self::tmp;
    
    self::__system("mkdir {$tmp}");

    
    if ($p == null) return $tmp;
    
    
    $tmp .= $p;

    if (is_dir($tmp)) self::__system("rm -r {$tmp}");
        
    self::__system("mkdir {$tmp}" );
      
       
    if (is_file("{$tmp}.zip")) self::__system("rm -r {$tmp}.zip");

    
    return $tmp;
  }

  public static function id_plantilla(FS_cbd $cbd, int $id_config):int {
    $r = $cbd->consulta("select id_plantilla from v_config where id_config = {$id_config} limit 0, 1");
    
    if ($_r = $r->next()) return $_r["id_plantilla"]; 

    return -1;
  }

  //~ public static function id_config(FS_cbd $cbd, int $id_plantilla):int {
    //~ $r = $cbd->consulta("select id_config from v_config where id_plantilla = {$id_plantilla} limit 0, 1");
    
    //~ if ($_r = $r->next()) return $_r["id_config"]; 

    //~ return -1;
  //~ }

  public static function __chdir($s, $verbose = false) {
    if ($verbose) echo "cd {$s}<br>\n";

    chdir($s);
  }

  public static function __system($s, $verbose = false) {
    if ($verbose) echo "{$s}<br>\n";

    passthru($s);
  }

  public static function __lconfig() {
//~ print_r($_p);

    $cbd = new FS_cbd();


    return $cbd->csv("select * from v_config");
  }

  public static function __lplantilla() {
//~ print_r($_p);

    $cbd = new FS_cbd();


    return $cbd->csv("select * from plantilla");
  }

  public static function __lsite() {
//~ print_r($_p);

    $cbd = new FS_cbd();


    return $cbd->csv("select id_site, nome, id_config from site");
  }
}
