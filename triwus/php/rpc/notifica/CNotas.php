<?php

final class CNotas extends    Componente 
                   implements IComponente_aa {
                            
  private $b = false; //* control unha vez por sesión.
  
  private $s; //* id_site
  private $m; //* momento_ini
  private $d; //* dominio
  private $a; //* cookie: trw_notifica

  //~ private $id_op;

  public function __construct($dominio, $data_anterior) {
    parent::__construct("cbusca", self::ptw);

    $this->d = $dominio;
    $this->a = $data_anterior;
  }

  public function aa_exec($tilia_op) { //* IMPLEMENTA IComponente_aa
    switch ($tilia_op) {
      case 1: return $this;
      
      case 2: return $this->aa_exec_stats(1, 0);
      case 3: return $this->aa_exec_stats(0, 1);
    }
    return $this; 
  }
  
  public function aa_json($tilia_op) {  //* IMPLEMENTA IComponente_aa
    if ($tilia_op != 1) return null;
    
    return json_encode( $this->leer_nota() );
  }

  private function leer_nota() {
    //* control 1 vez por sesión.

    if ($this->b) return null; //* xa se enviaron notificacións para esta sesión.

    $this->b = true;

    //* Busca notificacións pendentes.
    $cbd = new FS_cbd();
    
    $sql = "select a.id_site, a.txt, a.ico, a.momento_ini, a.url
              from site_notifica a inner join site_plesk b on (a.id_site = b.id_site)
             where a.id_site = '{$this->d}' and a.momento_ini > {$this->a} and a.momento_fin > now()
             limit 0, 1";

    $r = $cbd->consulta($sql);

    //* só lemos unha tupla, (limit 0, 1).
    if (!$_r = $r->next()) return null; //* sen notificacións.


    //* gardamos a k da notificación. (útil para stats).
    $this->s = $_r['id_site'    ];
    $this->d = $_r['momento_ini'];

    
    //* devolve notificación.
    return array("t" => $_r['txt'], "i" => $_r['ico'], "u" => $_r['url'], "m" => $_r['momento_ini']);
  }

  private function aa_exec_stats($a, $b) {
    $cbd = new FS_cbd();
    
    $sql = "update site_notifica
               set stats_a = stats_a + {$a}, stats_b = stats_b + {$b}
             where id_site = {$this->s} and momento_ini > '{$this->m}'";

    $cbd->executa($sql);

    
    return $this;
  }
}
