<?php

final class Cxpedidos extends Componente implements ICPC {
  const ptw_1 = "ptw/paneis/pcontrol/ventas/cxpedidos_1.html";

  public static $_ecolor   = array("A"=>"#F88602" , "D"=>"#ba0000" , "E"=>"#45a745");
  public static $_esimbolo = array("A"=>"+"       , "D"=>"&minus;" , "E"=>"&gt;"   );
  public static $_enome    = array("A"=>"Aceptado", "D"=>"Denegado", "E"=>"Enviado");

  public $id_site    = null;
  public $id_almacen = null;

  public function __construct($id_site, $id_almacen) {
    parent::__construct("cxpedidos", self::ptw_1);

//~ echo "Cxpedidos($id_site, $id_almacen)<br>";

    $this->id_site    = $id_site;
    $this->id_almacen = $id_almacen;

    $this->pon_obxeto(new Cxpedidos_ficha());

    $this->pon_obxeto(new Cxpedidos_pago());

    $this->pon_obxeto(new Cxpedidos_anexarFra());


    $this->pon_obxeto(new CXP_lista      ($id_site, $id_almacen));
    $this->pon_obxeto(new Cxpedidos_busca($id_site, $id_almacen));

    $this->pon_obxeto(new Hidden("hestado"));
  }

  public function cpc_miga() {
    return "ventas";
  }

  public function seleccionado() {
    return $this->obxeto("cxpf")->seleccionado();
  }

  public function operacion(EstadoHTTP $e) {
    if (($e_aux = $this->obxeto("cxpa")->operacion($e)) != null) return $e_aux;
    if (($e_aux = $this->obxeto("cxpp")->operacion($e)) != null) return $e_aux;
    if (($e_aux = $this->obxeto("cxpf")->operacion($e)) != null) return $e_aux;
    if (($e_aux = $this->obxeto("cxpl")->operacion($e)) != null) return $e_aux;
    if (($e_aux = $this->obxeto("cxpb")->operacion($e)) != null) return $e_aux;


    return null;
  }

  public function operacion_buscador(Epcontrol $e, $where = null) {
    //~ $id_a = $this->obxeto("cxpb")->id_almacen();

    $cxlp = $this->obxeto("cxpl");

    $cxlp->__where($where);


//~ echo $cxlp->sql_vista();


    return $e;
  }

  public function operacion_usuario(Epcontrol $e, $id_usuario = null) {
    $this->obxeto("cxpf")->ptw = null;

    $u = FGS_usuario::inicia(new FS_cbd(), $id_usuario);


    $this->preparar_saida($e->ajax());

    return $e;
  }

  public function operacion_pedido(Epcontrol $e, $a, $n) {
    $pe = Pedido_obd::inicia(new FS_cbd(), $this->id_site, $a, $n);

    $this->obxeto("cxpf")->pon_pedido($pe);

    $this->preparar_saida($e->ajax());

    return $e;
  }

  public function operacion_pago(Epcontrol $e, $_p) {
    list($ano, $numero) = explode("-", $_p);

    $p = Pedido_obd::inicia(new FS_cbd(), $this->id_site, $ano, $numero);

    $this->obxeto("cxpp")->post_pago($p);

    $this->preparar_saida($e->ajax());

    return $e;
  }

  public function operacion_lanexar(Epcontrol $e, $reanexar = false) {
    list($ano, $num) = explode("-", $e->evento()->subnome(0));

    $cbd = new FS_cbd();

    $p = Pedido_obd::inicia($cbd, $this->id_site, $ano, $num);

    $this->obxeto("cxpa")->pon_pedido( $p, $reanexar );

    $this->preparar_saida($e->ajax());

    return $e;
  }

  public function calcula_porcentaxe_envios($id_rama, $reset = false) {
    return $this->obxeto("cxpl")->calcula_porcentaxe_envios($id_rama, $reset);
  }

  public static function __iestado($estado) {
    $s = new Div("icoestado", "&nbsp;&nbsp;&nbsp;&nbsp;");

    $color_estado = self::$_ecolor[$estado[0]];

    $s->style("default", "width:11px; font-size: 9px; display: inline; background-color: {$color_estado};");


    return $s;
  }

  public static function __sestado($estado, $predet = null) {
    //~ $s = new Select("sestado", array('Aceptado' => "[" . self::$_esimbolo['A'] . "] Aceptado",
                                     //~ 'Denegado' => "[" . self::$_esimbolo['D'] . "] Denegado",
                                     //~ 'Enviado'  => "[" . self::$_esimbolo['E'] . "] Enviado "
                                    //~ )
                    //~ );

    $opt = ($predet == null)?null:array("**"=>$predet);

    $opt["Aceptado"] = "Aceptado";
    $opt["Denegado"] = "Denegado";
    $opt["Enviado" ] = "Enviado";


    $s = new Select("sestado", $opt);

    $s->post($estado);

    $s->pon_eventos("onchange", "cxpedido_cambiar_estado(this)");


    return $s;
  }

  public static function __gestado($_p, $lenda = false) {
    //~ if ($_p == null) return new Param("gestado");
    if ($_p == null) return null;

    $_p2   = self::$_enome;

    $css   = "float: left; height: 7px;";

    $d = ""; $l = ""; $t = "";
    foreach($_p as $k=>$v) {
      if ($v == 0) continue;

      if ($t != "") $t .= "\r\n";


      $t .= "{$_p2[$k]}\t = {$v}%";

      $d .= "<div style='{$css} width: {$_p[$k]}%; background-color: " . self::$_ecolor[$k] . ";'></div>";
    }

    if ($lenda) {
      $l = "<div style='white-space: pre-wrap; margin: 14px 0 3px 3px; font-weight: bold;'>{$t}</div>";
    }

    $d = new Div("gestado", $d . $l);

    $d->title = $t;

    $d->style("default", "clear: both; padding: 3%; cursor: pointer;");

    //~ $d->pon_eventos("onclick", "console.log(this)");


    return $d;
  }

}

//******************************************

final class Cxpedidos_busca extends Componente {
  const ptw_0 = "ptw/paneis/pcontrol/ventas/cxpedidos_busca.html";

  public function __construct($id_site, $id_almacen = null) {
    parent::__construct("cxpb", self::ptw_0);


    $this->pon_obxeto(Panel_fs::__bbusca   ("bBuscar", null, null));
    $this->pon_obxeto(Panel_fs::__bcancelar(null, null, "bLimpa"));

    $this->pon_obxeto(Panel_fs::__bexportar("bExcel" , "", "Exportar lista de pedidos a excel"));

    //~ $this->pon_obxeto(new Select("sestado", array("..."=>"...", "Aceptado"=>"Aceptado", "Denegado"=>"Denegado", "Enviado"=>"Enviado")));
    $this->pon_obxeto(new Select("spagado", array("..."=>"...", "n"=>"No", "s"=>"Si")));

    $this->pon_obxeto(Cxarticulos_busca::__salmacen($id_site, $id_almacen));

    $this->pon_obxeto(self::__sfpago  ($id_site));

    $this->pon_obxeto(self::__sservizo());

    $this->pon_obxeto(new DataInput("data_0"));
    $this->pon_obxeto(new DataInput("data_1"));
    $this->pon_obxeto(Panel_fs::__text("idCliente", 5, 10, " "));

    $this->obxeto("idCliente")->clase_css("default", "tbuscador");
    $this->obxeto("idCliente")->style    ("default", "text-align: right;width: 77px;");

    $this->obxeto("bExcel")->envia_MIME("onclick");

    $this->obxeto("bLimpa")->visible = false;
  }

  public function id_almacen() {
    return $this->obxeto("salmacen")->valor();
  }

  public function operacion(EstadoHTTP $e) {
    if ($this->obxeto("bBuscar")->control_evento()) return $this->operacion_bBuscar($e);
    if ($this->obxeto("bExcel" )->control_evento()) return $this->operacion_bExcel($e);
    if ($this->obxeto("bLimpa" )->control_evento()) return $this->operacion_bLimpa ($e);


    return null;
  }

  private function operacion_bExcel(Epcontrol $e) {
    $t = "ventas-" . date("YmdHis");

//~ Cxpedidos_excel::xls($t, $this->__where_ods());

    $e->pon_mime("{$t}.xls", "application/vnd.ms-excel", Cxpedidos_excel::xls($t, $this->__where_ods()), null);


    return $e;
  }

  private function operacion_bBuscar(EstadoHTTP $e, $blimpa = true) {
    $this->obxeto("bLimpa")->visible = $blimpa;

    $this->preparar_saida($e->ajax());

    return $this->pai->operacion_buscador($e, $this->__where());
  }

  private function operacion_bLimpa(EstadoHTTP $e) {
    $this->obxeto("idCliente")->post(null);
    $this->obxeto("data_0"   )->post(null);
    $this->obxeto("data_1"   )->post(null);
    $this->obxeto("sfpago"   )->post("**");
    $this->obxeto("sservizo" )->post("**");
    $this->obxeto("spagado"  )->post("...");

    if (!$this->obxeto("salmacen")->readonly) $this->obxeto("salmacen")->post("**");


    return $this->operacion_bBuscar($e, false);
  }

