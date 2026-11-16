<?php


final class Cxcategoria extends Componente implements ICPC {
  const ptw_1 = "ptw/paneis/pcontrol/ventas/cxcategoria.html";

  public $id_site = null;

  public function __construct($id_site) {
    parent::__construct("cxcategoria", self::ptw_1);

    $this->id_site = $id_site;

    $this->pon_obxeto(new CXCat_ficha($id_site));
    $this->pon_obxeto(new CXCat_lista($id_site));

    $this->pon_obxeto(new Param("msx"));
  }

  public function cpc_miga() {
    return "categoría";
  }

  public function seleccionado() {
    return $this->obxeto("cxcatf")->seleccionado();
  }

  public function operacion(EstadoHTTP $e) {
    if (($e_aux = $this->obxeto("cxcatf")->operacion($e)) != null) return $e_aux;
    if (($e_aux = $this->obxeto("cxcatl")->operacion($e)) != null) return $e_aux;


    return null;
  }

  //~ public function recarga_categoria(Epcontrol $e, $id_categoria = null) {
    //~ $this->obxeto("cxaf")->reinicia();
  //~ }

  public function operacion_categoria(Epcontrol $e, $id_categoria = null) {
    $a = Categoria_obd::inicia(new FS_cbd(), $this->id_site, $id_categoria);

    $this->obxeto("cxcatf")->post_categoria($a);


    $this->preparar_saida($e->ajax());

    return $e;
  }

  public function operacion_bborrar(Epcontrol $e, $id_categoria = null) {
    if ($id_categoria == null) return $e;

    $cbd = new FS_cbd();

    $cbd->transaccion();

    $c = Categoria_obd::inicia($cbd, $this->id_site, $id_categoria);

    if (!$c->delete($cbd)) {
      $cbd->rollback();

      return $e;
    }

    //~ $cbd->rollback();
    $cbd->commit();

    $this->obxeto("cxcatl")->control_limits_sup();


    return $e;
  }

  public function operacion_bmais(Epcontrol $e, $id_pai) {
    $o = Categoria_obd::inicia(new FS_cbd(), $this->id_site, $id_pai );

    $this->obxeto("cxcatf")->post_categoria_alta($o);


    $this->preparar_saida($e->ajax());

    return $e;
  }


  public function update(FS_cbd $cbd, Articulo_obd $a, $a_campos = null) {
    //~ if ($a_campos == null) $a_campos = $this->obxeto("cxaf")->a_campos();

    //~ if ($a->atr("id_categoria")->valor == null) return $a->insert($cbd, $a_campos);


    //~ return $a->update($cbd, $a_campos);
    return true;
  }
}

//************************************

final class CXCat_ficha extends    Componente {
                          
  const ptw_0 = "ptw/paneis/pcontrol/ventas/cxcategoria_ficha.html";

  private $id_categoria;
  private $nivel;

  public function __construct($id_site) {
    parent::__construct("cxcatf", self::ptw_0);

    $this->visible = false;


    $this->pon_obxeto(Panel_fs::__text("nome", 22, 55, " "));
    $this->pon_obxeto(Panel_fs::__textarea("notas"));
    $this->pon_obxeto(new Select("sCat1", null));


    $this->pon_obxeto(Panel_fs::__baceptar());
    $this->pon_obxeto(Panel_fs::__bcancelar());


    $this->obxeto("nome" )->clase_css("default", "tbuscador");

    $this->obxeto("bCancelar")->envia_ajax("onclick");
    $this->obxeto("bAceptar" )->envia_ajax("onclick");
  }

  public function readonly( $b = true ) {
    $this->obxeto("nome" )->readonly = $b;
    $this->obxeto("sCat1")->readonly = $b;
  }

  public function seleccionado() {
    return $this->id_categoria;
  }

  public function operacion(EstadoHTTP $e) {
    if ($this->obxeto("bAceptar" )->control_evento()) return $this->operacion_bAceptar ($e);
    if ($this->obxeto("bCancelar")->control_evento()) return $this->operacion_bCancelar($e);

    return null;
  }

