<?php


class Elmt_contacto extends AElemento {
  const ptw_0 = "ptw/paragrafo/elmt/elmt_contacto.html";

  public $id_site;
  public $id_idioma;

  public $a_asunto        = null;
  public $de              = null;
  public $paso            = null;
  public $uemail          = null;
  
  public bool $de_editable = false;
                          
  public $ticket          = 0;
                          
  public $a_campos        = null;
  public $a_anexos        = [];

  private $id_ticket      = null;
  private $ptw_lopd_email = null;

  private $spam_0         = null;
  private $spam_1         = null;

  private $tituloPaxina   = "";

  private $adxunto_MB_Max = 2;

  public function __construct(Elmt_contacto_obd $pe_obd) {
    parent::__construct( $pe_obd, Refs::url(self::ptw_0) );

    $this->de          = $pe_obd->atr("de"  )->valor;
    $this->paso        = $pe_obd->atr("paso")->valor;
    $this->a_asunto    = $pe_obd->asunto();
    $this->a_campos    = $pe_obd->campos();

    $this->ticket      = $pe_obd->atr("ticket"     )->valor;


    $this->pon_obxeto(new Text("tPrimeiro"));
    $this->pon_obxeto(new Text("tSegundo" ));
    
    $this->pon_obxeto(new Div("legal", $pe_obd->atr("legal")->valor));

    $this->pon_obxeto(new Div("msx"));

    $this->pon_obxeto(new Param("legais"));

    $this->pon_obxeto(new Checkbox("chenvia", false, ""));

    $this->pon_obxeto(new Button("bAceptar", "Aceptar")); //* Aceptar mensaxes, (ptw_2)

    $this->reset();

    $this->obxeto("bAceptar")->clase_css("default", "elmt_contacto_boton");

    $this->obxeto("tPrimeiro")->pon_etq("Primeiro", 1);
    $this->obxeto("tSegundo" )->pon_etq("Segundo" , 1);
  }

  public function config_usuario(IEfspax_xestor $e = null) {
    parent::config_usuario($e);

    //* id_idioma da paxina
        if (($this->id_idioma = $e->id_idioma()) == null)                   $this->id_idioma = "es";
    elseif (!isset(Msx::$elmt_contacto_campos['c_nome'][$this->id_idioma])) $this->id_idioma = "es";

   //Gardamos titulo de paxina.
    $this->tituloPaxina = $this->tituloPaxina($e->id_paxina());


    $this->pon_obxeto(new Checkbox("chenvia", false, Msx::$elmt_contacto_chenvia[$this->id_idioma]));

    $this->obxeto("bAceptar")->post(Msx::$_aceptar[$this->id_idioma]);


    //* benvia
    $b = new Button("benvia", Msx::$elmt_contacto_enviar[$this->id_idioma]);

    $b->clase_css("default", "elmt_contacto_boton");

    $b->envia_ajax("onclick");
    //~ $b->envia_submit("onclick");

    $this->pon_obxeto($b);


    //* gardamos id_site
    $s = $e->__site_obd();

    $this->id_site = $s->atr("id_site")->valor;

    //* configura remitente y asunto (XMail3, sin cliente SMTP)
    $_configSMTP = Site_config_obd::_cliemail($this->id_site);

    if ($_configSMTP != []) {
      $this->de = $_configSMTP['email'];
      
      $this->de_editable = false;
    }
    else {
      $this->uemail = $s->usuario_obd()->atr("email")->valor;
      
      $this->de_editable = true;
      
      if ($this->de == null) $this->de = "no-reply.{$this->uemail}";
    }

    //* configura legais
    $this->ptw_lopd_email = null;

    //** apendice email
    //~ $src_legais = Refs::url( Refs::url_sites . "/" . $s->atr("nome")->valor . "/" . Refs::url_legais . "/" );
    $src_legais = Refs::url( Refs::url_legais . "/" );

    if (is_file("{$src_legais}lopd_email.html")) {
      $this->ptw_lopd_email = "{$src_legais}lopd_email.html";

      $this->obxeto("legais")->post( file_get_contents($this->ptw_lopd_email) );
    }
  }

  public function elmt_obd() {
    $ec = new Elmt_contacto_obd();
    
    $ec->atr("de"         )->valor = $this->de;
    $ec->atr("paso"       )->valor = $this->paso;
    $ec->atr("ticket"     )->valor = ($this->ticket == 1)?"1":"0";
    $ec->atr("legal"      )->valor = trim( $this->obxeto("legal")->valor() );

    $ec->asunto($this->a_asunto);
    $ec->campos($this->a_campos);
    

    return $this->obd_base($ec);
  }

