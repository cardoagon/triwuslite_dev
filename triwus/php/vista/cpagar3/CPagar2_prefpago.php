<?php


interface ICPagar2_prefpago {
  public static function xrecargo(CPagar2_prefpago $cfp, Ususario_carro $carro);
  public static function xupdate(CPagar2_prefpago $cfp, Pedido_fpago_obd $fp);
  public static function xvalida(CPagar2_prefpago $cfp);
}

//--------------------------------------------

class CPagar2_prefpago extends Componente
                    implements ICPagar2_paso {

  const ptw_0   = "ptw/cpagar3/cpagar3_prefpago.html";
  const ptw_1   = "ptw/cpagar3/cpagar3_prefpago_1.html";
  const ptw_2   = "ptw/cpagar3/cpagar3_prefpago_2.html";
  const ptw_3   = "ptw/cpagar3/cpagar3_prefpago_3.html";
  const ptw_4   = "ptw/cpagar3/cpagar3_prefpago_4.html";
  const ptw_5   = "ptw/cpagar3/cpagar3_prefpago_5.html";
  const ptw_6   = "ptw/cpagar3/cpagar3_prefpago_6.html";
  const ptw_900 = "ptw/cpagar3/cpagar3_prefpago_900.html";

  private $id_fpago = -1;

  public function __construct() {
    parent::__construct("cpagar2_prefpago", Refs::url(self::ptw_0));

    //~ $this->visible = false;
  }

  public function id_fpago() {
    if ($this->id_fpago == null) return -1;

    return $this->id_fpago;
  }

  public function post_u(FGS_usuario $u = null) {
    if ($u == null) return;

    //~ $this->visible = ($u->cpago()->carro()->ct_articulos() > 0);

    $this->id_fpago = $u->cpago()->fpago()->atr("id_fpago")->valor;

    $this->__pon_chfp();

    $this->inicia_fp($u);
  }

  public function validar() {
    if ($this->id_fpago == null) return Erro::cpago_2;

    if (($erro = $this->valida_fp()) != null) return $erro;

    return null;
  }

  public function update(FGS_usuario $u) {
    $fp = new Pedido_fpago_obd();

    $fp->atr("id_fpago")->valor = $this->id_fpago();

    $this->update_fp($fp);

    $u->cpago()->fpago($fp);

    $this->recargo_fp($u->cpago()->carro());
  }

  public function operacion(EstadoHTTP $e) {
    if (($r = $this->obxeto("renderezo")) != null) {
      if ($r->control_evento()) {
        $u = $e->usuario();

        $this->update($u);

        $this->pai->obxeto("cpagar2_envio")->post_u($u);

        $this->pai->obxeto("ccarro2_lista")->preparar_saida($e->ajax());
        $this->pai->obxeto("cpagar2_envio")->preparar_saida($e->ajax());

        $this->preparar_saida($e->ajax());

        return $e;
      }
    }

    if (($e_aux = $this->operacion_chfp($e)) != null) return $e_aux;


    return null;
  }

  public function preparar_saida(Ajax $a = null) {
    $chfp_html = "";

    $a_chfp = $this->obxetos("chfp");

    if (count($a_chfp) > 0) {
      foreach($a_chfp as $ch) $chfp_html .= "<div class='cpagar2_fila_2'>" . $ch->html() . "</div>";
      //~ foreach($a_chfp as $ch) $chfp_html .= $ch->html();
    }

    $html = str_replace("[chfp]" , $chfp_html, $this->html00());
    $html = str_replace("[msxfp]", $this->msx_fp(), $html);

    $this->obxeto("illa")->post($html);

    if ($a == null) return;


    $a->pon($this->obxeto("illa"));

    $this->obxeto("illa")->post(null);
  }

  public function renderezo_html() {
    //* Este metodo chamase desde CPagar2_envio

    if ($this->id_fpago != 3) return null;

    $this->ptw = Refs::url(self::ptw_3);

    $html = "<div class='texto3_erro' style='padding: 11px;'>" . XCPagarFP_3::xvalida($this) . "</div>" . $this->html00();

    $this->ptw = Refs::url(self::ptw_0);


    return $html;
  }

  private function operacion_chfp(IEfspax_xestor $e) {
    $a_chfp = $this->obxetos("chfp");

    if (count($a_chfp) == 0) return null;

    foreach($a_chfp as $ch) {
      if (!$ch->control_evento()) continue;

      if ($this->id_fpago != null) $this->obxeto("chfp", $this->id_fpago)->post(false);

      $this->id_fpago = $e->evento()->subnome(0);

      $ch->post(true);

      $u = $e->usuario();

      $this->inicia_fp($u);

      $this->update($u);

      $this->pai->obxeto("cpagar2_envio")->update($u);
      $this->pai->obxeto("cpagar2_envio")->post_u($u);

      $this->pai->preparar_saida($e->ajax());

      return $e;
    }

    return null;
  }

  private function inicia_fp(FGS_usuario $u) {
    $id_fpago = $this->id_fpago;

        if ($id_fpago == 1  ) XCPagarFP_1  ::inicia($this    );
    elseif ($id_fpago == 2  ) XCPagarFP_2  ::inicia($this    );
    elseif ($id_fpago == 3  ) XCPagarFP_3  ::inicia($this, $u);
    elseif ($id_fpago == 4  ) XCPagarFP_4  ::inicia($this    );
    elseif ($id_fpago == 5  ) XCPagarFP_5  ::inicia($this    );
    elseif ($id_fpago == 6  ) XCPagarFP_6  ::inicia($this    );
    elseif ($id_fpago == 900) XCPagarFP_900::inicia($this    );
  }

  private function valida_fp() {
    $id_fpago = $this->id_fpago;

        if ($id_fpago == 1  ) return XCPagarFP_1  ::xvalida($this);
    elseif ($id_fpago == 2  ) return XCPagarFP_2  ::xvalida($this);
    elseif ($id_fpago == 3  ) return XCPagarFP_3  ::xvalida($this);
    elseif ($id_fpago == 4  ) return XCPagarFP_4  ::xvalida($this);
    elseif ($id_fpago == 5  ) return XCPagarFP_5  ::xvalida($this);
    elseif ($id_fpago == 6  ) return XCPagarFP_6  ::xvalida($this);
    elseif ($id_fpago == 900) return XCPagarFP_900::xvalida($this);
  }

  private function update_fp(Pedido_fpago_obd $fp) {
    $id_fpago = $fp->atr("id_fpago")->valor;

        if ($id_fpago == 1  ) XCPagarFP_1  ::xupdate($this, $fp);
    elseif ($id_fpago == 2  ) XCPagarFP_2  ::xupdate($this, $fp);
    elseif ($id_fpago == 3  ) XCPagarFP_3  ::xupdate($this, $fp);
    elseif ($id_fpago == 4  ) XCPagarFP_4  ::xupdate($this, $fp);
    elseif ($id_fpago == 5  ) XCPagarFP_5  ::xupdate($this, $fp);
    elseif ($id_fpago == 6  ) XCPagarFP_6  ::xupdate($this, $fp);
    elseif ($id_fpago == 900) XCPagarFP_900::xupdate($this, $fp);
  }

  private function recargo_fp(Ususario_carro $carro) {
    $id_fpago = $this->id_fpago;

        if ($id_fpago == 1  ) XCPagarFP_1  ::xrecargo($this, $carro);
    elseif ($id_fpago == 2  ) XCPagarFP_2  ::xrecargo($this, $carro);
    elseif ($id_fpago == 3  ) XCPagarFP_3  ::xrecargo($this, $carro);
    elseif ($id_fpago == 4  ) XCPagarFP_4  ::xrecargo($this, $carro);
    elseif ($id_fpago == 5  ) XCPagarFP_5  ::xrecargo($this, $carro);
    elseif ($id_fpago == 6  ) XCPagarFP_6  ::xrecargo($this, $carro);
    elseif ($id_fpago == 900) XCPagarFP_900::xrecargo($this, $carro);
  }

  private function msx_fp() {
        if ($this->id_fpago == 1  ) $this->ptw = Refs::url(self::ptw_1  );
    elseif ($this->id_fpago == 2  ) $this->ptw = Refs::url(self::ptw_2  );
    elseif ($this->id_fpago == 3  ) $this->ptw = Refs::url(self::ptw_3  );
    elseif ($this->id_fpago == 4  ) $this->ptw = Refs::url(self::ptw_4  );
    elseif ($this->id_fpago == 5  ) $this->ptw = Refs::url(self::ptw_5  );
    elseif ($this->id_fpago == 6  ) $this->ptw = Refs::url(self::ptw_6  );
    elseif ($this->id_fpago == 900) $this->ptw = Refs::url(self::ptw_900);
    else                          return "";

    $html = $this->html00();

    $this->ptw = Refs::url(self::ptw_0);


    return $html;
  }

  private function __pon_chfp() {
    $id_site  = $this->pai->id_site;
    $id_fpago = $this->id_fpago;

    $cbd = new FS_cbd();

    $r = $cbd->consulta("select id_fpago, nome, sinfo from v_site_fpago where id_site = {$id_site} and activa = 1 order by nome");
    
    while ($a = $r->next()) {
      $bizum = false;
      if ($a['id_fpago'] == 5) {
        $a_info = Site_fpago_obd::info_decode($a['sinfo']);

//~ echo "<pre>" . print_r($a, 1) . print_r(Site_fpago_obd::info_decode($a['sinfo']), 1) . "</pre>";

        $a['nome'] = "Tarjeta de crédito/débito";
        
        $bizum = ($a_info["config"]["bizum"] == 1);
      }

      $this->__pon_chfp_2($id_fpago, $a);
      
      if ($bizum) {
        $this->id_fpago = $id_fpago;
        
        $this->__pon_chfp_2($id_fpago, ["id_fpago"=>900, "nome"=>"Bizum"]);
      }
    }
  }

  private function __pon_chfp_2($id_fpago, $_ch) {
    $txt = "<div style='display: flex;align-items: center;'>
              <div style='min-width: 37px;'><img src='" . Refs::url("imx/cpagar/fp_{$_ch['id_fpago']}.png") . "' /></div>
              <div style='margin-left:0.3em;'>{$_ch['nome']}</div>
            </div>";

    $ch = new Checkbox("chfp", $_ch['id_fpago'] == $id_fpago, $txt);
    

    //~ $ch->envia_submit("onchange", "", "cpagar2_formapago_ancla");
    $ch->envia_ajax("onchange");

    $this->pon_obxeto($ch, $_ch['id_fpago']);
  }
}

