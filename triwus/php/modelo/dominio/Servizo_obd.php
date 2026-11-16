<?php

final class Servizo_obd extends Obxeto_bd  {
  public function __construct($id_servizo = null) {
    parent::__construct();

    $this->atr("id_servizo")->valor = $id_servizo;
    $this->atr("pvp_frac"  )->valor = 1;
  }

  public function mapa_bd() {
    return new Servizo_mbd();
  }

  public function baleiro() {
    return ($this->atr("id_servizo")->valor == null);
  }

  public static function inicia(Xesta_cbd $cbd, $id_servizo) {
    $s = new Servizo_obd($id_servizo);

    if ($id_servizo == null) return $s;

    $wh = $s->atr("id_servizo")->sql_where($cbd);

    $s->select($cbd, $wh);

    return $s;
  }

  public function select(Xesta_cbd $cbd, $where = null, $orderby = null) {
    $r = $cbd->consulta($this->sql_select($cbd, $where, $orderby));

    if (!$a = $r->next()) return null;

    $this->post($a);
  }

  public function sb_obd($id_sb = null, Xesta_cbd $cbd = null) {
    if ($id_sb != null) $this->atr("id_sb")->valor = $id_sb;

    return Servizo_base_obd::inicia( $this->atr("id_sb")->valor, $cbd );
  }

  public function fra_obd(Xesta_cbd $cbd = null) {
    if ($cbd == null) $cbd = new Xesta_cbd();

    $kc  = $this->atr("fra_kc" )->valor();
    $ano = $this->atr("fra_ano")->valor();
    $num = $this->atr("fra_num")->valor();

    return Servizo_fra_obd::inicia($cbd, $kc, $ano, $num);
  }

  public function update(Xesta_cbd $cbd, Servizo_base_obd $sb = null) {
    if ($this->baleiro()) return $this->insert($cbd);
    
    if ($sb != null) {
      $this->atr("nome")->valor     = $sb->atr("nome")->valor;
      $this->atr("pvp_base")->valor = $sb->atr("pvp_base")->valor;
      $this->atr("pvp_frac")->valor = $sb->atr("pvp_frac")->valor;
      $this->atr("caduca")->valor   = $sb->atr("caduca")->valor;
      $this->atr("custodia")->valor = $sb->atr("custodia")->valor;
    }

    if ($this->atr("pvp_base")->valor == null) $this->atr("pvp_base")->valor = "0";
    if ($this->atr("caduca"  )->valor == null) $this->atr("caduca"  )->valor = "0";

    return $cbd->executa($this->sql_update($cbd));
  }

  public function insert(Xesta_cbd $cbd, $id_sb = null, Servizo_fra_obd $sf = null) {
    if (($sb = $this->sb_obd($id_sb, $cbd)) == null) return false;

    if ($this->atr("nome"    )->valor == null) $this->atr("nome"    )->valor = $sb->atr("nome")->valor;
    if ($this->atr("pvp_base")->valor == null) $this->atr("pvp_base")->valor = $sb->atr("pvp_base")->valor;
    if ($this->atr("pvp_frac")->valor == null) $this->atr("pvp_frac")->valor = $sb->atr("pvp_frac")->valor;
    if ($this->atr("caduca"  )->valor == null) $this->atr("caduca"  )->valor = $sb->atr("caduca")->valor;
    if ($this->atr("custodia")->valor == null) $this->atr("custodia")->valor = $sb->atr("custodia")->valor;

    if ($this->atr("alta")->valor == null) $this->atr("alta")->valor = date("Ymd") . "000000";

    if ($this->atr("autorenove")->valor == null) $this->atr("autorenove")->valor = 1;

    if (!$cbd->executa($this->sql_insert($cbd))) return false;

    $this->calcula_id($cbd);

    if ($sf == null) return true;

    return $sf->firma_servizo($cbd, $this);
  }

  public function usuario(Xesta_cbd $cbd = null) {
    return Cliente_obd::inicia($cbd, $this->atr("id_usuario")->valor);
  }

