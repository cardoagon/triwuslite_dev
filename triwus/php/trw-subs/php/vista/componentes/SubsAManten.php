<?php

abstract class SubsAManten extends Componente
                        implements ISubsBotonera, 
                                   ISubsBusca {
                                     
  protected string $l = "gl";

  public function __construct($id, $ptw = null, string $l = "gl") {
    parent::__construct($id, $ptw);
    
    $this->l = $l;

    $this->pon_obxeto( $this->declara_busca   () );
    $this->pon_obxeto( $this->declara_botonera() );
    $this->pon_obxeto( $this->declara_lista   () );
    $this->pon_obxeto( $this->declara_ficha   () );
    
    $this->lista()->post_where( $this->busca()->sql_where() );
  }


  abstract public function campos_busqueda_texto():array; //* implementa ISubsBusca.campos_busqueda_texto()

  abstract public function op_ficha   (ESubs $e, $k):ESubs; 
  
  abstract public function op_aceptar (ESubs $e    ):ESubs; //* implementa ISubsBotonera.op_aceptar()
  abstract public function op_suprimir(ESubs $e    ):ESubs; //* implementa ISubsBotonera.op_suprimir()

  public function op_engadir(ESubs $e):ESubs { //* implementa ISubsBotonera.op_engadir()
    return $this->op_ficha($e, null);
  }

  public function op_cancelar(ESubs $e):ESubs { //* implementa ISubsBotonera.op_cancelar()
    $this->botonera()->config(0);

    $this->ficha()->pechar();

    $this->preparar_saida( $e->ajax() );

    return $e;
  }

  public function op_busca(ESubs $e, string $where):ESubs { //* implementa ISubsBusca.op_busca()
    $this->lista()->post_where($where);
  // echo $this->lista()->sql_vista();

    $this->preparar_saida($e->ajax());

    return $e;
  
  }

  public function op_csv  (ESubs $e, string $where):ESubs { //* implementa ISubsBusca.op_csv()
    $eCSV = $this->csv( $where );
    
    $eCSV->devolver($e);

    return $e;
  }

  final public function botonera():SubsBotonera {
    return $this->obxeto("cbotonera");
  }

  final public function busca():SubsBusca {
    return $this->obxeto("cbusca");
  }

  final public function ficha():SubsFicha {
    return $this->obxeto("cficha");
  }

  final public function lista():SubsClista {
    return $this->obxeto("clista");
  }

  public function k() { //* RETURN mixed
    return $this->ficha()->k();
  }

  public function operacion(EstadoHTTP $e) { //* RETURN EstadoHTTP
    if (($e_aux = $this->lista   ()->operacion($e)) != null) return $e_aux;
    if (($e_aux = $this->ficha   ()->operacion($e)) != null) return $e_aux;
    if (($e_aux = $this->busca   ()->operacion($e)) != null) return $e_aux;
    if (($e_aux = $this->botonera()->operacion($e)) != null) return $e_aux;

    return parent::operacion($e);
  }


  abstract protected function csv(string $where):SubsCSV;
  
  abstract protected function declara_lista  ():SubsClista;
  abstract protected function declara_ficha   ():SubsFicha;

  protected function declara_botonera():SubsBotonera {
    return new SubsBotonera( $this->l );
  }

  protected function declara_busca():SubsBusca {
    return new SubsBusca();
  }
}
