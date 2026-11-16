<?php

/** Componente Tilia Framework, que permite seleccionar datas dunha forma máis sinxela.
 *  Permite escribir datas gregorianas dende o 01-01-0001 a 31-12-2499.
 *  Este componente é multilinguaxe.
 *
 * @package tilia\vista\componentes\calendario
 */


class Calendario extends Componente {

  protected $mostrar_data = true;

  protected $usar_capa = true;

  protected $dias_significativos = null;

  protected $dias_descanso = null;

  public $dia = null;

  public $mes = null;

  public $ano = null;

  protected $formato_saida;

  public function __construct($nome, Idioma $idioma, $ano = null, $mes = null, $dia = null) {
    parent::__construct($nome);

    Cal_html::inicia_clasesCSS($this);

    $this->pon_data($dia, $mes, $ano);

    $this->formato_saida = $this->idioma->formato_fecha;

    $this->pon_obxeto(new Cal_capa($this));

    $this->pon_obxeto(new Cal_ctData($this, $this->readonly));

    $this->pon_obxeto(new Cal_bPechar());

    $this->pon_obxeto(new Cal_bBorrar($this->readonly));

    $this->pon_obxeto(new Cal_enlace("e_msig", "&raquo;"));
    $this->pon_obxeto(new Cal_enlace("e_mant", "&laquo;"));

    $pos = new Cal_pos();
    do {
      $this->pon_obxeto(new Cal_dia($this->readonly), $pos->sufixo());
    }
    while ($pos->seguinte());


    $this->pon_idioma(($idioma == null)?new Galego():$idioma);

  }

  public function declara_css() {
    return array(Tilia::home() . "css/calendario.css");
  }

  public function pon_idioma(Idioma $idioma) {
    $this->idioma = $idioma;
    $this->formato_saida = $idioma->formato_fecha;

    $this->obxeto("ct_data")->post($this->colle_strdata());
  }

  public function pon_diaSignificativo($ano, $mes, $dia, $color = null, $titulo = null) {
    if ($color == null) {
      unset($this->dias_significativos[$ano][$mes][$dia]);

      return;
    }

    $this->dias_significativos[$ano][$mes][$dia]['color'] = $color;
    $this->dias_significativos[$ano][$mes][$dia]['titulo'] = $titulo;
  }

  public function pon_diaDescanso($dia, $color = null, $titulo = null) {
    if ($color == null) {
      unset($this->dias_descanso[$dia]);

      return;
    }

    $this->dias_descanso[$dia]['color'] = $color;
    $this->dias_descanso[$dia]['titulo'] = $titulo;
  }

  public function colle_strdata($formato = null, $ano = null, $mes = null, $dia = null) {
    if ($formato == null) $formato = $this->formato_saida;
    if ($ano == null) $ano = $this->ano;
    if ($mes == null) $mes = $this->mes;
    if ($dia == null) $dia = $this->dia;

    return Cal_loxica::formatear_saida($ano, $mes, $dia, $formato);
  }

  public function formato_saida($formato = null) {
    if ($formato == null) return $this->formato_saida;


    $this->formato_saida = $formato;
  }

  public function operacion(EstadoHTTP $e) {
    $evento = $e->evento();

    if ($evento->control_ohttp($this->obxeto("ct_data")) == 1) {
        if ($evento->tipo() == "onclick") {
          $this->obxeto("ct_data")->config_2($this);

          $this->mostrar_data = false;
        }
        else
          Cal_loxica::validar($this);

        return $e;
    }
    if ($evento->control_ohttp($this->obxeto("e_pechar")) == 1) {
        $this->obxeto("ct_data")->config_1($this);

        $this->mostrar_data = true;

        return $e;
    }
    if ($evento->control_ohttp($this->obxeto("e_borrar")) == 1) {
        $this->ano = null;
        $this->mes = null;
        $this->dia = null;

        return $e;
    }
    if ($evento->control_ohttp($this->obxeto("e_msig")) == 1) {
        $this->obxeto("ct_data")->post($this->obxeto("e_msig")->title);

        Cal_loxica::validar($this);

        if ($this->ano != date("Y")) return $e;
        if ($this->mes != date("m")) return $e;

        $this->dia = date("d");

        $this->obxeto("ct_data")->post($this->colle_strdata());

        return $e;
    }
    if ($evento->control_ohttp($this->obxeto("e_mant")) == 1) {
        $this->obxeto("ct_data")->post($this->obxeto("e_mant")->title);

        Cal_loxica::validar($this);

        if ($this->ano != date("Y")) return $e;
        if ($this->mes != date("m")) return $e;

        $this->dia = date("d");

        $this->obxeto("ct_data")->post($this->colle_strdata());

        return $e;
    }
    if ($evento->control_str($this->nome_completo() . self::cnome . "dia_mes") == 1) {
        $subnome = $evento->subnome();

        $this->dia = $this->obxeto("dia_mes", $subnome[0])->valor();

        $this->obxeto("ct_data")->post($this->colle_strdata());

        return $e;
    }

    return null;
  }

