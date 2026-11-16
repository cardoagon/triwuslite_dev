<?php

class CContacto extends Componente
             implements IXestorCEditor_imx {

  const ptw_0      = "ptw/ccontacto/ccontacto.html";

  const pasterisco = "* ";


  public $url; 

  public string $scbd = "FS_cbd"; 

  public $_validar       = [];
  public $_validar_f     = ["razon_social", "email", "mobil", "cnif_d"];
  public $validar_url    = false;

  protected $id_contacto = null;

  private $c0_obd        = null;

  private $tipo          = -1;  //* forzamos o tipo de contacto, non se terá en conta stipo.
  private $wysiwyg       = false;

  public function __construct($tipo = -1, $id = "ccontacto", $wysiwyg = false, string $scbd = "FS_cbd") {
    parent::__construct($id, $this->ptw_0());

    //~ $this->id_contacto = $id_contacto; //* purga php8
    
    $this->tipo        = $tipo;
    $this->wysiwyg     = $wysiwyg;
    $this->scbd        = $scbd;

    $this->__iniciar();


    $this->pon_obxeto(new CEditor_imx());

    $this->pon_obxeto(CEditor_imx::__logo());
  }

  public function post_login($l) {
    if ($this->obxeto("stipo")->valor() != "p") return;

    $this->obxeto("email")->post( $l );
  }

  public function post_contacto(Contacto_obd $c = null, $baleirar = false) {
    if ($c == null) {
      $this->c0_obd = null; 
      
      return;
    }
   
    $this->c0_obd = $c;

    if ($baleirar && $c->baleiro()) {
      $this->obxeto("stipo"       )->post( $c->atr("tipo")->valor );
      $this->obxeto("cnif_d"      )->post( null );
      $this->obxeto("email"       )->post( null );
      $this->obxeto("nome"        )->post( null );
      $this->obxeto("razon_social")->post( null );
      $this->obxeto("mobil"       )->post( null );
      $this->obxeto("mobil_p0"    )->post( Contacto_obd::num_tlfn_prefix_0 );
      $this->obxeto("skype"       )->post( null );
      $this->obxeto("fax"         )->post( null );
      $this->obxeto("tlfn"        )->post( null );
      //~ $this->obxeto("foto"        )->post( null );

      if ($this->wysiwyg)
        $this->obxeto("atnotas")->post_txt( null );
      else
        $this->obxeto("atnotas")->post    ( null );

      $this->obxeto("cenderezo" )->post_enderezo( null );
    }


    $this->id_contacto = $c->atr("id_contacto")->valor;

//~ echo "tipo::{$this->tipo}<br>";

    if ($this->tipo != -1)
      $this->obxeto("stipo")->post( $this->tipo );
    else
      $this->obxeto("stipo")->post( $c->atr("tipo")->valor );

    $this->obxeto("cnif_d"      )->post( $c->atr("cnif_d")->valor );
    $this->obxeto("nome"        )->post( $c->atr("nome")->valor );
    $this->obxeto("email"       )->post( $c->atr("email")->valor );
    $this->obxeto("razon_social")->post( $c->atr("razon_social")->valor );
    $this->obxeto("mobil_p0"    )->post( $c->atr("mobil_p0")->valor );
    $this->obxeto("mobil"       )->post( $c->atr("mobil")->valor );
    $this->obxeto("skype"       )->post( $c->atr("skype")->valor );
    $this->obxeto("fax"         )->post( $c->atr("fax")->valor );
    $this->obxeto("tlfn"        )->post( $c->atr("tlfn")->valor );
    $this->obxeto("tlfn_p0"     )->post( $c->atr("tlfn_p0")->valor );
    $this->obxeto("web"         )->post( $c->atr("web")->valor );

    if ($this->wysiwyg)
      $this->obxeto("atnotas")->post_txt( $c->atr("notas")->valor );
    else
      $this->obxeto("atnotas")->post    ( $c->atr("notas")->valor );


    //~ $this->obxeto("email")->readonly = $this->obxeto("stipo")->valor() == "p";

    $this->post_logo($c);

    $this->obxeto("cenderezo" )->post_enderezo( $c->enderezo_obd() );

    $this->actualiza_pasterisco();
  }

  public function preparar_saida(Ajax $a = null) {
    parent::preparar_saida($a);

    if ($a == null) return;

    if ($this->wysiwyg) $this->obxeto("atnotas")->preparar_saida($a);
  }


  public function update_contacto(FS_cbd $cbd, Contacto_obd $c = null) {
    if ($c == null) $c = $this->contacto_obd(null, true, $cbd);

    if ( !$c->update($cbd) ) return false;
    
    $this->id_contacto = $c->atr("id_contacto")->valor; 
    
    return true;
  }

  public function id_contacto():?int {
    return $this->id_contacto;
  }

  public function contacto_obd(Contacto_obd $c = null, $precisa_envio = true, FS_cbd $cbd = null) {
    if ($c == null) {
      if ($cbd == null) $cbd = new $this->scbd();
      
      $c = Contacto_obd::inicia($cbd, $this->id_contacto, $this->obxeto("stipo")->valor());
    }
    
 
    if ( ( $mobil_p0 = trim($this->obxeto("mobil_p0")->valor()) ) == null ) $mobil_p0 =  Contacto_obd::num_tlfn_prefix_0;


    $c->atr("id_contacto" )->valor = $this->id_contacto;
    $c->atr("tipo"        )->valor = $this->obxeto("stipo")->valor();
    $c->atr("cnif_d"      )->valor = $this->obxeto("cnif_d")->valor();
    $c->atr("mobil_p0"    )->valor = $mobil_p0;
    $c->atr("mobil"       )->valor = $this->obxeto("mobil")->valor();
    $c->atr("email"       )->valor = $this->obxeto("email")->valor();
    $c->atr("skype"       )->valor = $this->obxeto("skype")->valor();
    $c->atr("nome"        )->valor = $this->obxeto("nome")->valor();
    $c->atr("razon_social")->valor = $this->obxeto("razon_social")->valor();
    $c->atr("fax"         )->valor = $this->obxeto("fax")->valor();
    $c->atr("tlfn"        )->valor = $this->obxeto("tlfn")->valor();
    $c->atr("tlfn_p0"     )->valor = $this->obxeto("tlfn_p0")->valor();
    $c->atr("web"         )->valor = $this->obxeto("web")->valor();
    $c->atr("notas"       )->valor = $this->obxeto("atnotas")->valor();

    if ($precisa_envio) $c->post($this->obxeto("cenderezo")->a_enderezo());

        
    if (($url_imx = $this->obxeto("ceditor_imx")->aceptar_imx()) != null) {
      $this->post_url( $url_imx );
    }


    return $c;
  }

  public function validar($_validar = null, $validar_enderezo = true) {
    //~ $this->obxeto("erro_0"  )->post(null);

    $c = $this->contacto_obd();


//~ echo "<pre>" .print_r($c->a_resumo(),1)."</pre>";

        if ($c->atr("tipo")->valor == "f" ) $_validar = $this->_validar_f;
    elseif ($_validar              == null) $_validar = $this->_validar;

//~ echo "<pre>" .print_r($_validar,1)."</pre>";

    if (($erro = $c->validar($_validar)) != null) return $erro;

    if ($validar_enderezo)
      if (($erro = $this->obxeto("cenderezo")->validar()) != null) return $erro;


    if (!$this->validar_url)  return null;

    if ($this->url() == null) return "Error, falta una imagen o logo.";

    return null;
  }

  public function operacion(EstadoHTTP $e) {
    //~ echo $e->evento()->html();
    
    //~ $this->obxeto("erro_0"  )->post(null);

    if ($this->obxeto("stipo")->control_evento()) return $this->operacion_stipo($e);

    if ($this->obxeto("logo" )->control_evento()) return $this->obxeto("ceditor_imx")->operacion_logo($e);


    if (($e_aux = $this->obxeto("cenderezo"  )->operacion($e)) != null) return $e_aux;
    if (($e_aux = $this->obxeto("ceditor_imx")->operacion($e)) != null) return $e_aux;

    return null;
  }

  public function url(?string $k = null) { //* IMPLEMENTA IXestorCEditor_imx
    if ($this->url != Imaxe_obd::noimx) return $this->url;

    return null;
  }

  public function post_url($url = null, ?string $k = null) { //* IMPLEMENTA IXestorCEditor_imx
    $this->url = $this->obxeto("ceditor_imx")->pon_src($url);

    $this->obxeto("logo")->style("default" , CEditor_imx::__style_logo($this->url, false, "left"));
    $this->obxeto("logo")->style("readonly", CEditor_imx::__style_logo($this->url, true , "left"));
  }

  protected function ptw_0() {
    return Refs::url(self::ptw_0);
  }

  private function operacion_stipo(EstadoHTTP $e) {
    $t = $this->obxeto("stipo")->valor();

    $c = Contacto_obd::inicia(new $this->scbd(), $this->id_contacto, $t);
    
    if ( $c->baleiro() ) {
      if ($this->c0_obd != null) {
        $c->clonar( $this->c0_obd );
      
        $c->atr("id_contacto")->valor = null;
        $c->atr("tipo"       )->valor = $t;
      }
    }

    $this->post_contacto($c, true);

    $this->preparar_saida( $e->ajax() );

    return $e;
  }

  private function actualiza_pasterisco() {
    $_ = ($this->obxeto("stipo")->valor() == "f")?$this->_validar_f:$this->_validar;


    $_past = $this->obxetos("pasterisco");

    foreach ($_past as $o) $o->post(null);

    if (count($_) == 0) return;

    foreach ($_ as $k2) $this->obxeto("pasterisco", $k2)->post("* ");

    if ($this->validar_url) $this->obxeto("pasterisco", "url")->post("* ");
  }

  private function __iniciar() {
    //~ $this->pon_obxeto(new Div("erro_0"));

    $this->pon_obxeto(self::__stipo($this->tipo));

    $this->pon_obxeto(Panel_fs::__text("email"       , null, 255, " "));
    $this->pon_obxeto(Panel_fs::__text("nome"        , null, 255, " "));
    $this->pon_obxeto(Panel_fs::__text("razon_social", null, 255, " "));
    $this->pon_obxeto(Panel_fs::__text("mobil_p0"    , null,   4, " "));
    $this->pon_obxeto(Panel_fs::__text("mobil"       , null,  12, " "));
    $this->pon_obxeto(Panel_fs::__text("skype"       , null, 111, " "));
    $this->pon_obxeto(Panel_fs::__text("cnif_d"      , null,  10, " "));
    $this->pon_obxeto(Panel_fs::__text("fax"         , null,  12, " "));
    $this->pon_obxeto(Panel_fs::__text("tlfn"        , null,  12, " "));
    $this->pon_obxeto(Panel_fs::__text("tlfn_p0"     , null,   4, " "));
    $this->pon_obxeto(Panel_fs::__text("web"         , null, 255, " "));

    if ($this->wysiwyg)
      $this->pon_obxeto(new CEditor_2("atnotas"));
    else {
      $this->pon_obxeto(Panel_fs::__textarea("atnotas"));

      $this->obxeto("atnotas")->maxlength = 555;
      $this->obxeto("atnotas")->style("default", "width: 33em; height: 48px;");
    }



    $this->pon_obxeto(new Param("pasterisco"), "email"       );
    $this->pon_obxeto(new Param("pasterisco"), "nome"        );
    $this->pon_obxeto(new Param("pasterisco"), "razon_social");
    $this->pon_obxeto(new Param("pasterisco"), "mobil_p0"    );
    $this->pon_obxeto(new Param("pasterisco"), "mobil"       );
    $this->pon_obxeto(new Param("pasterisco"), "skype"       );
    $this->pon_obxeto(new Param("pasterisco"), "cnif_d"      );
    $this->pon_obxeto(new Param("pasterisco"), "fax"         );
    $this->pon_obxeto(new Param("pasterisco"), "tlfn"        );
    $this->pon_obxeto(new Param("pasterisco"), "mobil_p0"    );
    $this->pon_obxeto(new Param("pasterisco"), "web"         );
    $this->pon_obxeto(new Param("pasterisco"), "url"         );
    $this->pon_obxeto(new Param("pasterisco"), "notas"       );



    $this->obxeto("tlfn" )->style("default", "min-width: initial;");
    $this->obxeto("mobil")->style("default", "min-width: initial;");

    $this->obxeto("mobil_p0")->post( Contacto_obd::num_tlfn_prefix_0 );


    $this->pon_obxeto( new CEnderezo($this->scbd) );
  }

  private function post_logo(Contacto_obd $c):void {
    if ($this->scbd == "FS_cbd") {
      $url = (($logo = $c->iclogo_obd(new $this->scbd())) != null)?$logo->url():Imaxe_obd::noimx;
    }
    else {
      $url = Imaxe_obd::noimx;
    }

    $this->post_url($url);
    
    $this->obxeto("ceditor_imx")->pon_src($url);
  }

  private static function __stipo($tipo = "p") {
    $s = new Select("stipo", Contacto_obd::_tipo(null));

    $s->post($tipo);

    //~ $s->envia_submit("onchange");
    $s->envia_ajax("onchange");

    return $s;
  }
}

