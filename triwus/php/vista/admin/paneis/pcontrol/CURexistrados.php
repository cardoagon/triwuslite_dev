<?php


class CURexistrados extends Componente implements ICPC {
  const ptw_1 = "ptw/paneis/pcontrol/usuario/curexistrados.html";

  public $protocolo = null;
  public $dominio   = null;

  protected $id_site = null;

  public function __construct($id_site, $id = "curexistrados") {
    parent::__construct($id, self::ptw_1);

    $this->id_site = $id_site;

    $this->pon_obxeto(new CXCli_ficha());

    $this->pon_obxeto(new CXCli_busca($id_site));

    $this->pon_obxeto(new CXCli_lista   ($id_site));
    $this->pon_obxeto(new CXCliDif_lista($id_site));

    $this->pon_obxeto(new CXCli_lista_invitacion($id_site));

    $this->pon_obxeto(new CXCli_invitacion($id_site));

    $this->pon_obxeto(new Param("msx"));

    $this->pon_obxeto(self::_chOpcion("chOpcion_l" , true , "Activar / Desactivar opción de login."        ));
    $this->pon_obxeto(self::_chOpcion("chOpcion_r" , true , "Activar / Desactivar opción de registro."     ));
    $this->pon_obxeto(self::_chOpcion("chOpcion_d" , false, "Activar / Desactivar registro con validación."));


// 13/05/2016
    $this->pon_obxeto(new Button("bEnviarInvitacion", "<span style='font-size: 1.3em; color: #00ba00;'>&#9881;</span> Enviar invitación"));

    $this->obxeto("bEnviarInvitacion")->style     ("default", "margin-bottom: 33px;");
    $this->obxeto("bEnviarInvitacion")->envia_ajax("onclick");


    $this->__configurar();
  }

  public function cpc_miga() {
    return "gestión de usuarios";
  }

  public function seleccionado() {
    return $this->obxeto("cxclif")->seleccionado();
  }

  public function operacion(EstadoHTTP $e) {
    if ($this->obxeto("chOpcion_l")->control_evento($e)) return $this->operacion_chopcion($e, "chOpcion_l", "opción de login"        );
    if ($this->obxeto("chOpcion_r")->control_evento($e)) return $this->operacion_chopcion($e, "chOpcion_r", "opción de registro"     );
    if ($this->obxeto("chOpcion_d")->control_evento($e)) return $this->operacion_chopcion($e, "chOpcion_d", "registro con validación");

    if ($this->obxeto("bEnviarInvitacion")->control_evento($e)) return $this->operacion_bEnviarInvitacion ($e);

    if (($e_aux = $this->obxeto("cxcli_inv"   )->operacion($e)) != null) return $e_aux;
    if (($e_aux = $this->obxeto("cxcli_li_inv")->operacion($e)) != null) return $e_aux;

    if (($e_aux = $this->obxeto("cxclib")->operacion($e)) != null) return $e_aux;
    if (($e_aux = $this->obxeto("cxclil")->operacion($e)) != null) return $e_aux;
    if (($e_aux = $this->obxeto("cxclif")->operacion($e)) != null) return $e_aux;

    if (($e_aux = $this->obxeto("cxclidifl")->operacion($e)) != null) return $e_aux;



    return null;
  }

  public function operacion_bEnviarInvitacion(Epcontrol $e) {
    $id_cambio = $e->evento()->subnome(0);

    $this->obxeto("cxcli_inv")->pon_invitacion($id_cambio);

    $this->obxeto("cxcli_inv")->preparar_saida($e->ajax());

    return $e;
  }

  public function operacion_buscador(Epcontrol $e, $where = null) {
    $this->obxeto("cxclil")->limit_0 = 0;

    $this->obxeto("cxclil")->__where($where);

    $this->obxeto("cxclil")->preparar_saida($e->ajax());


    return $e;
  }

  public function operacion_usuario(Epcontrol $e, $id_usuario = null) {
    $u = FGS_usuario::inicia(new FS_cbd(), $id_usuario);

//~ echo "<pre>" . print_r($u->a_resumo(), 1) . "</pre>";

    $this->obxeto("cxclif")->pon_usuario($u);

    $this->preparar_saida($e->ajax());

    return $e;
  }


  private function operacion_chopcion(Epcontrol $e, $k, $m) {
    $b = $this->obxeto($k)->valor();


    $cbd = new FS_cbd();

    $sc = $this->chOpcion_obd($cbd, $k, $b);

    $cbd->transaccion();

    if ($sc->update($cbd)) {
      $cbd->commit();

      $this->__configurar_sc($sc, $cbd);

      $ad = ($b)?"ACTIVADO":"DESACTIVADO";

      $e->post_msx("&bull;&nbsp;{$ad} {$m}.", "m");
    }
    else {
      $cbd->rollback();

      $this->__configurar_sc(null, $cbd); //* reiniciamos opcions, cos valores anteriores.

      $e->post_msx("Sucedió un error al actualizar la BD.");
    }


    $e->ajax()->pon( $this->obxeto("chOpcion_l") );
    $e->ajax()->pon( $this->obxeto("chOpcion_r") );
    $e->ajax()->pon( $this->obxeto("chOpcion_d") );


    return $e;
  }