  public function post_categoria(Categoria_obd $a) {
//~ echo "<pre>" . print_r($a->a_resumo(), 1) . "</pre>";

    $this->visible = true;

    $this->id_categoria = $a->atr("id_categoria")->valor;
    $this->nivel        = $a->atr("nivel"       )->valor;

    $this->readonly( $this->id_categoria == 1 );

    $this->obxeto("sCat1")->sup_opcion();
    $this->obxeto("sCat1")->options( Categoria_obd::_anteriores($this->pai->id_site, $this->id_categoria, null) );

    $this->obxeto("sCat1")->sup_opcion($this->id_categoria); //* eliminamos a categoria que estamos a editar

    $this->obxeto("nome" )->post( $a->atr("nome"  )->valor );
    $this->obxeto("notas")->post( $a->atr("notas" )->valor );
    $this->obxeto("sCat1")->post( $a->atr("id_pai")->valor );
  }

  public function post_categoria_alta(Categoria_obd $pai) {
//~ echo "<pre>" . print_r($pai->a_resumo(), 1) . "</pre>";

    $this->visible = true;

    $this->id_categoria = null;
    $this->nivel        = $pai->atr("nivel")->valor + 1;

    $this->obxeto("sCat1")->sup_opcion();
    $this->obxeto("sCat1")->options( [$pai->atr("id_categoria")->valor => $pai->atr("nome")->valor] );

    $this->obxeto("nome" )->post( "" );
    $this->obxeto("notas")->post( "" );
  }

  public function operacion_bCancelar(Epcontrol $e) {
    $this->visible = false;

    $this->id_categoria = null;

    $this->pai->preparar_saida($e->ajax());

    return $e;
  }

  private function operacion_bAceptar(Epcontrol $e) {
    if (($erro = $this->validar()) != null) {      
      $e->post_msx($erro);

      return $e;
    }

    $cbd = new FS_cbd();

    $cbd->transaccion(true);

    $c = $this->categoria_obd($cbd);

    if (!$c->update($cbd)) {
      $cbd->rollback();

      return $e;
    }


    //~ $cbd->rollback();
    $cbd->commit();


    $this->post_categoria($c);

    //~ $this->pai->preparar_saida($e->ajax());


    return $this->operacion_bCancelar($e);
  }

  private function categoria_obd(FS_cbd $cbd = null) {
    if ($cbd == null) $cbd = new FS_cbd();

    $a = Categoria_obd::inicia($cbd, $this->pai->id_site, $this->id_categoria);

    $a->atr("nivel" )->valor = $this->nivel;
    $a->atr("nome"  )->valor = trim($this->obxeto("nome"  )->valor());
    $a->atr("notas" )->valor = trim($this->obxeto("notas" )->valor());
    $a->atr("id_pai")->valor = $this->obxeto("sCat1")->valor();


    return $a;
  }

  private function validar() {
    $erro = "";

    if (($n = trim($this->obxeto("nome")->valor())) == "") return "Error, debes teclear el nombre de la categoría.";


    return $erro;
  }
}

//******************************************

class CXCat_lista extends  FS_lista {
  public    $id_pai  = null;

  protected $id_site = null;

  public function __construct($id_site, $id_pai = "0") {
    parent::__construct("cxcatl", new CXCat_ehtml());

    $this->id_site    = $id_site;
    $this->id_pai     = $id_pai;

    $this->selectfrom = "select * from categoria";
    $this->where      = "id_site = {$id_site} and id_pai = {$id_pai}";
    $this->orderby    = "id_categoria";

    $this->limit_f    = 10;

    //~ echo $this->sql_vista() . "<br/>";
  }

  public function seleccionado() {
    return $this->pai->seleccionado();
  }

  public function operacion(EstadoHTTP $e) {
    $evento = $e->evento();

//~ echo $evento->html();

    if ($this->control_fslevento($evento, "id_categoria")) {
      return $e->obxeto("cxcategoria")->operacion_categoria($e, $evento->subnome(0));
    }

    if ($this->control_fslevento($evento, "bmais")) {
      return $e->obxeto("cxcategoria")->operacion_bmais($e, $evento->subnome(0));
    }

    if ($this->control_fslevento($evento, "borrar")) {
      return $e->obxeto("cxcategoria")->operacion_bborrar($e, $evento->subnome(0));
    }

    return parent::operacion($e);
  }