  public function editor() {
    return new Editor_econtacto($this);
  }

  public function operacion(EstadoHTTP $e) {
    foreach ($this->a_campos as $i=>$_c) {
      if ($_c->tipo != "adx") continue;
      
      if ($this->obxeto("campo", $i)->control_evento()) return $this->operacion_cadxunto($e, $i);
    }

    if ($this->obxeto("bAceptar")->control_evento()) {
      $this->preparar_saida($e->ajax());

      return $e;
    }

    if ($this->obxeto("benvia")->control_evento()) return $this->operacion_benvia($e);


    return parent::operacion($e);
  }

  public function html_propio() {
    $html_asunto = $this->html_asunto();
    $html_campos = ""; foreach ($this->a_campos as $i=>$_c) $html_campos .= $this->html_campo($i, $_c);


    $html = $this->html00();
    
    $html = str_replace("[asunto]"         , $html_asunto, $html);
    $html = str_replace("[campos_contacto]", $html_campos, $html);
    
    
    return $html;
  }

  public function reset() {
    $this->a_anexos = [];
    
    foreach ($this->a_campos as $i=>$_c) {
          if ($_c->tipo == "dat") $this->pon_obxeto( $this->__dat(), $i );
      elseif ($_c->tipo == "dni") $this->pon_obxeto( $this->__txt(), $i );
      elseif ($_c->tipo == "ema") $this->pon_obxeto( $this->__txt(), $i );
      elseif ($_c->tipo == "ban") $this->pon_obxeto( $this->__txt(), $i );
      elseif ($_c->tipo == "txt") $this->pon_obxeto( $this->__txt(), $i );
      elseif ($_c->tipo == "atx") $this->pon_obxeto( $this->__atx(), $i );
      elseif ($_c->tipo == "adx") $this->pon_obxeto( $this->__adx(), $i );
    }
    
    $this->pon_obxeto(self::__sasunto($this->a_asunto)); //* c_asunto

    $this->spam_1 = strtotime("now");
    $this->spam_0 = substr(md5($this->spam_1), 0, 7);

    $this->obxeto("tPrimeiro")->post("");
    $this->obxeto("tSegundo" )->post($this->spam_0);
  }

  public static function _etq($etq_0, $id_idioma) {
    if ( isset( Msx::$elmt_contacto_campos[$etq_0][$id_idioma] ) ) return Msx::$elmt_contacto_campos[$etq_0][$id_idioma];
    
    return $etq_0;
  }

  public static function __sasunto($a_asunto) {
    $a = null;
    $i = 0;

    if (count($a_asunto) > 1) $a['**'] = "...";

    foreach($a_asunto as $asunto=>$email) $a[++$i] = $asunto;


    asort($a);

    $s = new Select("c_asunto", $a);

    $s->clase_css("default", "elmt_contacto_select");


    return $s;
  }


  private function html_asunto():string {
    if ( !is_array($this->a_asunto)  ) return "";
    if ( count($this->a_asunto) == 1 ) return "";
    
    
    $etq = self::_etq("c_asunto", $this->id_idioma);

    return "<div class='elmt_contacto_fila'><label>{$etq}&nbsp;(*)</label><br/>" . $this->obxeto("c_asunto")->html() . "</div>";
  }

  private function html_campo($i, object $_c):string {
    if (($o = $this->obxeto("campo", $i)) == null) return "";
    
    $etq = self::_etq($_c->etq, $this->id_idioma);
    $ast = ($_c->ob != "1")?"":"&nbsp;(*)";
    
    if (isset($_c->axuda))
      $axu = ($_c->axuda == null)?"":"<div>{$_c->axuda}</div>";
    else
      $axu = "";
    

    if ($_c->tipo == "adx") return $this->html_c_adxunto($o, $etq, $ast, $axu);

    $o->pon_etq("{$etq}{$ast}", 1);

    return "<div class='elmt_contacto_fila'>" . $o->html() . "{$axu}</div>";
  }

  private function html_c_adxunto(File $f, $k, $ast, $axu) {
    return "<div class='elmt_contacto_fila'><label>{$k}{$ast}</label><br/>" . $f->html() . "{$axu}</div>";
  }

