<?php

final class Contacto_obd extends    Obxeto_bd
                         implements ICLogo, ICambio_obd {

  const num_tlfn_prefix_0 = "34";

  public $pcontrol = true; //* de momento só se emprega en self::icambio_obd()

  private $baleiro = true;

  public function __construct($tipo = "p", $id_contacto = null) {
    parent::__construct();

    $this->atr("tipo"       )->valor = $tipo;
    $this->atr("id_contacto")->valor = $id_contacto;
    $this->atr("mobil_p0"   )->valor = self::num_tlfn_prefix_0;
  }

  public function mapa_bd() {
    return new Contacto_mbd();
  }

  public function icambio_obd() { //* IMPLEMENTA ICambio_obd::icambio_obd()
    $where = "click is null and id_contacto = " . $this->atr("id_contacto")->valor;

    $c = ($this->pcontrol)?new Cambio_email_obd():new Cambio_emailweb_obd();

    $c->select(new FS_cbd(), $where);

    $c->atr("id_contacto")->valor = $this->atr("id_contacto")->valor;

    return $c;
  }

  public function id_site() { //* IMPLEMENTA ICambio_obd::id_site()
    return $this->atr("id_site")->valor;
  }

  public static function inicia(FS_cbd $cbd, $id_contacto, $tipo = "p") {
    $c = new Contacto_obd($tipo, $id_contacto);

    if ($id_contacto == null) return $c;

    $c->select($cbd, "contacto.id_contacto = {$id_contacto} and contacto.tipo = '{$tipo}'");

    return $c;
  }

  public function copiar(Contacto_obd $c0, $copia_claves = false) {
    $k1 = $this->atr("id_contacto")->valor;
    $k2 = $this->atr("tipo"       )->valor;
    $k3 = $this->atr("id_enderezo")->valor;

    $this->clonar($c0);

    if ($copia_claves) return;

    $this->atr("id_contacto")->valor = $k1;
    $this->atr("tipo"       )->valor = $k2;
    $this->atr("id_enderezo")->valor = $k3;
  }

  public function enderezo_obd() {
    $e = new Enderezo_obd();

    foreach ($e->atr() as $k=>$atr) $atr->valor = $this->atr($k)->valor;

    $e->atr("endz_c")->valor = $this->atr("id_contacto")->valor;
    $e->atr("endz_t")->valor = $this->atr("tipo"       )->valor;

//~ echo "contacto:<pre>" . print_r($this->a_resumo(), true) . "</pre>";
//~ echo "enderezo:<pre>" . print_r($e->a_resumo(), true) . "</pre>";

    return $e;
  }

  public function fpago_obd(FS_cbd $cbd = null):?Contacto_fpago_obd {
    if ($this->atr("id_contacto")->valor == null) return null;
    
    if ($cbd == null) $cbd = new FS_cbd();
    
    return Contacto_fpago_obd::inicia($cbd, $this->atr("id_contacto")->valor, $this->atr("tipo")->valor);
  }

  public function select(FS_cbd $cbd, $where = null, $orderby = null) {
    $r = $cbd->consulta($this->sql_select($cbd, $where, $orderby));

    if (!$a = $r->next()) return;

    $this->baleiro = false;

    $this->post($a);
  }

  public function baleiro() {
    //~ return ($this->atr("id_contacto")->valor == null);
    return $this->baleiro;
  }

  public function select_email(FS_cbd $cbd, $email, $id_site = null) {
    $this->atr("email")->valor = $email;
    $wh_e = $this->atr("email")->sql_where($cbd);

    if ($id_site != null) {
      $this->atr("id_site")->valor = $id_site;

      $wh_s = " and " . $this->atr("id_site")->sql_where($cbd);
    }

    $this->select($cbd, "{$wh_e}{$wh_s}");
  }

  public function update(FS_cbd $cbd, $foto_src = -1) {
    if ($this->baleiro()) return $this->insert($cbd, $foto_src);

    $e = $this->enderezo_obd();

    if (!$e->update($cbd)) return false;

    $this->atr("id_enderezo")->valor = $e->atr("id_enderezo")->valor;

    if (!$this->iclogo_update($cbd, $foto_src)) return false;


    return $cbd->executa($this->sql_update($cbd));
  }

  public function insert(FS_cbd $cbd, $foto_src = -1) {
    if (!$this->calcula_id($cbd)) return false;

    $e = $this->enderezo_obd();

    if (!$e->update($cbd)) return false;

    $this->atr("id_enderezo")->valor = $e->atr("id_enderezo")->valor;

    if (!$this->iclogo_update($cbd, $foto_src)) return false;


    if ($this->atr("nome")->valor == null)
      list( $this->atr("nome")->valor, $x ) = explode("@", $this->atr("email")->valor);


    return $cbd->executa($this->sql_insert($cbd));
  }

  public function delete(FS_cbd $cbd, $todo = false) {
    if ($this->baleiro()) return true;

    $where = "id_contacto = " . $this->atr("id_contacto")->valor;
    
    if (!$todo) $where .= " and tipo = '" . $this->atr("tipo")->valor . "'";

    //* elimina logos asociados ao contacto.
    $sql = "select foto from contacto where {$where}";

    $r = $cbd->consulta($sql);
    while ($_r = $r->next()) {
      if ($_r['foto'] == null) continue;
      
      $l = Elmt_obd::inicia($cbd, $_r['foto']);

      if (!$l->delete($cbd)) return false;
    }
    
    //* elimina enderezos asociados ao contacto.
    $sql = "delete from enderezo where {$where}";

    if (!$cbd->executa($sql)) return false;
    
    //* elimina contactos.
    $sql = "delete from contacto where {$where}";

    return $cbd->executa($sql);
  }

  public static function _migas_provincia($id_prov, FS_cbd $cbd = null) {
    if ($cbd == null) $cbd = new FS_cbd();

    $r = $cbd->consulta("select * from v_xeo where id_prov = {$id_prov}");

    if (!$a = $r->next()) return null;

    return array(array($a['id_comunidad'], $a['comunidad']),
                 array($a['id_prov']     , $a['provincia'])
                );
  }

  public static function _migas_concello($id_concello, FS_cbd $cbd = null) {
    if ($cbd == null) $cbd = new FS_cbd();

    $r = $cbd->consulta("select * from v_xeo where id_concello = {$id_concello}");

    if (!$a = $r->next()) return null;

    return array(array($a['id_comunidad'], $a['comunidad']),
                 array($a['id_prov']     , $a['provincia']),
                 array($a['id_concello'] , $a['municipio'])
                );
  }

  public function iclogo_obd(FS_cbd $cbd, ?string $k = null) {
    if ($this->iclogo_id($k) == null) return null;

    return Elmt_obd::inicia($cbd, $this->iclogo_id());
  }

  public function iclogo_id(?string $k = null) {
    return $this->atr("foto")->valor;
  }

  public function iclogo_src(FS_cbd $cbd, ?string $k = null) {
    if (($l = $this->iclogo_obd($cbd, $k)) == null) return null;

    return $l->url(0, $cbd);
  }

  public function iclogo_update(FS_cbd $cbd, $src) {
    if ($src === -1) return true;

    if (($l = $this->iclogo_obd($cbd)) != null) {
      $l->post_url($src);

      return $l->update($cbd);
    }

    if ($src == null) return true;


    $l = new Imaxe_obd($src);

    if (!$l->insert($cbd)) return false;

    //* actualiza a foto no contacto.

    $k1 = $this->atr("id_contacto")->valor;
    $k2 = $this->atr("tipo"       )->valor;
    $k3 = $l   ->atr("id_elmt"    )->valor;


    $this->atr("foto")->valor = $k3; //* actualizamos o valor en RAM


    return $cbd->executa( "update contacto set foto='{$k3}' where id_contacto='{$k1}' and tipo='{$k2}'" );
  }

  public static function _tipo($predet = "···") {
    $a = null;

    if ($predet != null) $a["**"] = $predet;

    //~ $a["a"] = "Almacén";
    //~ $a["d"] = "Destino";
    $a["f"] = "Facturación";
    $a["p"] = "Personal";

    return $a;
  }

  
  public function validar($_o = []) {
    foreach ($_o as $k) {
      $v = $this->atr($k)->valor;
      
      //* valida a presenza do dato
      if ($v == null) return Erro::cpago_0;

      //* validacións especiais
      if ($k == "email" ) if (!Valida::email($v)              ) return Erro::cpago_11;

      if ($k == "cnif_d") if (Erro::valida_nif_cif_nie($v) < 1) return Erro::cpago_9;

      if ($k == "mobil" ) {
        $p = $this->atr("mobil_p0")->valor;
        
        if (Valida::tlfn($v, $p) == null) return Erro::cpago_13;
      }
    }


  /* fran */
    if ( !isset($_o["cnif_d"]) ) {
      if ($this->atr("cnif_d")->valor != "") {
        if(Valida::nif_cif_nie($this->atr("cnif_d")->valor) <= 0) {
          return Erro::cpago_9;
        }
      }
    }
    
    

    return null;
  }

  private function calcula_id(FS_cbd $cbd) {
    if ($this->atr("id_contacto")->valor != null) return true;

    $tipo = $this->atr("tipo")->valor;

    if (!$cbd->executa("insert into contacto_1_seq (id_contacto) values (null)")) return false;

    $r = $cbd->consulta("select LAST_INSERT_ID() as id");

    if (!$a = $r->next()) return false;

    $cbd->executa("delete from contacto_1_seq where id_contacto < {$a['id']}");

    $this->atr("id_contacto")->valor = $a['id'];

    return true;
  }
}

