<?php


final class Erro {
  const baleiro_titulo        = "&nbsp;&nbsp;&bull;&nbsp;Debes teclear un nombre de espacio web válido.";
  const nome_site_corto       = "&larr;&nbsp;El nombre debe tener al menos 2 caracteres";
  const nome_site_protexido   = "&larr;&nbsp;Nombre no permitido";
  const nome_site_incorrecto  = "&larr;&nbsp;Nombre no válido, <u>te sugerimos uno nuevo</u>";
  const nome_site_incorrecto2 = "Nombre no válido, <u>te sugerimos uno nuevo</u>, si estás de acuerdo pulsa [Aceptar]";
  const nome_site_ocupado2    = "El nombre elegido ya está usado por otro espacio web, por favor, escoje otro";

  public static $a_nome_pax_reservado = array("buscador"=>1, "legal"=>1, "login"=>1, "pago"=>1, "perfil"=>1, "confirmar"=>1, "producto"=>1);

  const nome_pax_0 = "Debe escribir el t&iacute;tulo de la página";
  const nome_pax_1 = "Nombre de página reservado, debes escribir otro nombre.";
  const nome_pax_2 = "Nombre de página duplicado, debes escribir otro nombre.";


  const ccs_lconfirm    = "&nbsp;&nbsp;&bull;&nbsp;Se produjo un error trantando de crear el codigo de confirmación. Por favor prueba otra vez.";
  const ccs_ru_diferido = "&nbsp;&nbsp;&bull;&nbsp;Se produjo un error trantando de guardar tu solicitud de registro. Por favor prueba otra vez.";

  const email_senNome   = "Error, debes teclear un nombre para el email.";
  const email_igual     = "Error, la dirección de correo electrónico es igual a la dirección antigua.";

  const fcontacto_temail    = "Error: debe teclear un emáil válido.";

  const fsuscripcion_temail   = "Error: el nombre de la lista no es válido.";
  const fsuscripcion_chactivo = "Error: debe aceptar nuestra pol&iacute;tica de protección de datos.";

  const cpago_0  = "Debe rellenar todos las casillas marcadas con (*).<br />\n";
  const cpago_1  = "No hay productos en tu carro de la compra.<br />\n";
  const cpago_2  = "Debe seleccionar una forma de pago.<br />\n";
  const cpago_3  = "Cod. Postal no válido.<br />\n";
  //~ const cpago_4  = "Cod. Postal no concuerda con la Provincia.<br />\n";
  const cpago_5  = "Debe seleccionar el local de recogida de su pedido.<br />\n";
  //~ const cpago_6  = "Nº de cuenta corriente, (IBAN), no es válido.<br />\n";
  const cpago_7  = "Debe aceptar las condiciones de servicio.<br />\n";
  const cpago_8  = "Error, sucedió un error al guardar el pedido, pedido no recibido.<br />\n";
  const cpago_9  = "Número NIF/CIF/NIE no válido.<br />\n";
  //~ const cpago_10 = "Error al solicitar los portes.<br />\n";
  const cpago_11 = "Error, correo electrónico no válido.<br />\n";
  //~ const cpago_12 = "Dato obligatorio.<br />\n";
  const cpago_13 = "Número de teléfono no válido.<br />\n";