  private function __where() {
    $wh_d0 = self::__where_momento($this->obxeto("data_0")->valor(), ">=");
    $wh_d1 = self::__where_momento($this->obxeto("data_1")->valor(), "<=");

    $pagado = $this->obxeto("spagado")->valor();
        if ($pagado == "...") $wh_pagado = "(1=1)";
    elseif ($pagado == "n"  ) $wh_pagado = "b.pago_momento is null";
    elseif ($pagado == "s"  ) $wh_pagado = "b.pago_momento is not null";

    $wh_cli = "(1=1)";
    if (($idCliente = $this->obxeto("idCliente")->valor()) != "") $wh_cli = "(b.id_cliente = '{$idCliente}')";

    $wh_fpg = "(1=1)";
    if (($idfPago = $this->obxeto("sfpago")->valor()) != "**") $wh_fpg = "(id_fpago = '{$idfPago}')";

    $wh_alm = "(1=1)";
    if (($id_alm = $this->obxeto("salmacen")->valor()) != "**") $wh_alm = "(a.id_almacen = {$id_alm})";

    $wh_ser = "(1=1)";
    if (($ids = $this->obxeto("sservizo")->valor()) != "**") {
      $wh_ser = "(a.tipo = 's')";
    }


    $where = "{$wh_d0} and {$wh_d1} and {$wh_ser} and {$wh_pagado} and {$wh_cli} and {$wh_fpg} and {$wh_alm}";


    return str_replace(" and (1=1)", "", $where);
  }

  private function __where_ods() {
    return "(a.id_site = {$this->pai->id_site}) and " . $this->__where();
  }

  private static function __where_momento($data, $op) {
    return Valida::buscador_data2sql("b.momento", $op, $data);
  }

  private static function __sfpago($id_site, $predet = "...") {
    $s = new Select("sfpago", Pedido_fpago_obd::_fpago($id_site, null, $predet));

    $s->style("default", "width: 99%;");


    return $s;
  }

  private static function __sservizo() {
    $_o = ["**" => "...",
           "00" => "Todos",
           "10" => "Activos",
           "20" => "Caducados"
          ];

    $s = new Select("sservizo", $_o);

    $s->style("default", "width: 99%;");


    return $s;
  }
}

//************************************

final class Cxpedidos_pago extends Componente {
  const ptw_0 = "ptw/paneis/pcontrol/ventas/cxpedidos_pago.html";
  const ptw_1 = "ptw/paneis/pcontrol/ventas/cxpedidos_pago_email.html";

  private $id_site;
  private $ano;
  private $numero;
  private $cli_nome;

  private $ref;

  private $pedido;

  private $dominio;

  public function __construct() {
    parent::__construct("cxpp", self::ptw_0);

    $this->visible = false;

    $this->pon_obxeto(Panel_fs::__benviar ("bEnviar", "Enviar email"));
    $this->pon_obxeto(Panel_fs::__bcancelar());

    $this->pon_obxeto(new Div("derro"));

    $this->pon_obxeto(Panel_fs::__text("temail" ));
    $this->pon_obxeto(Panel_fs::__text("tasunto"));
    $this->pon_obxeto(new Textarea("tcomentarios", 1, 1));

    $this->obxeto("derro")->clase_css("default", "texto2_erro");
    $this->obxeto("derro")->clase_css("default", "texto2_erro");

    //~ $this->obxeto("bAceptar" )->envia_ajax("onclick");
    $this->obxeto("bCancelar")->envia_ajax("onclick");
  }

  public function post_pago(Pedido_obd $p) {
    $this->visible = true;

    $this->pedido    = $p;

    $this->id_site   = $p->atr("id_site"      )->valor;
    $this->ano       = $p->atr("ano"          )->valor;
    $this->numero    = $p->atr("numero"       )->valor;
    $this->cli_nome  = $p->destino()->atr("nome")->valor;

    $this->ref     = Articulo_obd::ref($this->numero, 6) . "-{$this->ano}";

    $this->inicia_dominio($this->id_site);


    $this->obxeto("temail"      )->post( $p->atr("cliente_email")->valor );
    $this->obxeto("tasunto"     )->post( "{$this->dominio}. Retomar pago #Id: {$this->ref}" );
    $this->obxeto("tcomentarios")->post( "" );
  }

  public function operacion(EstadoHTTP $e) {
    $this->obxeto("derro")->post(null);

    if ($this->obxeto("bCancelar")->control_evento()) return $this->operacion_bCancelar($e);
    if ($this->obxeto("bEnviar"  )->control_evento()) return $this->operacion_bEnviar  ($e);


    return null;
  }

  private function inicia_dominio($id_site) {
    if ($this->dominio != null) return;

    //~ $sp = Site_plesk_obd::inicia_s(new FS_cbd(), $id_site);

    $this->dominio = TrwlConfig::dominio;

    if ($this->dominio == null) $this->dominio = "exemplo-dominio.com";
  }

  public function operacion_bCancelar(Efs $e) {
    $this->visible = false;

    $this->pai->preparar_saida($e->ajax());

    return $e;
  }

  public function operacion_bEnviar(Epcontrol $e) {
    //* 0.- Valida formulario.
    if (($erro = $this->validar()) != null) {
      $this->obxeto("derro")->post($erro);

      $this->preparar_saida( $e->ajax() );

      return $e;
    }


    //* 1.- Inserta cambio seguro.

    $cbd = new FS_cbd();

    $c = new Cambio_pago_obd();

    $c->atr("id_site")->valor = $this->id_site;
    $c->atr("ano"    )->valor = $this->ano;
    $c->atr("numero" )->valor = $this->numero;

    $cbd->transaccion();

    if (!$c->insert($cbd)) {
      $cbd->rollback();

      $this->obxeto("derro")->post("Sucedió un error al actualizar la BD (1).");


      $this->preparar_saida( $e->ajax() );

      return $e;
    }

    //* 1b.- actualiza email do pedido.
    $d_obd = $this->pedido->destino($cbd);

    if ($d_obd->atr("email")->valor != $this->obxeto("temail")->valor()) {
      $d_obd->atr("email")->valor = $this->obxeto("temail")->valor();

      if (!$d_obd->update($cbd)) {
        $cbd->rollback();

        $this->obxeto("derro")->post("Sucedió un error al actualizar la BD (2).");


        $this->preparar_saida( $e->ajax() );

        return $e;
      }
    }


    $cbd->commit();


    //* 2.- Envía email de pago

    $codigo  = $c->atr("id_cambio")->valor;
    $rmtnt   = $e->usuario()->atr("email")->valor;
    $momento = date("d-m-Y H:i:s");
    $comenta = self::email_comentarios( $this->obxeto("tcomentarios")->valor() );
    //~ $_pdf    = self::email_prepara_adxunto($this->pedido);


    $html = LectorPlantilla::plantilla( self::ptw_1 );

    $html = str_replace("[nome]"         , $this->cli_nome, $html);
    $html = str_replace("[pedido]"       , $this->ref, $html);
    $html = str_replace("[link_enlace]"  , "<a href='http://{$this->dominio}/pago.php?r={$codigo}' style='color: #00f;'>http://{$this->dominio}/pago.php?r={$codigo}</a>", $html);
    $html = str_replace("[dominio]"      , "{$this->dominio}", $html);
    $html = str_replace("[momento_crear]", "{$momento}", $html);
    $html = str_replace("[legal]"        , LectorPlantilla::plantilla(Efs::url_legais($this->id_site). "/lopd_email.html"), $html);
    $html = str_replace("[comentarios]"  , $comenta, $html);


  // $mail = new XMail2();
    $mail = new XMail3($this->id_site);

    try {
      (($_x = $mail->_config()) != [])
      ? $mail->pon_rmtnt($_x['email'])
      : $mail->pon_rmtnt($rmtnt);
	  
      $mail->pon_asunto   ( $this->obxeto("tasunto")->valor() );
      $mail->pon_cco      ( $rmtnt );
      $mail->pon_direccion( $this->obxeto("temail")->valor() );
	  
      $mail->pon_corpo($html);
	  
      //~ echo $mail->html();
      $mail->enviar();

	  $e->post_msx("Email 'retomar pago' ha sido enviado correctamente.", "m");
	}
	catch (Exception $ex) {
	  $e->post_msx($ex->getMessage());
	}


    return $this->operacion_bCancelar($e);
  }

  private function validar() {
    $erro = null;

    $email = trim($this->obxeto("temail")->valor());

    if (!Valida::email($email)) $erro = "Error, debes teclear un email válido.<br>";


    if ($erro != null) return $erro;


    $this->obxeto("temail")->post($email);


    return null;
  }

  public static function email_comentarios($c) {
    if (trim($c) == null) return "";

    return "<div style='margin-left: 2em;padding: 1.1em;border-left: 0.7em solid #d53838;font-size: 0.9em;'>{$c}</div>";
  }

  private static function email_prepara_adxunto(Pedido_obd $p) {
    $html2pdf = Pedido_obd::pdfdoc($p, false);


    $n = Articulo_obd::ref($p->atr("numero")->valor, 6);

    $d = Refs::url_tmp2();
    $f = "pedido-" . $n . $p->atr("ano")->valor . ".pdf";


    if ( !is_dir( $d ) ) mkdir( $d );


    $html2pdf->Output("{$d}/{$f}", "F");


    return array($f, "{$d}/{$f}", $d);
  }
}

//*********************************

final class Cxpedidos_anexarFra extends Componente {
  const ptw_0 = "ptw/paneis/pcontrol/ventas/cxpedidos_anexarFra_0.html";
  const ptw_1 = "ptw/paneis/pcontrol/ventas/cxpedidos_anexarFra_1.html";


