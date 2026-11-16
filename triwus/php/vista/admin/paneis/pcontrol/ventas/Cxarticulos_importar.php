<?php

final class Cxarticulos_importar extends Componente {
  const ptw_0 = "ptw/paneis/pcontrol/ventas/articulo/cxarticulos_importar.html";

  private $id_site    = null;

  private $url_upload = null;
  private $url_csv    = null;

  public function __construct($id_site, $id_almacen) {
    parent::__construct("cxai", self::ptw_0);

    $this->visible = false;

    $this->id_site = $id_site;


    $this->pon_obxeto(new Checkbox("chive", true, "Importes sin IVA."));

    $this->pon_obxeto($this->__fcsv($id_site));

    $this->pon_obxeto(self::__lcsv());

    $this->pon_obxeto( Cxarticulos_busca::__salmacen($id_site, $id_almacen, null) );

    $this->pon_obxeto(Panel_fs::__baceptar("Importar"));
    $this->pon_obxeto(Panel_fs::__bcancelar());

    $this->obxeto("salmacen")->post($id_almacen);
    $this->obxeto("salmacen")->readonly = ($id_almacen > 1);


    $this->obxeto("bCancelar")->envia_ajax("onclick");
    //~ $this->obxeto("bAceptar" )->envia_ajax("onclick");
  }

  public function operacion(EstadoHTTP $e) {
//~ echo $e->evento()->html();

    if (!$this->visible) return null;


    if ($this->obxeto("lcsv"     )->control_evento()) return $this->operacion_bExportar($e, 0);

    if ($this->obxeto("fcsv"     )->control_evento()) return $this->operacion_fcsv($e);

    if ($this->obxeto("bCancelar")->control_evento()) return $this->operacion_bCancelar($e);

    if ($this->obxeto("bAceptar" )->control_evento()) return $this->operacion_bAceptar($e);


    return null;
  }

  private function operacion_bExportar(Efs_admin $e, $tipo) {
    $eCSV = new Cxaimportar_pex($tipo);

    $titulo = "trw-productos-" . date("YmdHis") . ".csv";

    $e->pon_mime(null, "text/csv", $eCSV->escribe(), null, $titulo);

    return $e;
  }

  private function operacion_bAceptar(Efs_admin $e) {
    $id_almacen = $this->obxeto("salmacen")->valor();

    list($ok, $msx) = Cxaimportar_x::exec($this->id_site, $id_almacen, $this->url_csv, $this->obxeto("chive")->valor(), $e->url_arquivos());

        if ($ok == -2) $e->post_msx($msx, "e");
    elseif ($ok == -1) $e->post_msx($msx, "a");
    elseif ($ok ==  1) $e->post_msx($msx, "m");

    return $e;
  }

  private function operacion_fcsv(Efs_admin $e) {
    $this->url_csv = $this->obxeto("fcsv")->post_f();

    list($msx, $tipo) = Cxaimportar_x::valida_cab( $this->url_csv );

    $e->post_msx($msx, $tipo);


    return $e;
  }

  private function operacion_bCancelar(Efs $e) {
    $this->visible = false;

    $this->pai->preparar_saida($e->ajax());

    return $e;
  }

  private function __fcsv($id_site) {
    $tmp = Efs::url_site($id_site) . Refs::url_arquivos . "/" . Refs::url_tmp . "/";
    $mfs = 1024 * 1024 * 5;

    $f = new File("fcsv", $tmp, $mfs);

    $f->accept = ".csv";

    //~ $f->envia_submit("onchange");


    return $f;
  }

  private static function __lcsv() {
    $o = new Div("lcsv", "Descarga una plantilla CSV");

    $o->clase_css("default", "lista_elemento_a");

    $o->title = "Descarga una plantilla del archivo CSV de importación";

    $o->envia_MIME("onclick");


    return $o;
  }
}

//*******************************************

final class Cxaimportar_x {
  public static $val_cab    = ["nombre", "ean", "importe", "pive", "unidades", "id_marca", "id_categoria", "foto", "nombre_seo", "notas_seo", "notas"];

