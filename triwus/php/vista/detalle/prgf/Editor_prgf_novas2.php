<?php

class Editor_prgf_novas2 extends Editor_prgf {

  public function __construct(Prgf_inovas2 $p) {
    parent::__construct($p);

    $this->ptw = "ptw/paragrafo/editor_prgf_novas2.html";

    $this->obxeto("sColumnas")->sup_opcion(5);
    $this->obxeto("sColumnas")->sup_opcion(6);


    $this->pon_obxeto(self::_sbuscador($p));
    $this->pon_obxeto(self::_timx_proporcion($p));

    $this->pon_etiquetado($p);

    $this->pon_contidosRel($p);
  }

  public function operacion(EstadoHTTP $e) {
    //~ if (($e_aux = $this->obxeto("crs_editor")->operacion($e)) != null) return $e_aux;

    return parent::operacion($e);
  }

  public function operacion_aceptar(Efs_admin $e) {
    if (($erro = $this->validar()) != null) {
      $e->post_msx($erro);
      
      return null;
    }
    
    $this->prgf->iventas_buscador      = $this->obxeto("sbuscador"      )->valor();
    $this->prgf->inovas_imx_proporcion = $this->obxeto("timx_proporcion")->valor();
    $this->prgf->inovas_etqs           = serialize( $this->_etiquetado() );
    $this->prgf->inovas_rel_b          = ($this->obxeto("chcontidos")->valor())? 1 : null;
    $this->prgf->inovas_rel_t          = trim($this->obxeto("tcontidos")->valor());
    $this->prgf->inovas_rel_s          = $this->obxeto("scontidos")->valor();

    $this->prgf->preparar_saida($e->ajax());

    return parent::operacion_aceptar($e);
  }

  public function operacion_cancelar(Epaxina_edit $e) {
    //~ if (($e_aux = $this->obxeto("crs_editor")->operacion_cancelar($e)) != null) return $e_aux;

    return Editor_btnr_oeste::operacion_cancelar($e);
  }


  public function validar():?string {
    if ($this->obxeto("timx_proporcion")->validar()) return null;
    
    $p = trim($this->obxeto("timx_proporcion")->valor());
    
    if (($p = Imaxe_obd::valida_proporcion($p)) === null) return "La proporción de la imagen no es válida.";
    
    $this->obxeto("timx_proporcion")->post($p);
    
    return null;
  }


  private function _etiquetado() {
    return array("b" => array(1 => $this->obxeto("etq_b", 1)->valor()?1:0,
                              2 => $this->obxeto("etq_b", 2)->valor()?1:0,
                              3 => $this->obxeto("etq_b", 3)->valor()?1:0
                             ),
                 "t" => array(1 => $this->obxeto("etq_t", 1)->valor(),
                              2 => $this->obxeto("etq_t", 2)->valor(),
                              3 => $this->obxeto("etq_t", 3)->valor()
                             )                 );
  }

  private function pon_etiquetado(Prgf_inovas2 $p) {
    if (($_etq = $p->inovas_etqs) == null) {
      $_etq = array("b" => array(1 => false, 2 => false, 3 => false),
                    "t" => array(1 => "Etiquetado 1" , 2 => "Etiquetado 2" , 3 => "Etiquetado 3" )
                   );
    }
    else
      $_etq = unserialize($_etq);


    for ($i = 1; $i < 4; $i++) {
      $this->pon_obxeto(new Checkbox("etq_b", $_etq["b"][$i], "Nivel {$i}"), $i);

      $t = new Text("etq_t");

      $t->post($_etq["t"][$i]);

      $this->pon_obxeto($t, $i);
    }

  }

  private function pon_contidosRel(Prgf_inovas2 $p) {
    //* checker.
    $this->pon_obxeto(new Checkbox("chcontidos", $p->inovas_rel_b == 1, "Mostrar"));

    //* texto.
    $t = new Text("tcontidos");

    $t->post($p->inovas_rel_t);

    $this->pon_obxeto($t);
    
    //* selector.
    $s = new Select("scontidos", [1=>"Posición"]);
    //~ $s = new Select("scontidos", [1=>"Posición", 2=>"Etiquetado"]);
    
    $s->post($p->inovas_rel_s);
    
    $this->pon_obxeto($s);
  }

