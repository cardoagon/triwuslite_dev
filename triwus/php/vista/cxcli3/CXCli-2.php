<?php

interface IPanel_cxcli2 {
  public function id();
  public function titulo();
  public function notas();
  public function tipo_btnr();

  public function post_u(FGS_usuario $u = null);

  public function operacion_bCancelar(IEfspax_xestor $e);
  public function operacion_bAceptar(IEfspax_xestor $e);
}

//************************************

final class CXCli2 extends Componente {
  const ptw_0 = "ptw/clogin/web/cxcli2.html";
  const ptw_p = "ptw/clogin/web/cxcli2_panel.html";

  public  $id_site = null;
  private $venta   = true;

  private $paneis = null;
  private $paneis_orde = null;

  public function __construct(FGS_usuario $u, $id_site, $id_idioma, $venta = true) {
    parent::__construct("cxcli2");

    $this->id_site = $id_site;
    $this->venta   = $venta;

    $this->cambiar_idioma( Idioma::factory($id_idioma) );

    $this->__iniciar();

    $this->post_u($u);
  }

  public function post_u(FGS_usuario $u = null) {
    if ($u == null)      return;
    if (!$u->validado()) return;

    if (count($this->paneis) == 0) return;

    foreach($this->paneis as $p) $p->post_u($u);
  }

  public function operacion(EstadoHTTP $e) {
    if (count($this->paneis) == 0) return null;

    foreach($this->paneis as $p) {
      if ($p->obxeto("titulo")->control_evento()) return $this->operacion_titulo($e, $p); //* interceptamos o evento do div 'titulo'.

      if (($e_aux = $p->obxeto("btnr")->operacion_panel($e, $p)) != null) return $e_aux; //* interceptamos o evento da botonera.

      if (($e_aux = $p->operacion($e)) != null) return $e_aux;
    }


    return null;
  }

  public function panel($id) {
    return $this->paneis[$id];
  }

  public function pon_panel(IPanel_cxcli2 $p, $posicion = -1, $visible = false) {
    $p->visible = $visible;

    $this->pon_obxeto($p);

    $p->pon_obxeto( self::__erro() );
    $p->pon_obxeto( self::__titulo($p->titulo()) );
    $p->pon_obxeto( new Span("notas", $p->notas()) );
    $p->pon_obxeto( new CXCli2panel_btnr($p) );

    $p->obxeto("notas")->clase_css("default", "cxcli2_notas");

    $this->paneis[$p->id()] = $p;

    if ($posicion != -1) {
      $_orde_aux = null;

      if (($ct_panel = count($this->paneis_orde)) == 0)
        $_orde_aux[] = $p->id();
      else {
        for ($i = 0; $i < $ct_panel; $i++) {
          if ($i == $posicion) $_orde_aux[] = $p->id();

          $_orde_aux[] = $this->paneis_orde[$i];
        }
      }

      $this->paneis_orde = $_orde_aux;
    }
    else
      $this->paneis_orde[] = $p->id();


    $this->pon_obxeto(new Div("illa"), $p->id());
 }

  public function pos_panel($id, $posicion) {
    if (!isset($this->paneis[$id])) return;

    if (($posicion_0 = array_search($id , $this->paneis_orde)) === null) return;

    if (!isset($this->paneis_orde[$posicion])) {
      unset($this->paneis_orde[$posicion_0]);

      $this->paneis_orde[$posicion] = $id;

      return;
    }

    $id_aux = $this->paneis_orde[$posicion];

    $this->paneis_orde[$posicion]   = $id;
    $this->paneis_orde[$posicion_0] = $id_aux;
  }

  public function sup_panel($id = null) {
    if ($id == null) {
      if ($this->paneis == null) return;

      foreach($this->paneis as $id=>$p) $this->sup_panel($id);

      return;
    }
    
    

    //~ $kp = $this->paneis_orde[$id];
    $kp = $id;
    
//~ echo "kp::{$kp}<br>";

    if (!isset($this->paneis[$kp])) return;


    unset($this->paneis[$kp]);

    $this->sup_obxeto($kp);
    $this->sup_obxeto("illa", $kp);

    unset($this->paneis_orde[$id]);
  }


