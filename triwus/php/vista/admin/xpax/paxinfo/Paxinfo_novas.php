<?php

class Paxinfo_novas extends Paxinfo {
  public function __construct(Paxina_novas_obd $p) {
    parent::__construct($p);
  }

  public function titulo_ficha() {
    return "Propiedades del Noticiero";
  }

  public function paxina_obd(FS_cbd $cbd = null) {
    return $this->paxina_obd_base("novas", $cbd);
  }
}

//----------------------------------------------

final class Paxinfo_nova extends Paxinfo {
  public function __construct(Paxina_novas_obd $p) {
    parent::__construct($p);

    $this->control_action = false;
    
  }

  public function titulo_ficha() {
    return "Noticia";
  }
  
  public function paxina_obd(FS_cbd $cbd = null) {
    return $this->paxina_obd_base("novas", $cbd);
  }
}

//----------------------------------------------

class Paxinfo_nova_2_interna extends Paxinfo {
  protected $oms_grupo;
  
  public function __construct(Paxina_novas2_obd $p, $ptw = null) {
    parent::__construct($p, $ptw);
  }

  public function readonly($b = false) {
    parent::readonly($b);

    //~ $this->obxeto("estado")->readonly = true;
    $this->obxeto("puente" )->readonly = true;
    $this->obxeto("visible")->readonly = true;
  }

  public function titulo_ficha() {
    return "Noticia";
  }

  public function post_paxina(Paxina_obd $p) {
    parent::post_paxina($p);
    
    $this->oms_grupo = $p->atr("grupo")->valor;
    
    $this->obxeto("grupo")->post( $p->atr("grupo_aux")->valor );
  }
  
  public function paxina_obd(FS_cbd $cbd = null) {
    $p = $this->paxina_obd_base("novas2", $cbd);
    
    $p->atr("grupo"    )->valor = $this->oms_grupo;
    $p->atr("grupo_aux")->valor = $this->obxeto("grupo")->valor();

//~ print_r( $p->a_resumo() );
    
    return $p;
  }
}

//----------------------------------------------

final class Paxinfo_nova_2 extends Paxinfo_nova_2_interna {  
  public function __construct(Paxina_novas2_obd $p) {
    parent::__construct($p, "ptw/xpax/paxinfo/paxinfo_1_novas2_externa.html");
  }

  public function titulo_ficha() {
    return "Noticia";
  }

  public function post_paxina(Paxina_obd $p) {
    parent::post_paxina($p);
  }
  
  public function paxina_obd(FS_cbd $cbd = null) {
    $p = parent::paxina_obd($cbd);
    
    
    return $p;
  }
}


