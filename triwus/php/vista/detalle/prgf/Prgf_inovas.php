<?php


final class Prgf_inovas extends Paragrafo {
  private $erro;

  public function __construct(Paxina_novas_obd $p) {
    parent::__construct($p->prgf_indice(), "prgf", true, true);

    $this->excluir = 1;

    $this->alg_html_elmts = 4;


    $this->pon_obxeto(new Prgf_inovas_etqs());

    $this->pax_obd = $p;
  }

  public function inicia_ptw(Config_obd $c) {
    $this->ptw = $c->ptw_prgf("prgf_inovas");

    $elmts = $this->obxetos("elmt");

    if (count($elmts) == 0) return;

    foreach($elmts as $k=>$elmt) {
      list($nome, $subnome) = explode(Escritor_html::csubnome, $k);

      //~ if (($bposicionar = $this->obxeto("bPosicionar", $subnome)) != null) $this->anclar($bposicionar, null, false); //* Submit
      if (($bposicionar = $this->obxeto("bPosicionar", $subnome)) != null) $this->anclar($bposicionar, null, true);      //* AJAX

      $elmt->inicia_ptw($c, $this->inovas_imx_proporcion);

      $elmt->crs_obd($this->crs_obd);
    }
  }

  protected function pon_btnr() {
    $this->pon_obxeto(new Btnr_prgf(false));
  }


  public function crs_obd(CRS_obd $crs_obd) {
    $this->crs_obd = $crs_obd;

    $elmts = $this->obxetos("elmt");

    if (count($elmts) == 0) return;

    foreach($elmts as $k=>$elmt) $elmt->crs_obd($crs_obd);
  }

  public function operacion_editar(Epaxina_edit $e) {
    $e->obxeto("detalle_btnr_oeste")->pon_editor($e, new Editor_prgf_novas($this));

    return $e;
  }

  public function pos_elmt(AElemento $e) {
    if ($e->nome == "elmt_imx") return $this->orde_elmt[$e->pai->nome()];

    return $this->orde_elmt[$e->nome()];
  }

  public function a_elmt_obd() {
    if (count($this->orde_elmt) == 0) return null;

    $a = null;
    foreach($this->orde_elmt as $k=>$orde) {
      $elmt_obd = $this->obxeto($k)->elmt_obd();

      if (isset($this->sup_elmt[$k])) {
        if ($elmt_obd->atr("id_elmt")->valor == null) continue;

        $elmt_obd->atr("id_elmt")->valor *= -1;
      }

      $a[$orde] = $elmt_obd;
    }

    return $a;
  }

  public function operacion(EstadoHTTP $e) {
    if (($e_aux = $this->obxeto("prgf_inovas_etqs")->operacion($e)) != null) return $e_aux;
    //~ if (($e_aux = $this->obxeto("prgf_paxinador")->operacion($e)) != null) return $e_aux;

    return parent::operacion($e);
  }

  public function operacion_etq(IEfspax_xestor $e, Control $etq = null) {
    $this->sup_obxeto("elmt");

    return $this->operacion_reset($e, $etq);
  }

  public function operacion_reset(IEfspax_xestor $e, Control $etq = null) {
    $this->orde_elmt = null;
    $this->sup_elmt  = null;


    $cetq = $this->obxeto("prgf_inovas_etqs");

    $cetq->pon_etq($etq);

    $this->iniciar_elmts($this->prgf_obd(), $cetq->sql_where());

    $this->inicia_ptw($e->config());

    $this->config_usuario($e);

    $e->configurar();

    $this->preparar_saida($e->ajax());

    $e->ajax()->pon_ok(true, "tilia_scroll_top()");

    return $e;
  }

  public function operacion_bAdd(Epaxina_edit $e, Elmt_novas $elmt = null, $posicion = null) {
    if ($elmt == null) $elmt = Elmt_novas::inicia_nome();

    $elmt->inicia_ptw($e->config(), $this->inovas_imx_proporcion);

    $elmt->config_usuario($e);

    $elmt->config_edicion();

    if ($posicion == null) {
      $posicion = ( is_array($this->orde_elmt) )?end($this->orde_elmt) + 1:1;
    }

    $this->pon_elemento($elmt, $posicion);

    return $e;
  }

  public function preparar_saida(Ajax $a = null) {
    parent::preparar_saida();

    $html = $this->obxeto("illa")->valor();

    $html = str_replace("[nome_completo]"   , $this->nome_completo()                   , $html);
    $html = str_replace("[prgf_inovas_etqs]", $this->obxeto("prgf_inovas_etqs")->html(), $html);
    //~ $html = str_replace("[prgf_paxinador]"  , $this->obxeto("prgf_paxinador"  )->html(), $html);

    $this->obxeto("illa")->post($html);


    if ($a == null) return;


    $a->pon($this->obxeto("illa"));

    $this->obxeto("illa")->post(null);
  }

