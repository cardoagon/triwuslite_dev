<?php

final class CXAF_cc extends     Componente
                    implements ICXAF_cc_lista {

  const ptw_0 = "ptw/paneis/pcontrol/ventas/articulo/cxaf_cc.html";
  const ptw_1 = "ptw/paneis/pcontrol/ventas/articulo/cxaf_cc_alta.html";

  public $id_s  = null;
  public $id_a  = null;
  public $id_g  = null;

  private $tupla = null;

  public function __construct($id_almacen)  {
    parent::__construct("cxafcc", self::ptw_0);

    $this->visible = false;

    $this->pon_obxeto(new CXAF_cc_lista());

    $this->pon_obxeto(Panel_fs::__baceptar());
    $this->pon_obxeto(Panel_fs::__bcancelar());

    $this->pon_obxeto(Panel_fs::__bmais("bmais" , "Añadir campo cústom", "Asigna un campo a medida al producto" ));

    $this->pon_obxeto(new Div("erro"));


    $this->obxeto("erro")->clase_css("default", "texto3_erro");

    $this->obxeto("bAceptar" )->envia_AJAX("onclick");
    $this->obxeto("bCancelar")->envia_AJAX("onclick");
    $this->obxeto("bmais"    )->envia_AJAX("onclick");
  }

  public function pon_articulo(Articulo_obd $a) {
    $this->ptw = self::ptw_0; //* aseguramos esta ptw cando cambiamos de producto.

    $this->id_s  = $a->atr("id_site"    )->valor;
    $this->id_a  = $a->atr("id_articulo")->valor;
    $this->id_g  = $a->atr("id_grupo_cc")->valor;

    $this->tupla = Articulo_cc_obd::_tupla($this->id_s, $this->id_a);

//~ echo "<br>" . print_r($this->tupla, 1) . "<br>";

    $this->obxeto("cxafcc_lista")->config_where(); //* Importante a posición (despóis de asignar valores do articulo_obd $a)


    //*** Configura o botón bmais.

    $this->obxeto("bmais")->visible = ( $this->id_g == null );

/* 20181212. Primeira versión (e candidata). Oculta o botón engadir campo cando o artículo xa foi duplicado.
 *
 *
    $cbd = new FS_cbd();

    $sql = "select id_site, id_grupo_cc, count(id_articulo) as ct
              from v_grupo_cc
             where id_site = {$this->id_s} and id_grupo_cc = {$this->id_g}
          group by id_site, id_grupo_cc";

    $r = $cbd->consulta($sql);

    if (!$_r = $r->next()) {
      $this->obxeto("bmais")->visible = true;

      return;
    }

    $this->obxeto("bmais")->visible = ( $_r['ct'] < 2 );
*/
  }

  public function operacion(EstadoHTTP $e) {
    $this->obxeto("erro")->post(null);

    if ($this->obxeto("bmais"    )->control_evento()) return $this->operacion_bmais    ($e);
    if ($this->obxeto("bAceptar" )->control_evento()) return $this->operacion_bAceptar ($e);
    if ($this->obxeto("bCancelar")->control_evento()) return $this->operacion_bCancelar($e);

    if (($e_aux = $this->obxeto("cxafcc_lista")->operacion($e)) != null) return $e_aux;

    if (($scampo = $this->obxeto("scampo")) != null) if ($scampo->control_evento()) return $this->operacion_scampo($e);


    return null;
  }

  public function operacion_scampo(EstadoHTTP $e) {
    $id_cc_0 = $this->obxeto("scampo")->valor();

    $this->__svalor($id_cc_0);

    $this->preparar_saida($e->ajax());

    return $e;
  }

  public function operacion_bmais(EstadoHTTP $e) {
    $this->ptw = self::ptw_1;

    $this->__scampo();
    $this->__svalor();

    $this->preparar_saida($e->ajax());

    return $e;
  }

  public function operacion_bCancelar(EstadoHTTP $e) {
    $this->ptw = self::ptw_0;

    $this->preparar_saida($e->ajax());

    return $e;
  }

  public function operacion_bAceptar(EstadoHTTP $e) {
//~ echo $e->evento()->html();

    $id_cc_0 = $this->obxeto("scampo")->valor();
    $id_cc_1 = $this->obxeto("svalor")->valor();

//~ echo "aaaaaaaaaaaaaaaaaaaaaaaaaaaaa<br>";

    //* validar
    if (($id_cc_0 == "**") || ($id_cc_1 == "**")) {
      $this->preparar_saida($e->ajax());

      return $e;
    }

    //~ if (($erro = $this->validar_cc()) != null) {
      //~ $this->preparar_saida($e->ajax());

      //~ return $e;
    //~ }

    //* actualizar.
    $cbd = new FS_cbd();

    $cbd->transaccion();

    if (Articulo_cc_obd::insert($cbd, $this->id_s, $this->id_a, $id_cc_0, $id_cc_1, $this->id_g)) {
      Articulo_cc_obd::update_tupla($cbd, $this->id_s, $this->id_a);

      $cbd->commit();

      if ($this->id_g == null) {
        $this->id_g        = $this->id_a;
        //~ $this->id_grupo_cc = $this->id_a;
      }

      $this->obxeto("bmais")->visible = ($this->obxeto("scampo")->count() > 1);
    }
    else
      $cbd->rollback();

    return $this->operacion_bCancelar($e);
  }


  public function id_s() { //* IMPLEMENTA ICXAF_cc_lista
    return $this->id_s;
  }

  public function id_a() { //* IMPLEMENTA ICXAF_cc_lista
    return $this->id_a;
  }

  public function id_g() { //* IMPLEMENTA ICXAF_cc_lista
    return $this->id_g;
  }

  public function __scampo() { //* IMPLEMENTA ICXAF_cc_lista
    $_ = Articulo_cc_obd::_campo_disponible($this->id_s, $this->pai->id_almacen, $this->id_g, "...");

    $s = new Select("scampo", $_);

    $s->envia_ajax("onchange");
    //~ $s->envia_submit("onchange");

    $this->pon_obxeto($s);


    return $s;
  }

  public function __svalor($id_cc_0 = null) { //* IMPLEMENTA ICXAF_cc_lista
    $_ = Articulo_cc_obd::_valor($this->id_s, $id_cc_0, "...");

    $s = new Select("svalor", $_);

    $this->pon_obxeto($s);


    return $s;
  }

  public function tupla_validar($_t = null, FS_cbd $cbd = null) {
    if ($_t  == null) $_t  = $this->tupla_2();
    if ($cbd == null) $cbd = new FS_cbd();

    if ( Articulo_cc_obd::hai_tupla($this->id_s, $this->id_g, $_t, $cbd) == null ) return null;

    return "ERROR, selecciona valores para los campos cústom distintos para la tupla.<br />";
  }

  public function tupla_2($id_cc_0 = null, $id_cc_1 = null) {
    $_t0 = $this->tupla;

    if ($id_cc_0 != null) $_t0[$id_cc_0][0] = $id_cc_1;

    $_t1 = null;
    foreach($_t0 as $cc0=>$_v) $_t1[ $cc0 ] = $_v[0];

    return $_t1;
  }
}

