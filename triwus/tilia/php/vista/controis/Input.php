<?php


/** Representa ao elemento html <input>
 *
 * @package tilia\vista\controis\input
 */

class Input extends Control implements IAJAX {
  public $type = null;

  public $accept = null;

  public $autocomplete = null;
  public $autofocus = false;
  public $checked = false;
  //public $height = null;  //* pixels %  Defines the height of an input (for type="image")
  //public $list = null; //*New datalist-id Refers to a datalist containing predefined options for the input field
  public $maxlength = null;
  public $max = null; //* number date Specifies the input fields maximum value. Use together with the "min" attribute to create a range of legal values
  public $min = null; //* number date Specifies the input fields minimum value. Use together with the "max" attribute to create a range of legal values
  public $step = null; //*  New number  Specifies the legal number intervals for the input field

  public $formtarget = null; //* New (_blank, _self, _parent, _top, framename)   Overrides the form's target attribute. Specifies the target window used when the form is submitted (for type="submit" and type="image")

  public $pattern = null; //* New regexp_pattern  Specifes a pattern or format for the input field's value. Example: pattern="[0-9]" means that the input value must be a number between 0 an 9
  public $required = false; //* New required  Indicates that the input field's value is required in order to submit the form.
  public $size = null;
  public $src = null; //* URL Specifies the URL of the image to display (for type="image")


  public function  __construct($nome, $type) {
    parent::__construct($nome);

    $this->type = $type;

    $this->tipo_AJAX     = "INPUT";
    $this->tipo_htmljson = "INPUT";
  }

  protected function html_accept() {
    if ($this->accept === null) return;

    return " accept = \"{$this->accept}\"";
  }

  protected function html_autocomplete() {
    if ($this->autocomplete == null) return;

    return " autocomplete = \"{$this->autocomplete}\"";
  }

  protected function html_autofocus() {
    if ($this->autofocus) return " autofocus ";
  }

  protected function html_checked() {
    if ($this->checked) return " checked ";
  }

  protected function html_formtarget() {
    if ($this->formtarget !== null) return " formtarget = \"{$this->formtarget}\"";
  }

  protected function html_max() {
    if ($this->max !== null) return " max=\"{$this->max}\"";
  }

  protected function html_min() {
    if ($this->min !== null) return " min=\"{$this->min}\"";
  }

  protected function html_step() {
    if ($this->step !== null) return " step=\"{$this->step}\"";
  }

  protected function html_pattern() {
    if ($this->pattern !== null) return " pattern=\"{$this->pattern}\"";
  }

  protected function html_placeholder() {
    if ($this->placeholder !== null) return " placeholder=\"{$this->placeholder}\"";
  }

  protected function html_readonly() {
    if ($this->readonly) return " readOnly=\"true\" ";

    return "";
  }

  protected function html_required() {
    if ($this->required) return " required ";
  }

  protected function html_maxlength() {
    if ($this->maxlength !== null) return "maxlength='{$this->maxlength}'";
  }

  protected function html_size() {
    if ($this->size !== null) return "size='{$this->size}'";
  }

  protected function html_src() {
    if ($this->src !== null) return "src='{$this->src}'";
  }

