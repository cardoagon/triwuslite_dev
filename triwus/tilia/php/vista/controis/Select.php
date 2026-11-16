<?php


/** Representa ao elemento html select
 *
 * @package tilia\vista\controis
 */

class Select extends Control implements IAJAX {
  public $size     = 1;
  public $multiple = false;

  private $grupos  = null;
  private $options = null;

  public function  __construct($nome, $options = null, $readonly = false, $etq = null, $posicion = 4) { // <--
    parent::__construct($nome);

    $this->pon_grupo();

    $this->options($options);

    $this->readonly = $readonly;

    $this->tipo_AJAX     = "SELECT";
    $this->tipo_htmljson = "SELECT";

    if(($posicion > 0 && $posicion <5) && $etq != null) $this->pon_etq($etq, $posicion);
  }

  public function pon_grupo($k = -1, $v = null) {
    $this->grupos[$k] = $v;
  }

  public function pon_opcion($k, $v = null, $g = -1) {
    if ($v == null) $v = $k;

    $this->options[$g][$k] = $v;
  }

  public function sup_opcion($k = null, $g = -1) {
    if ($k == null) $k = $this->valor();

    if ($k == null) return;

    unset($this->options[$g][$k]);
  }

// 19/04/2016
  public function html():string {
    $multiple = (($this->size > 1) && ($this->multiple))?"multiple":"";

    $eventos = $this->html_eventos();
    $evento_onClick = strpos(strtoupper($eventos), "ONCLICK") > 0;

    $select = "
      " . $this->html_ancla() . "
      <select " . $this->html_nome() . " " . $this->html_title() . " size='{$this->size}'
              {$multiple}
             " . $this->html_clase() .  "
             " . $this->html_atr() .  "
             " . $this->html_estilo() .  "
              {$eventos}>";

    if (is_array($this->options)) {
      foreach ($this->options as $g=>$_g) {
        if ($g != -1) $select .= "<optgroup label='{$this->grupos[$g]}'>";

        foreach ($_g as $k=>$v) $select .= $this->html_option($k, $v, $evento_onClick);

        if ($g != -1) $select .= "</optgroup>";
      }
    }

    $select = "
        $select
      </select>";

// 19/04/2016 -------------------------------------------------



    if($this->posicion != null) {
      switch($this->posicion) {
        case 1: // arriba > pos_arriba ~ <br/>
          $select = $this->html_etq() . "<br/>" . $select;
          break;
        case 2: // derecha > pos_dereita
          $select = $select . $this->html_etq();
          break;
        case 3: // abajo > pos_ abaixo ~ <br/>
          $select = $select . "<br/>" . $this->html_etq();
          break;
        case 4: // izquierda > pos_esquerda
          $select = $this->html_etq() . $select;
          break;
      }
    }
// --------------------------------------------------------------

    return $select;
  }

  private function html_option($valor, $option, $evento_onClick) {
    if (is_array($this->valor)) {
      $selected = false;
      for($i = 0; ($i < count($this->valor)) && !$selected; $i++) $selected = ($this->valor[$i] == $valor);
    }
    else
      $selected = ("k_{$valor}" == "k_{$this->valor}");

    if (($this->readonly || $evento_onClick) && (!$selected)) return "";

    $selected = ($selected)?"selected":"";

    return "
      <option value='$valor' $selected>$option</option>";
  }

  public function desc($valor = null, $g = -1) {
    if ($valor === null) $valor = $this->valor;
    
    if ($valor === null) return null;
    
    if (is_array($valor)) {
      for ($i = 0; $i < count($valor); $i++) $descricion[] = $this->options[$g][$valor[$i]];

      return $descricion;
    }

    return $this->options[$g][$valor];
  }

  public function options($options = null, $g = -1) {
    if ($options == null) {
      if (!isset($this->options[$g])) return null;

      return $this->options[$g];
    }

    $this->options[$g] = $options;
  }

  public function count($g = -1) {
    return count($this->options[$g]);
  }

