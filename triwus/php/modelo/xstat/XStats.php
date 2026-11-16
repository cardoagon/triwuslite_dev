<?php

final class XStats {

  public static function paxina_obd($id_paxina, FS_cbd $cbd = null) {
    return array("c"=>null,
                 "n"=>null,
                 "p"=>null,
                 "v"=>XStats_visita::sum($id_paxina, $cbd));
  }

}

//---------------------------------------------------

final class XStats_nube {

  private $a_nube  = null;
  private $a_nubeb = null;

  private function __construct($a_nube = null) {
    if ($a_nube == null) return;

    $this->a_nube  = array_slice($a_nube, 0, 12);
    $this->a_nubeb = $a_nube;
  }

  public static function calcular_site($id_site) {
    $cbd = new FS_cbd();

    $r = $cbd->consulta("select * from v_site_paxina where id_site = {$id_site}");

    $xst_s = new XStats_nube();

    while ($a = $r->next()) {
      if ($a['estado'] == "borrador") continue;

      $p = Paxina_obd::inicia($cbd, $a['id_paxina']);

      $xst_p = self::inicia(Detalle::factory($p));

      if ($xst_p->a_nube == null) continue;

      for ($i = 0; ( ($i < 3) && (($a2 = each($xst_p->a_nube)) != null) ); $i++)
        $xst_s->a_nube[$a2['key']] = $a2['value'];
    }

    return self::a2txt($xst_s->a_nube, ", ", 12, 4);
  }

  public static function calcular_paxina($id_paxina, $contas = false) {
    $cbd = new FS_cbd();

    $p = Paxina_obd::inicia($cbd, $id_paxina);

    $xst = self::inicia(Detalle::factory($p));

    return self::a2txt($xst->a_nubeb, ", ", 12, 4, $contas);
  }

  public static function anotar(FS_cbd $cbd, Detalle $d, $id_paxina) {
    $n = new Nube_obd($id_paxina);

    $n->select($cbd, $n->atr("id_paxina")->sql_where($cbd));


    $xst = self::inicia($d);

    if ($n->atr("nube")->valor != null) $n->atr("nube")->valor  = self::a2txt($xst->a_nube, ", ", 12, 4);
    $n->atr("nubeb")->valor = self::a2txt($xst->a_nubeb, " ");

    return $n->anotar($cbd);
  }

  private static function a2txt($a, $sep, $len = 0, $fplen = 3, $contas = false) {
    if ($a == null) return "";
    if (count($a) == 0) return "";

    $t     = null;
    $ctlen = 1;

    foreach($a as $p=>$ct) {
      if (($len > 0) && ($ctlen > $len)) return $t;

      if (strlen($p) < $fplen) continue;

      $ctlen++;

      if ($t == null)
        $t = "{$p}";
      else
        $t .= "{$sep}{$p}";

      if ($contas) $t .= "({$ct})";
    }

    return $t;
  }

  private static function txt2a($txt) {
    if ($txt == null) return null;
  
    if (trim($txt) == "") return null;

    $txt = strtolower( htmlspecialchars_decode(strip_tags($txt)) );
    $txt = str_replace(array("á","é","í","ó","ú","ñ"), array("a","e","i","o","u","n"), $txt);

    $a_0 = str_word_count($txt, 1);

    if ($a_0 == null) return null;

    $a_1 = null;
    foreach ($a_0 as $palabra) {
      if (!self::__valida_palabra($palabra)) continue;

      $a_1[$palabra] = substr_count($txt, $palabra);
    }

    if ($a_1 == null) return null;

    arsort($a_1);
   

    return $a_1;
  }

  private static function inicia(Detalle $d) {
    if (($a_prgf = $d->a_prgf()) == null) return null;

    $txt = null;
    foreach ($a_prgf as $prgf) {
      if (($a_elmt = $prgf->obxetos("elmt")) == null) continue;

      foreach ($a_elmt as $elmt) {
        if (($elmt_obd = $elmt->elmt_obd()) == null) continue;

        switch ($elmt_obd->atr("tipo")->valor) {
          case Texto_obd::tipo_bd:
          case Indice_obd::tipo_bd:
            $txt .= "," . $elmt_obd->atr("txt")->valor;

            break;
          case Imaxe_obd::tipo_bd:
          //~ case Elmt_album_obd::tipo_bd:
          case Elmt_novas_obd::tipo_bd:
          case Elmt_ventas_obd::tipo_bd:
            $txt .= "," . $elmt_obd->atr("nome")->valor . "," . $elmt_obd->atr("notas")->valor;
        }
      }
    }

    return new XStats_nube(self::txt2a($txt));
  }

  private static function __valida_palabra($palabra) {
    if (strlen($palabra) < 3) return false;

    switch ($palabra) {
      case "como":  return false;
      case "unha":  return false;
      case "aacute":  return false;
      case "eacute":  return false;
      case "iacute":  return false;
      case "oacute":  return false;
      case "uacute":  return false;
      case "esta":  return false;
      case "estas": return false;
      case "este":  return false;
      case "estes": return false;
      case "estos": return false;
      case "las":   return false;
      case "los":   return false;
      case "nbsp":  return false;
      case "ntilde":  return false;
      case "para":  return false;
      case "segun": return false;
      case "según": return false;
      case "sobre": return false;
      case "tras":  return false;
    }

    if (count(count_chars($palabra, 1)) < 3) return false;


    return true;
  }
}

//---------------------------------------------------

final class XStats_visita {
  private $d     = null;

  private $disp  = null;
  private $nav   = null;

  private $pet_Y = null;
  private $pet_n = null;
  private $pet_z = null;

  private function __construct() {}

  public static function sum($id_paxina, FS_cbd $cbd = null) {
    if ($cbd == null) $cbd = new FS_cbd();

    $r = $cbd->consulta("select ifnull(sum(ct), 0) as sum from stats_visita where id_paxina={$id_paxina} and n=0 and z=0");

    if (!$a = $r->next()) return "0";

    return $a['sum'];
  }

  public static function anotar($id_paxina) {
    $xst = self::inicia($id_paxina);

    $cbd = new FS_cbd();

    $cbd->transaccion();

    if (!Visita_obd::__borra_z($cbd)) {
      $cbd->rollback();

      return;
    }

    if (!$xst->disp->anotar($cbd)) {
      $cbd->rollback();

      return;
    }

    //~ if (!$xst->nav->anotar($cbd)) {
      //~ $cbd->rollback();

      //~ return;
    //~ }

    if (!$xst->pet_Y->anotar($cbd)) {
      $cbd->rollback();

      return;
    }
    if (!$xst->pet_n->anotar($cbd)) {
      $cbd->rollback();

      return;
    }
    if (!$xst->pet_z->anotar($cbd)) {
      $cbd->rollback();

      return;
    }

    $cbd->commit();
  }

  private static function inicia($id_paxina) {
    $Y = date("Y");

    $xst = new XStats_visita();

    $xst->pet_Y = Visita_obd::inicia_Y($id_paxina, $Y);
    $xst->pet_n = Visita_obd::inicia_n($id_paxina, $Y, date("n"));
    $xst->pet_z = Visita_obd::inicia_z($id_paxina, $Y, date("z") + 1);

    $id_site = self::__idsite($id_paxina);

    $xst->disp = Dispositivo_obd::inicia($id_site);
    $xst->nav  = Navegador_obd  ::inicia($id_site);

    return $xst;
  }

  private static function __idsite($id_paxina) {
    return Paxina_obd::inicia(new FS_cbd(), $id_paxina)->atr("id_site")->valor;
  }
}


