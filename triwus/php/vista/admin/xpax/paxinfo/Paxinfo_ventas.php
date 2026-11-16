<?php

class Paxinfo_ventas2 extends Paxinfo {  
  public function __construct(Paxina_ventas2_obd $p) {
    parent::__construct($p, "ptw/xpax/paxinfo/paxinfo_1_ventas2.html");
  }

  public function titulo_ficha() {
    return "Propiedades de la página de ventas ";
  }

  public function paxina_obd(FS_cbd $cbd = null) {
    return $this->paxina_obd_base("ventas2", $cbd);
  }
}


//***********************************************



class Paxinfo_ventas2cat extends Paxinfo_ventas2 {  
  public function __construct(Paxina_ventas2_obd $p, int $id_cat) {
    parent::__construct($p);
    
    $this->obxeto("id3")->post( $id_cat );
  }

  public function titulo_ficha() {
    return "Propiedades de la página de ventas ";
  }

  public function paxina_obd(FS_cbd $cbd = null) {
    return $this->paxina_obd_base("ventas2", $cbd);
  }


  public function readonly($b = false) {
    Componente::readonly(true);

    //* casos especiales.
    $this->obxeto("titulo"    )->readonly = $b;
    $this->obxeto("cat_h1"    )->readonly = $b;
    $this->obxeto("descricion")->readonly = $b;

    //* botonera.
    $this->obxeto("bAceptar" )->readonly = $b;
    $this->obxeto("bCancelar")->readonly = $b;
    $this->obxeto("bEditar"  )->readonly = true;

    $this->obxeto("bAceptar" )->visible = !$b;
    $this->obxeto("bCancelar")->visible = !$b;
    $this->obxeto("bEditar"  )->visible = false;
  }
}


//***********************************************


class Paxinfo_ventas extends Paxinfo {
  public function __construct(Paxina_ventas_obd $p) {
    parent::__construct($p);
  }

  public function titulo_ficha() {
    return "Propiedades de la página de ventas";
  }

  public function paxina_obd(FS_cbd $cbd = null) {
    return $this->paxina_obd_base("ventas", $cbd);
  }
}


//***********************************************


final class Paxinfo_venta extends Paxinfo {
  public function __construct(Paxina_ventas_obd $p) {
    parent::__construct($p);

    //~ $this->control_action = false;
  }

  public function titulo_ficha() {
    return "Noticia";
  }

  public function paxina_obd(FS_cbd $cbd = null) {
    return $this->paxina_obd_base("ventas", $cbd);
  }
}

