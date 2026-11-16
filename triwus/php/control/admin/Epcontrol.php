<?php


final class Epcontrol extends Efs_admin {
  public $id_site;

  public $id_oms_0;
  public $id_pax_0;

  public $en_custodia = false;

  public static $_rutas = array("bweb_0"        => array("m1" => "mi web"         , "i" => "Cpcindex_dweb"   , "a" => null            , "u" => 0),
                                "bxpax"         => array("m1" => "mi web"         , "i" => null              , "a" => "xpax"          , "u" => 0),
                                "bmetatags"     => array("m1" => "mi web"         , "i" => null              , "a" => "cmetatags"     , "u" => 0),
                                "bplantilla"    => array("m1" => "mi web"         , "i" => null              , "a" => "cplantilla"    , "u" => 0),
                                "bconfig_0"     => array("m1" => "configuración"  , "i" => "Cpcindex_config" , "a" => null            , "u" => 1),
                                "bversion"      => array("m1" => "configuración"  , "i" => null              , "a" => "cusuario"      , "u" => 0),
                                "bmail"         => array("m1" => "configuración"  , "i" => null              , "a" => "cmail"         , "u" => 0),
                                "bredessoc"     => array("m1" => "configuración"  , "i" => null              , "a" => "credessoc"     , "u" => 0),
                                "bel"           => array("m1" => "configuración"  , "i" => null              , "a" => "cel"           , "u" => 0),
                                "bml"           => array("m1" => "configuración"  , "i" => null              , "a" => "cml"           , "u" => 0),
                                "burexistrados" => array("m1" => "configuración"  , "i" => null              , "a" => "curexistrados" , "u" => 0),
                                "btenda_0"      => array("m1" => "mi tienda"      , "i" => "Cpcindex_tenda"  , "a" => null            , "u" => 1),
                                "bxarticulos"   => array("m1" => "mi tienda"      , "i" => null              , "a" => "cxarticulos"   , "u" => 0),
                                "bxcategoria"   => array("m1" => "mi tienda"      , "i" => null              , "a" => "cxcategoria"   , "u" => 0),
                                "bxcc"          => array("m1" => "mi tienda"      , "i" => null              , "a" => "cxcc"          , "u" => 0),
                                "bxpromos"      => array("m1" => "mi tienda"      , "i" => null              , "a" => "cxpromos"      , "u" => 0),
                                "bxalmacen"     => array("m1" => "mi tienda"      , "i" => null              , "a" => "cxalmacen"     , "u" => 0),
                                "bxclientes"    => array("m1" => "mi tienda"      , "i" => null              , "a" => "cxclientes"    , "u" => 0),
                                "bxpedidos"     => array("m1" => "mi tienda"      , "i" => null              , "a" => "cxpedidos"     , "u" => 0),
                                "bxfpago"       => array("m1" => "mi tienda"      , "i" => null              , "a" => "cxfpago"       , "u" => 0),
                                "bxmarca"       => array("m1" => "mi tienda"      , "i" => null              , "a" => "cxmarca"       , "u" => 0),
                                "bmiscelanea"   => array("m1" => "mi tienda"      , "i" => null              , "a" => "cconfig_ventas", "u" => 0),
                                "bstats_0"      => array("m1" => "estadísticas"   , "i" => null              , "a" => "cstats"        , "u" => 0),
                                "bdrive_0"      => array("m1" => "mis documentos" , "i" => "Cpcindex_docs"   , "a" => null            , "u" => 0),
                                "bdocs_triwus"  => array("m1" => "mis documentos" , "i" => null              , "a" => "carquivo"      , "u" => 0),
                                "bdocs_dropbox" => array("m1" => "mis documentos" , "i" => null              , "a" => "cdropbox"      , "u" => 0),
                                "blogs_0"       => array("m1" => "accesos"        , "i" => "Cpcindex_accesos", "a" => null            , "u" => 0),
                                "blogs_apache"  => array("m1" => "accesos"        , "i" => null              , "a" => "apache"        , "u" => 0),
                                "blogs_triwus"  => array("m1" => "accesos"        , "i" => null              , "a" => "triwus"        , "u" => 0),
                                "bapis_1"       => array("m1" => "apis"           , "i" => "Cpcindex_apis"   , "a" => null            , "u" => 0),
                                "bcliemail"     => array("m1" => "apis"           , "i" => null              , "a" => "ccliemail"     , "u" => 0),
                                "bapis_triwus"  => array("m1" => "apis"           , "i" => null              , "a" => "capitriwus"    , "u" => 0),
                                "bapis_fscript" => array("m1" => "apis"           , "i" => null              , "a" => "capifscript"   , "u" => 0),
                                "bapis_dbox"    => array("m1" => "apis"           , "i" => null              , "a" => "capidbox"      , "u" => 0),
                                "bapis_pwa"     => array("m1" => "apis"           , "i" => null              , "a" => "cpwa"          , "u" => 0),
                                "caxuda_con"    => array("m1" => "axuda"          , "i" => null              , "a" => "caxuda_con"    , "u" => 0),
                                "caxuda_wiki"   => array("m1" => "axuda"          , "i" => null              , "a" => "caxuda_wiki"   , "u" => 0),
                               );


