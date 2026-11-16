<?php

class CLista_pedido extends FS_lista {

  public $documento     = false;

  public $ref_triwus    = "";
  public $ref_arquivos  = "";

  public $dto           = null;
  public $portes        = null;
  public $_portes       = null;
  public $dpago         = null;
  //~ public $pagado        = null;
  public $fpago_recargo = null;

  public $precisa_envio = false;

  public $ptw_0         = "ptw/clista_pedfra/clista-ped-0.html";
  public $ptw_1         = "ptw/clista_pedfra/clista-ped-1.html";


  protected $pedido     = null;

  public function __construct($documento = false, $ref_triwus = "", $ref_arquivos = "") {
    parent::__construct("clpedido", new Ehtml_pedido());

    $this->documento    = $documento;
    $this->ref_triwus   = $ref_triwus;
    $this->ref_arquivos = $ref_arquivos;

    $this->selectfrom = "select * from v_pedido_articulo";
    $this->orderby    = "ano DESC, numero DESC";

    $this->pon_obxeto(new Param("logo"));
    $this->pon_obxeto(new Param("id_pedido"));
    $this->pon_obxeto(new Param("cnif_d_emisor"));
    $this->pon_obxeto(new Param("nome_emisor"));
    $this->pon_obxeto(new Param("enderezo_emisor"));
    $this->pon_obxeto(new Param("momento"));
    //~ $this->pon_obxeto(new Param("momento_pagado"));
    $this->pon_obxeto(new Param("total"));
    $this->pon_obxeto(new Param("cnif_d_cliente"));
    $this->pon_obxeto(new Param("nomcli"));
    $this->pon_obxeto(new Param("cnif_d_contacto"));
    $this->pon_obxeto(new Param("nome_contacto"));
    $this->pon_obxeto(new Param("enderezo_resumo"));
    $this->pon_obxeto(new Param("prefs"));
    $this->pon_obxeto(new Param("id_fpago"));
    $this->pon_obxeto(new Param("fpago_resumo"));
    $this->pon_obxeto(new Param("T2LF"));
    $this->pon_obxeto(new Param("portes_aux"));
    $this->pon_obxeto(new Param("mobil"));
    $this->pon_obxeto(new Param("email"));
  }

  public function __pedido(Pedido_obd $p = null) {
    if ($p == null) return $this->pedido;

    $id_site = $p->atr("id_site")->valor;

    $this->ptw( $id_site );


    $cbd = new FS_cbd();

    $this->pedido = $p;

    $this->fpago_recargo = ($p->atr("id_fpago")->valor == 2)?$p->fpago($cbd)->precargo():null;

    $this->where = "id_site = " . $id_site . " and ano = " . $p->atr("ano")->valor . " and numero = " . $p->atr("numero")->valor;


    $cp = $this->contacto_p($cbd, $p);

//~ echo "cp::<pre>" . print_r($cp->a_resumo(), true) . "</pre>";

    $cd = Contacto_obd::inicia($cbd, $p->atr("id_destino")->valor, "d");

//~ echo "cd::<pre>" . print_r($cd->a_resumo(), true) . "</pre>";

    $e = $p->emisor($cbd)->contacto_obd($cbd);

    if (($nome_emisor = trim($e->atr("razon_social")->valor)) == "") $nome_emisor = $e->atr("nome")->valor;

    $fp = $p->fpago($cbd);

    $id_pedido = self::referencia($p->atr("numero")->valor, $p->atr("ano")->valor);

    $m1 = $p->atr("momento")->fvalor("d-m-Y H:i:s");

    //~ $m2 = "&nbsp;--- ";
    //~ if ($p->atr("pago_momento")->valor != null) {
      //~ $m2 = $p->atr("pago_momento")->fvalor("d-m-Y H:i:s");
    //~ }

    $this->dpago   = $p->atr("pago_momento")->valor;
    
    

    $this->dto     = $p->atr("dto")->valor;
    $this->portes  = $p->atr("portes")->valor;
    $this->_portes = Pedido_portes_obd::_portes($p, $cbd);

    $cnif_cliente  = ($cp->atr("cnif_d")->valor == null)?"":$cp->atr("cnif_d")->valor . " - ";
    //~ $cnif_cliente  .= ($cp->atr("mobil")->valor == null)?"":$cp->atr("mobil")->valor . " - ";
    //~ $cnif_cliente  .= ($cp->atr("email")->valor == null)?"":$cp->atr("email")->valor . " - ";

    $cnif_contacto = ($cd->atr("cnif_d")->valor == null)?"":$cd->atr("cnif_d")->valor . " - ";

    $portes_aux = "";
        if ($p->atr("portes_sabado")->valor != null) $portes_aux = "Entrega especial sábado día " . $p->atr("portes_sabado")->valor;
    elseif ($p->atr("recollida"    )->valor == 1   ) $portes_aux = "Recogida en tienda";

    $this->obxeto("logo"           )->post( $this->logo($id_site, $cbd)     );
    $this->obxeto("id_pedido"      )->post( $id_pedido                      );
    $this->obxeto("momento"        )->post( $m1                             );
    //~ $this->obxeto("momento_pagado" )->post( $m2                             );
    $this->obxeto("cnif_d_emisor"  )->post( $e->atr("cnif_d")->valor        );
    $this->obxeto("nome_emisor"    )->post( $nome_emisor                    );
    $this->obxeto("enderezo_emisor")->post( $e->enderezo_obd()->resumo()    );
    $this->obxeto("cnif_d_cliente" )->post( $cnif_cliente                   );
    $this->obxeto("nomcli"         )->post( $cp->atr("nome")->valor         );
    $this->obxeto("cnif_d_contacto")->post( $cnif_contacto                  );
    $this->obxeto("nome_contacto"  )->post( $cd->atr("nome")->valor         );
    $this->obxeto("enderezo_resumo")->post( $cd->enderezo_obd()->resumo()   );
    $this->obxeto("id_fpago"       )->post( $p->atr("id_fpago")->valor      );
    $this->obxeto("fpago_resumo"   )->post( $fp->resumo()                   );
    $this->obxeto("prefs"          )->post( $p->atr("prefs")->valor         );
    $this->obxeto("mobil"          )->post( $cd->atr("mobil")->valor        );
    $this->obxeto("email"          )->post( $cd->atr("email")->valor        );

    $this->obxeto("portes_aux"     )->post( $portes_aux );

    $this->pon_t2lf($cd->atr("cp")->valor,
                    $cd->atr("id_country")->valor,
                    $this->pedido->atr("tpvpive")->valor
                   );
  }

