<?php

/** Representa unha páxina HTML. EstadoHTTP emprega esta clase para xerar a páxina HTML, para enviar ao navegador web.
 *
 * @package tilia\vista\paxina
 */

class Paxina extends Escritor_html {


  protected $formulario = null;

  public $head = null;

  public $body = null;

  public $id_idioma = null;

  public function __construct($titulo = "") {
    parent::__construct("paxina");

    $this->head = new PaxHeader($titulo);
    $this->body = new PaxBody();

    $this->pon_formulario();
  }

  public function formulario() {
    return $this->formulario;
  }

  public function pon_formulario(Formulario $f = null) {
    if ($f == null) $f = new Formulario("findex");

    $this->formulario = $f;

    if (($a_css = $f->declara_css()) != null) foreach($a_css as $css) $this->head->pon_css($css);
    if (($a_js = $f->declara_js()) != null) foreach($a_js as $js) $this->head->pon_js($js);

    $f->paxina = $this;

    if (($obxetos = $f->obxetos()) == null) return;

    foreach($obxetos as $o) {
      if (($a_css = $o->declara_css()) != null) foreach($a_css as $css) $this->head->pon_css($css);
      if (($a_js = $o->declara_js()) != null) foreach($a_js as $js) $this->head->pon_js($js);
    }
  }

  public function evento(EventoHTTP $ev = null) {
    return $this->formulario()->evento($ev);
  }

  public function cambiar_idioma(Idioma $idioma = null, Panel $p = null) {
    if ($this->formulario() == null) return;

    $this->formulario()->cambiar_idioma($idioma, $p);
  }

  public function pon_panel(Panel $panel, $posicion = null) {
    $this->formulario()->pon_panel($panel, $posicion);
  }

  final public function declara_obxetos(Panel $p = null) {
    if ($this->formulario() == null) return;

    $this->formulario()->declara_obxetos($p);

    if ($p == null) return;

    if (($a_css = $p->declara_css()) != null) foreach($a_css as $css) $this->head->pon_css($css);
    if (($a_js = $p->declara_js()) != null) foreach($a_js as $js) $this->head->pon_js($js);
  }

  public function obxeto($nome) {
    return $this->formulario()->obxeto($nome);
  }

  public function pon_obxeto(Escritor_html $o) {
    $this->formulario()->pon_obxeto($o);

    $this->head->pon_ehtml($o);
  }

  public function post($post) {
    //echo "<div style='color: #0000ba;'><b>Paxina.post()</b>::" . print_r($post, true) . "</div>";

    $this->formulario->post();

    //~ if (count($post) == 0) return null;
    if (!is_array($post)) return null;

    $request_aux = null;
    foreach ($post as $k=>$v) {
      $a_k = explode(self::cnome, $k);

      if ($a_k[0] == $this->formulario()->nome()) $k = substr($k, strlen($a_k[0] . self::cnome));

      if (!$this->formulario()->post(array($k=>$v))) $request_aux[$k] = $v;
    }

    //~ $this->evento()->ajax( $post['ajax'] );

    return $request_aux;
 }

  public function readonly($b = true) {
    $this->formulario()->readonly($b);
  }

  public function html():string {
    $si = ($this->id_idioma == null)?"":" lang='{$this->id_idioma}'";

    $ptw = parent::html();

    if ($ptw == null)
      $ptw = $this->formulario()->html();
    else
      $ptw = str_replace("[" . $this->formulario()->nome() . "]", $this->formulario()->html(), $ptw);

/* 
    return "
<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\"
\"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">
<html{$si}>\n" . $this->head->html() . $this->body->html() . $ptw  . "\n</body>\n</html>"; */

    return "<!DOCTYPE html>
            <html{$si}>\n" . $this->head->html() . $this->body->html() . $ptw  . "\n</body>\n</html>";

  }
}


/** Implementa o elemento <head> dunha páxina HTML.
 *
 * @package tilia\vista\paxina
 */

final class PaxHeader {
  public $base = null;

  public $canonical = null;

  public $titulo = null;

  public $icono = null;

  public $image_src = null;

  public $charset = "utf-8";

  public $javascript = null;

  private $css = null;

  private $js = null;

  private $og = null;

  private $links_ver = null;

  private $meta = null;


  public function __construct($titulo = "") {
    $this->titulo = $titulo;

    $Tiliahome = Tilia::home();

    $this->pon_css("{$Tiliahome}/css/basico.css");

    $this->pon_js("{$Tiliahome}/js/escritor_html.js");
    $this->pon_js("{$Tiliahome}/js/basico.js");
    //~ $this->pon_js("{$Tiliahome}/js/ajax.js");
    //~ $this->pon_js("{$Tiliahome}/js/ajax-2.js");
    $this->pon_js("{$Tiliahome}/js/ajax-3.js");
    $this->pon_js("{$Tiliahome}/js/reloxio.js");
    $this->pon_js("{$Tiliahome}/js/capa.js");

    $this->meta = array();
  }

  public function pon_meta($name, $content, $http_equiv = null) {
    $this->meta[$name] = new stdClass();

    $this->meta[$name]->content = $content;
    $this->meta[$name]->http_equiv = $http_equiv;
  }

  public function sup_meta($name = null) {
    if ($name == null)
      $this->meta = null;
    else
      unset($this->meta[$name]);
  }

  public function pon_ehtml(Escritor_html $ehtml) {
    if (($a_css = $ehtml->declara_css()) != null) foreach($a_css as $css) $this->pon_css($css);
    if (($a_js  = $ehtml->declara_js()) != null) foreach($a_js as $js) $this->pon_js($js);
  }

