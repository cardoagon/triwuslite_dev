<?php



if (isset($_SERVER['HTTPS'      ])) putenv("server.https={$_SERVER['HTTPS']}"     );
if (isset($_SERVER['SERVER_NAME'])) putenv("server.nome={$_SERVER['SERVER_NAME']}");

if (!isset($fs_home)) $fs_home = "";

$tilia_home = "{$fs_home}tilia/";

putenv("fs_home={$fs_home}");
putenv("Tilia_HOME={$tilia_home}");



//* Interfaces

require "php/control/xefspax/IEfspax_xestor.php";

require "php/vista/I_fs.php";

require "php/modelo/IModelo.php";


//* Constantes

require "Config.php";

require "php/vista/Erro.php";
require "php/vista/Ico.php";
require "php/vista/Msx.php";


//* Clases

require "php/control/xefspax/Efspax_xestor.php";

require "php/control/Refs.php";

require "php/control/Efs.php";
require "php/control/Esubfs.php";
require "php/control/Efs_paxina.php";
require "php/control/XMail2.php";
require "php/control/XMail3.php";
require "php/control/Mobile_Detect.php";


require "php/html2pdf/html2pdf.class.php";
//~ require "php/html2pdf-2/autoload.php";

require "php/vista/fs_lista/FS_lista.php";
require "php/vista/fs_lista/FS_ehtml.php";
require "php/vista/fs_lista/CLista_pedfra.php";
require "php/vista/fs_lista/FSL_navelmt.php";
require "php/vista/Control_js.php";
require "php/vista/CIdioma.php";

require "php/vista/Panel_fs.php";
require "php/vista/Panel_fieldsets.php";

require "php/vista/ccontacto/CContacto.php";
require "php/vista/ccontacto/CFormasPago.php";

require "php/vista/clogin/CLogin.php";
require "php/vista/clogin/CLoginWeb.php";
require "php/vista/clogin/CLogin_pcontrol.php";

require "php/vista/cxcli3/CXCli-2.php";
require "php/vista/cxcli3/CXCli2_baixa.php";
require "php/vista/cxcli3/CXCli2panel_servizos.php";
require "php/vista/cxcli3/CXCli2panel_1.php";
require "php/vista/cxcli3/CXCli2panel_2.php";
require "php/vista/cxcli3/CXCli2_feedback.php";
require "php/vista/cxcli3/CXCli2_pechar.php";
require "php/vista/cxcli3/CLWxcli_lped.php";

//~ require "php/vista/ccs/CCS.php";
//~ require "php/vista/ccs/CCS_editor.php";
require "php/vista/ccs/XCCS.php";


require "php/rpc/paypal_2/PayPal_2.php";


require "php/vista/cpagar3/CCarro2.php";
require "php/vista/cpagar3/CPagar2.php";
require "php/vista/cpagar3/CPagar2_ml.php";
require "php/vista/cpagar3/CPagar2_codpromo.php";
require "php/vista/cpagar3/CPagar2_prefpago.php";
require "php/vista/cpagar3/CPagar2_envio.php";
require "php/vista/cpagar3/CPagar2_email.php";
require "php/vista/cpagar3/CPagar2_fin.php";

require "php/vista/btnr/Btnr.php";
require "php/vista/btnr/Btnr_detalle_este.php";
require "php/vista/btnr/Btnr_detalle_oeste.php";
require "php/vista/btnr/Btnr_detalle_norte.php";
require "php/vista/btnr/Btnr_elmt.php";
require "php/vista/btnr/Btnr_prgf.php";
//~ require "php/vista/btnr/CBorrador.php";

require "php/vista/CBuscador.php";

require "php/vista/CRS.php";

require "php/vista/CCookies.php";

require "php/vista/detalle/Detalle.php";
require "php/vista/detalle/Detalle_libre.php";
require "php/vista/detalle/Detalle_cxcli.php";
require "php/vista/detalle/Detalle_novas.php";
require "php/vista/detalle/Detalle_novas2.php";
require "php/vista/detalle/Detalle_marcas.php";
require "php/vista/detalle/Detalle_ventas.php";
//~ require "php/vista/detalle/Detalle_ventas2.php";
require "php/vista/detalle/Detalle_ventas2b.php";
require "php/vista/detalle/Detalle_reservado.php";

require "php/vista/detalle/cat/Catalogo.php";
require "php/vista/detalle/cat/Catalogo_p.php";
require "php/vista/detalle/cat/Catalogo_n.php";

require "php/vista/detalle/style/Editor_style.php";
require "php/vista/detalle/style/Editor_sprgf.php";
require "php/vista/detalle/style/Editor_selmt.php";
require "php/vista/detalle/style/CBorde.php";
require "php/vista/detalle/style/CMarxe.php";
require "php/vista/detalle/style/SColor.php";