//********************************************

class CEnderezo extends Componente {
  const ptw_0 = "ptw/ccontacto/cenderezo.html";

  public string $scbd = "FS_cbd"; 

  public $_validar   = [];
  public $_validar_f = ["codtipovia", "via", "num", "cp", "localidade"];

  public function __construct(string $scbd = "FS_cbd") {
    parent::__construct("cenderezo", $this->ptw_0());
    
    $this->scbd = $scbd;

    $this->__iniciar();
  }

  public function post_enderezo(Enderezo_obd $endrz = null) {
    $this->obxeto("clocal")->post_enderezo( $endrz );

    if ($endrz == null) {
      $this->obxeto("codtipovia" )->post( null );
      $this->obxeto("via"        )->post( null );
      $this->obxeto("num"        )->post( null );
      $this->obxeto("resto"      )->post( null );
      $this->obxeto("cp"         )->post( null );
      $this->obxeto("localidade" )->post( null );
      $this->obxeto("id_prov"    )->post( null );
      $this->obxeto("id_concello")->post( null );
      $this->obxeto("id_country" )->post( 203 );

      return;
    }

    $this->actualiza_pasterisco( $endrz->atr("endz_t")->valor );

    $this->obxeto("codtipovia")->post( $endrz->atr("codtipovia")->valor );
    $this->obxeto("via"       )->post( $endrz->atr("via"       )->valor );
    $this->obxeto("num"       )->post( $endrz->atr("num"       )->valor );
    $this->obxeto("resto"     )->post( $endrz->atr("resto"     )->valor );
    $this->obxeto("cp"        )->post( $endrz->atr("cp"        )->valor );
    $this->obxeto("localidade")->post( $endrz->atr("localidade")->valor );
    $this->obxeto("id_prov"   )->post( $endrz->atr("id_prov"   )->valor );
    $this->obxeto("id_country")->post( $endrz->atr("id_country")->valor );

    $this->pon_obxeto( self::__idconcello( new $this->scbd(), $endrz->atr("id_prov")->valor ) );

    $this->obxeto("id_concello")->post( $endrz->atr("id_concello")->valor );
  }

