<?php

final class Catalogo_n extends Catalogo {  
  public function __construct(ICatalogo_fonte $icp, bool $editor, Config_obd $c, string $action, bool $hai_busca = true) {
    parent::__construct($icp, $editor, $c, $action, $hai_busca);
  }
  
  public function _busca(ICatalogo_fonte $icp, bool $vpa, string $action, bool $hai_busca):Cat_busca { //* IMPLEMENTA Catalogo._busca()
    return new CatN_busca($icp, $vpa, $action, $hai_busca);
  }
  
  public function _lista(ICatalogo_fonte $icp, bool $editor, Config_obd $c_obd, Site_config_obd $sc_obd, string $action):Cat_lista { //* IMPLEMENTA Catalogo._lista()
    return new CatN_lista($icp, $editor, $c_obd, $sc_obd, $action);
  }
}


//*********************************************


final class CatN_lista extends Cat_lista {
  public function __construct(ICatalogo_fonte $icp, bool $editor, Config_obd $c_obd, Site_config_obd $sc_obd, string $action) {
    parent::__construct($icp, $editor, $c_obd, $sc_obd, $action);

    $this->limit_f    = 24;

    $this->pon_obxeto(new Hidden("hlistar"));
    
    $this->obxeto("hlistar")->pon_atr("hlistar", 1);
  }

  public function operacion(EstadoHTTP $e) {
    if ($this->obxeto("hlistar")->control_evento()) return $this->operacion_hlistar($e);
    

    return parent::operacion($e);
  }


  protected function _ehtml():Cat_ehtml { //* IMPLEMENTA Cat_lista._ehtml()
    return new CatNlista_ehtml();
  }

  private function operacion_hlistar(EstadoHTTP $e):EstadoHTTP {
    $s = $this->id_site;                      //* site
    $p = $this->obxeto("hlistar")->valor();   //* páxina
    
    if ($p == null) {
      $e->ajax()->pon_ok(true, json_encode(""));      

      return $e;
    }
    
 

    $html = CatN_elmt::html($p, $this->c_obd, $this->editor);
    
    $e->ajax()->pon_ok(true, json_encode($html));

    return $e;
  }
}

//-----------------------------------------

final class CatNlista_ehtml extends Cat_ehtml {
  private string $ptw_0;
  
  public function __construct() {
    parent::__construct();
  }
  
  protected function inicia_saida() {
    $this->ptw_0 = "";
    
    return $this->xestor->obxeto("hlistar")->html();
  }

  protected function finaliza_saida() {
    $this->ptw_0 = "";
    
    return "<script>trwCat_listar()</script>";
  }

  protected function iten_html($df, $f):string { //* IMPLEMENTA Cat_ehtml.iten_html()  
    return "<div k='{$f["id_paxina"]}'>{$this->ptw_0}</div>";
  }
}


//*********************************************


final class CatN_busca extends Cat_busca {
  public function __construct(ICatalogo_fonte $icp, bool $vpa, string $action, bool $hai_busca) {
    parent::__construct($icp, $vpa, $action, $hai_busca);
  }

  protected function sql_where_txt():string { //* IMPLEMENTA Cat_busca.sql_where_txt()
    $txt = $this->obxeto("txt")->valor();

    $wh  = Valida::buscador_txt2sql( $txt, array("a.nome", "a.titulo", "a.descricion") );

    if ($wh == "") return "(1)";

    return "({$wh})";
  }

  protected function sql_where_etq(int $i):string { //* IMPLEMENTA Cat_busca.sql_where_etq()
    $_ch = $this->obxetos("chEtq_{$i}");

    if (count($_ch) == 0) return "(1)";

    $wh = null;
    foreach($_ch as $ch) {
      if (!$ch->valor()) continue;

      list($x, $etq) = explode(Escritor_html::csubnome, $ch->nome());

      if ($wh != null) $wh .= " or ";

      $wh .= "(a.etq_{$i} like '%{$etq}%')";
    }

    if ($wh == null) return "(1)";


    return "({$wh})";
  }