  public static $upload = array("1"=> array("es"=>"Error: el archivo supera el tama&ntilde;o máx. permitido",
                                            "ca"=>"Error: l’arxiu supera la mida màx. permesa",
                                            "fr"=>"Erreur: le fichier dépasse max. autorisé",
                                            "gl"=>"Erro: o arquivo supera o tama&ntilde;o máx. permitido",
                                            "pt"=>"Erro: o arquivo excede o tamanho máximo permitido",
                                            "en"=>"Error: The file exceeds the maximum size allowed",
                                            "de"=>"Fehler: Die Datei überschreitet die maximal zulässige Größe."
                                            ),
                                "2"=> array("es"=>"Error: el archivo supera el tama&ntilde;o máx. permitido",
                                            "ca"=>"Error: l’arxiu supera la mida màx. permesa",
                                            "fr"=>"Erreur: le fichier dépasse max. autorisé",
                                            "gl"=>"Erro: o arquivo supera o tama&ntilde;o máx. permitido",
                                            "pt"=>"Erro: o arquivo excede o tamanho máximo permitido",
                                            "en"=>"Error: The file exceeds the maximum size allowed",
                                            "de"=>"Fehler: Die Datei überschreitet die maximal zulässige Größe."
                                          ),
                                "3"=> array("es"=>"Error: archivo recibido parcialmente",
                                            "ca"=>"Error: arxiu rebut parcialment",
                                            "fr"=>"Erreur: fichier partiellement reçu",
                                            "gl"=>"Erro: arquivo recibido parcialmente",
                                            "pt"=>"Erro: arquivo recebido parcialmente",
                                            "en"=>"Error: File partially received",
                                            "de"=>"Fehler: Datei teilweise empfangen"
                                          ),
                                "4"=> array("es"=>"Error: no se recibió el archivo",
                                            "ca"=>"Error: no s’ha rebut l’arxiu",
                                            "fr"=>"Erreur: le fichier n'a pas été reçu",
                                            "gl"=>"Erro: non se reciviu o arquivo",
                                            "pt"=>"Erro: o arquivo não foi recebido",
                                            "en"=>"Error: File not received",
                                            "de"=>"Fehler: Datei nicht empfangen"
                                          ),
                                "5"=> array("es"=>"Error: el archivo NO es una imagen",
                                            "ca"=>"Error: l’arxiu NO és una imatge",
                                            "fr"=>"Erreur: le fichier n'est PAS une image",
                                            "gl"=>"Erro: o arquivo non &eacute; unha imaxe",
                                            "pt"=>"Erro: o arquivo NÃO é uma imagem",
                                            "en"=>"Error: the file is not an image",
                                            "de"=>"Fehler: Die Datei ist KEIN Bild."
                                          ),
                                "6"=> array("es"=>"Error: debe subir un archivo",
                                            "ca"=>"Error: has de pujar un arxiu",
                                            "fr"=>"Erreur: vous devez télécharger un fichier",
                                            "gl"=>"Erro: debe subir un arquivo",
                                            "pt"=>"Erro: você deve carregar um arquivo",
                                            "en"=>"Error: You must upload a file",
                                            "de"=>"Fehler: Datei muss hochgeladen werden"
                                          )
                               );

  public static $elmt_contacto_erro = array("1"=> array("es"=>"Error: debes completar los campos con (*)",
                                                        "ca"=>"Error: has de completar els camps amb (*)",
                                                        "fr"=>"Erreur: Vous devez remplir les champs avec (*)",
                                                        "gl"=>"Erro: debes completar os campos con (*)",
                                                        "pt"=>"Erro: deves completar os campos com (*)",
                                                        "en"=>"Error: fields with (*) must be completed",
                                                        "de"=>"Fehler: Sie müssen die Felder mit (*) ausfüllen"
                                                        ),
                                            "2"=> array("es"=>"Error: debes teclear un <b>email</b> válido",
                                                        "ca"=>"Error: has de teclejar un <b>email</b> vàlid",
                                                        "fr"=>"Erreur: vous devez entrer une <b>adresse e-mail</b> valide",
                                                        "gl"=>"Erro: Ten que escribir un <b>email</b> válido",
                                                        "pt"=>"Erro: tem que escrever um <b>email</b> válido",
                                                        "en"=>"Error: You must enter a valid <b>email</b>",
                                                        "de"=>"Fehler: Sie müssen eine gültige <b>E-Mail-Adresse</b> eingeben."
                                                      ),
                                            "3"=> array("es"=>"Error: debes seleccionar un <b>asunto</b>",
                                                        "ca"=>"Error: has de seleccionar un <b>assumpte</b>",
                                                        "fr"=>"Erreur: vous devez sélectionner un <b>sujet</b>",
                                                        "gl"=>"Erro: ten que seleccionar un <b>asunto</b>",
                                                        "pt"=>"Erro: tem que seleccionar um <b>assunto</b>",
                                                        "en"=>"Error: You must select <b>subject</b>",
                                                        "de"=>"Fehler: Sie müssen einen Betreff auswählen."
                                                      ),
                                            "4"=> array("es"=>"Error: debes teclear: ",
                                                        "ca"=>"Error: has de teclejar: ",
                                                        "fr"=>"Erreur: vous devez entrer: ",
                                                        "gl"=>"Erro: ten que teclear: ",
                                                        "pt"=>"Erro: tem que digitar: ",
                                                        "en"=>"Error: You must type: ",
                                                        "de"=>"Fehler: Sie müssen etwas eingeben:"
                                                      ),
                                            "5"=> array("es"=>"Error: debes teclear un <b>NIF/CIF/NIE</b> válido",
                                                        "ca"=>"Error: has de teclejar un <b>NIF/CIF/NIE</b> vàlid",
                                                        "fr"=>"Erreur: vous devez entrer un NIF / CIF / NIE valide",
                                                        "gl"=>"Erro: Ten que escribir un <b>NIF/CIF/NIE</b> válido",
                                                        "pt"=>"Erro: Tem que escrever un <b>NIF</b> válido",
                                                        "en"=>"Error: You must enter a valid <b>NIF/CIF/NIE</b>",
                                                        "de"=>"Fehler: Sie müssen eine gültige <b>USt-IdNr/ Ust-Nr</b> eingeben."
                                                      ),
                                            "6"=> array("es"=>"Error: debes aceptar la política de privacidad",
                                                        "ca"=>"Error: has d'acceptar la política de privacitat",
                                                        "fr"=>"Erreur: vous devez accepter la politique de confidentialité",
                                                        "gl"=>"Erro: debes aceptar a política de privacidade",
                                                        "pt"=>"Erro: deves aceitar a política de privacidade",
                                                        "en"=>"Error: You must accept the privacy policy",
                                                        "de"=>"Fehler: Sie müssen die Datenschutzrichtlinie akzeptieren."
                                                      ),
                                            "7"=> array("es"=>"Error: <b>IBAN</b> no válido",
                                                        "ca"=>"Error: <b>IBAN</b> no válido",
                                                        "fr"=>"Erreur: <b>IBAN</b> is not valid",
                                                        "gl"=>"Erro: <b>IBAN</b> non válido",
                                                        "pt"=>"Erro: <b>IBAN</b> is not valid",
                                                        "en"=>"Error: <b>IBAN</b> is not valid",
                                                        "de"=>"Fehler: <b>IBAN</b> is not valid"
                                                      )
                                           );



