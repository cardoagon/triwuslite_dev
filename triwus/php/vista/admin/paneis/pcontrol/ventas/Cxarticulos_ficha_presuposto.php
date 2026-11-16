<?php


final class CXAF_prpsto_paso_lista extends FS_lista {
  const ptw_1 = "ptw/paneis/pcontrol/ventas/articulo/cxaf_presuposto_1.html";
  const ptw_2 = "ptw/paneis/pcontrol/ventas/articulo/cxaf_presuposto_2.html";

  public $id_site;
  public $id_articulo;

  public function __construct() {
    parent::__construct("cxafpto", new CXAFppl_ehtml(), self::ptw_1);

    $this->visible = false;

    $this->selectfrom = "select * from prsto_paso";
    $this->where      = "1 = 0";
    $this->orderby    = "posicion";


    $this->pon_obxeto( self::__lupmais() );

    $this->pon_obxeto( new CXAFpaso() );
  }

  public function pon_articulo(Articulo_obd $a) {
    $this->ptw = self::ptw_1;

    $this->id_site     = $a->atr("id_site"    )->valor;
    $this->id_articulo = $a->atr("id_articulo")->valor;

    $this->where = "id_site = {$this->id_site} and id_articulo = {$this->id_articulo}";
  }

  public function operacion(EstadoHTTP $e) {
    if ($this->obxeto("lupmais")->control_evento()) return $this->operacion_paso($e, null);

    if (($e_aux = $this->obxeto("cxaf_paso")->operacion($e)) != null) return $e_aux;

    $evento = $e->evento();

    //~ echo $evento->html();

    if ($this->control_fslevento($evento, "lnome")) return $this->operacion_paso($e, $evento->subnome(0));

    if ($this->control_fslevento($evento, "lsup")) return $this->operacion_lsup($e, $evento->subnome(0));

    if ($this->control_fslevento($evento, "lbaixar")) return $this->operacion_lbaixar($e, $evento->subnome(0));

    if ($this->control_fslevento($evento, "lsubir")) return $this->operacion_lsubir($e, $evento->subnome(0));

    return parent::operacion($e);
  }

  public function sublista_prototipo($id_rama) {
    list($id_paso, $tipo) = explode("@", $id_rama);

    return new CXAF_prpsto_opcion_lista($this->id_site, $this->id_articulo, $id_paso, $tipo);
  }

  protected function html_baleiro() {
    return "<div class='lista_baleira txt_adv'>
              No est&aacute;n definidos los pasos del presupuesto.
              <br /><br />
              " . $this->obxeto("lupmais")->html() . "
            </div>";
  }

  private function operacion_paso(Epcontrol $e, $id_paso = null) {
    $p = $this->obxeto("cxaf_paso");
    $t = ($id_paso == null)?"Presupuesto. Añadir un paso.":"Presupuesto. Editar paso.";
    $v = $e->obxeto("vm_pcontrol_aux");

    $p->pon_paso($id_paso);

    //~ $v->operacion_ficha($e, $p, $t);

    //~ return $v->operacion_abrir($e);

    return $v->operacion_ficha($e, $p, $t);
  }

  private function operacion_lsup(Epcontrol $e, $id_paso) {
    $o = new Presuposto_paso_obd($this->id_site, $this->id_articulo, $id_paso);

    $cbd = new FS_cbd();

    $cbd->transaccion();

    if (!$o->delete($cbd)) {
      $cbd->rollback();

      return $e;
    }

    $cbd->commit();

    $this->preparar_saida($e->ajax());

    return $e;
  }

  private function operacion_lbaixar(Epcontrol $e, $id_paso) {
    $cbd = new FS_cbd();

    $cbd->transaccion();

    $o = Presuposto_paso_obd::inicia($cbd, $this->id_site, $this->id_articulo, $id_paso);


    if (!$o->baixar($cbd)) {
      $cbd->rollback();

      return $e;
    }

    $cbd->commit();

    $this->preparar_saida($e->ajax());

    return $e;
  }

