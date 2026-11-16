<?php

final class Prgf_imarcas extends    Paragrafo
                         implements IPrgf_iventas {
                           
  const ptw_0 = "ptw/paragrafo/prgf_marcas_0.html";

  public $id_site     = null;
  public $m           = null;
  public $p           = null;
  public $id_marca    =  "t";
  public $json_marcas = null;
  
  
  public function __construct(Paxina_marcas_obd $p) {
    $this->p = $p;
    $prgf    = $p->prgf_indice();

    parent::__construct($prgf, "prgf", false);

    $this->id_site        = $p->atr("id_site")->valor;

    $this->alg_html_elmts = 7;
    $this->usa_bpos       = false;
    $this->excluir        = 1;
  }

  public function inicia_ptw(Config_obd $c) {
    $this->inicia_t($this->id_site);
  }
  
  public function operacion(EstadoHTTP $e) {
    if (($lrpro = $this->obxeto("cat")) != null) {
      if (($e_aux = $lrpro->operacion($e)) != null) return $e_aux;
    }


    
    if (($e_aux = $this->operacion_url_marca($e)) != null) return $e_aux;
    

    return parent::operacion($e);
  }

  public function operacion_editar(Epaxina_edit $e) {
    $e->obxeto("detalle_btnr_oeste")->pon_editor($e, new Editor_prgf_marcas($this));

    return $e;
  }

  private function operacion_url_marca(EstadoHTTP $e) {
    $ev = $e->evento();

//~ echo $ev->html();

    if ($ev->tipo()  != "op-reservada") return null;

    if ($ev->nome(0) != "marcas"      ) return null;

    $_p = $ev->nome(1);

    if ( !is_array($_p) ) {
      $e->redirect("index.php");

      return $e;
    }

//~ echo "<pre>" . print_r($_p, 1) . "</pre>";

    if ($_p[0] != "t") 
      return $this->operacion_inicia_x($e, $_p);
    
    
    return $this->operacion_inicia_t($e);
  }

  public function html_marcas() {
    if ($this->id_marca != "t") return $this->obxeto("cat")->html();
    

    $html = LectorPlantilla::plantilla( $this->ptw );

    $html = str_replace("[vista]"      , strval($this->imarcas_vista), $html);
    $html = str_replace("[json_marcas]", $this->json_marcas  , $html);
    $html = str_replace("[m]"          , "m"                 , $html);

    return $html;
  }

  private function operacion_inicia_t(EstadoHTTP $e) {
    $this->inicia_t($e->id_site, $e->paxina());
    
    return $e;
  }

  private function operacion_inicia_x(EstadoHTTP $e, array $_ev) {
    $this->ptw = null;

//~ echo "<pre>" . print_r($_ev, 1) . "</pre>";

    if ($this->id_marca == $_ev[0]) {
      $this->obxeto("cat")->control_url($_ev);
      
      return $e; //* xa está iniciado, (rechamada).
    }
    

    $this->id_marca = $_ev[0];
     
    
    $id_site = $e->id_site;
    //~ $c       = $e->config();
    
    
    $cbd = new FS_cbd();


    $this->m = Articulo_marca_obd::inicia($cbd, $id_site, $this->id_marca);

    $this->m->orderby( $this->orderby() );



    //* actualiza a información de cabeceira da paxina para compartir en RS.
    $smt = Site_metatags_obd::inicia($cbd, $id_site);

    $smt->__publicar($e->paxina(), $this->m);


    //* inicia Catalogo_p.
    $lpr = new Catalogo_p( $this->m, $e->edicion(), $e->config(), "{$_ev[2]}?m={$this->id_marca}");
    
    $lpr->control_url($_ev);
    
    $this->pon_obxeto($lpr);

    
    return $e;
  }

  private function inicia_t($id_site, Paxina $p = null) {
    $this->id_marca = "t";
    
    $this->ptw = Refs::url( self::ptw_0 );


    //* actualiza a información de cabeceira da paxina para compartir en RS.
    if ($p != null) {
      $smt = Site_metatags_obd::inicia(new FS_cbd(), $id_site);

      $smt->__publicar($p, $this->p);
    }



    if ($this->json_marcas != null) return;

    $this->json_marcas = json_encode( Articulo_marca_obd::_marca_2($id_site, null, true, 100) );
  }

  private function orderby() {
    if ($this->orderby == 1) return "a.id_articulo desc";
    
    if ($this->orderby == 2) return "a.id_articulo asc";
    
    if ($this->orderby == 9) return "rand()";

    return "a.nome asc"; //* opción ($this->orderby == 3) predeterminada.
  }
}
