<?php


final class Detalle_novas2 extends Detalle {
  private  Elmt_carrusel     $ec;
  private ?Paxina_novas2_obd $pax;
  private  int               $prgfAux_width = 1221;
  private  string            $id_idioma;
           
  private  float             $inovas_imx_proporcion = 1;
  private ?int               $inovas_rel_b          = 1;
  private ?string            $inovas_rel_t          = null;
  private  int               $inovas_rel_s          = 1;

  public function __construct(Paxina_novas2_obd $p, $editor = true) {
    parent::__construct($p, $editor);
    
    $this->pax = $p;
    
    $this->config_inovas();
  }

  public function config_usuario(IEfspax_xestor $e = null) {
    parent::config_usuario($e);
    
    if ($this->__editor           ) return;
    if ($this->inovas_rel_b   != 1) return;
    
    if ($this->pax->atr("id_prgf_indice")->valor > 0) return;


        if ( ($this->id_idioma = $e->id_idioma()) == null      ) $this->id_idioma = "es";
    elseif ( !isset(Msx::$novas2_rel["etq"][$this->id_idioma]) ) $this->id_idioma = "es";
    
    
    if (($w = $e->config()->atr("w")->valor) != null) $this->prgfAux_width = $w;
        
    $this->ec   = new Elmt_carrusel( new ElmtCarrusel_novas2_obd($this->pax, $e->usuario()->a_permisos(), $this->inovas_imx_proporcion) );
    
    
    $this->pax  = null; //* liberamos RAM.
  }
  
  public function declara_botoneras(Epaxina_edit $e, Paxina_obd $p) {
    //~ $e->pon_obxeto(new Btnr_detalle_norte_novas2($e, $p));
    $e->pon_obxeto(new Btnr_detalle_norte($e, $p));

    $e->pon_obxeto(new Btnr_detalle_este($this));

    $e->pon_obxeto(new Btnr_detalle_oeste());
  }

  public function ini_prgf(Paxina_obd $pax, Paragrafo_obd $prgf) {
    if ($pax->atr("id_prgf_indice")->valor == $prgf->atr("id_paragrafo")->valor) {
      $this->iprgf = new Prgf_inovas2($pax, $this->__editor);

      return $this->iprgf;
    }

    return new Paragrafo($prgf);
  }

  public function preparar_saida(Ajax $a = null) {
    parent::preparar_saida($a);

    if ($a           == null) return;
    if ($this->iprgf == null) return;
    
    
    $this->iprgf->preparar_saida($a);
  }

  protected function prgfs_html() { //* SOBRESCRIBE: Detalle.prgfs_html()
    $html = parent::prgfs_html();
    
    if ($this->iprgf != null) return $html;

    
    return $html . $this->prgf_carrusel_html();
  }

  private function prgf_carrusel_html():string {    
    if ($this->__editor           ) return "";
    if ($this->inovas_rel_b   != 1) return "";
    if ($this->ec->numitens() == 0) return "";

    if (($etq = $this->inovas_rel_t) == null) $etq = Msx::$novas2_rel["etq"][$this->id_idioma];

    return "
<div class='novas2_rel'>
  <div class='celda_prgf'>
    <div class='celda_prgf_2' style='max-width:{$this->prgfAux_width}px;'>
      <div class='novas2_rel_etq'>{$etq}</div>
      " . $this->ec->html() . "
    </div>
  </div>
</div>";
  }

  private function config_inovas() {
    if ($this->pax->atr("id_prgf_indice")->valor > 0) return;
    
    $id_opcion = $this->pax->atr("id_opcion")->valor;
    $id_idioma = $this->pax->atr("id_idioma")->valor;
    
    $sql = "
select d.inovas_imx_proporcion, d.inovas_rel_b, d.inovas_rel_t, d.inovas_rel_s
  from paxina a inner join paxina_novas b on (a.id_paxina = b.id_paxina)
                inner join v_ml_paxina  c on (a.id_paxina = c.id_paxina)
                inner join paragrafo    d on (a.id_prgf_indice = d.id_paragrafo)
  where (a.id_opcion = {$id_opcion} and c.id_idioma = '{$id_idioma}' and a.id_prgf_indice > 0)";


    $cbd = new FS_cbd();
       
    $r = $cbd->consulta($sql);
    
    if (!$_r = $r->next()) return;
    
    $this->inovas_imx_proporcion = floatval($_r["inovas_imx_proporcion"]);
    $this->inovas_rel_b          = intval  ($_r["inovas_rel_b"         ]);
    $this->inovas_rel_t          = strval  ($_r["inovas_rel_t"         ]);
    $this->inovas_rel_s          = intval  ($_r["inovas_rel_s"         ]);
  }
}