  private static function _timx_proporcion(Prgf_inovas2 $p) {
    $o = Panel_fs::__dlImxForma("timx_proporcion");
    
    $o->post($p->inovas_imx_proporcion);
    
    return $o;
    
/*
    $t = Panel_fs::__text("timx_proporcion", 5, 5);

    $t->post($p->inovas_imx_proporcion);

    $t->style("default" , "text-align: right;");
    $t->style("readonly", "text-align: right;");


    return $t;
*/
  }

  private static function _sbuscador(Prgf_inovas2 $p) {
    $_o = array("1" => "Usar buscador predeterminado",
                //~ "2", "Usar buscador a medida",
                "9" => "Sin buscador"
               );

    $s = new Select("sbuscador", $_o);

    $s->post($p->iventas_buscador);

    $s->envia_AJAX("onchange");


    return $s;
  }
}

//**********************************************

class Editor_novas2 extends Editor_prgf {
  const ptw = "ptw/paragrafo/editor_novas2.html";

  private $pax            = null;
  private $url_arquivos   = null;
  //~ private $url_upload     = null;
  private $imx_proporcion = null;


  public function __construct(Prgf_inovas2 $p, $url_arquivos, $id_paxina = null) {
    parent::__construct($p);

    $this->ptw            = self::ptw;
    $this->titulo         = "Editor de noticias";
    $this->url_arquivos   = $url_arquivos;

    $this->pax            = Paxina_obd::inicia(new FS_cbd(), $id_paxina, "novas2");
    $this->imx_proporcion = ($p->inovas_imx_proporcion > 0)?round($p->inovas_imx_proporcion, 3):"null";

    $this->inicia();
  }

  public function operacion(EstadoHTTP $e) {
    if (($e_aux = $this->obxeto("cn2_enlace")->operacion($e)) != null) return $e_aux;
    
    if ($this->obxeto("f_imaxe")->control_evento()) {
      $f = $this->obxeto("f_imaxe")->post_f();
      
      $this->obxeto("imaxe")->post( $f );

      $this->preparar_saida($e->ajax());

      return $e;
    }


    return parent::operacion($e);
  }

  public function operacion_aceptar(Efs_admin $e) {
    //* Valida nome
    if (($nome = trim($this->obxeto("tnome" )->valor())) == "") {
      $e->post_msx("ERROR, debes teclear el nombre de la noticia.");
      
      return $this->operacion_cancelar($e); //* non marcamos no editor a opción de actualizar.
    }
    
    
    //* Actualiza paxina_obd
    $b_insert = false;
    if ($this->pax->atr("id_paxina")->valor == null) {
      $this->pax->atr("id_opcion")->valor = $e->id_oms();
      
      $b_insert = true;
    }

    $this->pax->atr("nome"      )->valor = $nome;
    $this->pax->atr("titulo"    )->valor = $nome;
    $this->pax->atr("entradilla")->valor = trim($this->obxeto("tnotas")->valor());
    
    if ($this->pax->atr("descricion")->valor == null) $this->pax->atr("descricion")->valor = strip_tags($this->pax->atr("entradilla")->valor);


     //* fecha/hora do evento/noticia
    if ($this->obxeto("tdata")->valor() == null)
      $this->pax->atr("data")->valor = null;
    elseif ($this->obxeto("thora")->valor != null) 
      $this->pax->atr("data")->valor = $this->obxeto("tdata")->valor() . " " . $this->obxeto("thora" )->valor() . ":00";
    else
      $this->pax->atr("data")->valor = $this->obxeto("tdata")->valor() . " " . "00:00:00";
    
    
    //* grupo
    $this->pax->atr("grupo_aux")->valor = ( ($grupo = trim($this->obxeto("tgrupo")->valor())) == "" ) ? "pub" : $grupo;
    
    //* publicación
    $this->pax->atr("estado")->valor = $this->obxeto("chpub")->valor();
    
    if ($this->obxeto("tdatapub")->valor() == null)
      $this->pax->atr("publicar")->valor = null;
    elseif ($this->obxeto("thorapub")->valor != null) 
      $this->pax->atr("publicar")->valor = $this->obxeto("tdatapub")->valor() . " " . $this->obxeto("thorapub" )->valor() . ":00";
    else
      $this->pax->atr("publicar")->valor = $this->obxeto("tdatapub")->valor() . " " . "00:00:00";     

    //* etiquetas
    $this->pax->atr("etq_1")->valor = $this->obxeto("tetq_1")->valor();
    $this->pax->atr("etq_2")->valor = $this->obxeto("tetq_2")->valor();
    $this->pax->atr("etq_3")->valor = $this->obxeto("tetq_3")->valor();

    $this->pax = $this->obxeto("cn2_enlace")->obd($this->pax);

//~ print_r($this->pax->a_resumo());

    //* Actualiza na BD.
    $cbd = new FS_cbd();

    $cbd->transaccion();

    if ( $this->update($cbd, $e->id_idioma()) ) {
      $cbd->commit();

      
      if ($b_insert)
        $this->prgf->preparar_saida($e->ajax());
      else {
        $kpax = $this->pax->atr("id_paxina")->valor;
        $e->ajax()->pon_ok(true, "novas2_resposta_editar({$kpax})");
      }
    }
    else {
      $cbd->rollback();

      $e->post_msx("Sucedió un error al actualizar la BD.");
    }
    
    return $this->operacion_cancelar($e); //* non marcamos no editor a opción de actualizar.    
  }

