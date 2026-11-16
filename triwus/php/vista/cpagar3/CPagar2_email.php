<?php

final class CPagar2_email {

  const ptw_0   = "ptw/cpagar3/cpagar3_email_0.html"; //* Aviso de pedido previo pago telemático.
  const ptw_1   = "ptw/cpagar3/cpagar3_email_1.html"; //* Email de venta (mail_pago_ok)
  const ptw_2   = "ptw/cpagar3/cpagar3_email_2.html"; //* Stock-0
  const ptw_5   = "ptw/cpagar3/cpagar3_email_5.html"; //* invitacion de rexistro
  const ptw_6   = "ptw/cpagar3/cpagar3_email_6.html"; //* mail_pago_ko


  public static function mail_pago_ok(Pedido_obd $p, $d = "triwus/") {
    $cbd = new FS_cbd();

    $id_site = $p->atr("id_site")->valor;

    $p_ano = $p->atr("ano"   )->valor;
    $p_num = $p->atr("numero")->valor;

    $site_obd = Site_obd::inicia($cbd, $id_site);

    $tenda    = self::__tenda($site_obd);
    $nomesite = $site_obd->atr("nome")->valor;


    $texto = self::__ptw_config($d, $id_site, self::ptw_1);

    $texto = str_replace("[pedido]", "{$p_num}-{$p_ano}", $texto);
    $texto = str_replace("[tenda]" , $tenda, $texto);
    $texto = str_replace("[legal]" , self::__ptw_legal($d, $nomesite), $texto);

    $cd = $p->destino($cbd);


    $_nticket  = self::_nticket($id_site, $p_ano, $p_num);


    //* email para comprador.
    
//  $m = new XMail2(false);
    $m = new XMail3($id_site);

    try {
      (($_x = $m->_config()) != []) 
        ? $m->pon_rmtnt($_x['email'])
        : $m->pon_rmtnt("no-reply@{$tenda}");
      
      $m->pon_direccion(self::__email_u($cd));
      $m->pon_cc       (self::__email_v($site_obd), true);    //copia para vendedor
      
      $m->pon_asunto   ("Nuevo pedido: {$tenda}");
      $m->pon_corpo    ($texto);
      $m->pon_adxunto  ($_nticket[1], $_nticket[0], 'base64', 'application/pdf');

      $m->enviar();
    }
    catch (Exception $ex) {
      throw $ex;
    }
   

//~ echo $m->html();

    //* email para almacenes.
    self::mail_almacen($cbd, $p, $d, $tenda, $nomesite);


    //* executa un site-hook
    if (!function_exists("cpagar2_completar_pago_ok")) return;

    cpagar2_completar_pago_ok($p);
  }

  public static function mail_pago_ko(Pedido_obd $p, $d = "triwus/") {
    //* Este método é instaciando por cando sucede un erro nunha operación de pago telemático (paypal, tpv).
    //* Entón, aproveitamos este momento para devolver as unidades de cada artículo ao seu almacén, ( gardamos unha copia do pedido sen reservar as unidades ).

    $cbd = new FS_cbd();

    //* 1.- enviamos email ko.
    $_p = $p->a_resumo();

    $id_site = $_p["id_site"];

    $s = Site_obd::inicia($cbd, $id_site);

    $pedido = "{$_p['numero']}-{$_p['ano']}";
    $tenda  = self::__tenda($s);
    $laqui  = "http://{$tenda}/login.php";
    $legal  = self::__ptw_legal($d, $s->atr("nome")->valor);

    $texto = self::__ptw_config($d, $id_site, self::ptw_6);

    $texto = str_replace("[pedido]", $pedido, $texto);
    $texto = str_replace("[laqui]" , $laqui , $texto);
    $texto = str_replace("[tenda]" , $tenda , $texto);
    $texto = str_replace("[legal]" , $legal , $texto);


  //  $m = new XMail2(false);
    $m = new XMail3($id_site);

    try {
      (($_x = $m->_config()) != [])
       ? $m->pon_rmtnt($_x['email'])
       : $m->pon_rmtnt("no-reply@{$tenda}"); 
      
      $m->pon_direccion( self::__email_u( $p->destino($cbd) ) );
      $m->pon_cc(self::__email_v($s), true);                           
      $m->pon_asunto   ("Pago no completado: {$tenda}");
      $m->pon_corpo    ($texto);

      $m->enviar();
    }
    catch (Exception $ex) {
      throw $ex;
    }

    if (!function_exists("cpagar2_completar_pago_ko")) return;

    cpagar2_completar_pago_ko($p);
  }

