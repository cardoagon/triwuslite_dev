<?php
/*

* @author   Steve Winnington <stewinni@gmail.com>
* @version  1.000 13 Sep 2013
* @access   public

This library is free software; you can redistribute it and/or
modify it under the terms of the GNU Lesser General Public
License as published by the Free Software Foundation; either
version 2.1 of the License, or (at your option) any later version.

This library is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
Lesser General Public License for more details.

You should have received a copy of the GNU Lesser General Public
License along with this library; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

This Class is designed take a string and a set of tests to run it will check
the string for the selected tests and return true for safe and false for unsafe.

The use case is for the insertion of PHP code into a db by a user
but to restrict the functions allowed using this method.

*/
/*
This class manages the request of a domain in plesk.

*/

interface iPleskDomain
{
  public function request($webspaceName);
}

class PleskDomain extends Plesk implements iPleskDomain{

  public $id = false;
  public $cr_date = false;
  public $status = false;
  public $real_size = false;
  public $dns_ip_address = false;
  public $htype = false;
  public $guid = false;
  public $webspaceGuid = false;

  public  function __construct($host, $login, $password){
    parent::__construct($host, $login, $password);
  }

  public function request($webspaceName)
  {

    try {
      $xmldoc = new DomDocument('1.0', 'UTF-8');
      $xmldoc->formatOutput = true;

      // <packet>
      $packet = $xmldoc->createElement('packet');
      $packet->setAttribute('version', '1.6.3.0');
      $xmldoc->appendChild($packet);

      // <packet/domain>
      $domain = $xmldoc->createElement('site');
      $packet->appendChild($domain);

      // <packet/domain/get>
      $get = $xmldoc->createElement('get');
      $domain->appendChild($get);

      // <packet/domain/get/filter>
      $filter = $xmldoc->createElement('filter');
      $get->appendChild($filter);

      // <packet/domain/get/dataset>
      $webspace = $xmldoc->createElement('name');
      $filter->appendChild($webspace);
      $webspace->appendChild($xmldoc->createTextNode($webspaceName));

      $dataset = $xmldoc->createElement('dataset');
      $get->appendChild($dataset);
      $hosting = $xmldoc->createElement('gen_info');
      $dataset->appendChild($hosting);

//~ echo "<pre>" . print_r($xmldoc->saveXML(), true) . "</pre>";

      $resut = $this->sendRequest($xmldoc);

      if(!$resut ){ throw new Exception( 'Unable to Send Request'); };

      $xmlResponse = $this->parseResponse($resut);
      if (!$xmlResponse){ throw new Exception( 'Unable to Parse Response'); };

      $resultNode = $xmlResponse->xpath('/packet/site/get/result');
      $this->id  = (int)$resultNode[0]->id;

      foreach ($xmlResponse->xpath('/packet/site/get/result/data/gen_info') as $resultNode) {
        $this->cr_date  = (string)$resultNode->cr_date;
        $this->name  = (string)$resultNode->name;
        $this->status  = (string)$resultNode->status;
        $this->real_size  = (string)$resultNode->real_size;
        $this->dns_ip_address  = (string)$resultNode->dns_ip_address;
        $this->htype  = (string)$resultNode->htype;
        $this->guid  = (string)$resultNode->guid;
      }
      return $xmlResponse;

      }
    catch (Exception $e){
        echo $e;
        die();
    }
  }
}
