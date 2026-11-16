<?php


class XPax_lidioma extends FSL_navelmt {
  public $where_filtro = "";

  protected $id_site;
  protected $u;

  public function __construct(FGS_usuario $u, $id_site) {
    parent::__construct("loms", new XPax_lidioma_ehtml());

    $this->id_site = $id_site;
    $this->u       = $u;


    $this->selectfrom = "select a.id_idioma, a.nome, b.pos, b.activo
                           from idioma a inner join v_ml_site b on (a.id_idioma = b.id_idioma and b.id_site = {$id_site})";
    $this->orderby    = "b.pos ASC";
  }

  public function select_idoms($id_oms = null, $id_idioma = null, $id_pax = null) {
    if (($a_sublistas = $this->obxetos("sublista")) == null) return;

    foreach($a_sublistas as $sublista) $sublista->select_idoms($id_oms, $id_idioma, $id_pax);
  }

  public function inicia_where() {}

  public function preparar_saida(Ajax $a = null) {
    parent::preparar_saida($a);
    
    if ($a == null) return;
    
    $a->pon_ok(true, "pcontrol_xpax_link()");
  }

  public function sublista_prototipo($id_rama) {
    $loms = new XPax_loms($this->u, $this->id_site, $id_rama, null);

    $loms->inicia_where($this->where_filtro);

    return $loms;
  }
}

//---------------------------------

final class XPax_lidioma_ehtml extends FS_ehtml {
  public $i  = 0;
  public $nf = 0;

  protected $ini = true;

  public function __construct() {
    parent::__construct();

    $this->class_table = "";
    $this->style_table = "";
    $this->width       = "99%";
    $this->align       = "center";
    $this->cellspacing = 0;
    $this->cellpadding = 0;
    $this->border      = 0;
  }

  final public function en_custodia() {
    return $this->xestor->readonly;
  }

  protected function cabeceira($df = null) {
    $this->i  = 0;
    $this->nf = $this->rbd->numFilas();

    return "";
  }

  protected function linha_detalle($df, $f) {
    $id_idioma = $f['id_idioma'];

    if (($this->ini) && ($this->i == 0)) {
      $this->rama_click($id_idioma);

      $this->ini = false;
    }

    $this->rama_check($id_idioma);

    $rama_html = $this->rama_html($id_idioma);

    $html = "<tr>
               <td>

                 <table width=100% align=center border=0 cellspacing=0 cellpadding=0
                        style='padding: 5px 0; border-left: 3px solid #999; border-right: 1px solid #ddd; background-color: #ddd;'>
                   <tr>
                     <td width=17px align=center>" . $this->__bmais_html($id_idioma) . "</td>
                     <td width=* style='padding-left: 5px;'><img src='imx/bandeiras/{$id_idioma}.png' width='27px' /></td>
                   </tr>
                 </table>

               </td>
             </tr>";

    if ($rama_html == null) return $html;

    return "{$html}
            <tr>
              <td>

                <table width=100% align=center border=0 cellspacing=0 cellpadding=0>
                  <tr valign=top>
                    <td width=* colspan=3 style='border-left: 3px solid #b9b9b9; border-right: 1px solid #ddd; '>" . $this->rama_html($id_idioma) . "</td>
                  </tr>
                </table>

              </td>
            </tr>";
   }

}

//*******************************************

class XPax_loms extends FSL_navelmt {
  protected $id_site;

  public $u;

  public $id_idioma;
  public $id_oms_pai;
  public $id_pax_select;

  public function __construct(FGS_usuario $u, $id_site, $id_idioma, $pai) {
    parent::__construct("loms", new XPax_loms_ehtml());

    $this->id_site    = $id_site;
    $this->u          = $u;

    $this->id_idioma  = $id_idioma;
    $this->id_oms_pai = $pai;

    $this->selectfrom = "select id_opcion, id_idioma, id_paxina, tipo, nome_paxina, posicion, pai from v_site_opcion";
    $this->orderby    = "posicion ASC";
  }

  public function id_oms_select() {
    return $this->id_pax_select;
  }

  public function select_idoms($id_oms = null, $id_idioma = null, $id_pax = null) {
    $this->id_pax_select = $id_pax;

    if (($a_sublistas = $this->obxetos("sublista")) == null) return;

    foreach($a_sublistas as $sublista) $sublista->select_idoms($id_oms, $id_idioma, $id_pax);
  }

  public function inicia_where($where_filtro = 1) {
    $w_idioma = ($this->id_idioma  == null)?"id_idioma is null":"id_idioma = '{$this->id_idioma}'";
    $w_pai    = ($this->id_oms_pai == null)?"pai is null"      :"pai = {$this->id_oms_pai}";

    $this->where = "({$where_filtro}) and (id_site = {$this->id_site}) and ({$w_idioma}) and ({$w_pai})";

//~ echo $this->sql_vista();
  }

