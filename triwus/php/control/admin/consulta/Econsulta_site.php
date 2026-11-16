<?php


final class Econsulta_site extends Econsulta {

  public function __construct(FGS_usuario $u = null) {
    parent::__construct(self::__paxina(), $u);

    $this->pon_obxeto(self::fondo());

    $this->pon_obxeto(new CAcceso_pcontrol ());
    $this->pon_obxeto(new CCambiak_pcontrol());
  }

  public function operacion() {
    if (($e_aux = parent::operacion()) != null) return $e_aux;

    $this->pon_obxeto(self::fondo());


    return $this;
  }

  public function cambiar_panel() {
    $this->obxeto("cacceso" )->visible = !$this->obxeto("cacceso" )->visible;
    $this->obxeto("ccambiak")->visible = !$this->obxeto("ccambiak")->visible;

    if (!$this->evento()->ajax()) return;

    $this->obxeto("cacceso" )->preparar_saida( $this->ajax() );
    $this->obxeto("ccambiak")->preparar_saida( $this->ajax() );
  }

  private static function __paxina() {
    $px = new Paxina("triwus.com - Panel de control");

    $px->formulario()->action = "pcontrol.php";
    $px->formulario()->ptw    = "ptw/form/fpcontrol_login.html";

    $px->pon_panel(new Panel_site_pe());

    return $px;
  }

  private static function fondo() {
    $_f = scandir("imx/pcontrol/login/");

    $i = rand(2, count($_f) - 1);


    return new Param("fondo", $_f[$i]);
  }
}