  private function operacion_lsubir(Epcontrol $e, $id_paso) {
    $cbd = new FS_cbd();

    $cbd->transaccion();

    $o = Presuposto_paso_obd::inicia($cbd, $this->id_site, $this->id_articulo, $id_paso);


    if (!$o->subir($cbd)) {
      $cbd->rollback();

      return $e;
    }

    $cbd->commit();

    $this->preparar_saida($e->ajax());

    return $e;
  }

  private static function __lupmais() {
    $l = Panel_fs::__bmais("lupmais", "Añadir paso", "");

    //~ $l->envia_submit("onclick");
    $l->envia_ajax("onclick");

    return $l;
  }
}

//---------------------------------

final class CXAFppl_ehtml extends FS_ehtml {
  public $nf = 0;
  public $i = 0;

  private $_tipo = [];

  public function __construct() {
    parent::__construct();

    $this->style_table = "border: 1px solid #e6e6e6;";
    $this->align       = "center";
    $this->width       = "100%";
    $this->cellspacing = 0;
    $this->cellpadding = 0;
    $this->border      = 0;
    
    $this->_tipo = Presuposto_carrito_obd::_tipo();
  }

  protected function cabeceira($df = null) {
    $this->i  = -1;
    $this->nf = $this->xestor->__count();

    return "<theader>
            <tr>
              <th colspan=5 style='border: 0; background-color: #fff;'>
                <div class=texto2 style='padding: 17px 0 11px 0;'>Organiza las opciones de tu presupuesto</div>
              </th>
            </tr>
            <tr>
              <th style='width: 3em;'></th>
              <th>" . $this->__lOrdenar_html("nome", "Nombre") . "</th>
              <th>" . $this->__lOrdenar_html("tipo", "Tipo") . "</th>
              <th style='width: 3em;'></th>
              <th style='width: 3em;'></th>
              <th style='width: 3em;'></th>
            </tr>
            </theader>
            <tbody>";
  }

  protected function linha_detalle($df, $f) {
    $this->i++;

    $id_rama = "{$f['id_paso']}@{$f['tipo']}@{$f['id_articulo']}";

    return "<tr>
              <td align=center>" . $this->__bmais_html($id_rama) . "</td>
              <td >" . self::__lnome($this, $f) . "</td>
              <td >{$this->_tipo[$f['tipo']]}</td>
              <td align=right>" . self::__lsubir($this, $f) . "</td>
              <td align=right>" . self::__lbaixar($this, $f) . "</td>
              <td align=right>" . self::__lsup($this, $f) . "</td>
            </tr>
            <tr>
              <td  style='background-color: #fff;'></td>
              <td width=* colspan='5' style='background-color: #fff;'>" . $this->rama_html($id_rama) . "</td>
            </tr>";
  }

  protected function totais() {
    return "</tbody>
            <tfoot>
              <tr>
                <td colspan=9 style='padding: 1.3em; border-top: 1px solid #e6e6e6;'>" . $this->xestor->obxeto("lupmais")->html() . "</td>
              </tr>
            </tfoot>";
  }

  private static function __lnome(CXAFppl_ehtml $ehtml, $f) {
    $l = new Link("lnome", $f['nome']);

    $l->clase_css("default", "texto2_a");

    $l->envia_ajax("onclick");
    //~ $l->envia_submit("onclick");

    return $ehtml->__fslControl($l, $f['id_paso'])->html();
  }

  private static function __lsubir(CXAFppl_ehtml $ehtml, $f) {
    if ($ehtml->i == 0) return "";

    $etq = Panel_fs::__boton_etq("", "<span style='color: #000;font-size: 1.3em;'>&uarr;</span>");

    
    $l = Panel_fs::__button("lsubir", $etq);


    $l->envia_ajax("onclick");
    //~ $l->envia_submit("onclick");

    return $ehtml->__fslControl($l, $f['id_paso'])->html();
  }

