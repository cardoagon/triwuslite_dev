<?php


final class CCarro2 extends    Componente
                    implements I_fs  {

  const ptw_info = "ptw/cpagar3/ccarro3_bcarro_info-mobil.html";

  public $id_site;

  public function __construct(Site_obd $s) {
    parent::__construct("ccarro");

    $this->id_site = $s->atr("id_site")->valor;

    $this->pon_obxeto(new Hidden("hinfo1"));
    $this->pon_obxeto(new Hidden("hinfo2"));

    $this->pon_obxeto(new Param("bCarrito")); //* manter compatibilidade Tilia

    $this->obxeto("illa")->style("default", "display: inline-block;");
  }

  public function config_lectura() {}
  public function config_edicion() {}

  public function operacion(EstadoHTTP $e) {
    if ($this->obxeto("hinfo1")->control_evento()) return $this->operacion_hinfo_1($e);
    if ($this->obxeto("hinfo2")->control_evento()) return $this->operacion_hinfo_2($e);

    return null;
  }

  public function config_usuario(IEfspax_xestor $e = null) {
    $carro = $e->usuario()->cpago()->carro();


    $this->pon_obxeto( new Hidden("bCarro") );


    $src = Refs::url( $e->config()->venta("bcarro") );

    $this->pon_obxeto( self::__bCarrito($src, $carro->ct_articulos()) );
  }

  public function preparar_saida(Ajax $a = null) {
    $html = ($this->visible)?$this->obxeto("bCarrito")->html():"";

    $this->obxeto("illa")->post($html);

    if ($a == null) return;


    $a->pon($this->obxeto("illa"));

    $this->obxeto("illa")->post(null);
  }

  private function operacion_hinfo_1(EstadoHTTP $e) {
    $_info = $e->usuario()->cpago()->carro()->_info();

    $lpago = $html = Paxina_reservada_obd::action_pago($e->url_limpa);

    $html = LectorPlantilla::plantilla( Refs::url( self::ptw_info ) );

    $html = str_replace("[carro_t]", $_info["t"], $html);
    $html = str_replace("[carro_u]", $_info["u"], $html);
    $html = str_replace("[lpago]"  , $lpago     , $html);


    $r = json_encode(array("ok" => 1, "html" => $html));

    $e->ajax()->pon_ok(true, "elmt_ventas_postResposta({$r})");

    //~ echo "<pre>" . print_r($_info, true) . "</pre>";
    //~ echo "<pre>" . print_r($html , true) . "</pre>";


    return $e;
  }

  public function operacion_hinfo_2(EstadoHTTP $e) {
    $_info = $e->usuario()->cpago()->carro()->_info();

    $r = json_encode($_info);

    $e->ajax()->pon_ok(true, "ccarro_info2_resposta({$r})");

    //~ echo "<pre>" . print_r($_info, true) . "</pre>";
    //~ echo "<pre>" . print_r($html , true) . "</pre>";


    return $e;
  }

  private static function __bCarrito($src, $ct_articulos) {
    $etq = "&nbsp;"; $title = null;
    if ($ct_articulos > 0) {
      $etq   = "<div class='cpagar2_bcarro_ct'>{$ct_articulos}</div>";
      $title = "( {$ct_articulos} )";
    }


    $b = new Button("bCarrito", $etq);

    $b->title = $title;

    $b->clase_css("default", "cpagar2_bcarro");
    $b->style("default", "background-image: url({$src});background-size: contain; background-repeat: no-repeat;");

    //~ $b->pon_eventos("onclick", "ccarro_info()");

    return $b;
  }

}

//***************************************