//********************************

final class XCPagarFP_1 extends CPagar2_prefpago
                     implements ICPagar2_prefpago {

  public static function inicia(CPagar2_prefpago $cfp) {
    $sfp = Site_fpago_obd::inicia(new FS_cbd(), $cfp->pai->id_site, 1);

    $a_info = $sfp->info();

    $cfp->pon_obxeto(new Span("iban1", substr($a_info['iban'],  0, 4)));
    $cfp->pon_obxeto(new Span("iban2", substr($a_info['iban'],  4, 4)));
    $cfp->pon_obxeto(new Span("iban3", substr($a_info['iban'],  8, 4)));
    $cfp->pon_obxeto(new Span("iban4", substr($a_info['iban'], 12, 4)));
    $cfp->pon_obxeto(new Span("iban5", substr($a_info['iban'], 16, 4)));
    $cfp->pon_obxeto(new Span("iban6", substr($a_info['iban'], 20, 4)));

    $cfp->pon_obxeto(new Span("obs", $a_info['obs']));
  }

  public static function xrecargo(CPagar2_prefpago $cfp, Ususario_carro $carro) {
    $carro->sup_subtotal();
  }

  public static function xvalida(CPagar2_prefpago $cfp) {
    return null;
  }

  public static function xupdate(CPagar2_prefpago $cfp, Pedido_fpago_obd $fp) {
    $iban = $cfp->obxeto("iban1")->valor() . $cfp->obxeto("iban2")->valor() . $cfp->obxeto("iban3")->valor() .
            $cfp->obxeto("iban4")->valor() . $cfp->obxeto("iban5")->valor() . $cfp->obxeto("iban6")->valor();

    $a_cc = array($iban, $cfp->obxeto("obs")->valor());

    $fp->cc( $a_cc );
  }
}

