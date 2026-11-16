<?php

/** Representa ao elemento HTML form.
 *
 * @package tilia\vista
 */


class Formulario extends Escritor_html {

  public $action = "index.php";

  public $enctype = "multipart/form-data";

  public $pai = null;

  public $paxina = null;

  protected $obxetos = null;

  protected $paneis = null;

  protected $evento = null;

  public function __construct($nome, $ptw = null, $action = "index.php") {
    parent::__construct($nome);

    $this->ptw = $ptw;
    $this->action = $action;
  }

  public function operacion(EstadoHTTP $e) {
    if ($this->evento()->tipo() == null) return null;

    if ($this->paneis != null)
      foreach ($this->paneis as $p)
        if (($e2 = $p->operacion($e)) != null) return $e2;

    if ($this->obxetos != null)
      foreach ($this->obxetos as $o)
        if (($e2 = $o->operacion($e)) != null) return $e2;

    return null;
  }

  public function formulario() {
    return $this;
  }

  public function evento(EventoHTTP $ev = null) {
    if ($ev != null) $this->evento = $ev;

    if ($this->evento == null) return new EventoHTTP_nulo();

    return $this->evento;
  }

  public function cambiar_idioma(Idioma $idioma = null) {
    if ($this->obxetos != null)
      foreach($this->obxetos as $o) if ($o != null) $o->cambiar_idioma($idioma);

    if ($this->paneis != null)
      foreach($this->paneis as $p) if ($p != null) $p->cambiar_idioma($idioma);
  }

  final public function declara_obxetos(Panel $p = null) {
    if ($this->paxina == null) die("Formulario.declara_obxetos(), this.paxina == null");

    if ($p != null) {
      $a_o = $p->declara_obxetos();

      if (!is_array($a_o)) return;

      foreach ($a_o as $o) $this->paxina->pon_obxeto($o);

      return;
    }

    if ($this->paneis == null) return;

    foreach($this->paneis as $p) if ($p != null) $this->declara_obxetos($p);
  }

  public function post($post = null) {
    //~ echo "<div style='color: #ba0000;'><b>Formulario.post()</b>::" . print_r($post, true) . "</div>";

    if ($post == null) {
      $this->evento = null;

      return true;
    }

    foreach ($post as $k=>$v) {
      if ($k == "evento") {
        $this->evento = new EventoHTTP($v);

        return true;
      }

      $a_k = explode(self::cnome, $k);

      //* pode ser un Param ou un ObxetoUI declarado directamente neste formulario, senon será un parámetro para 'request';
      if ($a_k[0] == $k) {
        if ($this->obxeto($k) == null) return false;

        $this->obxeto($k)->post($v);

        return true;
      }

      //* pode ser un componente, declarado directamente neste formulario;
      if ($this->obxeto($a_k[0]) != null)
        if ($this->obxeto($a_k[0])->pai == $this) {
          $this->obxeto($a_k[0])->post(array(substr($k, strlen($a_k[0] . self::cnome))=>$v));

          return true;
        }

      return false;
    }
  }

  public function panel($nome) {
    if (($p = $this->paneis[$nome]) == null) die("Formulario.panel(), this.paneis[{$nome}] == null");

    for ($p_aux = $p->decorator(); $p_aux != null; $p_aux = $p_aux->decorator()) {
      if ($p_aux == null) return $p;

      $p = $p_aux;
    }

    return $p;
  }

  public function pon_panel(Panel $panel) {
    $this->paneis[$panel->nome()] = $panel;

    if ($this->paxina != null) $this->declara_obxetos($panel);

    if (($a_css = $panel->declara_css()) != null) foreach($a_css as $css) $this->paxina->head->pon_css($css);
    if (($a_js = $panel->declara_js()) != null) foreach($a_js as $js) $this->paxina->head->pon_js($js);
  }

  public function sup_panel($nome = "centro") {
    $this->paneis[$nome] = null;
  }

  public function obxeto($nome) {
    if (!isset($this->obxetos[$nome])) return null;

    return $this->obxetos[$nome];
  }

  public function pon_obxeto(ObxetoFormulario $o) {
    $this->obxetos[$o->nome] = $o;

    $o->pai = $this;
  }

  final public function obxetos() {
    return $this->obxetos;
  }

  public function readonly($b = true) {
    if ($this->obxetos == null) return;

    foreach ($this->obxetos as $o) $o->readonly($b);

    if ($this->paneis == null) return;

    foreach ($this->paneis as $p) $p->readonly($b);
  }

  public function html():string {
    $this->evento = null;

    if ($this->ptw == null) die("Formulario.html(), this.ptw == null");

    $html = parent::html();

    if ($this->paneis != null)
      foreach ($this->paneis as $k=>$panel) {
        if ($panel == null)
          $html = str_replace("[{$k}]", "", $html);
        else
          $html = str_replace("[{$k}]", $panel->html(), $html);
      }

    if ($this->obxetos != null) {
      foreach($this->obxetos as $k=>$o) {
        $m = "[{$k}]";

        if (strpos($html, $m) === FALSE) continue;

        $html = str_replace("[{$k}]", $o->html() ?? "", $html);
      }
    }

    $evento = new Hidden("evento");
    $evento->pai = $this;

    $local  = ($this->obxeto("location") == null)?"":$this->obxeto("location")->html();

    return "
<form name='{$this->nome}' action='{$this->action}' enctype='{$this->enctype}' method='POST' >
  " . $evento->html() . $local . "
  {$html}
</form>";
  }

}