final class CCarro2_lista extends FS_lista
                       implements ICPagar2_paso {

  const ptw_0 = "ptw/cpagar3/ccarro3_lista.html";
  const ptw_2 = "ptw/cpagar3/ccarro3_baleiro.html";

  public $id_site;
  public $ucarro;

  public $t      = null;
  public $t_ive  = null;
  public $t_alma = null;

  public $t_dto  = null;

  //~ public $mobil = false;


  public function __construct($id_site) {
    parent::__construct("ccarro2_lista", new CCarro2l_ehtml(), Refs::url(self::ptw_0));

    $this->id_site = $id_site;

    $this->pon_obxeto(Panel_fs::__text("tdesconto", 101, 99, " ... tu código promocional"));

    $this->obxeto("tdesconto")->style("default", "width: 55%; text-align: center;");


    $this->pon_obxeto( new CCarro2_totais  () );
    $this->pon_obxeto( new CCarro2_regalos ($id_site) );

    $this->pon_obxeto( new Hidden("elmt_ventas_uds") ); //* conectores con elmt_ventas_post
    $this->pon_obxeto( new Hidden("elmt_ventas_i"  ) ); //* conectores con elmt_ventas_post
  }

  public function id_idioma() {
    return $this->pai->id_idioma;
  }

  public function __count() {
    return $this->ucarro->ct();
  }

  protected function html_baleiro() {
    //~ if (!$this->visible) return "";

    $this->ptw = Refs::url(self::ptw_2);

    return "";
  }

  public function post_u(FGS_usuario $u = null) {
    $this->ucarro  = $u->cpago()->carro();

    $this->ptw = ($this->__count() > 0)?Refs::url(self::ptw_0):Refs::url(self::ptw_2);

    //~ $this->mobil = $mobil; //* purga php8
  }

  public function validar() {
    if ($this->ucarro->ct_articulos() == 0) return Erro::cpago_1;

    return null;
  }

  public function validar_stock($id_site) {
    return $this->ucarro->validar_stock($id_site);
  }

  public function operacion(EstadoHTTP $e) {
//~ echo $e->evento()->html();

    if ($this->obxeto("elmt_ventas_i")->control_evento()) { //* conexión con elmt_ventas_post, elimina artículo del carro.
      $i = $this->obxeto("elmt_ventas_i"  )->valor();

      $this->ucarro->sup($i);

      $e->ajax()->pon_ok();

      //* configura ccarro
      $e->obxeto("ccarro")->config_usuario($e);

      $e->obxeto("ccarro")->preparar_saida($e->ajax());



      return $e;
    }

    if ($this->obxeto("elmt_ventas_uds")->control_evento()) { //* conexión con elmt_ventas_post, cambia unidades.
      $i = $this->obxeto("elmt_ventas_i"  )->valor();
      $u = $this->obxeto("elmt_ventas_uds")->valor();

      $_u = $this->ucarro->post_unidades($i, $u);

      if ($_u[1]) {
        $e->post_msx(CPagar2_ml::$cpago2[3][$this->id_idioma()] . "  <b>{$_u[2]}</b>.");
        
        $_u[0] -= 1;
      }

      $t = number_format($this->ucarro->total(), 2, ",", ".");


      $e->ajax()->pon_ok(true, "elmt_ventas_postUnidadesResposta({$_u[0]}, '{$t}')");

      $this->preparar_saida($e->ajax());


      return $e;
    }

    if (($e_aux = $this->obxeto("cregalos")->operacion($e)) != null) return $e_aux;

    $evento = $e->evento();

    if ($this->control_fslevento($evento, "uds")) {
      $i = strval($evento->subnome(0));
      $u = $this->obxeto("uds", $i)->valor();

      $_u = $this->ucarro->post_unidades($i, $u);

      if ($_u[1]) {
        $e->post_msx(CPagar2_ml::$cpago2[3][$this->id_idioma()] . "  <b>{$_u[2]}</b>.");
      }

      $this->preparar_saida($e->ajax());


      return $e;
    }

    if ($this->control_fslevento($evento, "borrar")) {
      $i_carro = $evento->subnome(0);

      $this->ucarro->sup($i_carro);

      //* actualiza icono del carro.
      $e->obxeto("ccarro")->config_usuario($e);

      $e->obxeto("ccarro")->preparar_saida($e->ajax());

      //* actualiza cregalos.
      $this->obxeto("cregalos")->devolver_regalo($i_carro);

      //* o carro quedo baleiro.
      if ($this->ucarro->ct() == 0) {
        $this->pai->preparar_saida($e->ajax());

        $e = $e->obxeto("ccarro")->operacion_hinfo_2($e);

        return $e;
      }


      $this->preparar_saida($e->ajax());


      return $e;
    }

    return null;
  }

  public function chsabado() {
    return $this->pai->chsabado();
  }

  public function calcula_portes() {
    $t     = 0;
    $_p[1] = array("t"=> "", "i"=>0, "m"=>"€");

    if ($this->pai->obxeto("cpagar2_prefpago")->id_fpago() == 3            ) return array($t, $_p);
    if ($this->pai->obxeto("cpagar2_envio")->obxeto("chrecollida")->valor()) return array($t, $_p);
    if (!$this->ucarro->precisa_envio()                                    ) return array($t, $_p);

//~ echo "<pre>" . print_r($this->t_alma, true) . "</pre>";

    //* Calcula portes ordinarios.
    foreach($this->t_alma as $id_almacen=>$t_alma_i) {
      $a_obd = $this->ucarro->almacen($id_almacen);

      if (is_array($a_obd)) continue;

      $_param['total'     ] = $t_alma_i["i"];
      $_param['pesos'     ] = $t_alma_i["p"];
      $_param['chsabado'  ] = $this->chsabado();
      $_param['id_destino'] = $this->pai->id_destino();
      $_param['id_country'] = $this->pai->id_country();
      $_param['finde'     ] = $this->ucarro->finde;

      $ti = $a_obd->calcula($_param);

      $t += $ti;

      $_p[$id_almacen]['i'] = $ti;
      $_p[$id_almacen]['t'] = $a_obd->atr("nome")->valor;
      $_p[$id_almacen]['m'] = "€";
    }


    return array($t, $_p);
  }

  public function pvp($i, $ive = false, $des = false, $u = -1, $reserva = true) {
    return $this->ucarro->pvp($i, $ive, $des, $u, $reserva);
  }

  public function peso($i, $u = -1) {
    return $this->ucarro->peso($i, $u);
  }

  public function verificar_descontos_directos($p, $i) {
    return $this->ucarro->verificar_descontos_directos($p, $i);
  }

  public function verificar_descontos_totais($t) {
    $this->t_dto = "0";

    if (($p = $this->ucarro->verificar_descontos_totais($t)) == null) return null;

    $this->t_dto = $p->atr("dto")->valor;


    return $p;
  }

  public function oferta_html($p, $i) {
    return $this->ucarro->oferta_html($p, $i);
  }

  public function preparar_saida(Ajax $a = null) {
    $this->t      = 0;
    $this->t_ive  = array();
    $this->t_alma = array();  //* almacén para calcular portes.

    parent::preparar_saida($a);

    //~ $this->obxeto("ccodigo" )->preparar_saida($a);
    $this->obxeto("cregalos")->preparar_saida($a);
    $this->obxeto("ctotais" )->preparar_saida($a);
  }

  public function post_total($t, $peso, $pive, $id_almacen) {
    $this->t += $t;
    
    if (!isset($this->t_ive[$pive])) $this->t_ive[$pive] = 0;
    
    $this->t_ive[$pive] += $t;
    
    if (!isset($this->t_alma[$id_almacen])) $this->t_alma[$id_almacen] = ["i"=>0, "p"=>0];
      
    $this->t_alma[$id_almacen]["i"] += $t;
    $this->t_alma[$id_almacen]["p"] += $peso;
  }

  protected function __iterador() {
    return $this->ucarro;
  }
}

