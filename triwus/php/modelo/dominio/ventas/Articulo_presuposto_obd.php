<?php


final class Presuposto_carrito_obd extends Articulo_obd {
  public $a_ops = null;

  public function __construct(Articulo_obd $a = null) {
    parent::__construct($a->atr("id_site")->valor, $a->atr("id_articulo")->valor);

    $this->clonar($a);
  }

  public static function inicia(FS_cbd $cbd, $id_site, $id_articulo = null) {
    $a = Articulo_obd::inicia($cbd, $id_site, $id_articulo);

    return new Presuposto_carrito_obd($a);
  }

  public function metaprsto(FS_cbd $cbd = null) {
    if ($cbd == null) $cbd = new FS_cbd();

    return Articulo_obd::inicia($cbd, $this->atr("id_site")->valor, $this->atr("id_articulo")->valor);
  }

  public function pvp($unidades = 1, $ive = true, $des = false, Promo_obd $p = null, $pvp_plus = 0) {
    //~ if (function_exists("prsto_calcula_precio")) return prsto_calcula_precio($this, $unidades, $ive, $des);

    return parent::pvp($unidades, $ive, $des, $p, $this->pvp_plus());
  }

  public function pvp_plus() {
    //* estes prezos son con ive engadido.
    $a_ops = $this->a_ops;

    if (count($a_ops) == 0) return 0;


    $pvp = 0;
    foreach ($a_ops as $opt) {
      if ($opt['tipo'] == "m") {
        $pvp2 = 0;
        foreach($opt['ops'] as $id_opt=>$opt2) if ($opt2['valor'] == 1) $pvp2 += $opt2["pvp_suma"];

        $pvp += $pvp2;
      }
      elseif(isset($opt["pvp_suma"])) {
        $pvp += $opt["pvp_suma"];
      }
    }


    return $pvp;
  }

  public function a_ops($a_ops = null) {
    if ($a_ops != null) $this->a_ops = $a_ops;

    return $this->a_ops;
  }

  public function a_ops_resumo() {
    if (count($this->a_ops) == 0) return null;

//~ echo "<pre>" . print_r($this->a_ops, true) . "</pre>";

    $r = null;
    foreach($this->a_ops as $a_o) {
      $r[$a_o['id_p']]['tipo'] = $a_o['tipo'];

      if (!isset($a_o['ops'])) {
        $r[$a_o['id_p']][] = $a_o['id_o'];

        continue;
      }

      if (count($a_o['ops']) == 0) continue;

      foreach($a_o['ops'] as $a_o2) $r[$a_o['id_p']][] = $a_o2['valor'];
    }

//~ echo "----------------------------------------------<pre>" . print_r($r, true) . "</pre>";

    return $r;
  }