  public static function dias($id_servizo, Xesta_cbd $cbd = null) {
    if ($cbd == null) $cbd = new Xesta_cbd();

    $r = $cbd->consulta("select dias from v_dias_servizo where id_servizo = {$id_servizo}");

    if (!$a = $r->next()) return "*";

    return $a['dias'];
  }

  public function validar() {
    $this->atr("pvp_base")->valor = Valida::numero($this->atr("pvp_base")->valor, -1);
    $this->atr("pvp_frac")->valor = Valida::numero($this->atr("pvp_frac")->valor, -1);
    $this->atr("caduca"  )->valor = Valida::numero($this->atr("caduca"  )->valor, 0 );

    $erro = null;

    if($this->atr("id_usuario")->valor == ""  ) $erro .= "Falta o #id do cliente.<br/>";
    if($this->atr("alta"      )->valor == null) $erro .= "A data de alta non pode estar vacía.<br />";
    if($this->atr("id_sb"     )->valor == "**") $erro .= "Debes escoller un servizo base.<br/>";
    if($this->atr("nome"      )->valor == ""  ) $erro .= "Falta cubrir o detalle do servizo.<br/>";
    if($this->atr("autorenove")->valor == "**") $erro .= "Falta seleccionar autorenovación.<br/>";
    

    return $erro;

  }


  private function calcula_id(Xesta_cbd $cbd) {
    $r = $cbd->consulta("select LAST_INSERT_ID() as id");

    if (!$a = $r->next()) die("Servizo_obd.calcula_id(), (!a = r.next())");


    $this->atr("id_servizo")->valor = $a['id'];
  }
}

//-----------------------------------------------

final class Servizo_fra_obd extends Obxeto_bd {
  const pive = 21; //* pive predeterminado facturas triwus.
  
  public function __construct($kc = null, $ano = null, $numero = null) {
    parent::__construct();

    $this->atr("pive"  )->valor = self::pive;

    $this->atr("kc"    )->valor = $kc;
    $this->atr("ano"   )->valor = $ano;
    $this->atr("numero")->valor = $numero;
  }

  public function mapa_bd() {
    return new Servizo_fra_mbd();
  }

  public function baleiro():bool {
    return $this->atr("numero")->valor == null;
  }

  public function editable():bool {    
    if ($this->atr("momento_envio")->valor != null) return false;

    return $this->atr("momento_pago")->valor == null;
  }

  public function ultima(Xesta_cbd $cbd = null):bool {
    if ($this->baleiro()) return false;
    
    if ($cbd == null) $cbd = new Xesta_cbd();
    
    
    $k = $this->atr("kc"    )->valor;
    $a = $this->atr("ano"   )->valor;
    $n = $this->atr("numero")->valor;
    
    $r = $cbd->consulta("select count(*) as t from servizo_fra_seq where kc = '{$k}' and ano = {$a} and numero = {$n}");
    
    
    if (!$_r = $r->next()) return false;
    
    
    return $_r["t"] == 1;
  }

  public static function inicia(Xesta_cbd $cbd, $kc = null, $ano = null, $numero = null):Servizo_fra_obd {
    $p = new Servizo_fra_obd($kc, $ano, $numero);

    if ($numero == null) return $p;

    $wh_c = $p->atr("kc"    )->sql_where($cbd);
    $wh_a = $p->atr("ano"   )->sql_where($cbd);
    $wh_n = $p->atr("numero")->sql_where($cbd);

    $p->select($cbd, "{$wh_c} and {$wh_a} and {$wh_n}");

    return $p;
  }

  public static function inicia_ref(Xesta_cbd $cbd, ?string $ref):Servizo_fra_obd {
    if ($ref == null) return new Servizo_fra_obd();
    
    list($kc, $ano, $num) = self::fac_rid($ref);

    return self::inicia($cbd, $kc, $ano, $num);
  }

  public function select(Xesta_cbd $cbd, $where = null, $orderby = null) {
    $r = $cbd->consulta($this->sql_select($cbd, $where, $orderby));

    if (!$a = $r->next()) return;

    $this->post($a);
  }