  public function pon_data($dia = null, $mes = null, $ano = null) {
    if (($dia == null) && ($mes == null) && ($ano == null)) {
      $this->dia = null;
      $this->mes = null;
      $this->ano = null;

      return;
    }

    if ($dia != null) $this->dia = $dia;
    if ($mes != null) $this->mes = $mes;
    if ($ano != null) $this->ano = $ano;
  }

  public function pon_strdata($str_data = null, $formato = null) {
    $this->ano = null;
    $this->mes = null;
    $this->dia = null;

    if ($str_data == null) return;

    if ($formato == null) $formato = $this->formato_saida;

    //* creamos el patron de la fecha.
    $patron = str_replace(array("Y", "m", "d"), array("yyyy", "mm", "dd"), $formato);

    for($i = 0; $i < strlen($patron); $i++) {
      if ($patron[$i] == "y") $this->ano .= $str_data[$i];
      elseif ($patron[$i] == "m") $this->mes .= $str_data[$i];
      elseif ($patron[$i] == "d") $this->dia .= $str_data[$i];
    }
  }

  public function html():string {
    if (!$this->visible) return "";

    if ($this->mostrar_data) return Cal_html::mostrar_data($this);

    if ($this->obxeto("ct_data")->valor() == null) {
      $this->obxeto("ct_data")->post(date($this->formato_saida));

      Cal_loxica::validar($this);
    }

    return Cal_html::matriz($this);
  }
}

/** Encapsula a l&oacute;xica de operaci&oacute;ns con datas
 *
 * @package tilia\vista\componentes\calendario
 */

final class Cal_loxica extends Calendario {
  private static $m = array(0, 3, 3, 6, 1, 4, 6, 2, 5, 0, 3, 5);
  private static $s = array("00991231"=>4, "01991231"=>3, "02991231"=>2, "03991231"=>1, "04991231"=>0,
                            "05991231"=>6, "06991231"=>5, "07991231"=>4, "08991231"=>3, "09991231"=>2,
                            "10991231"=>1, "11991231"=>0, "12991231"=>6, "13991231"=>5, "14991231"=>4,
                            "15821004"=>3, "16821015"=>-1, "15991231"=>0, "16991231"=>6, "17991231"=>4,
                            "18991231"=>2, "19991231"=>0, "20991231"=>6, "21991231"=>4, "22991231"=>2,
                            "23991231"=>0, "24991231"=>6);

  protected static function validar(Calendario $cal) {
    $ct_data = $cal->obxeto("ct_data");
    $s_data = trim($ct_data->valor());

    if ($s_data == "") return;

    $sep = self::separadores($s_data);
    if (is_numeric($sep)) {
      $ct_data->config_2_erro($cal);

      return;
    }

    $amd = self::lista_amd($s_data, $sep, $cal->idioma->formato_fecha);

    if (self::s($amd[0], $amd[1], $amd[2]) < 0) {
      $ct_data->config_2_erro($cal);

      return;
    }

    if (!checkdate($amd[1], $amd[2], $amd[0])) {
      $ct_data->config_2_erro($cal);

      return;
    }

    $cal->pon_data($amd[2], $amd[1], $amd[0]);

    $ct_data->post($cal->colle_strdata());

    $ct_data->config_2($cal);
  }

  private static function separadores($data) {
    $sep = null;
    $ct_sep = 1;
    for ($i = 0; ($i < strlen($data)) && ($ct_sep < 3); $i++) {
      if (!is_numeric($data[$i])) {
        if ($sep == null) $sep = $data[$i];
        elseif ($sep == $data[$i]) $ct_sep++;
        elseif ($sep != $data[$i]) return -1;
      }
    }

    if ($ct_sep != 2) return -2;

    return $sep;
  }

  private static function lista_amd($data, $separador, $formato_entrada) {
    $formato_entrada = self::valida_formato_entrada($formato_entrada);

    if ($formato_entrada == "Ymd") list($a, $m, $d) = explode($separador, $data);
    elseif ($formato_entrada == "dmY") list($d, $m, $a) = explode($separador, $data);
    elseif ($formato_entrada == "mdY") list($m, $d, $a) = explode($separador, $data);

    return array($a, $m, $d);
  }

