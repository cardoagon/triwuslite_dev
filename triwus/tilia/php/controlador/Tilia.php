<?php
/** Implementa a máquina de estados de Tilia.
 *
 * @package tilia\controlador
 */


interface ITilia {
  public function postHTTP($http_request, $http_files = null);
  public function devolver();
  public function operacion(); //* return ITilia;
}

class Tilia {
  private static $log = null;

  private $nome_sesion = null;
  private $ehttp_inicio = null;

  public function __construct($nome_sesion, $tilia_home, $ehttp_inicio) {
    putenv("Tilia_HOME={$tilia_home}");

    $this->nome_sesion  = $nome_sesion;
    $this->ehttp_inicio = $ehttp_inicio;
  }

  public function transitar() {
    $ehttp = (isset($_SESSION[$this->nome_sesion]))?$_SESSION[$this->nome_sesion]:null;
    //~ $ehttp = (isset($_SESSION[$this->nome_sesion]))?unserialize($_SESSION[$this->nome_sesion]):null;

    if ($ehttp == null) {
      $ehttp = new $this->ehttp_inicio();
    }

//~ print_r($_REQUEST);

    $ehttp->postHTTP($_REQUEST, $_FILES);


    //* xeramos a saida antes de garar na sesión


    if (($ehttp_aux = $ehttp->operacion()) != null) {
      $html = $ehttp_aux->devolver();

      //~ $_SESSION[$this->nome_sesion] = serialize($ehttp_aux);
      $_SESSION[$this->nome_sesion] = $ehttp_aux;
    }
    else {
      $html = $ehttp->devolver();

      //~ $_SESSION[$this->nome_sesion] = serialize($ehttp);
      $_SESSION[$this->nome_sesion] = $ehttp;
    }


    exit($html);
  }

  public function transicion(&$sesion, $http_request, $http_files) {
    //~ $ehttp = (isset($sesion[$this->nome_sesion]))?unserialize($sesion[$this->nome_sesion]):null;
    $ehttp = (isset($sesion[$this->nome_sesion]))?$sesion[$this->nome_sesion]:null;

    if ($ehttp == null) {
      $ehttp = new $this->ehttp_inicio();
    }

    $ehttp->postHTTP($http_request, $http_files);


    $ehttp_aux = $ehttp->operacion();


    if ($ehttp_aux != null) {
      $html = $ehttp_aux->devolver();

      //~ $sesion[$this->nome_sesion] = serialize($ehttp_aux);
      $sesion[$this->nome_sesion] = $ehttp_aux;
    }
    else
      $html = $ehttp->devolver();


    exit($html);
  }

  public static function home() {
    return getenv("Tilia_HOME");
  }

  public static function log(Log $l = null) {
    if ($l != null) putenv("Tilia_LOG=" . base64_encode(serialize($l)));

    return unserialize(base64_decode(getenv("Tilia_LOG")));
  }


  public static function fcarga_0() {
    return "
<form>
<div style='position: fixed;
            height: 100%;
            background-image: url(\"data:image/gif;base64,R0lGODlhKwALAPEAAP///wAAAIKCggAAACH/C05FVFNDQVBFMi4wAwEAAAAh/hpDcmVhdGVkIHdpdGggYWpheGxvYWQuaW5mbwAh+QQJCgAAACwAAAAAKwALAAACMoSOCMuW2diD88UKG95W88uF4DaGWFmhZid93pq+pwxnLUnXh8ou+sSz+T64oCAyTBUAACH5BAkKAAAALAAAAAArAAsAAAI9xI4IyyAPYWOxmoTHrHzzmGHe94xkmJifyqFKQ0pwLLgHa82xrekkDrIBZRQab1jyfY7KTtPimixiUsevAAAh+QQJCgAAACwAAAAAKwALAAACPYSOCMswD2FjqZpqW9xv4g8KE7d54XmMpNSgqLoOpgvC60xjNonnyc7p+VKamKw1zDCMR8rp8pksYlKorgAAIfkECQoAAAAsAAAAACsACwAAAkCEjgjLltnYmJS6Bxt+sfq5ZUyoNJ9HHlEqdCfFrqn7DrE2m7Wdj/2y45FkQ13t5itKdshFExC8YCLOEBX6AhQAADsAAAAAAAAAAAA=\");
            background-repeat:no-repeat;
            background-position: 50% 37%;
            background-color: #ffffff;
            text-align: center;
            z-index: 999999999;
            width: 100%;
            display: block;
            opacity: .5;'>
</div>
</form>
<script>
  document.forms[0].submit();
</script>
";
  }

  public static function fcarga_1() {
    return "
<div id='tilia_div_cargando'
     style='position: fixed;
            height: 100%;
            background-image: url(\"data:image/gif;base64,R0lGODlhKwALAPEAAP///wAAAIKCggAAACH/C05FVFNDQVBFMi4wAwEAAAAh/hpDcmVhdGVkIHdpdGggYWpheGxvYWQuaW5mbwAh+QQJCgAAACwAAAAAKwALAAACMoSOCMuW2diD88UKG95W88uF4DaGWFmhZid93pq+pwxnLUnXh8ou+sSz+T64oCAyTBUAACH5BAkKAAAALAAAAAArAAsAAAI9xI4IyyAPYWOxmoTHrHzzmGHe94xkmJifyqFKQ0pwLLgHa82xrekkDrIBZRQab1jyfY7KTtPimixiUsevAAAh+QQJCgAAACwAAAAAKwALAAACPYSOCMswD2FjqZpqW9xv4g8KE7d54XmMpNSgqLoOpgvC60xjNonnyc7p+VKamKw1zDCMR8rp8pksYlKorgAAIfkECQoAAAAsAAAAACsACwAAAkCEjgjLltnYmJS6Bxt+sfq5ZUyoNJ9HHlEqdCfFrqn7DrE2m7Wdj/2y45FkQ13t5itKdshFExC8YCLOEBX6AhQAADsAAAAAAAAAAAA=\");
            background-repeat:no-repeat;
            background-position: 50% 37%;
            background-color: #ffffff;
            text-align: center;
            z-index: 999999999;
            width: 100%;
            display: block;
            opacity: .5;'>
</div>
<script>
  startclock('tilia_fin_cargado()', 5000, 1);
</script>
";
  }

}