  private $s = null;
  private $a = null;
  private $n = null;

  private $reanexar = false;

  private $pedido_ref = null;
  private $pedido_src = null;
  private $pedido_f   = null;

  public function __construct() {
    parent::__construct("cxpa", self::ptw_0);

    $this->visible = false;

    $this->pon_obxeto(new Param("datos_f"));
    $this->pon_obxeto(new Param("pedido" ));

    $this->pon_obxeto( Panel_fs::__text("fraref") );

    $this->pon_obxeto( Panel_fs::__text("temail" ) );
    $this->pon_obxeto( Panel_fs::__text("tasunto") );
    $this->pon_obxeto( Panel_fs::__textarea("tbody", "Te envío anexa la factura solicitada.") );

    $this->pon_obxeto( self::fpdf () );
    $this->pon_obxeto( self::bpdf0() );
    $this->pon_obxeto( self::bpdf1() );
    $this->pon_obxeto( self::bpdf2() );

    $this->pon_obxeto(Panel_fs::__bcancelar("Cerrar ventana"));
    $this->pon_obxeto(Panel_fs::__benviar  ("bEnviar", "Enviar email"));

    $this->obxeto("fraref")->envia_AJAX("onchange");
    //~ $this->obxeto("fraref")->envia_SUBMIT("onchange");

    $this->obxeto("bCancelar")->envia_AJAX("onclick");
    $this->obxeto("bEnviar"  )->envia_AJAX("onclick");
  }


  public function pon_pedido(Pedido_obd $p, bool $reanexar = false) {
  if ($this->s == null) $this->pon_obxeto( self::fpdf($this->pai->id_site) ); //* $this->s actuará como un switch.

    $this->visible = true;

    $this->s = $p->atr("id_site")->valor;
    $this->a = $p->atr("ano"    )->valor;
    $this->n = $p->atr("numero" )->valor;

    $this->reanexar = $reanexar;

    $this->pedido_ref = Pedido_obd::ref( $this->a, $this->n );
    $this->pedido_src = self::pedido_src( $p );

    if ($reanexar) {
    $this->ptw = self::ptw_0;

    $this->pedido_f  = "{$this->pedido_src}{$this->pedido_ref}.pdf";

      $this->obxeto("fraref")->post( $p->atr("id_factura")->valor );
    }
    else  {
    $this->ptw = self::ptw_1;

    $this->pedido_f  = null;

      $this->obxeto("fraref")->post( $this->fraref_aux() );
    }

    $this->obxeto("temail" )->post( $p->atr("cliente_email")->valor );
    $this->obxeto("tasunto")->post( "Tu factura para el pedido: {$this->pedido_ref}" );

    $this->obxeto("pedido" )->post( $this->pedido_ref );

    $this->obxeto("datos_f")->post( self::datos_facturar($p) );
  }

  public function operacion(EstadoHTTP $e) {
    if ($this->obxeto("fraref"   )->control_evento()) return $this->operacion_fraref   ($e);
    if ($this->obxeto("bbaixar"  )->control_evento()) return $this->operacion_bbaixar  ($e);
    if ($this->obxeto("fpdf"     )->control_evento()) return $this->operacion_fpdf     ($e);
    if ($this->obxeto("bsup"     )->control_evento()) return $this->operacion_bsup     ($e);
    if ($this->obxeto("bEnviar"  )->control_evento()) return $this->operacion_bEnviar  ($e);
    if ($this->obxeto("bCancelar")->control_evento()) return $this->operacion_bCancelar($e);

    return null;
  }


  private function operacion_fraref(Epcontrol $e) {
    $fraref = self::normaliza_fraref( trim( $this->obxeto("fraref")->valor() ) );

  if ($fraref == "") $fraref = $this->fraref_aux();

  $this->obxeto("fraref")->post( $fraref );


  $cbd = new FS_cbd();

  $p = Pedido_obd::inicia($cbd, $this->s, $this->a, $this->n);

  if (!$p->fra_update($cbd, $fraref)) {
    $this->pon_pedido($p, $this->reanexar);

    $e-preparar_saida( $e->ajax() );

      $e->post_msx("No se pudo modificar la referencia de la factura.");

    return $e;
  }


    $e->ajax()->pon( $this->obxeto("fraref") );

    $this->pai->obxeto("cxpl")->preparar_saida( $e->ajax() );

    $e->post_msx("Referencia de la factura modificada correctamene.", "m");

    return $e;
  }

  private function operacion_fpdf(Epcontrol $e) {
    $this->obxeto("fpdf")->post_f(false);

  $this->pedido_f = $this->obxeto("fpdf")->aceptar_f( $this->pedido_src, $this->pedido_ref );


    if ( ( $fraref = trim($this->obxeto("fraref")->valor()) ) != "" ) {
      $this->ptw = self::ptw_0;

      $e->post_msx("Fichero PDF anexado correctamene.", "m");

      $this->preparar_saida( $e->ajax() );

      $this->pai->obxeto("cxpl")->preparar_saida( $e->ajax() );
  }


  //* actualiza fraref.
    $fraref   = self::normaliza_fraref( $this->obxeto("fpdf")->f() );

  $cbd = new FS_cbd();

  $p = Pedido_obd::inicia($cbd, $this->s, $this->a, $this->n);

  if (!$p->fra_update($cbd, $fraref)) {
    $this->pon_pedido($p, $this->reanexar);

    $e-preparar_saida( $e->ajax() );

      $e->post_msx("No se pudo modificar la referencia de la factura.");

    return $e;
  }



  $this->obxeto("fraref")->post( $fraref );

    $this->ptw = self::ptw_0;

    $e->post_msx("Fichero PDF anexado correctamene.", "m");

    $this->preparar_saida( $e->ajax() );

    $this->pai->obxeto("cxpl")->preparar_saida( $e->ajax() );

    return $e;
  }

  private function operacion_bsup(Epcontrol $e) {
    unlink($this->pedido_f);
    
    
    $cbd = new FS_cbd();
    
    $p = Pedido_obd::inicia($cbd, $this->s, $this->a, $this->n);
    
    if (!$p->fra_delete($cbd)) {
      $this->pon_pedido($p, $this->reanexar);
    
      $e-preparar_saida( $e->ajax() );
    
      $e->post_msx("ERROR al actualizar la BD.");
    
      return $e;
    }


    $this->obxeto("fraref")->post( "" );

    $this->ptw = self::ptw_1;

    $e->post_msx("Factura eliminada correctamene.", "m");

    $this->preparar_saida( $e->ajax() );

    $this->pai->obxeto("cxpl")->preparar_saida( $e->ajax() );

    return $e;
  }

  private function operacion_bbaixar(Epcontrol $e) {
    $src_0 = $this->pedido_f;
    $src_1 = $this->obxeto("fraref")->valor() . ".pdf";


    $e->pon_mime( $src_1, "application/pdf", file_get_contents($src_0), filesize($src_0) );

    return $e;
  }

  private function operacion_bCancelar(Epcontrol $e) {
    $this->visible = false;


    return $e;
  }

  private function operacion_bEnviar(Epcontrol $e) {
    //* 0.- Valida formulario.
    if (($erro = $this->validar_bEnviar()) != null) {
      $e->post_msx($erro);

      return $e;
    }


    //* 2.- Envía email de pago

    $rmtnt  = $e->usuario()->atr("email")->valor;
    $body   = $this->obxeto("tbody" )->valor();
    $fraref = $this->obxeto("fraref")->valor();

   // $mail = new XMail2();
    $mail = new XMail3($e->id_site);   

    (($_x = $mail->_config()) != [])
    ? $mail->pon_rmtnt($_x['email'])
    : $mail->pon_rmtnt($rmtnt);

    try {
      $mail->pon_asunto   ( $this->obxeto("tasunto")->valor() );
      $mail->pon_cco      ( $rmtnt );
      $mail->pon_direccion( $this->obxeto("temail" )->valor() );
      $mail->pon_adxunto  ( $this->pedido_f, "{$fraref}.pdf", 'base64', 'application/pdf' );
	  
      $mail->pon_corpo($body);
	  
      //~ echo $mail->html();
      $mail->enviar();

	  $e->post_msx("La factura ha sido enviada vía email.", "m");
	}
	catch (Exception $ex) {
	  $e->post_msx($ex->getMessage());
	}

    return $e;
  }

  private function validar_bEnviar() {
  if ( !Valida::email($this->obxeto("temail")->valor()) ) return "&middot;&nbsp;Debes teclear un email válido.";

  if ( trim($this->obxeto("tasunto")->valor()) == ""    ) return "&middot;&nbsp;Debes teclear un asunto.";


    return null;
  }

  private function fraref_aux() {
    return "FRA_PED" . trim($this->pedido_ref);
  }

  private static function normaliza_fraref($fraref) {
    $_p = pathinfo( strtolower($fraref) );

//~ echo "<pre>" . print_r($_p, 1) . "</pre>";

    return str_replace(["/", " "], ["-", "_"], $_p["filename"]);
  }


  private static function bpdf0() {
    $b = Panel_fs::__bmais("bsubir", "Factura PDF", "Anexar archivo PDF de la factura");

    $b->pon_eventos("onclick", "cxpedido_bAnexar_click()");

    return $b;
  }

