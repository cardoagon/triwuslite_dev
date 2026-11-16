<?php

class TrwRestCliente extends TRW_rest {
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
    return array(405, "");
  }

  protected function _delete() { //* implementa TRW_rest._delete()
    return array(405, "");
  }

  protected function sql_get() {
    $sql_0 = "select distinct id_usuario, cnif_t, cnif_d, nome, razon_social, email, mobil_p0, mobil,
                            skype, web, a.notas, codtipovia, via, num, resto, tlfn, fax, cp,
                            localidade, country_cod
              from v_cliweb a left join v_pedido b on (a.id_usuario = b.id_cliente)
             where a.id_site = {$this->s} and (not b.numero is null)";

    
    if ($this->_r["total"] == 1)
      return "select count(*) as total from ({$sql_0}) a";

              
    $w_id = "1"; 
    if ( isset($this->_r["id"]) ) $w_id = "id_usuario='{$this->_r["id"]}'";

    return "{$sql_0} and ({$w_id}) " . $this->sql_limit();
  }
}
