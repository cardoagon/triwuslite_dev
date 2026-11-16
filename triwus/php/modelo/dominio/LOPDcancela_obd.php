<?php

/*************************************************

    Tilia Framework v.0

    LOPDcancela_obd.php
    
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


class LOPDcancela_obd extends Obxeto_bd  {
  public function __construct() {
    parent::__construct();
  }
  
  public function mapa_bd() {
    return new LOPDcancela_mbd();
  }

  public static function inicia(FS_cbd $cbd, $id_usuario) {
    $o = new LOPDcancela_obd();

    $o->select($cbd, "id_usuario = {$id_usuario}");
    
    return $o;
  }

  public function select(FS_cbd $cbd, $where = null, $orderby = null) {
    $r = $cbd->consulta($this->sql_select($cbd, $where, $orderby));
    
    if (!$a = $r->next()) return;
    
    $this->post($a);
  }
  
  public function en_custodia() {
    return $this->atr("id_usuario")->valor != null;
  }

  public static function cancela(FS_cbd $cbd, $id_site, $baixa = "now()") {
    $sql = "update site set baixa = {$baixa} where id_site = {$id_site}";
    
    return $cbd->executa($sql);
  }
}

//-----------------------------------------------

final class LOPDcancela_mbd extends Mapa_bd {
  public function __construct() {
    $t = new Taboa_dbd("v_lopdcancela_site");
    
    $t->pon_campo("id_site"   , new Numero());
    $t->pon_campo("id_usuario", new Numero());
    $t->pon_campo("baixa"     , new Data("YmdHis"));
    $t->pon_campo("fin"       , new Data("YmdHis"));
    $t->pon_campo("custodia"  , new Numero());
    
    $this->pon_taboa($t);
  }
}


?>