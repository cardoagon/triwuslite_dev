<?php

/*************************************************

    Triwus Framework v.0

    I_fs.php

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


interface I_fs {
  public function config_lectura();
  public function config_edicion();
  public function config_usuario(IEfspax_xestor $e = null);
}

interface IVM_pcontrol_ficha {
  public function validar(); //* :boolean
  public function operacion_bCancelar(Epcontrol $e);
  public function operacion_bAceptar (Epcontrol $e);
}

interface IXestorCEditor_imx {
  public function url(?string $k = null);
  public function post_url($url = null, ?string $k = null);
  public function preparar_saida(Ajax $a = NULL);
}

interface ICPC {
  public function cpc_miga();
}


interface ICLogo {
  public function iclogo_id(?string $k = null);
  public function iclogo_src(FS_cbd $cbd, ?string $k = null);
  public function iclogo_obd(FS_cbd $cbd, ?string $k = null); //* :Imaxe_obd
}


interface ICPagar2_paso {
  public function post_u(FGS_usuario $u = null);

  public function validar();
}


interface IControladorCCS {
  public function ccsk_actualiza_email($email); //* debemos invocar esta función para actualizar email_activar en CCS_K, despois dunha operacion de cambio en CCS_email.
}

interface I_ElmtAjaxBRecargable {
  
  public function operacion_bRecargable(IEfspax_xestor $e = null);
}

interface IPrgf_iventas {}; //* interface de marcado


interface ICXAF_cc_lista {
  public function id_s();
  public function id_a();
  public function id_g();

  public function __scampo();
  public function __svalor($id_cc_0 = null);
}

