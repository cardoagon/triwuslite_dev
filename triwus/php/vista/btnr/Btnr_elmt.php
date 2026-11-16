<?php

class Btnr_elmt extends Btnr {
  public function __construct($nome = "btnr") {
    parent::__construct($nome);

    $this->pon_obxeto(self::__bedit("Pulsa aqu&iacute; para &laquo;editar las propiedades&raquo; de la entrada", true));

    $this->pon_obxeto(self::__belmt("bMove"    , Ico::bmove    , "Pinchar y arrastrar para &laquo;mover&raquo; la entrada"  , true));
    $this->pon_obxeto(self::__belmt("bEditCopy", Ico::beditcopy, "Pinchar y arrastrar aqu&iacute; para &laquo;copiar&raquo; la entrada", true));

    $this->pon_obxeto(self::__beliminar("Pulsa aqu&iacute; para &laquo;eliminar&raquo; la entrada", true));

    $this->pon_obxeto(self::__capa());

    //~ $this->obxeto("bEditar")->envia_submit("onclick");
    $this->obxeto("bEditar"  )->pon_eventos("onclick", "editor_vm_esperar_abrir(this, 'vm_esperar', '', null)");
    
    $this->obxeto("bEliminar")->pon_eventos("onclick", "editor_vm_esperar_abrir(this, 'vm_esperar', '¿ Desea eliminar la entrada ?', function() {editor_enviaAJAX_resposta()} )");

    $this->obxeto("bMove"    )->pon_eventos("onmousedown", "detalle_btnr_bElmt_mousedown(this, 'celda_elmt_marcado')");
    $this->obxeto("bEditCopy")->pon_eventos("onmousedown", "detalle_btnr_bElmt_mousedown(this, 'celda_elmt_marcado')");

  }

  public function operacion(EstadoHTTP $e) {
    if ($this->obxeto("bEditar"  )->control_evento()) return $this->pai->operacion_editar  ($e);

    if ($this->obxeto("bEliminar")->control_evento()) return $this->pai->operacion_eliminar($e);

    return null;
  }

  public function html_json() {
    if (!$this->visible) return "null";

    $b1 = $this->obxeto("bEditar");
    $b2 = $this->obxeto("bMove");
    $b3 = $this->obxeto("bEditCopy");
    $b4 = $this->obxeto("bEliminar");

    $_json = array(self::boton_json($b1),
                   self::boton_json($b2),
                   self::boton_json($b3),
                   self::boton_json($b4)
                  );


    return json_encode($_json);
  }

  public function html():string {
    if (!$this->visible) return "";

    $b1 = $this->obxeto("bEditar")->html();
    $b2 = $this->obxeto("bMove")->html();
    $b3 = $this->obxeto("bEditCopy")->html();
    $b4 = $this->obxeto("bEliminar")->html();


    $this->obxeto("capa")->post("{$b1}{$b2}{$b3}{$b4}");

    $html = $this->obxeto("capa")->html();

    $this->obxeto("capa")->post(null);

    return $html;
  }

  public function html_eventos_mostrar(AElemento $ae) {
    $css      = $ae->clase_css("default");
    $css_edit = $ae->clase_css("edit");

    $nc = $this->obxeto("capa")->nome_completo();

    $onmouseover = "onmouseover = 'elmt_onmouseover(this, \"{$css_edit}\", \"{$nc}\", true)'";
    $onmouseout  = "onmouseout = 'elmt_onmouseout(this, \"{$css}\", \"{$nc}\", false)'";

    return "{$onmouseover} {$onmouseout}";
  }

  public function config_visibles($e = true, $c = false, $m = false, $b = false) {
    $this->obxeto("bEditar"  )->visible = $e;
    $this->obxeto("bEditCopy")->visible = $c;
    $this->obxeto("bMove"    )->visible = $m;
    $this->obxeto("bEliminar")->visible = $b;
  }

  public function config_e($e = true) {
    $this->config_visibles($e, false, false, false);
  }

  public static function boton_json(Control $b) {
    if (!$b->visible) return null;

    return array($b->nome(), $b->nome_completo(), $b->visible);
  }
}

//-------------------------------------

class Btnr_elmt_album extends Btnr_elmt {
  public function __construct($nome = "btnr") {
    parent::__construct($nome);

    $this->config_visibles(true, false, false, true);
  }
}

//-------------------------------------

class Btnr_elmt_imx extends Btnr_elmt {
  public function __construct($nome = "btnr") {
    parent::__construct($nome);

    $this->pon_obxeto(self::__belmt("bRecortar", Ico::brecortar, "Pulsa aqu&iacute; para &laquo;recortar&raquo; la imagen"  , true));

    //~ $this->obxeto("bRecortar")->envia_submit("onclick");
    $this->obxeto("bRecortar")->pon_eventos("onclick", "elmt_imx_brecortar_onclick(this)");
  }

