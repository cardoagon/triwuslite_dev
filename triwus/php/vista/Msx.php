<?php

final class Msx {
  const articulo_prgf_ab_titulo = "&Iacute;ndice de productos";
  const articulo_prgf_titulo    = "Párrafo título";
  const articulo_titulo_novo    = "Nuevo producto";

  const cml_msx_leditar         = "Editar los idiomas de tu espacio web";

  const cusuario_cta_calculando = "&nbsp;&nbsp;&nbsp;&middot;&middot;&middot;&nbsp;&nbsp;&nbsp;Calculando&nbsp;&nbsp;&nbsp;&middot;&middot;&middot;";

  const titulo_badd             = "Pinchar y arrastrar para añadir un nuevo párrafo a la página";
  const titulo_badd_ab          = "Pinchar y arrastrar para añadir un nuevo producto  al blog";
  const titulo_btexto           = "Pinchar y arrastrar para añadir un texto a la página";
  const titulo_bindice          = "Pinchar y arrastrar para añadir un índice a la página";
  const titulo_bhr              = "Pinchar y arrastrar para añadir un separador a la página";
  const titulo_bimaxe           = "Pinchar y arrastrar para añadir una imagen a la página";
  const titulo_bgaleria         = "Pinchar y arrastrar para añadir una galería de imágenes a la página";
  const titulo_blink            = "Pinchar y arrastrar para añadir un vínculo a la página";
  const titulo_bcontacto        = "Pinchar y arrastrar para añadir un formulario de contacto a la página";
  const titulo_bvcard           = "Pinchar y arrastrar para añadir un VCard a la página";
  const titulo_bpaxinador       = "Pinchar y arrastrar para añadir unas migas de pan a la página";

  const email_ok                = "La invitación ha sido envíada a las siguientes direcciones:-)";
  const email_placeholder       = " ... p.ej. pedro@acme.go";

  const itsuperoms_0            = "Contenido";
  const itsuperoms_1            = "Subopciones";

  const passwd_placeholder_0    = "... tu contraseña";
  const passwd_placeholder_1    = "... la nueva contraseña";
  const passwd_placeholder_2    = "... repite la contraseña";

  const baddpax_etq             = "&nbsp;Añadir otra página";
  const baddpax_titulo          = "Crear página";

  const coment_aceptado         = "Su comentario ha sido aceptado, gracias por su participación.";
  const coment_moderacion       = "Su comentario ha sido enviado y está <u>pendiente de moderación</u>, gracias por su participación.";


  const elmt_texto_baleiro      = "";

  const elmt_enlace_txtpaste    = "Pega aquí un nuevo &laquo;vínculo&raquo;";
  const elmt_enlace_baleiro     = "Vínculo vacía";

  const elmt_imaxe_pefoto       = " ... puedes teclear un texto";
  const elmt_imaxe_baleiro      = "Imagen vacía";

  const novosite_baleiro        = "&nbsp;&#150;.&nbsp;<i>vacío</i>&nbsp;.&#150;";
  const novosite_cancelar       = "¿ Desea cancelar su registro ?";

  const title_axuda             = "Ver el manual de ayuda";
  const title_editar_web        = "Pincha aquí para editar los contenidos de tu espacio web";
  const title_bpanelcontrol     = "Volver al Panel de Control";

  const ventas_0_uds            = "producto agotado, este producto no se visualizará en la web, para solucionar este inconveniente debe añadir más unidades al producto en el gestor de productos del panel de control";


