<?php

/** Permite escribir tablas html con diferentes controles
 *
 * @package tilia\vista\componentes\clista
 */

class CLista extends Componente {
  public $selectfrom = "select now()";
  public $where      = "1=1";
  public $orderby    = null;
  public $groupby    = null;
  public $limit_0    = null;
  public $limit_f    = null;

  protected $scbd = null; //* nome da clase de tipo Conexion_bd

  protected $ehtml = null;

  protected $count_aux = -1;

  protected array $_chMas     = [];    //* interface checkbox masivo.
  protected bool  $_chMas_neg = false; //* control negativo.
  


  public function __construct($nome, $scbd, CLista_ehtml $ehtml, $ptw = null) {
    parent::__construct($nome, $ptw);

    $this->scbd = $scbd;

    $this->__ehtml($ehtml);
    
    $this->pon_obxeto( self::chMas_0() ); 

    $this->pon_obxeto(new IllaAJAX("lista"));

    $this->sup_obxeto("illa");
  }

  public function declara_css() {
    return array(Tilia::home() . "/css/clista.css");
  }

  public function readonly($b = true) {
    $this->readonly = $b;

    $sublistas = $this->obxetos("sublista");

    if (!is_array($sublistas)) return;
    if (count($sublistas) == 0) return;

    foreach($sublistas as $subfl) $subfl->readonly($b);

    return null;
  }

  final public function sql_vista($limit = true) {
    if ($this->selectfrom == null) die("CLista.sql_vista(), (this.selectfrom == null)");

    $w = ""; $g = ""; $o = ""; $l = "";

    if ($this->where   != null) $w = " where {$this->where}";
    if ($this->groupby != null) $g = " group by {$this->groupby}";
    if ($this->orderby != null) $o = " order by {$this->orderby}";

    if ($limit)
      if ($this->limit_f != null) {
        if ($this->limit_0 == null) $this->limit_0 = "0";

        $l  = " limit {$this->limit_0}, {$this->limit_f}";
      }

    //~ echo "{$this->selectfrom}{$w}{$g}{$o}{$l}<br />";

    return "{$this->selectfrom}{$w}{$g}{$o}{$l}";
  }

  public function _orderby() {
    //* PRECOND: formato($this->orderby): "campo_0 {ASC, DESC}[,campo_1]"
    
    if ($this->orderby == null) return array(null, null);
    
    if (strpos($this->orderby, " ") === FALSE) return array(null, null);

    list($campo, $tipo) = explode(" ", $this->orderby);

    if (strpos($tipo, ",") !== false) list($tipo, $x) = explode(",", $tipo);


    return array($campo, $tipo);
  }
  
  public function post_chMas(array $_chMas = []) {
    $this->_chMas  = $_chMas;
    
    if ($_chMas == []) $this->obxeto("chMas_0")->post( false );
  }
  
  public function post_where($where = null) {
    $this->post_chMas( [] );
    
    $this->limit_0 = null;

    $this->where  = "";

    if ($where == null) return;

    $this->where = $where;
  }
  
  public function __i() {
    if ($this->limit_f == null) return 1;

    return ceil($this->limit_0 / $this->limit_f) + 1;
  }

  public function __t() {
    if ($this->limit_f == null) return 1;

    return ceil($this->__count() / $this->limit_f);
  }

  public function __count() {
    if ($this->count_aux != -1) return $this->count_aux;

    $this->count_aux = $this->__count_sql();


    return $this->count_aux;
  }

  public function __count_sql() {
    $sql = "select count(*) as ct from (" . $this->sql_vista(false) . ") xcount";

    $cbd = new $this->scbd();

    $r = $cbd->consulta($sql);

    $a = $r->next();

        if ( !isset($a['ct']) ) $a['ct'] = "0";
    elseif ( $a['ct'] == null ) $a['ct'] = "0";

    return $a['ct'];
  }

  public function __count_sublistas($abertas = false) {
    $a_sl = $this->sublista();

    if (!$abertas) return count($a_sl);

    if (count($a_sl) == 0) return 0;

    $ct = 0;
    foreach($a_sl as $sl) if ($sl->visible) $ct++;

    return $ct;
  }
  
  public function masivo($k):bool {
    if ( !isset($this->_chMas[$k]) )
      if ( $this->obxeto("chMas_0")->valor() ) $this->_chMas[$k] = 1;


    if ( !isset($this->_chMas[$k]) ) return 0;
    
    
    return $this->_chMas[$k];
  }
  
  public function _masivo():array {
    //~ if ( $this->obxeto("chMas_0")->valor() ) return [ true, [] ];
    
    $_chMas = []; foreach ( $this->_chMas as $k=>$v ) if ($v == 1) $_chMas[$k] = $v;


    return [ false, $_chMas ];
  }