//----------------------------------

final class Contacto_mbd extends Mapa_bd {
  public function __construct() {
    $t = new Taboa_dbd("contacto");

    $t->pon_campo("id_contacto", new Numero()     , true            );
    $t->pon_campo("tipo"       , new Varchar()    , true            );
    $t->pon_campo("cnif_t"                                          );
    $t->pon_campo("cnif_d"                                          );
    $t->pon_campo("nome"                                            );
    $t->pon_campo("apel"                                            );
    $t->pon_campo("razon_social"                                    );
    $t->pon_campo("foto"                                            );
    $t->pon_campo("email"                                           );
    $t->pon_campo("mobil"                                           );
    $t->pon_campo("mobil_p0"                                        );
    $t->pon_campo("skype"                                           );
    $t->pon_campo("web"                                             );
    $t->pon_campo("notas"      , new Varchar(true), false, "notas_0");

    $this->pon_taboa($t);

    $t2 = Enderezo_mbd::tenderezo(true);

    $this->relacion_fk($t2, array("id_contacto", "tipo"), array("id_contacto", "tipo"), "left");

    $t3 = new Taboa_dbd("usuario");

    $t3->pon_campo("id_site", new Numero());

    $this->relacion_fk($t3, array("id_contacto"), array("id_contacto"), "left");
  }
}

