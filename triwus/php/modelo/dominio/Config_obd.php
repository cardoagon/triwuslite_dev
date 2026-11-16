<?php


final class Config_obd extends Obxeto_bd {
  const url_imx      = "imx/configs/";
  const url_imx_rs   = "imx/rs/";
  const url_imx_v    = "imx/venta/";
  //~ const url_css      = "css/configs/";
  //~ const url_js       = "js/configs/";
  const url_css      = "plantilla/css/";
  const url_js       = "plantilla/js/";
  const url_ptw_pax  = "plantilla/ptw/pax/";
  const url_ptw_prgf = "plantilla/ptw/prgf/";
  const url_ptw_elmt = "plantilla/ptw/elmt/";

  public function __construct($id_config = null, $tipo = "escritorio", FS_cbd $cbd = null) {
    parent::__construct();

    if ($id_config == null) return;

    if ($cbd == null) $cbd = new FS_cbd();

    $this->select($cbd, "id_config = {$id_config} and tipo = '{$tipo}'");
  }

  public function mapa_bd() {
    return new Config_mbd();
  }

  public function select(FS_cbd $cbd, $where = null, $orderby = null) {
    $r = $cbd->consulta($this->sql_select($cbd, $where, $orderby));

    if ($a = $r->next()) {
      //~ echo "<pre>" . print_r($a, true) . "</pre>";
      $this->post($a);
    }
  }

  public function __nula():bool {
    return ($this->atr("id_config")->valor == null);
  }

  public function __venta_online():bool {
    return is_file( $this->ptw_elmt("elmt_ventas") );
  }

  public function plantilla_obd(FS_cbd $cbd = null) {
    if ($this->__nula()) return null;

    if ($cbd == null) $cbd = new FS_cbd();

    return Plantilla_obd::inicia($cbd, $this->atr("id_plantilla")->valor);
  }

  public function computed_style() {
    $c = $this->atr("color")->valor;
    $cf = $this->atr("color_fondo")->valor;

    $t = $this->atr("tipografia")->valor;
    $ts = $this->atr("tipografia_size")->valor;

    return "font-family: {$t}; font-size: {$ts}; color:#{$c}; background-color: #{$cf};";
  }

  public function a_cores() {
    return array($this->atr("color"       )->valor,
                 $this->atr("color2"      )->valor,
                 $this->atr("color_fondo" )->valor,
                 $this->atr("color_fondo2")->valor);
  }

  public function imx($nome) {
    return self::url_imx . $this->atr("id_config")->valor . "/{$nome}";
  }

  public function rs($nome) {
    $ext = ($this->atr("rs_tipo")->valor < 100)?"png":"svg";

    return self::url_imx_rs . $this->atr("rs_tipo")->valor . "/{$nome}.{$ext}";
  }

  public function mobil() {
    return $this->atr("tipo")->valor == "mobil";
  }

  public function venta($nome) {
    return self::url_imx_v . $this->atr("v_tipo")->valor . "/{$nome}.png";
  }

  public function css($sufixo) {
    $n0 = self::url_css . $this->atr("id_css")->valor . "/{$sufixo}";

    if ($this->atr("tipo")->valor == "escritorio") return Refs::url_plantilla_css("{$n0}.css");
    
    if ( is_file("{$n0}.m.css") ) return Refs::url_plantilla_css("{$n0}.m.css");

    return Refs::url_plantilla_css("{$n0}.css");
  }

  //~ public function js($sufixo = null) {
    //~ if ($sufixo != null) $sufixo = ".{$sufixo}";

    //~ return TrwlConfig::dominio . "/" . self::url_js . $this->atr("id_ptw")->valor . "{$sufixo}.js";
  //~ }

  public function ptw_pax($sufixo = null) {
    if ($sufixo != null) $sufixo = ".{$sufixo}";

    return TrwlConfig::pwd . "/" . self::url_ptw_pax . $this->atr("id_ptw")->valor . "{$sufixo}.html";
  }

