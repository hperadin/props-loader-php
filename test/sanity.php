<?php

require_once 'vendor/autoload.php';

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

use PropsLoader\Core\PropsLoaderImpl;
use PropsLoader\Core\PropsLoaderFactory;
use PropsLoader\Util\Utils;

function initLogger(){
  $logger = new Logger("PropsLoaderPhpTestLogger");
  $logger->pushHandler(new StreamHandler(__DIR__.'/props_loader.log', Logger::DEBUG));
//   $logger->pushHandler(new StreamHandler('php://stdout', Logger::DEBUG));

  return $logger;
}

$logger = initLogger();

/* Sanity check;
 * List all properties folders in usr.home, and try to loadem all
 * */

$propsLoaderFactory = PropsLoaderFactory::init($logger);

$propertiesHomeDir = Utils::getUserHome() . "/.props/";
$projects = scandir($propertiesHomeDir);

foreach ($projects as $project){
  try {
    print "======================================================\n";
    print "Loadin properties for project: $project\n";
    $propsLoader = $propsLoaderFactory -> loadPure($project);
    print "Loaded props from file: " . $propsLoader->toPath()."\n";
    var_dump($propsLoader->toProperties());
    print "======================================================\n";
  }catch(Exception $ex){
    //print $ex;
    print "Skipping $project...\n";
  }

}
