<?php


/** Tipo de panel que agrupa a un conxunto de paneis que permite internavegar entre eles coa t&eacute;cnica "paso a paso".
 *
 * @package tilia\vista\paneis
 */

abstract class Panel_multipaso extends Panel {
  const css_activo = "xmp_activo";
  const css_inactivo = "xmp_inactivo";
  const css_botonera = "xmp_botonera";

  private $paneis = null;
  private $ipanel = 0;

  protected $c_cancel = null;
  protected $c_end = null;
  protected $c_prev = null;
  protected $c_next = null;

  protected $redeclarar = false;

  public function __construct($nome, $ptw = null) {
    parent::__construct($nome, $ptw);

    $this->clase_css("activo", self::css_activo);
    $this->clase_css("inactivo", self::css_inactivo);
    $this->clase_css("botonera", self::css_botonera);

    $this->etq_cancel($this->declara_etq("Cancel"));
    $this->etq_end($this->declara_etq("OK"));
    $this->etq_prev($this->declara_etq("&lt;&lt;"));
    $this->etq_next($this->declara_etq("&gt;&gt;"));
  }

  public function cambiar_idioma(Idioma $idioma = null) {
    parent::cambiar_idioma($idioma);

    if ($this->paneis == null) return;

    foreach($this->paneis as $p) $p->cambiar_idioma($idioma);
  }

  public function declara_css() {
    return array(Tilia::home() . "css/xestor_multipaso.css");
  }

  public function pon(Panel_paso $p) {
    $this->paneis[] = $p;
  }

  protected function is_end() {
    return $this->ipanel != (count($this->paneis) - 1);
  }

  protected function operacion_cancel(EstadoHTTP $e) {
    return $e;
  }

  protected function operacion_end(EstadoHTTP $e) {
    return $e;
  }

  public function operacion(EstadoHTTP $e) {
    if ($this->c_cancel->control_evento()) return $this->operacion_cancel($e);

    if ($this->c_end->control_evento()) return $this->operacion_end($e);

    if ($this->c_prev->control_evento()) {
      if ($this->ipanel == 0) return $e;

      if (($e_aux = $this->paneis[$this->ipanel]->validate_prev($e)) != null) return $e_aux;

      $e_aux = $this->paneis[$this->ipanel]->operacion_prev($e);

      return $this->cambia_panel($e_aux, -1);
    }

    if ($this->c_next->control_evento()) {
      if (($this->ipanel + 1) == count($this->paneis)) return null;

      if (($e_aux = $this->paneis[$this->ipanel]->validate_next($e)) != null) return $e_aux;

      $e_aux = $this->cambia_panel($e, 1);


      return $this->paneis[$this->ipanel - 1]->operacion_next($e_aux);
    }

    return null;
  }

  public function declara_obxetos() {
    if ($this->paneis == null) die("Panel_multipaso.declara_obxetos(), this.paneis == null");

    $a1 = array($this->c_cancel, $this->c_prev, $this->c_next, $this->c_end);

    if (($this->redeclarar) || (!$this->pestanha_activa->declarada)) {
      $a2 = $this->paneis[$this->ipanel]->declara_obxetos();

      $this->panel[$this->ipanel]->declarada = true;
    }

    return array_merge((array)$a1, (array)$a2);
  }

  public function html():string {
    if (count($this->paneis) == 0) die("Panel_multipaso.html(), count(this.paneis) == 0");

    if (!$this->visible) return "";

    $html_0 = $this->paneis[$this->ipanel]->html() . self::html_botonera($this);


    if (($ptw = parent::html()) == "") return $html_0;

    return str_replace("[{$this->nome}]", $html_0, $ptw);
  }

  public function declara_etq($etq) {
    return new Button("b", $etq);
  }

  final public function etq_cancel(Control $c = null) {
    if ($c == null) return self::__control($this->c_cancel, "cancel");

    $this->c_cancel = self::__control($c, "cancel");
  }

  final public function etq_end(Control $c = null) {
    if ($c == null) return self::__control($this->c_end, "end");

    $this->c_end = self::__control($c, "end");
  }

  final public function etq_prev(Control $c = null) {
    if ($c == null) return self::__control($this->c_prev, "prev");

    $this->c_prev = self::__control($c, "prev");
  }

  final public function etq_next(Control $c = null) {
    if ($c == null) return self::__control($this->c_next, "next");

    $this->c_next = self::__control($c, "next");
  }

  private function __control(Control $c, $nome) {
    $c->nome = "{$this->nome}_{$nome}";

    $c->clase = $this->clase_css("activo");
    $c->clase_readonly = $this->clase_css("inactivo");

    $c->envia_SUBMIT("onclick");

    return $c;
  }

  private function cambia_panel(EstadoHTTP $e, $i) {
    if (!is_numeric($i)) die("Panel_multipaso.ipanel(), !is_numeric({$i})");

    $this->ipanel += round($i);

    if (($this->redeclarar) || (!$this->panel[$this->ipanel]->declarada))
      $e->inicia_obxetosHttp($this->panel[$this->ipanel]);

    return $e;
  }

  private static function html_botonera(Panel_multipaso $p) {
    $p->c_cancel->readonly(false);
    $p->c_prev->readonly($p->ipanel == 0);
    $p->c_next->readonly($p->ipanel >= (count($p->paneis) - 1));
    $p->c_end->readonly($p->is_end());

    return "<div class='" . $p->clase_css("botonera") . "'>
              " . $p->c_cancel->html() . "
              " . $p->c_prev->html() . "
              " . $p->c_next->html() . "
              " . $p->c_end->html() . "
            </div>";
  }
}



/** Panel compatible cun Panel_multipaso.
 *
 * @package tilia\vista\paneis
 */

abstract class Panel_paso extends Panel {
  public $declarada = false;

  public function __construct($nome, $ptw = null) {
    parent::__construct($nome, $ptw);
  }

  public function validate_prev(EstadoHTTP $e) {
    return null;
  }

  public function validate_next(EstadoHTTP $e) {
    return null;
  }

  public function operacion_prev(EstadoHTTP $e) {
    return $e;
  }

  public function operacion_next(EstadoHTTP $e) {
    return $e;
  }
}

