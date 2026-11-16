<?php


abstract class Panel_site_cab extends Panel_fs {
  public function __construct($ptw) {
    parent::__construct("site_cabeceira", $ptw);
  }
}

final class Panel_site_cab_0 extends Panel_site_cab {
  const ptw_0 = "ptw/paneis/pcontrol/cab_pcontrol_0.html";
  const ptw_1 = "ptw/paneis/pcontrol/cab_pcontrol_1000.html";
  const ptw_2 = "ptw/paneis/pcontrol/cab_pcontrol_vendedor.html";
  
  public function __construct(FGS_usuario $u) {
    $u_tipo = $u->pcontrol_tipo();

        if ($u_tipo == 30) $ptw = self::ptw_2;
    elseif ($u_tipo == 20) $ptw = self::ptw_1;
    else                   $ptw = self::ptw_0;
    
    parent::__construct($ptw);
  }
}

final class Panel_site_cab_1 extends Panel_site_cab {
  public function __construct() {
    parent::__construct("ptw/paneis/pcontrol/cab_pcontrol_1.html");
  }
}
