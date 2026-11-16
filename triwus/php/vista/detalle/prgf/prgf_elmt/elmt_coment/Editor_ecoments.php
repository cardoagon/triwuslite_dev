<?php

/*************************************************

    Triwus Framework v.0

    Editor_ecoments.php

    Author: Carlos Domingo Arias Gonz&aacute;lez

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

final class Editor_ecoments extends Edita_elmt {
  public function __construct(Elmt_comentarios $elmt) {
    parent::__construct(null, "Editor de comentarios", "ptw/paragrafo/elmt/comentarios/editor_comentarios.html", $elmt);

    $this->pon_obxeto( Panel_fs::__email("avisar_email") );

    $this->pon_obxeto(new Checkbox("anonimos", $elmt->anonimos));
    $this->pon_obxeto(new Checkbox("moderacion", $elmt->moderacion));
    $this->pon_obxeto(new Checkbox("valorar", $elmt->valorar));
    // polo de agora *** $this->pon_obxeto(new Checkbox("denunciar", $elmt->denunciar));
    $this->pon_obxeto(new Checkbox("avisar", $elmt->avisar));
    $this->pon_obxeto(new Checkbox("pechar", $elmt->pechar));


    $this->obxeto("avisar_email")->post ($elmt->avisar_email);
    $this->obxeto("avisar_email")->style("default", "width: 88%;");
  }

  public function operacion_aceptar(Efs_admin $e) {
    $this->elmt->anonimos  = $this->obxeto("anonimos")->valor();
    $this->elmt->moderacion  = $this->obxeto("moderacion")->valor();
    //$this->elmt->denunciar  = $this->obxeto("denunciar")->valor();
    $this->elmt->valorar  = $this->obxeto("valorar")->valor();
    $this->elmt->avisar  = $this->obxeto("avisar")->valor();
    $this->elmt->pechar  = $this->obxeto("pechar")->valor();

    $this->elmt->avisar_email = $this->obxeto("avisar_email")->valor();

    $this->elmt->obxeto("tcomentsdet")->iniciar($this->elmt);

    return parent::operacion_aceptar($e);
  }

  protected function __editor_style() {
    return new Editor_style_nulo();
  }
}

?>