  private function operacion_cadxunto(EstadoHTTP $e, int $i) {
    $this->a_anexos[$i] = $this->obxeto("campo", $i)->post_f();

    $e->ajax()->pon_ok();


    return $e;
  }

  private function operacion_benvia(Efs $e) {
    if ( !$this->validar_spam($e) ) {
      $this->reset();

      $this->preparar_saida($e->ajax());

      return $e;
    }

    if ( ($erro = $this->validar()) != null ) {
      $e->post_msx($erro);

      return $e;
    }

    $this->id_ticket = Elmt_contacto_obd::__colle_ticket($this->id_site);


    try {
      $this->email();
    }
    catch (Exception $exc) {
      $e->post_msx( $exc->getMessage() );
      
      return $e;
    }

    $this->gardar_csv  ($this->id_ticket);
    $this->garda_anexos($this->id_ticket);
    

    $this->reset();

    $this->preparar_saida($e->ajax());

    $e->post_msx(Msx::$elmt_contacto[1][$this->id_idioma], "m");
    
    
    return $e;
  }

  private function email() {
    $txt = "<br />" . $this->email_ticket() . $this->email_texto();

    try {
      $m = new XMail3($this->id_site, $this->id_idioma);
      
      $para = $this->email_para(); 

      $m->pon_rmtnt    ($this->de);
      $m->pon_direccion($para    );
      
      $this->pon_cc($m, $para);

      foreach ($this->a_campos as $i=>$_c) {
        if ($_c->tipo != "ema") continue;
        
        $c_m = trim( $this->obxeto("campo", $i)->valor() );
        
        if ($c_m == "") continue; //* ($c_m != "") => xa foi validado.
        
        $m->pon_cc( $c_m );
      }
      
      $m->pon_asunto($this->email_asunto());
      $m->pon_corpo ($txt);
    
      foreach ($this->a_anexos as $f) {
        if ( !is_file($f) ) continue;
        
        $m->pon_adxunto($f);
      }
 
      $m->enviar();
    }
    catch (Exception $e) {
      throw $e;
    }
    

  //~ echo $m->html();
  }

  private function email_ticket() {
    if ($this->ticket != 1) return "";

    return "#{$this->id_ticket} - <small><b>" . Msx::$elmt_contacto["2"][$this->id_idioma] . "</b></small> - <br /><br />";
  }

  private function email_para() {
    if (($i_asunto = $this->obxeto("c_asunto")->valor()) == null) $i_asunto = 1;


    $i = 1;
    foreach ($this->a_asunto as $asunto=>$email) {
      if ($i == $i_asunto) return $email;

      $i++;
    }

    return null;
  }

  private function email_asunto() {
    if (($i_asunto = $this->obxeto("c_asunto")->valor()) == null) $i_asunto = 1;

    $i = 1;
    foreach ($this->a_asunto as $asunto=>$email) {
      if ($i == $i_asunto) {
        if ($this->ticket != 1) return $asunto;

        return "#{$this->id_ticket} - {$asunto}";
      }

      $i++;
    }

    return null;
  }

  private function email_texto() {
    $t =  self::_etq("c_asunto", $this->id_idioma). ": " . $this->email_asunto() . "<br>";

    foreach ($this->a_campos as $i=>$_c) {
      if ($_c->tipo == "adx") continue;

      $o   = $this->obxeto("campo", $i);
      $etq = self::_etq($_c->etq, $this->id_idioma);

      if ($_c->tipo == "atx") {
        $atx_valor = str_replace("\n", "<br />", $o->valor());
        
        $t .= "<b>{$etq}</b>: <br /><p>{$atx_valor}</p><br />";
      }
      else
        $t .= "<b>{$etq}</b>: " . $o->valor();


      $t .= "<br />\n\r";
    }

    if ($this->ptw_lopd_email == null) return $t;


    return "{$t}<br /><hr /><small>" . $this->obxeto("legais")->valor() . "</small>";
  }

  private function pon_cc(XMail3 $m, ?string $para):void {
    //* Precond:: ($para != null) => $para es un email válido.
    //* Precond:: $this->de es un email válido.
    
    if ($para == null) return;
    
    $_dp = explode("@", $para    );
    $_dd = explode("@", $this->de);
    
    if ($_dp[1] == $_dd[1]) return;
    
    $m->pon_cc($this->de);
  }