  protected function inicia_etq(int $i, FS_cbd $cbd, ICatalogo_fonte $icp, bool $vpa):void {//* IMPLEMENTA Cat_busca.inicia_etq()
    if ( $this->_titetq[$i] == null) return; 
    
    if (($sql = $icp->icatF__etq_sql($i, $vpa)) == null) return;
    
    $r = $cbd->consulta( $sql );

    //* leemos etiquetas de novas e gardamos nun array.
    $_etq = [];
    while ($_a = $r->next()) {
//~ echo "<pre>" . print_r($_a, 1) . "</pre>";
      $_etq_aux = explode(",", $_a["etq"]);
      
      if ( !is_array($_etq_aux) ) {
        $etq_aux = strtoupper(trim($_a["etq"]));
        
        $_etq[ $etq_aux ] = $etq_aux;
         
        continue;
      }
      
      foreach($_etq_aux as $etq_aux) {
        $etq_aux = strtoupper(trim($etq_aux));
        
        $_etq[ $etq_aux ] = $etq_aux;
      }
    }
    
    if ($_etq == []) return;

    //* ordena etiquetas.
    ksort($_etq, SORT_STRING);

    //* inicia checkers.
    foreach($_etq as $k=>$v) $this->pon_obxeto(self::__checker($i, $v, ""), $v);
  }
}


//*********************************************


final class CatN_elmt { 
  public static function html(int $id_paxina, Config_obd $c, bool $editor, FS_cbd $cbd = null) {
    if ($cbd == null) $cbd = new FS_cbd();
    
    $pax = Paxina_obd::inicia($cbd, $id_paxina, "novas2");

    $html = LectorPlantilla::plantilla( $c->ptw_elmt("elmt_novas_0") );

    $html = str_replace("[btnr]"    , self::html_btnr ($pax, $editor), $html);
    $html = str_replace("[imaxe]"   , self::html_imaxe($pax, $editor), $html);
    $html = str_replace("[ldetalle]", self::html_nome ($pax, $editor), $html);
    $html = str_replace("[data]"    , self::html_data ($pax         ), $html);
    $html = str_replace("[cetq]"    , self::html_etq  ($pax, $editor), $html);
    $html = str_replace("[enotas]"  , self::html_notas($pax         ), $html);

    $html_btnr_eventos = self::html_btnr_eventos($pax, $editor);
    
    
    $html = "<div " . self::css_tipoEnlace($pax) . " {$html_btnr_eventos}>{$html}</div>";

    if (!$editor) return $html;

    return "
<div class='celda_elmt_marcado celda_elmt' novas2='{$pax->atr("id_paxina")->valor}'>
  {$html}
</div>";


    return $html;
  }

  private static function html_imaxe(Paxina_novas2_obd $p, $editor) {
    $u = (($l = $p->iclogo_obd( new FS_cbd() )) == null)?"":$l->url();

    $t = $p->atr("titulo")->valor;
    
    if ( $p->atr("enlace_tipo")->valor == 3 ) return "<img title='{$t}' alt='{$t}' src='{$u}' />";
    
    
    if ( $p->atr("enlace_tipo")->valor == 1 ) {
      $url = $p->atr("enlace_src"   )->valor;
      $tar = $p->atr("enlace_target")->valor;
      
      return "<a href='{$url}' title='{$t}' alt='{$t}' target='{$tar}'><img title='{$t}' alt='{$t}' src='{$u}' /></a>";
    }
    

    //* $p->atr("enlace_tipo")->valor == 2.
    if ($editor) {
      $fclick = self::fclick($p, $editor);

      return "<img style='cursor: pointer;' onclick=\"{$fclick}\" title='{$t}' alt='{$t}' src='{$u}' />";
    }
    

    $url = self::url($p, $editor);

    return "<a href='{$url}' title='{$t}' alt='{$t}'><img title='{$t}' alt='{$t}' src='{$u}' /></a>";
  }

  private static function html_nome(Paxina_novas2_obd $p, $editor) {
    $css    = "class='novas_titulo'";
    $t      = $p->atr("nome")->valor;
    
    $pub = (self::publicada($p)) ? "" : "<div title='No publicada o permiso especial' style='font-size: 1.3em;padding-right: 0.3em;'>⚠️</div>";
  
    
    if ( $p->atr("enlace_tipo")->valor == 3 ) return self::html_nome_2($pub, "<h2 {$css}>{$t}</h2>");
    
    
    if ( $p->atr("enlace_tipo")->valor == 1 ) {
      $url = $p->atr("enlace_src"   )->valor;
      $tar = $p->atr("enlace_target")->valor;
      
      $fclick = "window.open(\"{$url}\", \"{$tar}\")";
      
      return self::html_nome_2($pub, "<h2 {$css} style='cursor: pointer;' onclick='{$fclick}'>{$t}</h2>");
    }
    

    //* $p->atr("enlace_tipo")->valor == 2.
    $fclick = self::fclick($p, $editor);

    return self::html_nome_2($pub, "<h2 {$css} style='cursor: pointer;' onclick='{$fclick}'>{$t}</h2>");
  }

