<?php

final class PControl extends Panel {
  private $u = null;

  public function __construct(FGS_usuario $u) {
    parent::__construct("pcontrol", "ptw/paneis/pcontrol/pcontrol.html");

    $this->u = $u;

//~ echo "<pre>" . print_r($u->a_resumo(), true) . "</pre>";
  }

  public function declara_obxetos() {
    $p         = $this->u->atr("permisos"  )->valor;

    $bweb_0    = self::__bPth("bweb_0"   , "Mi Web");
    $bconfig_0 = self::__bPth("bconfig_0", "Configuraci&oacute;n");
    $bstats_0  = self::__bPth("bstats_0" , "Estad&iacute;sticas");
    $btenda_0  = self::__bPth("btenda_0" , "Mi Tienda");
    $bdrive_0  = self::__bPth("bdrive_0" , "Mis Documentos");
    $blogs_0   = self::__bPth("blogs_0"  , "Accesos");
    $bapis_1   = self::__bPth("bapis_1"  , "APIs");

    $bweb_0   ->visible = $p == "admin"; //* admin
    $bconfig_0->visible = true; //* admin ou triwus-correo
    //~ $bstats_0 ->visible = $p == "admin"; //* admin
    $bstats_0 ->visible = false; //* non se usa en triwuslite.
    $btenda_0 ->visible = $this->u->venta(); //* siteventas e vendedor
    $bdrive_0 ->visible = $p == "admin"; //* admin
    $blogs_0  ->visible = $p == "admin"; //* admin
    $bapis_1  ->visible = $p == "admin"; //* admin

    return array($bweb_0,
                 $bconfig_0,
                 $btenda_0, $bdrive_0, $blogs_0, $bapis_1, $bstats_0
                );
  }

  private static function __bPth($id, $etq, $title = "") {
    $l = new Link($id, $etq);

    $l->clase_css("default", "xp_pestanha_inactiva");

    $l->pon_eventos("onmouseover", "this.style.textDecoration='underline'");
    $l->pon_eventos("onmouseout" , "this.style.textDecoration='none'"     );
    $l->pon_eventos("onclick"    , "document.location.href = '?r={$id}'"  );

    return $l;
  }

}

//*************************************

abstract class Cpc_index extends Componente {
  //~ protected $id_plesk   = -1;

  public function __construct() {
    parent::__construct("cpc_index", "ptw/paneis/pcontrol/cpc_index.html");

    $this->pon_obxeto(new Param("titulo"    , $this->__titulo()));
    $this->pon_obxeto(new Param("descricion", $this->__descricion()));

    foreach($this->__a_bpc() as $k=>$bpc) $this->pon_obxeto($bpc, $k + 1);
  }

  abstract protected function __a_bpc();
  abstract protected function __titulo();
  abstract protected function __descricion();
  abstract protected function operacion_bcpc(Epcontrol $e);

  public function readonly($b = true) {
    parent::readonly($b);

    $a_bpcindex = $this->obxetos("bpcindex");

    foreach($a_bpcindex as $bpc) $bpc->readonly = false;
  }

  public function config_usuario(FGS_usuario $u) {
    //~ $splesk = Site_plesk_obd::inicia(new FS_cbd(), $u);

    //~ $this->id_plesk = $splesk->atr("id_plesk")->valor;
  }

  public function operacion(EstadoHTTP $e) {
    if (($e_aux = $this->operacion_bcpc($e)) != null) return $e_aux;

    return null;
  }

  public function preparar_saida(Ajax $a = null) {
    $html_botones = ""; foreach($this->obxetos("bpcindex") as $bpcindex) $html_botones .= $bpcindex->html();

    $html = str_replace("[a_botones]", $html_botones, $this->html00());

    $this->obxeto("illa")->post($html);

    if ($a == null) return;


    $a->pon($this->obxeto("illa"));

    $this->obxeto("illa")->post(null);
  }