  private static function bpdf1() {
    $b = Panel_fs::__bexportar("bbaixar", null, "Descargar archivo PDF de la factura");

    $b->envia_mime("onclick");

    return $b;
  }

  private static function bpdf2() {
    $b =  Panel_fs::__bsup("bsup", "Eliminar factura", "Eliminar la factura del pedido");

    $b->envia_AJAX("onclick", "¿ Deseas eliminar la factura del pedido ?");

    return $b;
  }

  private static function fpdf($id_site = null) {
    //~ $tmp = null; if ($id_site != null) $tmp = Efs::url_tmp( Efs::url_site($id_site) . Refs::url_arquivos . "/" );
    $tmp = Refs::url_arquivos(Refs::url_tmp . "/");

    $f = new File("fpdf", $tmp);

    $f->accept = "application/pdf";

    $f->clase_css("default", "file_apagado");

    return $f;
  }

  private static function pedido_src(Pedido_obd $p) {
    $fra_ano = date("Y");
    if ($p->atr("factura_momento")->valor != null) {
      if ($p->atr("id_factura")->valor != Pedido_obd::fra_solicita) {
        $fra_ano = $p->atr("factura_momento")->fvalor("Y");
      }
    }


    $src = TrwlConfig::priv . "/" . $p->atr("id_site")->valor . "/";

    if (!is_dir($src)) mkdir($src);

    $src .= "facturas/";

    if (!is_dir($src)) mkdir($src);

    $src .= "{$fra_ano}/";

    if (!is_dir($src)) mkdir($src);


    return $src;
  }

  private static function datos_facturar(Pedido_obd $p):String {
    $cbd = new FS_cbd();

    if (($u = $p->cliente($cbd)) == null) return  "{ No hay datos de facturación }";

    $cf = $u->contacto_obd($cbd, "f");

    $_cf = $cf->a_resumo();


    if ($_cf["cnif_d"] == null) return  "{ No hay datos de facturación }";


    if (($s = $_cf["razon_social"]) == null) $s = $_cf["nome"];

    if ($_cf["cnif_d"] != null) $s = "{$_cf["cnif_d"]} - {$s}";

    if ($_cf["mobil" ] != null) $s .= ", {$_cf["mobil"]}";

    if ($_cf["email" ] != null) $s .= ", {$_cf["email"]}";


    $s = "&bull;&nbsp;{$s}<br>&bull;&nbsp;" . $cf->enderezo_obd()->resumo() . "<br>";

    return $s;
  }
}

//******************************************

final class Cxpedidos_ficha extends Componente {
  const ptw_0 = "ptw/paneis/pcontrol/ventas/cxpedidosf_0.html";
  const ptw_1 = "ptw/paneis/pcontrol/ventas/cxpedidosf_1.html";

  public $id_site;
  public $ano;
  public $id_pedido;

  public function __construct() {
    parent::__construct("cxpf");

    $this->pon_obxeto(self::_text("id_pedido"));
    $this->pon_obxeto(self::_text("id_factura"));
    $this->pon_obxeto(Panel_fs::__text("tid_factura", 20, 55, " ej. 000111/2014"));
    $this->pon_obxeto(self::_text("momento"));
    $this->pon_obxeto(Panel_fs::__dataInput("pago_momento", true));
    $this->pon_obxeto(new Checkbox("ch_pagado"));
    $this->pon_obxeto(self::_text("total"));
    $this->pon_obxeto(self::_text("id_cliente"));
    $this->pon_obxeto(self::_text("cnif_d_cliente"));
    $this->pon_obxeto(self::_text("nomcli", "width: 33em;"));
    $this->pon_obxeto(self::_text("email"));
    $this->pon_obxeto(self::_text("tlfn"));
    $this->pon_obxeto(new Textarea("destino", 1, 1));
    $this->pon_obxeto(self::_text("fpago_resumo", "width: 33em;"));

    //~ $this->pon_obxeto(self::_text("portes_aux", "min-width: 31em;"));
    $this->pon_obxeto(new Div("portes_aux"));

    $this->pon_obxeto(new Button("bticket", "<span style='font-size: 1.3em;'>⬇</span> PDF"));

    $this->pon_obxeto(Panel_fs::__bcancelar());
    //~ $this->pon_obxeto(Panel_fs::__baceptar());

    $this->obxeto("bticket"   )->envia_mime("onclick");
    $this->obxeto("ch_pagado" )->envia_ajax("onclick");
    $this->obxeto("bCancelar" )->envia_ajax("onclick");

    $this->obxeto("destino"   )->readonly = true;

    $this->obxeto("portes_aux")->clase_css("default", "txt_adv");
  }

  public function seleccionado() {
    return array($this->ano, $this->id_pedido);
  }

  public function operacion(EstadoHTTP $e) {
    if ($this->ptw == null) return null;


    if ($this->pai->id_almacen < 2)
      if ($this->obxeto("sestado")->control_evento()) return $this->operacion_sestado ($e);

    if ($this->obxeto("ch_pagado")->control_evento()) return $this->operacion_chpagado ($e);
    if ($this->obxeto("bticket"  )->control_evento()) return $this->operacion_bticket  ($e);
    if ($this->obxeto("bCancelar")->control_evento()) return $this->operacion_bCancelar($e);


    return null;
  }

  public function pon_pedido(Pedido_obd $p) {
//~ echo "id_almacen:{$this->pai->id_almacen}<br/>";

    $this->ptw = ($this->pai->id_almacen > 1)?Refs::url(self::ptw_1):Refs::url(self::ptw_0);

    $this->id_site   = $p->atr("id_site")->valor;
    $this->ano       = $p->atr("ano"    )->valor;
    $this->id_pedido = $p->atr("numero" )->valor;


    $portes_aux = "";
        if ($p->atr("portes_sabado")->valor != null) $portes_aux = "&bull;&nbsp;Entrega especial sábado día " . $p->atr("portes_sabado")->valor;
    elseif ($p->atr("recollida"    )->valor == 1   ) $portes_aux = "&bull;&nbsp;Recogida en tienda";


    $cbd = new FS_cbd();

    $d = Contacto_obd::inicia($cbd, $p->atr("id_destino")->valor, "d");
    $f = $d;

    $id_cliente = $p->atr("id_cliente")->valor;
    $email      = $p->atr("cliente_email")->valor;

    if ($id_cliente == null) {
      //~ $f = $d;
      $id_cliente2 = "000000";
      $this->obxeto("id_cliente")->readonly = true;
    }
    else {
      //~ $f = FGS_usuario::inicia($cbd, $id_cliente)->contacto_obd($cbd, "p");

      $id_cliente2 = Articulo_obd::ref($id_cliente, 6);

      $this->obxeto("id_cliente")->readonly = false;
    }

    $fp = $p->fpago($cbd);

    $id_pedido  = Articulo_obd::ref($p->atr("numero")->valor, 6) . "-" . $p->atr("ano")->valor;


    //~ if (($id_factura = $p->atr("id_factura")->valor) == null) $id_factura = "----";

    $a_momento  = $p->atr("momento")->a_data();
    $pagado     = $p->atr("pago_momento")->valor != null;

    if ($p->atr("pago_momento")->valor != null) {
      $a_momentop = $p->atr("pago_momento")->a_data();

      $pago_momento = "{$a_momentop['Y']}-{$a_momentop['m']}-{$a_momentop['d']}";
    }
    else
      $pago_momento = "";


    $this->obxeto("ch_pagado")->readonly = ($this->pai->id_almacen > 1);


    $prefs = $d->atr("nome")->valor . "\n" . $d->enderezo_obd()->resumo() . "\n" . $p->atr("prefs")->valor;


    $this->obxeto("id_pedido"     )->post($id_pedido);
    $this->obxeto("id_factura"    )->post($p->atr("id_factura")->valor);
    $this->obxeto("momento"       )->post("{$a_momento ['d']}-{$a_momento ['m']}-{$a_momento ['Y']} {$a_momento ['H']}:{$a_momento ['i']}:{$a_momento ['s']}");
    $this->obxeto("pago_momento"  )->post($pago_momento);
    $this->obxeto("ch_pagado"     )->post($pagado);
    $this->obxeto("id_cliente"    )->post($id_cliente2);
    $this->obxeto("email"         )->post($email);
    $this->obxeto("tlfn"          )->post($d->atr("mobil")->valor);
    $this->obxeto("cnif_d_cliente")->post($f->atr("cnif_d")->valor);
    $this->obxeto("nomcli"        )->post($f->atr("nome")->valor);
    $this->obxeto("destino"       )->post($prefs);
    $this->obxeto("portes_aux"    )->post($portes_aux);
    $this->obxeto("fpago_resumo"  )->post($p->atr("id_fpago")->valor . " - " . $fp->resumo());


    $this->obxeto("portes_aux")->visible = ($portes_aux != "");

    if ($this->pai->id_almacen > 1) return; //* non é preciso para paneis multivendedor.


    //* informe de estado

    $gestado = Cxpedidos::__gestado( Pedido_obd::estado_p2array($p->atr("estado_p")->valor), true );

    $this->pon_obxeto($gestado);

    $this->pon_obxeto(Cxpedidos::__sestado( "**", "..." ));
  }

