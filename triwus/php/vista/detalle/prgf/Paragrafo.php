<?php

class Paragrafo extends    Componente
                implements I_fs {

  //~ public $uno = false; //* control para evitar no inicio que se lea na BD 2 veces, prgfnovas e prgfventas (paxinador)

  public $modificado = false;

  public $pnome     = 1;

  public $id_prgf   = null;

  public $pid_style  = null;
  public $pid_fondo  = null;
  public $src_fondo  = null;
  public $pcss      = null;
  public $bgcolor   = null;

  //~ public $crs_obd   = null;

  public $orderby   = 1;

  public $i_entradas = 0;

  public $inovas_etqs            = null;
  public $inovas_imx_proporcion  = 0.66;
  public $inovas_rel_b           = 1;
  public $inovas_rel_s           = 1;
  public $inovas_rel_t           = "";
  
  public $imarcas_vista  = null;

  public $iventas_buscador  = null;
  public $iventas_categoria = null;

  public $columnas_mobil  = "0";

  public $url    = null;
  public $action = null;

  public $elmt3_css = "";
                                  
  protected $num_cols             = 1;
  protected $orde_elmt            = null;
  protected $sup_elmt             = null;
                                  
  protected $width                = null;
  protected $width_f              = true;
                                  
  //* ver clase Prgf_html         
  protected $excluir              = "0";
  protected $mobil                = false;
  protected $alg_html_elmts       = 1;
  protected $usa_bpos             = true;
  protected $colpos_modulo        = true;
                                  
  protected $ancla                = null;


  public function __construct(Paragrafo_obd $p = null, $nome = "prgf", $iniciar_elmts = true) {
    parent::__construct($nome);

    $this->usa_bpos = true;

    //~ $this->crs_obd = $p->crs_obd();

    $this->clase_css("default", "celda_prgf");
    $this->clase_css("over"   , "celda_prgf_over");

    if ($p == null) return;


    $this->iniciar($p);


    if ($iniciar_elmts) $this->iniciar_elmts($p);
  }

  public function config_lectura() {
    $this->readonly = true;

    $this->sup_obxeto("btnr");

    $elmts = $this->obxetos("elmt");

    if (count($elmts) == 0) return null;

    foreach ($elmts as $elmt) $elmt->config_lectura();

    //~ if (($paxinador = $this->obxeto("prgf_paxinador")) != null) $paxinador->config_lectura();
  }

  public function config_edicion() {
    $this->readonly = false;

    $this->pon_btnr();

    $this->obxeto("btnr")->config_edicion();

    $this->obxeto("btnr")->obxeto("bEliminar")->visible = $this->ancla == null;

    $elmts = $this->obxetos("elmt");

    if (count($elmts) == 0) return null;

    foreach ($elmts as $elmt) $elmt->config_edicion();

    //~ if (($paxinador = $this->obxeto("prgf_paxinador")) != null) $paxinador->config_edicion();
  }

  public function config_usuario(IEfspax_xestor $e = null) {
    $this->action  = $e->action;

    $elmts = $this->obxetos("elmt");

    $c = $e->config();

    $this->mobil = $c->atr("tipo")->valor == "mobil";
    $this->width = $c->atr("w"   )->valor;

    $this->url = Paxina_obd::inicia(new FS_cbd(), $e->id_paxina())->action(true) . "#" . $this->__ancla();

    if (count($elmts) == 0) return null;

    foreach ($elmts as $elmt) $elmt->config_usuario($e);

    //~ if (($paxinador = $this->obxeto("prgf_paxinador")) != null) $paxinador->config_usuario($e);
  }

  protected function pon_btnr() {
    $this->pon_obxeto(new Btnr_prgf());
  }

  public function novo() {
    return $this->id_prgf == null;
  }

  public function num_cols($num_cols = null, $real = false) {
    if ($num_cols != null) $this->num_cols = $num_cols;

    //~ if (($this->mobil) && ($this->columnas_mobil > 0)) return 1;

    if ($real) return $this->num_cols;

    if ($this->num_cols < 5) return $this->num_cols;

    return 2;
  }

  public function reset_elementos() {
    $this->orde_elmt = null;
    $this->sup_elmt  = null;

    $this->sup_obxeto("elmt");
  }

  public function pon_elemento(AElemento $e, $subnome, $posicion = null) {
    if ($subnome == null) $subnome = count($this->obxetos($e->nome)) + 1;

    $this->pon_obxeto($e, $subnome);
    $this->pon_obxeto(new Bpos_elmt(), $subnome);

    //* insertamos en orde_elmt;
    if ($posicion == null) $posicion = $subnome;

    $num_cols = $this->num_cols();

    $colpos = (($posicion - 1) % $num_cols);

    if (is_array($this->orde_elmt))
      foreach($this->orde_elmt as $k=>$orde) {
        if ($colpos != (($orde - 1) % $num_cols)) continue;

        if ($orde >= $posicion) $this->orde_elmt[$k] += $num_cols;
      }

    $this->orde_elmt[$e->nome()] = $posicion;

    asort($this->orde_elmt);
  }

  public function elemento($id_elmt) {
    return $this->obxeto("elmt", $id_elmt);
  }

  public function elemento_count($eliminados = true) {
    $t = count($this->orde_elmt);

    if ($eliminados) return $t;

    return $t - count($this->sup_elmt);
  }

  public function elemento_pos($posicion) {
    $elmts = $this->obxetos("elmt");

    if (count($elmts) == 0) return null;

    foreach($elmts as $elmt)
      if ($this->pos_elmt($elmt) == $posicion) return $elmt;

    return null;
  }

  public function elemento_suprimido(AElemento $e) {
    return $this->elemento_suprimido_k( $e->nome() );
  }

  public function elemento_suprimido_k($k) {
    return isset($this->sup_elmt[$k]);
  }

  public function pos_elmt(AElemento $e) {
    return $this->orde_elmt[$e->nome()];
  }

  public function sup_elmt(AElemento $e) {
    if (isset($this->sup_elmt[$e->nome()])) {
      unset($this->sup_elmt[$e->nome()]);

      return;
    }

    $this->sup_elmt[$e->nome()] = $e;
  }

  public function operacion(EstadoHTTP $e) {
    if ($this->obxeto("illa")->control_evento()) {
      $this->preparar_saida( $e->ajax() );
      
      return $e;
    }
    
    
    if (($btnr = $this->obxeto("btnr")) != null) if (($e_aux = $btnr->operacion($e)) != null) return $e_aux;

    if (($e_aux = $this->operacion_prgfbaleiro($e)) != null) return $e_aux;

    $bps = $this->obxetos("bPosicionar");

//~ echo $this->nome_completo() . "::" . count($bps) . "<br>";

    if (count($bps) != 0) 
      foreach ($bps as $bp)
        if ($bp->control_evento()) return $this->operacion_bPosicionar($e, $bp);

    $elmts = $this->obxetos("elmt");
    
    if (count($elmts) != 0)
      foreach ($elmts as $elmt) {
        //~ echo $elmt->nome_completo() . "<br>";
        if (($e_aux = $elmt->operacion($e)) != null) return $e_aux;
      }


    return null;
  }

  public function preparar_saida(Ajax $a = null) {
    if (!$this->visible) {
      $this->obxeto("illa")->post(null);

      if ($a == null) return;

      $a->pon($this->obxeto("illa"));

      return;
    }

    $nc      = "<input type=hidden id='" . $this->nome_completo() . "__nc' value='{$this->num_cols}' />";
    $excluir = "<input type=hidden id='" . $this->nome_completo() . "__excluir' value='{$this->excluir}' />";
    $ancla   = "<a name='" . self::__ancla($this) . "'></a>";

    $css      = $this->clase_css("default");
    $css_over = $this->clase_css("over");

    if (($css_fimx = $this->__css_fimx()) != "") {
      $css      .= $css_fimx;
      $css_over .= $css_fimx;
    }

    $style       = $this->__style();
    $style_fondo = $this->__style_fondo();

    if ($this->readonly) {
      $s = "{$nc}{$excluir}{$ancla}
            <div id='" . $this->nome_completo() . "' class='{$css}' {$style_fondo}>
              <div {$style}>" . $this->html_elmts(null) . "</div>
            </div>";

      $this->obxeto("illa")->post($s);


      if ($a == null) return;


      $a->pon($this->obxeto("illa"));

      $this->obxeto("illa")->post(null);

      return;
    }

    if ($this->pnome == null) $this->pnome = "Párrafo";

    $btnr_html = (($btnr = $this->obxeto("btnr")) == null) ? "" : $btnr->html();

    $s = "{$nc}{$excluir}{$ancla}
           <div id='" . $this->nome_completo() . "'
                class='{$css}' {$style_fondo}
                onmouseover='prgf_onmouseover(this, \"{$css_over}\")'
                onmouseout='prgf_onmouseout(this, \"{$css}\")'>

              <div " . $this->__style_etq() . ">
                <span id='" . $this->nome_completo() . "_legend' class='etq_prgf' style='z-index: 99; display: none; position:absolute;'>
                   {$this->pnome}&nbsp;{$btnr_html}
                </span>
              </div>

              <div {$style}>" . $this->html_elmts(null) . "</div>
           </div>";


    $this->obxeto("illa")->post($s);


    if ($a == null) return;


    $a->pon($this->obxeto("illa"));

    $this->obxeto("illa")->post(null);


    if (($a_obx = $this->obxetos("elmt")) != null) {
      foreach($a_obx as $k=>$obx) {
        if (isset($this->sup_elmt[$k])) continue;

        if (($cb = $obx->js_callback()) != null) $a->pon_ok(true, $cb);
      }
    }
  }

  public function preparar_saida_crear(Ajax $a, int $p):void {
    $k = $this->nome_completo();
    
    $a->pon_ok(true, "prgf_crear('{$k}', {$p})");
  }

  public function preparar_saida_ocultar(Ajax $a):void {
    $k = $this->nome_completo();

    $a->pon_ok(true, "prgf_ocultar('{$k}')");
  }

  public function operacion_kp(IEfspax_xestor $e, $kp) {
    return null;
  }

  public function operacion_editar(Epaxina_edit $e) {
    $e->obxeto("detalle_btnr_oeste")->pon_editor($e, new Editor_prgf($this, $e->config()));

    return $e;
  }

  public function operacion_bSup(Epaxina_edit $e) {
    $this->modificado = true;

    $this->visible = false;

    $this->pai->sup_prgf($this);
    
    
    $this->preparar_saida_ocultar( $e->ajax() );

    $e->obxeto("detalle_btnr_este")->preparar_saida($e->ajax());


    return $e;
  }

  public function prgf_obd(Paragrafo_obd $p = null) {
    if ($p == null) $p = new Paragrafo_obd();

    $p->modificado = $this->modificado;

    $p->atr("id_paragrafo"           )->valor = $this->id_prgf;
    $p->atr("nome"                   )->valor = $this->pnome;
    $p->atr("columnas"               )->valor = $this->num_cols;
    $p->atr("columnas_mobil"         )->valor = $this->columnas_mobil;
    $p->atr("id_style"               )->valor = $this->pid_style;
    $p->atr("css"                    )->valor = $this->pcss;
    $p->atr("orde"                   )->valor = $this->orderby;
    $p->atr("i_entradas"             )->valor = $this->i_entradas;
    $p->atr("inovas_imx_proporcion"  )->valor = $this->inovas_imx_proporcion;
    $p->atr("inovas_rel_b"           )->valor = $this->inovas_rel_b;
    $p->atr("inovas_rel_s"           )->valor = $this->inovas_rel_s;
    $p->atr("inovas_rel_t"           )->valor = $this->inovas_rel_t;
    $p->atr("inovas_etqs"            )->valor = $this->inovas_etqs;
    $p->atr("ancla"                  )->valor = $this->ancla;
    $p->atr("imarcas_vista"          )->valor = $this->imarcas_vista;
    $p->atr("iventas_buscador"       )->valor = $this->iventas_buscador;
    $p->atr("iventas_categoria"      )->valor = $this->iventas_categoria;

    //~ $p->crs_obd( $this->crs_obd );

    return $p;
  }

  public function a_elmt_obd() {
    if (!is_array($this->orde_elmt)) return null;
    
    if (count($this->orde_elmt) == 0) return null;

    $num_cols = $this->num_cols();

    for ($c = 0; $c < $num_cols; $c++) $i[$c] = $c + 1;

    $a = null;
    foreach($this->orde_elmt as $k=>$orde) {
      $elmt_obd = $this->obxeto($k)->elmt_obd();

      if (isset($this->sup_elmt[$k])) {
        if ($elmt_obd->atr("id_elmt")->valor == null) continue;

        $elmt_obd->atr("id_elmt")->valor *= -1;
      }

      $resto = ($orde - 1) % $num_cols;

      $a[$i[$resto]] = $elmt_obd;

      $i[$resto] += $num_cols;
    }

    return $a;
  }

  public function configurar() {
    //* elimina os 'bPosicionar' _0_
    $bps = $this->obxetos("bPosicionar");

    if (count($bps) > 0)
      foreach ($bps as $bp) {
        $a_nome = explode(Escritor_html::csubnome, $bp->nome);

        if ($a_nome[1] == 0) $this->sup_obxeto($bp->nome);
      }

    //* reinicia
    for($c = 0; $c < $this->num_cols(); $c++) {
      $this->pon_obxeto(new Bpos_elmt(), "0" . Escritor_html::csubnome . $c);
    }
  }

  public function anclar(Control $c, $msx = "", $ajax = false) {
    if ($ajax) {
      $c->pon_eventos("onclick", "editor_vm_esperar_abrir(this, 'vm_esperar', '{$msx}', null)");
      //~ $c->envia_ajax("onclick", $msx);

      return;
    }

    if ($c->ancla != null) return;

    $c->envia_SUBMIT("onclick", $msx, Btnr::__ancla($this));
  }

  final public function __ancla() {
    if ($this->ancla != null) return md5($this->ancla);

    return Btnr::__ancla($this);
  }

  public function iniciar_elmts(ILista_xeobd $p, $where = null) {
    $a_pe = $p->a_elmt("id_elmt", new FS_cbd(), $where, null, $this->iniciar_elmts_orderby());

    if ($a_pe == null) return;

    $i = 1;
    foreach ($a_pe as $id_elmt=>$pe_obd) {
      if (($elmt_obd = $pe_obd->elmt()) == null) continue;

      if ($this->orderby == 1)
        //* posicionamento manual
        $this->pon_elemento( AElemento::factory($elmt_obd), $id_elmt, $pe_obd->posicion() );
      else 
        //* posicionamento automático
        $this->pon_elemento( AElemento::factory($elmt_obd), $id_elmt, $i++ );
    }
  }

  protected function iniciar(Paragrafo_obd $p) {

//~ echo "<pre>" . print_r($p->a_resumo(), true) . "</pre>";

    $this->id_prgf               = $p->atr("id_paragrafo")->valor;
    $this->num_cols              = $p->atr("columnas")->valor;
    $this->columnas_mobil        = $p->atr("columnas_mobil")->valor;
    $this->pnome                 = $p->atr("nome")->valor;
    $this->pid_style             = $p->atr("id_style")->valor;
    $this->pcss                  = $p->atr("css")->valor;
    $this->orderby               = $p->atr("orde")->valor;
    $this->i_entradas            = $p->atr("i_entradas")->valor;
    
    $this->inovas_etqs           = $p->atr("inovas_etqs")->valor;
    $this->inovas_imx_proporcion = $p->atr("inovas_imx_proporcion")->valor;
    $this->inovas_rel_b          = $p->atr("inovas_rel_b")->valor;
    $this->inovas_rel_t          = $p->atr("inovas_rel_t")->valor;
    $this->inovas_rel_s          = $p->atr("inovas_rel_s")->valor;
                                
    $this->imarcas_vista         = $p->atr("imarcas_vista")->valor;
    $this->iventas_buscador      = $p->atr("iventas_buscador")->valor;
    $this->iventas_categoria     = $p->atr("iventas_categoria")->valor;
                                
    $this->ancla                 = $p->atr("ancla")->valor;


    $this->configurar();
  }

  protected function html_elmts($alg = null) {
    if ($alg == null) $alg = $this->alg_html_elmts;

    $html = "";

        if ($alg ==  1) $html = Prgf_html::__elmts_1 ($this);
    elseif ($alg ==  2) $html = Prgf_html::__elmts_2 ($this);
    elseif ($alg ==  3) $html = Prgf_html::__elmts_3 ($this);
    elseif ($alg ==  4) $html = Prgf_html::__elmts_4 ($this);
    elseif ($alg ==  5) $html = Prgf_html::__elmts_5 ($this);
    elseif ($alg ==  6) $html = Prgf_html::__elmts_6 ($this);
    elseif ($alg ==  7) $html = Prgf_html::__elmts_7 ($this);
    elseif ($alg ==  8) $html = Prgf_html::__elmts_8 ($this);
    elseif ($alg ==  9) $html = Prgf_html::__elmts_9 ($this);
    elseif ($alg == 10) $html = Prgf_html::__elmts_10($this);
    elseif ($alg == 99) return "";
    else
      die("Paragrafo.html_elmts(), this.alg_html_elmts NON válido");


    if ($this->width == null) return $html;


    if (!$this->width_f)  return "<div class='celda_prgf_2'>{$html}</div>";


    $s = ($this->width < 700)?"100%":"{$this->width}px";


    return "<div class='celda_prgf_2' style='max-width:{$s};'>{$html}</div>";
  }

  protected function operacion_bPosicionar(Epaxina_edit $e, Button $bpos) {
    $this->modificado = true;

//~ echo "1111111111111111111111111111111<br>";

    //* calcula $posicion.
    if (($sn = $e->evento()->subnome(0)) == 0)
      $posicion = $e->evento()->subnome(1) + 1;
    else
      $posicion = $this->pos_elmt($this->obxeto("elmt", $sn)) + $this->num_cols();


    //* inicia $obd.
    $editar = true;

    $obd = $e->obxeto("detalle_btnr_este")->factory($e);

//~ print_r($obd->a_resumo());

    if ($obd == null) $obd = $e->obxeto("d_btnr_n")->obxeto("btnr_insertar")->factory($e);

    if ($obd == null)  {
      //* operacion mover ou copiar.

      $evento = $e->btnr_evento();

      $elmt_aux = $this->pai->obxeto($evento->nome(2))->obxeto($evento->nome(3));

      $btnr_eaux = $elmt_aux->obxeto("btnr");

      $obd = $elmt_aux->elmt_obd();

      $obd->atr("id_elmt" )->valor = null;
      $obd->atr("id_style")->valor = null;

      if ($evento->control_ohttp($btnr_eaux->obxeto("bMove"))) $elmt_aux->pai->sup_elmt($elmt_aux);

      //* garda a operacion $bpos no control de cambios.
      $e->cc_pon($bpos);

      //* nesta ocasion non abrimos o editor de $elmt_aux.
      $editar = false;
    }

    if ($obd == null) {
      $this->pai->preparar_saida($e->ajax());

      return $e;
    }

    //* crea o novo elemento '$elmt', a partir de '$obd'.
    $elmt = AElemento::factory($obd);

    $elmt->config_edicion();

    $elmt->config_usuario($e);

    $this->pon_elemento($elmt, null, $posicion);

    $e->cc_pon($bpos, $elmt->obxeto("btnr")->obxeto("bEliminar"));


    //* devolvemos o estado resultante da operacion.

    $this->pai->preparar_saida($e->ajax());

    $e->obxeto("detalle_btnr_este")->preparar_saida($e->ajax());

    if ((strtoupper($obd->atr("tipo")->valor)) == Paxinador_obd::tipo_bd); //* caso particular
    elseif ($editar) return $elmt->operacion_editar($e);


    return $e;
  }

  protected function iniciar_elmts_orderby() {
    if ($this->orderby == 9) return "posicion desc";

    return "posicion asc";
  }

  final protected function __css_fimx():string {
    $css = "";
    
    if ($this->pid_fondo == null) return $css;

    return " celda_prgf_fimx";
  }
  
  final protected function __style() {
    return " style = '{$this->pcss}'";
  }

  final protected function __style_fondo() {
    $css = strval( $this->pcss_fondo );
    
    if (($src_fondo = $this->tmp_fondo) == null) $src_fondo = $this->src_fondo;

    if ($src_fondo != null) {
      $css .= "background-image:url({$src_fondo});";
    }

    return " style = '{$css}'";
  }
  
  final protected function __style_bgcolor() {
    $_pcss = Style_obd::__props($this->pcss);
    
    if (!is_array($_pcss)) return "";
    
    if (!isset($_pcss["background-color"])) return "";

    
    return " style = 'background-color:{$_pcss["background-color"]}'";
  }

  final protected function __style_etq() {
    return "";
  }

  private function operacion_prgfbaleiro(Efs $e) {
    if (($a_prgf = $this->obxetos("baleiro")) == null) return null;

    foreach ($a_prgf as $k=>$baleiro) {
      if (($e_aux = $baleiro->operacion($e)) != null)  return $e_aux;
    }

    return null;
  }
}