  private function chOpcion_obd(FS_cbd $cbd, $id_ch, $b) {
    //* actualiza BD.
    $sc = Site_config_obd::inicia($cbd, $this->id_site);


    if ($b) {
      if ($id_ch == "chOpcion_l") { //* activamos login.
        $sc->atr("ru_visible")->valor = "1";
      }
      elseif ($id_ch == "chOpcion_r") { //* activamos rexistro diferido.
        $sc->atr("ru_visible"         )->valor = "1";
        $sc->atr("ru_registro_visible")->valor = "1";
      }
      elseif ($id_ch == "chOpcion_d") { //* activamos rexistro diferido.
        $sc->atr("ru_diferido"        )->valor = "1";
        $sc->atr("ru_visible"         )->valor = "1";
        $sc->atr("ru_registro_visible")->valor = "1";
      }
    }
    elseif ($id_ch == "chOpcion_d") { //* desactivamos rexistro diferido.
      $sc->atr("ru_diferido")->valor = "0";
    }
    elseif ($id_ch == "chOpcion_r") { //* desactivamos rexistro.
      $sc->atr("ru_registro_visible")->valor = "0";
      $sc->atr("ru_diferido"        )->valor = "0";
    }
    elseif ($id_ch == "chOpcion_l") { //* desactivamos login ou rexistro.
      $sc->atr("ru_visible"         )->valor = "0";
      $sc->atr("ru_registro_visible")->valor = "0";
      $sc->atr("ru_diferido"        )->valor = "0";
    }


    return $sc;
  }

  private function __configurar() {
    $cbd = new FS_cbd();


    $this->__configurar_sc( null, $cbd );

    $this->protocolo = "https://";

    $this->dominio = TrwlConfig::dominio;
  }

  private function __configurar_sc(Site_config_obd $sc = null, FS_cbd $cbd = null) {
    if ($cbd == null) $cbd = new FS_cbd();

    if ($sc  == null) $sc = Site_config_obd::inicia($cbd, $this->id_site);

    $ru_visible  = $sc->atr("ru_visible"         )->valor == "1";
    $ru_diferido = $sc->atr("ru_diferido"        )->valor == "1";
    $ru_registro = $sc->atr("ru_registro_visible")->valor == "1";

    $this->obxeto("chOpcion_l")->post($ru_visible );
    $this->obxeto("chOpcion_r")->post($ru_registro);
    $this->obxeto("chOpcion_d")->post($ru_diferido);
  }

/*
  public static function validar_permisos($v_permisos) {
    $permisos = Erro::__comas( strtolower($v_permisos) );

    if (strlen($permisos) == 0) return "pub";

    foreach ((array)$permisos as $p) {
      $p = trim($p);
      $p = str_replace("admin", "", $p);
    }

    if (strpos($p, "pub") === FALSE) $p = "pub, {$p}";

    $p = Erro::__comas( strtolower($p) );

    return $p;
  }
*/
  private static function _chOpcion(string $k, bool $b, string $t):Checkbox {
    $o = new Checkbox($k, $b, $t, 2);

    //~ $o->envia_submit("onclick", "¿ {$t} ?");
    $o->envia_ajax  ("onclick", "¿ {$t} ?");


    return $o;
  }
}

//******************************

final class CXCli_busca extends Componente {
  const ptw_0 = "ptw/paneis/pcontrol/usuario/curexistrados_busca.html";

  public function __construct($id_site) {
    parent::__construct("cxclib", self::ptw_0);


    $this->pon_obxeto(Panel_fs::__bbusca   ("bBusca", null, null));
    $this->pon_obxeto(Panel_fs::__bcancelar(null, null, "bLimpa"));

    $this->pon_obxeto(Panel_fs::__text("txt", 20, 111, " "));

    $this->obxeto("txt")->style    ("default", "width: 91%;");
    $this->obxeto("txt")->clase_css("default", "tbuscador"  );

    $this->obxeto("bLimpa")->visible = false;
  }

  public function operacion(EstadoHTTP $e) {
    if ($this->obxeto("bBusca")->control_evento()) return $this->operacion_bBuscar($e);
    if ($this->obxeto("bLimpa")->control_evento()) return $this->operacion_bLimpa ($e);


    return null;
  }

  private function operacion_bBuscar(EstadoHTTP $e, $blimpa = true) {
    $this->obxeto("bLimpa")->visible = $blimpa;

    $this->preparar_saida($e->ajax());

    return $this->pai->operacion_buscador($e, $this->__where());
  }

  private function operacion_bLimpa(EstadoHTTP $e) {
    $this->obxeto("txt")->post(null);

    return $this->operacion_bBuscar($e, false);
  }

  private function __where() {
    $_campos = array("a.nome", "a.login", "a.cnif_d", "a.permisos", "a.localidade", "a.provincia", "a.razon_social", "a.email", "a.skype");

    return Valida::buscador_txt2sql($this->obxeto("txt")->valor(), $_campos);
  }
}

//******************************

