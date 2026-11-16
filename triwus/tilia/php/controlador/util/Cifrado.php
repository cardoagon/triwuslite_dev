<?php
/** Permite cifrar cadeas de texto co algoritmo AES-256-CBC.
 *
 * @package tilia\controlador\util
 */




final class AES_256_CBC {
  const method = "AES-256-CBC";
  const k      = "pe2dowweDlerk--5ld(3·QcdEEE7";

  public static function cifrar($t, $k = null, $iv = null) {
    if ($k == null) $k = self::k;

    // hash
    $kh = hash('sha256', $k);

    // iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
    if ($iv == null) $iv = self::method;

    $ivh = substr(hash('sha256', $iv), 0, 16);


    return base64_encode( openssl_encrypt($t, self::method, $kh, 0, $ivh) );
  }

  public static function descifrar($t, $k = null, $iv = null) {
    if ($k == null) $k = self::k;

    // hash
    $kh = hash('sha256', $k);

    // iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
    if ($iv == null) $iv = self::method;

    $ivh = substr(hash('sha256', $iv), 0, 16);


    return openssl_decrypt(base64_decode($t), self::method, $kh, 0, $ivh);
  }
}