  public function insert(Xesta_cbd $cbd, string $wh_servizos) {
    if ($this->atr("ano")->valor == null) $this->atr("ano")->valor = date("Y");

    if (!$this->calcula_id($cbd)) return false;

    if ($this->atr("momento")->valor == null) $this->atr("momento")->valor = date("Y-m-d H:i:s");


    if (!$cbd->executa($this->sql_insert($cbd))) return false;
    
    
    $_f = $this->a_resumo();
    
    $sql = "update servizo
               set fra_kc = '{$_f['kc']}', fra_ano = '{$_f['ano']}', fra_num = '{$_f['numero']}'
             where {$wh_servizos}";
             
    return $cbd->executa($sql);
  }

  public function update(Xesta_cbd $cbd) {
    return $cbd->executa($this->sql_update($cbd));
  }

  public function validar(Xesta_cbd $cbd = null):?string {
    if ( ($erro = $this->validar_alta($cbd)) != null ) return $erro;

    //* Outras validacións, pex formatos numéricos.

    return null;
  }

  public function rectificar(Xesta_cbd $cbd):?Servizo_fra_obd {
    //* inserta servizo para a factura rectificativa.
    $s = new Servizo_obd();

    $s->atr("id_sb"     )->valor = "999999";
    $s->atr("id_usuario")->valor = $this->atr("id_usuario")->valor;
    $s->atr("nome"      )->valor = "Factura rectificada <b>" . $this->id() . "</b>";
    $s->atr("pvp_base"  )->valor = -1 * $this->calcula_total($cbd, true, false);
    $s->atr("pvp_frac"  )->valor = "1";

    if (!$s->insert($cbd)) return null;
    
    
    //* inserta servizo para a factura rectificativa.
    $fr = new Servizo_fra_obd("R", date("Y"));

    $fr->atr("fpago"     )->valor = $this->atr("fpago"     )->valor;
    $fr->atr("pive"      )->valor = $this->atr("pive"      )->valor;
    $fr->atr("id_usuario")->valor = $this->atr("id_usuario")->valor;
   
    $wh_servizos = "id_servizo = " . $s->atr("id_servizo")->valor;
    
    if (!$fr->insert($cbd, $wh_servizos)) return null;
    
    if (!$fr->pagar($cbd, true)) return null;


    //* actualiza id_rectificada
    $this->atr("id_rectificada")->valor = $fr->id();

    if (!$cbd->executa($this->sql_update($cbd))) return null;



    return $fr;
  }

  public function enviar($denvio = null, Xesta_cbd $cbd = null) {
    if ($denvio == null) $denvio = date("Y-m-d H:i:s"); 
       
    $this->atr("momento_envio")->valor = $denvio;

    if ($cbd == null) $cbd = new Xesta_cbd();

    return $cbd->executa($this->sql_update($cbd));
  }

  public function pagar(Xesta_cbd $cbd, $pago = true) {    
    $this->atr("momento_pago")->valor = ($pago)?date("Y-m-d H:i:s"):null;

    return $cbd->executa($this->sql_update($cbd));
  }

  public function firma_servizo(Xesta_cbd $cbd, Servizo_obd $s) {
    if ($s == null) return false;

    $s->atr("fra_kc" )->valor = $this->atr("kc"    )->valor;
    $s->atr("fra_ano")->valor = $this->atr("ano"   )->valor;
    $s->atr("fra_num")->valor = $this->atr("numero")->valor;


    return $cbd->executa($s->sql_update($cbd));
  }

  public function usuario(Xesta_cbd $cbd = null) {
    if ($cbd == null) $cbd = new Xesta_cbd();

    return Cliente_obd::inicia($cbd, $this->atr("id_usuario")->valor);
  }

  public function contacto(Xesta_cbd $cbd = null) {
    if ($cbd == null) $cbd = new Xesta_cbd();

    return $this->usuario($cbd)->contacto_obd($cbd);
  }

