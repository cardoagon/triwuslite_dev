<?php

final class CPaxdupli extends Componente {
  const ptw_0 = "ptw/xpax/cpaxdupli_0.html";

  private $paxdupli = null;

  public function __construct(FGS_usuario $u) {
    parent::__construct("cpaxdupli", self::ptw_0);
    
    $this->visible = false;

    $this->pon_obxeto(new CLista_idiomas($u));

    $this->pon_obxeto(Panel_fs::__text("nome", 22, 99, "... el t&iacute;tulo de la p&aacute;gina"));
    $this->pon_obxeto(new Textarea("descricion", 2, 22));

    $this->pon_obxeto(Panel_fs::__baceptar());
    $this->pon_obxeto(Panel_fs::__bcancelar());

    $this->obxeto("bAceptar" )->envia_ajax("onclick");
    $this->obxeto("bCancelar")->envia_ajax("onclick");
  }

  public function operacion(EstadoHTTP $e) {
    if ($this->obxeto("bAceptar" )->control_evento()) $this->operacion_bAceptar($e);
    if ($this->obxeto("bCancelar")->control_evento()) $this->operacion_bCancelar($e);


    return parent::operacion($e);
  }

  public function operacion_bdupli(Epcontrol $e, $id_paxina) {
    $this->visible = true;

    $this->paxdupli = Paxina_obd::inicia(new FS_cbd(), $id_paxina);

    $this->obxeto("nome"      )->post($this->paxdupli->atr("nome")->valor);
    $this->obxeto("descricion")->post($this->paxdupli->atr("descricion")->valor);

    $this->pai->preparar_saida($e->ajax());


    return $e;
  }

  private function operacion_bCancelar(Epcontrol $e) {
    $this->visible = false;

    $this->pai->preparar_saida($e->ajax());


    return $e;
  }

  private function operacion_bAceptar(Epcontrol $e) {
    if (($erro = $this->validar()) != null) {
      $e->post_msx($erro);

      return $e;
    }

//~ echo "111111111111111111111111111111111111<br>";

    $id_oms_0  = $this->paxdupli->atr("id_opcion")->valor;

    $cbd       = new FS_cbd();

    $oms       = Opcion_ms_obd::inicia($cbd, $id_oms_0);
    $pax       = $this->plantilla_paxina_obd($cbd);
    $a_idiomas = $this->obxeto("lidiomas")->a_checks();
    //~ $td        = $this->obxeto("std")->valor;
    $td        = "p";

//~ echo "22222222222222222222222222222222222222<br>";

    $cbd->transaccion();

    if (!self::duplicar($cbd, $oms, $pax, $a_idiomas, $td)) {
      $e->post_msx("Error al actualizar la base de datos");
      
      $cbd->rollback();

      return $e;
    }

    $cbd->commit();
    //~ $cbd->rollback();


    return $this->operacion_bCancelar($e);
  }

  private function validar():?string {
    $erro = null;

    $n = Erro::validar_nome_paxina( $this->obxeto("nome")->valor() );

        if ($n == -1) $erro .= Erro::nome_pax_0 . "<br>";
    elseif ($n == -2) $erro .= Erro::nome_pax_1 . "<br>";
    else              $this->obxeto("nome")->post($n);


    if ($this->obxeto("lidiomas")->a_checks() == null) $erro .= "Debes seleccionar al menos un idioma.<br>";

    return $erro;
  }

  public static function duplicar(FS_cbd $cbd, Opcion_ms_obd $oms, Paxina_obd $pax, $a_idiomas, $td = "p") {
    $id_sincro_0 = $pax->atr("id_sincro"     )->valor;
    $id_coment_0 = $pax->atr("id_comentarios")->valor;

    $a_sincro    = self::__a_sincro($cbd, $id_sincro_0);

//~ echo "oms<pre>" . print_r($oms->a_resumo(), true) . "</pre>";
//~ echo "pax<pre>" . print_r($pax->a_resumo(), true) . "</pre>";

//~ echo "Idiomas<pre>" . print_r($a_idiomas, true) . "</pre>";
//~ echo "SINCRO<pre> " . print_r($a_sincro , true) . "</pre>";

    foreach ($a_idiomas as $id_idioma) {
      if (isset($a_sincro[$id_idioma])) {
        if ($pax->atr("id_prgf_indice")->valor <> 0) { //* tipo libre ou páxina índice
          $oms->atr("id_oms"  )->valor = null;
          $oms->atr("posicion")->valor = null;
        }
      }
      
      if (!self::duplicar_p($cbd, $oms, $pax, $id_idioma, $id_coment_0)) return false;
    }


    return true;
  }

  public static function duplicar_p(FS_cbd $cbd, Opcion_ms_obd $oms, Paxina_obd $pax, $id_idioma, $id_coment) {
    $refsite = self::refsite($cbd, $pax->atr("id_site")->valor);
    
    if ($oms->atr("id_oms")->valor == null) { //* tipo libre ou páxina índice
      $oms->atr("action")->valor = Opcion_ms_obd::calcula_action($pax->atr("nome")->valor, $oms->atr("id_ms")->valor);

      if (!$oms->insert_base($cbd)) return false;

      $oms->escribe_exemplar($cbd);

      $pax->atr("id_opcion")->valor = $oms->atr("id_oms")->valor;

      if (!$pax->duplicar($cbd, $id_idioma, $refsite)) return false;


      $pax->atr("id_sincro")->valor = $pax->atr("id_paxina")->valor;

      if (($pax_ml_obd = $pax->ml_obd()) != null) if (!$pax_ml_obd->update($cbd)) return false;
      
      return true;
    }

//~ echo "{$id_idioma}:: oms->atr(id_oms)->valor != null<br />";

    if (!$pax->duplicar($cbd, $id_idioma, $refsite)) return false;


    return true;
  }

  private function plantilla_paxina_obd(FS_cbd $cbd) {
    $id_paxina = $this->paxdupli->atr("id_paxina")->valor;
    $tipo      = $this->paxdupli->atr("tipo"     )->valor;


    $pax = Paxina_obd::inicia($cbd, $id_paxina, $tipo);


    $pax->atr("nome"      )->valor = $this->obxeto("nome"      )->valor();
    $pax->atr("descricion")->valor = $this->obxeto("descricion")->valor();


    return $pax;
  }

  private static function __a_sincro(FS_cbd $cbd, $id_sincro) {
    $sql = "select id_idioma, id_paxina from v_ml_paxina where id_sincro = {$id_sincro}";

    $r = $cbd->consulta($sql);

    $a2 = null;
    while ($a = $r->next()) $a2[$a['id_idioma']] = $a['id_paxina'];


    return $a2;
  }

  private static function refsite(FS_cbd $cbd, int $id_site):?string {
    $sql = "select nome from site where id_site = {$id_site}";

    $r = $cbd->consulta($sql);


    if (!$_r = $r->next()) return null;
    

    return $_r['nome'];
  }

}

