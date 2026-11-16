<?php


final class Cxpromos extends Componente implements ICPC {
  const ptw_0 = "ptw/paneis/pcontrol/ventas/promos/cxpromos.html";

  public $id_site = null;

  public function __construct($id_site) {
    parent::__construct("cpromos", self::ptw_0);

    $this->id_site = $id_site;


    $this->pon_obxeto(Panel_fs::__bmais("bAlta", "Crear una promoción", "A&ntilde;adir una nueva promoci&oacute;n"));


    $this->pon_obxeto( new Cxpromos_ficha($id_site) );
    $this->pon_obxeto( new Cxpromos_lista($id_site) );


    $this->obxeto("bAlta")->envia_ajax("onclick");
  }

  public function seleccionado() {
    return $this->obxeto("cpromosf")->seleccionado();
  }

  public function cpc_miga() {
    return "promociones";
  }

  public function operacion(EstadoHTTP $e) {
    if ($this->ptw == null) return null;

    if ($this->obxeto("bAlta")->control_evento()) {
      $this->obxeto("cpromosf")->pon_promo( new Promo_obd($this->id_site) );

      $this->preparar_saida( $e->ajax() );

      return $e;
    }

    if (($e_aux = $this->obxeto("cpromos_lista")->operacion($e)) != null) return $e_aux;

    if (($e_aux = $this->obxeto("cpromosf")->operacion($e)) != null) return $e_aux;


    return null;
  }

  public function operacion_promo(Epcontrol $e, $id_promo = null) {
    $p = Promo_obd::inicia(new FS_cbd(), $this->id_site, $id_promo);

    $fpromo = $this->obxeto("cpromosf");

    $fpromo->pon_promo($p);

    $fpromo->preparar_saida($e->ajax());


    $this->obxeto("cpromos_lista")->preparar_saida($e->ajax());


    return $e;
  }
}

//************************************

final class Cxpromos_ficha extends Componente {
  const ptw_0    = "ptw/paneis/pcontrol/ventas/promos/cxpromosf.html";
  
  private $id_site;
  private $id_promo;

  public function __construct($id_site) {
    parent::__construct("cpromosf", self::ptw_0);
    
    $this->visible = false;

    $this->id_site = $id_site;

    $this->pon_obxeto(new Param("sa_0", "none"));
    $this->pon_obxeto(new Param("sa_1", "none"));

    $this->pon_obxeto(self::__idpromo());

    $this->pon_obxeto(self::__stipo());
    
    $this->pon_obxeto(self::__stipoDesc());

    $this->pon_obxeto(new Param("ct_ap"));
    $this->pon_obxeto(new Param("ct_ar"));

    $this->pon_obxeto(new Span("lcodigo", "Generar código"));

    $this->pon_obxeto(Panel_fs::__text("nome"));
    $this->pon_obxeto(Panel_fs::__text("codigo"));

    $this->pon_obxeto( new DataInput("data_ini") );
    $this->pon_obxeto( new DataInput("data_fin") );

    $this->pon_obxeto( Panel_fs::__text("dto") );
    $this->pon_obxeto( Panel_fs::__text("compra_min") );


    $this->pon_obxeto(Panel_fs::__baceptar());
    $this->pon_obxeto(Panel_fs::__bcancelar());


    $this->obxeto("lcodigo")->clase_css("default", "texto2_a");
    $this->obxeto("lcodigo")->style("default", "cursor: pointer;");
    $this->obxeto("lcodigo")->envia_ajax("onclick");
    //~ $this->obxeto("lcodigo")->envia_submit("onclick");


    $this->obxeto("codigo")->style    ("readonly", "text-align: center;");
    $this->obxeto("codigo")->clase_css("readonly", "text");
    

    $this->obxeto("dto")->style("default", "text-align: right;");

    $this->obxeto("compra_min")->style("default", "text-align: right;");

    $this->obxeto("id_promo")->readonly = true;

    $this->obxeto("bCancelar")->envia_ajax("onclick");
    $this->obxeto("bAceptar" )->envia_ajax("onclick");
  }

  public function seleccionado() {
    return $this->id_promo;
  }

