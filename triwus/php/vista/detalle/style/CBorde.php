<?php

/*************************************************

    Apiweb Framework v.0

    CBorde.php

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
    along with this program.  If not, see <http://www.gnu.org/licenses/ at http://www.gnu.org/licenses/>.

*************************************************/


class CBorde extends Componente {
  const ptw_0 = "ptw/style/cborde_0.html";

  public function __construct($nome = "cborde", $css = null) {
    parent::__construct($nome, Refs::url(self::ptw_0));

    $this->pon_obxeto( Panel_fs::__text("css", 17, 27) );

    $this->post_css($css);
  }

  public function post_css($css) {
    if ($css == null) $css = "0";

    $this->obxeto("css")->post($css);
  }

  public function valor() {
    if (($css = $this->obxeto("css")->valor()) == 0) return null;

    return $css;
  }
}
