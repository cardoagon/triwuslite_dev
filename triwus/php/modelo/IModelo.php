<?php

/*************************************************

    Tilia Framework v.0

    IModelo.php

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

interface ICambio_obd {
  public function id_site();
  public function icambio_obd();
}

//----------------------------------------------

interface IMetatags {
  public function _metatags($url_limpa, FS_cbd $cbd = null); //* return array(titulo, descricion, logo, robots, url[, idioma, canonical, scripts])
}


//----------------------------------------------

interface IElmt_ventas_rel {
  public function titulo();
  public function orderby($orderby = -1);
  public function selectfrom(FS_cbd $cbd = null):string;
  public function _articulo(FS_cbd $cbd = null, $max = -1);
}


//----------------------------------------------

interface ICatalogo_fonte {
  public function icatF__site():int;
  public function icatF__catRaiz():int;
  public function icatF__titulo():string;
  public function icatF__selectfrom(bool $editor, bool $vpa):string;
  public function icatF__orderby(int $orderby = -1):string;
  public function icatF__etq_tit(int $i, FS_cbd $cbd = null):?string;
  public function icatF__etq_sql(int $i, bool $vpa, FS_cbd $cbd = null):?string;
  //~ public function icatF__sql_categorias(bool $vpa, FS_cbd $cbd = null):string;
  //~ public function icatF__sql_marcas(bool $vpa):string;
}

//----------------------------------------------

interface IMenuSite_opcion {
  public function paxina_obd(FS_cbd $cbd = null, $id_idioma = -1); //* return IMenuSite_paxina
  public function a_suboms(FS_cbd $cbd = null, $id_idioma = null); //* return IMenuSite_opcion[]
  public function atr($nome = null); //* return Atributo_obd 
}

//----------------------------------------------

interface IMenuSite_paxina {
  public function action_2($limpa = false, $completa = false); //* return string
  public function action_custom($completa = false):?string; 
  public function atr($nome = null); //* return Atributo_obd
  public function a_grupo();  //* return String[]
}

//----------------------------------------------

interface IPortes_obd {
  public function calcula($a_param);
}

//----------------------------------------------

interface IXestor_eobd {
  public function elmt();
  public function posicion();
}

//----------------------------------------------

interface ILista_xeobd {
  public function a_elmt();
}

//----------------------------------------------

interface IStyle_obd  {
  public function style_obd(Style_obd $s = null);
}

//----------------------------------------------

interface IPax_indice  {
  /*
   * Interface que será empregada por páxinas dun tipo determinado, que poidan referenciar a algún paragrafo de indice.
   */

  public function __indice();
}

//----------------------------------------------

interface IEI {
  public function ei_export(FS_cbd $cbd, $version);    //* + ei_export(FS_cbd $cbd,    $version      ) :EI
  public function ei_import(FS_cbd $cbd, EI $ei);      //* + ei_import(FS_cbd $cbd, EI $ei           ) :boolean
  public function ei_report(EI $ei, $tipo = "html");   //* + ei_report(EI $ei     ,    $tipo = "html") :string
}