  public function operacion(EstadoHTTP $e) {
    //~ echo $e->evento()->html();

    if ($this->obxeto("id_prov")->control_evento()) {
      $this->pon_obxeto(self::__idconcello(new $this->scbd(), $this->obxeto("id_prov")->valor()));

      $e->ajax()->pon( $this->obxeto("id_concello") );

      return $e;
    }

    if ($this->obxeto("id_concello")->control_evento()) return $this->operacion_concello($e);

    if (($e_aux = $this->obxeto("clocal")->operacion($e)) != null) return $e_aux;

    return null;
  }

  public function a_enderezo() {
    $_e["codtipovia"] = $this->obxeto("codtipovia")->valor();
    $_e["via"       ] = $this->obxeto("via"       )->valor();
    $_e["num"       ] = $this->obxeto("num"       )->valor();
    $_e["resto"     ] = $this->obxeto("resto"     )->valor();
    $_e["cp"        ] = $this->obxeto("cp"        )->valor();
    $_e["localidade"] = $this->obxeto("localidade")->valor();
    $_e["id_prov"   ] = $this->obxeto("id_prov"   )->valor();
    $_e["id_country"] = $this->obxeto("id_country")->valor();

    if (($_e["id_concello"] = $this->obxeto("id_concello")->valor()) == Enderezo_obd::vs0) $_e["id_concello"] = "null";


    $_l = $this->obxeto("clocal")->_local();

    $_e["lat"] = $_l[0];
    $_e["lnx"] = $_l[1];

    return $_e;
  }

