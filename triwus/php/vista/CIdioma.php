<?php


abstract class CIdioma extends    Componente
                       implements I_fs       {

  protected $id_site          = null;
  protected $id_idioma_select = null;
  
  protected $editor = false;

  public function __construct($id_site, $id_idioma, $editor) {
    parent::__construct("cidioma");

    $this->id_site = $id_site;
    
    $this->editor  = $editor;

    $a_idiomas = $this->a_idiomas();

    $this->visible = (count($a_idiomas) > 1);

    $this->inicia_obxetos($a_idiomas);


    $this->id_idioma($id_idioma);
  }

  public static function inicia(Site_obd $s, Config_obd $c, $id_idioma, $editor) {
    switch ($c->atr("ci_tipo")->valor) {
      case "i": return new CIdioma_i($s->atr("id_site")->valor, $id_idioma, $editor);
      case "s": return new CIdioma_s($s->atr("id_site")->valor, $id_idioma, $editor);
      case "t": return new CIdioma_t($s->atr("id_site")->valor, $id_idioma, $editor);
    }

    return new CIdioma_i($s->atr("id_site")->valor, $id_idioma);
  }

  public function config_lectura() {}
  public function config_edicion() {}
  public function config_usuario(IEfspax_xestor $e = null) {}

  abstract public function id_idioma($id_idioma = null);
  abstract public function html_2();
  abstract protected function inicia_obxetos($a_idiomas);

  public function idioma_tilia() {
    return Idioma::factory( $this->id_idioma() );
  }

  public function a_idiomas() {
    //* en el editor muestra todos los idiomas propios.
    if ($this->editor) return MLsite_obd::__a_idiomas($this->id_site, 1);
    
    //* si no estamos en el editor muestra todos los idiomas propios y activos.
    return MLsite_obd::__a_idiomas($this->id_site, 3);
  }

  public function html():string {
    if (!$this->visible) return "";

    return "<div id='trw_cidioma' class='cidioma_marco'>" . $this->html_2() . "</div>";
  }

  protected function operacion_redirect(IEfspax_xestor $e, $id_idioma) {
    //~ echo $e->id_paxina() . "::{$id_idioma}<br>";
    
    if (($p = $this->__paxina_obd($e, $id_idioma)) == null) return $e;

    //~ echo $e->id_paxina() . "::" . $p->atr("id_paxina")->valor . "::{$id_idioma}::" . $p->atr("id_idioma")->valor . "<br>";
    
    if (($e->id_paxina() == $p->atr("id_paxina")->valor) && ($id_idioma == $p->atr("id_idioma")->valor)) return $e;


    $this->id_idioma($id_idioma);


    if ($e->edicion()) return Efspax_xestor::operacion_redirect($e, $p->atr("id_paxina")->valor);


    return $e->redirect( $this->action_2($p, $e->url_limpa, $id_idioma) );
  }

  private function __paxina_obd(IEfspax_xestor $e, $id_idioma = null) {
    if ($id_idioma == null) $id_idioma = $this->id_idioma();

    $cbd       = new FS_cbd();

    $pax_0     = Paxina_obd::inicia($cbd, $e->id_paxina());

    $id_sincro = $pax_0->atr("id_sincro")->valor;


    $sql = "select id_paxina from v_ml_paxina where id_sincro = {$id_sincro} and id_idioma = '{$id_idioma}'";

    $r = $cbd->consulta($sql);

    if ($a = $r->next()) return Paxina_obd::inicia($cbd, $a['id_paxina']);


    return $this->__paxina_obd_2($cbd, $e, $pax_0->atr("id_opcion")->valor, $id_idioma);
  }

  private function __paxina_obd_2(FS_cbd $cbd, IEfspax_xestor $e, $id_opcion, $id_idioma) {
    $p = Efspax_xestor::__paxobd($cbd, $id_opcion, null, $id_idioma, $e->edicion());

    if ($p != null) return $p;

    $id_pai = self::id_pai($cbd, $id_opcion);

    if ($id_pai != null) return $this->__paxina_obd_2($cbd, $e, $id_pai, $id_idioma);


    $oms_0 = Opcion_ms_obd::inicia_primeira($cbd, $this->id_site, $id_idioma);


    return Efspax_xestor::__paxobd($cbd, $oms_0->atr("id_oms")->valor, null, $id_idioma, $e->edicion());
  }

  private static function id_pai(FS_cbd $cbd, $id_opcion) {
    $r = $cbd->consulta("select pai from v_site_opcion where id_opcion = {$id_opcion}");

    if (!$a = $r->next()) return null;


    return $a['pai'];
  }

  private function action_2(Paxina_obd $p, $url_limpa, $id_idioma) {
    //* 20170201 non sirve para editor
    $a = $p->atr("action")->valor;
    
    if (!$url_limpa) return "{$a}?l={$id_idioma}";
    
    $a = str_replace(".php", "", $a);


    return "{$a}/{$id_idioma}/";
  }
}


