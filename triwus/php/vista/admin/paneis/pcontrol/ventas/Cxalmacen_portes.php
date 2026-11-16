<?php


final class Cxalmacen_portes extends Componente {
  const ptw_0 = "ptw/paneis/pcontrol/ventas/almacen/cxalmacen_ficha_portes_0.html";
  const ptw_1 = "ptw/paneis/pcontrol/ventas/almacen/cxalmacen_ficha_portes_1.html";

  public $admin = false;
  public $id_s  = null;  //* id_site.
  public $id_a  = null;  //* id_almacen en edición.

  public function __construct($id_site, $id_almacen) {
    parent::__construct("calmacenfportes");

    $this->visible = false;

//~ echo "Cxalmacen_portes::{$id_site}, {$id_almacen}<br>";

    if ($id_almacen > 1) {
      $this->admin = false;
      $this->ptw   = self::ptw_1;
    }
    else {
      $this->admin = true;
      $this->ptw   = self::ptw_0;
    }

    $this->pon_obxeto( new Param("psalmacen") );

    $this->pon_obxeto( self::__salmacen   ($id_site, $id_almacen) );
    $this->pon_obxeto( self::__sportestipo($id_site, $id_almacen) );

    $this->pon_obxeto( new Cxalmacen_portes_importe($id_site, $id_almacen) );
    $this->pon_obxeto( new Cxalmacen_portes_peso   ($id_site, $id_almacen) );
  }

  public function operacion(EstadoHTTP $e) {
    if ($this->obxeto("salmacen")->control_evento()) {
      $this->post_portes($this->obxeto("salmacen")->valor(), $a);

      $this->readonly();

      
      return $e;
    }
    
    if ($this->obxeto("sportestipo")->control_evento()) {
      $spt = $this->obxeto("sportestipo")->valor();

      $this->obxeto("calmacenfportes_importe")->visible = ($spt == "i");
      $this->obxeto("calmacenfportes_peso"   )->visible = ($spt == "p");

      $this->preparar_saida( $e->ajax() );
      
      return $e;
    }

    return null;
  }

  public function post_almacen(Almacen_obd $a) {
//~ echo "<pre>" . print_r($a->a_resumo(), true) . "</pre>";

    $id_ap      = $a->atr("id_almacen_portes")->valor;

    $this->id_s = $a->atr("id_site"   )->valor;
    $this->id_a = $a->atr("id_almacen")->valor;

    $this->obxeto("salmacen")->post( $id_ap );
    
    $this->obxeto("sportestipo")->post( $a->atr("portes_tipo")->valor );

    $this->obxeto("calmacenfportes_importe")->post_almacen($a);
    $this->obxeto("calmacenfportes_peso"   )->post_almacen($a);

    if ($a->atr("portes_tipo")->valor() == "i") {
      $this->obxeto("calmacenfportes_importe")->visible = true;
      $this->obxeto("calmacenfportes_peso"   )->visible = false;
    }
    else {
      $this->obxeto("calmacenfportes_importe")->visible = false;
      $this->obxeto("calmacenfportes_peso"   )->visible = true;      
    }

   
    $this->post_portes($id_ap, $a);


    $this->readonly();
  }

  public function completar_almacen(Almacen_obd $a) {
    $a->atr("id_almacen_portes")->valor = $this->obxeto("salmacen")->valor();
    
    $spt = $this->obxeto("sportestipo")->valor();

    $a->atr("portes_tipo")->valor = $spt;
    
    if ($spt == "i") {
      $a = $this->obxeto("calmacenfportes_importe")->completar_almacen($a);
    }
    elseif ($spt == "p") {
      $a = $this->obxeto("calmacenfportes_peso"   )->completar_almacen($a);

      $a->atr("finde")->valor = "0"; //* deshabilitamos opción sábado (TEMPORALMENTE)
    }
    

    return $a;
  }

  public function readonly($b = true) {
    //* non imos ter en conta o parámetro $b recibido.

    $b = ( $this->id_a != $this->obxeto("salmacen")->valor() );

    parent::readonly($b);

    $this->obxeto("salmacen")->readonly = !$this->admin;
  }
  
  public static function __check($id) {
    $s = new Checkbox($id);

    $s->title = "Activar/Desactivar destino";

    return $s;
  }