//---------------------------------------

final class CCarro2l_ehtml extends FS_ehtml {

  private $ct_alma = 0;

  public function __construct() {
    parent::__construct();

    $this->class_table = "lista_carro";
  }

  protected function inicia_saida() {
    return "
      <div class='{$this->class_table}'>\n";
  }

  protected function cabeceira($df = null) {
    $this->xestor->ucarro->id_destino = $this->xestor->pai->id_destino();
    $this->xestor->ucarro->id_country = $this->xestor->pai->id_country();

    $this->ct_alma = $this->xestor->ucarro->ct_alma();

    return "";
  }

  protected function linha_detalle($df, $f) {
    //~ echo "<pre>" . print_r($f, true) . "</pre>";

    if ($this->xestor->readonly) return "";

    $id_articulo = $f[0];

    $pvp_i  = $this->xestor->pvp ($f['i'], true, true);

    $peso_i = $this->xestor->peso($f['i']);

    $pive   = $this->xestor->ucarro->iva_aplicable($f['i']);

    $this->xestor->post_total($pvp_i, $peso_i, $pive, $f['id_almacen_portes']);


    $m = "&euro;"; //~ if ($this->m == null) $this->m = Articulo_obd::$a_moeda[$f['moeda']];

    $notas = Valida::split($f[2], 99);



    return self::__separador($f) .
           self::__nome($f) .
           self::__almacen($this, $f) . "
           <div style='display:flex;flex-wrap:nowrap;margin-top:0.5em;'>
             <div class='lista_carro_aux_3a'>" . self::__imx($this, $f) . "</div>
             <div class='lista_carro_aux_3b'>
               <div>
                 " . self::__unidades($this, $f) . "
                 " . self::__tpvp($this, $f, $m) . "
               </div>
               <div>" . self::describe_html($this, $f) . "</div>
             </div>
             <div class='lista_carro_aux_3c'>" . self::__bborrar($this, $f) . "</div>
           </div>";
  }