  public function html():string {
    if (!$this->visible) return "";

    $html = parent::html();

    if (!$this->documento) return $html;

    $html = str_replace( "[documento]", $html, LectorPlantilla::plantilla("{$this->ref_triwus}{$this->ptw_0}") );
    //~ $html = str_replace( "[titulo]"   , $this->titulo(), $html );
    $html = str_replace( "[legal]"     , $this->lopd()  , $html );

    return $html;
  }

  public static function referencia($n, $a) {
    return Articulo_obd::ref($n, 6) . "-{$a}";
  }

  protected function contacto_p(FS_cbd $cbd, Pedido_obd $p) {
    if (($c = $p->cliente($cbd)) != null) return $c->contacto_obd($cbd);

    return $p->destino($cbd);
  }

  protected function lopd() {
    return LectorPlantilla::plantilla(Efs::url_legais($this->pedido->atr("id_site")->valor) . "/lopd_email.html");
  }

  protected function titulo() {
    return "Id.Pedido: " . self::referencia($this->pedido->atr("numero")->valor, $this->pedido->atr("ano")->valor);
  }

  protected function logo($id_site, FS_cbd $cbd = null) {
    if ($cbd == null) $cbd = new FS_cbd();

    $c = Site_config_obd::inicia($cbd, $id_site);

    $l = $c->logo("ventas_pedfra_logo", $cbd);


    $u = $l->url(0, $cbd);

    if (strpos($u, "noimx.png") !== false) return "";


    $u = $this->ref_arquivos . $u;

    return "<img src=\"{$u}\" style=\"height: 77px;\" />";
  }

  private function pon_t2lf($codpostal, $id_country, $tpvpive) {
    //~ echo  "$codpostal, $tpvpive<br>";

    if ( !Efs::destino_t2lf($codpostal, $id_country) ) return;

    if ($tpvpive < 150 ) return;
    if ($tpvpive > 3000) return;

    $prov = substr($codpostal, 0, 2);

    $_t2lf = array("35"=>"LAS PALMAS", "38"=>"TFE");

    $s = "<div style='padding: 11px 0 11px 0; font-weight: bold; color: #ba0000;'>
            T2LF-MERCANC&Iacute;A SIN DUA DE EXPEDICI&Oacute;N.<br />
            Condición de Entrega: DDP/{$_t2lf[$prov]}.<br />
            Autorizado por el destinatario, despacho por cuenta del expedidor.
          </div>";

    $this->obxeto("T2LF")->post($s);
  }

  private function ptw($id_site) {
    //* instánciase dende $this->__pedido()

    $this->ptw = $this->ref_triwus . $this->ptw_1;
  }
}