  public function prsto_html($fontsize = "79%") {
    $css_0 = "style='font-size: {$fontsize}; clear: both;'";
    
    ksort($this->a_ops);

    if (count($this->a_ops) == 0) return "";

    //~ echo "<pre>" . print_r($this->a_ops, true) . "</pre>";

    $s = "";
    foreach($this->a_ops as $k=>$opt) {
      //~ echo "opt::<pre>" . print_r($opt, true) . "</pre>";
      if ($opt['tipo'] == "d") {
        $s2 = "";
        if (($opt['ops']) > 0) {
          foreach($opt['ops'] as $id_opt=>$opt2) {
            if (($valor = $opt2['valor']) == null) $valor = "--";

            $s2 .= "{$opt2['nome_o']} = {$valor}<br />";
          }

          $s2 = "<div style='float: left;'>{$s2}</div>";
          
        }
        
        $s .= "<div {$css_0}><div style='float: left;'>&bull;&nbsp;<b>{$opt["nome_p"]}</b>&nbsp;</div>{$s2}</div>";

        continue;
      }

      if ($opt['tipo'][0] == "t") {
        $s2 = "";
        if (($opt['ops']) > 0) {
          foreach($opt['ops'] as $id_opt=>$opt2) {
            if (($valor = $opt2['valor']) == null) $valor = "--";

            $s2 .= "{$opt2['nome_o']} = {$valor}<br />";
          }

          $s2 = "<div style='float: left;'>{$s2}</div>";
          
        }
        
        $s .= "<div {$css_0}><div style='float: left;'>&bull;&nbsp;<b>{$opt["nome_p"]}</b>&nbsp;</div>{$s2}</div>";

        continue;
      }

      if ($opt['tipo'] == "m") {
        $s2 = "";
        foreach($opt['ops'] as $id_opt=>$opt2) {
          if ($opt2['valor'] == 1) {
            $valor    = "S&iacute;";
            
            $pvp_suma = "";
            $pvp_suma_aux_i = round($opt2["pvp_suma"], true);
            if ($pvp_suma_aux_i > 0) {

              $pvp_suma = "<b>&nbsp;(&nbsp;+{$opt2["pvp_suma"]}&nbsp;&euro;&nbsp;&nbsp;+IVA)</b>";
            }
          }
          else {
            $valor    = "No";

            $pvp_suma = "";
          }

          $s2 .= "{$opt2['nome_o']}: <b>{$valor}</b>&nbsp;&nbsp;{$pvp_suma}<br />";
        }

        $s .= "<div {$css_0}><div style='float: left;'>&bull;&nbsp;<b>{$opt["nome_p"]}</b>&nbsp;</div><div style='float: left;'>{$s2}</div></div>";

        continue;
      }

      $pvp_suma     = "";
      $pvp_suma_aux_i = round($opt["pvp_suma"], true);
      if ($pvp_suma_aux_i > 0) {
        //~ $signo = ($pvp_suma_aux_i > 0)?"+":"";

        //~ $pvp_suma = "<b>&nbsp;(&nbsp;{$signo}{$opt["pvp_suma"]}&nbsp;&euro;&nbsp;+IVA)</b>";

        $pvp_suma = "<b>&nbsp;(&nbsp;+{$opt["pvp_suma"]}&nbsp;&euro;&nbsp;+IVA)</b>";
      }

      $n = $opt["nome_o"];

      if ($opt['grupo_o'] != null) $n = "{$opt['grupo_o']}. {$n}";

      $s .= "<div {$css_0}>&bull;&nbsp;<b>{$opt["nome_p"]}</b>&nbsp;{$n}{$pvp_suma}</div>";
    }

    return $s;
  }

  public static function _tipo() {
/*
    return array("r"  => "Checkers (exclusivo)",
                 "m"  => "Checkers (múltiple)",
                 "s"  => "Selector",
                 "t"  => "Texto",
                 "d"  => "Fecha",
                 "ta" => "Comentario",
                 "b1" => "Botón pequeño",
                 "b2" => "Botón medio",
                 "b3" => "Botón grande",
                 "b4" => "Botón + Notas"
                );
*/
    return array("r"  => "Checkers (exclusivo)",
                 "m"  => "Checkers (múltiple)",
                 "s"  => "Selector",
                 "t"  => "Texto",
                 "d"  => "Fecha",
                 "ta" => "Comentario"
                );
  }
}

//****************************************

final class Presuposto_paso_obd extends Obxeto_bd {
  public function __construct($id_site, $id_articulo, $id_paso = null) {
    parent::__construct();

    $this->atr("id_site"    )->valor = $id_site;
    $this->atr("id_articulo")->valor = $id_articulo;
    $this->atr("id_paso"    )->valor = $id_paso;
  }

  public function mapa_bd() {
    return new Presuposto_paso_mbd();
  }

  public static function inicia(FS_cbd $cbd, $id_site, $id_articulo, $id_paso = null) {
    $o = new Presuposto_paso_obd($id_site, $id_articulo, $id_paso);

    if ($id_paso == null) return $o;

    $wh_s = $o->atr("id_site"    )->sql_where($cbd);
    $wh_a = $o->atr("id_articulo")->sql_where($cbd);
    $wh_p = $o->atr("id_paso"    )->sql_where($cbd);


    $o->select($cbd, "{$wh_s} and {$wh_a} and {$wh_p}");

    return $o;
  }

