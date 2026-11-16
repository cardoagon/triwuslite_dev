<?php

final class PayPal_2_obd extends Obxeto_bd {
  public function __construct() {
    parent::__construct();
  }

  public function mapa_bd() {
    return new PayPal_2_mbd();
  }

  public function baleiro():bool {
    return $this->atr("transaction_id")->valor == null;
  }

  public static function inicia(FS_cbd $cbd, string $transaction_id = null):PayPal_2_obd {
    $o = new PayPal_2_obd($transaction_id);

    if ($transaction_id == null) return $o;


    $o->select( $cbd, "transaction_id = '{$transaction_id}'" );


    return $o;
  }

  public function select(FS_cbd $cbd, $where = null, $orderby = null):void {
    $r = $cbd->consulta($this->sql_select($cbd, $where, $orderby));

    if (!$a = $r->next()) return;

    $this->post($a);
  }

  public function insert(FS_cbd $cbd):bool {
    if ( !$cbd->executa($this->sql_insert($cbd)) ) return false;
    
    return $this->pagar_pedido($cbd);
  }

  public function update(FS_cbd $cbd) {
    return $cbd->executa($this->sql_update($cbd));
  }

  public function delete(FS_cbd $cbd) {
    return $cbd->executa($this->sql_delete($cbd));
  }

  private function pagar_pedido(FS_cbd $cbd):bool {
    $id_site = $this->atr("id_site")->valor;
    $ano     = $this->atr("ano"    )->valor;
    $numero  = $this->atr("numero" )->valor;


    $p = Pedido_obd::inicia($cbd, $id_site, $ano, $numero);
    
    if ($p->baleiro()) return false;
    

    return $p->update_pago( $cbd, date("Y-m-d"), $this->atr("transaction_id")->valor, true );
  }
}

//------------------------------------------

final class PayPal_2_mbd extends Mapa_bd {
  public function __construct() {
    parent::__construct();

    $t = new Taboa_dbd("rpc_paypal_2");

    $t->pon_campo("transaction_id"                 , new Varchar()     , true);
    $t->pon_campo("id_site"                        , new Numero()            );
    $t->pon_campo("ano"                            , new Numero()            );
    $t->pon_campo("numero"                         , new Numero()            );
    $t->pon_campo("order_id"                       , new Varchar()           );
    $t->pon_campo("payment_source"                 , new Varchar()           );
    $t->pon_campo("payment_source_card_name"       , new Varchar()           );
    $t->pon_campo("payment_source_card_last_digits", new Varchar()           );
    $t->pon_campo("payment_source_card_expiry"     , new Varchar()           );
    $t->pon_campo("payment_source_card_brand"      , new Varchar()           );
    $t->pon_campo("payment_source_card_type"       , new Varchar()           );
    $t->pon_campo("payment_source_paypal_email"    , new Varchar()           );
    $t->pon_campo("payment_source_paypal_id"       , new Varchar()           );
    $t->pon_campo("payment_source_paypal_status"   , new Varchar()           );
    $t->pon_campo("payment_status"                 , new Varchar()           );
    $t->pon_campo("custom_id"                      , new Varchar()           );
    $t->pon_campo("amount_value"                   , new Numero()            );
    $t->pon_campo("currency_code"                  , new Varchar()           );
    $t->pon_campo("created"                        , new Data("YmdHis")      );
    $t->pon_campo("modified"                       , new Data("YmdHis")      );


    $this->pon_taboa($t);
  }
}

