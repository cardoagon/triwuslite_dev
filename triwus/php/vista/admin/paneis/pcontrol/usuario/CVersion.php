<?php


final class CVersion extends Componente implements ICPC {
  private $u = null;

  public function __construct(int $id_site) {
    parent::__construct("cusuario", "ptw/paneis/pcontrol/usuario/usuario.html");
    
    $tcbd = new FS_cbd(1);
    

    $s = Site_obd::inicia($tcbd, $id_site);

    $this->u = $s->admin($tcbd);

    $this->pon_obxeto( new CInfoCuenta($this->u->cuenta_obd ($tcbd)) );
    $this->pon_obxeto( new CActualizar() );
  }

  public function cpc_miga() {
    return "Actualixador";
  }

  public function operacion(EstadoHTTP $e) {
//~ echo $e->evento()->html();

    if (($e_aux = $this->obxeto("cinfocuenta")->operacion($e)) != null) return $e_aux;
    if (($e_aux = $this->obxeto("cactualizar")->operacion($e)) != null) return $e_aux;

    return null;
  }
}

//***********************************

final class CInfoCuenta extends Componente {
  public function __construct(Cuenta_obd $c = null, string $ptw = "ptw/paneis/pcontrol/usuario/cinfocuenta.html") {
    parent::__construct("cinfocuenta", $ptw);

    //~ $this->pon_obxeto( new Param("pnome"         ) );
    $this->pon_obxeto( new Param("ver"           ) );
    //~ $this->pon_obxeto( new Param("pmax_GB"       ) );
    $this->pon_obxeto( new Param("pmultilinguaxe") );
    $this->pon_obxeto( new Param("pventa"        ) );
    //~ $this->pon_obxeto( new Param("pemails"       ) );
    $this->pon_obxeto( new Param("subs_n"        ) );
    $this->pon_obxeto( new Param("subs_v"        ) );
    $this->pon_obxeto( new Param("pdominio"      ) );
    //~ $this->pon_obxeto( new Param("consumo_GB"    ) );
    
    if ($c == null) return;

    $this->iniciar($c);
  }

  public function iniciar(Cuenta_obd $c) {
//~ echo "<pre>" . print_r($c->a_resumo(), 1) . "</pre>";

    $ml         = "Sí";
    $v          = ($c->venta)?"Sí":"No";

    if ( ($dom = TrwlConfig::dominio) == null ) {
      $dom  = "--";
    }


    //* calcula subs.
    $_subs  = $c->_subs();
    
    $subs_n = null;
    $subs_v = null;
    foreach ($_subs as $_s) {
      $subs_n .= "<div>{$_s["nome"   ]}</div>";
      $subs_v .= "<div>{$_s["version"]}</div>";
    }
    
    if ( $subs_n == null ) $subs_n = "<div>--</div>";
    if ( $subs_v == null ) $subs_v = "<div>--</div>";


    //* escribe datos.
    //~ $this->obxeto( "pnome"          )->post( $c->atr("nome")->valor );
    $this->obxeto( "ver"            )->post( TrwlConfig::version        );
    //~ $this->obxeto( "pmax_GB"        )->post( $gb                    );
    $this->obxeto( "pmultilinguaxe" )->post( $ml                    );
    $this->obxeto( "pventa"         )->post( $v                     );
    //~ $this->obxeto( "pemails"        )->post( $em                    );
    $this->obxeto( "subs_n"         )->post( $subs_n                );
    $this->obxeto( "subs_v"         )->post( $subs_v                );
    $this->obxeto( "pdominio"       )->post( $dom                   );
    //~ $this->obxeto( "consumo_GB"     )->post( $consumo_GB            );
  }
}

//***********************************

final class CActualizar extends Componente {
  const ptw_0 = "ptw/paneis/pcontrol/usuario/cactualizar_0.html";
  const ptw_1 = "ptw/paneis/pcontrol/usuario/cactualizar_1.html";
  const ptw_2 = "ptw/paneis/pcontrol/usuario/cactualizar_2.html";
  
  private array $_info;
  
  public function __construct() {
    parent::__construct("cactualizar", self::ptw_0);
    
    $this->pon_obxeto( new Param("info_ver"   ) );
    $this->pon_obxeto( new Param("info_titulo") );
    
    $this->pon_obxeto( Panel_fs::__bexportar("bActu", "Actualizar", "Actualizar la versión del sistema") );
    
    $this->info_actualizar();
  }

  //~ public function operacion(EstadoHTTP $e) {
    //~ $this->info_actualizar();

    //~ return null;
  //~ }

  private function info_actualizar():void {
    $this->_info = Refs::sActu_info();
    
echo "<pre>" . print_r($this->_info, 1) . "</pre>";
    
    if ( isset($this->_info["err"]) ) {
      $this->ptw = self::ptw_2;
      
      return;
    }
    
    
    if ( $this->_info["actualizar"] != 1 ) {
      $this->ptw = self::ptw_0;
      
      return;
    }
    
    
    $this->ptw = self::ptw_1;
    
    $this->obxeto("info_ver"   )->post( $this->_info["seg"]["version"] );
    $this->obxeto("info_titulo")->post( $this->_info["seg"]["titulo" ] );
    
    $this->obxeto("bActu")->pon_eventos("onclick", "document.location = '../instalador'");
  }
}
