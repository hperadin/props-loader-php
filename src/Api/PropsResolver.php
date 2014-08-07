<?php
namespace PropsLoader\Api;

interface PropsResolver {

  /** Load a PropsResolver by $key */
  public function loadResolver($key);

  /** Resolve a PropsLoader implementation by $key */
  public function resolve($key);

  /** Returns a full file path to the properties file */
  public function toPath();

}
