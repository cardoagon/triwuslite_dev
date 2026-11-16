<?php


final class CEditor_legais extends Componente implements ICPC {
  const ptw_0     = "ptw/paneis/pcontrol/ceditor_legais/cel_0.html";
  const urldocs_0 = "ptw/paneis/pcontrol/ceditor_legais/";

  private $id_site = null;

  public function __construct($id_site) {
    parent::__construct("cel", self::ptw_0);

    $this->id_site = $id_site;

    $this->pon_obxeto(self::__slista());

    $this->pon_obxeto(new checkbox("ch_cookies"));

    $this->pon_obxeto(new CEditor_2("ceditor", ""));

    $this->pon_obxeto(Panel_fs::__bAceptar(" Guardar Cambios"));
    $this->pon_obxeto(Panel_fs::__bCancelar());

    $this->pon_obxeto(new Div("msx_1"));
    $this->pon_obxeto(new Div("msx_2"));

    $this->obxeto("ch_cookies")->envia_ajax("onclick");

    $this->obxeto("bCancelar")->visible  = false;

    $this->obxeto("bAceptar")->visible  = false;

    $this->obxeto("msx_1")->clase_css("default", "texto3_erro");
    $this->obxeto("msx_2")->clase_css("default", "texto3_erro");

    //******************

    $sc = Site_config_obd::inicia(new FS_cbd(), $this->id_site);

    $this->obxeto("ch_cookies" )->post( $sc->atr("cookies")->valor );
  }

  public function cpc_miga() {
    return "textos legales";
  }

  public function operacion(EstadoHTTP $e) {
    $this->obxeto("msx_1")->post("");
    $this->obxeto("msx_2")->post("");

    if ($e->evento()->nome(0) == "bEditar") return $this->operacion_bEditar($e);

    if ($this->obxeto("ch_cookies")->control_evento()) return $this->operacion_ch_cookies($e);
    if ($this->obxeto("slista"    )->control_evento()) return $this->operacion_slista    ($e);
    if ($this->obxeto("bAceptar"  )->control_evento()) return $this->operacion_bAceptar  ($e);
    if ($this->obxeto("bCancelar" )->control_evento()) return $this->operacion_bCancelar ($e);


    return null;
  }

  private function operacion_ch_cookies(Epcontrol $e) {
    $cbd = new FS_cbd();

    $sc = Site_config_obd::inicia($cbd, $this->id_site);

    $sc->atr("cookies")->valor = $this->obxeto("ch_cookies" )->valor();

    $sc->update($cbd);

    return $e;
  }

  private function operacion_bEditar(Epcontrol $e) {
    $id_oms = $e->evento()->subnome(0);
    $id_pax = $e->evento()->subnome(1);
    
    return $e->op_editar($id_oms, $id_pax);
  }

  private function operacion_bCancelar(Epcontrol $e) {
    return $this->operacion_slista($e);
  }

  private function operacion_bAceptar(Epcontrol $e) {
    $slista = $this->obxeto("slista")->valor();

        if ($slista == "4") $src = Efs::url_legais($this->id_site) . "/lopd_email.html";
    elseif ($slista == "5") $src = Efs::url_legais($this->id_site) . "/cookies-aviso.html";

    $txt = urldecode($this->obxeto("ceditor")->valor());

    file_put_contents($src, $txt);

    $this->obxeto("ceditor")->post_txt($txt);


    return $e;
  }

  private function operacion_slista(Epcontrol $e) {
    $this->obxeto("bAceptar" )->visible = false;
    $this->obxeto("bCancelar")->visible = false;
    
    $this->obxeto("ceditor"  )->post_txt("");


    $slista = $this->obxeto("slista")->valor();
    
    if (!is_numeric($slista)) return $e;

    if ($slista == "4") return $this->operacion_slista_1($e, "lopd_email.html"   );

    if ($slista == "5") return $this->operacion_slista_1($e, "cookies-aviso.html");


    return $this->operacion_slista_2($e, $slista);
  }

  private function operacion_slista_1(Epcontrol $e, $f) {
    $d = Efs::url_legais($this->id_site);

    if (!is_dir($d)) mkdir($d);

    if (!is_file("{$d}{$f}")) copy(self::urldocs_0 . $f, "{$d}{$f}");


    $this->obxeto("bAceptar"  )->visible = true;
    $this->obxeto("bCancelar" )->visible = true;

    $this->obxeto("ceditor")->post_txt( file_get_contents("{$d}{$f}") );
    
    $this->obxeto("ceditor")->readonly = false;


    return $e;
  }

  private function operacion_slista_2(Epcontrol $e, $id_legal, $crear = true) {
    $cbd = new FS_cbd();

    $r = $cbd->consulta("select id_paxina, id_idioma from v_site_opcion where id_site = {$this->id_site} and tipo = 'legal'");

    $_pax = null; while($_a = $r->next()) $_pax[$_a['id_idioma']] = $_a['id_paxina'];

    if (($_pax == null) && $crear) {
      MKLegal::__c($this->id_site);

      return $this->operacion_slista_2($e, $id_legal, false);
    }


    $this->obxeto("ceditor")->post_txt( self::__lista_legal($cbd, $_pax, $id_legal) );
    
    $this->obxeto("ceditor")->readonly = true;
    

    return $e;
  }

