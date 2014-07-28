<?php

require_once 'Classes/PropsLoaderImpl.php';

use Monolog\Logger;

class PropsLoaderFactory {

  private $logger;
  private $propsHome;

  private function __construct(Monolog\Logger $logger){
    $this->logger = $logger;
    $this->propsHome = $this->resolvePropsHome();
  }

  /** Initialises and returns a new PropsLoaderFactory */
  public static function init(Monolog\Logger $logger){
    $logger->info("Initializing PropsLoaderFactory ...");
    return new PropsLoaderFactory($logger);
  }

  private function resolvePropsHome() {
    $userHome = self::getUserHome();
    return "$userHome/.props/";
  }

  private function resolveProperty($key){
    $value = ini_get($key);
    $this->logger->info("Resolving system property $key, $value");
    if($value === FALSE)
      throw new InvalidArgumentException("System property '$key' was undefined.");
    return $value;
  }

  /** Returns a no-branch(?) PropsLoader*/ //TODO:
  public function loadPure($projectName){
    return $this->loadBranch($projectName, null);
  }

  /** Returns the PropsLoader for a given branch (?)*/ // TODO:
  public function loadBranch($projectName, $branch='') {

    /* XXX: See the Java source. This is to circumvent the fact
     we cannot have multiple functions with the same name */
    if(!is_null($branch)) $branch = $projectName;

    if($branch !== null){
      $file_path = $this->propsHome . $projectName . "_" . $this->resolveProperty($branch.".branch");
    }else{
      $file_path = $this->propsHome . $projectName;
    }

    $this->logger->debug("Resolved path for _: $file_path");
    $propsResolver = new PropsLoaderImpl($this->logger, $this->propsHome, $file_path."/"."_");

    /* Eagerly try to resolve all dependencies, and fail early*/
    foreach($propsResolver->getProperties() as $key => $value){
      try{
        $resolvedProps = $propsResolver->resolve($key);
        $props = $resolvedProps->getProperties();
        $this->logger->info("$key = ".$resolvedProps->toPath());
      }catch(Exception $ex){
        throw new RuntimeException("Could not resolve key '$key' with value "
            .$propsResolver->get($key)." from ".$propsResolver->toPath(), 0, $ex);
      }
      return $propsResolver;
    }
  }

  /**
   * Gets the user home path (Windows and Linux)
   */
  private static function getUserHome(){
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
}
