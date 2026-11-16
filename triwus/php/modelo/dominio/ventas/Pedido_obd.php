<?php

final class Pedido_obd extends Obxeto_bd {
  const pago_ko       = "*KO*";
  const pago_simulado = "*TEST*";

  const fra_solicita  = "#solic@";

  const estadop       = "A=100:D=0:E=0";

  public function __construct($id_site, $ano = null, $numero = null) {
    parent::__construct();

    if ($ano == null) $ano = date("Y");

    $this->atr("id_site" )->valor = $id_site;
    $this->atr("ano"     )->valor = $ano;
    $this->atr("numero"  )->valor = $numero;
    $this->atr("estado_p")->valor = self::estadop;
  }

  public function mapa_bd() {
    return new Pedido_mbd();
  }

  public function baleiro():bool {
    if ($this->atr("ano"   )->valor == null) return true;
    
    return $this->atr("numero")->valor == null;
  }

  public static function inicia(FS_cbd $cbd, $id_site, $ano = null, $numero = null) {
    $p = new Pedido_obd($id_site, $ano, $numero);

    if ($ano    == null) return $p;
    if ($numero == null) return $p;

    $wh_s = $p->atr("id_site")->sql_where($cbd);
    $wh_a = $p->atr("ano"    )->sql_where($cbd);
    $wh_n = $p->atr("numero" )->sql_where($cbd);

    $p->select($cbd, "{$wh_s} and {$wh_a} and {$wh_n}");

    return $p;
  }

  public static function reinicia(FS_cbd $cbd, Pedido_obd $p) {
    return Pedido_obd::inicia($cbd,
                              $p->atr("id_site")->valor,
                              $p->atr("ano")->valor,
                              $p->atr("numero")->valor
                             );
  }

  public function kg(FS_cbd $cbd = null) {
    if ($cbd == null) $cbd = new FS_cbd();

    $wh_s = "id_site = " . $this->atr("id_site")->valor;
    $wh_a = "ano = "     . $this->atr("ano"    )->valor;
    $wh_n = "numero = "  . $this->atr("numero" )->valor;

    $r = $cbd->consulta("select servizo, peso, unidades from pedido_articulo where {$wh_s} and {$wh_a} and {$wh_n}");

    $kg = 0;
    while($a = $r->next()) {
      if ($a['servizo'] == 1) continue;

      $kg += ($a['peso'] * $a['unidades']);
    }

    return round($kg, 2);
  }

  public function pagado() {
    return $this->atr("pago_momento")->valor != null;
  }

  public function _pedido_articulo(FS_cbd $cbd = null) {
    if ($cbd == null) $cbd = new FS_cbd();

    $s = $this->atr('id_site')->valor;
    $a = $this->atr('ano'    )->valor;
    $n = $this->atr('numero' )->valor;

    $sql = "select id_articulo from pedido_articulo where id_site = {$s} and ano = {$a} and numero = {$n}";

    $r = $cbd->consulta($sql);

    $_ = null;
    while ($_r = $r->next())
      $_[] = Pedido_articulo_obd:: inicia($cbd, $s, $a, $n, $_r['id_articulo']);


    return $_;
  }

  public function ct_articulos(FS_cbd $cbd = null) {
    if ($cbd == null) $cbd = new FS_cbd();

    $wh  = "id_site = " . $this->atr('id_site')->valor . " and
                ano = " . $this->atr('ano'    )->valor . " and
             numero = " . $this->atr('numero' )->valor;

    $sql = "select count(*) as ct from pedido_articulo where {$wh}";

    $r = $cbd->consulta($sql);

    if (!$_a = $r->next()) return 0;


    return $_a['ct'];
  }

  public function emisor(FS_cbd $cbd = null) {
    if ($cbd == null) $cbd = new FS_cbd();

    return Site_obd::inicia($cbd, $this->atr("id_site")->valor)->usuario_obd($cbd);
  }

  public function cliente(FS_cbd $cbd = null) {
    if ($cbd == null) $cbd = new FS_cbd();

    if (($id_c = $this->atr("id_cliente")->valor) != null) {
      $c = FGS_usuario::inicia($cbd, $id_c);

      if ( $c->validado() ) return $c;

      return null;
    }
    elseif (($email = $this->atr("cliente_email")->valor) == null) return null;


    $c = new FGS_usuario();

    $c->select_email($cbd, $email, $this->atr("id_site")->valor);


    if ($c->validado()) return $c;


    return null;
  }

  public function destino(FS_cbd $cbd = null) {
    if ($cbd == null) $cbd = new FS_cbd();

    return Contacto_obd::inicia($cbd, $this->atr("id_destino")->valor, "d");
  }