  public function fillosAJAX() { //* implementa IAJAX.
    //* SEN IMPLEMENTAR a agrupación de opcións.

    if (!is_array($this->options)) return null;

    $_a = [];
    foreach ($this->options as $g=>$_g) {
      foreach ($_g as $k=>$v) $_a[] = ["k"=>$k, "v"=>$v];
    }

    return $_a;
  }
}

//**********************************

class DataList extends Control implements IAJAX {
  protected $options  = null;
  protected $onchange = null;

  public function  __construct($nome, $options = null, $readonly = false, $etq = null, $posicion = 4) { // <--
    parent::__construct($nome);

    $this->options($options);

    $this->readonly = $readonly;

    $this->tipo_AJAX     = "SELECT";
    $this->tipo_htmljson = "SELECT";

    if(($posicion > 0 && $posicion < 5) && $etq != null) $this->pon_etq($etq, $posicion);
  }

  public function pon_opcion($k, $v = null) {
    if ($v == null) $v = $k;

    $this->options[$k] = $v;
  }

  public function sup_opcion($k = null) {
    if ($k == null) $k = $this->valor();

    if ($k == null) return;

    unset($this->options[$k]);
  }

  public function html():string {
    $id_t = $this->nome_completo() . Escritor_html::csubnome . "texto";
    $id_l = $this->nome_completo() . Escritor_html::csubnome . "lista";

    //~ $valor = (isset($this->options[$this->valor]))?$this->options[$this->valor]:"";
    $valor = (isset($this->options[$this->valor]))?$this->options[$this->valor]:$this->valor;

    $select = "
      " . $this->html_ancla() . "
      <input type='hidden' " . $this->html_nome() . " value='{$this->valor}' dl_onchange='{$this->onchange}' " . $this->html_atr() .">
      <input type='text' autocomplete='off' id='{$id_t}' list='{$id_l}' " . $this->html_title() . "
             " . $this->html_clase() .  "  " . $this->html_estilo() . "  " . $this->html_readonly() . "
             onchange='tilia_dl_change(this)' value='{$valor}'><datalist id='{$id_l}'>";

    if (is_array($this->options)) foreach ($this->options as $k=>$v) $select .= $this->html_option($k, $v);

    $select .= "</datalist>";

// 19/04/2016 -------------------------------------------------



    if($this->posicion != null) {
      switch($this->posicion) {
        case 1: // arriba > pos_arriba ~ <br/>
          $select = $this->html_etq() . "<br/>" . $select;
          break;
        case 2: // derecha > pos_dereita
          $select = $select . $this->html_etq();
          break;
        case 3: // abajo > pos_ abaixo ~ <br/>
          $select = $select . "<br/>" . $this->html_etq();
          break;
        case 4: // izquierda > pos_esquerda
          $select = $this->html_etq() . $select;
          break;
      }
    }
// --------------------------------------------------------------

    return $select;
  }

  public function desc($k = null) {
    if ($k === null) $k = $this->valor;


    return $this->options[$k];
  }

  public function options($options = null) {
    if ($options == null) return $this->options;

    $this->options = $options;
  }

  public function count() {
    return count($this->options);
  }

  public function validar():bool {
    if (($k = $this->valor()) === null) return false;

//~ echo "$k\n";

    return isset($this->options[$k]);
  }

  public function fillosAJAX() { //* implementa IAJAX.
    if (!is_array($this->options)) return null;


    $_a = array($this->valor);
    foreach ($this->options as $k=>$v) $_a[] = array("k"=>$k, "v"=>$v);


    return $_a;
  }

  public function pon_eventos($evento, $accion) {
    $this->onchange =  $accion;
  }

  public function envia_SUBMIT($evento, $msxConfirma = null, $ancla_envia = null) {
    $this->onchange = "submit";
  }

  public function envia_AJAX($evento, $msxConfirma = null, $js_post_callback = "null", $js_pre_callback = "null") {
    $this->onchange = "ajax";
  }

  protected function html_readonly() {
    if ($this->readonly) return " readOnly=\"true\" ";

    return "";
  }

  protected function html_option($valor, $option) {
    return "
      <option tilia_dlo_valor='{$valor}' value='{$option}'>";
  }
}

