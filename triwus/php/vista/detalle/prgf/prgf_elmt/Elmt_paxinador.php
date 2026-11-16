<?php

class Elmt_paxinador extends AElemento {
  private $id_paxina = null;

  public function __construct(Paxinador_obd $pe_obd) {
    parent::__construct($pe_obd);

    $this->pon_obxeto(new Span("smigas", "smigas non iniciado"));
  }

  public function config_usuario(IEfspax_xestor $e = null) {
    parent::config_usuario($e);

    $this->id_paxina = $e->id_paxina();

    $this->config_smigas();
  }

  public function elmt_obd() {
    return $this->obd_base( new Paxinador_obd() );
  }

  protected function pon_btnr() {
    $btnr = new Btnr_elmt();

    $btnr->config_visibles(false, true, true, true);
    
    $this->pon_obxeto($btnr);
  }

  protected function config_smigas(Paxina_obd $pax = null) {
    $a_migas = ($pax == null) ? Paxinador_obd::a_migas($this->id_paxina) : $pax->a_migas();

    $this->config_smigas_2($a_migas);
  }

  protected function config_smigas_2(array $a_migas) {
    $this->obxeto("smigas")->post(null);

//~ echo "<pre>" . print_r($a_migas, 1) . "</pre>";

    if (($ct_html = count($a_migas)) == 0) return;

    $smigas_html = ""; 
    foreach ($a_migas as $p=>$_miga) {
      $lmiga = self::__lmiga($_miga[1], $_miga[2], $ct_html-- == 1);

      $this->pon_obxeto($lmiga, $_miga[0]);

      $smigas_html .= $lmiga->html();
    }

    $this->obxeto("smigas")->post($smigas_html);
  }

  public function editor() {
    return new Edita_elmt_nulo();
  }

  public function operacion(EstadoHTTP $e) {
    if (($e_aux = $this->operacion_lmiga($e)) != null) return $e_aux;

    return parent::operacion($e);
  }

  public function html_propio() {
    $smigas = $this->obxeto("smigas")->html();

    return "<div class='navegador_marco'>{$smigas}</div>";
  }

  private function operacion_lmiga(Efs $e) {
    $a_lmiga = $this->obxetos("lmiga");

    if (count($a_lmiga) == 0) return null;

    foreach ($a_lmiga as $lmiga) {
      if (!$lmiga->control_evento()) continue;

      return Efspax_xestor::operacion_redirect($e, $e->evento()->subnome(0), 1);
    }


    return null;
  }

  protected static function __lmiga($txt, $url, $readonly = false) {
    $lmiga = new Span("lmiga", $txt);

    $lmiga->readonly = $readonly;

    $lmiga->clase_css("default" , "navegador_lmiga");
    $lmiga->clase_css("readonly", "navegador_lmiga_r");
    
    if ( $readonly ) return $lmiga;
    
    if ($url != null)
      $lmiga->pon_eventos("onclick", "window.location.replace('{$url}')");

    //~ $lmiga->envia_submit("onclick");

    return $lmiga;
  }

  private static function migas_json_0($lista_json) {
    return "
<script type=\"application/ld+json\">
{
 \"@context\": \"http://schema.org\",
 \"@type\": \"BreadcrumbList\",
 \"itemListElement\":
 [
  {$lista_json}
 ]
}
</script>";
  }

  private static function migas_json_1($posicion, $url, $nome) {
    return "
      {
       \"@type\": \"ListItem\",
       \"position\": {$posicion},
       \"item\":
       {
        \"@id\": \"{$url}\",
        \"name\": \"{$nome}\"
        }
      }";
  }
}

//************************************

final class Elmt_paxinador_2 extends Elmt_paxinador {
  public function __construct(array $_migas) {
    parent::__construct(new Paxinador_obd());
    
    $this->config_smigas_2( $_migas );
  }

  public function config_usuario(IEfspax_xestor $e = null) {
    parent::config_usuario($e);
  }
}
