<?php

final class XMail3 extends Mail3 {
  public function __construct(int $s, ?string $l = null, FS_cbd $cbd = null) {
    parent::__construct( self::_c($s, $cbd), self::_l($s, $l, $cbd) );
  }

  private static function _c(int $s, FS_cbd $cbd = null):array {
    return Site_config_obd::_cliemail($s, $cbd);
  }

  private static function _l(int $s, string $l = null, FS_cbd $cbd = null):string {
    if ($l == null) $l = MLsite_obd::idioma_predeterminado($s, $cbd);

    if ($l == null) $l = "gl";


    //* Posible codificación de $l: Tilia -> PHPEmail.

    return $l;
  }
}