//************************************

class DataListM extends Control implements IAJAX {
  protected $options  = null;
  protected $onchange = null;
  
  public $aberto   = false;

  public function  __construct($nome, $options = null, $readonly = false) {
    parent::__construct($nome);

    $this->options($options);
    $this->readonly = $readonly;

    $this->tipo_AJAX     = "SELECT";
    $this->tipo_htmljson = "SELECT";

    $this->post(null);
  }


  public function readonly($b = true) { //* SOBREESCRIBE ObxetoFormulario.readonly($b = true)
    //* b in [... -3, -2, -1, 0, 1]

    parent::readonly($b);
  }

  public function post($post) {
    $this->valor = ["t" => "", "_s" => []];

    if ($post == null) return;

    if (Valida::json($post)) {
      $post = json_decode($post);

      $this->valor["t" ] = $post->t;
      $this->valor["_s"] = $post->_s;
    }
  }

  public function pon_opcion($k, $v = null) {
    if ($v == null) $v = $k;

    $this->options[$k] = $v;
  }

  public function sup_opcion($k = null) {
    if ($k == null) $k = $this->valor();

    if ($k == null) return;

    unset($this->options[$k]);
  }

  public function html():string {
    $html_o = ""; 
    if (is_array($this->options)) {
      foreach ($this->options as $k=>$v) {
        $b = ( array_search($k, $this->valor["_s"]) === false )?"0":"1";

        $html_o .= $this->html_option($k, $v, $b);
      }
    }
    
    if ($this->readonly < 1) {
      $txt_aux = " onkeyup='tilia_dlm_t_keyup(this)'";
      if ( !$this->aberto ) $txt_aux .= " onclick='tilia_dlm_t_click(this)'";
    }
    else {
      $txt_aux = " readonly='true'";
    }
    
    if ( $this->aberto ) {
      $style_ab = "display: flex; position: initial;";
    }
    else {
      $style_ab = "display: none";
    }


    return "
<div class='tilia_dlm' " . $this->html_dlm_readonly() . " dlm_onchange='{$this->onchange}'>
  <div>
    <input type='text'{$txt_aux} value='{$this->valor["t"]}' maxlength='11'>
  </div>
  <div style='{$style_ab}'>{$html_o}</div>
  <input type='hidden' " . $this->html_nome() . " value='" . json_encode($this->valor) . "'>
</div>";
  }

  public function options($options = null) {
    if ($options == null) return $this->options;

    $this->options = $options;
  }

  public function count() {
    return count($this->options);
  }

  public function fillosAJAX() { //* implementa IAJAX.
    if (!is_array($this->options)) return null;


    $_a = array($this->valor);
    foreach ($this->options as $k=>$v) $_a[] = array("k"=>$k, "v"=>$v);


    return $_a;
  }

  public function pon_eventos($evento, $accion) {
    $this->onchange =  $accion;
  }

  public function envia_SUBMIT($evento, $msxConfirma = null, $ancla_envia = null) {
    $this->onchange = "submit";
  }

  public function envia_AJAX($evento, $msxConfirma = null, $js_post_callback = "null", $js_pre_callback = "null") {
    $this->onchange = "ajax";
  }

  protected function html_dlm_readonly() {
    //* $this->readonly IN [... -3, -2, -1, 0, 1]

        if ($this->readonly < 0) $r = (String)$this->readonly;
    elseif ($this->readonly    ) $r = "1";
    else                         $r = "0";

    return "dlm_readonly='{$r}'";
  }

  protected function html_option($valor, $option, $b) {
    $checked = ($b == "1")?"checked":"";
    
    $v = "1";
    if ($this->valor["t"] != null) {
      $v = (preg_match("/{$this->valor["t"]}/i", $option))?"1":"0";
    }

    return "
    <div dlm_l_v='{$v}' title='{$option}'>
      <input type='checkbox' dlm_l_i='{$valor}' {$checked} onclick='tilia_dlm_l_click(this)' /><label>{$option}</label>
    </div>";
  }
}

