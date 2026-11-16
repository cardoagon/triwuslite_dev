<?php

include "php-sitemap-generator/src/SitemapGenerator.php";
include "php-sitemap-generator/src/FileSystem.php";
include "php-sitemap-generator/src/Runtime.php";


final class XSiteMap {
  private function __construct() {}

  public static function enviar($url_sitemap) {
    $generator = new \Icamys\SitemapGenerator\SitemapGenerator("");

    $_r = $generator->submitSitemap(null, $url_sitemap);
    
    return self::enviar_parseResultado($_r);
  }
  
  public static function xerar($id_site, $outputDir) {
    $cbd = new FS_cbd();


    if (($s = self::site_obd($cbd, $id_site)) == null) return null;
    
//~ echo "<pre>" . print_r($s->a_resumo(), 1) . "</pre>";
//~ echo $outputDir;

    $url_0   = "https://" . TrwlConfig::dominio;

    $_idioma = self::_idioma($cbd, $s);
     
    //~ $outputDir = getcwd();

    $generator = new \Icamys\SitemapGenerator\SitemapGenerator($url_0, $outputDir);

    //~ $generator->enableCompression();

    $generator->setMaxUrlsPerSitemap(50000);

    $generator->setSitemapFileName("sitemap.xml");

    $generator->setSitemapIndexFileName("sitemap-index.xml"); //* Set the sitemap index file name

    self::xerar_urls_custom      ($cbd, $generator, $id_site, $_idioma, $url_0);
    self::xerar_urls_libre       ($cbd, $generator, $id_site, $_idioma, $url_0);
    self::xerar_urls_novas_0     ($cbd, $generator, $id_site, $_idioma, $url_0);
    self::xerar_urls_ventas_0    ($cbd, $generator, $id_site, $_idioma, $url_0);
    self::xerar_urls_categorias_0($cbd, $generator, $id_site, $_idioma, $url_0);
    self::xerar_urls_marcas_0    ($cbd, $generator, $id_site, $_idioma, $url_0);
    self::xerar_urls_buscador_0  ($cbd, $generator, $id_site, $_idioma, $url_0);
    self::xerar_urls_producto_0  ($cbd, $generator, $id_site, $_idioma, $url_0);


    
    $generator->flush();    //* Flush all stored urls from memory to the disk and close all necessary tags.
    $generator->finalize(); //* Move flushed files to their final location. Compress if the option is enabled.

    $generator->updateRobots(); //* Update robots.txt file in output directory or create a new one

    // Submit your sitemaps to Google, Yahoo, Bing and Ask.com
    //~ $generator->submitSitemap();


    return [$outputDir, $generator->totalUrlCount];
  }

  public static function enviar_parseResultado($_r) {
    if (!is_array($_r)) return null;


    //* modifica array de resultado.
    $_r2 = [];
    for ($i = 0; $i < count($_r); $i++) {
      $_r2[ $_r[$i]["site"] ]["fullsite" ] = $_r[$i]["fullsite" ];
      $_r2[ $_r[$i]["site"] ]["message"  ] = $_r[$i]["message"  ];
      $_r2[ $_r[$i]["site"] ]["http_code"] = $_r[$i]["http_code"];
    }

    //* resultado html.
    //~ $html = print_r($_r2, 1);

    $html = "";
    foreach($_r2 as $k=>$_v) {
      $s1 = "<li><a target='_blank' style='color: #00f;' href='{$_v["fullsite" ]}'>{$_v["fullsite" ]}</a></li>";
      $s2 = "<li>{$_v["message" ]}</li>";
      
      $html .= "
<b>{$k}</b>
<ul>{$s1}{$s2}</ul>";
    }

    $html = "<div style='white-space: pre-wrap;'>{$html}</div>";
    
    return [$html, $_r, $_r2];
  }
 

  
  private static function xerar_urls_custom(FS_cbd $cbd, \Icamys\SitemapGenerator\SitemapGenerator $smg, $id_site, $_idioma, $url_0): \Icamys\SitemapGenerator\SitemapGenerator {
    $sql = "
      select url_custom, actualizado, id_sincro
      from v_site_paxina
      where id_site = {$id_site} and id_idioma = '{$_idioma[0]}'
      and grupo = 'pub' and estado = 'publicada' and (not url_custom is null) and (publicar is null or publicar <= now())
      order by posicion";

    $r = $cbd->consulta($sql);

    while ($_r = $r->next()) {
      $alternates = self::xerar_alternates_custom($cbd, $url_0, $_r["id_sincro"]);
      //~ $alternates = [];
      
      $smg->addURL("/{$_r["url_custom"]}", new DateTime($_r["actualizado"]), 'daily', 0.5, $alternates);
    }

    return $smg;
  }
  