  public function fpago(FS_cbd $cbd = null) {
    if ($cbd == null) $cbd = new FS_cbd();

    return Pedido_fpago_obd::inicia($cbd, $this->atr("id_site")->valor, $this->atr("ano")->valor, $this->atr("numero")->valor);
  }

  public function select(FS_cbd $cbd, $where = null, $orderby = null) {
    $this->atr("ano"    )->valor = null;
    $this->atr("numero" )->valor = null;
    
    $r = $cbd->consulta($this->sql_select($cbd, $where, $orderby));

    if (!$a = $r->next()) return;

    $this->post($a);
  }

  public function insert(FS_cbd $cbd, Pedido_fpago_obd $pfp, Contacto_obd $c, $a_pedido_arti, $_portes) {
    if (count($a_pedido_arti) == 0) return array("r"=>0);

    //* inserta contacto destino
    $c->atr("id_contacto")->valor = null;
    $c->atr("tipo"       )->valor = "d";
    $c->atr("id_enderezo")->valor = null;

    if (!$c->insert($cbd)) return array("r"=>0);

    $this->atr("id_destino")->valor = $c->atr("id_contacto")->valor;


    //* calcula el numero de pedido
    if (!$this->calcula_id($cbd)) return array("r"=>0);

    //* inserta forma de pago
    $pfp->atr("id_site"  )->valor = $this->atr("id_site")->valor;
    $pfp->atr("ano"      )->valor = $this->atr("ano"    )->valor;
    $pfp->atr("id_pedido")->valor = $this->atr("numero" )->valor;

    if ( !$pfp->insert($cbd) ) return array("r"=>0);


//~ echo "<pre>" . print_r($_portes, true) . "</pre>";

    //* inserta pedido_portes
    foreach($_portes[1] as $id_almacen=>$p_i) {
      $sa = $this->atr("id_site")->valor;
      $aa = $this->atr("ano"    )->valor;
      $na = $this->atr("numero" )->valor;

      if ($p_i['i'] == 0) $p_i['i'] = "0";

      $sql = "insert into pedido_portes (id_site, ano, numero, id_almacen, portes)
                   values ({$sa}, {$aa}, {$na}, {$id_almacen}, {$p_i['i']})";

      if (!$cbd->executa($sql)) array("r"=>0);
    }


    //* inserta tupla pedido
    if (($this->atr("portes")->valor = $_portes[0]) == null) $this->atr("portes" )->valor = "0";

    if ($this->atr("id_pago")->valor                == null) $this->atr("id_pago")->valor = Pedido_obd::pago_ko;

    if ($this->atr("momento")->valor                == null) $this->atr("momento")->valor = date("YmdHis");

    if (!$cbd->executa($this->sql_insert($cbd))) return array("r"=>0);


    //* inserta pedido_articulo + control de stock
    $resposta_ok = array("r"=>1, "articulos_0"=>array());

    foreach($a_pedido_arti as $pa) {
      $pa->atr("numero")->valor = $this->atr("numero")->valor;

      if (!$pa->insert($cbd)) return array("r"=>0);
    }

    return $resposta_ok;
  }

  public function update(FS_cbd $cbd) {
    return $cbd->executa($this->sql_update($cbd));
  }

  public function fra_solicita(FS_cbd $cbd, Contacto_obd $c) {
    if ( !$c->update($cbd) ) return false;
    
    $s = $this->atr("id_site")->valor;
    $a = $this->atr("ano"    )->valor;
    $n = $this->atr("numero" )->valor;

    $sql = "update pedido 
               set id_factura      = '" . Pedido_obd::fra_solicita . "',
                   factura_momento = '" . date("YmdHis") . "'
             where id_site = {$s} and ano = {$a} and numero = {$n}";


    return $cbd->executa($sql);
  }

  public function fra_update(FS_cbd $cbd, $fraref) {
    $s = $this->atr("id_site")->valor;
    $a = $this->atr("ano"    )->valor;
    $n = $this->atr("numero" )->valor;

    $sql = "update pedido 
               set id_factura      = '{$fraref}',
                   factura_momento = '" . date("YmdHis") . "'
             where id_site = {$s} and ano = {$a} and numero = {$n}";


    return $cbd->executa($sql);
  }

  public function fra_delete(FS_cbd $cbd) {
    $s = $this->atr("id_site")->valor;
    $a = $this->atr("ano"    )->valor;
    $n = $this->atr("numero" )->valor;

    $sql = "update pedido 
               set id_factura      = null,
                   factura_momento = null
             where id_site = {$s} and ano = {$a} and numero = {$n}";


    return $cbd->executa($sql);
  }

