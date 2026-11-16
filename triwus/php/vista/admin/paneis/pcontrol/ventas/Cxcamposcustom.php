<?php

final class Cxcamposcustom extends Componente implements ICPC {
  const ptw_1 = "ptw/paneis/pcontrol/ventas/campos-custom/cxcc.html";

  public $id_site    = null;
  public $id_almacen = null;

  private $_msx_borra = null;

  public function __construct($id_site, $id_almacen) {
    parent::__construct("cxcc", self::ptw_1);

    $this->id_site    = $id_site;
    $this->id_almacen = $id_almacen;

    $this->pon_obxeto(Panel_fs::__bmais("bmais" , "Crear campo cústom", "A&ntilde;adir un nuevo campo a custom" ));

    CampoCustom_0_obd::__crea_0($id_site, new FS_cbd()); //* aplica plantillas, se aínda non foron aplicadas.

    $this->pon_obxeto(self::larticulos());

    $this->pon_obxeto( new Cxcc_ficha  ($id_site, $id_almacen) );
    $this->pon_obxeto( new Cxcc_lista_0($id_site, $id_almacen) );


    //~ $this->pon_obxeto(new Span("erro_bsup"));
    //~ $this->obxeto("erro_bsup")->clase_css("default", "texto3_erro");

    $this->obxeto("bmais")->envia_AJAX("onclick");
  }

  public function cpc_miga() {
    return "campos cústom";
  }

  public function seleccionado() {
    return $this->obxeto("cxccf")->seleccionado();

    return null;
  }

  public function operacion(EstadoHTTP $e) {
    //~ $this->obxeto("erro_bsup")->post(null);

    if ($this->obxeto("bmais")->control_evento()) return $this->operacion_ficha($e, null);

    if ($this->obxeto("larticulos")->control_evento("onclick")) return $this->operacion_larticulos($e);

    if (($e_aux = $this->obxeto("cxccf"  )->operacion($e)) != null) return $e_aux;
    if (($e_aux = $this->obxeto("cxccl_0")->operacion($e)) != null) return $e_aux;


    return null;
  }

  public function operacion_ficha(EstadoHTTP $e, $id_cc_0) {
    $cc_0 = CampoCustom_0_obd::inicia(new FS_cbd(0), $this->id_site, $id_cc_0);

    $this->obxeto("cxccf")->pon_cc_0($cc_0);

    $this->preparar_saida($e->ajax());

    return $e;
  }

  public function msx_borra_cc_erro($_msx) {
    $this->_msx_borra = $_msx;

    $this->obxeto("larticulos")->post($_msx["n"]);

    $s = "ERROR: - cc en uso en el artículo: " . $this->obxeto("larticulos")->html();


    if ($_msx["t"] < 2) return "{$s}.";


    return "{$s} y otros {$_msx["t"]} artículos más." ;
  }

  private function operacion_larticulos(EstadoHTTP $e) {
    $e->operacion_cpcactivo( Cpc_index::factory($e, "cxarticulos") );

    //~ echo $e->obxeto("cxarticulos")->nome_completo();

    $cxarti = $e->obxeto("cxarticulos");


    $e = $cxarti->obxeto("cxab")->operacion_busca_txt($e, $this->_msx_borra["n"]);

    $e = $cxarti->operacion_articulo($e, $this->_msx_borra["a"]);


    //~ echo $e->evento()->html();

    return $e;
  }

  private static function larticulos() {
    $d = new Span("larticulos");

    $d->clase_css("default", "lista_elemento_a texto3");

    $d->envia_submit("onclick");


    return $d;
  }

}


//******************************************


final class Cxcc_ficha extends Componente {
  const ptw_0 = "ptw/paneis/pcontrol/ventas/campos-custom/cxcc_f.html";
  const ptw_1 = "ptw/paneis/pcontrol/ventas/campos-custom/cxcc_f_alta.html";

  public $id_cc_0;

  public function __construct($id_site, $id_almacen) {
    parent::__construct("cxccf");

    $this->pon_obxeto(Panel_fs::__text("nome", 33, 99, " "));

    $this->pon_obxeto(new Cxcc_lista_1($id_site, $id_almacen));

    $this->pon_obxeto(self::_tipo());

    $this->pon_obxeto(Panel_fs::__bmais("bmais" , "Añadir un valor", "Crea un nuevo valor del campo a medida" ));

    $this->pon_obxeto(Panel_fs::__baceptar());
    $this->pon_obxeto(Panel_fs::__bcancelar());


    $this->pon_obxeto(new Span("erro_bsup"));
    $this->obxeto("erro_bsup")->clase_css("default", "texto3_erro");


    $this->obxeto("bCancelar")->envia_ajax("onclick");
    $this->obxeto("bAceptar" )->envia_ajax("onclick");
  }

