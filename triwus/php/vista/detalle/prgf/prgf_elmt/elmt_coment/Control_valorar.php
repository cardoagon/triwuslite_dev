<?php

/*************************************************

    Triwus Framework v.0

    Control_valorar.php

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

final class Control_valorar extends Componente {
  const bmais    = "imx/elmt/comentarios/opinion-agree.png";
  const bmais_2  = "imx/elmt/comentarios/opinion-agree_2.png";
  const bmenos   = "imx/elmt/comentarios/opinion-disagree.png";
  const bmenos_2 = "imx/elmt/comentarios/opinion-disagree_2.png";

  private $id_puntos;
  private $anonimos;

  public function __construct(Puntuacion_obd $p, $anonimos, FGS_usuario $u = null) {
    parent::__construct("cValorar");

    $this->id_puntos = $p->atr("id_puntos")->valor;
    $this->anonimos  = $anonimos;

    $this->pon_obxeto(self::__textsumsup("txtsum", $p->atr("sum")->valor), $this->id_puntos);
    $this->pon_obxeto(self::__textsumsup("txtsup", $p->atr("sup")->valor), $this->id_puntos);

    $this->__uconfig($u);
  }

  public function __uconfig(FGS_usuario $u = null) {
    $this->visible = $u != null;

    if ($u == null) return;

    $txtsum = $this->obxeto("txtsum", $this->id_puntos)->html();
    $txtsup = $this->obxeto("txtsup", $this->id_puntos)->html();

    if ($u->puntuado($this->id_puntos)) {
      $bmais  = self::__button("bmais", "+&nbsp;1", $txtsum);
      $bmenos = self::__button("bmenos", "-&nbsp;1", $txtsup);

      $bmais->readonly = true;
      $bmenos->readonly = true;
    }
    else {
      $bmais  = self::__button("bmais", "<font color=green>+</font>&nbsp;1", $txtsum);
      $bmenos = self::__button("bmenos", "<font color=red>-</font>&nbsp;1", $txtsup);

      $bmais->pon_eventos("onclick", "elmt_comentarios_bmais(this)");
      $bmenos->pon_eventos("onclick", "elmt_comentarios_bmenos(this)");
    }

    $this->pon_obxeto($bmais, $this->id_puntos);
    $this->pon_obxeto($bmenos, $this->id_puntos);
  }

  public function id_puntos() {
    return $this->id_puntos;
  }

  public function html():string {
    if (!$this->visible) return "";

    if (($bmenos = $this->obxeto("bmenos", $this->id_puntos)) == null) return "";
    if (($bmais  = $this->obxeto("bmais", $this->id_puntos))  == null) return "";


    return self::__ptw($bmenos->html(), $bmais->html());
  }

  public static function operacion_puntuar(Efs $e, $suma) {
    $id_puntos = $e->evento()->subnome(0);

    $e->usuario()->puntuar($id_puntos, $suma);

    return $e;
  }

  public function preparar_saida(Ajax $a = null) {
    $bmais = Panel_fs::__bimage("bmais", Control_valorar::bmais_2, null, true, $this->obxeto("txtsum", $this->id_puntos)->html());
    $bmenos = Panel_fs::__bimage("bmenos", Control_valorar::bmenos_2, null, true, $this->obxeto("txtsup", $this->id_puntos)->html());

    $this->pon_obxeto($bmais, $this->id_puntos);
    $this->pon_obxeto($bmenos, $this->id_puntos);

    $a->pon($this->obxeto("bmais", $this->id_puntos));
    $a->pon($this->obxeto("bmenos", $this->id_puntos));

  }

  private static function __ptw($bmenos, $bmais) {
    return "<table border=0 cellspacing=0 cellpadding=0>
              <tr>
                <td>{$bmais}</td>
                <td>&nbsp;</td>
                <td>{$bmenos}</td>
              </tr>
            </table>";
  }

  private static function __textsumsup($id, $v) {
    $t = new Text($id, true);

    $t->size = 1;
    $t->maxlength = 4;

    $t->post($v);

    $t->clase_css("readonly", "coments_txtsumsup");

    return $t;
  }

  private static function __button($nome, $ico, $etq) {
    $ico = "<span class='coments_icosumsup'>{$ico}</span>";

    $etq = "<table border=0 cellspacing=0 cellpadding=0><tr><td>{$ico}</td><td width='23px;'>{$etq}</td></tr></table>";

    $b = Panel_fs::__button($nome, $etq);

    $b->clase_css("default" , "boton_apagado");
    $b->clase_css("readonly" , "boton_apagado");

    $b->pon_eventos("onmouseover", "this.className = 'boton_apagado_over'");
    $b->pon_eventos("onmouseout", "this.className = 'boton_apagado'");

    return $b;
  }
}

