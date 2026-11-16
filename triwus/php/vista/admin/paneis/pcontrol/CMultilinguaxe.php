<?php

final class CMultilinguaxe extends Componente implements ICPC {
  const ptw = "ptw/paneis/pcontrol/idiomas/cml_lista.html";

  private $id_site  = null;
  private $ml       = false;

  public function __construct(int $id_site) {
    parent::__construct("cml", self::ptw);

//~ echo "site: $id_site<br>";

    $this->id_site = $id_site;
    $this->ml      = self::__calcula_ml($id_site);

    $this->pon_obxeto(new CML_idioma($this->id_site));
    $this->pon_obxeto(new CML_lista ($this->id_site));

    $this->pon_obxeto(self::__baddidioma($this->ml));
  }

  public function cpc_miga() {
    return "idiomas";
  }

  public function operacion(EstadoHTTP $e) {
    if (($e_aux = $this->obxeto("cmll"      )->operacion($e)) != null) return $e_aux;
    if (($e_aux = $this->obxeto("cml_idioma")->operacion($e)) != null) return $e_aux;
    
    if ($this->obxeto("baddidioma")->control_evento()) return $this->operacion_idioma($e, null, true);


    return parent::operacion($e);
  }

  public function operacion_idioma(Epcontrol $e, $id_idioma_0, bool $addIdioma) {
    $this->obxeto("cml_idioma")->abrir($id_idioma_0, $addIdioma);

    $this->preparar_saida($e->ajax());

    return $e;
  }

  private static function __sidioma($id_site) {
    $s = new Select("sidioma", MLsite_obd::__a_idiomas($id_site, 2));

    return $s;
  }

  private static function __baddidioma(bool $ml) {
    $b = Panel_fs::__bmais("baddidioma" , "Añadir un idioma" );
    
    $b->visible = $ml;

    //~ $b->envia_ajax("onclick");
    

    return $b;
  }

  private static function __calcula_ml(int $s):bool {
/*
    $tcbd = new FS_cbd   ();
    $xcbd = new Xesta_cbd();
    
    $xs = XSite_obd::inicia_sitebd($xcbd, $tcbd->bd, $s);
    
    $s2 = $xs->atr("id_site")->valor;
    
    $r = $xcbd->consulta("select multilinguaxe from v_servizo_plan where id_site = {$s2}");
    
    if (!$_r = $r->next()) return false;
*/
    
    return true;
  }

}


//**************************************


final class CML_idioma extends Componente{
  const ptw = "ptw/paneis/pcontrol/idiomas/cml_idioma.html";

  private $id_site     = null;
  private $id_idioma_0 = null;
  private $addIdioma   = true;

  public function __construct($id_site) {
    parent::__construct("cml_idioma", self::ptw);
    
    $this->visible = false;
    $this->id_site = $id_site;

    $this->pon_obxeto( Panel_fs::__baceptar () );
    $this->pon_obxeto( Panel_fs::__bcancelar() );
    
    $this->obxeto("bCancelar")->envia_AJAX("onclick");
  }

  public function abrir($id_idioma_0, bool $addIdioma) {
    $this->visible     = true;
    $this->id_idioma_0 = $id_idioma_0;
    $this->addIdioma   = $addIdioma;

    $this->pon_obxeto( self::__sidioma($this->id_site) );
  }

  public function operacion(EstadoHTTP $e) {
    if (!$this->visible) return null;

    if ($this->obxeto("bCancelar")->control_evento()) return $this->operacion_bCancelar($e);
    if ($this->obxeto("bAceptar" )->control_evento()) return $this->operacion_bAceptar ($e);


    return parent::operacion($e);
  }

  private function operacion_bAceptar(Epcontrol $e) {
    $cbd = new FS_cbd();

    $cbd->transaccion();

    if ($this->addIdioma) {
      if (!$this->__pon_idioma($cbd)) {
        $cbd->rollback();

        return $e;
      }
    }
    else {
      if (!$this->__cambia_idioma($cbd)) {
        $cbd->rollback();

        return $e;
      }
    }

    //~ $cbd->rollback();
    $cbd->commit();

    if ($e->obxeto("xpax") != null) $e->pon_obxeto( new XPaxinas($e->usuario()) );


    return $this->operacion_bCancelar($e);
  }

