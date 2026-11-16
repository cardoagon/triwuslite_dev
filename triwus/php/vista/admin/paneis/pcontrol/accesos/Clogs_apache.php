<?php

final class Clogs_apache extends Componente implements ICPC {

  private $_glob  = null;

  
  public function __construct($id_site) {
    parent::__construct("clogs", "ptw/paneis/pcontrol/logs/apache.html");


    $this->pon_obxeto(new Param("lista_logs"));
    
    $this->pon_obxeto(new Hidden("hlogsevento"));
        
    $this->inicia($id_site);
  }

  public function operacion(EstadoHTTP $e) {
   
    if ($this->obxeto("hlogsevento")->control_evento()) {
      $i = $this->obxeto("hlogsevento")->valor();
      
      $e->pon_mime(null,
          mime_content_type( $this->_glob[$i] ),
          file_get_contents( $this->_glob[$i] ),
          filesize         ( $this->_glob[$i] ),
          basename         ( $this->_glob[$i] )
      ); 

      return $e;
    }

    return null;
  }
  
  public function declara_css() {
    return array(Refs::url("css/fs_lista.css"));
  }
  
  public function cpc_miga() {
    return "apache";
  }


  private function inicia($id_site) {

    $finfo = finfo_open(FILEINFO_MIME_TYPE); // devuelve el tipo mime de su extensión

    array_multisort(array_map('filemtime', ($this->_glob = glob(Efs::url_logs($id_site)))), SORT_DESC, $this->_glob);

    $_lista = [];

    $tabla = "";

    foreach($this->_glob as $g) {
      $g2 = basename($g);

      if ($g2 == ".")  continue;
      if ($g2 == "..") continue;

      $i = (is_array($_lista))?count($_lista):0;

      $a_i = array("i"=>$i,
       "Nombre"=>$g2,
       "Ruta"  => $g,
       "Size"=>File::html_bytes(filesize($g)),
       "Tipo"  =>finfo_file($finfo, $g),
       "Modificado"=>date("d/m/Y H:i:s",filemtime($g))
      );

      $_lista[$i] = $a_i;

      $tabla .= "<tr>
                   <td>{$a_i['Nombre']}</td>
                   <td align='right'>{$a_i['Size']}</td>
                   <td>{$a_i['Modificado']}</td>
                   <td style='text-align:right;width:19px;'>" . self::bDescarga($i) . "</td>
                 </tr>";
    }

    finfo_close($finfo);

    $this->obxeto("lista_logs")->post($tabla);
  }

  private function bDescarga($i) {
    $b = Panel_fs::__bexportar("x{$i}", null);
    
    $b->pon_eventos("onclick", "pcontrol_logs_descargar({$i})");

    return $b->html();
  }
}
