<?php

final class Elmt_ventas_cc extends Componente {
  const ptw_0 = "ptw/paragrafo/elmt/elmt_ventas_cc.html";

  public $id_s = null;
  public $id_a = null;
  public $id_g = null;

  public $_gcc = null;


  private $vpa      = null;
  
  //~ private $resumo   = false;
  
  private $tupla_0  = 0;


  public function __construct() {
    parent::__construct("elmt_cc", Refs::url(self::ptw_0));

    $this->visible = false;

    $this->pon_obxeto( new Param("pelmt_cc") );
  }

  public function post_articulo(Articulo_obd $a, bool $resumo = false) {
    if ($a->atr("id_grupo_cc")->valor == null) return;

    $this->visible = false;
    //~ $this->resumo  = $resumo;

    $this->id_s = $a->atr("id_site"    )->valor;
    $this->id_a = $a->atr("id_articulo")->valor;
    $this->id_g = $a->atr("id_grupo_cc")->valor;
    
    $this->pon_obx();
  }

  public function operacion(EstadoHTTP $e) {

//~ echo $this->nome_completo() . "::{$this->visible}<br>";

    if (!$this->visible) return null;


    $_o = $this->obxetos("svalor");

    foreach ($_o as $o) if ($o->control_evento()) return $this->operacion_id($e);


    return null;
  }

  public function html():string {
    $this->obxeto("pelmt_cc")->post( $this->html_cc() );

    return parent::html();
  }

  private function tupla_2($id_cc_0 = null, $id_cc_1 = null) {
    $_t0 = $this->tupla_0;

    if ($id_cc_0 != null) $_t0[$id_cc_0][0] = $id_cc_1;

    $_t1 = null;
    foreach($_t0 as $cc0=>$_v) $_t1[ $cc0 ] = $_v[0];

    return $_t1;
  }

  private function pon_obx() {
    $cbd = new FS_cbd();

    $this->vpa = Site_config_obd::inicia($cbd, $this->id_s)->atr("ventas_producto_agotados")->valor;


    //~ if ($this->resumo) {
      //~ $this->visible = true;

      //~ return;
    //~ }
    
    
    $this->tupla_0 = Articulo_cc_obd::_tupla($this->id_s, $this->id_a, null, $cbd);

//~ echo "<pre style='color: #0b0;'>this.tupla_0(id_a, id_g) == ({$this->id_a}, {$this->id_g})\n" . print_r($this->tupla_0, true) . "</pre>";


    $_gcc = Articulo_cc_obd::_grupo($this->id_s, $this->id_g, $this->vpa, $cbd);

//~ echo "<pre style='color: #0b0;'>_gcc(id_a, id_g) == ({$this->id_a}, {$this->id_g})\n" . print_r($_gcc, true) . "</pre>";



    if (count($_gcc) < 1) return;

    $_scc0 = null;
    foreach($_gcc as $id_cc_0 => $_cc_0) {
      $this->pon_obxeto(self::pcampo($_cc_0['c']), $id_cc_0); //* Campo

      foreach($_cc_0['v'] as $id_cc_1 => $_v0) $_scc0[$id_cc_0][$id_cc_1] = $_v0;
    }

    foreach($_scc0 as $id_cc_0 => $_v) {
//~ echo "{$_gcc[$id_cc_0]["t"]}::::<pre style='color: #0b0;'>_v(id_cc_0) == ({$id_cc_0})\n" . print_r($_v, true) . "</pre>";

      if ($_gcc[$id_cc_0]["t"] == "color")
        $this->pon_obxeto( self::svalor_color($_v, $this->id_a), $id_cc_0 );
      else
        $this->pon_obxeto( self::svalor      ($_v, $this->id_a), $id_cc_0 );
    }

    $this->visible = true;
  }