  private static function valida_formato_entrada($formato_entrada) {
    $fe_aux = "";

    for ($i = 0; $i < strlen($formato_entrada); $i++)
      if (($formato_entrada[$i] == "Y") || ($formato_entrada[$i] == "m") || ($formato_entrada[$i] == "d")) $fe_aux .= $formato_entrada[$i];

    if ($fe_aux == "Ymd");
    elseif ($fe_aux == "dmY");
    elseif ($fe_aux == "mdY");
    else die("Cal_loxica.valida_formato_entrada(), formato de entrada de data, '{$formato_entrada}', NON valido.<br>");

    return $fe_aux;
  }

  protected static function formatear_saida($ano, $mes, $dia, $formato) {
    if (($ano == null) && ($mes == null) && ($dia == null)) return null;

    while (strlen($ano) < 4) $ano = "0{$ano}";
    while (strlen($mes) < 2) $mes = "0{$mes}";
    while (strlen($dia) < 2) $dia = "0{$dia}";

    return str_replace(array("Y", "m", "d"), array($ano, $mes, $dia), $formato);
  }

  public static function msig(Calendario $cal) {
    $ano = $cal->ano;
    $mes = ($cal->mes + 1) % 12;

    if ($mes == 0) $mes = 12;
    elseif($mes == 1) $ano++;

    $dia = 1;

    return $cal->colle_strdata(null, $ano, $mes, $dia);
  }

  public static function mant(Calendario $cal) {
    $ano = $cal->ano;
    $mes = ($cal->mes - 1) % 12;

    if ($mes == 0) {
      $mes = 12;

      $ano--;
    }

    $dia = 1;

    return $cal->colle_strdata(null, $ano, $mes, $dia);
  }

  public static function dia_semana(Calendario $cal, $dia = "01") {
    $a = substr($cal->ano, 2);
    $m = self::$m[$cal->mes - 1];
    $d = $dia;
    $e = (int)($a / 4);
    $s = self::s($cal->ano, $cal->mes, $dia);

    $dias = $d + $m + $a + $e + $s;

    $dias = $dias - $cal->idioma->primerDiaSemana;

    return $dias % Cal_pos::j_max;
  }

  public static function ultimo_dia_mes(Calendario $cal) {
    //* devolve o ultimo dia dun mes e anos dados.

    for ($ultimo_dia = 28; checkdate($cal->mes, $ultimo_dia + 1, $cal->ano); $ultimo_dia++);

    return $ultimo_dia;
  }

  private static function s($ano, $mes, $dia) {
    while (strlen($ano) < 4) $ano = "0{$ano}";
    if (strlen($mes) < 2) $mes = "0{$mes}";
    if (strlen($dia) < 2) $dia = "0{$dia}";

    $k = "{$ano}{$mes}{$dia}";

    foreach (self::$s as $ki=>$si) if ($k <= $ki) return $si;

    return -2;
  }
}

/** Clase que define unha posici&oacute;n nun calendario
 *
 * @package tilia\vista\componentes\calendario
 */

final class Cal_pos {
  const i_max = 6;
  const j_max = 7;

  public $i;
  public $j;

  public function __construct($i = 0, $j = 0) {
    $this->i = $i;
    $this->j = $j;
  }

  public function compara(Cal_pos $pos) {
    $pij_1 = ($this->i * self::j_max) + $this->j;
    $pij_2 = ($pos->i * self::j_max) + $pos->j;

    if ($pij_1 < $pij_2) return -1;
    elseif ($pij_1 > $pij_2) return 1;
    else return 0;
  }

  public function sufixo() {
    return "{$this->i}_{$this->j}";
  }

  public function seguinte($n = 1) {
    $pos = $this->suma($n);

    $this->i = $pos->i;
    $this->j = $pos->j;

    if (($this->i == 0) && ($this->j == 0)) return false;

    return true;
  }

  public function anterior($n = 1) {
    return $this->seguinte($n * -1);
  }

  public function suma($n = 1) {
    $mod_ij = self::i_max * self::j_max;

    $pij = ($this->i * self::j_max) + $this->j;

    if (($pij = ($pij + $n) % $mod_ij) < 0) $pij += $mod_ij;

    $i = (int)($pij / self::j_max);
    $j = $pij % self::j_max;

    return new Cal_pos($i, $j);
  }
}

/** Controla a visualizaci&oacute;n html dun calendario.
 *
 * @package tilia\vista\componentes\calendario
 */