  public function a_conceptos(Xesta_cbd $cbd = null) {
    if ($cbd == null) $cbd = new Xesta_cbd();
    
    $myxesta_b = TrwlConfig::mysql_b;

    $wh_c = "fra_kc = '" . $this->atr("kc")->valor . "'";
    $wh_a = "fra_ano = " . $this->atr("ano")->valor;
    $wh_n = "fra_num = " . $this->atr("numero")->valor;

    $sql = "select id_servizo, nome as concepto, pvp,
                   DATE_FORMAT(alta, '%d-%m-%Y') as alta, DATE_FORMAT(baixa, '%d-%m-%Y') as baixa
              from {$myxesta_b}.v_servizo where {$wh_c} and {$wh_a} and {$wh_n} 
          order by id_servizo";

    $r = $cbd->consulta($sql);

    $a_conceptos = null;
    while ($a = $r->next()) $a_conceptos[] = $a;

    return $a_conceptos;
  }

  public function a_descontos() {
    if ($this->atr("dto_valor")->valor == null) return null;
    
    
    $_d   = [];
    
    $_d[] = ["info"  => $this->atr("dto_info" )->valor,
             "valor" => $this->atr("dto_valor")->valor
            ];

//~ echo "<pre>" . print_r($_d, 1) . "</pre>";

    return $_d;
  }

  public function calcula_total(Xesta_cbd $cbd = null, $descontar = true, $ive = true) {
    if ($cbd == null) $cbd = new Xesta_cbd();

    $t = 0;
    if (($a_conceptos = $this->a_conceptos($cbd)) != null) foreach ($a_conceptos as $a) $t += $a['pvp'];

    if ($descontar) $t -= $this->calcula_total_descontos($cbd);

    if ($ive) $t += $t * ($this->atr("pive")->valor / 100);

    return $t;
  }

  public function calcula_total_descontos(Xesta_cbd $cbd = null) {
    if ($cbd == null) $cbd = new Xesta_cbd();

    if (($_d = $this->a_descontos()) == null) return 0;

    $t = 0; foreach ($_d as $a) $t += $a['valor'];


    return $t;
  }

  public function __html($ptw = null, Xesta_cbd $cbd = null) {
    if ($cbd == null) $cbd = new Xesta_cbd();

    if ($this->atr("momento")->valor == null)  return "\nServizo_fra_obd::__html(), this.atr(\"momento\").valor == null , non hai factura {$ano}-{$num}\n\n";

    $kc  = $this->atr("kc")->valor;
    $ano = $this->atr("ano")->valor;
    $num = str_pad($this->atr("numero")->valor, 3, "0", STR_PAD_LEFT);

    $id_fra = strtoupper("{$kc}{$ano}/{$num}");

//~ echo "f::$id_fra<br>";

    $u_obd = $this->usuario($cbd);
    $c_obd = $u_obd->contacto_obd($cbd);
    $e_obd = $c_obd->enderezo_obd();

    if ($ptw == null) {
      $t0 = ($this->atr("momento")->valor < "20170405000000")?"0":"1";
      
      $ptw = "ptw/paneis/pcontrol/usuario/fra/{$t0}/fratriwus_{$kc}.html";
    }

//~ echo $ptw . "<br>";
    
    $f_ptw = LectorPlantilla::plantilla($ptw);

    $tot = $this->calcula_total($cbd, true, false);
    $ive = $tot * ($this->atr("pive")->valor / 100);

    $emision = $this->atr("momento")->fvalor("d-m-Y H:i:s");

    $pago = $this->atr("momento_pago")->fvalor("d-m-Y H:i:s");

    $tlfn = $e_obd->atr("tlfn")->valor . " " . $c_obd->atr("mobil")->valor;

    if (($cliente_nome = trim($c_obd->atr("razon_social")->valor)) == null) $cliente_nome = $c_obd->atr("nome")->valor;

    $f_ptw = str_replace("[id_fra]"           , $id_fra                                 , $f_ptw);
    $f_ptw = str_replace("[emision]"          , $emision                                , $f_ptw);
    $f_ptw = str_replace("[dpago]"            , $pago                                   , $f_ptw);
    $f_ptw = str_replace("[fpago]"            , $this->atr("fpago")->valor              , $f_ptw);
    $f_ptw = str_replace("[cliente_id]"       , $this->atr("id_usuario")->valor         , $f_ptw);
    $f_ptw = str_replace("[descricion]"       , $this->atr("descricion")->valor         , $f_ptw);
    $f_ptw = str_replace("[cliente_nome]"     , $cliente_nome                           , $f_ptw);
    $f_ptw = str_replace("[cliente_ncif]"     , $c_obd->atr("cnif_d")->valor            , $f_ptw);
    $f_ptw = str_replace("[cliente_dirpostal]", $e_obd->resumo()                        , $f_ptw);
    $f_ptw = str_replace("[cliente_tlfn]"     , $tlfn                                   , $f_ptw);
    $f_ptw = str_replace("[cliente_email]"    , $c_obd->atr("email")->valor             , $f_ptw);
    $f_ptw = str_replace("[precio]"           , number_format($tot, 2, ".", ",")        , $f_ptw);
    $f_ptw = str_replace("[pIVA]"             , $this->atr("pive")->valor               , $f_ptw);
    $f_ptw = str_replace("[IVA]"              , number_format($ive, 2, ".", ",")        , $f_ptw);
    $f_ptw = str_replace("[PVP]"              , number_format($tot + $ive, 2, ".", ",") , $f_ptw);
    $f_ptw = str_replace("[conceptos]"        , $this->conceptos_html($cbd)             , $f_ptw);
    $f_ptw = str_replace("[descontos]"        , $this->descontos_html($cbd)             , $f_ptw);
    //~ $f_ptw = str_replace("[firma_sha]"        , $this->firmar($cbd)                     , $f_ptw);


    return $f_ptw;
  }

