<?php

abstract class Editor_style extends Componente {
  protected static $a_eah = array("0"=>"left", "1"=>"center", "2"=>"right", "left"=>"0", "center"=>"1", "right"=>"2");
  protected static $a_eav = array("0"=>"top", "1"=>"middle", "2"=>"bottom", "top"=>"0", "middle"=>"1", "bottom"=>"2");
 
  protected bool $ffondo_borrar = false;

  private ?int $id_style = null;

  public function __construct(Style_obd $s = null, ?string  $ptw = null) {
    parent::__construct("cstyle", $ptw);

    $this->style_obd($s);
  }

  public function style_obd(Style_obd $s = null) {
    if ($s != null) {
      $this->id_style = $s->atr("id_style")->valor;

      $this->post_style($s);

      return;
    }

    $s = new Style_obd($this->id_style);

    //~ echo "<pre>" . print_r($this->a_props(), true) . "</pre>";


    $s->a_props($this->a_props());

    return $s;
  }

  abstract protected function a_props():?array; 
  abstract protected function post_style(Style_obd $s):void;


  final public function align_h() {
    return self::$a_eah[$this->obxeto("align")->valor()];
  }

  final public function ffondo():?string {
    if ($this->ffondo_borrar()                  ) return null;
    
    if (($obx = $this->obxeto("ffondo")) == null) return null;
    
    return $obx->f();
  }

  final public function ffondo_borrar(bool $b = null):bool {
    if ($b == null) return $this->ffondo_borrar;
    
    
    $this->ffondo_borrar = $b;
    
    return $this->ffondo_borrar;
  }

  final public static function __parseN($cadea) {
    //* recibe unha cadea alpha-numérica e dovolve un número natural.
    $cadea = (string)$cadea;

    $n = "";
    for ($i = 0; $i < strlen($cadea); $i++) {
      if (!is_numeric($cadea[$i])) continue;

      $n .= $cadea[$i];
    }


    return $n;
  }

  final protected static function __number($nome, $valor, $min = 0, $max = 100, $step = 1, $uds = "px") {
    $t = new Range($nome, $min, $max, $step, $uds);

    $t->post(self::__parseN($valor));

    return $t;
  }

  final protected static function __scolor($nome, $color) {
    return new SColor($color, $nome);
  }

  final protected static function __imx($nome, $src) {
    return new Param($nome, $src);
  }

  final protected static function __cborde($nome, $css) {
    return new CBorde($nome, $css);
  }

  final protected static function __cmarxe($nome, $css_margin) {
    return new CMarxe($nome, $css_margin);
  }

  final protected static function __sunidades($nome, $valor = "px") {
    $s = new Select($nome, array("px"=>"px", "%"=>"%"));

    $s->post($valor);

    return $s;
  }

  final protected static function __ralign_v($nome, $valor) {
    $r = new Radio($nome);

    $r->pon_opcion(self::etq_opcion(Ico::balign_vt, "arriba"));
    $r->pon_opcion(self::etq_opcion(Ico::balign_vc, "medio"));
    $r->pon_opcion(self::etq_opcion(Ico::balign_vb, "abajo"));

    $r->post(self::$a_eav[$valor]);

    return $r;
  }

  final protected static function __ralign_h($nome, $valor) {
    $r = new Radio($nome);

    $r->pon_opcion(self::etq_opcion(Ico::balign_hl, "izda"));
    $r->pon_opcion(self::etq_opcion(Ico::balign_hc, "centro"));
    $r->pon_opcion(self::etq_opcion(Ico::balign_hr, "dcha"));

    $r->post(self::$a_eah[$valor]);

    return $r;
  }

  final protected static function __ffondo(string $nome, int $megas = 1):File {
    $f = new File($nome, refs::url_tmp2(), 1024 * 1024 * $megas);

    //~ $f->clase_css("default", "file_apagado");


    $f->accept      = "image/gif, image/jpg, image/jpeg, image/png, image/webp";


    return $f;
  }

  final protected static function __ffondo_bsup(string $nome):Button {
    $b = Panel_fs::__bsup($nome, "Borrar fondo", "Elimina la imagen de fondo el párrafo.");

    $b->envia_AJAX("onclick");

    return $b;
  }
  
  private static function etq_opcion($img, $txt) {
    return "<img src='{$img}' style='width: 12px; height: 12px;' /><small>&nbsp;{$txt}</small>";
  }
}

//------------------------------------

final class Editor_style_nulo extends Editor_style {

  public function __construct() {
    parent::__construct();
  }

  public function style_obd(Style_obd $s = null) {
    return null;
  }


  protected function a_props():?array {}
  protected function post_style(Style_obd $s):void {}
}

