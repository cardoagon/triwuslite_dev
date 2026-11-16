<?php

class Elmt_vcard extends AElemento {
  const ptw_0 = "ptw/paragrafo/elmt/elmt_vcard.html";

  const qr_talla = 2;

  public $vcard_visible;

  public $url_arquivos;

  public function __construct(Elmt_vcard_obd $pe_obd) {
    parent::__construct($pe_obd, Refs::url(self::ptw_0));

    $this->vcard_visible = $pe_obd->atr("visible")->valor == 1;


    $this->pon_obxeto(new Image("qr", $pe_obd->url(), false));

    $this->pon_obxeto(new Param("nome"      , $pe_obd->atr("nome"      )->valor));
    $this->pon_obxeto(new Param("rs"        , $pe_obd->atr("notas"     )->valor));
    $this->pon_obxeto(new Param("tlfn"      , $pe_obd->atr("tlfn"      )->valor));
    $this->pon_obxeto(new Param("email"     , $pe_obd->atr("email"     )->valor));
    $this->pon_obxeto(new Param("dirpostal" , $pe_obd->atr("dirpostal" )->valor));
    $this->pon_obxeto(new Param("localidade", $pe_obd->atr("localidade")->valor));
    $this->pon_obxeto(new Param("cp"        , $pe_obd->atr("cp"        )->valor));
    $this->pon_obxeto(new Param("pais"      , $pe_obd->atr("pais"      )->valor));
    $this->pon_obxeto(new Param("www"       , $pe_obd->atr("www"       )->valor));
  }

  public function config_usuario(IEfspax_xestor $e = null) {
    parent::config_usuario($e);

    //~ $this->url_arquivos = $e->url_arquivos();
    $this->url_arquivos = Refs::url_arquivos() . "/";

    if ($this->id_elmt != null) return;

    //* 1- inicia datos predeterminados.
    $site = $e->__site_obd();
    $cont = $e->usuario()->contacto_obd(null, "p");
    $endz = $cont->enderezo_obd();

    if (($t = $cont->atr("mobil")->valor) == null) $t = $cont->atr("tlfn")->valor;
    if (($w = TrwlConfig::dominio           ) == null) $w = "";

    $_d["nome"      ] = $cont->atr("nome")->valor;
    $_d["rs"        ] = $cont->atr("razon_social")->valor;
    $_d["tlfn"      ] = $t;
    $_d["email"     ] = $cont->atr("email")->valor;
    $_d["localidade"] = $endz->atr("localidade")->valor;
    $_d["dirpostal" ] = $endz->resumo();
    $_d["cp"        ] = $endz->atr("cp")->valor;
    $_d["pais"      ] = "ES";
    $_d["www"       ] = "https://{$w}";

    //* 2.- asigna datos predeterminados.
    $this->obxeto("nome"      )->post( $_d["nome"      ] );
    $this->obxeto("rs"        )->post( $_d["rs"        ] );
    $this->obxeto("tlfn"      )->post( $_d["tlfn"      ] );
    $this->obxeto("email"     )->post( $_d["email"     ] );
    $this->obxeto("localidade")->post( $_d["localidade"] );
    $this->obxeto("dirpostal" )->post( $_d["dirpostal" ] );
    $this->obxeto("cp"        )->post( $_d["cp"        ] );
    $this->obxeto("pais"      )->post( $_d["pais"      ] );
    $this->obxeto("www"       )->post( $_d["www"       ] );

    //* 3.- crea o primeiro QR.
    $this->obxeto("qr")->pon_src( QR_elmt_vcard::xerar( Efs::url_tmp($this->url_arquivos), $_d, self::qr_talla ) );
  }

  public function elmt_obd() {
    $this->enome  = $this->obxeto("nome")->valor();
    $this->enotas = $this->obxeto("rs"  )->valor();
    
    $ec = new Elmt_vcard_obd();
    
    if (($src = $this->obxeto("qr")->src()) != Imaxe_obd::url_nula()) $ec->post_url($src);

    $ec->atr("visible"   )->valor = ($this->vcard_visible)?"1":"0";

    $ec->atr("tlfn"      )->valor = $this->obxeto("tlfn"      )->valor();
    $ec->atr("email"     )->valor = $this->obxeto("email"     )->valor();
    $ec->atr("localidade")->valor = $this->obxeto("localidade")->valor();
    $ec->atr("dirpostal" )->valor = $this->obxeto("dirpostal" )->valor();
    $ec->atr("cp"        )->valor = $this->obxeto("cp"        )->valor();
    $ec->atr("pais"      )->valor = $this->obxeto("pais"      )->valor();
    $ec->atr("www"       )->valor = $this->obxeto("www"       )->valor();


    $ec = $this->obd_base($ec);

//~ echo "<pre>" . print_r($ec->a_resumo(), true) . "</pre>";

    return $ec;
  }