  public function pon_promo(Promo_obd $p) {
    $this->visible  = true;
    
    $this->id_promo = $p->atr("id_promo")->valor;


    $this->obxeto("id_promo")->post($this->id_promo);

    $this->obxeto("stipo")->post( $p->atr("tipo")->valor );
    $this->obxeto("stipo")->readonly = ($this->id_promo != null);

    $this->obxeto("stipoDesc")->post($p->atr("tipo_dto")->valor);
    $this->obxeto("stipoDesc")->readonly = ($this->id_promo != null); //* ???

    //~ $this->obxeto("codigo")->visible = ($this->id_promo == null);
    $this->obxeto("codigo")->post($p->atr("codigo")->valor);

    $this->obxeto("nome" )->post($p->atr("nome")->valor);


    $this->obxeto("data_ini")->post( $p->atr("data_ini")->valor );
    $this->obxeto("data_fin")->post( $p->atr("data_fin")->valor );

    $this->obxeto("dto" )->post($p->atr("dto")->valor);

    if (($c = $p->atr("compra_min")->valor) == null) $c = "0.00";
    $this->obxeto("compra_min")->post($c);
  }

  public function operacion(EstadoHTTP $e) {
    if ($this->ptw == null) return null;

    if ($this->obxeto("lcodigo")->control_evento())  return $this->operacion_lcodigo($e);

    if ($this->obxeto("bAceptar" )->control_evento()) return $this->operacion_bAceptar ($e);
    if ($this->obxeto("bCancelar")->control_evento()) return $this->operacion_bCancelar($e);

    return null;
  }

  public function operacion_lcodigo(Epcontrol $e) {
    $tcodigo = $this->obxeto("codigo");

    $tcodigo->post( strtoupper(substr(md5(uniqid(rand(), true)), 1, 9)) );

    $e->ajax()->pon($tcodigo);


    return $e;
  }

  public function operacion_bCancelar(Epcontrol $e) {
    $this->visible  = false;
    
    $this->id_promo = null;

    $this->pai->preparar_saida($e->ajax());

    return $e;
  }

  private function operacion_bAceptar(Epcontrol $e) {
    if (($erro = $this->validar()) != null) {
      $e->post_msx($erro);

      $e->ajax()->pon( $this->obxeto("dto") );
      $e->ajax()->pon( $this->obxeto("compra_min") );

//~ echo "<pre>" . print_r($e->ajax(), true) . "</pre>----------------------------------------\n";

      return $e;
    }

    $cbd = new FS_cbd();

    $cbd->transaccion(true);

    $a = $this->promo_obd($cbd);

    if (!$a->update($cbd)) {
      $e->post_msx("Sucedió un error al actualizar la BD.");

      $e->ajax()->pon( $this->obxeto("dto"       ) );
      $e->ajax()->pon( $this->obxeto("compra_min") );

      $cbd->rollback();

      return $e;
    }

    //~ $cbd->rollback();
    $cbd->commit();

    
    $e->post_msx("Promoción actualizada correctamente.", "m");


    $this->pon_promo($a);

    $this->pai->preparar_saida($e->ajax());


    return $e;
  }

  private function promo_obd(FS_cbd $cbd = null) {
    if ($cbd == null) $cbd = new FS_cbd();

    $p = Promo_obd::inicia($cbd, $this->pai->id_site, $this->id_promo);

    $td = $this->obxeto("stipoDesc")->valor();
    $d  = trim($this->obxeto("dto")->valor());
    $cm = trim($this->obxeto("compra_min")->valor());

    $p->atr("tipo"      )->valor = $this->obxeto("stipo")->valor();
    $p->atr("tipo_dto"  )->valor = $td;
    $p->atr("nome"      )->valor = trim($this->obxeto("nome")->valor());
    $p->atr("dto"       )->valor = $d;
    $p->atr("codigo"    )->valor = trim($this->obxeto("codigo")->valor());

    if (($td == "a") && ($cm < $d)) $cm = $d; 

    $p->atr("compra_min")->valor = $cm;

    $p->atr("data_ini")->valor = $this->obxeto("data_ini")->valor();
    $p->atr("data_fin")->valor = $this->obxeto("data_fin")->valor();


    return $p;
  }