  public static function __sentregau($id, $title) {
    $_a1[ Almacen_obd::$_entregau[0] ] = "días";
    $_a1[ Almacen_obd::$_entregau[1] ] = "horas";    
    
    $t = new Select($id, $_a1);

    $t->title = $title;

    $t->clase_css("default" , "text");
    $t->clase_css("readonly", "text");

    return $t;
  }

  public static function __slimite_d($id, $title) {
    $_a0 = Almacen_obd::$_semana;

    
    $t = new Select($id, $_a0);

    $t->title = $title;

    $t->clase_css("default" , "text");
    $t->clase_css("readonly", "text");

    return $t;
  }

  public static function __slimite_h($id, $title) {
    $t = new Text($id);

    $t->type = "time";

    $t->title = $title;

    $t->style("default" , "width: 8em;");
    $t->style("readonly", "width: 8em;");

    $t->clase_css("default" , "text");
    $t->clase_css("readonly", "text");

    return $t;
  }

  public static function __number($id, $titulo = null) {
    $t = Panel_fs::__text($id);

    $t->title = $titulo;
    $t->size  = 2;

    $t->style("default" , "text-align: right; max-width: 100%;");
    $t->style("readonly", "text-align: right; max-width: 100%;");

    return $t;
  }

  public static function __postais() {
    $id     = "portes_postais";
    $titulo = null;
    
    $t = new Textarea($id);

    $t->title = $titulo;

    $t->style("default" , "width: 100%;");
    $t->style("readonly", "width: 100%;");

    return $t;
  }
  
  private function post_portes($id_ap, Almacen_obd $a = null) {
    if ($this->id_a != $id_ap) {
      $a = Almacen_obd::inicia(new FS_cbd(), $this->id_s, $id_ap); //* lemos o almacén derivado, para escribir os seus portes.

      $this->obxeto("psalmacen")->post( "Portes agrupados con el almacén: <b>" . $a->atr("nome")->valor . "</b>." );
    }
    else
      $this->obxeto("psalmacen")->post( "" );

/*
    if ($a == null) return;


    $this->rexion_post("p"  , $a);
    $this->rexion_post("finde"  , $a);
    $this->rexion_post("b"  , $a);
    $this->rexion_post("c35", $a);
    $this->rexion_post("c38", $a);
    $this->rexion_post("cee", $a);

    $this->obxeto("chfinde"   )->post( $a->atr("finde"   )->valor );
    $this->obxeto("chbaleares")->post( $a->atr("baleares")->valor );
    $this->obxeto("chcanarias")->post( $a->atr("canarias")->valor );
    $this->obxeto("chcee"     )->post( $a->atr("cee"     )->valor );
*/
  }

  private static function __salmacen($id_site, $id_almacen) {
    $s = new Select("salmacen", Almacen_obd::_almacen($id_site, null));

    $s->style("default", "width: 99%;");
    
    $s->sup_opcion("**");

    $s->envia_submit("onchange");
    

    return $s;
  }

  private static function __sportestipo($id_site, $id_almacen) {
    $s = new Select("sportestipo", array("i"=>"Importes", "p"=>"Pesos"));

    $s->style("default", "width: 99%;");
    
    //~ $s->envia_submit("onchange");
    $s->envia_ajax("onchange");
    

    return $s;
  }
}

//***************************************

final class Cxalmacen_portes_importe extends Componente {
  const ptw_0 = "ptw/paneis/pcontrol/ventas/almacen/cxalmacen_ficha_portes_importe.html";

  public function __construct($id_site, $id_almacen) {
    parent::__construct("calmacenfportes_importe", self::ptw_0);

    //* local
    $this->pon_obxeto(Cxalmacen_portes::__postais());

    $this->pon_obxeto(Cxalmacen_portes::__check("chlocal"));

    $this->rexion_inicia("l");

    //* península
    $this->pon_obxeto(Cxalmacen_portes::__check("chpeninsula"));

    $this->rexion_inicia("p");

    //* península sábado
    $this->pon_obxeto(Cxalmacen_portes::__check("chfinde"));

    $this->rexion_inicia("finde");

    //* baleares
    $this->pon_obxeto(Cxalmacen_portes::__check("chbaleares"));

    $this->rexion_inicia("b");

    //* canarias
    $this->pon_obxeto(Cxalmacen_portes::__check("chcanarias"));
    
    $this->rexion_inicia("c35");
    $this->rexion_inicia("c38");

    //* outros
    $this->pon_obxeto(Cxalmacen_portes::__check("chcee"));
    
    $this->rexion_inicia("cee");
  }