//*************************

final class Prgf_html extends Paragrafo {

  //~ const ptw_1 = "ptw/paragrafo/prgf_elmts_1.html";
  const ptw_4 = "ptw/paragrafo/prgf_elmts_4.html";

  protected static function __elmts_1(Paragrafo $prgf) {
    // escribe elmts de prgf na sua columna.

    $numcols = self::__elmts_1_numcols($prgf);
    $class   = self::__elmts_1_class  ($prgf);

    //~ if (($prgf->mobil) && ($prgf->columnas_mobil == 2)) return self::__elmts_1_axustarMobil_cols($prgf);
    
    $html = [];
    if (($a_obx = $prgf->obxetos("elmt")) != null) {
      foreach($prgf->orde_elmt as $k=>$orde) {
        if (isset($prgf->sup_elmt[$k])) continue;
        
        $colpos = $orde - 1;
        if ($prgf->colpos_modulo) $colpos = $colpos % $numcols;


        list($nome, $subnome) = explode(Escritor_html::csubnome, $k);

//~ echo "k:{$k}||" . $a_obx[$k]->nome_completo() . ".colpos:$colpos::numcols:$numcols<br>";

        if (!isset($html[$colpos])) $html[$colpos] = "";

        $html[$colpos] .= "<input type=hidden id='" . $a_obx[$k]->nome_completo() . "_nc' value='{$colpos}' />" . $a_obx[$k]->html();
      }
    }

    $html_cols = "";

    for ($c = 0; $c < $numcols; $c++) {
      if (!$prgf->readonly && $prgf->usa_bpos) {
        if ( !isset($html[$c]) ) $html[$c] = self::html_prgfbaleiro($prgf, $c);
      }
      elseif ( !isset($html[$c]) ) $html[$c] ="";

      $html_cols .= "<div>{$html[$c]}</div>";
    }

    return "<div class='{$class}'>{$html_cols}</div>";
  }

