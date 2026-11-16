<?php

class Editor_prgf_ventas extends Editor_prgf {
  const ptw_0 = "ptw/paragrafo/editor_prgf_ventas_0.html";

  public function __construct(IPrgf_iventas $p) {
    parent::__construct($p);

    $this->ptw = self::ptw_0;


    $this->pon_obxeto(self::_sbuscador ($p));
    $this->pon_obxeto(self::_scategoria($p));
    $this->pon_obxeto(self::_sorde     ($p));
  }

  public function operacion(EstadoHTTP $e) {
    if ($this->obxeto("sbuscador")->control_evento()) {
      $this->obxeto("scategoria")->readonly = $this->obxeto("sbuscador")->valor() != 1;
      
      $this->obxeto("scategoria")->post(1);

      $this->preparar_saida($e->ajax());
      //~ $e->ajax()->pon( $this->obxeto("scategoria") );

      return $e;
    }

    return parent::operacion($e);
  }

  public function operacion_aceptar(Efs_admin $e) {
    //* validar.
    if ( ($erro = $this->obxeto("scategoria")->validar()) == null ) {
      $this->obxeto("scategoria")->post(1);
    }
    
    //* actualizar.
    $this->prgf->iventas_buscador  = $this->obxeto("sbuscador" )->valor();
    $this->prgf->iventas_categoria = $this->obxeto("scategoria")->valor();

    $orderby_aux = $this->prgf->orderby;

    $this->prgf->orderby = $this->obxeto("sorde")->valor(); 

    if ($this->prgf->orderby != $orderby_aux) {
      //~ $this->prgf->operacion_reset($e);


      $this->prgf->config_edicion();
    }
    

    return parent::operacion_aceptar($e);
  }

  private static function _sbuscador(IPrgf_iventas $p) {
    $_o = array("1" => "Usar buscador predeterminado",
                "9" => "Sin buscador"
               );
    
    $s = new Select("sbuscador", $_o);

    $s->post($p->iventas_buscador);

    $s->envia_AJAX("onchange");
    

    return $s;
  }

  private static function _scategoria(IPrgf_iventas $p) {
    $s = new DataList("scategoria", Categoria_obd::_migas($p->id_site));
    
    if ($p->iventas_categoria == null)
      $s->post(1);
    else
      $s->post($p->iventas_categoria);
      

    $s->style("default", "width: 99%;");


    return $s;
  }

  private static function _sorde(IPrgf_iventas $p) {
    $_o = array("1" => "Posicionar manualmente",
                "9" => "Primero los más recientes"
               );
    
    $s = new Select("sorde", $_o);

    $s->post($p->orderby);
    

    return $s;
  }
}

//***************************************
/*
class Editor_prgf_cxcli extends Editor_prgf_ventas {
  public function __construct(IPrgf_iventas $p) {
    parent::__construct($p);

    $this->ptw = "ptw/paragrafo/editor_prgf_cxcli.html";

    $this->obxeto("sbuscador")->pon_opcion("2", "Usar buscador a medida");
    $this->obxeto("sbuscador")->sup_opcion("9");
  }
}
*/
//***************************************

class Editor_prgf_ventas2 extends Editor_prgf_ventas {
  public function __construct(IPrgf_iventas $p) {
    parent::__construct($p);

    $this->ptw = "ptw/paragrafo/editor_prgf_ventas2.html";


    $this->pon_obxeto(self::_sorde($p));


    //~ $this->obxeto("sbuscador")->pon_opcion("2", "Usar buscador a medida");
  }

  private static function _sorde(IPrgf_iventas $p) {
    $_o = array("1" => "Los más recientes primero",
                "2" => "Los más recientes al final",
                "3" => "Ordenar por nombre",
                "9" => "Aleatorio"
               );
    
    $s = new Select("sorde", $_o);

    $s->post($p->orderby);
    

    return $s;
  }
}


//***************************************

class Editor_prgf_marcas extends Editor_prgf_ventas2 {
  public function __construct(IPrgf_iventas $p) {
    parent::__construct($p);

    $this->ptw = "ptw/paragrafo/editor_prgf_marcas_0.html";

    $this->pon_obxeto(self::_svista($p));


    $this->obxeto("sorde")->pon_opcion("3", "Nombre");
  }

  public function operacion_aceptar(Efs_admin $e) {
    $this->prgf->modificado     = true;
    $this->prgf->imarcas_vista  = $this->obxeto("svista")->valor();
    $this->prgf->orderby        = $this->obxeto("sorde" )->valor(); 

    $e->ajax()->pon_ok(true, "prgf_imarcas_0000( {$this->prgf->json_marcas}, {$this->prgf->imarcas_vista} )");

    return Editor_prgf::operacion_aceptar($e);
  }

  private static function _svista(IPrgf_iventas $p) {
    $_o = array("1"=>"Parrilla", "2"=>"Collage");
    
    $s = new Select("svista", $_o);

    $s->post($p->imarcas_vista);
    

    return $s;
  }
}

