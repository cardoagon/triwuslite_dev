<?php

/*************************************************

    Triwus Framework v.0

    MRW.php

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


final class MRW {
  const url_wsdl = "http://sagec-test.mrw.es/MRWEnvio.asmx?WSDL";  //* PROBAS

  private $soap    = null;

  public $id_site  = null;

  public $estado   = -1;
  public $msx      = -1;
  public $url      = -1;
  public $id_solic = -1;
  public $id_envio = -1;

  private $a_param = null;

  public function __construct($id_site) {
    $this->id_site = $id_site;
  }

  public function envia_solicitude(FS_cbd $cbd, Pedido_obd $p, $a_param) {
    $this->a_param = $a_param;

    $mrw = Site_mrw_obd::inicia($cbd, $this->id_site);

    $this->open();

    $header = new SoapHeader('http://www.mrw.es/', 'AuthInfo', $this->cabeceira($mrw));


    $this->soap->__setSoapHeaders($header);

    try {
      $m = $this->msx_solicitude($cbd, $p);

      //~ echo "<pre>" . print_r($m, true) . "</pre>";

      $resposta = $this->soap->TransmEnvio($m);
    }
    catch (SoapFault $e) {
      return false;
    }

    $this->estado     = ($resposta->TransmEnvioResult->Estado == 0)?"0":"1";
    $this->msx        = $resposta->TransmEnvioResult->Mensaje;
    $this->url        = $this->__url($mrw, $resposta);
    $this->id_envio   = $resposta->TransmEnvioResult->NumeroEnvio;
    $this->id_solic   = $resposta->TransmEnvioResult->NumeroSolicitud;

    $this->close();

    return $this->update_pedido_mrw($cbd, $p); //* reposta recibida, queda comprobar this.msx, this.estado ...
  }

  private function update_pedido_mrw(FS_cbd $cbd, Pedido_obd $p) {
    $p_mrw = Pedido_mrw_obd::inicia_pedido($cbd, $p);

    $p_mrw->atr("msx")->valor      = $this->msx;
    $p_mrw->atr("url")->valor      = $this->url;
    $p_mrw->atr("estado")->valor   = $this->estado;
    $p_mrw->atr("id_solic")->valor = $this->id_solic;
    $p_mrw->atr("id_envio")->valor = $this->id_envio;

    return $p_mrw->update($cbd);
  }

  private function open() {
    $this->pedido   = null;

    $this->estado   = -1;
    $this->msx      = -1;
    $this->url      = -1;
    $this->id_solic = -1;
    $this->id_envio = -1;

    try {
        $this->soap = new SoapClient(self::url_wsdl, array('trace' => TRUE));
    }
    catch (SoapFault $e) {
      printf("Error creando cliente SOAP: %s<br />\n", $e->__toString());

      return null;
    }
  }

  private function close() {
    $this->soap = null;
  }

  private function __url(Site_mrw_obd $mrw, $resposta) {
    $url = $resposta->TransmEnvioResult->Url;
    $fra = $mrw->atr("franquicia")->valor;
    $abo = $this->a_param["CodigoAbonado"];
    $dep = $this->a_param["CodigoDepartamento"];
    $usu = $mrw->atr("usuario")->valor;
    $pwd = $mrw->atr("k")->valor;
    $nso = $resposta->TransmEnvioResult->NumeroSolicitud;
    $nen = $resposta->TransmEnvioResult->NumeroEnvio;

    return  "{$url}?Franq={$fra}&Ab={$abo}&Dep={$dep}&Pwd={$pwd}&NumSol={$nso}&Us={$usu}&NumEnv={$nen}";
  }

  private function cabeceira(Site_mrw_obd $mrw) {
    $a = null;

    $a['CodigoFranquicia'] = $mrw->atr("franquicia")->valor;
    $a['UserName']         = $mrw->atr("usuario")->valor;
    $a['Password']         = $mrw->atr("k")->valor;
    $a['CodigoAbonado']    = $this->a_param["CodigoAbonado"];

    if ( $this->a_param['CodigoDepartamento'] != null) $a['CodigoDepartamento'] = $this->a_param['CodigoDepartamento'];

//~ echo "<pre>" . print_r($a, true) . "</pre>";

    return $a;
  }

  private function msx_solicitude(FS_cbd $cbd, Pedido_obd $p) {
    $a_param = $this->a_param;

    date_default_timezone_set('CET');

    $p = Pedido_obd::reinicia($cbd, $p);

    $c = Contacto_obd::inicia($cbd, $p->atr("id_destino")->valor);


    $a_direcion = array ('CodigoTipoVia' => $c->atr("codtipovia")->valor,
                         'Via'           => $c->atr("via")->valor,
                         'Numero'        => $c->atr("num")->valor,
                         'Resto'         => $c->atr("resto")->valor,
                         'CodigoPostal'  => $c->atr("cp")->valor,
                         'Poblacion'     => $c->atr("localidade")->valor,
                         'CodigoPais'    => 'ESP');

    $a_horario = array ('Rangos' => array ('HorarioRangoRequest' => array ('Desde' => '08:30', 'Hasta' => '19:30')));

    $a_datos_entrega = array ('Direccion'     => $a_direcion,
                              'Nif'           => $c->atr("cnif_d")->valor,
                              'Nombre'        => $c->atr("nome")->valor,
                              'Telefono'      => $c->atr("mobil")->valor,
                              'Contacto'      => $c->atr("nome")->valor,
                              'ALaAtencionDe' => $c->atr("nome")->valor,
                              'Horario'       => $a_horario);


    $a = array("request" => array ('DatosEntrega'  => $a_datos_entrega,
                                   'Observaciones' => $a_param['Observaciones'],
                                   'DatosServicio' => $this->msx_datos_servizo($cbd, $p, $c),
                                   'TramoHorario'  =>  '0'));


    return $a;
  }

  private function msx_datos_servizo(FS_cbd $cbd, Pedido_obd $p, Contacto_obd $c) {
    $a_param = $this->a_param;

    $a_notificacion = array ('NotificacionRequest' => array (0 => array ('CanalNotificacion' => '2',
                                                                         'TipoNotificacion'  => '2',
                                                                         'MailSMS'           => $c->atr("email")->valor
                                                                        )));

    $reembolso = $p->calcula_reembolso(-1, $cbd);


    $a_ds = array ('Fecha'            => date("d/m/Y"),
                   'Referencia'       => $p->atr("id_site")->valor . "-" . $p->atr("ano")->valor . "-" . $p->atr("numero")->valor,
                   'EnFranquicia'     => $a_param['EnFranquicia'],
                   'CodigoServicio'   => $a_param['CodigoServicio'],
                   'NumeroBultos'     => $a_param['NumeroBultos'],
                   'Peso'             => $a_param['Peso'],
                   'NumeroPuentes'    => '',
                   'EntregaSabado'    => 'N',
                   'Entrega830'       => 'N',
                   'EntregaPartirDe'  => '09:15',
                   'Reembolso'        => (($reembolso == 0)?"N":"O"),
                   'ImporteReembolso' => number_format($reembolso, 2, ",", ""),
                   'Notificaciones'   => $a_notificacion);

    return $a_ds;
  }
}

?>