<?php

abstract class Cxfp_config extends Componente {
  const ptw_0 = "ptw/paneis/pcontrol/ventas/fpago/cxfpc_0.html";

  protected $sfp  = null;
  protected $info = null;

  private $ptw_0 = null;

  abstract public function info_inicia();
  abstract public function info_post();
  abstract public function info_valida();

  public function __construct(Site_fpago_obd $sfp, $ptw) {
    parent::__construct("cxfp_config", $ptw);

    $this->post_sfp($sfp);


    $this->pon_obxeto(Panel_fs::__bcancelar());
    $this->pon_obxeto(Panel_fs::__baceptar());

    $this->obxeto("bAceptar" )->envia_ajax("onclick");
    $this->obxeto("bCancelar")->envia_ajax("onclick");
  }

  public static function inicia(Site_fpago_obd $sfp) {
    switch ($sfp->atr("id_fpago")->valor) {
      case 1: return new Cxfpago_fp_1($sfp);
      case 2: return new Cxfpago_fp_2($sfp);
      case 3: return new Cxfpago_fp_3($sfp);
      case 4: return new Cxfpago_fp_4($sfp);
      case 5: return new Cxfpago_fp_5($sfp);
      case 6: return new Cxfpago_fp_6($sfp);
    }

    echo("Cxfp_config::inicia(), sfp.atr(\"id_fpago\").valor desconhecido ");
  }

  public function post_sfp(Site_fpago_obd $sfp) {
    $this->sfp = $sfp;

    if ($sfp->atr("sinfo")->valor == null)
      $this->info = $this->info_inicia();
    else
      $this->info = $sfp->info();
  }

  public function operacion(EstadoHTTP $e) {
    if ($this->obxeto("bAceptar" )->control_evento()) return $this->operacion_bAceptar ($e);
    if ($this->obxeto("bCancelar")->control_evento()) return $this->operacion_bCancelar($e);


    return parent::operacion($e);
  }

  public function preparar_saida(Ajax $a = null) {
    $ptw_aux = $this->ptw;

    $this->ptw = self::ptw_0;

    $html_0 = $this->html00();


    $this->ptw = $ptw_aux;

    $html = str_replace("[cxfpc_x]", $this->html00(), $html_0);


    $this->obxeto("illa")->post($html);


    if ($a == null) return;


    $a->pon($this->obxeto("illa"));

    $this->obxeto("illa")->post(null);
  }

  final public function update(FS_cbd $cbd = null) {
    $this->info_post();

    return $this->sfp->activar($cbd);
  }

  private function operacion_bAceptar(Epcontrol $e) {
    if (($erro = $this->info_valida()) != null) {
      $e->post_msx($erro);

      return $e;
    }

    $this->update();

    return $this->operacion_bCancelar($e, false);
  }

  private function operacion_bCancelar(Epcontrol $e, $control_validar = true) {
    if ($control_validar) {
      if ($this->info_valida() != null) $this->sfp->desactivar();
    }
    
    $this->visible = false;

    $this->pai->preparar_saida($e->ajax());

    return $e;
  }
}

//---------------------------------

final class Cxfpago_fp_nula extends Cxfp_config  {

  public function __construct() {
    parent::__construct(new Site_fpago_obd(), null);
    
    $this->visible = false;
  }

  public function info_inicia() {
    return null;
  }

  public function info_post() {}

  public function info_valida() {
    return null;
  }
}

//---------------------------------

final class Cxfpago_fp_1 extends Cxfp_config  {

  public function __construct(Site_fpago_obd $sfp) {
    parent::__construct($sfp, "ptw/paneis/pcontrol/ventas/fpago/cxfpc_1.html");

    $this->pon_obxeto( new Textarea("obs", 4, 55) );

    $this->pon_obxeto( Panel_fs::__text("iban1", 4, 4, " ") );
    $this->pon_obxeto( Panel_fs::__text("iban2", 4, 4, " ") );
    $this->pon_obxeto( Panel_fs::__text("iban3", 4, 4, " ") );
    $this->pon_obxeto( Panel_fs::__text("iban4", 4, 4, " ") );
    $this->pon_obxeto( Panel_fs::__text("iban5", 4, 4, " ") );
    $this->pon_obxeto( Panel_fs::__text("iban6", 4, 4, " ") );

    $this->obxeto("iban1")->post(substr($this->info['iban'],  0, 4));
    $this->obxeto("iban2")->post(substr($this->info['iban'],  4, 4));
    $this->obxeto("iban3")->post(substr($this->info['iban'],  8, 4));
    $this->obxeto("iban4")->post(substr($this->info['iban'], 12, 4));
    $this->obxeto("iban5")->post(substr($this->info['iban'], 16, 4));
    $this->obxeto("iban6")->post(substr($this->info['iban'], 20, 4));

    $this->obxeto("obs")->post($this->info['obs']);

    $this->obxeto("iban1")->style("default", "text-align: center;");
    $this->obxeto("iban2")->style("default", "text-align: center;");
    $this->obxeto("iban3")->style("default", "text-align: center;");
    $this->obxeto("iban4")->style("default", "text-align: center;");
    $this->obxeto("iban5")->style("default", "text-align: center;");
    $this->obxeto("iban6")->style("default", "text-align: center;");
  }