  private function validar() {
    $erro = null;


    if ( !$this->validar_asunto() ) $erro .= Erro::$elmt_contacto_erro[3][$this->id_idioma] . ".<br />";
    

    foreach ($this->a_campos as $i => $_c) {
      $etq = self::_etq($_c->etq, $this->id_idioma);
      
      $o = $this->obxeto("campo", $i);

      switch ($_c->tipo) {
        case "ema":
          $m = trim($o->valor());
          
          if ( ($_c->ob != 1) && ($m == "") ) break;
          
          if ( !Valida::email($m) ) $erro .= Erro::$elmt_contacto_erro[2][$this->id_idioma] . "<b>{{$etq}}</b><br />";

          break;

        case "adx":
          if ( $_c->ob != 1 ) break;

          if ($this->a_anexos[$i] == null) $erro .= "{$etq}: " . Erro::$upload[6][$this->id_idioma] . "<b>{{$etq}}</b>.<br />";

          break;

        case "atx":
        case "dat":
        case "txt":
          if ( $_c->ob != 1 ) break;

          $v = trim($o->valor());

          if ( $v == "" ) $erro .= "{$etq}: " . Erro::$elmt_contacto_erro[4][$this->id_idioma] . "<b>{{$etq}}</b>.<br />";

          $o->post($v);

          break;

        case "dni":
          $v = trim($o->valor());

          if ( ($_c->ob != 1) && ($v == "") ) break;

          if ( Erro::valida_nif_cif_nie($v) < 1 ) $erro .= "{$etq}: " . Erro::$elmt_contacto_erro[5][$this->id_idioma] ."<b>{{$etq}}</b>.<br />";

          $o->post($v);

          break;

        case "ban":
          $v = trim($o->valor());

          if ( ($_c->ob != 1) && ($v == "") ) break;

          if ( Valida::iban($v) != null ) $erro .= "{$etq}: " . Erro::$elmt_contacto_erro[7][$this->id_idioma] ."<b>{{$etq}}</b>.<br />";

          $o->post($v);

          break;
      }
    }
    
    if ($erro != null) return Erro::$elmt_contacto_erro[1][$this->id_idioma] . "<br /><br />{$erro}";



    if ( !$this->obxeto("chenvia")->valor() ) return Erro::$elmt_contacto_erro[6][$this->id_idioma] .".<br />";


    return null;
  }

  private function validar_asunto() {
    return $this->obxeto("c_asunto")->valor() != "**";
  }

  private function validar_spam(Efs $e) {
    if (!$e->evento()->ajax()) return false;

    if ((time() - $this->spam_1) < 4) return false;
    
    if (($v = trim( $this->obxeto("tPrimeiro")->valor() )) != ""           ) return false;
    if (($v = trim( $this->obxeto("tSegundo" )->valor() )) != $this->spam_0) return false;

    return true;
  }

  private function __dat() {
    $o = Panel_fs::__dataInput("campo");

    return $o;
  }

  private function __txt() {
    $o = Panel_fs::__text("campo", 20, 99, " ");

    return $o;
  }

  private function __adx() {
    $max = $this->adxunto_MB_Max;

    $tmp = Refs::url_tmp2();

    $mfs = 1024 * 1024 * $max;

    $f = new File("campo", $tmp, $mfs);

    $f->style("default", "max-width: 100%;");

    return $f;
  }

  private function __atx() {
    $ta = Panel_fs::__textarea("campo");

    $ta->clase_css("default", "elmt_contacto_textarea");

    return $ta;
  }


  /* fran */
  private function tituloPaxina($idPaxina) {
    $sql = "select titulo from v_site_paxina vsp  where id_paxina = {$idPaxina}";

    $cbd = new FS_cbd();
    $r = $cbd->consulta($sql);
    if (!$a = $r->next()) return null;

    return ucwords($a['titulo']);
  }

  private function gardar_csv($id_ticket) {
    $fecha = date('Y-m-d H:i:s');
    //~ $remitente = $this->obxeto("c_email")->valor();
    $destinatario = $this->email_para();
    $asunto = str_replace('"',"'",$this->email_asunto());
    $idioma = $this->id_idioma;
    $seccion = $this->tituloPaxina;
    $anexo = strval( count($this->a_anexos) );

    $rexistro = "\"{$id_ticket}\";\"{$fecha}\";\"{$idioma}\";\"{$seccion}\";\"{$anexo}\";\"{$destinatario}\";\"{$asunto}\"";

    //src carpeta privada
    $src = self::rutaCarpeta($this->id_site) . "registros_formularios_contacto.csv";

    $msx = "";
    foreach ($this->a_campos as $i => $_c) {
      if ($_c->tipo == "adx" ) continue;
      
      $etq = self::_etq($_c->etq, $this->id_idioma);
      
      $msx .= "{$etq}:\n" . strip_tags(str_replace('"',"'",$this->obxeto("campo", $i)->valor())) . "\n";
    }

    $rexistro .= ";\"{$msx}\"\r\n";

    if (!is_file($src)) {
      $cab_csv  = "\"Id\";\"Fecha\";\"Idioma\";\"Sección\";\"Anexos\";\"Destinatario\";\"Asunto\";\"Mensaje\"\r\n";
      
      file_put_contents($src, $cab_csv);
    }

    file_put_contents($src, $rexistro,  FILE_APPEND | LOCK_EX);
  }

