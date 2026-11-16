<?php


final class CPagar2_envio extends CContacto
                       implements ICPagar2_paso {

  const AST          = "(*)";
  const aviso_postes = "Los precios de los portes para un destino insular pueden variar, revisa el carro de la compra.";

  const ptw_0 = "ptw/cpagar3/cpagar3_envio.html";
  const ptw_1 = "ptw/cpagar3/cpagar3_envio_1.html";
  const ptw_2 = "ptw/cpagar3/cpagar3_envio_enderezo.html";

  public  $url; 

  public  $precisa_envio = true;
  public  $id_destino    = false; //* 20170204 validación de destino, según configuración de portes.

  private $sabado        = null; //* data portes_sabado

  private $fp3           = false;

  private $alma_1        = null;

  private $cnife         = false; //* cnife obrigatorio
  private $tlfn          = false; //* tlfn obrigatorio

  public function __construct($id_site) {
    parent::__construct("d", "cpagar2_envio");

    //~ $this->visible = false;

    $this->pon_obxeto( new Param("psabado_display") );

    $this->pon_obxeto( new Param("ast_cnife") );
    $this->pon_obxeto( new Param("ast_tlfn" ) );

    $this->pon_obxeto( self::dmsx("paviso") );
    $this->pon_obxeto( self::dmsx("paviso_1") );

    $this->pon_obxeto( self::derro("perro") );
    $this->pon_obxeto( self::derro("perro_1") );

    $this->pon_obxeto( new Textarea("cprefservizo", 2, 30) );

    $this->pon_obxeto( self::__chrecollida() );

    $this->pon_obxeto( self::__chsabado() );

    $this->pon_obxeto(Panel_fs::__text("email", 20, 255, " "));

    $this->obxeto("email"       )->clase_css("default", "text cpagar3_text");
    $this->obxeto("cnif_d"      )->clase_css("default", "text cpagar3_text");
    $this->obxeto("nome"        )->clase_css("default", "text cpagar3_text");
    $this->obxeto("razon_social")->clase_css("default", "text cpagar3_text");

    $this->obxeto("mobil"       )->clase_css("default", "text cpagar3_text");
    $this->obxeto("mobil"       )->style    ("default", "width: 75%;");
    $this->obxeto("mobil"       )->envia_ajax("onchange");

    $this->obxeto("mobil_p0"    )->clase_css("default", "text cpagar3_text");
    $this->obxeto("mobil_p0"    )->style    ("default", "width: 11%; margin-right: 5%;");

    $this->obxeto("cprefservizo")->clase_css("default", "text cpagar3_text");
    $this->obxeto("cprefservizo")->style    ("default", "height: 55px;");

    $cenderezo = $this->obxeto("cenderezo");

    $cenderezo->_validar = $cenderezo->_validar_f;

    $cenderezo->obxeto("codtipovia")->clase_css("default", "text cpagar3_text");
    $cenderezo->obxeto("via"       )->clase_css("default", "text cpagar3_text");
    $cenderezo->obxeto("num"       )->clase_css("default", "text cpagar3_text");
    $cenderezo->obxeto("resto"     )->clase_css("default", "text cpagar3_text");
    $cenderezo->obxeto("cp"        )->clase_css("default", "text cpagar3_text");
    $cenderezo->obxeto("localidade")->clase_css("default", "text cpagar3_text");

    $cenderezo->obxeto("id_country")->style    ("default", "width: 100%;");

    $cenderezo->obxeto("cp")->envia_ajax("onchange");
    //~ $cenderezo->obxeto("cp")->envia_submit("onchange");

    $cenderezo->obxeto("id_country")->envia_ajax("onchange");
    //~ $cenderezo->obxeto("id_country")->envia_submit("onchange");


    //* configuración do site
    $sc = Site_config_obd::inicia(new FS_cbd(), $id_site);

    if ($sc->atr("ventas_cpagar2_cnife")->valor == 1) {
      $this->cnife = true;
      $this->obxeto("ast_cnife")->post( self::AST );
    }

    if ($sc->atr("ventas_cpagar2_tlfn")->valor == 1) {
      $this->tlfn = true;
      $this->obxeto("ast_tlfn")->post( self::AST );
    }
  }

  protected function ptw_0() {
    return Refs::url(self::ptw_0);
  }

  public function post_u(FGS_usuario $u = null) {
    if ($u == null)      return;


    //**** Para validar el destino SÓLO TEMPORALMENTE, tenemos en cuenta el alamcen 1.
    //* controlamos se hai un cambio de destino que poda afectar ao prezo dos portes e/ou aos envíos.

    if ($this->alma_1 == null) $this->alma_1 = Almacen_obd::inicia(new FS_cbd(), $this->pai->id_site, 1);


    //* Engade informacion de contacto do usuario.
    $_prefs = $u->cpago()->_prefs();
    
//~ echo "11111111111111111111111111111111111111111<pre>" . print_r($_prefs, 1) . "</pre>";

    $this->obxeto("cprefservizo")->post( $_prefs[0] );

    if (($c = $u->cpago()->destino()) == null) $c = $u->contacto_obd_2("p");

    $this->post_contacto($c);



    //* Configura recollida en tenda.
    $recoller_en = $this->alma_1->calcula_chrecollida();

    CPagar2_ml::chrecollida($this->obxeto("chrecollida"),
                            $this->pai->id_idioma,
                            $recoller_en
                           );

    if ($recoller_en != null) if (isset($_prefs[2])) $this->obxeto("chrecollida")->post($_prefs[2]);


    //* Engade enderezo de destino.
    $this->fp3           = $u->cpago()->fpago()->atr("id_fpago")->valor == 3;
    $this->precisa_envio = $u->cpago()->carro()->precisa_envio();


    if ($this->fp3) {
      $this->ptw = Refs::url(self::ptw_1);

      return;
    }
    elseif (!$this->precisa_envio) {
      $this->ptw = Refs::url(self::ptw_1);

      return;
    }
    else {
      $this->ptw = $this->ptw_0();

      //* chsabado
      $this->sabado = Almacen_obd::calcula_chsabado( $u->cpago()->carro()->almacen() ); //* reiniciamos sábado.

      if ($this->sabado == null) {
        $this->obxeto("psabado_display")->post("none");
      }
      else {
        $this->obxeto("psabado_display")->post("initial");

        $this->obxeto("chsabado")->post( ($_prefs[1] != null) );
      }


      CPagar2_ml::chsabado($this->obxeto("chsabado"),
                           $this->pai->id_idioma,
                           $this->sabado
                          );

      //* cenderezo
      $this->obxeto("cenderezo")->ptw = Refs::url(self::ptw_2);
    }
  }

  public function update(FGS_usuario $u) {
    $c = $this->contacto_obd(new Contacto_obd("d"), $this->precisa_envio);
    //~ else
      //~ $c = $this->alma_1->contacto_obd();


    $c->atr("email")->valor = $this->obxeto("email")->valor();


    $u->cpago()->destino($c);


    $_prefs = array($this->obxeto("cprefservizo")->valor(),
                    ($this->obxeto("chsabado")->valor())?$this->sabado:null,
                    $this->obxeto("chrecollida")->valor()
                   );

    $u->cpago()->_prefs($_prefs);


    return true;
  }

  public function operacion(EstadoHTTP $e) {
    if ( $this->obxeto("mobil")->control_evento() ) {
      $o_m = $this->obxeto("mobil"   );
      $o_p = $this->obxeto("mobil_p0");

      $mobil = Valida::tlfn( $o_m->valor(), $o_p->valor() );

      if ($mobil != null) $o_m->post( $mobil );

      $e->ajax()->pon( $o_m );

      return $e;
    }

    if (($e_aux = $this->operacion_chrecollida($e))     != null) return $e_aux;

    if (($e_aux = $this->operacion_control_destino($e)) != null) return $e_aux;

    return null;
  }

  public function operacion_chrecollida(IEfspax_xestor $e) {
//~ echo $e->evento()->html();
    if ( !$this->obxeto("chrecollida")->control_evento() ) return null;

    if ( $this->obxeto("chrecollida")->valor() ) {
      $endz = $this->alma_1->contacto_obd()->enderezo_obd();
      $this->obxeto("psabado_display")->post("none");
    }
    else {
      $endz = $e->usuario()->contacto_obd()->enderezo_obd();

      if ($this->sabado == null)
        $this->obxeto("psabado_display")->post("none");
      else
        $this->obxeto("psabado_display")->post("inline");
    }


//~ echo "<pre>" . print_r($endz->a_resumo(), true) . "</pre>";

    $this->obxeto("cenderezo")->post_enderezo($endz);

    $this->update( $e->usuario() );

    $this->pai->preparar_saida($e->ajax());

    return $e;
  }

  public function operacion_control_destino(IEfspax_xestor $e) {
//~ echo $e->evento()->html();
    $operro    = $this->obxeto("perro"   );
    $opaviso   = $this->obxeto("paviso"  );
    $operro1   = $this->obxeto("perro_1" );
    $opaviso1  = $this->obxeto("paviso_1");

    $operro  ->post("");
    $opaviso ->post("");
    $operro1 ->post("");
    $opaviso1->post("");

//~ echo "111111<br>";

    if ($this->obxeto("chrecollida")->valor()) {
      $e->ajax()->pon($operro  , true);
      $e->ajax()->pon($operro1 , true);
      $e->ajax()->pon($opaviso , true);
      $e->ajax()->pon($opaviso1, true);

      $this->pai->obxeto("ccarro2_lista")->preparar_saida($e->ajax());

      return $e;
    }

//~ echo "222222<br>";

    $sco = $this->obxeto("cenderezo")->obxeto("id_country");
    $ocp = $this->obxeto("cenderezo")->obxeto("cp");

    $chs = $this->obxeto("chsabado");

    if ( (!$sco->control_evento()) && (!$ocp->control_evento()) && (!$chs->control_evento()) ) {
      $e->ajax()->pon($operro  , true);
      $e->ajax()->pon($operro1 , true);
      $e->ajax()->pon($opaviso , true);
      $e->ajax()->pon($opaviso1, true);

      $this->pai->obxeto("ccarro2_lista")->preparar_saida($e->ajax());

      return $e;
    }

//~ echo "3333333<br>";


    if (($erro = $this->validar_destino()) != null) {
      $operro->post($erro);
    }
    elseif (Almacen_obd::destino_extranxeiro( $sco->valor() )) { //* se mostra o aviso no caso de non atopar erros
      $opaviso->post( CPagar2_ml::$envio[2][$this->pai->id_idioma] );
    }
    elseif (Almacen_obd::destino_insular( $sco->valor(), $ocp->valor() )) { //* se mostra o aviso no caso de non atopar erros
      $opaviso->post( CPagar2_ml::$envio[3][$this->pai->id_idioma] );
    }

    if ($chs->valor()) {
      if (Almacen_obd::destino_peninsular( $sco->valor(), $ocp->valor() )) {
        $opaviso1->post( CPagar2_ml::$envio[4][$this->pai->id_idioma] );
      }
      else {
        $chs    ->post(false);
        $operro1->post(CPagar2_ml::$envio[5][$this->pai->id_idioma]);

        $e->ajax()->pon($chs, true); //* neste caso e preciso modificar o valor de chs.
      }
    }

    $e->ajax()->pon($operro  , true);
    $e->ajax()->pon($operro1 , true);
    $e->ajax()->pon($opaviso , true);
    $e->ajax()->pon($opaviso1, true);

    $this->pai->obxeto("ccarro2_lista")->preparar_saida($e->ajax());


    return $e;
  }

  public function validar($_o = null, $validar_enderezo = true) {
    if (($erro = $this->validar_contacto_fp3()) != null) return $erro;

    if ($this->fp3)                                      return null; //* serve validar_contacto_fp3()

    if (!$this->precisa_envio)                           return null; //* serve validar_contacto_fp3()

//~ echo "Valida enderezo<br>";

    if (($erro = $this->obxeto("cenderezo")->validar()) != null) return $erro;


    return $this->validar_destino();
  }

  protected function validar_contacto_fp3() {
    $_o = array("nome", "email");

    if ($this->cnife) $_o[] = "cnif_d";
    if ($this->tlfn ) $_o[] = "mobil";

    $erro = parent::validar($_o, false);

//~ echo "$erro<br>";

    return $erro;
  }

  protected function validar_destino() {
    $id_country = $this->obxeto("cenderezo")->obxeto("id_country")->valor();
    $codpostal  = trim($this->obxeto("cenderezo")->obxeto("cp")->valor());

    $this->obxeto("cenderezo")->obxeto("cp")->post( $codpostal );

    //**** Para validar el destino SOLO TEMPORALMENTE, tenemos en cuenta el alamcen 1.
    //* controlamos se hai un cambio de destino que poida afectar ao prezo dos portes e/ou aos envíos.

    $this->alma_1 = Almacen_obd::inicia(new FS_cbd(), $this->pai->id_site, 1);

    if ( $this->alma_1->validar_destino($id_country, $codpostal) ) return null;


    return "El destino no está habilitado para venta. Perdone las molestias.<br />";
  }

  private static function __chsabado() {
    $ch = new Checkbox("chsabado", false);

    $ch->envia_ajax("onclick");
    //~ $ch->envia_submit("onclick");

    return $ch;
  }

  private static function __chrecollida() {
    $ch = new Checkbox("chrecollida", false);

    $ch->envia_ajax("onclick");
    //~ $ch->envia_submit("onclick");

    return $ch;
  }

  private static function dmsx($id, $erro = false) {
    $color = ($erro)?"#ba0000":"#0000ba";

    $d = new Span($id);

    $d->style("default", "margin: 0.7em 0;color: {$color};");

    return $d;
  }

  private static function derro($id) {
    return self::dmsx($id, true);
  }
}
