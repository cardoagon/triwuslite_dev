<?php

final class CInstalar extends Componente {
  //~ const src_instalador = "instalador";
  
  const ptw_00 = "ptw/cinstalar/00.html";
  const ptw_01 = "ptw/cinstalar/01.html";

  public function __construct(bool $instalar) {
    parent::__construct("cinstalar", self::ptw_00);

    if ( !$instalar ) {
      $this->visible = false;
      
      return;
    }
    
    
    //* básico.
    $this->pon_obxeto(new Text    ("dominio", true));
    $this->pon_obxeto(new Text    ("pwd"    , true));
    $this->pon_obxeto(new Text    ("pri"           ));

    //* bd. mariadb.
    $this->pon_obxeto(new Text    ("host"));
    $this->pon_obxeto(new Text    ("bd"  ));
    $this->pon_obxeto(new Text    ("bd_u"));
    $this->pon_obxeto(new Password("bd_k", false, true));

    $this->pon_obxeto(new Button("bAceptar", "Instalar"));

    $this->pon_obxeto(new Button("bpcontrol", "Ir al panel de control"));
    $this->pon_obxeto(new Button("bweb", "Ir a la web"));
    
    $this->obxeto("bAceptar")->envia_AJAX("onclick");
    
    
    $this->inicia_refs();
  }

  public function operacion(EstadoHTTP $e) { //* RETURN: ?EstadoHTTP.
//~ echo $e->evento()->html();

    if ( !$this->visible ) return null;

    if ($this->obxeto("bAceptar")->control_evento()) return $this->operacion_bAceptar($e);

    return null;
  }

  private function operacion_bAceptar(EstadoHTTP $e):EstadoHTTP {
    try {
      //* validar formulario.
      $_val = $this->_validar();

//~ echo "<pre>" . print_r($_val, 1) . "</pre>";

      if ( $_val["ok"] != 1 ) {
        $e->post_msx( $_val["msx"] );

        return $e;
      }

      //* Configurar.
      //~ $cbd = $_val["_b"][1];
      $this->operacion_bAceptar_config( $_val );
    }
    catch (Exception $ex) {
      $cbd->rollback();

      $e->post_msx( $ex->getMessage() );
      

      return $e;
    }


    //* Config OK.
    $this->ptw = self::ptw_01;
    
    $this->operacion_bAceptar_botones( $_val );
    
    $this->preparar_saida($e->ajax());
    

    return $e;
  }

  private function operacion_bAceptar_config(array $_val):void {
    try {
      if ( ($ptw = file_get_contents(EInstalar::config_dist)) == false ) {
        throw new Exception("CInstalar.operacion_bAceptar_config(). !file_get_contents().");
        
        return;
      };

      $ptw = str_replace("[mysql_h]", $_val["_b"][0]["h"]      , $ptw);
      $ptw = str_replace("[mysql_b]", $_val["_b"][0]["b"]      , $ptw);
      $ptw = str_replace("[mysql_u]", $_val["_b"][0]["u"]      , $ptw);
      $ptw = str_replace("[mysql_p]", $_val["_b"][0]["k"]      , $ptw);

      $ptw = str_replace("[version]" , "202606"                , $ptw);
      $ptw = str_replace("[dominio]" , $_val["_r"]["_d"]["dom"], $ptw);
      $ptw = str_replace("[pwd]"     , $_val["_r"]["_d"]["pwd"], $ptw);
      $ptw = str_replace("[priv]"    , $_val["_r"]["_d"]["pri"], $ptw);

      if ( !file_put_contents(EInstalar::config_dest, $ptw) ) {
        throw new Exception("CInstalar.operacion_bAceptar_config(). !file_put_contents().");
        
        return;
      };
    }
    catch (Exception $ex) {
      throw $ex;
      
      return;
    }
  }

  private function operacion_bAceptar_botones(array $_val):void {
    $protocolo = (strtoupper(getenv("server.https")) != "ON") ? "http://" : "https://";
    
    $url_web = "{$protocolo}{$_val["_r"]["_d"]["dom"]}";
    $url_pct = "{$url_web}/triwus/pcontrol.php";
    
    $this->obxeto("bweb"     )->pon_eventos("onclick", "document.location='{$url_web}'");
    $this->obxeto("bpcontrol")->pon_eventos("onclick", "document.location='{$url_pct}'");
  }


