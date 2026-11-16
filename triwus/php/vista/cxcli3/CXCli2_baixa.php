<?php

/*************************************************

    Triwus Framework v.0

    CLoginWeb_xcli-2.php

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


final class CXCli2_baixa extends Componente
                      implements IPanel_cxcli2 {

  private $id_site;

  private $email_a;
  private $email_u;

  public function __construct() {
    parent::__construct("cbaixa", Refs::url("ptw/clogin/web/cxcli2_feedback.html"));

    //~ $this->pon_obxeto(new Textarea("tfeedback", 2, 33));
//~
    //~ $this->obxeto("tfeedback")->style("default", "width: 99%; height: 55px;");
  }

  public function id() { //* IMPLEMENTS IPanel_cxcli2.id();
    return $this->nome;
  }

  public function titulo() { //* IMPLEMENTS IPanel_cxcli2.titulo();
    return "Baja";
  }

  public function notas() { //* IMPLEMENTS IPanel_cxcli2.notas();
    return "En esta sección podrás darte de baja, si ya no deseas estar registrado en esta web.";
  }

  public function post_u(FGS_usuario $u = null) { //* IMPLEMENTS IPanel_cxcli2.post_u();
    //* gardamos informacion util
    //~ $cbd = new FS_cbd();
//~
    //~ $this->id_site = $u->atr("id_site")->valor;
    //~ $this->email_u = $u->atr("email")->valor;
    //~ $this->email_a = Site_obd::inicia($cbd, $this->id_site)->usuario_obd($cbd)->atr("email")->valor;
//~
    //~ //* cambimos a configuración do boton bAceptar
//~
    //~ $this->obxeto("btnr")->pon_obxeto(Panel_fs::__baceptar("&nbsp;Enviar", "Env&iacute;a feedback"));
//~
    //~ $this->obxeto("btnr")->obxeto("bAceptar")->envia_submit("onclick", "", "cxcli2_buscador_bfeedback");
  }

  public function tipo_btnr() {  //* IMPLEMENTS IPanel_cxcli2.tipo_btnr();
    return 2;
  }

  public function operacion_bCancelar(IEfspax_xestor $e) {} //* IMPLEMENTS IPanel_cxcli2.operacion_bCancelar();

  public function operacion_bAceptar(IEfspax_xestor $e) { //* IMPLEMENTS IPanel_cxcli2.operacion_bAceptar();
    //~ $texto  = $this->obxeto("tfeedback")->valor();
    //~ $ticket = Elmt_contacto_obd::__colle_ticket($this->id_site); //* site arestora prospecta.
//~
    //~ $m = new Mail(false);
//~
    //~ $m->de     = "info@arestoraprospecta.com";
    //~ $m->asunto = "consulta: #id = {$ticket}";
//~
    //~ //* copia usuario
    //~ $m->para  = $this->email_u;
    //~ $m->texto = "\n{$texto}";
//~
    //~ $m->enviar();
//~
    //~ //* copia admon.
    //~ $m->para  = $this->email_a;
    //~ $m->texto = "\n****** " . Msx::$elmt_contacto["1"]["es"] . " ******\n\n{$texto}";
//~
    //~ $m->enviar();
//~
    //~ //* actualiza formulario.
    //~ $this->obxeto("tfeedback")->post("\n\t-- Su mensaje ha sido enviado correctamente --");

    return $e;
  }
}

