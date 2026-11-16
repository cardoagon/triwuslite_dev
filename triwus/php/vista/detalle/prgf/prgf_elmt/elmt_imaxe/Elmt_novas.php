<?php


final class Elmt_novas extends Elmt_imx {
  public $num_cols   = 1;

  private $ptw_0     = "ptw/paragrafo/elmt/elmt_novas_0.html";
  private $ptw_1     = "ptw/paragrafo/elmt/elmt_novas_1.html";

  private $editando  = false; //* estamos editando a nocitica

  private $id_idioma = null;

  public $id_site   = null; //* será usado para facer unha redirección á páxina detalle.
  public $id_paxina = null;

  public $url_limpa    = null;

  public $url_upload   = null;
  public $url_arquivos = null;

  public $crs_obd   = null;

  public $modificado = false;

  public function __construct(Elmt_novas_obd $pe_obd) {
    parent::__construct($pe_obd, null, "celda_elmtnovas_marcado");


    $this->html_btnr = false;



    $this->modificado = $pe_obd->atr("id_elmt")->valor == null;

    $this->id_paxina  = $pe_obd->atr("id_paxina")->valor;


    //~ $this->pon_obxeto(new Param("btnr"));

    $this->pon_obxeto(new Param("data", $pe_obd->data()));

    $this->pon_obxeto(new Hidden("hcrop"));

    $this->pon_obxeto( new CEtiquetas($pe_obd) );
    $this->pon_obxeto( new CEtiquetas($pe_obd, 1) );
    $this->pon_obxeto( new CEtiquetas($pe_obd, 2) );

    $this->pon_obxeto(self::__ldetalle($this->enome, $this->id_paxina));
    $this->pon_obxeto(new Textarea("tnome"      , 1, 22));
    $this->pon_obxeto(new Textarea("tdescricion", 2, 22));


    $this->pon_obxeto( new CRS(null, true) );

    $this->obxeto("crs")->css_marco("CRS_novas_marco");

    $this->obxeto("crs")->css_ico("novas");


    $this->obxeto("imaxe")->style("default" , "cursor: pointer;");
    $this->obxeto("imaxe")->style("readonly", "cursor: initial;");

    $this->obxeto("tnome")->clase_css("default", "novas_titulo");

    $this->obxeto("tdescricion")->clase_css("default" , "novas_entrada");
    $this->obxeto("tdescricion")->clase_css("readonly", "novas_entrada");

    $this->pon_obxeto(self::__lcambiar());

    $this->pon_obxeto(Panel_fs::__baceptar());
    $this->pon_obxeto(Panel_fs::__bcancelar());


    $this->obxeto("bCancelar")->pon_eventos("onclick", "editor_vm_esperar_abrir(this, 'vm_esperar', '', null)");
    $this->obxeto("bAceptar" )->pon_eventos("onclick", "elmt_novas_bAceptar_click(this)");
  }

  public static function inicia_nome() {
    $ea_obd = new Elmt_novas_obd();

    $ea_obd->atr("nome")->valor = "Noticia " . date("d-m-Y H:i:s");


    $ea = new Elmt_novas($ea_obd);


    return $ea;
  }

  public function crs_obd(CRS_obd $crs_obd = null) {
    $this->obxeto("crs")->crs_obd($crs_obd);
  }

  public function config_lectura() {
    AElemento::config_lectura();

    $imx = $this->obxeto("imaxe");
    $ld  = $this->obxeto("ldetalle");

    if ($this->id_paxina == null) {
      $imx->readonly = true;

      $ld->readonly  = true;
      $ld->title     = $this->enotas;
    }
    else {
      $imx->readonly = false;
      $imx->title    = $this->enome;

      $ld->readonly  = false;
      $ld->title     = $this->enome;

      if (!$this->pcontrol) {
        $ld->sup_eventos();

        $href = Paxina_obd::inicia(new FS_cbd(), $this->id_paxina)->action_2($this->url_limpa);

        $ld->href = $href;

        $imx->pon_eventos("onclick", "document.location='{$href}'");
      }
    }
  }

