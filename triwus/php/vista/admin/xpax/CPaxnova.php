<?php

final class CPaxnova extends Componente {
  const ptw_0 = "ptw/xpax/cpaxnova_1.html";

  public $id_oms_pai = null;

  public function __construct(FGS_usuario $u) {
    parent::__construct("cpaxnova", self::ptw_0);
    
    $this->visible = false;

    $this->pon_obxeto( self::__stipo($u) );

    $this->pon_obxeto(Panel_fs::__text("nome"));
    $this->pon_obxeto(new Textarea("descricion", 2, 22));

    $this->pon_obxeto(new CLista_idiomas($u));

    $this->pon_obxeto(Panel_fs::__baceptar());
    $this->pon_obxeto(Panel_fs::__bcancelar());

    $this->obxeto("bCancelar")->envia_ajax("onclick");
    //~ $this->obxeto("bAceptar" )->envia_ajax("onclick");
  }

  public function operacion(EstadoHTTP $e) {
    if ($this->obxeto("bCancelar")->control_evento()) $this->operacion_bCancelar($e);
    if ($this->obxeto("bAceptar" )->control_evento()) $this->operacion_bAceptar($e);


    return parent::operacion($e);
  }

  public function operacion_bpaxnova(Epcontrol $e, $id_oms = null, $tipopax = 'libre') {
    $this->obxeto("tipo")->post($tipopax);

    $e = $this->pai->op_config_alta($e, $id_oms);

    $this->pai->preparar_saida($e->ajax());


    return $e;
  }

  private function operacion_bCancelar(Epcontrol $e) {
    $this->visible = false;

    $this->pai->preparar_saida($e->ajax());


    return $e;
  }

  private function operacion_bAceptar(Epcontrol $e) {
    if (($erro = $this->validar()) != null) {
      //~ $this->obxeto("perro_n")->post($erro['n']);
      //~ $this->obxeto("perro_l")->post($erro['l']);

      $e->post_msx($erro);

      return $e;
    }

    $cbd       = new FS_cbd();
    $oms       = $this->oms_obd();
    $pax       = $this->paxina_obd();
    $a_idiomas = $this->obxeto("lidiomas")->a_checks();

    $cbd->transaccion();

    if (!$this->insert($cbd, $oms, $pax, $a_idiomas)) {
      $cbd->rollback();
      
      $e->post_msx("Sucedió un error al actualizar la BD.");

      return $e;
    }
      
    
    $e->post_msx("Sección añadida correctamente.", "m");


    $cbd->commit();
    //~ $cbd->rollback();

    return $this->operacion_bCancelar($e);
  }

  private function validar():?string {
    $erro = null;

    $n = Erro::validar_nome_paxina( $this->obxeto("nome")->valor() );

        if ($n == -1) $erro .= Erro::nome_pax_0 . "<br>";
    elseif ($n == -2) $erro .= Erro::nome_pax_1 . "<br>";
    else              $this->obxeto("nome")->post($n);


    if ($this->obxeto("lidiomas")->a_checks() == null) $erro .= "Debes seleccionar al menos un idioma.<br>";

    return $erro;
  }

  private function oms_obd() {
    $oms = new Opcion_ms_obd();

    $oms->atr("id_ms")->valor = $this->pai->id_site;
    $oms->atr("pai"  )->valor = $this->id_oms_pai;


    return $oms;
  }

  private function paxina_obd() {
    $pax = Paxina_obd::factory($this->obxeto("tipo")->valor());

    $pax->atr("nome"      )->valor = $this->obxeto("nome"      )->valor();
    $pax->atr("descricion")->valor = $this->obxeto("descricion")->valor();
    $pax->atr("estado"    )->valor = "borrador";


    return $pax;
  }

  private function insert(FS_cbd $cbd, Opcion_ms_obd $oms, Paxina_obd $pax, $a_idiomas) {
    if ($a_idiomas == -1) return $this->insert_0($cbd, $oms, $pax, null);

    $id_sincro = null;

    foreach ($a_idiomas as $id_idioma) {
      if ($id_sincro == null) {
        if (!$this->insert_0($cbd, $oms, $pax, $id_idioma)) return false;

        $id_sincro = $pax->atr("id_paxina")->valor;

        continue;
      }

      $pax->atr("id_paxina")->valor = $id_sincro; //* asi duplicamos sempre a primeira

      if (!$pax->duplicar($cbd, $id_idioma)) return false;
    }

    return true;
  }

  private function insert_0(FS_cbd $cbd, Opcion_ms_obd $oms, Paxina_obd $pax, $id_idioma) {
    $pax->atr("id_idioma")->valor = $id_idioma;

    return $oms->insert($cbd, $pax);
  }

  //~ private static function __perro($id) {
    //~ $d = new Div($id);

    //~ $d->clase_css("default", "texto3_erro");

    //~ //$d->visible = false;


    //~ return $d;
  //~ }

  private static function __stipo(FGS_usuario $u):Select {
    $id_a  = $u->atr("id_almacen")->valor;
    $id_sb = $u->atr("id_sb"     )->valor;

    $venta = ( (($id_sb % 3) == 0) && ($id_a != null) ); //* siteventas e vendedor


    $_o = array("libre"=>"Diseño libre", "novas2"=>"Blog/Noticias");

    if ($u->venta()) {
      $_o["marcas" ] = "Venta online (marcas)";
      $_o["ventas2"] = "Venta online";
    }
    
    $s = new Select("tipo", $_o);


    return $s;
  }
}

