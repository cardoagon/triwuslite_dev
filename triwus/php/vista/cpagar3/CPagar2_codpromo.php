<?php


final class CPagar2_codpromo extends Componente {
  const ptw_0 = "ptw/cpagar3/ccarro3_codigo.html";

  public function __construct() {
    parent::__construct("cpagar2_ccodigo", Refs::url(self::ptw_0));

    //~ $this->visible = false;

    $this->pon_obxeto( self::__codpromocion() );

    $this->pon_obxeto( new Button("bAceptar", "Validar código") );
    
    //~ $this->pon_obxeto( new Param("p5") );
    //~ $this->pon_obxeto( new Param("p6") );
    $this->pon_obxeto( new Div("codigos_validados") );
    $this->pon_obxeto( new Div("erro_codigo") );

    $this->obxeto("bAceptar")->sup_eventos(); //* de atrezo, o evento salta sii se hai un cambio en codpromocion.
    $this->obxeto("bAceptar")->style("default", " margin-right: 17px;");
    
    $this->obxeto("codigos_validados")->visible = false;
    $this->obxeto("codigos_validados")->clase_css("default", "txt_msx");
    
    $this->obxeto("erro_codigo")->visible = false;
    $this->obxeto("erro_codigo")->clase_css("default", "txt_err");
  }

  public function operacion(EstadoHTTP $e) {
//~ echo $e->evento()->html();

    if ($this->obxeto("codpromocion")->control_evento()) return $this->operacion_validar_codpromocional($e);

    return null;
  }

  public function post_u(FGS_usuario $u = null) {
    $this->obxeto("codigos_validados")->visible = false;
    $this->obxeto("erro_codigo"      )->visible = false;
    
    if ($u == null) return;

    $xpromos = $u->cpago()->carro()->xpromos();

    if ($xpromos == null) return;

    $this->html_codigos_validados_0( $xpromos->promos_codigo() );
  }
  
  public function operacion_validar_codpromocional(EstadoHTTP $e) {
    $this->obxeto("codigos_validados")->visible = false;
    $this->obxeto("erro_codigo"      )->visible = false;
    
    $ucarro = $this->pai->obxeto("ccarro2_lista")->ucarro;
    
    $_promo = $ucarro->post_promo_codigo( $this->obxeto("codpromocion")->valor() );

//~ echo "<pre>" . print_r($_promo, 1) . "</pre>";

    if ($_promo != null)
      $this->html_codigos_validados_0( array($_promo) );
    else
      $this->html_codigo_erro(  );


    $this->preparar_saida($e->ajax());
    $this->pai->obxeto("ccarro2_lista")->preparar_saida($e->ajax());

    return $e;
  }

  private function html_codigo_erro(  ) {
    $this->obxeto("erro_codigo")->visible = true;

    $cod = $this->obxeto("codpromocion")->valor();

    $html = "<div>El código: <b>{$cod}</b>, no es válido o no hay un producto promocionado en el carrito.</div>";

    $this->obxeto("erro_codigo")->post( $html );
  }

  private function html_codigos_validados_0( $_promos ) {
    if (!is_array($_promos)) return;

    $html = "";
    foreach($_promos as $_promo)
      $html .= $this->html_codigos_validados( $_promo );
    

    $this->obxeto("codigos_validados")->visible = $html != "";

    $this->obxeto("codigos_validados")->post( $html );
  }

  private function html_codigos_validados( $_promo ) {
    if ($_promo == null) return null;
    
    if ($_promo['regalo'])
      $d = "Escoje tu regalo";
    elseif ($_promo['compra_min'] > 0)
      $d = "El descuento se aplicará sólo a los productos promocionados, para compras superiores a {$_promo['compra_min']} €";
    else 
      $d = "El descuento se aplicará sólo a los productos promocionados";

//~ print_r($_promo);
      
    return "<div>CÓDIGO VALIDADO: <b>{$_promo['codigo']}</b>. Promoción: <b>{$_promo['nome']}</b>.</div>
            <div><b>{$d}</b>.</div>";
  }

  private static function __codpromocion() {
    $t = Panel_fs::__text("codpromocion", 9, 9, " ");

    $t->clase_css("default", "text cpagar3_text");

    $t->style("default" , "text-align: center; width: 44%; min-width: 121px; margin-right: 17px;");

    $t->pon_eventos("onchange", "cpagar2_codprom_onchange(this)");


    return $t;
  }
}