  public function config_edicion() {
    AElemento::config_edicion();

    $this->readonly = false;

    $imx = $this->obxeto("imaxe");
    $ld  = $this->obxeto("ldetalle");

    if ($this->id_paxina != null) {
      $ld->title = "Ver detalle";

      $imx->title = "Ver detalle";
      $imx->envia_submit("onclick");
    }
    else {
      $ld->title = "Ampliar detalle";
      $ld->pon_eventos("onclick", "elmt_album_ampliar(this)");

      $imx->title = "Ampliar detalle";
      $imx->pon_eventos("onclick", "elmt_album_ampliar(this)");
    }
  }

  public function config_usuario(IEfspax_xestor $e = null) {
    parent::config_usuario($e);

    $this->id_site   = $e->id_site;
    $this->pcontrol  = $e->edicion();

    $this->url_limpa = $e->url_limpa;

    $this->url_arquivos = $e->url_arquivos();

    $this->pon_obxeto($this->__fimaxe()); //* está vinculado con la línea anterior: "$this->url_arquivos = $e->url_arquivos();"

    $this->pon_btnr(  );


    //~ $this->obxeto("crs")->pon_paxina($this->id_paxina);

    $this->obxeto("crs")->config_usuario($e);
  }

  public function inicia_ptw(Config_obd $c, $imx_proporcion) {
    $this->ptw_0 = $c->ptw_elmt("elmt_novas_0");
    $this->ptw_1 = $c->ptw_elmt("elmt_novas_1");

    if ($this->ptw == null) $this->ptw = $this->ptw_0;

    $this->imx_proporcion = $imx_proporcion;
    $this->detalle_w      = $c->atr("w")->valor;
  }

  protected function pon_btnr($id_idioma = null) {
    $btnr = new Btnr_elmt_novas();

    if ($this->id_elmt == null) $btnr->config_visibles(true, false, false, true);

    $this->pon_obxeto( $btnr );
  }

  public function elmt_obd() {
    $e = $this->obd_base(new Elmt_novas_obd());

    $e->modificado = $this->modificado;

    if (($src = $this->obxeto("imaxe")->src()) != Imaxe_obd::url_nula()) $e->post_url($src);

    //~ $e->atr("link_href" )->valor     = ($this->id_paxina == null)?null:Paxina_obd::inicia(new FS_cbd(), $this->id_paxina)->action_2($this->url_limpa);

    $e->atr("id_paxina")->valor      = $this->id_paxina;

    $e->atr("etiquetas"  )->valor    = $this->obxeto("cetq"  )->etiquetas();
    $e->atr("etiquetas_1")->valor    = $this->obxeto("cetq_1")->etiquetas();
    $e->atr("etiquetas_2")->valor    = $this->obxeto("cetq_2")->etiquetas();


    return $e;
  }

  public function operacion(EstadoHTTP $e) {
    if (($e_aux = $this->obxeto("cetq")->operacion($e)) != null) return $e_aux;

    if (($e_aux = $this->obxeto("cetq_1")->operacion($e)) != null) return $e_aux;

    if (($e_aux = $this->obxeto("cetq_2")->operacion($e)) != null) return $e_aux;

    if ($this->obxeto("imaxe")->control_evento()) {
      if ($this->id_paxina == null) return $this->operacion_ampliar($e);

      return $this->operacion_detalle($e);
    }

    if ($this->obxeto("ldetalle")->control_evento()) {
      if ($this->id_paxina == null) return $this->operacion_ampliar($e);

      return $this->operacion_detalle($e);
    }

    if ($this->obxeto("bCancelar")->control_evento()) return $this->operacion_bCancelar($e);
    if ($this->obxeto("bAceptar")->control_evento() ) return $this->operacion_bAceptar($e);

    if ($this->obxeto("f_imaxe")->control_evento()) {
      $this->obxeto("imaxe")->post( $this->url_upload );

      $this->pon_obxeto( $this->__fimaxe() );


      return $this->operacion_editar($e);
    }


    return parent::operacion($e);
  }

  public function operacion_bdupli(Efs $e) {
    $cbd       = new FS_cbd();

    $pax       = Paxina_obd::inicia($cbd, $this->id_paxina, "novas");
    $oms       = Opcion_ms_obd::inicia($cbd, $pax->atr("id_opcion")->valor);

    $id_idioma = $e->id_idioma();
    $td        = "p";
    $id_coment = $pax->atr("id_comentarios")->valor;

    $cbd->transaccion();

    if (!CPaxdupli::duplicar_p($cbd, $oms, $pax, $id_idioma, $td, $id_coment)) {
      $cbd->rollback();

      return $e;
    }

    $cbd->commit();
    //~ $cbd->rollback();

    return $this->pai->operacion_reset($e);
  }