  public function info_inicia() {
    return array("iban"=>"", "obs"=>"", "cancelar"=>5);
  }

  public function info_post() {
    $iban = $this->obxeto("iban1")->valor() . $this->obxeto("iban2")->valor() . $this->obxeto("iban3")->valor() .
            $this->obxeto("iban4")->valor() . $this->obxeto("iban5")->valor() . $this->obxeto("iban6")->valor();

    $a_info = array("iban"     => $iban,
                    "obs"      => $this->obxeto("obs")->valor(),
                    "cancelar" => 5
                   );

    $this->sfp->info($a_info);
  }

  public function info_valida() {
    //* valida iban.

    $iban = $this->obxeto("iban1")->valor() . $this->obxeto("iban2")->valor() . $this->obxeto("iban3")->valor() .
            $this->obxeto("iban4")->valor() . $this->obxeto("iban5")->valor() . $this->obxeto("iban6")->valor();

    if (($erro = Erro::__valida_iban($iban)) != null) return $erro;

    return null;
  }
}

//---------------------------------

final class Cxfpago_fp_2 extends Cxfp_config {
  public function __construct(Site_fpago_obd $sfp) {
    parent::__construct($sfp, "ptw/paneis/pcontrol/ventas/fpago/cxfpc_2.html");

    $this->pon_obxeto( Panel_fs::__text("precargo", 2, 3, " ") );
    $this->pon_obxeto( Panel_fs::__text("min", 2, 3, " ") );
    $this->pon_obxeto( Panel_fs::__text("max", 2, 3, " ") );

    $this->obxeto("precargo")->style("default", "text-align: right;");
    $this->obxeto("min"     )->style("default", "text-align: right;");
    $this->obxeto("max"     )->style("default", "text-align: right;");

    $this->obxeto("precargo")->post($this->info['precargo']);
    $this->obxeto("min")->post($this->info['min']);
    $this->obxeto("max")->post($this->info['max']);
  }

  public function info_inicia() {
    return array("precargo"=>2, "min"=>0, "max"=>0);
  }

  public function info_post() {
    $a_info = array("precargo" => $this->obxeto("precargo")->valor(),
                    "min"      => $this->obxeto("min")->valor(),
                    "max"      => $this->obxeto("max")->valor());

    $this->sfp->info($a_info);
  }

  public function info_valida() {
    //* val precargo
    $precargo = trim($this->obxeto("precargo")->valor());

    if (!is_numeric($precargo)) return "Error, el porcentaje de cargo debe ser un número mayor o igual que cero.";

    $precargo = round(abs($precargo));

    if ($precargo < 0) return "Error, el porcentaje de cargo debe ser un número mayor o igual que cero.";

    $this->obxeto("precargo")->post($precargo);


    //* val min
    if (($min = Erro::valida_numero($this->obxeto("min")->valor(), 2)) == null)
      return "Error, el cargo m&iacute;nimo debe ser un n&uacute;mero mayor o igual que cero.";

    $this->obxeto("min")->post($min);


    //* val max
    if (($max = Erro::valida_numero($this->obxeto("max")->valor(), 2)) == null)
      return "Error, el cargo m&aacute;ximo debe ser un n&uacute;mero mayor o igual que cero.";

    $this->obxeto("max")->post($max);

    return null;
  }

}

//---------------------------------

final class Cxfpago_fp_3 extends Cxfp_config {
  public function __construct(Site_fpago_obd $sfp) {
    parent::__construct($sfp, "ptw/paneis/pcontrol/ventas/fpago/cxfpc_3.html");

    $this->pon_obxeto( new CXfpc3_lista($this->info) );
  }