  public static $val_cab_0  = ["nombre", "importe", "pive"];


  private static $t_max = 50; //* maximo de filas a importar.

  private function __construct() {}


  public static function exec($id_site, $id_almacen, $src, $importes_ive, $url_arquivos) {
//~ echo "$id_site, $src, $importes_ive<br>";

    if ( !is_file( strval($src) ) ) {
      return [-1, "Cxaimportar_x.exec(). !is_file({$src})."];
    }

    //* Inicia vbles necesarias.
    $lcsv    = new Cxaimportar_lectorCSV($src);

    $_ive    = self::_ive();

    $_cab    = $lcsv->_cab();

    $cab_t   = count( $_cab );

    //* iniciamos bucle de importacion.
    $adv = ""; $ct_ok = 0;
    for ( $i = 1; (($_csv = $lcsv->_next()) && ($i < self::$t_max)); $i++ ) {

      $cbd = new FS_cbd(); //* 1 conexion por tupla csv.


      //* validacion det_0.
      list($ok, $msx) = self::valida_det_0($_csv, $i, $cab_t);

      if ($ok == -1) {
        //~ $adv .=  print_r($_cab, 1) . $msx;
        $adv .= $msx;

        $cbd->close();

        continue;
      }


      //* iniciamos Articulo_obd.
      list($ok, $a_obd, $msx) = self::articulo_obd($cbd, $i, $id_site, $id_almacen, $_cab, $_csv, $_ive, $importes_ive);

      if ($ok == -1) {
        $adv .= $msx;

        $cbd->close();

        continue;
      }


      //* insert/update Articulo_obd.
      $cbd->transaccion();

      if (!self::update($cbd, $a_obd)) {
        $adv .= self::val_msx("Fila {$i} será ignorada: sucedio un error al actualizar la BD");

        $cbd->rollback();
        $cbd->close();

        continue;
      }

      //* probamos a engadir foto.
      if ( !self::update_foto($cbd, $a_obd, $_cab, $_csv, $url_arquivos) ) {
        $adv .= self::val_msx("Fila {$i}: no se importó la foto");
      }

      //* tudo bem.
      $ct_ok++;

      $cbd->commit();
      $cbd->close();
    }

    //* devolvemos resultado da importacion.
    $m_ct = self::val_msx( "<b>{$ct_ok}</b> productos importados o actualizados" );


    if ($adv != "") return [-1, $adv . $m_ct];

    return [ 1, self::val_msx( "Archivo CSV importado sin errores" ) . $m_ct ];
  }

  public static function valida_cab($src) {
    $lcsv = new Cxaimportar_lectorCSV($src);

    $_cab = $lcsv->_cab();

    if ( ($m = self::valida_cab_0($_cab)) != "") return [$m, "e"];

    if ( ($m = self::valida_cab_1($_cab)) != "") return [$m, "a"];


    return [null, null];
  }


  private static function valida_cab_0($_c) {
    //* valida campos necesarios.
    $s = "";
    foreach (self::$val_cab_0 as $k=>$v) {
      if ( !in_array($v, $_c) ) $s .= self::val_msx("<b>{$v}</b>: falta columna necesaria");
    }

    return $s;
  }

  private static function valida_cab_1($_c) {
    //* valida campos desconocidos.
    $s = "";
    foreach ($_c as $k=>$v) {
      if ( !in_array($v, self::$val_cab) ) $s .= self::val_msx("<b>{$v}</b>: columna desconocida, será ignorada");
    }

    return $s;
  }

  private static function valida_det_0($_d, $i, $cab_t) {
    if (count($_d) != $cab_t) {
      $m = "Fila {$i} será ignorada: nº de columnas de la fila, no coincide con el nº de columnas de la cabecera";
      //~ $m = "Fila {$i}: <pre>" . print_r($_d, 1) . "</pre>";

      return [-1, self::val_msx($m)];
    }

    return [1, null];
  }