  public function delete(FS_cbd $cbd) {
    $wh_s  = "id_site = "   . $this->atr("id_site")->valor;
    $wh_a  = "ano = "       . $this->atr("ano"    )->valor;
    $wh_n1 = "numero = "    . $this->atr("numero" )->valor;
    $wh_n2 = "id_pedido = " . $this->atr("numero" )->valor;

    $sql = "delete from pedido_articulo where {$wh_s} and {$wh_a} and {$wh_n1}";

    if (!$cbd->executa($sql)) return false;

    $sql = "delete from pedido_portes where {$wh_s} and {$wh_a} and {$wh_n1}";

    if (!$cbd->executa($sql)) return false;

    //~ $sql = "delete from pedido_mrw where {$wh_s} and {$wh_a} and {$wh_n1}";

    //~ if (!$cbd->executa($sql)) return false;

    $sql = "delete from pedido_fpago where {$wh_s} and {$wh_a} and {$wh_n2}";

    if (!$cbd->executa($sql)) return false;

    if ($this->atr("id_destino")->valor != null) {
      $c = Contacto_obd::inicia($cbd, $this->atr("id_destino")->valor, "d");

      if (!$c->delete($cbd)) return false;
    }

    if ($this->atr("id_factuaricion")->valor != null) {
      $c = Contacto_obd::inicia($cbd, $this->atr("id_factuaricion")->valor, "f");

      if (!$c->delete($cbd)) return false;
    }


    //~ return $cbd->executa($this->sql_delete($cbd));
    return $cbd->executa("delete from pedido where {$wh_s} and {$wh_a} and {$wh_n1}");
  }

  public function cancelar(FS_cbd $cbd) {
    if ($this->atr("pago_momento")->valor != null)
      if (!$this->devolver($cbd, false)) return false;


    $this->atr("cancelado")->valor = date("YmdHis");


    return $this->update($cbd);
  }

  public function devolver(FS_cbd $cbd, $delete = true, $signo = -1) {
    $wh_s = "id_site = " . $this->atr("id_site")->valor;
    $wh_a = "ano = "     . $this->atr("ano"    )->valor;
    $wh_n = "numero = "  . $this->atr("numero" )->valor;

    $sql = "select id_articulo, unidades from pedido_articulo where {$wh_s} and {$wh_a} and {$wh_n}";

    $r = $cbd->consulta($sql);
    while($_r = $r->next()) {
      $articulo_obd = Articulo_obd::inicia($cbd, $this->atr("id_site")->valor, $_r['id_articulo']);

      if (!$articulo_obd->resta_unidades($cbd, $signo * $_r['unidades'])) return false;

      //~ if ( !$this->devolver_servizo($cbd, $articulo_obd, ($signo == -1)) ) return false;
    }

    if (!$delete) return true;



    return $this->delete($cbd);
  }

  public function devolver_servizo(FS_cbd $cbd, Articulo_obd $a, $b = true) {
    if ( $a->atr("tipo"            )->valor != "s" ) return true;
    if ( $a->atr("servizo_permisos")->valor == ""  ) return true;

    $u  = FGS_usuario::inicia_email($cbd, $this->atr("cliente_email")->valor, $this->atr("id_site")->valor);

    //~ $_p = explode(", ", $a->atr("servizo_permisos")->valor);
    $_p = FGS_usuario::explode_permisos($a->atr("servizo_permisos")->valor);

    foreach ($_p as $p) {
      if ($b)
        $u->sup_permiso($p);
      else
        $u->pon_permiso($p);
    }

    return true;
  }

  public function renumerar(FS_cbd $cbd) {
    $s_0 = $this->atr("id_site")->valor;
    $a_0 = $this->atr("ano")->valor;
    $n_0 = $this->atr("numero")->valor;

    if (!$this->calcula_id($cbd)) return false;

    $n_1 = $this->atr("numero")->valor;


    $sql = "update pedido set numero = {$n_1} where id_site = {$s_0} and ano = {$a_0} and numero = {$n_0}";

    if ( !$cbd->executa($sql) ) return false;


    $sql = "update pedido_portes set numero = {$n_1} where id_site = {$s_0} and ano = {$a_0} and numero = {$n_0}";

    if ( !$cbd->executa($sql) ) return false;


    $sql = "update pedido_articulo set numero = {$n_1} where id_site = {$s_0} and ano = {$a_0} and numero = {$n_0}";

    if ( !$cbd->executa($sql) ) return false;


    $sql = "update pedido_fpago set id_pedido = {$n_1} where id_site = {$s_0} and ano = {$a_0} and id_pedido = {$n_0}";

    if ( !$cbd->executa($sql) ) return false;


    return true;
  }

