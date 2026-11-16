<?php

final class CPaxsup extends Componente {
  const ptw_0 = "ptw/xpax/cpaxsup_0.html";

  private $paxsup = null;

  public function __construct(FGS_usuario $u) {
    parent::__construct("cpaxsup", self::ptw_0);
    
    $this->visible = false;

    $this->pon_obxeto(new CLista_idiomas($u, "ptw/xpax/cpaxsup_lidiomas.html"));

    $this->pon_obxeto(Panel_fs::__text("nome", 22, 99, "... el t&iacute;tulo de la p&aacute;gina"));
    $this->pon_obxeto(new Textarea("descricion", 2, 22));

    $this->pon_obxeto(Panel_fs::__baceptar());
    $this->pon_obxeto(Panel_fs::__bcancelar());

    $this->pon_obxeto(new Param("pnome"));

    $this->pon_obxeto(self::__perro("perro_l"));


    $this->obxeto("nome"      )->readonly = true;
    $this->obxeto("descricion")->readonly = true;
    
    $this->obxeto("nome"      )->style("readonly", "width:93%;");
    $this->obxeto("descricion")->style("readonly", "width:93%;height:99px;");


    $this->obxeto("bAceptar" )->envia_ajax("onclick");
    $this->obxeto("bCancelar")->envia_ajax("onclick");
  }

  public function operacion(EstadoHTTP $e) {
    $this->obxeto("perro_l")->post("");

    if ($this->obxeto("bAceptar" )->control_evento()) $this->operacion_bAceptar($e);
    if ($this->obxeto("bCancelar")->control_evento()) $this->operacion_bCancelar($e);


    return parent::operacion($e);
  }

  public function operacion_bsup(Epcontrol $e, $id_paxina, $ref = false) {
    $cbd = new FS_cbd();

    $this->paxsup = ($ref)?Paxina_ref_obd::inicia($cbd, $id_paxina):Paxina_obd::inicia($cbd, $id_paxina);

    $this->obxeto("nome"      )->post( $this->paxsup->atr("nome")->valor );
    $this->obxeto("pnome"     )->post( $this->paxsup->atr("nome")->valor );
    $this->obxeto("descricion")->post( $this->paxsup->atr("descricion")->valor );

    $this->pon_obxeto(new CLista_idiomas($e->usuario(), "ptw/xpax/cpaxsup_lidiomas.html"));

    $this->obxeto("lidiomas")->reset();
    $this->obxeto("lidiomas")->activar( $this->paxsup->atr("id_idioma")->valor );

    $e = $this->pai->op_config_sup($e);

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
      $this->obxeto("perro_l")->post($erro['l']);

      $this->preparar_saida($e->ajax());

      return $e;
    }


    $cbd = new FS_cbd();

    $cbd->transaccion();

    
    if ($this->paxsup->atr("tipo")->valor == "novas") {
      if (!$this->paxsup->__indice()) {
        if ($this->borra_1($cbd)) return $this->operacion_bAceptar_OK($e, $cbd);

        return $this->operacion_bAceptar_KO($e, $cbd);
      }
    }

/*
    if ($this->paxsup->atr("tipo")->valor == "ventas") {
      if (!$this->paxsup->__indice()) {
        if ($this->borra_2($cbd)) return $this->operacion_bAceptar_OK($e, $cbd);

        return $this->operacion_bAceptar_KO($e, $cbd);
      }
    }
*/
    
    if ($this->paxsup->atr("tipo")->valor == "novas2") {
      if (!$this->paxsup->__indice()) {
        if ($this->borra_3($cbd)) return $this->operacion_bAceptar_OK($e, $cbd);

        return $this->operacion_bAceptar_KO($e, $cbd);
      }
    }


    if ($this->borra_0($cbd)) return $this->operacion_bAceptar_OK($e, $cbd);

