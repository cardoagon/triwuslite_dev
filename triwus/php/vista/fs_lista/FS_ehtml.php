<?php


abstract class FS_ehtml extends CLista_ehtml {
  const style_tr   = "";
  const style_tr_a = "style='background-color: #fcf5d5;'";


  public function __construct(Iterador_bd $rbd = null) {
    parent::__construct($rbd);

    $this->class_table = "lista_00";
  }

  protected function __bmais_html($id_rama, $readonly = false, $css = null) {
    if ($this->xestor->sublista_prototipo($id_rama) == null) {
      return "";
    }
    else {
      $readonly_aux = $readonly;

      if (!$this->rama_check($id_rama)) {
        $id = "brama_mais";
        $etq = "▶";
        $tit = "Expandir rama";
      }
      else {
        $id = "brama_menos";
        $etq = "▼";
        $tit = "Contraer rama";
      }
    }

    $b = self::__boton($id, $tit, $etq, $readonly_aux, $css);

    $b->clase_css("default", "lista_bmais");
    $b->clase_css("readonly", "lista_bmais");

    return $this->__fslControl($b, $id_rama)->html();
  }
}
