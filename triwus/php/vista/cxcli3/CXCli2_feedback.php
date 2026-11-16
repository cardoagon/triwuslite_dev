<?php

final class CXCli2_feedback extends Componente
                         implements IPanel_cxcli2 {

  private $id_site;
  private $nomsite;

  private $email_a;
  private $email_u;

  private $legais;

  public function __construct() {
    parent::__construct("cfeedback", Refs::url("ptw/clogin/web/cxcli2_feedback.html"));

    $this->pon_obxeto(new Textarea("tfeedback", 2, 33));

    $this->obxeto("tfeedback")->style("default", "width: 99%; height: 5em;padding: 0.5em 1em;font-family:inherit;box-sizing:border-box;resize:vertical;");
  }

  public function id() { //* IMPLEMENTS IPanel_cxcli2.id();
    return $this->nome;
  }

  public function titulo() { //* IMPLEMENTS IPanel_cxcli2.titulo();
    return Msx::$cxcli[$this->pai->idioma->codigo]["feed"]["t"];
  }

  public function notas() {  //* IMPLEMENTS IPanel_cxcli2.notas();
    return Msx::$cxcli[$this->pai->idioma->codigo]["feed"]["n"];
  }

  public function post_u(FGS_usuario $u = null) { //* IMPLEMENTS IPanel_cxcli2.post_u();
    //* gardamos informacion util
    $cbd = new FS_cbd();

    $s = Site_obd::inicia($cbd,  $u->atr("id_site")->valor);

    $this->id_site = $u->atr("id_site")->valor;
    $this->email_u = $u->atr("login")->valor;
    $this->email_a = $s->usuario_obd($cbd)->atr("login")->valor;

    $this->nomsite = $s->atr("nome")->valor;

    //* cambimos a configuración do boton bAceptar

    $idioma = $this->pai->idioma->codigo;
    $this->obxeto("btnr")->pon_obxeto(Panel_fs::__baceptar(Msx::$cxcli[$idioma]["feed"]['btn'],Msx::$cxcli[$idioma]["feed"]["btn_title"]));

    $this->obxeto("btnr")->obxeto("bAceptar")->envia_submit("onclick", "", "cxcli2_buscador_bfeedback");
  }

  public function tipo_btnr() {  //* IMPLEMENTS IPanel_cxcli2.tipo_btnr();
    return 2;
  }

  public function operacion_bCancelar(IEfspax_xestor $e) {} //* IMPLEMENTS IPanel_cxcli2.operacion_bCancelar();

  public function operacion_bAceptar(IEfspax_xestor $e) { //* IMPLEMENTS IPanel_cxcli2.operacion_bAceptar();
    $texto  = strip_tags(trim( $this->obxeto("tfeedback")->valor()));

    if ($texto == null) {
      $e->post_msx( Msx::$cxcli[$this->pai->idioma->codigo]["feed"]["err"]);
        return $e;
    }

    $ticket = Elmt_contacto_obd::__colle_ticket($this->id_site); //* site arestora prospecta.

    $this->enviar_email($ticket, $texto);

    $this->gardar_csv($ticket, $texto);


    //* actualiza formulario.
    $this->obxeto("tfeedback")->post("");


    $e->post_msx( Msx::$cxcli[$this->pai->idioma->codigo]["feed"]["r"], "m");

    return $e;
  }

  private function enviar_email($ticket, $texto) { //* IMPLEMENTS IPanel_cxcli2.operacion_bAceptar();
   // $m = new XMail2(false);
    $m = new XMail3($this->id_site);

    (($_x = $m->_config()) != [])
    ? $m->pon_rmtnt($_x['email'])
    : $m->pon_rmtnt($this->email_a);

    //~ $src_legais = Refs::url( Refs::url_sites . "/" . $this->nomsite . "/" . Refs::url_legais . "/");
    $src_legais = Refs::url( Refs::url_legais . "/");
    $txt_legais = "";

    if(is_file("{$src_legais}lopd_email.html")) {
      $this->legais = "{$src_legais}lopd_email.html";
       $txt_legais = file_get_contents("{$src_legais}lopd_email.html");
    }

    $labelUsuario = Msx::$cxcli[$this->pai->idioma->codigo]["feed"]["u"];

    $texto = "<div>
                <h4><strong>".$labelUsuario."</strong></h4>
                <div>". $this->email_u ."</div>
                <h4><strong>Feedback</strong></h4>
                <p><pre>". $texto ."</pre></p>
                <br><br>
                <p><small>". $txt_legais ."</small></p>
              </div>"
    ;

    $m->pon_asunto   ("consulta: #id = {$ticket}");

    //* copia usuario
    $m->pon_direccion($this->email_u);
    $m->pon_cco      ($this->email_a);
    $m->pon_corpo    ($texto);

    $m->enviar();

  }


/* fran */

  private function gardar_csv($id_ticket, $feedback) {

    $src = self::rutaCarpeta($this->id_site);
    $src .= "Feedbacks.csv";

    if (!is_file($src)) {
      $cab = "\"Id\";\"Fecha\";\"Usuario\";\"Feedback\"\r\n";

      file_put_contents($src, $cab);
    }

    $fecha = date('Y-m-d H:i:s');

    $txtSaneado = str_replace('"',"'",$feedback);

    $rexistro = "\"{$id_ticket}\";\"{$fecha}\";\"{$this->email_u}\";\"{$txtSaneado}\"\r\n";

    file_put_contents($src, $rexistro,  FILE_APPEND | LOCK_EX);
  }


  private static function rutaCarpeta($site, $id_ticket = null) {

    $src = TrwlConfig::pwd . "/" . Config::priv . "/" . $site. "/";

    $src .= "Feedbacks/";

    if(!is_dir($src)) mkdir($src);

    return $src;
  }

}