//**************************************

final class Enderezo_obd extends    Obxeto_bd {

  const vs0 = "**";  //* valor neutro para controis de tipo select

  public function __construct() {
    parent::__construct();
  }

  public function mapa_bd() {
    return new Enderezo_mbd();
  }

  public static function inicia(FS_cbd $cbd, $id_enderezo) {
    $e = new Enderezo_obd();

    $e->select($cbd, "enderezo.id_enderezo = {$id_enderezo}");

    return $e;
  }

  public function select(FS_cbd $cbd, $where = null, $orderby = null) {
    $r = $cbd->consulta($this->sql_select($cbd, $where, $orderby));

    if (!$a = $r->next()) return;

    $this->post($a);
  }

  public function baleiro() {
    return (trim(strval($this->atr("num")->valor)) == null);
    //~ return ($this->atr("id_enderezo")->valor == null);
  }

  public function id_prov() {
    if ($this->atr("id_country")->valor != 203) return "**"; //* só para España

    if ($this->atr("cp")->valor == null) return "**";

    return substr($this->atr("cp")->valor, 0, 2);
  }

  public function prov(FS_cbd $cbd = null) {
    if (($id_prov = $this->id_prov()) == "**") return null;

    if ($cbd == null) $cbd = new FS_cbd();

    $r = $cbd->consulta("select provincia from provincias where id_prov = {$id_prov}");

    if (!$a = $r->next()) return null;


    return ucwords($a['provincia']);
  }

