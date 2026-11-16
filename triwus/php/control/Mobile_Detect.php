<?php

class Mobile_Detect {
    private static $userAgents = array(
        'Chrome'         => '\bCrMo\b|CriOS|Android.*Chrome/[.0-9]* (Mobile)?',
        'Dolfin'         => '\bDolfin\b',
        'Opera'          => 'Opera.*Mini|Opera.*Mobi|Android.*Opera|OPR/[0-9.]+',
        'Skyfire'        => 'Skyfire',
        'IE'             => 'IEMobile|MSIEMobile',
        'Firefox'        => 'fennec|firefox.*maemo|(Mobile|Tablet).*Firefox|Firefox.*Mobile',
        'Bolt'           => 'bolt',
        'TeaShark'       => 'teashark',
        'Blazer'         => 'Blazer',
        'Safari'         => 'Version.*Mobile.*Safari|Safari.*Mobile',
        'Tizen'          => 'Tizen',
        'UCBrowser'      => 'UC.*Browser|UCWEB',
        'DiigoBrowser'   => 'DiigoBrowser',
        'Puffin'         => 'Puffin',
        'Mercury'        => '\bMercury\b',
        'GenericBrowser' => 'NokiaBrowser|OviBrowser|OneBrowser|TwonkyBeamBrowser|SEMC.*Browser|FlyFlow|Minimo|NetFront|Novarra-Vision'
    );

  public static function __exec() {
      if ( !isset($_SERVER['HTTP_ACCEPT'    ]) ) $_SERVER['HTTP_ACCEPT'    ] = "";
      if ( !isset($_SERVER['HTTP_USER_AGENT']) ) $_SERVER['HTTP_USER_AGENT'] = "";

      $tablet_browser = 0;
      $mobile_browser = 0;
      $body_class = 'desktop';

      if (preg_match('/(tablet|ipad|playbook)|(android(?!.*(mobi|opera mini)))/i', strtolower($_SERVER['HTTP_USER_AGENT']))) {
        $tablet_browser++;
        $body_class = "tablet";
      }

      if (preg_match('/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone|android|iemobile)/i', strtolower($_SERVER['HTTP_USER_AGENT']))) {
        $mobile_browser++;
        $body_class = "mobile";
      }

      
      if ((strpos(strtolower($_SERVER['HTTP_ACCEPT']),'application/vnd.wap.xhtml+xml') > 0) or ((isset($_SERVER['HTTP_X_WAP_PROFILE']) or isset($_SERVER['HTTP_PROFILE'])))) {
        $mobile_browser++;
        $body_class = "mobile";
      }

      $mobile_ua = strtolower(substr($_SERVER['HTTP_USER_AGENT'], 0, 4));
      $mobile_agents = array(
        'w3c ','acs-','alav','alca','amoi','audi','avan','benq','bird','blac',
        'blaz','brew','cell','cldc','cmd-','dang','doco','eric','hipt','inno',
        'ipaq','java','jigs','kddi','keji','leno','lg-c','lg-d','lg-g','lge-',
        'maui','maxo','midp','mits','mmef','mobi','mot-','moto','mwbp','nec-',
        'newt','noki','palm','pana','pant','phil','play','port','prox',
        'qwap','sage','sams','sany','sch-','sec-','send','seri','sgh-','shar',
        'sie-','siem','smal','smar','sony','sph-','symb','t-mo','teli','tim-',
        'tosh','tsm-','upg1','upsi','vk-v','voda','wap-','wapa','wapi','wapp',
        'wapr','webc','winw','winw','xda ','xda-');

      if (in_array($mobile_ua,$mobile_agents)) {
        $mobile_browser++;
      }

      if (strpos(strtolower($_SERVER['HTTP_USER_AGENT']),'opera mini') > 0) {
        $mobile_browser++;
        //Check for tablets on opera mini alternative headers
        $stock_ua = strtolower(isset($_SERVER['HTTP_X_OPERAMINI_PHONE_UA'])?$_SERVER['HTTP_X_OPERAMINI_PHONE_UA']:(isset($_SERVER['HTTP_DEVICE_STOCK_UA'])?$_SERVER['HTTP_DEVICE_STOCK_UA']:''));
        if (preg_match('/(tablet|ipad|playbook)|(android(?!.*mobile))/i', $stock_ua)) {
          $tablet_browser++;
        }
      }

          if ($tablet_browser > 0) return 1; //return 2;
      elseif ($mobile_browser > 0) return 3;
      else                         return 1;
  }

  public static function __userAgent($resumo = false) {
    $ua = strtolower($_SERVER['HTTP_USER_AGENT']);

    if (!$resumo) return $ua;

    foreach (self::$userAgents as $k=>$v) {
      $k = strtolower($k);

      if (strpos($ua, $k) !== FALSE) return $k;
    }

    return "otros";
  }
}
?>
