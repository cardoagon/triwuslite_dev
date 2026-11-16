<?php

abstract class Prgf_config extends Paragrafo {
  protected $id_idioma = null;
  
  public function __construct(Paragrafo_obd $p, $nome, Config_obd $cnfg) {
    parent::__construct($p, $nome);

    $this->ptw = $this->cnfg_ptw($cnfg);

    $this->alg_html_elmts = 2;

    $elmts = $this->obxetos("elmt");

    if (count($elmts) == 0) return;

    foreach($elmts as $elmt) {
//~ echo $elmt->nome_completo() . "::" . $this->pos_elmt($elmt) . "<br>";

      $elmt->obxeto("illa")->clase_css("default", ""); //* Elimina el acceso directo a los elementos para operaciones de posicionamiento.
      
      if (($btnr = $elmt->obxeto("btnr")) == null) continue;

      $btnr->obxeto("bEditCopy")->visible = false;
      $btnr->obxeto("bMove"    )->visible = false;
      $btnr->obxeto("bEliminar")->visible = false;
    }
  }

  public function config_usuario(IEfspax_xestor $e = null) {
    parent::config_usuario($e);

    $this->width = null;

    if ($e == null) return;

    if (($this->id_idioma = $e->id_idioma()) == null) $this->id_idioma = "es";
  }

  public function config_edicion() {
    parent::config_edicion();

    $this->obxeto("btnr")->visible = false;
  }


  public function preparar_saida(Ajax $a = null) {
    if ($a == null) {
      parent::preparar_saida();
      
      return;
    }
    
    if (($a_obx = $this->obxetos("elmt")) != null) {
      foreach($a_obx as $k=>$obx) {
        if (isset($this->sup_elmt[$k])) continue;

        if (($cb = $obx->js_callback()) != null) 
          $a->pon_ok(true, $cb);
        else
          $obx->preparar_saida($a);
      }
    }
  }

  abstract protected function cnfg_ptw(Config_obd $cnfg = null);
}

//***********************************************

final class Prgf_cab extends Prgf_config {
  const ancla = "site_cab";

  private $pos_logo = 1;

  public function __construct(Site_obd $s, Config_obd $c, Paxina_obd $p) {
    parent::__construct($s->cab(), "cab", $c);

    $this->pnome = "Cabecera";

    $this->clase_css("default", "celda_cabeceira");
    $this->clase_css("over", "celda_cabeceira");

    if (($elmt_logo = $this->elmt_logo()) != null) {
      $elmt_logo->pon_maxwh($c);

      if (($btnr = $elmt_logo->obxeto("btnr")) != null) $btnr->obxeto("bEditar")->envia_ajax("onclick");

      $elmt_logo->clase_css("default", "celda_elmt_cabpe");
      $elmt_logo->clase_css("over", "celda_elmt_cabpe_over");

      $elmt_logo->obxeto("imaxe")->clase_css("default", "celda_logo");
    }

    if (($elmt_txt = $this->elemento_pos(2)) != null) {
      $elmt_txt->clase_css("default", "cab_titulo");
      $elmt_txt->clase_css("edit"   , "cab_titulo");
      
      $elmt_txt->obxeto("texto")->body_class = "cab_titulo";
    }

  }

  public function elmt_logo() {
    return $this->elemento_pos($this->pos_logo);
  }

  public function html():string {
    return "<header><a name='" . self::ancla . "'></a>" . parent::html() . "</header>";
  }

  protected function cnfg_ptw(Config_obd $cnfg = null) {
    return $cnfg->ptw_prgf("cab");
  }
}

//***********************************************

final class Prgf_pe extends Prgf_config {
  public function __construct(Site_obd $s, Config_obd $c) {
    parent::__construct($s->pe(), "pe", $c);

    $this->pnome = "Pie de página";

    $this->clase_css("default", "celda_pe");
    $this->clase_css("over"   , "celda_pe_over");

    if (($elmt_txt = $this->elemento_pos(1)) != null) {
      $elmt_txt->clase_css("default", "pe_texto");
      $elmt_txt->clase_css("edit"   , "pe_texto");

      $elmt_txt->obxeto("texto")->body_class = "pe_texto";

      //~ if (($btnr = $elmt_txt->obxeto("btnr")) != null) $btnr->config_visibles();
    }

    if (($elmt_carrusel = $this->elemento_pos(3)) != null) {
      $elmt_txt->clase_css("default", "pe_texto");
      $elmt_txt->clase_css("edit"   , "pe_texto");

      $elmt_txt->obxeto("texto")->body_class = "pe_texto";

      if (($btnr = $elmt_txt->obxeto("btnr")) != null) $btnr->config_visibles();
    }

  }

  public function html():string {
    return "<footer>" . parent::html() . "</footer>";
  }

  protected function cnfg_ptw(Config_obd $cnfg = null) {
    return $cnfg->ptw_prgf("pe");
  }
}
