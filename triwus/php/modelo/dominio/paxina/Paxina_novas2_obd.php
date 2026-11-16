<?php


final class Paxina_novas2_obd extends Paxina_obd
                           implements IPax_indice, ICatalogo_fonte {
 
  public string $icatF__orderby;
  public string $icatF__permisos_u;
  
  public static array $_enlaceTipo = [1 => "Externo",
                                      2 => "Interno",
                                      3 => "Sin enlace"
                                     ];

  public function __construct($id_paxina = null) {
    parent::__construct("novas2", $id_paxina);

    $this->atr("enlace_tipo"  )->valor = 2;
    $this->atr("enlace_target")->valor = "self";
  }

  public function mapa_bd() {
    return new Paxina_novas2_mbd();
  }

  public function __indice() { //* IMPLEMENTS IPax_indice->__indice()
    return $this->atr("id_prgf_indice")->valor > 0;
  }


  public function a_grupo() {
    if ($this->atr("id_prgf_indice")->valor > 0) return parent::a_grupo();
      
    
    return Opcion_ms_obd::explode_grupo($this->atr("grupo_aux")->valor);
  }


  public function update(FS_cbd $cbd, $dactu = true) {
    if ( !parent::update($cbd, $dactu) ) return false;

    return $cbd->executa( $this->sql_update($cbd, null, "paxina_novas") );
  }

  public static function update_mover(FS_cbd $cbd, $id_oms, $id_idioma, $id_pax_0, $id_pax_1, $pos_0 = -1, $pos_1 = -1) {
    if ($id_pax_0 == $id_pax_1) return true;
    
    if ($pos_0 == -1) {
      $p = Paxina_obd::inicia($cbd, $id_pax_0, "novas2");
      
      $pos_0 = $p->atr("pos")->valor;
    }
    
    if ($pos_1 == -1) {
      $p = Paxina_obd::inicia($cbd, $id_pax_1, "novas2");
      
      $pos_1 = $p->atr("pos")->valor;
    }
    
    if ($pos_0 < $pos_1) {
      $suma = "b.pos = b.pos - 1";
      $wh_p = "(b.pos <= {$pos_1}) && (b.pos > {$pos_0})";
    }
    else {
      $suma = "b.pos = b.pos + 1";
      $wh_p = "(b.pos >= {$pos_1}) && (b.pos < {$pos_0})";
    }


    $sql = "update v_site_paxina a inner join paxina_novas b on (a.id_paxina = b.id_paxina)
               set {$suma}
             where a.id_oms = {$id_oms} and a.id_idioma = '{$id_idioma}' and id_prgf_indice = 0 and {$wh_p}";

    if (!$cbd->executa($sql)) return false;

    $sql = "update paxina_novas b set b.pos = {$pos_1} where b.id_paxina = {$id_pax_0}";

    return $cbd->executa($sql);
  }


  public function insert(FS_cbd $cbd, $id_idioma) {
    $iprgf = new Prgf_inovas2_obd();

    $iprgf->atr("nome")->valor = "Noticiero";

    if (!$this->insert_base($cbd, $id_idioma, $iprgf)) return false;
    
    $this->atr("id_prgf_indice")->valor = $iprgf->atr("id_paragrafo")->valor; //* forzamos páxina índice

    if (!$cbd->executa($this->sql_update($cbd))) return false;

    if (!$cbd->executa($this->sql_insert($cbd, "paxina_novas"))) return false;


    return true;
  }

  public function insert_paxina_detalle(FS_cbd $cbd, $id_idioma, $url_imx) {
    //* inserta base
    $this->atr("id_prgf_indice")->valor = "0"; //* forzamos páxina detalle

    if (!$this->insert_base($cbd, $id_idioma)) return false;

    //* inserta paxina_novas2.
    $this->atr("pos")->valor = self::calcula_maxpos($this->atr("id_opcion")->valor, $id_idioma, $cbd);
    
    $this->atr("grupo_aux"    )->valor = "pub";
    $this->atr("enlace_target")->valor = "_self";

    if (!$cbd->executa($this->sql_insert($cbd, "paxina_novas"))) return false;

    //* actualiza logo
    $this->atr("logo")->valor = null; //* duplica logo
    
    if (!$this->iclogo_update( $cbd, $url_imx )) return false;

    //* inserta os contidos
    $prgf1 = new Paragrafo_obd();
    $prgf2 = new Paragrafo_obd();
    $prgf3 = new Paragrafo_obd();

    if (!$this->insert_detalle($cbd, $prgf1, 1)) return false;
    if (!$this->insert_detalle($cbd, $prgf2, 2)) return false;
    if (!$this->insert_detalle($cbd, $prgf3, 3)) return false;
    
    if (!self::insert_migaspan($cbd, $prgf1       )) return false;
    if (!self::insert_nova    ($cbd, $prgf2, $this)) return false;
    if (!self::insert_rs      ($cbd, $prgf3       )) return false;


    return true;
  }


  public function duplicar(FS_cbd $cbd, $id_idioma, ?string $refsite = null) {
    //* duplicado dunha nova.
    if ($this->atr("id_prgf_indice")->valor == 0) return $this->duplicar_paxina_detalle($cbd, $id_idioma, $refsite);

    
    //* Lemos o posición do prgf_indice.
    $iprgf_0 = $this->prgf_indice($cbd);

    //* duplica páxina
    $id_pax_dupli  = $this->atr("id_paxina")->valor;

    $this->atr("id_paxina"     )->valor = null;
    $this->atr("id_comentarios")->valor = null;

        if ($this->atr("id_idioma")->valor == $id_idioma) $this->atr("id_sincro")->valor = null;
    elseif ($this->atr("id_sincro")->valor == null      ) $this->atr("id_sincro")->valor = $id_dupli;

    if (!$this->insert_base($cbd, $id_idioma)) return false;

    if (!$cbd->executa($this->sql_insert($cbd, "paxina_novas"))) return false;


    //* duplica detalle
    $d = new Detalle_obd($this->atr("id_paxina")->valor);


    if (!$d->duplicar($cbd, $id_pax_dupli, $refsite)) return false;


    //* actualizamos prgf_indice. Buscamos o novo prgf_indice, pola posición do prgf_indice orixinal.
    $_prgf = $d->a_prgf($cbd);

    if (!is_array($_prgf)) return false;

    foreach($_prgf as $pos=>$prgf) {
      if ($pos != $iprgf_0->atr("posicion")->valor) continue;

      $this->atr("id_prgf_indice")->valor = $prgf->atr("id_paragrafo")->valor;

      if ( !$cbd->executa($this->sql_update($cbd)) ) return false; //* update so da taboa paxina.

      break;
    }

    //* duplicamos as paxinas de novas relacionadas.
    $sql = "select id_paxina
              from paxina
             where id_prgf_indice = 0 and id_opcion in (select id_opcion from paxina where id_paxina = {$id_pax_dupli})";
    
    $r = $cbd->consulta($sql);

    while ($_r = $r->next()) {
      $pax_aux = Paxina_obd::inicia($cbd, $_r["id_paxina"], "novas2");

      $pax_aux->atr("id_opcion")->valor = $this->atr("id_opcion")->valor;

      if ( !$pax_aux->duplicar_paxina_detalle($cbd, $id_idioma, $refsite) ) return false;
    }

    return true;
  }

  public function duplicar_paxina_detalle(FS_cbd $cbd, $id_idioma, ?string $refsite = null) {
    //* calcula logo
    $url = null;
    if ( ($l = $this->iclogo_obd($cbd)) != null ) $url = $l->url(false);

    if ($url == null) $url = Imaxe_obd::noimx;

    
    //* duplica páxina
    $id_dupli = $this->atr("id_paxina")->valor;

    $this->atr("id_paxina"     )->valor = null;
    $this->atr("logo"          )->valor = null;
    $this->atr("id_prgf_indice")->valor = "0";
    $this->atr("id_comentarios")->valor = null;

        if ($this->atr("id_idioma")->valor == $id_idioma) $this->atr("id_sincro")->valor = null;
    elseif ($this->atr("id_sincro")->valor == null      ) $this->atr("id_sincro")->valor = $id_dupli;

    if ($this->atr("id_sincro")->valor == null) {
      $this->atr("nome"  )->valor .= " (dupli)";
      $this->atr("titulo")->valor .= " (dupli)";
    }
    

    if (!$this->insert_base($cbd, $id_idioma)) return false;

    if (!$this->iclogo_update($cbd, $url)) return false;

    if (!$cbd->executa($this->sql_insert($cbd, "paxina_novas"))) return null;

    
    //* update posiciones
    $id_oms = $this->atr("id_opcion")->valor;
    $pos    = $this->atr("pos"      )->valor;
    
    $sql = "update v_site_paxina a inner join paxina_novas b on (a.id_paxina = b.id_paxina)
               set pos = pos + 1
             where a.id_oms = {$id_oms} and a.id_idioma = '{$id_idioma}' and id_prgf_indice = 0 
               and b.pos >= {$pos} and b.id_paxina <> {$id_dupli}";

    if (!$cbd->executa($sql)) return false;


    //* duplica detalle
    $d = new Detalle_obd($this->atr("id_paxina")->valor);

    return $d->duplicar($cbd, $id_dupli, $refsite);
  }


  public function delete(FS_cbd $cbd) {
    if ( !parent::delete($cbd)                                         ) return false;
    if ( !$cbd->executa($this->sql_delete($cbd, null, "paxina_novas")) ) return false;
    
    //* update posiciones.
    if ( ($pos = $this->atr("pos")->valor) == null ) return true;
    
    $id_oms    = $this->atr("id_opcion")->valor;
    $id_idioma = $this->atr("id_idioma")->valor;
    $pos       = $this->atr("pos"      )->valor;
    
    $sql = "update v_site_paxina a inner join paxina_novas b on (a.id_paxina = b.id_paxina)
               set pos = pos - 1
             where a.id_oms = {$id_oms} and a.id_idioma = '{$id_idioma}' and id_prgf_indice = 0 
               and b.pos > {$pos}";

    return $cbd->executa($sql);
  }

  public function prgf_indice(FS_cbd $cbd = null) {
    if ($cbd == null) $cbd = new FS_cbd();

    return Prgf_inovas2_obd::inicia($cbd, $this->atr("id_prgf_indice")->valor);
  }

  public function select_pai(FS_cbd $cbd = null) {
    if ($cbd == null) $cbd = new FS_cbd();

    $id_prgf_indice = $this->atr("id_prgf_indice")->valor;

    if ($id_prgf_indice > 0) return parent::select_pai($cbd);

    $id_opcion = $this->atr("id_opcion")->valor;

    $sql_inner_2 = "";
    if (($id_idioma = $this->atr("id_idioma")->valor) != null) $sql_inner_2 = "inner join v_ml_paxina c on (a.id_paxina = c.id_paxina and c.id_idioma = '{$id_idioma}')";

    $sql = "select a.id_paxina as id_paxina
              from paxina a inner join paxina_novas b on (a.id_paxina = b.id_paxina)
                            {$sql_inner_2}
                      where id_opcion = {$id_opcion} and a.id_prgf_indice > 0";

    $r = $cbd->consulta($sql);

    if (!$a = $r->next()) return null;


    return Paxina_obd::inicia($cbd, $a['id_paxina'], "novas2");
  }

  public function action_2($limpa = false, $completa = false) {
  //* implementa IMenuSite_paxina->action_2($limpa = false, $completa = false)
  
    if ( !$limpa            ) return parent::action_2($limpa, $completa);    
    if ( !$this->__indice() ) return parent::action_2($limpa, $completa);
    
    
    $a = $this->atr("action"   )->valor;
    $p = $this->atr("id_paxina")->valor;
    $i = $this->atr("id_idioma")->valor;

    if (!$completa) return "{$a}?k={$p}&f={$i}&f1=&f2=&f3=";
    
    return $this->action_site() . "{$a}?k={$p}&f={$i}&f1=&f2=&f3=";
  }

  public static function calcula_maxpos($id_oms, $id_idioma, FS_cbd $cbd = null) {
    if ($cbd == null) $cbd = new FS_cbd();

    $sql = "select max(b.pos) as maxpos
              from v_site_paxina a inner join paxina_novas b on (a.id_paxina = b.id_paxina)
             where a.id_oms = {$id_oms}  and a.id_idioma = '{$id_idioma}' and id_prgf_indice = 0";

    $r = $cbd->consulta($sql);

    if (!$_r = $r->next()) return  "1";


    return $_r["maxpos"] + 1;
  }


  public function icatF__site():int {  //* IMPLEMENTA ICatalogo_fonte.icatF__site()
    return $this->atr("id_site")->valor;
  }

  public function icatF__catRaiz():int {  //* IMPLEMENTA ICatalogo_fonte.icatF__catRaiz()
    return -1;
  }
  
  public function icatF__titulo():string {  //* IMPLEMENTA ICatalogo_fonte.icatF__titulo()
    return "";
  }
  
  public function icatF__selectfrom(bool $editor, bool $vpa):string {  //* IMPLEMENTA ICatalogo_fonte.icatF__selectfrom()   
    $w_l   = "c.id_idioma = '{$this->atr("id_idioma")->valor}'";
    $w_o   = "a.id_opcion = {$this->atr("id_opcion")->valor}";
    $w_pub = $this->icatF__where_pub($editor);
    $w_per = $this->icatF__where_per($editor);

    $w = "({$w_o} and {$w_l} and a.id_prgf_indice = 0 and {$w_pub} and {$w_per})";

    return "
select distinct a.id_paxina, a.nome, a.titulo, a.descricion, a.etq_1, a.etq_2, a.etq_3
 from ( 
   select b.id_paxina, b.pos, b.grupo_aux, a.nome, a.titulo, a.descricion, b.etq_1, b.etq_2, b.etq_3
     from paxina a inner join paxina_novas b on (a.id_paxina = b.id_paxina) 
                   inner join v_ml_paxina c on (a.id_paxina = c.id_paxina) 
    where {$w} 
    ) a";
  }
  
  public function icatF__orderby(int $orderby = -1):string {  //* IMPLEMENTA ICatalogo_fonte.icatF__orderby()
    return $this->icatF__orderby;
  }
  
  public function icatF__etq_tit(int $i, FS_cbd $cbd = null):?string {  //* IMPLEMENTA ICatalogo_fonte.icatF__etq_tit()
    if ($i < 0) return null;
    if ($i > 3) return null;
    
    if ($cbd == null) $cbd = new FS_cbd();
    
    $r = $cbd->consulta("select inovas_etqs from paragrafo where id_paragrafo = {$this->atr("id_prgf_indice")->valor}");

    if (!$_r = $r->next()         ) return null;
    if ($_r["inovas_etqs"] == null) return null;

//~ echo "<pre>" . print_r($_r, 1) . "</pre>";
    
    $_etqs = unserialize( $_r["inovas_etqs"]);

//~ echo "<pre>" . print_r($_etqs, 1) . "</pre>";

    if (!is_array($_etqs)  ) return null;
    
    if (!isset($_etqs["b"][$i])) return null;
    
    if ($_etqs["b"][$i] != 1) return  null;
    
    if (!isset($_etqs["t"][$i])) return null;

    
    return $_etqs["t"][$i];
  }
  
  public function icatF__etq_sql(int $i, bool $vpa, FS_cbd $cbd = null):?string {  //* IMPLEMENTA ICatalogo_fonte.icatF__etq_sql()
    $w_l   = "c.id_idioma = '{$this->atr("id_idioma")->valor}'";
    $w_o   = "a.id_opcion = {$this->atr("id_opcion")->valor}";
    $w_pub = $this->icatF__where_pub(false);

    return "
select distinct b.etq_{$i} as etq
  from paxina a inner join paxina_novas b on (a.id_paxina = b.id_paxina)
               inner join v_ml_paxina  c on (a.id_paxina = c.id_paxina)
 where ({$w_o} and {$w_l} and a.id_prgf_indice = 0 and {$w_pub} and (not b.etq_{$i} is null))";
  }


  private function icatF__where_per(bool $editor):string {
    if ($editor                            ) return "(1)";
    if ($this->icatF__permisos_u == "admin") return "(1)";
    
    if ($this->icatF__permisos_u == ""     ) return "(1=0)";

    $_p = FGS_usuario::explode_permisos( $this->icatF__permisos_u );
    
//~ echo "11111111111111111111<pre>" . print_r($_p, 1) . "</pre>";
    
    $w = null;
    foreach ($_p as $p) {
      if ($p == null ) continue;
      
      if ($w != null) $w .= " or ";

      $w .= "b.grupo_aux = '{$p}'";
    }
    
    if ($w == null) return "(1=0)";

    return "($w)";
  }

  private function icatF__where_pub(bool $editor):string {
    if ($editor) return "(1)";

    return "( (a.estado = 'publicada') and ((a.publicar is null) or (now() > a.publicar)) )";
  }


  private static function insert_nova(FS_cbd $cbd, Paragrafo_obd $prgf, Paxina_novas2_obd $pax) {
    $pe_obd = new Paragrafo_elmt_obd();

    $pe_obd->atr("posicion"    )->valor = 1;
    $pe_obd->atr("id_paragrafo")->valor = $prgf->atr("id_paragrafo")->valor;

    $txt = "<h2>" . $pax->atr("titulo")->valor . "</h2>";

    if ( !$pe_obd->insert($cbd, new Texto_obd($txt, 1)) ) return false;


    $pe_obd->atr("posicion"    )->valor = 2;
    $pe_obd->atr("id_paragrafo")->valor = $prgf->atr("id_paragrafo")->valor;

    $l = $pax->iclogo_obd($cbd);
    
    $l->atr("id_elmt")->valor = null;

    if ( !$pe_obd->insert($cbd, $l) ) return false;


    $pe_obd->atr("posicion"    )->valor = 3;
    $pe_obd->atr("id_paragrafo")->valor = $prgf->atr("id_paragrafo")->valor;

    $txt = $pax->atr("descricion")->valor;

    return $pe_obd->insert($cbd, new Texto_obd($txt, 1));
  }

  private static function insert_migaspan(FS_cbd $cbd, Paragrafo_obd $p) {
    $pe_obd = new Paragrafo_elmt_obd();

    $pe_obd->atr("posicion"    )->valor = 1;
    $pe_obd->atr("id_paragrafo")->valor = $p->atr("id_paragrafo")->valor;


    return $pe_obd->insert($cbd, Paxinador_obd::crea_migaspan());
  }

  private static function insert_rs(FS_cbd $cbd, Paragrafo_obd $p) {
    $pe_obd = new Paragrafo_elmt_obd();

    $pe_obd->atr("posicion"    )->valor = 1;
    $pe_obd->atr("id_paragrafo")->valor = $p->atr("id_paragrafo")->valor;


    return $pe_obd->insert($cbd, Difucom2_obd::crea_rs());
  }
}

//-------------------------------------

final class Paxina_novas2_mbd extends Paxina_mbd {
  public function __construct() {
    parent::__construct();

    $t = new Taboa_dbd("paxina_novas");

    $t->pon_campo("id_paxina"    , new Numero()           , true);
    $t->pon_campo("etq_1"                                       );
    $t->pon_campo("etq_2"                                       );
    $t->pon_campo("etq_3"                                       );
    $t->pon_campo("data"         , new Data("Y-m-d H:i:s")      );
    $t->pon_campo("enlace_tipo"  , new Numero()                 );
    $t->pon_campo("enlace_src"                                  );
    $t->pon_campo("enlace_target"                               );
    $t->pon_campo("pos"          , new Numero()                 );
    $t->pon_campo("grupo_aux"                                   );
    $t->pon_campo("entradilla"                                  );

    $this->relacion_fk($t, array("id_paxina"), array("id_paxina"));
  }
}