  public function operacion(EstadoHTTP $e) {
    if (($e_aux = $this->obxeto("cxfpc3_lista")->operacion($e)) != null) return $e_aux;


    return parent::operacion($e);
  }

  public function info_inicia() {
    return array(1=>" ");
  }

  public function info_post() {
    $a_info = $this->obxeto("cxfpc3_lista")->a_info();

    $this->sfp->info($a_info);
  }

  public function info_valida() {
    return $this->obxeto("cxfpc3_lista")->valida();
  }
}

//---------------------------------
/*
final class Cxfpago_fp_4 extends Cxfp_config {
  public function __construct(Site_fpago_obd $sfp) {
    parent::__construct($sfp, "ptw/paneis/pcontrol/ventas/fpago/cxfpc_4.html");

    $this->pon_obxeto( new Checkbox("test", false, "Usar en modo de pruebas (usar las credenciales de la Sandbox)") );
    $this->pon_obxeto( Panel_fs::__email("id_paypal", 20, 99, " ") );

    $this->obxeto("test"     )->post( $this->info['test'] == 1 );
    $this->obxeto("id_paypal")->post( $this->info['id_paypal'] );
    
    $this->obxeto("id_paypal")->style( "default", "width: 100%;" );
  }

  public function info_inicia() {
    return array("test"=>1, "id_paypal"=>"");
  }

  public function info_post() {
    $a_info = array("test"      => ($this->obxeto("test")->valor())?"1":"0",
                    "id_paypal" => $this->obxeto("id_paypal")->valor()
                    );

    $this->sfp->info($a_info);
  }

  public function info_valida() {
    if (!Valida::email($this->obxeto("id_paypal")->valor())) 
      return "Error, debes teclear una dirección de correo elctrónico válida.";


    return null;
  }
}
*/

//---------------------------------

final class Cxfpago_fp_4 extends Cxfp_config {
  const id_ttpvvconf = "ttpvv_config";

  public function __construct(Site_fpago_obd $sfp) {
    parent::__construct($sfp, "ptw/paneis/pcontrol/ventas/fpago/cxfpc_4.html");

    $this->pon_obxeto(self::__sttpvv());
    $this->pon_obxeto(self::__tpvvconfig($this->info, $this->id_site()));
    $this->pon_obxeto( new Checkbox("test", false, "Usar en modo de pruebas") );

    $this->obxeto("test"  )->post( $this->info['test'] == 1 );
    $this->obxeto("sttpvv")->post( $this->info['tipo'] );
  }

  public function operacion(EstadoHTTP $e) {
    if ($this->obxeto("sttpvv")->control_evento()) return $this->operacion_sttpvv($e);

    if (($e_aux = $this->obxeto(self::id_ttpvvconf)->operacion($e)) != null) return $e_aux;


    return parent::operacion($e);
  }

  public function id_site() {
    return $this->sfp->atr("id_site")->valor;
  }

  public function operacion_sttpvv(Epcontrol $e) {
    $this->info['tipo']   = $this->obxeto("sttpvv")->valor();
    $this->info['config'] = "";

    $this->pon_obxeto(self::__tpvvconfig($this->info, $this->id_site()));

    //~ $this->sfp->atr("activa")->valor = "0";

    $this->preparar_saida($e->ajax());


    return $e;
  }

  public function info_inicia() {
    return array("test"=>"1", "tipo"=>"**", "config"=>null);
  }

  public function info_post() {
    if (($tipo = $this->obxeto("sttpvv")->valor()) != "**") {
      $a_info['test']   = ($this->obxeto("test")->valor())?"1":"0";
      $a_info['tipo']   = $this->obxeto("sttpvv")->valor();
      $a_info['config'] = $this->obxeto(self::id_ttpvvconf)->a_info();

      $this->sfp->info($a_info);
    }
    else
      $this->sfp->atr("activa")->valor = 0;
  }

  public function info_valida() {
    if (($tipo = $this->obxeto("sttpvv")->valor()) == "**") return "Error, debes seleccionar el tipo de TPV virtual.";

    return $this->obxeto(self::id_ttpvvconf)->valida();
  }

  private static function __sttpvv() {
    $o = array("**"=>" ... Selecciona una configuración",
                "1"=>"Checkout",
                "2"=>"Advanced Checkout");

    $s = new Select("sttpvv", $o);

    //~ $s->envia_submit("onchange");
    $s->envia_ajax("onchange");

    return $s;
  }

