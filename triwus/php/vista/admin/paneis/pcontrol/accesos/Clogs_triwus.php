<?php

final class Clogs_triwus extends Componente implements ICPC {

  public function __construct($id_site) {
    parent::__construct("clogs", "ptw/paneis/pcontrol/logs/triwus.html");
    
    $this->pon_obxeto(new CAcceso_lista($id_site));
    $this->pon_obxeto(new CAcceso_busca($id_site));
  }

  public function operacion(EstadoHTTP $e) {
    if ( ($e_aux = $this->obxeto("laccesos")->operacion($e)) != null ) return $e_aux;
    if ( ($e_aux = $this->obxeto("cxab"    )->operacion($e)) != null ) return $e_aux;

    return null;
  }
  
  public function declara_css() {
    return array(Refs::url("css/fs_lista.css"));
  }
  
  public function cpc_miga() {
    return "triwus";
  }
}


//******************************************

final class CAcceso_busca extends Componente {
  const ptw_0 = "ptw/paneis/pcontrol/logs/triwus_b.html";

  private $id_site    = null;

  public function __construct($id_site) {
    parent::__construct("cxab", self::ptw_0);


    $this->id_site    = $id_site;

    $this->pon_obxeto(Panel_fs::__bexportar("bCSV"   , null, null));
    $this->pon_obxeto(Panel_fs::__bbusca   ("bBuscar", null, null));
    
    $this->pon_obxeto(Panel_fs::__bcancelar(null, null, "bLimpa"));

    $this->pon_obxeto(Panel_fs::__text("ct_buscador", 22, 111, " "));

    $this->pon_obxeto(Panel_fs::__dataInput("dataini"));
    $this->pon_obxeto(Panel_fs::__dataInput("datafin"));
    $this->pon_obxeto(self::__sTipoAcceso("sAcceso"));

    $this->obxeto("ct_buscador")->clase_css("default", "tbuscador"  );

    $this->obxeto("bLimpa" )->visible = false;
    
    $this->obxeto("bLimpa" )->envia_AJAX("onclick");
    $this->obxeto("bBuscar")->envia_AJAX("onclick");
  }

  public function operacion(EstadoHTTP $e) {
    if ($this->obxeto("bBuscar")->control_evento()) return $this->operacion_buscador($e);
    if ($this->obxeto("bCSV"   )->control_evento()) return $this->operacion_csv     ($e);
    if ($this->obxeto("bLimpa" )->control_evento()) return $this->operacion_limpa   ($e);

    return null;
  }

  private function operacion_csv(EstadoHTTP $e, $vblimpa = true) {
    $ecsv = new CAcceso_csv( $this->__where() );
    
    $d = utf8_decode($ecsv->escribe());
    $t = "RexTriwus_" . date("YmdHis") . ".csv";
    
    $e->pon_mime(null, "application/vnd.ms-excel", $d, null, $t, "ISO-8859-1");

    
    return $e;
  }

  private function operacion_buscador(EstadoHTTP $e, $vblimpa = true) {
    $this->obxeto("bLimpa")->visible = $vblimpa;
    
    $this->pai->obxeto("laccesos")->post_where( $this->__where() );
    
    $this->pai->preparar_saida( $e->ajax() );

    return $e;
  }

  private function operacion_limpa(EstadoHTTP $e) {
    $this->obxeto("ct_buscador")->post(""  );
    $this->obxeto("dataini"    )->post(""  );
    $this->obxeto("datafin"    )->post(""  );
    $this->obxeto("sAcceso"    )->post("**");

    return $this->operacion_buscador($e, false);
  }

  private function __validar() {
    return null;
  }

  public function __where() {
    $wh_0= Valida::buscador_txt2sql($this->obxeto("ct_buscador")->valor(), ["a.ip", "a.login", "a.txt"]);

    if ($wh_0 == null) $wh_0 = "(1)";

    $wh_1 = "(1)";
    if (($v = $this->obxeto("sAcceso")->valor()) != "**") {
      $wh_1 = "( a.ok = '{$v}')";
    }

    $wh_2 = "(1)";
    if (($v = $this->obxeto("dataini")->valor()) != null) {
     $wh_2 = Valida::buscador_data2sql("momento", ">=", $v);
   }

   $wh_3 = "(1)";
    if (($v = $this->obxeto("datafin")->valor()) != null) {
      $wh_3 = Valida::buscador_data2sql("momento", "<=", $v);
    }

    return "(a.id_site = {$this->id_site}) and {$wh_0} and {$wh_1} and {$wh_2} and {$wh_3}";
  }


  public static function __sTipoAcceso($nome) {
    $options = [
      "**" => "...",
      "1"  => "Válido",
      "0"  => "Fallido"
    ];

    $s = new Select($nome, $options);

    return $s;
  }

}


//******************************


class CAcceso_lista extends FS_lista {
  public function __construct($id_site) {
    parent::__construct("laccesos", new CAcceso_ehtml());

    $this->selectfrom = "select a.* from acceso a";

    $this->post_where("a.id_site = {$id_site}");

    $this->orderby    = "a.momento desc";
    $this->limit_f    = 50;
  }

  protected function html_baleiro() {
    if (!$this->visible) return "";

    return "<div class='lista_baleira txt_adv'>Sin resultados</div>";
  }
}

//---------------------------------

class CAcceso_ehtml extends FS_ehtml {
  //~ public $nf = 0;

  public function __construct() {
    parent::__construct();

    $this->class_table   = "lista_00";
    $this->style_table = "border: 1px #aaa solid;";
    $this->align       = "left";
    $this->width       = "99%";
    $this->cellspacing = 0;
    $this->cellpadding = 0;
    $this->border      = 0;
  }

  protected function cabeceira($df = null) {
    return "<tr>
              <th align='left' width='12em'>" . $this->__lOrdenar_html("momento", "Momento"  ) . "</th>
              <th align='left' width='12em'>" . $this->__lOrdenar_html("ip"     , "IP"       ) . "</th>
              <th align='left' width='77px'>" . $this->__lOrdenar_html("login"  , "Login"    ) . "</th>
              <th align='left' width='*'   >" . $this->__lOrdenar_html("txt"    , "Solicitud") . "</th>
            </tr>";
  }

  protected function linha_detalle($df, $f) {
    $css = ($f["ok"] == 1)?"txt_msx":"txt_err";

    return "<tr class='{$css}'>
              <td style='white-space:nowrap;'>{$f['momento']}</td>
              <td>{$f['ip']}</td>
              <td>{$f['login']}</td>
              <td style='white-space:nowrap;'>{$f['txt']}</td>
            </tr>";
  }

  protected function totais() {
    return "<tr style = 'background-color: #fff;'>
              <td align=center colspan=9>" . $this->__paxinador_html() . "</td>
            </tr>";
  }
}


//******************************


final class CAcceso_csv extends EscritorResultado_csv {
  public function __construct(string $where) {
    parent::__construct( $this->iterador($where) );
  }

  protected function cabeceira($df = null) {
    return "\"Momento\";\"IP\";\"Login\";\"Solicitud\";\"Resultado\"\r\n";
  }

  protected function linha_detalle($df, $f) {
    $oko = ($f["ok"] == 1)?"ok":"ko";

    return "\"{$f['momento']}\";\"{$f['ip']}\";\"{$f['login']}\";\"{$f['txt']}\";\"{$oko}\"\r\n";

  }

  private function iterador(string $where):Iterador_bd {
    $sql = "select a.* 
             from acceso a
            where {$where} 
         order by a.momento";

    $cbd = new FS_cbd();

    return $cbd->consulta($sql);
  }
}
