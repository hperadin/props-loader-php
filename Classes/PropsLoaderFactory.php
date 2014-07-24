<?php

// TODO: install and import monolog

// TODO: Type hints wherever whenever

class PropsLoaderFactory {

  private $logger;
  private $propsHome;

  public function __construct($logger){
    $this->logger = $logger;
  }

  /** Initialises and returns a new PropsLoaderFactory */
  public static function init($logger){
    $logger->trace("Initializing PropsLoaderFactory ...");
    return new PropsLoaderFactory($logger);
  }

  private function resolvePropsHome() {
    $userHome = self::getUserHome();
    return "$userHome.props";
  }

  private function resolveProperty($key){
    $value = ini_get($key);
    if($value === FALSE)
      throw new InvalidArgumentException("System property $key was undefined.");
    $this->logger->trace("Resolved system property: $key: $value");
    return $value;
  }

  /** Returns a no-branch(?) PropsLoader*/ //TODO:
  public function loadPure($projectName){
    return $this->loadBranch($projectName, null);
  }

  /** Returns the main branch(?) PropsLoader */ //TODO:
  public function loadBranch($projectName) {
    return loadBranch($projectName, $projectName);
  }

  /** Returns the PropsLoader for a given branch (?)*/ // TODO:
  public function loadBranch($projectName, $branch) {
    if($branch !== null){
      $file_path = $projectName . "_" . resolveProperty($branch);
    }else{
      $file_path = $projectName;
    }

    $this->logger.debug("Resolved path for _: $file_path");
    $propsResolver = new PropsLoaderImpl($this->logger, $this->propsHome, "$file_path_");

    foreach($propsResolver->getProperties() as $key => $value){
      try{
        $resolvedProps = $propsResolver->resolve($key);
        $props = $resolvedProps->getProperties();
        $this->logger->info("$key = ".$resolvedProps->toPath());
      }catch(Exception $e){
        throw new RuntimeException("Could not resolve key $key with value "
            .$propsResolver->get($key)." from ".$propsResolver->toPath());
      }
      return $propsResolver;
    }
  }

  private static function getUserHome(){
    $home = $_SERVER['HOME'];
    $homeDrive = $_SERVER['HOMEDRIVE'];
    $homePath = $_SERVER['HOMEDRIVE'];

    if(!empty($home)){
      return $home;
    }else if(!empty($homeDrive) && !empty($homePath)){
      return $homeDrive.$homePath;
    }else{
      throw new Exception("Unable to retrieve the user home directory path.");
    }
  }
}