  protected function totais() {
    return "";
  }

  protected function finaliza_saida() {
    return "
      </div>";
  }

  private function describe_html(CCarro2l_ehtml $ehtml, $f) {
    if (($a = $f['describe_html']) != null) $a = "<div style='padding: 5px;'>{$a}</div>";
    if (($b = $f['prsto_html'   ]) != null) $b = "<div style='padding: 5px;'>{$b}</div>";


    if (($a == null) && ($b == null)) return "";

    if (($a != null) && ($b != null)) $a .= "---"; //* engadimos separador.

    return "{$a}{$b}";
  }

  private static function __nome($f) {
    $n = Valida::split($f[1], 33);

    $href = Paxina_reservada_obd::action_producto($f[0], $f[1], true);

    return "<div class='lista_carro_aux_2_0'><a href='{$href}'>{$n}</a></div>";
  }

  private static function __almacen(CCarro2l_ehtml $ehtml, $f) {
    if ($ehtml->ct_alma < 2) return "";

    $a = $ehtml->xestor->ucarro->almacen($f['id_almacen_portes']);

    if ($a == null) return "";


    return "<div>" . $a->atr("nome")->valor . "</div>";
  }

  private static function __separador($f) {
    if ($f['i'] == 0) return "";

    return "<div style='margin-top:7px;padding-top:7px;border-top:1px solid #eee;'></div>";
  }

  private static function __ive(CCarro2l_ehtml $ehtml, $pive) {
    $tive = CPagar2_ml::$carro[2][$ehtml->xestor->id_idioma()];

    return "<div style = 'display: inline-block;'>{$tive}: {$pive}%</div>";
  }

  private static function __pvpplus(CCarro2l_ehtml $ehtml, $f, $moeda) {
    if ($f['pvp_plus'] == 0) return "0.00&nbsp;{$moeda}";

    return number_format( ($f['pvp_plus'] * $f[3]), 2, ",", "." ) . "&nbsp;{$moeda}";
  }

  private static function __unidades(CCarro2l_ehtml $ehtml, $f) {
    $i = $f['i'];
    $u = $f[3];

    if ($ehtml->xestor->ucarro->esRegalo($i)) return "<div class='lista_carro_aux_2_1'></div>";

    $t = Panel_fs::__text("uds", 4, 4, " ");


    $style = "width: 37px; max-width: 37px; text-align: right; margin: 0 3px;";

    if ($ehtml->xestor->readonly) {
      $t->readonly = true;

      $t->style("readonly", "{$style} border: 0;");
    }
    else {
      $t->envia_ajax("onchange");
      //~ $t->envia_submit("onchange");

      $t->style("default", $style);
      $t->style("readonly", $style);
    }

    $t->clase_css("default", "text cpagar3_text");

    $t->post($u);


    if ($i == 0) $i = "0";


    $html_b = self::__bmais($ehtml, $f, -1);
    $html_t = $ehtml->__fslControl($t, $i)->html();
    $html_B = self::__bmais($ehtml, $f, 1);

    return "<div class='lista_carro_aux_2_1'>{$html_b}{$html_t}{$html_B}</div>";
  }