  private function garda_anexos($idTicket) {
    foreach ($this->a_anexos as $i => $f_0) {
      if ( !is_file($f_0) ) continue;
      
      $src = self::rutaCarpeta($this->id_site) . "Anexos/";
      
      if(!is_dir($src)) mkdir($src);
      
      link($f_0, "{$src}{$idTicket}--{$i}");
      
      //~ $this->obxeto("campos", $i)->aceptar_f($src, "{$idTicket}--{$i}", 1);
    }
  }

  private static function rutaCarpeta(int $site):string {
    $src = TrwlConfig::pwd . "/" . TrwlConfig::priv . "/" . $site. "/Registros_formulario_contacto-v2/";
    
    if(!is_dir($src)) mkdir($src);

    $src .= date("Y/");
    
    if(!is_dir($src)) mkdir($src);
    

    return $src;
  }
}

//*******************************

final class Editor_econtacto extends Edita_elmt {
  const ptw_0 = "ptw/paragrafo/elmt/editor_econtacto.html";
  const ptw_1 = "ptw/paragrafo/elmt/editor_econtacto_1.html";
  const ptw_2 = "ptw/paragrafo/elmt/editor_econtacto_2.html";

  private $lmaisAsunto_i  = null;
  private $lmaisCampos_i  = null;


  public function __construct(Elmt_contacto $elmt) {
    parent::__construct(null, "Formulario de contacto", self::ptw_0, $elmt);

    //~ $this->pon_obxeto(new Span("erro"));

    $this->pon_obxeto(new EEC_lasunto($elmt->a_asunto));

    $this->pon_obxeto(new EEC_lcampos($elmt->a_campos));

    $this->pon_obxeto( new Checkbox("chticket", $elmt->ticket == 1, "Añade al mensaje un identificador único de consulta.") );

    $this->pon_obxeto(Panel_fs::__text("de"));

    $this->pon_obxeto(new Textarea("legal"));

    $this->pon_obxeto(Panel_fs::__password("tpaso", 20, 99, " ... contraseña"));

    $this->pon_obxeto(Panel_fs::__text("tetq_c"));
    $this->pon_obxeto(new Select("stipo_c", Elmt_contacto_obd::$_campo_tipo ));
    $this->pon_obxeto(new Select("sob_c"  , ["1"=>"Sí", "0"=>"No"]          ));
    $this->pon_obxeto(Panel_fs::__text("taxuda_c"));

    $this->pon_obxeto(Panel_fs::__text("tasunto"));
    $this->pon_obxeto(Panel_fs::__text("temail", null, null, $elmt->uemail));

    $this->pon_obxeto( self::__laux("lmaisAsunto"   , "+ Añadir asunto"  , "Añadir un nuevo asunto al formulario de contacto") );
    $this->pon_obxeto( self::__laux("lmaisCampos"   , "+ Añadir Campo"   , "Añadir un nuevo campo al formulario de contacto" ) );


    $this->obxeto("de")->post($elmt->de);
    $this->obxeto("de")->readonly = !$elmt->de_editable;

    $this->obxeto("legal")->post($elmt->obxeto("legal")->valor());

    $this->obxeto("tpaso")->post($elmt->paso);
    $this->obxeto("tpaso")->visible = true; //true para que no dependa de activación del checkbox.
  }

  public function operacion(EstadoHTTP $e) {
    //~ $this->obxeto("erro")->post(null);

    if (($e_aux = $this->obxeto("lasunto")->operacion($e)) != null) return $e_aux;
    if (($e_aux = $this->obxeto("lcampos")->operacion($e)) != null) return $e_aux;

    if ($this->obxeto("lmaisAsunto")->control_evento()) return $this->operacion_lmaisAsunto($e);
    if ($this->obxeto("lmaisCampos")->control_evento()) return $this->operacion_lmaisCampos($e);


    return parent::operacion($e);
  }