  public function preparar_saida(Ajax $a = null) {
    $html_0 = LectorPlantilla::plantilla(Refs::url(self::ptw_0));

    if (count($this->paneis) == 0) {
      $html = str_replace("[paneis]", "", $html_0);

      $this->obxeto("illa")->post($html);

      if ($a == null) return;


      $a->pon($this->obxeto("illa"));

      $this->obxeto("illa")->post(null);
    }

    $html    = "";
    foreach($this->paneis_orde as $id) {
      if (!isset($this->paneis[$id])) continue;

      $this->panel_preparar_saida($this->paneis[$id]);

      $html .= "<div><a name='acxcli2{$id}' ></a>" . $this->obxeto("illa", $id)->html() . "</div>";
    }

    $html = str_replace("[paneis]", $html, $html_0);

    $this->obxeto("illa")->post($html);

    if ($a == null) return;

    $a->pon($this->obxeto("illa"));

    $this->obxeto("illa")->post(null);
  }

  public function panel_readonly(IPanel_cxcli2 $p, $b = true) {
    $p->readonly($b);

    $p->obxeto("titulo")->readonly = false;
  }

  public function panel_preparar_saida(IPanel_cxcli2 $p, Ajax $a = null) {
    $btnr = $p->obxeto("btnr");

    $html_p  = LectorPlantilla::plantilla(Refs::url(self::ptw_p));

    $html_p = str_replace("[titulo]"   , $p->obxeto("titulo")->html(), $html_p);
    $html_p = str_replace("[notas]"    , $p->obxeto("notas")->html() , $html_p);
    $html_p = str_replace("[sGardando]", $btnr->sgardando_html()     , $html_p);
    $html_p = str_replace("[erro]"     , $btnr->erro_html()          , $html_p);
    $html_p = str_replace("[baceptar]" , $btnr->baceptar_html()      , $html_p);
    $html_p = str_replace("[bcancelar]", $btnr->bcancelar_html()     , $html_p);
    $html_p = str_replace("[beditar]"  , $btnr->beditar_html()       , $html_p);

    if ($p->visible) {
      $html_p = str_replace("[panel]", $p->html()                , $html_p);
      $html_p = str_replace("[erro]" , $p->obxeto("erro")->html(), $html_p);
    }
    else {
      $html_p = str_replace("[panel]", "", $html_p);
      $html_p = str_replace("[erro]" , "", $html_p);
    }

    $this->obxeto("illa", $p->id())->post($html_p);

    if ($a == null) return;

    $a->pon( $this->obxeto("illa", $p->id()) );

    $this->obxeto("illa", $p->id())->post(null);
  }

  public function operacion_titulo(IEfspax_xestor $e, IPanel_cxcli2 $p) {
    $p->visible = !$p->visible;

    if ( $e->evento()->ajax() ) $this->panel_preparar_saida($p, $e->ajax());

    return $e;
  }

  private function __iniciar() {
    //~ $this->pon_panel(new CXCli2panel_1());
    $this->pon_panel(new CXCli2panel_2());

    if ($this->venta) {
      $this->pon_panel(new CLWxcli_lserv());
      $this->pon_panel(new CLWxcli_lped());
    }


    $this->pon_panel(new CXCli2_feedback(), -1, true);
    $this->pon_panel(new CXCli2_pechar  (), -1, true);

    if (!function_exists("CXCli2_pestanhas_site")) return;


    CXCli2_pestanhas_site($this);
  }

  private static function __erro() {
    $d = new Div("erro");

    $d->clase_css("default", "texto2_erro");

    return $d;
  }

  private static function __titulo($txt) {
    $t = new Div("titulo", $txt);

    $t->title = "Editar / Cancelar Edición";

    $t->clase_css("default", "texto1 cxcli2_p_titulo");

    $t->style("default", "cursor: pointer;");

    $t->envia_ajax("onclick");
    //~ $t->envia_submit("onclick");

    return $t;
  }

}

//****************************

final class CXCli2panel_btnr extends Componente {
  private $tipo;

