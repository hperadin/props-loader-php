<?php
interface PropsResolver {

  /** Load a PropsResolver by $key */
  public function loadResolver($key);

  /** Resolve a PropsLoader by $key */
  public function resolve($key);

  /** Returns a full file path for this PropsResolver */
  public function toPath();

}
