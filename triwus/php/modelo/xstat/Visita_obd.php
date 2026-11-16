<?php

/*************************************************

    Tilia Framework v.0

    Visita_obd.php
    
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


final class Visita_obd extends    Obxeto_bd {
  
  public function __construct() {
    parent::__construct();
  }
 
  public function mapa_bd() {   
    return new Visita_mbd();
  }

  public static function inicia_Y($id_paxina, $Y) {
    return self::inicia($id_paxina, $Y, "0", "0");
  }

  public static function inicia_n($id_paxina, $Y, $n) {
    return self::inicia($id_paxina, $Y, $n, "0");
  }

  public static function inicia_z($id_paxina, $Y, $z) {
    return self::inicia($id_paxina, $Y, "0", $z);
  }

  public function select(FS_cbd $cbd, $where = null, $orderby = null) {
    $r = $cbd->consulta($this->sql_select($cbd, $where, $orderby));
    
    if ($a = $r->next()) $this->post($a);
  }

  public function anotar(FS_cbd $cbd) {
    $this->atr("ct")->valor++;
    
    if ($this->atr("ct")->valor == 1) 
      return $cbd->executa($this->sql_insert($cbd));
    else
      return $cbd->executa($this->sql_update($cbd));
  }
    
  public static function __borra_z(FS_cbd $cbd, $dias = 7) {
    $a_dias = self::__dias($dias);
    
    $wz = "0";
    foreach($a_dias as $z=>$txt) $wz .= ", {$z}";
  
    
    $sql = "delete from stats_visita where (n = 0) and (not (z in ({$wz})))";
    
    return $cbd->executa($sql);
  }  

  public static function __dias($n = 7) {
    $s = 86400; //* segundos/dia
    $t = mktime(0, 0, 0, date("n"), date("d"), date("Y"));

    $es = new Espanhol();
    
    $a = [];

    for($i = ($n-1); $i >= 0; $i--) {
      $d = getdate($t - ($i * $s));
      
      $a[$d['yday'] + 1] = $es->dias[$d['wday']];
    }
    
    if (isset($a[$d['yday']]    )) $a[$d['yday']]     .= " (ayer)";
    if (isset($a[$d['yday'] + 1])) $a[$d['yday'] + 1] .= " (hoy)";
    
    return $a;
  }

  public static function __meses($k = 12) {
    $n = date("n");
    $Y = date("Y");
    

    $es = new Espanhol();
    
    $a = null;
    for($i = ($k - 1); $i >= 0; $i--) {
      $Yi = $Y;
      if (($ni = ($n - $i)) < 1) {
        $ni += 12; //* num. de meses == 12 == $k !!! coincide pero non ten nada que ver.
        $Yi--;
      }
      
      $a[$ni] = substr($es->meses[$ni - 1], 0, 3) . " {$Yi}";
    }
    
    return $a;
  }

  private static function inicia($id_paxina, $Y, $n, $z) {
    $cbd = new FS_cbd();
    
    $p = new Visita_obd();
    
    $p->atr("id_paxina")->valor = $id_paxina;
    $p->atr("Y")->valor = $Y;
    $p->atr("n")->valor = $n;
    $p->atr("z")->valor = $z;
    $p->atr("ct")->valor = 0;

    $w_p = $p->atr("id_paxina")->sql_where($cbd);
    $w_Y = $p->atr("Y")->sql_where($cbd);
    $w_n = $p->atr("n")->sql_where($cbd);
    $w_z = $p->atr("z")->sql_where($cbd);

    $where = "({$w_p}) and ({$w_Y}) and ({$w_n}) and ({$w_z})";
        
    $p->select($cbd, $where);
       
    return $p;
  }
  
  private static function __where(FS_cbd $cbd, Visita_obd $p) {
    $w_p = $p->atr("id_paxina")->sql_where($cbd);
    $w_Y = $p->atr("Y")->sql_where($cbd);
    $w_n = $p->atr("n")->sql_where($cbd);
    $w_z = $p->atr("z")->sql_where($cbd);

    return "({$w_p}) and ({$w_Y}) and ({$w_n}) and ({$w_z})";
  }
}

//******************************************

class Visita_mbd extends Mapa_bd {
  public function __construct() {
    $t = new Taboa_dbd("stats_visita");
    
    $t->pon_campo("id_paxina", new Numero(), true);
    $t->pon_campo("Y", new Numero(), true);
    $t->pon_campo("n", new Numero(), true);
    $t->pon_campo("z", new Numero(), true);
    $t->pon_campo("ct", new Numero());
    
    $this->pon_taboa($t);
  }
}
