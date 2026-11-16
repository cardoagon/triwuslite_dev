<?php


final class Galeria_obd extends Elmt_obd {
  const tipo_bd = "GALERIA";

  public static $_tgaleria = ["m" => "Mosaico", "p" => "Producto"];

  public $f = null;

  public function __construct() {
    parent::__construct();

    $this->atr("nome")->valor = uniqid();
  }

  public function mapa_bd() {
    return new Galeria_mbd();
  }

  protected function tipo_bd() {
    return self::tipo_bd;
  }

  public function post_fotos(BD_fotos $f) {
    $this->f = $f;
  }

  public function update(FS_cbd $cbd) {
    if (!$this->modificado) return true;
    
    if ($this->f != null) if (!self::update_fotos($this->f)) return false;


    return parent::update($cbd);
  }

  public function insert(FS_cbd $cbd) {
    if (!$this->modificado) return true;
    
    if ($this->f != null) if (!self::update_fotos($this->f)) return false;


    return parent::insert($cbd);
  }
  
  public function duplicar(FS_cbd $cbd, ?string $refsite = null) {
    //* duplicamos BD.
    $this->modificado = true;
    
    $nome_0 = $this->atr("nome")->valor;
    
    $this->atr("nome")->valor = uniqid();

    if ( !parent::duplicar($cbd, $refsite) ) return false;
    
    //* duplicamos carpeta imaxes.
    //~ if ($refsite == null) return true;
    
    $src_0 = self::duplicar_calcula_src($nome_0                  ); if ( !is_dir($src_0) ) return true ;
    $src_1 = self::duplicar_calcula_src($this->atr("nome")->valor); if ( !mkdir ($src_1) ) return false;
    
    foreach (glob("{$src_0}*") as $f) {
      if (is_dir($f)) continue;
      
      if ($f == "{$src_0}bd.json") {
        file_put_contents( "{$src_1}bd.json", 
                           str_replace($nome_0, $this->atr("nome")->valor, file_get_contents($f)) 
                         );
                         
        continue;
      }
            
      //~ echo "link($f1, $f)";
      //~ link($f, $src_1);
      exec("ln '{$f}' '{$src_1}'");
    }
    
    return true;
  }

  public function delete(FS_cbd $cbd, $delete_base = true) {
    //~ if (!$this->modificado) return true;
    
    //~ $this->f->borra_carpeta();

    return parent::delete($cbd, $delete_base);
  }

  private static function update_fotos(BD_fotos $f) {
    $f->pecha();

    
    return true;
  }

  private static function duplicar_calcula_src(tring $nome):string {
    return Refs::url_galerias . "/{$nome}/";
  }
/*
  private static function duplicar_calcula_src(string $refsite, string $nome):string {
    return Refs::url_sites . "/{$refsite}/" . Refs::url_galerias . "/{$nome}/";
  }
*/
}

//--------------------------------------------------------

final class Galeria_mbd extends Elmt_mbd {
  public function __construct() {
    parent::__construct();

    $t = new Taboa_dbd("elmt_galeria");

    $t->pon_campo("id_elmt"   , new Numero(), true);
    $t->pon_campo("frecuencia", new Numero()      );
    $t->pon_campo("numcols"   , new Numero()      );
    $t->pon_campo("formato"   , new Numero()      );
    $t->pon_campo("tgaleria"                      );

    $this->relacion_fk($t, array("id_elmt"), array("id_elmt"));
  }
}

//***********************************

final class BD_fotos {
  
  const url_json = "bd.json";


  private $_bd = [];

  private $src = null;

  public function __construct($src = null) {
    if ($src == null) return;
    
    $this->src = $src;

    if (!is_file($src . self::url_json)) self::__reset_bd($src);

    $this->abre();
  }
  
  public static function __reset_bd($src) {
    if(!is_dir($src)) { mkdir($src, 0777, true); }

    $_bd = array();
    
    foreach (glob("{$src}*") as $url) {
      if (($b = basename($url)) == self::url_json) continue;
      
            
      $_bd[] = self::__rexistrar($url, "0");
    }
    
    file_put_contents( $src . self::url_json, json_encode($_bd) );
  }
  
