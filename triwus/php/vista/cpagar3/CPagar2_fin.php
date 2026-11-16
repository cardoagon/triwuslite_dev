<?php


class CPagar2_fin extends Componente
               implements ICPagar2_paso {

  const ptw_0 = "ptw/cpagar3/cpagar3_fin.html";


  private $id_fpago = -1;

  public function __construct() {
    parent::__construct("cpagar2_fin", Refs::url(self::ptw_0));

    $this->pon_obxeto(new Checkbox("chlogin", false));
    $this->pon_obxeto(new Checkbox("ch_cxs" , false));
    
    $this->pon_obxeto(new Param("href_cxs", "legal.php#" . md5("legal-3")));
  }

  public function post_u(FGS_usuario $u = null) {
    if ($u == null)      return;

    $this->obxeto("ch_cxs" )->pon_etq( CPagar2_ml::$cpago2["2"][$this->pai->id_idioma], 2 );
    $this->obxeto("chlogin")->pon_etq( CPagar2_ml::$cpago2["6"][$this->pai->id_idioma], 2 );

    $this->obxeto("chlogin")->visible = !$u->validado();
  }

  public function rexistro() {
    return $this->obxeto("chlogin")->valor;
  }

  public function cxs() {
    return $this->obxeto("ch_cxs")->valor;
  }

  public function validar() {}
}