  private static function __lista_legal(FS_cbd $cbd, $_pax, $id_legal) {
    if ($_pax == null) return "O site non ten páxinas de tipo 'legal'";
      
    $t = "";
    foreach($_pax as $id_idioma=>$id_paxina) {
      $p = Paxina_obd::inicia($cbd, $id_paxina, "legal");

      $l0 = $p->enlace($id_legal);
      
      $l1 = "<a target='_blank' href='{$l0}' style='color: #0000ba; font-size: 11px;'>{$l0}</a>";

      $t .= "<tr>
              <td style='padding: 1px 9px 1px 3px;'>{$id_idioma}</td>
              <td style='padding: 1px 9px 1px 3px;'>{$l1}</td>
              <td style='padding: 1px 9px 1px 3px;'>" . self::__bEditar($p) . "</td>
             </tr>";
    }
    
    return "<table>
              <tr style='font-weight: bold;'>
                <td>Idioma</td>
                <td></td>
                <td></td>
              </tr>
              {$t}
            </table>";
  }

  private static function __slista() {
    $a = array("*" => " ... Selecciona un documento",
               "1" => " ... Avisos Legales",
               "3" => " ... Condiciones Generales de Contratación",
               "2" => " ... Política de Privacidad",
               "4" => " ... Apéndice e-mail (LOPD)",
               "5" => " ... Cookies (Aviso)",
               "6" => " ... Cookies (Texto)"
               );

    $s = new Select("slista", $a);

    $s->style("default", "cursor: pointer;");

    $s->envia_submit("onchange");

    return $s;
  }

  private static function __bEditar(Paxina_legal_obd $p) {
    $s0 = Escritor_html::csubnome;

    $id = "bEditar{$s0}" . $p->atr("id_opcion")->valor . $s0 . $p->atr("id_paxina")->valor;
    
    $b = new Button($id, "editar");

    $b->envia_submit("onchange");

    return $b->html();
  }
}

//************************************************************

final class MKLegal {
  public static function __c($id_site) {
    if (!is_numeric($id_site)) return -1;


    $cbd = new FS_cbd();

    $cbd->transaccion();

    $_info   = self::_info($cbd, $id_site);

//~ print_r($_info);

    if ($_info == null) return -2;

    $_proto = self::__proto($cbd, $_info);

    
    foreach ($_info['site']['idioma'] as $k=>$id_idioma) {
      if ($k == 0) continue; //* este xa existe. ver
      
      if ( !$_proto['pax']->insert($cbd, $id_idioma) ) {
        $cbd->rollback();

        return;
      }
    }

    $cbd->commit();
    //~ $cbd->rollback();
  }

  private static function insert_oms(FS_cbd $cbd, Paxina_obd $pax_proto, $nome, $id_site, $id_idioma) {
    $oms = new Opcion_ms_obd();

    $oms->atr("id_ms")->valor = $id_site;


    $pax_proto->atr("nome"     )->valor = $nome;
    $pax_proto->atr("id_idioma")->valor = $id_idioma;


    return $oms->insert($cbd, $pax_proto);
  }

  private static function __proto(FS_cbd $cbd, $_info) {
    $pax = Paxina_obd::inicia($cbd, $_info['pax']['id'], "legal");
    
    if (($id_oms = $_info['oms']['id']) != null) {
      $oms = Opcion_ms_obd::inicia($cbd, $id_oms);
    }
    else {
      $oms = new Opcion_ms_obd();

      $oms->atr("id_ms")->valor = $_info['site']['id'];
      
      $pax->atr("id_site"  )->valor = $_info['site']['id'];
      $pax->atr("nome"     )->valor = "legal";
      $pax->atr("id_idioma")->valor = $_info['site']['idioma'][0]; //* tomamos como idioma predeterminado a $_info['site']['idioma'][0]

      $oms->insert($cbd, $pax);

      $_info['oms']['id'] = $oms->atr("id_oms"   )->valor;
      $_info['pax']['id'] = $pax->atr("id_paxina")->valor;
      
      $_info['oms']['idioma'][$pax->atr("id_idioma")->valor] = 1;
    }
    

//~ print_r($oms->a_resumo());
//~ print_r($pax->a_resumo());


    return array("oms" => $oms, "pax" => $pax);
  }

  private static function _info(FS_cbd $cbd, $id_site) {
    $_info['site']['id'] = $id_site;

    //* idiomas site.
    $r = $cbd->consulta("select id_idioma from v_ml_site where id_site = {$id_site} order by pos");

    while ($_r = $r->next()) $_info['site']['idioma'][] = $_r['id_idioma'];
    

    if (!is_array($_info['site']['idioma'])) return null;
    

    //* info oms.
    $tipo = "legal";
    
    $r = $cbd->consulta("select id_oms, id_paxina, id_idioma from v_site_paxina where id_site = {$id_site} and tipo = '{$tipo}'");

    while ($_r = $r->next()) {
      if ($_info['oms']['id'] == null) {
        $_info['oms']['id'] = $_r['id_oms'];
        
        $_info['pax']['id'] = $_r['id_paxina'];
      }
      
      $_info['oms']['idioma'][ $_r['id_idioma'] ] = 1;
    }


    return $_info;
  }
}