  final public static function factory(Epcontrol $e, $id) {
    if (($cpc = $e->obxeto($id)) != null) return $cpc;

    $u = $e->usuario();
    $s = $e->id_site();
    $a = $u->atr("id_almacen")->valor;


        if ($id == "cplantilla")     $cpc = new CPlantilla    ($u);
    elseif ($id == "xpax")           $cpc = new XPaxinas      ($u);
    elseif ($id == "cconfig_ventas") $cpc = new CConfig_ventas($s);
    elseif ($id == "cxarticulos")    $cpc = new Cxarticulos   ($s, $a);
    elseif ($id == "cxclientes")     $cpc = new Cxclientes    ($s);
    elseif ($id == "curexistrados")  $cpc = new CURexistrados ($s);
    elseif ($id == "cxpedidos")      $cpc = new Cxpedidos     ($s, $a);
    elseif ($id == "cxfpago")        $cpc = new Cxfpago       ($s);
    elseif ($id == "cusuario")       $cpc = new CVersion      ($s);
    elseif ($id == "cmail")          $cpc = new CMail         ($u);
    elseif ($id == "cel")            $cpc = new CEditor_legais($s);
    elseif ($id == "credessoc")      $cpc = new CRedesSoc     ($e->__site_obd());
    elseif ($id == "cmetatags")      $cpc = new CMetatags     ($s);
    elseif ($id == "cxpromos")       $cpc = new Cxpromos      ($s);
    elseif ($id == "cxalmacen")      $cpc = new Cxalmacen     ($s, $a);
    elseif ($id == "cxmarca")        $cpc = new Cxmarca       ($s, $a);
    elseif ($id == "cxcategoria")    $cpc = new Cxcategoria   ($s);
    elseif ($id == "cxcc")           $cpc = new Cxcamposcustom($s, $a);
    elseif ($id == "cml")            $cpc = new CMultilinguaxe($s);
    elseif ($id == "carquivo")       $cpc = new CArquivo      ($u);
    elseif ($id == "cdropbox")       $cpc = new CDropbox      ($s);
    elseif ($id == "cstats")         $cpc = new Cstats        ($s);
    elseif ($id == "triwus")         $cpc = new Clogs_triwus  ($s);
    elseif ($id == "apache")         $cpc = new Clogs_apache  ($s);
    elseif ($id == "capis")          $cpc = new CApis         ($s);
    elseif ($id == "ccliemail")      $cpc = new CCliEmail     ($u);
    elseif ($id == "capitriwus")     $cpc = new CApiTriwus    ($s);
    elseif ($id == "capidbox")       $cpc = new CApiDbox      ($s);
    elseif ($id == "capifscript")    $cpc = new CFscripts     ($s);
    elseif ($id == "cpwa")           $cpc = new CPwa          ($s);
    else                             die ("Cpc_index::factory(), id desconhecido");

    //~ $cpc->readonly($e->en_custodia);

    $e->pon_obxeto($cpc);

    return $cpc;
  }

  final protected static function __bcpc_index($href, $src, $txt1, $txt2, $activo = true) {
    $etq = "<div class='pcontrol_bindex_marco'>
              <div class='pcontrol_bindex_col'>
                <div><img class='pcontrol_bindex_imx' src='{$src}' /></div>
                <div class='pcontrol_bindex_txt1' >{$txt1}</div>
                <div class='pcontrol_bindex_txt2' >{$txt2}</div>
              </div>
            </div>";

    $b = new Button("bpcindex", $etq);

    $b->clase_css("default" , "boton_apagado");
    $b->clase_css("readonly" , "boton_apagado_r");

    $b->pon_eventos("onmouseover", "this.className = 'boton_apagado_over'");
    $b->pon_eventos("onmouseout", "this.className = 'boton_apagado'");

        if (!$activo     ) $b->pon_eventos("onclick", "alert('Perdone las molestias, esta utilidad estará disponible en breve.')");
    elseif ($href != null) $b->pon_eventos("onclick", "document.location.href = '?r={$href}'");

    return $b;
  }

  private function operacion_config(Epcontrol $e) {
    return null;
  }
}

//-----------------------------------------------

final class Cpcindex_dweb extends Cpc_index {
  public function __construct() {
    parent::__construct();
  }

  protected function __a_bpc() {
    return array(self::__bcpc_index("bxpax"     , "imx/pcontrol/cpc_index/design/d_paginas.gif"           , "Secciones"        , "Crea, modifica o elimina, las secciones de tu p&aacute;gina web."),
                 self::__bcpc_index("bmetatags" , "imx/pcontrol/cpc_index/design/c_basico.gif"            , "Básico"           , "Escoge las palabres clave y la manera de indexar tu web en los motores de busqueda."),
                 self::__bcpc_index(null        , "imx/pcontrol/cpc_index/design/d_edicion.gif"           , "Edici&oacute;n"   , "Edita c&oacute;modamente el contenido a tus p&aacute;ginas."),
                 self::__bcpc_index("bplantilla", "imx/pcontrol/cpc_index/design/d_estilo.gif"            , "Dise&ntilde;o Web", "Escoge un esquema gr&aacute;fico para comenzar a dise&ntilde;ar tu web."));
  }

  protected function __titulo() {
    return "Dise&ntilde;o Web";
  }