  public function operacion_lmaisAsunto(Epaxina_edit $e, $i = -1) {
    $this->lmaisAsunto_i = $i;
  
    $_asunto = $this->obxeto("lasunto")->colle_asunto($i);

    $this->obxeto("temail" )->post( $_asunto[0] );
    $this->obxeto("tasunto")->post( $_asunto[1] );
  
  
    $this->ptw     = self::ptw_1;

    $this->pai->preparar_saida($e->ajax());

    return $e;
  }

  public function operacion_lmaisCampos(Epaxina_edit $e, $i = -1) {
    $this->lmaisCampos_i = $i;
  
    $_c = $this->obxeto("lcampos")->colle_campo($i);

    $this->obxeto("tetq_c"  )->post( $_c->etq   );
    $this->obxeto("stipo_c" )->post( $_c->tipo  );
    $this->obxeto("sob_c"   )->post( $_c->ob    );
    $this->obxeto("taxuda_c")->post( $_c->axuda );
  
    $this->ptw     = self::ptw_2;

    $this->pai->preparar_saida($e->ajax());

    return $e;
  }

  public function operacion_cancelar(Epaxina_edit $e) {
    if ($this->ptw != self::ptw_0) {
      $this->ptw = self::ptw_0;

      $this->pai->preparar_saida($e->ajax());

      return $e;
    }

    return parent::operacion_cancelar($e);
  }

  public function operacion_aceptar(Efs_admin $e) {
    if ($this->ptw == self::ptw_1) return $this->operacion_aceptar_1($e);
    if ($this->ptw == self::ptw_2) return $this->operacion_aceptar_2($e);

    //* actualiza elmt_contacto
    $this->elmt->de       = $this->obxeto("de"     )->valor();
    $this->elmt->paso     = $this->obxeto("tpaso"  )->valor();
    $this->elmt->a_asunto = $this->obxeto("lasunto")->colle_asunto();
    $this->elmt->a_campos = $this->obxeto("lcampos")->_campos();
    $this->elmt->ticket   = "1";

    $this->elmt->obxeto("legal")->post( $this->obxeto("legal")->valor() );

    $this->elmt->reset();


    return parent::operacion_aceptar($e);
  }

  private function operacion_aceptar_1(Efs_admin $e) {
    $asunto = trim($this->obxeto("tasunto")->valor());
    $email  = trim($this->obxeto("temail")->valor());

    if (($erro = $this->validar_asunto($asunto, $email)) != null) {
      $e->post_msx($erro);
      //~ $this->obxeto("erro")->post($erro);

      //~ $this->pai->preparar_saida($e->ajax());;

      return null; //* Indica a Btnr_detalle_oeste q debe mantener abierto el editor
    }

    if ($email == null) $email = $this->elmt->uemail;

    $this->obxeto("lasunto")->pon_asunto($email, $asunto, $this->lmaisAsunto_i);
    
    $this->lmaisAsunto_i = -1;

    $this->obxeto("tasunto")->post(null);
    $this->obxeto("temail" )->post(null);


    $this->ptw = self::ptw_0;

    $this->pai->preparar_saida($e->ajax());

    return null; //* Indica a Btnr_detalle_oeste q debe mantener abierto el editor
  }

  private function operacion_aceptar_2(Efs_admin $e) {
    $etq  = trim($this->obxeto("tetq_c")->valor());
    $tipo = $this->obxeto("stipo_c")->valor();
    $ob   = $this->obxeto("sob_c"  )->valor();
    $axu  = trim($this->obxeto("taxuda_c")->valor());

    if (($erro = $this->validar_campo($etq, $tipo, $ob)) != null) {
      $e->post_msx($erro);
      //~ $this->obxeto("erro")->post($erro);

      //~ $this->pai->preparar_saida($e->ajax());

      return null; //* Indica a Btnr_detalle_oeste q debe mantener abierto el editor
    }

    $this->obxeto("lcampos")->pon_campo($etq, $tipo, $ob, $axu, $this->lmaisCampos_i);
    
    $this->lmaisCampos_i = -1;


    $this->ptw = self::ptw_0;

    $this->pai->preparar_saida($e->ajax());

    return null; //* Indica a Btnr_detalle_oeste q debe mantener abierto el editor
  }

  protected function __editor_style() {
    return new Editor_style_nulo();
  }