  private function inicia_refs():void {
    //~ echo "Ruta del error.log: " . ini_get('error_log');
    //~ echo "<pre>" . print_r($_SERVER, 1) . "</pre>";
    
    $dir = $this->inicia_refs_dir();
    
    $pwd = "{$_SERVER["DOCUMENT_ROOT"]}/{$dir}";
    
    $dom = $this->inicia_refs_dom($dir);
    //~ $nom = $this->inicia_refs_nom($pwd);
    
    //~ echo "dir::{$dir}<br>";
    
    $this->obxeto("dominio")->post( $dom );
    $this->obxeto("pwd"    )->post( $pwd );
    $this->obxeto("pri"    )->post( ""   );
  }

  private function inicia_refs_dir():string {
    if (($cwd = getcwd()) == false) {
      throw new Exception("CInstalar.inicia_refs_dir(). (getcwd() == false).");
      
      return "";
    }
    
    $_dir = explode("/", $cwd);
    
    return $_dir[ count($_dir) - 2 ]; //* penúltimo
  }

  private function inicia_refs_dom(string $dir):string {
    $dom = $_SERVER["HTTP_HOST"];
    
    if ($dom == "localhost") return "{$dom}/{$dir}";
    if ($dom == "127.0.0.1") return "{$dom}/{$dir}";
    
    
    return $dom;
  }

  private function _validar():array {
    $_b = $this->_validar_BD();

    if ( $_b["ok"] != 1 ) return $_b;


    $_r = $this->_validar_refs( $_b["cbd"] );

    if ( $_r["ok"] != 1 ) return $_r;


    return ["ok"  => 1,
            "_b" => [$_b["_d"], $_b["cbd"]],
            "_r" => $_r
           ];
  }

  private function _validar_BD(): array {
    $h = trim($this->obxeto("host")->valor());
    $b = trim($this->obxeto("bd")->valor());
    $u = trim($this->obxeto("bd_u")->valor());
    $k = trim($this->obxeto("bd_k")->valor());

    $msx = null;

    if ($h == "") { $msx .= "Error, debes teclear un host.<br>"; }
    if ($b == "") { $msx .= "Error, debes teclear unha base de datos.<br>"; }
    if ($u == "") { $msx .= "Error, debes teclear un usuario (bd).<br>"; }
    if ($k == "") { $msx .= "Error, debes teclear un contrasinal (bd).<br>"; }


    if ( $msx != null ) return ["ok"  => 0, "msx" => $msx ];

    try {
        $cbd = self::cbd($h, $b, $u, $k, false);
    }
    catch (Exception $ex) {
        return [
            "ok"  => 0,
            "msx" => $ex->getMessage()
        ];
    }

    return [
        "ok"  => 1,
        "cbd" => $cbd,
        "_d"  => [
            "h" => $h,
            "b" => $b,
            "u" => $u,
            "k" => $k
        ]
    ];
}

  private function _validar_refs(MySql $cbd):array {
    $dom = trim( $this->obxeto("dominio")->valor() );
    $pwd = trim( $this->obxeto("pwd"    )->valor() );
    $pri = trim( $this->obxeto("pri"    )->valor() );
    
    //* valida pri.
    if (!is_dir($pri)) {
      return [
          "ok"  => 0,
          "msx" => "CInstalar._validar_refs(!is_dir([$pri])), "
      ];
    }

    return ["ok" => 1,
            "_d" => ["dom" => $dom,
                     "pwd" => $pwd,
                     "pri" => $pri
                    ]
           ];
  }


  private static function cbd(string $h, string $b, string $u, string $k, bool $verbose = false):?Conexion_bd {
    try {
      $cbd = new MySql($h, $u, $k, $b);

      $cbd->verbose = $verbose;

      //~ $cbd->transaccion(true);

      return $cbd;
    }
    catch (Exception $ex) {
      throw $ex;

      return null;
    }
  }
}
