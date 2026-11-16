<?php
final class CValidar_Conta extends Componente {
  //~ const ptw_0 = "ptw/paneis/pcontrol/usuario/cvalidar_conta.html";
  const ptw_1 = "ptw/paneis/pcontrol/usuario/cvalidar_conta_1.html";
  const ptw_2 = "ptw/paneis/pcontrol/usuario/cvalidar_conta_2.html";
  const ptw_3 = "ptw/paneis/pcontrol/usuario/email-invitacion-confirmar.html";
  const ptw_4 = "ptw/paneis/pcontrol/usuario/cvalidar_conta_3.html";

  public $sp       = null;
  public $redirect = null;

  public function __construct($id_idioma) {
    parent::__construct("cvalidar", Refs::url(self::ptw_2));

    $this->pon_obxeto(new Div("erro", ""));
    $this->pon_obxeto(new Cknovo("cknovo"));
    $this->pon_obxeto(Panel_fs::__baceptar( Msx::$clogin[$id_idioma][10] ));

    $this->cambiar_idioma( Idioma::factory($id_idioma) );

    $this->obxeto("bAceptar")->envia_AJAX("onclick");

    $this->obxeto("erro")->clase_css("default", "texto2_erro");
    $this->obxeto("erro")->style    ("default", "margin: 17px 0;");
  }


  public function operacion(EstadoHTTP $e) {
    if ($this->obxeto("bAceptar")->control_evento()) return $this->operacion_bAceptar($e);

//~ echo $e->evento()->html();

    $_param = $e->evento()->nome(1);
    
//~ echo "<pre>" . print_r($_param, 1) . "</pre>";

    $codigo         = $_param[0];
    $this->redirect = $_param[1];

    if ($codigo == null) {
      $this->ptw = Refs::url(self::ptw_2);
      
      $this->preparar_saida( $e->ajax() );

      return $e;
    }
    
    $this->sp = Cambio_invitaweb_obd::inicia($codigo, new FS_cbd());
    
//~ echo "<pre>" . print_r($this->sp->a_resumo(), 1) . "</pre>";
    
    $this->ptw = ($this->sp != null )?Refs::url(self::ptw_1):Refs::url(self::ptw_2);
    

    $this->preparar_saida( $e->ajax() );

    
    return $e;
  }

  private function operacion_bAceptar(EstadoHTTP $e) {
    if(($passwd_erro = $this->obxeto("cknovo")->validar()) != null) {
      $e->post_msx($passwd_erro);
      
      return $e;
    }
    
    try {
      $cbd = new FS_cbd();
      
      $this->gardar_bd_usuario($cbd, $e);
      
      $this->enviar_correo($cbd, $e, $this->sp->atr("email")->valor);

      $u_aux = $this->sp->usuario_obd();
      $u_aux = Pedido_obd::asignar_permisos($u_aux, $cbd);

              
      $e->usuario( $u_aux );
      
      $r = ($this->redirect == null) ? "perfil.php" : $this->redirect;
      
      $e->ajax()->pon_ok( true, "window.history.replaceState(null, '', '{$r}');document.location = '{$r}';" );

      return $e;
    }
    catch(Exception $exp) {
      $e->post_msx($exp->getMessage());
      
      return $e;
    }    
  }


  public function gardar_bd_usuario(FS_cbd $cbd, EstadoHTTP $e) {

    $k = $this->obxeto("cknovo")->__k();

    $this->sp->post_k($k);


    $cbd->transaccion();

    if (!$this->sp->click($cbd)) {
      
      $cbd->rollback();
      //~ $this->preparar_saida( $e->ajax() );
      
      //~ return $e;
    }

    $cbd->commit();
 }

  public function enviar_correo(FS_cbd $cbd, EstadoHTTP $e, $correo_des) {
    $sp2        = Site_plesk_obd::inicia_s($cbd, $e->id_site);
    
    $dominio    = $sp2->atr("dominio")->valor;

    $html = LectorPlantilla::plantilla(Refs::url(self::ptw_3), $this->idioma);
    $html = str_replace("[legal]", LectorPlantilla::plantilla(Efs::url_legais($e->id_site). "/lopd_email.html"), $html);

   // $mail_inv = new XMail2();
    $mail_inv = new XMail3($e->id_site);

    (($_x = $mail_inv->_config()) != [])
    ? $mail_inv->pon_rmtnt($_x['email'])
    : $mail_inv->pon_rmtnt("no-reply@{$dominio}");

    $mail_inv->pon_asunto(Msx::$clogin[$this->idioma->codigo][115] . " {$dominio}");
    $mail_inv->pon_direccion($correo_des);

    $mail_inv->pon_corpo($html);

    try {
      $mail_inv->enviar();
    }

    catch (Exception $e) {
      throw $e;
    }
    
  }
}