  public static function mail_stock_0($a_articulos_0, $id_site, $d = "triwus/") {
    if (!is_array($a_articulos_0)) return;

    if (count($a_articulos_0) == 0) return;

    $cbd = new FS_cbd(); $_lista = array(); $_email = array();
    foreach ($a_articulos_0 as $a) {
      if (($al = $a->almacen_obd($cbd)) == null) continue; //* SII ($a->atr("id_almacen")->valor == null)

      if ( !Valida::email($al->atr("email")->valor) ) continue;

      $k_al = $al->atr("id_almacen")->valor;

      $_lista[$k_al] .= "<li>" . $a->atr("id_articulo")->valor . ", " . $a->atr("nome")->valor . ": " . $a->atr("unidades")->valor . " unidades.</li>";

      $_email[$k_al] = $al->atr("email")->valor;
    }

    if (count($_email) == 0) return;


    $s = Site_obd::inicia($cbd, $id_site);

    $tenda     = self::__tenda($s);
    $email_ad  = self::__email_v($s);
    $ptw_corpo = self::__ptw($d, self::ptw_2);


   // $m = new XMail2(false);
    $m = new XMail3($id_site);

    (($_x = $m->_config()) != [])
     ? $m->pon_rmtnt($_x['email'])
     : $m->pon_rmtnt("no-reply@{$tenda}"); 

    $m->pon_asunto("info: {$tenda}: artículos stock 0");
    $m->pon_cc    ($email_ad);

    foreach($_email as $k_al=>$email_al) {
      $m->pon_direccion($email_al, true);

      $m->pon_corpo    (str_replace("[lista_articulos]", $_lista[$k_al], $ptw_corpo));

      try {
        $m->enviar();
      }
      catch (Exception $ex) {
        throw $ex;
      }
    }
  }
  
/* non se usa
  public static function mail_prepago_tpv(Pedido_obd $p, $d = "triwus/") {
    $a_data = $p->atr("momento")->a_data("YmdHis");

    $data   = "{$a_data['d']}-{$a_data['m']}-{$a_data['Y']} {$a_data['H']}:{$a_data['i']}:{$a_data['s']}";

    $_p = $p->a_resumo();

    $ref = Articulo_obd::ref($_p['numero'], 6) . "-" . $_p['ano'];

    $datos_pedido  = "Num. pedido: {$ref}, con fecha {$data}.";


    $cbd = new FS_cbd();

    $s = Site_obd::inicia($cbd, $_p['id_site']);

    $m = new XMail2(false);

    $m->pon_rmtnt    ("no-reply@" . self::__tenda($s));
    $m->pon_direccion(self::__email_v($s));
    $m->pon_asunto   ("Aviso de pedido previo pago telematico");
    $m->pon_corpo    ( str_replace("[datos_pedido]" , $datos_pedido , self::__ptw($d, self::ptw_0)) );

    $m->enviar();
  }
*/
  public static function mail_inviticion_rexistro(Pedido_obd $p, $d = "triwus/") {
    $cbd = new FS_cbd();


    $id_site = $p->atr("id_site")->valor;
    $co      = $p->destino($cbd);


    $s = Site_obd::inicia($cbd, $id_site);

    $tenda = self::__tenda($s);

    $legal = self::__ptw_legal($d, $s->atr("nome")->valor);


    $c = new Cambio_invitaweb_obd(null, 24 * 365 * 5, 16);

    $c->atr("id_site" )->valor = $id_site;
    $c->atr("email"   )->valor = $co->atr("email")->valor;
    $c->atr("nome"    )->valor = $co->atr("nome" )->valor;
    $c->atr("permisos")->valor = "pub";

    $cbd->transaccion();

    if (!$c->insert($cbd)) {
      $cbd->rollback();

      return;
    }

    $codigo = $c->atr("id_cambio")->valor;

    $cbd->commit();

//-19/05/2016-

    $html = self::__ptw($d, self::ptw_5);
    $html = str_replace("[nome]", $co->atr("nome")->valor, $html);
    $html = str_replace("[link_enlace]", "<a href='http://{$tenda}/confirmar.php?c={$codigo}'>http://{$tenda}/confirmar.php?c={$codigo}</a>", $html);
    $html = str_replace("[dominio]", "{$tenda}", $html);
    $html = str_replace("[legal]", $legal, $html);

   // $mail_inv = new XMail2();
    $mail_inv = new XMail3($id_site);

    (($_x = $mail_inv->_config()) != [])
     ? $mail_inv->pon_rmtnt($_x['email'])
     : $mail_inv->pon_rmtnt("no-reply@{$tenda}"); 
    
    $mail_inv->pon_asunto   ("Invitación de registro {$tenda}");
  //  $mail_inv->pon_rmtnt    ("no-reply@{$tenda}"    );
    $mail_inv->pon_direccion($co->atr("email")->valor );

    $mail_inv->pon_corpo($html);

    $mail_inv->enviar();
  }

  public static function _nticket($s, $a, $n) {
    $ref = self::__referencia($a, $n);

    $d = Refs::url_tmp2();
    $f = "pedido-{$ref}.pdf";

    return array($f, "{$d}/{$f}", $d);
  }