  public function clonar() {
    $bd2 = new BD_fotos();

    if (is_array($this->_bd))
      foreach($this->_bd as $r) $bd2->_bd[] = clone $r;

    $bd2->src = $this->src;
    

    return $bd2;
  }

  public function total() {
    return count($this->_bd);
  }

  public function intercmabiar($i_0, $i_1) {
    $bd_0 = $this->_bd[$i_0];

    $this->_bd[$i_0] = $this->_bd[$i_1];

    $this->_bd[$i_1] = $bd_0;
  }

  public function pon($url, $m = "+") {
    $this->_bd[] = self::__rexistrar($url, $m);
  }

  public function sup($i) {
    if (file_exists(($this->_bd[$i]->url))) unlink($this->_bd[$i]->url);
    
    unset( $this->_bd[$i] );
  }

  public function nome($i, $nome = null) {
    if ($nome != null) {
      $this->_bd[$i]->nome = $nome;
      $this->_bd[$i]->m    = "1";
    }
    

    return $this->_bd[$i]->nome;
  }

  public function url($i, $url = null) {
    if ($url != null) $this->_bd[$i]->url = $url;

    return $this->_bd[$i]->url;
  }

  public function marca($i) {
    return $this->_bd[$i]->m;
  }

  public function marcar($i, $m = "-") {
    $this->_bd[$i]->m = $m;
  }

  public function _url() {
    $_url = null;

    for ($i = 0; $i < count($this->_bd); $i++) $_url[$this->_bd[$i]->url] = 1;
    

    return $_url;
  }
  
  public function __json() {
    return json_encode($this->_bd);
  }

  public function pecha($confirmar = true, $marca_sup = "-") {
    if (!$confirmar) return;

//~ echo "<pre>" . print_r($this->_bd, true) . "</pre>";
    
    $_bd2 = array();
    
    for($i = 0; $i < $this->total(); $i++) {
      if ($this->_bd[$i]->m == $marca_sup) {
        if (file_exists(($this->_bd[$i]->url))) unlink($this->_bd[$i]->url);

        continue;
      }

      if ($this->_bd[$i]->m == "+") $this->_bd[$i]->m = "0";

      $_bd2[] = $this->_bd[$i];
    }
    
    
    file_put_contents( $this->src . self::url_json, json_encode($_bd2) );
  }

  public function borra_carpeta($src = null) {
    if ($src == null) $src = $this->src;
    
    foreach (glob("{$src}*") as $url) {
      if (is_dir($url)) return $this->borra_carpeta($url);

      unlink($url);
    }
    
    if (is_dir($src)) rmdir($src);
  } 
  
  private function abre($b = true) {
    $this->_bd = array();


//~ print_r("{$elmt->src}\n");
    $ubd = $this->src . self::url_json;

    if (!is_file($ubd)) return;

    $this->_bd = json_decode( file_get_contents($ubd) );

    if ($b) {
      $this->pecha(true, "+");

      $this->abre(false);
    }
  }

/*
  private static function __rexistrar($url, $m) {
    //* controla a url en función desde donde se crea a base de datos (editor ou web).
    list($url1, $url2) = explode("triwus/", $url);

    $url = ($url2 == null)?$url1:$url2;

    //* xenera o rexistro da foto.
    
    $_r = array("url" => $url, "nome" => basename($url), "m" => $m);

    return json_decode(json_encode($_r)); //* devolve un obxeto.
  }
*/

  private static function __rexistrar($url, $m) {
    //* controla a url en función desde donde se crea a base de datos (editor ou web).
    $_url = explode("triwus/", $url);

    $url = ( isset($_url[1]) ) ? $_url[1] : $_url[0];

    //* xenera o rexistro da foto.
    
    $_r = array("url" => $url, "nome" => basename($url), "m" => $m);

    return json_decode(json_encode($_r)); //* devolve un obxeto.
  }

}