  public function seleccionado() {
    return $this->id_cc_0;
  }

  public function pon_cc_0(CampoCustom_0_obd $cc_0) {
    $this->id_cc_0 = $cc_0->atr("id_cc_0")->valor;

    $this->ptw = ($this->id_cc_0 == null)?Refs::url(self::ptw_1):Refs::url(self::ptw_0);

    $this->obxeto("nome")->post( $cc_0->atr("nome")->valor );
    $this->obxeto("tipo")->post( $cc_0->atr("tipo")->valor );

    $this->obxeto("cxccl_1")->post_cc0( $this->id_cc_0 );
  }

  public function operacion(EstadoHTTP $e) {
    if ($this->ptw == null) return null;

    //$this->post_erro(false);
    $e->post_msx("");

    $this->obxeto("erro_bsup")->post(null);


    if ($this->obxeto("bmais"    )->control_evento()) return $this->operacion_bMais    ($e);
    if ($this->obxeto("bAceptar" )->control_evento()) return $this->operacion_bAceptar ($e);
    if ($this->obxeto("bCancelar")->control_evento()) return $this->operacion_bCancelar($e);

    if (($e_aux = $this->obxeto("cxccl_1")->operacion($e)) != null) return $e_aux;

    return null;
  }

  public function operacion_bMais(Efs $e) {
    $cbd = new FS_cbd(0);

    $cbd->transaccion();

    $cc1 = CampoCustom_1_obd::inicia($cbd, $this->pai->id_site, $this->id_cc_0);

    if ($cc1->atr("tipo" )->valor == "color")
      $cc1->atr("nome" )->valor = "#000000";
    else
      $cc1->atr("nome" )->valor = "nombre_valor";

    if ($cc1->insert($cbd)) {
      $cbd->commit();
    }
    else {
     // $this->post_erro();
      $e->post_msx("Se ha producido un error al intentar actualizar la BD");

      $cbd->rollback();
    }

    $this->obxeto("cxccl_1")->preparar_saida($e->ajax());

    return $e;
  }

  public function operacion_bAceptar(Efs $e) {
    $n = $this->obxeto("nome" );
    $t = $this->obxeto("tipo" );

    if ($n->valor() == null) {
     // $this->post_erro();     // erro si input nombre do campoCustom vacío
      $e->post_msx("Error. Nombre de Campo Custom vacío.");

     // $this->preparar_saida($e->ajax());

      return $e;
    }

    $cbd = new FS_cbd();

    $cbd->transaccion();

    $cc0 = CampoCustom_0_obd::inicia($cbd, $this->pai->id_site, $this->id_cc_0, $this->pai->id_almacen);

    $cc0->atr("nome")->valor = $n->valor();
    $cc0->atr("tipo")->valor = $t->valor();

    if (!$cc0->update($cbd)) {
      //$this->post_erro();
      $e->post_msx("Se ha producido un error al intentar actualizar la BD");

      $cbd->rollback();

     // $this->preparar_saida($e->ajax());

      return $e;
    }

    $cbd->commit();


    return $this->operacion_bCancelar($e);
  }

  public function operacion_bCancelar(Efs $e) {
    $this->id_cc_0 = null;
    
    $this->obxeto("cxccl_1")->editando = null;

    $this->ptw = null;

    $this->pai->preparar_saida($e->ajax());

    return $e;
  }

 /*  private function post_erro($b = true) {
    $n = $this->obxeto("nome");

    if ($b) {
      $n->title = "Teclea un nombre";

      $n->style("default", "border-bottom: #ba0000 1px solid;background-color: #d8686880;");

      return;
    }

    $n->title = null;

    $n->style("default", null);
  } */

  private static function _tipo() {
    $s = new Select("tipo", CampoCustom_0_obd::$_tipo);

    return $s;
  }
}


//************************************


final class Cxcc_lista_0 extends FS_lista {
  public $id_site    = null;
  public $id_almacen = null;