  private static function __lbaixar(CXAFppl_ehtml $ehtml, $f) {
    if ($ehtml->i == ($ehtml->nf - 1)) return "";


    $etq = Panel_fs::__boton_etq("", "<span style='color: #000;font-size: 1.3em;'>&darr;</span>");
    
    $l = Panel_fs::__button("lbaixar", $etq);


    $l->envia_ajax("onclick");
    //~ $l->envia_submit("onclick");

    return $ehtml->__fslControl($l, $f['id_paso'])->html();
  }

  private static function __lsup(CXAFppl_ehtml $ehtml, $f) {
    $l = Panel_fs::__bsup("lsup", null);

    $l->envia_ajax("onclick", "&iquest; Deseas eliminar este paso del presupesto ?");

    return $ehtml->__fslControl($l, $f['id_paso'])->html();
  }
}

//**************************************

final class CXAFpaso extends Componente implements IVM_pcontrol_ficha {
  public $id_paso;

  public function __construct() {
    parent::__construct("cxaf_paso", "ptw/paneis/pcontrol/ventas/articulo/cxaf_presuposto_2_paso.html");

    $this->pon_obxeto(CXA_ficha::__text("nome"));
    $this->pon_obxeto(new Textarea("notas", 2, 22));
    $this->pon_obxeto(CXA_ficha::__number("cantidade"));

    $this->pon_obxeto( new Select("stipo", Presuposto_carrito_obd::_tipo()) );

    $this->pon_obxeto(new Span("msx_erro"));

    //~ $this->obxeto("nome")->style("default", "width: 252px;");
    $this->obxeto("notas")->style("default", "width: 97%;");

    //~ $this->obxeto("cantidade")->style("default", "width: 252px;");

    //~ $this->obxeto("stipo")->style("default", "width: 252px;");

    $this->obxeto("msx_erro")->clase_css("default", "tecto3_erro");
  }

  public function operacion(EstadoHTTP $e) {
    $this->obxeto("msx_erro")->post(null);

    return null;
  }

  public function pon_paso($id_paso = null) {
    $this->id_paso = $id_paso;

    if ($id_paso == null) {
      $this->obxeto("nome" )->post(null);
      $this->obxeto("notas")->post(null);
      $this->obxeto("cantidade")->post( 1 );

      return;
    }

    $o = Presuposto_paso_obd::inicia(new FS_cbd(), $this->pai->id_site, $this->pai->id_articulo, $this->id_paso);

    $this->obxeto("nome"     )->post( $o->atr("nome"     )->valor );
    $this->obxeto("notas"    )->post( $o->atr("notas"    )->valor );
    $this->obxeto("cantidade")->post( $o->atr("cantidade")->valor );
    $this->obxeto("stipo"    )->post( $o->atr("tipo"     )->valor );
  }

  public function validar() {
    if ($this->obxeto("nome")->valor() != null) return true;

    $this->obxeto("msx_erro")->post("Debe teclear el campo nombre");

    return false;
  }

  public function operacion_bAceptar(Epcontrol $e) {
    $cbd = new FS_cbd();

    $o = Presuposto_paso_obd::inicia($cbd, $this->pai->id_site, $this->pai->id_articulo, $this->id_paso);

    $o->atr("nome"     )->valor = $this->obxeto("nome")->valor();
    $o->atr("notas"    )->valor = $this->obxeto("notas")->valor();
    $o->atr("cantidade")->valor = $this->obxeto("cantidade")->valor();
    $o->atr("tipo"     )->valor = $this->obxeto("stipo")->valor();

    $cbd->transaccion();

    if (!$o->update($cbd)) {
      $this->obxeto("msx_erro")->post("Sucedi&oacute; un error al guardar en la BD");

      $this->rollback();

      return $this->operacion_bCancelar($e);
    }

    $cbd->commit();

    return $this->operacion_bCancelar($e);
  }