  public function calcula_total($tpvpive = -1, FS_cbd $cbd = null, $reembolso = true) {
    $tpvpive = ($tpvpive == -1)?$this->atr("tpvpive")->valor:0;
    
    $tpvpive -= $this->atr("dto"          )->valor;

    $tpvpive += $this->atr("portes"       )->valor;
    $tpvpive += $this->atr("tpvp_plus_ive")->valor;

    if (!$reembolso) return $tpvpive;

    return $tpvpive + $this->calcula_reembolso($tpvpive, $cbd);
  }

  public function calcula_reembolso($tpvpive = -1, FS_cbd $cbd = null) {
    if ($this->atr("id_fpago")->valor != 2) return 0;

    if ($cbd == null) $cbd = new FS_cbd();

    if ($tpvpive == -1) $tpvpive = $this->calcula_total(-1, $cbd, false);

    $a_recargo = $this->fpago($cbd)->precargo();


    return self::calcula_reembolso_2($tpvpive, $a_recargo);
  }

  public static function calcula_reembolso_2($tpvpive, $a_recargo) {
    $reembolso = ($tpvpive * ($a_recargo['porcentaxe'] / 100));

        if ($reembolso > $a_recargo['max']) $reembolso = $a_recargo['max'];
    elseif ($reembolso < $a_recargo['min']) $reembolso = $a_recargo['min'];


    return $reembolso;
  }

  public static function pon_cliente(FS_cbd $cbd, FGS_usuario $u, $update_contacto = true) {
    //* 1.- actualiza dato pedido.id_cliente
    $id_cliente = $u->atr("id_usuario")->valor; //* xa se inseriu o usuario na mesma transación ($cbd))
    $id_site    = $u->atr("id_site"   )->valor;
    $email      = $u->atr("email"     )->valor;

    $sql = "update pedido
               set id_cliente = {$id_cliente}
             where id_site    = {$id_site} and cliente_email = '{$email}' and id_cliente is null";


    if (!$cbd->executa($sql)) return false;


    //* 2.- Actualiza información de contacto del nuevo usuario, con la info de contacto, pedido.id_destino

    //* 2.1.-Busca último pedido.

    if (!$update_contacto) return true;

    $sql = "select ano, numero
              from pedido
             where id_cliente = {$id_cliente} and id_site = {$id_site}
          order by momento desc
             limit 0, 1";

    $r = $cbd->consulta($sql);

    if (!$_a = $r->next()) return true;

    $p = Pedido_obd::inicia($cbd, $id_site, $_a['ano'], $_a['numero']);

    //* 2.2.-Actualiza info de contacto do novo usuario.

    $d = $p->destino($cbd);

    return $u->update_contacto($cbd, $d);

  }

  public static function asignar_permisos(FGS_usuario $u, FS_cbd $cbd = null) {
    /* Asigna permisos ao usuario, a partir de servizos comprados no proio site e non caducados.
     * 
     * PREOCOND. $u->validado() == true.
     */
     
    if (!$u->validado()) return $u;
    
    
    $c = $u->atr("id_usuario")->valor;
    $s = $u->atr("id_site"   )->valor;
    
    $sql = "select a.servizo_permisos
             from pedido_articulo a inner join pedido b on (a.id_site = b.id_site and a.ano = b.ano and a.numero = b.numero)
            where ( a.id_site = '{$s}' ) 
              and ( a.tipo = 's' )
              and ( b.id_cliente = '{$c}' )
              and ( not b.pago_momento is null )
              and ( not a.servizo_permisos is null ) 
              and ( not a.servizo_dias is null ) 
              and ( DATE_ADD(ifnull(a.servizo_data, b.pago_momento), INTERVAL a.servizo_dias DAY) > now() )";
    

    if ($cbd == null) $cbd = new FS_cbd();
    
    $r = $cbd->consulta( $sql );
    
    while ($_r = $r->next()) {
      //~ $_p = explode(", ", $_r["servizo_permisos"]);
      $_p = FGS_usuario::explode_permisos($_r["servizo_permisos"]);
      
      foreach ($_p as $p) $u->pon_permiso($p);
    }
    

    return $u;

  }

  public static function estado_p2array($s_estado_p) {
    $_ep   = array("A"=>0, "D"=>0, "E"=>0);

    $_ep1 = explode(":", $s_estado_p);

    foreach ($_ep1 as $ep_i) {
      list($k, $v) = explode("=", $ep_i);

      $_ep[$k] = $v;
    }


    return $_ep;
  }

