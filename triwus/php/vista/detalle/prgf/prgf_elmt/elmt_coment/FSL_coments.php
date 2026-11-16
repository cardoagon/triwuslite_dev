<?php

/*************************************************

    Triwus Framework v.0

    FSL_coments.php

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

final class FSL_coments extends FS_lista {
  private $id_elmt = null;

  private $usuario;

  public $readonly = true;

  public $anonimos = null;
  public $valorar = null;
  public $moderacion = null;
  public $denunciar = null;

  public function __construct(Elmt_comentarios $elmt) {
    parent::__construct("tcomentsdet", new FSehtml_coments());

    $this->iniciar($elmt);
  }

  public function usuario(FGS_usuario $usuario = null) {
    if ($usuario != null) $this->usuario = $usuario;

    return $this->usuario;
  }

  public function iniciar(Elmt_comentarios $elmt) {
    $this->id_elmt = $elmt->id_elmt();

    $this->ehtml->anonimos   = $elmt->anonimos;
    $this->ehtml->valorar    = $elmt->valorar;
    $this->ehtml->denunciar  = $elmt->denunciar;
    $this->ehtml->moderacion = $elmt->moderacion;

    $this->selectfrom = "select * from v_elmt_comentsdet";
    $this->where = Cab_coments::__where($this->id_elmt);
    $this->orderby =  " momento desc";
  }

  public function __where($where = null) {
    $this->where = $where;
  }

  public function __order($order = null) {
    $this->orderby = $order;
  }

  public function operacion(EstadoHTTP $e) {
    $evento = $e->evento();

    if ($this->control_fslevento($evento, "bDenunciar")) {
      list($x, $id_elmt) = explode(Escritor_html::csubnome, $evento->nome(2));

      $id_ecd = $evento->subnome(0);

      Comentarios_detalle_obd::denunciar($id_elmt, $id_ecd, true);

      return $e;
    }

    if ($this->control_fslevento($evento, "bbloquear")) {
      list($x, $id_elmt) = explode(Escritor_html::csubnome, $evento->nome(2));

      $id_ecd = $evento->subnome(0);

      Comentarios_detalle_obd::bloquear($id_elmt, $id_ecd, true);

      return $e;
    }

    if ($this->control_fslevento($evento, "bbloquear_des")) {
      list($x, $id_elmt) = explode(Escritor_html::csubnome, $evento->nome(2));

      $id_ecd = $evento->subnome(0);

      Comentarios_detalle_obd::bloquear($id_elmt, $id_ecd, false);

      return $e;
    }

    if ($this->control_fslevento($evento, "bmais"))  {
      if ($this->anonimos != 1) {
        if (!$e->usuario()->validado()) return $this->pai->operacion_cacceso($e);
      }

      return Control_valorar::operacion_puntuar($e, 1);
    }

    if ($this->control_fslevento($evento, "bmenos"))  {
      if ($this->anonimos != 1) {
        if (!$e->usuario()->validado()) return $this->pai->operacion_cacceso($e);
      }

      return Control_valorar::operacion_puntuar($e, -1);
    }

    return null;
  }
}

?>