final class CXCli_ficha extends Componente {
  private $id_usuario;
  private $u_baixa;

  public  function __construct() {
    parent::__construct("cxclif");

    $this->pon_obxeto(self::__idusuario());
    $this->pon_obxeto(self::__permisos ());


    $this->pon_obxeto(new Select("baixa", array("0"=>"No", "1"=>"Sí")));

    $this->pon_obxeto(new Div("dgardando", "Guardando ..."));
    $this->pon_obxeto(new Div("erro"));

    $this->pon_obxeto(new Param("pbloqueado"));


    $this->pon_obxeto(Panel_fs::__text("login", null, 255, " "));
    $this->pon_obxeto(self::__k());

    $this->pon_obxeto(self::__ccontacto());

    $this->pon_obxeto(Panel_fs::__baceptar());
    $this->pon_obxeto(Panel_fs::__bcancelar());

    $this->obxeto("erro")->clase_css("default", "texto2_erro");

    $this->obxeto("dgardando")->style("default", "display: none; padding: 0 0 5px 0;");

    $this->obxeto("bCancelar")->envia_ajax("onclick");
    $this->obxeto("bAceptar" )->envia_ajax("onclick");
  }

  public  function seleccionado() {
    return $this->obxeto("id_usuario")->valor();
  }

  public  function operacion(EstadoHTTP $e) {
    if ($this->ptw == null) return null;

    if (($e_aux = $this->obxeto("ccontacto")->operacion($e)) != null) return $e_aux;

    if ($this->obxeto("bAceptar" )->control_evento()) return $this->operacion_bAceptar ($e);
    if ($this->obxeto("bCancelar")->control_evento()) return $this->operacion_bCancelar($e);

    return null;
  }

  public  function reinicia() {
    $u = FGS_usuario::inicia(new FS_cbd(), $this->seleccionado());


    $this->pon_usuario($u);
  }

  public  function pon_usuario(FGS_usuario $u) {
    $this->ptw = "ptw/paneis/pcontrol/usuario/cxurexistrado.html";

    $this->id_usuario = $u->atr("id_usuario")->valor;
    //~ $this->u_baixa    = $u->atr("baixa"     )->valor;


    $this->obxeto("id_usuario")->post(Articulo_obd::ref($this->id_usuario));
    $this->obxeto("id_usuario")->readonly = true;

    $this->obxeto("permisos")->post($u->atr("permisos")->valor);
    $this->obxeto("permisos")->readonly = $u->permiso("admin");

    $this->obxeto("login")->post($u->atr("login")->valor);

    $this->obxeto("k")->post("");

    $this->obxeto("ccontacto")->post_contacto($u->contacto_obd());

    $m = "";
        if ( $u->atr("u_baixa")->valor == 1) $m = "usuario bloqueado";
    elseif ( $u->atr("u_baixa")->valor == 2) $m = "registro no validado";

    $this->obxeto("pbloqueado")->post($m);
  }

  public  function operacion_bCancelar(Epcontrol $e) {
    $this->ptw = null;

    $this->obxeto("id_usuario")->post(null);

    $this->preparar_saida($e->ajax());

    $this->pai->obxeto("cxclil")->preparar_saida($e->ajax());

    return $e;
  }

  private function operacion_bAceptar(Epcontrol $e) {
    $this->obxeto("erro")->post(null);

    if (($erro = $this->validar()) != null) {
      $this->obxeto("erro")->post($erro);

      $this->preparar_saida($e->ajax());

      return $e;
    }

    $cbd = new FS_cbd();

    $cbd->transaccion();

    $u = FGS_usuario::inicia($cbd, $this->id_usuario);

    $u->atr("login"   )->valor = $this->obxeto("login"   )->valor();
    $u->atr("permisos")->valor = $this->obxeto("permisos")->valor();

//~ echo "<pre>" . print_r($u->a_resumo(), 1) . "</pre>";

    if (!$u->update($cbd)) {
      $this->obxeto("erro")->post("Cxclientes.operacion_bAceptar(), !u.update()");

      $cbd->rollBack();

      $this->preparar_saida($e->ajax());

      return $e;
    }


    //~ $this->obxeto("ccontacto")->post_login( $this->obxeto("login")->valor() );

    if (!$this->obxeto("ccontacto")->update_contacto($cbd)) {
      $this->obxeto("erro")->post("Cxclientes.operacion_bAceptar(), !ccontacto.update()");

      $cbd->rollBack();

      $this->preparar_saida($e->ajax());

      return $e;
    }


    if (!$this->update_k($cbd, $u)) {
      $this->obxeto("erro")->post("Cxclientes.operacion_bAceptar(), !update_k()");

      $cbd->rollBack();

      $this->preparar_saida($e->ajax());

      return $e;
    }

    $cbd->commit();

    return $this->operacion_bCancelar($e);
  }

  private function validar() {
    $erro = null;

    if ( !Valida::email( $this->obxeto("login")->valor() ) ) return Erro::cpago_11;

    $ccontacto = $this->obxeto("ccontacto");

    if (($erro = $ccontacto->validar()) != null) return $erro;


    //* valida grupo.
    if ($this->obxeto("permisos")->readonly) return $erro; //* non se modifica

    $permisos = FGS_usuario::valida_permisos($this->obxeto("permisos")->valor());

    $this->obxeto("permisos")->post($permisos);


    return $erro;
  }