  public function sup_ehtml(Escritor_html $ehtml) {
    if (($a_css = $ehtml->declara_css()) != null) foreach($a_css as $css) $this->sup_css($css);
    if (($a_js = $ehtml->declara_js()) != null) foreach($a_js as $js) $this->sup_js($js);
  }

  public function links_ver($v = null) {
    $this->links_ver = $v;
  }

  public function pon_css($css) {
    if (($css = str_replace("//", "/", $css)) == null) return;

    $this->css[$css] = "x";
  }

  public function sup_css($css = null) {
    if ($css == null)
      $this->css = null;
    else
      unset($this->css[str_replace("//", "/", $css)]);
  }

  public function pon_og($p, $c) {
    if (( $v = trim(str_replace('"', '/"', $c)) ) == null) {
      $this->sup_og($p);

      return;
    }

    $this->og[$p] = $v;
  }

  public function sup_og($p = null) {
    if ($p == null)
      $this->og = null;
    else
      unset($this->og[$p]);
  }

  public function pon_js($js) {
    if (($js = str_replace("//", "/", $js)) == null) return;

    $this->js[$js] = "x";
  }

  public function sup_js($js = null) {
    if ($js == null)
      $this->js = null;
    else
      unset($this->js[str_replace("//", "/", $js)]);
  }

  public function pon_script($script) {
    if ($script == null) return;

    $this->javascript[] = $script;

  }

  public function html():string {
    $css = ""; $js = ""; $og = ""; $meta = ""; if (($lv = $this->links_ver) != null) $lv = "?cache={$lv}";

    if (is_array($this->og)) foreach ($this->og  as $k=>$v) $og  .= "<meta property=\"$k\" content=\"{$v}\" />\n";

    if (count($this->css) > 0) foreach ($this->css as $k=>$v) $css .= "<link rel=\"stylesheet\" href=\"{$k}{$lv}\" type=\"text/css\" />\n";
    if (count($this->js)  > 0) foreach ($this->js  as $k=>$v) $js  .= "<script type=\"text/javascript\" src=\"{$k}{$lv}\"></script>\n";

    if (count($this->meta) > 0) {
      foreach ($this->meta as $k=>$v) {
        if ($v->content == null) continue;

        if ($v->http_equiv != null)
          $meta .= "<meta http-equiv=\"{$v->http_equiv}\" content=\"{$v->content}\" />\n";
        else
          $meta .= "<meta name=\"{$k}\" content=\"{$v->content}\" />\n";
      }
    }

    $js2 = ""; if (is_array($this->javascript)) for ($i = 0; $i <  count($this->javascript); $i++) $js2 .= $this->javascript[$i];

    $icono = ($this->icono == null)?"":"<link rel=\"SHORTCUT ICON\" href=\"{$this->icono}\" />";

    $image_src = ($this->image_src == null)?"":"<link rel=\"image_src\" href=\"{$this->image_src}\" />";

    $base = ($this->base != null)?"<base href=\"{$this->base}\" />":"";

    $canonical = ($this->canonical != null)?"<link rel=\"canonical\" href=\"{$this->canonical}\" />":"";

    return "
  <head>
    {$base}
    {$canonical}
    <meta http-equiv=\"content-type\" content=\"text/html\" charset=\"{$this->charset}\" />
    <title>{$this->titulo}</title>
    {$icono}
    {$image_src}
    {$meta}
    {$og}
    {$css}
    {$js}
    {$js2}
  </head>";
  }
}



/** Implementa o elemento <body> dunha páxina HTML.
 *
 * @package tilia\vista\paxina
 */

final class PaxBody {

  public $t = "#000000";

  public $l = "#000000";

  public $vl = "#000000";

  public $al = "#000000";

  public $bg = null;

  public $bgcolor = "#ffffff";

  public $style = null;

  public $onload = "tilia_body_onload()";

  //~ public $menuContextual = true;

  //~ public $borraHistorial = false;

  //~ private $eventos = null;

  public function __construct() {}

  //~ public function sup_eventos() {
    //~ $this->eventos = null;
  //~ }

  //~ public function pon_eventos($evento = null, $accion = null) {
    //~ if ($evento == null) $this->sup_eventos();

    //~ $this->eventos[$evento] = str_replace("'", "\"", $accion);
  //~ }

  public function html():string {
    
    //~ $borraHistorial = ""; $menuContextual = "";

    //~ if ($this->borraHistorial ) $borraHistorial = "history.forward();";
    //~ if (!$this->menuContextual) $menuContextual = "document.oncontextmenu = function(){return false;}";

    $bg = ""; if ($this->bg != null) $bg = "background='{$this->bg}'";


    return "
  <body onload='{$this->onload}' bgcolor='{$this->bgcolor}' text='{$this->t}' link='{$this->l}' vlink='{$this->vl}' alink='{$this->al}' {$bg} " . $this->html_style() . ">";
  }
/*
  private function html_eventos() {
    if ($this->eventos == null) return "";

    if (!is_array($this->eventos)) die('ERROR: PaxBody.html_eventos(), !is_array($this->eventos)');

    $eventos = "";
    foreach ($this->eventos as $e=>$o) $eventos .= "{$e}='{$o}' ";

    return $eventos;
  }
*/
  private function html_style() {
    if ($this->style == null) return "";

    return "style='{$this->style}'";
  }
}