  public function country(FS_cbd $cbd = null) {
    $id_country = $this->atr("id_country")->valor;

    if ($cbd == null) $cbd = new FS_cbd();

    $r = $cbd->consulta("select nome from country where id = '{$id_country}'");

    if (!$a = $r->next()) return null;


    return ucwords($a['nome']);
  }

  public function resumo() {
    if ($this->baleiro()) return "";

    $resto = $this->atr("resto")->valor;
        if ($resto == null                 ) $resto = "";
    elseif (($resto = trim($resto)) != null) $resto = ", {$resto}";

    if (($prov = $this->prov()) != null) $prov = ", {$prov}";

    $r = $this->atr("codtipovia")->valor . " " . $this->atr("via")->valor . ", " . $this->atr("num")->valor . "{$resto}, " .
         $this->atr("localidade")->valor . " - " . $this->atr("cp")->valor . "{$prov}, " . $this->country();


    if (str_replace(" ", "", trim($r)) != ",,-,") return $r;

    return "";
  }

  public function update(FS_cbd $cbd) {
    if ($this->atr("id_enderezo")->valor == null) return $this->insert($cbd);

    if ($this->atr("id_country")->valor == null) $this->atr("id_country")->valor = 203; //* españa predet

    return $cbd->executa($this->sql_update($cbd));
  }

  public function insert(FS_cbd $cbd) {
    if ($this->atr("id_country")->valor == null) $this->atr("id_country")->valor = 203; //* españa predet

    if (!$cbd->executa($this->sql_insert($cbd))) return false;

    $this->calcula_id($cbd);

    return true;
  }

  public function delete(FS_cbd $cbd) {
    return $cbd->executa($this->sql_delete($cbd));
  }

  
  public function validar($_o = []) {
    //~ if ($_o == null) $_o = self::$_validar;

    foreach ($_o as $k) {
      $v = $this->atr($k)->valor;

      //* valida a presenza do dato
      if ($v == null     ) return Erro::cpago_0;
      if ($v == self::vs0) return Erro::cpago_0;

      //* validacións especiais
      if ( ($k == "cp") && ($this->atr("id_country")->valor == 203) ) { 
        $cp = $this->atr("cp")->valor;

        if ( strlen($cp) != 5 ) return Erro::cpago_3;
        if ( !is_numeric($cp) ) return Erro::cpago_3;
      }
    }


    return null;
  }
  
  private function calcula_id(FS_cbd $cbd) {
    $r = $cbd->consulta("select LAST_INSERT_ID() as id");

    if (!$a = $r->next()) die("Enderezo_obd.calcula_id(), (!a = r.next())");


    if ($a['id'] == null) $a['id'] = 1;

    $this->atr("id_enderezo")->valor = $a['id'];
  }
}

//----------------------------------

final class Enderezo_mbd extends Mapa_bd {
  public function __construct() {
    parent::__construct();

    $this->pon_taboa(self::tenderezo());
  }

  public static function tenderezo($v2 = false) {
    $t = new Taboa_dbd("enderezo");

    $t->pon_campo("id_enderezo", new Numero(), true);

    $t->pon_campo("id_contacto", new Numero() , false, "endz_c");
    $t->pon_campo("tipo"       , new Varchar(), false, "endz_t");
    $t->pon_campo("codtipovia"                                 );
    $t->pon_campo("via"                                        );
    $t->pon_campo("num"                                        );
    $t->pon_campo("resto"                                      );
    $t->pon_campo("cp"                                         );
    $t->pon_campo("id_prov"                                    );
    $t->pon_campo("id_concello"                                );
    $t->pon_campo("id_country"                                 );
    $t->pon_campo("localidade"                                 );
    $t->pon_campo("tlfn"                                       );
    $t->pon_campo("tlfn_p0"                                    );
    $t->pon_campo("fax"                                        );
    $t->pon_campo("lat"        , new Numero());
    $t->pon_campo("lnx"        , new Numero());
    $t->pon_campo("notas"                                      );

    return $t;
  }
}

//**************************************

final class Contacto_fpago_obd extends Obxeto_bd {
  const trw_pref  = "t"; 
  const trw_iban  = "ES62 0081 2197 1400 0112 9215";
  const trw_swift = "BSAB ESBB";
  const trw_bene  = "TRIWUS SOCIEDADE COOPERATIVA GALEGA";
  const trw_vence = 30;
  
