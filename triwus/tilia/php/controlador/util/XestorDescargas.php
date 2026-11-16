<?php

/**  Esta clase facilita a xeración de arquivos temporais no servidor.
 *
 * @package tilia\controlador\util\xestorDescargas
 */


class XestorDescargas {


  protected $indice = null;
  protected $usa_id_unico = null;

  public function __construct($usa_id_unico = false) {
    $this->usa_id_unico = $usa_id_unico;
  }

  public static function borra_antigos($ruta, $antigo = 60, $nivelmax = 9) {
    //* PRECOND:: $antigo, tempo en minutos.
    if ($antigo < 0) return;

    if ($ruta == null ) die ("XestorDescargas.borra_antigos, ruta == null");

    
    if (!is_dir($ruta)) return;

    $momento = (int)(date("U")  / 60);

    $d = dir($ruta);
    while($f = $d->read()) {
      if ($f == "." ) continue;
      if ($f == "..") continue;
      
      $nf_i = "{$ruta}/{$f}";

      $fmomento = (int)(fileatime($nf_i) / 60);

      if (($momento - $fmomento) < $antigo) continue;

      if (is_dir($nf_i)) {
        exec("rm -r {$nf_i}");
      }
      else {
        unlink($nf_i);
      }
    }
    
    $d->close();
  }
  
/*
  public static function borra_antigos($ruta, $antigo = 60) {
    //* PRECOND:: $antigo, tempo en minutos.

    if ($antigo < 0) return;

    if ($ruta == null) die ("XestorDescargas.borra_antigos, ruta == null");

    $momento = (int)(date("U")  / 60);

    $d = dir($ruta);
    while($f = $d->read()) {
      $nf_i = "{$ruta}/{$f}";

      if (!is_dir($nf_i)) {
        $fmomento = (int)(fileatime($nf_i) / 60);

        if (($momento - $fmomento) > $antigo) unlink($nf_i);

      }
    }
    $d->close();
  }
*/
  //~ public function borra_carpeta($src = null) {
    //~ if ($src == null) $src = $this->src;
    
    //~ foreach (glob("{$src}*") as $url) {
      //~ if (is_dir($url)) return $this->borra_carpeta($url);

      //~ unlink($url);
    //~ }
    
    //~ if (is_dir($src)) rmdir($src);
  //~ } 

  public static function nome_descarga($ruta, $nome, $extension = null, $zip = false, $usa_id_unico = false) {
    if (($nome_descarga = trim($nome)) == "") $nome_descarga = "tilia.xd";

    if ($usa_id_unico) $nome_descarga = "{$nome_descarga}." . uniqid("", true);

    if (($ruta = trim($ruta)) != "") $nome_descarga = "{$ruta}/{$nome_descarga}";

    if ($extension != null) $nome_descarga .= ".{$extension}";

    if ($zip) $nome_descarga .= ".gz";

    return $nome_descarga;
  }

  public function gardar($ruta, $nome, $extension, $corpo, $zip = true) {
    $nome_descarga = self::nome_descarga($ruta, $nome, $extension, $zip, $this->usa_id_unico);

    $this->indexar($nome_descarga);

    if ($zip)
      return self::gardar_gz($nome_descarga, $corpo);
    else
      return self::gardar_normal($nome_descarga, $corpo);
  }

  public function inicia_indice($ruta, $nome_indice) {
    $this->indice = new IndiceDescargas($ruta, $nome_indice);
    $this->usa_id_unico = true;
  }

  public function indice() {
    return $this->indice;
  }

  protected function indexar($descarga) {
    if ($this->indice != null) $this->indice->indexar($descarga);
  }

  protected static function gardar_normal($nome_descarga, $corpo) {
    if (!($f = fopen($nome_descarga, "w"))) return false;
    fputs($f, $corpo);
    fclose($f);

    return $nome_descarga;
  }

  protected static function gardar_gz($nome_descarga, $corpo) {
    if (!($f = gzopen($nome_descarga, "w"))) return false;
    gzwrite($f, $corpo);
    gzclose($f);

    return $nome_descarga;
  }
}



/**  Representa un índice de arquivos xenerados cunha instancia dun obxeto XestorDescargas. Para realizar a lectura do índice, o metodo de traballo será como o dun iterador.
 *
 * @package tilia\controlador\util\xestorDescargas
 */

class IndiceDescargas {

  protected $nome = "";

  protected $f_lectura = null;

  public function __construct($ruta, $nome) {
    $this->nome = XestorDescargas::nome_descarga($ruta, $nome, "txt", false);
  }

  public function nome() {
    return $this->nome;
  }

  public function indexar($entrada) {
    if (!($f = fopen($this->nome, "a"))) return false;
    fwrite($f, "{$entrada};\n");
    fclose($f);

    return true;
  }

  public function inicia_lectura() {
    if ($this->f_lectura != null) $this->cerrar();

    $this->f_lectura = fopen($nome,"r");
  }

  public function seguinte_entrada() {
    if ($this->f_lectura == null) return false;

    if ($data = fgetcsv($this->f_lectura, 1000, ";")) return $data[0];

    $this->close();

    return false;
  }

  public function close() {
     fclose($this->f_lectura);
     $this->f_lectura = null;
  }
}

//*******************************************

/**  Permite a un obxeto xestionar un log.
 *
 * @package tilia\controlador\util\xestorDescargas
 */

class Log extends XestorDescargas {
  private $url;
  private $zip = true;
  private $timestamp = true;

  public function __construct($url, $zip = true, $timestamp = true) {
    parent::__construct(null, null);

    $a_url = pathinfo($url);

    $this->zip = $zip;
    $this->timestamp = $timestamp;

    $this->url = XestorDescargas::nome_descarga($a_url['dirname'], $a_url['filename'], $a_url['extension'], $zip);
  }

  public function anotar($anotacion) {
    $txt = "{$anotacion}\n";
    if ($this->timestamp) $txt = "--- " . $this->timestamp() . "\n{$txt}\n--- FA ---\n";

    if ($this->zip) {
      if (!($f = gzopen($this->url, "a9"))) return false;
      gzwrite($f, $txt);
      gzclose($f);
    }
    else {
      if (!($f = fopen($this->url, "a"))) return false;
      fwrite($f, $txt);
      fclose($f);
    }

    return true;
  }

  protected function timestamp() {
    return date("D;d-m-Y;H:i:s");
  }
}

