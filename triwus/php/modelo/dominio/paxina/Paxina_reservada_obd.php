<?php

final class Paxina_reservada_obd extends Paxina_obd
                              implements IPax_indice {


  public function __construct($tipo, $id_paxina = null) {
    parent::__construct($tipo, $id_paxina);

    $this->atr("visible"  )->valor = "0";
    $this->atr("reservada")->valor = "1";
  }

  public static function test($tipo, FS_cbd $cbd = null) {
    //* Control pax reservada.
    if ($cbd == null) $cbd = new FS_cbd();
    
    $r = $cbd->consulta("select reservada from paxina_tipo where ref = '{$tipo}' and reservada=1");
    
    if ($_r = $r->next()) return true;


    return false;
  }

  public static function id_paxina(FS_cbd $cbd, $id_site, $tipo) {
    $r = $cbd->consulta("select id_paxina from v_site_paxina where id_site = '{$id_site}' and tipo = '{$tipo}'");

    if ($a = $r->next()) return $a['id_paxina'];

    return null;
  }

  public function mapa_bd() {
    return new Paxina_mbd();
  }

  public function __indice() {
    return $this->atr("id_prgf_indice")->valor > 0;
  }

  public function prgf_indice(FS_cbd $cbd = null) {
    if ($cbd == null) $cbd = new FS_cbd();

    return Prgf_reservado_obd::inicia($cbd, $this->atr("id_prgf_indice")->valor);
  }

  public function select_pai(FS_cbd $cbd = null) {
    return null;
  }

  public function select_ant(FS_cbd $cbd = null) {
    return null;
  }

  public function select_seg(FS_cbd $cbd = null) {
    return null;
  }

  public function insert(FS_cbd $cbd, $id_idioma) {
    $prgf = new Prgf_reservado_obd();

    if (!$this->insert_base($cbd, $id_idioma, $prgf)) return false;

    $this->atr("id_prgf_indice")->valor = $prgf->atr("id_paragrafo")->valor;


    return $cbd->executa($this->sql_update($cbd));
  }

  public function duplicar(FS_cbd $cbd, $id_idioma, ?string $refsite = null) {
    return true;
  }


  public static function action_buscador($b, $url_limpa = true) {
    if ($url_limpa) return "buscador/{$b}/";

    return "buscador.php?b={$b}";
  }

  public static function action_confirmar($c, $url_limpa = true) {
    if ($url_limpa) return "confirmar/{$c}/";

    return "confirmar.php?c={$c}";
  }

  public static function action_pago($url_limpa = true) {
    if ($url_limpa) return "pago/";

    return "pago.php";
  }

  public static function action_producto($p, $n = "", $url_limpa = true) {
    $n = str_replace([" ", "/"], ["-", ""], $n);
    
    if ($url_limpa) {
      if ($n == "") return "producto/{$p}/";

      return "producto/{$n}/{$p}/";
    }

    return "producto.php?pnome={$n}&p={$p}";
  }

  public static function action_almacen($a, $n, $url_limpa = true) {
    if ($url_limpa) return "almacen/{$n}/{$a}/";

    return "almacen.php?anome={$n}&a={$a}";
  }

}