  public static function ref($ano, $num):string {
    return "{$ano}-" . Articulo_obd::ref($num, 6);
  }
  
  public function calcula_estado_p(FS_cbd $cbd = null) {
    if ($cbd == null) $cbd = new FS_cbd();

    $wh  = "id_site = " . $this->atr('id_site')->valor . " and
                ano = " . $this->atr('ano'    )->valor . " and
             numero = " . $this->atr('numero' )->valor;


    $estado_p = null;
    $ct = $this->ct_articulos($cbd);


    $sql = "select estado, count(estado) as ct_e from pedido_articulo a where {$wh} group by estado";

    $r = $cbd->consulta($sql);

    while ($_a = $r->next()) {
      if ($estado_p != null) $estado_p .= ":";

      $estado_p .= $_a['estado'][0] . "=" . round( $_a['ct_e'] / $ct * 100, 0);
    }


    $sql = "update pedido set estado_p = '{$estado_p}' where {$wh}";

    if (!$cbd->executa($sql)) return null;


    return self::estado_p2array($estado_p);
  }

  public function update_pago(FS_cbd $cbd = null, $pago_momento = -1, $id_pago = null, $control_stock = true) {
    if ($cbd == null) return $cbd = new FS_cbd();

    if ($pago_momento == -1) {
      $this->atr("pago_momento")->valor = ($this->atr("pago_momento")->valor != null)?null:date("Y-m-d");
    }
    else {
      $this->atr("pago_momento")->valor = $pago_momento;
    }

    $this->atr("id_pago")->valor = $id_pago;

    if (!$this->update($cbd)) return false;
  
   
    $signo = ($this->atr("pago_momento")->valor == null)?-1:1;


    //*** control de stock.
    if ($control_stock) if (!$this->devolver($cbd, false, $signo)) return false;
    

    //*** actualizar servizos do pedido con servizo_data=pago_momento cando (servizo_data == null).
    if (($_pa = $this->_pedido_articulo($cbd)) == null) return true;
    
    foreach($_pa as $pa) {
      if ( $pa->atr("tipo")->valor != "s" ) continue;
      
      if ($signo == 1) {
        if ($pa->atr("servizo_data")->valor != null) continue;
		
        $pa->atr("servizo_data")->valor = $this->atr("pago_momento")->valor;
      }
      else {
        if ( $pa->atr("servizo_data")->fvalor("Ymd") != $this->atr("pago_momento")->fvalor("Ymd") ) continue;
		
        $pa->atr("servizo_data")->valor = null;
      }
            
      if (!$pa->update($cbd)) return false;
    }
    
    

    return true;
  }

  public function update_estado(FS_cbd $cbd, $estado, $id_articulo = null, $id_almacen = null) {
    $wh  = "id_site = " . $this->atr('id_site')->valor . " and
                ano = " . $this->atr('ano'    )->valor . " and
             numero = " . $this->atr('numero' )->valor;


    if (($id_almacen == null) && ($id_articulo == null)) {
      $sql = "update pedido_articulo set estado = '{$estado}' where {$wh}";

      if (!$cbd->executa($sql)) return false;

      $sql = "update pedido set estado_p = '{$estado[0]}=100' where {$wh}";

      return $cbd->executa($sql);
    }


    $wh_a = ($id_almacen != null)?"id_almacen = {$id_almacen}":"id_articulo = {$id_articulo}";

    $sql = "update pedido_articulo set estado = '{$estado}' where {$wh} and {$wh_a}";

    if (!$cbd->executa($sql)) return false;


    return ($this->calcula_estado_p($cbd) != null);
  }

  private function calcula_id(FS_cbd $cbd) {
    $id_site = $this->atr("id_site")->valor;
    $ano     = $this->atr("ano"    )->valor;

    if (!$cbd->executa("insert into pedido_seq (id_site, ano) values ({$id_site}, {$ano})")) return false;

    $r = $cbd->consulta("select LAST_INSERT_ID() as id");

    if (!$a = $r->next()) return false;

    $cbd->executa("delete from pedido_seq where id_site = {$id_site} and ano = {$ano} and numero < {$a['id']}");

    $this->atr("numero")->valor = $a['id'];

    return true;
  }

