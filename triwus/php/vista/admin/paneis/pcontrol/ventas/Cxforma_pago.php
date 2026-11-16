<?php

final class Cxfpago extends Componente implements ICPC {
  const ptw_1 = "ptw/paneis/pcontrol/ventas/fpago/cxfpago_2.html";

  private $id_site = null;

  private $_activada = null;

  public function __construct($id_site) {
    parent::__construct("cxfpago", self::ptw_1);

    $this->id_site = $id_site;

    $this->pon_obxeto(new CXfpago_lista($id_site));

    $this->pon_obxeto(new Param("msx"));

    $this->pon_obxeto(new Cxfpago_fp_nula()); //* config. inicial.
  }

  public function cpc_miga() {
    return "formas de pago";
  }

  public function operacion(EstadoHTTP $e) {
    if (($e_aux = $this->obxeto("cxfpagol"   )->operacion($e)) != null) return $e_aux;

    if (($e_aux = $this->obxeto("cxfp_config")->operacion($e)) != null) return $e_aux; //* es importante hacer esta comprobación después de comprobar las operaciones en 'cxfpagol'.

    return null;
  }

  public function operacion_CXFPiniciar(Epcontrol $e) {
    $cxfpc = $this->obxeto_cxfp( $e->evento()->subnome(0) );

    $cxfpc->preparar_saida($e->ajax());

    return $e;
  }

  public function operacion_CXFPactivar(Epcontrol $e) {
    $id_fpago = $e->evento()->subnome(0);
    
    $cbd      = new FS_cbd();

    $fp = FPago_obd::inicia($cbd, $id_fpago);
    
    if ($fp->activada($this->id_site, $cbd)) {
      $fp->desactivar($this->id_site, $cbd);
      
      return $e;
    }

    $fp->activar($this->id_site, $cbd);

    return $this->operacion_CXFPiniciar($e);
  }
  
  private function obxeto_cxfp($id_fpago) {      
    $sfp = Site_fpago_obd::inicia(new FS_cbd(), $this->id_site, $id_fpago);

    $cxfpc = Cxfp_config::inicia($sfp);

    $this->pon_obxeto($cxfpc);

    return $cxfpc;
  }
}

//*********************************

final class CXfpago_lista extends FS_lista {
  public $id_site = null;

  public function __construct($id_site) {
    parent::__construct("cxfpagol", new CXfpago_ehtml());

    $this->id_site    = $id_site;


    $this->selectfrom = "select a.*, b.activa 
                           from fpago a left join site_fpago b on (b.id_site = {$id_site} and a.id_fpago = b.id_fpago)";
    $this->where      = "1=1";
    //~ $this->where      = "a.id_fpago < 6";
  }

  public function operacion(EstadoHTTP $e) {
    $evento = $e->evento();

    if ($this->control_fslevento($evento, "lnome" )) return $this->pai->operacion_CXFPiniciar($e);
    if ($this->control_fslevento($evento, "activa")) return $this->pai->operacion_CXFPactivar($e);

    return parent::operacion($e);
  }
}

//---------------------------------

final class CXfpago_ehtml extends FS_ehtml {
  public $nf = 0;

  public function __construct() {
    parent::__construct();

    $this->style_table = "margin: 9px 5px 11px 5px;";
    $this->align       = "center";
    $this->width       = "99%";
    $this->cellspacing = 0;
    $this->cellpadding = 0;
    $this->border      = 0;
  }

  protected function cabeceira($df = null) {
    $this->nf = $this->rbd->numFilas();

    return "<tr>
              <td width=1px >Activa</td>
              <td>Nombre</td>
            </tr>";
  }

  protected function linha_detalle($df, $f) {
    //~ echo "<pre>" . print_r($f, true) . "</pre>";

    $style_tr = FS_ehtml::style_tr;

    return "<tr  {$style_tr} >
              <td>" . self::__chactiva($this, $f) . "</td>
              <td>" . self::__lnome($this, $f) . "</td>
            </tr>";
  }

  private static function __chactiva(CXfpago_ehtml $ehtml, $f) {
    $ch = new Checkbox("activa", $f['activa']);

    if ($f['activa'] == 1)
      $msx = "&iquest; Desea desactivar la forma de pago {$f['nome']} ?";
    else
      $msx = "";

    $ch->envia_ajax("onclick", $msx);
    //~ $ch->envia_submit("onclick", $msx);


    return $ehtml->__fslControl($ch, $f['id_fpago'])->html();
  }

  private static function __lnome(CXfpago_ehtml $ehtml, $f) {
    $l = new Div("lnome", $f['nome']);

    $l->readonly = $f['activa'] != 1;
    
    if (!$l->readonly) $l->style("default", "color: #0000ba; cursor: pointer;");


    $l->envia_ajax("onclick");
    //~ $l->envia_submit("onclick");


    return $ehtml->__fslControl($l, $f['id_fpago'])->html();
  }
}