//-------------------------

final class XCPagarFP_2 extends CPagar2_prefpago
                        implements ICPagar2_prefpago {

  public static function inicia(CPagar2_prefpago $cfp) {
    $sfp = Site_fpago_obd::inicia(new FS_cbd(), $cfp->pai->id_site, 2);

    $a_info = $sfp->info();

    $cfp->pon_obxeto(new Span("precargo", $a_info['precargo']));
    $cfp->pon_obxeto(new Span("min"     , $a_info['min']));
    $cfp->pon_obxeto(new Span("max"     , $a_info['max']));
  }

  public static function xvalida(CPagar2_prefpago $cfp) {}

  public static function xrecargo(CPagar2_prefpago $cfp, Ususario_carro $carro) {
    $precargo = $cfp->obxeto("precargo")->valor();
    $min      = $cfp->obxeto("min")->valor();
    $max      = $cfp->obxeto("max")->valor();

    $_cargofpago = array("p"=>$precargo, "m"=>$min, "M"=>$max);

    $carro->post_cargofpago("{$precargo}% recargo, m&iacute;n.&nbsp;{$min}&euro;,&nbsp;máx.&nbsp;{$max}&euro;", $_cargofpago, "%");
  }

  public static function xupdate(CPagar2_prefpago $cfp, Pedido_fpago_obd $fp) {
    $precargo = round($cfp->obxeto("precargo")->valor(), 1);
    $min      = round($cfp->obxeto("min")->valor(), 2);
    $max      = round($cfp->obxeto("max")->valor(), 2);

    $fp->precargo( array("porcentaxe"=>$precargo, "min"=>$min, "max"=>$max) );
  }
}

