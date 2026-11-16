<?php

final class Efspax_xestor extends EstadoHTTP {

  public static function construir(IEfspax_xestor $e, $id_idioma = null, $editor = true) {
    $cbd = new FS_cbd();

    $site_obd  = self::__site_obd($e, $cbd);

    $id_site   = $site_obd->atr("id_site")->valor;
    $id_oms    = self::parse_idoms($e->id_oms());
    $id_paxina = self::parse_idpax($e->id_paxina());

    if (!$editor) if ($site_obd->atr("baixa")->valor != null) die(self::__erro("1"));

    $e->config      ($site_obd->config_obd());
    $e->config_mobil($site_obd->config_obd("mobil"));

    $p = self::__paxobd($cbd, $id_oms, $id_paxina, $id_idioma, $editor, $site_obd);
    
    if ($p == null) die(self::__erro("1"));

    if (!$editor) {
      if (!$p->publicada()) die(self::__erro("1"));
    }

    $e->id_site   = $id_site;
    $e->venta     = $site_obd->usuario_obd()->venta();
    $e->url_limpa = $site_obd->atr("url_limpa")->valor == 1;
    if (!$editor) $e->url_limpa = $site_obd->atr("url_limpa")->valor == 1;


    $u = $e->usuario();

    self::parse_permisos($p, $u);


    $config = $e->config();

    $e->pon_obxeto(new Edita_elmt_nulo());
    $e->pon_obxeto(new Prgf_cab($site_obd, $config, $p));
    $e->pon_obxeto(new Prgf_pe($site_obd, $config));

    $e->pon_obxeto(new Param("ccarro")); //* IMPORTANTE !!! ESTA INSTRUCCIÓN DEBE SER DESPUES DE: $e->pon_obxeto(new Prgf_cab($site_obd, $config, $p));

    $e->pon_obxeto(new Param("elmt_difucom")); //* compatibilidade hacia atras


    $sc = Site_config_obd::inicia($cbd, $id_site);

    $e->pon_obxeto(new CCookies($sc, $p->atr("id_idioma")->valor));

    $e->pon_obxeto(new CRS_0($sc, $config));

    $e->pon_obxeto(new Hidden("nome_site", $site_obd->atr("nome")->valor));
    $e->pon_obxeto(new Hidden("id_idioma"));
    $e->pon_obxeto(new Hidden("id_css"));
    $e->pon_obxeto(new Hidden("dominio", TrwlConfig::dominio) );
    $e->pon_obxeto(new Hidden("editor", (($editor)?"1":"0")) );
    $e->pon_obxeto(new Hidden("haipanoramix", "1")); //* en pricipio iniciamos a 1, no se realizará esta comprobación en el editor.


    self::pon_paxina_obd($e, $p, $editor);
  }

