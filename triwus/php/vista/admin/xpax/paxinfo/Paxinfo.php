<?php

abstract class Paxinfo extends    Componente
                       implements IXestorCEditor_imx {

  const ptw_0 = "ptw/xpax/paxinfo/paxinfo_0.html";
  const ptw_1 = "ptw/xpax/paxinfo/paxinfo_1.html";


  public $url   = null;
  public $ptw_0 = null;

  private ?Paxina_obd $pax_0 = null;

  private $paxnome_0 = null;
  private $action_0  = null;
  private $urlcus_0  = null;

  private $config_urlNula = true;
  private $hai_erros      = false;
  
  protected function __construct(Paxina_obd $p = null, $ptw = null) {
    if ($ptw == null) $ptw = self::ptw_1;

    parent::__construct("cpaxinfo", Refs::url($ptw));
    
    $this->ptw_0 = Refs::url(self::ptw_0);

    //~ $this->pax_0 = $p;


    $this->pon_obxeto(new Param("titulo_ficha", $this->titulo_ficha()));

    $this->pon_obxeto(new Hidden("tipo"));
    $this->pon_obxeto(new Hidden("id_oms"));
    $this->pon_obxeto(new Hidden("id_pax"));
    $this->pon_obxeto(new Hidden("id3"));
    $this->pon_obxeto(new Hidden("id_idioma"));
    $this->pon_obxeto(new Hidden("sidioma")); //* mentres non carga a páxina

    $this->pon_obxeto(new Checkbox("estado" , true , "Página publicada"  ));
    $this->pon_obxeto(new Checkbox("visible", true , "Mostrar en el menú"));
    $this->pon_obxeto(new Checkbox("puente" , false, "Sin enlace"        ));

    $this->pon_obxeto(new DataInput("tdatapub"));
    $this->pon_obxeto(Panel_fs::__hhmm("thorapub"));


    $this->pon_obxeto(Panel_fs::__text("nome", 1, 99));
    $this->pon_obxeto(Panel_fs::__text("titulo", 1, 255, "... el meta título de la página"));
    $this->pon_obxeto(Panel_fs::__text("cat_h1", 1, 255, ""));
    $this->pon_obxeto(Panel_fs::__text("robots", 1, 55, "... pej. index, follow"));
    $this->pon_obxeto(Panel_fs::__text("grupo", 1, 255, "... pej. comprador, vendedor"));
    $this->pon_obxeto(new Textarea("descricion", 2, 1));
    $this->pon_obxeto(new Div("abstract"));
    
    
    $this->pon_obxeto(new Param("url"      ));
    $this->pon_obxeto(new Param("url_limpa"));
    $this->pon_obxeto(new Param("url_site" ));
    $this->pon_obxeto(Panel_fs::__text("url_custom"));

  
    $this->pon_obxeto(self::paxinfo_enlace("bCopiar", "Copiar", "Copiar URL"));
    $this->pon_obxeto(self::paxinfo_enlace("bAbrir" , "Abrir" , "Abrir URL" ));


    $this->pon_obxeto(new Textarea("script_cabecera", 2, 1));


    $this->pon_obxeto(new Paxinfo_props());


    $this->pon_obxeto(Panel_fs::__baceptar());
    $this->pon_obxeto(Panel_fs::__bcancelar());
    $this->pon_obxeto(Panel_fs::__beditar("bEditar", "Editar", "Guardar y editar"));

    $this->pon_obxeto(new CEditor_imx());
    $this->pon_obxeto(self::paxinfo_logo());
    $this->pon_obxeto((Panel_fs::__bsup("bSupLogo", "Borrar logo", "Elimina el logo asignado a la sección y asigna el logo predeterminado.")));


    $this->obxeto("bSupLogo" )->envia_AJAX("onclick");

    $this->obxeto("bCancelar")->pon_eventos("onclick", "cxaf_gardando(this)");
    $this->obxeto("bAceptar" )->pon_eventos("onclick", "cxaf_gardando(this)");


    $this->__reset($p);
  }

  abstract public function titulo_ficha();
  abstract public function paxina_obd(FS_cbd $cbd = null);

  public function post_paxina(Paxina_obd $p) {
    $this->post_paxina_base($p);
  }

  public function readonly($b = false) {
    parent::readonly($b);

    $this->obxeto("bAceptar" )->visible = !$b;
    $this->obxeto("bCancelar")->visible = !$b;
    $this->obxeto("bEditar"  )->visible = !$b;
  }

  public function readonly_nome(Paxina_obd $p, $b = false) {
    if ($b) return $b;

    if ($p->atr("tipo")->valor == "libre"   ) return false;

    if ($p->atr("tipo")->valor == "ventas2cat") return true;
    
    if ( Paxina_reservada_obd::test($p->atr("tipo")->valor) ) return true;
    
    if ($p->atr("id_prgf_indice")->valor > 0) return false;
    
   
    return true;
  }

  public function readonly_grupo(Paxina_obd $p, $b = false) {
    if ($b) return $b;
    
    if ($p->atr("id_prgf_indice")->valor > 0) return false;

    if ($p->atr("tipo")->valor == "novas2"  ) return true;
    
   
    return false;
  }


  public function operacion(EstadoHTTP $e) {
    $this->hai_erros = false;

    if ($this->obxeto("bEditar")->control_evento())   return $this->operacion_bEditar($e);

    if ($this->obxeto("bAceptar")->control_evento())  return $this->operacion_bAceptar($e);

    if ($this->obxeto("bCancelar")->control_evento()) return $this->operacion_bCancelar($e);

    if ($this->obxeto("bSupLogo")->control_evento()) return $this->operacion_bSupLogo($e);

    //~ if ($this->obxeto("bAbrir")->control_evento())  return $this->operacion_bAbrir($e);

    //~ if ($this->obxeto("labstract")->control_evento()) return $this->operacion_labstract($e);

    if ($this->obxeto("sidioma")->control_evento()) {
      return $e->obxeto("xpax")->operacion_idoms($e, $this->pax_0->atr("tipo")->valor, $this->obxeto("sidioma")->valor(), null);
    }

    if ($this->obxeto("logo")->control_evento()) return $this->obxeto("ceditor_imx")->operacion_logo($e);
    if (($e_aux = $this->obxeto("ceditor_imx")->operacion($e)) != null) return $e_aux;

    //~ if (($e_aux = $this->obxeto("cprops")->operacion($e)) != null) return $e_aux;


    return null;
  }

  public function validar(Paxina_obd $p) {
    if ( Paxina_reservada_obd::test($p->atr("tipo")->valor) ) return null;

    //* Valida nome.
    $n = Erro::validar_nome_paxina( $this->obxeto("nome")->valor() );

//~ echo "$n::" . $this->obxeto("nome")->valor() . "<br />";

    if ($n == -1) return Erro::nome_pax_0;
    if ($n == -2) return Erro::nome_pax_1;

    
    //* Valida action.
    if ($this->paxnome_0 != $n) {
      $s        = $p->atr("id_site")->valor;
      $url_s    = Efs::url_site($s);
      $action_1 = Opcion_ms_obd::calcula_action($n, $s, false);

      if ($this->action_0 != $action_1) {
  //~ echo "{$url_s}{$action_1}<br/>";
        if (is_file("{$url_s}{$action_1}")) {
          $this->obxeto("nome")->post( $this->paxnome_0 );

          return Erro::nome_pax_2;
        }
      }
    }

    
    //* Valida url_custon.
    if (($urlcus = $p->atr("url_custom")->valor) != "")
      if ($this->urlcus_0 != $urlcus) {
        if (Opcion_ms_obd::hai_action($p->atr("id_site")->valor, $urlcus)) 
          return "Error, el nombre de la URL 'a medida' está duplicado, teclea otro.";
    }

    //* nome, action e url_custom, validadxs.
    $this->obxeto("nome")->post($n);


    //* valida grupo.
    $grupo = Erro::__comas( strtolower($this->obxeto("grupo")->valor()) );

    if ($grupo == null) $grupo = "pub";

    $this->obxeto("grupo")->post($grupo);


    return null;
  }

  public static function inicia(Paxina_obd $p = null, $readonly = false) {
    if ($p == null) return new Paxinfo_nulo();

    $paxinfo = null;
    switch($p->atr("tipo")->valor) {
      case "ref":
        $paxinfo = new Paxinfo_ref($p);

        break;

      case "cxcli":
      case "marcas":
      case "libre":
        $paxinfo = new Paxinfo_libre($p);

        break;
        
      case "ventas2":
        $paxinfo = new Paxinfo_ventas2($p);
        
        break;
        
      case "ventas2cat":
        $paxinfo = new Paxinfo_ventas2cat($p, $p->categoria());

        break;

      case "legal":
        $paxinfo = new Paxinfo_legal($p);

        break;

      case "novas":
        if ($p->atr("id_prgf_indice")->valor > 0)
          $paxinfo = new Paxinfo_novas($p);
        else
          $paxinfo = new Paxinfo_nova($p);

      case "novas2":
        if ($p->atr("id_prgf_indice")->valor > 0)
          $paxinfo = new Paxinfo_libre($p);
        elseif ($p->atr("enlace_tipo")->valor == 2)
          $paxinfo = new Paxinfo_nova_2_interna($p);
        else
          $paxinfo = new Paxinfo_nova_2($p);

        break;
      
      default:
        if ( Paxina_reservada_obd::test($p->atr("tipo")->valor) ) $paxinfo = new Paxinfo_reservado($p);
    }
    

    if ($paxinfo == null) die("Paxinfo.inicia(), p.atr(\"tipo\").valor NON valido");

    
    $paxinfo->paxnome_0 = $p->atr("nome"      )->valor;
    $paxinfo->action_0  = $p->atr("action"    )->valor;
    $paxinfo->urlcus_0  = $p->atr("url_custom")->valor;

    $paxinfo->readonly($readonly);

    $paxinfo->obxeto("nome"      )->readonly = $paxinfo->readonly_nome ($p, $readonly);
    $paxinfo->obxeto("grupo"     )->readonly = $paxinfo->readonly_grupo($p, $readonly);
    $paxinfo->obxeto("url_custom")->readonly = $readonly || $p->atr("id_site")->valor == 58;  //* utilidade non permitida para trw_envolve.


    return $paxinfo;
  }

  public function __reset(Paxina_obd $p = null) {
    if ($p == null) {
      $p = $this->pax_0;
    }
    
    if ($p == null) {
      if (($id_pax = $this->obxeto("id_pax")->valor()) == null) return;

      $p = Paxina_obd::inicia(new FS_cbd(), $id_pax);
    }

    $this->post_paxina($p);
  }

  public function preparar_saida(Ajax $a = null) {
    $html = $this->html00();

    if ($this->ptw_0 != null) {
      $html_0 = LectorPlantilla::plantilla($this->ptw_0);

      $html = str_replace("[cpaxinfo]", $html, $html_0);

      $html = str_replace("[titulo_ficha]", $this->obxeto("titulo_ficha")->html(), $html);
      $html = str_replace("[bCancelar]"   , $this->obxeto("bCancelar"   )->html(), $html);
      $html = str_replace("[bAceptar]"    , $this->obxeto("bAceptar"    )->html(), $html);
      $html = str_replace("[bEditar]"     , $this->obxeto("bEditar"     )->html(), $html);
    }

    $this->obxeto("illa")->post($html);

    if ($a == null) return;


    $a->pon($this->obxeto("illa"), true);
    
    $a->pon_ok(true, "pcontrol_xpax_link()");

    $this->obxeto("illa")->post(null);
  }

  public function url(?string $k = null) { //* IMPLEMENTA IXestorCEditor_imx
    return $this->url;
  }

  public function post_url($url = null, ?string $k = null) { //* IMPLEMENTA IXestorCEditor_imx
    $this->url = $this->obxeto("ceditor_imx")->pon_src($url);

    $this->obxeto("logo")->style("default" , "background-image: url({$this->url});cursor: pointer;");
    $this->obxeto("logo")->style("readonly", "background-image: url({$this->url});");
  }

  public function operacion_bAceptar(Efs $e) {
    $cbd = new FS_cbd();

    if (($p = $this->paxina_obd($cbd)) == null) return $e;


    if (($erro = $this->validar($p)) != null) {
      $e->post_msx( $erro );

      return $e;
    }

    $cbd->transaccion();
    
    try {
      if ($this->__update($cbd, $p)) {
        $e->post_msx( "Sección actualizada correctamente", "m" );
        
        $cbd->commit();
      }
      else {
        $e->post_msx( "Sucedió un error al actualizar la BD" );
        
        $cbd->rollback();
      }
    }
    catch (Exception $ex) {
      $e->post_msx("<pre>{$ex}</pre>");
    }

    //* actualiza a lista de páxinas
    //~ if (($loms = $this->pai->obxeto("loms")) == null) return $e;
    


    $this->preparar_saida($e->ajax());


    return $e;
  }

  public function valor_urlCustom():?string {
    $_urlc = parse_url( trim($this->obxeto("url_custom")->valor()) );

//~ print_r($_urlc);

    if (($path = $_urlc["path"]) == null) {
      $this->obxeto("url_custom")->post(null);
      
      return null;
    }
    
    if ($path[0                ] == "/") $path  = substr($path, 1); 
    if ($path[strlen($path) - 1] != "/") $path .= "/"; 
    
    
    $this->obxeto("url_custom")->post($path);
     
     
    return $path;
  }

  public static function __logo($id = "logo") {
    $l = new Div($id);

    //~ $l->envia_SUBMIT("onclick");
    $l->envia_AJAX("onclick");


    return $l;
  }

  protected function post_paxina_base(Paxina_obd $p) {
    $this->pax_0 = $p;

    $this->obxeto("id_oms"   )->post($p->atr("id_opcion")->valor);
    $this->obxeto("id_pax"   )->post($p->atr("id_paxina")->valor);
    $this->obxeto("tipo"     )->post($p->atr("tipo"     )->valor);
    $this->obxeto("id_idioma")->post($p->atr("id_idioma")->valor);
    
    $this->obxeto("id3"      )->post(null);

    $this->pon_obxeto(self::__sidioma($p->atr("id_sincro")->valor, $p->atr("id_paxina")->valor));

    $this->obxeto("visible")->post( $p->atr("visible")->valor == 1 );
    $this->estado( $p->atr("estado")->valor );

// programar publicacion (x fran)
    $this->obxeto("tdatapub")->post($p->atr("publicar")->fvalor("Y-m-d"));
    $this->obxeto("thorapub")->post($p->atr("publicar")->fvalor("H:i"  ));
    
    $this->obxeto("url_site"  )->post($p->action_site());
    
    $this->obxeto("url_limpa" )->post($p->action_2(true , false));
    $this->obxeto("url"       )->post($p->action_2(false, false));

    $this->post_urlCustom($p);
    
    $this->obxeto("nome"      )->post($p->atr("nome"      )->valor);
    $this->obxeto("titulo"    )->post($p->atr("titulo"    )->valor);
    $this->obxeto("cat_h1"    )->post($p->atr("cat_h1"    )->valor);
    $this->obxeto("grupo"     )->post($p->atr("grupo"     )->valor);
    $this->obxeto("robots"    )->post($p->atr("robots"    )->valor);
    $this->obxeto("descricion")->post($p->atr("descricion")->valor);

    $this->obxeto("script_cabecera")->post($p->atr("script")->valor);

    $this->obxeto("cprops")->post_paxina($p);


    //* calcula url_logo.
    $cbd = new FS_cbd();
    
    $this->config_urlNula($p, $cbd);
    
    $url = null; 
    if (($l = $p->iclogo_obd($cbd)) != null) $url = $l->url();
    
    $this->post_url($url);
    
    $this->obxeto("bSupLogo")->visible = $l != null;
  }

  protected function paxina_obd_base($tipopax, FS_cbd $cbd = null) {
    //~ if ($cbd == null) $cbd = new FS_cbd();

    //~ $pax = Paxina_obd::inicia($cbd, $this->obxeto("id_pax")->valor(), $tipopax);
    $pax = $this->pax_0;

    $pax->atr("titulo"    )->valor = $this->obxeto("titulo"    )->valor();
    $pax->atr("cat_h1"    )->valor = $this->obxeto("cat_h1"    )->valor();
    $pax->atr("descricion")->valor = $this->obxeto("descricion")->valor();
    $pax->atr("robots"    )->valor = $this->obxeto("robots"    )->valor();

    $pax->atr("script"    )->valor = trim($this->obxeto("script_cabecera")->valor());

    $pax->atr("url_custom")->valor = $this->valor_urlCustom();

    $pax->atr("grupo")->valor = $this->obxeto("grupo")->valor();


    $pax->atr("nome" )->valor = $this->obxeto("nome" )->valor();


    $pax->atr("visible")->valor = ($this->obxeto("visible")->valor())?"1":"0";
    $pax->atr("estado" )->valor = $this->estado();

//~ print_r($pax->a_resumo());

   if ($this->obxeto("tdatapub")->valor() == null)
      $pax->atr("publicar")->valor = null;
   elseif ($this->obxeto("thorapub")->valor == null) 
      $pax->atr("publicar")->valor = $this->obxeto("tdatapub")->valor() . " " . "00:00:00";
   else
      $pax->atr("publicar")->valor = $this->obxeto("tdatapub")->valor() . " " . $this->obxeto("thorapub" )->valor() . ":00";

     /*  if ($this->obxeto("tdatapub2")->valor() == null) $pax->atr("publicar")->valor = null;
      else $pax->atr("publicar")->valor = $this->obxeto("tdatapub2")->valor(); */
      
   
    return $pax;

  }

/*
  protected function operacion_bAbrir(Efs $e) {
  
    $url_dominio = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'];

    $url_completa = rtrim($url_dominio, '/') . '/' . ltrim($this->urlCustom(), '/');

    header("Location: $url_completa");

  }
*/
  protected function operacion_bEditar(Efs $e) {
    //~ $e->evento()->html();

    $e_aux = $this->operacion_bAceptar($e);

    if ($this->hai_erros) return $e;

    return $e->op_editar($this->obxeto("id_oms")->valor(), $this->obxeto("id_pax")->valor());
  }

  protected function operacion_bCancelar(Efs $e) {
    //~ $p = XPaxinas::paxina_obd(null, null, $this->obxeto("id_pax")->valor());

    $this->__reset($this->pax_0);

    $this->preparar_saida($e->ajax());

    return $e;
  }

  protected function operacion_bSupLogo(Efs $e) {
    $cbd = new FS_cbd();

    if (($p = $this->paxina_obd($cbd)) == null) {
      $e->ajax()->pon_ok();
      
      return $e;
    }


    $cbd->transaccion();

    if ($p->iclogo_delete($cbd)) {
      $this->post_paxina($p);
      
      $cbd->commit();
    }
    else
      $cbd->rollback();


  
    $this->preparar_saida($e->ajax());


    return $e;
  }

  protected function __update(FS_cbd $cbd, Paxina_obd $p = null) {
    if ($p == null) return true;

    if (!$this->update_grupo($cbd, $p)) return false;

    if (!$this->update_logo ($cbd, $p)) return false;

    if (!$p->update($cbd, false, false)) return false;

    return self::control_reindexar($cbd, $p);
}

  private function estado($estado = null) {
    //* set
    if ($estado != null) {
      if ($estado == "borrador") {
        $this->obxeto("estado")->post(false);
        $this->obxeto("estado")->etq = "Página borrador";
      }
      else {
        $this->obxeto("estado")->post(true);
        $this->obxeto("estado")->etq = "Página publicada";
      }

      if ($estado == "ponte") {
        $this->obxeto("puente")->post(true);
        $this->obxeto("puente")->etq = "Página puente";
      }
      else {
        $this->obxeto("puente")->post(false);
      }

      return;
    }

    //* get
    if (!$this->obxeto("estado")->valor()) return "borrador";

    if ($this->obxeto("puente")->valor() ) return "ponte";


    return "publicada";
  }

  private function post_urlCustom(Paxina_obd $p):void {
    if(($url = $p->action_custom(false)) == null) {
      $this->obxeto("bCopiar"   )->visible = false;      
      $this->obxeto("bAbrir"    )->visible = false;
            
      $this->obxeto("url_custom")->post(null);      
      
      return;
    }
    
    $this->obxeto("url_custom")->post($url);
    
    $url_0 = $p->action_site();
    
    $this->obxeto("bCopiar")->pon_eventos("onclick", "carquivo_benlace('{$url_0}{$url}')");
    $this->obxeto("bAbrir" )->pon_eventos("onclick", "window.open('{$url_0}{$url}', '_blank')");
  }

  private function update_logo(FS_cbd $cbd, Paxina_obd $p) {
    if (($url = $this->obxeto("ceditor_imx")->aceptar_imx()) == null) return true;
    
    if ($url == Imaxe_obd::noimx) return true;


    return $p->iclogo_update($cbd, $url);
  }

  private function update_grupo(FS_cbd $cbd, Paxina_obd $p) {
    $id_ms  = $p->atr("id_site"  )->valor;
    $id_oms = $p->atr("id_opcion")->valor;
    $grupo  = $p->atr("grupo"    )->valor;


    return $cbd->executa("update menu_site_opcion set grupo = '{$grupo}' where id_ms = {$id_ms} and id_oms = {$id_oms}");
  }

  private function config_urlNula(Paxina_obd $p, FS_cbd $cbd = null) {
    if ($p == null) return;
    
    if (($s = $p->atr("id_site")->valor) == null) return;
    
    
    if ($cbd == null) $cbd = new FS_cbd();
    
        
    $l = Site_metatags_obd::inicia($cbd, $s)->iclogo_obd($cbd);
    
    if ($l == null) return;
    
    if (($url = $l->url()) == null) return;
    
    if (!is_file($url)) return;
    
    $this->obxeto("ceditor_imx")->pon_src_nula($url);
    
    $this->config_urlNula = true;
  }

  private function control_reindexar(FS_cbd $cbd, Paxina_obd $p):bool {
    //* devolve true se as operacións foron correctas.
    
    $reindexar = false;
    $id_site   = $p->atr("id_site")->valor;
      
    $this->post_urlCustom($p);

    if (($oms = $this->control_reindexar_action($cbd, $p)) != null) {
      $reindexar = true; //* pode afectar a url_custom 
      
      if (!$oms->update($cbd)) return false;
      

      $url_site = Efs::url_site($id_site, $cbd);
      
      if ( is_file("{$url_site}{$this->action_0}") ) unlink("{$url_site}{$this->action_0}");
      
      try {
        $oms->escribe_exemplar(null, $url_site);
      }
      catch (Exception $ex) {
        throw $ex;
        
        return false;
      }
    }
    
    if (!$reindexar) $reindexar = ($this->urlcus_0  != $p->atr("url_custom")->valor); //* htaccess. urls_custom.
    
    
    if ($reindexar) {
          if ( ($oms_aux = $oms) == null     );
      elseif ( !$oms->control_primeira($cbd) ) $oms_aux = null;
      
      try {
        Opcion_ms_obd::indexar_site($id_site, $oms_aux, $cbd);
      }
      catch (Exception $ex) {
        throw $ex;
        
        return false;
      }
    }

    //* actualizamos valores de paxinfo.
    $this->urlcus_0  = $p->atr("url_custom")->valor;

    if ($oms != null) {
      $this->paxnome_0 = $p  ->atr("nome"  )->valor;
      
      $this->action_0  = $oms->atr("action")->valor;
    
      $this->obxeto("url_limpa" )->post($p->action_2(true , false));
      $this->obxeto("url"       )->post($p->action_2(false, false));
    }

    return true;
  }

  private function control_reindexar_action(FS_cbd $cbd, Paxina_obd $p):?Opcion_ms_obd {
    //* devolve null, se NON é necesario reindexar o site.

    if ($this->paxnome_0 == $p->atr("nome")->valor) return null;

    if ($p->atr("id_sincro")->valor != null)
      if ($p->atr("id_paxina")->valor != $p->atr("id_sincro")->valor) return null;


    $id_site  = $p->atr("id_site")->valor;

    //** $action_0 = Opcion_ms_obd::calcula_action($this->paxnome_0, $id_site, false);
    $action_0 = $this->action_0;
    $action_1 = Opcion_ms_obd::calcula_action($p->atr("nome")->valor, $id_site, false);


    if ($action_0 == $action_1) return null;


    $oms = Opcion_ms_obd::inicia($cbd, $p->atr("id_opcion")->valor);

    $oms->atr("action")->valor = $action_1;
    
    
    return $oms;
  }

  private static function __sidioma($id_sincro, $id_paxina) {
    $cbd = new FS_cbd();

    $sql = "select a.id_paxina, a.id_idioma, b.nome
              from v_ml_paxina a inner join idioma b on (a.id_idioma = b.id_idioma)
             where a.id_sincro = {$id_sincro}";

    $r = $cbd->consulta($sql);

    $a = null;
    while ($a2 = $r->next()) $a[$a2['id_paxina']] = $a2['nome'];

    $s = new Select("sidioma", $a);

    //~ $s->envia_submit("onchange");
    $s->envia_ajax("onchange");


    $s->post($id_paxina);

    return $s;
  }

  private static function paxinfo_logo() {
    $l = new Div("logo");

    $l->envia_AJAX("onclick");

    $l->clase_css("default", "paxinfo_logo");


    return $l;
  }

  private static function paxinfo_enlace($k, $txt, $tit) {
    $l = new Div($k, $txt);
    
    $l->title = $tit;

    $l->clase_css("default", "lista_elemento_a");


    return $l;
  }
}

