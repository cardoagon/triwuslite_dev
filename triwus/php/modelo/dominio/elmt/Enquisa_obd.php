<?php

/*************************************************

    Tilia Framework v.0

    Enquisa_obd.php
    
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


final class Enquisa_obd extends Elmt_obd {
  
  const tipo_bd = "ENQUISA";
  
  public $eer_updates = null;
  
  public function __construct() {
    parent::__construct();

    $this->atr("creado")->valor = date("YmdHmd");    
    $this->atr("grafica")->valor = "q";
  }
 
  public function mapa_bd() {   
    return new Enquisa_mbd();
  }
 
  protected function tipo_bd() {
    return self::tipo_bd;
  }
   
  public function update(FS_cbd $cbd) {
    if (!parent::update($cbd)) return false;

    return self::update_respostas($cbd, $this->atr("id_elmt")->valor, $this->a_updates);
  }
   
  public function insert(FS_cbd $cbd) {
    if (!parent::insert($cbd)) return false;

    return self::update_respostas($cbd, $this->atr("id_elmt")->valor, $this->a_updates);
  }
  
  public function delete(FS_cbd $cbd, $delete_base = true) {
    if (!parent::delete($cbd)) return false;
    
    $sql = "delete from elmt_enquisa_resposta where id_elmt = " . $this->atr("id_elmt")->valor;
    
    return $cbd->executa($sql);
  }

  public function select_eer(FS_cbd $cbd = null) {
    if ($cbd == null) $cbd = new FS_cbd();
    
    if (($id_elmt = $this->atr("id_elmt")->valor) == null) return null;
    
    $r = $cbd->consulta("select * from elmt_enquisa_resposta where id_elmt = {$id_elmt}");
    
    while ($a = $r->next()) {
      $eer = new Enquisa_resposta_obd();
      
      $eer->post($a);
      
      $a_elmt[$a['id_eer']] = $eer;
    }
    
    return $a_elmt;
  }

  private static function update_respostas(FS_cbd $cbd, $id_elmt, $a_updates) {
    //* falla, rehacer borra_todo, inserta_todo
    if ($a_updates == null) return true;
    
    foreach($a_updates as $k=>$v) {
      if ($v == -1) {
        $sql = "delete from elmt_enquisa_resposta where id_elmt = {$id_elmt} and id_eer = {$k}";
        
        if (!$cbd->executa($sql)) return false;
      }
      elseif($k < 100) {
        $sql = "update elmt_enquisa_resposta set resposta = '{$v}' where id_elmt = {$id_elmt} and id_eer = {$k}";
        
        if (!$cbd->executa($sql)) return false;
      }
      else {
        $eer = new Enquisa_resposta_obd();
        
        $eer->atr("id_elmt")->valor  = $id_elmt;
        $eer->atr("resposta")->valor = $v;
        
        if (!$eer->insert($cbd)) return false;
      }
    }
    
    return true;
  }
}

//--------------------------------------------------------

final class Enquisa_resposta_obd extends Obxeto_bd {

  public function __construct() {
    parent::__construct();
    
    $this->atr("ct")->valor = "0";
  }
 
  public function mapa_bd() {   
    return new Enquisa_resposta_mbd();
  }
 
  public function insert(FS_cbd $cbd = null) {
    if ($cbd == null) $cbd = new FS_cbd();
    
    $this->calcula_id($cbd);

    return $cbd->executa($this->sql_insert($cbd));
  }
 
  public static function resposta($id_elmt, $id_eer) {
    $sql = "update elmt_enquisa_resposta set ct = ct +1 where id_elmt = {$id_elmt} and id_eer = {$id_eer}";
    
    $cbd = new FS_cbd();
    
    return $cbd->executa($sql);
  }

  private function calcula_id(FS_cbd $cbd) {
    if (($id_elmt = $this->atr("id_elmt")->valor) == null) 
      die("Enquisa_resposta_obd.calcula_id(), ((id_elmt = this.atr('id_elmt').valor) == null)");
    
    $r = $cbd->consulta("select max(id_eer) + 1 as id from elmt_enquisa_resposta where id_elmt = {$id_elmt}");
    
    $a = $r->next();
    
    if ($a['id'] == null) $a['id'] = 1;
    
    $this->atr("id_eer")->valor = $a['id'];
  }
}

//********************************************************

final class Enquisa_mbd extends Elmt_mbd {
  public function __construct() {
    parent::__construct();
    
    $t = new Taboa_dbd("elmt_enquisa");

    $t->pon_campo("id_elmt", new Numero(), true);
    $t->pon_campo("pregunta");
    $t->pon_campo("grafica");
    $t->pon_campo("creado", new Data("YmdHis"));
    $t->pon_campo("baixa", new Data("YmdHis"));
        
    $this->relacion_fk($t, array("id_elmt"), array("id_elmt"));
  }
}

//--------------------------------------------------------

final class Enquisa_resposta_mbd extends Mapa_bd {
  public function __construct() {
    parent::__construct();
    
    $t = new Taboa_dbd("elmt_enquisa_resposta");

    $t->pon_campo("id_elmt", new Numero(), true);
    $t->pon_campo("id_eer", new Numero(), true);
    $t->pon_campo("resposta", new Varchar());
    $t->pon_campo("ct", new Numero());
    
    $this->pon_taboa($t);
  }
}