  public function enderezo_obd() {
    $e = new Enderezo_obd();

    $e->post( $this->a_enderezo() );

    return $e;
  }

  public function validar($_validar = null) {
    $o = $this->enderezo_obd();

        if ($this->pai->obxeto("stipo")->valor() == "f" ) $_validar = $this->_validar_f;
    elseif ($_validar                            == null) $_validar = $this->_validar;

    return $o->validar($_validar);
  }

  protected function ptw_0() {
    return Refs::url(self::ptw_0);
  }

  private function operacion_concello(EstadoHTTP $e) {
    if (($id_c = $this->obxeto("id_concello")->valor()) == Enderezo_obd::vs0) {
      $e->ajax()->pon_ok();

      return $e;
    }

    $cbd = new $this->scbd();

    $r = $cbd->consulta("select latitud, longitud from municipios where id = {$id_c}");

    if (!$_r = $r->next()) {
      $e->ajax()->pon_ok();

      return $e;
    }

    $this->obxeto("clocal")->pon_coords(3, $_r['latitud'], $_r['longitud']);


    $this->obxeto("clocal")->preparar_saida($e->ajax());

    return $e;
  }

  private function actualiza_pasterisco( $tipo ) {
    $_ = ($tipo == "f")?$this->_validar_f:$this->_validar;


    $_past = $this->obxetos("pasterisco");

    foreach ($_past as $o) $o->post(null);

    if (count($_) == 0) return;

    foreach ($_ as $k2) $this->obxeto("pasterisco", $k2)->post("* ");
  }