  public function html():string {
    if (!$this->visible) return "";

    $b1 = $this->obxeto("bEditar")->html();
    $b2 = $this->obxeto("bMove")->html();
    $b3 = $this->obxeto("bEditCopy")->html();
    $b4 = $this->obxeto("bEliminar")->html();
    $b5 = $this->obxeto("bRecortar")->html();


    $this->obxeto("capa")->post("{$b1}{$b2}{$b3}{$b5}{$b4}");

    $html = $this->obxeto("capa")->html();

    $this->obxeto("capa")->post(null);

    return $html;
  }
}

//-------------------------------------

class Btnr_elmt_carrusel extends Btnr_elmt {
  public function __construct($nome = "btnr") {
    parent::__construct($nome);

    //~ $this->obxeto("bEditar")->envia_submit("onclick");
  }

  public function html():string {
    if (!$this->visible) return "";

    $b1 = $this->obxeto("bEditar"  )->html();
    $b3 = $this->obxeto("bEliminar")->html();


    $this->obxeto("capa")->post("{$b1}{$b3}");

    $html = $this->obxeto("capa")->html();

    $this->obxeto("capa")->post(null);

    return $html;
  }
}

//-------------------------------------

class Btnr_elmt_novas extends Btnr_elmt {

  public function __construct($nome = "btnr") {
    parent::__construct($nome);

    $this->obxeto("bEditCopy")->title = "Pincha aquí para duplicar la entrada";

    $this->obxeto("bEditCopy")->sup_eventos();
    $this->obxeto("bEditCopy")->pon_eventos("onclick", "editor_vm_esperar_abrir(this, 'vm_esperar', '', null)");
  }

  public function operacion(EstadoHTTP $e) {
    if ($this->obxeto("bEditCopy")->control_evento()) return $this->pai->operacion_bdupli($e);

    return parent::operacion($e);
  }
}

//-------------------------------------

class Btnr_elmt_novas2 extends Btnr_elmt {

  public function __construct($id_paxina, $nome = "btnr") {
    parent::__construct( $nome . Escritor_html::csubnome . $id_paxina );

    $this->obxeto("bEditCopy")->title = "Pincha aquí para duplicar la entrada";

    $this->obxeto("bEditar"  )->sup_eventos();
    //~ $this->obxeto("bMove"    )->sup_eventos();
    $this->obxeto("bEditCopy")->sup_eventos();
    $this->obxeto("bEliminar")->sup_eventos();
    
    $this->obxeto("bEditar"  )->pon_eventos("onclick", "novas2_editar_1_click({$id_paxina}, 'edit1')"  );
    //~ $this->obxeto("bMove"    )->pon_eventos("onclick", "detalle_btnr_bElmt_mousedown(this, 'celda_elmt_marcado')"  );
    $this->obxeto("bEditCopy")->pon_eventos("onclick", "novas2_editar_1_click({$id_paxina}, 'edit3')"  );
    $this->obxeto("bEliminar")->pon_eventos("onclick", "novas2_editar_1_click({$id_paxina}, 'suprime')");
  }

  public function operacion(EstadoHTTP $e) {
    if ($this->obxeto("bEditCopy")->control_evento()) return $this->pai->operacion_bdupli($e);

    return parent::operacion($e);
  }
}

//-------------------------------------

class Btnr_elmt_novas3 extends Btnr_elmt {

  public function __construct($id_paxina, $nome = "btnr") {
    parent::__construct( $nome . Escritor_html::csubnome . $id_paxina );

    $this->obxeto("bEditCopy")->title = "Pincha aquí para duplicar la entrada";

    $this->obxeto("bEditar"  )->sup_eventos();
    $this->obxeto("bEditCopy")->sup_eventos();
    $this->obxeto("bEliminar")->sup_eventos();
    
    $this->obxeto("bEditar"  )->pon_eventos("onclick"    , "novas2_editar_1_click({$id_paxina}, 'edit1')"  );
    $this->obxeto("bEditCopy")->pon_eventos("onclick"    , "novas2_editar_1_click({$id_paxina}, 'edit3')"  );
    $this->obxeto("bEliminar")->pon_eventos("onclick"    , "novas2_editar_1_click({$id_paxina}, 'suprime')");
  }

  public function operacion(EstadoHTTP $e) {
    if ($this->obxeto("bEditCopy")->control_evento()) return $this->pai->operacion_bdupli($e);

    return parent::operacion($e);
  }
}

