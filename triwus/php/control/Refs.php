<?php

final class Refs {
  const url_tmp             = "tmp";
  const url_sites           = "..";
  const url_stmp            = "tmp";
  const url_arquivo         = "arquivo";
  const url_arquivos        = "arquivos";
  const url_legais          = "legais";
  const url_galerias        = "galerias";
  const url_panoramix       = "panoramix";

  //~ const trwsactu            = "http://localhost/triwuslite-sactu/";
  const trwsactu            = "https://version.triwus.com/";

  public static function fs_home() {
    return getenv("fs_home");
  }

  public static function protocolo($https = -1) {
    if ($https == 1) return "https://";
    if ($https == 0) return "http://";

    if (strtoupper(getenv("server.https")) != "ON") return "http://";

    return "https://";
  }

  public static function url_home($https = -1) {
    return self::protocolo($https) . TrwlConfig::dominio;
  }

  public static function snome($https = -1) {
    $snome = getenv("server.nome");

    if (strtolower($snome) == "localhost") return self::url_home($https);
    if (self::valida_IPv4($snome))         return self::url_home($https);


    return self::protocolo($https) . $snome;
  }

  public static function url($url) {
    return self::fs_home() . $url;
    //~ return $url;
  }

  public static function url_plantilla_css(string $url):string {
//~ echo "$url<br>";

    if (getenv("pcontrol") != 1) return $url;
    
//~ echo "11111111111<br>";

    return Refs::url_sites . "/{$url}";
  }

  public static function url_arquivo(?string $url_0 = null) {
    $url = Refs::url_arquivo . "/";
    
    if ($url_0 != null) $url .= "{$url_0}/";

    if (getenv("pcontrol") != 1) return $url;
    
//~ echo "11111111111<br>";

    return Refs::url_sites . "/{$url}";
  }

  public static function url_arquivos(?string $url_0 = null) {
    $url = Refs::url_arquivos;
    
    if ($url_0 != null) $url .= "/{$url_0}";


    if (getenv("pcontrol") != 1) return $url;
    
//~ echo "11111111111<br>";

    return Refs::url_sites . "/{$url}";
  }
  
  public static function url_tmp2(?string $src_b = null):string {
    $src = TrwlConfig::pwd . "/triwus/" . Refs::url_tmp . "/";
    
    if ($src_b == null) return $src;
    
    return "{$src}/{$src_b}/";
  }

  public static function url_split($url) {
    if ($url == null) return null;


    list($x  , $src_0  ) = explode("/" . self::url_arquivos . "/", $url);
    list($src, $extmini) = explode(".", $src_0);

    //~ $ref_site = str_replace(self::url_sites . "/", "", str_replace("triwus/", "", $x));

    //~ return array(0=>$ref_site, 1=>$src, "ref_site"=>$ref_site, "url"=>$src);
    return array(0=>null, 1=>$src, "ref_site"=>null, "url"=>$src);
  }

  public static function valida_url_protexida($n):?string {
    $_ptx = ["triwus", "triwuslite", "sites", "galerias", "panoramix", 
             TrwlConfig::priv, self::url_tmp, self::url_arquivo, self::arquivos, self::legais
            ];
    
    
    foreach($_ptx as $ptx) {
      $ptx2 = strtolower(Refs::ptx);

      if (strpos($n, $ptx2) !== FALSE) return Erro::nome_site_protexido;
    }
    
    
    return null;
  }

  public static function sActu_info(?string $v = null):array {
    if ($v == null) $v = TrwlConfig::version;
        
    return json_decode(file_get_contents( self::trwsactu . "?op=info&v={$v}" ), true);
  }

  public static function sActu_dspg(string $src):?string {
    $f = @file_get_contents(self::trwsactu . $src);
    
    if ($f === false) return null;
    
    return $f;
  }


  private static function valida_IPv4($ip) {
    $long = ip2long($ip);

    return !($long == -1 || $long === FALSE);
  }
}
