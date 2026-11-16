<?php

abstract class Cxarticulos_masivo extends Componente {
  protected function __construct() {
    parent::__construct("cxam");

    $this->ptw = $this->ptw();

    $this->pon_obxeto(Panel_fs::__baceptar());
    $this->pon_obxeto(Panel_fs::__bcancelar());


    $this->obxeto("bCancelar")->envia_ajax("onclick");
    $this->obxeto("bAceptar" )->envia_ajax("onclick");
  }

  abstract protected function ptw();
  abstract protected function bAceptar_exec  (Efs_admin $e);
  abstract protected function bAceptar_valida();

  public static function inicia($tipo = null, $id_site = null) {
    switch ($tipo) {
      case "cat":
        return new Cxamasivo_cat($id_site);
      case "del":
        return new Cxamasivo_del();
      case "dnov":
        return new Cxamasivo_dnov();
      case "dpro":
        return new Cxamasivo_dpro();
      case "dpub":
        return new Cxamasivo_dpub($id_site);
      case "nov":
        return new Cxamasivo_nov();
      case "pro":
        return new Cxamasivo_pro($id_site);
      case "pub":
        return new Cxamasivo_pub($id_site);
    }

    return new Cxamasivo_nulo();
  }

  public function operacion(EstadoHTTP $e) {
//~ echo $e->evento()->html();

    if (!$this->visible) return null;


    if ($this->obxeto("bCancelar")->control_evento()) return $this->operacion_bCancelar($e);

    if ($this->obxeto("bAceptar" )->control_evento()) return $this->operacion_bAceptar($e);


    return null;
  }

  final protected function _cheq_comas() {
//~ echo $e->evento()->html();

    $s = "";
    foreach ($this->pai->_cheq as $k=>$v) {
      if ($s != "") $s .= ",";

      $s .= $k;
    }


    return $s;
  }

  private function operacion_bAceptar(Efs $e) {
    if (!is_array($this->pai->_cheq)) {
      $e->post_msx("Error, no hay productos selecionados.");

      return $this->operacion_bCancelar($e);
    }

    if (count($this->pai->_cheq) == 0) {
      $e->post_msx("Error, no hay productos selecionados.");

      return $this->operacion_bCancelar($e);
    }

    if (($err = $this->bAceptar_valida()) != null) {
      $e->post_msx($err);

      return $this->operacion_bCancelar($e);
    }

    if (($e_aux = $this->bAceptar_exec($e)) != null) {
      $e_aux->post_msx("Operación realizada con éxito", "m");

      return $this->operacion_bCancelar($e_aux);
    }

    $e->post_msx("Sucedió un error al actualizar la BD");

    return $this->operacion_bCancelar($e);
  }

  private function operacion_bCancelar(Efs $e) {
    $this->visible = false;

    $this->pai->obxeto("smasivo")->post("**");

    $this->pai->preparar_saida($e->ajax());

    return $e;
  }
}

//*******************************************

final class Cxamasivo_nulo extends Cxarticulos_masivo {
  public function __construct() {
    parent::__construct();

    $this->visible = false;
  }

  protected function ptw() { //* IMPLEMENTA Cxarticulos_masivo.ptw()
    return null;
  }

  protected function bAceptar_exec(Efs_admin $e) { //* IMPLEMENTA Cxarticulos_masivo.bAceptar_exec()
    return null;
  }

  protected function bAceptar_valida() { //* IMPLEMENTA Cxarticulos_masivo.bAceptar_valida()
    return null;
  }

}

//*******************************************

final class Cxamasivo_cat extends Cxarticulos_masivo {
  public function __construct($id_site) {
    parent::__construct();

    $this->pon_obxeto( Cxarticulos_busca::__scategoria($id_site) );
  }

  protected function ptw() { //* IMPLEMENTA Cxarticulos_masivo.ptw()
    return "ptw/paneis/pcontrol/ventas/articulo/cxamasivo_cat.html";
  }

  protected function bAceptar_exec(Efs_admin $e) { //* IMPLEMENTA Cxarticulos_masivo.bAceptar_exec()
//~ echo "<pre>" . print_r($this->pai->_cheq, 1) . "</pre>";

    $cbd = new FS_cbd();

    $cbd->transaccion();

    $s = $this->pai->id_site;
    $c = $this->obxeto("scategoria")->valor();

    foreach($this->pai->_cheq as $a => $x) {
      $sql = "update articulo set id_categoria = {$c} where id_site = {$s} and id_articulo = {$a}";

      if (!$cbd->executa($sql)) {
        $cbd->rollback();

        return null;
      }
    }

    $cbd->commit();

    return $e;
  }

