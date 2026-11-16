<?php

/** Escribe la salida html dun obxeto CLista
 *
 * @package tilia\vista\componentes\clista
 */

abstract class CLista_ehtml extends EscritorResultado_html {
  public $xestor = null;

  public function __construct(Iterador_bd $rbd = null) {
    parent::__construct($rbd);

    $this->style_table  = "";
    $this->style_cab    = "";
    $this->style_tr     = "";
    $this->style_tr_alt = "";
  }

  public function pon_xestor(CLista $xestor) {
    $this->xestor = $xestor;
  }

  final public function __fslControl(Control $c, $subnome = null) {
    return $this->xestor->adopta($c, $subnome);
  }

  final public function rama_click($id_rama) {
    $sublista = $this->xestor->sublista($id_rama);

    if ($sublista == null) {
      $this->xestor->pon_sublista($id_rama);

      return;
    }


    $sublista->visible = !$sublista->visible;
  }

  final protected function rama_check($id_rama) {
    $sublista = $this->xestor->sublista($id_rama);

    if ($sublista == null) return false;

    return $sublista->visible;
  }

  protected function rama_html($id_rama) {
    if ($id_rama == null) return "";

    $sublista = $this->xestor->sublista($id_rama);

    if ($sublista == null) return "";
    
    if (!$sublista->visible) return "";

    return $sublista->html();
  }

  protected function cabeceira($df = null) {
    if (!is_array($df)) return "";

    //~ echo "<pre>" . print_r($df, true) . "</pre>";

    $df_aux = null;
    foreach ($df as $k_dc=>$dc) {
      $k_dc = $this->__lOrdenar_html($k_dc);

      $df_aux[$k_dc] = $dc;
    }

    return parent::cabeceira($df_aux);
  }

  protected function __lEditar_html($id, $etq = "editar") {
    $l = Panel_fs::__link("editar" . Escritor_html::csubnome . $id, $etq);

    return $this->__fslControl($l)->html();
  }

  protected function __lVer_html($id, $etq = "ver") {
    $l = Panel_fs::__link("ver" . Escritor_html::csubnome . $id, $etq, "texto2", "texto2_a");

    $l->envia_ajax("onclick");

    return $this->__fslControl($l)->html();
  }

  protected function __lOrdenar_html($id, $etq = null, $js = "ajax") {
    /* $js == "submit" => tipo de envío submit.
     * $js == "ajax"   => tipo de envío ajax.
     * $js != null     => Entendemos que é unha chamada a unha función de javascript.
     */

    $id = str_replace(" ", "", $id);

    list($campo, $tipo) = $this->xestor->_orderby();

    $tipo_aux = ($tipo == "ASC")?"&darr;":"&uarr;";

    if ($etq == null) $etq = $id;


    //~ if (strpos($id, $campo) === 0) $etq .= "<span class='lista_orde'>&nbsp;{$tipo_aux}</span>";
    if ($id == $campo) $etq .= "<span class='lista_orde'>&nbsp;{$tipo_aux}</span>";

    $l = new Link("orderby", $etq);

        if ($js == "submit") $l->envia_submit("onclick"     );
    elseif ($js == "ajax"  ) $l->envia_ajax  ("onclick"     );
    elseif ($js != null    ) $l->pon_eventos ("onclick", $js);

    return $this->__fslControl($l, $id)->html();
  }

  protected function __bmais_html($id_rama, $readonly = false, $css = null) {
    if (($sl = $this->xestor->sublista_prototipo($id_rama)) == null) {
      return "";
    }
    elseif ($sl->__count() == 0) {
      return "";
    }
    else {
      $readonly_aux = $readonly;

      if (!$this->rama_check($id_rama)) {
        $id = "brama_mais";
        $etq = "▶";
        $tit = "Expandir rama";
      }
      else {
        $id = "brama_menos";
        $etq = "▼";
        $tit = "Contraer rama";
      }
    }

    $b = self::__boton($id, $tit, $etq, $readonly_aux, $css);

    $b->clase_css("default" , "lista_bmais");
    $b->clase_css("readonly", "lista_bmais");

    return $this->__fslControl($b, $id_rama)->html();
  }

  protected function __bmaiscab_html($readonly = false, $css = null) {
    if ($this->xestor->__count_sublistas(true)) {
      $id = "bramacab_menos";
      $etq = "━";
      $tit = "Contraer todas las ramas";
    }
    else {
      $id = "bramacab_mais";
      $etq = "✚";
      $tit = "Expandir todas las ramas";
    }

    if ($css == null) $css = 'min-width: 23px; padding: 1px; text-align:center;';

    $b = self::__boton($id, $tit, $etq, $readonly, $css);

    return $this->__fslControl($b, "clista-cab")->html();
  }

  protected function __paxinador_html() {
    if ($this->xestor->limit_f == null) return "";

    $i = $this->xestor->__i();

    if (($t = $this->xestor->__t()) < 2) return "";


    $a = self::__boton("lpax_a", "", "&#9668;", $i == 1 );
    $s = self::__boton("lpax_s", "", "&#9658;", $i == $t);

    $a = $this->__fslControl($a)->html();
    $s = $this->__fslControl($s)->html();

    $i2 = self::__text($this, "ti", $i);
    $t2 = self::__text($this, "tt", $t, true);

    $i2->pon_eventos("onmouseover", "this.style.borderColor = '#ccc'");
    $i2->pon_eventos("onfocus"    , "this.style.borderColor = '#ccc'");
    $i2->pon_eventos("onblur"     , "this.style.borderColor = 'transparent'");

    return "{$a}&nbsp;" . $i2->html() . "&nbsp;de&nbsp;" . $t2->html() . "&nbsp;{$s}";
  }
  
  final protected static function chMas(CLista_ehtml $ehtml, $k) {
//~ echo "1111:::$k, " . $ehtml->xestor->masivo($k) . "<br>";

    $ch = new Checkbox("chMas", $ehtml->xestor->masivo($k) == 1, "");
    
    //~ $ch->envia_SUBMIT("onclick");
    $ch->envia_ajax("onclick");
  
    return $ehtml->__fslControl($ch, $k)->html();
  }

  final protected static function __boton($id, $title, $etq, $readonly, $css = "lista_paxinador_boton") {
    $b = new Button($id, $etq);

    $b->clase_css("default" , $css);
    $b->clase_css("readonly", "{$css}_r");

    $b->ancla    = $id;
    $b->readonly = $readonly;


    //~ $b->envia_submit("onclick");
    $b->envia_AJAX("onclick");

    return $b;
  }

  final protected static function __tr_onmouseover($css1, $css2) {
    return "onmouseover=\"this.className='{$css1}'\" onmouseout=\"this.className='{$css2}'\"";
  }

  final protected static function __text(CLista_ehtml $ehtml, $id, $i, $readonly = false) {
    if (($t = $ehtml->xestor->obxeto($id)) != null) {
      $t->post($i);

      $t->readonly = $readonly;

      return $t;
    }

    $t = new Text($id);

    $t->post($i);

    $t->readonly = $readonly;

    $t->style("default" , "text-align: center; width: 47px; border: 1px solid transparent; background-color: transparent;");
    $t->style("readonly", "text-align: center; width: 47px; border: 0; background-color: transparent;");

    //~ $t->envia_submit("onchange");
    $t->envia_ajax("onchange");


    $ehtml->__fslControl($t);

    return $t;
  }
}