  private static function __tpvp(CCarro2l_ehtml $ehtml, $f, $moeda) {
    $tpvp = $ehtml->xestor->pvp($f['i'], true, true);

    $tpvp_html = number_format($tpvp, 2, ",", ".") . "&nbsp;{$moeda}";

    if ($ehtml->xestor->ucarro->esRegalo($f['i'])) {
      return "<div class='lista_carro_aux_2_2'>
                <div style='color:#ba0000;'>{$tpvp_html}</div>
                <div style='font-size: 77%; margin: 5px 0;'>REGALO</div>
              </div>";
    }

    if ( !$ehtml->xestor->verificar_descontos_directos($f["desc_p"], $f['i']) )
      return "<div class='lista_carro_aux_2_2'>{$tpvp_html}</div>";

    $pvp_0 = $ehtml->xestor->pvp($f['i'], true, false);
    $pvp_0 = number_format($pvp_0, 2, ",", ".") . "&nbsp;{$moeda}";

    $oferta_html = $ehtml->xestor->oferta_html($f["desc_p"], $f['i']);


    return "<div class='lista_carro_aux_2_2'>
              <div style='color:#ba0000;'>{$tpvp_html}</div>
              <div style='font-size: 77%; margin: 5px 0;'>{$oferta_html}</div>
              <div style='font-size: 91%;'><strike>{$pvp_0}</strike></div>
            </div>";
  }

/*
  private static function lprsto_modif(CCarro2l_ehtml $ehtml, $f) {
    if ($f['prsto_ops'] == null) return "";


    $i = $f['i'];

    $l = new Div("lprsto_modif", "Modificar");

    $l->clase_css("default" , "lista_carro_link");
    $l->clase_css("readonly", "lista_carro_link_r");

    $l->clase_css("default" , "lista_carro_link");
    $l->clase_css("readonly", "lista_carro_link_r");

    $l->visible = !$ehtml->xestor->readonly;


    //~ $l->envia_ajax("onclick");
    $l->envia_submit("onclick");


    if ($i == 0) $i = "0";

    return $ehtml->__fslControl($l, $i)->html();
  }
*/
  private static function __bmais(CCarro2l_ehtml $ehtml, $f, $s = 1) {
    if ($ehtml->xestor->readonly) return "";

    $i = $f['i'];

    if ($s == 1) {
      $t = "+";
    }
    else {
      $t = "&minus;";
    }

    $b = new Button("bmais", $t);

    $b->clase_css("default", "lista_carro_bmais");

    $b->pon_eventos("onclick", "cpagar2_bmais({$i}, {$s})");


    if ($i == 0) $i = "0";

    return $ehtml->__fslControl($b, $i)->html();
  }

  private static function __bborrar(CCarro2l_ehtml $ehtml, $f) {
    if ($ehtml->xestor->readonly) return "";

    $i = $f['i'];

    $b = Panel_fs::__bsup("borrar", null, null);


    //~ $b->envia_submit("onclick", "", "ccarro_lista");
    $b->envia_ajax("onclick");

    if ($i == 0) $i = "0";

    return $ehtml->__fslControl($b, $i)->html();
  }

  private static function __imx(CCarro2l_ehtml $ehtml, $f) {
    $estrela = "";
    if ($ehtml->xestor->ucarro->esRegalo($f['i'])) {
      $estrela = "<div class='lista_carro_imx_b'>&starf;</div>";
    }

    return "<div class='lista_carro_imx' style='background-image: url(\"{$f['url']}\");'>{$estrela}</div>";
  }
}

//***************************************

final class CCarro2_totais extends Componente {
  const ptw_0 = "ptw/cpagar3/ccarro3_total.html";


  public function __construct() {
    parent::__construct("ctotais", Refs::url(self::ptw_0));

    $this->pon_obxeto(new Param("p"));
  }

  public function preparar_saida(Ajax $a = null) {
    $this->obxeto("p")->post( $this->html_totais() );

    parent::preparar_saida($a);
  }

