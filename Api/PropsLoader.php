<?php

require_once 'Api/PropsResolver.php';

interface PropsLoader extends PropsResolver, Iterator {

  /** Gets the String value of a property */
  public function get($key);
  /** Gets the Int value of a property */
  public function getInt($key);

  /** Returns the properties' associative array */
  public function getProperties();

  public function __toString();
  public function toString($encoding);
}