  protected function __descricion() {
    return "Controla y configura todo lo relacionado con tu p&aacute;gina web, como el dise&ntilde;o, p&aacute;ginas, ... etc.";
  }

  protected function operacion_bcpc(Epcontrol $e) {
    if ($this->obxeto("bpcindex", 1)->control_evento()) return $e->operacion_cpcactivo(Cpc_index::factory($e, "xpax"));
    if ($this->obxeto("bpcindex", 2)->control_evento()) return $e->operacion_cpcactivo(Cpc_index::factory($e, "cusuario"));
    if ($this->obxeto("bpcindex", 3)->control_evento()) return $e->op_editar();
    if ($this->obxeto("bpcindex", 4)->control_evento()) return $e->operacion_cpcactivo(Cpc_index::factory($e, "cplantilla"));

    return null;
  }
}

//-----------------------------------------------

final class Cpcindex_tenda extends Cpc_index {
  public function __construct() {
    parent::__construct();
  }

  public function config_usuario(FGS_usuario $u) {
    parent::config_usuario($u);

    if (($a = $u->atr("id_almacen")->valor) < 2) return;


    $this->obxeto("bpcindex", 2 )->visible = false;
    $this->obxeto("bpcindex", 4 )->visible = false;
    //~ $this->obxeto("bpcindex", 6 )->visible = false;
    $this->obxeto("bpcindex", 7 )->visible = false;
    $this->obxeto("bpcindex", 8 )->visible = false;
    $this->obxeto("bpcindex", 9 )->visible = false;
  }

  protected function __a_bpc() {
    return array(self::__bcpc_index("bxarticulos", "imx/pcontrol/cpc_index/tienda/t_gestor.gif"    , "Productos"         , "Añade, crea y modifica los productos mostrados en tu tienda on-line."   ),
                 self::__bcpc_index("bxcategoria", "imx/pcontrol/cpc_index/tienda/t_categoria.png" , "Categorías"        , "Gestiona tus categorías de productos."                                  ),
                 self::__bcpc_index("bxcc"       , "imx/pcontrol/cpc_index/tienda/t_perfil.png"    , "Campos cústom"     , "Crea campos a medida para tus productos."                               ),
                 self::__bcpc_index("bxpromos"   , "imx/pcontrol/cpc_index/tienda/t_promos.png"    , "Promociones"       , "Crea promociones especiales de ventas."                                 ),
                 self::__bcpc_index("bxalmacen"  , "imx/pcontrol/cpc_index/tienda/t_alma_porte.png", "Portes y almacenes", "Configura tus almacenes y los gastos de envío asociados."               ),
                 //~ self::__bcpc_index("bxclientes" , "imx/pcontrol/cpc_index/tienda/t_clientes.gif"  , "Clientes"          , "Puedes consultar los de clientes asociados a tu tienda."                ),
                 self::__bcpc_index("bxpedidos"  , "imx/pcontrol/cpc_index/tienda/t_factura.gif"   , "Pedidos"           , "Gestiona los pedidos de tu tienda en internet."                         ),
                 self::__bcpc_index("bxfpago"    , "imx/pcontrol/cpc_index/tienda/t_pago.gif"      , "Formas de Pago"    , "Define las distintas formas de pago permitidas en tu tienda."           ),
                 self::__bcpc_index("bxmarca"    , "imx/pcontrol/cpc_index/tienda/t_marcas.png"    , "Marcas"            , "Define las marcas de tu tienda."                                        ),
                 self::__bcpc_index("bmiscelanea", "imx/pcontrol/cpc_index/tienda/t_general.gif"   , "Miscelánea"        , "Configuraciones varias relacionadas con tu tienda on-line."             )
                );
  }

  protected function __titulo() {
    return "Mi Tienda";
  }

  protected function __descricion() {
    return "Controla y configura todo lo relacionado con tu tienda en internet.";
  }

