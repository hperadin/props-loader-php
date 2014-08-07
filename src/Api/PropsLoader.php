<?php

namespace PropsLoader\Api;

interface PropsLoader extends \PropsLoader\Api\PropsResolver, \Iterator {

  /** Gets the String value of a property */
  public function get($key);
  /** Gets the Int value of a property */
  public function getInt($key);

  /** Returns the properties' associative array */
  public function toProperties();

  public function __toString();
  public function toString($encoding);
}