  private static function xerar_alternates_custom(FS_cbd $cbd, String $url_0, int $id_sincro): Array {
    $sql = "select url_custom, id_idioma 
              from v_site_paxina 
             where id_sincro = {$id_sincro} and (not url_custom is null)
          order by id_idioma";

    $r = $cbd->consulta($sql);

    $_a = [];
    while ($_r = $r->next()) {
      $_a[] = ['hreflang' => $_r["id_idioma"], 'href' => "{$url_0}/{$_r["url_custom"]}"];
    }

    return $_a;
  }
  
  
  private static function xerar_urls_libre(FS_cbd $cbd, \Icamys\SitemapGenerator\SitemapGenerator $smg, $id_site, $_idioma, $url_0): \Icamys\SitemapGenerator\SitemapGenerator {
    $sql = "
      select action, actualizado, id_sincro
      from v_site_paxina
      where id_site = {$id_site} and id_idioma = '{$_idioma[0]}'
      and grupo = 'pub' and estado = 'publicada' and tipo = 'libre' and (publicar is null or publicar <= now())
      order by posicion";

    $r = $cbd->consulta($sql);

    while ($_r = $r->next()) {
      $action     = "/" . substr($_r["action"], 0, -4) . "/";
      $alternates = self::xerar_alternates_libre($cbd, $url_0, $action, $_r["id_sincro"]);
      //~ $alternates = [];
      
      $smg->addURL($action, new DateTime($_r["actualizado"]), 'daily', 0.5, $alternates);
    }

    return $smg;
  }
  
  private static function xerar_alternates_libre(FS_cbd $cbd, String $url_0, String $action, int $id_sincro): Array {
    $sql = "select id_idioma from v_site_paxina where id_sincro = {$id_sincro} order by id_idioma";

    $r = $cbd->consulta($sql);

    $_a = [];
    while ($_r = $r->next()) {
      $_a[] = ['hreflang' => $_r["id_idioma"], 'href' => "{$url_0}{$action}{$_r["id_idioma"]}/"];
    }

    return $_a;
  }


  private static function xerar_urls_novas_0(FS_cbd $cbd, \Icamys\SitemapGenerator\SitemapGenerator $smg, $id_site, $_idioma, $url_0): \Icamys\SitemapGenerator\SitemapGenerator {
    $sql = "
       select action, actualizado, id_oms, id_sincro
       from v_site_paxina
       where id_site = {$id_site} and id_idioma = '{$_idioma[0]}'
       and grupo = 'pub' and estado = 'publicada' and tipo in ('novas', 'novas2') and id_prgf_indice > 0 and (publicar is null or publicar <= now())
       order by posicion";

    $r = $cbd->consulta($sql);

    while ($_r = $r->next()) {
      $action = "/" . substr($_r["action"], 0, -4) . "/";
      $alternates = self::xerar_alternates_novas_0($cbd, $url_0, $action, $_r["id_sincro"]);
      //~ $alternates = [];
      
      $smg->addURL($action, new DateTime($_r["actualizado"]), 'daily', 0.5, $alternates);

      self::xerar_urls_novas_1($cbd, $smg, $action, $_r["id_oms"], $_idioma, $url_0);
    }

    return $smg;
  }
  
  private static function xerar_alternates_novas_0(FS_cbd $cbd, String $url_0, String $action, int $id_sincro): Array {
    $sql = "select id_idioma from v_site_paxina where id_sincro = {$id_sincro} order by id_idioma";

    $r = $cbd->consulta($sql);

    $_a = [];
    while ($_r = $r->next()) {
      $_a[] = ['hreflang' => $_r["id_idioma"], 'href' => "{$url_0}{$action}{$_r["id_idioma"]}/"];
    }

    return $_a;
  }
  
  private static function xerar_urls_novas_1(FS_cbd $cbd, \Icamys\SitemapGenerator\SitemapGenerator $smg, String $action, String $id_oms, $_idioma, $url_0): \Icamys\SitemapGenerator\SitemapGenerator {
    $sql = "
      select a.nome_paxina, a.id_sincro, a.actualizado
      from v_site_paxina a inner join paxina_novas b on (a.id_paxina = b.id_paxina)
      where a.id_oms = {$id_oms} and a.id_idioma = '{$_idioma[0]}' and a.id_prgf_indice = 0 
        and (a.publicar is null or a.publicar <= now())
        and b.enlace_tipo = 2";

    $r = $cbd->consulta($sql);

    while ($_r = $r->next()) {
      $alternates = self::xerar_alternates_novas_1($cbd, $url_0, $action, $_r["id_sincro"]);
      //~ $alternates = [];
      
      $smg->addURL("{$action}{$_r["nome_paxina"]}/", new DateTime($_r["actualizado"]), 'daily', 0.5, $alternates);
    }

    return $smg;
  }
  