final class Cal_html extends Calendario {
  const css_capa = "calendario__capa";
  const css_cab = "calendario__cab";
  const css_ctData_1 = "calendario__ctData_1";
  const css_ctData_1_sl = "calendario__ctData_1_sl";
  const css_ctData_2 = "calendario__ctData_2";
  const css_ctData_2_erro = "calendario__ctData_2_erro";
  const css_ctData_2_sl = "calendario__ctData_2_sl";
  const css_dia = "calendario__dia";
  const css_semana = "calendario__semana";
  const css_mes = "calendario__mes";
  const css_dia_select = "calendario__dia_select";

  protected static function inicia_clasesCSS(Calendario $cal) {
    $cal->clase_css("capa", self::css_capa);
    $cal->clase_css("cab", self::css_cab);
    $cal->clase_css("ctData_1", self::css_ctData_1);
    $cal->clase_css("ctData_1_sl", self::css_ctData_1_sl);
    $cal->clase_css("ctData_2", self::css_ctData_2);
    $cal->clase_css("ctData_2_erro", self::css_ctData_2_erro);
    $cal->clase_css("ctData_2_sl", self::css_ctData_2_sl);
    $cal->clase_css("dia", self::css_dia);
    $cal->clase_css("semana", self::css_semana);
    $cal->clase_css("mes", self::css_mes);
    $cal->clase_css("dia_select", self::css_dia_select);
  }

  protected static function matriz(Calendario $cal) {
    $p = self::cab($cal);

    $pos = new Cal_pos();
    $pos_pdm = $pos->suma(Cal_loxica::dia_semana($cal, 1));
    $pos_udm = $pos_pdm->suma(Cal_loxica::ultimo_dia_mes($cal) - 1);

    $dia = 1;
    do {
      if ($pos->compara($pos_pdm) == -1) $p .= self::dia($cal, $pos);
      elseif ($pos->compara($pos_udm) == 1) $p .= self::dia($cal, $pos);
      else $p .= self::dia($cal, $pos, $dia++);
    }
    while ($pos->seguinte());

    $p .= self::pe();

    if (!$cal->usar_capa) return $p;

    $cal->obxeto("capa")->post($p);

    return $cal->obxeto("capa")->html();
  }

  protected static function mostrar_data(Calendario $cal) {
    if (($cal->ano !== null) && ($cal->mes !== null) && ($cal->dia !== null)) {
      $str_data = $cal->colle_strdata();
      $cal->obxeto("ct_data")->readonly(false);
    }
    else
      $str_data = "";

    $cal->obxeto("ct_data")->post($str_data);

    return "<span>" . $cal->obxeto("ct_data")->html() . $cal->obxeto("e_borrar")->html() . "</span>";
  }

  private static function cab(Calendario $cal) {
    $a_meses = $cal->idioma->meses;

    $cal->obxeto("e_mant")->title = Cal_loxica::mant($cal);
    $cal->obxeto("e_msig")->title = Cal_loxica::msig($cal);

    $cal->obxeto("ct_data")->readonly($cal->readonly);

    return "
    <table cellspacing=0 cellpadding=2 border=0 align=center>
      <tr class=" . $cal->clase_css("cab") . ">
        <td colspan=6 align=center>" . $cal->obxeto("ct_data")->html() . "</td>
        <td align=center>" . $cal->obxeto("e_pechar")->html() . "</td>
      </tr>
      <tr>
        <td class='" . $cal->clase_css("semana") . "' align=left>" . $cal->obxeto("e_mant")->html() . "</td>
        <td class=" . $cal->clase_css("mes") . " colspan=5 align=center>{$a_meses[$cal->mes - 1]}&nbsp;{$cal->ano}</td>
        <td class='" . $cal->clase_css("semana") . "' class='' align=right>" . $cal->obxeto("e_msig")->html() . "</td>
      </tr>
      <tr>
        " . self::cab_semana($cal) . "
      </tr>";
  }

  private static function cab_semana(Calendario $cal) {
    $a_dias = $cal->idioma->dias;

    for ($j = 0; $j < Cal_pos::j_max; $j++) {
      $jd0 = ($j + $cal->idioma->primerDiaSemana) % Cal_pos::j_max;

      $dia = $a_dias[$jd0];

      $pj = substr($dia, 0, 1);

      $dd = $cal->dias_descanso[$jd0];
      if (is_array($dd)) $pj = "<font color='{$dd['color']}' title='{$dd['titulo']}'>{$pj}</font>";

      $p .= "\n<td class=" . $cal->clase_css("semana") . " title = '$dia'>{$pj}</td>";
    }

    return $p;
  }

