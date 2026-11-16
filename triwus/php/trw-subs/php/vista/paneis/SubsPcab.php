<?php

abstract class SubsPcab extends Panel {
  public function __construct(string $ref = "") {
    parent::__construct("pcab", $ref . $this->ptw());
  }

  abstract protected function ptw():string;
}