  private function update_k(FS_cbd $cbd, FGS_usuario $u) {
    if (($k = trim( $this->obxeto("k")->valor() )) == "") return true;

    return $u->update_k($cbd, $k);
  }

  private static function __ccontacto() {
    $c = new CContacto();

    return $c;
  }

  private static function __idusuario() {
    $t = Panel_fs::__text("id_usuario");

    $t->readonly = true;

    return $t;
  }

  private static function __k() {
    // $t = Panel_fs::__text("k");
    $t = Panel_fs::__password("k");
    $t->placeholder = " ";

    return $t;
  }

  private static function __permisos() {
    $t = Panel_fs::__text("permisos");
    
    $t->style("default", "text-transform:lowercase;");

    return $t;
  }
}

//******************************

class CXCli_lista extends FS_lista {
  protected $id_site = null;

  public function __construct($id_site, $id = "cxclil") {
    parent::__construct($id, new CXCli_ehtml());

    $this->id_site    = $id_site;

    $this->selectfrom = "select distinct a.* from v_cliweb a";

    $this->__where();

    $this->limit_f    = 10;
  }

  public function __where($wh = null) {
    $this->where = "a.id_site = {$this->id_site} and a.bloqueado < 2";


    if ($wh == null) return;

    $this->where .= " and {$wh}";
  }

  public function seleccionado() {
    return $this->pai->seleccionado();
  }

  public function operacion(EstadoHTTP $e) {
    $evento = $e->evento();

//~ echo $evento->html();

    if ($this->control_fslevento($evento, "id_usuario")) {
      return $this->pai->operacion_usuario($e, $evento->subnome(0));
    }

    if ($this->control_fslevento($evento, "bbloq")) {
      return $this->operacion_bloquear($e, $evento->subnome(0));
    }

    if ($this->control_fslevento($evento, "bsup")) {
      return $this->operacion_suprimir($e, $evento->subnome(0));
    }

    return parent::operacion($e);
  }

  public function operacion_bloquear(Epcontrol $e, $id_usuario) {
    $cbd = new FS_cbd();

    $u = FGS_usuario::inicia($cbd, $id_usuario);

    $u->bloquear($cbd);

    $this->preparar_saida($e->ajax());

    return $e;
  }

  public function operacion_suprimir(Epcontrol $e, $id_usuario) {
    $cbd = new FS_cbd();

    $u = FGS_usuario::inicia($cbd, $id_usuario);

    if (($err = $u->delete_valida($cbd)) != null) {
      $e->post_msx($err);

      return $e;
    }

    if (!$u->delete($cbd)) {
      $e->post_msx("Error, no se pudo actualizar la BD.");

      return $e;
    }

    return $e;
  }
}

//---------------------------------

class CXCli_ehtml extends FS_ehtml {
  //~ public $nf = 0;

  public function __construct() {
    parent::__construct();

    $this->class_table = "lista_00";
    $this->style_table = "border: 1px #aaa solid;";
    $this->align       = "left";
    $this->width       = "88%";
    $this->cellspacing = 0;
    $this->cellpadding = 0;
    $this->border      = 0;
  }

  protected function cabeceira($df = null) {
    //~ $this->nf = $this->rbd->numFilas();

    return "<tr>
              <th align='left' width=1px>"     . $this->__lOrdenar_html("id_usuario", "#Usuario")   . "</th>
              <th align='left' width='*'>"     . $this->__lOrdenar_html("login"     , "Login")    . "</th>
              <th align='left' width='*'>"     . $this->__lOrdenar_html("nome"      , "Nombre")     . "</th>
              <th align='left' width='77px'>"  . $this->__lOrdenar_html("permisos"  , "Permisos")   . "</th>
              <th align='left' width='99px'>"  . $this->__lOrdenar_html("email"     , "Email")      . "</th>
              <th align='left' width='77px'>"  . $this->__lOrdenar_html("mobil"     , "Tlfn")       . "</th>
              <th align='left' width='131px'>" . $this->__lOrdenar_html("localidade", "Localidad")  . "</th>
              <th width='27px'></th>
              <th width='27px'></th>
              <th width='27px'></th>
            </tr>";
  }

  protected function linha_detalle($df, $f) {

    $style_tr = ($this->xestor->seleccionado() != $f['id_usuario'])?FS_ehtml::style_tr:FS_ehtml::style_tr_a;

    $enderezo = "{$f['codtipovia']} {$f['via']} {$f['num']} {$f['resto']}";

    $mp0 = ""; if ($f['mobil_p0'] != null) $mp0 = "+{$f['mobil_p0']}-";

    return "<tr  {$style_tr}>
              <td>" . self::__lcli($this, $f) . "</td>
              <td>{$f['login']}</td>
              <td>{$f['nome']}</td>
              <td>{$f['permisos']}</td>
              <td style='white-space:nowrap;'>{$f['email']}</td>
              <td>{$mp0}{$f['mobil']}</td>
              <td>{$f['localidade']}</td>
              <td><a style='font-size: 252%;' href='mailto:{$f['email']}' title='mailto:{$f['email']}'>&#9993;</a></td>
              <td align=center>" . self::__bbloq($this, $f) . "</td>
              <td align=center>" . self::__bSup($this, $f) . "</td>
            </tr>";
  }