  public function __construct($id_site, $id_almacen) {
    parent::__construct("cxccl_0", new Cxcc_ehtml_0());

    $this->id_site    = $id_site;
    $this->id_almacen = $id_almacen;

    $this->selectfrom = "select * from campocustom_0";
    $this->orderby    = "id_cc_0";
    $this->limit_f    = 10;

    $this->__where();

    //~ echo $this->sql_vista() . "<br>\n";
  }

  public function __where($where = null) {
    $this->where = "id_site = {$this->id_site}";

    if ($this->id_almacen != 1) $this->where .= " and (id_almacen in (1, {$this->id_almacen}))";


    if ($where == null) return;

    $this->where .= " and {$where}";
  }

  public function seleccionado() {
    return $this->pai->seleccionado();
  }

  public function operacion(EstadoHTTP $e) {
    $evento = $e->evento();

//~ echo $evento->html();

    $id_cc_0 = $evento->subnome(0);

    if ($this->control_fslevento($evento, "bsup"   )) return $this->operacion_bsup      ($e, $id_cc_0);
    if ($this->control_fslevento($evento, "id_cc_0")) return $this->pai->operacion_ficha($e, $id_cc_0);


    return parent::operacion($e);
  }

  private function operacion_bsup(Efs $e, $id_cc_0) {
    $cbd = new FS_cbd();

    //* 1.- Valida borrado.
    if (($_m = Articulo_cc_obd::cc_enUso($cbd, $this->id_site, $id_cc_0)) != null) {
      $m = $e->obxeto("cxcc")->msx_borra_cc_erro( $_m );
      
      $e->post_msx($m);

      //~ $this->pai->obxeto("erro_bsup")->post($m);

      return $e;
    }

    //* 2.- elimina cc.

    $cbd->transaccion();

    $cc_0 = new CampoCustom_0_obd($this->id_site, $id_cc_0);

    if (!$cc_0->delete($cbd)) {
      $cbd->rollback();

      return $e;
    }

    $cbd->commit();

    $this->control_limits_sup();

    return $e;
  }
}

//---------------------------------

final class Cxcc_ehtml_0 extends FS_ehtml {
  public function __construct() {
    parent::__construct();

    $this->class_table   = "lista_00";
    $this->style_table = "border: 1px #aaa solid;";
    $this->align       = "left";
    $this->width       = "95%";
    $this->cellspacing = 0;
    $this->cellpadding = 0;
    $this->border      = 0;
  }

  protected function cabeceira($df = null) {
    $s = ""; if ($this->xestor->id_almacen == 1) $s = "<th width=55px>" . $this->__lOrdenar_html("id_almacen", "#Almacen") . "</th>";

    return "<tr>
              <th style='width: 10em;'>" . $this->__lOrdenar_html("id_cc_0", "#Campo") . "</th>
              <th>" . $this->__lOrdenar_html("nome", "Nombre") . "</th>
              {$s}
              <th style='width: 1em;'></th>
            </tr>";
  }

  protected function linha_detalle($df, $f) {
    $s = "";
    if ($this->xestor->id_almacen == 1) {
      if ($f['id_almacen'] > 1)
        $s = "<td align=center>" . Articulo_obd::ref($f['id_almacen'], 3) . "</td>";
      else
        $s = "<td align=center></td>";
    }

    $style_tr = ($this->xestor->seleccionado() != $f['id_cc_0'])?FS_ehtml::style_tr:FS_ehtml::style_tr_a;

    return "<tr height='38px' {$style_tr} >
              <td align=center>" . self::__lcc($this, $f) . "</td>
              <td style='white-space: nowrap;'>{$f['nome']}</td>
              {$s}
              <td>" . self::__bSup($this, $f) . "</td>
            </tr>";
  }

  protected function totais() {
    $c = ($this->xestor->id_almacen == 1)?4:3;

    return "<tr>
              <td align=center colspan={$c}>" . $this->__paxinador_html() . "</td>
            </tr>";
  }

  private static function __lcc(Cxcc_ehtml_0 $ehtml, $f) {
    $b = new Div("id_cc_0", Articulo_obd::ref($f['id_cc_0'], 3));

    $b->clase_css("default", "lista_elemento_a texto3");

    $b->title = "Editar campo cústom";

    $b->envia_ajax("onclick");
    //~ $b->envia_submit("onclick");

    return $ehtml->__fslControl($b, $f['id_cc_0'])->html();
  }