  public function post_almacen(Almacen_obd $a) {
    $this->post_portes($a);
  }

  public function completar_almacen(Almacen_obd $a) {
    $this->rexion_completar("l"     , $a);
    $this->rexion_completar("finde" , $a);
    $this->rexion_completar("l"     , $a);
    $this->rexion_completar("p"     , $a);
    $this->rexion_completar("b"     , $a);
    $this->rexion_completar("c35"   , $a);
    $this->rexion_completar("c38"   , $a);
    $this->rexion_completar("cee"   , $a);
    
    $a->atr("portes_postais")->valor = $this->obxeto("portes_postais")->valor();
    
    $a->atr("finde"    )->valor = ($this->obxeto("chfinde"    )->valor())?"1":"0";
    $a->atr("local"    )->valor = ($this->obxeto("chlocal"    )->valor())?"1":"0";
    $a->atr("peninsula")->valor = ($this->obxeto("chpeninsula")->valor())?"1":"0";
    $a->atr("baleares" )->valor = ($this->obxeto("chbaleares" )->valor())?"1":"0";
    $a->atr("canarias" )->valor = ($this->obxeto("chcanarias" )->valor())?"1":"0";
    $a->atr("cee"      )->valor = ($this->obxeto("chcee"      )->valor())?"1":"0";

    return $a;
  }

  private function post_portes(Almacen_obd $a = null) {
    if ($a == null) return;

    $this->obxeto("portes_postais")->post( $a->atr("portes_postais")->valor );

    $this->obxeto("chfinde"    )->post( $a->atr("finde"    )->valor );
    $this->obxeto("chlocal"    )->post( $a->atr("local"    )->valor );
    $this->obxeto("chpeninsula")->post( $a->atr("peninsula")->valor );
    $this->obxeto("chbaleares" )->post( $a->atr("baleares" )->valor );
    $this->obxeto("chcanarias" )->post( $a->atr("canarias" )->valor );
    $this->obxeto("chcee"      )->post( $a->atr("cee"      )->valor );

    $this->rexion_post("l"  , $a);
    $this->rexion_post("p"  , $a);
    $this->rexion_post("finde"  , $a);
    $this->rexion_post("b"  , $a);
    $this->rexion_post("c35", $a);
    $this->rexion_post("c38", $a);
    $this->rexion_post("cee", $a);
  }

  private function rexion_inicia($id) {
    $this->pon_obxeto(Cxalmacen_portes::__number   ("portes_gratis_{$id}"  , "Portes gratis"));
    $this->pon_obxeto(Cxalmacen_portes::__number   ("portes_gratis_{$id}_2", "Portes gratis"));
    $this->pon_obxeto(Cxalmacen_portes::__number   ("portes_{$id}"         , "Precio de los portes"));
    $this->pon_obxeto(Cxalmacen_portes::__number   ("portes_{$id}_2"       , "Precio de los portes_2"));
    $this->pon_obxeto(Cxalmacen_portes::__number   ("portes_{$id}_3"       , "Precio de los portes_2"));
    $this->pon_obxeto(Cxalmacen_portes::__number   ("entrega_{$id}"        , "Tiempo de entrega"));
    $this->pon_obxeto(Cxalmacen_portes::__sentregau("entrega_u_{$id}"      , "Unidades de tiempo de entrega"));

    if ($id == "finde") {
      $this->pon_obxeto(Cxalmacen_portes::__slimite_d("limite_{$id}_d", "Límite semanal (Día)"));
      $this->pon_obxeto(Cxalmacen_portes::__slimite_h("limite_{$id}_h", "Límite semanal (Hora)"));
    }
  } 