//**********************************************************


class CIdioma_t extends CIdioma {

  public function __construct($id_site, $id_idioma, $editor) {
    parent::__construct($id_site, $id_idioma, $editor);
  }

  public function html_2() {
    $html = "";

    $a_tidioma = $this->obxetos("tidioma");

    if (count($a_tidioma) < 2) return $html;

    foreach ($a_tidioma as $tidioma) $html .= $tidioma->html();


    return $html;
  }

  public function id_idioma($id_idioma = null) {
    if ($id_idioma == null) return $this->id_idioma_select;

    if ($this->id_idioma_select != null) $this->obxeto("tidioma", $this->id_idioma_select)->readonly = false;

    if (($tidioma = $this->obxeto("tidioma", $id_idioma)) != null) $tidioma->readonly = true;


    $this->id_idioma_select = $id_idioma;
  }

  protected function inicia_obxetos( $a_idiomas ) {
    if (count($a_idiomas) == 0) return;

    foreach ($a_idiomas as $id_idioma=>$nome_idioma) {
      $t = new Div("tidioma", $id_idioma);

      $t->title = $nome_idioma;

      $t->clase_css("default" , "cidioma_texto");
      $t->clase_css("readonly", "cidioma_texto_sl");

      if ($this->editor)
        $t->pon_eventos("onclick", "editor_enviaEvento(this, true)");
      else
        $t->envia_submit("onclick");
        

      $this->pon_obxeto($t, $id_idioma);
    }
  }

  public function operacion(EstadoHTTP $e) {        
    $a_tidioma = $this->obxetos("tidioma");

    if (count($a_tidioma) < 2) return null;
    
    foreach ($a_tidioma as $tidioma) {
      if (!$tidioma->control_evento()) continue;

      list($x, $id_idioma) = explode(Escritor_html::csubnome, $tidioma->nome());

      return $this->operacion_redirect($e, $id_idioma);
    }

    return null;
  }
}


//**********************************************************


final class CIdioma_i extends CIdioma_t {

  public function __construct($id_site, $id_idioma, $editor) {
    parent::__construct($id_site, $id_idioma, $editor);
  }

  protected function inicia_obxetos( $a_idiomas ) {
    if (count($a_idiomas) == 0) return;

    foreach ($a_idiomas as $id_idioma=>$nome_idioma) {
      $t = new Image("tidioma", Refs::url("imx/bandeiras/{$id_idioma}.png"), false);

      $t->title = $nome_idioma;

      $t->clase_css("default", "cidioma_imx");
      $t->clase_css("readonly", "cidioma_imx_sl");

      if ($this->editor)
        $t->pon_eventos("onclick", "editor_enviaEvento(this, true)");
      else
        $t->envia_submit("onclick");

      $this->pon_obxeto($t, $id_idioma);
    }
  }
}


//**********************************************************


final class CIdioma_s extends CIdioma {

  public function __construct($id_site, $id_idioma, $editor) {
    parent::__construct($id_site, $id_idioma, $editor);
  }

  protected function inicia_obxetos( $a_idiomas ) {
    $s = new Select("sidioma", $a_idiomas);

    $s->clase_css("default", "cidioma_input");


    if ($this->editor)
      $s->pon_eventos("onchange", "editor_enviaEvento(this, true)");
    else
      $s->envia_submit("onchange");
      

    $this->pon_obxeto($s);
  }

  public function id_idioma($id_idioma = null) {
    if ($id_idioma == null) return $this->id_idioma_select;

    $this->obxeto("sidioma")->post($id_idioma);

    $this->id_idioma_select = $id_idioma;
  }

  public function html_2() {
    return $this->obxeto("sidioma")->html();
  }

  public function operacion(EstadoHTTP $e) {
    if (($sidioma = $this->obxeto("sidioma")) == null) return;

    if (!$sidioma->control_evento()) return null;


    return $this->operacion_redirect($e, $sidioma->valor());
  }
}