  public static function pon_paxina_obd(IEfspax_xestor $e, Paxina_obd $p, $editor = true, Site_obd $site_obd = null) {
    $cbd       = new FS_cbd();

    if ($site_obd == null) $site_obd  = $e->__site_obd(null, $cbd);

    $id_site   = $site_obd->atr("id_site")->valor;
    $id_oms    = $p->atr("id_opcion")->valor;
    $id_pax    = $p->atr("id_paxina")->valor;

    if (!self::__concordar($cbd, $id_site, $id_oms, $id_pax)) die(self::__erro());


    $id_idioma = $p->atr("id_idioma")->valor;
    $config    = $e->config();

    $e->id_oms($p->atr("id_opcion")->valor);
    $e->id_paxina($p->atr("id_paxina")->valor);

    $e->action = $p->action();

    $e->pon_obxeto( new Menu_site($site_obd, $e->usuario(), $config, $id_idioma, $editor) );

/*
    if ($e->obxeto("menu_app") == null) {
      $e->pon_obxeto( new Menu_site($site_obd, $e->usuario(), $config, $id_idioma, $editor) );
    }
    elseif ($e->obxeto("menu_app")->id_idioma != $id_idioma) {
      $e->pon_obxeto( new Menu_site($site_obd, $e->usuario(), $config, $id_idioma, $editor) );
    }
*/
    $e->pon_obxeto(Detalle::factory($p, $editor));

    $a_actu = $p->atr("actualizado")->a_data();

    //~ echo "<pre>" . print_r($a_actu, true) . "</pre>";

    $e->pon_obxeto( new Param("id_oms"          , $p->atr("id_opcion")->valor)   );
    $e->pon_obxeto( new Param("id_paxina"       , $p->atr("id_paxina")->valor)   );
    $e->pon_obxeto( new Param("nome_paxina"     , $p->atr("nome")->valor)        );
    $e->pon_obxeto( new Param("paxina-timestamp", "{$a_actu['d']}/{$a_actu['m']}/{$a_actu['Y']} {$a_actu['H']}:{$a_actu['i']}:{$a_actu['s']}") );

    $e->pon_obxeto(new CBuscador($site_obd, $editor));
    $e->pon_obxeto(CIdioma::inicia($site_obd, $config, $id_idioma, $editor));
    $e->pon_obxeto(new CLoginWeb($site_obd, $id_idioma));

    $e->obxeto("menu_app")->selecionar($e->id_oms());

    if ($editor) $e->obxeto("detalle")->declara_botoneras($e, $p);

    //~ if ($e->venta) {
      //~ $e->pon_obxeto(new CPagar2($site_obd));
      //~ $e->pon_obxeto(new CCarro2($site_obd));
    //~ }


    //* PARCHE. Mellor deixalas iniciadas aínda que o site non teña permisos para venta. REVISAR. algunhas operacións de venta, en sites sen permisos xeran ERROS GRAVES.
    $e->pon_obxeto( new CPagar2($site_obd) );
    $e->pon_obxeto( new CCarro2($site_obd) );


    self::config_usuario($e);

    self::configurar_paxina($e);
  }

  public static function config_lectura(IEfspax_xestor $e, $editor = true) {
    $config = $e->config();

    $e->paxina()->head->pon_css($config->css("basico"));
    $e->paxina()->head->pon_css($config->css("novas" ));
    $e->paxina()->head->pon_css($config->css("ms"    ));
    $e->paxina()->head->pon_css($config->css("venta" ));

    if ($editor) {
      $e->paxina()->head->sup_css(Refs::url("css/edicion.css"));
      $e->paxina()->head->sup_css($config->css("edicion"));
    }

    if ($config->atr("tipo")->valor == "mobil") {
      $e->paxina()->head->pon_css(Refs::url("css/mobil.css"));
      $e->paxina()->head->pon_js(Refs::url("js/mobil.js"));
    }


    $e->obxeto("cab")->config_lectura();
    $e->obxeto("detalle")->config_lectura();

    $e->obxeto("pe")->config_lectura();

    $e->obxeto("cloginweb")->config_lectura();

    if (!$e->venta) return;

    $e->obxeto("ccarro")->config_lectura();
    $e->obxeto("cpagar2")->config_lectura();
  }

  public static function config_edicion(IEfspax_xestor $e, $editor = true) {
    $config = $e->config();

    $e->paxina()->head->pon_css($config->css("basico"));
    $e->paxina()->head->pon_css($config->css("novas") );
    $e->paxina()->head->pon_css($config->css("ms")    );
    $e->paxina()->head->pon_css($config->css("venta") );

    if ($editor) {
      //~ $css_editor = ($e->mobil)?"css/edicion.m.css":"css/edicion.css";

      //~ $e->paxina()->head->pon_css($css_editor);
      $e->paxina()->head->pon_css($config->css("edicion"));

      $e->paxina()->head->pon_js(Refs::url("js/carrusel-2.js"));
      //~ $e->paxina()->head->pon_js(Refs::url("js/galeria.js"));
    }

    if ($e->mobil) {
      $e->paxina()->head->pon_css(Refs::url("css/mobil.css"));

      $e->paxina()->head->pon_js(Refs::url("js/mobil.js"));
    }


    $e->obxeto("cab")->config_edicion();
    $e->obxeto("detalle")->config_edicion();
    $e->obxeto("pe")->config_edicion();

    $e->obxeto("cloginweb")->config_edicion();

    if (!$e->venta) return;

    $e->obxeto("ccarro" )->config_edicion();
    $e->obxeto("cpagar2")->config_edicion();
  }