  public function operacion_cancelar(Epaxina_edit $e) {
    return Editor_btnr_oeste::operacion_cancelar($e);
  }

  public function preparar_saida(Ajax $a = null) {
    $this->obxeto("hcrop")->post($this->imx_proporcion);

    parent::preparar_saida($a);

    if ($a == null) return;

    $a->pon_ok(true, "novas2_editar_1_click_resposta()");
  }

  public function declara_baceptar() {
    $b = Panel_fs::__baceptar();

    $b->pon_eventos("onclick", "elmt_novas_bAceptar_click(this, 'findex;detalle_btnr_oeste;editor')");

    return $b;
  }


  private function inicia() {
    //* inicia controis.

//~ echo "<pre>" . print_r($this->pax->a_resumo(), 1) . "</pre>";
//~ echo $this->pax->atr("data"      )->fvalor("Y/m/d"). "<br>";
//~ echo $this->pax->atr("data"      )->fvalor("H:i"  ). "<br>";


    $this->pon_obxeto(new Hidden("hcrop"));

    $this->pon_obxeto($this->__imx( $this->pax ));

    $this->pon_obxeto($this->__fimaxe( Efs::url_tmp($this->url_arquivos) ));


    $this->pon_obxeto(self::__lcambiar());

    $this->pon_obxeto(new Text("tnome"));
    $this->pon_obxeto(new Textarea("tnotas", 2, 22));

    $this->pon_obxeto(self::__tdata("tdata"));
    $this->pon_obxeto(Panel_fs::__hhmm("thora"));
    $this->pon_obxeto(new Text("tgrupo"));

    $this->pon_obxeto(new Select("chpub", ["publicada" => "Sí", "borrador" => "No"]));
    $this->pon_obxeto(self::__tdata("tdatapub"));
    $this->pon_obxeto(Panel_fs::__hhmm("thorapub"));

    $this->pon_obxeto(self::__tetq("tetq_1"));
    $this->pon_obxeto(self::__tetq("tetq_2"));
    $this->pon_obxeto(self::__tetq("tetq_3"));

    $this->pon_obxeto(new Editor_novas2_enlace($this->pax));


    //* asignamos a $this->pax aos controis.
    $this->obxeto("hcrop" )->post( $this->imx_proporcion );

    $this->obxeto("tnome" )->post( $this->pax->atr("nome"      )->valor           );
    $this->obxeto("tnotas")->post( $this->pax->atr("entradilla")->valor           );

// data e hora d noticia/evento  
    $this->obxeto("tdata" )->post( $this->pax->atr("data")->fvalor("Y-m-d") );
    $this->obxeto("thora" )->post( $this->pax->atr("data")->fvalor("H:i"  ) );

// publicación de noticia

    $this->obxeto("tgrupo"   )->post( $this->pax->atr("grupo_aux")->valor );
    
    $this->obxeto("chpub"    )->post( $this->pax->atr("estado")->valor  );
    
    $this->obxeto("tdatapub" )->post( $this->pax->atr("publicar"  )->fvalor("Y-m-d") );
    $this->obxeto("thorapub" )->post( $this->pax->atr("publicar"  )->fvalor("H:i"  ) );

    $this->obxeto("tetq_1")->post( $this->pax->atr("etq_1"     )->valor           );
    $this->obxeto("tetq_2")->post( $this->pax->atr("etq_2"     )->valor           );
    $this->obxeto("tetq_3")->post( $this->pax->atr("etq_3"     )->valor           );
  }

