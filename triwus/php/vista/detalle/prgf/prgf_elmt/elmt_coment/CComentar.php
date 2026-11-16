<?php

/*************************************************

    Triwus Framework v.0

    CComentar.php

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

final class CComentar extends Componente
                      implements I_fs {

  private $id_elmt;

  public function __construct(Comentarios_obd $pe_obd) {
    parent::__construct("ccomentar", Refs::url("ptw/paragrafo/elmt/comentarios/ccomentar.html"));

    $this->id_elmt = $pe_obd->atr("id_elmt")->valor;

    $this->pon_obxeto(new Cab_coments($pe_obd->atr("id_elmt")->valor));
    $this->pon_obxeto(self::__bcomentar());

    $this->pon_obxeto(Panel_fs::__text("nome", 33, 255, "Escribe tu nombre, p.ej. Arturo ... "));
    $this->pon_obxeto(Panel_fs::__text("email", 33, 255, "Escribe tu email, p.ej. arturo@mail.es ... "));

    $this->pon_obxeto(new Div("resposta"));
    $this->pon_obxeto(Panel_fs::__textarea("texto", null, false, "Escribe aqu&iacute; tu comentario ;-)"));
    $this->pon_obxeto(Panel_fs::__baceptar());
    $this->pon_obxeto(Panel_fs::__bcancelar());

    $this->pon_obxeto(Panel_fs::__bapagado("normas_comentar", null, null, false, "<span class='texto2_a'>Normas para comentar</span>"));

    $this->obxeto("nome" )->clase_css("default", "coments_input");
    $this->obxeto("texto")->clase_css("default", "coments_input");

    $this->__resposta();

    //~ $this->obxeto("texto")->style("default", "height:127px;");
    //~ $this->obxeto("texto")->clase_css("default", "coments_input");

    $this->obxeto("bCancelar")->envia_ajax("onclick");
    $this->obxeto("bAceptar")->envia_ajax("onclick");
  }

  public function config_lectura() {
    $this->obxeto("cab")->config_lectura();
  }

  public function config_edicion() {
    $this->obxeto("cab")->config_edicion();
  }

  public function config_usuario(IEfspax_xestor $e = null) {}

  public function operacion(EstadoHTTP $e) {
    if (($e_aux = $this->obxeto("cab")->operacion($e)) != null) {
      $sql_where = $this->obxeto("cab")->sql_where();
      $sql_orde  = $this->obxeto("cab")->sql_orde();

      $this->pai->obxeto("tcomentsdet")->__where($sql_where);
      $this->pai->obxeto("tcomentsdet")->__order($sql_orde);

      return $e_aux;
    }

    if (!$this->visible) return null;

    $this->__resposta();

    if ($this->obxeto("comentar")->control_evento()) return $this->operacion_comentar($e);

    if ($this->obxeto("bCancelar")->control_evento()) {
      $this->obxeto("comentar")->visible = true;

      return $e;
    }

    if ($this->obxeto("bAceptar")->control_evento()) return $this->operacion_aceptar($e);

    //~ if ($this->obxeto("normas_comentar")->control_evento()) return $e->mensaxe_pop(new FSalert_normas());

    return parent::operacion($e);
  }

  public function html():string {
    if ($this->pai->pechar) return "";

    $this->obxeto("normas_comentar")->envia_SUBMIT("onclick", null, $this->pai->__ancla());

    $cab = $this->obxeto("cab")->html();

    $cab = str_replace("[comentar]", $this->obxeto("comentar")->html(), $cab);
    $cab = str_replace("[normas_comentar]", $this->obxeto("normas_comentar")->html(), $cab);
    $cab = str_replace("[resposta]", $this->obxeto("resposta")->html(), $cab);

    if ($this->obxeto("comentar")->visible) return $cab;

    return $cab . $this->html00();
  }

  private function operacion_aceptar(IEfspax_xestor $e) {
    list($x, $id_elmt) = explode(Escritor_html::csubnome, $e->evento()->nome(2));

    if (($erro = $this->__validar($id_elmt)) != null) {
      $this->__resposta($erro, "fgs_erro");

      return $e;
    }

    $cbd = new FS_cbd();
    if ($this->__obd($id_elmt)->insert()) {
      if ($this->pai->avisar) {
        $pax = Paxina_obd::inicia($cbd, $e->id_paxina());

        $email = $this->pai->avisar_email;
        $url   = XMail::__link( $pax->action(true) )->html();
        $nome  = $this->obxeto("nome")->valor();

        self::__mail_avisa($email, $url, $nome, $this->pai->moderacion);
      }

      $this->obxeto("nome")->post("");
      $this->obxeto("texto")->post("");
      //~ $this->obxeto("email")->post("");
    }
    else {
      $this->__resposta("Sucedi&oacute; un error, por favor pruebe otra vez.", "fgs_erro");

      return $e;
    }

    $this->obxeto("comentar")->visible = true;

    if ($this->pai->moderacion)
      $this->__resposta(Msx::coment_moderacion);
    else
      $this->__resposta(Msx::coment_aceptado);


    return $e;
  }

  private function operacion_comentar(IEfspax_xestor $e) {
    if ($this->pai->anonimos != 1) {
      $u = $e->usuario();

      if (!$u->validado()) return $this->pai->operacion_cacceso($e); //* debemos solicitar a autenticaci&oacute;n do usuario.

      //~ $this->obxeto("nome")->post( $u->atr("email")->valor );

    }

    $this->obxeto("comentar")->visible = false;

    return $e;
  }

  private function __obd($id_elmt) {
    $obd = new Comentarios_detalle_obd();

    $obd->atr("id_elmt" )->valor = $id_elmt;
    $obd->atr("firma"   )->valor = $this->obxeto("nome")->valor();
    $obd->atr("txt"     )->valor = $this->obxeto("texto")->valor();
    //~ $obd->atr("email"   )->valor = $this->obxeto("email")->valor();
    $obd->atr("moderado")->valor = ($this->pai->moderacion)?"0":"1";

    $baixa = $this->pai->moderacion;

    $obd->atr("baixa")->valor = ($baixa)?"1":"0";

    return $obd;
  }

  private function __resposta($txt = null, $css = "fgs_msx") {
    $this->obxeto("resposta")->post($txt);
    $this->obxeto("resposta")->clase_css("default", $css);
    $this->obxeto("resposta")->visible = ($txt != null);
  }

  private function __validar($id_elmt) {
    $erro = null;

    //~ $email = $this->obxeto("email")->valor();

    if (trim($this->obxeto("nome")->valor()) == null) $erro .= "&bull;&nbsp;Debe escribir un nombre";
    //~ if (!Valida::email($email)) $erro .= "<br />&bull;&nbsp;" . Erro::email;
    if (trim($this->obxeto("texto")->valor()) == null) $erro .= "<br />&bull;&nbsp;Debe escribir un comentario";

    return $erro;
  }

  private static function __bcomentar() {
    $b = Panel_fs::__bimage("comentar",
                            Ico::bmenu,
                            "Para a&ntilde;adir un comentario, pulsa aqu&iacute; ...",
                            true,
                            "&nbsp;&nbsp;Comentar"
                           );

    //~ $b->envia_SUBMIT("onclick");
    $b->envia_ajax("onclick");

    return $b;
  }

  private static function __mail_avisa($to, $url, $usuario, $moderacion) {
    $m = new XMail();

    $m->para    = $to;
    $m->asunto  = "(no-reply) - Comentario nuevo - " . Refs::url_home();


    $txt_moderacion = ($moderacion)?"Atenci&oacute;n, <u>el comentario est&aacute; pendiente de moderaci&oacute;n</u>. Este comentario no aparecer&aacute; publicado hasta recibir su visto bueno":"";

    $m->texto   = "Nuevo comentario de <b>{$usuario}</b> en la página {$url}.<br /><br />{$txt_moderacion}.";

    $m->enviar();
  }
}

//************************************************************

final class Cab_coments extends Componente
                        implements I_fs {

  private $id_elmt;

  public function __construct($id_elmt) {
    parent::__construct("cab");

    $this->id_elmt = $id_elmt;

    $this->pon_obxeto(new Btnr_cab());
    $this->pon_obxeto(Panel_fs::__bapagado("bmenu", Ico::bmenu, null, false));

    $this->pon_obxeto(new Param("t"));
    $this->pon_obxeto(new Param("a"));
    $this->pon_obxeto(new Param("p"));
    $this->pon_obxeto(new Param("b"));

    $this->obxeto("bmenu")->pon_eventos("onclick", "elmt_comentarios_bmenu(this)");
  }

  public function config_lectura() {
    $this->ptw = Refs::url("ptw/paragrafo/elmt/comentarios/cabcoment_b.html");

    $this->obxeto("btnr_cab")->filtro = "a";

    $this->obxeto("btnr_cab")->obxeto("ch_filtro", "t")->post(false);
    $this->obxeto("btnr_cab")->obxeto("ch_filtro", "a")->post(true);
    $this->obxeto("btnr_cab")->obxeto("ch_filtro", "p")->post(false);
    $this->obxeto("btnr_cab")->obxeto("ch_filtro", "b")->post(false);
  }

  public function config_edicion() {
    $this->ptw = Refs::url("ptw/paragrafo/elmt/comentarios/cabcoment.html");

    $this->obxeto("btnr_cab")->filtro = "t";

    $this->obxeto("btnr_cab")->obxeto("ch_filtro", "t")->post(true);
    $this->obxeto("btnr_cab")->obxeto("ch_filtro", "a")->post(false);
    $this->obxeto("btnr_cab")->obxeto("ch_filtro", "p")->post(false);
    $this->obxeto("btnr_cab")->obxeto("ch_filtro", "b")->post(false);
  }

  public function config_usuario(IEfspax_xestor $e = null) {}

  public function sql_where() {
    return self::__where($this->id_elmt, $this->obxeto("btnr_cab")->filtro);
  }

  public function sql_orde() {
    return self::__orderby($this->obxeto("btnr_cab")->orde_1);
  }

  public function operacion(EstadoHTTP $e) {
    if (($e_aux = $this->obxeto("btnr_cab")->operacion($e)) != null) return $e_aux;

    return null;
  }

  public function html():string {
    $a_ct = self::__contar($this->id_elmt);

    $this->obxeto("t")->post($a_ct['t']);
    $this->obxeto("a")->post($a_ct['a']);
    $this->obxeto("p")->post($a_ct['p']);
    $this->obxeto("b")->post($a_ct['b']);

    return parent::html();
  }

  public static function __where($id_elmt, $filtro = "a") {
    $w = "id_elmt = {$id_elmt}";

    switch ($filtro) {
      case "a": return "{$w} and moderado = 1 and baixa = 0";
      case "p": return "{$w} and moderado = 0";
      case "b": return "{$w} and moderado = 1 and baixa = 1";
    }

    return $w;
  }

  public static function __orderby($o1 = "d", $o2 = "d") {
    $o2 = ($o2 == "a")?"ASC":"DESC";

    switch ($o1) {
      case "d": return "momento {$o2}";
      //~ case "e": return "email {$o2}";
      case "v": return "votos {$o2}";
      case "p": return "puntos {$o2}";
    }
  }

  private static function __contar($id_elmt) {
    $sql = "select count(*) as ct from elmt_comentarios_detalle where id_elmt = '{$id_elmt}'";

    $cbd = new FS_cbd();

    //* conta total
    $r = $cbd->consulta($sql);
    if ($a = $r->next()) $t = $a['ct'];

    //* conta pdte. de moderacion
    $r = $cbd->consulta("{$sql} and moderado = 0");
    if ($a = $r->next()) $p = $a['ct'];

    //* conta bloqueados
    $r = $cbd->consulta("{$sql} and baixa = 1");
    if ($a = $r->next()) $b = $a['ct'];


    return array("t"=>$t,
                 "a"=>$t - $b,
                 "p"=>$p,
                 "b"=>$b - $p);
  }
}

//-------------------------------------------

final class Btnr_cab extends Btnr {
  public $filtro = "a";
  public $orde_1 = "d";
  public $orde_2 = "d";

  public function __construct() {
    parent::__construct("btnr_cab");

    $this->pon_obxeto(self::__check("ch_filtro", "Todos")                    , "t");
    $this->pon_obxeto(self::__check("ch_filtro", "Activos", true)            , "a");
    $this->pon_obxeto(self::__check("ch_filtro", "Pdt. de moderaci&oacute;n"), "p");
    $this->pon_obxeto(self::__check("ch_filtro", "Bloquedos")                , "b");

    $this->pon_obxeto(self::__check("ch_orde_1", "Fecha/hora", true) , "d");
    $this->pon_obxeto(self::__check("ch_orde_1", "Nombre")           , "a");
    $this->pon_obxeto(self::__check("ch_orde_1", "Votos")            , "v");
    $this->pon_obxeto(self::__check("ch_orde_1", "Puntuaci&oacute;n"), "p");

    $this->pon_obxeto(self::__capa(false, "coments_cab_capa"));

    $this->obxeto("capa")->style("default: 17px 0 0 -5px;");
  }

  public function operacion(EstadoHTTP $e) {
    if (($e_aux = $this->operacion_filtro($e)) != null) return $e_aux;
    if (($e_aux = $this->operacion_orde_1($e)) != null) return $e_aux;

    return null;
  }

  public function html():string {
    if (!$this->visible) return "";

    $ch_f_t = $this->obxeto("ch_filtro", "t")->html();
    $ch_f_a = $this->obxeto("ch_filtro", "a")->html();
    $ch_f_p = $this->obxeto("ch_filtro", "p")->html();
    $ch_f_b = $this->obxeto("ch_filtro", "b")->html();

    $ch_o1_d = $this->obxeto("ch_orde_1", "d")->html();
    $ch_o1_a = $this->obxeto("ch_orde_1", "a")->html();
    $ch_o1_v = $this->obxeto("ch_orde_1", "v")->html();
    $ch_o1_p = $this->obxeto("ch_orde_1", "p")->html();

    $s_0  .= "<div style='text-align: center; font-weight: bold;'>... mostrar ...</div>
              <div style='text-align: left;'>{$ch_f_t}</div>
              <div style='text-align: left;'>{$ch_f_a}</div>
              <div style='text-align: left;'>{$ch_f_p}</div>
              <div style='text-align: left;'>{$ch_f_b}</div>
              <hr color=transparent size=1 />
              <div style='text-align: center; font-weight: bold;'>... ordenar ...</div>
              <div style='text-align: left;'>{$ch_o1_d}</div>
              <div style='text-align: left;'>{$ch_o1_a}</div>";

    $this->obxeto("capa")->post($s_0);

    return $this->obxeto("capa")->html();
  }

  private function operacion_filtro(IEfspax_xestor $e) {
    foreach (array("t", "a", "p", "b") as $f)
      if ($this->obxeto("ch_filtro", $f)->control_evento()) {
        $this->obxeto("ch_filtro", $this->filtro)->post(false);

        if ($this->obxeto("ch_filtro", $f)->valor())
          $this->filtro = $f;
        else {
          $this->filtro = "t";
          $this->obxeto("ch_filtro", "t")->post(true);
        }

        return $e;
      }

    return null;
  }

  private function operacion_orde_1(IEfspax_xestor $e) {
    foreach (array("d", "a", "v", "p") as $o1)
      if ($this->obxeto("ch_orde_1", $o1)->control_evento()) {
        if ($this->orde_1 != "")
          $this->obxeto("ch_orde_1", $this->orde_1)->post(false);

        $this->orde_1 = ($this->obxeto("ch_orde_1", $o1)->valor())?$o1:"";

        return $e;
      }

    return null;
  }

  private static function __check($id, $etq, $checked = false) {
    $ch = new Checkbox($id, $checked, $etq);

    $ch->envia_AJAX("onclick");
    //~ $ch->envia_SUBMIT("onclick");

    return $ch;
  }
}

//************************************************************

//~ final class FSalert_normas extends FS_alert {
  //~ public function __construct() {
    //~ parent::__construct("fs_alert", null, "Normas para comentar");
//~
    //~ $this->width = 777;
//~
    //~ $this->mensaxe = LectorPlantilla::plantilla(Refs::url("ptw/paragrafo/elmt/comentarios/normas_comentar.html"));
//~
    //~ $this->obxeto("bAceptar")->envia_SUBMIT("onclick", null, $this->__ancla());
  //~ }
//~ }

?>
