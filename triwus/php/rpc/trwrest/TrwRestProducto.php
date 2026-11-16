<?php

class TrwRestProducto extends TRW_rest {
  public function __construct($id_site, $_request) {
    parent::__construct($id_site, $_request);
  }

  protected function _get() {    //* implementa TRW_rest._get()
    $cbd = new FS_cbd();

    $sql = $this->sql_get();


    $c = (($json = $cbd->json($sql)) != "[]")?200:404;


    return array($c, $json);
  }

  protected function _post() {   //* implementa TRW_rest._post()
    return array(405, "");
  }

  protected function _put() {    //* implementa TRW_rest._put()
    $ud = intval($this->_r["ud"]);
    
    $sql = "update articulo set unidades = {$ud}, momento_fs=now() where id_site='{$this->s}' and id_articulo='{$this->_r["id"]}'";

    $cbd = new FS_cbd();

    if (!$cbd->executa($sql)) return array(500, json_encode("KO"));

    return array(200, json_encode("OK"));
  }

  protected function _delete() { //* implementa TRW_rest._delete()
    return array(405, "");
  }


  protected function sql_get() {
    if ($this->_r["total"] == 1)
      return "select count(*) as total
                from v_articulo a  
             where a.id_site = {$this->s}
          order by a.id_grupo_cc, a.id_articulo";


    $w_id = "1"; $w_data = "1"; 
    if ( isset($this->_r["id"]  ) ) $w_id   = "a.id_articulo='{$this->_r["id"]}'";
    //~ if ( isset($this->_r["data"]) ) $w_data = "DATE_FORMAT(a.momento_fs, '%Y%m%d') >= '{$this->_r["data"]}'";
    if ( isset($this->_r["data"]) ) $w_data = "a.momento_fs >= '{$this->_r["data"]}'";
    

    return "select a.id_articulo, a.ean, a.ref_externa, 
                   if( a.tupla_cc is null, a.nome, concat(a.nome, ' ', a.tupla_cc) ) as nome,
                   a.notas, a.tipo, concat('arquivos/', c.url) as imx,
                   a.momento_fs, a.pedido_min, a.pedido_max,
                   a.pvp as prezo, a.id_ive, a.pive, a.moeda, a.unidades, a.desc_u, a.desc_p, a.peso,
                   a.id_marca, a.marca, a.aux_0, a.aux_1, a.aux_2, a.id_almacen, a.id_categoria,
                   a.id_grupo_cc, a.tupla_cc, a.id_promo, a.id_comunidad, a.id_prov, a.id_concello, a.lat, a.lnx
              from v_articulo a left join articulo_imx b on (a.id_site = b.id_site and a.id_articulo = b.id_articulo and b.posicion = 1)
                                left join elmt_imaxe   c on (b.id_logo = c.id_elmt) 
             where a.id_site = {$this->s} and ({$w_id}) and ({$w_data})
          order by a.id_grupo_cc, a.id_articulo " . $this->sql_limit();
  }
}