  public function operacion(EstadoHTTP $e) {
    $evento = $e->evento();

//~ echo "xpax-1:" . $evento->html(); //* ollo ajax

    if ($this->control_fslevento($evento, "op")) {
      $_ev = explode(Escritor_html::cnome   , $evento->tipo());

      if (isset($_ev[1])) $_p = explode(Escritor_html::csubnome, $_ev[1]);

      switch ( $_ev[0] ) {
        case "onclick":
          return $e->obxeto("xpax")->operacion_idoms($e, $evento->subnome(0), $evento->subnome(1), null);


        case "ba":
          return $e->obxeto("xpax")->obxeto("cpaxnova")->operacion_bpaxnova($e, $_p[0]);

        case "bd":
          return $e->obxeto("xpax")->obxeto("cpaxdupli")->operacion_bdupli($e, $_p[0]);

        case "be":
          return $e->op_editar($_p[0], $_p[1]);

        case "bs":
          return $e->obxeto("xpax")->obxeto("cpaxsup")->operacion_bsup($e, $_p[0], ($_p[1] == "ref"));


        case "bmove_b":
          $e->op_posicion($this->id_site, $_p[0], $this->id_oms_pai, 1);

          $e->obxeto("xpax")->obxeto("loms")->preparar_saida($e->ajax());


          return $e;

        case "bmove_bn":
          $e->op_nivel($_p[0], 1);

          $e->obxeto("xpax")->obxeto("loms")->preparar_saida($e->ajax());


          return $e;

        case "bmove_s":
          $e->op_posicion($this->id_site, $_p[0], $this->id_oms_pai, -1);

          $e->obxeto("xpax")->obxeto("loms")->preparar_saida($e->ajax());

          return $e;

        case "bmove_sn":
          $e->op_nivel($_p[0], -1);

          $e->obxeto("xpax")->obxeto("loms")->preparar_saida($e->ajax());


          return $e;
      }
    }

    return parent::operacion($e);
  }

  public function sublista_prototipo($id_rama) {
    list($id_oms, $tipo) = explode("@", $id_rama);

    return $this->factory_sublista($id_oms, $this->id_idioma, $tipo);
  }

  public function factory_sublista($id_oms, $id_idioma, $tipo) {
    switch ($tipo) {
      case "novas":
        $fsl = new XPax_loms2_novas($id_oms, $id_idioma);

        $fsl->readonly = $this->readonly;

        return $fsl;

      case "novas2":
        $fsl = new XPax_loms2_novas2($id_oms, $id_idioma);

        $fsl->readonly = $this->readonly;

        return $fsl;

      case "ventas":
        $fsl = new XPax_loms2_ventas($id_oms, $id_idioma);

        $fsl->readonly = $this->readonly;

        return $fsl;

      case "ventas2":
        $fsl = new XPax_loms2_ventas2($id_oms, $id_idioma);

        $fsl->readonly = $this->readonly;

        return $fsl;

      case "libre":
        $fsl = new XPax_loms($this->u, $this->id_site, $this->id_idioma, $id_oms);

        $fsl->inicia_where();

        if ($fsl->__count() == 0) return null;

        $fsl->readonly = $this->readonly;

        return $fsl;

    }

    return null;
  }
}

//---------------------------------

class XPax_loms_ehtml extends FS_ehtml {
  public $nf = 0;

  public function __construct() {
    parent::__construct();


    $this->class_table = "";
    $this->style_table = "";
    $this->width       = "100%";
    $this->cellspacing = 0;
    $this->cellpadding = 0;
    $this->border      = 0;
  }

  final public function en_custodia() {
    return $this->xestor->u->en_custodia();
  }

  protected function cabeceira($df = null) {
    $this->nf = $this->rbd->numFilas();

    return "";
  }

  protected function linha_detalle($df, $f) {
    //~ echo "<pre>" . print_r($f, true) . "</pre>";

    $id_opcion        = $f['id_opcion'];
    $id_paxina        = $f['id_paxina'];
    $id_paxina_select = $this->xestor->id_oms_select();

    $id_rama = "{$id_opcion}@{$f['tipo']}";

    $this->rama_check($id_rama);


    $loms = $this->__loms($f['nome_paxina'], $f['id_paxina'], $f['tipo'], []);

    Btnr_loms::config($loms, $this->nf, $this->ct_lineas, $f);


    $ico = self::__ico($f['tipo']);

    $html = "<tr>
              <td width=23px align=center style='min-width: 23px;'>" . $this->__bmais_html($id_rama) . "</td>
              <td width=17px style='padding: 5px 0; min-width: 23px;'>
                <img src='{$ico}' width='17px' />
              </td>
              <td width=* style='padding-left: 7px;'>
                " . $loms->html() . "
              </td>
            </tr>";

    if (($rama_html = $this->rama_html($id_rama)) == null) return $html;

    return "{$html}
            <tr valign=top>
              <td colspan=3 style='border-left: 7px solid #cccccc; padding: 5px 0;'>
                {$rama_html}
              </td>
            </tr>";
  }