  private static function xerar_alternates_novas_1(FS_cbd $cbd, String $url_0, String $action, int $id_sincro): Array {
    $sql = "select nome_paxina, id_idioma from v_site_paxina where id_sincro = {$id_sincro} order by id_idioma";

    $r = $cbd->consulta($sql);

    $_a = [];
    while ($_r = $r->next()) {
      $_a[] = ['hreflang' => $_r["id_idioma"], 'href' => "{$url_0}{$action}{$_r["nome_paxina"]}/"];
    }

    return $_a;
  }

  private static function xerar_urls_marcas_0(FS_cbd $cbd, \Icamys\SitemapGenerator\SitemapGenerator $smg, $id_site, $_idioma, $url_0): \Icamys\SitemapGenerator\SitemapGenerator {
    $sql = "
      select action, actualizado, id_sincro
      from v_site_paxina
      where id_site = {$id_site} and id_idioma = '{$_idioma[0]}'
      and grupo = 'pub' and estado = 'publicada' and tipo = 'marcas' and (publicar is null or publicar <= now())
      order by posicion";

    $r = $cbd->consulta($sql);

    while ($_r = $r->next()) {
      $action     = "/" . substr($_r["action"], 0, -4) . "/";
      $alternates = self::xerar_alternates_libre($cbd, $url_0, $action, $_r["id_sincro"]);
      //~ $alternates = [];
      
      $smg->addURL($action, new DateTime($_r["actualizado"]), 'daily', 0.5, $alternates);

      self::xerar_urls_marcas_1($cbd, $smg, $id_site, $action, $_idioma, $url_0);
    }

    return $smg;
  }
  
  private static function xerar_urls_marcas_1(FS_cbd $cbd, \Icamys\SitemapGenerator\SitemapGenerator $smg, $id_site, String $action, $_idioma, $url_0): \Icamys\SitemapGenerator\SitemapGenerator {

    $_m = Articulo_marca_obd::_marca_2($id_site, $cbd, false);

    for ($i = 0; $i < count($_m); $i++) {
      $url_1      = "{$action}{$_m[$i]['nome']}/m={$_m[$i]['id_marca']}/";
      $alternates = self::xerar_urls_alternates_marcas_1($cbd, $url_0, $url_1, $_idioma[0]);
      //~ $alternates = [];

      $smg->addURL($url_1,new DateTime( date("Y-m-d H:i:s") ), 'daily', 0.5, $alternates);
    }

    return $smg;
  }

  private static function xerar_urls_alternates_marcas_1(FS_cbd $cbd, String $url_0, String $url_1, String $id_idioma_0): Array {
    $_a = [];

    $_a[] = ['hreflang' => $id_idioma_0, 'href' => "{$url_0}{$url_1}"];

    return $_a;
  }

  
  private static function xerar_urls_buscador_0(FS_cbd $cbd, \Icamys\SitemapGenerator\SitemapGenerator $smg, $id_site, $_idioma, $url_0): \Icamys\SitemapGenerator\SitemapGenerator {
    $sql = "
      select action, actualizado, id_sincro
      from v_site_paxina
      where id_site = {$id_site} and id_idioma = '{$_idioma[0]}'
      and grupo = 'pub' and estado = 'publicada' and tipo = 'buscador' and (publicar is null or publicar <= now())
      order by posicion";

    $r = $cbd->consulta($sql);

    while ($_r = $r->next()) {
      $action     = "/" . substr($_r["action"], 0, -4) . "/";
      $alternates = self::xerar_urls_alternates_buscador_0($cbd, $url_0, $action, $_idioma[0]);
      //~ $alternates = [];
      
      $smg->addURL($action, new DateTime($_r["actualizado"]), 'daily', 0.5, $alternates);
    }

    return $smg;
  }
  
  private static function xerar_urls_alternates_buscador_0(FS_cbd $cbd, String $url_0, String $action, String $id_idioma_0): Array {
    $_a = [];

    $_a[] = ['hreflang' => $id_idioma_0, 'href' => "{$url_0}{$action}"];

    return $_a;
  }