  protected function html_totais() {
    $t = $this->pai->t;

    $html = $this->html_dato("Importe", $t, 2, "€");

    list($t, $html) = $this->html_dto       ($t, $html);
    list($t, $html) = $this->html_portes    ($t, $html);
    list($t, $html) = $this->html_cargofpago($t, $html);

    $html .= $this->html_dato("Total a pagar", $t, 2, "€", "lista_carro_total_0000");


    return $html;
  }

  private function html_dto($t, $html) {
    $p = $this->pai->verificar_descontos_totais($t);

    if ($p == null) return array($t, $html);

//~ print_r($p->a_resumo());

    $dto = $p->atr("dto")->valor;

    $html .= $this->html_titulo( "Descuento promocional" );

    $html .= $this->html_dato( $p->atr("nome")->valor, -1 * $dto, 2, "€" );


    return array($t - $dto, $html);
  }

  private function html_portes($t, $html) {
    /**
     *  $_p[i] = array("t"=> texto descriptivo, "i"=>importe, "m"=>moeda)
     */

    $_p = $this->pai->calcula_portes();

//~ echo "<pre>" . print_r($_p, true) . "</pre>";

    if (count($_p[1]) == 0) return array($t, $html);

    $t += $_p[0]; //* acumulamos en $t o total de gastos de envío ($_p[0])

    $titulo = "Gastos de envío";

    if ($this->pai->chsabado()) $titulo .= " ( sábado )";

    $html .= $this->html_titulo( $titulo );

    foreach($_p[1] as $id_almacen => $_pi) {
      if ($id_almacen == 1) if ($_pi['i'] <= 0) if (count($_p[1]) > 1) continue;

      $html .= $this->html_dato( $_pi['t'], $_pi['i'], 2, $_pi['m'] );
    }


    return array($t, $html);
  }

  private function html_cargofpago($t, $html) {
    $_c = $this->pai->ucarro->cargofpago();


    if ($_c == null) return array($t, $html);


    $_v = $_c['v'];

    if ($_c['tipo'] == "%") {
      $v = (($t) * ($_v['p'] / 100));

          if ($v > $_v['M']) $v = $_v['M'];
      elseif ($v < $_v['m']) $v = $_v['m'];
    }
    else
      $v = $_v; //* $_v é un escalar.


    $html .= $this->html_titulo( "Gastos contra Reembolso" );

    $html .= $this->html_dato($_c['txt'], $v, 2, "€");


    return array($t + $v, $html);
  }

  private function html_titulo($t, $css = null) {
    if ($css != null) $css = " class = '{$css}'";

    return "<div{$css} style='margin: 0.7em 0 0.3em 0;font-weight: bold;'>{$t}</div>";
  }

  private function html_dato($tt, $dt, $decimales = 2, $simbolo = "&nbsp;", $css = null) {
    if ($css != null) $css = " class = '{$css}'";

    $dt = number_format($dt, $decimales, ",", ".");

    return "<div{$css}>
              <div class='cpagar2_2c_66' style='padding-bottom: 0.3em;'>{$tt}</div>
              <div class='cpagar2_2c_33' style='padding-bottom: 0.3em;text-align: right;'>{$dt} {$simbolo}</div>
            </div>";
  }
}

//***************************************

final class CCarro2_regalos extends Componente {
  const ptw_0 = "ptw/cpagar3/ccarro3_regalos.html";

  private $id_site = null;

  private $_regalos = null; //* artículos para regalo.

  private $_icarro  = null; //* índice de artículos seleccionados no carro.

  public function __construct($id_site) {
    parent::__construct("cregalos", Refs::url(self::ptw_0));

    $this->id_site = $id_site;

    $this->pon_obxeto(new Hidden("h"));

    $this->pon_obxeto(new Param("p"));
  }

  public function operacion(EstadoHTTP $e) {
    if ($this->obxeto("h")->control_evento()) return $this->operacion_h($e);

    return null;
  }

  public function devolver_regalo($i_carro) {
    unset($this->_icarro[$i_carro]);
  }

  public function preparar_saida(Ajax $a = null) {
    $this->actualiza_promos();

    parent::preparar_saida($a);
  }