  final static public function __ico($tipo) {
    switch ($tipo) {
      case "libre":
        return Ico::xpax_libre;

      case "album":
        return Ico::xpax_album;

      case "novas":
      case "novas2":
        return Ico::xpax_novas;

      case "cxcli":
      case "marcas":
      case "ventas":
      case "ventas2":
        return Ico::xpax_ventas;

      case "ref":
        return Ico::xpax_ref;
    }

    return Ico::xpax_reservada;
  }

  protected function __loms(string $txt, int $id_paxina, string $tipo, array $_param) {
    return $this->__fslControl(self::__etq($txt, $tipo, $id_paxina, $_param), 
                               $this->__loms_sparam($id_paxina, $tipo, $_param)
                              );
  }

  final protected static function __loms_sparam(int $id_paxina, string $tipo, array $_param):string {
    $sep = Escritor_html::csubnome;
    
    $sparam = "{$tipo}{$sep}{$id_paxina}";
    
    if ($_param == []) return $sparam;
    
    
    foreach ($_param as $p) $sparam .= "{$sep}{$p}";
    
    return $sparam;
  }

  final public static function __etq(string $txt, string $tipo, int $id_paxina, array $_param):Control {
    $txt = Valida::split($txt, 33);

/*
    if ($tipo == "ref") {
      $paxref = Paxina_obd::inicia(new FS_cbd(), $id_paxina, "ref");

      $txt2 = $paxref->atr("nome")->valor;

      if (strlen($txt2) > 33) $txt2 = substr($txt2, 0, 29) . " ...";

      $txt = "{$txt}<br />&rarr;&nbsp;{$txt2}";
    }
*/


    $b = new Span("op", $txt);  

    $b->pon_atr("tipo", $tipo     );
    $b->pon_atr("id2" , $id_paxina);
    
    $i = 3;
    foreach ($_param as $p) {
      $b->pon_atr("id{$i}", $p);
      
      $i++;
    }

    $b->style("default", "cursor: pointer; ");

    $b->pon_eventos("onmouseover", "this.style.textDecoration = 'underline'");
    $b->pon_eventos("onmouseout" , "this.style.textDecoration = 'initial'"  );

    $b->title = "Pincha aquí";


    return $b;
  }
}

//*****************************

class Btnr_loms {
  public $id_base;
  public $id_opcion;
  public $id_paxina;
  public $posicion;
  public $pai;
  public $tipo;

  public $be  = false;
  public $ba  = false;
  public $bd  = false;
  public $bs  = false;
  public $bsr = false;

  public $bmove_s  = false;
  public $bmove_b  = false;
  public $bmove_sn = false;
  public $bmove_bn = false;

  public static function config(Control $loms, $nf, $i, $f) {
    $btnr = new Btnr_loms();

    $btnr->configurar($nf, $loms->nome_completo(), $i, $f);

    $json = json_encode($btnr);

    $loms->pon_atr("btnr", $json);
    
    $loms->envia_AJAX("onclick");
  }

  public static function config2(Control $loms, $f, $tipo) {
    $btnr = new Btnr_loms();

    $btnr->configurar2($loms->nome_completo(), $f, $tipo);

    $json = json_encode($btnr);

    $loms->pon_atr("btnr", $json);

    $loms->envia_AJAX("onclick");
  }

  private function configurar($nf, $id_loms, $i, $f) {
    $this->id_base   = $id_loms;
    $this->id_opcion = $f['id_opcion'];
    $this->id_paxina = $f['id_paxina'];
    $this->posicion  = $f['posicion'];
    $this->pai       = $f['pai'];
    $this->tipo      = $f['tipo'];

    $this->be = true;

    if ( Paxina_reservada_obd::test($this->tipo) ) return;


    $this->ba = ($this->tipo == "libre");

    $this->bd = ($this->tipo != "ref"  );


    if ($nf > 1) {
      $this->bmove_s = ($i  > 1 );
      $this->bmove_b = ($nf > $i);
    }

    $this->bmove_sn = ($this->pai != null);
    $this->bmove_bn = ($i         >  1   );

    if     ($nf        >  1   ) $this->bs = true;
    elseif ($this->pai != null) $this->bs = true;
  }

  private function configurar2($id_loms, $f, $tipo) {
    $this->id_base   = $id_loms;
    $this->id_opcion = $f['id_opcion'] ?? null;
    $this->id_paxina = $f['id_paxina'];
    $this->posicion  = $f['posicion'] ?? null;
    $this->pai       = $f['pai'     ] ?? null;
    $this->tipo      = $tipo;

    $this->be = true;

    switch ($tipo) {
      case "novas":

        $this->bd = true;
        $this->bs = true;

        break;

      case "novas2":

        $this->be = $f['enlace_tipo'] == 2;
        $this->bd = true;

        break;

      case "ventas2cat":

        $this->be = false;

        break;
    }
  }
}

