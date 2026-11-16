<?php

abstract class SubsPpe extends Panel {
  public function __construct($ref = "") {
    parent::__construct("ppe", $ref . $this->ptw());
  }

  abstract protected function ptw():string;
}
