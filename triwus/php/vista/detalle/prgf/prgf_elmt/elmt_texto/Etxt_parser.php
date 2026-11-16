<?php

/*************************************************

    Tilia Framework v.0

    Etxt_parser.php
    
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


abstract class Etxt_parser {
  protected function __construct() {}
  
  abstract public function html($url, $path = null);
 
  public static function inicia($mime) {
    switch ($mime) {
      case ODT_parser::mime:  return new ODT_parser();
      case XHTML_parser::mime:  return new XHTML_parser();
    }

    if (substr(TXT_parser::mime, 0, 5) == substr($mime, 0, 5)) return new TXT_parser();
    
    return null;
  }
  
  public static function __strip_tags($txt) {
    return $txt;
    //~ return str_replace("<a href", "<a target='_blank' href", $txt);
    //~ return strip_tags($txt, "<span><hr><a><i><u><sub><sup><b><br><div><font><img><strike><ol><ul><li><table><tr><td><blockquote>");
  }

  protected static function __htmlconvert($html) {

    $search = array ("'<script[^>]*?>.*?</script>'si",
                     "'<head[^>]*?>.*?</head>'si", 
                     "'<body[^>]*?>'si",
                     "'</body>'si",
                     "'<html>'si",
                     "'</html>'si"); 
                     
    $replace = array ("", "", "", "", "", "");
                      
    return self::__strip_tags(preg_replace($search, $replace, $html));

    //~ return self::__strip_tags($html);
  }
}

//---------------------------------

final class ODT_parser extends Etxt_parser {
  const mime = "application/vnd.oasis.opendocument.text";
  
  protected function __construct() {}
  
  public function html($url, $path = null) {
    $parser = new ODT2XHTML($path);
    
    return $parser->oo_convert($parser->oo_unzip($url));
  }
}

//---------------------------------

final class XHTML_parser extends Etxt_parser {
  const mime = "text/html";
  
  protected function __construct() {}
  
  public function html($url, $path = null) {
    return self::__htmlconvert(file_get_contents($url));
  }
}

//---------------------------------

final class TXT_parser extends Etxt_parser {
  const mime = "text/html";
  
  protected function __construct() {}
  
  public function html($url, $path = null) {
    return nl2br(self::__htmlconvert(file_get_contents($url)));
  }
}

?>
