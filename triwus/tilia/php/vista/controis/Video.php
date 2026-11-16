<?php

/**
 *
 * @package tilia\vista\controis
 */


class Video extends Audio {

  public function  __construct($nome, $src = null) {
    parent::__construct($nome, $src);

    $this->tag = "video";
    $this->mediagroup = "video";
  }
}
