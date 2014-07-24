<?php
interface PropsLoaderInterface {

  /* TODO: Return types */

  /** Resolves a PropsLoader implementation */
  public function resolve($key);

  /** Gets the String value of a property */
  public function get($key);

  /** Gets the Int value of a property */
  public function getInt($key);

  /** Returns the properties' associative array */
  public function getProperties();

  /** Returns the path to the properties source file */
  public function toPath();

  public function __toString();
  public function toString();
  public function toString($encoding);
}
