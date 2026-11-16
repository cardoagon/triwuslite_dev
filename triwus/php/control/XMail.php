<?php


/*************************************************

    Triwus Framework v.0

    XMail.php

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


final class XMail extends Mail {
  const de       = "no-reply@triwus.com";
  const respaldo = "log@triwus.com";

  private static $a_class = array("class='imx_paxlogo'",
                                   "class='texto2'",
                                   "class='texto3'",
                                   "class='texto4'",
                                   "class='texto5'");

  private static $a_style = array("style='margin: 0; padding: 0; height: 61px; width: 61px; border: 2px solid transparent; border-radius: 4px; -webkit-border-radius: 4px; -khtml-border-radius: 4px;'",
                                   "style='font-size: 12px; color: #555555;'",
                                   "style='font-size: 11px; color: #333333;'",
                                   "style='font-size: 11px; font-style: italic; color: #444444;'",
                                   "style='font-size: 11px; text-transform: uppercase; color: #404040;'");


  public $respaldar = true;



  public function __construct($respaldar = true) {
    parent::__construct();

    $this->respaldar = $respaldar;
  }

  public function enviar($verbose = false) {
    $this->charset = "utf-8";
    $this->texto   = self::__parsehtml($this->texto);

    if ($this->de == null) $this->de = self::de;

    if ($this->respaldar)  $this->pon_cco(self::respaldo);


    parent::enviar($verbose);
  }

  private static function __parsehtml($txt) {
    $txt = self::__refs($txt);

    //* estilos
    $txt = str_replace(self::$a_class, self::$a_style, $txt);

    return $txt;
  }

  private static function __refs($txt) {
    $txt = str_replace("../../sites", "sites", $txt);

    $txt = str_replace("src='sites" , "src='"  . Refs::url_home() . "/sites", $txt);
    $txt = str_replace("src=\"sites", "src=\"" . Refs::url_home() . "/sites", $txt);

    $txt = str_replace("href='sites" , "href='"  . Refs::url_home() . "/sites", $txt);
    $txt = str_replace("href=\"sites", "href=\"" . Refs::url_home() . "/sites", $txt);

    return $txt;
  }

  public static function __link($href, $id = "lmail") {
    return new Link($id, $href, "_blank", $href);
  }

}

