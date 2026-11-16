<?php

class Imaxe_obd extends Elmt_obd {
  const noimx   = "imx/elmt/imx/noimx.png";
  const tipo_bd = "IMX";
  const tmp     = "tmp/";
  
  private $src_tmp = null;


  public function __construct($url = null) {
    parent::__construct();

    //~ $this->atr("css")->valor = "width:100%;align:center;";

    if ($url != null) $this->post_url($url);
  }

  public function mapa_bd() {
    return new Imaxe_mbd();
  }

  protected function tipo_bd() {
    return self::tipo_bd;
  }

  public function update(FS_cbd $cbd) {
    if (!$this->modificado) return true;

    //~ if ($this->atr("ref_site")->valor == null) die("Imaxe_obd.update(), (this.atr('ref_site').valor == null)");
    
//~ echo "<pre>" . print_r($this->a_resumo(), 1) . "</pre>";

    $this->tmp_valida();
    
//~ echo "url_0::" . $this->url_0() . "<br>";
//~ echo "url_tmp::" . $this->src_tmp . "<br>";
    

    if ( !parent::update($cbd) ) return false;
    
    
    return $this->tmp_update();
  }
  
  public function insert(FS_cbd $cbd) {
    //~ if ($this->atr("ref_site")->valor == null) return false;

    $this->tmp_valida();

    if ( !parent::insert($cbd) ) return false;
    
    
    return $this->tmp_update();
  }

  public function delete(FS_cbd $cbd, $delete_base = true) {
    //~ $url_0 = $this->url();
    
    if (!parent::delete($cbd, $delete_base)) return false;


    //* falta eliminar a imaxe "físicamente". 


    return true;
  }


  public function duplicar(FS_cbd $cbd, ?string $refsite = null) {
    if ($refsite != null) $this->atr("ref_site" )->valor = $refsite;

    return parent::duplicar($cbd, $refsite);
  }


  public function url($mini = 0, FS_cbd $cbd = null) {
    if ($cbd == null) $cbd = new FS_cbd();

    if ($this->atr("url")->valor == null) return self::url_nula();


    return $this->url_0();
  }
  
  public function post_url($url) {
    $this->modificado = true;

    $this->src_tmp    = $url;

//~ echo "post_url()::$this->src_tmp<br>";
    
    if ($url == null) {
      $this->atr("url")->valor = null;

      return;
    }

    $_url = self::url_split($url);

//~ echo "<pre>" . print_r($_url, 1) . "</pre>";

    //~ $this->atr("ref_site")->valor = $_url["ref_site"];
    $this->atr("ref_site")->valor = null;
    $this->atr("url"     )->valor = $_url["url"];
  }

  final public function url_0() {
    return Refs::url_arquivos( $this->atr("url")->valor );
  }

  final public function recortar($x, $y, $w, $h, $carpeta) {
    $src = self::__recortar($this->url(), $x, $y, $w, $h, $carpeta);

    if ($src == null) return false;

    $this->atr("url")->valor = basename($src);


    return true;
  }


  public static function url_nula() {
    return Refs::url(self::noimx);
  }

  final public static function es_mini($url) {
    if ($url == null) return null;

    list($x, $src_0)     = explode("/" . Refs::url_arquivos . "/", $url);
    list($src, $extmini) = explode(".", $src_0);

    return $extmini != null;
  }

  final public static function es_nula($url) {
    return ($url != self::noimx);
  }