  public static $cxcli = array("gl" =>  array("feed"    => array("t" =>"Feedback",
                                                                 "u" => "Usuario",
                                                                 "n" =>"¿ Tes algunha dúbida, suxestión, queixa ... ?, ¿ Botas algo en falta ? . Non dubides en consultarnos, estaremos encantados de poder axudarche.",
                                                                 "r" =>"O teu 'feedback' foi enviado correctamente",
                                                                 "err" => "Erro. O campo feedback non pode estar baleiro",
                                                                 "btn" => "Enviar",
                                                                 "btn_title" => "Envía feedback"
                                                                ),
                                              "lped"    => array("t" =>"Os teus pedidos",
                                                                 "n" =>"Revisa o estado dos teus pedidos e descarga as facturas asociadas. Aquí tamén poderás retomar o pago dos teus pedidos."
                                                                ),
                                              "panel_1" => array("t" =>"Os teus datos de acceso",
                                                                 "n" =>"Modifica aquí os teus datos de rexistro/acceso email e contrasinal."
                                                                ),
                                              "panel_2" => array("t" =>"A túa información de contacto",
                                                                 "n" =>"Mantén actualizada a túa información de contacto."
                                                                ),
                                              "panel_3" => array("t" => "Servizos contratados.",
                                                                 "n" => "Información dos teus servizos contratados."
                                                                ),
                                              "pechar"  => array("t" =>"Pechar a sesión",
                                                                 "n" =>"Pica no botón para pechar a túa sesión."
                                                                )
                                             ),
                               "es" =>  array("feed"    => array("t" =>"Feedback",
                                                                 "u" =>"Usuario",
                                                                 "n" =>"¿ Tienes alguna duda, sugerencia, queja ... ?, ¿ Echas algo en falta ? . No dudes en consultarnos, estaremos encantados de poder ayudarte.",
                                                                 "r" =>"Tu 'feedback' ha sido enviado correctamente",
                                                                 "err" => "Error. El campo feedback no puede estar vacío",
                                                                 "btn" => "Enviar",
                                                                 "btn_title" => "Envía feedback"
                                                                ),
                                              "lped"    => array("t" =>"Tus Pedidos",
                                                                 "n" =>"Revisa el estado de tus pedidos y descarga las facturas asociadas. Aquí también podrás retomar el pago de tus pedidos."
                                                                ),
                                              "panel_1" => array("t" =>"Tus datos de acceso",
                                                                 "n" =>"Modifica aquí tus datos de registro/acceso correo electrónico y contraseña."
                                                                ),
                                              "panel_2" => array("t" =>"Tu información de contacto",
                                                                 "n" =>"Mantén actualizada tu información de contacto."
                                                                ),
                                              "panel_3" => array("t" => "Servicios contratados.",
                                                                 "n" => "Información de tus servicios contratados."
                                                                ),
                                              "pechar"  => array("t" =>"Cerrar la sesión",
                                                                 "n" =>"Pulsa el botón para cerrar tu sesión."
                                                                )
                                             ),
                               "ca" =>  array("feed"    => array("t" =>"Feedback",
                                                                 "u" =>"Usuari",
                                                                 "n" =>"Tens algun dubte, suggeriment, queixa ... ?, Tires alguna cosa en falta ? . No dubtis a consultar-nos, estarem encantats de poder ajudar-te.",
                                                                 "r" =>"El teu 'feedback' ha estat enviat correctament",
                                                                 "err" => "Error. El camp feedback no pot estar buit",
                                                                 "btn" => "Enviar",
                                                                 "btn_title" => "Envia feedback"
                                                                ),
                                              "lped"    => array("t" =>"Les teves Comandes",
                                                                 "n" =>"Revisa l’estat de les teves comandes i descarrega les factures associades. Aquí també podràs reprendre el pagament de les teves comandes."
                                                                ),
                                              "panel_1" => array("t" =>"Les teves dades d’accés",
                                                                 "n" =>"Modifica aquí les teves dades de registre/accés correu electrònic i contrasenya."
                                                                ),
                                              "panel_2" => array("t" =>"La teva informació de contacte",
                                                                 "n" =>"Mantén actualitzada la teva informació de contacte."
                                                                ),
                                              "panel_3" => array("t" => "Serveis contractats.",
                                                                 "n" => "Informació dels teus serveis contractats."
                                                                ),
                                              "pechar"  => array("t" =>"Tancar la sessió",
                                                                 "n" =>"Prem el botó per a tancar la teva sessió."
                                                                )
                                             ),
                               "pt" =>  array("feed"    => array("t" =>"Feedback",
                                                                 "u"=>"Usuário",
                                                                 "n" =>" Estás com alguma dúvida, sugestão, queixa ... ?,  Botas algo em falta ? . Não duvides em consultar-nos, estaremos encantados de poder ajudar-te.",
                                                                 "r" =>"O teu 'feedback' foi enviado con éxito",
                                                                 "err" => "Erro. O campo de feedback não pode estar vazio",
                                                                 "btn" => "Enviar",
                                                                 "btn_title" => "Envía feedback"
                                                                ),
                                              "lped"    => array("t" =>"Os teus pedidos",
                                                                 "n" =>"Revê o estado dos teus pedidos e descarga as facturas associadas. Aqui também poderás retomar o pago dos teus pedidos."
                                                                ),
                                              "panel_1" => array("t" =>"Os teus datos de utilizador",
                                                                 "n" =>"Modifica aqui os teus dados de registro/acesso  email e  contrasinal."
                                                                ),
                                              "panel_2" => array("t" =>"A tua informação de contacto",
                                                                 "n" =>"Mantém actualizada a tua informação de contacto."
                                                                ),
                                              "panel_3" => array("t" => "Serviços contratados.",
                                                                 "n" => "Informações sobre seus serviços contratados."
                                                                ),
                                              "pechar"  => array("t" =>"Log out",
                                                                 "n" =>"Pressione o botão para fechar a tua sessão."
                                                                )
                                             ),
                               "en" =>  array("feed"    => array("t" =>"Feedback",
                                                                 "u" => "User",
                                                                 "n" =>"Do you have any questions, suggestions, complaints ...? Do you miss something? . Do not hesitate to ask us, we will be happy to help you.",
                                                                 "r" =>"Your feedback has been sent correctly",
                                                                 "err" => "Error. Feedback field cannot be empty",
                                                                 "btn" => "Send",
                                                                 "btn_title" => "Send feedback"
                                                                ),
                                              "lped"    => array("t" =>"Your Orders",
                                                                 "n" =>"Check the status of your orders and download the associated invoices. Here you can also resume paymentde tus pedidos."
                                                                ),
                                              "panel_1" => array("t" =>"Your access data",
                                                                 "n" =>"Modify your registration / access email and password information here."
                                                                ),
                                              "panel_2" => array("t" =>"Your contact information",
                                                                 "n" =>"Keep your contact information updated."
                                                                ),
                                              "pechar"  => array("t" =>"Log out",
                                                                 "n" =>"Press the button to close your session."
                                                                )
                                             ),
                               "fr" =>  array("feed"    => array("t" =>"Feedback",
                                                                 "u" =>"User",
                                                                 "n" =>"Do you have any questions, suggestions, complaints ...? Do you miss something? . Do not hesitate to ask us, we will be happy to help you.",
                                                                 "r" =>"Your feedback has been sent correctly",
                                                                 "err" => "Error. Feedback field cannot be empty",
                                                                 "btn" => "Send",
                                                                 "btn_title" => "Send feedback"
                                                                ),
                                              "lped"    => array("t" =>"Your Orders",
                                                                 "n" =>"Check the status of your orders and download the associated invoices. Here you can also resume paymentde tus pedidos."
                                                                ),
                                              "panel_1" => array("t" =>"Your access data",
                                                                 "n" =>"Modify your registration / access email and password information here."
                                                                ),
                                              "panel_2" => array("t" =>"Your contact information",
                                                                 "n" =>"Keep your contact information updated."
                                                                ),
                                              "pechar"  => array("t" =>"Log out",
                                                                 "n" =>"Press the button to close your session."
                                                                )
                                              ),
                              "de" =>  array("feed"    => array("t" =>"Feedback",
                                                                "u" =>"Benutzer",
                                                                "n" =>"Haben Sie Fragen, Anregungen, Beschwerden…? Fehlt Ihnen etwas? Fehlt Ihnen etwas? Zögern Sie nicht, uns zu kontaktieren, wir helfen Ihnen gerne weiter.",
                                                                "r" =>"Ihr Feedback wurde erfolgreich gesendet.",
                                                                "err" => "Fehler: Das Feedbackfeld darf nicht leer sein.",
                                                                "btn" => "Senden",
                                                                "btn_title" => "Feedback senden"
                                                                ),
                                              "lped"    => array("t" =>"Deine Bestellungen",
                                                                 "n" =>"Überprüfen Sie den Status Ihrer Bestellungen und laden Sie die zugehörigen Rechnungen herunter. Hier können Sie auch die Zahlung Ihrer Bestellungen fortsetzen."
                                                                ),
                                              "panel_1" => array("t" =>"Ihre Zugangsdaten",
                                                                 "n" =>"Ändern Sie hier Ihre Registrierungs-/Zugangsdaten, E-Mail und Passwort."
                                                                ),
                                              "panel_2" => array("t" =>"Ihre Kontaktdaten",
                                                                 "n" =>"Halten Sie Ihre Kontaktdaten aktuell."
                                                                ),
                                              "panel_3" => array("t" => "Beauftragte Dienstleistungen.",
                                                                 "n" => "Informationen zu deinen beauftragten Dienstleistungen."
                                                                ),
                                              "pechar"  => array("t" =>"Abmelden",
                                                                 "n" =>"Drücken Sie auf die Taste, um Ihre Sitzung zu schließen."
                                                                )
                                              )

                              );