  private function operacion_bCancelar(Epcontrol $e) {
    $this->visible   = false;
    $this->addIdioma = true;

    $this->pai->preparar_saida($e->ajax());
    
    return $e;
  }

  private function __pon_idioma(FS_cbd $cbd) {
    $id_site   = $this->id_site;
    $id_idioma = $this->obxeto("sidioma")->valor();

    $ml = new MLsite_obd();

    $ml->atr("id_site"  )->valor = $id_site;
    $ml->atr("id_idioma")->valor = $id_idioma;
    $ml->atr("activo"   )->valor = 1;


    if (!$ml->insert($cbd)) return false;

    
    if ( MLsite_obd::activado($this->id_site, $cbd) ) //* esta comprobación non debería ser necesaria, porque todos os sites teñen, polo menos, un idioma asignado. 
      return self::__copia_paxinas_1($cbd, $id_site, $id_idioma);
    

    return self::__copia_paxinas_0($cbd, $id_site, $id_idioma);
  }

  private function __cambia_idioma(FS_cbd $cbd) {
    $id_idioma = $this->obxeto("sidioma")->valor();

//~ echo "11111111111111111::$this->id_site, $this->id_idioma_0<br>";

    //* cambiamos ml_site
    $ml_s = MLsite_obd::inicia($cbd, $this->id_site, $this->id_idioma_0);

    $ml_s->atr("id_idioma")->valor = $id_idioma;

    if (!$ml_s->update($cbd)) return false;

    //* cambiamos ml_paxina
    $sql = "select a.id_ml
              from v_ml_paxina a inner join v_site_paxina b on (a.id_paxina = b.id_paxina)
             where b.id_site = {$this->id_site} and b.id_idioma = '{$this->id_idioma_0}'";

    $r = $cbd->consulta($sql);

    while ($a = $r->next()):
      $ml_p = MLpaxina_obd::inicia($cbd, $a['id_ml']);

      $ml_p->atr("id_idioma")->valor = $id_idioma;

      if (!$ml_p->update($cbd)) return false;

    endwhile;


    return true;
  }

  private static function __copia_paxinas_0($cbd, $id_site, $id_idioma) {
    $r = $cbd->consulta("select id_paxina from v_site_paxina where id_site = {$id_site}");

    while ($a = $r->next()) {
      $ml = new MLpaxina_obd($id_idioma, $a['id_paxina']);

      if (!$ml->insert($cbd)) return false;
    }


    return true;
  }

  private static function __copia_paxinas_1($cbd, $id_site, $id_idioma) {
    $id_idioma_predet = MLsite_obd::idioma_predeterminado($id_site, $cbd);

    $sql = "select a.id_paxina, a.tipo, b.id_sincro
              from v_site_paxina a inner join v_ml_paxina b on (a.id_paxina = b.id_paxina and
                                                                a.id_site   = {$id_site} and
                                                                a.id_idioma = '{$id_idioma_predet}' and
                                                                a.tipo      in ('libre', 'album', 'novas')
                                                                )";

    $r = $cbd->consulta($sql);

    while ($a = $r->next()) {
      $pax = Paxina_obd::inicia($cbd, $a['id_paxina'], $a['tipo']);

      if ($a['tipo'] == 'novas') if (!$pax->__indice()) continue;

      if (!$pax->duplicar($cbd, $id_idioma)) return false;
    }


    return true;
  }

  private static function __sidioma($id_site = null) {
    $_o = []; if ($id_site != null) $_o = MLsite_obd::__a_idiomas($id_site, 2);
    
    
    return new Select("sidioma", $_o);
  }
}


//**************************************


final class CML_lista extends FS_lista {
  public $id_site;
  
  public $_pos = [];


  public function __construct($id_site) {
    parent::__construct("cmll", new Ehtml_cmll());

    $this->id_site    = $id_site;

    $this->selectfrom = "select a.id_idioma, a.nome, b.pos, b.activo
                           from idioma a inner join v_ml_site b on (a.id_idioma = b.id_idioma)";
    $this->where      = "b.id_site = {$id_site}";
    $this->orderby    = "b.pos ASC";
    
    
    $this->pon_obxeto( new Hidden("hpos") );
  }

