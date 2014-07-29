<?php
interface PropsResolver {

  /** Load a PropsResolver by $key */
  public function loadResolver($key);

  /** Resolve a PropsLoader by $key */
  public function resolve($key);

  /** Returns a full file path for this PropsResolver */
  public function toFile();

  // TODO: see if nomenclature should be changed, this is the same as toFile in the Java version
  /** Returns a full file path for this PropsResolver */
  public function toPath();

}