  private static function __tpvvconfig($a_info, $id_site) {
    switch ($a_info['tipo']) {
      case "**": return new Param(self::id_ttpvvconf);
      case 1:    return new Cxfpago_fp_4_1($a_info['config']);
      case 2:    return new Cxfpago_fp_4_2($a_info['config'], $id_site);
    }

    die("Cxfpago_fp_4.__tpvvconfig(), a_info['tipo'] decohecido.");
  }
}

//---------------------------------

final class Cxfpago_fp_5 extends Cxfp_config {
  const id_ttpvvconf = "ttpvv_config";

  public function __construct(Site_fpago_obd $sfp) {
    parent::__construct($sfp, "ptw/paneis/pcontrol/ventas/fpago/cxfpc_5.html");

    $this->pon_obxeto(self::__sttpvv());
    $this->pon_obxeto(self::__tpvvconfig($this->info, $this->id_site()));
    $this->pon_obxeto( new Checkbox("test", false, "Usar en modo de pruebas (usar las credenciales del servidor de desarrollo)") );

    $this->obxeto("test"  )->post( $this->info['test'] == 1 );
    $this->obxeto("sttpvv")->post( $this->info['tipo'] );
  }

  public function operacion(EstadoHTTP $e) {
    if ($this->obxeto("sttpvv")->control_evento()) return $this->operacion_sttpvv($e);

    if (($e_aux = $this->obxeto(self::id_ttpvvconf)->operacion($e)) != null) return $e_aux;


    return parent::operacion($e);
  }

  public function id_site() {
    return $this->sfp->atr("id_site")->valor;
  }

  public function operacion_sttpvv(Epcontrol $e) {
    $this->info['tipo']   = $this->obxeto("sttpvv")->valor();
    $this->info['config'] = "";

    $this->pon_obxeto(self::__tpvvconfig($this->info, $this->id_site()));

    //~ $this->sfp->atr("activa")->valor = "0";

    $this->preparar_saida($e->ajax());


    return $e;
  }

  public function info_inicia() {
    return array("test"=>"1", "tipo"=>"**", "config"=>null);
  }

  public function info_post() {
    if (($tipo = $this->obxeto("sttpvv")->valor()) != "**") {
      $a_info['test']   = ($this->obxeto("test")->valor())?"1":"0";
      $a_info['tipo']   = $this->obxeto("sttpvv")->valor();
      $a_info['config'] = $this->obxeto(self::id_ttpvvconf)->a_info();

      $this->sfp->info($a_info);
    }
    else
      $this->sfp->atr("activa")->valor = 0;
  }

  public function info_valida() {
    if (($tipo = $this->obxeto("sttpvv")->valor()) == "**") return "Error, debes seleccionar el tipo de TPV virtual.";

    return $this->obxeto(self::id_ttpvvconf)->valida();
  }

  private static function __sttpvv() {
    $o = array("**"=>" ... Selecciona un TPV virtual",
                "1"=>"Redsys",
                "2"=>"CECA");

    $s = new Select("sttpvv", $o);

    //~ $s->envia_submit("onchange");
    $s->envia_ajax("onchange");

    return $s;
  }

  private static function __tpvvconfig($a_info, $id_site) {
    switch ($a_info['tipo']) {
      case "**": return new Param(self::id_ttpvvconf);
      case 1:    return new Cxfpago_fp_5_redsys($a_info['config']);
      case 2:    return new Cxfpago_fp_5_ceca($a_info['config'], $id_site);
    }

    die("Cxfpago_fp_5.__tpvvconfig(), a_info['tipo'] decohecido.");
  }
}

//---------------------------------

final class Cxfpago_fp_6 extends Cxfp_config {
  public function __construct(Site_fpago_obd $sfp) {
    parent::__construct($sfp, "ptw/paneis/pcontrol/ventas/fpago/cxfpc_6.html");

    $this->pon_obxeto( new Checkbox("test", false, "Usar en modo de pruebas") );
    $this->pon_obxeto( Panel_fs::__email("id_gpay", 20, 99, " ") );

    $this->obxeto("test"   )->post( $this->info['test'] == 1 );
    $this->obxeto("id_gpay")->post( $this->info['id_gpay'] );
    
    $this->obxeto("id_gpay")->style( "default", "width: 100%;" );
  }

