<?php

final class Plesk_siteinfo extends Plesk {
  public  function __construct() {
    parent::__construct();
  }

  public function getinfo_realSize($dominio, $ref_carpeta = "/var/www/vhosts/triwus.com/httpdocs/") {
    $sxml = str_replace("[dominio]",
                        $dominio,
                        file_get_contents("{$ref_carpeta}php/rpc/plesk/ptw/siteinfo.xml")
                       );

//~ print_r($sxml);


    try {
      $xmldoc = new DomDocument('1.0', 'UTF-8');
      $xmldoc->formatOutput = true;

      $xmldoc->loadXML($sxml);

      $r = $this->sendRequest($xmldoc->saveXML());

      if(!$r) { throw new Exception( 'Unable to Send Request'); }

//~ print_r($r);
 
      $xml_aux = $this->parseResponse($r);
      if (!$xml_aux){ throw new Exception( 'Unable to Parse Response'); }

//~ print_r($xml_aux->site->get->result->data->gen_info->real_size);

      foreach ($xml_aux->site->get->result->data->gen_info as $xml_i) {
        return $xml_i->{"real_size"};
      }

      return -1;
    }
    catch (Exception $e){
      exit($e);
    }

  }
}