  protected function html_value() {
    if (is_array($this->valor())) return null;

    if ($this->valor() !== null) return " value=\"" . $this->valor() . "\"";
  }

// 14/04/2016 > Añadidos $this->html_etq() + $this->html_etq_der() para habilitar los Label.
// 19/04/2016 > Quitamos o anterior añadimos o apartado $html_etq cas variables para poñela na posición desexada.
  public function html():string {
    $html = $this->html_ancla() . "
<input type=\"{$this->type}\"
  " . $this->html_nome() . "
  " . $this->html_src() . "
  " . $this->html_atr() . "
  " . $this->html_readonly() . "
  " . $this->html_eventos() . "
  " . $this->html_checked() . "
  " . $this->html_accept() . "
  " . $this->html_formtarget() . "
  " . $this->html_title() . "
  " . $this->html_clase() .  "
  " . $this->html_estilo() .  "
  " . $this->html_size() .  "
  " . $this->html_maxlength() .  "
  " . $this->html_multiple() .  "
  " . $this->html_autocomplete() .  "
  " . $this->html_autofocus() .  "
  " . $this->html_min() . "
  " . $this->html_max() . "
  " . $this->html_step() . "
  " . $this->html_pattern() .  "
  " . $this->html_placeholder() .  "
  " . $this->html_required() .  "
  " . $this->html_value() .  "/>";


  $etq = $this->html_etq();

// 19/04/2016
    if($this->posicion != null) {
      switch($this->posicion) {
        case 1: // arriba > pos_arriba
          if ($etq != "") $etq .= "<br/>";

          $html = $etq . $html;
          break;
        case 2: // derecha > pos_dereita
          $html = $html . $etq;
          break;
        case 3: // abajo > pos_ abaixo
          if ($etq != "") $html .= "<br/>";

          $html = $html . $etq;
          break;
        case 4: // izquierda > pos_esquerda
          $html = $etq . $html;
          break;
      }
    }
// ---------------------------------------------------------

    $html = str_replace(array("\n", "\r", "    "), array("", "", " "), $html);
    $html = str_replace("   ", " ", $html);

    return str_replace("  ", " ", $html);
  }
}
// ---------------------------------------------------------------------------

/** Representa ao elemento html &lt;input type='hidden' /&gt;
 *
 * @package tilia\vista\controis\input
 */

class Hidden extends Input {
  public function  __construct($nome, $value = null) {
    parent::__construct($nome, "hidden");

    $this->post($value);
  }

  protected function html_etq() {
    return "";
  }
}


/** Representa ao elemento html &lt;input type='text' /&gt;
 *
 * @package tilia\vista\controis\input
 */

class Text extends Input {
  public $placeholder = null;

  public function  __construct($nome, $readonly = false) {
    parent::__construct($nome, "text");

    $this->readonly = $readonly;

    $this->clase_css("default" , "text"  );
    $this->clase_css("readonly", "text_r");
  }

  public function valor() {
    return strval($this->valor);
  }
}


/** Representa ao elemento html &lt;input type='date' /&gt;
 *
 * @package tilia\vista\controis\input
 */

class DataHoraInput extends Input {
  public $placeholder = null;

  public function  __construct($nome, $readonly = false) {
    parent::__construct($nome, "datetime-local");

    $this->readonly = $readonly;
  }
}


/** Representa ao elemento html &lt;input type='date' /&gt;
 *
 * @package tilia\vista\controis\input
 */

class DataInput extends Input {
  public $placeholder = null;

  public function  __construct($nome, $readonly = false) {
    parent::__construct($nome, "date");

    $this->readonly = $readonly;
  }
}

/** Representa ao elemento html &lt;input type='time' /&gt;
 *
 * @package tilia\vista\controis\input
 */

class HoraInput extends Input {
  public $placeholder = null;

  public function  __construct($nome, $readonly = false) {
    parent::__construct($nome, "time");

    $this->readonly = $readonly;
  }
}


/** Representa ao elemento html <input type='password' />
 *
 * @package tilia\vista\controis\input
 */

class Password extends Text {

  public $ollo;

  public function  __construct($nome, $readonly = false, $ollo = false) {
    parent::__construct($nome, $readonly);

    $this->type = "password";
    $this->ollo = $ollo;
  }

  public function html():string {
    $html = parent::html();

    if (!$this->ollo) return $html;

    return "<div class='tilia-form__group--pass'> "
               . $html . "
               <span class='tilia-form__ollo' onclick='tilia_mostra_contrasinal(this)'></span>
            </div>";
  }
}

/** Representa ao elemento html <input type='search' />
 *
 * @package tilia\vista\controis\input
 */

class Search extends Text {
  public function  __construct($nome, $readonly = false) {
    parent::__construct($nome, $readonly);

    $this->type = "search";
  }
}


/** Representa ao elemento html <input type='url' />
 *
 * @package tilia\vista\controis\input
 */

class Url extends Text {
  public function  __construct($nome, $readonly = false) {
    parent::__construct($nome, $readonly);

    $this->type = "url";
  }
}