  private static function articulo_obd(FS_cbd $cbd, $i, $id_site, $id_almacen, $_cab, $_det, $_ive, $importes_ive) {
//~ echo "<pre>" . print_r($_ive, 1) . "</pre>";
    $ref_trw = self::csv2atr("ref_trw", $_cab, $_det);

    //* tipo.
    if (($tipo = self::csv2atr("tipo", $_cab, $_det)) == null) $tipo = "a";

    //* pive.
    if (($pive = self::csv2atr("pive", $_cab, $_det)) == null) $pive = "21";
    if ( !isset($_ive[ $pive ]) ) {
      $m = "Fila {$i} será ignorada: %IVA incorrecto";

      return [-1, null, self::val_msx($m)];
    }

  //~ public static $csv_pex0_0 = ["nombre", "ean", "importe", "pive", "unidades", "id_marca", "id_categoria", "foto", "nombre_seo", "notas_seo", "notas"];


    $a_obd = Articulo_obd::inicia($cbd, $id_site, $ref_trw);

    $a_obd->atr("tipo"        )->valor = $tipo;
    $a_obd->atr("id_almacen"  )->valor = $id_almacen;
    $a_obd->atr("id_ive"      )->valor = $_ive[ $pive ];
    $a_obd->atr("pvp"         )->valor = self::csv2importe($importes_ive, $pive, $_cab, $_det);
    $a_obd->atr("nome"        )->valor = self::csv2atr("nombre"      , $_cab, $_det);
    $a_obd->atr("ean"         )->valor = self::csv2atr("ean"         , $_cab, $_det);
    $a_obd->atr("notas"       )->valor = self::csv2atr("notas"       , $_cab, $_det);
    $a_obd->atr("unidades"    )->valor = self::csv2atr("unidades"    , $_cab, $_det);
    $a_obd->atr("id_marca"    )->valor = self::csv2atr("id_marca"    , $_cab, $_det);
    $a_obd->atr("id_categoria")->valor = self::csv2atr("id_categoria", $_cab, $_det);
    $a_obd->atr("nome_seo"    )->valor = self::csv2atr("nombre_seo"  , $_cab, $_det);
    $a_obd->atr("notas_seo"   )->valor = self::csv2atr("notas_seo"   , $_cab, $_det);

//~ echo "<pre>" . print_r($a_obd->a_resumo(), 1) . "</pre>";


    return [1, $a_obd, null];
  }

  private static function update(FS_cbd $cbd, Articulo_obd $a) {
    if ($a->baleiro()) {
      if (!$a->insert($cbd, null, null)) return false;
    }
    else {
      if (!$a->update($cbd)) return false;
    }


    return true;
  }

  private static function csv2atr($k, $_cab, $_det) {
//~ echo "{$k}::";

    if (($i_k = array_search($k, $_cab)) === false) return null;

//~ echo "({$i_k})::{$_det[$i_k]}||<br>";

    return $_det[$i_k];
  }

  private static function csv2importe($importes_ive, $pive, $_cab, $_det) {
    $i = Valida::numero( self::csv2atr("importe" , $_cab, $_det), -1 );

    if (!$importes_ive) return round($i, 3);

    $i = abs($i) / (1 + ( $pive / 100 ));

    return round($i, 3);
  }

  private static function _ive() {
    $cbd = new FS_cbd();

    $r = $cbd->consulta("select id_ive, pive from tipos_ive");

    $_ive = [];
    while ($_r = $r->next()) $_ive[$_r['pive']] = $_r['id_ive'];

    return $_ive;
  }

  private static function update_foto(FS_cbd $cbd, Articulo_obd $a, $_cab, $_det, $url_arquivos) {
    //* Control de existencia. Se NON hai foto, damos a operacion, daremola por boa.
    if (($url_ext = self::csv2atr("foto" , $_cab, $_det)) == null) return true;

    $url_f = uniqid("{$url_arquivos}/");

    if (!Parse_Html_meta::imxscan($url_ext, $url_f)) return false;

    //* Actualizamos a BD.
    return $a->iclogo_update($cbd, $url_f);
  }

  private static function val_msx($m) {
    return "<div>&bull;&nbsp;{$m}.</div>";
  }
}

//******************************************

