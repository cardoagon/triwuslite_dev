<?php

abstract class CLogo extends Componente {
  public $bmais  = 0; //* ($bmais ==  0) => NON permite asociar imaxes.
                      //* ($bmais >   0) => Permite asociar ate $bmais imaxes.
                      //* ($bmais == -1) => Permite asociar 'infinitas' imaxes.
  public $height = 64;
  public $width  = 64;
  public $mini   = false;

  protected $icl        = null;
  protected $a_src      = null;
  protected $a_txt      = null;
  protected $indice_src = null;
  protected $i_src      = null;

  protected $r_url      = null;
  protected $r_target   = null;


  public function __construct(ICLogo $icl, $id, $bmais = 0, $mini = true) {
    parent::__construct($id, Refs::url("ptw/clogo/clogo.html"));

    $this->bmais = $bmais;
    $this->mini  = false;


    $this->pon_obxeto(new CLogo_imx($this));

    $this->pon_obxeto(self::__flogo());

    $this->pon_obxeto(new Param("texto_logo"));

    $this->__inicia($icl);
  }

  public function __mini($mini) {}

  public function src($split = false) {
    $src = $this->a_src[$this->indice_src[$this->i_src]];

    if ($split) return basename($src);

    return $src;
  }

  public function id_src() {
    if ($this->indice_src[$this->i_src] == -1) return null;

    return $this->indice_src[$this->i_src];
  }

  public function selecciona($id) {
    if (!is_array($this->a_src)) return;

    $ct = count($this->a_src);

    //* escolle a imaxe selecionada
    for ($this->i_src = 0;
        (($id != $this->indice_src[$this->i_src]) && ($this->i_src < $ct));
        $this->i_src++);


    if ($this->i_src == $ct) $this->i_src = 0; //* sempre hai unha: 'Ico::nologo'
  }

  public function _redireccion($url = null, $target = "_self") {
    if ($url != null) {
      $this->r_url    = $url;
      $this->r_target = $target;
    }

    if ($this->r_url == null) return null;


    return array($this->r_url, $this->r_target);
  }

  public function pon_src($id, $src) {
    $this->a_src[$id] = $src;
  }

  public function pon_txt($id, $txt) {
    $this->a_txt[$id] = $txt;
  }

  public function operacion(EstadoHTTP $e) {
    //~ echo $e->evento()->html();

    if (($e_aux = $this->operacion_flogo($e, $this->obxeto("flogo"))) != null) return $e_aux;

    if ($this->a_src == null) return null;

    return null;
  }

  public function preparar_saida(Ajax $a = null) {
    //* PRECOND: count($this->a_src) > 0

    $this->obxeto("logo")->cambia_src();


    $this->pon_obxeto(new Span("etq", self::__etq($this)));

    $i = $this->indice_src[$this->i_src];

    if (isset($this->a_txt[$i])) $this->obxeto("texto_logo")->post($this->a_txt[$i]);

    return parent::preparar_saida($a);
  }

  protected function __inicia(ICLogo $icl) {
    $this->icl = $icl;

    $this->a_src = $icl->iclogo_src(new FS_cbd(), $this->mini);

    if ($this->a_src != null) $this->indice_src = array_keys($this->a_src);

    $this->obxeto("flogo")->visible = $this->bmais != 0;

    $this->selecciona($icl->iclogo_id());
  }

  final protected static function __flogo($id = "flogo") {
    $f = new File($id);

    $f->maxFileSize = 1572864;

    $f->accept      = "image/*";

    $f->envia_submit("onchange", "");


    $f->clase_css("default", "file_apagado");
    $f->clase_css("readonly", "file_apagado");


    return $f;
  }

  private static function __etq(CLogo $clp) {
    if (!is_array($clp->a_src)) return "";

    if (count($clp->a_src) < 2) return "";

    if (($t = count($clp->a_src)) == 0) $t = "0";
    if (($i = ($clp->i_src + 1) ) == 0) $i = "0";

    return "<div class='clogo_texto'>{$i}&nbsp;-&nbsp;{$t}</div>";
  }

  final public static function __boton($id, $css) {
    $b = Panel_fs::__bapagado($id, null, null, false, "<div class='{$css}'></div>");

    //~ $b->envia_AJAX("onclick");

    return $b;
  }

  final protected function operacion_flogo(Efs $e, File $f) {
    if ($this->bmais < 1)     return null;

    if (!$f->control_evento()) return null;


    $a_file = $f->valor();

    if (($erro = Erro::valida_upload($a_file)) != null) return $e;


    $id_file = uniqid($e->url_arquivos());

//~ echo "{$a_file['tmp_name']}::{$id_file}::" . basename($id_file) . "<br />";

    move_uploaded_file($a_file['tmp_name'], $id_file);

    if (!$this->update_logo($id_file)) return null;

    $this->__inicia($this->icl);

    return $e;
  }

  protected function update_logo($id_file) {
    $cbd = new FS_cbd();

    if (($icl_obd = $this->icl->iclogo_obd($cbd)) == null) die("CLogo.update_logo(), ((icl_obd = this.iclogo_obd()) == null)");

    $a_url   = Imaxe_obd::url_split($id_file);


    $icl_obd->atr("url"     )->valor = $a_url["url"];
    $icl_obd->atr("ref_site")->valor = $a_url["ref_site"];

    $cbd->transaccion();

    if ($icl_obd->atr("id_elmt")->valor == null) {
      if (!$icl_obd->insert($cbd)) {
        $cbd->rollback();

        return false;
      }
    }
    elseif (!$icl_obd->update($cbd)) {
      $cbd->rollback();

      return false;
    }



    //* escribimos o icl
    $this->icl->iclogo_id($icl_obd->atr("id_elmt")->valor);

    if (!$this->icl->update($cbd, null, null)) {
      $cbd->rollback();

      return false;
    }

    $cbd->commit();


    return true;
  }
}

//------------------------------------

final class CLogo_imx extends Image {
  public  $max_width = "100%";

  private $clp = null;

  public function __construct(CLogo $clp) {
    parent::__construct("logo", null, false);

    $this->clp = $clp;

    $title = "";

    $this->clase_css("default" , "celda_imaxe_album_destacada");
    $this->clase_css("readonly", "celda_imaxe_album_destacada");

    if ($clp->bmais != 0) {
      $title  = "Pincha aqu&iacute; para a&ntilde;adir o cambiar la foto";
      //~ $style .= " cursor: pointer;";

      $this->pon_eventos("onclick", "clogo_imx_click(this, 0)");
    }

    $this->title = $title;
  }

  public function cambia_src() {
    $this->pon_src( $this->clp->src(false) );

    if ($this->src == null) $this->src = Refs::url(Imaxe_obd::noimx);

    if ( !is_file($this->src()) ) return;


    $this->style("default" , "cursor:pointer;");
  }

  public function html():string {
    $html = parent::html();

    if (($_r = $this->clp->_redireccion()) == null) return $html;


    return "<a href='{$_r[0]}' target='{$_r[1]}'>{$html}</a>";
  }
}

//************************************

final class CLogo_articulo_ppo extends CLogo {
  public $minis_mostrar = 1; //* ($minis_mostrar == 0) => pinta todas as miniaturas.
                             //* ($minis_mostrar == 1) => NON pinta as miniaturas.

  public function __construct(Presuposto_paso_opcion_obd $ppo, $bmais = 1, $id = "clogoarti_ppo") {
    parent::__construct($ppo, $id, $bmais);

    $this->obxeto("flogo")->envia_submit("onchange", null, "def-prsto");
  }
}