  protected function totais() {
    return "<tr style = 'background-color: #fff;'>
              <td align=center colspan='11'>" . $this->__paxinador_html() . "</td>
            </tr>";
  }

  protected static function __lcli(CXCli_ehtml $ehtml, $f) {
    $b = new Div("id_usuario", Articulo_obd::ref($f['id_usuario'], 6));

    $b->clase_css("default", "lista_elemento_a");

    $b->title = "Editar datos del cliente";

    //~ $b->envia_submit("onclick");
    $b->envia_ajax("onclick");

    return $ehtml->__fslControl($b, $f['id_usuario'])->html();
  }

  protected static function __bbloq(CXCli_ehtml $ehtml, $f) {
    if ($ehtml->xestor->readonly) return "";

    if ($f['permisos'] == "admin") return "";


    if ($f['bloqueado'] == "0") {
      $txt = "BLOQUEAR usuario";
      $ico = Ico::bcandado;
    }
    else {
      $txt = "DESBLOQUEAR usuario";
      $ico = Ico::bcandado2;
    }


    $l = new Link("bbloq", "<img src='{$ico}' title='{$txt}' style='min-width: 19px;min-width: 19px;'/>");


    $l->envia_ajax("onclick");
    //~ $l->envia_submit("onclick");


    return $ehtml->__fslControl($l, $f['id_usuario'])->html();
  }

  protected static function __bSup(CXCli_ehtml $ehtml, $f) {
    if ($ehtml->xestor->readonly) return "";

    if ($f['permisos'] == "admin") return "";

    $t = "<span style='color: #ba0000;font-size: 1.7em;'>♻</span>";


    $b = new Div("bsup", $t);

    $b->style("default", "cursor:pointer;");


    $b->title = "ELIMINAR usuario {$f['id_usuario']}";

    $b->envia_SUBMIT("onclick", "¿ Deseas eleminar el usuario {$f['id_usuario']} ?");


    return $ehtml->__fslControl($b, $f['id_usuario'])->html();
  }
}


//******************************

final class CXCliDif_lista extends CXCli_lista {
  public function __construct($id_site) {
    parent::__construct($id_site, "cxclidifl");

    $this->__ehtml(new CXCliDif_ehtml());

    $this->orderby = "alta desc";
  }

  protected function html_baleiro() {
    if (!$this->visible) return "";

    return "<div class='lista_baleira txt_adv'>No hay registros pendientes de validación.</div>";
  }

  public function __where($wh = null) {
    $this->where = "a.id_site = {$this->id_site} and a.bloqueado > 1";

    if ($wh == null) return;

    $this->where .= " and {$wh}";
  }

  public function operacion(EstadoHTTP $e) {
    $evento = $e->evento();

    if ($this->control_fslevento($evento, "bAceptar")) {
      return $this->operacion_bAceptar($e, $evento->subnome(0));
    }

    if ($this->control_fslevento($evento, "lsup")) {
      return $this->operacion_lsup($e, $evento->subnome(0));
    }

    return parent::operacion($e);
  }

  public function operacion_bAceptar(Epcontrol $e, $id_usuario) {
    $cxcli_inv = $this->pai->obxeto("cxcli_inv");

    $cxcli_inv->pon_usuario($id_usuario);

    $cxcli_inv->preparar_saida($e->ajax());


    return $e;
  }

  public function operacion_lsup(Epcontrol $e, $id_usuario) {
    $cbd = new FS_cbd();

    $cbd->transaccion();

    $u = FGS_usuario::inicia($cbd, $id_usuario);

    if (!$u->delete($cbd)) {
      $cbd->rollback();

      return $e;
    }


    $cbd->commit();

    $this->preparar_saida($e->ajax());

    return $e;
  }
}

//---------------------------------

class CXCliDif_ehtml extends CXCli_ehtml {

  public function __construct() {
    parent::__construct();
  }

  protected function cabeceira($df = null) {
    return "<tr>
              <th align='left' width=1px>"     . $this->__lOrdenar_html("id_usuario", "#Usuario" ) . "</th>
              <th align='left' width='*'>"     . $this->__lOrdenar_html("nome"      , "Usuario"  ) . "</th>
              <th align='left' width='77px'>"  . $this->__lOrdenar_html("permisos"  , "Permisos" ) . "</th>
              <th align='left' width='99px'>"  . $this->__lOrdenar_html("email"     , "Email"    ) . "</th>
              <th align='left' width='77px'>"  . $this->__lOrdenar_html("mobil"     , "Tlfn"     ) . "</th>
              <th align='left' width='131px'>" . $this->__lOrdenar_html("localidade", "Localidad") . "</th>
              <th align='left' width='131px'>" . $this->__lOrdenar_html("alta"      , "Alta"     ) . "</th>
              <th width='27px'></th>
              <th width='27px'></th>
            </tr>";
  }