  protected function bAceptar_valida() { //* IMPLEMENTA Cxarticulos_masivo.bAceptar_valida()
    if ( !$this->obxeto("scategoria")->validar() ) return "Error, debes seleccionar una categoría";


    return null;
  }

}

//*******************************************

final class Cxamasivo_nov extends Cxarticulos_masivo {
  public function __construct() {
    parent::__construct();

    $this->pon_obxeto(new DataInput("dnovo"));
  }

  protected function ptw() { //* IMPLEMENTA Cxarticulos_masivo.ptw()
    return "ptw/paneis/pcontrol/ventas/articulo/cxamasivo_nov.html";
  }

  protected function bAceptar_exec(Efs_admin $e) { //* IMPLEMENTA Cxarticulos_masivo.bAceptar_exec()
//~ echo "<pre>" . print_r($this->pai->_cheq, 1) . "</pre>";

    $d = $d = $this->obxeto("dnovo")->valor();
    $b = $this->_cheq_comas();

    $cbd = new FS_cbd();

    $sql = "update articulo set momento_novo = '{$d}' where id_site = {$this->pai->id_site} and id_articulo in ({$b})";

    $cbd->executa($sql);

    return $e;
  }

  protected function bAceptar_valida() { //* IMPLEMENTA Cxarticulos_masivo.bAceptar_valida()
    if (($d = $this->obxeto("dnovo")->valor()) == null) return "Error, debes seleccionar una fecha";


    return null;
  }

}

//*******************************************

final class Cxamasivo_dnov extends Cxarticulos_masivo {
  public function __construct() {
    parent::__construct();
  }

  protected function ptw() { //* IMPLEMENTA Cxarticulos_masivo.ptw()
    return "ptw/paneis/pcontrol/ventas/articulo/cxamasivo_dnov.html";
  }

  protected function bAceptar_exec(Efs_admin $e) { //* IMPLEMENTA Cxarticulos_masivo.bAceptar_exec()
//~ echo "<pre>" . print_r($this->pai->_cheq, 1) . "</pre>";

    $b = $this->_cheq_comas();

    $cbd = new FS_cbd();

    $sql = "update articulo set momento_novo = null where id_site = {$this->pai->id_site} and id_articulo in ({$b})";

    $cbd->executa($sql);

    return $e;
  }

  protected function bAceptar_valida() { //* IMPLEMENTA Cxarticulos_masivo.bAceptar_valida()
    return null;
  }

}

//*******************************************

final class Cxamasivo_pub extends Cxarticulos_masivo {
  public function __construct($id_site) {
    parent::__construct();

    $this->pon_obxeto( Cxarticulos_busca::__spaxina($id_site) );
  }

  protected function ptw() { //* IMPLEMENTA Cxarticulos_masivo.ptw()
    return "ptw/paneis/pcontrol/ventas/articulo/cxamasivo_pub.html";
  }

  protected function bAceptar_exec(Efs_admin $e) { //* IMPLEMENTA Cxarticulos_masivo.bAceptar_exec()
//~ echo "<pre>" . print_r($this->pai->_cheq, 1) . "</pre>";

    $cbd = new FS_cbd();

    $cbd->transaccion();

    $s = $this->pai->id_site;
    $p = $this->obxeto("spaxina")->valor();
    $d = date("YmdHis");

    foreach($this->pai->_cheq as $a => $x) {
      $sql = "insert into articulo_publicacions (id_site, id_articulo, id_paxina, momento) values ({$s}, {$a}, {$p}, '{$d}') ON DUPLICATE KEY UPDATE momento = {$d}";

      if (!$cbd->executa($sql)) {
        $cbd->rollback();

        return null;
      }
    }

    $cbd->commit();

    return $e;
  }

  protected function bAceptar_valida() { //* IMPLEMENTA Cxarticulos_masivo.bAceptar_valida()
    if (($d = $this->obxeto("spaxina")->valor()) == "**") return "Error, debes seleccionar una página para publicación";


    return null;
  }

}

//*******************************************

final class Cxamasivo_dpub extends Cxarticulos_masivo {
  public function __construct($id_site) {
    parent::__construct();

    $this->pon_obxeto( Cxarticulos_busca::__spaxina($id_site, "Todas las páginas") );
  }

  protected function ptw() { //* IMPLEMENTA Cxarticulos_masivo.ptw()
    return "ptw/paneis/pcontrol/ventas/articulo/cxamasivo_dpub.html";
  }

