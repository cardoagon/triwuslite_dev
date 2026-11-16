<?php


final class Cusuario extends Componente implements ICPC {
  private $u = null;

  public function __construct(int $id_site) {
    parent::__construct("cusuario", "ptw/paneis/pcontrol/usuario/usuario.html");
    
    $tcbd = new FS_cbd   (1);
    //~ $xcbd = new Xesta_cbd(1);
    
    $s = Site_obd::inicia($tcbd, $id_site);

    $this->u = $s->admin($tcbd);

    $c       = $this->u->contacto_obd($tcbd, "p");
    
    $this->pon_obxeto( new CPerfilContacto($c, $tcbd) );
    
    $this->pon_obxeto( new CInfoCuenta($this->u->cuenta_obd ($tcbd)) );
    //~ $this->pon_obxeto( new CServizos  ($this->u                    ) );
  }

  public function cpc_miga() {
    return "perfil de usuario";
  }

  public function operacion(EstadoHTTP $e) {
//~ echo $e->evento()->html();

    //~ if (($e_aux = $this->obxeto("catperm"        )->operacion($e)) != null) return $e_aux;
    //~ if (($e_aux = $this->obxeto("cservizos"      )->operacion($e)) != null) return $e_aux;
    if (($e_aux = $this->obxeto("cinfocuenta"    )->operacion($e)) != null) return $e_aux;
    if (($e_aux = $this->obxeto("cperfilcontacto")->operacion($e)) != null) return $e_aux;


    return null;
  }
}

//**********************************************

final class CPerfilContacto extends Componente {
  private ?int $s = null;

  public function __construct(Contacto_obd $o, FS_cbd $cbd) {
    parent::__construct("cperfilcontacto", "ptw/paneis/pcontrol/usuario/cperfilcontacto.html");

    $this->pon_obxeto( self::__ccontacto($o) );

    //~ $this->pon_obxeto( new CFormasPago($o->fpago_obd($cbd)) );

    $this->pon_obxeto( Panel_fs::__baceptar("Guardar", "Guardar contacto") );

    $this->obxeto("bAceptar")->envia_ajax("onclick");
  }

  public function operacion(EstadoHTTP $e) {
//~ echo $e->evento()->html();

    if (($e_aux = $this->obxeto("ccontacto"   )->operacion($e)) != null) return $e_aux;

    //~ if (($e_aux = $this->obxeto("cformas_pago")->operacion($e)) != null) return $e_aux;

    if ($this->obxeto("bAceptar")->control_evento()) return $this->operacion_baceptar($e);


    return null;
  }

  private function operacion_baceptar(Epcontrol $e) {
    $ccontacto = $this->obxeto("ccontacto");

    if (($erro = $ccontacto->validar()) != null) {
      $e->post_msx($erro);

      return $e;
    }
    
    $cbd = new Xesta_cbd();

    $cfp = $this->obxeto("cformas_pago")->obd($cbd);

    if (($erro = $cfp->validar()) != null) {
      $e->post_msx($erro);

      return $e;
    }
    
    

    $cbd->transaccion();

    if (!$ccontacto->update_contacto($cbd)) {
      $e->post_msx( "Error, sucedi&oacute; un error al guardar tu informaci&oacute;n de contacto." );

      $cbd->rollback();
    }

    if (!$cfp->update($cbd)) {
      $e->post_msx( "Error, sucedi&oacute; un error al guardar tus preferencias de pago." );

      $cbd->rollback();
    }
    
    $cbd->commit();
    
    $e->post_msx( "Operación realizada correctamente.", "m" );

    return $e;
  }

  private static function __ccontacto(Contacto_obd $o):CContacto {
    $cc = new CContacto();

    $cc->scbd = "Xesta_cbd";
    
    $cc->post_contacto($o);
    
    return $cc;
  }
}

//**********************************************
/*
final class CAtPermiso extends Componente {
  private ?int $s = null;

  public function __construct(Site_config_obd $o) {
    parent::__construct("catperm", "ptw/paneis/pcontrol/usuario/catperm.html");

    $this->s = $o->atr("id_site")->valor;

    $this->pon_obxeto( self::__ch("chat_0", $o->atr("at_0")->valor == 1, "Permitir acceso de administración."                        ) );
    $this->pon_obxeto( self::__ch("chat_1", $o->atr("at_1")->valor == 1, "Quiero recibir un email por cada acceso de administración.") );
  }

  public function operacion(EstadoHTTP $e) {
//~ echo $e->evento()->html();

    if ($this->obxeto("chat_0")->control_evento()) return $this->operacion_chat($e);
    if ($this->obxeto("chat_1")->control_evento()) return $this->operacion_chat($e);


    return null;
  }

  private function operacion_chat(Epcontrol $e) {
    $b0 = $this->obxeto("chat_0")->valor();
    $b1 = $this->obxeto("chat_1")->valor();
    

    return $this->operacion_aceptar($e, $b0, $b1);
  }

  private function operacion_aceptar(Epcontrol $e, bool $at_0, bool $at_1) {
    $cbd = new FS_cbd();


    $sc = Site_config_obd::inicia($cbd, $this->s);
    
    $sc->atr("at_0")->valor = ($at_0)?1:null;
    $sc->atr("at_1")->valor = ($at_1)?1:null;
    
    if ( $sc->update($cbd) )
      $e->post_msx("Actualizado permisos de administración", "m");
    else 
      $e->post_msx("Sucedió un error al actualizar la BD");

    
    $this->preparar_saida( $e->ajax() );
    
    return $e;
  }

  private static function __ch(string $k, bool $v, string $m):Checkbox {
    $o = new Checkbox($k, $v, $m);
    
    //~ $o->envia_ajax("onclick");
    $o->envia_submit("onclick");
    
    return $o;
  }
}
*/