  public function __construct(IPanel_cxcli2 $p) {
    parent::__construct("btnr");

    $this->tipo = $p->tipo_btnr();
    
    $idioma = $p->pai->idioma->codigo;

    $this->pon_obxeto( self::__bEditar() );

    $this->pon_obxeto(new Span("erro"));

    $this->pon_obxeto( Panel_fs::__bcancelar(Msx::$_cancelar[$idioma],Msx::$_cancelar[$idioma]) );

    $this->obxeto("bCancelar")->envia_ajax("onclick");

    $this->obxeto("erro")->clase_css("default", "texto2_erro");
    $this->obxeto("erro")->style    ("default", "cursor:pointer; position: fixed;top:22%;left:50%;width:330px; margin-left:-165px;background-color:#f5c5c5;padding:2%;border-radius:7px;border:1px solid #ba0000;");

    $this->obxeto("erro")->visible = false;

    $this->obxeto("erro")->pon_eventos("onclick", "this.style.display = 'none'");

    //~ if ($this->tipo < 2) return;


    $this->pon_obxeto(new Span("sGardando", "... Guardando ..."));

    $this->pon_obxeto( Panel_fs::__baceptar(Msx::$_aceptar[$idioma]["txt"], Msx::$_aceptar[$idioma]["title"]) );


    $this->obxeto("sGardando")->style("default", "display: none;");

    //~ $this->obxeto("bAceptar")->pon_eventos("onclick", "cxcli2_bAceptar(this, 1)");
  }

  public function readonly($b = true) {
    $this->obxeto("bAceptar")->visible = !$b;
  }

  public function tipo($t = -1) {
    if ($t == -1) return $this->tipo;

    $this->tipo = $t;
  }

  public function operacion_panel(IEfspax_xestor $e, IPanel_cxcli2 $p) {
    //~ echo $e->evento()->html();

    $this->post_erro(null, $e);

    if ($this->obxeto("bEditar"  )->control_evento()) return $p->pai->operacion_titulo($e, $p);
    
    if ($this->tipo < 2) return null;

    if ($this->obxeto("bCancelar")->control_evento()) return $this->operacion_bCancelar($e, $p);

    if ($this->obxeto("bAceptar")->control_evento()) return $p->operacion_bAceptar($e);


    return null;
  }

  public function post_erro($erro, IEfspax_xestor $e = null) {
    if ($erro != null) {
      $erro .= "<div style='margin: 17px 11px 0 0;'>
                  <button type='button' style='cursor: pointer;'>
                    <span style='color: #00ba00;font-size: 1.3em;'>✔</span>&nbsp;&nbsp;&nbsp;Aceptar
                  </button>
                </div>";
    }

    $this->obxeto("erro"     )->post($erro);
    $this->obxeto("sGardando")->post("");

    $this->obxeto("erro")->visible = ($erro != null);

    if ($e == null) return;

    $e->ajax()->pon($this->obxeto("erro"));
    $e->ajax()->pon($this->obxeto("sGardando"));
  }

  public function sgardando_html() {
    if (!$this->pai->visible) return "";

    if ($this->tipo != 2)     return "";

    return $this->obxeto("sGardando")->html();
  }

  public function baceptar_html() {
    if (!$this->pai->visible) return "";

    if ($this->tipo != 2)     return "";

    return $this->obxeto("bAceptar")->html();
  }

  public function erro_html() {
    if (!$this->pai->visible) return "";

    return $this->obxeto("erro")->html();
  }

  public function bcancelar_html() {
    if ($this->tipo != 2)     return "";
    
    if ($this->pai->visible) return $this->obxeto("bCancelar")->html();

    return "";
  }

  public function beditar_html() {
    $b = $this->obxeto("bEditar");

    $b->post( self::__bEditar_etq(!$this->pai->visible) );

    return $b->html();
  }

  private function operacion_bCancelar(IEfspax_xestor $e, IPanel_cxcli2 $p) {
    if (($e_aux = $p->operacion_bCancelar($e)) != null) return $e_aux;

    return $p->pai->operacion_titulo($e, $p);
  }

  public static function __bEditar($mas = true) {
    $b = new Button("bEditar", self::__bEditar_etq($mas));

    $b->clase_css("default", "");
    $b->style("default", "background: transparent; cursor: pointer; min-width: 23px; padding: 0; text-align:center; border: 0;");


    $b->envia_ajax("onclick");

    return $b;
  }

  public static function __bEditar_etq($mas = true) {
    $etq = ($mas)?"▶":"▼";

    return "<span class='texto1 cxcli2_p_titulo'>{$etq}</span>";
  }
}


final class CXCli3_util {
  public static function action( $p ) {
    return __FILE__;
  }

  public static function ptw( $ptw_0 ) {
    list($a, $b) = explode("/php/", getcwd());

    return $a . $ptw_0;
  }
}