  private $bpth_select = null;
  private $cpc_select  = null;

  public function __construct(FGS_usuario $u) {
    parent::__construct(self::__paxina($u), $u);

    $c = $u->cuenta_obd();
    $a = $u->atr("id_almacen")->valor;

    $this->id_site = $u->atr("id_site")->valor;


//~ echo "<pre>" . print_r($u->a_resumo(), true) . "</pre>";

    $this->pon_obxeto(new VM_pcontrol_aux());


    $this->pon_obxeto(new Param("paviso")); //* para readonly, en desuso.

    $this->pon_obxeto(new Param("pinfoAlmacenCentral"));

    $this->pon_obxeto(new IllaAJAX("cpc_activo"));

    $this->pon_obxeto(new Cpcontrol_migas( "bweb_0" ));


    $this->obxeto("pdominio")->post(TrwlConfig::dominio);



    if ($c->edita_web())
      $this->inicia_cpcindex($this->obxeto("bweb_0"), new Cpcindex_dweb());
    elseif ($a > 1) {
      $cpcindex = new Cpcindex_tenda();

      $cpcindex->config_usuario($u);

      $this->inicia_cpcindex($this->obxeto("btenda_0"), $cpcindex);
    }
    else {
      $cpcindex = new Cpcindex_config();

      $cpcindex->config_usuario($u);

      $this->inicia_cpcindex($this->obxeto("bconfig_0"), $cpcindex);

      $this->obxeto("beditarweb")->visible = false;
    }

    $this->obxeto("beditarweb")->visible = $u->atr("permisos")->valor == "admin";


    $this->en_custodia = $u->en_custodia();

    $this->readonly($this->en_custodia);


    if ($a < 2) return;

    //* Crea parámetros para info sobre o almacén central.

    self::info_almacen_1( $this->id_site );
  }

  public function readonly($b = true) {
    parent::readonly($b);

    $this->obxeto("paviso")->visible = $b;

    $paviso = ($b)?$this->obxeto("clopd_cancela")->html():null;

    $this->obxeto("paviso")->post($paviso);

    $this->obxeto("blogout")->readonly = false;

    $this->obxeto("bversion")->readonly = false;
    $this->obxeto("beditarweb")->readonly = false;

    $this->obxeto("bconfig_0")->readonly = false;
    $this->obxeto("bweb_0"   )->readonly = false;
    $this->obxeto("btenda_0" )->readonly = false;
    $this->obxeto("bstats_0" )->readonly = false;
    $this->obxeto("bdrive_0" )->readonly = false;
  }

  public function id_site() {
    return $this->id_site;
  }

  public function __site_obd($id_site = null, FS_cbd $cbd = null) {
    if ($cbd == null) $cbd = new FS_cbd();

    return Site_obd::inicia($cbd, $this->id_site);
  }

  public function operacion() {
    $this->post_msx();

    $this->update_site();

    if ($this->obxeto("beditarweb")->control_evento()) return $this->op_editar();

    if (($e_aux = $this->operacion_r()) != null) return $e_aux;

    if (($e_aux = $this->operacion_bpth()) != null) return $e_aux;


    if (($e_aux = parent::operacion()) != null) {
      if ($this->cpc_select != null) {
        $this->obxeto("cpc_activo")->post( $this->html_cpc_select() );
      }

      return $e_aux;
    }


    return $this;
  }