  public static function pdfdoc(Pedido_obd $p, $factura = true, $ref_triwus = "", $ref_arquivos = "") {
    $s = $p->atr("id_site")->valor;
    $a = $p->atr("ano"    )->valor;
    $n = $p->atr("numero" )->valor;


    $ehtml = ($factura)?new CLista_factura(true, $ref_triwus, $ref_arquivos):new CLista_pedido(true, $ref_triwus, $ref_arquivos);

    $ehtml->__pedido($p);

//~ echo $ehtml->html();

    $d = Refs::url_tmp2() . $s;
    $f = "ticket-" . Articulo_obd::ref($n, 6) . "{$a}.pdf";

    //~ echo "{$d}/{$f}";

    try {
      $html2pdf = new HTML2PDF('P', 'A4', 'es');
      
      $html2pdf->setTestTdInOnePage(false);
      $html2pdf->setDefaultFont('Arial');
      $html2pdf->writeHTML($ehtml->html());
    }
    catch(HTML2PDF_exception $e) {
      echo "ERRO HTML2PDF_exception - $e <br>";
      return null;
    }


    return $html2pdf;
  }
}

//-----------------------------------------------

final class Pedido_mbd extends Mapa_bd {
  public function __construct() {
    $t = new Taboa_dbd("pedido");

    $t->pon_campo("id_site"        , new Numero(), true);
    $t->pon_campo("ano"            , new Numero(), true);
    $t->pon_campo("numero"         , new Numero(), true);
    $t->pon_campo("momento"        , new Data("YmdHis"));
    $t->pon_campo("portes"         , new Numero());
    $t->pon_campo("portes_sabado"  , new Data("Ymd"));
    $t->pon_campo("recollida"      , new Numero());
    $t->pon_campo("prefs");
    $t->pon_campo("id_cliente"     , new Numero());
    $t->pon_campo("cliente_email");
    //~ $t->pon_campo("id_facturacion" , new Numero());
    $t->pon_campo("id_destino"     , new Numero());
    $t->pon_campo("estado");
    $t->pon_campo("estado_p");
    $t->pon_campo("id_pago");
    $t->pon_campo("pago_momento"   , new Data("YmdHis"));
    //~ $t->pon_campo("pago_aplazado"  , new Data("YmdHis"));
    $t->pon_campo("dto"            , new Numero());
    $t->pon_campo("id_factura");
    $t->pon_campo("factura_momento", new Data("YmdHis"));
    $t->pon_campo("cancelado"      , new Data("YmdHis"));

    $this->pon_taboa($t);


    $t3 = new Taboa_dbd("v_pedido");

    $t3->pon_campo("tpvp"         , new Numero());
    $t3->pon_campo("tpvp_plus"    , new Numero());
    $t3->pon_campo("tpvp_plus_ive", new Numero());
    $t3->pon_campo("tpvpive"      , new Numero());
    $t3->pon_campo("id_fpago"     , new Numero());
    $t3->pon_campo("fpago"        , new Numero());

    $this->relacion_fk($t3, array("id_site", "ano", "numero"), array("id_site", "ano", "numero"), "left");
  }
}


//***********************************************


final class Pedido_portes_obd extends Obxeto_bd {

  public function __construct($id_site, $ano, $numero, $id_almacen) {
    parent::__construct();

    $this->atr("id_site"   )->valor = $id_site;
    $this->atr("ano"       )->valor = $ano;
    $this->atr("numero"    )->valor = $numero;
    $this->atr("id_almacen")->valor = $id_almacen;
    $this->atr("estado"    )->valor = "Aceptado";
  }

  public function mapa_bd() {
    return new Pedido_portes_mbd();
  }

  public static function inicia(FS_cbd $cbd, $id_site, $ano, $numero, $id_almacen) {
    $po = new Pedido_articulo_obd($id_site, $ano, $numero, $id_almacen);

    $wh_s = $po->atr("id_site"   )->sql_where($cbd);
    $wh_a = $po->atr("ano"       )->sql_where($cbd);
    $wh_n = $po->atr("numero"    )->sql_where($cbd);
    $wh_r = $po->atr("id_almacen")->sql_where($cbd);

    $po->select($cbd, "{$wh_s} and {$wh_a} and {$wh_n} and {$wh_r}");


    return $po;
  }

  public static function _portes(Pedido_obd $p, FS_cbd $cbd = null) {
    if ($cbd == null) $cbd = new FS_cbd();

    $wh_s = "a.id_site = " . $p->atr("id_site")->valor;
    $wh_a = "a.ano = "     . $p->atr("ano"    )->valor;
    $wh_n = "a.numero = "  . $p->atr("numero" )->valor;

    $sql = "select a.id_almacen, a.portes, b.nome, b.email
              from pedido_portes a inner join v_almacen b on (a.id_site = b.id_site and a.id_almacen = b.id_almacen)
             where {$wh_s} and {$wh_a} and {$wh_n}";

    $r = $cbd->consulta($sql);

    $n = 0; $t = 0; $_pi = array();
    while ($_a = $r->next()) {
      $n += 1;
      $t += $_a['portes'];

      $_pi[ $_a['id_almacen'] ]  = $_a;
    }


    return array($n, $t, $_pi);
  }

