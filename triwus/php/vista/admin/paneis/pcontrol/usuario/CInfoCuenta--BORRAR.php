<?php

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