  private static function mail_almacen(FS_cbd $cbd, Pedido_obd $p, $d, $tenda, $nomesite) {
    $ano = $p->atr("ano"   )->valor;
    $num = $p->atr("numero")->valor;

    $id_site = $p->atr("id_site")->valor;

  //  $m = new XMail2(false);
    $m = new XMail3($id_site);

    (($_x = $m->_config()) != [])
     ? $m->pon_rmtnt($_x['email'])
     : $m->pon_rmtnt("no-reply@{$tenda}");
    
    $m->pon_asunto("Pedido: " . self::__referencia($ano, $num) . " {$tenda}");

    $destino = Contacto_obd::inicia($cbd, $p->atr("id_destino")->valor, "d");
    $prefs   = $p->atr("prefs")->valor;

    $_alma_0 = Pedido_articulo_obd::_almacen($p, $cbd);

//~ echo "<pre style='color: #ba0000;'>" . print_r($_alma_0, true) . "</pre>";

    foreach($_alma_0 as $id_almacen=>$_alma_arti) {
      if ($id_almacen == 1) continue; //* xa se enviou un email do pedido completo ao almacén 1.

      //* 1.- email para vendedor.

      $_arti = Pedido_articulo_obd::_articulo($p, $id_almacen, $cbd);

      $corpo = self::__ptw_almacen($ano, $num, $d, $_arti, $_alma_arti, $nomesite, $destino, $prefs);

      $m->pon_direccion($_alma_arti["email"], true);
      $m->pon_corpo    ($corpo);


      //* 2.- Copia do email para o almacén de portes (se fora preciso).

      if ($_alma_arti["portes_email"] != null)
        if ($_alma_arti["portes_id"] != 1)
          $m->pon_cc($_alma_arti["portes_email"], true);

      try {
        $m->enviar();
      }
      catch (Exception $e) {
        throw $e;
      }

//~ echo $m->html();
    }
  }

  private static function __email_u(Contacto_obd $c) {
    return $c->atr("email")->valor;
  }

  private static function __txt_vendedor() {
    return "<div style='color:#ba0000;'>** Copia para vendedor ** </div>";
  }

  private static function __email_v(Site_obd $s) {
    return $s->usuario_obd()->atr("login")->valor;
  }

  private static function __tenda(Site_obd $s) {
    //~ if ($s->atr("dominio")->valor != null) return $s->atr("dominio")->valor;

    //~ return $s->atr("nome")->valor;

    return TrwlConfig::dominio;
  }

  private static function __referencia($ano, $num) {
    return Articulo_obd::ref($num, 6) . "-{$ano}";
  }

  private static function __ptw($d, $src) {
    return LectorPlantilla::plantilla("{$d}{$src}");
  }

  private static function __ptw_config($d, $id_site, $src) {
    $a_path = pathinfo($src);

    $ptw_2 = Efs::url_legais($id_site) . $a_path['basename'];

    if (is_file($ptw_2)) return LectorPlantilla::plantilla($ptw_2);


    return LectorPlantilla::plantilla("{$d}{$src}");
  }

  private static function __ptw_legal($d, $nome_site) {
    //~ $src = Refs::url_sites . "/{$nome_site}/" . Refs::url_legais . "/lopd_email.html";
    $src = Refs::url_legais . "/lopd_email.html";

    $ptw = self::__ptw($d, $src);

    return $ptw;
  }

  private static function __ptw_almacen($ano, $num, $d, $_arti, $_vendedor, $nomesite, Contacto_obd $destino, $prefs) {
    $referencia  = self::__referencia($ano, $num);

    $destino_str = $destino->atr("nome")->valor . ".<br/>\n" . $destino->enderezo_obd()->resumo() . ".<br/>\n" . $prefs;


    $s0 = "
<style>
table, th, td {
  border: 1px solid black;
  border-collapse: collapse;
}
th, td {
  padding: 5px;
  text-align: left;
}

tr:hover {
  background-color: #f5f5f5;
}

th {
  background-color: #eee;
  text-transform: capitalize;
}
</style>

<div style='margin: 0 1%;'>
  <p style='color: #ba0000;'>*** Copia para almacén / vendedor. ***</p>
  <p>
    #Ticket: <b>{$referencia}</b>
  </p>
  <p>
    <b>Vendedor</b><br /><br />
    " . Articulo_obd::ref($_vendedor['id'], 4) . " - {$_vendedor['nome']}.
  </p>
  <p>
    <b>Información de destino</b><br /><br />
    {$destino_str}
  </p>
  <p>
    <b>Productos solicitados</b>
  </p>
  <table>
    <tr>
      <th>#Artículo</th>
      <th>Artículo</th>
      <th></th>
      <th>Unidades</th>
    </tr>
    [tarti]
  </table>
  <br />
  <hr />
  <small>[legal]</small>
</div>";

    $s1 = "";
    foreach($_arti as $id_almacen=>$_arti_i) {
      $s1 .= "
  <tr valign=top>
    <td>{$_arti_i['id_articulo']}</td>
    <td>{$_arti_i['nome']}</td>
    <td>{$_arti_i['describe_html']}</td>
    <td>{$_arti_i['unidades']}</td>
  </tr>";
    }


    $s0 = str_replace("[tarti]", $s1                             , $s0);
    $s0 = str_replace("[legal]", self::__ptw_legal($d, $nomesite), $s0);

    return $s0;
  }

}