  public static $rgpd_msx =
[
 "en" => "We use our own and third-party cookies to analyze the use of this website and improve your experience. You can accept all cookies, reject them, or learn more about our cookie policy.",
 "es" => "Utilizamos cookies propias y de terceros para analizar el uso de esta web y mejorar tu experiencia. Puedes aceptar todas las cookies, rechazarlas o saber más sobre nuestra política de cookies.",
 "fr" => "We use our own and third-party cookies to analyze the use of this website and improve your experience. You can accept all cookies, reject them, or learn more about our cookie policy.",
 "gl" => "Utilizamos cookies propias e de terceiros para analizar o uso desta web e mellorar a túa experiencia. Podes aceptar todas as cookies, rexeitalas ou saber máis sobre a nosa política de cookies.",
 "pt" => "Utilizamos cookies próprias e de terceiros para analisar a utilização deste site e melhorar a sua experiência. Pode aceitar todos os cookies, rejeitá-los ou saber mais sobre a nossa política de cookies."
];

  public static $rgpd_btn =
[
 "en" => array(1 => "I agree" , 2 => "Find out more"  , 3 => "Reject"  ),
 "es" => array(1 => "Aceptar" , 2 => "Saber más"      , 3 => "Rechazar"),
 "fr" => array(1 => "Accepter", 2 => "Savoir plus"    , 3 => "Reject"  ),
 "gl" => array(1 => "Aceptar" , 2 => "Saber máis"     , 3 => "Rexeitar"),
 "pt" => array(1 => "Entendí" , 2 => "Saber máis"     , 3 => "Rejeitar")
];