  private function update(FS_cbd $cbd, $id_idioma) {
//~ print_r($this->pax->a_resumo());


    //* recorta imaxe.
    if (($url = $this->__url_imaxe()) == null) return true;


    //* update.
    if ($this->pax->atr("id_paxina")->valor != null) {
      if (!$this->pax->update($cbd, true, false)) return false;


      return $this->pax->iclogo_update($cbd, $url);
    }

    //* else insert.
    return $this->pax->insert_paxina_detalle($cbd, $id_idioma, $url);
  }
/*
  private function __url_imaxe() {
    if (($url = $this->obxeto("imaxe")->src()) == Imaxe_obd::noimx) return null;
    
    if (($hcrop = $this->obxeto("hcrop")->valor()) == "") return $url; //* non cambiou nada.
    
    list($x, $y, $w, $h) = explode(Escritor_html::csubnome, $hcrop);
    
    
    $fimx = $this->obxeto("f_imaxe");
    
    if ( !$fimx->recortar($x, $y, $w, $h) ) return $url;
    
    
    return $fimx->aceptar_f( $this->url_arquivos );
  }
*/
  private function __url_imaxe() {
    if (($url = $this->obxeto("imaxe")->src()) == Imaxe_obd::noimx) return null;
    
    if (($hcrop = $this->obxeto("hcrop")->valor()) == "") return $url; //* non cambiou nada.
    
    list($x, $y, $w, $h) = explode(Escritor_html::csubnome, $hcrop);
    
    
    $fimx = $this->obxeto("f_imaxe");
    
    if ( !$fimx->recortar($x, $y, $w, $h) ) return $url;
    
    
    return $fimx->f(); //* Imaxe_obd. encargarase da xestión tmp.
  }

  private function validar() {
  }

  private static function __fimaxe($src_tmp) {
    $f = new File("f_imaxe", $src_tmp, 1024 * 1024 * 3);

    $f->clase_css("default", "file_apagado");


    $f->accept      = "image/gif, image/jpg, image/jpeg, image/png, image/webp";


    return $f;
  }

  private static function __imx(Paxina_novas2_obd $pax) {
    $url = null;
    if ( ($l = $pax->iclogo_obd(new FS_cbd())) != null ) $url = $l->url(false);

    if ($url == null) $url = Imaxe_obd::noimx;


    $i = new Image("imaxe", $url, false);


    return $i;
  }

  private static function __tdata($id) {
    return new DataInput($id);
  }

  private static function __tetq($id) {
    $t = Panel_fs::__text($id, 20, 255);

    return $t;
  }

  private static function __lcambiar() {
    /* $l = Panel_fs::__link("imx_cambiar", "Cambiar la imagen", "texto2_a"); */
    $l = Panel_fs::__link("imx_cambiar", "Cambiar la imagen", "trw-btn");

    $l->title = "Pincha aqu&iacute; para &laquo;cambiar&raquo; la imagen";

    $l->pon_eventos("onclick", "elmt_imx_cambiar(this)");

    return $l;
  }
}