    return $this->operacion_bAceptar_KO($e, $cbd);
  }

  private function validar() {
    $erro = null;

    if ($this->obxeto("lidiomas")->a_checks() == null) $erro['l'] = "Debe seleccionar al menos un idioma";

    return $erro;
  }

  private function operacion_bAceptar_OK(Epcontrol $e, FS_cbd $cbd) {
    $cbd->commit();
    
    $this->visible = false;

    $this->pai->pon_obxeto(Paxinfo::inicia(null));

    return $this->operacion_bCancelar($e);
  }

  private function operacion_bAceptar_KO(Epcontrol $e, FS_cbd $cbd) {
    $cbd->rollback();

    $this->preparar_saida($e->ajax());

    return $e;
  }

  private function borra_0(FS_cbd $cbd) {
    //* borra oms e paxina.

    $oms = Opcion_ms_obd::inicia($cbd, $this->paxsup->atr("id_opcion")->valor);

    $a_idiomas = $this->obxeto("lidiomas")->a_checks();

    foreach ($a_idiomas as $id_idioma)
      if (!$oms->delete($cbd, $id_idioma, true, -1)) return false;


    return true;
  }

  private function borra_1(FS_cbd $cbd) {
    //* borra paxinas de tipo 'novas' se (id_prgf_indice == null)

    $a_idiomas = $this->obxeto("lidiomas")->a_checks();

    if (count($a_idiomas) < 2) return $this->borra_1b($cbd, $this->paxsup);
    

    $id_sincro = $this->paxsup->atr("id_sincro")->valor;

    foreach ($a_idiomas as $id_idioma) {
      $sql = "select id_paxina from v_ml_paxina where id_sincro = {$id_sincro} and id_idioma = '{$id_idioma}'";

      $r = $cbd->consulta($sql);

      if (!$a = $r->next()) continue;

      $pax = Paxina_obd::inicia($cbd, $a['id_paxina'], 'novas');

      if (!$this->borra_1b($cbd, $pax)) return false;
    }

    return true;
  }

  private function borra_1b(FS_cbd $cbd, Paxina_obd $pax) {
    $elmt_novas = $pax->elmt_novas($cbd);


    if ($elmt_novas == null) return $pax->delete($cbd);
    
    return $elmt_novas->delete($cbd);
  }

/*
  private function borra_2(FS_cbd $cbd) {
    //* borra paxinas de tipo 'ventas' e (id_prgf_indice == null)

    $id_sincro = $this->paxsup->atr("id_sincro")->valor;

    $a_idiomas = $this->obxeto("lidiomas")->a_checks();

    foreach ($a_idiomas as $id_idioma) {
      $sql = "select id_paxina from v_ml_paxina where id_sincro = {$id_sincro} and id_idioma = '{$id_idioma}'";

      $r = $cbd->consulta($sql);

      if (!$a = $r->next()) continue;

      $pax = Paxina_obd::inicia($cbd, $a['id_paxina'], "ventas");

      $elmt_vetas = $pax->elmt_ventas($cbd);

      if ($elmt_vetas == null) {
        if (!$pax->delete($cbd)) return false;
      }
      elseif (!$elmt_vetas->delete($cbd)) return false;
    }

    return true;
  }
*/

  private function borra_3(FS_cbd $cbd) {
    //* borra paxinas de tipo 'novas2' se (id_prgf_indice == null)

    $a_idiomas = $this->obxeto("lidiomas")->a_checks();
    $id_sincro = $this->paxsup->atr("id_sincro")->valor;

    foreach ($a_idiomas as $id_idioma) {
      $sql = "select id_paxina from v_ml_paxina where id_sincro = {$id_sincro} and id_idioma = '{$id_idioma}'";

      $r = $cbd->consulta($sql);

      if (!$a = $r->next()) continue;

      $pax = Paxina_obd::inicia($cbd, $a['id_paxina'], 'novas2');

      if (!$pax->delete($cbd)) return false;
    }

    return true;
  }

  private static function __perro($id) {
    $d = new Div($id);

    $d->clase_css("default", "texto3_erro");

    return $d;
  }
}

