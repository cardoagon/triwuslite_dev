<?php

interface ISubsBotonera {
  public function op_aceptar (ESubs $e):ESubs;
  public function op_cancelar(ESubs $e):ESubs;
  public function op_suprimir(ESubs $e):ESubs;
  public function op_engadir (ESubs $e):ESubs;
}

//************************************

class SubsBotonera extends Componente {
  public $ptw_0 = "ptw/componentes/comun/btnr/[config].html";

  private $t = 0;
  
  private static array $_l = ["gl" => ["a" => "Aceptar", "e" => "Engadir", "s" => "Suprimir", "p" => "Pechar"],
                              "es" => ["a" => "Aceptar", "e" => "Añadir" , "s" => "Suprimir", "p" => "Cerrar"]
                             ];

  public function __construct(string $l = "gl") {
    parent::__construct("cbotonera");

    $this->pon_obxeto( Panel_fs::__bcancelar(self::$_l[$l]["p"]));
    $this->pon_obxeto( Panel_fs::__baceptar (self::$_l[$l]["a"]) );

    $this->pon_obxeto( Panel_fs::__bmais    ("bEngadir" , self::$_l[$l]["e"]) );
    $this->pon_obxeto( Panel_fs::__bsup     ("bSuprimir", self::$_l[$l]["s"]) );
    $this->config(0);

    //~ $this->obxeto("bSuprimir")->envia_submit("onclick", "¿ Desexas eliminar o rexistro seleccionado ?");
    $this->obxeto("bSuprimir")->envia_ajax("onclick", "¿ Desexas eliminar o rexistro seleccionado ?");
    $this->obxeto("bEngadir" )->envia_ajax("onclick");
    $this->obxeto("bCancelar")->envia_ajax("onclick");
    $this->obxeto("bAceptar" )->envia_ajax("onclick");
  }

  public function readonly($b = true) {
    $this->aceptar ( !$b );
    $this->engadir ( !$b );
    $this->suprimir( !$b );
  }

  public function aceptar(bool $b) {
    $this->obxeto("bAceptar")->visible = $b;
  }

  public function engadir(bool $b) {
    $this->obxeto("bEngadir")->visible = $b;

    $this->config( $this->t );
  }

  public function suprimir(bool $b) {
    $this->obxeto("bSuprimir")->visible = $b;
  }

  public function operacion(EstadoHTTP $e) {
    if ($this->obxeto("bCancelar")->control_evento()) return $this->pai->op_cancelar($e);

    if ($this->obxeto("bEngadir" )->control_evento()) return $this->pai->op_engadir ($e);
    if ($this->obxeto("bSuprimir")->control_evento()) return $this->pai->op_suprimir($e);
    if ($this->obxeto("bAceptar" )->control_evento()) return $this->pai->op_aceptar ($e);

    return null;
  }

  public function config($t) {
    $this->t = 0;

    if (($t == 0) && (!$this->obxeto("bEngadir")->visible))
      $this->visible = false;
    else
      $this->visible = true;

    $this->ptw = str_replace("[config]", "{$t}", $this->ptw_0);
  }
}