  private function operacion_chpagado(Efs $e) {
    $cbd = new FS_cbd();

    $p = Pedido_obd::inicia($cbd, $this->id_site, $this->ano, $this->id_pedido);

    if (!$p->update_pago($cbd)) {
      $this->pai->preparar_saida($e->ajax());

      return $e;
    }

    if ($p->atr("pago_momento")->valor != null) {
      $pago_momento = $p->atr("pago_momento")->fvalor("Y-m-d");
    }
    else
      $pago_momento = "";


    $this->obxeto("pago_momento")->post( $pago_momento );


    $this->pai->preparar_saida($e->ajax());

    return $e;
  }

  public function operacion_sestado(Efs $e) {
    $id_articulo = null;
    $estado      = $this->pai->obxeto("hestado")->valor();


    $cbd = new FS_cbd();

    $cbd->transaccion();

    $p = Pedido_obd::inicia($cbd, $this->id_site, $this->ano, $this->id_pedido);

    if ($p->update_estado($cbd, $estado, $id_articulo))
      $cbd->commit();
    else
      $cbd->rollback();


    //* actualizamos gestado
    $gestado = Cxpedidos::__gestado( Pedido_obd::estado_p2array("{$estado[0]}=100"), true );

    $this->pon_obxeto($gestado);



    $this->pai->preparar_saida($e->ajax());


    return $e;
  }

  public function operacion_bCancelar(Efs $e) {
    $this->id_pedido = null;

    $this->ptw = null;

    $this->pai->preparar_saida($e->ajax());

    return $e;
  }

  private function operacion_bticket(Epcontrol $e) {
    $cbd = new FS_cbd();

    //~ echo "$this->id_site, $this->ano, $this->id_pedido<br>";

    $p = Pedido_obd::inicia($cbd, $this->id_site, $this->ano, $this->id_pedido);


    $html2pdf = Pedido_obd::pdfdoc($p, false);


    $d = Refs::url_tmp2();
    $f = "pedido-" . Articulo_obd::ref($this->id_pedido, 6) . "{$this->ano}.pdf";

    $e->pon_mime("{$d}/{$f}", "application/pdf", $html2pdf->Output('', true), null);

    return $e;
  }

  private static function _text($id, $css = null, $clase_css = "text") {
    $t = Panel_fs::__text($id, null, null);

    $t->readonly = true;

    $t->clase_css("default", $clase_css);
    $t->style    ("default", $css      );


    return $t;
  }
}

//******************************************

final class Cxpedidos_excel extends Componente {

  public static function xls($titulo, $sql_where) {
    $i = 1;

    $xls = new ExcelWriterXML();

    $css = $xls->addStyle('css_rol');
    $css->fontBold();

    $sheet = $xls->addSheet($titulo);

    self::xls_cab($sheet, $i);

    self::xls_detalle($sheet, $i, $sql_where);


    return $xls->writeData();
  }

  private static function xls_cab(ExcelWriterXML_Sheet $folla, $i) {
    $folla->writeString($i,  1, "#Pedido"      , 'css_rol');
    $folla->writeString($i,  2, "Base"         , 'css_rol');
    $folla->writeString($i,  3, "IVA %"        , 'css_rol');
    $folla->writeString($i,  4, "IVA €"        , 'css_rol');
    $folla->writeString($i,  5, "PVP Portes"   , 'css_rol');
    $folla->writeString($i,  6, "Total"        , 'css_rol');
    $folla->writeString($i,  7, "#Factura"     , 'css_rol');
    $folla->writeString($i,  8, "Fecha pedido" , 'css_rol');
    $folla->writeString($i,  9, "Fecha pago"   , 'css_rol');
    $folla->writeString($i, 10, "#FPago"       , 'css_rol');
    $folla->writeString($i, 11, "FPago"        , 'css_rol');
    $folla->writeString($i, 12, "DNI"          , 'css_rol');
    $folla->writeString($i, 13, "Nome"         , 'css_rol');
    $folla->writeString($i, 14, "RSocial"      , 'css_rol');
    $folla->writeString($i, 15, "Email"        , 'css_rol');
    $folla->writeString($i, 16, "Mobil"        , 'css_rol');
    $folla->writeString($i, 17, "TipoVía"      , 'css_rol');
    $folla->writeString($i, 18, "Vía"          , 'css_rol');
    $folla->writeString($i, 19, "Número"       , 'css_rol');
    $folla->writeString($i, 20, "CodPostal"    , 'css_rol');
    $folla->writeString($i, 21, "Loaclidad"    , 'css_rol');
    $folla->writeString($i, 22, "Provincia"    , 'css_rol');
    $folla->writeString($i, 23, "País"         , 'css_rol');
  }

  private static function xls_detalle(ExcelWriterXML_Sheet $folla, $i, $sql_where) {
    $cbd = new FS_cbd();

    $r = $cbd->consulta( self::sql($sql_where) );

    while ($_r = $r->next()) {
      self::xls_detalle_1($folla, ++$i, $_r);
    }
  }

  private static function xls_detalle_1(ExcelWriterXML_Sheet $folla, $i, $f) {
    $b        = $f['tbi'] + $f['tbi_plus'];
    $bive     = $b * ( $f['pive'] / 100 );
    $tpvpive  = $b + $bive;
    $tbi_plus = 0 + $f['tbi_plus'];

    $b2    = number_format($b       , 2, ".", "");
    $bive2 = number_format($bive    , 2, ".", "");
    $pvp2  = number_format($tpvpive , 2, ".", "");
    $plus  = number_format($tbi_plus, 2, ".", "");


    $id_ticket = Articulo_obd::ref($f['numero']  , 6) . "-{$f['ano']}";

    $folla->writeString($i,  1, $id_ticket         , '');
    $folla->writeNumber($i,  2, $b2                , '');
    $folla->writeNumber($i,  3, $f['pive']         , '');
    $folla->writeNumber($i,  4, $bive2             , '');
    $folla->writeNumber($i,  5, ""                 , '');
    $folla->writeNumber($i,  6, $pvp2              , '');
    $folla->writeNumber($i,  7, $f['id_factura']   , '');
    $folla->writeString($i,  8, $f['momento']      , '');
    $folla->writeString($i,  9, $f['pago_momento'] , '');
    $folla->writeString($i, 10, $f['id_fpago']     , '');
    $folla->writeString($i, 11, $f['fpago']        , '');
    $folla->writeString($i, 12, $f['cnif_d']       , '');
    $folla->writeString($i, 13, $f['nome']         , '');
    $folla->writeString($i, 14, $f['razon_social'] , '');
    $folla->writeString($i, 15, $f['email']        , '');
    $folla->writeString($i, 16, $f['mobil']        , '');
    $folla->writeString($i, 17, $f['codtipovia']   , '');
    $folla->writeString($i, 18, $f['via']          , '');
    $folla->writeString($i, 19, $f['num']          , '');
    $folla->writeString($i, 20, $f['cp']           , '');
    $folla->writeString($i, 21, $f['localidade']   , '');
    $folla->writeString($i, 22, $f['provincia']    , '');
    $folla->writeString($i, 23, $f['country']      , ''); 
  }

  private static function sql($sql_where) {
    return "select a.id_site, a.ano, a.numero, a.pive,
                   c.id_fpago, c.nome as fpago,
                   sum(a.tpvp) as tbi,sum(a.tpvp_plus) as tbi_plus, sum(a.tpvpive) as tpvpive,
                   b.momento, b.id_cliente, b.pago_momento, b.id_factura, b.factura_momento,
                   d.cnif_d, d.nome, d.razon_social, d.email, d.mobil, d.codtipovia, d.via, d.num, d.cp, d.localidade, d.provincia, d.country
              from v_pedido_articulo a inner join pedido b on (a.id_site = b.id_site and a.ano = b.ano and a.numero = b.numero)
                                       inner join v_pedido_fpago c on (a.id_site = c.id_site and a.ano = c.ano and a.numero = c.id_pedido )
                                       inner join v_contacto d on (b.id_destino = d.id_contacto and d.tipo = 'd')
                where {$sql_where}
             group by a.id_site, a.ano, a.numero, a.pive
             order by a.ano desc, a.numero desc, a.pive asc";
  }

}

//************************************

final class CXP_lista extends FS_lista {
  public $id_site    = null;
  public $id_almacen = null;

  public function __construct($id_site, $id_almacen = null) {
    parent::__construct("cxpl", new CXP_ehtml());

    $this->id_site    = $id_site;
    $this->id_almacen = $id_almacen;

    $this->__selectfrom();
    $this->__where();
    $this->__groupby();

    $this->orderby = "b.ano DESC, b.numero DESC";
    $this->limit_f = 10;

//~ echo $this->sql_vista() . "<br>\n";
  }


  public function __where($where = null) {
    $this->limit_0 = 0;

    $this->where = "b.id_site = {$this->id_site}";

    if ($this->id_almacen > 1) $this->where .= " and a.id_almacen = {$this->id_almacen}";

    if ($where == null) return;

    $this->where .= " and {$where}";
  }

  public function seleccionado() {
    return $this->pai->seleccionado();
  }

