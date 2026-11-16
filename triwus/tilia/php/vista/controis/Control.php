<?php

/** Especialización abstracta dunha clase ObxetoFormulario. Representa a obxetos comunes en GUI's, com Caixas de texto, Selectores, Botones ... etc.
 *
 * @package tilia\vista\controis
 */


abstract class Control extends ObxetoFormulario {
//* 19/04/2016 -------------
  const pos_arriba    = 1;
  const pos_dereita   = 2;
  const pos_abaixo    = 3;
  const pos_esquerda  = 4;

  public $posicion    = null;
  public $etq         = null; 
  public $n_etq       = null; //* Variable que referencia el contenido del Label a incrustar.
//* FIN 19/04/2016 -------------

  public $ancla       = null;
  public $disabled    = false;
  public $msxConfirma = "";
  public $multiple    = false;
  public $title       = null;
  public $placeholder = null;

  public $ajax_css_display = "initial";

  protected $tipo_AJAX = null;
  protected $eventos   = null;
  protected $_atr      = null;

  protected function  __construct($nome) {
    parent::__construct($nome);
  }

  public function _atr($id) {
    return $this->_atr[$id];
  }

  public function pon_atr($id, $v = null) {
    $this->_atr[$id] = $v;
  }

  public function sup_atr($id = null) {
    if ($id == null) {
      $this->_atr = null;

      return;
    }

    unset( $this->_atr[$id] );
  }

  public function pon_ancla($ancla = null) {
    $this->ancla = $ancla;
  }

  /* 14/04/2016
  * Nueva función para añadir un label al componente.
  * El primer parámetro define el texto del Label.
  * El segundo hace referencia a la posición; false(por defecto) = izquierda del componente, true = derecha del componente.
  */
  public function pon_etq($n_etq = null, $posicion = 4) {
    $this->n_etq = $n_etq;

    if($posicion > 0 && $posicion < 5) $this->posicion = $posicion;
  }

  public function pon_eventos($evento, $accion) {
    if ($evento == null) $this->sup_eventos();

    $this->eventos[strtolower($evento)] = str_replace("'", "\"", $accion);
  }

  public function sup_eventos($evento = null) {
    if ($evento == null)
      $this->eventos = null;
    else
      unset($this->eventos[$evento]);
  }

  public function envia_SUBMIT($evento, $msxConfirma = null, $ancla_envia = null) {
    if ($evento == null) die('Control.envia_SUBMIT(), $evento == null.');

    if ($msxConfirma == null) if ($this->msxConfirma != null) $msxConfirma = $this->msxConfirma;

    if ($ancla_envia == null) if ($this->ancla != null) $ancla_envia = $this->ancla;

    $this->pon_eventos($evento, "enviaEventoSubmit(this, '{$evento}', '{$msxConfirma}', '{$ancla_envia}')");
  }

  public function envia_AJAX($evento, $msxConfirma = null, $js_post_callback = "null", $js_pre_callback = "null") {
    if ($evento == null) die('Control.envia_AJAX(), $evento == null.');

    if ($msxConfirma == null) if ($this->msxConfirma != null) $msxConfirma = $this->msxConfirma;

    $this->pon_eventos($evento, "enviaEventoAJAX(this, '{$evento}', '{$msxConfirma}', {$js_post_callback}, {$js_pre_callback})");
  }

  public function envia_MIME($evento, $msxConfirma = null) {
    if ($evento == null) die('Control.envia_MIME(), $evento == null.');

    if ($msxConfirma == null) if ($this->msxConfirma != null) $msxConfirma = $this->msxConfirma;

    $this->pon_eventos($evento, "enviaEventoMime(this, '{$evento}', '{$msxConfirma}')");
  }

  public function tipoAJAX() { //* plantilla para implementar IAJAX.
    return $this->tipo_AJAX;
  }

  public function atributosAJAX() { //* plantilla para implementar IAJAX.
    if (($class_r = $this->clase_css("readonly")) == "") $class_r = $this->clase_css("default");

    return array("nome"     => $this->nome_completo(),
                 "readonly" => $this->readonly,
                 "visible"  => ( ($this->visible)?$this->ajax_css_display:"none" ),
                 "clase"    => ( ($this->readonly)?$class_r:$this->clase_css("default") )
                );
  }

  public function fillosAJAX() { //* plantilla para implementar IAJAX.
    return $this->valor();
  }

  protected function html_nome() {
    $html_id = $this->nome_completo();

    $html_m = ($this->multiple)?"[]":"";

    $html_name = ($this->html_name != null)?$this->html_name:$html_id;

    return "id='{$html_id}' name='{$html_name}{$html_m}'";
  }

  protected function html_estilo() {
    $style_r = ($this->style("readonly") == "")?$this->style("default"):$this->style("readonly");

    $style = ($this->readonly)?$style_r:$this->style("default");
    if (!$this->visible) $style .= "display:none;";

    if (($style = trim($style ?? '')) == "") return "";

    return "style='{$style}'";
  }

  protected function html_clase() {
    if (($class_r = $this->clase_css("readonly")) == "") $class_r = $this->clase_css("default");

    $class = ($this->readonly)?$class_r:$this->clase_css("default");

    if ($class == null) return "";

    return "class='{$class}'";
  }

  protected function html_atr() {
    if (!is_array($this->_atr)) return "";

    $html = "";
    foreach ($this->_atr as $k=>$v) $html .= "{$k}='{$v}' ";


    return $html;
  }

  protected function html_eventos() {
    if ($this->readonly       ) return "";
    if ($this->eventos == null) return "";

    if (!is_array($this->eventos)) die("ERROR: Control.html_eventos(), <b>{$this->nome}.eventos non &eacute; un array</b>.");

    $eventos = "";
    foreach ($this->eventos as $e=>$o) $eventos .= " {$e}='{$o}'";

    return $eventos;
  }

  protected function html_ancla() {
    if ($this->ancla == null) return "";

    return "<a name='{$this->ancla}'></a>";
  }

  protected function html_title() {
    if ($this->title !== null) return "title='{$this->title}'";
  }

  protected function html_placeholder() {
    if ($this->placeholder !== null) return " placeholder=\"{$this->placeholder}\"";
  }

  protected function html_multiple() {
    if (!$this->multiple) return "";

    return " multiple=\"true\"";
  }

  protected function html_etq() {
    if($this->n_etq == null) return "";

    return $etq_html = "<label for =" . $this->nome_completo() . " id='label_{$this->nome}'>" . $this->n_etq . "</label>";
  }

/*
  protected function _htmljson_dato($_subdato = null) {
    $_dato = parent::_htmljson_dato($_subdato);

    $_dato['_atr'   ] = $this->_atr;
    $_dato['eventos'] = $this->eventos;
    $_dato['title'  ] = $title;
    $_dato['valor'  ] = $this->valor();

    if ($this->ancla != null) $_dato['ancla']  = $this->ancla;

    $_dato['valor'  ] = $this->valor();

    return $_dato;
  }
*/
}
