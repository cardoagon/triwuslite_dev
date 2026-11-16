<?php

abstract class Panel_fs extends Panel {

  public function __construct($nome = "panel_fs", $ptw = null) {
    parent::__construct($nome, $ptw);
  }

  public function validar(Efs_admin $e) {
    return null;
  }

  public function op_next(Efs_admin $e) {
    return $e;
  }

  public static function __sFPago($nome, $predet = "**") {
    $o = new Select( $nome, Contacto_fpago_obd::_pref($predet) );

    
    return $o;
  }

  public static function __email($nome, $size = 20, $maxlength = 99, $placeholder = null) {
    $t = new Email($nome);

    if ($placeholder == null) $placeholder = " ";

    $t->size = $size;
    $t->maxlength = $maxlength;
    $t->placeholder = $placeholder;

    return $t;
  }

  public static function __text($nome, $size = null, $maxlength = null, $placeholder = null) {
     $t = new Text($nome);

    if ($placeholder == null) $placeholder    = " ";

    if ($size        != null) $t->size        = $size;
    if ($maxlength   != null) $t->maxlength   = $maxlength;
    if ($placeholder != null) $t->placeholder = $placeholder;


    return $t;
  }

  public static function __dataInput($nome, $readonly = false, $title = "") {
    $t = new DataInput($nome);

    $t->title   = $title;
    $t->readonly = $readonly;


    return $t;
  }

  public static function __hhmm($id, $readonly = false, $hhmm = null) {
    $o = new HoraInput($id);

    $o->post($hhmm);

    $o->clase_css("default" , null);
    $o->clase_css("readonly", null);

    $o->readonly = $readonly;

    return $o;
  }

  public static function __search($nome, $size = 20, $maxlength = 99, $placeholder = null) {
    $t = new Search($nome);

    if ($placeholder == null) $placeholder = $nome;

    $t->size = $size;
    $t->maxlength = $maxlength;
    $t->placeholder = $placeholder;

    return $t;
  }

 /*
  public static function __password($nome, $size = 20, $maxlength = 99, $placeholder = null) {
    $t = new Password($nome);

    if ($placeholder == null) $placeholder = $nome;

    $t->size = $size;
    $t->maxlength = $maxlength;
    $t->placeholder = $placeholder;

    return $t;
  }
*/

  public static function __sSiNon($k, $predet = "...") {
    $_o = array();

    if ($predet != null) $_o["**"] = $predet;

    $_o["1"] = "Sí";
    $_o["0"] = "No";

    $s = new Select($k, $_o);

    $s->post("**");

    return $s;
  }

  public static function __sImxForma($k) {
    $s = new Select($k, []);

    $s->pon_grupo("0", "Proporción");
    
    $s->pon_opcion("**", "Libre", "0");

    $s->pon_grupo("1", "Cuadrado");
    
    $s->pon_opcion( 1, "1 : 1","1");

    $s->pon_grupo("h", "Horizontal");
    
    $s->pon_opcion( "1.78", "16 : 9", "h");
    $s->pon_opcion( "1.5" , "3 : 2" , "h");
    $s->pon_opcion( "1.33", "4 : 3" , "h");
    $s->pon_opcion( "1.25", "5 : 4" , "h");

    $s->pon_grupo("v", "Vertical");
    
    $s->pon_opcion( "0.56", "9 : 16", "v");
    $s->pon_opcion( "0.67", "2 : 3" , "v");
    $s->pon_opcion( "0.75", "3 : 4" , "v");
    $s->pon_opcion( "0.8" , "4 : 5" , "v");


    $s->post("**");
    

    return $s;
  }

  public static function __dlImxForma($k, bool $libre = false) {
    $s = new DataList($k);
    
    if ($libre) $s->pon_opcion("**", "Libre");
    
    $s->pon_opcion( 1, "1 : 1 - Cuadrado");
    
    $s->pon_opcion( "1.78", "16 : 9 - Horizontal");
    $s->pon_opcion( "1.5" , " 3 : 2 - Horizontal");
    $s->pon_opcion( "1.33", " 4 : 3 - Horizontal");
    $s->pon_opcion( "1.25", " 5 : 4 - Horizontal");
    
    $s->pon_opcion( "0.56", "9 : 16 - Vertical"  );
    $s->pon_opcion( "0.67", "2 :  3 - Vertical"  );
    $s->pon_opcion( "0.75", "3 :  4 - Vertical"  );
    $s->pon_opcion( "0.8" , "4 :  5 - Vertical"  );


    $s->post("**");
    

    return $s;
  }

  public static function __password($nome, $ollo = true, $readonly = false, $size = 20, $maxlength = 99, $placeholder = null) {
    $t = new Password($nome, $readonly, $ollo);

    if ($placeholder == null) $placeholder = " ";

    $t->size        = $size;
    $t->maxlength   = $maxlength;
    $t->placeholder = $placeholder;

    return $t;
  }
/*
  public static function __password_lver($nome, $p, $asterisco = "*") {
    $lp      = strlen($p);
    $ocultar = $lp / 2;

    $p_ver = str_pad(substr($p, $ocultar), $lp, $asterisco, STR_PAD_LEFT);


    $l = new Link($nome, "ver");

    $l->clase_css("default", "texto3_a");

    $l->pon_eventos("onclick", "alert('{$p_ver}')");

    return $l;
  }
*/
  public static function __number($nome, $min = 0, $max = 100, $step = 1) {
    $t = new Text($nome);

    return $t;
  }

  public static function __textarea($nome, $valor = null, $readonly = false, $placeholder = null) {
    $ta = new Textarea($nome, 4, 20, $readonly);

    $ta->post($valor);

    $ta->placeholder = $placeholder;

    $ta->clase_css("default", "textarea");
    $ta->clase_css("readonly", "textarea_r");

    return $ta;
  }

