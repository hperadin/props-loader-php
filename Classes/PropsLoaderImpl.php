<?php
use PropsLoaderInterface;
// TODO: install and import monolog

class PropsLoaderImpl implements PropsLoaderInterface {
  private $logger;
  private $propsHome;
  private $file_path;

  private $resolveMap;
  private $source;

  private $propertiesArray;

  public function __construct($logger, $propsHome, $file_path){
    $this->logger = $logger;
    $this->propsHome = $propsHome;
    $this->file_path = $this -> findSingleFile($file_path);
  }

  /** Resolves a PropsLoader implementation */
  public function resolve($key) {
    try{
      if(!isset($resolveMap)){
        $resolveMap = array();
      }

      $cachedLoader = $resolveMap[$key];
      if($cachedLoader) return $cachedLoader;

      $base_dir = dirname($this->file_path);
      $value = $this->get($key);

      $resolvedFile = $value === "."
        ? $this->findSingleFile($base_dir.$key)
        : $this->findSingleFile($this->propsHome.$value."/".$key);

      $newLoader = new PropsLoaderImpl($this->logger, $this->propsHome, $this->file_path);
      $this->resolveMap[$key] = $newLoader;
      return $newLoader;
    }catch (Exception $ex){
      throw new InvalidArgumentException("Could not resolve key $key!", 0, $ex);
    }
  }

  /**
   * Looks into the parent directory of $file_path,
   * and checks that one and only one such file exists.
   */
  private static function findSingleFile($file_path){
    /* In the current dir filter all files starting with $file_prefix */
    $parent_dir = dirname($file_path);
    $file_prefix = basename($file_path);
    $files = array_filter(scandir($parent_dir),function ($f){return self::startsWith($file_prefix, $f);});

    switch (count($files)){
      case 0:
        throw new InvalidArgumentException("File with prefix '$file_prefix' not found!");
      case 1:
        return $files[0];
      default:
        throw new InvalidArgumentException("Ambiguous resolution, more than one file with prefix '$file_prefix' was found!");
    }
  }

  /** Gets the String value of a property */
  public function get($key){
    if($key === null){
      throw new InvalidArgumentException("Key cannot be null");
    }

    if(isset($this->getProperties()[$key])){
      return $this->getProperties()[$key];
    }else{
      throw new InvalidArgumentException("Key $key not found!");
    }
  }

  /** Gets the Int value of a property */
  public function getInt($key){
    $value = $this->get($key);
    if(is_int($value)){
      return intval($value);
    }else{
      throw new InvalidArgumentException("Key $key with value $value cannot be cast to integer!");
    }
  }

  private function loadProperties(){
    try{
      $logger -> trace("Loading properties from $file_name...");
      $this -> propertiesArray = (new JavaPropertiesReader($propsFile) ) -> read();
    } catch (Exception $ex) {
      throw new InvalidArgumentException("An error occured while parsing properties from $file_name", 0, $ex);
    }
  }

  /** Returns the properties' associative array */
  public function getProperties(){
    if(!isset($this->propertiesArray)){
      $this->loadProperties();
    }
    return $this->propertiesArray;
  }

  /** Returns the path to the properties source file */
  public function toPath(){
    return $this->file_path;
  }

  public function __toString(){
    return self::toString();
  }

  public function toString(){
    return $this->toString("ISO-8859-1");
  }

  public function toString($encoding){
    $flattened=array();
    foreach($this->propertiesArray as $key=>$value){
      $flattened[]=$key."=".value;
    }

    $imploded = implode("\n", $flattened);

    return mb_convert_encoding($imploded, $encoding);
  }

  private static function startsWith($prefix, $string){
    return substr($string, 0, count($prefix)) === $prefix;
  }

}