/** Representa ao elemento html <input type='email' />
 *
 * @package tilia\vista\controis\input
 */

class Email extends Text {
  public function  __construct($nome, $readonly = false) {
    parent::__construct($nome, $readonly);

    $this->type = "email";
  }
}


/** Representa ao elemento html <input type='color' />
 *
 * @package tilia\vista\controis\input
 */

class Color extends Text {
  public function  __construct($nome, $hex = "transparent") {
    parent::__construct($nome, false);

    $this->type = "color";
  }
}


/** Representa ao elemento html <input type='number' />
 *
 * @package tilia\vista\controis\input
 */

class Number extends Input {
  public function  __construct($nome, $min = null, $max = null, $step = null) {
    parent::__construct($nome, "number");

    $this->min = $min;
    $this->max = $max;

    $this->step = $step;

    $this->clase_css("default", "number");
    $this->clase_css("readonly", "number_r");
  }
}


/** Representa ao elemento html range
 *
 * @package tilia\vista\controis\input
 */

class Range extends Number {
  public $uds = null;

  public function  __construct($nome, $min = null, $max = null, $step = null, $uds = null) {
    parent::__construct($nome, $min, $max, $step);

    $this->type = "range";

    $this->uds = $uds;

    $this->pon_eventos("oninput", "tilia_range_etq(this)");

    $this->style("default" , "cursor: pointer;");
    $this->style("readonly", "cursor: pointer;");
  }

  public function post($post) {
    parent::post($post);

    $this->title = $this->valor . $this->uds;
  }

  public function html():string {
    if (!$this->visible) return "";

    $id_etq = $this->nome_completo() . Escritor_html::csubnome . "etq";

    return "<div style='white-space: nowrap;'>
              <div style='display: inline-block;'>{$this->min}{$this->uds}</div>
              <div style='display: inline-block; vertical-align: middle;'>" . parent::html() . "</div>
              <div style='display: inline-block;' id='{$id_etq}'>{$this->valor}</div>
              <div style='display: inline-block;'> / {$this->max}{$this->uds}</div>
            </div>";
  }

}


/** Representa ao elemento html range
 *
 * @package tilia\vista\controis\input
 */

class MultiRange extends Number {
  public $uds = null;

  public function  __construct($nome, $min = null, $max = null, $step = null, $uds = null) {
    parent::__construct($nome, $min, $max, $step);

    $this->type = "range";

    $this->multiple = true;

    $this->uds = $uds;

    $this->post("{$this->min},{$this->max}");

    $this->pon_eventos("oninput", "tilia_multirange_etq(this, '{$uds}')");

    $this->style("default" , "cursor: pointer;");
    $this->style("readonly", "cursor: pointer;");
  }

  public function post($post) {
    //* 20180606 BETA.
    if (is_array($post)) return;

    if ($post == null) {
      parent::post(null);

      return;
    }

    list($m, $M) = explode(",", $post);

    if ($m > $M) list($M, $m) = $post;

    parent::post("{$m},{$M}");
  }

  public function html():string {
    if (!$this->visible) return "";

    $id_etq_0 = $this->nome_completo() . Escritor_html::csubnome . "etq_0";
    $id_etq_1 = $this->nome_completo() . Escritor_html::csubnome . "etq_1";


    list($m, $M) = explode(",", $this->valor());


    return "<div style='white-space: nowrap;'>
              <div style='display: inline-block;
                          width: 55px;
                          vertical-align: bottom;
                          text-align: right;
                          margin: 3px;' id='{$id_etq_0}'>{$m}{$this->uds}</div>
              <div style='display: inline-block;'>" . parent::html() . "</div>
              <div style='display: inline-block;
                          width: 55px;
                          vertical-align: bottom;
                          margin: 3px;' id='{$id_etq_1}'>{$M}{$this->uds}</div>
            </div>";
  }

}


/** Representa ao elemento html <input type='reset' />
 *
 * @package tilia\vista\controis\input
 */

class Reset extends Input {
  public function  __construct($nome, $value = null) {
    parent::__construct($nome, "reset");

    if ($value == null) $value = $this->nome;

    $this->post($value);

    $this->clase_css("default", "button");
    $this->clase_css("readonly", "button_r");
  }
}


