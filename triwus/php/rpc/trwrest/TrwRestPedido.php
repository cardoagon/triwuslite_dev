<?php

class TrwRestPedido extends TRW_rest {
  public function __construct($id_site, $_request) {
    parent::__construct($id_site, $_request);
  }

  protected function _get() {    //* implementa TRW_rest._get()
    if ($this->_r["total"] == 1) return $this->_get_total();


    $cbd = new FS_cbd();

    $w_a = "1"; $w_n = "1"; $w_data = "1";
    if ( isset($this->_r["a"]   ) ) $w_a    = "a.ano = '{$this->_r["a"]}'";
    if ( isset($this->_r["n"]   ) ) $w_n    = "a.numero = '{$this->_r["n"]}'";
    if ( isset($this->_r["data"]) ) $w_data = "(not a.pago_momento is null) and (DATE_FORMAT(a.pago_momento, '%Y%m%d') >= '{$this->_r["data"]}')";

    $sql = "select a.ano, a.numero, a.momento, a.ref, a.notas,
                   a.id_cliente, a.cliente_email, a.id_destino, a.prefs, a.estado_p,
                   a.tpvp, a.tpvp_plus, a.tpvpive, a.tpvp_plus_ive, a.portes, a.portes_sabado, a.dto, a.moeda,
                   a.id_fpago, a.fpago, a.id_pago, a.pago_momento, a.id_factura, a.factura_momento, a.cancelado,
                   a.sprecargo
              from v_pedido a
             where a.id_site = {$this->s} and ({$w_data}) and ({$w_a}) and ({$w_n}) " . $this->sql_limit();

    $r = $cbd->consulta($sql);

    $_p = null;
    while ($_r = $r->next()) {
      $_pi = null;

      $sprecargo = null;

      foreach ($_r as $k=>$v) {
        if (is_numeric($k)) continue;

        if ($k == "sprecargo") {
          $sprecargo = unserialize($v);

          continue;
        }

        $_pi[$k] = $v;
      }

      if ($sprecargo != null) {
        $t  = $_pi["tpvpive"] - $_pi["dto"] + $_pi["portes"] + $_pi["tpvp_plus_ive"];

        $_pi["cr"] = Pedido_obd::calcula_reembolso_2($t, $sprecargo);
      }
      else
        $_pi["cr"] = 0;

      $_pi["_detalle"] = $this->_get_detalles($cbd, $_pi["ano"], $_pi["numero"]);

      $_p[] = $_pi;
    }


    if ($_p == null) return array(404, "[]");


    return array(200, json_encode($_p));
  }

  protected function _post() {   //* implementa TRW_rest._post()
    return array(405, "");
  }

  protected function _put() {    //* implementa TRW_rest._put()
    return array(405, "");
  }

  protected function _delete() { //* implementa TRW_rest._delete()
    return array(405, "");
  }

  protected function _get_detalles(FS_cbd $cbd, $a, $n) {
    $sql = "select a.ano, a.numero, a.k_aux, a.estado, a.id_articulo, a.id_almacen, a.tipo, a.desc_p, a.pvp, a.pvp_plus, a.pive,
                   a.moeda, a.unidades, ifnull(b.unidades, 0) as stock_actual, a.peso, a.describe_html,
                   a.nome, a.notas, a.ref, a.tpvp, a.tpvp_plus, a.tpvp_plus_ive, a.tpvpive
              from v_pedido_articulo a left join articulo b on (a.id_site = b.id_site and a.id_articulo = b.id_articulo)
             where a.id_site = {$this->s} and a.ano = {$a} and a.numero = {$n}
            order by a.k_aux";

    $r = $cbd->consulta($sql);

    $_d = array();
    while ($_r = $r->next()) {
      $_di = null;

      foreach ($_r as $k=>$v) {
        if (is_numeric($k)) continue;

        $_di[$k] = $v;
      }

      $_d[] = $_di;
    }

    return $_d;
  }

  protected function _get_total() {
    $cbd = new FS_cbd();

    $w_data = "1";
    if ( isset($this->_r["data"]) ) $w_data = "DATE_FORMAT(a.momento, '%Y%m%d') >= '{$this->_r["data"]}'";

    $sql = "select count(*) as total
              from v_pedido a
             where a.id_site = {$this->s} and ({$w_data})";


    $c = (($json = $cbd->json($sql)) != "[]")?200:404;


    return array($c, $json);
  }
}