final class Cxaimportar_pex extends    EscritorResultado_csv
                            implements Iterador_bd {
  private $tipo = -1;

  public static $csv_pex0_0 = ["nombre", "ean", "importe", "pive", "unidades", "id_marca", "id_categoria", "foto", "nombre_seo", "notas_seo", "notas"];
  public static $csv_pex0_d =
[
  ["lorem ipsum 1", "", 20.3, 21, 0, "", 1, "https://triwus.com/imx/pcontrol/login/bg24.jpg", "SEO - lorem ipsum 1", "SEO - notas lorem ipsum 1", "notas lorem ipsum 1"],
  ["lorem ipsum 2", "", 20.3, 10, 0, "", 1, "https://triwus.com/imx/pcontrol/login/bg20.jpg", "SEO - lorem ipsum 2", "SEO - notas lorem ipsum 2", "notas lorem ipsum 2"],
  ["lorem ipsum 3", "", 20.3, 4 , 0, "", 1, "https://triwus.com/imx/pcontrol/login/bg21.jpg", "SEO - lorem ipsum 3", "SEO - notas lorem ipsum 3", "notas lorem ipsum 3"],
  ["lorem ipsum 4", "", 20.3, 0 , 0, "", 1, "https://triwus.com/imx/pcontrol/login/bg25.jpg", "SEO - lorem ipsum 4", "SEO - notas lorem ipsum 4", "notas lorem ipsum 4"]
];

  //~ public static $csv_pex0_0 = ["nombre", "importe", "pive", "unidades", "foto", "notas"];
  //~ public static $csv_pex0_d =
//~ [
  //~ ["lorem ipsum 1", 20.3, 21, 0, "https://triwus.com/imx/pcontrol/login/bg24.jpg", ""],
  //~ ["lorem ipsum 2", 20.3, 10, 0, "https://triwus.com/imx/pcontrol/login/bg20.jpg", ""],
  //~ ["lorem ipsum 3", 20.3, 4 , 0, "https://triwus.com/imx/pcontrol/login/bg21.jpg", ""],
  //~ ["lorem ipsum 4", 20.3, 0 , 0, "https://triwus.com/imx/pcontrol/login/bg25.jpg", ""]
//~ ];

  private $t = 0;  //* tipo
  private $i = -1; //* contador

  public function __construct($tipo) {
    parent::__construct( $this );

    $this->t = $tipo;
  }

  protected function cabeceira($df = null) {
    if ($this->t == -1) return "";

    $_cab = null;

    if ($this->t == 0) $_cab = self::$csv_pex0_0;

    if ($_cab == null) return "";

    return self::escibe_fila_csv( $_cab );
  }

  protected function linha_detalle($df, $f) {
    return self::escibe_fila_csv( $f );
  }

  //~ private static function iterador() {
    //~ return $this;
  //~ }

  public function descFila() {} //* IMPLEMENTA Iterador_bd.descFila()

  public function next()  { //* IMPLEMENTA Iterador_bd.next()
    switch ($this->t) {
      case 0: return $this->next_0();
    }

    return null;
  }

  private function next_0()  {
    $t = count(self::$csv_pex0_d);

    $this->i++;

    if ($this->i >= $t) {
      $this->i = -1;

      return null;
    }

    return self::$csv_pex0_d[$this->i];
  }

  private static function escibe_fila_csv( $_a )  {
    $csv = "";
    foreach($_a as $k=>$v) {
      if ($csv != "") $csv .= ";";

      if ($v === 0) $v = "0";

      if (!is_numeric($v))  $v = "\"{$v}\"";

      $csv .= $v;
    }

    return "{$csv}\n";
  }
}

//*******************************************

final class Cxaimportar_lectorCSV {
  private $f;

  private $_cab;

  public function __construct($src) {
    $this->f    = fopen($src, "r");

    $this->_cab = $this->_next();
  }

  public function _cab() {
    return $this->_cab;
  }

  public function _next() {
    $_r = fgetcsv($this->f, 0, ";");

    if ($_r === FALSE) return null;

    return $_r;
  }
}