  public static function config_usuario(IEfspax_xestor $e) {
    $u = $e->usuario();

    $e->obxeto("cab")->config_usuario($e);
    $e->obxeto("detalle")->config_usuario($e);

    $e->obxeto("pe")->config_usuario($e);

    $e->obxeto("cloginweb")->config_usuario($e);
    $e->obxeto("cbuscador")->config_usuario($e);

    //~ if (!$e->venta) return; 

    $e->obxeto("cpagar2")->config_usuario($e);
    $e->obxeto("ccarro")->config_usuario($e);
  }

  public static function parse_idoms($id_oms) {
    if ($id_oms == null) die(self::__erro());

    if (!is_numeric($id_oms)) die(self::__erro());

    return round($id_oms);
  }

  public static function parse_idpax($id_paxina = null) {
    if ($id_paxina == null) return null;

    if (!is_numeric($id_paxina)) die(self::__erro());

    return round($id_paxina);
  }

  private static function parse_permisos(Paxina_obd $p, FGS_usuario $u) {
    if  ( Erro::__parse_permisos($p->a_grupo(), $u->a_permisos()) ) return;

    die(self::__erro());
  }

  public static function __paxobd(FS_cbd $cbd, $id_oms, $id_paxina = null, $id_idioma = null, $editor = true) {
    //~ echo "$id_oms,$id_paxina,$id_idioma,$editor<br />";
    if ($id_paxina != null) return Paxina_obd::inicia($cbd, $id_paxina);

    $oms = Opcion_ms_obd::inicia($cbd, $id_oms);
    

    if ($id_idioma == null) $id_idioma = MLsite_obd::idioma_predeterminado($oms->atr("id_ms")->valor, $cbd);

    if (($p = $oms->paxina_obd($cbd, $id_idioma)) != null) return $p;;
    
    
    return $oms->paxina_obd($cbd);
  }

  public static function __erro($i_erro = "0") {
    return Erro::__paxina($i_erro)->html();
  }

  public static function configurar_paxina(IEfspax_xestor $e) {
    $config = $e->config();

    $e->panel("centro")->ptw = $config->ptw_pax();

    $e->obxeto("id_idioma")->post($e->id_idioma());

    $e->obxeto("id_css")   ->post($config->atr("id_css")->valor);
  }

  public static function __plataforma() {
    return Mobile_Detect::__exec();
  }

  public static function operacion_redirect(IEfspax_xestor $e, $id_paxina, $sincro = 0) {
    //~ $id_paxina_0 = $e->id_paxina();
    $id_idioma   = $e->id_idioma();


    $cbd = new FS_cbd();

    $p = Paxina_obd::inicia($cbd, $id_paxina);

    if (($sincro == 1) && ($id_idioma != $p->atr("id_idioma")->valor)) {
      $id_sincro = $p->atr("id_sincro")->valor;

      $r = $cbd->consulta("select id_paxina from v_ml_paxina where id_sincro = '{$id_sincro}' and id_idioma = '{$id_idioma}'");

      if ($a = $r->next()) $p = Paxina_obd::inicia($cbd, $a['id_paxina']);
    }


    if ($e->edicion()) return $e->__crear_instancia($p->atr("id_opcion")->valor, $p->atr("id_paxina")->valor);

    //~ echo "action::" . $p->action(true) . "<br>";


    $e->redirect($p->action_2($e->url_limpa));

    return $e;
  }

  private static function __site_obd(IEfspax_xestor $e, FS_cbd $cbd = null) {
    if ($cbd == null) $cbd = new FS_cbd();

    $s = $e->__site_obd(null, $cbd);

    if (!self::__concordar($cbd, $s->atr("id_site")->valor, $e->id_oms(), $e->id_paxina())) die(self::__erro());


    return $s;
  }

  private static function __concordar(FS_cbd $cbd, $id_site, $id_oms, $id_paxina) {
    if ($id_oms  == null) die(self::__erro());
    if ($id_site == null) die(self::__erro());

    $where = "id_site = {$id_site} and id_oms = {$id_oms}";
    if ($id_paxina != null) $where .= " and id_paxina = {$id_paxina}";


    $r = $cbd->consulta("select * from v_site_paxina where {$where}");


    return $r->next();
  }
}