  public function actualizar() {}

  public function actualizar_config($id_config) {
    if (($scs = $this->obxeto("sConfig_site")) == null) return;


    $scs->id_config = $id_config;

    $scs->obxeto("config_selec")->post( Sconfig_visor::config_selec($scs, $scs->id_config) );
  }

  public function op_cambiak() {
    if (($obx = $this->obxeto("cambiak")) == null) return null;

    if (!$obx->control_evento()) return null;


    return new Efs_finsesion_cambiak($this->usuario()->atr("email")->valor);
  }

  public function op_posicion($id_ms, $pos0, $pai, $suma) {
    $cbd = new FS_cbd();

    $cbd->transaccion();

    $ms = Menu_site_obd::inicia($cbd, $id_ms);

    if (!$ms->intercambia_opciones($cbd, $pos0, $pai, $suma)) {
      $cbd->rollback();

      return;
    }

    $cbd->commit();
  }

  public function op_nivel($id_oms, $suma) {

//~ echo "(id_oms, suma): ($id_oms, $suma)<br>";

    $cbd = new FS_cbd();

    $cbd->transaccion();

    $oms = Opcion_ms_obd::inicia($cbd, $id_oms);

//~ echo "<pre>" . print_r($oms->a_resumo(), 1) . "</pre>";



    if ($suma == 1)
      $ok = $oms->baixar_nivel($cbd);
    else
      $ok = $oms->subir_nivel ($cbd);

    if ($ok)
      $cbd->commit();
    else
      $cbd->rollback();
  }

  public function op_editar($id_oms = null, $id_pax = null) {
    $cbd = new FS_cbd();

//~ echo "aaaaaaaaaaaaaaaa:: $id_oms, $id_pax<br>";

    if ($id_oms == null) {
      $id_idioma = MLsite_obd::idioma_predeterminado($this->id_site(), $cbd);
      
      $oms = Opcion_ms_obd::inicia_primeira($cbd, $this->id_site(), $id_idioma);
      
//~ echo "<pre>" . print_r($oms->a_resumo(), 1) . "</pre>";

      $id_oms = $oms->atr("id_oms")->valor;
      
      $pax = $oms->paxina_obd($cbd, $id_idioma);
      
      $id_pax = $pax->atr("id_paxina")->valor;
      
//~ echo "<pre>" . print_r($pax->a_resumo(), 1) . "</pre>";
    }

    $this->id_oms_0 = $id_oms;
    $this->id_pax_0 = $id_pax;



    $_SESSION['triwus.pcontrol.php'] = $this;



    //~ return $this;
    return $this->redirect("editor.php?pax={$id_pax}");
  }

  public function update_site() {
    $cbd = new FS_cbd();

    if (($s = $this->__site_obd(null, $cbd)) == null) return;

    $s->update($cbd);
  }

  public function operacion_cpcactivo(ICPC $c) {
    $this->cpc_select = $c->nome;

    $this->obxeto("cpc_index")->visible = false;

    $this->obxeto("cpc_migas")->pon_m2($c->cpc_miga());

    $this->obxeto("cpc_activo")->post($this->html_cpc_select());
  }

  protected function __site_idconfig() {
    if ( ($sc = $this->obxeto("sConfig_site")) == null ) return -1;

    return array("escritorio" => $sc->id(),
                 "mobil"      => $sc->id("mobil"));
  }

  private function operacion_r() {
//~ echo "<pre>" . print_r($_GET, true) . "</pre>";
//~ echo $this->evento()->html();

    if (!isset($_GET['r'])) return null;

    return $this->operacion_bpth($_GET['r']);
  }

  private function operacion_bpth($k = null) {
    if ($k == null) $k = $this->evento()->nome(1);

    if ($k == null) return null;

    if (!isset(self::$_rutas[$k])) return null;

    if (($_r = self::$_rutas[$k]) == null) return null;



    $c  = null;

    if ($_r['i'] != null) {
      $c = new $_r['i']();

      if ($_r['u'] == 1) $c->config_usuario($this->usuario());
    }

    $this->inicia_cpcindex($this->obxeto($k), $c);


    if ($_r['a'] == null) return $this;


    $this->operacion_cpcactivo(Cpc_index::factory($this, $_r['a']));


    return $this;
  }