  protected function operacion_bPosicionar(Epaxina_edit $e, Button $bpos) {
    $this->modificado = true;

    $btnr_evento = $e->btnr_evento();

    $elmt_0 = $this->pai->obxeto($btnr_evento->nome(2))->obxeto($btnr_evento->nome(3));
    $elmt_1 = $this->obxeto("elmt", $e->evento()->subnome(0));

//~ echo "<pre>" . print_r($this->orde_elmt, true) . "</pre>";


    $p_0 = $this->orde_elmt[$elmt_0->nome];
    $p_1 = $this->orde_elmt[$elmt_1->nome];

//~ echo "posicion('{$elmt_0->nome}')::({$p_0}, {$p_1})<br />";

    if ($p_0 == ($p_1 - 1)) {
      $this->orde_elmt[$elmt_0->nome] = $p_1;
      $this->orde_elmt[$elmt_1->nome] = $p_0;
    }
    else {
      $this->orde_elmt[$elmt_0->nome] = $p_1;

      foreach ($this->orde_elmt as $k=>$p) if (($k != $elmt_0->nome) && ($p >= $p_1)) $this->orde_elmt[$k]++;
    }

//~ echo "<pre>" . print_r($this->orde_elmt, true) . "</pre>";

    asort($this->orde_elmt);

//~ echo "<pre>" . print_r($this->orde_elmt, true) . "</pre>";

    $pos_aux = 1;
    foreach($this->orde_elmt as $k=>$pos) {
      $this->orde_elmt[$k] = $pos_aux;

      $pos_aux++;
    }

//~ echo "<pre>" . print_r($this->orde_elmt, true) . "</pre>";


    $e->cc_pon($bpos);


    $this->preparar_saida($e->ajax());

    $e->obxeto("detalle_btnr_este")->preparar_saida($e->ajax());


    return $e;
  }

  protected function recibir_arquivo(Epaxina_edit $e, $a_file) {
    if (($erro = Erro::valida_upload($a_file)) != null) {
      $this->erro[basename($a_file['name'])] = $erro;

      return;
    }

    $id_file = uniqid($e->url_arquivos());

    move_uploaded_file($a_file['tmp_name'], $id_file);

    return $id_file;
  }

}

//-------------------------------------

final class Prgf_inovas_etqs extends Componente {

  private $a_sql_where = null;

  public function __construct() {
    parent::__construct("prgf_inovas_etqs");

    $this->obxeto("illa")->clase_css("default", "novas_filtro_marco");
  }

  public function pon_etq(Control $etq = null) {
    if ($etq == null) return;

    $i = $etq->pai->indice();
    $v = $etq->valor();


    $etq_2 = self::__etq($v);

    $this->pon_obxeto($etq_2, $v);

    $this->a_sql_where[$etq_2->nome] = "(etiquetas{$i} like '%{$v}%')";
  }

  public function sql_where() {
    if (count($this->a_sql_where) == 0) return null;

    $where = null;
    foreach($this->a_sql_where as $sql_i) {
      if ($where != null) $where .= " and ";

      $where .= $sql_i;
    }


    return $where;
  }

  public function operacion(EstadoHTTP $e) {
    $a_etq = $this->obxetos("etq");

    if (count($a_etq) == 0) return null;

    foreach($a_etq as $etq)
      if ($etq->control_evento()) {
        unset( $this->a_sql_where[ $etq->nome ] );

        $this->sup_obxeto($etq->nome);

        return $this->pai->operacion_etq($e);
      }

    return null;
  }

  public function preparar_saida(Ajax $a = null) {
    $a_etq = $this->obxetos("etq");

    if (count($a_etq) == 0) {
      $this->obxeto("illa")->post(null);

      if ($a != null) $a->pon($this->obxeto("illa"));

      return;
    }

    $html = null;
    foreach($a_etq as $etq) $html .= $etq->html();

    $this->obxeto("illa")->post($html);


    if ($a == null) return;


    $a->pon($this->obxeto("illa"));

    $this->obxeto("illa")->post(null);
  }

  private static function __etq($etq) {
    $s = new Div("etq", $etq);

    $s->title = "Eliminar filtro";

    $s->clase_css("default", "novas_filtro_etq");

    //~ $s->pon_eventos("onmouseover", "this.style.textDecoration='line-through'");
    //~ $s->pon_eventos("onmouseout" , "this.style.textDecoration='none'");

    //~ $s->envia_submit("onclick");
    //~ $s->envia_ajax("onclick");
    $s->pon_eventos("onclick", "prgfinovas_cetq_click(this, null, null)");


    return $s;
  }
}

