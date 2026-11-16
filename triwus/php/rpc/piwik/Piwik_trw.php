<?php

final class Piwik_trw {
  const url = "https://stats.triwus.net/matomo/index.php";
  const ta  = "388964ed673d8e06cddf03fa59d02280";
  
  private function __construct() {}

  public static function js_tracking_code($id_piwik) {
    $url = self::url . "?module=API&method=SitesManager.getJavascriptTag&idSite={$id_piwik}";

    $curl = self::curlInit($url);

    $r = curl_exec($curl);

    curl_close($curl);

    $_r = json_decode($r);

    $js  = $_r->value;


    //* engadimos propiedades ao js de tracking

    $s1 = "_paq.push(['setDocumentTitle', document.domain + '/' + document.title]);";
    $s2 = "_paq.push(['trackPageView']);";

    $js = str_replace($s2, "{$s1}\n{$s2}", $js);

    //* cambiamos a url a piwik: stats.triwus.net -> https://stats.triwus.net

    $js = str_replace("//stats.triwus.net/", "https://stats.triwus.net/", $js);
    

    return "\n{$js}\n";
  }

  public static function site_add($site_nome, $site_url) {
    $url = self::url . "?module=API&method=SitesManager.addSite&siteName={$site_nome}&urls[0]={$site_url}";

    $curl = self::curlInit($url);

    $r = curl_exec($curl);

    curl_close($curl);

    $_r = json_decode($r);

    return $_r->value;
  }

  private static function curlInit($url) {
    $url .= "&format=JSON&token_auth=" . self::ta;
    
    try {
      $curl = curl_init();
      if(!curl_setopt($curl, CURLOPT_URL, $url)){ throw new Exception( 'Problem With CURLOPT_URL');  };
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($curl, CURLOPT_POST,           true);
      curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
      curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
      if(!curl_setopt($curl, CURLOPT_HTTPHEADER,
         array("Content-Type: text/jsom")
      )){ throw new Exception( 'Problem With CURLOPT_HTTPHEADER');  }

      return $curl;
    }
    catch (Exception $e) {
      return false;
    }
  }
}
