<?php

/*************************************************

    Triwus Framework v.0

    PleskMail.php

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


final class PleskMail extends Plesk {
  private $id_plesk;

  public  function __construct($id_plesk) {
    parent::__construct();

    $this->id_plesk = $id_plesk;
  }

  public function create($nome, $k) {
    $sxml = str_replace(array("[id_plesk]", "[nome]", "[k]"),
                        array($this->id_plesk, $nome, $k),
                        LectorPlantilla::plantilla("php/rpc/plesk/ptw/email/create.xml")
                       );

    try {
      $xmldoc = new DomDocument('1.0', 'UTF-8');
      $xmldoc->formatOutput = true;

      $xmldoc->loadXML($sxml);

      $r = $this->sendRequest($xmldoc->saveXML());

      if(!$r ){ throw new Exception( 'Unable to Send Request'); }

      if ($xmlResponse == -1) {
        return "{$this->status}:{$this->errcode}<br>{$this->errtext}";
      }

      $xml_aux = $this->parseResponse($r);
      
      if (!$xml_aux) { throw new Exception( 'Unable to Parse Response'); }
    }
    catch (Exception $e){
      echo($e);
    }
  }

  public function bloquear($nome, $enabled) {
    $e = ($enabled)?"false":"true"; //* se esta bloqueado => desbloquea , y vicelaberza.

    $sxml = str_replace(array("[id_plesk]", "[nome]", "[enabled]"),
                        array($this->id_plesk, $nome, $e),
                        LectorPlantilla::plantilla("php/rpc/plesk/ptw/email/bloquear.xml")
                       );

//~ exit($sxml);

    try {
      $xmldoc = new DomDocument('1.0', 'UTF-8');
      $xmldoc->formatOutput = true;

      $xmldoc->loadXML($sxml);

      $r = $this->sendRequest($xmldoc->saveXML());

      if(!$r ){ throw new Exception( 'Unable to Send Request'); }

      $xmlResponse = $this->parseResponse($r);
      if (!$xmlResponse) { throw new Exception( 'Unable to Parse Response'); }

//~ exit("<pre>" . print_r($xmlResponse->saveXML(), true) . "</pre>");
   }
    catch (Exception $e){
      echo($e);
    }
  }

  public function contrasinal($nome, $k) {
    $sxml = str_replace(array("[id_plesk]", "[nome]", "[k]"),
                        array($this->id_plesk, $nome, $k),
                        LectorPlantilla::plantilla("php/rpc/plesk/ptw/email/contrasinal.xml")
                       );

//~ exit($sxml);

    try {
      $xmldoc = new DomDocument('1.0', 'UTF-8');
      $xmldoc->formatOutput = true;

      $xmldoc->loadXML($sxml);

      $r = $this->sendRequest($xmldoc->saveXML());

      if(!$r ){ throw new Exception( 'Unable to Send Request'); }

      $xmlResponse = $this->parseResponse($r);

      if ($xmlResponse == -1) {
        return "{$this->status}:{$this->errcode}<br>{$this->errtext}";
      }
            
      if (!$xmlResponse) { throw new Exception( 'Unable to Parse Response'); }

//~ exit(print_r($xmlResponse, true));
   }
    catch (Exception $e){
      echo($e);
    }
    
    return null;
  }

  public function getinfo_lista() {
    $sxml = str_replace("[id_plesk]",
                        $this->id_plesk,
                        LectorPlantilla::plantilla("php/rpc/plesk/ptw/email/getinfo_todo.xml")
                       );

//~ exit($sxml);

    try {
      $xmldoc = new DomDocument('1.0', 'UTF-8');
      $xmldoc->formatOutput = true;

      $xmldoc->loadXML($sxml);

      $r = $this->sendRequest($xmldoc->saveXML());

      if(!$r) { throw new Exception( 'Unable to Send Request'); }
//~ echo $r;
      $xml_aux = $this->parseResponse($r);
      if (!$xml_aux){ throw new Exception( 'Unable to Parse Response'); }

//~ exit( print_r($xml_aux->saveXML(), true) );

      $a_lista = null;
      foreach ($xml_aux->mail[0]->get_info->result as $xml_i) {
        $a_i = null;

        $a_i['status']                = (string)$xml_i->status;
        $a_i['id']                    = (string)$xml_i->mailname[0]->id;
        $a_i['name']                  = (string)$xml_i->mailname[0]->name;
        $a_i['mailbox-enabled']       = (string)$xml_i->mailname[0]->mailbox->enabled;
        $a_i['mailbox-usage']         = (string)$xml_i->mailname[0]->mailbox->usage;
        $a_i['mailbox-quota']         = (string)$xml_i->mailname[0]->mailbox->quota;
        $a_i['forwarding-enabled']    = (string)$xml_i->mailname[0]->forwarding->enabled;
        $a_i['forwarding-address']    = (string)$xml_i->mailname[0]->forwarding->address;
        $a_i['autoresponder-enabled'] = (string)$xml_i->mailname[0]->autoresponder->enabled;
        $a_i['autoresponder-subject'] = (string)$xml_i->mailname[0]->autoresponder->subject;
        $a_i['autoresponder-text']    = (string)$xml_i->mailname[0]->autoresponder->text;

        $a_lista[] = $a_i;
      }

      return $a_lista;
    }
    catch (Exception $e){
      //~ echo($e);

      return null;
    }
  }

  public function redirecion($nome, $email_r = null, $enabled = true) {
    if ($email_r == null) return; //* neste caso eliminamos a redirecion

    $m = ($enabled)?"redireccion.xml":"redireccion-2.xml";

    $sxml = str_replace(array("[id_plesk]", "[nome]", "[email_r]"),
                        array($this->id_plesk, $nome, $email_r),
                        LectorPlantilla::plantilla("php/rpc/plesk/ptw/email/{$m}")
                       );

    try {
      $xmldoc = new DomDocument('1.0', 'UTF-8');
      $xmldoc->formatOutput = true;

      $xmldoc->loadXML($sxml);

      $r = $this->sendRequest($xmldoc->saveXML());

      if (!$r) { throw new Exception('Unable to Send Request'); }

      $xmlResponse = $this->parseResponse($r);

      if ($xmlResponse == -1) {
        echo("{$this->status}::{$this->errcode}::{$this->errtext}\n");
      }

    }
    catch (Exception $e){
      exit($e);
    }
  }

  public function autoresposta($nome, $asunto, $dfin, $txt, $enabled) {
    //~ $m = ($enabled)?"redireccion.xml":"redireccion-2.xml";

    $ok = ($enabled)?"true":"false";

    $sxml = str_replace(array("[id_plesk]", "[nome]", "[asunto]", "[dfin]", "[txt]", "[ok]"),
                        array($this->id_plesk, $nome, $asunto, $dfin, $txt, $ok),
                        LectorPlantilla::plantilla("php/rpc/plesk/ptw/email/autoresposta.xml")
                       );

    try {
      $xmldoc = new DomDocument('1.0', 'UTF-8');
      $xmldoc->formatOutput = true;

      $xmldoc->loadXML($sxml);

      $r = $this->sendRequest($xmldoc->saveXML());

      if (!$r) { throw new Exception('Unable to Send Request'); }

      $xmlResponse = $this->parseResponse($r);

      if ($xmlResponse == -1) {
        echo("{$this->status}::{$this->errcode}::{$this->errtext}\n");
      }

    }
    catch (Exception $e){
      exit($e);
    }
  }

  public function rename($nome, $renome) {
    $sxml = str_replace(array("[id_plesk]", "[nome]", "[renome]"),
                        array($this->id_plesk, $nome, $renome),
                        LectorPlantilla::plantilla("php/rpc/plesk/ptw/email/rename.xml")
                       );

    try {
      $xmldoc = new DomDocument('1.0', 'UTF-8');
      $xmldoc->formatOutput = true;

      $xmldoc->loadXML($sxml);

      $r = $this->sendRequest($xmldoc->saveXML());

      if (!$r) { throw new Exception('Unable to Send Request'); }

      $xmlResponse = $this->parseResponse($r);
      if (!$xmlResponse) { throw new Exception('Unable to Parse Response'); }

    }
    catch (Exception $e){
      exit($e);
    }
  }

  public function delete($nome) {
    $sxml = str_replace(array("[id_plesk]", "[nome]"),
                        array($this->id_plesk, $nome),
                        LectorPlantilla::plantilla("php/rpc/plesk/ptw/email/delete.xml")
                       );

    try {
      $xmldoc = new DomDocument('1.0', 'UTF-8');
      $xmldoc->formatOutput = true;

      $xmldoc->loadXML($sxml);

      $r = $this->sendRequest($xmldoc->saveXML());

      if( !$r ){ throw new Exception( 'Unable to Send Request'); };

      $xml_aux = $this->parseResponse($r);
      if (!$xml_aux){ throw new Exception( 'Unable to Parse Response'); }


      return (string)$xml_aux->mail->remove->result->status == "ok";
    }
    catch (Exception $e) {
      exit($e);
    }
  }
}