//------------------------------------------

class Ehtml_pedido extends FS_ehtml {
  public $nf = 0;

  private $m = null;
  private $t = [ "0" => ["pvp" => 0, "pvpive" => 0],
                 "4" => ["pvp" => 0, "pvpive" => 0],
                "10" => ["pvp" => 0, "pvpive" => 0],
                "21" => ["pvp" => 0, "pvpive" => 0]
               ];
  //~ private $p = null;

  public function __construct(Iterador_bd $rbd = null) {
    parent::__construct($rbd);

    $this->style_table = "width: 100%; font-size: 8pt;";
    $this->align       = "center";
    $this->width       = "100%";
    $this->cellspacing = 0;
    $this->cellpadding = 0;
    $this->border      = 0;
  }

  protected function cabeceira($df = null) {
    $this->xestor->precisa_envio = false;

    return "<tr style=\"background: #E7E7E7; font-weight: bold;\">
              <td colspan=2 style=\"width: 33%; \">#Producto</td>
              <td style=\"width: 11%;  text-align: right;\">Precio&nbsp;&nbsp;</td>
              <td style=\"width: 7%;   text-align: right;\">Dto.&nbsp;</td>
              <td style=\"width: 7%;   text-align: right;\">Uds.&nbsp;</td>
              <td style=\"width: 9%; text-align: right; font-size: 121%; \" title=\"Costes adicionales.\">(+)&nbsp;&nbsp;</td>
              <td style=\"width: 11%; text-align: right;\">Total&nbsp;</td>
              <td style=\"width: 11%; \">&nbsp;&nbsp;IVA</td>
              <td style=\"width: 11%;  text-align: right;\">PVP&nbsp;&nbsp;</td>
            </tr>
            <tr>
              <td colspan=9 style=\"width: 100%; border-top: 1px solid #000000;\"></td>
            </tr>";
  }

  protected function linha_detalle($df, $f) {
    if (!$this->xestor->precisa_envio) $this->xestor->precisa_envio = $f["tipo"] != "s";

    $id_articulo = $f['id_articulo'];

    $pvp_des = ($f['unidades'] == 0)?$f['tpvp']:$f['tpvp'] / $f['unidades'];

    if ($this->m == null) $this->m = Articulo_obd::$a_moeda[$f['moeda']];


    if (($d = $f['desc_p']) != "") $d .= "%";

    $this->t[$f['pive']]['pvp']    += ($f['tpvp'] + $f['tpvp_plus']);

    $this->t[$f['pive']]['pvpive'] += ($f['tpvpive'] + $f['tpvp_plus_ive']);

    $pvp_ive = number_format(($f['tpvpive'] + $f['tpvp_plus_ive']) - ($f['tpvp'] + $f['tpvp_plus']), 2, ",", ".");


    $tpvpive_html = number_format(($f['tpvpive'] + $f['tpvp_plus_ive']), 2, ",", ".") . "&nbsp;{$this->m}";

    return "<tr>
              <td style=\"width: 7%; \">" . Articulo_obd::ref($id_articulo, 6) . "</td>
              <td style=\"width: 26%; \">{$f['nome']}</td>
              <td style=\"width: 11%;  text-align: right;\">" . number_format($f['pvp'], 2, ",", ".") . "&nbsp;{$this->m}</td>
              <td style=\"width: 7%; text-align: right;\">{$d}</td>
              <td style=\"width: 7%; text-align: right;\">{$f['unidades']}</td>
              <td style=\"width: 9%; text-align: right;\">" . number_format(0 + $f['pvp_plus'], 2, ",", ".") . "&nbsp;{$this->m}</td>
              <td style=\"width: 11%; text-align: right;\">" . number_format(($f['tpvp'] + $f['tpvp_plus']), 2, ",", ".") . "&nbsp;{$this->m}</td>
              <td style=\"width: 11%; \">&nbsp;&nbsp;{$f['pive']}%<small>&nbsp;({$pvp_ive}&nbsp;{$this->m})</small></td>
              <td style=\"width: 11%;  text-align: right;\">{$tpvpive_html}</td>
            </tr>
            " . $this->tr_servizo($f) . "
            <tr>
              <td style=\"width: 7%; \"></td>
              <td style=\"width: 93%; \" colspan=8>{$f['describe_html']}</td>
            </tr>
            <tr>
              <td colspan=9 style=\"width: 100%; border-top: 1px solid #000000;\"></td>
            </tr>";
  }