  public function ptw_prgf($sufixo = null, $id_idioma = null) {
    if ($sufixo    != null) $sufixo    = "/{$sufixo}";
    if ($id_idioma != null) $id_idioma = ".{$id_idioma}";

    return TrwlConfig::pwd . "/" . self::url_ptw_prgf . $this->atr("id_ptw")->valor . "{$sufixo}{$id_idioma}.html";
  }

  public function ptw_elmt($sufixo = null) {
    if ($sufixo != null) $sufixo = "/{$sufixo}";

    return TrwlConfig::pwd . "/" . self::url_ptw_elmt . $this->atr("id_ptw")->valor . "{$sufixo}.html";
  }
}

//--------------------------------------------

final class Config_mbd extends Mapa_bd {
  public function __construct() {
    parent::__construct();

    $t = new Taboa_dbd("v_config");

    $t->pon_campo("id_config", new Numero(), true);
    $t->pon_campo("tipo");
    $t->pon_campo("id_plantilla", new Numero());
    $t->pon_campo("id_ptw", new Numero());
    $t->pon_campo("id_css", new Numero());
    $t->pon_campo("notas");
    $t->pon_campo("tema");
    $t->pon_campo("w", new Numero());
    $t->pon_campo("w_min", new Numero());
    $t->pon_campo("w_max", new Numero());
    $t->pon_campo("logo_w", new Numero());
    $t->pon_campo("logo_h", new Numero());
    $t->pon_campo("factor_h", new Numero());
    $t->pon_campo("ms_tipo");
    $t->pon_campo("ci_tipo");
    $t->pon_campo("rs_tipo");
    $t->pon_campo("v_tipo");
    $t->pon_campo("color");
    $t->pon_campo("color2");
    $t->pon_campo("color_fondo");
    $t->pon_campo("color_fondo2");
    $t->pon_campo("tipografia");
    $t->pon_campo("tipografia_size");

    $this->pon_taboa($t);
  }
}

//********************************************

final class Plantilla_obd extends Obxeto_bd {

  public function __construct() {
    parent::__construct();
  }

  public function mapa_bd() {
    return new Plantilla_mbd();
  }

  public static function inicia(FS_cbd $cbd, $id_plantilla = null) {
    $p = new Plantilla_obd();

    $p->select($cbd, "id_plantilla = {$id_plantilla}", null);

    return $p;
  }

  public function select(FS_cbd $cbd, $where = null, $orderby = null) {
    $r = $cbd->consulta($this->sql_select($cbd, $where, $orderby));

    if (!$a = $r->next()) return;

    $this->post($a);
  }

  public function a_info(FS_cbd $cbd = null) {
    $a_info['id'        ] = $this->atr("id_plantilla")->valor;
    $a_info['nome'      ] = $this->atr("nome")->valor;
    $a_info['creado'    ] = $this->atr("creado")->valor;
    $a_info['id_usuario'] = $this->atr("id_usuario")->valor;

    if ($cbd == null) $cbd = new FS_cbd();

    $r = $cbd->consulta("select id_config, tipo from config where id_plantilla = {$a_info['id']}");

    while ($a = $r->next()) {
      $c_i = new Config_obd($a['id_config'], $a['tipo'], $cbd);

      if ($a['tipo'] != "mobil") {
        //* so temos en conta a v. escritorio para ver os colores, porque a v. mobil ten os mesmos (REDUNDANTE).
        //* manter compatibilidade hacia atras.

        $a_info['cores'][ $a['id_config'] ] = $c_i->a_cores();
      }

      $a_info[ $a['tipo'] ]['w'] = $c_i->atr("w")->valor . "px";
    }


    if ($c_i == null) return $a_info;


    $a_info['tipo'      ] = $c_i->atr("tipografia")->valor . ", " . $c_i->atr("tipografia_size")->valor;


    return $a_info;
  }
}

//--------------------------------------------

final class Plantilla_mbd extends Mapa_bd {
  public function __construct() {
    parent::__construct();

    $t = new Taboa_dbd("plantilla");

    $t->pon_campo("id_plantilla", new Numero(), true);
    $t->pon_campo("nome");
    $t->pon_campo("creado", new Data("d-m-Y H:i:s"));
    $t->pon_campo("id_usuario", new Numero());

    $this->pon_taboa($t);
  }
}