require "php/vista/detalle/ms/Link_pax.php";
require "php/vista/detalle/ms/Opcion_ms.php";
require "php/vista/detalle/ms/Menu_site.php";
require "php/vista/detalle/ms/Subms.php";

require "php/vista/admin/CEditor_imx.php";

require "php/vista/admin/clogo/CLogo.php";

require "php/vista/admin/xpax/XPaxinas.php";
require "php/vista/admin/xpax/XPaxinas_loms.php";
require "php/vista/admin/xpax/XPaxinas_loms2.php";
require "php/vista/admin/xpax/CLista_idiomas.php";
require "php/vista/admin/xpax/CPaxnova.php";
require "php/vista/admin/xpax/CPaxdupli.php";
require "php/vista/admin/xpax/CPaxsup.php";
require "php/vista/admin/xpax/paxinfo/Paxinfo.php";
require "php/vista/admin/xpax/paxinfo/Paxinfo_ref.php";
require "php/vista/admin/xpax/paxinfo/Paxinfo_libre.php";
require "php/vista/admin/xpax/paxinfo/Paxinfo_novas.php";
require "php/vista/admin/xpax/paxinfo/Paxinfo_ventas.php";

require "php/vista/detalle/prgf/Editor_prgf.php";
require "php/vista/detalle/prgf/Editor_prgf_novas2.php";
require "php/vista/detalle/prgf/Editor_prgf_venta.php";
require "php/vista/detalle/prgf/Paragrafo.php";
require "php/vista/detalle/prgf/Prgf_config.php";
require "php/vista/detalle/prgf/Prgf_inovas.php";
require "php/vista/detalle/prgf/Prgf_inovas2.php";
require "php/vista/detalle/prgf/Prgf_iventas2.php";  
require "php/vista/detalle/prgf/Prgf_iventas2b.php";  
require "php/vista/detalle/prgf/Prgf_imarcas.php";
require "php/vista/detalle/prgf/Prgf_reservado.php";

require "php/vista/admin/paneis/pcontrol/CValidar_Conta.php";

require "php/vista/detalle/prgf/prgf_elmt/Edita_elmt.php";
require "php/vista/detalle/prgf/prgf_elmt/AElemento.php";
require "php/vista/detalle/prgf/prgf_elmt/Elmt_enlace.php";
require "php/vista/detalle/prgf/prgf_elmt/Elmt_hr.php";
require "php/vista/detalle/prgf/prgf_elmt/Elmt_difucom2.php";
require "php/vista/detalle/prgf/prgf_elmt/Elmt_galeria.php";
require "php/vista/detalle/prgf/prgf_elmt/Elmt_contacto.php";
require "php/vista/detalle/prgf/prgf_elmt/Elmt_paxinador.php";

require "php/vista/detalle/prgf/prgf_elmt/elmt_imaxe/Elmt_imx.php";
require "php/vista/detalle/prgf/prgf_elmt/elmt_imaxe/Elmt_logo.php";
require "php/vista/detalle/prgf/prgf_elmt/elmt_imaxe/Elmt_novas.php";
require "php/vista/detalle/prgf/prgf_elmt/elmt_imaxe/Elmt_carrusel.php";

require "php/trw-qr/phpqrcode/phpqrcode.php";
require "php/trw-qr/QR_elmt_vcard.php";
require "php/vista/detalle/prgf/prgf_elmt/Elmt_vcard.php";

require "php/vista/detalle/prgf/prgf_elmt/elmt_ventas/Elmt_ventas.php";
require "php/vista/detalle/prgf/prgf_elmt/elmt_ventas/Elmt_ventas_cc.php";
require "php/vista/detalle/prgf/prgf_elmt/elmt_ventas/Elmt_ventas_lprto.php";

require "php/vista/detalle/prgf/prgf_elmt/elmt_texto/Elmt_texto2.php";
require "php/vista/detalle/prgf/prgf_elmt/elmt_texto/Elmt_indice.php";
require "php/vista/detalle/prgf/prgf_elmt/elmt_texto/Etxt_parser.php";
require "php/vista/detalle/prgf/prgf_elmt/elmt_texto/ophir.php";


require "php/modelo/FS_cbd.php";

require "php/modelo/logs/Acceso.php";

require "php/modelo/xstat/XStats.php";
require "php/modelo/xstat/Dispositivo_obd.php";
require "php/modelo/xstat/Navegador_obd.php";
require "php/modelo/xstat/Nube_obd.php";
require "php/modelo/xstat/Puntuacion_obd.php";
require "php/modelo/xstat/Visita_obd.php";


