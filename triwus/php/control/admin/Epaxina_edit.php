<?php


class Epaxina_edit extends    Efs_admin
                   implements I_fs, IEfspax_xestor {


  public $id_site   = null;
  public $action    = null;

  public $mobil = false;
  //~ public $mobil = true;

  private $cc = null;

  private $a_ts = null;
  private $id_oms;
  private $id_paxina;
  private $editable = true;
  private $config;
  private $config_mobil;


  public function __construct($id_oms = null, $id_pax = null, $id_idioma = null, $editable = true, Config_obd $config = null) {
    $ehttp = $_SESSION['triwus.pcontrol.php'];

    parent::__construct(self::__paxina(), $ehttp->usuario());


    if ($id_oms == null) $id_oms = $ehttp->id_oms_0;
    if ($id_pax == null) $id_pax = $ehttp->id_pax_0;

    $this->id_site = $ehttp->id_site;

    $this->id_oms   ($id_oms);
    $this->id_paxina($id_pax);

    $this->config   ($config);

    $this->editable = $editable;

    Efspax_xestor::construir($this, $id_idioma, true);

    $this->cc = new Log_eventos();

    //~ $this->pon_obxeto(new VM_editor_recargar());
    //~ $this->pon_obxeto(new Param("vm_confirm")); //* Compatibilidade tilia

    $this->pon_obxeto(new Hidden("hccores"));

    $this->configurar();


    $this->obxeto("blogout")->envia_SUBMIT("onclick", "¿ Desea salir del panel de control ?");

    
    $this->obxeto("pdominio")->post(TrwlConfig::dominio);
  }

  protected function inicia_usuario_anterior(FGS_usuario $u = null) {
    $this->usuario( $u );
  }

  public function hai_cambios() {
    return $this->cc->hai_eventos();
  }

  //~ public function control_cambios(Efs_admin $e_aux = null) {
    //~ if (!$this->hai_cambios()) return $e_aux;

    //~ $this->pon_obxeto( new VM_editor_confirmar($e_aux) );

    //~ return $this;
  //~ }

  public function cc_pon(ObxetoFormulario $c, ObxetoFormulario $undo = null) {
    $this->cc->pon($c, $undo);

    $hai_cambios = $this->hai_cambios();

    $this->obxeto("detalle_btnr_este")->hai_cambios(true);
  }

  public function configurar() {
    $this->paxina()->body->onload = "trw_body_onload({$this->id_paxina})";


    $custodia = $this->usuario()->en_custodia();

    if     ($custodia)       $this->config_lectura();
    elseif ($this->editable) $this->config_edicion();
    else                     $this->config_lectura();

    $this->config_usuario();
  }

  public function configurar_vp() {
    $this->editable = !$this->editable;

    $this->configurar();
  }

  public function config_lectura() {
    $ptw_aux = ($this->mobil)?"m":"e";

    $this->paxina()->formulario()->ptw = "ptw/form/editor/feditor_1_{$ptw_aux}.html";

    $this->editable = false;

    $this->obxeto("d_btnr_n")->config_lectura();
    $this->obxeto("detalle_btnr_este")->config_lectura();

    Efspax_xestor::config_lectura($this);
  }

  public function config_edicion() {
    $ptw_aux = ($this->mobil)?"m":"e";

    $this->paxina()->formulario()->ptw = "ptw/form/editor/feditor_0_{$ptw_aux}.html";

    $this->editable = true;

    $this->obxeto("detalle_btnr_este")->config_edicion();

    $this->obxeto("d_btnr_n")->config_edicion();

    Efspax_xestor::config_edicion($this);
  }

  public function config_usuario(IEfspax_xestor $e = null) {
    $this->obxeto("d_btnr_n"         )->config_usuario($this);
    $this->obxeto("detalle_btnr_este")->config_usuario($this);

    $c = $this->config();

    $a = $c->a_cores();

    $hccores = null;
    foreach($a as $hex) {
      if ($hccores == null)
        $hccores = $hex;
      else
        $hccores .= Escritor_html::cnome . $hex;
    }

    $this->obxeto("hccores")->post($hccores);
  }

  public function edicion() {
    return true;
  }

  public function btnr_evento() {
    if (($str_evento = $this->obxeto("d_btnr_n")->obxeto("btnr_insertar")->obxeto("evento")->valor()) != null) return new EventoHTTP($str_evento);

    if (($str_evento = $this->obxeto("detalle_btnr_este")->obxeto("evento")->valor()) != null) return new EventoHTTP($str_evento);

    return null;
  }

  public function operacion() {
    $this->post_msx();
    
    if (($e_aux = $this->op_get()) != null) return $e_aux;

    //~ if ( ($vmc = $this->obxeto("vm_confirm")) != null ) {
      //~ if (($e_aux = $vmc->operacion($this)) != null) return $e_aux;
    //~ }

    //~ if (($e_aux = $this->obxeto("vm_recargar")->operacion($this)) != null) return $e_aux;


    if ($this->evento()->nome(1) == "redirect") {
      $id_paxina = $this->evento()->subnome(0);
      $sincro    = $this->evento()->subnome(1);

      return Efspax_xestor::operacion_redirect($this, $id_paxina, $sincro);
    }


    if (($e_aux = parent::operacion()) != null) return $e_aux;


    return $this;
  }
  
  public function op_get() {
    //~ $m = $this->evento()->html(). "<br>Pax: {$this->id_paxina}::<pre>" . print_r($_GET, 1) . "</pre>";
    
    //~ $this->post_msx($m, "m");
    
    if (!isset($_GET["pax"])) return null;
    
    if ($_GET["pax"] == null) return null;
    
    if ($_GET["pax"] == $this->id_paxina) return null;

    
    return Efspax_xestor::operacion_redirect($this, $_GET["pax"]);
  }
  
  public function op_gardar() {
    $cbd = new FS_cbd();

    $cbd->transaccion();
    
    try {
      if (!$this->op_gardar_2($cbd)) {
        $cbd->rollback();

        return false;
      }

      $cbd->commit();
    }
    catch (Exception $ex) {
      $cbd->rollback();
      
      $this->post_msx("Sucedió un error al actualizar la BD.<br>" . $ex->getMessage());

      return false;
    }


    $this->__reset($cbd);


    return true;
  }
/*
  public function __crear_instancia($id_oms, $cc = true, $id_paxina = null) {
    $id_idioma = $this->id_idioma();

//~ echo "$id_oms, $id_paxina, $id_idioma<br>";

    $e = new Epaxina_edit($id_oms, $id_paxina, $id_idioma, $this->editable, $this->config);

    if ($cc) return $this->control_cambios($e);
    

    return $e;
  }
*/
  public function __crear_instancia($id_oms, $id_paxina = null) {    
    $id_idioma = $this->id_idioma();

//~ echo "$id_oms, $id_paxina, $id_idioma<br>";

    $e = new Epaxina_edit($id_oms, $id_paxina, $id_idioma, $this->editable, $this->config);
    
    if ($id_paxina == null) return $e;

    
    $e->redirect("?pax={$id_paxina}");

    return $e;
  }

  public function __site_obd($id_site = null, FS_cbd $cbd = null) {
    if ($cbd == null) $cbd = new FS_cbd();

    return Site_obd::inicia($cbd, $this->id_site);
  }

  public function id_oms($id_oms = null) {
    if ($id_oms == null) return $this->id_oms;

    $this->id_oms = $id_oms;
  }

  public function id_paxina($id_paxina = null) {
    if ($id_paxina == null) return $this->id_paxina;

    $this->id_paxina = $id_paxina;
  }

  public function id_idioma($id_idioma = null) {
    return $this->obxeto("cidioma")->id_idioma($id_idioma);
  }

  public function config(Config_obd $c = null) {
    if ($this->mobil) return $this->config_mobil;

    if ($c != null) {
      $this->config = $c;

      return;
    }

    if ($this->mobil) if ($this->config_mobil != null) return $this->config_mobil;

    return $this->config;

  }

  public function config_mobil(Config_obd $c = null) {
    if ($c == null) return $this->config_mobil;

    $this->config_mobil = $c;
  }

  public function elemento($id_elmt) {
    if (($elmt = $this->obxeto("cab")->elemento($id_elmt)) != null) return $elmt;

    if (($a_prgf = $this->obxeto("detalle")->a_prgf()) != null)
      foreach ($a_prgf as $prgf)
        if (($elmt = $prgf->elemento($id_elmt)) != null) return $elmt;


    if (($elmt = $this->obxeto("pe")->elemento($id_elmt)) != null) return $elmt;

    return null;
  }

  public function operacion_mobil() {
    $this->mobil = !$this->mobil;

    Efspax_xestor::configurar_paxina($this);

    $this->configurar();

    return $this;
  }

  //~ protected function operacion_binicio() {
    //~ return parent::operacion_binicio();
  //~ }

  protected function operacion_bpanelcontrol() {
    $this->redirect("pcontrol.php");

    return $this;
  }

  protected function operacion_bpecharses() {
    $this->redirect("pcontrol.php?k=0");

    return $this;
  }


  private function __reset(FS_cbd $cbd) {
    $this->cc->limpar();

    $this->obxeto("detalle_btnr_este")->hai_cambios(false);

    Efspax_xestor::pon_paxina_obd($this, Paxina_obd::inicia($cbd, $this->id_paxina));

    $this->configurar();
    
    $this->preparar_ajax();
  }

  private function preparar_ajax() {  
    //~ $e->pon_obxeto(new CBuscador($site_obd, $editor));
    //~ $e->pon_obxeto(CIdioma::inicia($site_obd, $config, $id_idioma, $editor));
    //~ $e->pon_obxeto(new CLoginWeb($site_obd, $id_idioma));



    $this->obxeto("detalle_btnr_este")->preparar_saida( $this->ajax() );
    $this->obxeto("cab"              )->preparar_saida( $this->ajax() );
    
    //~ $this->obxeto("menu_app"         )->preparar_saida( $this->ajax() );
    
    $this->obxeto("detalle"          )->preparar_saida( $this->ajax() );
    $this->obxeto("pe"               )->preparar_saida( $this->ajax() );
    
    $this->ajax()->pon_ok(true, "editor_enviaAJAX_resposta()");
  }

  private function op_gardar_2($cbd) {
    if (!self::__prgf_update($this->obxeto("cab"), $cbd)         ) {
      $this->post_msx("Sucedió un error al actualizar la BD-cab.");

      return false;
    }
    
    if (!self::__prgf_update($this->obxeto("pe") , $cbd)         ) {
      $this->post_msx("Sucedió un error al actualizar la BD-pe.");

      return false;
    }
    
    if (!$this->obxeto("detalle")->update($cbd, $this->id_paxina)) {
      $this->post_msx("Sucedió un error al actualizar la BD-detalle.");
      
      return false;
    }
    
    if (!Paxina_obd::inicia($cbd, $this->id_paxina)->update($cbd)) {
      $this->post_msx("Sucedió un error al actualizar la BD-metadatos.");
      
      return false;
    }

    return true;
  }
  
  private static function __prgf_update(Paragrafo $prgf, FS_cbd $cbd) {
    if ($prgf->prgf_obd()->update($cbd, $prgf->a_elmt_obd())) return true;

    //~ $cbd->rollback();
    //~ $cbd->close();

    return false;
  }

  private static function __paxina() {
    $p = new Paxina("www.triwus.com - editor");

    $p->formulario()->action = "editor.php";
    //~ $p->formulario()->ptw    = "ptw/form/fpaxina_edicion.html";

    $p->pon_panel(new Panel_site_cab_1());
    $p->pon_panel(new Panel_paxina_edicion());
    $p->pon_panel(new Panel_site_pe());

    $p->head->pon_css( "css/fs_lista.css" );

    $p->head->pon_js("js/editor.js");
    $p->head->pon_js(Tilia::home() . "/js/cknovo.js");


    return $p;
  }
}

