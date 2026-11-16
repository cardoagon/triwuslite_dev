<?php

/*************************************************

    Tilia Framework v.0

    Elmt_enquisa.php

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


final class Elmt_enquisa extends AElemento {
  public $ct_novas = 100;
  public $a_borrar = null;

  private $id_eer = null;  //* ($this->id_eer != null) <=> (usuario votó)

  public function __construct(Enquisa_obd $pe_obd) {
    parent::__construct($pe_obd);

    $this->obxeto("btnr")->obxeto("bMove")->visible = false;
    $this->obxeto("btnr")->obxeto("bEditCopy")->visible = false;

    $this->pon_obxeto(new Param("pregunta", $pe_obd->atr("pregunta")->valor));

    $this->pon_obxeto(new Grafica_enquisa($pe_obd));

    $this->pon_obxeto(new Param("creado", $pe_obd->atr("creado")->valor));
    $this->pon_obxeto(new Param("baixa", $pe_obd->atr("baixa")->valor));

    $this->ini_respostas($pe_obd);
  }

  public function config_usuario(IEfspax_xestor $e = null) {
    $this->id_eer = $e->usuario()->enquisa_resposta($this->id_elmt);

    $this->obxeto("dia")->post_config($e->config());
  }

  public function elmt_obd() {
    $e = new Enquisa_obd();

    $e->atr("pregunta")->valor = $this->obxeto("pregunta")->valor();
    $e->atr("grafica" )->valor = $this->obxeto("dia")->__tipo();
    $e->atr("baixa"   )->valor = $this->obxeto("baixa")->valor();


    if (($a_r = $this->obxetos("resposta")) == null) return $this->obd_base($e);

    foreach ($a_r as $k=>$r) {
      list($k1, $k2) = explode(Escritor_html::csubnome, $k);

      if (isset($this->a_borrar[$k2]) && ($k2 < 100))
        $e->a_updates[$k2] = -1;
      else
        $e->a_updates[$k2] = $r->etq;
    }

    return $this->obd_base($e);
  }

  public function editor() {
    return new Editor_eenquisa($this);
  }

  public function html_propio() {
    $html = $this->obxeto("dia")->html();

    $html = str_replace("[pr]", $this->html_pregunta_respostas(), $html);

    return $html;
  }

  public function operacion(EstadoHTTP $e) {
    if (($e_aux = $this->obxeto("dia")->operacion($e)) != null) return $e_aux;

    if (($a_r = $this->obxetos("resposta")) == null) return parent::operacion($e);

    foreach($a_r as $r) if ($r->control_evento()) return $this->operacion_resposta($e);

    return parent::operacion($e);
  }

  public static function __ch_resposta($etq, $readonly = false) {
    $ch = new Checkbox("resposta", false, $etq);

    //~ $ch->envia_SUBMIT("onclick");
    $ch->envia_AJAX("onclick");

    $ch->readonly = $readonly;

    return $ch;
  }

  private function operacion_resposta(Efs $e) {
    $id_eer = $e->evento()->subnome(0);

    if (!$e->usuario()->resposta($this->id_elmt, $id_eer)) return $e;

    $this->id_eer = $id_eer;

    $this->preparar_saida($e->ajax());

    return $e;
  }

  private function ini_respostas(Enquisa_obd $pe_obd) {
    if (($a_eer = $pe_obd->select_eer()) == null) return;

    $readonly = $pe_obd->atr("baixa")->valor != null;

    foreach ($a_eer as $eer) {
      $id_eer   = $eer->atr("id_eer")->valor;
      $resposta = $eer->atr("resposta")->valor;

      $this->pon_obxeto(self::__ch_resposta($resposta, $readonly), $id_eer);
    }
  }

  private function html_pregunta_respostas() {
    if ($this->id_eer != null) return "";

    if (($a_resp = $this->obxetos("resposta")) == null) return "";

    $html = "<div>¿&nbsp;" . $this->obxeto("pregunta")->html() . "&nbsp;?</div>";

    foreach ($a_resp as $k=>$r) {
      list($k1, $k2) = explode(Escritor_html::csubnome, $k);

      if (isset($this->a_borrar[$k2])) continue;

      $html .= "<div>" . $r->html() . "</div>";
    }

    return $html;
  }

}

//*******************************

final class Editor_eenquisa extends Edita_elmt {
  const ico_bh = "imx/elmt/enquisa/rgrafica_bh.png";
  const ico_bv = "imx/elmt/enquisa/rgrafica_bv.png";
  const ico_q  = "imx/elmt/enquisa/rgrafica_q.png";

  private static $a_grafica = array("bh"=>0, "bv"=>1, "q"=>2, 2=>"q", 1=>"bv", 0=>"bh");

  public function __construct(Elmt_enquisa $elmt) {
    parent::__construct(Ico::benquisa, "Encuesta web", "ptw/paragrafo/elmt/editor_enquisa.html", $elmt);

    $this->width = 444;

    $this->pon_obxeto(self::__text("pregunta", $v, " ... escribe una pregunta aqu&iacute;"));
    $this->pon_obxeto(Panel_fs::__link("rnova", "A&ntilde;adir otra respuesta"));
    $this->pon_obxeto(self::__rgrafica());
    $this->pon_obxeto(new Checkbox("pechar", false, "<span class='vm_taboaform_th'>&nbsp;Encuesta cerrada</span>"));

    $this->pon_obxeto(new IllaAJAX("iar", null, true));

    $this->obxeto("pregunta")->post($elmt->obxeto("pregunta")->valor());
    $this->obxeto("grafica")->post( self::$a_grafica[$elmt->obxeto("dia")->__tipo()] );

    if ($elmt->obxeto("baixa")->valor() != null) {
      $this->obxeto("pechar")->post(true);

      $this->obxeto("rnova")->visible = false;
    }
    else {
      $this->obxeto("pechar")->post(false);

      $this->obxeto("rnova")->envia_ajax("onclick");
    }

    $this->ini_respostas($elmt);
  }

  public function operacion(EstadoHTTP $e) {
    //~ echo $e->evento()->html();

    if ($this->obxeto("rnova")->control_evento()) {
      $this->pon_resposta(null, null);

      $e->obxeto("detalle_btnr_oeste")->preparar_saida($e->ajax());

      return $e;
    }

    $a_brr = $this->obxetos("brr");
    foreach($a_brr as $brr)
      if ($brr->control_evento()) {
        $e = $this->operacion_brr($e);

        $e->obxeto("detalle_btnr_oeste")->preparar_saida($e->ajax());

        return $e;
      }


    return parent::operacion($e);
  }

  public function html():string {
    $this->html_respostas();

    return parent::html();
  }

  public function operacion_aceptar(Efs_admin $e) {
    $this->elmt->obxeto("pregunta")->post($this->obxeto("pregunta")->valor());

    $this->elmt->obxeto("dia")->__tipo(self::$a_grafica[$this->obxeto("grafica")->valor()], $e);

    if ($this->obxeto("pechar")->valor())
      $this->elmt->obxeto("baixa")->post(date("YmdHms"));
    else
      $this->elmt->obxeto("baixa")->post("");


    $a_resposta = $this->obxetos("resposta");

    if (count($a_resposta) < 2 ) die("Elmt_enquisa.operacion_aceptar(), count(a_resposta) < 2 )");

    foreach($a_resposta as $k=>$v) {
      list($k1, $k2) = explode(Escritor_html::csubnome, $k);

      $o = $this->elmt->obxeto($k1, $k2);

      $r_branco = trim($v->valor()) == "";
      $r_borrar = isset($this->elmt->a_borrar);

      if ($r_branco) {
        if ($r_borrar)   continue;
        if ($o == null)  continue;
        if ($k2 > "100") $this->elmt->a_borrar[$k2] = -1;


        $this->elmt->pon_obxeto(Elmt_enquisa::__ch_resposta($v->valor()), $k2);
      }
      elseif($o == null)
        $this->elmt->pon_obxeto(Elmt_enquisa::__ch_resposta($v->valor()), $k2);
      else
        $this->elmt->obxeto($k1, $k2)->etq = $v->valor();
    }

    return parent::operacion_aceptar($e);
  }

  protected function __editor_style() {
    return new Editor_style_nulo();
  }

  private function operacion_brr(Epaxina_edit $e) {
    $k2 = $e->evento()->subnome(0);

    $txt_r = $this->obxeto("resposta", $k2);

    $recuperar = isset($this->elmt->a_borrar[$k2]);

    if ($recuperar) unset($this->elmt->a_borrar[$k2]);
    elseif ((trim($txt_r->valor()) == "") && ($k2 > "100")) {
      $this->sup_obxeto("resposta", $k2);
      $this->sup_obxeto("brr"     , $k2);

      return $e;
    }
    else $this->elmt->a_borrar[$k2] = -1;

    $this->pon_obxeto(self::__brr($recuperar), $k2);

    $txt_r->readonly = !$recuperar;

    return $e;
  }

  private function ini_respostas(Elmt_enquisa $elmt) {
    if (($a_resp = $elmt->obxetos("resposta")) == null) {
      $this->pon_resposta(null, null);
      $this->pon_resposta(null, null);

      return;
    }

    foreach($a_resp as $k=>$v)  {
      list($k1, $k2) = explode(Escritor_html::csubnome, $k);

      $this->pon_resposta($k2, $v->etq, isset($this->elmt->a_borrar[$k2]));
    }
  }

  private function pon_resposta($k, $v, $readonly = false) {
    if ($k == null) {
      $this->elmt->ct_novas++;

      $k = $this->elmt->ct_novas;
    }

    $text = self::__text("resposta", $v, " ... escribe una respuesta, pej. NS/NC");

    $text->post($v);
    $text->readonly = $readonly;
    $this->pon_obxeto($text, $k);


    $this->pon_obxeto(self::__brr(!$readonly), $k);
  }

  private function html_respostas() {
    $sr = "";

    $a_resp = $this->obxetos("resposta");

    foreach ($a_resp as $k=>$r) {
      list($k1, $k2) = explode(Escritor_html::csubnome, $k);

      $sr .= "<tr>
                <td align=center>" . $this->obxeto("brr", $k2)->html() . "</td>
                <td align=center>" . $r->html() . "</td>
                <td></td>
              </tr>";
    }

    $this->obxeto("iar")->post($sr);
  }

  private static function __text($id, $v, $placeholder) {
    $t = Panel_fs::__text($id, 22, 255, $placeholder);

    $t->clase_css("default", "elmt_eeenquisa_resposta");
    $t->clase_css("readonly", "elmt_eeenquisa_resposta_b");

    $t->post($v);

    return $t;
  }

  private static function __brr($borrar) {
    $ico = ($borrar)?Ico::brama_menos:Ico::brec;

    $b = Panel_fs::__bapagado("brr", $ico, null, true);

    $b->envia_ajax("onclick");

    return $b;
  }

  private static function __rgrafica() {
    $ico_bh = Refs::url(self::ico_bh);
    $ico_bv = Refs::url(self::ico_bv);
    $ico_q  = Refs::url(self::ico_q);

    $etq_style = "style='padding: 1px 11px 1px 3px;'";

    $r = new Radio("grafica");

    $r->pon_opcion("<img src='{$ico_bh}' {$etq_style} title='Barras horizontales' />");
    $r->pon_opcion("<img src='{$ico_bv}' {$etq_style} title='Barras verticales' />");
    $r->pon_opcion("<img src='{$ico_q}'  {$etq_style} title='Tarta' />");

    return $r;
  }
}

?>