  private function rexion_post($id, Almacen_obd $a) {

    $this->obxeto("portes_gratis_{$id}"  )->post( $a->atr("portes_gratis_{$id}"  )->valor );
    $this->obxeto("portes_gratis_{$id}_2")->post( $a->atr("portes_gratis_{$id}_2")->valor );
    $this->obxeto("portes_{$id}"         )->post( $a->atr("portes_{$id}"         )->valor );
    $this->obxeto("portes_{$id}_2"       )->post( $a->atr("portes_{$id}_2"       )->valor );
    $this->obxeto("portes_{$id}_3"       )->post( $a->atr("portes_{$id}_3"       )->valor );
    $this->obxeto("entrega_{$id}"        )->post( $a->atr("entrega_{$id}"        )->valor );
    $this->obxeto("entrega_u_{$id}"      )->post( $a->atr("entrega_u_{$id}"      )->valor );

    if ($id == "finde") {
      $this->obxeto("limite_{$id}_d")->post( $a->atr("limite_{$id}_d")->valor );
      $this->obxeto("limite_{$id}_h")->post( $a->atr("limite_{$id}_h")->valor );
    }
  } 

  private function rexion_completar($id, Almacen_obd $a) {
    $a->atr("portes_gratis_{$id}"  )->valor = $this->obxeto("portes_gratis_{$id}"   )->valor();
    $a->atr("portes_gratis_{$id}_2")->valor = $this->obxeto("portes_gratis_{$id}_2" )->valor();
    $a->atr("portes_{$id}"         )->valor = $this->obxeto("portes_{$id}"          )->valor();
    $a->atr("portes_{$id}_2"       )->valor = $this->obxeto("portes_{$id}_2"        )->valor();
    $a->atr("portes_{$id}_3"       )->valor = $this->obxeto("portes_{$id}_3"        )->valor();
    $a->atr("entrega_{$id}"        )->valor = round($this->obxeto("entrega_{$id}"   )->valor());
    $a->atr("entrega_u_{$id}"      )->valor = $this->obxeto("entrega_u_{$id}"       )->valor();

    if ($id == "finde") {
      if (($h = $this->obxeto("limite_{$id}_h")->valor()) == null) $h = "00:00";
      
      $a->atr("limite_{$id}_d")->valor = $this->obxeto("limite_{$id}_d")->valor();
      $a->atr("limite_{$id}_h")->valor = $h;
    }

    return $a;
  } 
}

//***************************************

final class Cxalmacen_portes_peso extends Componente {
  const ptw_0 = "ptw/paneis/pcontrol/ventas/almacen/cxalmacen_ficha_portes_peso.html";

  public function __construct($id_site, $id_almacen) {
    parent::__construct("calmacenfportes_peso", self::ptw_0);

    //* local
    $this->pon_obxeto(Cxalmacen_portes::__postais());

    $this->pon_obxeto(Cxalmacen_portes::__check("chlocal"));

    $this->rexion_inicia("l");

    //* península
    $this->pon_obxeto(Cxalmacen_portes::__check("chpeninsula"));

    $this->rexion_inicia("p");

    //* península sábado
    $this->pon_obxeto(Cxalmacen_portes::__check("chfinde"));

    $this->rexion_inicia("finde");

    //* baleares
    $this->pon_obxeto(Cxalmacen_portes::__check("chbaleares"));

    $this->rexion_inicia("b");

    //* canarias
    $this->pon_obxeto(Cxalmacen_portes::__check("chcanarias"));
    
    $this->rexion_inicia("c35");
    $this->rexion_inicia("c38");

    //* outros
    $this->pon_obxeto(Cxalmacen_portes::__check("chcee"));
    
    $this->rexion_inicia("cee");
  }

  public function post_almacen(Almacen_obd $a) {
    $this->post_portes($a);
  }

  public function completar_almacen(Almacen_obd $a) {
    //~ $this->rexion_completar("finde", $a);
    $this->rexion_completar("l"    , $a);
    $this->rexion_completar("p"    , $a);
    $this->rexion_completar("b"    , $a);
    $this->rexion_completar("c35"  , $a);
    $this->rexion_completar("c38"  , $a);
    $this->rexion_completar("cee"  , $a);

  
    $a->atr("portes_postais")->valor = $this->obxeto("portes_postais")->valor();
    
    $a->atr("finde"    )->valor = "0";
    $a->atr("local"    )->valor = ($this->obxeto("chlocal"    )->valor())?"1":"0";
    $a->atr("peninsula")->valor = ($this->obxeto("chpeninsula")->valor())?"1":"0";
    $a->atr("baleares" )->valor = ($this->obxeto("chbaleares" )->valor())?"1":"0";
    $a->atr("canarias" )->valor = ($this->obxeto("chcanarias" )->valor())?"1":"0";
    $a->atr("cee"      )->valor = ($this->obxeto("chcee"      )->valor())?"1":"0";


    return $a;
  }