/** Representa ao elemento html <input type='submit' />
 *
 * @package tilia\vista\controis\input
 */

final class Submit extends Reset {
  public function  __construct($nome, $value = null) {
    parent::__construct($nome, $value);

    $this->type = "submit";
  }

  public function control_evento() {
    $f = $this->formulario();

    $evento = $f->evento();

    return $evento->control_str($f->nome());
  }

  public function html():string {
    if (!$this->readonly) return parent::html();

    $this->type = "button";

    $html = parent::html();

    $this->type = "submit";

    return $html;
  }
}


class Checkbox extends Hidden {
  public function  __construct($nome, $checked = true, $etq = null, $posicion = 2) {
    parent::__construct($nome);

    $this->tipo_AJAX     = "CHECK";
    $this->tipo_htmljson = "CHECK";

    $this->pon_etq($etq, $posicion);

    $this->post((($checked)?"1":"0"));

    $this->clase_css("default" , "checker");
    $this->clase_css("readonly", "checker");

    $this->style("default" , "cursor: pointer;");

    $this->pon_eventos("onclick", "chekea(this, '', '', '')");
  }

  public function envia_SUBMIT($evento = null, $msgConfirmar = null, $ancla_envia = null) {
    $this->pon_eventos("onclick", "chekea(this, 'submit', '{$msgConfirmar}', '{$ancla_envia}')");
  }

  public function envia_AJAX($evento = null, $msgConfirmar = null, $js_post_callback = "null", $js_pre_callback = "null") {
    $this->pon_eventos("onclick", "chekea(this, 'ajax', '{$msgConfirmar}', '')");
  }

  public function html():string {
    if (!$this->visible) return "";

    $hidden = "<input type='hidden' " . $this->html_nome() . " " . $this->html_value() . " " . $this->html_atr() . "/>";

    return $hidden . $this->checker();
  }

  private function checker() {
    $ch = ($this->valor() == "1")?"checked":"";



    $checker = new Div($this->nome_completo() . self::csubnome . "checker");

    $id_aux = $this->nome_completo() . self::csubnome . "input";

    $checker->title = $this->title;

    $ro_onclick = "";
    if ($this->readonly) {
      $checker->clase_css("default", $this->clase_css("readonly"));
      $checker->style    ("default", $this->style("readonly"));

      $ro_onclick = " onclick='chequea_readonly(this)'";
    }
    else {
      $checker->clase_css("default", $this->clase_css("default"));
      $checker->style("default", $this->style("default"));
      $checker->eventos = $this->eventos;
    }

    $checker->post("<input id='{$id_aux}' type='checkbox' {$ch} {$ro_onclick}/><label>{$this->n_etq}</label>");

//~ echo $checker->html();

    return $checker->html();
  }
}


/** Representa ao elemento html <input type='radio' />
 *
 * @package tilia\vista\controis\input
 */

class Radio extends Hidden {
  const onclick = "predet";

  private $a_opcion = null;

  private $tipo_envio = null;

  public $horizontal = true;

  public $msgConfirmar = true;

// Variable añadida para habilitala posición do texto no RadioButton.  14/04/2016
  public $posicion = null;

// Modificado con el nuevo argumento $posicion para habilitar esta misma del Label. 14/04/2016 & 19/04/2016
  public function  __construct($nome, $horizontal = true, $posicion = 2) {
    parent::__construct($nome, 0);

    $this->tipo_AJAX     = "BALEIRO";
    $this->tipo_htmljson = "RADIO";

    $this->horizontal = $horizontal;
    $this->posicion   = $posicion; // ---

    $this->clase_css("iten", "radio_iten");

    $this->pon_eventos("onclick", self::onclick);
  }

  final public function pon_opcion($opcion) {
    $this->a_opcion[] = $opcion;

    if ($this->a_opcion === null) $this->post(0);
  }

  final public function colle_opcion($i_opcion) {
    return $this->a_opcion[$i_opcion];
  }

  final public function limpa_opcions($i_opcion = null) {
    if ($i_opcion !== null)
      unset($this->a_opcion[$i_opcion]);
    else
      $this->a_opcion = null;
  }