//*********************************

final class CXAF_cc_lista extends FS_lista {

  public $alta     = false;

  public $id_cc_0  = null;
  public $id_cc_1  = null;

  public function __construct($alta = false) {
    parent::__construct("cxafcc_lista", new CXAF_cc_ehtml());

    $this->alta       = $alta;

    $this->selectfrom = "select * from v_grupo_cc";
    $this->where      = "1=0";
    $this->orderby    = "id_cc_0";

    $this->pon_obxeto( Panel_fs::__baceptar (null) );
    $this->pon_obxeto( Panel_fs::__bcancelar(null) );

    $this->obxeto("bAceptar" )->style("default", "padding: 0;");
    $this->obxeto("bCancelar")->style("default", "padding: 0;");

    $this->obxeto("bAceptar" )->envia_AJAX("onclick");
    $this->obxeto("bCancelar")->envia_AJAX("onclick");
  }

  public function config_where() {
    $this->selectfrom = "select * from v_grupo_cc";
    $this->where      = "id_site = " . $this->pai->id_s() . " and id_articulo = " . $this->pai->id_a();
    $this->orderby    = "id_cc_0";
  }

  public function operacion(EstadoHTTP $e) {
    $evento = $e->evento();

//~ echo $evento->html();

    if ( $this->obxeto("bCancelar")->control_evento() ) return $this->operacion_editar ($e, null);
    if ( $this->obxeto("bAceptar" )->control_evento() ) return $this->operacion_aceptar($e);

    if ( $this->control_fslevento($evento, "beditar") ) return $this->operacion_editar($e, $evento->subnome());


    return parent::operacion($e);
  }

