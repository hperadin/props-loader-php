<?php

function startsWith($prefix, $string){
  return substr($string, 0, strlen($prefix)) === $prefix;
}

/**
 * Gets the user home path (Windows and Linux)
 */
function getUserHome(){
  if(isset($_SERVER['HOME']) && !empty($_SERVER['HOME'])){
    /* This should work on a Linux */
    return $_SERVER['HOME'];
  }else if(
      isset($_SERVER['HOMEDRIVE']) && !empty($_SERVER['HOMEDRIVE'])
      && isset($_SERVER['HOMEPATH']) && !empty($_SERVER['HOMEPATH'])){
    /* This should work on Windows */
    return $_SERVER['HOMEDRIVE'].$_SERVER['HOMEPATH'];
  }else{
    throw new Exception("Unable to retrieve the user home directory path.");
  }
}