  protected function linha_detalle($df, $f) {

    $style_tr = ($this->xestor->seleccionado() != $f['id_usuario'])?FS_ehtml::style_tr:FS_ehtml::style_tr_a;

    $mp0 = ""; if ($f['mobil_p0'] != null) $mp0 = "+{$f['mobil_p0']}-";

    return "<tr  {$style_tr}>
              <td>" . self::__lcli($this, $f) . "</td>
              <td>{$f['nome']}</td>
              <td>{$f['permisos']}</td>
              <td style='white-space:nowrap;'>{$f['email']}</td>
              <td>{$mp0}{$f['mobil']}</td>
              <td>{$f['localidade']}</td>
              <td>{$f['alta']}</td>
              <td>" . self::_lSup($this, $f) . "</td>
              <td align=center>" . self::_lAceptar($this, $f) . "</td>
            </tr>";
  }

  private static function _lSup(CXCliDif_ehtml $ehtml, $f) {
    $idu = Articulo_obd::ref($f['id_usuario'], 6);

    $l = Panel_fs::__bsup("lsup", null, "Cancelar y eliminar el registro diferido {$idu}");


    //~ $l->envia_submit("onclick", "&iquest; Deseas cancelar y eliminar registro diferido {$idu} ?");
    $l->envia_ajax("onclick", "&iquest; Deseas cancelar y eliminar registro diferido {$idu} ?");


    return $ehtml->__fslControl($l, $f['id_usuario'])->html();
  }

  private static function _lAceptar(CXCliDif_ehtml $ehtml, $f) {
    $idu = Articulo_obd::ref($f['id_usuario'], 6);

    $l = Panel_fs::__baceptar(null, "Aceptar el registro diferido {$idu}");


    //~ $l->envia_submit("onclick");
    $l->envia_ajax("onclick");


    return $ehtml->__fslControl($l, $f['id_usuario'])->html();
  }
}

//******************************

final class CXCli_lista_invitacion extends FS_lista {
  private $id_site    = null;
  private $src_invita = null;

  public function __construct($id_site) {
    parent::__construct("cxcli_li_inv", new CXCli_ehtml_invitacion());

    $this->id_site = $id_site;

    $this->selectfrom = "select a.creado, (`a`.`creado` + INTERVAL `a`.`htl` HOUR) as caduca,
                                b.id_site, b.id_cambio, b.email, b.nome, b.permisos
                           from cambio a inner join cambio_invitaweb b on (a.id_cambio = b.id_cambio)";

    $this->where = "b.id_site = {$id_site} and (ISNULL(`a`.`click`) AND ((`a`.`creado` + INTERVAL `a`.`htl` HOUR) > NOW()))";

    $this->orderby = "a.creado desc";

    $this->limit_f = 5;
  }

  public function operacion(EstadoHTTP $e) {
    $evento = $e->evento();

    if ($this->control_fslevento($evento, "bReenviar")) return $this->pai->operacion_bEnviarInvitacion($e);
    if ($this->control_fslevento($evento, "lsup"     )) return $this->operacion_lsup($e, $evento->subnome(0));


    return parent::operacion($e);
  }

  public function src_invita($c) {
    if ($this->src_invita != null) return $this->src_invita . $c;


    if (($d = $this->pai->dominio) == null) $d = "fake.com";

    $this->src_invita = "{$this->pai->protocolo}{$d}/confirmar.php?c=";


    return  $this->src_invita . $c;
  }

  protected function html_baleiro() {
    if (!$this->visible) return "";

    return "<div class='lista_baleira txt_adv'>No hay invitaciones registradas.</div>";
  }

  private function operacion_lsup(Epcontrol $e, $id_cambio) {
    $cbd = new FS_cbd();

    $c = new Cambio_invitaweb_obd($id_cambio, $cbd);

    $cbd->transaccion();

    if ($c->delete($cbd)) {
      $cbd->commit();

      $this->control_limits_sup();
    }
    else
      $cbd->rollback();


    $this->preparar_saida($e->ajax());


    return $e;
  }
}

//---------------------------------

final class CXCli_ehtml_invitacion extends CXCli_ehtml {
  public function __construct() {
    parent::__construct();
  }

  protected function cabeceira($df = null) {
    return "<tr>
              <th align='left' width='1px'>"   . $this->__lOrdenar_html("cambio"  , "#Invitación") . "</th>
              <th align='left' width='99px'>"  . $this->__lOrdenar_html("email"   , "Email"      ) . "</th>
              <th align='left' width='*'>"     . $this->__lOrdenar_html("nome"    , "Nombre"     ) . "</th>
              <th align='left' width='77px'>"  . $this->__lOrdenar_html("permisos", "Permisos"   ) . "</th>
              <th align='left' width='121px'>" . $this->__lOrdenar_html("creado"  , "Creado"     ) . "</th>
              <th align='left' width='121px'>" . $this->__lOrdenar_html("caduca"  , "Caduca"     ) . "</th>
              <th width='37px'></th>
              <th width='37px'></th>
              <th width='37px'></th>
            </tr>";
  }

