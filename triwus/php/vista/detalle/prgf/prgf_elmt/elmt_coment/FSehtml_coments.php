<?php

/*************************************************

    Triwus Framework v.0

    FSehtml_coments.php

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

final class FSehtml_coments extends FS_ehtml {
  const batencion     = "imx/elmt/comentarios/bAtencion.png";

  public function __construct() {
    parent::__construct();

    $this->border = 0;

    $this->align = "center";
    $this->width = "97%";
    $this->cellpadding = 0;
    $this->cellspacing = 0;
  }

  public function usuario(FGS_usuario $usuario = null) {
    if ($usuario == null) return $this->usuario;

    $this->usuario = $usuario;
  }

  protected function cabeceira($df = null) {
    return "";
  }

  protected function linha_detalle($df, $f) {
    if ((($baixa = $f['baixa']) == 1) && ($this->readonly)) return "";

    $id_elmt = $f['id_elmt'];
    $id_ecd  = $f['id_ecd'];

                   //~ <td align=right>" . self::__idecd($this, $id_ecd, $id_ecd, $f['id_puntos'], $f['sum'], $f['sum']) . "</td>

    return "<tr height=11px><td></td></tr>
            <tr>
              <td>
                <table align=center width=100% border=0 cellspacing=1 cellpadding=0
                       class='coments_titulo'>
                  <tr>
                    <td width='55%'>" . self::__idecd($this, $id_ecd, $f['firma'], $f['email'], $f['momento'], $baixa) . "</td>
                    <td width='1px'>" . self::__bbloquear($this, $id_elmt, $id_ecd, $baixa) . "</td>
                    <td align=right>" . self::__cvalorar($this, $id_ecd, $id_ecd, $f['id_puntos'], $f['sum'], $f['sum']) . "</td>
                  </tr>
                </table>
              </td>
            </tr>

            <tr>
              <td style='padding: 3px 17px 3px 17px;'>
                <table width=100% border=0 cellspacing=0 cellpadding=0>
                  <tr valign=top height=84px>
                    <td align=center>" . self::__comentario($this, $id_ecd, $f['txt'], $baixa) . "</td>
                  </tr>
                  <tr>
                    <td align=center>
                        " . self::__bmoderar($this, $id_elmt, $id_ecd, $baixa, $f['moderado']) . "
                        " . self::__bdenunciar($this, $id_elmt, $id_ecd, $baixa, $f['denunciado']) . "
                    </td>
                  </tr>
                </table>
              </td>
            </tr>";
  }

  private static function __idecd(FSehtml_coments $fsl, $id_ecd, $firma, $email, $momento, $baixa) {
    //~ return "#<b>{$firma}</b>{$email}&nbsp;&bull;&nbsp;{$momento}";
    return "#<b>{$firma}</b>&nbsp;&bull;&nbsp;{$momento}";
  }

  private static function __comentario(FSehtml_coments $fsl, $id_ecd, $txt, $baixa) {
    $css = ($baixa == 1)?"coments_txt_bloqueado":"coments_txt";

    $ta = new Textarea("tacomentario", 1, 1);

    $ta->readonly = true;

    $ta->post($txt);

    $ta->clase_css("readonly", $css);

    if ($baixa == 1) $ta->title = "Este comentario est&aacute; bloqueado.";

    return $ta->html();
  }

  private static function __cvalorar(FSehtml_coments $fsl, $id_elmt, $id_ecd, $id_puntos, $sum, $sup) {
    return "";
    /*
//~ echo $fsl->xestor->usuario->atr("email")->valor . "::{$id_puntos}::jjjjjjjjjjjjjjj<br>";

    if ($fsl->xestor->usuario() == null) return "ostia";

    if (!$fsl->xestor->valorar) return "";
//~ echo "ssssssssssss";

    $p = new Puntuacion_obd();

    $p->atr("id_puntos")->valor = $id_puntos;
    $p->atr("sum"      )->valor = $sum;
    $p->atr("sup"      )->valor = $sup;

    $cvalorar = new Control_valorar($p, $fsl->xestor->anonimos, $fsl->xestor->usuario);


    $fsl->__fslControl($cvalorar->obxeto("bmais", $id_puntos));
    $fsl->__fslControl($cvalorar->obxeto("bmenos", $id_puntos));

    return $cvalorar->html();
    */
  }

  private static function __bdenunciar(FSehtml_coments $fsl, $id_elmt, $id_ecd, $baixa, $denunciado) {
    if (!$fsl->xestor->denunciar) return "";

    if ($denunciado == 1) {
      $id = "bbloquear_des";
      $txt = "<img src='" . Refs::url(self::batencion) . "' height=12.1px width=12.1px />&nbsp;Este comentario ha sido <b>denunciado</b> por un internauta, revisar el correo electrónico asunto: XXXXXX, para conocer motivaci&oacute;n.";
    }
    else {
      $id = "bDenunciar";
      $txt = "<b>Denunciar</b> comentario inapropiado";
    }

    $b = Panel_fs::__link($id, $txt);

    $b->envia_AJAX("onclick");

    return $fsl->__fslControl($b, $id_ecd)->html();
  }

  private static function __bmoderar(FSehtml_coments $fsl, $id_elmt, $id_ecd, $baixa, $moderado) {
    if (!$fsl->xestor->moderacion) return "";

    if ($moderado == 1) return "";

    $id = "bbloquear_des";
    $txt = "<img src='" . Refs::url(self::batencion) . "' height=17px width=17px />&nbsp;Este comentario est&aacute; <b>pendiente de moderaci&oacute;n</b>. Pincha aqu&iacute; para publicar el comentario.";

    $b = Panel_fs::__link($id . Escritor_html::csubnome . $id_ecd, $txt);

    $b->envia_AJAX("onclick");

    return $fsl->__fslControl($b)->html();
  }

  private static function __bbloquear(FSehtml_coments $fsl, $id_elmt, $id_ecd, $baixa) {
    if ($fsl->xestor->readonly) return "";

    if ($baixa == 1) {
      $id = "bbloquear_des";
      //~ $src = self::bbloquear_des;
      $title = "Desbloquear";
    }
    else {
      $id = "bbloquear";
      //~ $src = self::bbloquear;
      $title = "Bloquear";
    }

    $b = Panel_fs::__link($id, "{$title}&nbsp;el&nbsp;comentario", "texto2_a");

    $b->envia_AJAX("onclick");

    return $fsl->__fslControl($b, $id_ecd)->html();
  }
}

?>