  public function baixar(FS_cbd $cbd) {
    $id_site     = $this->atr("id_site")->valor;
    $id_articulo = $this->atr("id_articulo")->valor;
    $id_paso     = $this->atr("id_paso")->valor;
    $posicion    = $this->atr("posicion")->valor;


    $sql = "select id_paso, posicion from prsto_paso where id_site = {$id_site} and id_articulo = {$id_articulo} and posicion > {$posicion} order by posicion asc limit 0, 1";

    $r = $cbd->consulta($sql);

    if (!$a = $r->next()) return false;

    $sql = "update prsto_paso set posicion = {$a['posicion']} where id_paso = {$id_paso}";

    if (!$cbd->executa($sql)) return false;

    $sql = "update prsto_paso set posicion = {$posicion} where id_paso = {$a['id_paso']}";


    return $cbd->executa($sql);
  }

  public function subir(FS_cbd $cbd) {
    $id_site     = $this->atr("id_site")->valor;
    $id_articulo = $this->atr("id_articulo")->valor;
    $id_paso     = $this->atr("id_paso")->valor;
    $posicion    = $this->atr("posicion")->valor;


    $sql = "select id_paso, posicion from prsto_paso where id_site = {$id_site} and id_articulo = {$id_articulo} and posicion < {$posicion} order by posicion desc limit 0, 1";

    $r = $cbd->consulta($sql);

    if (!$a = $r->next()) return false;

    $sql = "update prsto_paso set posicion = {$a['posicion']} where id_paso = {$id_paso}";

    if (!$cbd->executa($sql)) return false;

    $sql = "update prsto_paso set posicion = {$posicion} where id_paso = {$a['id_paso']}";


    return $cbd->executa($sql);
  }

  public function select(FS_cbd $cbd, $where = null, $orderby = null) {
    $r = $cbd->consulta($this->sql_select($cbd, $where, $orderby));

    if (!$a = $r->next()) return;

    $this->post($a);
  }

  public function a_opcions(FS_cbd $cbd) {
    if ($this->atr("id_paso")->valor == null) return [];

    $s = $this->atr("id_site"    )->valor;
    $a = $this->atr("id_articulo")->valor;
    $p = $this->atr("id_paso"    )->valor;

    $sql = "select id_opcion from prsto_paso_opcion where id_site = {$s} and id_articulo = {$a} and id_paso = {$p}";

    $r = $cbd->consulta($sql);

    $a_o = [];
    while($a2 = $r->next()) $a_o[] = Presuposto_paso_opcion_obd::inicia($cbd, $s, $a, $p, $a2['id_opcion']);

    return $a_o;
  }

  public function update(FS_cbd $cbd) {
    if ($this->atr("id_paso")->valor == null) return $this->insert($cbd);

    return $cbd->executa($this->sql_update($cbd));
  }

  public function duplicar(FS_cbd $cbd, $id_articulo_novo) {
    $a_ppo = $this->a_opcions($cbd);

    $this->atr("id_articulo")->valor = $id_articulo_novo;
    $this->atr("id_paso"    )->valor = null;

    if (!$this->insert($cbd)) return false;

    if ($a_ppo == null) return true;

    foreach ($a_ppo as $ppo) {
      $ppo->atr("id_articulo")->valor = $this->atr("id_articulo")->valor;
      $ppo->atr("id_paso"    )->valor = $this->atr("id_paso"    )->valor;

      if (!$ppo->duplicar($cbd)) return false;
    }

    return true;
  }

  public function insert(FS_cbd $cbd) {
    if (!$this->calcula_id($cbd)) return false;

    if ($this->atr("posicion")->valor == null) $this->atr("posicion")->valor = $this->atr("id_paso")->valor;

    return $cbd->executa($this->sql_insert($cbd));
  }

  public function delete(FS_cbd $cbd) {
    $a_o = $this->a_opcions($cbd);

    for($i = 0; $i < count($a_o); $i++) if (!$a_o[$i]->delete($cbd)) return false;

    return $cbd->executa($this->sql_delete($cbd));
  }