  public static function __valida_cc($cc) {
    return Valida::cc($cc);
  }

  public static function __valida_iban($iban) {
    return Valida::iban($iban);
  }

  public static function __comas($txt) {
    return Valida::comas($txt);
  }

  public static function __parse_permisos($a_grupo, $a_permisos) {
    if (count($a_permisos) == 0) return false; //* O usuario $u NON ten permisos => ERRO, non pode ser

    if (array_search("admin", $a_permisos) !== FALSE) return true;


    if (count($a_grupo) == 0) return false; //* A paxina $p NON ten grupo => ERRO, non pode ser

    foreach($a_grupo as $permiso_g) {
      if ($permiso_g == "pub") continue;

      if (array_search($permiso_g, $a_permisos) === FALSE) return false;
    }

    //* O usuario ten permisos para visitar a paxina $p
    return true;
  }

  public static function __html($s, $css = "texto3_erro") {
    return "<div class='{$css}'>{$s}</div>";
  }

  public static function valida_numero($n, $d = 0) {
    return Valida::numero($n, $d);
  }

  public static function valida_upload($a_file, $so_imx = true, $id_idioma = "es") {
    $r = Valida::upload($a_file, $so_imx);

    switch ($r) {
      case 1: return self::$upload[1][$id_idioma];
      case 2: return self::$upload[2][$id_idioma];
      case 3: return self::$upload[3][$id_idioma];
      case 4: return self::$upload[4][$id_idioma];
      case 5: return self::$upload[5][$id_idioma];
    }

    return null;
  }

  public static function valida_nif_cif_nie($d) {
    return Valida::nif_cif_nie($d);
  }

  public static function validar_nome_paxina($n) {
    $a_r = self::$a_nome_pax_reservado;

    //* devolve $n válido.

    $n = trim($n);

    if ($n == "") return -1;

    if (isset( $a_r[ strtolower($n) ] )) return -2;

    return $n;
  }

  public static function __paxina($i_erro, $action = null, $titulo = "Error") { //* páxina de erro
    $p = new Paxina($titulo);

    $p->head->pon_meta("robots", "noindex, nofllow");


    $p->formulario()->ptw =  Refs::url("ptw/form/erros/ferro.html");

    if ($action != null) $p->formulario()->action = $action;


    return $p;
  }
}
