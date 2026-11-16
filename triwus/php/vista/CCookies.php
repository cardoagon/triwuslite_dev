<?php

final class CCookies extends Componente {
  //~ private static $_ml = array("en" => array(1 => "I agree" , 2 => "Find out more"  , 3 => "Reject"  ),
                              //~ "es" => array(1 => "Aceptar" , 2 => "Más información", 3 => "Rechazar"),
                              //~ "fr" => array(1 => "Accepter", 2 => "Savoir plus"    , 3 => "Reject"  ),
                              //~ "gl" => array(1 => "Aceptar" , 2 => "Saber máis"     , 3 => "Rexeitar"),
                              //~ "pt" => array(1 => "Entendí" , 2 => "Saber máis"     , 3 => "Rejeitar")
                            //~ );

  private int    $trw_cookies_mostrar = -1;
  private string $id_idioma;

  public function __construct(Site_config_obd $sc, $id_idioma) {
    parent::__construct("ccookies");

    $this->visible = $sc->atr("cookies")->valor == 1; //* configuración de cookies no pcontrol.

    $this->pon_idioma($id_idioma);
  }

  public function pon_idioma($id_idioma):void {
    if (!$this->visible) return;

    if ($id_idioma == null) $id_idioma = "es";
    if ( !isset(Msx::$rgpd_msx[$id_idioma]) ) $id_idioma = "en";

    $this->id_idioma = $id_idioma;

    $this->pon_laceptar();
    $this->pon_lVerMais();
    $this->pon_lNecs   ();
 }

  public function operacion(EstadoHTTP $e) {
    if (!$this->visible) return null;

    if ($this->obxeto("bAceptar")->control_evento()) return $this->operacion_menu($e, 2);
    if ($this->obxeto("lNecs"   )->control_evento()) return $this->operacion_menu($e, 1);

    return null;
  }

  public function preparar_saida(Ajax $a = null) {
    $html = $this->__ptw();

    $this->obxeto("illa")->post($html);

    if ($a == null) return;


    $a->pon($this->obxeto("illa"));

    $this->obxeto("illa")->post(null);
  }

  private function __ptw():string {
    if (!$this->visible) return "<script>ccookies_config(-2)</script>"; //* desactiva script para control de cookies de terceiros.

    //~ $this->pon_daviso  ($sc->atr("id_site")->valor);
    //~ $this->pon_laceptar();
    //~ $this->pon_lVerMais();
    //~ $this->pon_lNecs   ();


    $daviso   = $this->__aviso();
    $baceptar = $this->obxeto("bAceptar")->html();
    $lnecs    = $this->obxeto("lNecs"   )->html();
    $lvermais = $this->obxeto("lVerMais")->html();


    return "
<div id='trw_cookies_men' class='ccookies_sites_0'></div>
<script>
  ccookies_config({$this->trw_cookies_mostrar});

  const trw_cook_aviso  = `{$daviso}`;
  const trw_cook_acept  = `{$baceptar}`;
  const trw_cook_rexei  = `{$lnecs}`;
  const trw_cook_mais   = `{$lvermais}`;
</script>";
  }

  private function operacion_menu(EstadoHTTP $e, int $config):EstadoHTTP {
    $this->trw_cookies_mostrar = $config;

    $e->ajax()->pon_ok();

    return $e;
  }
/*
  private function pon_daviso($id_idioma, $id_site) {
    $f = "cookies-aviso.html";

    $d = Efs::url_legais($id_site);

    if (!is_dir($d)) mkdir($d);

    if (!is_file("{$d}{$f}")) copy("ptw/paneis/pcontrol/ceditor_legais/{$f}", "{$d}{$f}");

    $d = new Span("daviso", file_get_contents("{$d}{$f}"));

    $this->pon_obxeto($d);
  }
*/
  private function __aviso() {
    return "<span>" . Msx::$rgpd_msx[$this->id_idioma] . "</span>";
  }

  private function pon_laceptar() {
    $l = new Link("bAceptar", Msx::$rgpd_btn[$this->id_idioma][1]);

    $l->clase_css("default", "ccookies_boton");    
    $l->pon_eventos( "onclick", "ccookies_aceptar_click(this)"             );


    $this->pon_obxeto($l);
  }

  private function pon_lVerMais() {
    $href = "legal.php#" . md5("legal-6");


    $l = new Link("lVerMais", Msx::$rgpd_btn[$this->id_idioma][2], "_blank", $href);

    $l->clase_css("default", "ccookies_boton");

    //$l->style      ( "default", "color: #fff; text-decoration: underline;" );
    //$l->pon_eventos( "onclick", "ccookies_aceptar_click(this)"             );

    $this->pon_obxeto($l);
  }

  private function pon_lNecs() {
    $l = new Link("lNecs", Msx::$rgpd_btn[$this->id_idioma][3]);

    $l->clase_css("default", "ccookies_boton");
    $l->pon_eventos( "onclick", "ccookies_necesarias_click(this)"          );

    $this->pon_obxeto($l);
  }
}