  public function select(FS_cbd $cbd, $where = null, $orderby = null) {
    $r = $cbd->consulta($this->sql_select($cbd, $where, $orderby));

    if (!$a = $r->next()) return;

    $this->post($a);
  }

  public function insert(FS_cbd $cbd) {
    if ($this->atr("portes")->valor == null) $this->atr("portes")->valor = "0";

    return $cbd->executa($this->sql_insert($cbd));
  }

  public function update(FS_cbd $cbd) {
    return $cbd->executa($this->sql_update($cbd));
  }
}

//-----------------------------------------------

final class Pedido_portes_mbd extends Mapa_bd {
  public function __construct() {
    $t = new Taboa_dbd("pedido_portes");

    $t->pon_campo("id_site"   , new Numero(), true);
    $t->pon_campo("ano"       , new Numero(), true);
    $t->pon_campo("numero"    , new Numero(), true);
    $t->pon_campo("id_almacen", new Numero(), true);
    $t->pon_campo("portes"    , new Numero());
  }
}


//***********************************************


final class Pedido_articulo_obd extends    Obxeto_bd
                                implements ICLogo     {

  public function __construct(Pedido_obd $p = null) {
    parent::__construct();

    if ($p == null) return;

    $this->atr("id_site")->valor = $p->atr("id_site")->valor;
    $this->atr("ano"    )->valor = $p->atr("ano")->valor;
    $this->atr("numero" )->valor = $p->atr("numero")->valor;
    $this->atr("estado" )->valor = "Aceptado";
  }

  public function mapa_bd() {
    return new Pedido_articulo_mbd();
  }

  public static function inicia(FS_cbd $cbd, $id_site, $ano, $numero, $id_articulo) {
    $pa = new Pedido_articulo_obd();

    $pa->atr("id_site"    )->valor = $id_site;
    $pa->atr("ano"        )->valor = $ano;
    $pa->atr("numero"     )->valor = $numero;
    $pa->atr("id_articulo")->valor = $id_articulo;


    $wh_s = $pa->atr("id_site"    )->sql_where($cbd);
    $wh_a = $pa->atr("ano"        )->sql_where($cbd);
    $wh_n = $pa->atr("numero"     )->sql_where($cbd);
    $wh_r = $pa->atr("id_articulo")->sql_where($cbd);

    $pa->select($cbd, "{$wh_s} and {$wh_a} and {$wh_n} and {$wh_r}");

    return $pa;
  }

  public function iclogo_obd(FS_cbd $cbd, ?string $k = null) {
    if ($cbd == null) $cbd = new FS_cbd();

    $id_logo = $this->iclogo_id($k);

    if ($id_logo != null) return Elmt_obd::inicia($cbd, $id_logo);


    return new Articulo_imaxe_obd();
  }

  public function iclogo_id(?string $k = null) {
    $where = "id_site = " . $this->atr("id_site")->valor . " and id_articulo = " . $this->atr("id_articulo")->valor;

    $sql = "select id_logo from articulo_imx where {$where} order by posicion";

    $cbd = new FS_cbd();

    $r = $cbd->consulta($sql);

    if ($a = $r->next()) return $a['id_logo'];

    return null;
  }

  public function iclogo_src(FS_cbd $cbd, ?string $k = null) {
    //* ICLogo. operación nom implementada.

    return null;
  }


  public static function _almacen(Pedido_obd $p, FS_cbd $cbd = null) {
    if ($cbd == null) $cbd = new FS_cbd();

    $wh_s  = "a.id_site = " . $p->atr("id_site")->valor;
    $wh_a1 = "a.ano = "     . $p->atr("ano"    )->valor;
    $wh_n  = "a.numero = "  . $p->atr("numero" )->valor;

    $sql = "select distinct a.id_almacen, b.id_almacen_portes, b.nome, b.email, c.email as email_portes
  from v_almacen b inner join pedido_articulo a  on (a.id_site = b.id_site and a.id_almacen = b.id_almacen)
                   left  join v_almacen c        on (b.id_almacen_portes <> a.id_almacen and
                                                     b.id_site            = c.id_site    and
                                                     b.id_almacen_portes  = c.id_almacen
                                                    )
            where {$wh_s} and {$wh_a1} and {$wh_n}
            order by a.id_almacen";

    $r = $cbd->consulta($sql);

    $_ai = array();
    while ($_r = $r->next())
      $_ai[ $_r["id_almacen"] ] = array("id"           => $_r["id_almacen"],
                                        "nome"         => $_r["nome"],
                                        "email"        => $_r["email"],
                                        "portes_id"    => $_r["id_almacen_portes"],
                                        "portes_email" => $_r["email_portes"]
                                        );


    return $_ai;
  }

  public static function _articulo(Pedido_obd $p, $id_almacen = null, FS_cbd $cbd = null) {
    if ($cbd == null) $cbd = new FS_cbd();

    $wh_s  = "a.id_site = " . $p->atr("id_site")->valor;
    $wh_a1 = "a.ano = "     . $p->atr("ano"    )->valor;
    $wh_n  = "a.numero = "  . $p->atr("numero" )->valor;

    $wh_a2 = ($id_almacen == null)?"1=1":"a.id_almacen = {$id_almacen}";

    $sql = "select a.*
              from pedido_articulo a
             where {$wh_s} and {$wh_a1} and {$wh_a2} and {$wh_n}";

    $r = $cbd->consulta($sql);

    $_ai = array();
    while ($_a = $r->next()) $_ai[]  = $_a;


    return $_ai;
  }


  public function select(FS_cbd $cbd, $where = null, $orderby = null) {
    $r = $cbd->consulta($this->sql_select($cbd, $where, $orderby));

    if (!$a = $r->next()) return;

    $this->post($a);
  }

  public function insert(FS_cbd $cbd) {
    if ($this->atr("pvp")->valor == null) $this->atr("pvp")->valor = "0";
    
    if ($this->atr("servizo_data")->valor == null) if ($this->atr("servizo_dias")->valor > 0) $this->atr("servizo_renove")->valor = 1;

    return $cbd->executa($this->sql_insert($cbd));
  }

  public function update(FS_cbd $cbd) {
    return $cbd->executa($this->sql_update($cbd));
  }

  public function update_renove(FS_cbd $cbd):bool {
    $this->atr("servizo_renove")->valor = ($this->atr("servizo_renove")->valor == 1) ? null : 1;
          
    return $cbd->executa($this->sql_update($cbd));
  }

  public function delete(FS_cbd $cbd) {
    return $cbd->executa($this->sql_delete($cbd));
  }
}