require "php/modelo/rpc/Redsys_obd.php";
require "php/modelo/rpc/CECA_obd.php";
require "php/modelo/rpc/PayPal_obd.php";
require "php/modelo/rpc/PayPal_2_obd.php";

require "php/modelo/dominio/Servizo_obd.php";
require "php/modelo/dominio/Cliente_obd.php";
require "php/modelo/dominio/FGS_usuario.php";
require "php/modelo/dominio/Cuenta_obd.php";
require "php/modelo/dominio/Multilinguaxe_obd.php";
require "php/modelo/dominio/Cambio_obd.php";
require "php/modelo/dominio/CRS_obd.php";
require "php/modelo/dominio/Style_obd.php";
require "php/modelo/dominio/Color_obd.php";
require "php/modelo/dominio/Config_obd.php";
//~ require "php/modelo/dominio/XSite_obd.php";
require "php/modelo/dominio/Site_obd.php";
require "php/modelo/dominio/Contacto_obd.php";
require "php/modelo/dominio/Detalle_obd.php";
require "php/modelo/dominio/Menu_site_obd.php";

require "php/modelo/dominio/ventas/Almacen_obd.php";
require "php/modelo/dominio/ventas/CamposCustom_obd.php";
require "php/modelo/dominio/ventas/Articulo_marca_obd.php";
require "php/modelo/dominio/ventas/Articulo_obd.php";
require "php/modelo/dominio/ventas/Articulo_grupo_obd.php";
require "php/modelo/dominio/ventas/Articulo_presuposto_obd.php";
require "php/modelo/dominio/ventas/FPago_obd.php";
require "php/modelo/dominio/ventas/Pedido_obd.php";
require "php/modelo/dominio/ventas/Categoria_obd.php";
require "php/modelo/dominio/ventas/Promo_obd.php";

require "php/modelo/dominio/paxina/Paxina_obd.php";
require "php/modelo/dominio/paxina/Paxina_ref_obd.php";
require "php/modelo/dominio/paxina/Paxina_dl_obd.php";
require "php/modelo/dominio/paxina/Paxina_legal_obd.php";
require "php/modelo/dominio/paxina/Paxina_novas_obd.php"; //* 20241004 borrar.
require "php/modelo/dominio/paxina/Paxina_novas2_obd.php";
require "php/modelo/dominio/paxina/Paxina_marcas_obd.php";
require "php/modelo/dominio/paxina/Paxina_ventas_obd.php"; //* 20210828 borrar.
require "php/modelo/dominio/paxina/Paxina_ventas2_obd.php";
require "php/modelo/dominio/paxina/Paxina_reservada_obd.php";

require "php/modelo/dominio/elmt/Elmt_obd.php";
require "php/modelo/dominio/elmt/Texto_obd.php";
require "php/modelo/dominio/elmt/Iframe_obd.php";
require "php/modelo/dominio/elmt/HR_obd.php";
require "php/modelo/dominio/elmt/Paxinador_obd.php";
require "php/modelo/dominio/elmt/Difucom2_obd.php";
require "php/modelo/dominio/elmt/Galeria_obd.php";
require "php/modelo/dominio/elmt/Elmt_contacto_obd.php";
require "php/modelo/dominio/elmt/Elmt_ventas_obd.php";

require "php/modelo/dominio/elmt/imaxe/Imaxe_obd.php";
require "php/modelo/dominio/elmt/imaxe/Articulo_imx_obd.php";
require "php/modelo/dominio/elmt/imaxe/Ico_obd.php";
require "php/modelo/dominio/elmt/imaxe/Logo_obd.php";
require "php/modelo/dominio/elmt/imaxe/Elmt_novas_obd.php";
require "php/modelo/dominio/elmt/imaxe/Elmt_carrusel_obd.php";

require "php/modelo/dominio/elmt/Elmt_vcard_obd.php";

require "php/modelo/dominio/prgf/Paragrafo_obd.php";
require "php/modelo/dominio/prgf/Cab_obd.php";
require "php/modelo/dominio/prgf/Prgf_inovas_obd.php";
require "php/modelo/dominio/prgf/Prgf_inovas2_obd.php";
//~ require "php/modelo/dominio/prgf/Prgf_iventas2_obd.php"; 
require "php/modelo/dominio/prgf/Prgf_iventas2b_obd.php"; 
require "php/modelo/dominio/prgf/Prgf_reservado_obd.php";
require "php/modelo/dominio/prgf/Pe_obd.php";
require "php/modelo/dominio/prgf/Prgf_comentarios_obd.php";

//~ require "php/rpc/dropbox/DropboxAPI.php";

