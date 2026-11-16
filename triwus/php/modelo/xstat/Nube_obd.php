<?php

/*************************************************

    Tilia Framework v.0

    Nube_obd.php
    
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

final class Nube_obd extends    Obxeto_bd {
  
  public function __construct($id_paxina = null, $nube = null) {
    parent::__construct();
    
    $this->atr("id_paxina")->valor = $id_paxina;
    $this->atr("nube"     )->valor = $nube;
  }
 
  public function mapa_bd() {   
    return new Nube_mbd();
  }

  public function select(FS_cbd $cbd, $where = null, $orderby = null) {
    $r = $cbd->consulta($this->sql_select($cbd, $where, $orderby));
    
    if ($a = $r->next()) $this->post($a);
  }

  public function anotar(FS_cbd $cbd) {
    if ($this->atr("id_paxina")->valor == null) die("Nube_obd::anotar(), this.atr('id_paxina').valor == null");

    if (!$cbd->executa($this->sql_delete($cbd))) return false;
    
      
    return $cbd->executa($this->sql_insert($cbd));
  }

  public static function nube($id_paxina, FS_cbd $cbd = null) {
    if ($cbd == null) $cbd = new FS_cbd();
    
    $n = new Nube_obd($id_paxina);
    
    $n->select($cbd, $n->atr("id_paxina")->sql_where($cbd));
    
    return $n->atr("nube")->valor;
  }

  public static function delete($id_paxina, FS_cbd $cbd = null) {
    $n = new Nube_obd($id_paxina);
    
    $n->select($cbd, $n->atr("id_paxina")->sql_where($cbd));
    
    return $cbd->executa($n->sql_delete($cbd));
  }
}

//------------------------------------------

final class Nube_mbd extends Mapa_bd {
  public function __construct() {
    $t = new Taboa_dbd("stats_nube");
    
    $t->pon_campo("id_paxina", new Numero (), true);
    $t->pon_campo("nube"     , new Varchar()     );
    $t->pon_campo("nubeb"    , new Varchar()     );
    
    $this->pon_taboa($t);
  }
}

?>