  public function envia_SUBMIT($evento, $msgConfirmar = "", $ancla_envia = "") {
    if ($msgConfirmar != null) $this->msgConfirmar = $msgConfirmar;

    $this->ancla = $ancla_envia;

    $this->tipo_envio = "submit";
  }

  public function envia_AJAX($evento = null, $msgConfirmar = "", $js_post_callback = "null", $js_pre_callback = "null") {
    if ($msgConfirmar != null) $this->msgConfirmar = $msgConfirmar;

    $this->tipo_envio = "ajax";
  }

  public function html():string {
    if ($this->valor === null) $this->post(0);

    $s = parent::html();

    if (count($this->a_opcion) == 0) return $s;

    foreach($this->a_opcion as $i_opcion=>$opcion) $s .= $this->html_opcion($i_opcion);

    return $s;
  }

  protected function colle_iEvento($i_opcion) {
    if ($this->readonly) return "onclick='radio_click(this, \"{$this->tipo_envio}\", {$i_opcion}, \"\", \"\")'";

    if ($this->eventos['onclick'] != self::onclick) return $this->html_eventos();

    //~ $this->pon_eventos("onclick", "radio_click(this, \"{$this->tipo_envio}\", {$i_opcion}, \"{$this->msgConfirmar}\", \"{$this->ancla}\")");
    $this->pon_eventos("onclick", "radio_click(this, \"{$this->tipo_envio}\", {$i_opcion}, \"\", \"{$this->ancla}\")");

    $s_accion = $this->html_eventos();

    $this->pon_eventos("onclick", self::onclick);

    return $s_accion;
  }

  private function html_opcion($i_opcion) {
    $s_cambio_opcion = "";

    $ncr = $this->nome_completo() . self::csubnome;

    $checked = ($i_opcion == $this->valor)?" checked = 'true'":"";

    $style = ($this->readonly)?"":" style='cursor: pointer;'";

    $s_radio = "<input type=radio id='{$ncr}{$i_opcion}' " . $this->html_readonly() . " name='{$ncr}rb'{$style}{$checked} " . $this->colle_iEvento($i_opcion) . "/>";

    $etq = new Div("{$ncr}etq_{$i_opcion}");

    $etq->readonly = $this->readonly;
    $etq->visible  = $this->visible;

    $etq_class = $this->clase_css("iten");

    //~ $etq_style = "";
    //~ if ($this->horizontal) $etq_style .= " float: left;";

    $etq->clase_css("default" , $etq_class);
    $etq->clase_css("readonly", $etq_class);

    //~ $etq->style("default" , $etq_style);
    //~ $etq->style("readonly", $etq_style);


    // 19/04/2016
    if($this->posicion != null) {
      switch($this->posicion) {
        case 1: // arriba > pos_arriba
          $etq->post("<label for='{$ncr}{$i_opcion}'>{$this->a_opcion[$i_opcion]}</label><br/>{$s_radio}{$s_cambio_opcion}");
          break;
        case 2: // derecha > pos_dereita
          $etq->post("{$s_radio}<label for='{$ncr}{$i_opcion}'>{$this->a_opcion[$i_opcion]}</label>{$s_cambio_opcion}");
          break;
        case 3: // abajo > pos_ abaixo
          $s = "<div style='display:flex; flex-direction: column;align-items: center;'>
                  <div>{$s_radio}</div>
                  <div><label for='{$ncr}{$i_opcion}'>{$this->a_opcion[$i_opcion]}</label>{$s_cambio_opcion}</div>
                </div>";
          $etq->post($s);
          break;
        case 4: // izquierda > pos_esquerda
          $etq->post("<label for='{$ncr}{$i_opcion}'>{$this->a_opcion[$i_opcion]}</label>{$s_radio}{$s_cambio_opcion}");
          break;
      }
    }
    // ----------------------------------------------------------------

    return $etq->html();
  }
}

/** Representa ao elemento html <input type='file' />
 *
 * @package tilia\vista\controis\input
 */

class File extends Input {

  public $maxFileSize = 1000000;

  private $tmp_c      = null;
  private $tmp_f      = [];

