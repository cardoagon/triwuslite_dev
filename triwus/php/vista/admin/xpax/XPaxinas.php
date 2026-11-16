<?php


final class XPaxinas extends Componente implements ICPC {
  const ptw_0 = "ptw/xpax/xpax_0.html";

  public $id_site;

  public function __construct(FGS_usuario $u) {
    parent::__construct("xpax", self::ptw_0);

    $this->id_site  = $u->atr("id_site")->valor;

    $this->readonly = $u->en_custodia();

    $this->pon_obxeto( Panel_fs::__bmais("bmais", "Crear página") );

    $this->pon_obxeto( new XPaxinas_filtro() );

    $this->pon_obxeto( Paxinfo::inicia(null) );

    $this->pon_obxeto( new CPaxdupli($u)     );
    $this->pon_obxeto( new CPaxnova ($u)     );
    $this->pon_obxeto( new CPaxsup  ($u)     );


    $this->inicia_loms($u);
    
    $this->obxeto("bmais")->envia_AJAX("onclick");


    $this->readonly();
  }

  public function cpc_miga() {
    return "secciones";
  }

  public function actualizar(Efs_admin $e) {
    $this->inicia_loms($e->usuario());

    $this->obxeto("cpaxinfo")->__reset();
  }

  public function readonly($b = false) {
    $this->readonly = $b;

    if (($o = $this->obxeto("cpaxinfo")) != null) $o->readonly($this->readonly);

    $this->obxeto("loms")->readonly($b);

    $this->obxeto("bmais")->visible = !$b;
  }

  public function id_oms() {
    return $this->obxeto("cpaxinfo")->obxeto("id_oms")->valor();
  }

  public function id_paxina() {
    return $this->obxeto("cpaxinfo")->obxeto("id_pax")->valor();
  }

  public function operacion(EstadoHTTP $e) {
    if ($this->obxeto("bmais")->control_evento()) return $this->op_config_alta($e);

    if (($e_aux = $this->obxeto("xpax_filtro")->operacion($e)) != null) return $e_aux;

    if (($e_aux = $this->obxeto("cpaxinfo")->operacion($e)) != null) return $e_aux;

    if (($e_aux = $this->obxeto("loms")->operacion($e)) != null) return $e_aux;

    if (($e_aux = $this->obxeto("cpaxdupli")->operacion($e)) != null) return $e_aux;

    if (($e_aux = $this->obxeto("cpaxnova")->operacion($e)) != null) return $e_aux;

    if (($e_aux = $this->obxeto("cpaxsup")->operacion($e)) != null) return $e_aux;

    return parent::operacion($e);
  }

  public function preparar_saida(Ajax $a = null) {
    parent::preparar_saida($a);
    
    if ($a == null) return;
    
    $a->pon_ok(true, "pcontrol_xpax_link()");
  }

  public function operacion_idoms(Efs_admin $e, ?string $tipo, ?int $id_pax, ?string $id3) {
//~ echo $e->evento()->html();

//~ echo "$tipo, $id_pax, $id3\n";

    $p = self::paxina_obd($tipo, $id_pax, $id3);

//~ print_r( $p->a_resumo() );

    $this->pon_obxeto(Paxinfo::inicia($p, $this->readonly));


    
    $this->obxeto("cpaxinfo")->preparar_saida($e->ajax());


    return $e;
  }

  public function op_config_alta(Efs_admin $e, $id_oms = null) {
    $this->obxeto("cpaxnova")->visible = true;

    $this->obxeto("cpaxnova")->obxeto("tipo")->post("libre");

    $this->obxeto("cpaxnova")->id_oms_pai = $id_oms;

    $this->preparar_saida($e->ajax());


    return $e;
  }

  //~ public function op_config_dupli(Efs_admin $e) {
    //~ $this->obxeto("cpaxdupli")->visible = true;
    
    //~ $this->preparar_saida( $e->ajax() );

    //~ return $e;
  //~ }

  public function op_config_sup(Efs_admin $e) {
    $this->obxeto("cpaxsup")->visible = true;
    
    $this->preparar_saida( $e->ajax() );
    
    $e->ajax()->pon_ok(true, "pcontrol_xpax_link()");

    return $e;
  }

  public function inicia_loms(FGS_usuario $u) {
    $loms = new XPax_lidioma($u, $this->id_site);

    $loms->ptw = "ptw/xpax/xpax_loms.html";

    $loms->where_filtro = $this->obxeto("xpax_filtro")->where();


    $this->pon_obxeto($loms);
  }

  private static function paxina_obd(?string $tipo, ?int $id_pax, ?string $id3) {
    $cbd = new FS_cbd();

    if ($tipo == "ventas2cat") return Paxina_v2cat_obd::inicia_cat($cbd, $id_pax, $id3);


    return Paxina_obd::inicia($cbd, $id_pax);
  }
}


//**************************************


final class XPaxinas_filtro extends Componente {
  const ptw_0 = "ptw/xpax/xpax_filtro.html";

  public function __construct() {
    parent::__construct("xpax_filtro", self::ptw_0);

    $this->pon_obxeto(new Checkbox("chReservadas", false, "Sólo páginas reservadas"));

    $this->obxeto("chReservadas")->envia_ajax("onclick");
  }

  public function where() {
    //~ $wh = "tipo in ('libre','cxcli','marcas','ventas','ventas2','novas','novas2','ref')";
    $wh = "tipo in (select ref from paxina_tipo pt where reservada = 1 and baixa is null)";

    if ( !$this->obxeto("chReservadas")->valor() ) $wh = "(not {$wh})";


    return $wh;
  }

  public function operacion(EstadoHTTP $e) {
    if ($this->obxeto("chReservadas")->control_evento()) return $this->operacion_filtra($e);


    return null;
  }

  private function operacion_filtra(EstadoHTTP $e) {
    $this->pai->inicia_loms($e->usuario());

    $this->pai->obxeto("loms")->preparar_saida($e->ajax());

    return $e;
  }
}