//-----------------------------------------------

final class Pedido_articulo_mbd extends Mapa_bd {
  public function __construct() {
    $t = new Taboa_dbd("pedido_articulo");

    $t->pon_campo("id_site"         , new Numero(), true);
    $t->pon_campo("ano"             , new Numero(), true);
    $t->pon_campo("numero"          , new Numero(), true);
    $t->pon_campo("id_articulo"     , new Numero(), true);
    $t->pon_campo("k_aux"           , new Numero(), true);
    $t->pon_campo("id_almacen"      , new Numero()      );
    $t->pon_campo("tipo"                                );          
    $t->pon_campo("pvp"             , new Numero()      );
    $t->pon_campo("pvp_plus"        , new Numero()      );
    $t->pon_campo("desc_p"          , new Numero()      );
    $t->pon_campo("sinal_p"         , new Numero()      );
    $t->pon_campo("pive"            , new Numero()      );
    $t->pon_campo("moeda"                               );         
    $t->pon_campo("unidades"        , new Numero()      );
    $t->pon_campo("servizo_data"    , new Data("Y-m-d") );
    $t->pon_campo("servizo_dias"    , new Numero()      );
    $t->pon_campo("servizo_renove"  , new Numero()      );
    $t->pon_campo("servizo_permisos"                    );
    $t->pon_campo("peso"            , new Numero()      );
    $t->pon_campo("describe_html"                       );
    $t->pon_campo("estado"                              );
    $t->pon_campo("nome"                                );
    $t->pon_campo("notas"                               );
    //~ $t->pon_campo("dias_servizo"    , new Numero());
    //~ $t->pon_campo("test_servizo"    , new Numero());


    $this->pon_taboa($t);


    $t3 = new Taboa_dbd("v_pedido_articulo");

    $t3->pon_campo("tpvp"     , new Numero());
    $t3->pon_campo("tpvp_plus", new Numero());
    $t3->pon_campo("tpvp_plus_ive", new Numero());
    $t3->pon_campo("tpvpive"  , new Numero());


    $this->relacion_fk($t3, array("id_site", "ano", "numero", "id_articulo"),
                            array("id_site", "ano", "numero", "id_articulo"), "left");


    $t4 = new Taboa_dbd("almacen");

    $t4->pon_campo("id_almacen_portes", new Numero());


    $this->relacion_fk($t4, array("id_site", "id_almacen"),
                            array("id_site", "id_almacen"), "left");
  }
}
