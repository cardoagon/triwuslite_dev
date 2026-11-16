<?php


/** Representa ao elemento html &lt;iframe&gt; ... &lt;/iframe&gt;
 *
 * @package tilia\vista\controis
 */

class IFrame extends Control implements IAJAX {

  public $url = "";

  public $align = "center";

  public $borde = "0";

  public $scrolling = "auto";

  public $height = "100%";

  public $width = "100%";

  public function  __construct($nome, $url = null) {
    parent::__construct($nome);

    $this->url = $url;
  }

  public function html():string {
    $h = ($this->height == null)?"":"height='{$this->height}'";
    $w = ($this->width == null)?"":"width='{$this->width}'";

    if ($this->url != null) $src = " src='{$this->url}'";

    return "
      " . $this->html_ancla() . "
      <iframe " . $this->html_nome() . "
              {$w} {$h} {$src}
              scrolling='{$this->scrolling}'
              align='{$this->align}' frameborder='{$this->borde}'
                " . $this->html_clase() .  " " . $this->html_estilo() .  ">
      </iframe>
      " . self::html_contido($this->nome_completo(), $this->valor());
  }

  private static function html_contido($id, $contido) {
    if ($contido == null) return "";

    return "<div id='{$id}_aux'>{$contido}</div>
            <script>
              iframe_poncontido('{$id}');
            </script>";
  }

  public function fillosAJAX() { //* plantilla para implementar IAJAX.
    return $this->url;
  }
}