  private function operacion_h(EstadoHTTP $e) {
    list($id_promo, $id_articulo) = explode(";", $this->obxeto("h")->valor());

//~ echo "$id_promo, $id_articulo<br>";
//~ foreach ($this->_regalos as $k=>$v) {
  //~ echo "Promo Regalo: $k<br>";

  //~ foreach ($this->_regalos[$k] as $k1=>$v1) {
    //~ echo "Artículo Regalo: $k1<br><pre>" . print_r($v1->a_resumo(), true) . "</pre>";
  //~ }
//~ }

    if (($a = $this->_regalos[$id_promo][$id_articulo]) == null) {
      $e->ajax()->pon_ok();

      return $e;
    }


    $i = $this->pai->ucarro->post_regalo($a);

    $this->_icarro[$i] = array($id_promo, $id_articulo);


    $this->pai->preparar_saida( $e->ajax() );


    return $e;
  }

  private function actualiza_promos() {
    $this->obxeto("h")->post(null);
    $this->obxeto("p")->post(null);


    $ucarro = $this->pai->ucarro;

    if ($ucarro->xpromos() == null) return;

    $total = $ucarro->total(); //* total de contraste, para aplicar dtos. com compra mín. (€))

    $_p = $ucarro->xpromos()->_promo(true); //* devolve promos tipo regalo, sen código ou con código validado.


    $html = $this->html_promos($_p, $total);

    if ($html != null) {
      $html = "<div class='lista_carro_aux_1' style='width: 98%;'>
                 <div class='cpagar3_titulo'>Con tu compra te regalamos ...</div>
                 <div class='lista_carro_aux'>{$html}</div>
              </div>";
    }

    $this->obxeto("p")->post($html);
  }

  private function html_promos($_p, $total) {
    $html = "";

//~ $html = "TOTAL CARRITO: {$total}<br />----------------------------------------<br />";

    if (!is_array($_p)) return "";

    foreach ($_p as $id_promo => $p) $html .= $this->html_promo(new FS_cbd(), $p, $total);


    return $html;
  }

  private function html_promo(FS_cbd $cbd, $p, $total) {
    if (!$p->verificar($total, -1)) return "";

    $id_promo = $p->atr("id_promo")->valor;

    if ( !isset($this->_regalos[$id_promo]) ) {
      $this->_regalos[$id_promo] = $p->a_articulos($cbd, true, true);
    }

    if (count($this->_regalos[$id_promo]) == 0) return "";

    $html = "";

    $num_regalos = $p->atr("dto")->valor;

    foreach ($this->_regalos[$id_promo] as $id_articulo => $a) {
      $ct_disponible = $p->atr("dto")->valor - $this->calcula_dispuesto($id_promo);

      $html .= self::html_regalo($cbd, $a, $id_promo, $ct_disponible <= 0);
    }


    if ($html == "") return "";

    $html = "
<div class='cpagar3_cregalo_0004a'>
  <div class='trw_col_2' style='>Promoción: " . $p->atr("nome")->valor . "</div>
  <div class='trw_col_2' style='width: 49%;'>Nº de regalos a escojer: {$ct_disponible} de " . $p->atr("dto")->valor . "</div>
</div>
<div class='cpagar3_cregalo_0004b'>{$html}</div>";


    return $html;
  }

  private function html_regalo(FS_cbd $cbd, Articulo_obd $a, $id_promo, $readonly) {
    if ($a->atr("unidades")->valor < 1) return "";

    $id_articulo = $a->atr("id_articulo")->valor;

    $onclick = ($readonly)?"":"onclick='cpagar2_cregalo_click({$id_promo}, {$id_articulo})'";

    return "
<div class='cpagar3_cregalo_0001' {$onclick} >
  <div class='cpagar3_cregalo_0002' style='background-image: url(" . Ususario_carro::__imx_url($a, $cbd) . ");'>
    <div class='cpagar3_cregalo_0003'><div style='font-size: 202%;'>&starf;</div>" . $a->atr("nome")->valor . "</div>
  </div>
</div>";
  }

  private function calcula_dispuesto($id_promo) {
    if (count($this->_icarro) == 0) return 0;

    $ct_d = 0;
    foreach ($this->_icarro as $i => $_a) if ($_a[0] == $id_promo) $ct_d++;


    return $ct_d;
  }
}