  public static $_aceptar  = array(
    "es" => array( "txt" => "Aceptar",     "title" => "Guardar cambios"),
    "ca" => array( "txt" => "Acceptar",    "title" => "Desar canvis"),
    "fr" => array( "txt" => "Accept",      "title" => "Enregistrer les modifications"),
    "gl" => array( "txt" => "Aceptar",     "title" => "Gardar cambios"),
    "pt" => array( "txt" => "Aceitar",     "title" => "Guardar alterações"),
    "en" => array( "txt" => "Accept",      "title" => "Save changes"),
    "de" => array( "txt" => "Akzeptieren", "title" => "Änderungen speichern")
  );

  public static $_cancelar = array("es"=>"Cancelar","ca"=>"Cancel·lar","fr"=>"Annuler","gl"=>"Cancelar","pt"=>"Cancelar","en"=>"Cancel", "de"=>"Abbrechen");

  public static $_pedidos = array(
    "es" => "No hay pedidos asociados.",
    "ca" => "No hi ha comandes associades.",
    "fr" => "Il n'y a pas de commandes associées.",
    "gl" => "Non hai pedidos asociados.",
    "pt" => "Não há pedidos associados.",
    "en" => "No orders associated.",
    "de" => "Es sind keine zugehörigen Bestellungen vorhanden."
  );