  public function operacion(EstadoHTTP $e) {
    $evento = $e->evento();

//~ echo $evento->html(); 

    $id_idioma = $evento->subnome(0);
    
    if ($this->control_fslevento($evento, "chactivo")) return $this->operacion_chactivo   ($e                   );
    if ($this->control_fslevento($evento, "bborrar" )) return $this->operacion_supidioma  ($e, $id_idioma       );
    if ($this->control_fslevento($evento, "spos"    )) return $this->operacion_spos       ($e, $id_idioma       );
    if ($this->control_fslevento($evento, "bcambiar")) return $this->pai->operacion_idioma($e, $id_idioma, false);

    return parent::operacion($e);
  }

  public function operacion_spos(Epcontrol $e, $l_ini) {
    $l_fin = $this->obxeto("hpos")->valor();
    
    
    $_pos_fin = $this->operacion_spos_0( $l_ini, $l_fin );

    $cbd = new FS_cbd();
    
    $cbd->transaccion();
    
    if ( $this->operacion_spos_1($cbd, $_pos_fin) ) {
      //~ $cbd->rollback();
      $cbd->commit();
      
      if ($e->obxeto("xpax") != null) $e->pon_obxeto( new XPaxinas($e->usuario()) );
      
      $e->post_msx("Se cambió la prioridad de los idiomas correctamente.", "m");
    }
    else {
      $cbd->rollback();
      
      $e->post_msx("Sucedió un error al actualizar la BD.");
    }
    
    $this->preparar_saida($e->ajax());

    return $e;
  }

  public function operacion_supidioma(Epcontrol $e, $id_idioma) {
    $cbd = new FS_cbd();

    $cbd->transaccion();

    if ( !$this->operacion_supidioma_0($cbd, $id_idioma) ) {
      $cbd->rollback();

      return $e;
    }

    //~ $cbd->rollback();
    $cbd->commit();

    if ($e->obxeto("xpax") != null) $e->pon_obxeto( new XPaxinas($e->usuario()) );


    return $e;
  }

  private function operacion_spos_0(string $l_ini, string $l_fin):array {
    $_pos_ini = $this->_pos;
    
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

  private function operacion_spos_1(FS_cbd $cbd, array $_pos_fin):bool {
    $p_j = 1;
    foreach ($_pos_fin as $l => $x) {
      
      $sql = "update ml_site a inner join v_ml_site b on (a.id_ml = b.id_ml)
                set a.pos = $p_j
              where b.id_site = {$this->id_site} and b.id_idioma = '{$l}'";
              
      if ( !$cbd->executa($sql) ) return false;
      
      $p_j++;
    }
    
    return true;
  }


  private function operacion_supidioma_0(FS_cbd $cbd, $id_idioma):bool {
    //* borra ML.
    $ml_site = MLsite_obd::inicia($cbd, $this->id_site, $id_idioma);

    if (!$ml_site->delete($cbd)) return false;


    //* renumera posicións do idioma.
    unset( $this->_pos[$id_idioma] );
    
    if ( !$this->operacion_spos_1($cbd, $this->_pos) ) return false;
    
    
    //* borra seccións do idioma.
    $ms = Menu_site_obd::inicia($cbd, $this->id_site);

    $a_oms = $ms->a_oms($cbd, $id_idioma, false);

    if (count($a_oms) == 0) return true;


    foreach ($a_oms as $oms) {
      if (!$oms->delete($cbd, $id_idioma, false, false)) return false;
    }


    return true;
  }


  private function operacion_chactivo(Epcontrol $e) {
    $cbd = new FS_cbd();
    
    $id_idioma = $e->evento()->subnome(0);
        
    $mls = MLsite_obd::inicia($cbd, $this->id_site, $id_idioma);
    
    $mls->atr("activo")->valor = ( $this->obxeto("chactivo", $id_idioma)->valor() )?"1":"0";


//~ echo "<pre>" . print_r($mls->a_resumo(), 1) . "</pre>";


    $cbd->transaccion();
    
    if ($mls->update($cbd)) {
      $cbd->commit();

      $m_0 = ($mls->atr("activo")->valor == 1)?"ACTIVADO":"DESACTIVADO";
      $m_1 = "El idioma: '" . $mls->atr("nome")->valor . "', ha sido <b>{$m_0}</b> correctamente.";

      $e->post_msx($m_1, "m");
    }
    else {
      $e->post_msx("Sucedió un error al actualizar la BD");

      $cbd->rollback();
    }
    
    
    $e->ajax()->pon_ok();
    
    
    return $e;
  }
}

//---------------------------------

final class Ehtml_cmll extends FS_ehtml {
  public function __construct() {
    parent::__construct();

    $this->class_table = "lista_00";
    $this->style_table = "border: 1px #aaa solid;";
    $this->align       = "center";
    $this->width       = "99%";
    $this->cellspacing = 0;
    $this->cellpadding = 2;
    $this->border      = 0;
  }

