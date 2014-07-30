<?php

require_once 'Core/PropsLoaderImpl.php';

require_once 'Util/utils.php';

use Monolog\Logger;

class PropsLoaderFactory {

  private $logger;
  private $propsHome;

  private $propertiesResolversCache=array();

  private function __construct(Monolog\Logger $logger){
    $this->logger = $logger;
    $this->propsHome = $this->resolvePropsHome();
  }

  /** Initialises and returns a new PropsLoaderFactory */
  public static function init(Monolog\Logger $logger){
    $logger->debug("Initializing PropsLoaderFactory ...");
    return new PropsLoaderFactory($logger);
  }

  private function resolvePropsHome() {
    $userHome = self::getUserHome();
    return "$userHome/.props/";
  }

  /**
   * Tries to resolve a system property.
   *
   * First checks in php.ini, on fail tries to read the
   * property from the environment.
   * */
  private function resolveProperty($key){
    $value = ini_get($key);
    $this->logger->debug("Resolving system property $key, $value");

    if($value === FALSE){
      throw new InvalidArgumentException("System property '$key' was undefined.");
    }

    return $value;
  }

  /** Loads a PropsResolver by $projectName with null $branch */
  public function loadPure($projectName){
    return $this->loadBranch($projectName, null);
  }

  /** Loads a PropsResolver by $projectName and $branch */
  public function loadBranch($projectName, $branch='') {
    /* If the branch is omited, the $branchName is taken to be sam as $projectName
     * This is to mirror the java code, where there is a difference
     * between an omited and a null branch */
    if(!is_null($branch)) $branch = $projectName;

    $projectBranch = $projectName.":".$branch;

    $cachedPropsResolver = isset($this->propertiesResolversCache[$projectBranch])
      ?   $this->propertiesResolversCache[$projectBranch]
      : null;
    if($cachedPropsResolver) return $cachedPropsResolver;

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
        $isResolver = PropsLoaderImpl::isResolver($value);
        $this->logger->debug("Is resolver = $isResolver");

        if($isResolver === TRUE){
          $resolvedProps = $propsResolver->loadResolver($key);
          $this->logger->info("$key = ". $resolvedProps->toPath());
        }else{
          $loadedProps = $propsResolver->resolve($key);
          $loadedProps->getProperties();
          $this->logger->info("$key = ". $loadedProps->toPath());
        }

        $resolvedProps = $propsResolver->resolve($key);
        $props = $resolvedProps->getProperties();
        $this->logger->info("$key = ".$resolvedProps->toPath());
      }catch(Exception $ex){
        throw new RuntimeException("Could not resolve key '$key' with value "
            .$propsResolver->get($key)." from ".$propsResolver->toPath(), 0, $ex);
      }
      $this->propertiesResolversCache[$projectBranch] = $propsResolver;
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