  public function sublista_prototipo($id_rama) {
    return new CXCat_lista($this->id_site, $id_rama);
  }
}

//---------------------------------

class CXCat_ehtml extends FS_ehtml {
  const style_td   = "style='padding: 2px 2px 3px 2px;'";
  const style_td_2 = "style='padding: 2px 2px 3px 2px; color: #707070;'";

  public $nf = 0;

  public function __construct() {
    parent::__construct();

    $this->style_table = "";
    $this->align       = "center";
    $this->width       = "100%";
    $this->cellspacing = 0;
    $this->cellpadding = 0;
    $this->border      = 0;
  }

  protected function cabeceira($df = null) {
    return "";
  }

  protected function linha_detalle($df, $f) {
    $id_categoria = $f['id_categoria'];

    $style_tr   = ($this->xestor->seleccionado() != $id_categoria)?FS_ehtml::style_tr:FS_ehtml::style_tr_a;
    $style_td   = self::style_td;
    $style_td_2 = self::style_td_2;

    $this->rama_check($id_categoria);
    
    $bmais = ($f["nivel"] > Categoria_obd::nivel_max)?"":$this->__bmais_html($id_categoria);

    $html = "<tr class='texto3' {$style_tr}>
               <td width=23px align=center style='min-width: 23px;'>{$bmais}</td>
               <td width=* {$style_td_2}>" . Articulo_obd::ref($id_categoria, 4) . " - " . self::__lcategoria($this, $f) . "</td>
               <td width='50%' {$style_td_2}>{$f["notas"]}</td>
               <td width=55px {$style_td_2}>" . self::__bmais($this, $f) . "</td>
               <td width=55px {$style_td_2}>" . self::__bborrar($this, $id_categoria) . "</td>
             </tr>";

    

    if (($rama_html = $this->rama_html($id_categoria)) == null) return $html;
    
        if ($f["nivel"] <  2) $color_bl = "#ccc";
    elseif ($f["nivel"] == 2) $color_bl = "#ddd";
    elseif ($f["nivel"] >  2) $color_bl = "#eee";

    return "{$html}
            <tr valign=top>
              <td colspan=6 style='background-color: #fff; border-left: 7px solid {$color_bl}; padding: 5px 0;'>
                {$rama_html}
              </td>
            </tr>";
  }

  protected function totais() {
    return "<tr>
              <td colspan=6 style='font-size:1px; border-bottom: 1px solid #e6e6e6;'>&nbsp;</td>
            </tr>
            <tr  style='color: #707070;'>
              <td align=center colspan=6 style='white-space: nowrap;'>" . $this->__paxinador_html() . "</td>
            </tr>";
  }

  private static function __lcategoria(CXCat_ehtml $ehtml, $f) {
    if ($f['id_categoria'] == 1) return $f['nome'];

    $b = new Span("id_categoria", $f['nome']);

    $b->clase_css("default", "lista_elemento_a");

    $b->title = "Editar producto";

    $b->envia_ajax("onclick");
    //~ $b->envia_submit("onclick");

    return $ehtml->__fslControl($b, $f['id_categoria'])->html();
  }

  private static function __bmais(CXCat_ehtml $ehtml, $f) {
    if ($ehtml->xestor->readonly               ) return "";
    if ($f["nivel"] > Categoria_obd::nivel_max ) return "";

    $b = Panel_fs::__bmais("bmais", "", "Añadir subcategoría");

    //~ $b->envia_SUBMIT("onclick");
    $b->envia_AJAX  ("onclick");
    
    
    return $ehtml->__fslControl($b, $f["id_categoria"])->html();
  }

  private static function __bborrar(CXCat_ehtml $ehtml, $id) {
    if ($id == 1)                 return "";
    
    if ($ehtml->xestor->readonly) return "";

    $b = Panel_fs::__bsup("borrar", "", "Eliminar la categoría");

    //~ $b->envia_SUBMIT("onclick");
    $b->pon_eventos("onclick", "bborra_ccategoria(this)");

    return $ehtml->__fslControl($b, $id)->html();
  }
}