  private static function __bSup(Cxcc_ehtml_0 $ehtml, $f) {
    if ($f['id_cc_0'] < 3) return ""; //* non se poden borrar camposcustom auto-creados.

    if ($ehtml->xestor->id_almacen > 1) if ($f['id_almacen'] == 1) return ""; //* un almacén so pode borrar os seus campos custom.

    $b = Panel_fs::__bsup("bsup", null);

    //~ $b->envia_ajax("onclick", "¿ Deseas eliminar el campo cústom seleccionado ?");
    $b->envia_submit("onclick", "¿ Deseas eliminar el campo cústom seleccionado ?");

    return $ehtml->__fslControl($b, $f['id_cc_0'])->html();
  }
}


//************************************


final class Cxcc_lista_1 extends FS_lista {
  public $id_site    = null;
  public $id_cc_0    = null;

  public $editando   = null;
  
  public $_orde      = [];

  public function __construct($id_site) {
    parent::__construct("cxccl_1", new Cxcc_ehtml_1());

    $this->id_site    = $id_site;

    $this->selectfrom = "select * from v_campocustom";
    $this->orderby    = "orde";
    $this->limit_f    = 5;

    $this->post_cc0();

    //~ echo $this->sql_vista() . "<br>\n";
    
    
    $this->pon_obxeto( new Hidden("horde") );


    $this->pon_obxeto(Panel_fs::__text("nome"  , 14, 99, " "));
    $this->pon_obxeto(Panel_fs::__text("notas" , 14, 99, " "));

    $this->pon_obxeto(Panel_fs::__baceptar (null));
    $this->pon_obxeto(Panel_fs::__bcancelar(null));

    $this->obxeto("bAceptar" )->envia_AJAX("onclick");
    $this->obxeto("bCancelar")->envia_AJAX("onclick");
  }

  public function post_cc0($id_cc_0 = null) {
    $this->id_cc_0 = $id_cc_0;

    $wh = ($id_cc_0 == null) ? "1=0" : "id_site = {$this->id_site} and id_cc_0 = {$this->id_cc_0}";
    
    $this->post_where($wh);
  }

  public function operacion(EstadoHTTP $e) {
    $evento = $e->evento();

//~ echo $evento->html();

    if ( $this->obxeto("bCancelar")->control_evento() ) return $this->operacion_editar  ($e, null);
    if ( $this->obxeto("bAceptar" )->control_evento() ) return $this->operacion_bAceptar($e);
    
    if ( $this->control_fslevento($evento, "sorde"  ) ) return $this->operacion_sorde ($e, $evento->subnome(0));
    if ( $this->control_fslevento($evento, "beditar") ) return $this->operacion_editar($e, $evento->subnome(0));
    if ( $this->control_fslevento($evento, "bsup"   ) ) return $this->operacion_bsup  ($e, $evento->subnome(0));


    return parent::operacion($e);
  }

  public function operacion_sorde(Epcontrol $e, $l_ini) {
    $l_fin = $this->obxeto("horde")->valor();
    
    
    $_pos_fin = $this->operacion_sorde_0( $l_ini, $l_fin );

    $cbd = new FS_cbd();
    
    $cbd->transaccion();
    
    if ( $this->operacion_sorde_1($cbd, $_pos_fin) ) {
      //~ $cbd->rollback();
      $cbd->commit();
      
      $e->post_msx("Se cambió el orden de los campos custom correctamente.", "m");
    }
    else {
      $cbd->rollback();
      
      $e->post_msx("Sucedió un error al actualizar la BD.");
    }
    
    $this->preparar_saida($e->ajax());

    return $e;
  }

  private function operacion_bAceptar(EstadoHTTP $e) {
    $n1 = $this->obxeto("nome"  );
    $n2 = $this->obxeto("notas" );

    if ($n1->valor() == null) {
     // $this->post_erro();
     $e->post_msx("Debes teclear el nombre del campo cústom");
     // $this->preparar_saida($e->ajax());

      return $e;
    }

    $cbd = new FS_cbd();

    $cc1 = CampoCustom_1_obd::inicia($cbd, $this->id_site, $this->id_cc_0, $this->editando);

    $cc1->atr("nome"  )->valor = $n1->valor();
    $cc1->atr("notas" )->valor = $n2->valor();

    if ($cc1->update($cbd)) return $this->operacion_editar($e, null);

    //$this->post_erro();
    $e->post_msx("Sucedió un error al tratar de actualizar la BD");

   // $this->preparar_saida($e->ajax());

    return $e;
  }

