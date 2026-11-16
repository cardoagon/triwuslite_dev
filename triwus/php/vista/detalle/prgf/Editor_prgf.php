<?php

class Editor_prgf extends Editor_btnr_oeste {
  protected $prgf = null;
  
  private $numcols_0 = null;

  public function __construct(Paragrafo $p) {
    parent::__construct(null , "Editor de p&aacute;rrafo", "ptw/paragrafo/editor_prgf.html");

    $this->prgf = $p;

    $this->numcols_0 = $p->num_cols(null, true);

    $this->ancla_baceptar = Btnr::__ancla($p);

    if ($p->orderby    == null) $p->orderby    = "1";
    if ($p->i_entradas == null) $p->i_entradas = "0"; //* paxinado. en deshuso. 20190215.
    
    $this->pon_obxeto(new Param("url"      , $p->url      ));
    $this->pon_obxeto(new Param("url_ancla", $p->__ancla()));

    $this->pon_obxeto(Panel_fs::__text("nome", 20, 255));
    //~ $this->pon_obxeto(new Select("s_columnas_mobil", array("0"=>"No ajustar", "1"=>"Ajustar por filas", "2"=>"Ajustar por columnas")));
    $this->pon_obxeto(self::__sColumnas());
    $this->pon_obxeto(new Editor_sprgf($p));

    $this->obxeto("nome"            )->post($p->pnome);
    $this->obxeto("sColumnas"       )->post($this->numcols_0);
    //~ $this->obxeto("s_columnas_mobil")->post($p->columnas_mobil);

    //~ $this->pon_obxeto(new Link("url", $p->url, "_blank", $p->url));
    //~ $this->obxeto("url")->style("default", "color: #0000ba; font-size: 9px;");
    //~ $this->obxeto("url")->clase_css("default", "trw-editor--url");
  }

  public function operacion(EstadoHTTP $e) {
    if (($e_aux = $this->obxeto("cstyle")->operacion($e)) != null) return $e_aux;

   return parent::operacion($e);
  }

  public function operacion_aceptar(Efs_admin $e) {
    $this->prgf->modificado     = true;
    $this->prgf->pnome          = $this->obxeto("nome")->valor();
    //~ $this->prgf->columnas_mobil = $this->obxeto("s_columnas_mobil")->valor();
    $this->prgf->pcss           = $this->obxeto("cstyle")->style_obd()->atr("css")->valor;

    $numcols_1 = $this->obxeto("sColumnas")->valor();

    $this->prgf->num_cols($numcols_1);


    if ($this->numcols_0 != $numcols_1) $this->prgf->configurar(); //* se cambia o número de columnas, debemos reconfigurar.

    $this->prgf->preparar_saida($e->ajax());

    return $e;
  }

  public function declara_baceptar() {
    $b = Panel_fs::__baceptar();

    //~ $b->envia_submit("onclick");
    $b->envia_ajax("onclick", null, "editor_enviaAJAX_resposta");

    return $b;
  }

  private static function __sColumnas() {
    $s = new Select("sColumnas", array(1=>"1", 2=>"2", 3=>"3", 4=>"4", 5=>"2 + 1", 6=>"1 + 2"));

    return $s;
  }

}

//********************************************

class Editor_prgf_novas extends Editor_prgf {
  public function __construct(Prgf_inovas $p) {
    parent::__construct($p);

    $this->ptw = "ptw/paragrafo/editor_prgf_novas.html";

    $this->obxeto("sColumnas")->sup_opcion(5);
    $this->obxeto("sColumnas")->sup_opcion(6);

    //~ $this->obxeto("s_columnas_mobil")->sup_opcion(2);


    $this->pon_obxeto( new Select("sentradas", array(0=>"Sin Paginaci&oacute;n")) );

    $this->obxeto("sentradas")->post(0);

    $this->obxeto("sentradas")->style("default" , "width: 44%;");
    $this->obxeto("sentradas")->style("readonly", "width: 44%;");


    $this->pon_obxeto( Panel_fs::__text("timx_proporcion", 5, 5) );

    $this->obxeto("timx_proporcion")->post($p->inovas_imx_proporcion);

    $this->obxeto("timx_proporcion")->style("default" , "text-align: right;");
    $this->obxeto("timx_proporcion")->style("readonly", "text-align: right;");


    $this->pon_obxeto( new CRS_editor( $p->crs_obd ) );
  }

  public function operacion(EstadoHTTP $e) {
    if (($e_aux = $this->obxeto("crs_editor")->operacion($e)) != null) return $e_aux;

    return parent::operacion($e);
  }

  public function operacion_aceptar(Efs_admin $e) {
    $crs_editor = $this->obxeto("crs_editor");

    if (($e_aux = $crs_editor->operacion_aceptar($e)) != null) {


      return null; //* manter compatibilidade con Btnr_detalle_oeste
    }

    $this->prgf->inovas_imx_proporcion = $this->obxeto("timx_proporcion")->valor();

    //~ $this->prgf->i_entradas                        = $this->obxeto("sentradas")->valor();
    //~ $this->prgf->obxeto("prgf_paxinador")->limit_f = $this->obxeto("sentradas")->valor();
    //~ $this->prgf->i_entradas                        = 0;
    //~ $this->prgf->obxeto("prgf_paxinador")->limit_f = 0;

    $this->prgf->crs_obd($crs_editor->crs_obd);

    return parent::operacion_aceptar($e);
  }

  public function operacion_cancelar(Epaxina_edit $e) {
    if (($e_aux = $this->obxeto("crs_editor")->operacion_cancelar($e)) != null) return $e_aux;

    return Editor_btnr_oeste::operacion_cancelar($e);
  }
}

