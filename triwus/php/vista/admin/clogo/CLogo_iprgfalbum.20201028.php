<?php

final class CLogo_iprgfalbum extends CLogo {
  public function __construct(Paxina_album_obd $p) {
    parent::__construct($p, "clogo_prgfialbum", 0, false);

    $this->obxeto("bant")->envia_ajax("onclick");
    $this->obxeto("bseg")->envia_ajax("onclick");
  }

  protected function __inicia(ICLogo $icl) {
    $this->icl = $icl;

    $a_src_aux = $icl->iclogo_src(new FS_cbd());

    $this->a_src = null;
    $this->a_txt = null;

    if ($a_src_aux != null) {

      foreach($a_src_aux as $id_elmt=>$elmt) {
        //~ echo "{$this->a_src[$id_elmt]}::{$this->mini}<br />";
        $this->a_src[$id_elmt] = $elmt->url($this->mini);
        $this->a_txt[$id_elmt] = self::txt($elmt->atr("nome")->valor, $elmt->atr("notas")->valor);
      }

      $this->indice_src = array_keys($a_src_aux);
    }

    $this->obxeto("flogo")->visible = $this->bmais != 0;

    $this->selecciona($icl->iclogo_id());
  }

  public static function txt($nome, $notas) {
    if (($nome != null) && ($notas != null)) return "{$nome}. {$notas}";

    return "{$nome}{$notas}";
  }

}

