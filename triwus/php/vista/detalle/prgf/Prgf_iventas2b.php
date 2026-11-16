<?php

final class Prgf_iventas2b extends Paragrafo
                        implements IPrgf_iventas, IMetatags {
 
  public int $id_site;
                         
  private bool               $editor;
  private array              $_param;
  private Paxina_ventas2_obd $p;
                            
  public function __construct(Paxina_ventas2_obd $p, bool $editor) {
    parent::__construct($p->prgf_indice());

    $this->alg_html_elmts = 10;
    $this->usa_bpos       = false;
    $this->excluir        = 1;
    
    $this->id_site        = $p->atr("id_site")->valor; //* compatibilidade con Editor_prgf_ventas2.
    $this->editor         = $editor;
    
    //* config ICatalogo_fonte.
    $this->p                  = $p;
    $this->p->id_categoria    = $this->iventas_categoria;
    
    $this->p->icatF__orderby = $this->orderby();
  }

  public function inicia_ptw(Config_obd $c) {
    $action    = $this->p->atr("action")->valor . "?ctlg=" . $this->p->atr("id_idioma")->valor;
    $hai_busca = $this->iventas_buscador == 1;
    

    $this->pon_obxeto( new Catalogo_p( $this->p, $this->editor, $c, $action, $hai_busca) );
  }

  public function operacion(EstadoHTTP $e) {
    if ( ($e_aux = parent::operacion($e)                ) != null ) return $e_aux;

    if ( ($e_aux = $this->obxeto("cat")->operacion($e)) != null ) return $e_aux;

    return $this->operacion_url($e);
  }

  public function operacion_editar(Epaxina_edit $e) { 
    $e->obxeto("detalle_btnr_oeste")->pon_editor($e, new Editor_prgf_ventas2($this));

    return $e;
  }

  public function html_ventas2b() {
    if (($catp = $this->obxeto("cat")) == null) return "";
    
    return $catp->html();
  }

  public function _metatags($url_limpa, FS_cbd $cbd = null) {  //* IMPLEMENTA IMetatags._metatags()
    if ($cbd == null) $cbd = new FS_cbd();
    //~ $cbd = new FS_cbd(1);

    //~ echo "<br><br><br><br><br><pre>" . print_r($this->_param, 1) . "</pre>";
    
    $paxaux = $this->calcula_paxaux($cbd);   
    
    //~ echo "<pre>" . print_r($paxaux->a_resumo(), 1) . "</pre>";
    //~ echo "<pre>" . print_r($paxaux->a_migas(), 1) . "</pre>";
    

    $tit    = $paxaux->atr("titulo"    )->valor;
    $des    = $paxaux->atr("descricion")->valor;
    $log    = $paxaux->iclogo_src($cbd);
    $rob    = $paxaux->atr("robots")->valor; //* "index, follow";
    $script = null;

    $url = null;
    $idi = $paxaux->atr("id_idioma")->valor;
    $can = $paxaux->action_2(true, false);
    
    
    //* actualizamos el H1 del catálogo.
    $this->obxeto("cat")->obxeto("clista")->titulo = "<h1>" . $this->calcula_h1($paxaux) . "</h1>"; 
    $this->obxeto("cat")->obxeto("clista")->migas  = $this->calcula_migas($paxaux); 


    return array($tit, $des, $log, $rob, $url, $idi, $can, $script);
  }

  protected function pon_btnr() {
    $this->pon_obxeto(new Btnr_prgf(false));
  }

  private function operacion_url(EstadoHTTP $e) {
    $ev = $e->evento();

//~ echo $ev->html();

    if ($ev->tipo()  != "op-reservada") return null;

    if ($ev->nome(0) != "catalogo"    ) return null;

    $this->_param = $ev->nome(1);

    if ( !is_array($this->_param) ) {
      $e->redirect("index.php");

      return $e;
    }

    $this->obxeto("cat")->control_url($this->_param);
 

    if ( !$this->modifica_metatags() ) return $e;

    

    //* actualiza a información de cabeceira da paxina (head).
    $smt = Site_metatags_obd::inicia(new FS_cbd(), $e->id_site);

    $smt->__publicar($e->paxina(), $this);


    return $e;
  }

  private function orderby() {
    if ($this->orderby == 1) return "momento desc";

    if ($this->orderby == 2) return "momento asc";

    if ($this->orderby == 3) return "nome asc";

    return "rand()"; //* opción ($this->orderby == 9) predeterminada.
  }

  private function modifica_metatags():bool {
    if ($this->id_site == 124) return true; //* solo para o site 124.
        
    
    return false;
  }

  private function calcula_paxaux(FS_cbd $cbd):Paxina_ventas2_obd {
    if ( !isset($this->_param["_f"])       ) return $this->p;
    if ( !isset($this->_param["_f"]["f1"]) ) return $this->p;
    
    if (($f1 = $this->_param["_f"]["f1"]) == null) return $this->p;
    
    $_f1 = explode(",", $f1);
    
    
    if ( count($_f1) > 1              ) return $this->p;
    if ( Valida::numero($_f1[0]) == 0 ) return $this->p;
    
    
    return Paxina_v2cat_obd::inicia_cat($cbd, $this->p->atr("id_paxina")->valor, $_f1[0]);
  }

  private function calcula_h1(Paxina_ventas2_obd $paxaux):string {
    if (($h1 = $paxaux->atr("cat_h1")->valor) != null) return $h1;
            
    return $paxaux->atr("titulo")->valor;
  }

  private function calcula_migas(Paxina_ventas2_obd $paxaux):string {
    $a_migas = $this->p->a_migas();
    
    if ($paxaux->atr("tipo")->valor == "ventas2cat") $a_migas[] = [null, $paxaux->atr("cat_h1")->valor, null];
    
    $migas  = new Elmt_paxinador_2($a_migas);
            
    return $migas->html();
  }

}
