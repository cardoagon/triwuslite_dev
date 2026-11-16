<?php

final class CLista_idiomas extends    FS_lista
                           implements Iterador_bd {

  private $a_idiomas = null;
  private $i         = -1;

  public function __construct(FGS_usuario $u, $ptw = null) {
    parent::__construct("lidiomas", new FSehtml_lidiomas(), $ptw);


    $id_site = $u->atr("id_site")->valor;


    $this->selectfrom = "select a.id_idioma, b.nome
                           from v_ml_site a inner join idioma b on (a.id_idioma = b.id_idioma)";
    $this->where      = "a.id_site = {$id_site}";
    $this->orderby    = "a.pos ASC";

    $this->inicia($id_site);
  }

  public function __iterador() {
    return $this;
  }

  public function activar($id_idioma, $b = -1) {
    if (($o = $this->obxeto("ch", $id_idioma)) == null) return;

    if ($b === -1) $b = !$o->valor();

    $o->post($b);
  }

  public function reset() {
    if (!$this->visible) return; //* non esta activado multilinguaxe

    if (count($this->a_idiomas) == 0) return;

    $a = null;

    foreach ($this->a_idiomas as $id_idioma) $this->activar($id_idioma, false);


    return $a;
  }

  public function a_checks() {
    if (!$this->visible) return -1; //* non esta activado multilinguaxe

    if (count($this->a_idiomas) == 0) return null;

    $a = null;

    foreach ($this->a_idiomas as $id_idioma) if ($this->obxeto("ch", $id_idioma)->valor()) $a[$id_idioma] = $id_idioma;


    return $a;
  }

  public function descFila() {}

  public function next() {
    $this->i++;

    if ($this->i >= count($this->a_idiomas)) {
      $this->i = -1;

      return null;
    }

    return $this->obxeto("ch", $this->a_idiomas[$this->i]);
  }

  private function inicia($id_site) {
    $this->a_idiomas = null;
    $this->i         = -1;


    $cbd = new FS_cbd();

    if (!MLsite_obd::activado($id_site)) {
      $this->visible = false;

      return;
    }


    $r = $cbd->consulta($this->sql_vista());

    while ($a = $r->next()) {
      //~ $checked = (count($this->a_idiomas) == 0);
      $checked = (!is_array($this->a_idiomas));

      $this->a_idiomas[] = $a['id_idioma'];

      $t = "&nbsp;&nbsp;&nbsp;{$a['id_idioma']}&nbsp;-&nbsp;{$a['nome']}";

      $this->pon_obxeto(new Checkbox("ch", $checked, $t), $a['id_idioma']);
    }
  }
}

//---------------------------------

final class FSehtml_lidiomas extends FS_ehtml {
  public $nf = 0;

  public function __construct() {
    parent::__construct();

    $this->class_table = "";
    $this->style_table = "";
    $this->align       = "center";
    $this->width       = "100%";
    $this->cellspacing = 0;
    $this->cellpadding = 2;
    $this->border      = 0;
  }

  protected function linha_detalle($df, $f) {
    return "<tr>
              <td>" . $f->html() . "</td>
            </tr>";
  }
}

