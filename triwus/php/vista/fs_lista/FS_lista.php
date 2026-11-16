<?php

class FS_lista extends CLista {
  public function __construct($nome, FS_ehtml $ehtml, $ptw = null) {
    parent::__construct($nome, "FS_cbd", $ehtml, $ptw);
  }

  public function declara_css() {
    return array(Refs::url("css/fs_lista.css"));
  }

  public function paxinar(?int $pax = null):void { 
//~ echo "2.-pax:: $pax<br>";
        if ( $pax == null                   ) $pax = 1;
    elseif ( !is_numeric($pax)              ) $pax = 1;
    elseif ( ($pax = round(abs($pax))) == 0 ) $pax = 1;
    
//~ echo "3.-pax:: $pax<br>";

    $this->limit_0 = ($pax - 1) * $this->limit_f; 
  }
}
