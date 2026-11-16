<?php

final class CLogo_paxina extends CLogo {
  public function __construct(Paxina_obd $p) {
    parent::__construct($p, "clogopax");
  }

  protected function __inicia(ICLogo $icl) {
    $this->icl = $icl;

    $a_src_aux = $icl->iclogo_src(new FS_cbd());

    $this->a_src = null;
    $this->a_txt = null;

    if ($a_src_aux != null) {

      foreach($a_src_aux as $id_elmt=>$elmt) {
        $this->a_src[$id_elmt] = $elmt->url($this->mini);
        //~ $this->a_txt[$id_elmt] = self::txt($elmt->atr("nome")->valor, $elmt->atr("notas")->valor);
      }

      $this->indice_src = array_keys($a_src_aux);
    }

    $this->obxeto("flogo")->visible = $this->bmais != 0;

    $this->selecciona($icl->iclogo_id());
  }
}