  protected function cabeceira($df = null) {
    $this->xestor->obxeto("hpos")->post(null);
    
    $this->xestor->_pos = MLsite_obd::_pos( $this->xestor->id_site );
    
    return "<tr>
              <th style='width:1em;'>Activo</th>
              <th colspan=2 style='width:17em;'>Idioma</th>
              <th style='text-align: right;padding-right: 1.7em;'>Prioridad</th>
              <th style='text-align: right;padding-right: 1.7em;'>Secciones</th>
              <th width=37px></th>
            </tr>";
  }

  protected function linha_detalle($df, $f) {
    $id_idioma = $f['id_idioma'];
    $chactivo  = self::__chactivo    ($this, $id_idioma, $f['activo']);
    $secciones = self::__ct_secciones($this, $id_idioma              );

    return "<tr >
              <td align=center>{$chactivo}</td>
              <td><img src='imx/bandeiras/{$id_idioma}.png' width='27px' /></td>
              <td>{$f['nome']}</td>
              <td style='text-align: right;padding-right: 1.7em;'>" . self::__spos($this, $f) . "</td>
              <td style='text-align: right;padding-right: 1.7em;'>{$secciones}</td>
              <td align=center>" . self::__bsup($this, $f) . "</td>
            </tr>";
  }

  protected function totais() {
    return "<tr>
              <td colspan=9>" . $this->xestor->obxeto("hpos")->html() . "</td>
            </tr>";
  }

  private static function __chactivo(Ehtml_cmll $ehtml, $id_idioma, $activo) {
    $xestor = $ehtml->xestor;
    
    if (($ch = $xestor->obxeto("chactivo")) != null) return $ch->html();
    
    $ch = new Checkbox("chactivo", $activo);
    
    $ch->envia_ajax("onclick");
    //~ $ch->envia_submit("onclick");
    
    
    $xestor->pon_obxeto($ch, $id_idioma);
    

    return $ch->html();
  }

  private static function __ct_secciones(Ehtml_cmll $ehtml, $id_idioma) {
    $xestor = $ehtml->xestor;
    
    $cbd = new FS_cbd();
    
    $r = $cbd->consulta("select count(*) as ct from v_site_paxina where id_site = {$xestor->id_site} and id_idioma = '{$id_idioma}'");
    
    if (!$_r = $r->next()) return "0";
    

    return number_format( $_r["ct"], 0, ",", "." );
  }

  private static function __bsup(Ehtml_cmll $ehtml, $f) {
    if ($ehtml->xestor->readonly) return "";

    if ($ehtml->xestor->__count() == 1)
      $b = Panel_fs::__bsup("bcambiar", "Cambiar", "Cambiar idioma");
    else {
      $b = Panel_fs::__bsup("bborrar" , "Borrar" , "Borrar idioma" );

      $b->envia_SUBMIT("onclick", "¿ Deseas eliminar el idioma {$f['nome']} ?");
    }
    

    return $ehtml->__fslControl($b, $f['id_idioma'])->html();
  }

  private static function __spos(Ehtml_cmll $ehtml, $f) {
    $s = new Select("spos", $ehtml->xestor->_pos);
    
    $s->post( $f["id_idioma"] );
    
    $s->pon_eventos("onchange", "cml_spos_change(this)");
    
    $s->readonly = $ehtml->xestor->readonly;
     

    return $ehtml->__fslControl($s, $f['id_idioma'])->html();
  }
}