  private function validar() {
    $erro = "";

    if ($this->obxeto("stipo")->valor() == "**"   ) $erro .= "Debes seleccionar el tipo de promoci&oacute;n.<br />";

    if (trim($this->obxeto("nome")->valor()) == "") $erro .= "Debes teclear el nombre de la promoci&oacute;n.<br />";

    if ($this->obxeto("data_ini")->valor() == ""  ) $erro .= "Debes teclear la fecha de inicio de la promoci&oacute;n.<br />";

    if ($this->obxeto("data_fin")->valor() == ""  ) $erro .= "Debes teclear la fecha final de la promoci&oacute;n.<br />";

    $dto = Valida::numero($this->obxeto("dto")->valor(), 0);

    if ($dto == 0) $erro .= "Debes teclear un % de descuento mayor que cero.<br />";

    $this->obxeto("dto")->post($dto);


    $c = Valida::numero($this->obxeto("compra_min")->valor(), 2);

    $this->obxeto("compra_min")->post($c);



    return $erro;
  }

  private static function __idpromo() {
    $t = Panel_fs::__text("id_promo", 11, 9, "000000000");

    $t->title    = "... N&uacute;mero que identifica el producto";
    $t->readonly = true;


    return $t;
  }

  private static function __stipo() {
    $s = new Select("stipo", Promo_obd::a_tipo(false));

    return $s;
  }

  private static function __stipoDesc() {
    $s = new Select("stipoDesc", Promo_obd::a_tipoDesc(false));

    return $s;
  }

}

//************************************

final class Cxpromos_lista extends FS_lista {
  public $id_site;
  
  public function __construct($id_site) {
    parent::__construct("cpromos_lista", new Cxpromos_ehtml());

    $this->id_site    = $id_site;

    $this->selectfrom = "select * from v_promo a";
    $this->where      = "a.id_site = {$this->id_site}";
    $this->orderby    = "a.id_promo";

    $this->limit_f    = 10;
  }

  public function sublista_prototipo($id_rama) {
    if ($id_rama == null) return null;


    list($id_grupo, $id_promo) = explode("@", $id_rama);

    return new CXA_lista_2($this->id_site, $id_grupo, $id_promo);
  }

  public function __where($where = null) {
    $this->where  = "a.id_site = {$this->id_site}";

    if ($where == null) return;

    $this->where .= " and {$where}";

    //~ echo $this->sql_vista() . "<br>";
  }

  public function seleccionado() {
    return $this->pai->seleccionado();
  }

  public function operacion(EstadoHTTP $e) {
    $evento = $e->evento();

    //~ echo $evento->html();

    if ($this->control_fslevento($evento, "lpromo")) return $this->pai->operacion_promo($e, $evento->subnome(0));
    if ($this->control_fslevento($evento, "borrar")) return $this->operacion_bborrar   ($e, $evento->subnome(0));


    return parent::operacion($e);
  }

  private function operacion_bBorrar(Epcontrol $e, $id_promo) {
    $cbd = new FS_cbd();

    $cbd->transaccion();

    $p = Promo_obd::inicia($cbd, $this->pai->id_site, $id_promo);

    if (!$p->delete($cbd)) {
      $cbd->rollback();

      $e->post_msx("Sucedió un error al actualizar la BD.");

      $this->preparar_saida($e->ajax());

      return $e;
    }

    $cbd->commit();


    if ($this->seleccionado() == $id_promo) return $this->pai->obxeto("cpromosf")->operacion_bCancelar($e);

    $this->preparar_saida($e->ajax());

    return $e;
  }

  protected function html_baleiro() {
    if (!$this->visible) return "";

    return "<div class='lista_baleira txt_adv'>No se encontraron coincidencias.</div>";
  }

}

//-----------------------------------

final class Cxpromos_ehtml extends FS_ehtml {
  private $a_tipo = null;

  public function __construct() {
    parent::__construct();

    $this->a_tipo = Promo_obd::a_tipo();

    $this->style_table = "border: 1px #aaa solid;";
    $this->align       = "center";
    $this->width       = "100%";
    $this->cellspacing = 0;
    $this->cellpadding = 0;
    $this->border      = 0;
  }

