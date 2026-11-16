<?php

final class QR_elmt_vcard {
  private function __construct() {}

  public static function xerar($src, $_vcart, $talla) {
    $vcard = "BEGIN:VCARD
N:;{$_vcart['nome']}
TEL;WORK:{$_vcart['tlfn']}
ADR;WORK:;;{$_vcart['dirpostal']}
POSTAL-CODE:{$_vcart['cp']}
COUNTRY-CODE:{$_vcart['pais']}
ORG:{$_vcart['rs']};
EMAIL:{$_vcart['email']}
URL:{$_vcart['www']}
END:VCARD";

    $src .= uniqid() . ".png";

    QRcode::png($vcard, $src, QR_ECLEVEL_L, $talla);

    return $src;
  }
}

