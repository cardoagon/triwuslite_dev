<?php


final class FPago_obd extends Obxeto_bd {
  public function __construct() {
    parent::__construct();
  }

  public function mapa_bd() {
    return new FPago_mbd();
  }

  public static function inicia(FS_cbd $cbd, $id_fpago) {
    $fp = new FPago_obd();

    if ($id_fpago == null) return $fp;

    $fp->atr("id_fpago")->valor = $id_fpago;

    $fp->select($cbd, $fp->atr("id_fpago")->sql_where($cbd));

    return $fp;
  }

  public function select(FS_cbd $cbd, $where = null, $orderby = null) {
    $r = $cbd->consulta($this->sql_select($cbd, $where, $orderby));

    if (!$a = $r->next()) return;

    $this->post($a);
  }

  public function activada($id_site, FS_cbd $cbd = null) {
    if ($cbd == null) $cbd = new FS_cbd();

    $sfp = Site_fpago_obd::inicia($cbd, $id_site, $this->atr("id_fpago")->valor);

    return $sfp->activada();
  }

  public function activar($id_site, FS_cbd $cbd = null) {
    if ($cbd == null) $cbd = new FS_cbd();

    $sfp = Site_fpago_obd::inicia($cbd, $id_site, $this->atr("id_fpago")->valor);

    return $sfp->activar($cbd);
  }

  public function desactivar($id_site, FS_cbd $cbd = null) {
    if ($cbd == null) $cbd = new FS_cbd();

    $sfp = Site_fpago_obd::inicia($cbd, $id_site, $this->atr("id_fpago")->valor);

    return $sfp->desactivar($cbd);
  }

}

//-----------------------------------------------

final class FPago_mbd extends Mapa_bd {
  public function __construct() {
    $t = new Taboa_dbd("fpago");

    $t->pon_campo("id_fpago", new Numero(), true);
    $t->pon_campo("nome");

    $this->pon_taboa($t);
  }
}

//**********************************

final class Site_fpago_obd extends Obxeto_bd {
  public function __construct($id_site = null, $id_fpago = null) {
    parent::__construct();

    $this->atr("id_site")->valor  = $id_site;
    $this->atr("id_fpago")->valor = $id_fpago;

    $this->atr("activa")->valor = -1; //* indica que non existe o rexistro en site_fpago
  }

  public function mapa_bd() {
    return new Site_fpago_mbd();
  }

  public static function inicia(FS_cbd $cbd, $id_site, $id_fpago) {
    $fp = new Site_fpago_obd($id_site, $id_fpago);

    if ($id_site  == null) return $fp;
    if ($id_fpago == null) return $fp;

    $wh_1 = $fp->atr("id_site")->sql_where($cbd);
    $wh_2 = $fp->atr("id_fpago")->sql_where($cbd);

    $fp->select($cbd, "{$wh_1} and {$wh_2}");

    return $fp;
  }

  public function select(FS_cbd $cbd, $where = null, $orderby = null) {
    $r = $cbd->consulta($this->sql_select($cbd, $where, $orderby));

    if (!$a = $r->next()) return;

    $this->post($a);
  }

  public function info($info = null) {
    if ($info != null) $this->atr("sinfo")->valor = self::info_encode( $info );

    if ($this->atr("sinfo")->valor == null) return null;

    return self::info_decode( $this->atr("sinfo")->valor );
  }

  public function activada() {
    return $this->atr("activa")->valor == 1;
  }

  public function activar(FS_cbd $cbd = null) {
    if ($cbd == null) $cbd = new FS_cbd();

    $b = $this->atr("activa")->valor == -1;

    $this->atr("activa")->valor = 1;

    if ($b) return $cbd->executa( $this->sql_insert($cbd) );


    return $cbd->executa( $this->sql_update($cbd) );
  }

  public function desactivar(FS_cbd $cbd = null) {
    if ($cbd == null) $cbd = new FS_cbd();

    if (!$this->activada()) return true;

    $this->atr("activa")->valor = "0";

    return $cbd->executa( $this->sql_update($cbd) );
  }