  protected function linha_detalle($df, $f) {
    return "<tr>
              <td >{$f['id_cambio']}</td>
              <td style='white-space:nowrap;'>{$f['email']}</td>
              <td >{$f['nome']}</td>
              <td >{$f['permisos']}</td>
              <td >{$f['creado']}</td>
              <td >{$f['caduca']}</td>
              <td>" . self::_bCompartir($this, $f) ."</td>
              <td>" . self::_bReenviar($this, $f) . "</td>
              <td>" . self::_lSup($this, $f) . "</td>
            </tr>";
  }


  private static function _bCompartir(CXCli_ehtml_invitacion $ehtml, $f) {
    if ($ehtml->xestor->readonly) return "";

    $href = $ehtml->xestor->src_invita( $f['id_cambio'] );

    $b = Panel_fs::__bapagado("enlace", Ico::blink2, "Copiar enlace", "19", "");

    $b->pon_eventos("onclick", "carquivo_benlace('{$href}')");

    return $b->html();
  }


  private static function _bReenviar(CXCli_ehtml_invitacion $ehtml, $f) {
    if ($ehtml->xestor->readonly) return "";

    $c = new Span("bReenviar", "&#9993;");

    $c->title    = "Reenvía invitacición a {$f["email"]}";

    $c->style("default", "cursor: pointer; font-size: 252%;");

    //~ $c->envia_submit("onclick");
    $c->envia_ajax("onclick");

    return $ehtml->__fslControl($c, $f["id_cambio"])->html();
  }

  private static function _lSup(CXCli_ehtml_invitacion $ehtml, $f) {
    $l = Panel_fs::__bsup("lsup", null, "Elimina la invitación {$f['id_cambio']}");


    //~ $l->envia_submit("onclick", "&iquest; Deseas eliminar la invitación {$f['id_cambio']} ?");
    $l->envia_ajax("onclick", "&iquest; Deseas eliminar la invitación {$f['id_cambio']} ?");


    return $ehtml->__fslControl($l, "{$f['id_cambio']}")->html();
  }

}

//******************************

final class CXCli_invitacion extends Componente {
  const ptw_0 = "ptw/paneis/pcontrol/usuario/cxclif_inv.html";
  const ptw_1 = "ptw/paneis/pcontrol/usuario/cxclif_inv_1.html";
  const ptw_7 = "ptw/paneis/pcontrol/usuario/email-inv.html";

  public  $id_site    = null;
  public  $id_cambio  = null;
  public  $id_usuario = null;

  private $tipo       = -1;

  public function __construct($id_site) {
    parent::__construct("cxcli_inv");

    $this->id_site = $id_site;

    $this->pon_obxeto(self::__sidioma($id_site));

    $this->pon_obxeto(Panel_fs::__text("email_inv"   ));
    $this->pon_obxeto(Panel_fs::__text("nome_inv"    ));
    $this->pon_obxeto(Panel_fs::__text("permisos_inv"));
    $this->pon_obxeto(new Span("erro_inv"));

    $this->pon_obxeto(Panel_fs::__baceptar());
    $this->pon_obxeto(Panel_fs::__bcancelar());

    //~ $this->obxeto("email_inv"   )->clase_css("default", "trw_cxpedf_dato");
    //~ $this->obxeto("nome_inv"    )->clase_css("default", "trw_cxpedf_dato");
    //~ $this->obxeto("permisos_inv")->clase_css("default", "trw_cxpedf_dato");
    $this->obxeto("erro_inv"    )->clase_css("default", "texto2_erro"    );

    $this->obxeto("bCancelar")->envia_ajax("onclick");
    //~ $this->obxeto("bAceptar" )->envia_ajax("onclick");
  }

  public  function pon_invitacion($id_cambio = null) {
    $this->tipo       = 1;
    $this->id_cambio  = $id_cambio;
    $this->id_usuario = null;
    $this->ptw        = self::ptw_0;

    $this->obxeto("email_inv"   )->post(null);
    $this->obxeto("permisos_inv")->post(null);
    $this->obxeto("nome_inv"    )->post(null);

    if ($id_cambio == null) return;

    $c = new Cambio_invitaweb_obd($id_cambio, new FS_cbd());

    $this->obxeto("email_inv"   )->post( $c->atr("email"   )->valor );
    $this->obxeto("permisos_inv")->post( $c->atr("permisos")->valor );
    $this->obxeto("nome_inv"    )->post( $c->atr("nome"    )->valor );
  }

  public  function pon_usuario($id_usuario) {
    $this->tipo       = 2;
    $this->id_cambio  = null;
    $this->id_usuario = $id_usuario;
    $this->ptw        = self::ptw_1;

    $u = FGS_usuario::inicia(new FS_cbd(), $id_usuario);

    $this->obxeto("email_inv"   )->post( $u->atr("email"   )->valor );
    $this->obxeto("permisos_inv")->post( $u->atr("permisos")->valor );
    $this->obxeto("nome_inv"    )->post( $u->atr("nome"    )->valor );
  }