  public function id() {
    $c = $this->atr("kc")->valor;
    $a = $this->atr("ano")->valor;
    $n = $this->atr("numero")->valor;

    return self::fac_id($c, $a, $n);
  }

  public static function eliminar_ultima(Xesta_cbd $cbd, $id_fra) {
    list($k, $a, $n) = self::fac_rid($id_fra);

    if ($k == "R")
      $sql = "delete from servizo where fra_kc = '{$k}' and fra_num = '{$n}' and fra_ano = '{$a}'";
    else
      $sql = "update servizo set fra_kc = null, fra_num = null, fra_ano = null where fra_kc = '{$k}' and fra_num = '{$n}' and fra_ano = '{$a}'";
    
    if (!$cbd->executa($sql)) return false;


    $sql = "delete from servizo_fra where kc = '{$k}' and numero = '{$n}' and ano = '{$a}'";
    
    if (!$cbd->executa($sql)) return false;


    $sql = "update servizo_fra set id_rectificada = null where id_rectificada = '{$id_fra}'";
    
    if (!$cbd->executa($sql)) return false;


    $sql = "update servizo_fra_seq set numero = numero - 1 where kc = '{$k}' and ano = '{$a}'";

    return $cbd->executa($sql);
  }

  public static function fac_id($kc, $ano, $numero) {
    $numero = str_pad($numero, 4, '0', STR_PAD_LEFT);
    
    return "{$kc}-{$ano}-{$numero}";
  }

  public static function fac_rid($id_factura) {
    if ($id_factura == null) return [null, null, null];
    
    return explode("-", $id_factura);
  }

  private function validar_alta(Xesta_cbd $cbd = null):?string {
    if ( $this->atr("numero")->valor != null ) return null; //* non é alta.


    if ( $this->atr("id_usuario")->valor == null ) return "ERROR, debes teclear o #cliente.";
    if ( $this->atr("kc"        )->valor == null ) return "ERROR, debes seleccionar a serie.";
    if ( $this->atr("momento"   )->valor == null ) return "ERROR, debes seleccionar a data de alta.";
    
    if ( $this->atr("ano")->valor == null ) $this->atr("ano")->valor = $this->atr("momento")->fvalor("Y");


    if ($cbd == null) $cbd = new Xesta_cbd();
    
    //* control momento válido.
    $wh_s = $this->atr("kc" )->sql_where($cbd);
    $wh_a = $this->atr("ano")->sql_where($cbd);
    
    $r = $cbd->consulta("select max(momento) as mm from servizo_fra where {$wh_s} and {$wh_a}");
    
    if (!$_r = $r->next()) return null;
    
    if ( $this->atr("momento")->valor < $_r["mm"] ) return "ERROR, momento alta " . $this->atr("momento")->valor ." debe ser posterior a {$_r["mm"]}.";
     
    //* control usuario. datos facturación..
   

    return null;
  }