  protected function operacion_bcpc(Epcontrol $e) {
    if ($this->obxeto("bpcindex", 1 )->control_evento()) return $e->operacion_cpcactivo(Cpc_index::factory($e, "cxarticulos"));
    if ($this->obxeto("bpcindex", 2 )->control_evento()) return $e->operacion_cpcactivo(Cpc_index::factory($e, "cxcategoria"));
    if ($this->obxeto("bpcindex", 3 )->control_evento()) return $e->operacion_cpcactivo(Cpc_index::factory($e, "cxcc"));
    if ($this->obxeto("bpcindex", 4 )->control_evento()) return $e->operacion_cpcactivo(Cpc_index::factory($e, "cxpromos"));
    if ($this->obxeto("bpcindex", 5 )->control_evento()) return $e->operacion_cpcactivo(Cpc_index::factory($e, "cxalmacen"));
    //~ if ($this->obxeto("bpcindex", 6 )->control_evento()) return $e->operacion_cpcactivo(Cpc_index::factory($e, "cxclientes"));
    if ($this->obxeto("bpcindex", 6 )->control_evento()) return $e->operacion_cpcactivo(Cpc_index::factory($e, "cxpedidos"));
    if ($this->obxeto("bpcindex", 7 )->control_evento()) return $e->operacion_cpcactivo(Cpc_index::factory($e, "cxfpago"));
    if ($this->obxeto("bpcindex", 8 )->control_evento()) return $e->operacion_cpcactivo(Cpc_index::factory($e, "cxmarca"));
    if ($this->obxeto("bpcindex", 9 )->control_evento()) return $e->operacion_cpcactivo(Cpc_index::factory($e, "cconfig_ventas"));

    return null;
  }
}

//-----------------------------------------------

final class Cpcindex_config extends Cpc_index {
  public function __construct() {
    parent::__construct();
  }

  public function config_usuario(FGS_usuario $u) {
    parent::config_usuario($u);

    $this->obxeto("bpcindex", 1)->visible = false;
    $this->obxeto("bpcindex", 5)->visible = false;

    if (($u->atr("id_sb")->valor >= 1000) && ($u->atr("id_sb")->valor <= 1001)) {
      $this->obxeto("bpcindex", 2)->visible = false;
      $this->obxeto("bpcindex", 3)->visible = false;
      $this->obxeto("bpcindex", 6)->visible = false;
      $this->obxeto("bpcindex", 7)->visible = false;
    }
  }

  protected function __a_bpc() {
    return array(self::__bcpc_index(null           , "imx/pcontrol/cpc_index/configuracion/c_mail.gif"       , "E-mail"              , "Configura, añade o elimina tus cuentas de correo electrónico."),
                 self::__bcpc_index("bredessoc"    , "imx/pcontrol/cpc_index/configuracion/c_social.gif"     , "Redes Sociales"      , "Decide que redes sociales quieres conectar con tu página web."),
                 self::__bcpc_index("bml"          , "imx/pcontrol/cpc_index/configuracion/c_idioma.gif"     , "Idiomas"             , "Añade o elimina versiones de tu web en distintos idiomas."),
                 self::__bcpc_index("bversion"     , "imx/pcontrol/cpc_index/configuracion/c_user.gif"       , "Versión de triwus"   , "Mantén actualizado tu site"),
                 self::__bcpc_index(null           , "imx/pcontrol/cpc_index/configuracion/c_certificado.gif", "Certificados SSL"    , "Gestiona el certificado de seguridad asociado a tu Plan Triwus.", false),
                 self::__bcpc_index("bel"          , "imx/pcontrol/cpc_index/configuracion/c_legales.gif"    , "Avisos legales"      , "Edita los textos legales obligatorios que proporcionas a tus usuarios."),
                 self::__bcpc_index("burexistrados", "imx/pcontrol/cpc_index/configuracion/c_users.gif"      , "Usuarios Registrados", "Mantenimiento de usuarios registrados en tu sitio web.")
                );
  }

  protected function __titulo() {
    return "Configuraci&oacute;n";
  }

  protected function __descricion() {
    return "Configura tu plan Triwus";
  }

  protected function operacion_bcpc(Epcontrol $e) {
    //~ if ($this->obxeto("bpcindex", 1)->control_evento()) return $e->operacion_cpcactivo(Cpc_index::factory($e, "cmail"));
    if ($this->obxeto("bpcindex", 2)->control_evento()) return $e->operacion_cpcactivo(Cpc_index::factory($e, "credessoc"));
    if ($this->obxeto("bpcindex", 3)->control_evento()) return $e->operacion_cpcactivo(Cpc_index::factory($e, "cml"));
    if ($this->obxeto("bpcindex", 4)->control_evento()) return $e->operacion_cpcactivo(Cpc_index::factory($e, "cmetatags"));
    if ($this->obxeto("bpcindex", 5)->control_evento()) return $e->operacion_cpcactivo(Cpc_index::factory($e, "cel"));
    if ($this->obxeto("bpcindex", 6)->control_evento()) return $e->operacion_cpcactivo(Cpc_index::factory($e, "curexistrados"));


    return null;
  }
}

//-----------------------------------------------

final class Cpcindex_accesos extends Cpc_index {
  public function __construct() {
    parent::__construct();
  }

  public function config_usuario(FGS_usuario $u) {
    parent::config_usuario($u);
  }