  public function __ehtml(CLista_ehtml $ehtml = null) {
    if ($ehtml == null) return $this->ehtml;

    $ehtml->pon_xestor($this);

    $this->ehtml = $ehtml;

    return $this->ehtml;
  }

  public function operacion(EstadoHTTP $e) {
    if ( ($e_aux = $this->operacion_chMas_0($e)) != null ) return $e_aux;

    $evento = $e->evento();

//~ echo "CLista::" . $evento->html();
    
    if ($this->control_fslevento($evento, "chMas")) return $this->operacion_chMas_i($e, $evento->subnome(0));

    if ($this->control_fslevento($evento, "bramacab_mais"))  return $this->op_rama_cab($e, true);
    if ($this->control_fslevento($evento, "bramacab_menos")) return $this->op_rama_cab($e, false);

    if ($this->control_fslevento($evento, "brama_mais"))  return $this->op_rama($e, $evento->subnome(0));
    if ($this->control_fslevento($evento, "brama_menos")) return $this->op_rama($e, $evento->subnome(0));

    if (($e_aux = $this->sublista_operacion($e)) != null) return $e_aux;

    if ($this->control_fslevento($evento, "orderby")) return $this->operacion_ordenar_0($e, $evento->subnome(0));

    if ($this->control_fslevento($evento, "lpax_a")) return $this->operacion_lpax($e, -1);
    if ($this->control_fslevento($evento, "lpax_s")) return $this->operacion_lpax($e, 1);

    if (($e_aux = $this->operacion_ti($e)) != null) return $e_aux;

    return null;
  }

  protected function operacion_chMas_i(EstadoHTTP $e, $k) {
    $this->_chMas_neg = true;
    
    if ( !isset($this->_chMas[$k]) ) 
      $this->_chMas[$k] = 1;
    else
      $this->_chMas[$k] = ($this->_chMas[$k] + 1) % 2;

    $e->ajax()->pon_ok();

    return $e;
  }

  protected function operacion_chMas_0(EstadoHTTP $e) {
    if (!$this->obxeto("chMas_0")->control_evento()) return null;

//~ echo "chMas_0::" . $this->obxeto("chMas_0")->valor() . "<br>";

//~ echo "chMas_neg::{$this->_chMas_neg}<br>";

    if ($this->_chMas_neg) {
      $this->obxeto("chMas_0")->post(true);
      
      foreach ($this->_chMas as $k=>$v) $this->_chMas[$k] = ($this->_chMas[$k] + 1) % 2;
    }
    
    $this->_chMas_neg = false;
    
    if (!$this->obxeto("chMas_0")->valor()) $this->post_chMas(); 

    $this->preparar_saida($e->ajax());

    return $e;
  }
  
  public function operacion_ordenar_0(EstadoHTTP $e, $campo, $tipo = null) {
    $this->operacion_ordenar($campo, $tipo);


    if ($e->evento()->ajax() == 1) $this->preparar_saida($e->ajax());

    return $e;
  }

  public function operacion_ordenar($campo, $tipo = null) {
    $this->post_chMas();    

   // list($campo_0, $campo_1) = explode(",", $campo);
    $campos = explode(",", $campo);
    
// Verificar si el índice 1 está definido antes de asignarlo
    $campo_0 = isset($campos[0]) ? $campos[0] : null;
    $campo_1 = isset($campos[1]) ? $campos[1] : null;

    list($o_campo, $o_tipo ) = $this->_orderby();

    $this->orderby = $campo_0;

        if ($campo_0  != $o_campo) $this->orderby .= " ASC";
    elseif ($tipo     != null    ) $this->orderby .= " {$tipo}";
    elseif ($o_tipo   == "ASC"   ) $this->orderby .= " DESC";
    else                           $this->orderby .= " ASC";

    if ($campo_1 != null) $this->orderby .= ",{$campo_1}";
  }

  public function operacion_lpax(EstadoHTTP $e, $suma = 1) {
    //* PRECOND: ($suma != 0)
    //* PRECOND: ($this->limit_f > 0)
    //* PRECOND: ($this->limit_0 + ($suma * $this->limit_f)) > 0


    $l_0 = ($suma * $this->limit_f);

    if (($this->limit_0 + $l_0) < 0) return $e;
    
    
    $this->post_chMas();    

    $this->limit_0 += $l_0;

    $this->preparar_saida($e->ajax());

    return $e;
  }

  public function operacion_ti(EstadoHTTP $e) {
    if (($ti = $this->obxeto("ti")) == null) return null;
    if (!$ti->control_evento())              return null;


    $ti = abs( Valida::numero($ti->valor()) );

    $i = $this->__i();
    $t = $this->__t();

    $suma = $ti - $i;


    if (($i + $suma) > $t) return $this->operacion_lpax($e, 0);
    if (($i + $suma) < 1 ) return $this->operacion_lpax($e, 0);


    return $this->operacion_lpax($e, $suma);
  }

