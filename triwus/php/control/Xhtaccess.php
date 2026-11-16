<?php


final class Xhtaccess {
  private int    $site_k; //* Id do site na BD.
  private string $site_n; //* Nome do site na BD.
  private string $s_url;
  
  public function __construct(int $site_k, FS_cbd $cbd = null) {
    $this->site_k = $site_k;
    
    $this->s_url = Efs::url_site($site_k, $cbd);
    
    if (!is_dir($this->s_url)) {
      throw new Exception("!is_dir(this.s_url)");
       
      return;
    }
    
    
    $_url = explode("/", $this->url);
    
    $this->site_n = $_url[1]; //* site 
  }

  public function escribe(string $action_0, FS_cbd $cbd = null):void {
    if ($action_0 != null) {
      try {
        $this->escribe_index($action_0); 
      }
      catch (Exception $ex) {
        throw $ex;
      }
    }
    
    
    $url = $this->s_url . ".htaccess";
    
    try {
      $ptw = LectorPlantilla::plantilla( Refs::url("ptw/action/sites.htaccess") );

      $ptw = str_replace("[site_n]"     , $this->site_n          , $ptw);
      $ptw = str_replace("[url-index]"  , $action_0              , $ptw);
      $ptw = str_replace("[urls-custom]", $this->urlsCustom($cbd), $ptw);

      file_put_contents($url, $ptw);
    } 
    catch (Exception $ex) {
      throw $ex;
    }
  }


  private function escribe_index(string $action_0):void {
    if ( !is_file("{$this->s_url}{$action_0}") ) throw new Exception("!is_file('{$this->s_url}{$action_0}')");


    $url = $this->s_url . "index.php";
    
    try {
      $ptw = LectorPlantilla::plantilla(Refs::url("ptw/action/fs_index.php"));

      $ptw = str_replace("[url-index]", $action_0, $ptw);

      file_put_contents($url, $ptw);
    } 
    catch (Exception $ex) {
      throw $ex;
    }
  }
  
  private function urlsCustom(FS_cbd $cbd = null):string {
    $sql = "select a.url_custom, concat('sites/{$this->site_n}/', b.`action`, '?k=', a.id_paxina) as url_action
              from paxina a inner join menu_site_opcion b on (a.id_opcion = b.id_oms )
             where b.id_ms = {$this->site_k} and (not a.url_custom is null)";
             
    $urls = "";
    

    if ($cbd == null) $cbd = new FS_cbd();

    $r = $cbd->consulta($sql);
    
    while ($_r = $r->next()) {
      $urls .= "RewriteRule ^{$_r["url_custom"]}$ {$_r["url_action"]} [L]\n\r";
    }
    
    return $urls;
  }
}