  private function operacion_editar(EstadoHTTP $e, $editando) {
/*
    if ($editando == null) 
      //~ // $this->post_erro(false);
      $e->post_msx("dddddddddddddddddd");
*/
    $this->editando = $editando;

    $this->preparar_saida($e->ajax());

    return $e;
  }

  private function operacion_sorde_0(string $l_ini, string $l_fin):array {
    $_pos_ini = $this->_orde;
    
//~ echo "(l_ini -> l_fin): ({$l_ini} -> {$l_fin})<br>";
//~ echo "<br>_pos_ini:<pre>" . print_r($_pos_ini, 1) . "</pre>";

    $pos_lini   = $_pos_ini[$l_ini];
    $pos_lfin   = $_pos_ini[$l_fin];

//~ echo "pos($l_ini): {$pos_lini}<br>";
//~ echo "pos($l_fin): {$pos_lfin}<br>";

    unset($_pos_ini[$l_ini]);

//~ echo "<br>unset(_pos_ini[{$l_ini}]):<pre>" . print_r($_pos_ini, 1) . "</pre>";


    $_pos_fin = [];

    foreach($_pos_ini as $l_j => $pos_j) {
      
//~ echo "(j, pos_j, l_j, l_fin): ({$j}, {$pos_j}, {$l_j}, {$l_fin})<br>";

      if ($pos_lini > $pos_lfin) {
        
        if ($pos_j == $pos_lfin) {
          $_pos_fin[$l_ini] = $pos_j;
          
          $_pos_fin[$l_j] = $pos_j + 1;
        }
        elseif ($pos_j > $pos_lfin) {
          $_pos_fin[$l_j] = $pos_j + 1;
        }
        else {
          $_pos_fin[$l_j] = $pos_j;
        }
        
      }
      else { //* ($pos_lini <= $pos_lfin)
        
        if ($pos_j == $pos_lfin) {
          $_pos_fin[$l_j] = $pos_j-1;
          
          $_pos_fin[$l_ini] = $pos_j;
        }
        elseif ($pos_j < $pos_lfin) {
          $_pos_fin[$l_j] = $pos_j - 1;
        }
        else {
          $_pos_fin[$l_j] = $pos_j;
        }
        
      }

      
   } //* FIN: foreach($_pos_ini as $l_j => $pos_j)

//~ echo "<br>_pos_fin:<pre>" . print_r($_pos_fin, 1) . "</pre>";

    return $_pos_fin;
  }

  private function operacion_sorde_1(FS_cbd $cbd, array $_pos_fin):bool {
    $p_j = 1;
    foreach ($_pos_fin as $l => $x) {
      
      $sql = "update campocustom_1 a
                set a.orde = $p_j
              where a.id_site = {$this->id_site} and a.id_cc_0 = {$this->id_cc_0} and a.id_cc_1 = {$l}";
              
      if ( !$cbd->executa($sql) ) return false;
      
      $p_j++;
    }
    
    return true;
  }

  private function operacion_bsup(Efs $e, $id_cc_1) {
    $cbd = new FS_cbd();

    //* 1.- Valida borrado.
    if (($_m = Articulo_cc_obd::cc_enUso($cbd, $this->id_site, $this->id_cc_0, $id_cc_1)) != null) {
      $m = $e->obxeto("cxcc")->msx_borra_cc_erro( $_m );
      
      $e->post_msx($m);

      //~ $this->pai->obxeto("erro_bsup")->post($m);

      //~ $this->preparar_saida($e->ajax());

      return $e;
    }

    //* 2.- elimina cc.

    $cbd->transaccion();

    $cc_1 = new CampoCustom_1_obd($this->id_site, $this->id_cc_0, $id_cc_1);

    if (!$cc_1->delete($cbd)) {
      $e->post_msx("Sucedió un error al tratar de actualizar la BD");
      
      $cbd->rollback();

      //~ $this->preparar_saida($e->ajax());

      return $e;
    }

    $cbd->commit();

    $this->control_limits_sup();


    $this->preparar_saida($e->ajax());


    return $e;
  }

  /* private function post_erro($b = true) {
    $n = $this->obxeto("nome");

    if ($b) {
      $n->title = "Teclea un nombre";

      $n->style("default", "border-bottom: #ba0000 1px solid;background-color: #d8686880;");

      return;
    }

    $n->title = null;

    $n->style("default", null);
  } */
}

//---------------------------------