  private function html_cc() {
    if (!$this->visible) return "";

    if ( !is_array($this->tupla_0) ) return "";


    $html_cc = "";
    foreach($this->tupla_0 as $id_cc_0 => $x) {
      $c = $this->obxeto("pcampo", $id_cc_0)->html();
      $v = $this->obxeto("svalor", $id_cc_0)->html();

      $html_cc .= "<div>{$c}</div><div>{$v}</div>";
    }

    return "<div class='elmt_cc_00'>{$html_cc}</div>";
  }
  
  
/**********************

  private function html_cc_resumo() {
    $_gcc = Articulo_cc_obd::_grupo_resumo($this->id_s, $this->id_g, $this->vpa);

//~ print_r($_gcc, 1);

    if ($_gcc == []) return "";
    
    $html_cc = "";
    foreach($_gcc as $n => $_x) {
      $html_cc .= ($_x[0] > 0) ? "<div unid='{$_x[0]}'>{$n}</div>" : "<div class='elmt_cc__sin-stock'>{$n}</div>";
    }

   return "<div class='elmt_cc'>
             <div class='elmt_cc__btn'></div>
             <div class='elmt_cc__lista'>{$html_cc}</div>
           </div>";
  }
*/


/**********************
  private function operacion_id(EstadoHTTP $e) {
//~ echo $e->evento()->html();
//~ echo "<pre style='color: #0b0;'>tupla_0(id_s, id_a) == ({$this->id_s}, {$this->id_a})\n" . print_r($this->tupla_0, true) . "</pre>";

    //* 1.- inicia.
    $id_cc_0 = $e->evento()->subnome(0);
    $id_cc_1 = $this->obxeto("svalor", $id_cc_0)->valor();

//~ echo "<pre style='color: #0b0;'>tupla_2(id_cc_0, id_cc_1) == ({$id_cc_0}, {$id_cc_1})\n" . print_r($_tupla_2, true) . "</pre>";

//~ echo "<pre style='color: #0b0;'>this._gcc[1]\n" . print_r($this->_gcc[1], true) . "</pre>";

    //* 2.- busca alternativas.

    if ( ($id_articulo = $this->busca_id($id_cc_0, $id_cc_1)) == null ) $id_articulo = $this->id_a;

    //* 5.- reinicia elmt.
    $cbd = new FS_cbd();

    $a_obd  = Articulo_obd::inicia($cbd, $this->id_s, $id_articulo);
    $sc_obd = Site_config_obd::inicia($cbd, $this->id_s);

    $this->pai->inicia($a_obd, false);

    $this->pai->inicia_ptw($e->config(), $sc_obd, true, $this->pai->multivendedor);

    $this->pai->preparar_saida( $e->ajax() );


    return $e;
  }

  private function busca_id($id_cc_0, $id_cc_1) {
    $_tupla_2 = $this->tupla_2($id_cc_0, $id_cc_1);

    while (count($_tupla_2) > 0) {
      if ( ($id_articulo = Articulo_cc_obd::hai_tupla($this->id_s, $this->id_g, $_tupla_2)) != null ) return $id_articulo;

      array_pop($_tupla_2);
    }

    return null;
  }
*/
  private static function pcampo($t) {
    $d = new Div("pcampo", $t);

    $d->clase_css("default", "elmt_cc_02");

    return $d;
  }

  private static function svalor($_v, $v) {
    $_v2 = array(); 
    foreach($_v as $_n) {
      $n2 = ""; if ($_n[2] < 1) $n2 = " (0)";
      
      $_v2[ $_n[3] ] = "{$_n[0]}{$n2}";
    }


    $s = new Select("svalor", $_v2);

    $s->post($v);

    $s->clase_css("default", "elmt_cc_03");

    //~ $s->envia_SUBMIT("onchange");
    $s->envia_ajax("onchange");
    
    $s->pon_eventos("onchange", "prgf_producto_solicita_1(this)");


    return $s;
  }

  private static function svalor_color($_v, $v) {
    return new Elmt_ventas_cc_scolor("svalor", $v, $_v);
  }
}

//**************************************

final class Elmt_ventas_cc_scolor extends Hidden {
  private $_c = null;

  public function __construct($k, $c, $_c) {
    parent::__construct($k);

    $this->post($c);

    $this->_c = $_c;

//~ echo "111111111111111111111111<pre>" . print_r($this->_c, 1) . "</pre>";
  }

  public function html():string {
    $k  = $this->nome_completo();

    $html = ""; foreach ($this->_c as $_c2) $html .= $this->html_color($k, $_c2);


    return parent::html() . "<div class='elmt_cc_04'>{$html}</div>";
  }

  private function html_color($k, $_c2) {
    $css1 = "elmt_cc_05";
    $css2 = "elmt_cc_06";
    $js   = "";
    if ($_c2[3] == $this->valor()) {
      $css1 .= " elmt_cc_05-select";
    }
    else
      $js    = "onclick=\"prgf_producto_solicita_2('{$_c2[3]}')\"";

    return "<div class='{$css1}' {$js} title='{$_c2[1]}'><div class='{$css2}' style='background-color: {$_c2[0]};'></div></div>";
  }

}