  public function csv($separador = ";", $cualificadorTexto = "\"") {
    $cbd = new $this->scbd();

    return $cbd->csv($this->sql_vista(), $separador, $cualificadorTexto);
  }

  public function html():string {
    if (!$this->visible) return "";

    $this->preparar_saida();

    $html_aux = ($this->ptw != null)?$this->html00():$this->obxeto("lista")->html();

    $this->obxeto("lista")->post(null);

    return $html_aux;
  }

  public function xml($nome_raiz, $nome_rexistro) {
    $cbd = new $this->scbd();

    return $cbd->xml($this->sql_vista(), $nome_raiz, $nome_rexistro);
  }

  public function preparar_saida(Ajax $a = null) {
    if (($it = $this->__iterador()) == null) return $this->html_baleiro();
    
    $this->ehtml->pon_iterador($it);

    $this->count_aux = -1;

    $html = ($this->__count() == 0)?$this->html_baleiro():$this->ehtml->escribe();

    $this->obxeto("lista")->post($html);

    if ($a != null) {
      $a->pon($this->obxeto("lista"));

      $this->obxeto("lista")->post(null);
    }
  }

  final public function control_fslevento(EventoHTTP $evento, $orden) {
    $k_orde = $this->nome_completo() . Escritor_html::cnome . $orden;

    return ($evento->control_str($k_orde));
  }

  final public function adopta(Control $c, $subnome = null) {
    $this->pon_obxeto($c, $subnome);

    return $c;
  }

  final public function a_rama_check($a_checks = null) {
    $sublistas = $this->obxetos("sublista");

    if (count($sublistas) == 0) return null;

    $a_checks_aux = array();
    foreach($sublistas as $subfl) {
      list($x, $id_rama) = explode(Escritor_html::csubnome, $subfl->nome);

      $a_checks_aux[$id_rama] = $subfl->visible;

      if (!is_array($a_checks)) continue;

      $subfl->visible = $a_checks[$id_rama];
    }

    return $a_checks_aux;
  }

  public function borra_sublistas() {
    return $this->sup_obxeto("sublista");
  }

  final public function pon_sublista($id_rama) {
    if (($sublista = $this->sublista_prototipo($id_rama)) == null) return;

    $sublista->nome = "sublista";

    $this->pon_obxeto($sublista, $id_rama);
  }

  final public function sublista($id_rama = null) {
    if ($id_rama == null) return $this->obxetos("sublista");

    return $this->obxeto("sublista", $id_rama);
  }

  protected function sublista_operacion(EstadoHTTP $e) {
    $sublistas = $this->obxetos("sublista");

    //~ echo $e->evento()->html();

    if (!is_array($sublistas)) return null;

    foreach($sublistas as $subfl)
      if (($e_aux = $subfl->operacion($e)) != null) return $e_aux;

    return null;
  }

  public function sublista_prototipo($id_rama) {
    return null;
  }

  protected function __iterador() {
    $cbd = new $this->scbd();

    return $cbd->consulta($this->sql_vista());
  }

  protected function op_rama(EstadoHTTP $e, $id_rama) {
    $this->ehtml->rama_click($id_rama);

    $this->preparar_saida($e->ajax());

    return $e;
  }

  final protected function op_rama_cab(EstadoHTTP $e, $b = false) {
    $a_sl = $this->sublista();

    if (count($a_sl) == 0) return $e;

    foreach($a_sl as $sl) $sl->visible = $b;

    $this->preparar_saida($e->ajax());

    return $e;
  }

  final public function control_limits_sup() {
    $this->count_aux = -1;

    $ct = $this->__count();

//~ echo "(ct, l0, lf) = ({$ct}, {$this->limit_0}, {$this->limit_f})<br />";

    if ($this->limit_0 == 0) return;

    if ($ct >= ($this->limit_0 + $this->limit_f)) return;

    if ((($ct + 1) % $this->limit_f) == 1) $this->limit_0 -= $this->limit_f;

//~ echo "(ct, l0, lf) = ({$ct}, {$this->limit_0}, {$this->limit_f})<br />";
  }

  protected function html_baleiro() {
    if (!$this->visible) return "";

    return "";
    //~ return "<div>No se encontraron coincidencias.</div>";
  }

  private static function chMas_0($paxina = false) {
    $ch = new Checkbox("chMas_0", false, "");
    
    //~ $ch->envia_submit("onclick");
    $ch->envia_ajax("onclick");
  
    return $ch;
  }
}