  protected function bAceptar_exec(Efs_admin $e) { //* IMPLEMENTA Cxarticulos_masivo.bAceptar_exec()
//~ echo "<pre>" . print_r($this->pai->_cheq, 1) . "</pre>";

    $cbd = new FS_cbd();

    $s = $this->pai->id_site;

    $wh_p = ""; if (($p = $this->obxeto("spaxina")->valor()) != "**") $wh_p = " and id_paxina = {$p}";

    foreach($this->pai->_cheq as $a => $x) {
      $sql = "delete from articulo_publicacions where id_site = {$s}{$wh_p} and id_articulo = {$a}";

      if (!$cbd->executa($sql)) {
        $cbd->rollback();

        return null;
      }
    }

    $cbd->commit();

    return $e;
  }

  protected function bAceptar_valida() { //* IMPLEMENTA Cxarticulos_masivo.bAceptar_valida()
    //~ if (($d = $this->obxeto("spaxina")->valor()) == "**") return "Error, debes seleccionar una página para publicación";

    return null;
  }

}

//*******************************************

final class Cxamasivo_del extends Cxarticulos_masivo {
  public function __construct() {
    parent::__construct();
  }

  protected function ptw() { //* IMPLEMENTA Cxarticulos_masivo.ptw()
    return "ptw/paneis/pcontrol/ventas/articulo/cxamasivo_del.html";
  }

  protected function bAceptar_exec(Efs_admin $e) { //* IMPLEMENTA Cxarticulos_masivo.bAceptar_exec()
//~ echo "<pre>" . print_r($this->pai->_cheq, 1) . "</pre>";

    $cbd = new FS_cbd();

    $s = $this->pai->id_site;

    foreach($this->pai->_cheq as $a => $x) {
      $a = Articulo_obd::inicia($cbd, $s, $a);

      if (!$a->delete($cbd)) {
        $cbd->rollback();

        return null;
      }
    }

    $cbd->commit();
    //~ $cbd->rollback();

    return $e;
  }

  protected function bAceptar_valida() { //* IMPLEMENTA Cxarticulos_masivo.bAceptar_valida()
    //~ if (($d = $this->obxeto("spaxina")->valor()) == "**") return "Error, debes seleccionar una página para publicación";

    return null;
  }

}

//*******************************************

final class Cxamasivo_pro extends Cxarticulos_masivo {
  public function __construct($id_site) {
    parent::__construct();

    $this->pon_obxeto( Cxarticulos_busca::__spromo($id_site, false) );
  }

  protected function ptw() { //* IMPLEMENTA Cxarticulos_masivo.ptw()
    return "ptw/paneis/pcontrol/ventas/articulo/cxamasivo_pro.html";
  }

  protected function bAceptar_exec(Efs_admin $e) { //* IMPLEMENTA Cxarticulos_masivo.bAceptar_exec()
//~ echo "<pre>" . print_r($this->pai->_cheq, 1) . "</pre>";

    $cbd = new FS_cbd();

    $cbd->transaccion();

    $s = $this->pai->id_site;
    $p = $this->obxeto("spromo")->valor();

    foreach($this->pai->_cheq as $a => $x) {
      $sql = "insert into promo_producto (id_site, id_promo, id_articulo, regalo) values ({$s}, {$p}, {$a}, 0) ON DUPLICATE KEY UPDATE regalo = 0";

      if (!$cbd->executa($sql)) {
        $cbd->rollback();

        return null;
      }
    }

    $cbd->commit();

    return $e;
  }

  protected function bAceptar_valida() { //* IMPLEMENTA Cxarticulos_masivo.bAceptar_valida()
    if (($d = $this->obxeto("spromo")->valor()) == "**") return "Error, debes seleccionar una promoción";


    return null;
  }

}

//*******************************************

final class Cxamasivo_dpro extends Cxarticulos_masivo {
  public function __construct() {
    parent::__construct();
  }

  protected function ptw() { //* IMPLEMENTA Cxarticulos_masivo.ptw()
    return "ptw/paneis/pcontrol/ventas/articulo/cxamasivo_dpro.html";
  }

  protected function bAceptar_exec(Efs_admin $e) { //* IMPLEMENTA Cxarticulos_masivo.bAceptar_exec()
//~ echo "<pre>" . print_r($this->pai->_cheq, 1) . "</pre>";

    $cbd = new FS_cbd();

    $s = $this->pai->id_site;

    foreach($this->pai->_cheq as $a => $x) {
      $sql = "delete from promo_producto where id_site = {$s} and id_articulo = {$a}";

      if (!$cbd->executa($sql)) {
        $cbd->rollback();

        return null;
      }
    }

    $cbd->commit();

    return $e;
  }

  protected function bAceptar_valida() { //* IMPLEMENTA Cxarticulos_masivo.bAceptar_valida()
    return null;
  }

}