  public function operacion_editar(Efs_admin $e) {
    $this->editando = true;

    $this->obxeto("imaxe")->sup_eventos();

    $this->obxeto("btnr")->visible = false;


    $this->ptw = $this->ptw_1;

    $this->obxeto("tnome"      )->post($this->enome);
    $this->obxeto("tdescricion")->post($this->enotas);


    //* preparamos saida ajax
    $this->obxeto("hcrop")->post(null);

    $this->obxeto("illa")->post( $this->obxeto("hcrop")->html() . $this->html_propio() ); //* para non ter que modificar todas as plantillas.

    $nc =  $this->nome_completo();
    $ip =  ($this->imx_proporcion > 0)?round(1 / $this->imx_proporcion, 3):"null";


    $e->ajax()->pon($this->obxeto("illa"), true, "elmt_novas_editar('{$nc}', {$ip})");

    $this->obxeto("illa")->post(null); //* liberamos buffer.


    return $e;
  }

  protected function html_propio() {
    $this->obxeto("cetq"  )->readonly = !$this->editando;
    $this->obxeto("cetq_1")->readonly = !$this->editando;
    $this->obxeto("cetq_2")->readonly = !$this->editando;

    //~ $this->obxeto("elmt_imx")->src(-1);

    $html = $this->html00();

    if ($this->editando) return $html;

    $html = str_replace("[enome]" , $this->enome , $html);
    $html = str_replace("[enotas]", $this->enotas, $html);


    return $html;
  }

  public function operacion_ampliar(Epaxina_edit $e) {
    $cbd = new FS_cbd();

    $cbd->transaccion();

    $pnovas = Paxina_obd::inicia($cbd, $e->id_paxina(), "novas");

    if (($p = $pnovas->insert_paxina_detalle($cbd, $this->elmt_obd())) == null) {
      $cbd->rollback();

      return $e;
    }

    $cbd->commit();

    //~ return new Epaxina_edit($e->anterior(),
                            //~ $p->atr("id_opcion")->valor,
                            //~ $p->atr("id_paxina")->valor,
                            //~ $e->id_idioma());

    return new Epaxina_edit($p->atr("id_opcion")->valor,
                            $p->atr("id_paxina")->valor,
                            $e->id_idioma());
  }

  private function operacion_bCancelar(Efs_admin $e) {
    $this->editando = false;

    if ($this->id_elmt == null)
      $this->obxeto("imaxe")->pon_eventos("onclick", "elmt_album_ampliar(this)");
    else
      $this->obxeto("imaxe")->envia_submit("onclick");


    $this->obxeto("btnr")->visible = true;


    $this->ptw = $this->ptw_0;


    $this->preparar_saida($e->ajax());

    return $e;
  }

  private function operacion_bAceptar(Efs_admin $e) {
    //* 1.- recorta imaxe
    if (($url = $this->obxeto("imaxe")->src()) != Imaxe_obd::noimx) {
      list($x, $y, $w, $h) = explode(Escritor_html::csubnome, $this->obxeto("hcrop")->valor());

      $url_2 = Imaxe_obd::__recortar($url, $x, $y, $w, $h, $this->url_arquivos);

      if ($url_2 != null) $this->pon_src($url_2);
    }


    //* 2.- outra info
    $this->enome  = $this->obxeto("tnome")->valor();
    $this->enotas = $this->obxeto("tdescricion")->valor();

    $this->obxeto("ldetalle")->post($this->enome);

    $this->obxeto("cetq"  )->aceptar_etiquetas();
    $this->obxeto("cetq_1")->aceptar_etiquetas();
    $this->obxeto("cetq_2")->aceptar_etiquetas();


    //* 3.- devolve saída.
    $e->cc_pon($this->obxeto("bAceptar"));

    $this->modificado = true;

    $e->obxeto("detalle_btnr_este")->preparar_saida($e->ajax());

    return $this->operacion_bCancelar($e);
  }