  private function validar_asunto($asunto, $email) {
    $erro = null;

    if ($asunto == null) $erro = "Error: debes teclear un asunto.<br />";

    if ($email != null) if (!Valida::email($email)) $erro .= Erro::fcontacto_temail . "<br />";

    return $erro;
  }

  private function validar_campo($etq, $tipo, $ob) {
    $erro = null;

    if ($etq == null) $erro = "Error: debes teclear una etiqueta.<br />";


    return $erro;
  }

  private static function __laux($k, $t, $m) {
    $d = new Span($k, $t);


    $d->title = $m;
    
    
    $d->clase_css("default","trw-btn");
    
    //~ $d->envia_submit("onclick");
    $d->envia_ajax("onclick");


    return $d;
  }
}

//*******************************

final class EEC_lasunto extends FS_lista implements Iterador_bd {
  public $a = null;
  public $i = 0;

  public function __construct($a_asunto) {
    parent::__construct("lasunto", new EEClasunto_ehtml());

    $i = 0;
    foreach ($a_asunto as $asunto=>$email) $this->a[++$i] = array($email, $asunto, $i);
  }

  public function __count() {
    return count($this->a);
  }

  public function descFila() {}

  public function next() {
    $this->i++;

    if ($this->i > $this->__count()) {
      $this->i = 0;

      return null;
    }


    return $this->a[$this->i];
  }

  public function colle_asunto($i = -1) {
    if ($i > -1) return $this->a[$i];
    
    $a = null;

    foreach ($this->a as $a2) $a[$a2[1]] = $a2[0];

    return $a;
  }

  public function pon_asunto($email, $asunto, $i = -1) {
    if ($i == -1) $i = $this->__count() + 1;

    $this->a[$i] = array($email, $asunto, $i);
  }

  public function sup_asunto($i) {
    unset($this->a[$i]);

    $a_aux = $this->a;

    $i       = 0;
    $this->a = null;
    foreach($a_aux as $a) $this->a[++$i] = array($a[0], $a[1], $i);
  }

  public function operacion(EstadoHTTP $e) {
    $evento = $e->evento();

//~ echo $evento->html();

    if ($this->control_fslevento($evento, "borrar")) {
      $this->sup_asunto($evento->subnome(0));

      $this->preparar_saida($e->ajax());

      return $e;
    }

    if ($this->control_fslevento($evento, "leditar")) return $this->pai->operacion_lmaisAsunto($e, $evento->subnome(0));


    return null;
  }

  protected function __iterador() {
    return $this;
  }
}

//---------------------------------

final class EEClasunto_ehtml extends FS_ehtml {
  public function __construct() {
    parent::__construct();

    $this->style_table = "border: 1px #ddd solid;";
    $this->align       = "center";
    $this->width       = "100%";
    $this->cellspacing = 0;
    $this->cellpadding = 0;
    $this->border      = 0;
  }

  protected function cabeceira($df = null) {
    return "<tr>
              <th>Asunto</th>
              <th>Email</th>
              <th width=1px></th>
            </tr>";
  }

  protected function linha_detalle($df, $f) {
    //~ echo "<pre>" . print_r($f, true) . "</pre>";

    $style_tr = FS_ehtml::style_tr;

    return "<tr {$style_tr} >
              <td>" . self::__leditar($this, $f) ."</td>
              <td>{$f[0]}</td>
              <td align=center>" . self::__bborrar($this, $f) ."</td>
            </tr>";
  }

  private static function __leditar(EEClasunto_ehtml $ehtml, $f) {
    if ($ehtml->xestor->readonly      ) return "";
    
    $b = new Div("leditar", $f[1]);
    
    $b->clase_css("default", "lista_elemento_a");

    $b->title = "Editar asunto";


    $b->envia_ajax("onclick");
    //~ $b->envia_submit("onclick");

    return $ehtml->__fslControl($b, $f[2])->html();
  }

  private static function __bborrar(EEClasunto_ehtml $ehtml, $f) {
    if ($ehtml->xestor->__count() == 1) return "";
    if ($ehtml->xestor->readonly      ) return "";
    
    $b = Panel_fs::__bsup("borrar", null);

    $b->envia_ajax("onclick");
    //~ $b->envia_submit("onclick");

    return $ehtml->__fslControl($b, $f[2])->html();
  }
}

//*******************************

final class EEC_lcampos extends FS_lista implements Iterador_bd {
  public $a = null;
  public $i = -1;
  
