<?php

class FS_cbd extends MySql {
  
  public function __construct(bool $verbose = false, string $h = null, string $u = null, string $p = null, string $b = null) {
    parent::__construct( self::__h($h), self::__u($u), self::__p($p), self::__b($b) );

    $this->verbose = $verbose;
  }
  
  private static function __h(string $h = null):string {
    if ($h != null) return $h;
    
    if ( class_exists("ConfigLocal", false) ) if ( ConfigLocal::mysql_h != null ) return ConfigLocal::mysql_h;
    
    return TrwlConfig::mysql_h;
  }
  
  private static function __u(string $u = null):string {
    if ($u != null) return $u;
    
    if ( class_exists("ConfigLocal", false) ) if ( ConfigLocal::mysql_u != null ) return ConfigLocal::mysql_u;
    
    return TrwlConfig::mysql_u;
  }
  
  private static function __p(string $p = null):string {
    if ($p != null) return $p;
    
    if ( class_exists("ConfigLocal", false) ) if ( ConfigLocal::mysql_p != null ) return ConfigLocal::mysql_p;
    
    return TrwlConfig::mysql_p;
  }
  
  private static function __b(string $b = null):string {
    if ($b != null) return $b;
    
    if ( class_exists("ConfigLocal", false) ) if ( ConfigLocal::mysql_b != null ) return ConfigLocal::mysql_b;
    
    return TrwlConfig::mysql_b;
  }
}


//**********************************************


final class Xesta_cbd extends FS_cbd {
  public function __construct(bool $verbose = false) {
    parent::__construct($verbose, null, null, null, TrwlConfig::mysql_b);
  }
}

