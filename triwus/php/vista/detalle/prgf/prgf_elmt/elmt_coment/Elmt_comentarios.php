<?php

/*************************************************

    Triwus Framework v.0

    Elmt_comentarios.php

    Author: Carlos Domingo Arias Gonz&aacute;lez

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

final class Elmt_comentarios extends AElemento {
  public $avisar;
  public $avisar_email;
  public $baixa;
  public $anonimos;
  public $denunciar;
  public $moderacion;
  public $pechar;
  public $valorar;

  private $u;

  public function __construct(Comentarios_obd $pe_obd) {
    parent::__construct($pe_obd);

    $this->ptw        = Refs::url("ptw/paragrafo/elmt/comentarios/elmt_comentarios.html");

    $this->avisar       = $pe_obd->atr("avisar")->valor;
    $this->avisar_email = $pe_obd->atr("avisar_email")->valor;
    $this->anonimos     = $pe_obd->atr("anonimos")->valor;
    $this->moderacion   = $pe_obd->atr("moderacion")->valor;
    $this->denunciar    = $pe_obd->atr("denunciar")->valor;
    $this->baixa        = $pe_obd->atr("baixa")->valor;
    $this->pechar       = $pe_obd->atr("pechar")->valor;
    $this->valorar      = $pe_obd->atr("valorar")->valor;

    $this->pon_obxeto(new FSL_coments($this));

    $this->pon_obxeto(new CComentar($pe_obd));

    $this->pon_obxeto(new CAccesoWeb_coments());
  }

  public function config_lectura() {
    parent::config_lectura();

    $this->obxeto("ccomentar")->config_lectura();

    $this->obxeto("tcomentsdet")->readonly(true);
    $this->obxeto("tcomentsdet")->__where($this->obxeto("ccomentar")->obxeto("cab")->sql_where());
  }

  public function config_edicion() {
    parent::config_edicion();

    $this->obxeto("ccomentar")->config_edicion();

    $this->obxeto("tcomentsdet")->readonly(false);
    $this->obxeto("tcomentsdet")->__where($this->obxeto("ccomentar")->obxeto("cab")->sql_where());
  }

  public function config_usuario(IEfspax_xestor $e = null) {
    $u = $e->__site_obd()->usuario_obd(); //*usuario xestor do site.

    if ($this->avisar_email == null) $this->avisar_email = $u->atr("email")->valor;

    return $this->obxeto("tcomentsdet")->usuario($e->usuario()); //* usuario cliente ou visitante
  }

  public function operacion(EstadoHTTP $e) {
    if (($e_aux = $this->obxeto("cacceso")->operacion($e)) != null) return $e_aux;

    if (($e_aux = $this->obxeto("ccomentar")->operacion($e)) != null) {
      $this->preparar_saida($e_aux->ajax());

      return $e_aux;
    }

    if (($e_aux = $this->obxeto("tcomentsdet")->operacion($e)) != null) {
      $this->preparar_saida($e_aux->ajax());

      return $e_aux;
    }

    return parent::operacion($e);
  }

  public function operacion_cacceso(Efs $e) {
    $this->obxeto("cacceso")->visible = true;

    return $e;
  }

  public function elmt_obd() {
    $e = new Comentarios_obd();

    $e->atr("avisar")->valor       = ($this->avisar)?"1":"0";
    $e->atr("avisar_email")->valor = $this->avisar_email;
    $e->atr("baixa")->valor        = ($this->baixa)?"1":"0";
    $e->atr("denunciar")->valor    = ($this->denunciar)?"1":"0";
    $e->atr("anonimos")->valor     = ($this->anonimos)?"1":"0";
    $e->atr("moderacion")->valor   = ($this->moderacion)?"1":"0";
    $e->atr("pechar")->valor       = ($this->pechar)?"1":"0";
    $e->atr("valorar")->valor      = ($this->valorar)?"1":"0";

    return $this->obd_base($e);
  }

  public function editor() {
    return new Editor_ecoments($this);
  }

  public function html_propio() {
    return $this->html00();
  }

}

//****************************

class CAccesoWeb_coments extends Componente {
  const ptw_0 = "ptw/paragrafo/elmt/comentarios/ccomentar_caccesoweb.html";

  public function __construct() {
    parent::__construct("cacceso", Refs::url(self::ptw_0));

    $this->visible = false;

    $this->pon_obxeto(Panel_fs::__button("brexistro", "Reg&iacute;strate"));
    $this->pon_obxeto(Panel_fs::__button("blogin", "Acceso / Login"));
  }

  public function operacion(EstadoHTTP $e) {
    if ($this->obxeto("blogin")->control_evento()) return $this->operacion_blogin($e);

    if ($this->obxeto("brexistro")->control_evento()) return $this->operacion_brexistro($e);

    return null;
  }

  public function operacion_blogin(IEfspax_xestor $e) {
    $id_site = $e->__site_obd()->atr("id_site")->valor;

    $e->obxeto("detalle")->activar_mensaxe(new CAccesoWeb($id_site, $e->usuario()), true);

    $this->visible = false;

    return $e;
  }

  public function operacion_brexistro(IEfspax_xestor $e) {
    $id_site = $e->__site_obd()->atr("id_site")->valor;

    $e->obxeto("detalle")->activar_mensaxe(new CRexistroWeb($id_site), true);

    $this->visible = false;

    return $e;
  }
}

?>