//-------------------------

final class XCPagarFP_3 extends CPagar2_prefpago
                        implements ICPagar2_prefpago {

  public static function inicia(CPagar2_prefpago $cfp, FGS_usuario $u) {
    $enderezo = $u->cpago()->fpago()->enderezo();
    $id_fpago = 3;

    $cfp->pon_obxeto( self::__renderezo($cfp->pai->id_site, $id_fpago, $enderezo) );
  }

  public static function xrecargo(CPagar2_prefpago $cfp, Ususario_carro $carro) {
    $carro->sup_subtotal();
  }

  public static function xupdate(CPagar2_prefpago $cfp, Pedido_fpago_obd $fp) {
    $r        = $cfp->obxeto("renderezo");

    if ($r->valor() == -1) return;

    $i        = $r->valor() + 1;
    $enderezo = $r->colle_opcion($r->valor());

    $fp->enderezo( array($i=>$enderezo) );
  }

  public static function xvalida(CPagar2_prefpago $cfp) {
    if ($cfp->obxeto("renderezo")->valor() == -1) return Erro::cpago_5;

    return null;
  }

  private static function __renderezo($id_site, $id_fpago, $enderezo) {
    $sfp = Site_fpago_obd::inicia(new FS_cbd(), $id_site, $id_fpago);

    $a_info = $sfp->info();

    $r = new Radio("renderezo", false);

    $r->envia_ajax("onclick");
    //~ $r->envia_submit("onclick");

    foreach ($a_info as $i=>$enderezo_i) $r->pon_opcion($enderezo_i);

    if (!is_array($enderezo)) {
      if (count($a_info) > 1)
        $r->post(-1);
      else
        $r->post(0);

      return $r;
    }

    $a_kenderezo = array_keys($enderezo);

    $r->post($a_kenderezo[0] - 1);


    return $r;
  }
}

//-------------------------

final class XCPagarFP_4 extends CPagar2_prefpago
                        implements ICPagar2_prefpago {

  public static function inicia(CPagar2_prefpago $cfp) {
  }

  public static function xrecargo(CPagar2_prefpago $cfp, Ususario_carro $carro) {
    $carro->sup_subtotal();
  }

  public static function xvalida(CPagar2_prefpago $cfp) {}

  public static function xupdate(CPagar2_prefpago $cfp, Pedido_fpago_obd $fp) {}
}

//-------------------------

final class XCPagarFP_5 extends CPagar2_prefpago
                        implements ICPagar2_prefpago {

  public static function inicia(CPagar2_prefpago $cfp) {
  }

  public static function xrecargo(CPagar2_prefpago $cfp, Ususario_carro $carro) {
    $carro->sup_subtotal();
  }

  public static function xvalida(CPagar2_prefpago $cfp) {}

  public static function xupdate(CPagar2_prefpago $cfp, Pedido_fpago_obd $fp) {}
}


//-------------------------

final class XCPagarFP_6 extends CPagar2_prefpago
                        implements ICPagar2_prefpago {

  public static function inicia(CPagar2_prefpago $cfp) {
  }

  public static function xrecargo(CPagar2_prefpago $cfp, Ususario_carro $carro) {
    $carro->sup_subtotal();
  }

  public static function xvalida(CPagar2_prefpago $cfp) {}

  public static function xupdate(CPagar2_prefpago $cfp, Pedido_fpago_obd $fp) {}
}

//-------------------------

final class XCPagarFP_900 extends CPagar2_prefpago
                        implements ICPagar2_prefpago {

  public static function inicia(CPagar2_prefpago $cfp) {
  }

  public static function xrecargo(CPagar2_prefpago $cfp, Ususario_carro $carro) {
    $carro->sup_subtotal();
  }

  public static function xvalida(CPagar2_prefpago $cfp) {}

  public static function xupdate(CPagar2_prefpago $cfp, Pedido_fpago_obd $fp) {}
}