  private function post_portes(Almacen_obd $a = null) {
    if ($a == null) return;

    $this->obxeto("portes_postais")->post( $a->atr("portes_postais")->valor );

    //~ $this->obxeto("chfinde"    )->post( $a->atr("finde"    )->valor );
    $this->obxeto("chlocal"    )->post( $a->atr("local"    )->valor );
    $this->obxeto("chpeninsula")->post( $a->atr("peninsula")->valor );
    $this->obxeto("chbaleares" )->post( $a->atr("baleares" )->valor );
    $this->obxeto("chcanarias" )->post( $a->atr("canarias" )->valor );
    $this->obxeto("chcee"      )->post( $a->atr("cee"      )->valor );


    //~ $this->rexion_post("finde"  , $a);
    $this->rexion_post("l"  , $a);
    $this->rexion_post("p"  , $a);
    $this->rexion_post("b"  , $a);
    $this->rexion_post("c35", $a);
    $this->rexion_post("c38", $a);
    $this->rexion_post("cee", $a);
  }

  private function rexion_inicia($id) {
    for ($i = 1; $i < 22; $i++)
      $this->pon_obxeto(Cxalmacen_portes::__number("pesos_{$id}_{$i}", ""));

    /*
    $this->pon_obxeto(Cxalmacen_portes::__number   ("entrega_{$id}"        , "Tiempo de entrega"));
    $this->pon_obxeto(Cxalmacen_portes::__sentregau("entrega_u_{$id}"      , "Unidades de tiempo de entrega"));

    if ($id == "finde") {
      $this->pon_obxeto(Cxalmacen_portes::__slimite_d("limite_{$id}_d", "Límite semanal (Día)"));
      $this->pon_obxeto(Cxalmacen_portes::__slimite_h("limite_{$id}_h", "Límite semanal (Hora)"));
    }
    */
  } 

  private function rexion_post($id, Almacen_obd $a) {
    $_pesos = unserialize( $a->atr("pesos_{$id}")->valor );

//~ echo "rexion_post: <pre>" . print_r($_pesos, 1) . "</pre>";

    if ( !is_array($_pesos) ) return;
    
    for ($i = 1; $i < 22; $i++)
      $this->obxeto("pesos_{$id}_{$i}")->post( $_pesos[$i] );
    
    /*
    $this->obxeto("entrega_{$id}"        )->post( $a->atr("entrega_{$id}"        )->valor );
    $this->obxeto("entrega_u_{$id}"      )->post( $a->atr("entrega_u_{$id}"      )->valor );

    if ($id == "finde") {
      $this->obxeto("limite_{$id}_d")->post( $a->atr("limite_{$id}_d")->valor );
      $this->obxeto("limite_{$id}_h")->post( $a->atr("limite_{$id}_h")->valor );
    }
    */
  } 

  private function rexion_completar($id, Almacen_obd $a) {
    $_pesos = array();

    for ($i = 1; $i < 22; $i++)
      $_pesos[$i] = Valida::numero( $this->obxeto("pesos_{$id}_{$i}")->valor(), 2);

//~ echo "rexion_completar: <pre>" . print_r($_pesos, 1) . "</pre>";

    $a->atr("pesos_{$id}")->valor = serialize( $_pesos );

    /*
    $a->atr("entrega_{$id}"        )->valor = round($this->obxeto("entrega_{$id}"   )->valor());
    $a->atr("entrega_u_{$id}"      )->valor = $this->obxeto("entrega_u_{$id}"       )->valor();

    if ($id == "finde") {
      if (($h = $this->obxeto("limite_{$id}_h")->valor()) == null) $h = "00:00";
      
      $a->atr("limite_{$id}_d")->valor = $this->obxeto("limite_{$id}_d")->valor();
      $a->atr("limite_{$id}_h")->valor = $h;
    }
    */

    return $a;
  }
}