  public static function __bactualiza($id, $txt = "Actualizar", $title = "Actualizar") {
    $etq = self::__boton_etq($txt, "<span style='font-size: 1.3em;'>⟲</span>");

    return self::__button($id, $etq, true, $title);
  }

  public static function __bexportar($id, $txt = "Exportar", $title = "Exportar") {
    $etq = self::__boton_etq($txt, "<span style='font-weight: bold; font-size: 1.1em; text-decoration:underline;'>↓</span>");

    return self::__button($id, $etq, true, $title);
  }

  public static function __bimportar($id, $txt = "Importar", $title = "Importar") {
    $etq = self::__boton_etq($txt, "<span style='font-weight: bold; font-size: 1.1em; text-decoration:overline;'>↑</span>");

    return self::__button($id, $etq, true, $title);
  }

  public static function __bbusca($id, $txt = "Buscar", $title = "Buscar") {
    $etq = self::__boton_etq($txt, "<span style='font-weight: bold; font-size: 1.1em;'>&#128269;</span>");

    return self::__button($id, $etq, true, $title);
  }

  public static function __beditar($id, $txt = "Editar", $title = "Editar") {
    $etq = self::__boton_etq($txt, "<span style='color: #333;font-size: 1.3em;'>&#9881;</span>");

    return self::__button($id, $etq, true, $title);
  }

  public static function __benviar($id, $txt = "Enviar", $title = "Enviar") {
    $etq = self::__boton_etq($txt, "<span style='color: #00ba00;font-size: 1.3em;'>▶</span>");

    return self::__button($id, $etq, true, $title);
  }

  public static function __bcopiar($id, $txt = "Copiar", $title = "Copiar") {
    
    $etq = "<span style='display:flex;gap:0.5em;align-items:center'><img src='imx/accions/enlace.svg' style='width: 15px;' alt='copiar' />{$txt}</span>";

    return self::__button($id, $etq, true, $title);
  }

  public static function __bmais($id, $txt = "Añadir", $title = "Añadir") {
    $etq = self::__boton_etq($txt, "<span style='color: #00ba00;font-size: 1.3em;'>✚</span>");

    return self::__button($id, $etq, true, $title);
  }

  public static function __baceptar($txt = "Aceptar", $title = "Aceptar", $id = "bAceptar") {
    $etq = self::__boton_etq($txt, "<span style='color: #00ba00;font-size: 1.3em;'>&#10004;</span>");

    return self::__button($id, $etq, true, $title);
  }

  public static function __bcancelar($txt = "Cancelar", $title = "Cancelar", $id = "bCancelar") {
    if ($txt != null) $txt = "&nbsp;&nbsp;{$txt}";

    return self::__button($id, "<span style='color: #ba0000;font-size: 1.3em;'>&#10005;</span>{$txt}", true, $title);
  }

  public static function __bsup($id, $txt = "Borrar", $title = "Borrar") {
    $etq = self::__boton_etq($txt, "<span style='color: #ba0000;font-size: 1.3em;'>&#9851;</span>");

    return self::__button($id, $etq, true, $title);
  }

  public static function __link($nome, $etq = null, $css = "texto3_a", $css2 = null) {
    $etq = str_replace(Refs::fs_home(), "", $etq);

    $l = new Link($nome, $etq);

    $l->clase_css("default", $css);

    if ($css2 != null) {
      $l->pon_eventos("onmouseover", "this.className='{$css2}'");
      $l->pon_eventos("onmouseout" , "this.className='{$css}'");
    }

    $l->envia_SUBMIT("onclick");

    return $l;
  }

  public static function __button($nome, $etq, $visible = true, $title = null) {
    $b = new Button($nome, $etq);

    $b->visible($visible);

    $b->title = $title;

    $b->style("default", "white-space: nowrap;");

    return $b;
  }

  public static function __bimage($nome, $src, $title = null, $mini = false, $txt = null) {
    if ($src == null) return self::__button($nome, $txt, true, $title);

    if ($mini > 1) $wh_mini = " style='height:{$mini}px; width:{$mini}px;'";
    elseif ($mini) $wh_mini = " style='height:15px; width:15px;'";
    else           $wh_mini = "";

    $imx = "<img src='" . Refs::url($src) . "'{$wh_mini} />";


    return self::__button($nome, "{$imx}&nbsp;{$txt}", true, $title);
  }

  public static function __bimage_hreflink($nome, $href, $icono, $txt = null, $title = null, $mini = false) {
    $b = self::__bimage("bihl", $icono, $title, $mini, $txt);

    $b->sup_eventos();

    return new Link($nome, $b->html(), "_blank", $href);
  }

  public static function __bapagado($id, $src, $title = null, $mini = true, $txt = null) {
    $b = Panel_fs::__bimage($id, $src, $title, $mini, $txt);

    $b->clase_css("default" , "boton_apagado");
    $b->clase_css("readonly" , "boton_apagado_r");

    $b->pon_eventos("onmouseover", "this.className = 'boton_apagado_over'");
    $b->pon_eventos("onmouseout", "this.className = 'boton_apagado'");

    return $b;
  }

  public static function __espera($visible = false) {
    $i = new Image("espera", Refs::url(Ico::espera), false);


    $display = ($visible)?"inline":"none";

    $i->style("default", "display: {$display}; height: 33px; width: 33px;");


    return $i;
  }

  public static function __boton_etq($txt, $ico = ""):string {
    if ($txt != "") $txt = "<span style='padding-left:0.7em'>{$txt}</span>";

    return "<span>{$ico}{$txt}</span>";
  }
}

