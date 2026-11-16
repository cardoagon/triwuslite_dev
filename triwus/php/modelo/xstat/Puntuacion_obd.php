<?php

/*************************************************

    Tilia Framework v.0

    Puntuacion_obd.php
    
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

final class Puntuacion_obd extends Obxeto_bd {
  
  public function __construct() {
    parent::__construct();
    
    $this->atr("sum")->valor = "0";
    $this->atr("sup")->valor = "0";
  }
 
  public function mapa_bd() {   
    return new Puntuacion_mbd();
  }

  public static function inicia(FS_cbd $cbd, $id_puntos) {
    $pt = new Puntuacion_obd();
    
    $pt->atr("id_puntos")->valor = $id_puntos;
      
    $pt->select($cbd, $pt->atr("id_puntos")->sql_where($cbd));
    
    return $pt;
  }
  
  public function select(FS_cbd $cbd, $where = null, $orderby = null) {
    $r = $cbd->consulta($this->sql_select($cbd, $where, $orderby));
    
    if ($a = $r->next()) $this->post($a);
  }
  
  public static function puntuar($id_puntos, $suma) {
    //* PRECOND: $id_puntos != null
    
    $set = ($suma > 0)?"sum = sum + {$suma}":"sup = sup + abs({$suma})";
    
    $cbd = new FS_cbd();
    
    return $cbd->executa("update stats_puntos set {$set} where id_puntos = {$id_puntos}");
  }
  
  public function update(FS_cbd $cbd) {
    if ($this->atr("id_puntos")->valor != null) return $cbd->executa($this->sql_update($cbd));
    
    return $this->insert($cbd);
  }

  public function insert(FS_cbd $cbd) {
    if ($this->atr("sum")->valor == null) $this->atr("sum")->valor = "0";
    if ($this->atr("sup")->valor == null) $this->atr("sup")->valor = "0";
    
    if (!$cbd->executa($this->sql_insert($cbd))) return false;
    
    $this->calcula_id($cbd);
    
    return true;
  }

  public function delete(FS_cbd $cbd) {
    if ($this->atr("id_puntos")->valor == null) return true;
    
    return $cbd->executa($this->sql_delete($cbd));
  }
  
  private function calcula_id(FS_cbd $cbd) {
    $r = $cbd->consulta("select LAST_INSERT_ID() as id");
    
    if (!$a = $r->next()) die("Puntuacion_obd.calcula_id(), (!a = r.next())");
        
    $this->atr("id_puntos")->valor = $a['id'];
  }
}

//------------------------------------------

final class Puntuacion_mbd extends Mapa_bd {
  public function __construct() {
    $t = new Taboa_dbd("stats_puntos");
    
    $t->pon_campo("id_puntos", new Numero(), true);
    $t->pon_campo("sum"      , new Numero());
    $t->pon_campo("sup"      , new Numero());
    
    $this->pon_taboa($t);
  }
}

?>