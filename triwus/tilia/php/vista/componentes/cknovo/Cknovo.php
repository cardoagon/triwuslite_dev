<?php

final class Cknovo extends Componente {
  const ptw_h = "php/vista/componentes/cknovo/cknovo_h.html";
  const ptw_v = "php/vista/componentes/cknovo/cknovo_v.html";

  private static $_err = [
    ["es" => "Contraseña 1 y contraseña 2 deben ser iguales.",
     "fr" => "Le mot de passe 1 et le mot de passe 2 doivent être identiques.",
     "gl" => "Contrasinal 1 e contrasinal 2 deben ser iguais.",
     "pt" => "A senha 1 e a senha 2 devem ser iguais.",
     "en" => "Password 1 and password 2 must be the same."
    ],
    ["es" => "Tu contraseña es débil. Utiliza 8 o más caracteres combinando letras, números y símbolos.",
     "fr" => "Votre mot de passe est faible. Utilisez 8 caractères ou plus en combinant des lettres, des chiffres et des symboles.",
     "gl" => "O teu contrasinal é débil. Usa 8 ou máis caracteres combinando letras, números e símbolos.",
     "pt" => "Sua senha é fraca. Use 8 ou mais caracteres combinando letras, números e símbolos.",
     "en" => "Your password is weak. Use 8 or more characters by combining letters, numbers, and symbols."
    ]
  ];
  
/*
 * 
 * name: Cknovo::__construct
 * @param $tipo IN ("h", "v")
 * @return
 * 
 */
  public function __construct($id = "cknovo", $tipo = "v", $id_idioma = "es") {
    parent::__construct("cknovo", self::__ptw($tipo));

    $this->cambiar_idioma( Idioma::factory($id_idioma) );

    $this->pon_obxeto(new Hidden("hvalor", "0"));

    $this->pon_obxeto(self::password(1));
    $this->pon_obxeto(self::password(2));
  }

  public function declara_js() {
    return array(Tilia::home() . "/js/cknovo.js");
  }

  public function __limpar() {
    $this->obxeto("k1")->post("");
    $this->obxeto("k2")->post("");
    $this->obxeto("hvalor")->post("0");
  }

  public function __k() {
    return $this->obxeto("k1")->valor();
  }

  public function validar() {
    $l = $this->idioma->codigo;
    
    if ($this->obxeto("hvalor")->valor() != 1) return self::$_err[1][$l];

    $k1 = $this->obxeto("k1")->valor();
    $k2 = $this->obxeto("k2")->valor();

    if ($k1 != $k2) return self::$_err[0][$l];

    return null;
  }

  private static function password($i) {
   // $p = new Password("k{$i}");
    $campos = [
      1 => new Password("k{$i}", false, true),
      2 => new Password("k{$i}")
    ];

    $p = $campos[$i] ?? new Password("k{$i}");


    $p->autocomplete = "new-password";

    if ($i != 1) return $p;


    $p->pon_eventos("onpaste", "analiza_contrasinal_onpaste(this)");
    $p->pon_eventos("onkeyup", "cknovo_pinta(event, this)"        );


    return $p;
  }

  private static function __ptw($tipo) {
    switch($tipo) {
      case "v": return getenv("Tilia_HOME") . "/" . self::ptw_v;
      case "h": return getenv("Tilia_HOME") . "/" . self::ptw_h;
    }

    die("Cknovo.__ptw(), tipo deco&ntilde;ecido.");
  }
}