  public function operacion(EstadoHTTP $e) {
    $evento = $e->evento();

    //~ echo $evento->html();

    if ($this->control_fslevento($evento, "id_pedido")) {
      list($a, $n) = explode("-", $evento->subnome(0));

      return $this->pai->operacion_pedido($e, $a, $n);
    }

    if ($this->control_fslevento($evento, "gestado")) {
      list($a, $n) = explode("-", $evento->subnome(0));

      return $this->pai->operacion_pedido($e, $a, $n);
    }

    if ($this->control_fslevento($evento, "lanexar"    )) return $this->pai->operacion_lanexar($e, false );
    if ($this->control_fslevento($evento, "lreanexar"  )) return $this->pai->operacion_lanexar($e, true);

    if ($this->control_fslevento($evento, "id_cliente" )) return $this->pai->operacion_usuario($e, $evento->subnome(0));
    if ($this->control_fslevento($evento, "ldevolver"  )) return $this->operacion_ldevolver   ($e, $evento->subnome(0));
    if ($this->control_fslevento($evento, "lverfactura")) return $this->operacion_fac_ver     ($e);
    if ($this->control_fslevento($evento, "sestado"    )) return $this->operacion_sestado     ($e);
    if ($this->control_fslevento($evento, "chpagado"   )) return $this->operacion_chpagado    ($e);
    if ($this->control_fslevento($evento, "lpago"      )) return $this->pai->operacion_pago   ($e, $evento->subnome(0));

    return parent::operacion($e);
  }

  public function sublista_prototipo($id_rama) {
    list($num, $ano) = explode("-", $id_rama);

    $p = Pedido_obd::inicia(new FS_cbd(), $this->id_site, $ano, $num);

    return new CXPA_lista($p, $this->id_almacen);
  }

  public function calcula_porcentaxe_envios($numero, $ano) {
    $cbd = new FS_cbd();

    $p = Pedido_obd::inicia($cbd, $this->id_site, $ano, $numero);


    return $p->calcula_estado_p($cbd);
  }

  private function __selectfrom() {

    //~ if ($this->id_almacen == "**") {
      //~ $this->selectfrom = "select distinct b.*, DATE_FORMAT(b.momento, '%d-%m-%Y %H:%i:%S') as momento_2 from v_pedido b";

      //~ return;
    //~ }


    $this->selectfrom = "select a.id_site, a.ano, a.numero, b.cancelado, b.id_pago, b.estado_p, a.moeda,
                                sum(a.tpvpive) as tpvpive, sum(a.tpvp_plus_ive) as tpvp_plus_ive,
                                DATE_FORMAT(b.momento, '%d-%m-%Y %H:%i:%S') as momento_2, b.id_cliente, b.pago_momento, b.id_factura, b.factura_momento,
                                c.id_fpago, c.nome as fpago
                           from v_pedido_articulo a inner join pedido b on (a.id_site = b.id_site and
                                                                            a.ano     = b.ano and
                                                                            a.numero  = b.numero
                                                                           )
                                                    inner join v_pedido_fpago c on (a.id_site = c.id_site and
                                                                                    a.ano     = c.ano and
                                                                                    a.numero  = c.id_pedido
                                                                                   )";
  }

  private function __groupby() {
    $this->groupby = "";

    //~ if ($this->id_almacen == "**") return;

    $this->groupby = "a.id_site, a.ano, a.numero, c.id_fpago";
  }

  private function operacion_ldevolver(Epcontrol $e) {
    list($ano, $numero) = explode("-", $e->evento()->subnome(0));

    $cbd = new FS_cbd();

    $p = Pedido_obd::inicia($cbd, $this->id_site, $ano, $numero);

    $cbd->transaccion();

    if ($p->cancelar($cbd))
      $cbd->commit();
    else
      $cbd->rollback();

    $this->preparar_saida($e->ajax());


    return $e;
  }

  private function operacion_fac_ver(Epcontrol $e) {
    list($ano, $numero) = explode("-", $e->evento()->subnome(0));

    $cbd = new FS_cbd();

    $p_obd = Pedido_obd::inicia($cbd, $this->id_site, $ano, $numero);


    $fra_ano = $p_obd->atr("factura_momento")->fvalor("Y");
    $fra_nom = $p_obd->atr("id_factura"     )->valor();

    $src = TrwlConfig::priv . "/{$this->id_site}/facturas/{$fra_ano}/{$fra_nom}.pdf";

//~ echo $src;

    $e->pon_mime("{$fra_nom}.pdf", "application/pdf", file_get_contents($src), null);


    return $e;
  }

  private function operacion_sestado(Epcontrol $e) {
    list($ano, $numero) = explode("-", $e->evento()->subnome(0));

    $cbd = new FS_cbd();

    $p = Pedido_obd::inicia($cbd, $this->id_site, $ano, $numero);

    $p->atr("estado")->valor = $this->pai->obxeto("hestado")->valor();

    $p->update($cbd);

    return $e;
  }

  private function operacion_chpagado(Epcontrol $e) {
    list($ano, $numero) = explode("-", $e->evento()->subnome(0));

    $cbd = new FS_cbd();

    $p = Pedido_obd::inicia($cbd, $this->id_site, $ano, $numero);

    $p->update_pago($cbd);


    $this->preparar_saida($e->ajax());

    return $e;
  }
}

//---------------------------------

final class CXP_ehtml extends FS_ehtml {
  public $nf = 0;

  public function __construct() {
    parent::__construct();

    $this->class_table  = "lista_00";
    $this->style_table = "border: 1px #aaa solid;";
    $this->align       = "left";
    $this->width       = "95%";
    $this->cellspacing = 0;
    $this->cellpadding = 0;
    $this->border      = 0;
  }

  protected function cabeceira($df = null) {
    if ($this->xestor->id_almacen > 1) return $this->cabeceira_2($df);

    return $this->cabeceira_1($df);
  }

  protected function cabeceira_1($df = null) {
    $this->nf = $this->rbd->numFilas();

    return "<tr>
              <th style='width: 1em;'></th>
              <th style='width: 1em; text-align: center;'>"   . $this->__lOrdenar_html("momento", "#Pedido")     . "</th>
              <th style='width: 1em; text-align: center;'>" . $this->__lOrdenar_html("momento", "Fecha")      . "</th>
              <th style='text-align: center;'>" . $this->__lOrdenar_html("estado", "Estado") . "</th>
              <th style='text-align: center;'>" . $this->__lOrdenar_html("id_cliente", "#Cliente") . "</th>
              <th style='text-align: center;'>" . $this->__lOrdenar_html("pago_momento", "Pagado") . "</th>
              <th style='text-align: center;'>" . $this->__lOrdenar_html("id_fpago", "#FPago")      . "</th>
              <th style='text-align: right;' >" . $this->__lOrdenar_html("tpvpive", "Total")  . "</th>
              <th style='width: 1em;'></th>
              <th astyle='text-align: center;'></th>
              <th style='text-align: center;'></th>
            </tr>";
  }

  protected function cabeceira_2($df = null) {
    $this->nf = $this->rbd->numFilas();

    return "<tr>
              <th style='width: 1em;'></th>
              <th style='width: 1em;'>"   . $this->__lOrdenar_html("ref", "#Pedido")     . "</th>
              <th style='width: 1em;text-align: center;'>" . $this->__lOrdenar_html("momento", "Fecha")      . "</th>
              <th style='text-align: center;'>" . $this->__lOrdenar_html("id_cliente", "#Cliente") . "</th>
              <th style='width: 1em;' style='text-align: center;'>" . $this->__lOrdenar_html("pago_momento", "Pagado") . "</th>
              <th style='width: 1em;' style='text-align: center;'>" . $this->__lOrdenar_html("id_fpago", "#FPago")      . "</th>
              <th style='text-align: right;' >" . $this->__lOrdenar_html("tpvpive", "Total")  . "</th>
              <th style='width: 1em;'></th>
            </tr>";
  }

  protected function linha_detalle($df, $f) {
    //~ echo "<pre>" . print_r($f, true) .  "</pre>";

    if ($this->xestor->id_almacen > 1) return $this->linha_detalle_2($df, $f);

    return $this->linha_detalle_1($df, $f);
  }

  protected function linha_detalle_1($df, $f) {
    $a_s = $this->xestor->seleccionado();

    $style_tr = ( ($a_s[0] != $f['ano']) || ($a_s[1] != $f['numero']) )?FS_ehtml::style_tr:FS_ehtml::style_tr_a;

    $moeda = Articulo_obd::$a_moeda[$f['moeda']];


    $id_rama = "{$f['numero']}-{$f['ano']}";

    $this->rama_check($id_rama);

    return "<tr  {$style_tr} >
              <td> " . $this->__bmais_html($id_rama) . "</td>
              <td align=center>" . self::__lped($this, $f) . "</td>
              <td style='white-space: nowrap;'>{$f['momento_2']}</td>
              <td >" . self::__penvios($this, $id_rama, $f) . "</td>
              <td align=center>" . self::__lcli($this, $f) . "</td>
              <td align=center>" . self::__chpagado($this, $f) . "</td>
              <td align=center title='{$f['fpago']}'>" . Articulo_obd::ref($f['id_fpago'], 2) . "</td>
              <td align=right>" . self::__tpvpive($this, $f) . "</td>
              <td align=center>{$moeda}</td>
              <td align=center >" . self::__lfactura($this, $f) . "</td>
              <td align=right>" . self::__ldevolver($this, $f) . "</td>
            </tr>
            <tr style='background-color: #fff;'>
              <td style='border-bottom: 0;'></td>
              <td style='border-bottom: 0;' colspan=11>" . $this->rama_html($id_rama) . "</td>
            </tr>";
  }

