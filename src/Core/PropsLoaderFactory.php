<?php

require_once 'src/Core/PropsLoaderImpl.php';

require_once 'src/Util/utils.php';

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
    $userHome = getUserHome();
    return "$userHome/.props/";
  }

  /**
   * Tries to resolve a system property.
   *
   * First checks in php.ini, on fail tries to read the
   * property from the environment.
   * */
  private function resolveProperty($key){
    $this->logger->debug("Resolving the system property for: '$key'");
    $value = ini_get($key);

    if($value === FALSE){
      $this->logger->debug("Nothing found in the system, trying to find '$key' in the environment variables.");
      $value = getenv($key);
      if($value === FALSE){
        $message = "No property found for key '$key', nor in the system configuration, nor the environment";
        $this->logger->error($message);
        throw new InvalidArgumentException($message);
      }
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

    $cachedPropsResolver = valOrNull($this->propertiesResolversCache[$projectBranch]);
    if($cachedPropsResolver) return $cachedPropsResolver;

    if($branch !== null){
      $file_path = $this->propsHome . $projectName . "_" . $this->resolveProperty($branch.".branch");
    }else{
      $file_path = $this->propsHome . $projectName;
    }

    $this->logger->debug("Resolved path for _: $file_path");
    $propsResolver = new PropsLoaderImpl($this->logger, $this->propsHome, $file_path."/"."_");

    $this->logger->debug("Eagerly resolving all dependencies...");
    foreach($propsResolver->toProperties() as $key => $value){
      try{
        $this->logger->debug("Resolving dependencies for $key => $value");
        $isResolver = PropsLoaderImpl::isResolver($value);
        $this->logger->debug("Is resolver = $isResolver");

        if($isResolver){
          $resolvedProps = $propsResolver->loadResolver($key);
          $this->logger->info("$key = ". $resolvedProps->toPath());
        }else{
          $loadedProps = $propsResolver->resolve($key);
          $loadedProps->toProperties();
          $this->logger->info("$key = ". $loadedProps->toPath());
        }
      }catch(Exception $ex){
        $this->logger->error($ex);
        throw new RuntimeException("Could not resolve key '$key' with value "
            .$propsResolver->get($key)." from ".$propsResolver->toPath(), 0, $ex);
      }
    }
    $this->logger->debug("Caching a resolver for $projectBranch");
    $this->propertiesResolversCache[$projectBranch] = $propsResolver;
    return $propsResolver;
  }
}