  private function conceptos_html(Xesta_cbd $cbd) {
    if (($a_conceptos = $this->a_conceptos($cbd)) == null) die("Servizo_fra_obd.conceptos_html(), (a_conceptos = this.a_conceptos(cbd)) == null");

    $s_tr = "";
    foreach ($a_conceptos as $k=>$a_concepto) {
      $s_td_datas = ($a_concepto['baixa'] != null)?"desde {$a_concepto['alta']} al {$a_concepto['baixa']}":"--";

      $s_tr .= "<tr valign=top>
                  <td class='td-concepto' style=\"width: 50%;\">" . ($k + 1) . ".-&nbsp;{$a_concepto['concepto']}</td>
                  <td class='td-concepto' style=\"width: 35%; white-space:nowrap;\">{$s_td_datas}</td>
                  <td class='td-concepto' style=\"width: 15%; text-align: right;\">" . number_format($a_concepto['pvp'], 2, ".", ",") . "&nbsp;&euro;</td>
                </tr>";
    }

    return $s_tr;
  }

  private function descontos_html(Xesta_cbd $cbd) {
    if (($a_descontos = $this->a_descontos()) == null) return ""; //* podemos ter unha factura sen descontos.

    $s_tr = "";
    foreach ($a_descontos as $k=>$a_desconto) {
      $s_tr .= "<tr valign=top>
                  <td colspan=2 style=\"width: 80%;\" >&nbsp;&nbsp;&nbsp;{$a_desconto['info']}</td>
                  <td style=\"width: 15%; text-align: right;\">-&nbsp;" . number_format($a_desconto['valor'], 2, ".", ",") . "&nbsp;&euro;</td>
                </tr>";
    }

    if ($s_tr == "") return "";

    return "<tr><td colspan=3 style=\"width: 100%; font-size: 5pt; border-top: 0.1mm solid #2EAADD;\">&nbsp;</td></tr>
            {$s_tr}";
  }

  private function calcula_id(Xesta_cbd $cbd) {
    $kc  = $this->atr("kc" )->valor;
    $ano = $this->atr("ano")->valor;

    if (!$cbd->executa("insert into servizo_fra_seq (kc, ano) values ('{$kc}', {$ano})")) return false;

    $r = $cbd->consulta("select LAST_INSERT_ID() as id");

    if (!$a = $r->next()) return false;

    $cbd->executa("delete from servizo_fra_seq where kc = '{$kc}' and ano = {$ano} and numero < {$a['id']}");

    $this->atr("numero")->valor = $a['id'];

    return true;
  }

  private function firma() :string {
     return "";
  }
}

//-----------------------------------------------

class Servizo_base_obd extends Obxeto_bd  {
  public function __construct($id_sb, Xesta_cbd $cbd = null) {
    parent::__construct();

    if ($id_sb == null) return;

    if ($cbd   == null) $cbd = new Xesta_cbd();

    $this->select(new Xesta_cbd(), "servizo_base.id_sb = {$id_sb}");
  }

  public function mapa_bd() {
    return new Servizo_base_mbd();
  }

  public static function inicia($id_sb, Xesta_cbd $cbd = null) {
    $sql = "select tipo from servizo_base where id_sb = {$id_sb}";

    if ($cbd == null) $cbd = new Xesta_cbd();

    $r = $cbd->consulta($sql);

    if (!$a = $r->next()) return null;

    return self::factory($cbd, $a['tipo'], $id_sb);
  }

  public function select(Xesta_cbd $cbd, $where = null, $orderby = null) {
    $r = $cbd->consulta($this->sql_select($cbd, $where, $orderby));

    if (!$a = $r->next()) return;

    $this->post($a);
  }