  public function info_inicia() {
    return array("test"=>1, "id_gpay"=>"");
  }

  public function info_post() {
    $a_info = array("test"    => ($this->obxeto("test")->valor())?"1":"0",
                    "id_gpay" => $this->obxeto("id_gpay")->valor()
                    );

    $this->sfp->info($a_info);
  }

  public function info_valida() {
    if ($this->obxeto("id_gpay")->valor() == "") 
      return "Error, debes teclear un <i>MerchantId</i>.";


    return null;
  }
}

//*************************************************

final class CXfpc3_lista extends FS_lista
                      implements Iterador_bd {

  private $i = 0;

  public function __construct($a_enderezo) {
    parent::__construct("cxfpc3_lista", new CXfpc3_ehtml());

    $this->pon_obxeto(self::__lengadir());

    for ($i = 1; $i <= count($a_enderezo); $i++) $this->pon_obxeto( self::__tenderezo( $a_enderezo[$i] ), $i );
  }

  public function __count() {
    return count($this->obxetos("tenderezo"));
  }

  public function descFila() {}

  public function next() {
    $this->i++;

    if ($this->i > $this->__count()) {
      $this->i = 0;

      return null;
    }


    return array($this->i, $this->obxeto("tenderezo", $this->i));
  }

  public function operacion(EstadoHTTP $e) {
    if ($this->obxeto("lengadir")->control_evento()) return $this->operacion_lengadir($e);

    $evento = $e->evento();

    if ($this->control_fslevento($evento, "lsuprimir")) return $this->operacion_lsuprimir($e, $evento->subnome(0));


    return parent::operacion($e);
  }

  public function valida() {
    while ($a_t = $this->next()) {
      $a_t[1]->post( trim($a_t[1]->valor()) );

      if ($a_t[1]->valor() == "") $this->suprimir($a_t[0]);
    }



    if ($this->__count() > 1) return null;

    if ($this->obxeto("tenderezo", 1)->valor() == " ") return "Error, debes teclear al menos una dirección postal.";


    return null;
  }

  public function a_info() {
    $a = null;

    while ($a_t = $this->next()) $a[ $a_t[0] ] = $a_t[1]->valor();


    return $a;
  }

  protected function __iterador() {
    return $this;
  }

  private function operacion_lengadir(Epcontrol $e) {
    $i = $this->__count() + 1;

    $this->pon_obxeto(self::__tenderezo(" "), $i);

    $this->preparar_saida($e->ajax());

    return $e;
  }

  private function operacion_lsuprimir(Epcontrol $e, $i) {
    $this->suprimir($i);

    $this->preparar_saida($e->ajax());

    return $e;
  }

  private function suprimir($i) {
    if ( ($ct = $this->__count()) == 1 )
      $this->obxeto("tenderezo", $i)->post(" ");
    else {
      for ($i = 1; $i < $ct; $i++) $this->obxeto("tenderezo", $i)->post( $this->obxeto("tenderezo", $i)->valor() );

      $this->sup_obxeto("tenderezo", $i);
    }
  }

  private static function __lengadir() {
    $l = Panel_fs::__bmais("lengadir", "Añadir", "Añadir dirección postal de recogida.");

    $l->envia_ajax("onclick");


    return $l;
  }

  private static function __tenderezo($enderezo) {
    $t = Panel_fs::__text("tenderezo", 33, 255, " ");

    $t->post($enderezo);

    $t->style("default", "width: 97%;");

    return $t;
  }

}

//---------------------------------

final class CXfpc3_ehtml extends FS_ehtml {
  const style_td   = "style='padding: 2px 4px 3px 5px;'";


  public function __construct() {
    parent::__construct();

    $this->style_table = "";
    $this->align       = "center";
    $this->width       = "100%";
    $this->cellspacing = 0;
    $this->cellpadding = 0;
    $this->border      = 0;
  }

  protected function cabeceira($df = null) {
    return "<tr {$style_tr}>
              <td colspan=2>Lista de de direcciones de postales.</td>
            </tr>";
  }

  protected function linha_detalle($df, $f) {
    $style_tr = FS_ehtml::style_tr;
    $style_td = self::style_td;

    return "<tr>
              <td width='*'>" . $f[1]->html() . "</td>
              <td {$style_td}>" . self::__lsuprimir($this, $f[0]) . "</td>
            </tr>";
  }