  public  function operacion(EstadoHTTP $e) {
    $this->obxeto("erro_inv")->post(null);

    if ($this->obxeto("bAceptar" )->control_evento()) return $this->operacion_bAceptar ($e);
    if ($this->obxeto("bCancelar")->control_evento()) return $this->operacion_bCancelar($e);

    return null;
  }

  public function validar() {
    $erro = "";
    $flag = true;

    $this->obxeto("permisos_inv")->post( FGS_usuario::valida_permisos($this->obxeto("permisos_inv")->valor()) );

    $email = $this->obxeto("email_inv")->valor();

    if (Valida::email($email)) {
      //comprobar que non este dado de alta nin pendente. FGS_Usuario & Cambio_invitaweb_obd;
      $cbd = new FS_cbd();

      if ($this->id_cambio == null) {
        if( !Cambio_invitaweb_obd::valida_email($cbd, $email, $this->id_site) ) {
          $erro .= "Ya existe una invitación pendiente para el email.<br/>";
          $flag = false;
        }
      }

      if ($this->tipo == 1) {
        //* non temos en conta para invitacións para o cambio de contrasinal ou ru-diferido.
        $rexi = new FGS_usuario();
        $rexi->select_email($cbd, $email, $this->id_site);
        if( $rexi->validado() ) {
          $erro .= "Ya existe una cuenta de usuario para el email.<br/>";
          $flag = false;
        }
      }
    }
    else {
      $erro .= "Teclea correctamente el campo EMAIL.<br/>";
      $flag = false;
    }

    if ($this->obxeto("nome_inv")->valor() == "") {
      $erro .= "Teclea el campo NOMBRE.<br/>";
      $flag = false;
    }

    $this->obxeto("erro_inv")->post($erro);

    return $flag;
  }

  public function operacion_bCancelar(Epcontrol $e) {
    $this->ptw = null;

    $this->pai->preparar_saida($e->ajax());

    return $e;
  }

  public function operacion_bAceptar (Epcontrol $e) {
    if (!$this->validar()) {
      $this->preparar_saida($e->ajax());

      return $e;
    }


    $cbd = new FS_cbd();

    $c = new Cambio_invitaweb_obd($this->id_cambio, 168);

    $c->atr("id_site" )->valor = $e->id_site;
    $c->atr("email"   )->valor = $this->obxeto("email_inv")->valor();
    $c->atr("nome"    )->valor = $this->obxeto("nome_inv")->valor();
    $c->atr("permisos")->valor = FGS_usuario::valida_permisos( $this->obxeto("permisos_inv")->valor() );

    $cbd->transaccion();

    if (!$c->insert($cbd)) {
      $cbd->rollback();

      $e->post_msx("Error al intentar escribir en la BD.");


      return $e;
    }

    if ($this->tipo == 2) {
      $u = FGS_usuario::inicia($cbd, $this->id_usuario);

      if (!$u->bloquear($cbd)) {
        $cbd->rollback();

        $e->post_msx("Error al intentar escribir en la BD.");

        return $e;
      }
    }

    $cbd->commit();



    try {
      self::__email($cbd, $c, $this->obxeto("sidioma")->valor());
    }
    catch (Exception $ex) {
      $e->post_msx( "<pre>{$ex}</pre>" );

      return $e;
    }


    return $this->operacion_bCancelar($e);
  }

  private static function __email(FS_cbd $cbd, Cambio_invitaweb_obd $c, $id_idioma) {
    $s          = Site_obd::inicia($cbd, $c->atr("id_site")->valor);

    $idioma     = Idioma::factory($id_idioma);

    $id_site    = $s->atr("id_site"  )->valor;

    $dominio    = TrwlConfig::dominio;

    $codigo     = $c->atr("id_cambio")->valor;
    $email      = $c->atr("email"    )->valor;
    $nome       = $c->atr("nome"     )->valor;

    $html_legal = LectorPlantilla::plantilla(Efs::url_legais($id_site) . "/lopd_email.html", $idioma);


    $html = LectorPlantilla::plantilla(self::ptw_7, $idioma);

    $html = str_replace("[nome]", $nome, $html);
    $html = str_replace("[link_enlace]", "<a href='http://{$dominio}/confirmar.php?c={$codigo}'>http://{$dominio}/confirmar.php?c={$codigo}</a>", $html);
    $html = str_replace("[dominio]", "{$dominio}", $html);
    $html = str_replace("[legal]", $html_legal, $html);

    try {
    //  $m = new XMail2();
      $m = new XMail3($id_site);

      (($_x = $m->_config()) != [])
      ? $m->pon_rmtnt($_x['email'])
      : $m->pon_rmtnt("no-reply@{$dominio}"); 

      $m->pon_asunto   (Msx::$clogin[$id_idioma][8] . $dominio);
      $m->pon_direccion($email               );
      $m->pon_corpo    ($html                );
      $m->enviar();
    }
    catch (Exception $exc) {
      throw $exc;
    }
   
  }

  private static function __sidioma($id_site) {
    $s = new Select("sidioma", MLsite_obd::__a_idiomas($id_site));

    $s->post( MLsite_obd::idioma_predeterminado($id_site) );

    return $s;
  }
}