  private function calcula_id(FS_cbd $cbd) {
    $id_site     = $this->atr("id_site")->valor;
    $id_articulo = $this->atr("id_articulo")->valor;

    if (!$cbd->executa("insert into prsto_paso_seq (id_site, id_articulo) values ({$id_site}, {$id_articulo})")) return false;

    $r = $cbd->consulta("select LAST_INSERT_ID() as id");

    if (!$a = $r->next()) return false;

    $cbd->executa("delete from prsto_paso_seq where id_site = {$id_site} and id_articulo = {$id_articulo} and id_paso < {$a['id']}");

    $this->atr("id_paso")->valor = $a['id'];

    return true;
  }
}

//-----------------------------------------------

final class Presuposto_paso_mbd extends Mapa_bd {
  public function __construct() {
    $t = new Taboa_dbd("prsto_paso");

    $t->pon_campo("id_site"    , new Numero(), true);
    $t->pon_campo("id_articulo", new Numero(), true);
    $t->pon_campo("id_paso"    , new Numero(), true);
    $t->pon_campo("tipo");
    $t->pon_campo("nome");
    $t->pon_campo("notas");
    $t->pon_campo("cantidade"  , new Numero());
    $t->pon_campo("posicion"   , new Numero());

    $this->pon_taboa($t);
  }
}

//****************************************