  private function __iniciar() {
    //~ $this->pon_obxeto(new Param("erro_0"));

    $this->pon_obxeto(Panel_fs::__text("codtipovia", null, 10 , " "));
    $this->pon_obxeto(Panel_fs::__text("via"       , null, 111, " "));
    $this->pon_obxeto(Panel_fs::__text("num"       , null, 10 , " "));
    $this->pon_obxeto(Panel_fs::__text("resto"     , null, 55 , " "));
    $this->pon_obxeto(Panel_fs::__text("cp"        , null, 11 , " "));
    $this->pon_obxeto(Panel_fs::__text("localidade", null, 55 , " "));

    $this->pon_obxeto(new CLocal_trw());



    $cbd = new $this->scbd();

    $this->pon_obxeto(self::__idprov    ($cbd));
    $this->pon_obxeto(self::__idconcello($cbd));
    $this->pon_obxeto(self::__idcountry ());


    $this->pon_obxeto(new Param("pasterisco"), "codtipovia" );
    $this->pon_obxeto(new Param("pasterisco"), "via"        );
    $this->pon_obxeto(new Param("pasterisco"), "num"        );
    $this->pon_obxeto(new Param("pasterisco"), "resto"      );
    $this->pon_obxeto(new Param("pasterisco"), "cp"         );
    $this->pon_obxeto(new Param("pasterisco"), "localidade" );
    $this->pon_obxeto(new Param("pasterisco"), "id_prov"    );
    $this->pon_obxeto(new Param("pasterisco"), "id_concello");


    $this->obxeto("via")->style("default", "width: 99%;");
  }