  protected function totais($df = null) {
    return "<tr {$style_tr}>
              <td colspan=2>" . $this->xestor->obxeto("lengadir")->html() . "</td>
            </tr>";
  }

  private static function __lsuprimir(CXfpc3_ehtml $ehtml, $i) {
    $l = Panel_fs::__bsup("lsuprimir", "");

    $l->envia_ajax("onclick");


    return $ehtml->__fslControl($l, $i)->html();
  }
}


//*************************************************

interface ICxfp_config {
  public function a_info();
  public function valida();
}

//-------------------------------

final class Cxfpago_fp_4_1 extends Componente
                        implements ICxfp_config {

  public function __construct($a_info) {
    parent::__construct(Cxfpago_fp_4::id_ttpvvconf, "ptw/paneis/pcontrol/ventas/fpago/cxfpc_4_1.html");

    $this->pon_obxeto( Panel_fs::__email("id_paypal", 20, 99, " ") );
    

    if ($a_info != null) {
      $this->obxeto("id_paypal")->post( $a_info['id_paypal'] );
    }

  }
  
  public function info_post() {
    $a_info = array("test"      => ($this->obxeto("test")->valor())?"1":"0",
                    "id_paypal" => $this->obxeto("id_paypal")->valor()
                    );

    $this->sfp->info($a_info);
  }

  public function info_valida() {
    if (!Valida::email($this->obxeto("id_paypal")->valor())) 
      return "Error, debes teclear una dirección de correo elctrónico válida.";


    return null;
  }
  
  public function a_info() {
    return array("id_paypal" => $this->obxeto("id_paypal")->valor());
  }

  public function valida() {
    $a_info = $this->a_info();

    if (!Valida::email($a_info["id_paypal"])) 
      return "Error, debes teclear un email válido.";


    return null;
  }
}

//-------------------------------------------------------

final class Cxfpago_fp_4_2 extends Componente
                           implements ICxfp_config {

  public function __construct($a_info) {
    parent::__construct(Cxfpago_fp_4::id_ttpvvconf, "ptw/paneis/pcontrol/ventas/fpago/cxfpc_4_2.html");


    $this->pon_obxeto(Panel_fs::__text    ("kpub"      ));
    $this->pon_obxeto(Panel_fs::__password("kpri", true));

    //~ $this->pon_obxeto(new Param("lverfirma"));

    if ($a_info != null) {
      $this->obxeto("kpub" )->post($a_info["kpub"]);
      $this->obxeto("kpri" )->post($a_info["kpri"]);

      //~ $this->pon_obxeto(Panel_fs::__password_lver("lverfirma", $a_info['firma']));
    }
  }

  public function a_info() {
    //~ $this->pon_obxeto(Panel_fs::__password_lver("lverfirma", trim($this->obxeto("firma")->valor())));

    return array("kpub"  => trim($this->obxeto("kpub")->valor()),
                 "kpri"  => trim($this->obxeto("kpri")->valor())
                );
  }

  public function valida() {
    $a_info = $this->a_info();

    $erro = null;

    if ($a_info["kpub"] == null) $erro .= "&bull;&nbsp;Error, debes pegar el token público.<br />";
    if ($a_info["kpri"] == null) $erro .= "&bull;&nbsp;Error, debes pegar el token privado.<br />";


    return $erro;
  }
}


//-------------------------------

