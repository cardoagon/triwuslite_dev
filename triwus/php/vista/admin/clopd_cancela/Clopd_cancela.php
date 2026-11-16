<?php

/*************************************************

    Tilia Framework v.0

    Clopd_cancela.php

    Author: Carlos Domingo Arias González

    Copyright (C) 2011

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation version 3.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.

*************************************************/


final class Clopd_cancela extends Componente {
  const ptw_0 = "ptw/clopd_cancela/site_0.html";
  const ptw_1 = "ptw/clopd_cancela/site_1.html";
  const ptw_2 = "ptw/clopd_cancela/site_2.html";

  private $id_site = null;

  public function __construct(FGS_usuario $u) {
    parent::__construct("clopd_cancela", self::ptw_0);

    $this->pon_obxeto(new Param("prulhome", Refs::url_home()));
    $this->pon_obxeto(new Param("pcustodia"));
    $this->pon_obxeto(new Param("pemail"));
    $this->pon_obxeto(new Param("pbaixa"));
    $this->pon_obxeto(new Param("pfin"));

    $this->pon_obxeto(self::__lcancelar0());
    $this->pon_obxeto(self::__lcancelar1());
    $this->pon_obxeto(self::__lcancelar2());
    $this->pon_obxeto(self::__lcancelar3());

    $this->inicia($u);
  }

  public function operacion(EstadoHTTP $e) {
    if ($this->obxeto("lcancelar0")->control_evento()) {
      $this->ptw = self::ptw_1;

      $this->preparar_saida($e->ajax());

      return $e;
    }

    if ($this->obxeto("lcancelar1")->control_evento()) {
      $this->ptw = self::ptw_0;

      $this->preparar_saida($e->ajax());

      return $e;
    }

    if ($this->obxeto("lcancelar2")->control_evento()) {
      if (!LOPDcancela_obd::cancela(new FS_cbd(), $this->id_site)) return $this;

      return $this->__reinicia_estado($e);
    }

    if ($this->obxeto("lcancelar3")->control_evento()) {
      if (!LOPDcancela_obd::cancela(new FS_cbd(), $this->id_site, "null")) return $this;

      return $this->__reinicia_estado($e);
    }

    return null;
  }

  private function inicia(FGS_usuario $u) {
    //~ $this->id_site = $u->atr("id_site")->valor;

    $cbd = new FS_cbd();


    if ($u->en_custodia()) {
      $this->ptw = self::ptw_2;

      //~ $a = $lopdc->atr("baixa")->a_data();
      //~ $this->obxeto("pbaixa")->post("{$a['d']}-{$a['m']}-{$a['Y']}");

      //~ $a = $lopdc->atr("fin")->a_data();
      //~ $this->obxeto("pfin")->post("{$a['d']}-{$a['m']}-{$a['Y']}");
    }
    else
      $this->ptw = self::ptw_0;

    $this->obxeto("pemail")->post($u->atr("email")->valor);
    //~ $this->obxeto("pcustodia")->post($u->atr("custodia")->valor);
  }

  private function __reinicia_estado(Epcontrol $e) {
    $e = new Epcontrol($e->usuario());

    $e->panel("centro")->activar_pestanha($e, "Usuario");

    return $e;
  }

  private static function __lcancelar0() {
    $l = Panel_fs::__link("lcancelar0", "pincha aqu&iacute;", "texto2_a", "texto2_a");

    $l->envia_ajax("onclick");

    return $l;
  }

  private static function __lcancelar1() {
    $l = Panel_fs::__bimage("lcancelar1", Ico::bcancel, "Deseo &laquo; mantener &raquo; mi cuenta de usuario en " . Refs::url_home(), true, "&nbsp;Mantener");

    $l->envia_ajax("onclick");

    return $l;
  }

  private static function __lcancelar2() {
    $l = Panel_fs::__bimage("lcancelar2", Ico::bapply,  "Deseo &laquo; cancelar &raquo; mi cuenta de usuario en " . Refs::url_home(), true, "&nbsp;Cancelar");

    $l->envia_submit("onclick", "&iquest; Quieres cancelar tu cuenta de usuario ?", "ay0ms9tnqo83023lkd");

    return $l;
  }

  private static function __lcancelar3() {
    $l = Panel_fs::__link("lcancelar3", "pincha aqu&iacute;", "texto2_a", "texto2_a");

    $l->envia_submit("onclick", "&iquest; Quieres recuperar tu cuenta de usuario ?", "ay0ms9tnqo83023lkd");

    return $l;
  }
}

?>