  protected function linha_detalle_2($df, $f) {
    $a_s = $this->xestor->seleccionado();

    $style_tr = ( ($a_s[0] != $f['ano']) || ($a_s[1] != $f['numero']) )?FS_ehtml::style_tr:FS_ehtml::style_tr_a;

    $moeda = Articulo_obd::$a_moeda[$f['moeda']];


    $id_rama = "{$f['numero']}-{$f['ano']}";

    $this->rama_check($id_rama);


    return "<tr  {$style_tr} >
              <td> " . $this->__bmais_html($id_rama) . "</td>
              <td align=center>" . self::__lped($this, $f) . "</td>
              <td style='white-space: nowrap;'>{$f['momento_2']}</td>
              <td align=center>" . self::__lcli($this, $f) . "</td>
              <td align=center>" . self::__chpagado($this, $f) . "</td>
              <td align=center title='{$f['fpago']}'>" . Articulo_obd::ref($f['id_fpago'], 2) . "</td>
              <td align=right>" . self::__tpvpive($this, $f) . "</td>
              <td align=center>{$moeda}</td>
            </tr>
            <tr style='background-color: #fff;'>
              <td style='border-bottom: 0;'></td>
              <td style='border-bottom: 0;' colspan='11'>" . $this->rama_html($id_rama) . "</td>
            </tr>";
  }

  protected function totais() {
    return "<tr >
              <td style='border-bottom: 0;' align='center' colspan='11'>" . $this->__paxinador_html() . "</td>
            </tr>";
  }

  private static function __lped(CXP_ehtml $ehtml, $f) {
    $b = new Div( "id_pedido", Pedido_obd::ref($f['ano'], $f['numero']) );

    $b->clase_css("default", "lista_elemento_a");

    $b->title = "Ver/Editar la información del pedido";

    //~ $b->envia_submit("onclick");
    $b->envia_ajax("onclick");

    return $ehtml->__fslControl($b, "{$f['ano']}-{$f['numero']}-1")->html();
  }

  private static function __tpvpive(CXP_ehtml $ehtml, $f) {
    if ($ehtml->xestor->id_almacen > 1) {
      return "<b>" . number_format($f['tpvpive'] + $f['tpvp_plus_ive'], 2, ",", ".") . "</b>";
    }

    $cbd = new FS_cbd();

    $p = Pedido_obd::inicia($cbd, $ehtml->xestor->id_site, $f['ano'], $f['numero']);


    return "<b>" . number_format($p->calcula_total(-1, $cbd, true), 2, ",", ".") . "</b>";
  }

  private static function __lcli(CXP_ehtml $ehtml, $f) {
    $t = ($f['id_cliente'] == null)?"---":Articulo_obd::ref($f['id_cliente'], 6);


    return $t;
  }

  private static function __chpagado(CXP_ehtml $ehtml, $f) {
    $pagado = ($f['pago_momento'] != null);

    $confirm = ($pagado)?"El pedido seleccionado está pagado, - ¿ quieres cancelar el pago del pedido ?":null;

    $ch = new Checkbox("chpagado", $pagado);

    $ch->style("default" , "cursor:pointer; width:1em;");
    $ch->style("readonly", "cursor:initial; width:1em;");

    $ch->envia_submit("onclick", $confirm);
    //~ $ch->envia_AJAX("onclick", $confirm);

    $ch->readonly = ($f['cancelado'] != null) || ($f['id_factura'] != null) || ($ehtml->xestor->id_almacen > 1);

    return $ehtml->__fslControl($ch, "{$f['ano']}-{$f['numero']}")->html();
  }

  private static function __penvios(CXP_ehtml $ehtml, $id_rama, $f) {
    if ($f['estado_p'] == null)
      $_p = $ehtml->xestor->calcula_porcentaxe_envios($f['numero'], $f['ano']);
    else
      $_p = Pedido_obd::estado_p2array($f['estado_p']);


    if (($d = Cxpedidos::__gestado( $_p )) == null) return "---";


    //~ $d->envia_submit("onclick");
    $d->envia_ajax("onclick");


    return $ehtml->__fslControl($d, "{$f['ano']}-{$f['numero']}")->html();
  }

  private static function __lfactura(CXP_ehtml $ehtml, $f) {
    if ($f['cancelado'] != null) return "";

    $id = "lanexar"; $m = ""; $t = ""; $onclick = "submit";

        if ($f['id_pago']      == Pedido_obd::pago_simulado) return "";
    elseif ($f['pago_momento'] == null)                      return self::__lpago($ehtml, $f);
    elseif ($f['id_factura']   == null) {
      $e       = "Anexar&nbsp;factura";
      $t       = "Solicitar una factura de tu pedido";
    }
    elseif ($f['id_factura']   == Pedido_obd::fra_solicita) {
      $e       = "Factura&nbsp;solicitada";
      $t       = "Volver a anexar la factura de tu pedido";
      //~ $onclick = "ajax";
    }
    else {
      $id      = "lreanexar";
      $e       = $f['id_factura'];
      $onclick = "ajax";
    }


    $l = new Link($id, $e, "_blank");

    $l->clase_css("default", "lista_elemento_a");
    $l->style("default", "color: #0000ba");

    $l->title = $t;

        if ($onclick == "ajax"  ) $l->envia_ajax  ("onclick", $m);
    elseif ($onclick == "mime"  ) $l->envia_mime  ("onclick");
    elseif ($onclick == "submit") $l->envia_submit("onclick", $m);


    return $ehtml->__fslControl($l, "{$f['ano']}-{$f['numero']}")->html();
  }

  private static function __lpago(CXP_ehtml $ehtml, $f) {
    $l = new Link("lpago", "Retomar pago");

    $l->clase_css("default", "lista_elemento_a");
    $l->style    ("default", "color: #0000ba");

    $l->title = "Envía un email al cliente que le permita retomar el pago del pedido cómodamente";

    //~ $l->envia_ajax  ("onclick");
    $l->envia_submit("onclick");


    return $ehtml->__fslControl($l, "{$f['ano']}-{$f['numero']}")->html();
  }

  private static function __ldevolver(CXP_ehtml $ehtml, $f) {
    if ($f['cancelado'] != null) return "";

    $l = Panel_fs::__bsup("ldevolver", null, "");

    $ref = Articulo_obd::ref($f['numero'], 6) . "-{$f['ano']}";

    //~ $l->envia_submit("onclick", "&iquest; Desea cancelar el pedido/factura {$ref} ?");
    $l->envia_ajax("onclick", "&iquest; Deseas cancelar el pedido/factura {$ref} ?");


    return $ehtml->__fslControl($l, "{$f['ano']}-{$f['numero']}")->html();
  }
}

//************************************

final class CXPA_lista extends FS_lista {
  public $id_almacen    = null;     
  public $dto           = null;
  public $portes        = null;
  public $pagado        = null;
  public $dpago         = null;
  public $fpago_recargo = null;

  private $id_site = null;
  private $ano     = null;
  private $numero  = null;

  public function __construct(Pedido_obd $p, $id_almacen) {
    parent::__construct("cxpal", new CXPA_ehtml());

    $this->id_site    = $p->atr("id_site")->valor;
    $this->ano        = $p->atr("ano")->valor;
    $this->numero     = $p->atr("numero")->valor;

    $this->id_almacen = $id_almacen;

    $this->selectfrom = "select * from v_pedido_articulo";
    $this->orderby    = "ano, numero";

    $this->where = "id_site = {$this->id_site} and ano = {$this->ano} and numero = {$this->numero}";

    if ($id_almacen > 1) $this->where .= " and id_almacen = {$id_almacen}";

    $this->__ini_atr_pedido($p);
  }

  public function operacion(EstadoHTTP $e) {
    $evento = $e->evento();

    if ($this->control_fslevento($evento, "sestado"))      return $this->operacion_sestado($e);


    return parent::operacion($e);
  }

  private function operacion_sestado(Epcontrol $e) {
    $id_articulo = $e->evento()->subnome(0);
    $estado      = $this->pai->pai->obxeto("hestado")->valor();


    $cbd = new FS_cbd();

    $cbd->transaccion();

    $p = Pedido_obd::inicia($cbd, $this->id_site, $this->ano, $this->numero);


    if ($p->update_estado($cbd, $estado, $id_articulo))
      $cbd->commit();
    else
      $cbd->rollback();


    $this->pai->preparar_saida($e->ajax());


    return $e;
  }

  private function __update($id_articulo) {
    $cbd = new FS_cbd();

    $cbd->transaccion();

    $pa = Pedido_articulo_obd::inicia($cbd, $this->id_site, $this->ano, $this->numero, $id_articulo);

    $pa->atr("nome"    )->valor = $this->obxeto("nome", $id_articulo)->valor();
    $pa->atr("unidades")->valor = Erro::valida_numero($this->obxeto("unidades", $id_articulo)->valor());
    $pa->atr("pvp"     )->valor = Erro::valida_numero($this->obxeto("pvp"     , $id_articulo)->valor(), 2);

    if (!$pa->update($cbd)) {
      $cbd->rollback();

      return;
    }


    $p = Pedido_obd::inicia($cbd, $this->id_site, $this->ano, $this->numero);

    if (!$p->update($cbd)) {
      $cbd->rollback();

      return;
    }

    $this->portes = $p->atr("portes")->valor;


    $cbd->commit();

    $cbd->close();
  }

