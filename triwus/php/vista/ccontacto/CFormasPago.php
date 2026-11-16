<?php


final class CFormasPago extends Componente {
  private $contacto_k = null;
  private $contacto_t = "p";

  public function __construct(Contacto_fpago_obd $cfp = null) {
    parent::__construct("cformas_pago", Refs::url("ptw/ccontacto/fpago.html"));

    $this->pon_obxeto(Panel_fs::__sFPago("pref"  ));
    $this->pon_obxeto(Panel_fs::__text  ("iban"  ));
    $this->pon_obxeto(Panel_fs::__text  ("bene"  ));
    $this->pon_obxeto(Panel_fs::__text  ("swift" ));
    $this->pon_obxeto(Panel_fs::__text  ("vence" ));

    //~ $this->obxeto("pref")->envia_SUBMIT("onchange");
    $this->obxeto("pref")->envia_AJAX("onchange");

    $this->obxeto("bene"   )->readonly = true;
    $this->obxeto("vence"  )->readonly = true;
    
        
    if ($cfp == null) return;
    
    
    $this->post_obd($cfp);
  }

  public function post_obd(Contacto_fpago_obd $o) {
//~ echo "<pre>" . print_r($o->a_resumo(), 1) . "</pre>";

    $this->contacto_k = $o->atr("id_contacto")->valor;
    $this->contacto_t = $o->atr("tipo"       )->valor;

    $this->obxeto("bene"  )->post( Contacto_fpago_obd::trw_bene  );
    $this->obxeto("vence" )->post( Contacto_fpago_obd::trw_vence );


    $this->obxeto("pref")->post( $o->atr("pref")->valor );

    if ($o->atr("pref")->valor == "t") {
      $this->obxeto("iban"  )->post( Contacto_fpago_obd::trw_iban  );
      $this->obxeto("swift" )->post( Contacto_fpago_obd::trw_swift );

      $this->obxeto("iban"  )->readonly = true;
      $this->obxeto("swift" )->readonly = true;
    }
    else {
      $this->obxeto("iban"  )->post( $o->iban () );
      $this->obxeto("swift" )->post( $o->swift() );
      
      $this->obxeto("iban"  )->readonly = false;
      $this->obxeto("swift" )->readonly = false;
    }
  }

  public function obd(FS_cbd $cbd):Contacto_fpago_obd {
    $o = Contacto_fpago_obd::inicia($cbd, $this->contacto_k, $this->contacto_t);
     
    $o->atr("pref")->valor = $this->obxeto("pref")->valor();

    if ($this->obxeto("pref")->valor() == "t") return $o;
    
    
    $o->iban ( $this->obxeto("iban" )->valor() );
    $o->swift( $this->obxeto("swift")->valor() );
    
    
    return $o;
  }


  public function operacion(EstadoHTTP $e) {
    if ($this->obxeto("pref")->control_evento()) return $this->operacion_pref($e);
    
    return null;
  }


  private function operacion_pref(EstadoHTTP $e) {
    $o = Contacto_fpago_obd::inicia(new FS_cbd(), $this->contacto_k, $this->contacto_t);
    
    $o->atr("pref")->valor = $this->obxeto("pref")->valor();
    
    $this->post_obd($o);
    
    $this->preparar_saida( $e->ajax() );
    
    return $e;
  }
}