  public function  __construct($nome, $tmp_c = null, $maxFileSize = 1000000) {
    parent::__construct($nome, "file");

    if (($this->tmp_c = $tmp_c) != null) $this->pon_eventos("onchange", "tilia_subir_adxunto(this)");

    $this->clase_css("default" , "file"  );
    $this->clase_css("readonly", "file_r");

    $this->maxFileSize($maxFileSize);
  }

  public function maxFileSize($m) {
    $this->maxFileSize = $m;

    $this->title = "max. file size = " . self::html_bytes($m);
  }

  public function html():string {
    $hidden = new Hidden("MAX_FILE_SIZE", $this->maxFileSize);

    return $hidden->html() . parent::html();
  }

  public function f() {
    if ($this->tmp_c == null) return null;
    if ($this->tmp_f == null) return null;


    if (!$this->multiple) return $this->f_i(0);


    if ( !is_array($this->tmp_f) ) return null;

    $_f = [];
    foreach ($this->tmp_f as $i => $tmp_f) {
      if ($tmp_f == null) continue;

      $_f[] = $this->f_i($i);
    }

    return $_f;
  }

  public function post_f($renomear = true, $antigo = 555) {
    /*  move un arquivo vinculado na carpeta temporal, a unha carpeta 'de produción'.
     *
     * $renomear: 0 ou false: non renomea o arquivo.
     *            1 ou true : creará un nome aleatorio para un arquivo.
     *            2         : combina ambas.
     */


    if ($antigo > 0) XestorDescargas::borra_antigos($this->tmp_c, $antigo);

    if (!isset($_REQUEST["tsa_i"])) return null;

    if (($i = $_REQUEST["tsa_i"]) == 0) $this->tmp_f = [];


    $k = $this->nome_completo();

//~ echo "i::$i\n";
//~ print_r($_FILES);

    $this->tmp_f[$i] = self::calcula_nome( $_FILES[$k]["name"], $renomear, $i );

//~ print_r($this->tmp_f);

    $f = $this->f_i( $i );


    move_uploaded_file($_FILES[$k]["tmp_name"], $f);

//~ echo "f($i)::$f\n";

    return $f;
  }

  public function aceptar_f($src, $n = null, $zip = false) {
    /* move un arquivo vinculado na carpeta temporal, á carpeta 'de produción' $src.
     *
     * $zip: 0 ou false: non comprime.
     *       1 ou true : comprime imaxes, e ZIP para o resto de arquivos.
     *       2         : comprime so imaxes.
     */


    if ( !$this->multiple ) return $this->aceptar_f_i(0, $src, $n, $zip);


    if ( !is_array($this->tmp_f) ) return null;


    $_f = [];
    for ($i = 0; $i < count($this->tmp_f); $i++) {
      if ( ($f = $this->aceptar_f_i($i, $src, $n, $zip)) == null ) continue;

      $_f[] = $f;
    }

    return $_f;
  }

  public function vincular_f($url, $renomear = true) {
    //* vincula un arquivo do servidor ao control.

    $this->tmp_f = [];

    $this->tmp_f[ 0 ] = self::calcula_nome( $url, $renomear );

    $f = $this->f_i( 0 );

//~ echo "$url, $f<br>";

    copy($url, $f);

    return $f;
  }


  public static function comprimir(string $src) {
    $n0 = $src;
    $n1 = $n0;
    
    $_imxprops = getimagesize( $n0 );

    if ( $_imxprops[2] == IMAGETYPE_JPEG ) {   
      $imx_id = imagecreatefromjpeg($n0);
      
      $capa = self::imaxe_resize($imx_id, $_imxprops[0], $_imxprops[1]);
      
      imagejpeg($capa, $n1, 50);
    }
    elseif ( $_imxprops[2] == IMAGETYPE_GIF ) {  
      $imx_id = imagecreatefromgif($n0);
      
      $capa = self::imaxe_resize($imx_id, $_imxprops[0], $_imxprops[1]);
      
      imagegif($capa, $n1, 50);
    }
    elseif( $_imxprops[2] == IMAGETYPE_PNG ) {
      $imx_id = imagecreatefrompng($n0);

      imagealphablending($imx_id, false);
      imagesavealpha    ($imx_id, true );
      
      $capa = self::imaxe_resize($imx_id, $_imxprops[0], $_imxprops[1]);
      
      imagepng($capa, $n1, 5);
    }
    else {
      return "-1";
    }

    return $n1;
  }

