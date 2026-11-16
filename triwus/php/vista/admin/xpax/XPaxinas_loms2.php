<?php


abstract class XPax_loms2 extends FSL_navelmt {
  public $tipo;
  public $id_idioma;
  public $id_pax_select;

  public function __construct($tipo, $id_opcion, $id_idioma) {
    parent::__construct("tPaxinas", new XPax_loms2_ehtml());

    $this->tipo      = $tipo;
    $this->id_idioma = $id_idioma;

    $w_idioma = ($id_idioma == null)?"id_idioma is null":"id_idioma = '{$id_idioma}'";

    $this->selectfrom = "select id_opcion, id_idioma, id_paxina, tipo, nome_paxina, posicion, pai from v_site_opcion";
    $this->where = "id_opcion = {$id_opcion} and {$w_idioma}";
    $this->orderby = "creado DESC";

    $this->limit_f = 10;
  }

  public function select_idoms($id_oms = null, $id_idioma = null, $id_pax = null) {
    $this->id_idioma     = $id_idioma;
    $this->id_pax_select = $id_pax;
  }

  public function inicia_where() {}

  public function operacion(EstadoHTTP $e) {
    $evento = $e->evento();

//~ echo "xpax-2:" . $evento->html(); //* ollo ajax

    if ($this->control_fslevento($evento, "op")) {
      $_ev = explode(Escritor_html::cnome   , $evento->tipo());

      if (isset($_ev[1])) $_p = explode(Escritor_html::csubnome, $_ev[1]);
      
      switch ( $_ev[0] ) {          
        case "onclick":
          return $e->obxeto("xpax")->operacion_idoms($e, $evento->subnome(0), $evento->subnome(1), $evento->subnome(2));


        case "ba":
          return $e->obxeto("xpax")->obxeto("cpaxnova")->operacion_bpaxnova($e, $_p[0]);
          
        case "bd":
          return $e->obxeto("xpax")->obxeto("cpaxdupli")->operacion_bdupli($e, $_p[0]);
          
        case "be":
          return $e->op_editar($_p[0], $_p[1]);
          
        case "bs":
          return $e->obxeto("xpax")->obxeto("cpaxsup")->operacion_bsup($e, $_p[0], ($_p[1] == "ref"));

      }
    }


    return parent::operacion($e);
  }

  public function sublista_prototipo($id_rama) {
    return null;
  }

}

//-------------------

class XPax_loms2_ehtml extends XPax_loms_ehtml {
  public function __construct() {
    parent::__construct();
  }

  protected function linha_detalle($df, $f) {
    $ico  = XPax_loms_ehtml::__ico( $this->xestor->tipo );

    $loms = $this->__loms($f['titulo'], $f['id_paxina'], $this->xestor->tipo, []);

    Btnr_loms::config2($loms, $f, $this->xestor->tipo);

    return "<tr>
              <td width=23px align=center style='min-width: 23px;'>" . $this->__bmais_html($f['id_paxina']) . "</td>
              <td width=17px style='padding: 5px 0; min-width: 23px;'>
                <img src='{$ico}' width='17px' />
              </td>
              <td width=* style='padding-left: 7px;'>" . $loms->html() . "</td>
            </tr>";
  }

  protected function totais() {
    return "
            <tr style='color: #707070;'>
              <td colspan=7 style='padding-left: 23px;'>" . $this->__paxinador_html() . "</td>
            </tr>";
  }

}

//**************************

final class XPax_loms2_novas extends XPax_loms2 {

  public function __construct($id_opcion, $id_idioma) {
    parent::__construct("novas", $id_opcion, $id_idioma);

    $this->selectfrom = "select * from v_novas";
  }
}

//**************************

final class XPax_loms2_novas2 extends XPax_loms2 {

  public function __construct($id_opcion, $id_idioma) {
    parent::__construct("novas2", $id_opcion, $id_idioma);

    $this->selectfrom = "select * from v_novas";
    $this->orderby    = "pos DESC";
  }
}

//**************************

final class XPax_loms2_ventas extends XPax_loms2 {

  public function __construct($id_opcion, $id_idioma) {
    parent::__construct("ventas", $id_opcion, $id_idioma);

    $this->selectfrom = "select * from v_ventas";
  }
}

//**************************

final class XPax_loms2_ventas2 extends XPax_loms2 {

  public function __construct($id_opcion, $id_idioma) {
    parent::__construct("ventas2cat", $id_opcion, $id_idioma);
    
    $this->__ehtml(new XPax_loms2_ventas2_ehtml());

    $this->selectfrom = "select  a.id_paxina, c.id_site, c.id_categoria, c.nome as titulo, d.h1 as meta_h1, d.titulo as meta_titulo, d.descri as meta_descri
  from v_site_paxina a inner join paragrafo b on (a.tipo = 'ventas2' and a.id_prgf_indice > 0 and a.id_prgf_indice = b.id_paragrafo) 
                       inner join categoria c on (a.id_site = c.id_site and b.iventas_categoria = c.id_pai)
                       left  join paxcat    d on (a.id_paxina = d.id_paxina and c.id_categoria = d.id_categoria )";

    //~ $this->selectfrom = "select  a.id_paxina, c.id_site, c.id_categoria, c.nome as titulo, d.h1 as meta_h1, d.titulo as meta_titulo, d.descri as meta_descri
  //~ from v_site_paxina a inner join paragrafo b on (a.tipo = 'ventas2' and a.id_prgf_indice > 0 and a.id_prgf_indice = b.id_paragrafo) 
                       //~ inner join categoria c on (a.id_site = c.id_site and b.iventas_categoria = c.id_pai)";

    $this->where = "a.id_oms = {$id_opcion} and a.id_idioma = '{$id_idioma}'";
    $this->orderby = "c.nome ASC";
  }
}

//-------------------

class XPax_loms2_ventas2_ehtml extends XPax_loms2_ehtml {
  protected function linha_detalle($df, $f) {
    $ico  = XPax_loms_ehtml::__ico( $this->xestor->tipo );

    $loms = $this->__loms($f['titulo'], $f['id_paxina'], $this->xestor->tipo, [ $f["id_categoria"] ]);

    Btnr_loms::config2($loms, $f, $this->xestor->tipo);

    return "<tr>
              <td width=23px align=center style='min-width: 23px;'>" . $this->__bmais_html($f['id_paxina']) . "</td>
              <td width=17px style='padding: 5px 0; min-width: 23px;'>
                <img src='{$ico}' width='17px' />
              </td>
              <td width=* style='padding-left: 7px;'>" . $loms->html() . "</td>
            </tr>";
  }
}