  public static $novas2_rel = array("etq" => array("es" => "Contenidos relacionados",
                                                   "ca" => "Continguts relacionats",
                                                   "fr" => "Contenu associé",
                                                   "gl" => "Contidos relacionados",
                                                   "pt" => "Conteúdo relacionado",
                                                   "en" => "Related content",
                                                   "de" => "Verwandte Inhalte"
                                                  )
                                   );


  public static $elmt_contacto_enviar = array("es"=>"Enviar","ca"=>"Enviar","fr"=>"Envoyer","gl"=>"Enviar","pt"=>"Enviar","en"=>"Send", "de"=>"Senden");

  public static $elmt_contacto_chenvia = array("es"=>"He leído y acepto la <a href='[url_legal]' target='_blank'>política de privacidad</a>",
                                               "ca"=>"He llegit i accepto la <a href='[url_legal]' target='_blank'>política de privacitat</a>",
                                               "fr"=>"J\'ai lu et accepté la <a href='[url_legal]' target='_blank'>politique de confidentialité</a>",
                                               "gl"=>"Leo e acepto a <a href='[url_legal]' target='_blank'>política de privacidade</a>",
                                               "pt"=>"Leio e aceito a <a href='[url_legal]' target='_blank'>política de privacidade</a>",
                                               "de"=>"Ja, ich habe die <a href='[url_legal]' target='_blank'>Datenschutzrichtlinie gelesen und akzeptiert.</a>",
                                               "en"=>"I have read and accept the <a href='[url_legal]' target='_blank'>privacy policy</a>");

  public static $elmt_contacto_campos = array("c_nome"    => array("es"=>"Nombre",
                                                                   "ca"=>"Nom",
                                                                   "fr"=>"Nom",
                                                                   "gl"=>"Nome",
                                                                   "pt"=>"Nome",
                                                                   "en"=>"Name",
                                                                   "de" => "Vorname"
                                                                   ),
                                              "c_apel"    => array("es"=>"Apellidos",
                                                                   "ca"=>"Cognoms",
                                                                   "fr"=>"Noms de famille",
                                                                   "gl"=>"Apelidos",
                                                                   "pt"=>"Apelidos",
                                                                   "en"=>"Surname",
                                                                   "de"=>"Nachname"
                                                                   ),
                                              "c_dni"     => array("es"=>"NIF/CIF/NIE",
                                                                   "ca"=>"NIF/CIF/NIE",
                                                                   "fr"=>"NIF/CIF/NIE",
                                                                   "gl"=>"NIF/CIF/NIE",
                                                                   "pt"=>"NIF",
                                                                   "en"=>"NIF/CIF/NIE",
                                                                   "de"=>"NIF/CIF/NIE"
                                                                   ),
                                              "c_empresa" => array("es"=>"Razón social",
                                                                   "ca"=>"Raó social",
                                                                   "fr"=>"Société",
                                                                   "gl"=>"Razón social",
                                                                   "pt"=>"Razão social",
                                                                   "en"=>"Company",
                                                                   "de"=>"Firmenname"
                                                                   ),
                                              "c_email"   => array("es"=>"Email",
                                                                   "ca"=>"Email",
                                                                   "fr"=>"Email",
                                                                   "gl"=>"Email",
                                                                   "pt"=>"E-mail",
                                                                   "en"=>"Email",
                                                                   "de"=>"Email"
                                                                   ),
                                              "c_skype"   => array("es"=>"Id.Skype",
                                                                   "ca"=>"Id.Skype",
                                                                   "fr"=>"Id.Skype",
                                                                   "gl"=>"Id.Skype",
                                                                   "pt"=>"Id.Skype",
                                                                   "en"=>"Id.Skype",
                                                                   "de"=>"Id.Skype"
                                                                   ),
                                              "c_mobil"   => array("es"=>"Móvil",
                                                                   "ca"=>"Mòbil",
                                                                   "fr"=>"Portable",
                                                                   "gl"=>"Móbil",
                                                                   "pt"=>"Celular",
                                                                   "en"=>"Mobile",
                                                                   "de"=>"Handy Nr"
                                                                   ),
                                              "c_tlfn"    => array("es"=>"Teléfono",
                                                                   "ca"=>"Telèfon",
                                                                   "fr"=>"Téléphone",
                                                                   "gl"=>"Teléfono",
                                                                   "pt"=>"Telefone",
                                                                   "en"=>"Landline",
                                                                   "de"=>"Telefon Nr."
                                                                   ),
                                              "c_fax"     => array("es"=>"Fax",
                                                                   "ca"=>"Fax",
                                                                   "fr"=>"Fax",
                                                                   "gl"=>"Fax",
                                                                   "pt"=>"Fax",
                                                                   "en"=>"Fax",
                                                                   "de"=>"Fax"
                                                                   ),
                                              "c_dirpostal" => array("es"=>"Dirección postal",
                                                                     "ca"=>"Adreça postal",
                                                                     "fr"=>"Adresse",
                                                                     "gl"=>"Enderezo",
                                                                     "pt"=>"Endereço postal",
                                                                     "en"=>"Address",
                                                                     "de"=>"Anschrift"
                                                                   ),
                                              "c_asunto"  => array("es"=>"Asunto",
                                                                   "ca"=>"Assumpte",
                                                                   "fr"=>"Sujet",
                                                                   "gl"=>"Asunto",
                                                                   "pt"=>"Assunto",
                                                                   "en"=>"Subject",
                                                                   "de"=>"Thema"
                                                                   ),
                                              "c_msx"     => array("es"=>"Mensaje",
                                                                   "ca"=>"Missatge",
                                                                   "fr"=>"Poste",
                                                                   "gl"=>"Mensaxe",
                                                                   "pt"=>"Mensagem",
                                                                   "en"=>"Message",
                                                                   "de"=>"Textnachricht"
                                                                   ),
                                              "c_adxunto" => array("es"=>"Adjuntar",
                                                                   "ca"=>"Adjuntar",
                                                                   "fr"=>"Annexer",
                                                                   "gl"=>"Anexar",
                                                                   "pt"=>"Anexo",
                                                                   "en"=>"Attach",
                                                                   "de"=>"Anfügen"
                                                                   )
                                             );

