<?php


class Acceso  {
  private function __construct() {}

  public static function rexistrar(FS_cbd $cbd, int $s, string $ip, string $login, string $txt, int $ok):bool {
    $sql = "insert into acceso (id_site, momento, ip, login, txt, ok)
                        values ({$s}, '" . date("YmdHis") . "', '{$ip}', '{$login}', '{$txt}', {$ok})";
                 
    return $cbd->executa($sql);
  }
}