  protected static function __elmts_2(Paragrafo $prgf) {
    $html = LectorPlantilla::plantilla($prgf->ptw);

    $elmts = $prgf->obxetos("elmt");

    if (count($elmts) == 0) return $html;

    foreach($elmts as $elmt) $html = str_replace("[" . $prgf->pos_elmt($elmt) . "]", $elmt->html(), $html);

    return $html;
  }

  protected static function __elmts_3(Paragrafo $prgf) {
    if (count($prgf->obxetos("elmt")) == 0) return null;
    
    if (count($prgf->orde_elmt) == 0) return null;

    $html = "";
    foreach($prgf->orde_elmt as $k=>$orde) {
      if (isset($prgf->sup_elmt[$k])) continue;

      if (!$prgf->obxeto($k)->visible) continue;

      list($nome, $subnome) = explode(Escritor_html::csubnome, $k);


      $html .= "<div class='{$prgf->elmt3_css}'>" . $prgf->obxeto($k)->html() . "</div>";
    }

    return $html;
  }

  protected static function __elmts_4(Prgf_inovas $prgf) {
    $class = self::__elmts_4_class($prgf);

    if (($_obx = $prgf->obxetos("elmt")) != null) {
      $a = array_reverse($prgf->orde_elmt, true);

      $c = 0;
      foreach($a as $k=>$orde) {
        if (isset($prgf->sup_elmt[$k])) continue;


        list($nome, $subnome) = explode(Escritor_html::csubnome, $k);

        $html_td .= $_obx[$k]->html();
      }

      if ($html_td != "") {
        $html_tr .= "<div class='{$class}'>{$html_td}</div>";
      }
    }


    return self::ptw_4($prgf->ptw, $html_tr);
  }

