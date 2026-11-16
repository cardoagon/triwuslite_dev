<?php


/** Tipo de panel que agrupa a un conxunto de paneis que permite internavegar entre eles mediante "pesta&ntilde;as".
 *
 * @package tilia\vista\paneis
 */


class Panel_pestanhas extends Panel {
  const css_cab                    = "xp_cab";
  const css_det                    = "xp_det";
  const css_pestanha_inactiva      = "xp_pestanha_inactiva";
  const css_pestanha_inactiva_over = "xp_pestanha_inactiva_over";
  const css_pestanha_activa        = "xp_pestanha_activa";

  protected $pestanhas = null;

  protected $pestanha_activa = null;

  protected $redeclarar = false;

  public function __construct($nome, $redeclarar = false) {
    parent::__construct($nome);

    $this->redeclarar = $redeclarar;

    $this->clase_css("cab"                   , self::css_cab);
    $this->clase_css("det"                   , self::css_det);
    $this->clase_css("pestanha_inactiva"     , self::css_pestanha_inactiva);
    $this->clase_css("pestanha_inactiva_over", self::css_pestanha_inactiva_over);
    $this->clase_css("pestanha_activa"       , self::css_pestanha_activa);
  }

  public function declara_css() {
    if ($this->decorator() == null) return array(Tilia::home() . "/css/xestor_pestanhas.css");

    return $this->decorator()->declara_css();
  }

  public function pon(Pestanha $p) {
    $p->clase_css("pestanha_inactiva", $this->clase_css("pestanha_inactiva"));
    $p->clase_css("pestanha_activa", $this->clase_css("pestanha_activa"));

    $this->pestanhas[$p->nome()] = $p;

    if ($this->pestanha_activa == null) $this->pestanha_activa = $p;
  }

  public function pestanha_activa() {
    if (($p = $this->pestanha_activa) == null) return null;

    if (($decorator = $p->decorator()) == null) return $p;

    return $decorator;
  }

  public function activar_pestanha(EstadoHTTP $e, $id_pestanha) {
    if (!isset($this->pestanhas[$id_pestanha])) die("Panel_pestanhas.activar_pestanha(), !isset(this.pestanhas['{$id_pestanha}'])");

    $this->pestanha_activa = $this->pestanhas[$id_pestanha];

    if (($this->redeclarar) || (!$this->pestanha_activa->declarada)) {
      $e->inicia_obxetosHttp($this->pestanha_activa);

      $this->pestanha_activa->declarada = true;
    }

    return $e;
  }

  public function cambiar_idioma(Idioma $idioma = null) {
    parent::cambiar_idioma($idioma);

    if ($this->pestanhas == null) return;

    foreach($this->pestanhas as $p) $p->cambiar_idioma($idioma);
  }

  public function operacion(EstadoHTTP $e) {
    if ($this->pestanhas == null) die("Panel_pestanhas.operacion(), this.pestanhas == null");

    $p_aux = null;
    foreach($this->pestanhas as $p) {
      if ($p->etq()->control_evento()) {
        $this->activar_pestanha($e, $p->nome);

        $e->redirect("?pth={$p->nome}");

        return $e;
      }
      elseif ($p->decorator() != null)
        if (($e_aux = $p->decorator()->operacion($e)) != null) return $e_aux;
    }

    return null;
  }

  public function declara_obxetos() {
    if ($this->pestanha_activa == null) die("Panel_pestanhas.declara_obxetos(), this.pestanha_activa == null");

    $pa = $this->pestanha_activa;

    $d = null;
    if ($pa->decorator() != null) $d = $pa->decorator()->declara_obxetos();
    elseif (($this->redeclarar) || (!$pa->declarada)) $d = $pa->declara_obxetos();

    foreach ($this->pestanhas as $p) if (!$p->declarada) $d[] = $p->etq();

    $pa->declarada = true;

    return $d;
  }

  public function html():string {
    if (!$this->visible) return "";

    if ($this->pestanhas       == null) die("Panel_pestanhas.html(), this.pestanhas == null");
    if ($this->pestanha_activa == null) die("Panel_pestanhas.html(), this.pestanha_activa == null");

    $p_pestanhas = "";
    foreach($this->pestanhas as $p) {
      $ohttp = $p->etq();

      $idtd = $ohttp->nome_completo() . Escritor_html::cnome . "td";

      $css      = "";
      $css_over = "";

      $actions  = "";

      if ($p->nome() == $this->pestanha_activa->nome()) {
        $ohttp->readonly = true;
        $css = $this->clase_css("pestanha_activa");
      }
      else {
        $ohttp->readonly = false;

        $css      = $this->clase_css("pestanha_inactiva");
        $css_over = $this->clase_css("pestanha_inactiva_over");

        $actions = ($this->readonly)?"":" onclick='__ptnh_onclick(this, \"{$ohttp->msxConfirma}\")' onmouseover='__ptnh_onmouseoverout(this, \"{$css_over}\")' onmouseout='__ptnh_onmouseoverout(this, \"{$css}\")'";
      }

      $p_pestanhas .= "<div id='{$idtd}' {$actions} class='{$css}' >[" . $ohttp->nome() . "]</div>";
    }

    $ptw_ptnh =  "<div class='" . $this->clase_css("cab") . "'><div style='clear: both;'>{$p_pestanhas}</div></div>
                  <div class='" . $this->clase_css("det") . "'>" . $this->pestanha_activa->html() . "</div>";

     if (($ptw = parent::html()) == null) return $ptw_ptnh;

     return str_replace("[{$this->nome}]", $ptw_ptnh, $ptw);
  }
}



/** Panel compatible cun Panel_pestanhas.
 *
 * @package tilia\vista\paneis
 */

abstract class Pestanha extends Panel {

  public $declarada   = false;
  public $msx_onclick = "";

  private $etq        = null;

  public function __construct($nome, $ptw = null) {
    parent::__construct($nome, $ptw);

    $this->etq($this->declara_etq($nome));
  }

  public function declara_etq($etq) {
    return new Link("link", $etq);
  }

  final public function etq(Control $o = null) {
    if ($o == null) return $this->etq;

    $o->nome = "{$this->nome}_etq";

    $this->etq = $o;
  }
}

