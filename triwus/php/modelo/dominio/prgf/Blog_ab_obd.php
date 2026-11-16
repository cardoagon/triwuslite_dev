<?php

/*************************************************

    Tilia Framework v.0

    Blog_ab_obd.php
    
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

final class Blog_ab_obd extends Paragrafo_obd {
  public function __construct() {
    parent::__construct();

    $this->atr("nome")->valor = Msx::articulo_prgf_ab_titulo;
  }
 
  public static function inicia(FS_cbd $cbd, $id_prgf) {
    $prgf = new Blog_ab_obd();
    
    if ($id_prgf == null) return $prgf;
    
    $prgf->atr("id_paragrafo")->valor = $id_prgf;
    
    $prgf->select($cbd, $prgf->atr("id_paragrafo")->sql_where($cbd));
  
    return $prgf;
  }

  public function insert(FS_cbd $cbd) {
    $this->atr("nome")->valor = Msx::articulo_prgf_ab_titulo;
    
    return parent::insert($cbd);
  }
  
}

?>