  public function operacion_bCancelar(Epcontrol $e) {
    $this->pai->preparar_saida($e->ajax());

    return $e;
  }
}

//**********************************

final class CXAF_prpsto_opcion_lista extends FS_lista {
  const ptw_1 = "ptw/paneis/pcontrol/ventas/articulo/cxaf_presuposto_1.html";
  const ptw_2 = "ptw/paneis/pcontrol/ventas/articulo/cxaf_presuposto_3.html";

  public $id_paso = null;
  public $tipo    = null;

  public function __construct($id_site, $id_articulo, $id_paso, $tipo) {
    parent::__construct("cxafpto", new CXAFppol_ehtml(), self::ptw_1);

    $this->id_paso = $id_paso;
    $this->tipo    = $tipo;

    $this->selectfrom = "select * from prsto_paso_opcion";
    $this->where      = "id_site = {$id_site} and id_articulo = {$id_articulo} and id_paso = {$id_paso}";
    $this->orderby    = "grupo, id_opcion";

    $this->pon_obxeto( self::__lupomais() );

    $this->pon_obxeto( new CXAFpaso_opcion() );
  }

  public function id_site() {
    return $this->pai->id_site;
  }

  public function id_articulo() {
    return $this->pai->id_articulo;
  }

  public function operacion(EstadoHTTP $e) {
    if ($this->obxeto("lupomais")->control_evento()) return $this->operacion_opcion($e, null);

    if (($e_aux = $this->obxeto("cxafp_opcion")->operacion($e)) != null) return $e_aux;

    $evento = $e->evento();

    //~ echo $evento->html();

    if ($this->control_fslevento($evento, "lnome")) return $this->operacion_opcion($e, $evento->subnome(0));

    if ($this->control_fslevento($evento, "lsup")) return $this->operacion_lsup($e, $evento->subnome(0));


    return parent::operacion($e);
  }

  private function operacion_opcion(Epcontrol $e, $id_opcion = null) {
    $p = $this->obxeto("cxafp_opcion");
    $t = "Presupuesto. Añadir una opción.";
    $v = $e->obxeto("vm_pcontrol_aux");

    $p->pon_opcion($this->tipo, $id_opcion);

    $v->operacion_ficha($e, $p, $t);

    return $v->operacion_abrir($e);
  }

  private function operacion_lsup(Epcontrol $e, $id_opcion) {
    $o = new Presuposto_paso_opcion_obd($this->id_site(), $this->id_articulo(), $this->id_paso, $id_opcion);

    $cbd = new FS_cbd();

    $cbd->transaccion();

    if (!$o->delete($cbd)) {
      $cbd->rollback();

      return $e;
    }

    $cbd->commit();

    $this->preparar_saida($e->ajax());

    return $e;
  }

  protected function html_baleiro() {
    return "<div class='lista_baleira txt_adv'>
              No est&aacute;n definidas opciones para este paso en el presupuesto.
              <br /><br />
              " . $this->obxeto("lupomais")->html() . "
            </div>";
  }

  private static function __lupomais() {
    $l = Panel_fs::__bmais("lupomais", "Añadir opción", "");

    $l->envia_ajax("onclick");

    return $l;
  }
}

//---------------------------------

final class CXAFppol_ehtml extends FS_ehtml {
  public $nf = 0;
  public $i  = 0;

  public function __construct() {
    parent::__construct();

    $this->style_table = "border: 1px solid #e6e6e6;";
    $this->align       = "center";
    $this->width       = "100%";
    $this->cellspacing = 0;
    $this->cellpadding = 0;
    $this->border      = 0;
  }

