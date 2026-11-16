<?php

/** Representa un elemento audio de html5.
 *
 * @package tilia\vista\controis
 */

class Audio extends Control {
  public $autoplay = false;
  public $controls = true;
  public $preload = "none";
  public $mediagroup = "audio";
  public $loop = false;
  public $muted = false;

  protected $sources = null;

  protected $tag = "audio";

  public function  __construct($nome, $src = null) {
    parent::__construct($nome);

    if ($src != null) $this->pon_source($src);
  }

  public function pon_source($src, $codecs = null, $type = null) {
    $this->sources[] = new Source($src, $codecs, $type);
  }

  public function html():string {
    if (!$this->visible) return "";

    for ($i = 0; $i < count($this->sources); $i++) $sources .= $this->sources[$i]->html();

    if ($this->controls) $controls = "controls";
    if ($this->autoplay) $autoplay = "autoplay";
    if ($this->loop) $loop = "loop";
    if ($this->muted) $muted = "muted";

    $height = ($this->height == null)?"":"height=\"{$this->height}\"";
    $width = ($this->width == null)?"":"width=\"{$this->width}\"";
    $ancla = $this->html_ancla();

    return "
      {$ancla}
      <{$this->tag} " . $this->html_nome() . "
           {$height} {$width}
           " . $this->html_clase() .  "
           " . $this->html_estilo() .  "
           " . $this->html_eventos() .  "
           " . $this->html_title() .  "
           {$controls} {$autoplay} {$loop} {$muted} >
        {$sources}
      </{$this->tag}>";
  }
}


/** Representa un elemento audio de html5.
 *
 * @package tilia\vista\controis
 */
final class Source {
  private $src = null;
  private $type = null;
  private $codecs = null;

  public function  __construct($src, $codecs = null, $type = null) {
    $this->src = $src;
    $this->codecs = $codecs;
    $this->type = ($type == null)?mime_content_type($src):$type;
  }

  public function html():string {
    if ($this->codecs != null) $codecs = " codecs = \"{$this->codecs}\"";
    if ($this->type != null) $type = " type = \"{$this->type}\"";

    return "<source src='{$this->src}'{$codecs}{$type} />";
  }
}