  public static $elmt_contacto = array("1"=> array("es"=>"El correo electrónico fue enviado correctamente",
                                                   "ca"=>"El correu electrònic va ser enviat correctament",
                                                   "fr"=>"Le courrier électronique a été envoyé avec succès",
                                                   "gl"=>"O correo electrónico foi enviado con éxito",
                                                   "pt"=>"O email foi enviado com sucesso",
                                                   "en"=>"Email was sent successfully",
                                                   "de"=>"Die E-Mail wurde erfolgreich gesendet."),
                                       "2"=> array("es"=>"puedes usar este #Id. para el seguimiento de esta consulta.",
                                                   "ca"=>"pots usar aquest #Id. per al seguiment d'aquesta consulta.",
                                                   "fr"=>"vous pouvez utiliser ce #Id. pour suivre cette requête.",
                                                   "gl"=>"podes empregar este #Id. para o seguimento de esta consulta.",
                                                   "pt"=>"podes empregar este #Id. para o seguimento desta consulta.",
                                                   "en"=>"You can use this #Id. for tracking this query",
                                                   "de"=>"Sie können diese #Id. zur Verfolgung dieser Anfrage verwenden.")
                                      );


  public static $clogin = array("es"=> array("1"=>"Recordarme durante 30 días",
                                             "2"=>"Olvidaste tu contraseña",
                                             "3"=>"Acceder",
                                             "4"=>"Acceder",
                                             "5"=>"Solicitar registro",
                                             "6"=>"Cambiar",
                                             "7"=>"Inicia el proceso de cambio de su contraseña.",
                                             "8"=>"Invitación a ",
                                             "9"=>"Email enviado correctamente",
                                             "10"=>"Aceptar",
                                             "11"=>"Volver a enviar",
                                             "12"=>"Registro OK :)",
                                             "100"=>"Debes teclear una dirección de correo electrónico válida.",
                                             "101"=>"Error de credenciales.",
                                             "102"=>"Error, usuario desconocido.",
                                             "103"=>"Debes teclear una contraseña.",
                                             "104"=>"Debes aceptar las condiciones de uso.",
                                             "105"=>"Dato obligatorio.",
                                             "111"=>"Debes teclear un número de teléfono",
                                             "115"=>"Registro confirmado en ",
                                             "116"=>"Pega aquí el código de activación",
                                             "117"=>"El código de verificación ha sido enviado a ",
                                             "700"=>"Tu solicitud de registro ha sido enviada y será contestada tan pronto como sea posible."
                                            ),
                                "ca"=> array("1"=>"Recordar-me durant 30 dies",
                                             "2"=>"Vas oblidar la teva contrasenya",
                                             "3"=>"Accedir",
                                             "4"=>"Accedir",
                                             "5"=>"Sol·licitar registre",
                                             "6"=>"Canviar",
                                             "7"=>"Inicia el procés de canvi de la seva contrasenya.",
                                             "8"=>"Invitació a ",
                                             "9"=>"Email enviat correctament",
                                             "10"=>"Acceptar",
                                             "11"=>"Tornar a enviar",
                                             "12"=>"Registre OK :)",
                                             "100"=>"Has de teclejar una adreça de correu electrònic vàlida.",
                                             "101"=>"Error de credencials.",
                                             "102"=>"Error, usuari desconegut.",
                                             "103"=>"Has de teclejar una contrasenya.",
                                             "104"=>"Has d'acceptar les condicions d'ús.",
                                             "105"=>"Dada obligatòria.",
                                             "111"=>"Has de teclejar un número de telèfon",
                                             "115"=>"Validació de la invitació a ",
                                             "116"=>"Enganxa el codi d'activació aquí",
                                             "117"=>"El codi de verificació ha estat enviat a ",
                                             "700"=>"La teva sol·licitud de registre ha estat enviada i serà contestada tan aviat com sigui possible."
                                            ),
                                "en"=> array("1"=>"Keep me logged in",
                                             "2"=>"Forgot your password?",
                                             "3"=>"Log in",
                                             "4"=>"Log in",
                                             "5"=>"Request registration",
                                             "6"=>"Change password",
                                             "7"=>"Start the process to change your password.",
                                             "8"=>"Invitation to ",
                                             "9"=>"Email was sent successfully",
                                             "10"=>"Accept",
                                             "11"=>"Resend",
                                             "12"=>"Registration OK :)",
                                             "100"=>"You must enter a valid email address.",
                                             "101"=>"Credential error.",
                                             "102"=>"Unknown user.",
                                             "103"=>"You must enter a password.",
                                             "104"=>"You must accept the conditions of use.",
                                             "105"=>"Required field.",
                                             "111"=>"You must type a telephone number",
                                             "115"=>"VALIDATION OF INVITATION.",
                                             "116"=>"Paste your activation code here",
                                             "117"=>"The verification code has been sent to ",
                                             "700"=>"Your registration request has been registered and will be answered as soon as possible."
                                            ),
                                "fr"=> array("1"=>"Garder ma session active",
                                             "2"=>"Mot de passe oublié",
                                             "3"=>"Se connecter",
                                             "4"=>"Se connecter",
                                             "5"=>"Request registration",
                                             "6"=>"Change password",
                                             "7"=>"Start the process to change your password.",
                                             "8"=>"Invitation to ",
                                             "9"=>"Email was sent successfully",
                                             "10"=>"Accept",
                                             "11"=>"Resend",
                                             "12"=>"Enregistrement OK :)",
                                             "100"=>"You must enter a valid email address.",
                                             "101"=>"Erreur d'identification",
                                             "102"=>"Unknown user.",
                                             "103"=>"You must enter a password.",
                                             "104"=>"You must accept the conditions of use.",
                                             "105"=>"Required field.",
                                             "111"=>"Debes teclear un número de teléfono",
                                             "115"=>"VALIDATION OF INVITATION.",
                                             "116"=>"Paste your activation code here",
                                             "117"=>"The verification code has been sent to ",
                                             "700"=>"Your registration request has been registered and will be answered as soon as possible."
                                            ),
                                "pt"=> array("1"=>"Manter-me autenticado(a) 30 dias",
                                             "2"=>"Esqueceste a tua palavra-passe",
                                             "3"=>"Entrar",
                                             "4"=>"Entrar",
                                             "5"=>"Solicitar registo",
                                             "6"=>"Mudar",
                                             "7"=>"Inicia o processo de mudança da palavra-passe.",
                                             "8"=>"Convite a ",
                                             "9"=>"O e-mail foi enviado com sucesso",
                                             "10"=>"Aceitar",
                                             "11"=>"Reenviar",
                                             "12"=>"Registro OK :)",
                                             "100"=>"Escreve um endereço de correio eletrónico válido.",
                                             "101"=>"Erro de credenciais.",
                                             "102"=>"Utilizador desconhecido.",
                                             "103"=>"Tens que escrever uma palavra-passe.",
                                             "104"=>"Tens que aceitar as condições de uso.",
                                             "105"=>"Dado obrigatório.",
                                             "111"=>"Debes teclear un número de teléfono",
                                             "115"=>"Validação do convite para ",
                                             "116"=>"Insira o código de ativação aqui",
                                             "117"=>"O código de verificação foi enviado para ",
                                             "700"=>"O seu pedido de registo foi registado e será respondido o mais rapidamente possível."
                                            ),
                                "gl"=> array("1"=>"Lémbrame 30 días",
                                             "2"=>"Esqueceste o teu contrasinal?",
                                             "3"=>"Acceder",
                                             "4"=>"Inicio de sesión",
                                             "5"=>"Solicitar rexistro",
                                             "6"=>"Cambiar",
                                             "7"=>"Inicia o proceso de cambio de contrasinal.",
                                             "8"=>"Invitación a ",
                                             "9"=>"Email foi enviado correctamente.",
                                             "10"=>"Aceptar",
                                             "11"=>"Volver a enviar",
                                             "12"=>"Rexistro OK :)",
                                             "100"=>"Debes teclear una dirección de correo electrónico válida.",
                                             "101"=>"Erro de credenciais",
                                             "102"=>"Usuario descoñecido.",
                                             "103"=>"Tes que teclear un contrasinal.",
                                             "104"=>"Tes que aceptar as condicións de uso.",
                                             "105"=>"Dato obligratorio",
                                             "111"=>"Debes teclear un número de teléfono",
                                             "115"=>"Validación da invitación a ",
                                             "116"=>"Pega aquí o código de activación",
                                             "117"=>"O Código de verificación foi enviado a ",
                                             "700"=>"A túa solicitude de rexistro foi enviada e será contestada tan pronto como sexa posible."
                                          ),
                                  "de"=> array("1"=>"Erinnere dich 30 Tage lang an mich",
                                               "2"=>"Haben Sie Ihr Passwort vergessen?",
                                               "3"=>"Zugang",
                                               "4"=>"Inicio de sesión",
                                               "5"=>"Registrierung beantragen",
                                               "6"=>"Wechseln",
                                               "7"=>"Beginnen Sie den Prozess zum Ändern Ihres Passworts.",
                                               "8"=>"Einladung zu ",
                                               "9"=>"E-Mail wurde erfolgreich gesendet.",
                                               "10"=>"Akzeptieren",
                                               "11"=>"Erneut senden",
                                               "12"=>"Register OK :)",
                                               "100"=>"Sie müssen eine gültige E-Mail-Adresse eingeben.",
                                               "101"=>"Fehler bei den Anmeldeinformationen.",
                                               "102"=>"Fehler, unbekannter Benutzer.",
                                               "103"=>"Sie müssen ein Passwort eingeben.",
                                               "104"=>"Sie müssen die Nutzungsbedingungen akzeptieren.",
                                               "105"=>"Benötigte Daten",
                                               "111"=>"Sie müssen eine Telefonnummer eingeben.",
                                               "115"=>"Bestätigung der Einladung zu ",
                                               "116"=>"Fügen Sie hier den Aktivierungscode ein.",
                                               "117"=>"Der Bestätigungscode wurde gesendet an ",
                                               "700"=>"Ihre Registrierungsanfrage wurde gesendet und wird so schnell wie möglich beantwortet."
                                           )
                                      );

  public static function __html($txt, $css = "fgs_msx") {
    return "<p class='{$css}'>{$txt}</p>";
  }
}