  private function operacion_editar(EstadoHTTP $e, $_id) {
//~ echo "_id<pre>" . print_r($_id, true) . "</pre>";
    $this->id_cc_0 = (is_array($_id))?$_id[0]:null;
    $this->id_cc_1 = (is_array($_id))?$_id[1]:null;

    $e->ajax()->pon( $this->pai->obxeto("erro") );

    $this->preparar_saida($e->ajax());

    return $e;
  }

  private function operacion_aceptar(EstadoHTTP $e) {
    $id_cc_1 = $this->pai->obxeto("svalor")->valor();


    $_t  = $this->pai->tupla_2($this->id_cc_0, $id_cc_1);

    if ( ($erro = $this->validar()) != null ) {
      $this->pai->obxeto("erro")->post($erro);

      $e->ajax()->pon( $this->pai->obxeto("erro") );

      return $e;
    }


    $cbd = new FS_cbd();

    $cbd->transaccion();

    $id_cc_1 = $this->pai->obxeto("svalor")->valor();

    if (!Articulo_cc_obd::update($cbd, $this->pai->id_s, $this->pai->id_a, $this->id_cc_0, $id_cc_1)) {
      $cbd->rollback();

      $this->pai->obxeto("erro")->post("Sucedeu un erro ao actualizar a BD.<br />");

      $e->ajax()->pon( $this->pai->obxeto("erro") );

      return $e;
    }

    $cbd->commit();

    return $this->operacion_editar($e, null);
  }

  protected function html_baleiro() {
    if (!$this->visible) return "";

    return "<div class='lista_baleira txt_adv'>No hay campos cústom</div>";
  }

  private function validar() {
    $id_cc_1 = $this->pai->obxeto("svalor")->valor();

    if ($id_cc_1 == "**") return "Erro. Debes seleccionar un valor.<br />";


    $_t  = $this->pai->tupla_2($this->id_cc_0, $id_cc_1);

    return $this->pai->tupla_validar($_t);
  }
}

//---------------------------------

final class CXAF_cc_ehtml extends FS_ehtml {
  public $nf = 0;

  public function __construct() {
    parent::__construct();

    $this->class_table = "lista_00";
    $this->style_table = "border: 1px solid #e6e6e6;";
    $this->width       = "88%";
    $this->align       = "left";
    $this->cellspacing = 0;
    $this->cellpadding = 0;
    $this->border      = 0;
  }

  protected function cabeceira($df = null) {
    $this->nf = 0;
    
    $s = ($this->xestor->alta)?"":$this->__lOrdenar_html("id_cc_1", "#Valor");
    
    return "<tr>
             <th style='width:44px; text-align:center'>" . $this->__lOrdenar_html("id_cc_0", "#Campo") . "</th>
             <th style='width:30%; text-align:left'>"  . $this->__lOrdenar_html("campo"  , "Campo" ) . "</th>
             <th style='width:44px; text-align:center'>{$s}</th>
             <th style='text-align:left;'>"   . $this->__lOrdenar_html("valor"  , "Valor" ) . "</th>
             <th style='width:44px; text-align=center;'></th>
             <th style='width:44px; text-align=center;'></th>
           </tr>";
  }