  protected function cabeceira($df = null) {
    $this->i  = -1;
    $this->nf = $this->xestor->__count();

    return "<thead>
              <tr>
                <th>" . $this->__lOrdenar_html("nome", "Nombre") . "</th>
                <th></th>
                <th>" . $this->__lOrdenar_html("obligado", "Obligatorio") . "</th>
                <th style='text-align: right;'>" . $this->__lOrdenar_html("pvp_suma", "Precio") . "</th>
                <th style='width: 3em;'></th>
              </tr>
            </thead>
            <tbody>";
  }

  protected function linha_detalle($df, $f) {
    $this->i++;

    //~ $not = Valida::split($f['notas'], 17);
    $obr = ($f['obligado'] == 1)?"Sí":"No";

    return "<tr>
              <td >" . self::__lnome($this, $f) . "</td>
              <td ><small>{$f['notas']}</small></td>
              <td >{$obr}</td>
              <td style='text-align: right;'>{$f['pvp_suma']}&euro;</td>
              <td style='text-align: right;'>" . self::__lsup($this, $f) . "</td>
            </tr>";
  }

  protected function totais() {
    return "</tbody>
            <tfoot>
              <tr>
                <td colspan=5 style='padding: 1.3em; border-top: 1px solid #e6e6e6;'>" . $this->xestor->obxeto("lupomais")->html() . "</td>
              </tr>
            </tfoot>";
  }

  private static function __lnome(CXAFppol_ehtml $ehtml, $f) {
    $l = new Link("lnome", $f['nome']);

    $l->clase_css("default", "texto2_a");

    $l->envia_ajax("onclick");
    //~ $l->envia_submit("onclick");

    return $ehtml->__fslControl($l, $f['id_opcion'])->html();
  }


  private static function __lsup(CXAFppol_ehtml $ehtml, $f) {
    $l = Panel_fs::__bsup("lsup", null);

    $l->envia_ajax("onclick", "&iquest; Deseas eliminar esta opci&oacute;n ?");
    //~ $l->envia_submit("onclick", "&iquest; Desea eliminar esta opci&oacute;n ?");

    return $ehtml->__fslControl($l, $f['id_opcion'])->html();
  }
}

//**************************************

final class CXAFpaso_opcion extends Componente implements IVM_pcontrol_ficha {
  const ptw_0  = "ptw/paneis/pcontrol/ventas/articulo/cxaf_presuposto_3_opcion_b.html";
  const ptw_04 = "ptw/paneis/pcontrol/ventas/articulo/cxaf_presuposto_3_opcion_b4.html";
  const ptw_1  = "ptw/paneis/pcontrol/ventas/articulo/cxaf_presuposto_3_opcion_s.html";
  const ptw_2  = "ptw/paneis/pcontrol/ventas/articulo/cxaf_presuposto_3_opcion_t.html";
  const ptw_3  = "ptw/paneis/pcontrol/ventas/articulo/cxaf_presuposto_3_opcion_m.html";

  public $id_opcion;

  public function __construct() {
    parent::__construct("cxafp_opcion", self::ptw_0);

    $this->pon_obxeto(CXA_ficha::__text("nome"));
    $this->pon_obxeto(CXA_ficha::__text("grupo"));
    $this->pon_obxeto(new Textarea("notas", 2, 22));

    $this->pon_obxeto(CXA_ficha::__number("pvp_suma", 1, 999));

    $this->pon_obxeto(new Select("obligado", array("0"=>"No", "1"=>"S&iacute;")));

    $this->pon_obxeto(new Span("msx_erro"));

/*
    $this->obxeto("nome" )->style("default", "width: 252px;");
    $this->obxeto("grupo")->style("default", "width: 252px;");
    $this->obxeto("notas")->style("default", "width: 252px;");
    $this->obxeto("notas")->style("default", "width: 252px;");

    $this->obxeto("pvp_suma")->style("default", "width: 252px;");
*/

    $this->obxeto("msx_erro")->clase_css("default", "tecto3_erro");
  }