  private function __ini_atr_pedido(Pedido_obd $p) {
    //~ $this->pagado = $p->pagado();
    $this->dpago  = $p->atr("pago_momento")->valor;

    $this->portes = $p->atr("portes")->valor;
    $this->dto    = $p->atr("dto"   )->valor;

    if ($p->atr("id_fpago")->valor == 2) $this->fpago_recargo = $p->fpago()->precargo();
  }
}

//---------------------------------

final class CXPA_ehtml extends FS_ehtml {
  public $nf = 0;

  private $m = null;
  private $t = [];

  public function __construct() {
    parent::__construct();

    $this->style_table = "margin: 7px 3px 9px 3px; padding: 0; border: 1px #ccc solid; ;";
    $this->align       = "left";
    $this->width       = "99%";
    $this->cellspacing = 0;
    $this->cellpadding = 0;
    $this->border      = 0;
  }

  protected function cabeceira($df = null) {
    $this->nf = $this->rbd->numFilas();

    $this->m  = null;
    $this->t  = [];

    return "<tr>
              <th width=77px style='max-width: 77px;'></th>
              <th width=11%>" . $this->__lOrdenar_html("id_articulo", "#Producto") . "</th>
              <th width=11%>" . $this->__lOrdenar_html("id_almacen" , "#Almacén")  . "</th>
              <th width=12%>Estado</th>
              <th width=12% style='text-align: right;'>Precio</th>
              <th width=7%  style='text-align: right;'>Dto.</th>
              <th width=7%  style='text-align: right;'>Uds.</th>
              <th width=12% style='text-align: right;'>(+)</th>
              <th width=7%  style='text-align: right;'>IVA</th>
              <th width=*   style='text-align: right;'>PVP</th>
            </tr>";
  }

  protected function linha_detalle($df, $f) {
    $id_articulo = $f["id_articulo"];

    $style_tr = FS_ehtml::style_tr;

    $s = ($f["sinal_p"] !== null)?($f["sinal_p"] / 100):1;

	  if ( !isset($this->t[$f["pive"]]) ) $this->t[$f["pive"]] = ["pvp" => 0, "pvpive" => 0];

    $this->t[$f["pive"]]["pvp"   ] += ($f["tpvp"   ] + $f["tpvp_plus"    ]) * $s;
    $this->t[$f["pive"]]["pvpive"] += ($f["tpvpive"] + $f["tpvp_plus_ive"]) * $s;

    if ($this->m == null) $this->m = Articulo_obd::$a_moeda[$f["moeda"]];


    return "<tr height='44px;' {$style_tr}>
              <td rowspan='3' " . self::__td_imx($f) . "></td>
              <td>" . Articulo_obd::ref($id_articulo, 6) . "</td>
              <td>" . Articulo_obd::ref($f["id_almacen"], 6) . "</td>
              <td>" . self::__sestado($this, $f) . "</td>
              <td style='text-align:right;'>" . number_format($f["pvp"], 2, ",", ".") . "{$this->m}</td>
              <td style='text-align:right;'>{$f["desc_p"]}%</td>
              <td style='text-align:right;'>{$f["unidades"]}</td>
              <td style='text-align:right;'>" . number_format(floatval($f["tpvp_plus"]), 2, ",", ".") . "{$this->m}</td>
              <td style='text-align:right;'>{$f["pive"]}%</td>
              <td style='text-align:right;'>" . number_format(($f["tpvpive"] + $f["tpvp_plus_ive"]) * $s, 2, ",", ".") . "{$this->m}</td>
            </tr>
            " . self::__tr_nome($f) . "
            " . self::__tr_servizo($this, $f) . "
            " . self::__describe_html($f);
  }

  protected function totais() {
    if ($this->xestor->id_almacen > 1) return "";

    //* subtotais IVE
    $st_ive = "";
    if ($this->t != null) {
      $tpvp    = 0;
      $tpvpive = 0;
      foreach ($this->t as $ive=>$a_pvp) {
        $lenda = ($tpvp == 0)?"Subtotales IVA&nbsp;":"";

        $tpvp    += $a_pvp['pvp'];
        $tpvpive += $a_pvp['pvpive'];

        $pvp    = number_format($a_pvp['pvp']   , 2, ",", ".");
        $pvpive = number_format($a_pvp['pvpive'], 2, ",", ".");

        $st_ive .= "<tr  valign=top>
                      <td ></td>
                      <td colspan=6><b>{$lenda}</b></td>
                      <td align=right>{$pvp}{$this->m}</td>
                      <td align=right>{$ive}%</td>
                      <td align=right>{$pvpive}{$this->m}</td>
                    </tr>";
      }
    }

    //* subtotais portes
    $portes = $this->xestor->portes;

    $st_portes = "<tr  valign=top>
                    <td ></td>
                    <td colspan=5><b>Gastos de env&iacute;o&nbsp;</b></td>
                    <td colspan=3></td>
                    <td align=right>" . number_format($portes, 2, ",", ".") . "{$this->m}</td>
                  </tr>";

    $tpvpive += $portes;

    //* subtotais fpago = 2
    $st_fpago = "";
    if ($this->xestor->fpago_recargo != null) {
      $a_recargo = $this->xestor->fpago_recargo;

      $recargo = Pedido_obd::calcula_reembolso_2($tpvpive, $a_recargo);

      $st_fpago = "<tr  valign=top>
                    <td ></td>
                    <td colspan=5><b>Contra Reembolso</b> (<span style='font-size: 91%;'> recargo {$a_recargo['porcentaxe']}%, min. {$a_recargo['min']} &euro;, max. {$a_recargo['max']} &euro; </span>)</td>
                    <td align=right></td>
                    <td align=right>" . number_format($tpvpive, 2, ",", ".") . "{$this->m}</td>
                    <td align=center></td>
                    <td align=right>" . number_format($recargo, 2, ",", ".") . "{$this->m}</td>
                  </tr>";
                  
      $tpvpive += $recargo;
    }

    //* subtotais dto (cod promocional)
    $st_dto = "";
    if ($this->xestor->dto > 0) {
      $st_dto = "<tr>
                   <td colspan=10 style='font-size:1px; border-bottom: 1px solid #555555;'>&nbsp;</td>
                 </tr>
                 <tr  valign=top>
                    <td ></td>
                    <td colspan=5><b>Descuento promoción</b></td>
                    <td align=right></td>
                    <td align=right></td>
                    <td align=center></td>
                    <td align=right>-" . number_format($this->xestor->dto, 2, ",", ".") . "{$this->m}</td>
                  </tr>";
    }

    $tpvpive -= $this->xestor->dto;

    return "<tfoot>

              {$st_ive}
              {$st_portes}
              {$st_fpago}
              {$st_dto}
              <tr>
                <td colspan=9></td>
                <td width=1px align=right><b>" . number_format($tpvpive, 2, ",", ".") . "{$this->m}</b></td>
              </tr>
            </tfoot>";
  }

  private static function __describe_html($f) {
    $a = "";
    if ($f['sinal_p'] !== null) {
      //~ $tpvp_0 = number_format(( 100 * ( $f['tpvpive'] + $f['tpvp_plus'] ) ) / $f['sinal_p'], 2, ",", ".");
      $tpvp       = number_format($f['tpvpive'] + $f['tpvp_plus'], 2, ",", ".");
      $tpvp_sinal = number_format(($f['tpvpive'] + $f['tpvp_plus']) * ($f['sinal_p'] / 100), 2, ",", ".");

      $a = "<div style='padding: 9px 0; font-size: 91%;'>&nbsp;<span style='color: #ba0000;'>RESERVA</span> ({$f['sinal_p']}% de {$tpvp}&euro;) = {$tpvp_sinal}&euro;</div>";
    }

    $b = $f['describe_html'];

    //~ if (($a == null) && ($b == null)) return "";

    return "<tr><td colspan=9 style='border-top: 1px dotted #999;'>{$a}{$b}</td></tr>";
  }

  private static function __tr_nome($f) {
    return "<tr><td colspan=9 style='font-size: 131%; padding: 7px;'>{$f['nome']}</td></tr>";
 }

  private static function __tr_servizo(CXPA_ehtml $ehtml, $f) {
    if ($f['tipo']         != "s") return "";
    if ($f['servizo_dias'] <  1  ) return "";

    if (($dini_aux = $f['servizo_data']) == null) $dini_aux = $ehtml->xestor->dpago;

    $dini = date( "d/m/Y", strtotime($dini_aux) );
    $dfin = date( "d/m/Y", strtotime("{$dini_aux} + {$f['servizo_dias']} days") );


    return "<tr><td colspan=9 style='font-size: 111%; padding: 0 0 0.1em 7px;'>desde {$dini} hasta {$dfin}  ·  ( {$f['servizo_dias']} días )</td></tr>";
 }

  private static function __sestado(CXPA_ehtml $ehtml, $f) {
    $s = Cxpedidos::__sestado($f['estado']);


    return $ehtml->__fslControl($s, $f['id_articulo'])->html();
  }

  private static function __td_imx($f) {
    $cbd = new FS_cbd();

    $logo_obd = Articulo_obd::inicia($cbd, $f['id_site'], $f['id_articulo'])->iclogo_obd($cbd);

    $src = $logo_obd->url(null, $cbd);


    return "style='background-image: url({$src});
                   background-position: center center;
                   background-size: cover;
                   background-color: #fff;
                   border: 7px solid #fff;
                   min-width: 77px;'";
  }
}