  protected function tr_servizo($f) {
    if ($f['tipo']         != "s") return "";
    if ($f['servizo_dias'] <  1  ) return "";

    if (($dini_aux = $f['servizo_data']) == null) $dini_aux = $ehtml->xestor->dpago;

    $dini = date( "d/m/Y", strtotime($dini_aux) );
    $dfin = date( "d/m/Y", strtotime("{$dini_aux} + {$f['servizo_dias']} days") );


    return "<tr>
              <td style=\"width: 7%; \"></td>
              <td style=\"width: 93%; \" colspan=8>&bull; Desde {$dini} hasta {$dfin}  ·  ( {$f['servizo_dias']} días )</td>
            </tr>";
  }

  protected function totais() {
    //~ $st_ive = "";
    if ($this->t != null) {
      $tpvp    = 0;
      $tpvpive = 0;

      //* subtotais IVE
      foreach ($this->t as $ive=>$a_pvp) {
        $lenda = ($tpvp == 0)?"Subtotales IVA:&nbsp;":"";

        $tpvp    += $a_pvp['pvp'];
        $tpvpive += $a_pvp['pvpive'];

        $pvp    = number_format($a_pvp['pvp']   , 2, ".", "");
        $pvpive = number_format($a_pvp['pvpive'], 2, ".", "");

        //~ $st_ive .= "<tr>
                      //~ <td colspan=2 style=\"width: 54%; font-weight: bold; text-align: right;\">{$lenda}&nbsp;</td>
                      //~ <td colspan=2 style=\"width: 16%;\"></td>
                      //~ <td style=\"width: 11%;  text-align: right;\">{$pvp}&nbsp;{$this->m}</td>
                      //~ <td style=\"width: 6%;   text-align: right;\">{$ive}&nbsp;%</td>
                      //~ <td style=\"width: 13%;  text-align: right;\">{$pvpive}&nbsp;{$this->m}</td>
                    //~ </tr>";
      }

      //* subtotais portes
      $_portes = $this->xestor->_portes;
      $st_portes = "";
      if ($this->xestor->precisa_envio) {
        foreach ($_portes[2] as $_porte_i)
          $st_portes .= "<tr>
                           <td colspan=8 style=\"width: 89%; font-weight: bold;  text-align: right; \">Gastos de env&iacute;o&nbsp;({$_porte_i['nome']})&nbsp;</td>
                           <td style=\"width: 11%;  text-align: right;\">" . number_format($_porte_i['portes'], 2, ",", ".") . "&nbsp;{$this->m}</td>
                         </tr>";
      }

      $tpvpive += $this->xestor->_portes[1];


      $st_dto = "";
      if ($this->xestor->dto > 0)
        $st_dto = "<tr>
                     <td colspan=9 style=\"width: 100%;\"></td>
                   </tr>
                   <tr>
                     <td colspan=8 style=\"width: 89%; font-weight: bold;  text-align: right; \">Descuento promoción&nbsp;</td>
                     <td style=\"width: 11%;  text-align: right;\">-" . number_format($this->xestor->dto, 2, ",", ".") . "&nbsp;{$this->m}</td>
                   </tr>";

      $tpvpive -= $this->xestor->dto;


      //* subtotais fpago = 2
      $st_fpago = "";
      if ($this->xestor->fpago_recargo != null) {
        $a_recargo = $this->xestor->fpago_recargo;

        $recargo = ($tpvpive * ($a_recargo['porcentaxe'] / 100));

            if ($recargo < $a_recargo['min']) $recargo = $a_recargo['min'];
        elseif ($recargo > $a_recargo['max']) $recargo = $a_recargo['max'];


        $st_fpago = "<tr>
                       <td colspan=8 style=\"width: 89%; font-weight: bold;  text-align: right; \">Contra Reembolso:&nbsp; (recargo {$a_recargo['porcentaxe']}%, min. {$a_recargo['min']} &euro;, max. {$a_recargo['max']} &euro;)&nbsp;</td>
                       <td style=\"width: 11%;  text-align: right;\">" . number_format($recargo, 2, ",", ".") . "&nbsp;{$this->m}</td>
                     </tr>";

        $tpvpive += $recargo;
      }

    }

    return "<tr>
              <td colspan=9 style=\"width: 100%;\"></td>
            </tr>
            {$st_portes}
            {$st_fpago}
            {$st_dto}
            <tr>
              <td colspan=9 style=\"width: 100%;\"></td>
            </tr>
            <tr>
              <td colspan=8 style=\"width: 89%; font-weight: bold;  text-align: right;\"><br />TOTAL:</td>
              <td style=\"width: 11%;  text-align: right; font-weight: bold; \"><br />" . number_format($tpvpive, 2, ",", ".") . "&nbsp;{$this->m}</td>
            </tr>";
  }
}