final class Presuposto_paso_opcion_obd extends    Obxeto_bd
                                       implements ICLogo    {

  public function __construct($id_site, $id_articulo, $id_paso, $id_opcion = null) {
    parent::__construct();

    $this->atr("id_site"    )->valor = $id_site;
    $this->atr("id_articulo")->valor = $id_articulo;
    $this->atr("id_paso"    )->valor = $id_paso;
    $this->atr("id_opcion"  )->valor = $id_opcion;
  }

  public function mapa_bd() {
    return new Presuposto_paso_opcion_mbd();
  }

  public function iclogo_obd(FS_cbd $cbd, ?string $k = null) {
    if ($cbd == null) $cbd = new FS_cbd();

    $id_logo = $this->atr("id_logo")->valor;

    if ($id_logo != null) return Elmt_obd::inicia($cbd, $id_logo);


    return new Imaxe_obd();
  }

  public function iclogo_id(?string $k = null) {
    if ($id != -1) $this->atr("id_logo")->valor = $id;

    return $this->atr("id_logo")->valor;
  }

  public function iclogo_src(FS_cbd $cbd, ?string $k = null) {
        //~ if (is_numeric($mini));
    //~ elseif ($mini            ) $mini = Articulo_obd::logo_width;
    
    $mini = Articulo_obd::logo_width;

    $id_logo  = $this->iclogo_id ( $k       );
    $logo_obd = $this->iclogo_obd( $cbd, $k );

    $a_src[$id_logo] = $logo_obd->url($mini);

    //~ echo "<pre>" . print_r($a_src, true) . "</pre>";

    return $a_src;
  }

  public function iclogo_update(FS_cbd $cbd, $id_file, $id_logo = null) {
    if ($id_logo != null) $this->iclogo_id($id_logo);

    $logo_obd = $this->iclogo_obd($cbd);

    $a_url = Imaxe_obd::url_split($id_file);

    $logo_obd->atr("url"     )->valor = $a_url["url"];
    $logo_obd->atr("ref_site")->valor = $a_url["ref_site"];

    if (!$this->update($cbd)) return false;

    if ($id_logo != null) return $logo_obd->update($cbd);


    return $logo_obd->insert($cbd);
  }

  public function iclogo_duplicar(FS_cbd $cbd, Imaxe_obd $logo_0) {
    if ($logo_0->atr("url")->valor == null) return true;

    $logo_0->atr("id_elmt")->valor = null;

    if (!$logo_0->insert($cbd)) return false;

    $this->atr("id_logo")->valor = $logo_0->atr("id_elmt")->valor;

    return $this->update($cbd);
  }

  public function iclogo_delete(FS_cbd $cbd) {
    return $this->iclogo_obd($cbd)->delete($cbd);
  }

  public static function inicia(FS_cbd $cbd, $id_site, $id_articulo, $id_paso, $id_opcion = null) {
    $o = new Presuposto_paso_opcion_obd($id_site, $id_articulo, $id_paso, $id_opcion);

    if ($id_opcion == null) return $o;

    $wh_s = $o->atr("id_site"    )->sql_where($cbd);
    $wh_a = $o->atr("id_articulo")->sql_where($cbd);
    $wh_p = $o->atr("id_paso"    )->sql_where($cbd);
    $wh_o = $o->atr("id_opcion"  )->sql_where($cbd);


    $o->select($cbd, "{$wh_s} and {$wh_a} and {$wh_p} and {$wh_o}");

    return $o;
  }

  public function select(FS_cbd $cbd, $where = null, $orderby = null) {
    $r = $cbd->consulta($this->sql_select($cbd, $where, $orderby));

    if (!$a = $r->next()) return;

    $this->post($a);
  }

  public function paso_obd(FS_cbd $cbd = null) {
    if ($this->atr("id_paso")->valor == null) return null;

    if ($cbd == null) $cbd = new FS_cbd();

    return Presuposto_paso_obd::inicia($cbd, $this->atr("id_site")->valor, $this->atr("id_articulo")->valor, $this->atr("id_paso")->valor);
  }

  public function update(FS_cbd $cbd) {
    if ($this->atr("pvp_suma")->valor == null) $this->atr("pvp_suma")->valor = "0";

    if ($this->atr("id_opcion")->valor == null) return $this->insert($cbd);

    return $cbd->executa($this->sql_update($cbd));
  }

  public function insert(FS_cbd $cbd) {
    if (!$this->calcula_id($cbd)) return false;

    return $cbd->executa($this->sql_insert($cbd));
  }

  public function duplicar(FS_cbd $cbd) {
    $this->atr("id_opcion"  )->valor = null;

    if (!$this->insert($cbd)) return false;

    return $this->iclogo_duplicar( $cbd, $this->iclogo_obd($cbd) );
  }

  public function delete(FS_cbd $cbd) {
    if (!$this->iclogo_delete($cbd)) return false;

    return $cbd->executa($this->sql_delete($cbd));
  }

  private function calcula_id(FS_cbd $cbd) {
    $id_site     = $this->atr("id_site")->valor;
    $id_articulo = $this->atr("id_articulo")->valor;
    $id_paso     = $this->atr("id_paso")->valor;

    if (!$cbd->executa("insert into prsto_paso_opcion_seq (id_site, id_articulo, id_paso) values ({$id_site}, {$id_articulo}, {$id_paso})")) return false;

    $r = $cbd->consulta("select LAST_INSERT_ID() as id");

    if (!$a = $r->next()) return false;

    $cbd->executa("delete from prsto_paso_opcion_seq where id_site = {$id_site} and id_articulo = {$id_articulo} and id_paso = {$id_paso} and id_opcion < {$a['id']}");

    $this->atr("id_opcion")->valor = $a['id'];

    return true;
  }
}

//-----------------------------------------------

final class Presuposto_paso_opcion_mbd extends Mapa_bd {
  public function __construct() {
    $t = new Taboa_dbd("prsto_paso_opcion");

    $t->pon_campo("id_site"    , new Numero(), true);
    $t->pon_campo("id_articulo", new Numero(), true);
    $t->pon_campo("id_paso"    , new Numero(), true);
    $t->pon_campo("id_opcion"  , new Numero(), true);
    $t->pon_campo("nome");
    $t->pon_campo("grupo");
    $t->pon_campo("notas");
    $t->pon_campo("id_logo"    , new Numero());
    $t->pon_campo("obligado"   , new Numero());
    $t->pon_campo("pvp_suma"   , new Numero());
    $t->pon_campo("paso_seg"   , new Numero());


    $this->pon_taboa($t);
  }
}