//----------------------------------------------

class Paxinfo_nulo extends Paxinfo {
  public function __construct($ptw = "ptw/xpax/paxinfo/paxinfo_nulo.html") {
    parent::__construct(null, $ptw);

    $this->ptw_0 = null;
  }

  public function titulo_ficha() {
    return "";
  }

  //~ public function post_paxina(Paxina_obd $p) {
  //~ }

  public function paxina_obd(FS_cbd $cbd = null) {
    return null;
  }
}


//*****************************************

final class Paxinfo_props extends Componente {
  public $id_site = null;
  public $url     = null;

  public function __construct() {
    parent::__construct("cprops", Refs::url("ptw/xpax/paxinfo/paxinfo_props.html"));

    $this->pon_obxeto(new Param("pidpax"));
    $this->pon_obxeto(new Param("ptipo"));

    $this->pon_obxeto(new Param("c"));
    $this->pon_obxeto(new Param("p"));
    $this->pon_obxeto(new Param("v"));
    $this->pon_obxeto(new Param("pdata_a"));
    $this->pon_obxeto(new Param("pdata_c"));
  }

  public function post_paxina(Paxina_obd $p) {
    $this->id_site = $p->atr("id_site")->valor;


    $_stats = XStats::paxina_obd( $p->atr("id_paxina")->valor );

    $this->obxeto("c")->post( $_stats["c"] );
    $this->obxeto("p")->post( $_stats["p"] );
    $this->obxeto("v")->post( $_stats["v"] );

    $a = $p->atr("creado")->a_data();
    $data_c = "{$a['d']}/{$a['m']}/{$a['Y']}&nbsp;{$a['H']}:{$a['i']}:{$a['s']}";

    $a = $p->atr("actualizado")->a_data();
    $data_a = "{$a['d']}/{$a['m']}/{$a['Y']}&nbsp;{$a['H']}:{$a['i']}:{$a['s']}";

    $this->obxeto("pidpax")->post( $p->atr("id_paxina")->valor );

    $this->obxeto("pdata_c")->post($data_c);
    $this->obxeto("pdata_a")->post($data_a);

    $this->obxeto("ptipo")->post($p->atr("tipo")->valor);
  }
}