  final public static function __recortar($src, $x, $y, $w, $h, $carpeta, $zip = true) {
//~ echo "$src, $x, $y, $w, $h, $carpeta<br>";
    if (!is_file(($src))) return null;

    $tipo = exif_imagetype($src);

    $p1x = $x;
    $p1y = $y;
    $p2x = $w;
    $p2y = $h;

//~ echo "p1 = ({$p1x}, {$p1y}); p2 = ({$p2x}, {$p2y})<br />\n";
//~ echo "tipos(jpg, png, gif) = (" . IMAGETYPE_JPEG . ",  " . IMAGETYPE_PNG . ",  " . IMAGETYPE_GIF . ")<br>\n";

    if     ($tipo == IMAGETYPE_JPEG) $imx0 = imagecreatefromjpeg($src);
    elseif ($tipo == IMAGETYPE_PNG ) $imx0 = imagecreatefrompng ($src);
    elseif ($tipo == IMAGETYPE_GIF ) $imx0 = imagecreatefromgif ($src);
    else   {
      //~ echo "1.-Imaxe non válida, tipo=($tipo)<br>";

      return null;
    };

    $w1 = $w;
    $h1 = $h;

    $imx1 = imagecreatetruecolor($w1, $h1);

    if ($tipo == IMAGETYPE_PNG ) {
      imagealphablending($imx1, false);
      imagesavealpha    ($imx1, true );
    }

    imagecopyresampled($imx1, $imx0, 0, 0, $p1x, $p1y, $w1, $h1, $w1, $h1);

    //~ $id_file = uniqid( $carpeta );
    $id_file = $carpeta . File::calcula_nome($src);
    
    $z = ($zip)?50:100;

    //~ echo "Imaxe 2=($id_file)<br>\n";
    if     ($tipo == IMAGETYPE_JPEG) imagejpeg($imx1, $id_file, $z     );
    elseif ($tipo == IMAGETYPE_PNG ) imagepng ($imx1, $id_file, $z / 10);
    elseif ($tipo == IMAGETYPE_GIF ) imagegif ($imx1, $id_file, $z     );
    else   {
      //~ echo "2.-Imaxe non válida, tipo=($tipo)<br>\n";

      imagedestroy($imx0);
      imagedestroy($imx1);

      return null;
    };


    imagedestroy($imx0);
    imagedestroy($imx1);


    return $id_file;
  }

  final public static function url_split($url) {
    if ($url == null) return null;

//~ echo "(url)::($url)\n";

    list($x, $url_2) = explode("/" . Refs::url_arquivos . "/", $url);

//~ echo "(x, url_2)::($x, $url_2)\n";

    //~ $ref_site = str_replace(Refs::url_sites . "/", "", str_replace("triwus/", "", $x));

    //~ return array(0=>$ref_site, 1=>$url_2, "ref_site"=>$ref_site, "url"=>$url_2);
    return array(0=>null, 1=>$url_2, "ref_site"=>null, "url"=>$url_2);
  }

  final public static function valida_proporcion(?string $p):?float {
    //* valida numeros reales

    if ($p       == null) return null;
    if (trim($p) == ""  ) return null;

    if ( ($pos = strpos($p, ":")) !== false ) {
      if ($pos < 1) return null;
      
      list($num, $den) = explode(":", $p);
      
      if (($num = Valida::numero($num)) == 0) return null;
      if (($den = Valida::numero($den)) == 0) return null;
      
      return abs( round($num / $den, 3) ); 
    }


    if (($p = Valida::numero($p, 3)) > 0) return $p;


    return null;
  }

  private function tmp_valida():void {
    $url = $this->atr("url")->valor;
    
    if ( strpos(strval($url), self::tmp) === false ) return;


    $this->atr("url")->valor = str_replace(self::tmp, "", $url);
  }

  private function tmp_update():bool {
    if ($this->atr("url")->valor == null) return true;
    
    
    $url_0 = $this->url_0();
  
//~ echo "$url_0::$this->src_tmp<br>";
  
    if ( is_file($url_0)          ) return true;
                                  
    if ( $this->src_tmp == null   ) return true;
    
    if ( !is_file($this->src_tmp) ) return true;

    return link($this->src_tmp, $url_0);
  }
}

//--------------------------------------------------------

final class Imaxe_mbd extends Elmt_mbd {
  public function __construct() {
    parent::__construct();

    $t = new Taboa_dbd("elmt_imaxe");

    $t->pon_campo("id_elmt"    , new Numero(), true);
    $t->pon_campo("ref_site"   , new VarChar());
    $t->pon_campo("url"        , new VarChar());
    $t->pon_campo("link_href"  , new VarChar());
    $t->pon_campo("link_target", new VarChar());

    $this->relacion_fk($t, array("id_elmt"), array("id_elmt"));
  }
}