  private static function __idcountry() {
    $a_p = array("203" => "España",
                 "177" => "Portugal",
                 "72"  => "France",
                 "80"  => "Germany",
                 "106" => "Italy"
                );

    $s = new Select("id_country", $a_p);


    //~ $s->envia_submit("onchange");
    $s->envia_ajax("onchange");


    return $s;
  }

  private static function __idprov(FS_cbd $cbd) {
    $a_p = array(Enderezo_obd::vs0 => "...");

    $r = $cbd->consulta("select id_prov, provincia from provincias order by provincia");

    while ($a = $r->next()) $a_p[ $a['id_prov'] ] = ucwords($a['provincia']);

    $s = new Select("id_prov", $a_p);


    //~ $s->envia_submit("onchange");
    $s->envia_ajax("onchange");


    return $s;
  }

  private static function __idconcello(FS_cbd $cbd, $id_prov = null) {
    if ($id_prov == null) $id_prov = Enderezo_obd::vs0;

    $a_p = array(Enderezo_obd::vs0=>"...");

    if ($id_prov != Enderezo_obd::vs0) {
      $r = $cbd->consulta("select municipio, id from municipios where provincia_id = {$id_prov} order by municipio");

      while ($a = $r->next()) $a_p[ $a['id'] ] = ucwords($a['municipio']);
    }

    $s = new Select("id_concello", $a_p);


    $s->envia_ajax("onchange");
    //~ $s->envia_SUBMIT("onchange");


    return $s;
  }
}

//********************************************

final class CLocal_trw extends Componente {
  const ptw_0 = "ptw/ccontacto/clocal.html";

  private $coords_erro = 0;
  private $coords_tipo = 0; //* 0 => non está seteado  |  1 => (lat, lnx) INE  |  2 => (lat, lnx) tilia  |  3 => (lat, lnx) BD.

  public function __construct($id = "clocal") {
    parent::__construct($id, self::ptw_0);

    $this->reset();

    $this->pon_obxeto( new Button("bCoords", "<span style='font-size: 1.3em; color: #00ba00;'>⚙</span>&nbsp;&nbsp;Utiliza tu ubicación actual") );

    $this->obxeto("bCoords")->pon_eventos("onclick", "trw_cloca_coords(this)");
  }

  public function post_enderezo(Enderezo_obd $endrz = null) {
    if ($endrz == null) {
      $this->sup_coords();

      return;
    }

    $this->pon_coords(3, $endrz->atr("lat")->valor, $endrz->atr("lnx")->valor);
  }

  public function _local() {
    return array($this->obxeto("lat")->valor(),
                 $this->obxeto("lnx")->valor()
                );
  }

  public function reset() {
    $this->sup_coords();
  }

  public function config($lat, $lnx) {
    $this->pon_coords(3, $lat, $lnx);
  }

  public function config_tilia(EstadoHTTP $e) {
    $this->sup_coords();

    $a_l = $e->location();

    if ($a_l[0] == "erro") {
      $this->coords_erro = $a_l[1];

      return $e;
    }

    $this->pon_coords(2, $a_l[0], $a_l[1]);
  }

  public function pon_coords($tipo, $lat, $lnx) {
    //* 1.- valida coord. (non se escriben coordenadas 'incorrectas')
    if (!is_numeric($lat)) return;
    if (!is_numeric($lnx)) return;

    //* 2.- inicia valores.
    $this->coords_tipo = $tipo;

    $this->obxeto("lat")->post( $lat );
    $this->obxeto("lnx")->post( $lnx );
  }

  public function sup_coords() {
    $this->coords_erro = 0;
    $this->coords_tipo = 0;

    $this->pon_obxeto( self::__latlnx("lat") );
    $this->pon_obxeto( self::__latlnx("lnx") );
  }

  public function operacion(EstadoHTTP $e) {
    if ($this->obxeto("bCoords")->control_evento()) {
      $this->coords_erro = 0;
      $this->coords_tipo = 1;

      $e->ajax()->pon_ok();

      return $e;
    }


    return null;
  }

  private static function __latlnx($id) {
    $t = new Text($id, "--");

    $t->readonly = true;

    return $t;
  }
}
