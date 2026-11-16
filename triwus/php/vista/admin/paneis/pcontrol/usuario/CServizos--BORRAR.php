<?php

final class CServizos extends Componente {
  const ptw         = "ptw/paneis/pcontrol/usuario/cservizos.html";
  const ptw_factura = "ptw/paneis/pcontrol/usuario/fratriwus.html";

  public function __construct(FGS_usuario $u) {
    parent::__construct("cservizos", self::ptw);

    $this->pon_obxeto(new CServizos_lista($u));
  }

  public function operacion(EstadoHTTP $e) {
//~ echo $e->evento()->html(); //* ollo ajax

    if (($e_aux = $this->obxeto("cservizosl")->operacion($e)) != null) return $e_aux;

    return null;
  }

  public function operacion_chautorenove(Epcontrol $e, $id_servizo) {
    $cbd = new FS_cbd();

    $s = Servizo_obd::inicia($cbd, $id_servizo);

    $s->atr("autorenove")->valor = ($s->atr("autorenove")->valor == 1)?"0":"1";

    $s->update($cbd);

    $this->obxeto("cservizosl")->preparar_saida( $e->ajax() );


    return $e;
  }

  public function operacion_lfactura(Epcontrol $e, $id_servizo) {
    $cbd = new Xesta_cbd();

    $f_obd = Servizo_obd::inicia($cbd, $id_servizo)->fra_obd($cbd);

    $ano = $f_obd->atr("ano"   )->valor;
    $num = $f_obd->atr("numero")->valor;

//~ echo $f_obd->__html();

    try {
        $html2pdf = new HTML2PDF('P', 'A4', 'es');

        $html2pdf->setDefaultFont('times');
        $html2pdf->writeHTML($f_obd->__html(null, $cbd));
        $html2pdf->Output("factura.{$ano}-{$num}.pdf", 'D');
    }
    catch(HTML2PDF_exception $e) {
        echo $e;
        exit;
    }


    return $e;
  }
}

//********************************

final class CServizos_lista extends FS_lista {
  public function __construct(FGS_usuario $u) {
    parent::__construct("cservizosl", new FSehtml_cservizos());
    
    $myxesta_b = TrwlConfig::mysql_b;

    $this->selectfrom = "
    select id_servizo, nome, autorenove, date_format(alta, '%d-%m-%Y') as alta_dma, fra_kc,  fra_ano, fra_num,
           if( caduca = 0, null, ( date_format( date_add(alta, interval caduca DAY) , '%d-%m-%Y')) ) as baixa_dma,
           caduca - datediff(now(), alta) as dias
      from {$myxesta_b}.servizo";
      
    $this->where      = "id_usuario = " . $u->atr("id_usuario")->valor;
    $this->orderby    = "alta desc";
    $this->limit_f    = 10;
  }

  public function operacion(EstadoHTTP $e) {
    $evento = $e->evento();

//~ echo $evento->html(); //* ollo ajax

    if ($this->control_fslevento($evento, "chautorenove")) return $this->pai->operacion_chautorenove($e, $evento->subnome(0));
    if ($this->control_fslevento($evento, "lfactura"))     return $this->pai->operacion_lfactura($e, $evento->subnome(0));

    return parent::operacion($e);
  }
}

//---------------------------------

final class FSehtml_cservizos extends FS_ehtml {
  public $nf = 0;

  public function __construct() {
    parent::__construct();

    $this->class_table = "lista_00";
    $this->style_table = "border: 1px #aaa solid;";
    $this->align       = "center";
    $this->width       = "100%";
    $this->cellspacing = 0;
    $this->cellpadding = 0;
    $this->border      = 0;
  }

  protected function cabeceira($df = null) {
    $this->nf = $this->rbd->numFilas();

    return "<tr>
              <th style='width: 3em;'></th>
              <th style='width: 14em;'>Nombre del servicio</th>
              <th style='width: 11em;'>Fecha&nbsp;alta</th>
              <th style='width: 11em;'>Fecha&nbsp;fin</th>
              <th style='width: 5em; text-align: center;' title='Renovación del servicio automáticamente'>Renovar</th>
              <th style='width: 11em; text-align: center;' >Factura</th>
            </tr>";
  }

  protected function linha_detalle($df, $f) {
    //~ if (($pvp = $f['pvp']) == 0) $pvp = "---";

    if ($f['baixa_dma'] == null) {
      $estado     = "";
      $baixa      = "--";
      $autorenove = "";
    }
    else {
      $estado     = self::__iestado($f);
      $baixa      = $f['baixa_dma'];
      $autorenove = self::__chautorenove($this, $f);
    }

    $factura    = self::__lfactura($this, $f);

    return "<tr height=29px >
              <td style='text-align:center;'>{$estado}</td>
              <td>{$f['nome']}</td>
              <td>{$f['alta_dma']}</td>
              <td>{$baixa}</td>
              <td style='text-align:center;'>{$autorenove}</td>
              <td style='text-align:center;'>{$factura}</td>
            </tr>";
  }

  protected function totais() {
    return "
            <tr>
              <td colspan=9 align=center style='border-top: 1px solid #cccccc;'>" . $this->__paxinador_html() . "</td>
            </tr>";
  }
  private static function __iestado($f) {
    $s = new Div("icoestado", "&bull;");

    if ($f['dias'] > 40) {
      $color = "#00ba00";
      $title = "";
    }
    elseif ($f['dias'] > 0) {
      $color = "#FFA500";
      $title = "El servicio caducará en {$f['dias']} días";
    }
    else {
      $color = "#ba0000";
      $title = "El servicio está caducado";
    }

    $s->title = $title;
    
    $s->style("default", "font-size:2.3em; color: {$color};");


    return $s->html();
  }

  private static function __chautorenove(FSehtml_cservizos $ehtml, $f) {
    $checked  = ($f['autorenove'] == 1) || ($f['autorenove'] == 3);

    $ch = new Checkbox("chautorenove", $checked);

    $ch->style("default", "cursor: pointer; justify-content: center;");

    $ch->readonly = $f['autorenove'] > 1;

    if (!$ch->readonly) {
      $m = ($checked)?"¿ Quieres CANCELAR la auto-renovación del servicio seleccionado ?":null;
      
      $ch->pon_eventos("onclick", "chekea_ajax(this, '{$m}')");

      $ch->title = ($checked)?"Cancelar autorenovaci&oacute;n":"Activar autorenovaci&oacute;n";
    }

    return $ehtml->__fslControl($ch, $f['id_servizo'])->html();
  }

  private static function __lfactura(FSehtml_cservizos $ehtml, $f) {
    if (($f['fra_ano'] == null) || ($f['fra_num'] == null)) return "";

    $fnum = str_pad($f['fra_num'], 3, "0", STR_PAD_LEFT);

    $l = new Link("lfactura", "{$f['fra_kc']}{$f['fra_ano']}/{$fnum}");

    $l->clase_css("default", "lista_elemento_a");

    $l->envia_mime("onclick");


    return $ehtml->__fslControl($l, $f['id_servizo'])->html();
  }
}