  private static function pe() {
    return "
    </table>";
  }

  private static function dia(Calendario $cal, Cal_pos $pos, $dia_mes = null) {
    if ($dia_mes == null)
      $cal->obxeto("dia_mes", $pos->sufixo())->post("");
    else
      $cal->obxeto("dia_mes", $pos->sufixo())->post($dia_mes);


    $p .= $cal->obxeto("dia_mes", $pos->sufixo())->html();

    if (($cal->dia != null) && ($cal->dia == $dia_mes)) $p = "<b><u>{$p}</u></b>";

    $ds = $cal->dias_significativos[$cal->ano][$cal->mes][$dia_mes];
    if (is_array($ds))
      $p = "<font color='{$ds['color']}' title='{$ds['titulo']}'>{$p}</font>";
    else {
      $dd = $cal->dias_descanso[($pos->j + $cal->idioma->primerDiaSemana) % Cal_pos::j_max];
      if (is_array($dd)) $p = "<font color='{$dd['color']}' title='{$dd['titulo']}'>{$p}</font>";
    }

    $p = "<td class='" . $cal->clase_css("dia") . "'>{$p}</td>";

    if ($pos->j == 0) $p = "<tr>{$p}";
    elseif ($pos->j == (Cal_pos::j_max + 1)) $p = "{$p}</tr>";

    return $p;
  }
}

/**
 *
 * @package tilia\vista\componentes\calendario
 */

final class Cal_dia extends Etiqueta {
  public function __construct($readonly) {
    parent::__construct("dia_mes");

    $this->readonly = $readonly;

    $this->envia_SUBMIT("onclick");

    $this->style("default", "cursor: pointer;");
    $this->style("readonly", "cursor: default;");
  }
}

/**
 *
 * @package tilia\vista\componentes\calendario
 */

final class Cal_enlace extends Etiqueta {
  public function __construct($nome, $txt) {
    parent::__construct($nome, $txt);

    $this->style("default", "cursor: pointer;");

    $this->envia_SUBMIT("onclick");
  }

  public function readonly($b = true) {
    $this->visible(!$b);
  }
}

/**
 *
 * @package tilia\vista\componentes\calendario
 */

final class Cal_bPechar extends Image {
  public function __construct() {
    parent::__construct("e_pechar");

    $url_imx = Tilia::home() . "imaxes/";

    $this->pon_src("{$url_imx}bpechar.png", null, "{$url_imx}bpechar_over.png", "{$url_imx}bpechar_readonly.png");

    $this->style("default", "cursor: pointer;");

    $this->envia_SUBMIT("onclick");
  }

  public function readonly($b = true) {
    $this->readonly = false;
  }
}

/**
 *
 * @package tilia\vista\componentes\calendario
 */

final class Cal_bBorrar extends Image {
  public function __construct($readonly) {
    parent::__construct("e_borrar");

    $url_imx = Tilia::home() . "imaxes/";

    $this->pon_src("{$url_imx}b_brocha.png", null, "{$url_imx}b_brocha_over.png", "{$url_imx}b_brocha_readonly.png");

    $this->style("default", "cursor: pointer; width: 13px; height:13px;");
    $this->style("readonly", "width: 13px; height:13px;");

    $this->envia_SUBMIT("onclick");

    $this->readonly = $readonly;
  }

  public function readonly($b = true) {
    $this->visible(!$b);
  }
}

/**
 *
 * @package tilia\vista\componentes\calendario
 */

final class Cal_ctData extends Text {
  public function __construct(Calendario $cal, $readonly) {
    parent::__construct("ct_data", $readonly);

    $this->size = 10;

    $this->config_1($cal);
  }

  public function config_1(Calendario $cal) {
    $this->sup_eventos();

    $this->clase_css("default", $cal->clase_css("ctData_1"));

    $this->envia_SUBMIT("onclick");
  }

  public function config_2(Calendario $cal) {
    $this->sup_eventos();

    $this->clase_css("default", $cal->clase_css("ctData_2"));
    $this->clase_css("readonly", $cal->clase_css("ctData_2_sl"));

    $this->envia_SUBMIT("onchange");
  }

  public function config_2_erro(Calendario $cal) {
    $this->clase_css("default", $cal->clase_css("ctData_2_erro"));
  }
}

/**
 *
 * @package tilia\vista\componentes\calendario
 */

final class Cal_capa extends Capa {
  public function __construct(Calendario $cal) {
    parent::__construct("capa", "143px", $cal->clase_css("capa"));

    $this->sempre_visible = true;
  }
}