//**********************************************
/*
class Editor_novas2_filtro extends Editor_prgf {
  const ptw = "ptw/paragrafo/editor_novas2_filtro.html";

  //~ private $pax            = null;
  //~ private $url_arquivos   = null;
  //~ private $url_upload     = null;
  //~ private $imx_proporcion = null;


  public function __construct(Prgf_inovas2 $p) {
    parent::__construct($p);

    $this->ptw    = self::ptw;
    $this->titulo = "Filtrado de noticias";

    $this->inicia($p);
  }

  public function operacion_aceptar(Efs_admin $e) {
    $this->prgf->f_txt  = $this->obxeto("txt" )->valor();
    $this->prgf->f_dini = $this->obxeto("dini")->valor();
    $this->prgf->f_dfin = $this->obxeto("dfin")->valor();
    $this->prgf->f_pub  = $this->obxeto("spub")->valor();

//~ echo $this->where();

    $this->prgf->where_aux($this->where());

    $this->prgf->preparar_saida( $e->ajax() );
    
    $e->ajax()->pon_ok(true, "novas2_pechar_editor()");
    

    return $this->operacion_cancelar($e); ; //* non marcamos no editor a opción de actualizar. 
  }

  private function inicia(Prgf_inovas2 $p) {
    $this->pon_obxeto(Panel_fs::__sSiNon("spub"));

    $this->pon_obxeto(self::__tdata("dini"));
    $this->pon_obxeto(self::__tdata("dfin"));

    $this->pon_obxeto(Panel_fs::__text("txt", 20, 20));

    $this->obxeto("txt" )->post($p->f_txt );
    $this->obxeto("dini")->post($p->f_dini);
    $this->obxeto("dfin")->post($p->f_dfin);
    $this->obxeto("spub")->post($p->f_pub );
  }

  private function where() {
    $wh_txt = Valida::buscador_txt2sql($this->obxeto("txt")->valor(), array("a.nome", "a.titulo", "a.descricion", "b.etq_1", "b.etq_2", "b.etq_3"));

    if ($wh_txt == null) $wh_txt = "(1)";

    $wh_pub = "(1)";
    if (($v = $this->obxeto("spub")->valor()) != "**") {
      $wh_pub = ($v == 1) ? "estado = 'publicada'" : "estado <> 'publicada'" ;
    }

    $wh_d0 = Valida::buscador_data2sql("creado", ">=", $this->obxeto("dini")->valor());
    $wh_d1 = Valida::buscador_data2sql("creado", "<=", $this->obxeto("dfin")->valor());


    return "{$wh_txt} and {$wh_pub} and ({$wh_d0} and {$wh_d1})";
  }

  private static function __tdata($id) {
    return new DataInput($id);
  }
}
*/
//**********************************************

class Editor_novas2_enlace extends Componente {
  const ptw_0 = "ptw/paragrafo/editor_novas2_enlace_0.html";
  const ptw_1 = "ptw/paragrafo/editor_novas2_enlace_1.html";


  public function __construct(Paxina_novas2_obd $p) {
    parent::__construct( "cn2_enlace", self::ptw($p->atr("enlace_tipo")->valor) );

    $this->pon_obxeto(self::__senlace());
    $this->pon_obxeto(Panel_fs::__text("src"));
    $this->pon_obxeto(new Select("starget", ["_self"=>"Misma ventana", "_blank"=>"Nueva ventana"]));


    $this->obxeto("senlace")->post( $p->atr("enlace_tipo"  )->valor );
    $this->obxeto("src"    )->post( $p->atr("enlace_src"   )->valor );
    $this->obxeto("starget")->post( $p->atr("enlace_target")->valor );
  }
  
  public function obd(Paxina_novas2_obd $p):Paxina_novas2_obd {
    $p->atr("enlace_tipo"  )->valor = $this->obxeto("senlace")->valor();
    $p->atr("enlace_src"   )->valor = trim($this->obxeto("src")->valor());
    $p->atr("enlace_target")->valor = $this->obxeto("starget")->valor();
    
    if ($p->atr("enlace_tipo")->valor != 1) return $p;
    
    
    if ($p->atr("enlace_src")->valor == "") $p->atr("enlace_tipo")->valor = 3;
    
    return $p;
  }

  public function operacion(EstadoHTTP $e) {
    if ($this->obxeto("senlace")->control_evento()) {
      $t = $this->obxeto("senlace")->valor();
      
      $this->ptw = self::ptw($t);

      $this->preparar_saida($e->ajax());

      return $e;
    }
    
    return null;
  }


  private static function __senlace():Select {
    $o = new Select( "senlace", Paxina_novas2_obd::$_enlaceTipo );
    
    //~ $o->envia_submit("onchange");
    $o->envia_ajax("onchange");
    
    return $o;
  }

  private static function ptw(?int $t):string {
    if ($t == 1) return self::ptw_1;
    
    return self::ptw_0;
  }
}
