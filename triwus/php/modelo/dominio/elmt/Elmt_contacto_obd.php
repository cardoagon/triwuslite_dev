<?php

final class Elmt_contacto_obd extends Elmt_obd {
  const tipo_bd = "CONTACTO";
  
  public static array $_campo      = ["etq" => "", "ob" => "1", "tipo"=>"txt", "axuda"=>""];
  public static array $_campo_tipo = ["txt" => "Texto",
                                      "ban" => "IBAN",
                                      "dat" => "Fecha",
                                      "dni" => "DNI",
                                      "ema" => "Email",
                                      "atx" => "Área texto",
                                      "adx" => "Anexo"
                                     ];

  public function __construct() {
    parent::__construct();

    $this->atr("ticket"     )->valor = 1;
                            
    //~ $this->atr("c_nome"     )->valor = "10";
    //~ $this->atr("c_apel"     )->valor = "00";
    //~ $this->atr("c_dni"      )->valor = "00";
    //~ $this->atr("c_empresa"  )->valor = "00";
    //~ $this->atr("c_email"    )->valor = "11";
    //~ $this->atr("c_skype"    )->valor = "00";
    //~ $this->atr("c_tlfn"     )->valor = "00";
    //~ $this->atr("c_fax"      )->valor = "00";
    //~ $this->atr("c_mobil"    )->valor = "00";
    //~ $this->atr("c_msx"      )->valor = "11";
    //~ $this->atr("c_dirpostal")->valor = "00";
    //~ $this->atr("c_asunto"   )->valor = "10";
    //~ $this->atr("c_adxunto"  )->valor = "00";
  }

  public function mapa_bd() {
    return new Elmt_contacto_mbd();
  }

  public function inicia_asunto(FGS_usuario $u) {
    $this->asunto( array("Información"=>$u->atr("email")->valor) );
  }

  public function asunto($asunto = null) {
    if ($asunto != null) $this->atr("c_asunto_s")->valor = serialize($asunto);

    if ($this->atr("c_asunto_s")->valor == null) return null;

    return unserialize($this->atr("c_asunto_s")->valor);
  }


  public function inicia_campos() {
    $_c = [
           ["etq" => "Nombre" , "ob" => 0, "tipo"=>"txt", "axuda"=>""],
           ["etq" => "Email"  , "ob" => 1, "tipo"=>"ema", "axuda"=>""],
           ["etq" => "Mensaje", "ob" => 1, "tipo"=>"atx", "axuda"=>""]
          ];
          
    $this->campos( $_c );
  }
  
  public function campos($campos = null):array {
    if ($campos != null) $this->atr("c_campos_s")->valor = json_encode($campos);

    if ($this->atr("c_campos_s")->valor == null) return [];

    return json_decode($this->atr("c_campos_s")->valor, false);
  }

  public static function __colle_ticket($id_site, FS_cbd $cbd = null) {
    if ($cbd == null) $cbd = new FS_cbd();

    $ano = date("Y");

    if (!$cbd->executa("insert into elmt_contacto_ticket (id_site, ano) values ({$id_site}, {$ano})")) return -1;

    $r = $cbd->consulta("select LAST_INSERT_ID() as id");

    if (!$a = $r->next()) return -1;

    $cbd->executa("delete from elmt_contacto_ticket where id_site = {$id_site} and ano = {$ano} and numero < {$a['id']}");


    return "{$ano}-" . str_pad($a['id'], 6, "0", STR_PAD_LEFT);
  }

  protected function tipo_bd() {
    return self::tipo_bd;
  }
}


//--------------------------------------------------------


final class Elmt_contacto_mbd extends Elmt_mbd {
  public function __construct() {
    parent::__construct();

    $t = new Taboa_dbd("elmt_contacto");

    $t->pon_campo("id_elmt", new Numero(), true);
    //~ $t->pon_campo("mini", new Numero());
    $t->pon_campo("ticket", new Numero());
    $t->pon_campo("de");
    $t->pon_campo("legal");
    $t->pon_campo("paso");
    //~ $t->pon_campo("c_nome");
    //~ $t->pon_campo("c_apel");
    //~ $t->pon_campo("c_dni");
    //~ $t->pon_campo("c_empresa");
    //~ $t->pon_campo("c_email");
    //~ $t->pon_campo("c_skype");
    //~ $t->pon_campo("c_tlfn");
    //~ $t->pon_campo("c_fax");
    //~ $t->pon_campo("c_msx");
    //~ $t->pon_campo("c_dirpostal");
    //~ $t->pon_campo("c_mobil");
    //~ $t->pon_campo("c_asunto");
    $t->pon_campo("c_asunto_s");
    $t->pon_campo("c_campos_s");
    //~ $t->pon_campo("c_adxunto");
    //~ $t->pon_campo("c_adxunto_bytes");
    //~ $t->pon_campo("c_adxunto_mime");

    $this->relacion_fk($t, array("id_elmt"), array("id_elmt"));
  }
}