  private function html_cpc_select() {
    $cs = $this->obxeto( $this->cpc_select );

    return $cs->html();
  }

  private function inicia_cpcindex(Link $bpth = null, Cpc_index $c = null) {
    //~ if ($this->bpth_select != null) $this->bpth_select->clase_css("default", "xp_pestanha_inactiva");

    if ($bpth == null) return;

    $this->obxeto("cpc_migas")->pon_m1($bpth->nome());


    $this->bpth_select = $bpth;

    //~ $this->bpth_select->clase_css("default", "xp_pestanha_activa");


    $this->cpc_select = null;

    $this->obxeto("cpc_activo")->post(null);


    if ($c == null) return;


    $this->obxeto("cpc_migas")->pon_m2(null);


    $c->readonly($this->en_custodia);

    $this->pon_obxeto($c);
  }

  private function info_almacen_1() {
    $cbd = new FS_cbd();

    $a_obd = Almacen_obd::inicia($cbd, $this->id_site, 1);
    $c_obd = $a_obd->contacto_obd($cbd);
    $e_obd = $c_obd->enderezo_obd();

    $url = ( ($logo = $c_obd->iclogo_obd($cbd)) != null )?$logo->url():Imaxe_obd::noimx;

    $t = "<div style='border-top: 1px solid #fff; color: #fff; margin: 15px 33px; padding-top: 15px;'>
            <div style='margin: 15px 0;'><img src='{$url}' style='max-height: 111px;'/></div>
            <div>DATOS ALMACÉN CENTRAL</div>
            <div style='margin: 15px 0;'>" . $e_obd->resumo() .  "</div>
            <div style='margin: 15px 0 0 0;'>" . $c_obd->atr("email")->valor .  "</div>
            <div style='margin:  5px 0 0 0;'>" . $c_obd->atr("mobil")->valor .  "</div>
          </div>";


    $this->obxeto("pinfoAlmacenCentral")->post( $t );

    return;
  }

  private static function __paxina(FGS_usuario $u) {
    $px = new Paxina("triwus.com - Panel de control");


    $px->formulario()->action = "pcontrol.php";

//~ echo "<pre>" . print_r($u->a_resumo(), true) . "</pre>";

    $u_tipo = $u->pcontrol_tipo();

        if ($u_tipo == 30) $px->formulario()->ptw = "ptw/form/ffs_vendedor.html";
    elseif ($u_tipo == 20) $px->formulario()->ptw = "ptw/form/ffs_1000.html";
    else                   $px->formulario()->ptw = "ptw/form/ffs.html";

    $px->pon_panel(new Panel_site_cab_0($u));
    $px->pon_panel(new PControl($u));
    $px->pon_panel(new Panel_site_pe());

    $px->head->pon_meta("content-language", "es", "content-language");


    return $px;
  }
}

//**********************************************

final class Cpcontrol_migas extends Componente {
  public function __construct($k_m1 = null, $m2 = null) {
    parent::__construct("cpc_migas");

    $this->pon_obxeto(self::_m1());

    $this->pon_obxeto(new Param("m2", $m2));

    $this->pon_m1($k_m1);
    $this->pon_m2($m2  );
  }

  public function pon_m1($k_m1) {
    $this->obxeto("m1")->post( Epcontrol::$_rutas[$k_m1]["m1"] );

    $this->obxeto("m1")->pon_eventos("onclick", "document.location.href = 'pcontrol.php?r={$k_m1}'");
  }

  public function pon_m2($m2) {
    $this->obxeto("m2")->post($m2);
  }

  public function html():string {
    if (($m2 = $this->obxeto("m2")->valor()) != null) $m2 = "<span>{$m2}</span>";

    $this->obxeto("m1")->readonly = ($m2 == null);

    return "<div class='pcontrol_migas_0'>". $this->obxeto("m1")->html() ."{$m2}</div>";
  }


  private static function _m1() {
    $s = new Span ("m1");

    $s->style("default" , "cursor:pointer;");
    $s->style("readonly", "cursor:initial;");

    $s->pon_eventos("onmouseover", "this.style.textDecoration='underline'");
    $s->pon_eventos("onmouseout" , "this.style.textDecoration='initial'"  );

    $s->readonly = true;

    return $s;
  }
}