  private static $k = "7go/q#'Nw4^-";
  
  private static $_pref = ["d" => "Domiciliación",
                           "t" => "Transferencia"
                          ];
  
  private bool $baleiro = true;

  public function __construct($contacto_t = "p") {
    parent::__construct();

    $this->baleiro = true;

    $this->atr("tipo")->valor = $contacto_t;
    $this->atr("pref")->valor = "t";
  }

  public function mapa_bd() {
    return new Contacto_fpago_mbd();
  }

  public function baleiro() {
    return $this->baleiro;
  }

  public static function inicia(FS_cbd $cbd, $contacto_k, $contacto_t = "p") {
    $c = new Contacto_fpago_obd($contacto_t);

    $c->atr("id_contacto")->valor = $contacto_k;
    
    if ($contacto_k == null) return $c;

    $wh_k = $c->atr("id_contacto")->sql_where( $cbd );
    $wh_t = $c->atr("tipo"       )->sql_where( $cbd );

    $c->select($cbd, "{$wh_k} and {$wh_t}");

    return $c;
  }

  public function select(FS_cbd $cbd, $where = null, $orderby = null) {
    $r = $cbd->consulta( $this->sql_select($cbd, $where, $orderby) );

    if (!$a = $r->next()) return;

    $this->baleiro = false;

    $this->post($a);
  }

  public function update(FS_cbd $cbd) {
    if ($this->baleiro) return $this->insert($cbd);

    return $cbd->executa($this->sql_update($cbd));
  }

  public function insert(FS_cbd $cbd) {
    return $cbd->executa($this->sql_insert($cbd));
  }

  public function delete(FS_cbd $cbd) {
    if ($this->baleiro) return true;
    
    return $cbd->executa($this->sql_delete($cbd));
  }


  public function validar() {
    if ($this->atr("pref")->valor == "t") return null;
    
    //~ $this->atr("iban" )->valor = trim( $this->atr("iban" )->valor );
    //~ $this->atr("swift")->valor = trim( $this->atr("swift")->valor );
    //~ $this->atr("bene" )->valor = trim( $this->atr("bene" )->valor );
    //~ $this->atr("vence")->valor = trim( $this->atr("vence")->valor );
    
    
    return Valida::iban( $this->iban() );
  }


  public function iban($iban = -1) {
    return $this->atr_cifrado("iban", $iban);
  }

  public function swift($swift = -1) {
    return $this->atr_cifrado("swift", $swift);
  }


  public function _publicar() {
    $_p = self::_publicar_baleiro();
    
    if ($this->baleiro()                ) return $_p;
    
    if ($this->atr("pref")->valor == "t") return $_p;
    
    
    $_p["pref"]      = $this->atr("pref")->valor;
    $_p["_o"  ]["d"] = self::$_pref["d"] . ": **************** " . substr($this->iban(), -4, 4);
    
    return $_p; 
    
  }

  public static function _publicar_baleiro() {
    return ["pref" => "t",
            "_o"   => [ "t" => self::$_pref["t"] . ": " . self::trw_swift . "  -  " . self::trw_iban 
                      ]
           ];
  }
  
  public static function _pref() {
    return self::$_pref;
  }



  private function atr_cifrado($k, $v = -1) {
    if ($v != -1) {
      if ($v == null)
        $this->atr($k)->valor = null;
      else
        $this->atr($k)->valor = AES_256_CBC::cifrar($v, self::$k);
        
      return;
    }

    if ($this->atr($k)->valor == null) return null;

    return AES_256_CBC::descifrar($this->atr($k)->valor, self::$k);
  }
}

//----------------------------------

final class Contacto_fpago_mbd extends Mapa_bd {
  public function __construct() {
    $t = new Taboa_dbd("contacto_fpago");

    $t->pon_campo("id_contacto", new Numero() , true);
    $t->pon_campo("tipo"       , new Varchar(), true);
    $t->pon_campo("pref"                            );
    $t->pon_campo("iban"                            );
    $t->pon_campo("swift"                           );
    $t->pon_campo("bene"                            );
    $t->pon_campo("vence"      , new Numero()       );


    $this->pon_taboa($t);
  }
}