  public static function info_encode($info) {
    return AES_256_CBC::cifrar( serialize($info), "03we.rTy" );
  }

  public static function info_decode($info) {
    return unserialize( AES_256_CBC::descifrar($info, "03we.rTy") );
  }
}

//-----------------------------------------------

final class Site_fpago_mbd extends Mapa_bd {
  public function __construct() {
    $t = new Taboa_dbd("site_fpago");

    $t->pon_campo("id_site", new Numero(), true);
    $t->pon_campo("id_fpago", new Numero(), true);
    $t->pon_campo("activa", new Numero());
    $t->pon_campo("sinfo");

    $this->pon_taboa($t);
  }
}


//**********************************

class Pedido_fpago_obd extends Obxeto_bd {

  public function __construct() {
    parent::__construct();
  }

  public function mapa_bd() {
    return new Pedido_fpago_mbd();
  }

  public static function inicia(FS_cbd $cbd, $id_site, $ano, $id_pedido) {
    $fp = new Pedido_fpago_obd();

    $fp->atr("id_site")->valor   = $id_site;
    $fp->atr("ano")->valor       = $ano;
    $fp->atr("id_pedido")->valor = $id_pedido;

    $wh_s = $fp->atr("id_site")->sql_where($cbd);
    $wh_a = $fp->atr("ano")->sql_where($cbd);
    $wh_p = $fp->atr("id_pedido")->sql_where($cbd);

    $fp->select($cbd, "{$wh_s} and {$wh_a} and {$wh_p}");

    return $fp;
  }

  public function select(FS_cbd $cbd, $where = null, $orderby = null) {
    $r = $cbd->consulta($this->sql_select($cbd, $where, $orderby));

    if (!$a = $r->next()) return;

    $this->post($a);
  }

  public function insert(FS_cbd $cbd) {
    return $cbd->executa($this->sql_insert($cbd));
  }

  public function fpago_obd(FS_cbd $cbd = null) {
    $id_fpago = $this->atr("id_fpago")->valor;

    if ($id_fpago == null) return null;
    if ($id_fpago == 0   ) return null;

    if ($cbd == null) $cbd = new FS_cbd();

    return FPago_obd::inicia($cbd, $id_fpago);
  }

  public function enderezo($enderezo = null) {
    if ($enderezo != null) $this->atr("senderezo")->valor = serialize($enderezo);

    if ($this->atr("senderezo")->valor == null) return null;

    return unserialize($this->atr("senderezo")->valor);
  }

  public function precargo($precargo = null) {
    if ($precargo != null) $this->atr("sprecargo")->valor = serialize($precargo);


    if ($this->atr("sprecargo")->valor == null) return null;

    return unserialize($this->atr("sprecargo")->valor);
  }

  public function cc($cc = null) {
    if ($cc != null) $this->atr("scc")->valor = serialize($cc);

    if ($this->atr("scc")->valor == null) return null;

    return unserialize($this->atr("scc")->valor);
  }

  public function resumo($extendido = false) {
    if (($fpago = $this->fpago_obd()) == null) return "";

    $intro  = $fpago->atr("nome")->valor;
    $resumo = XPedidoFPago::__resumo($this, $extendido);

    if ($resumo == null) return $intro;

    return "{$intro} ( {$resumo} )";
  }

  public static function _fpago($id_site, FS_cbd $cbd = null, $predet = "...") {
    if ($cbd == null) $cbd = new FS_cbd();

    $r = $cbd->consulta("select distinct b.id_fpago, b.nome
                           from pedido_fpago a inner join fpago b on (a.id_fpago = b.id_fpago)
                          where a.id_site = {$id_site}
                          order by a.id_fpago"
                       );

    $_a = []; if ($predet != null) $_a["**"] = $predet;
    while ($_r = $r->next()) $_a[ $_r["id_fpago"] ] = "{$_r["id_fpago"]} - {$_r["nome"]}";

    return $_a;
  }
}

//-----------------------------------------------

final class XPedidoFPago extends Pedido_fpago_obd {