  protected static function __elmts_5(Prgf_reservado $prgf) {
    return self::ptw_1( $prgf->html_reservado() );
  }

  protected static function __elmts_6(Prgf_cxcli $prgf) {
    return $prgf->html_cxcli();
  }

  protected static function __elmts_7(Prgf_imarcas $prgf) {
    return $prgf->html_marcas();
  }

  protected static function __elmts_8(Prgf_iventas2 $prgf) {
    return $prgf->html_ventas2();
  }

  protected static function __elmts_9(Prgf_inovas2 $prgf) {
    return $prgf->html_novas2();
  }

  protected static function __elmts_10(Prgf_iventas2b $prgf) {
    return $prgf->html_ventas2b();
  }

  public static function __elmts_1_numcols(Paragrafo $prgf) {
    $numcols = $prgf->num_cols(null, true);

    if ($numcols > 4) return 2;

    return $numcols;
  }

  public static function __elmts_1_class(Paragrafo $prgf) {
    $numcols = $prgf->num_cols(null, true);

    if ($numcols < 1) {
      die("Prgf_html.__elmts_1_class(), ({$numcols} < 1)");
      
      return;
    }
    
    if ($numcols <  7) return "trw_grid_0_{$numcols}";


    die("Prgf_html.__elmts_1_class(), (numcols > 6)");
  }

  public static function __elmts_4_class(Paragrafo $prgf) {
    return self::__elmts_1_class($prgf);
  }

  private static function ptw_1($html) {
    return $html;
  }

  private static function ptw_4($ptw, $html) {
    if (!is_file($ptw)) $ptw = Refs::url(self::ptw_4);

    return str_replace("[elmts]", $html, LectorPlantilla::plantilla($ptw));
  }

  private static function html_prgfbaleiro(Paragrafo $prgf, $c) {
    $cnome    = Escritor_html::cnome;
    $csubnome = Escritor_html::csubnome;

    $id = $prgf->nome_completo() . "{$cnome}elmt{$csubnome}0{$csubnome}{$c}";

    return "<div id='{$id}' class='celda_elmt_marcado'>
              <center><br /><h3 style='color: #dddddd;'>Párrafo vacío</h3><br /></center>
            </div>";
  }

}