  private static function html_nome_2(string $pub, string $txt) {
    return "<div style='display: flex;align-items: center;'>{$pub}{$txt}</div>";
  }

  private static function html_data(Paxina_novas2_obd $p) {
    if (($d = $p->atr("data")->valor) == null) return "";

    if (($l = $p->atr("id_idioma")->valor) == null) return $d;

    $L = Idioma::factory($l);

    return date($L->formato_fecha, strtotime($d));
  }

  private static function html_etq(Paxina_novas2_obd $p, $editor) {
    $html = "";

    for ($i = 0; $i < 3; $i++) {
      $i2 = $i + 1;

      $_etq = null;
      if ($p->atr("etq_{$i2}")->valor != null)
        $_etq = preg_split("/[,;:]+/", trim($p->atr("etq_{$i2}")->valor) );

      if (!is_array($_etq)) continue;

      $html_aux = "";
      foreach ($_etq as $j => $t) {
        if ($t == "") continue;

        $html_aux .= self::etq_link($i, $j, $t, $editor);
      }

      $html .= "<div>{$html_aux}</div>";
    }


    return $html;
  }

  private static function html_notas(Paxina_novas2_obd $p) {
    return strval($p->atr("entradilla")->valor);
  }
/*
  private static function html_crs(Prgf_inovas2 $prgf, Paxina_novas2_obd $pax, $editor) {
    if ($editor) {
      $prgf->crs->obxeto("rs_facebook_url")->sup_eventos();
      $prgf->crs->obxeto("rs_twitter_url" )->sup_eventos();
      $prgf->crs->obxeto("rs_linkedin_url")->sup_eventos();
      $prgf->crs->obxeto("rs_whatsapp_url")->sup_eventos();

      return $prgf->crs->html();
    }

    $url = self::url($pax, $editor);

    $prgf->crs->obxeto("rs_facebook_url")->pon_eventos("onclick", "novas2_trw2RS('facebook', '{$url}')");
    $prgf->crs->obxeto("rs_twitter_url" )->pon_eventos("onclick", "novas2_trw2RS('twitter' , '{$url}')");
    $prgf->crs->obxeto("rs_linkedin_url")->pon_eventos("onclick", "novas2_trw2RS('linkedin', '{$url}')");
    $prgf->crs->obxeto("rs_whatsapp_url")->pon_eventos("onclick", "novas2_trw2RS('whatsapp', '{$url}')");


    return $prgf->crs->html();
  }
*/
  private static function html_btnr(Paxina_novas2_obd $p, $editor) {
    if (!$editor) return "";


    $btnr = new Btnr_elmt_novas3($p->atr("id_paxina")->valor);

    return $btnr->html();
  }

  private static function html_btnr_eventos(Paxina_novas2_obd $p, $editor) {
    if (!$editor) return "";

    $idp = $p->atr("id_paxina")->valor;

    return "onmouseover='novas2_elmt_btnr_over({$idp}, true)' onmouseout='novas2_elmt_btnr_over({$idp}, false)'";
  }

  private static function css_tipoEnlace(Paxina_novas2_obd $p):string {
    switch ($p->atr("enlace_tipo")->valor) {
      case 1: return "class='novas2_enlace_interno'";
      case 2: return "";
      case 3: return "";
    }
    
    return "";
  }
  
  private static function url(Paxina_novas2_obd $p, $editor) {
    if ($editor) return null;

    return $p->action_2(true, true);
  }

  private static function fclick(Paxina_novas2_obd $p, $editor) {
    if ($editor) return "novas2_editar_2_click(" . $p->atr("id_paxina")->valor . ")";

    $url = self::url($p, $editor);

    return "document.location.href = \"{$url}\"";
  }

  private static function etq_link($i, $j, $txt, $editor) {
    $id = "link" . Escritor_html::csubnome . $i . Escritor_html::csubnome . $j;

    //~ $txt = trim($txt);

    $l = new Span($id, $txt);

    if ($i == 0)
      $l->clase_css("default" , "novas_etq_link");
    else
      $l->clase_css("default" , "novas_etq_link_{$i}");


    if (!$editor) $l->pon_eventos("onclick", "prgf_inovas_url_fetq({$i}, '{$txt}')");


    return $l->html();
  }

  private static function publicada(Paxina_novas2_obd $p):bool {
    if ($p->atr("grupo_aux")->valor != "pub"      ) return false;
    
    if ($p->atr("estado"   )->valor != "publicada") return false;

    if ($p->atr("publicar")->valor == null) return true;
    
    if ($p->atr("publicar")->fvalor("YmdHis") >= date("YmdHis")) return false;
    

    return true;
  }
}
