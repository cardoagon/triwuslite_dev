<?php


final class Link_pax extends Span {
  public $id_oms = null;
  public $id_pax = null;

  public $href   = null;

  public function __construct(IMenuSite_paxina $pax, $edicion) {
    parent::__construct("loms", self::__etq($pax->atr("nome")->valor));


    $this->id_oms = $pax->atr("id_opcion")->valor;
    $this->id_pax = $pax->atr("id_paxina")->valor;

    $this->title  = $pax->atr("descricion")->valor;

    //~ $this->style("default", "cursor: pointer; display: inline;");


    if ($edicion  == 2 ) {
      //~ if ($this->id_pax > 0) $this->envia_SUBMIT("onclick");
      if ($this->id_pax > 0) $this->pon_eventos("onclick", "editor_enviaEvento(this, true)");
      
      return;
    }

    if ($pax->atr("estado")->valor != "ponte") {
      $b = $edicion == 1;
      
      if (($this->href = $pax->action_custom($b)) == null) $this->href = $pax->action_2($b);
    }

//~ echo $pax->atr("id_paxina")->valor . "::" . $pax->atr("nome")->valor . "::" . $this->href . "<br>";
  }

  public function operacion(EstadoHTTP $e) {
    if ($this->control_evento()) return Efspax_xestor::operacion_redirect($e, $this->id_pax, 0);


    return null;
  }

  private static function __etq($v) {
    return wordwrap($v, 55, "<br />", false);
  }
}

