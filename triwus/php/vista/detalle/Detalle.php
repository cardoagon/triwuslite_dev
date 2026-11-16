<?php

abstract class Detalle extends    Componente
                       implements I_fs {


  public $iprgf = null;

  public $__editor;

  private $prgf_orde     = null;
  private $prgf_sup      = null;

  public function __construct(Paxina_obd $p, $editor = true) {
    parent::__construct("detalle");

    $this->__editor = $editor;

    $this->iniciar($p);
  }

  abstract public function declara_botoneras(Epaxina_edit $e, Paxina_obd $p);
  abstract public function ini_prgf(Paxina_obd $pax, Paragrafo_obd $prgf);

  public function config_lectura() {
    $this->readonly = true;

    if (($a_obx = $this->obxetos("prgf")) == null) return;

    foreach($a_obx as $k=>$o) $this->obxeto($k)->config_lectura();
  }

  public function config_edicion() {
    $this->readonly = false;

    if (($a_obx = $this->obxetos("prgf")) == null) return;

    foreach($a_obx as $k=>$o) $this->obxeto($k)->config_edicion();
  }

  public function config_usuario(IEfspax_xestor $e = null) {
    if (($a_obx = $this->obxetos("prgf")) == null) return;

    foreach($a_obx as $k=>$o) $this->obxeto($k)->config_usuario($e);
  }

  public function pon_prgf(Paragrafo $p, $subnome = null, $posicion = null) {
    if ($subnome == null) $subnome = count($this->obxetos($p->nome)) + 1;

    $this->pon_obxeto($p, $subnome);
    $this->pon_obxeto(new Bpos_prgf(), $subnome);

    if ($posicion == null) $posicion = $subnome;

    //* insertamos en prgf_orde;
    if (is_array($this->prgf_orde))
      foreach($this->prgf_orde as $k=>$orde)
        if ($orde >= $posicion) $this->prgf_orde[$k]++;

    $this->prgf_orde[$p->nome()] = $posicion;

    asort($this->prgf_orde);
  }

  public function sup_prgf(Paragrafo $p) {
    $this->prgf_sup[$p->nome()] = $p;
  }

  public function rec_prgf(Paragrafo $p) {
    unset($this->prgf_sup[$p->nome()]);
  }

  public function pos_prgf(Paragrafo $p) {
    return $this->prgf_orde[$p->nome()];
  }

  public function busca_elmt($id_elmt) {
    $a_prgf = $this->a_prgf();

    if (count($a_prgf) == 0) return null;

    foreach ($a_prgf as $prgf) if (($elmt = $prgf->elemento($id_elmt)) != null) return $elmt;

    return null;
  }

  public function operacion(EstadoHTTP $e) {
    if (!$this->visible) return null;

    $a_obx = $this->obxetos("bPosicionar");

    foreach($a_obx as $k=>$o)
      if ($o->control_evento()) return $this->operacion_bPosicionar($e);

    if (($a_obx = $this->obxetos("prgf")) == null) return null;


    foreach($a_obx as $k=>$o) {
      if (($e_aux = $this->obxeto($k)->operacion($e)) != null) return $e_aux;
    }

    return null;
  }

  public function operacion_bPosicionar(Epaxina_edit $e) {
    //~ echo $e->evento()->html();

    $sn = $e->evento()->subnome(0);

    $posicion = ($sn == 0)?1:$this->pos_prgf($this->obxeto("prgf", $sn)) + 1;

    $prgf = new Paragrafo(new Paragrafo_obd());

    $prgf->config_edicion();

    $this->pon_prgf($prgf, null, $posicion);

    $e->cc_pon($this->obxeto("bPosicionar", $sn));


    $e->obxeto("detalle_btnr_este")->preparar_saida($e->ajax());

    $this->preparar_saida($e->ajax());
    //~ $prgf->preparar_saida_crear($e->ajax(), $posicion);

    return $e;
  }

  public function operacion_kp(IEfspax_xestor $e, $kp) {
    if ($this->iprgf == null) return null;

    return $this->iprgf->operacion_kp($e, $kp);
  }

  public function activar_mensaxe_iframe(Iframe $ifr, $limpar = false, $volver = true) {
    $this->activar_mensaxe(new Cmsxdet_iframe($ifr), $limpar, $volver);
  }
/*
  public function preparar_saida(Ajax $a = null) {
    if ($a == null) {
      $html = $this->prgfs_html();
      
      $this->obxeto("illa")->post($html);
      
      return;
    }
    
    if (($_prgf = $this->a_prgf()) == null) return;

    foreach($_prgf as $prgf) {
      if ($prgf->novo()) 
        $prgf->preparar_saida_crear($a, $this->pos_prgf($prgf));
      else
        $prgf->preparar_saida($a);
    }
    
    
    if ($this->prgf_sup == null) return true;

    foreach ($this->prgf_sup as $prgf) $prgf->preparar_saida_ocultar( $a );

  }
*/

  public function preparar_saida(Ajax $a = null) {
    if (!$this->visible) return "";


    if ($this->ptw != null) $html = $this->html00();
    else                    $html = $this->prgfs_html();

    if ($this->__editor) {
      //* insertamos un prgf baleiro para poder insertar novos prgf nas posicion 0
      $cnome    = Escritor_html::cnome;
      $csubnome = Escritor_html::csubnome;

      $id = $this->nome_completo() . "{$cnome}prgf{$csubnome}0";

      $html = "<div id='{$id}' class='celda_prgf'>
                <center><br /><h3 style='color: transparent;'>&nbsp;</h3><br /></center>
               </div>
               {$html}";
    }


    $this->obxeto("illa")->post($html);



    if ($a == null) return;


    $a->pon($this->obxeto("illa"), true, "trw_elmt_recargar()");
    //~ $a->pon($this->obxeto("illa"));

    $this->obxeto("illa")->post(null);
  }

  public function update(FS_cbd $cbd, $id_paxina) {
    //* actualiza detalle. (updates & inserts) prgfs.
    $det_obd = new Detalle_obd($id_paxina);

    if (($a_prgf = $this->a_prgf()) != null)
      foreach ($a_prgf as $k=>$prgf) {
        //~ echo $prgf->nome_completo() . "<br />";
        if (!$det_obd->update($cbd, $prgf)) return false;
      }

    //* actualiza detalle. xstats_nube.
    if (!XStats_nube::anotar($cbd, $this, $id_paxina)) return false;

    //* actualiza detalle. (deletes) prgfs.
    if ($this->prgf_sup == null) return true;

    foreach ($this->prgf_sup as $prgf) {
      if ($prgf->novo()) continue;

      if (!$det_obd->delete($cbd, $prgf)) return false;
    }

    return true;
  }

  public function a_prgf() {
    $a_prgf = $this->obxetos("prgf");

    if (count($a_prgf) == 0) return null;

    //* control prgfs suprimidos
    if (is_array($this->prgf_sup)) foreach($this->prgf_sup as $k=>$prgf) unset($a_prgf[$k]);

    return $a_prgf;
  }

  public function ct_prgf() {
    $ct_1 = 0; if (is_array($this->prgf_sup)) $ct_1 = count($this->prgf_sup);

    return count($this->obxetos("prgf")) - $ct_1;
  }

  final public static function factory(Paxina_obd $p, $editor = true) {
//~ echo $p->atr("nome")->valor . "::" . $p->atr("tipo")->valor . "<br />";

    switch ($p->atr("tipo")->valor) {
      case "libre"  :  return new Detalle_libre  ($p, $editor);
      case "legal"  :  return new Detalle_legal  ($p, $editor);
      case "cxcli"  :  return new Detalle_cxcli  ($p, $editor);
      case "novas"  :  return new Detalle_novas  ($p, $editor);
      case "novas2" :  return new Detalle_novas2 ($p, $editor);
      case "marcas" :  return new Detalle_marcas ($p, $editor);
      //~ case "ventas" :  return new Detalle_ventas ($p, $editor);
      //~ case "ventas2":  return new Detalle_ventas2($p, $editor);
      case "ventas2":  return new Detalle_ventas2b($p, $editor);
      
      default:
        if ( Paxina_reservada_obd::test( $p->atr("tipo")->valor ) ) return new Detalle_reservado($p, $editor);
    }

    die("Detalle.factory(), p.atr(\"tipo\").valor descoñecido.");
  }

  protected function iniciar(Paxina_obd $p) {
    $this->pon_obxeto(new Div("illa"));

    //~ $this->pon_obxeto(new Cmsxdet()); //* Candidata a desaparecer

    $this->prgfs_ini($p);
  }

  protected function prgfs_html() {
    $html = "";

    if (($a_obx = $this->obxetos("prgf")) == null) return $html;

    if (count($this->prgf_orde) == 0) return $html;

    $bsup_visible = count($this->a_prgf()) > 1; //* conta so os prgfs ordinarios.
    foreach($this->prgf_orde as $k=>$orde) {
      list($nome, $subnome) = explode(Escritor_html::csubnome, $k);

      $html .= $a_obx[$k]->html();
    }

    return $html;
  }

  final protected function prgfs_ini(Paxina_obd $pax) {
    $d = $pax->detalle_obd();

    //* inicio 0
    $this->prgf_orde = null;
    $this->prgf_sup = null;

    $this->sup_obxeto("prgf");
    $this->sup_obxeto("bPosicionar");

    //* inicio Detalle_obd

    $this->pon_obxeto(new Bpos_prgf(), "0");

    if ($d == null) return;

    if (($a_prgf = $d->a_prgf()) == null) return;

    $posicion = 1;
    foreach ($a_prgf as $prgf_obd) $this->pon_prgf($this->ini_prgf($pax, $prgf_obd), null, $posicion++);
  }
}