  public function operacion(EstadoHTTP $e) {
    $this->obxeto("msx_erro")->post(null);

    if ($this->obxeto("clogoarti_ppo") != null)
      if (($e_aux = $this->obxeto("clogoarti_ppo")->operacion($e)) != null) return $e_aux;

    //~ if ($this->obxeto("bAceptar")->control_evento()) $this->operacion_baceptar($e);

    //~ if ($this->obxeto("bCancelar")->control_evento()) $this->operacion_bcancelar($e);

    return null;
  }

  public function pon_opcion($tipo, $id_opcion = null) {
    $this->id_opcion = $id_opcion;

        if ($tipo == "t" ) $this->ptw = self::ptw_2;
    elseif ($tipo == "d" ) $this->ptw = self::ptw_2;
    elseif ($tipo == "ta") $this->ptw = self::ptw_2;
    elseif ($tipo == "s" ) $this->ptw = self::ptw_1;
    elseif ($tipo == "b4") $this->ptw = self::ptw_04;
    elseif ($tipo == "m" ) $this->ptw = self::ptw_3;
    elseif ($tipo == "r" ) $this->ptw = self::ptw_3;
    else                   $this->ptw = self::ptw_0;


    if ($id_opcion == null) {
      $this->obxeto("nome"    )->post(null);
      $this->obxeto("grupo"   )->post(null);
      $this->obxeto("notas"   )->post(null);
      $this->obxeto("obligado")->post(null);

      $this->obxeto("pvp_suma")->post("0");


      return;
    }

    $o = Presuposto_paso_opcion_obd::inicia(new FS_cbd(), $this->pai->id_site(), $this->pai->id_articulo(), $this->pai->id_paso, $id_opcion);

    $this->obxeto("nome"    )->post( $o->atr("nome"    )->valor );
    $this->obxeto("grupo"   )->post( $o->atr("grupo"   )->valor );
    $this->obxeto("notas"   )->post( $o->atr("notas"   )->valor );
    $this->obxeto("pvp_suma")->post( $o->atr("pvp_suma")->valor );
    $this->obxeto("obligado")->post( $o->atr("obligado")->valor );
    //~ $this->obxeto("paso_seg")->post( $o->atr("paso_seg")->valor );

    if ($tipo[0] != "b") return;

    $this->pon_obxeto( new CLogo_articulo_ppo($o) );
  }

  public function validar() {    
    if ($this->obxeto("nome")->valor() != null) return true;

    $this->obxeto("msx_erro")->post("Debe teclear el campo nombre");

    return false;
  }

  public function operacion_baceptar(Epcontrol $e) {
/*
    if (($erro = $this->validar()) != null) {
      $this->obxeto("msx_erro")->post($erro);

      return $this->operacion_bcancelar($e);
    }
*/
    $cbd = new FS_cbd();

    $o = Presuposto_paso_opcion_obd::inicia($cbd, $this->pai->id_site(), $this->pai->id_articulo(), $this->pai->id_paso, $this->id_opcion);

    $o->atr("nome"    )->valor = $this->obxeto("nome")->valor();
    $o->atr("grupo"   )->valor = $this->obxeto("grupo")->valor();
    $o->atr("notas"   )->valor = $this->obxeto("notas")->valor();
    $o->atr("obligado")->valor = $this->obxeto("obligado")->valor();
    $o->atr("pvp_suma")->valor = abs($this->obxeto("pvp_suma")->valor());


    $cbd->transaccion();

    if (!$o->update($cbd)) {
      $this->obxeto("msx_erro")->post("Sucedi&oacute; un error al guardar en la BD");

      $this->rollback();

      return $this->operacion_bcancelar($e);
    }

    $cbd->commit();

    return $this->operacion_bcancelar($e);
  }

  public function operacion_bcancelar(Epcontrol $e) {
    //~ $this->pai->ptw = CXAF_prpsto_opcion_lista::ptw_1;

    $this->pai->preparar_saida($e->ajax());

    return $e;
  }
}