  protected function linha_detalle($df, $f) {
    $this->nf++;
    
    if ($this->xestor->alta)                     return $this->linha_detalle_alta    ($df, $f);

    if ($this->xestor->id_cc_0 == $f["id_cc_0"]) return $this->linha_detalle_editando($df, $f);


    if ($f["tipo"] == "color") {
      $v = "
          <div style='display: flex;align-items: center;'>
            <div style='margin-right: 0.5em; height: 0.9em; width: 1.9em;
                        border: 1px solid #000; background-color: {$f["valor"]};'></div>
            {$f["notas"]}
          </div>";
    }
    else {
      $v = $f["valor"];
    }

    return "<tr>
              <td align=center>" . Articulo_obd::ref($f["id_cc_0"], 3) . "</td>
              <td>{$f['campo']}</td>
              <td align=center>" . Articulo_obd::ref($f["id_cc_1"], 3) . "</td>
              <td>{$v}</td>
              <td></td>
              <td>" . self::__bEditar($this, $f) . "</td>
            </tr>";
  }

  protected function linha_detalle_alta($df, $f) {
    return "<tr>
              <td align=center>" . Articulo_obd::ref($f["id_cc_0"], 3) . "</td>
              <td>{$f['campo']}</td>
              <td align=center></td>
              <td>" . self::__svalor($this, $f) . "</td>
              <td></td>
              <td></td>
            </tr>";
  }

  protected function linha_detalle_editando($df, $f) {
    return "<tr>
              <td align=center>" . Articulo_obd::ref($f["id_cc_0"], 3) . "</td>
              <td>{$f['campo']}</td>
              <td align=center>" . Articulo_obd::ref($f["id_cc_1"], 3) . "</td>
              <td>" . self::__svalor($this, $f) . "</td>
              <td>" . $this->xestor->obxeto("bCancelar")->html() . "</td>
              <td>" . $this->xestor->obxeto("bAceptar" )->html() . "</td>
            </tr>";
  }

  protected function totais() {
    if ( ($bmais = $this->xestor->pai->obxeto("bmais")) == null ) return;
    
    $bmais->visible = $bmais->visible && ($this->nf < 1);
  }

  private static function __bEditar(CXAF_cc_ehtml $ehtml, $f) {
    $b = Panel_fs::__beditar("beditar", null);

    $b->style("default", "padding: 0;");

    $b->envia_ajax("onclick");

    return $ehtml->__fslControl($b, self::kcc($f))->html();
  }

  private static function __snome(CXAF_cc_ehtml $ehtml, $f) {
    $snome = $ehtml->xestor->pai->__snome();

    if ($ehtml->xestor->alta)
      $ehtml->xestor->pai->pon_obxeto($snome, $f["id_cc_0"]);

    return $snome->html();
  }

  private static function __svalor(CXAF_cc_ehtml $ehtml, $f) {
    if ($ehtml->xestor->alta) {
      $kcc = self::kcc($f);

      $svalor = $ehtml->xestor->pai->obxeto("svalor", $kcc);

      if ($svalor == null) {
        $svalor = $ehtml->xestor->pai->__svalor($f["id_cc_0"]);

        $ehtml->xestor->pai->pon_obxeto($svalor, $kcc);
      }


      return $svalor->html();
    }


    $svalor = $ehtml->xestor->pai->__svalor($f["id_cc_0"]);

    return $svalor->html();
  }

  private static function kcc($f) {
    return $f['id_cc_0'] . Escritor_html::csubnome . $f['id_cc_1'];
  }
}