final class Cxcc_ehtml_1 extends FS_ehtml {
  public function __construct() {
    parent::__construct();

    $this->class_table   = "lista_00";
    $this->style_table = "border: 1px #aaa solid;";
    $this->align       = "left";
    $this->width       = "100%";
    $this->cellspacing = 0;
    $this->cellpadding = 0;
    $this->border      = 0;
  }

  protected function cabeceira($df = null) {
    $this->xestor->obxeto("horde")->post(null);
    
    $this->xestor->_orde = CampoCustom_1_obd::_orde( $this->xestor->id_site, $this->xestor->id_cc_0 );

//~ print_r($this->xestor->_orde);

    return "<tr>
              <th width=55px>" . $this->__lOrdenar_html("id_cc_1", "#Valor") . "</th>
              <th width='7em'>" . $this->__lOrdenar_html("nome", "Valor") . "</th>
              <th width='*'>" . $this->__lOrdenar_html("notas", "Notas") . "</th>
              <th style='text-align: right;padding-right: 1.7em;'>" . $this->__lOrdenar_html("orde", "Orden") . "</th>
              <th width=1px></th>
              <th width=1px></th>
            </tr>";
  }

  protected function linha_detalle($df, $f) {
    if ($this->xestor->editando == $f["id_cc_1"]) return $this->linha_detalle_editando($df, $f);


    if ($f["tipo"] == "color") {
      $n = "
        <div style='display:flex; align-items: center; flex-wrap: nowrap;'>
          <div style='height: 0.9em; width: 2.1em; margin-right: 0.5em; border: 1px solid #000; background-color: {$f["valor"]};'></div>
          <div>{$f["valor"]}</div>
        </div>";
    }
    else {
      $n = $f["valor"];
    }


    return "<tr height='38px'>
              <td align=center>" . Articulo_obd::ref($f["id_cc_1"], 3) . "</td>
              <td>{$n}</td>
              <td>{$f["notas"]}</td>
              <td style='text-align: right;padding-right: 1.7em;'>" . self::__sorde($this, $f) . "</td>
              <td align=right>" . self::__bEditar($this, $f) . "</td>
              <td align=right>" . self::__bSup   ($this, $f) . "</td>
            </tr>";
  }

  protected function linha_detalle_editando($df, $f) {
    $n1 = $this->xestor->obxeto("nome");

    $n1->type = ($f["tipo"] == "color")?"color":"text";

    $n1->post($f["valor"]);

    $n2 = $this->xestor->obxeto("notas");

    $n2->post($f["notas"]);

    return "<tr>
              <td align=center>" . Articulo_obd::ref($f["id_cc_1"], 3) . "</td>
              <td>" . $n1->html() . "</td>
              <td>" . $n2->html() . "</td>
              <td style='text-align: right;padding-right: 1.7em;'>" . self::__sorde($this, $f) . "</td>
              <td align=right>" . $this->xestor->obxeto("bCancelar")->html() . "</td>
              <td align=right>" . $this->xestor->obxeto("bAceptar" )->html() . "</td>
            </tr>";
  }

  protected function totais() {
    return "<tr >
              <td align=center colspan=5>" . $this->__paxinador_html() . $this->xestor->obxeto("horde")->html() . "</td>
            </tr>";
  }

  private static function __bEditar(Cxcc_ehtml_1 $ehtml, $f) {
    if ($ehtml->xestor->editando != null) return "";

    $b = Panel_fs::__beditar("beditar", null);

    $b->envia_ajax("onclick");
    //~ $b->envia_submit("onclick");

    return $ehtml->__fslControl($b, $f['id_cc_1'])->html();
  }

  private static function __bSup(Cxcc_ehtml_1 $ehtml, $f) {
    if ($ehtml->xestor->editando   != null) return "";

    $b = Panel_fs::__bsup("bsup", null);

    $b->envia_ajax("onclick");
    //~ $b->envia_submit("onclick");

    return $ehtml->__fslControl($b, $f['id_cc_1'])->html();
  }

  private static function __sorde(Cxcc_ehtml_1 $ehtml, $f) {
    $s = new Select("sorde", $ehtml->xestor->_orde);
    
    $s->post( $f["id_cc_1"] );
    
    $s->pon_eventos("onchange", "ccustom_sorde_change(this)");
    
    $s->readonly = ($ehtml->xestor->editando != null);
     

    return $ehtml->__fslControl($s, $f['id_cc_1'])->html();
  }
}