  public function recortar($x, $y, $w, $h, $i = 0, $zip = true) {
//~ echo "$x, $y, $w, $h<br>";
    ini_set('memory_limit', '256M'); //* prevenir error "allowed memory size" durante la ejecución del script.


    $src = $this->f_i($i);

//~ echo "$src<br>";

    if (!is_file(($src))) return false;

    $tipo = exif_imagetype($src);

    $p1x = $x;
    $p1y = $y;
    $p2x = $w;
    $p2y = $h;

//~ echo "tipo: = ($tipo); p1 = ({$p1x}, {$p1y}); p2 = ({$p2x}, {$p2y})<br />\n";

    if     ($tipo == IMAGETYPE_JPEG) $imx0 = imagecreatefromjpeg($src);
    elseif ($tipo == IMAGETYPE_AVIF) $imx0 = imagecreatefromavif($src);
    elseif ($tipo == IMAGETYPE_WEBP) $imx0 = imagecreatefromwebp($src);
    elseif ($tipo == IMAGETYPE_PNG ) $imx0 = imagecreatefrompng ($src);
    elseif ($tipo == IMAGETYPE_GIF ) $imx0 = imagecreatefromgif ($src);
    else   {
      //echo "1.-Imaxe non válida, tipo=($tipo)<br>";

      return false;
    };

    $w1 = $w;
    $h1 = $h;

    $imx1 = imagecreatetruecolor($w1, $h1);

    if ($tipo == IMAGETYPE_PNG ) {
      imagealphablending($imx1, false);
      imagesavealpha    ($imx1, true );
    }

    imagecopyresampled($imx1, $imx0, 0, 0, $p1x, $p1y, $w1, $h1, $w1, $h1);

    //~ $id_file = uniqid( $carpeta );
    $tmp_f_2 = self::calcula_nome($src, true, $i);
    $id_file = $this->tmp_c . $tmp_f_2;

    $z = ($zip)?50:100;

    //~ echo "Imaxe 2=($id_file)<br>\n";
    if     ($tipo == IMAGETYPE_JPEG) imagejpeg($imx1, $id_file, $z     );
    elseif ($tipo == IMAGETYPE_AVIF) imageavif($imx1, $id_file); //* non empregamos compresión.
    elseif ($tipo == IMAGETYPE_WEBP) imagewebp($imx1, $id_file); //* non empregamos compresión.
    elseif ($tipo == IMAGETYPE_PNG ) imagepng ($imx1, $id_file, $z / 10);
    elseif ($tipo == IMAGETYPE_GIF ) imagegif ($imx1, $id_file, $z     );
    else {
      //~ echo "2.-Imaxe non válida, tipo=($tipo)<br>\n";

      imagedestroy($imx0);
      imagedestroy($imx1);

      return false;
    };


    imagedestroy($imx0);
    imagedestroy($imx1);


    $this->tmp_f[$i] = $tmp_f_2;


    return true;
  }

  public static function html_bytes($bytes) {
    $au = array("B", "KB", "MB", "GB", "TB");

    for($i_au = 0; (($bytes > 1024) && ($i_au < count($au))); $i_au++) $bytes /= 1024;

    $bytes = round($bytes, 1);

    return "{$bytes} {$au[$i_au]}";
  }

  public static function calcula_nome( $n, $renomear = true, $i = null ) {
    /* calcula o nome dun arquivo.
     *
     * $renomear: 0 ou false: mantén o nome orixinal do arquivo.
     *            1 ou true : creará un nome aleatorio para un arquivo.
     *            2         : combina ambas.
     */

    $_n = pathinfo( $n );
    
//~ echo "<pre>" . print_r($_n, 1) . "</pre>";

    $mt = md5(microtime());
    
    $renomear = intval($renomear);
    
        if ($renomear == 2) $n = "{$_n["filename"]}__" . substr($mt, 0, 7);
    elseif ($renomear == 1) $n = $mt;
    else                    $n = $_n["filename"];

    if ($i !== null) $n .= "." . strval($i);

    if ( !isset($_n["extension"]) ) return $n;
    if ($_n["extension"] == null) return $n;


    return "{$n}." . strtolower( $_n["extension"] );
  }