  public static function __resumo(Pedido_fpago_obd $fp, $extendido = false) {
    switch ( $fp->atr("id_fpago")->valor ) {
      case   1: return self::resumo_1  ($fp, $extendido);
      case   2: return self::resumo_2  ($fp, $extendido);
      case   3: return self::resumo_3  ($fp, $extendido);
      case   4: return self::resumo_4  ($fp, $extendido);
      case   5: return self::resumo_5  ($fp, $extendido);
      case   6: return self::resumo_6  ($fp, $extendido);
      case 900: return self::resumo_900($fp, $extendido);
    }

    die("XPedidoFPago.resumo(), id_fpago desconhecido");
  }

  private static function resumo_1(Pedido_fpago_obd $fp, $extendido = false) {
    $a = $fp->cc();

    $iban1 = substr($a[0],  0, 4);
    $iban2 = substr($a[0],  4, 4);
    $iban3 = substr($a[0],  8, 4);
    $iban4 = substr($a[0], 12, 4);
    $iban5 = substr($a[0], 16, 4);
    $iban6 = substr($a[0], 20, 4);

    $resumo = "{$iban1} {$iban2} {$iban3} {$iban4} {$iban5} {$iban6}";

    //* este resumo non mostra as observacions//~ if (trim($a[1]) != null) $resumo .= "&nbsp;-&nbsp;{$a[1]}";

    return $resumo;
  }

  private static function resumo_2(Pedido_fpago_obd $fp, $extendido = false) {
    $a = $fp->precargo();

    return "{$a['porcentaxe']}&nbsp;%,&nbsp;m&iacute;n.&nbsp;{$a['min']}&euro;,&nbsp;m&aacute;x.&nbsp;{$a['max']}&euro;";
  }

  private static function resumo_3(Pedido_fpago_obd $fp, $extendido = false) {
    $a = $fp->enderezo();

    if (count($a) == 0) return "<span class='texto3_erro'>Seleccione el local de recogida</span>";

    $a_k = array_keys($a);

    return $a[$a_k[0]];
  }

  private static function resumo_4(Pedido_fpago_obd $fp, $extendido = false) {
    if (!$extendido) return null;

    $s = $fp->atr("id_site")->valor;
    $a = $fp->atr("ano")->valor;
    $n = $fp->atr("id_pedido")->valor;

    $rpc = PayPal_obd::inicia(new FS_cbd(), $s, $a, $n);


    if (($ipn = $rpc->atr("IPN_response")->valor) == null) return null;

    if ($ipn == "I") return ", IPN_response: <b>INVALID</b>";


    $tx     = $rpc->atr("tx"    )->valor;
    $status = $rpc->atr("status")->valor;

    $tx_a = "<a target='_blank' href='https://www.sandbox.paypal.com/es/vst/id={$tx}'><span style='color:#0000FF;'>{$tx}</span></a>";

    return ", IPN_response: <b>VERIFIED</b>, txt_id: <b>{$tx_a}</b>, status: <b>{$status}</b>";
  }

  private static function resumo_5(Pedido_fpago_obd $fp, $extendido = false) {
    return "Pago con tarjeta de cr&eacute;dito o d&eacute;bito";
  }

  private static function resumo_6(Pedido_fpago_obd $fp, $extendido = false) {
    return "Pagon con Google Pay";
  }

  private static function resumo_900(Pedido_fpago_obd $fp, $extendido = false) {
    return "Pago Bizum";
  }
}

//-----------------------------------------------

final class Pedido_fpago_mbd extends Mapa_bd {
  public function __construct() {
    $t = new Taboa_dbd("pedido_fpago");

    $t->pon_campo("id_site"  , new Numero(), true);
    $t->pon_campo("ano"      , new Numero(), true);
    $t->pon_campo("id_pedido", new Numero(), true);
    $t->pon_campo("id_fpago" , new Numero());
    $t->pon_campo("scc");
    $t->pon_campo("senderezo");
    $t->pon_campo("sprecargo");

    $this->pon_taboa($t);

    $t2 = new Taboa_dbd("fpago");

    $t2->pon_campo("nome");

    $this->relacion_fk($t2, array("id_fpago"), array("id_fpago"));
  }
}

