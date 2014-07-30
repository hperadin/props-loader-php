<?php

function startsWith($prefix, $string){
  return substr($string, 0, strlen($prefix)) === $prefix;
}
