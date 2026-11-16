<?php

final class Prgf_inovas2 extends Paragrafo
                      implements IMetatags {
 
  public int    $id_site;
  public int    $id_opcion;
  public string $id_idioma;
                           
  private bool              $editor;
  private array             $_param;
  private Paxina_novas2_obd $p;
  
  public function __construct(Paxina_novas2_obd $p, $editor) {
    parent::__construct($p->prgf_indice());

    $this->alg_html_elmts = 9;
    $this->usa_bpos       = false;
    $this->excluir        = 1;
    
    $this->id_site        = $p->atr("id_site"  )->valor;
    $this->id_opcion      = $p->atr("id_opcion")->valor;
    $this->id_idioma      = $p->atr("id_idioma")->valor;
    $this->editor         = $editor;
    
    //* config ICatalogo_fonte.
    $this->p                  = $p;
    $this->p->icatF__orderby  = $this->orderby();
    
    //* inicia obxetos.
    $this->pon_obxeto(new Div   ("_h1" ) ); //* canal S. resposta html.
    $this->pon_obxeto(new Hidden("_h10") ); //* canal E. solicitude edición dunha nova.

    $this->pon_obxeto( Panel_fs::__bmais  ("bEngadir", "Añadir entrada") );


    $this->obxeto("_h1")->style("default", "display: none;");

    $this->obxeto("bEngadir")->envia_ajax("onclick");
  }

  public function declara_js() {
    return array(Refs::url("js/catalogo_novas2.js"));
  }

  public function inicia_ptw(Config_obd $c) {}
  
  public function config_usuario(IEfspax_xestor $e = null) {
    $this->pon_catalogo($e->usuario(), $e->config());

    parent::config_usuario($e);
  }

  public function operacion(EstadoHTTP $e) {
//~ echo $e->evento()->html();

    if ( ($e_aux = parent::operacion($e)                ) != null ) return $e_aux;

    //~ if ($this->obxeto("_h0" )->control_evento()) return $this->operacion_h0 ($e);
    if ($this->obxeto("_h10")->control_evento()) return $this->operacion_h10($e);

    if ($this->obxeto("bEngadir")->control_evento()) return $this->operacion_editar_nova($e);

    if ( ($e_aux = $this->obxeto("cat")->operacion($e)) != null ) return $e_aux;

    return $this->operacion_url($e);
  }

  public function operacion_editar(Epaxina_edit $e) {
    $e->obxeto("detalle_btnr_oeste")->pon_editor($e, new Editor_prgf_novas2($this));

    return $e;
  }

  public function operacion_editar_nova(Epaxina_edit $e, $id_paxina = null) {
    $url_imx = Refs::url_arquivos();

    $editor  = new Editor_novas2($this, $url_imx, $id_paxina);

    $e->obxeto("detalle_btnr_oeste")->pon_editor($e, $editor);

    return $e;
  }

  public function preparar_saida(Ajax $a = null) {
    parent::preparar_saida($a);

    if ($a == null) return;

    $a->pon_ok(true, "trwCat_listar()");
  }

  public function operacion_duplicar_nova(Epaxina_edit $e, $id_paxina) {
    $cbd = new FS_cbd();

    $cbd->transaccion();

    if ($this->operacion_duplicar_nova_2($cbd, $id_paxina)) {
      $cbd->commit();

      $this->obxeto("cat")->obxeto("clista")->preparar_saida($e->ajax());
      
      $e->ajax()->pon_ok(true, "trwCat_listar()");
    }
    else {
      $cbd->rollback();
      
      $e->ajax()->pon_ok(true);
    }


    return $e;
  }

  public function operacion_suprime_nova(Epaxina_edit $e, $id_paxina) {
    $cbd = new FS_cbd();

    $cbd->transaccion();

    $p = Paxina_obd::inicia($cbd, $id_paxina, "novas2");

    if ($p->delete($cbd)) {
      //~ $cbd->rollback();
      $cbd->commit();
      
      $this->obxeto("cat")->obxeto("clista")->control_limits_sup();
    }
    else
      $cbd->rollback();
      

    $this->obxeto("cat")->obxeto("clista")->preparar_saida($e->ajax());
    
    $e->ajax()->pon_ok(true, "trwCat_listar()");

    return $e;
  }

  public function html_novas2() {
    if (($catn = $this->obxeto("cat")) == null) return "";
        
    return "
<input type='hidden' id='trw-catalogo-id' value='" . $this->nome_completo() . "' />" .
$this->html_editor() . $this->obxeto("_h1")->html() . $this->obxeto("_h10")->html() . 
$catn->html();
  }
  
  public function _metatags($url_limpa, FS_cbd $cbd = null) {  //* IMPLEMENTA IMetatags._metatags()
    return [];
  }

  protected function pon_btnr() {
    $this->pon_obxeto(new Btnr_prgf(false));
  }

  private function html_editor():string {
    if ( !$this->editor ) return "";
    
    $html = $this->obxeto("bEngadir")->html();

    return "<div style='display:flex;  gap: 1.3em; justify-content: center; padding:1.7em 0;'>{$html}</div>";
  }

  private function operacion_h0(EstadoHTTP $e) {
    $_h0 = $this->obxeto("_h0");
    $_h1 = $this->obxeto("_h1");

    $_a = explode(",", $_h0->valor());

    $cbd = new FS_cbd();

    $html = ""; for ($i = 0; $i < count($_a); $i++) {
      $html .= $this->html_elmt($cbd, $_a[$i]);
    }

//~ echo "<pre>" . print_r($_a, true) . "</pre>";

    $_h1->post($html);

    $e->ajax()->pon( $_h1 );

    return $e;
  }

  private function operacion_h10(EstadoHTTP $e) {
   list($codigo, $id_paxina) = json_decode( $this->obxeto("_h10")->valor() );

//~ echo "$codigo, $id_paxina<br>";

    switch ($codigo) {
      case "edit1":
        return $this->operacion_editar_nova($e, $id_paxina);

      case "edit2":
        return Efspax_xestor::operacion_redirect($e, $id_paxina, 1);

      case "edit3":
        return $this->operacion_duplicar_nova($e, $id_paxina);

      case "suprime":
        return $this->operacion_suprime_nova($e, $id_paxina);
    }

    if (strpos($codigo, "move") === 0) return $this->operacion_mover_nova($e, $codigo, $id_paxina);

    return $e;
  }

  private function operacion_duplicar_nova_2(FS_cbd $cbd, $id_paxina) {
    $p = Paxina_obd::inicia($cbd, $id_paxina, "novas2");

    if (!$p->duplicar_paxina_detalle($cbd, $this->id_idioma)) return false;


    return Paxina_novas2_obd::update_mover($cbd, $this->id_opcion, $this->id_idioma,
                                           $p->atr("id_paxina")->valor, $id_paxina,
                                           $p->atr("pos")->valor
                                          );
  }

  private function operacion_mover_nova(Epaxina_edit $e, $codigo, $id_paxina_0) {
    list($x, $id_paxina_1) = explode("::", $codigo);

    $cbd = new FS_cbd();

    $cbd->transaccion();

    $b = Paxina_novas2_obd::update_mover($cbd, $this->id_opcion, $this->id_idioma, $id_paxina_0, $id_paxina_1);

    if ($b) {
      $cbd->commit();
      
      $e->ajax()->pon_ok(true, "novas2_resposta_intercambiar({$id_paxina_0}, {$id_paxina_1})");
    }
    else {
      $cbd->rollback();

      $e->post_msx("Sucedió un error al actualizar la BD.");
    }

    return $e;
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
 
    //* actualiza a información de cabeceira da paxina (head).
    //~ $smt = Site_metatags_obd::inicia(new FS_cbd(), $e->id_site);

    //~ $smt->__publicar($e->paxina(), $this);


    return $e;
  }

  private function pon_catalogo(FGS_usuario $u, Config_obd $c) {
    $action    = $this->p->atr("action")->valor . "?ctlg=" . $this->p->atr("id_idioma")->valor;
    $hai_busca = $this->iventas_buscador == 1;

    $this->p->icatF__permisos_u = $u->atr("permisos")->valor;
    
//~ echo "000000000000::{$this->p->icatF__permisos_u}<br>";

    $cat = new Catalogo_n( $this->p, $this->editor, $c, $action, $hai_busca);
    
    $cat->obxeto("clista")->css_cols = Prgf_html::__elmts_1_class($this);

    $this->pon_obxeto( $cat );
  }

  private function orderby() {
    return "a.pos desc";
  }

/*
  private static function inicia_crs(CRS_obd $crs_obd) {
    $crs = new CRS($crs_obd);

    $crs->css_marco("CRS_novas_marco");
    $crs->css_ico  (1);

    return $crs;
  }
*/
}