  protected function __a_bpc() {
    return array(self::__bcpc_index("blogs_triwus", "imx/pcontrol/cpc_index/accesos/accesos_trw.jpg", "Registro triwus", "Registro de accesos de triwus.")
                );
  }

  protected function __titulo() {
    return "Accesos";
  }

  protected function __descricion() {
    return "Registro de accesos";
  }

  protected function operacion_bcpc(Epcontrol $e) {
    if ($this->obxeto("bpcindex", 1)->control_evento()) return $e->operacion_cpcactivo(Cpc_index::factory($e, "clogs_triwus"));


    return null;
  }
}

//-----------------------------------------------

final class Cpcindex_docs extends Cpc_index {
  public function __construct() {
    parent::__construct();
  }

  public function config_usuario(FGS_usuario $u) {
    parent::config_usuario($u);
  }

  protected function __a_bpc() {
     return array(self::__bcpc_index("bdocs_triwus", "imx/pcontrol/cpc_index/documentos/unidad_trw.jpg", "Mis documentos", "Tu unidad en triwus.")
                );
  }

  protected function __titulo() {
    return "Mis Documentos";
  }

  protected function __descricion() {
    return "Configura y accede a tus documentos";
  }

  protected function operacion_bcpc(Epcontrol $e) {
    if ($this->obxeto("bpcindex", 1)->control_evento()) return $e->operacion_cpcactivo(Cpc_index::factory($e, "carquivo" ));

    return null;
  }
}

//-----------------------------------------------

final class Cpcindex_apis extends Cpc_index {
  public function __construct() {
    parent::__construct();
  }

  public function config_usuario(FGS_usuario $u) {
    parent::config_usuario($u);
  }

  protected function __a_bpc() {
    return array(self::__bcpc_index("bapis_triwus" , "imx/pcontrol/cpc_index/apis/d_triwus.gif"  , "Triwus"        , "Conexión API triwus "),
                 self::__bcpc_index("bapis_fscript", "imx/pcontrol/cpc_index/apis/d_fscripts.gif", "Facturascripts", "Descargar el plugin triwus para Facturascripts"),
                 self::__bcpc_index("bcliemail"    , "imx/pcontrol/cpc_index/apis/c_smtp.jpg"    , "Cliente SMTP"  , "Confiura un cliente SMTP, para el envío de emails desde tu site."),
                 self::__bcpc_index("bapis_pwa", "imx/pcontrol/cpc_index/apis/d_pwa.png"         , "PWA"           , "Configura tu web como aplicación móvil"),
                );
  }

  protected function __titulo() {
    return "APIs";
  }

  protected function __descricion() {
    return "Configura conexiones con APIs";
  }

  protected function operacion_bcpc(Epcontrol $e) {
    if ($this->obxeto("bpcindex", 1)->control_evento()) return $e->operacion_cpcactivo(Cpc_index::factory($e, "capitriwus" ));
    if ($this->obxeto("bpcindex", 2)->control_evento()) return $e->operacion_cpcactivo(Cpc_index::factory($e, "capifscript"));
    if ($this->obxeto("bpcindex", 3)->control_evento()) return $e->operacion_cpcactivo(Cpc_index::factory($e, "bcliemail"));
    if ($this->obxeto("bpcindex", 4)->control_evento()) return $e->operacion_cpcactivo(Cpc_index::factory($e, "cpwa"));

    return null;
  }
}

//-----------------------------------------------

final class Cpcindex_axuda extends Cpc_index {
  public function __construct() {
    parent::__construct();
  }

  public function config_usuario(FGS_usuario $u) {
    parent::config_usuario($u);
  }

  protected function __a_bpc() {
    return array(self::__bcpc_index("baxuda_con" , "imx/pcontrol/cpc_index/apis/d_triwus.gif"  , "Contacto"     , "Solicita asistencia"),
                 self::__bcpc_index("baxuda_wiki", "imx/pcontrol/cpc_index/apis/d_fscripts.gif", "Documentación", "Revisa los manuales")
                );
  }

  protected function __titulo() {
    return "Ayuda";
  }

  protected function __descricion() {
    return "Solicita asistencia o revisa la documentación.";
  }

  protected function operacion_bcpc(Epcontrol $e) {
    if ($this->obxeto("bpcindex", 1)->control_evento()) return $e->operacion_cpcactivo(Cpc_index::factory($e, "caxuda_con" ));
    if ($this->obxeto("bpcindex", 2)->control_evento()) return $e->operacion_cpcactivo(Cpc_index::factory($e, "caxuda_wiki"));

    return null;
  }
}