  private $b = 0; //* borrados.

  public function __construct($a_campos) {
    parent::__construct("lcampos", new EEClcampos_ehtml());

    foreach ($a_campos as $i=>$_c) {
      $this->a[] = [$_c, $i, false];
    }
  }

  public function __count() {
    return count($this->a);
  }

  public function descFila() {}

  public function next() {
    $this->i++;

    if ($this->i >= $this->__count()) {
      $this->i = -1;

      return null;
    }


    return $this->a[$this->i];
  }

  public function _campos():array {
    $_a = [];
    
    foreach ($this->a as $i => $_c) {
      if ($_c[2]) continue;
      
      $_a[] = $_c[0];
    }
//~ print_r($_a);
    return $_a;
  }

  public function colle_campo($i = -1):object {
    if ($i > -1) return $this->a[$i][0];
    

    return (object)Elmt_contacto_obd::$_campo;
  }

  public function pon_campo($etq, $tipo, $ob, $axuda, $i = -1) {
    if ($i == -1) $i = $this->__count();


    $o_c = (object)Elmt_contacto_obd::$_campo;
    
    $o_c->etq   = $etq;
    $o_c->tipo  = $tipo;
    $o_c->ob    = $ob;
    $o_c->axuda = $axuda;
    

    $this->a[$i] = [$o_c, $i, false];
  }

  public function operacion(EstadoHTTP $e) {
    $evento = $e->evento();

//~ echo $evento->html();

    if ($this->control_fslevento($evento, "borrar")) {
//~ echo "<pre>" . print_r($this->a, 1) . "</pre>";
      $this->a[$evento->subnome(0)][2] = true;
      
      $this->b++;

      $this->preparar_saida($e->ajax());

      return $e;
    }

    if ($this->control_fslevento($evento, "leditar")) return $this->pai->operacion_lmaisCampos($e, $evento->subnome(0));


    return null;
  }
  
  public function permite_borrar():bool {
    return ($this->b + 1) < $this->__count();
  }

  protected function __iterador() {
    return $this;
  }
}

//---------------------------------

final class EEClcampos_ehtml extends FS_ehtml {
  public function __construct() {
    parent::__construct();

    $this->style_table = "border: 1px #ddd solid;";
    $this->align       = "center";
    $this->width       = "100%";
    $this->cellspacing = 0;
    $this->cellpadding = 0;
    $this->border      = 0;
  }

  protected function cabeceira($df = null) {
    return "<tr>
              <th>Etiqueta</th>
              <th>Tipo</th>
              <th>Obligatorio</th>
              <th>Axuda</th>
              <th width=1px></th>
            </tr>";
  }

  protected function linha_detalle($df, $f) {
    //~ echo "<pre>" . print_r($f, true) . "</pre>";
    
    if ($f[2]) return ""; //* campo borrado.
    

    $style_tr = FS_ehtml::style_tr;
    
    $tipo     = Elmt_contacto_obd::$_campo_tipo[ $f[0]->tipo ];
    $ob       = ($f[0]->ob == 1) ? "Sí" : "No";
    
    if (isset($f[0]->axuda))
      $axu = (strlen(strval($f[0]->axuda)) > 0) ? "Sí" : "No";
    else
      $axu = "No";

    return "<tr {$style_tr} >
              <td>" . self::__leditar($this, $f) ."</td>
              <td>{$tipo}</td>
              <td>{$ob}</td>
              <td>{$axu}</td>
              <td align=center>" . self::__bborrar($this, $f) ."</td>
            </tr>";
  }

  private static function __leditar(EEClcampos_ehtml $ehtml, $f) {
    if ($ehtml->xestor->readonly      ) return "";
    
    $b = new Div("leditar", $f[0]->etq);
    
    $b->clase_css("default", "lista_elemento_a");

    $b->title = "Editar campo";


    $b->envia_ajax("onclick");
    //~ $b->envia_submit("onclick");

    return $ehtml->__fslControl($b, strval($f[1]))->html();
  }

  private static function __bborrar(EEClcampos_ehtml $ehtml, $f) {
    if (  $ehtml->xestor->readonly         ) return "";
    if ( !$ehtml->xestor->permite_borrar() ) return "";
    
    $b = Panel_fs::__bsup("borrar", null);

    $b->envia_ajax("onclick");
    //~ $b->envia_submit("onclick");

    return $ehtml->__fslControl( $b, strval($f[1]) )->html();
  }
}