  private static function xerar_urls_producto_0(FS_cbd $cbd, \Icamys\SitemapGenerator\SitemapGenerator $smg, $id_site, $_idioma, $url_0): \Icamys\SitemapGenerator\SitemapGenerator {
    $sql = "
      select distinct a.id_articulo, a.nome, a.momento, a.id_paxina
      from (
          select a.id_articulo, a.nome, h.momento, h.id_paxina
          from articulo a inner join articulo_publicacions h on (a.id_site = h.id_site and a.id_articulo = h.id_articulo)
          where a.id_site = {$id_site} and (not a.id_grupo_cc is null)
          group by a.id_grupo_cc
          union
          select a.id_articulo, a.nome, h.momento, h.id_paxina
          from articulo a inner join articulo_publicacions h on (a.id_site = h.id_site and a.id_articulo = h.id_articulo)
          where a.id_site = {$id_site} and (a.id_grupo_cc is null)
      ) a inner join v_site_paxina b on (a.id_paxina = b.id_paxina and b.estado = 'publicada' and b.grupo = 'pub' and (b.publicar is null or b.publicar <= now()))";

    $r = $cbd->consulta($sql);

    while ($_r = $r->next()) {
      $action = "/producto/{$_r["nome"]}/{$_r["id_articulo"]}/";
      $alternates = self::xerar_urls_alternates_producto_0($cbd, $url_0, $action, $_idioma[0]);
      //~ $alternates = [];
      
      $smg->addURL($action, new DateTime($_r["momento"]), 'daily', 0.5, $alternates);
    }

    return $smg;
  }
  
  private static function xerar_urls_alternates_producto_0(FS_cbd $cbd, String $url_0, String $action, String $id_idioma_0): Array {
    $_a = [];

    $_a[] = ['hreflang' => $id_idioma_0, 'href' => "{$url_0}{$action}"];

    return $_a;
  }


  private static function xerar_urls_categorias_0(FS_cbd $cbd, \Icamys\SitemapGenerator\SitemapGenerator $smg, $id_site, $_idioma, $url_0): \Icamys\SitemapGenerator\SitemapGenerator {
    $sql = "
      select action, actualizado
      from v_site_paxina
      where id_site = {$id_site} and id_idioma = '{$_idioma[0]}'
      and grupo = 'pub' and estado = 'publicada' and tipo in ('categoria') and id_prgf_indice > 0 and (publicar is null or publicar <= now())
      order by posicion";

    $r = $cbd->consulta($sql);

    while ($_r = $r->next()) {
      $action = "/" . substr($_r["action"], 0, -4) . "/";
      $alternates = self::xerar_alternates_categorias_0($cbd, $url_0, $action, $_idioma[0]);
      //~ $alternates = [];
      
      $smg->addURL($action, new DateTime($_r["actualizado"]), 'daily', 0.5, $alternates);

      //~ self::xerar_urls_novas_1($cbd, $smg, $action, $_r["id_oms"], $_idioma, $url_0);
    }

    return $smg;
  }
  
  private static function xerar_alternates_categorias_0(FS_cbd $cbd, String $url_0, String $action, String $id_idioma_0): Array {
    $_a = [];

    $_a[] = ['hreflang' => $id_idioma_0, 'href' => "{$url_0}{$action}"];

    return $_a;
  }


  private static function xerar_urls_ventas_0(FS_cbd $cbd, \Icamys\SitemapGenerator\SitemapGenerator $smg, $id_site, $_idioma, $url_0): \Icamys\SitemapGenerator\SitemapGenerator {
    $sql = "
      select action, actualizado
      from v_site_paxina
      where id_site = {$id_site} and id_idioma = '{$_idioma[0]}'
      and grupo = 'pub' and estado = 'publicada' and tipo in ('ventas2') and id_prgf_indice > 0 and (publicar is null or publicar <= now())
      order by posicion";

    $r = $cbd->consulta($sql);

    while ($_r = $r->next()) {
      $action = "/" . substr($_r["action"], 0, -4) . "/";
      $alternates = self::xerar_alternates_ventas_0($cbd, $url_0, $action, $_idioma[0]);
      //~ $alternates = [];
      
      $smg->addURL($action, new DateTime($_r["actualizado"]), 'daily', 0.5, $alternates);

      //~ self::xerar_urls_novas_1($cbd, $smg, $action, $_r["id_oms"], $_idioma, $url_0);
    }

    return $smg;
  }
  
  private static function xerar_alternates_ventas_0(FS_cbd $cbd, String $url_0, String $action, String $id_idioma_0): Array {
    $_a = [];

    $_a[] = ['hreflang' => $id_idioma_0, 'href' => "{$url_0}{$action}"];

    return $_a;
  }

 
  private static function _idioma(FS_cbd $cbd, Site_obd $s) {
    $k = $s->atr("id_site")->valor;
    
    $sql = "select id_idioma, pos from v_ml_site where id_site = {$k} and activo = 1";

    $r = $cbd->consulta($sql);

    $_a = []; while ($_r = $r->next()) $_a[] = $_r["id_idioma"]; 


    return $_a;
  }

  private static function site_obd(FS_cbd $cbd, $id_site) {
    $s = Site_obd::inicia($cbd, $id_site);

    if ($s->atr("nome")->valor == null) return null;

    return $s;
  }

}