  public function operacion_detalle(Efs $e) {
    if ($this->id_paxina == null) return $e;

    return Efspax_xestor::operacion_redirect($e, $this->id_paxina, 1);
  }

  private static function __ldetalle($etq) {
    $l = Panel_fs::__link("ldetalle", $etq, "novas_titulo", "novas_titulo_a");

    $l->clase_css("readonly", "novas_titulo");

    return $l;
  }

  private static function __lcambiar() {
    $l = Panel_fs::__link("imx_cambiar", "Cambiar la imagen", "texto2", "texto2_a");

    $l->style("default", "padding: 3px 3px 3px 11px;");

    $l->title = "Pincha aqu&iacute; para &laquo;cambiar&raquo; la imagen";

    $l->pon_eventos("onclick", "elmt_imx_cambiar(this)");

    return $l;
  }

  private function __fimaxe() {
    $this->url_upload = uniqid($this->url_arquivos); //* reiniciamos url_upload

    $Mb_max  = 3;

    $url_b64 = base64_encode( $this->url_upload );


    $f = new File("f_imaxe");

    $f->maxFileSize = 1024 * 1024 * $Mb_max;

    $f->accept      = "image/*";

    $f->clase_css("default", "file_apagado");

    $f->pon_eventos("onchange", "elmt_imx_fimaxe_change(this, {$Mb_max}, '{$url_b64}')");


    return $f;
  }
}

//******************************************

final class CEtiquetas extends Componente {
  private $etiquetas;

  private $indice;

  public function __construct(Elmt_novas_obd $pe_obd, $i = 0) {
    if ($i > 0) $this->indice = "_{$i}";

    parent::__construct("cetq{$this->indice}");

    $this->etiquetas = $pe_obd->atr("etiquetas{$this->indice}")->valor;

    $this->__ini();

    $this->obxeto("illa")->style("default", "display: inline;");
    $this->obxeto("illa")->style("readonly", "display: inline;");
  }

  public function indice() {
    return $this->indice;
  }

  public function aceptar_etiquetas() {
    $this->etiquetas = strtolower( trim( $this->obxeto("tetqs")->valor() ) );

    $this->__ini();
  }

  public function operacion(EstadoHTTP $e) {
    $a_l = $this->obxetos("link");

    if (count($a_l) == 0) return null;

    foreach ($a_l as $l) if ($l->control_evento()) return $this->operacion_link($e);


    return null;
  }

  public function preparar_saida(Ajax $a = null) {
    $html = "";

    if ($this->readonly) {
      $a_l = $this->obxetos("link");

      if (count($a_l) > 0) foreach ($a_l as $l) $html .= $l->html();
    }
    else
      $html = $this->obxeto("tetqs")->html();

    $this->obxeto("illa")->post($html);


    if ($a == null) return;


    $a->pon($this->obxeto("illa"));

    $this->obxeto("illa")->post(null);
  }

  public function etiquetas($etiquetas = null) {
    if ($etiquetas != null) $this->etiquetas = $etiquetas;

    return $this->etiquetas;
  }

  private function operacion_link(IEfspax_xestor $e) {
    $l = $this->obxeto("link", $e->evento()->subnome(0));

    return $this->pai->prgf()->operacion_etq($e, $l);
  }

  private function __ini() {
    $this->pon_obxeto(self::__text($this->etiquetas, $this->indice));

    if ($this->etiquetas == null) {
      $this->sup_obxeto("link");

      return;
    }

    $a_etq = explode(",", $this->etiquetas);


    if (($f = count($a_etq)) == 0) {
      $this->sup_obxeto("link");

      return;
    }

    for($i = 1; $i <= $f; $i++) $this->pon_obxeto(self::__link($a_etq[$i - 1], $this->indice), $i);
  }

  private static function __text($etq, $indice) {
    $t = Panel_fs::__text("tetqs", 11, 255, " ");

    $t->post($etq);

    $t->clase_css("default" , "novas_etq_txt{$indice}");

    return $t;
  }

  private static function __link($etq, $indice) {
    $l = new Span("link", trim($etq));

    $l->clase_css("default" , "novas_etq_link{$indice}");

    //~ $l->envia_submit("onclick");
    //~ $l->envia_ajax("onclick");
    $l->pon_eventos("onclick", "prgfinovas_cetq_click(this, null, null)");


    return $l;
  }
}

