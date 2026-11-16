<?php


/*************************************************

    Triwus Framework v.0

    Esubfs.php

    Author: Carlos Domingo Arias González

    Copyright (C) 2011

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation version 3.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.

*************************************************/


abstract class Esubfs extends Efs {
  public function __construct(Paxina $p) {
    parent::__construct($p);
  }

  public function __site_obd($id_site = null, FS_cbd $cbd = null) {
    if (($pai = $this->pai()) == null) return null;

    return $pai->__site_obd($id_site, $cbd);
  }

  protected function superconfig() {
    if (($pai = $this->pai()) == null) return null;


    $site = $this->__site_obd();

    $a_sc['site_id']     = $site->atr("id_site")->valor;
    $a_sc['site_nome']   = $site->atr("nome")->valor;
    $a_sc['oms_id']      = $pai->obxeto("id_oms")->valor();
    $a_sc['paxina_id']   = $pai->obxeto("id_paxina")->valor();
    $a_sc['paxina_nome'] = $pai->obxeto("nome_paxina")->valor();
    $a_sc['idioma_id']   = $pai->id_idioma();
    $a_sc['mobil']       = ($pai->config()->atr("tipo")->valor != "escritorio")?1:0;
    $a_sc['usuario_id']  = $pai->usuario()->atr("id_usuario" )->valor;

//~ echo "<pre>" . print_r($a_sc, true) . "</pre>";

    return $a_sc;
  }

  protected static function __paxsub($titulo) {
    $p = new Paxina($titulo);

    $p->head->pon_js("../triwus/js/basico.sub.js");

    $p->head->pon_meta("author"   , "triwus.com");
    $p->head->pon_meta("copyright", "&copy;triwus");
    $p->head->pon_meta("robots"   , "NOINDEX,NOFOLLOW,NOARCHIVE");
    //~ $p->head->pon_meta("viewport" , "width=device-width, initial-scale=1");

    $p->body->bgcolor = "transparent";

    return $p;
  }
}

?>