  public function editor() {
    return new Editor_evcard($this);
  }

  public function html_propio() {
    return $this->html00();    
  }
}

//*****************************************************

final class Editor_evcard extends Edita_elmt {
  const ptw_0 = "ptw/paragrafo/elmt/editor_evcard.html";

  public function __construct(Elmt_vcard $elmt) {
    parent::__construct(null, "Gestor VCard", self::ptw_0, $elmt);

    $this->pon_obxeto( new Image("qr", $elmt->obxeto("qr")->src(), false) );

    $this->pon_obxeto( self::__text("nome"      , $elmt->obxeto("nome"      )->valor()) );
    $this->pon_obxeto( self::__text("rs"        , $elmt->obxeto("rs"        )->valor()) );
    $this->pon_obxeto( self::__text("tlfn"      , $elmt->obxeto("tlfn"      )->valor()) );
    $this->pon_obxeto( self::__text("email"     , $elmt->obxeto("email"     )->valor()) );
    $this->pon_obxeto( self::__text("localidade", $elmt->obxeto("localidade")->valor()) );
    $this->pon_obxeto( self::__text("dirpostal" , $elmt->obxeto("dirpostal" )->valor()) );
    $this->pon_obxeto( self::__text("cp"        , $elmt->obxeto("cp"        )->valor()) );
    $this->pon_obxeto( self::__text("pais"      , $elmt->obxeto("pais"      )->valor()) );
    $this->pon_obxeto( self::__text("www"       , $elmt->obxeto("www"       )->valor()) );

    $this->pon_obxeto(new Select("stalla", array("2"=>"S", "3"=>"M", "4"=>"L")));

    $this->pon_obxeto(new Div("erro"));


    $this->obxeto("qr")->style("default", "vertical-align: top;");
    
    $this->obxeto("stalla")->post(Elmt_vcard::qr_talla);

    $this->obxeto("erro")->clase_css("default", "texto3_erro");
  }

  public function operacion_aceptar(Efs_admin $e) {
    $_d["nome"      ] = $this->obxeto("nome"      )->valor();
    $_d["rs"        ] = $this->obxeto("rs"        )->valor();
    $_d["tlfn"      ] = $this->obxeto("tlfn"      )->valor();
    $_d["email"     ] = $this->obxeto("email"     )->valor();
    $_d["localidade"] = $this->obxeto("localidade")->valor();
    $_d["dirpostal" ] = $this->obxeto("dirpostal" )->valor();
    $_d["cp"        ] = $this->obxeto("cp"        )->valor();
    $_d["pais"      ] = $this->obxeto("pais"      )->valor();
    $_d["www"       ] = $this->obxeto("www"       )->valor();

    //* ACTUALIZA datos do elmt.
    $this->obxeto("erro")->post(null);
        

    $this->elmt->modificado = true;

    $this->elmt->obxeto("nome"      )->post( $_d["nome"      ] );
    $this->elmt->obxeto("rs"        )->post( $_d["rs"        ] );
    $this->elmt->obxeto("tlfn"      )->post( $_d["tlfn"      ] );
    $this->elmt->obxeto("email"     )->post( $_d["email"     ] );
    $this->elmt->obxeto("localidade")->post( $_d["localidade"] );
    $this->elmt->obxeto("dirpostal" )->post( $_d["dirpostal" ] );
    $this->elmt->obxeto("cp"        )->post( $_d["cp"        ] );
    $this->elmt->obxeto("pais"      )->post( $_d["pais"      ] );
    $this->elmt->obxeto("www"       )->post( $_d["www"       ] );

    $qr = QR_elmt_vcard::xerar( Efs::url_tmp($this->elmt->url_arquivos), $_d, $this->obxeto("stalla")->valor() );

    $this->elmt->obxeto("qr")->pon_src( $qr );


    return parent::operacion_aceptar($e);
  }

  protected function __editor_style() {
    return new Editor_svcard($this->elmt->elmt_obd());
  }

  private static function __text($id, $v) {
    $t = Panel_fs::__text($id);

    $t->post($v);
    
    return $t;
  }
}
