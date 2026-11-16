<?php

/*************************************************

    Triwus.Tilia Framework v.0

    manifesto.php

    Author: Carlos Domingo Arias González

    Copyright (C) 2011

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation version 3.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/ at http://www.gnu.org/licenses/>.

*************************************************/


//* configuracion para TRIWUS


require "{$tilia_home}/php/modelo/clientes/Conexion_bd.php";
require "{$tilia_home}/php/modelo/clientes/MySql.php";
require "{$tilia_home}/php/modelo/clientes/Sqlit3.php";

require "{$tilia_home}/php/modelo/escritoresSQL/Obxeto_bd.php";
require "{$tilia_home}/php/modelo/escritoresSQL/Tipo_sql.php";
require "{$tilia_home}/php/modelo/escritoresSQL/Mapa_bd.php";
require "{$tilia_home}/php/modelo/escritoresSQL/Escritor_sql.php";
require "{$tilia_home}/php/modelo/escritoresSQL/Sintaxis_sql.php";
require "{$tilia_home}/php/modelo/escritoresSQL/Sintaxis_MySql.php";
require "{$tilia_home}/php/modelo/escritoresSQL/Sintaxis_Sqlite.php";

require "{$tilia_home}/php/modelo/escritoresResultado/EscritorResultado.php";
require "{$tilia_home}/php/modelo/escritoresResultado/EscritorResultado_csv.php";
require "{$tilia_home}/php/modelo/escritoresResultado/EscritorResultado_html.php";
require "{$tilia_home}/php/modelo/escritoresResultado/EscritorResultado_json.php";
require "{$tilia_home}/php/modelo/escritoresResultado/EscritorResultado_xml.php";

require "{$tilia_home}/php/controlador/util/Cifrado.php";
require "{$tilia_home}/php/controlador/util/Mail.php";
require "{$tilia_home}/php/controlador/util/phpmailer/Mail2.php";
require "{$tilia_home}/php/controlador/util/phpmailer6/Mail3.php";
require "{$tilia_home}/php/controlador/util/LectorPlantilla.php";
require "{$tilia_home}/php/controlador/util/XestorDescargas.php";
require "{$tilia_home}/php/controlador/util/Valida.php";

require "{$tilia_home}/php/controlador/Tilia.php";
require "{$tilia_home}/php/controlador/EstadoHTTP.php";
require "{$tilia_home}/php/controlador/EventoHTTP.php";
require "{$tilia_home}/php/controlador/ActionAJAX.php";

require "{$tilia_home}/php/controlador/util/Zip_carpeta.php";

require "{$tilia_home}/php/controlador/util/idiomas/Idioma.php";
require "{$tilia_home}/php/controlador/util/idiomas/Aleman.php";
require "{$tilia_home}/php/controlador/util/idiomas/Catalan.php";
require "{$tilia_home}/php/controlador/util/idiomas/Espanhol.php";
require "{$tilia_home}/php/controlador/util/idiomas/English.php";
require "{$tilia_home}/php/controlador/util/idiomas/Frances.php";
require "{$tilia_home}/php/controlador/util/idiomas/Galego.php";
require "{$tilia_home}/php/controlador/util/idiomas/Portugues.php";

require "{$tilia_home}/php/vista/Escritor_html.php";

require "{$tilia_home}/php/vista/Paxina.php";

require "{$tilia_home}/php/vista/Formulario.php";

require "{$tilia_home}/php/vista/ObxetoFormulario.php";

require "{$tilia_home}/php/vista/Param.php";

require "{$tilia_home}/php/vista/AJAX-2.php";

require "{$tilia_home}/php/vista/controis/Control.php";
require "{$tilia_home}/php/vista/controis/Input.php";
require "{$tilia_home}/php/vista/controis/Button.php";
require "{$tilia_home}/php/vista/controis/Capa.php";
require "{$tilia_home}/php/vista/controis/Etiqueta.php";
require "{$tilia_home}/php/vista/controis/IFrame.php";
require "{$tilia_home}/php/vista/controis/Image.php";
require "{$tilia_home}/php/vista/controis/Link.php";
require "{$tilia_home}/php/vista/controis/Select.php";
require "{$tilia_home}/php/vista/controis/Textarea.php";
//~ require "{$tilia_home}/php/vista/controis/InputFile_Ajax.php"; // 25/04/2016

require "{$tilia_home}/php/vista/componentes/Componente.php";
require "{$tilia_home}/php/vista/componentes/cknovo/Cknovo.php";
require "{$tilia_home}/php/vista/componentes/ceditor/CEditor.php";
require "{$tilia_home}/php/vista/componentes/ceditor/CEditor_2.php";
require "{$tilia_home}/php/vista/componentes/clista/CLista.php";
require "{$tilia_home}/php/vista/componentes/clista/CLista_ehtml.php";
require "{$tilia_home}/php/vista/componentes/ventanaModal/VentanaModal.php";
require "{$tilia_home}/php/vista/componentes/ventanaModal/Alert.php";
require "{$tilia_home}/php/vista/componentes/ventanaModal/Confirm.php";
require "{$tilia_home}/php/vista/componentes/ventanaModal-2/VentanaModal-2.php";
require "{$tilia_home}/php/vista/componentes/ventanaModal-2/Alert-2.php";
require "{$tilia_home}/php/vista/componentes/ventanaModal-2/Confirm-2.php";

require "{$tilia_home}/php/vista/paneis/Panel.php";
require "{$tilia_home}/php/vista/paneis/Panel_pestanhas.php";
require "{$tilia_home}/php/vista/paneis/Panel_multipaso.php";

