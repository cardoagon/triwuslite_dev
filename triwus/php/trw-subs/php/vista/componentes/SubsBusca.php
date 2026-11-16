<?php

interface ISubsBusca {
  public function campos_busqueda_texto():array;   //* return String[]
  public function op_busca(ESubs $e, string $where):ESubs;
  public function op_csv  (ESubs $e, string $where):ESubs;
}

//************************************

class SubsBusca extends Componente {
  const ptw_0 = "ptw/componentes/comun/busca/0.html";

  public function __construct($ptw = null) {
    parent::__construct("cbusca");

    $this->ptw = ($ptw == null)?self::ptw_0:$ptw;

    $this->pon_obxeto(Panel_fs::__bbusca   ("bBuscar"  , null, null    ));
    $this->pon_obxeto(Panel_fs::__bcancelar(null       , null, "bLimpa"));
    $this->pon_obxeto(Panel_fs::__bexportar("bExportar", null, null    ));


    $this->pon_obxeto(self::_txt("txt"));

    $this->obxeto("txt")->placeholder = "Buscar ...";


    $this->obxeto("bLimpa")->visible = false;


    $this->obxeto("bBuscar")->envia_ajax("onclick");
    $this->obxeto("bLimpa" )->envia_ajax("onclick");
    //~ $this->obxeto("bBuscar")->envia_submit("onclick");
    //~ $this->obxeto("bLimpa" )->envia_submit("onclick");
  }

  public function expotar(bool $b) {
    $this->obxeto("bExportar")->visible = $b;
  }

  public function operacion(EstadoHTTP $e) {
    if ($this->obxeto("bExportar")->control_evento()) if ($this->obxeto("bExportar")->visible) return $this->operacion_exportar($e);
    
    if ($this->obxeto("bBuscar"  )->control_evento()) return $this->operacion_buscador($e);
    if ($this->obxeto("bLimpa"   )->control_evento()) return $this->operacion_limpa   ($e);

    return null;
  }

  public function sql_where() {
    $wh = Valida::buscador_txt2sql($this->obxeto("txt")->valor(), $this->pai->campos_busqueda_texto());

    if ($wh == null) $wh = "(1)";


    return $wh;
  }

  protected function operacion_buscador(ESubs $e, $blimpa = true):ESubs {
    $this->obxeto("bLimpa")->visible = $blimpa;
    
    return $this->pai->op_busca($e, $this->sql_where());
  }

  protected function operacion_exportar(ESubs $e):ESubs {
    return $this->pai->op_csv($e, $this->sql_where());
  }

  protected function operacion_limpa(ESubs $e):ESubs {
    $this->obxeto("txt")->post("");

    return $this->operacion_buscador($e, false);
  }

  protected static function _txt($id, $readonly = false) {
    $o = new Text($id);

    $o->clase_css("default" , null);
    $o->clase_css("readonly", null);

    $o->readonly = $readonly;

    return $o;
  }
}