  private function f_i($i) {
//~ print_r($this->tmp_f);

    if ($this->tmp_c == null) return null;

    if ( !isset($this->tmp_f[$i]) ) return null;


    return $this->tmp_c . $this->tmp_f[$i];
  }

  private function aceptar_f_i($i, $src, $n, $zip) {
    /* move un arquivo vinculado na carpeta temporal, á carpeta 'de produción' $src.
     *
     * $zip: 0 ou false: non comprime.
     *       1 ou true : comprime imaxes, e ZIP para o resto de arquivos.
     *       2         : comprime so imaxes.
     */

    if (($n0 = $this->f_i($i)) == null) return null;

    $_n0 = pathinfo( $n0 );

    $ext = strtolower( $_n0["extension"] );


//~ print_r($_n0);

    if ($n == null) $n = $_n0["filename"];

    $n1 = "{$src}{$n}.";

    if ((filesize($n0) < 111111) || ($zip == 0)) {
      $n1 .= $ext;

      //~ copy($n0, $n1);
      if (!is_file($n1)) link($n0, $n1);

      return $n1;
    }


//~ print_r($n0);
//~ print_r($n1);

    //* zip. comprime arquivo.
    $_imxprops = getimagesize( $n0 );

//~ print_r($_imxprops);

    if ( $_imxprops[2] == IMAGETYPE_AVIF ) {
      $imx_id = imagecreatefromavif($n0);

      $capa = self::imaxe_resize($imx_id, $_imxprops[0], $_imxprops[1]);

      $n1 .= "avif";

//~ echo $n1 . "<br>";

      imagejpeg($capa, $n1, 50);
    }
    elseif ( $_imxprops[2] == IMAGETYPE_JPEG ) {
      $imx_id = imagecreatefromjpeg($n0);

      $capa = self::imaxe_resize($imx_id, $_imxprops[0], $_imxprops[1]);

      $n1 .= "jpg";

//~ echo $n1 . "<br>";

      imagejpeg($capa, $n1, 50);
    }
    elseif ( $_imxprops[2] == IMAGETYPE_GIF ) {
      $imx_id = imagecreatefromgif($n0);

      $capa = self::imaxe_resize($imx_id, $_imxprops[0], $_imxprops[1]);

      $n1 .= "gif";

      imagegif($capa, $n1, 50);
    }
    elseif( $_imxprops[2] == IMAGETYPE_PNG ) {
      $imx_id = imagecreatefrompng($n0);

      imagealphablending($imx_id, false);
      imagesavealpha    ($imx_id, true );

      $capa = self::imaxe_resize($imx_id, $_imxprops[0], $_imxprops[1]);

      $n1 .= "png";

      imagepng($capa, $n1, 5);
    }
    elseif ($zip == 1) {
      $z = new ZipArchive();
      $z->open("{$n0}.zip", ZIPARCHIVE::CREATE);

      $z->addFile($n0, "{$n}.{$ext}");

      $z->close();

      $n0 .= ".zip";
      $n1 .= "{$ext}.zip";

      //~ copy($n0, $n1);
      if (!is_file($n1)) link($n0, $n1);
    }
    else  {
      $n1 .= $ext;

      //~ copy($n0, $n1);
      if (!is_file($n1)) link($n0, $n1);

      return $n1;
    }

    return $n1;
  }


  private static function imaxe_resize($imx_id, $w, $h) {
    ini_set('memory_limit', '256M'); //* prevenir error "allowed memory size" durante la ejecución del script.


    $w2 = $w * 0.5;
    $h2 = $h * 0.5;

    $capa = imagecreatetruecolor($w2, $h2);

    imagecopyresampled($capa, $imx_id, 0, 0, 0, 0, $w2, $h2, $w, $h);

    return $capa;
  }

}
