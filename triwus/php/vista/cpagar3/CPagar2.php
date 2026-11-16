<?php

final class CPagar2 extends    Componente
                    implements I_fs  {

  const ptw_0 = "ptw/cpagar3/cpagar3_0.html";
  const ptw_2 = "ptw/cpagar3/cpagar3_1.html";
  const ptw_3 = "ptw/cpagar3/cpagar3_2.html";


  public $id_site   = null;

  public $id_idioma = null;

  public $paso      = 1;

  private $pedido_obd = null;

  private $pedido_ano = null;
  private $pedido_num = null;

  public function __construct(Site_obd $s) {
    parent::__construct("cpagar2");

    $this->id_site = $s->atr("id_site")->valor;

    $this->pon_obxeto( new CCarro2_lista   ($this->id_site) );
    $this->pon_obxeto( new CPagar2_codpromo()               );
    $this->pon_obxeto( new CPagar2_envio   ($this->id_site) );
    $this->pon_obxeto( new CPagar2_prefpago()               );
    $this->pon_obxeto( new CPagar2_fin     ()               );

    $this->pon_obxeto( new Param("msx_erro") );
    $this->pon_obxeto( new Param("pref"    ) );
    $this->pon_obxeto( new Param("pfecha"  ) );
    $this->pon_obxeto( new Param("email"   ) );

    $this->pon_obxeto( new Image("imx_kok", null, false) );

    $this->pon_obxeto( self::__lmime("lticket", "aquí") );
    
//~ echo "aaaaaaaaaaaaaaaaaaaaaaaaaaaa<br>aaaaaaaaaaaaaaaaaaaaaaaaaaaa<br>aaaaaaaaaaaaaaaaaaaaaaaaaaaa<br>aaaaaaaaaaaaaaaaaaaaaaaaaaaa<br>";
  }

  public function config_2(FGS_usuario $u) {
    $this->paso = 2;


    $this->ptw(self::ptw_0);

    $this->pon_obxeto( self::__boton("Finalizar la compra", "Finalizar la compra", false) );


    $this->obxeto("bAceptar")->style("default", "margin: 33px 0 0 5px;");
    //~ $this->obxeto("bAceptar")->envia_submit("onclick", null, "819jd93429nfsijf0394");
    $this->obxeto("bAceptar")->pon_eventos("onclick", "cpagar2_finalizar()");
  }

  public function config_3($email) {
    $this->paso = 3;

    $this->obxeto("email"    )->post($email);

    $this->obxeto("imx_kok"  )->pon_src( Refs::url("imx/cpagar/ped_ok_2.png") );

    $this->pon_obxeto( self::__boton(" ", "Aceptar", false) );

    $this->obxeto("bAceptar")->pon_eventos("onclick", "document.location = 'index.php'");

    $this->ptw( self::ptw_2 );
  }

  public function config_4($email) {
    $this->paso = 3;

    $this->obxeto("email"    )->post($email);

    $this->obxeto("imx_kok"  )->pon_src( Refs::url("imx/cpagar/ped_ko_2.png") );

    $this->pon_obxeto( self::__boton(" ", "Aceptar", false) );

    $this->obxeto("bAceptar")->pon_eventos("onclick", "document.location = 'index.php'");

    $this->ptw( self::ptw_3 );
  }

  public function config_lectura() {}
  public function config_edicion() {}
  public function config_usuario(IEfspax_xestor $e = null) {
//~ echo "cpagar2.config_usuario()<br>";

    $this->id_idioma = $e->id_idioma();

    $u = $e->usuario();

    $this->config_2($u);

    $this->obxeto("ccarro2_lista"   )->post_u($u);
    $this->obxeto("cpagar2_prefpago")->post_u($u);
    $this->obxeto("cpagar2_ccodigo" )->post_u($u);
    $this->obxeto("cpagar2_envio"   )->post_u($u);
    $this->obxeto("cpagar2_fin"     )->post_u($u);
  }

  public function config_tpv($email) {
    $this->ptw( self::ptw_2 );
  }

  //~ public function pedido() {
    //~ return $this->pedido_obd;
  //~ }

  public function _t_alma() {
    return $this->obxeto("ccarro2_lista")->t_alma;
  }

  public function id_country() {
    return $this->obxeto("cpagar2_envio")->obxeto("cenderezo")->obxeto("id_country")->valor();
  }

  public function id_destino() {
    return $this->obxeto("cpagar2_envio")->obxeto("cenderezo")->obxeto("cp")->valor();
  }

  public function chsabado() {
    return $this->obxeto("cpagar2_envio")->obxeto("chsabado")->valor();
  }

  public function operacion(EstadoHTTP $e) {
    if (($e_aux = $this->operacion_tpvresponse($e)) != null) return $e_aux;

    if ($this->obxeto("bAceptar")->control_evento()) return $this->operacion_bAceptar($e);

    if ($this->obxeto("lticket" )->control_evento()) return $this->operacion_lticket($e);

    if (($e_aux = $this->obxeto("ccarro2_lista")->operacion($e)) != null) return $e_aux;

    if (($e_aux = $this->obxeto("cpagar2_ccodigo")->operacion($e)) != null) return $e_aux;

    if (($e_aux = $this->obxeto("cpagar2_prefpago")->operacion($e)) != null) return $e_aux;

    if (($e_aux = $this->obxeto("cpagar2_envio")->operacion($e)) != null) return $e_aux;

    if (($e_aux = $this->obxeto("cpagar2_fin")->operacion($e)) != null) return $e_aux;


    return null;
  }

  public function operacion_bAceptar(IEfspax_xestor $e) {
//~ echo "cpagar2.operacion_bAceptar($this->paso)<br>";

    switch ($this->paso) {
      case 2:
        return $this->operacion_bAceptar_2($e);

      case 3:
        $this->config_usuario($e);

        $this->config_2( $e->usuario() );

        return $e;
    }

    return $e;
  }

  public function operacion_lticket(IEfspax_xestor $e) {
    $_nticket  = CPagar2_email::_nticket($this->id_site, $this->pedido_ano, $this->pedido_num);


    $e->pon_mime($_nticket[0], "application/pdf", file_get_contents($_nticket[1]), null);

    return $e;
  }

  private function operacion_bAceptar_2(IEfspax_xestor $e) {
    if (($erro = $this->validar()) != null) {
      $e->post_msx($erro);

      return $e;
    }

    //* Crease un novo pedido, para intentar ou reintentar o pago.
    $u = $e->usuario();

    if (!$this->update($u)) {
      $e->post_msx(Erro::cpago_8);

      return $e;
    }


    //* Comprobamos se temos q realizar o pago electronico
    $id_fpago = $this->obxeto("cpagar2_prefpago")->id_fpago();

    $pagado = $this->pedido_obd->pagado(); //* IMPORTANTE ORDEN. neste momento, o pedido so pode estar pagado, por unha simulación de compra. Despois de finalizar o pedido, (this.pedido_obd == null)

    if ( ($id_fpago < 4) || ($pagado) ) {
      try {
        CPagar2_email::mail_pago_ok( $this->pedido_obd );
      }
      catch(Exception $exc) {}
      
      $this->__finalizar($e); //* O Pedido esta na BD => (baleiramos carrito).

      return $e;
    }

    //* Precisamos operar cun TPV.

    if ($id_fpago == 4) {
      $fp_info = Site_fpago_obd::inicia(new FS_cbd(), $this->id_site, $id_fpago)->info();

      if ($fp_info['tipo'] == 1) return $this->operacion_bPagar ($e, "paypal");
      if ($fp_info['tipo'] == 2) return $this->operacion_bPagar3($e, "pago_paypal.php");
      
      $e->post_msx("Error, forma de pago 4 descoñecida.");

      return $e;
    }

    if ($id_fpago == 6) return $this->operacion_bPagar3($e, "pago_gpay.php");


    $bizum    = ($id_fpago == 900);
    $id_fpago = 5; //* A opción Bizum, almacénase como unha subopción de pago da opción 5.
    
    $fp_info = Site_fpago_obd::inicia(new FS_cbd(), $this->id_site, $id_fpago)->info();

    if ($fp_info['tipo'] == 1) return $this->operacion_bPagar($e, "redsys", $bizum);
    if ($fp_info['tipo'] == 2) return $this->operacion_bPagar($e, "ceca"  , $bizum);


    return $e;
  }

  public static function __boton($tit, $txt, $ajax = true) {
    $b = Panel_fs::__baceptar($txt, $tit);

    $b->style("default", "margin: 33px 0 0 5px; font-size: 111%;");

    if ($ajax) $b->envia_ajax("onclick");

    return $b;

  }

  public static function __lmime($id, $etq, $title = "") {
    $l = new Link($id, $etq);

    $l->title = $title;

    //~ $l->envia_submit("onclick");
    $l->envia_mime("onclick");


    return $l;
  }

  private function operacion_tpvresponse(IEfspax_xestor $e) {
//~ echo "bbbbbbbbbbbbbbbbbbbbbbb" . $e->evento()->html();
    //** AdvCkout
    if ($e->evento()->tipo() == "op-reservada") {
      if ($e->evento()->nome(0) == "tpvResponse3") return $this->operacion_tpvresponse_3($e);
    }


    //** Ckout
    if ($e->evento()->tipo() != "tpvresponse") return null;

    //* tpv PayPal
    if ($e->evento()->nome(4) == "paypal_request") return $this->operacion_tpvresponse_2($e);

    //* tpv redsys
    if ($e->evento()->nome(4) == "redsys_request") return $this->operacion_tpvresponse_2($e);

    //* tpv ceca
    if ($e->evento()->nome(4) == "ceca_request"  ) return $this->operacion_tpvresponse_2($e);

    return $e;
  }

  private function operacion_tpvresponse_2(IEfspax_xestor $e) {
    $cbd = new FS_cbd();

    if ($e->evento()->subnome(0) == "ko") {
      //* facilita retomar pago.
      $p = Pedido_obd::inicia($cbd, $this->id_site, $this->pedido_ano, $this->pedido_num);

      //* busca al cliente en la BD, se existe, asigna pedido.id_cliente
      if (($u = $p->cliente($cbd)) == null) {
        $u = $e->usuario();
      }
      else {
        $u->copia_historia($e->usuario());
      }

      $u->cpago()->destino( $p->destino($cbd) ); //* engade os datos de destino do pedido, ao formulario de pago.

      $u->cpago()->carro()->post_pedido( $p );

      $this->pedido_ano = null;
      
      $this->preparar_saida($e->ajax());

      return $e;
    }


    return $this->__finalizar( $e );
  }

  private function operacion_tpvresponse_3(IEfspax_xestor $e) {
    $_param = $e->evento()->nome(1);
    
    $kped = $_param[0];
    $kok  = $_param[1];
    
    $_p = explode("-", AES_256_CBC::descifrar($kped));
    
    $cbd = new FS_cbd();
    
    $p = Pedido_obd::inicia($cbd, $this->id_site, $_p[0], $_p[1]);

//~ echo "kok::{$kok}------------------<pre>" . print_r($p->a_resumo(), 1) . "</pre>";


    if ($kok == "ok") {
      try {
        CPagar2_email::mail_pago_ok( $p );
      }
      catch(Exception $exc) {}

      return $this->__finalizar($e, $p);
    }
    

    //* facilita retomar pago.

    //* busca al cliente en la BD, se existe, asigna pedido.id_cliente
    $u = $p->cliente($cbd); if ($u != null) $u->copia_historia($e->usuario());


    $u->cpago()->destino( $p->destino($cbd) ); //* engade os datos de destino do pedido, ao formulario de pago.

    $u->cpago()->carro()->post_pedido( $p );

    $this->pedido_ano = null; //* compatibilidade hacia atrás.

  
    try {
      CPagar2_email::mail_pago_ko( $p );
    }
    catch(Exception $exc) {}
    
    
    $this->preparar_saida($e->ajax());


    return $e;
  }

  private function operacion_bPagar(IEfspax_xestor $e, $t, $bizum = false) {
    $s = $this->id_site;
    $a = $this->pedido_ano;
    $n = $this->pedido_num;
    
    $protocolo = isset($_SERVER['HTTPS'])?"https":"http";

    $r1 = "{$protocolo}://{$_SERVER['HTTP_HOST']}/rpc/{$t}/";
    $r2 = "{$protocolo}://{$_SERVER['HTTP_HOST']}{$_SERVER['PHP_SELF']}";

    $p  = "{$s}-{$a}-{$n}";
    $z  = ($bizum)?"1":"0";

    $url = "{$r1}request.php?k=" . base64_encode("{$p}::{$r1}::{$r2}::{$z}");

//~ echo $url;

    //~ $e->redirect($url);
    
    $e->ajax()->pon_ok(true, "cpagar2_finalizar_redirect('{$url}')");

    return $e;
  }

  private function operacion_bPagar3(IEfspax_xestor $e, string $action, bool $bizum = false):IEfspax_xestor {
    $kped = AES_256_CBC::cifrar("{$this->pedido_ano}-{$this->pedido_num}");
    
    //~ $e->redirect("{$action}?kped={$kped}");
    
    $e->ajax()->pon_ok(true, "cpagar2_finalizar_redirect('{$action}?kped={$kped}')");
    
    return $e;
  }

  private function __finalizar(IEfspax_xestor $e, Pedido_obd $p = null):IEfspax_xestor {
    if ($p == null) $p = $this->pedido_obd;
    
    if ($p == null) {
      //~ header("Location: index.php");
      $e->ajax()->pon_ok(true, "cpagar2_finalizar_redirect('index.php')");

      return $e;
    }

    //* compatiblidade con operacion_bticket().
    $this->pedido_ano = $p->atr("ano"   )->valor; 
    $this->pedido_num = $p->atr("numero")->valor;

    //* busca al cliente en la BD, se existe, asigna pedido.id_cliente
    $cbd = new FS_cbd();

    if (($u = $p->cliente($cbd)) == null) {
      $u = $e->usuario();
    }
    else {
      $u->copia_historia($e->usuario());
    }

    if ($this->obxeto("cpagar2_fin")->rexistro()) {
      CPagar2_email::mail_inviticion_rexistro($p);
    }
    elseif($u->validado()) {
      Pedido_obd::pon_cliente($cbd, $u, false);
    }
    
    
    //* FIN:: busca al cliente en la BD

    $this->config_3( $u->atr("email")->valor );


    $u->cpago()->limpar();

    $e->obxeto("ccarro")->config_usuario($e);

    $this->pedido_obd = null;
    
    
    $this->preparar_saida($e->ajax());
    
    
    return $e;
  }

  private function update(FGS_usuario $u) {
    $this->obxeto("cpagar2_prefpago")->update($u);
    $this->obxeto("cpagar2_envio"   )->update($u);

    $cpago   = $u->cpago();
    $carro   = $cpago->carro();
    $_prefs  = $cpago->_prefs();
    $_portes = $this->obxeto("ccarro2_lista")->calcula_portes();


    $dto = $this->obxeto("ccarro2_lista")->t_dto;

    $cbd = new FS_cbd();

    $cbd->transaccion();

    $fpago   = $cpago->fpago();
    $destino = $cpago->destino();

    $this->pedido_obd = $this->crear_pedido_obd($cbd, $u, $destino->atr("email")->valor, $_prefs, $dto, $carro->simular_compra($_portes[0]));

    if (!$this->update_u_enderezo($cbd, $u, $destino)) {
      $e->post_msx(Erro::cpago_8);
      
      $cbd->rollback();

      return false;
    }

    if (($a_pedido_arti = $carro->__a_pedido_arti($this->pedido_obd)) == null) return false;

    $r_pedido = $this->pedido_obd->insert($cbd, $fpago, $destino, $a_pedido_arti, $_portes);


    if ($r_pedido['r'] != 1) {
      $e->post_msx(Erro::cpago_8);
      
      $cbd->rollback();

      return false;
    }


    $cbd->commit();

    return $this->update_finalizar($r_pedido);
  }
  
  private function update_u_enderezo(FS_cbd $cbd, FGS_usuario $u, Contacto_obd $destino) {
    $c = $u->contacto_obd($cbd);

    if ($c->enderezo_obd()->baleiro())    return $this->update_u_enderezo_2($cbd, $c, $destino);

    return true;
  }

  private function update_u_enderezo_2(FS_cbd $cbd, Contacto_obd $c, Contacto_obd $destino) {
    $email_0 = $c->atr("email")->valor;

    $c->copiar($destino);

    $c->atr("email")->valor = $email_0;
    $c->atr("tipo" )->valor = "p";

    $c->atr("id_contacto")->valor = $c->atr("id_contacto")->valor;

    if ($destino->atr("email")->valor == null) $destino->atr("email")->valor = $email_0;


    return $c->update($cbd);
  }

  private function update_finalizar($r_pedido = -1) {
    //* 1.- actualizamos cpagar.

    $this->pedido_ano = $this->pedido_obd->atr("ano"   )->valor;
    $this->pedido_num = $this->pedido_obd->atr("numero")->valor;

    $ref = Articulo_obd::ref($this->pedido_num, 6) . "-" . $this->pedido_ano;

    $this->obxeto("pref"  )->post($ref);
    $this->obxeto("pfecha")->post( $this->pedido_obd->atr("momento")->fvalor("d-m-Y H:i:s") );

    //* 2.- imprimimos un ticket PDF en tmp. (evitamos erro html2pdf cando chamamos desde IPN's)

    $this->prepara_ticket();

    //* 3.- avismos ao xestor da web se temos articulos con stock <= 0

    if ($r_pedido == -1) return true;

    CPagar2_email::mail_stock_0($r_pedido['articulos_0'], $this->id_site);


    return true;
  }

  private function crear_pedido_obd(FS_cbd $cbd, FGS_usuario $cliente, $email, $_prefs, $dto, $simular_compra) {
    $cliente = FGS_usuario::inicia($cbd, $cliente->atr("id_usuario")->valor);

//~ echo "<pre>" . print_r($_prefs, 1) . "</pre>";

    $portes_sabado = ($_prefs[1] == null)?null:substr($_prefs[1], 6, 4) . substr($_prefs[1], 3, 2) . substr($_prefs[1], 0, 2);

//~ echo "<br />********* {$portes_sabado} *************<br />";

    $p = new Pedido_obd($this->id_site);

    $p->atr("id_cliente"   )->valor = $cliente->atr("id_usuario")->valor;
    $p->atr("cliente_email")->valor = $email;
    $p->atr("dto"          )->valor = $dto;
    $p->atr("portes_sabado")->valor = $portes_sabado;
    $p->atr("recollida"    )->valor = ($_prefs[2])?"1":"0";

    $p->atr("prefs"        )->valor = $_prefs[0];

    if ($simular_compra) {
      $p->atr("id_pago"     )->valor = Pedido_obd::pago_simulado;
      $p->atr("pago_momento")->valor = date("YmdHis");
    }
    else
      $p->atr("id_pago")->valor = Pedido_obd::pago_ko;

    return $p;
  }

  private function validar() {
    $erro = null;


    //* 1.- comprobamos se hai un stock sufiente para todos os articulos.
    $erro .= $this->obxeto("ccarro2_lista")->validar_stock($this->id_site);

    if ($erro != null) return $erro;


    //* 2.- comprobamos se o formulario está cumplimentado correctamente, login + articulos.
    $erro .= $this->obxeto("ccarro2_lista")->validar();

    if ($erro != null) return $erro;


    //* 3.- comprobamos se o formulario está cumplimentado correctamente, envio habilitado para venta + forma de pago + condiciones de venta.
    $erro .= $this->obxeto("cpagar2_prefpago")->validar();

    $erro .= $this->obxeto("cpagar2_envio")->validar();

    if ($erro != null) return $erro;


    //* 4.- comprobamos CXS.
    if (!$this->obxeto("cpagar2_fin")->cxs()) $erro .= Erro::cpago_7;


    return $erro;
  }

  private function ptw($ptw_0) {
    $this->ptw = Refs::url($ptw_0);


    $a_path = pathinfo($ptw_0);

    $ptw_2 = Efs::url_legais($this->id_site) . $a_path['basename'];

    if (!is_file($ptw_2)) return;


    $this->ptw = $ptw_2;
  }

  private function prepara_ticket($d = "triwus/") {
    $p = Pedido_obd::inicia(new FS_cbd(), $this->id_site, $this->pedido_ano, $this->pedido_num);

    $ehtml = new CLista_pedido(true, $d);

    $ehtml->__pedido($p); //* $ehtml->documento = $pdf

    try {
      $html2pdf = new HTML2PDF('P', 'A4', 'es');
      $html2pdf->setDefaultFont('Arial');
      $html2pdf->writeHTML($ehtml->html());
    }
    catch(HTML2PDF_exception $e) {
      echo $e;
      exit;
    }

    $_nticket  = CPagar2_email::_nticket($this->id_site, $this->pedido_ano, $this->pedido_num);

    if ( !is_dir( $_nticket[2] ) ) mkdir( $_nticket[2] );


    return $html2pdf->Output($_nticket[1], "F");
  }
}
