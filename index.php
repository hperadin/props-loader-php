<?php

require_once 'vendor/autoload.php';

require_once 'Core/PropsLoaderImpl.php';
require_once 'Core/PropsLoaderFactory.php';

require_once 'Util/utils.php';

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

function initLogger(){
  $logger = new Logger("PropsLoaderPhpTestLogger");
  $logger->pushHandler(new StreamHandler(__DIR__.'/props_loader.log', Logger::DEBUG));

  return $logger;
}

$logger = initLogger();

/* Sanity check;
 * List all properties folders in usr.home, and try to loadem all
 * */

$propsLoaderFactory = PropsLoaderFactory::init($logger);

$propertiesHomeDir = getUserHome() . "/.props/";
$projects = scandir($propertiesHomeDir);

foreach ($projects as $project){
    if($project === '.' || $project === '..' || $project === 'IPCalculator_SE_Private_Karl'
        || $project === 'bsp-ds-api_SE_Private_Karl'
        || $project === 'tmp')
      continue;
    print "\n";
    print "Trying to load all properties for project: $project\n";
    $propsLoader = $propsLoaderFactory -> loadPure($project);
}