  private static function factory(Xesta_cbd $cbd, $tipo, $id_sb = null) {
    switch ($tipo) {
      case "plan"   : return new SB_plan_obd($id_sb);
      case "cc_2013": return new SB_plan_obd($id_sb);
    }

    return new Servizo_base_obd($id_sb, $cbd);
  }
}

//-----------------------------------------------

final class SB_plan_obd extends Servizo_base_obd  {
  public function __construct($id_sb) {
    parent::__construct($id_sb);
  }

  public function mapa_bd() {
    return new SB_plan_mbd();
  }
}


//***********************************************

final class Servizo_mbd extends Mapa_bd {
  public function __construct() {
    parent::__construct();

    $t = new Taboa_dbd("servizo");

    $t->pon_campo("id_servizo", new Numero()      , true);
    $t->pon_campo("id_usuario", new Numero()            );
    $t->pon_campo("id_sb"     , new Numero()            );
    $t->pon_campo("nome"                                );
    $t->pon_campo("version"                             );
    $t->pon_campo("notas"                               );    
    $t->pon_campo("pvp_base"  , new Numero()            );
    $t->pon_campo("pvp_frac"  , new Numero()            );
    $t->pon_campo("caduca"    , new Numero()            );
    $t->pon_campo("custodia"  , new Numero()            );
    $t->pon_campo("autorenove", new Numero()            );
    $t->pon_campo("alta"      , new Data("YmdHis")      );
    $t->pon_campo("fra_kc"                              );
    $t->pon_campo("fra_ano"   , new Numero()            );
    $t->pon_campo("fra_num"   , new Numero()            );


    $this->pon_taboa($t);
  }
}

//-----------------------------------------------

final class Servizo_fra_mbd extends Mapa_bd {
  public function __construct() {
    parent::__construct();

    $t = new Taboa_dbd("servizo_fra");

    $t->pon_campo("kc"            , new Varchar()          , true);
    $t->pon_campo("ano"           , new Numero()           , true);
    $t->pon_campo("numero"        , new Numero()           , true);
    $t->pon_campo("id_usuario"    , new Numero()                 );
    $t->pon_campo("pive"          , new Numero()                 );
    $t->pon_campo("momento"       , new Data("Y-m-d H:i:s")      );
    $t->pon_campo("momento_envio" , new Data("Y-m-d H:i:s")      );
    $t->pon_campo("momento_pago"  , new Data("Y-m-d H:i:s")      );
    $t->pon_campo("dto_info"                                     );
    $t->pon_campo("dto_valor"     , new Numero()                 );
    $t->pon_campo("id_rectificada"                               );
    $t->pon_campo("descricion"                                   );
    $t->pon_campo("fpago"                                        );
    $t->pon_campo("notas"                                        );

    $this->pon_taboa($t);
  }
}

//-----------------------------------------------

class Servizo_base_mbd extends Mapa_bd {
  public function __construct(Taboa_dbd $t0 = null) {
    parent::__construct();

    if ($t0 == null) {
      $this->pon_taboa(self::__t1());

      return;
    }

    $this->pon_taboa($t0);

    $this->relacion_fk( self::__t1(), array("id_sb"), array("id_sb") );
  }

  private static function __t1() {
    $t = new Taboa_dbd("servizo_base");

    $t->pon_campo("id_sb", new Numero(), true);
    $t->pon_campo("nome");
    $t->pon_campo("pvp_base",  new Numero());
    $t->pon_campo("pvp_frac",  new Numero());
    $t->pon_campo("caduca", new Numero());
    $t->pon_campo("custodia", new Numero()); //* tiempo de custodia m&iacute;nimo segun LOPD
    $t->pon_campo("baixa", new Numero());

    return $t;
  }
}

//-----------------------------------------------

final class SB_plan_mbd extends Servizo_base_mbd {
  public function __construct() {
    parent::__construct(self::__t0());
  }

  private static function __t0() {
    $t = new Taboa_dbd("sb_plan");

    $t->pon_campo("id_sb", new Numero(), true);
    $t->pon_campo("max_GB", new Numero());
    $t->pon_campo("multilinguaxe", new Numero());
    $t->pon_campo("venta", new Numero());

    return $t;
  }
}

