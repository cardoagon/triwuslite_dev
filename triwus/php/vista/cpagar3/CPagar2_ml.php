<?php

final class CPagar2_ml {

  public static $carro = array("1"=> array("es"=>"Precio",
                                           "gl"=>"Prezo"
                                          ),
                               "2"=> array("es"=>"IVA",
                                           "gl"=>"IVE"
                                          ),
                               "3"=> array("es"=>"Costes adicionales",
                                           "gl"=>"Costes adicionais"
                                          ),
                               "4"=> array("es"=>"Tu compra",
                                           "gl"=>"A túa compra"
                                          ),
                               "5"=> array("es"=>"",
                                           "gl"=>""
                                          ),
                               "6"=> array("es"=>"",
                                           "gl"=>""
                                          ),
                               "7"=> array("es"=>"Teclea o pega tu código promocional",
                                           "gl"=>"Teclea ou pega o teu código promocional"
                                          ),
                               "8"=> array("es"=>"El código introducido es erróneo o está caducado.",
                                           "gl"=>"O código introducido é erróneo ou está caducado."
                                          ),
                               "9"=> array("es"=>"Esta promoción sólo será aplicable para compras superiores a",
                                           "gl"=>"Esta promoción só será aplicable para compras superiores a"
                                          ),
                               "6000"=> array("es"=>"Error: debe teclear un <b>NIF/CIF/NIE</b> válido",
                                              "gl"=>"Erro: Ten que escribir un <b>NIF/CIF/NIE</b> válido"
                                          )
                              );

  public static $cpago2 = array("2"=> array("es"=>"Leo y acepto las",
                                            "gl"=>"Leo e acepto as"
                                           ),
                                "3"=> array("es"=>"Las unidades solicitadas superan el stock del producto ",
                                            "gl"=>"As unidades solicitadas superan o stock do produto "
                                           ),
                                "4"=> array("es"=>"Tarjeta de cr&eacute;dito/d&eacute;bito",
                                            "gl"=>"Tarxeta de cr&eacute;dito/d&eacute;bito"
                                           ),
                                "6"=> array("es"=>"Pincha aquí si quieres <b>crear una cuenta de usuario</b>. Te enviaremos un correo electrónico para que te registres fácilmente",
                                            "gl"=>"Pica aquí se quieres <b>crear unha cuenta de usuario</b>. Enviarémosche un correo electrónico para que te rexistres facilmente"
                                           ),
                                "6000"=> array("es"=>"Error: debe teclear un <b>NIF/CIF/NIE</b> válido",
                                            "   gl"=>"Erro: Ten que escribir un <b>NIF/CIF/NIE</b> válido"
                                           )
                               );

  public static $fpago = array("2"=> array("es"=>"Efectivo ( Recoger en Local )",
                                           "gl"=>"Efectivo ( Recoller no Local )"
                                          ),
                               "4"=> array("es"=>"Tarjeta de cr&eacute;dito/d&eacute;bito",
                                           "gl"=>"Tarxeta de cr&eacute;dito/d&eacute;bito"
                                          ),
                               "6000"=> array("es"=>"Error: debe teclear un <b>NIF/CIF/NIE</b> válido",
                                           "gl"=>"Erro: Ten que escribir un <b>NIF/CIF/NIE</b> válido"
                                          )
                              );

  public static $envio = array("1"=> array("es"=>"Sí, deseo recibir mi compra el próximo sábado día [dsabado]",
                                           "gl"=>"Si, desexo recibir a miña compra o vindeiro sábado día [dsabado]"
                                          ),
                               "2"=> array("es"=>"El precio de los portes para un destino fuera de la península puede variar, revisa el carro de la compra.",
                                           "gl"=>"O prezo dos portes para un destino fóra da península pode variar, revisa o carro da compra."
                                         ),
                               "3"=> array("es"=>"El precio de los portes para un destino insular puede variar, revisa el carro de la compra.",
                                           "gl"=>"O prezo dos portes para un destino insular pode variar, revisa o carro da compra."
                                         ),
                               "4"=> array("es"=>", <b>revisa el carro de la compra</b>",
                                           "gl"=>", <b>revisa o carro da compra</b>"
                                         ),
                               "5"=> array("es"=>"El servicio de entrega en sábado sólo está disponible para destinos peninsulares.",
                                           "gl"=>"O servizo de entrega en sábado só está dispoñible para destinos peninsulares."
                                         ),
                               "6"=> array("es"=>"Recoger en tienda ( [denderezo] ).",
                                           "gl"=>"Recoller na tenda ( [denderezo] )."
                                         )
                              );


  public static function chsabado(Checkbox $ch, $id_idioma, $dsabado) {
    $ch->visible = false;
    
    if ($dsabado == null) return;

    $ch->visible = true;
        
    $ch->pon_etq( str_replace("[dsabado]", $dsabado, self::$envio[1][$id_idioma]) );
  }


  public static function chrecollida(Checkbox $ch, $id_idioma, $denderezo) {
    $ch->visible = false;
    
    if ($denderezo == null) return;

    $ch->visible = true;
        
    $ch->pon_etq( str_replace("[denderezo]", $denderezo, self::$envio[6][$id_idioma]) );
  }
}