final class Cxfpago_fp_5_redsys extends Componente
                             implements ICxfp_config {

  public function __construct($a_info) {
    parent::__construct(Cxfpago_fp_5::id_ttpvvconf, "ptw/paneis/pcontrol/ventas/fpago/cxfpc_5_redsys.html");

    $this->pon_obxeto(Panel_fs::__text    ("comercio", 14,  9, " el campo comercio"));
    $this->pon_obxeto(Panel_fs::__text    ("terminal", 14, 10, " el campo terminal"));
    
    $this->pon_obxeto(Panel_fs::__password("firma", true, false, 15, 55, " el campo firma"   ));

    $this->pon_obxeto(new Param("lverfirma"));

    $this->pon_obxeto( new Checkbox("chBizum", false, "Aceptar pagos con Bizum") );


    if ($a_info != null) {
      $this->obxeto("comercio")->post($a_info['comercio']);
      $this->obxeto("terminal")->post($a_info['terminal']);
      $this->obxeto("firma"   )->post($a_info['firma']);
      $this->obxeto("chBizum" )->post($a_info['bizum']);

      //~ $this->pon_obxeto(Panel_fs::__password_lver("lverfirma", $a_info['firma']));
    }
  }

  public function a_info() {
    //~ $this->pon_obxeto(Panel_fs::__password_lver("lverfirma", trim($this->obxeto("firma")->valor())));

    return array("comercio" => trim($this->obxeto("comercio")->valor()),
                 "terminal" => trim($this->obxeto("terminal")->valor()),
                 "firma"    => trim($this->obxeto("firma")->valor()),
                 "bizum"    => trim($this->obxeto("chBizum")->valor())
                );
  }

  public function valida() {
    $a_info = $this->a_info();

    $erro = null;

    if ($a_info['comercio'] == null) $erro .= "&bull;&nbsp;Error, debes teclear el &laquo;merchantID&raquo;.<br />";
    if ($a_info['terminal'] == null) $erro .= "&bull;&nbsp;Error, debes teclear el &laquo;yerminal&raquo;.<br />";
    if ($a_info['firma'   ] == null) $erro .= "&bull;&nbsp;Error, debes teclear la &laquo;firma&raquo;.<br />";


    return $erro;
  }
}

//-------------------------------------------------------

final class Cxfpago_fp_5_ceca extends Componente
                             implements ICxfp_config {

  public function __construct($a_info, $id_site) {
    parent::__construct(Cxfpago_fp_5::id_ttpvvconf, "ptw/paneis/pcontrol/ventas/fpago/cxfpc_5_ceca.html");


    $this->pon_obxeto(Panel_fs::__password("firma" , true, false  , 15, 55));
    $this->pon_obxeto(Panel_fs::__text("MerchantID" , 14,  9));
    $this->pon_obxeto(Panel_fs::__text("AcquirerBIN", 14, 10));
    $this->pon_obxeto(Panel_fs::__text("TerminalID" , 14,  8));

    //~ $this->pon_obxeto(new Param("lverfirma"));

    $this->pon_obxeto(self::tipn($id_site));

    $this->pon_obxeto( new Checkbox("chBizum", false, "Aceptar pagos con Bizum") );

    if ($a_info != null) {
      $this->obxeto("firma"      )->post($a_info['firma'      ]);
      $this->obxeto("MerchantID" )->post($a_info['MerchantID' ]);
      $this->obxeto("AcquirerBIN")->post($a_info['AcquirerBIN']);
      $this->obxeto("TerminalID" )->post($a_info['TerminalID' ]);
      $this->obxeto("chBizum"    )->post($a_info['bizum'      ]);

      //~ $this->pon_obxeto(Panel_fs::__password_lver("lverfirma", $a_info['firma']));
    }


  }

  public function a_info() {
    //~ $this->pon_obxeto(Panel_fs::__password_lver("lverfirma", trim($this->obxeto("firma")->valor())));

    return array("firma"       => trim($this->obxeto("firma"      )->valor()),
                 "MerchantID"  => trim($this->obxeto("MerchantID" )->valor()),
                 "AcquirerBIN" => trim($this->obxeto("AcquirerBIN")->valor()),
                 "TerminalID"  => trim($this->obxeto("TerminalID" )->valor()),
                 "bizum"       => trim($this->obxeto("chBizum"    )->valor())
                );
  }

  public function valida() {
    $a_info = $this->a_info();

    $erro = null;

    if ($a_info['MerchantID' ] == null) $erro .= "&bull;&nbsp;Error, debes teclear el &laquo;merchantID&raquo; .<br />";
    if ($a_info['AcquirerBIN'] == null) $erro .= "&bull;&nbsp;Error, debes teclear el &laquo;acquirerBIN&raquo;.<br />";
    if ($a_info['TerminalID' ] == null) $erro .= "&bull;&nbsp;Error, debes teclear el &laquo;terminalID&raquo;.<br />";
    if ($a_info['firma'      ] == null) $erro .= "&bull;&nbsp;Error, debes teclear la &laquo;firma&raquo;.<br />";


    return $erro;
  }

  private static function tipn($id_site) {
    $t = new Text("tipn", 20, true);

    $t->post( Efs::url_paxina($id_site) . "/rpc/ceca/ipn.php" );

    //~ $t->style("readonly", "border: 1px solid #777777; width:100%; color: #000000; background-color: #B7BDDC; padding: 3px 6px 3px 6px;");


    return $t;
  }
}