  protected function cabeceira($df = null) {
    return "<tr>
              <th style='text-align:center;'>" . $this->__lOrdenar_html("id_promo", "#Promo") . "</th>
              <th>" . $this->__lOrdenar_html("tipo", "Tipo") . "</th>
              <th>" . $this->__lOrdenar_html("nome", "Nombre") . "</th>
              <th style='text-align:center;'>Activa</th>
              <th>" . $this->__lOrdenar_html("data_ini", "Inicio") . "</th>
              <th>" . $this->__lOrdenar_html("data_fin", "&nbsp;Fin&nbsp;") . "</th>
              <th>" . $this->__lOrdenar_html("codigo", "Codigo") . "</th>
              <th style='text-align:right;'>" . $this->__lOrdenar_html("compra_min", "Compra&nbsp;m&iacute;n") . "</th>
              <th style='text-align:right;'>" . $this->__lOrdenar_html("dto", "&nbsp;Dto&nbsp;") . "</th>
              <th>&nbsp;</th>
            </tr>";
  }

  protected function linha_detalle($df, $f) {
    $style_tr = ($this->xestor->seleccionado() != $f['id_promo'])?FS_ehtml::style_tr:FS_ehtml::style_tr_a;

    $_dto = self::__dto($f);

    return "<tr  {$style_tr} >
              <td align=center>" . self::__lpromo($this, $f) . "</td>
              <td>{$this->a_tipo[$f['tipo']]}</td>
              <td>{$f['nome']}</td>
              <td align=center>" . self::__activa($f) . "</td>
              <td>" . self::__data($f['data_ini']) . "</td>
              <td>" . self::__data($f['data_fin']) . "</td>
              <td>{$f['codigo']}</td>
              <td align=right>{$f['compra_min']}&nbsp;&euro;</td>
              <td align=right title='{$_dto[2]}'>{$_dto[0]}&nbsp;{$_dto[1]}</td>
              <td align=right>" . self::__bborrar($this, $f) . "</td>
            </tr>";
  }

  protected function totais() {
    return "<tr >
              <td align=center colspan=11>" . $this->__paxinador_html() . "</td>
            </tr>";
  }

  private static function __lpromo(Cxpromos_ehtml $ehtml, $f) {
    $b = new Div("lpromo", Articulo_obd::ref($f['id_promo'], 6));

    $b->clase_css("default", "lista_elemento_a");

    $b->title = "Editar promoción";

    $b->envia_ajax("onclick");
    //~ $b->envia_submit("onclick");

    return $ehtml->__fslControl($b, $f['id_promo'])->html();
  }

  private static function __dto($f) {
    if ($f['tipo_dto'] == "a") {
      $dto        = number_format($f['dto'], 2, ",", ".");
      $tdto       = "€";
      $tdto_title = "{$dto} (€) euros";
    }
    else {
      $dto  = number_format($f['dto'], 0, ",", ".");

      if ($f['tipo_dto'] == "p") {
        $tdto       = "%";
        $tdto_title = "{$dto}% por ciento";
      }
      else {
        $tdto       = "&starf;";
        $tdto_title = "{$dto} regalo(s)";
      }
    }


    return array($dto, $tdto, $tdto_title);
  }

  private static function __activa($f) {
    if ($f["activa"] == 1) return "<span style='color: #00ba00; font-size: 2.3em;'>&bull;</span>";

    return "<span style='color: #ba0000; font-size: 2.3em;'>&bull;</span>";
  }

  private static function __bborrar(Cxpromos_ehtml $ehtml, $f) {
    if ($ehtml->xestor->readonly) return "";

    $id2 = Articulo_obd::ref($f['id_promo'], 4);

    $b = Panel_fs::__bsup("borrar", null, "Eliminar la promocion id={$id2}");

    $m = "¿ Quieres eliminar la promocion id={$id2} ?";

    //~ $b->envia_ajax("onclick", $m);
    $b->envia_submit("onclick", $m);

    return $ehtml->__fslControl($b, $f['id_promo'])->html();
  }
  
  public static function __data($d, $nulo = "&nbsp;--&nbsp;") {
    if ($d == null) return $nulo;

    list($Y, $m, $d) = explode("-", substr($d, 0, 10));


    return "{$d}-{$m}-{$Y}";
  }
}

