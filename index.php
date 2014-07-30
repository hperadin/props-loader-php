<?php

require_once 'vendor/autoload.php';

require_once 'Core/PropsLoaderImpl.php';
require_once 'Core/PropsLoaderFactory.php';

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

function initLogger(){
  $logger = new Logger("PropsLoaderPhpTestLogger");
  $logger->pushHandler(new StreamHandler(__DIR__.'/props_loader.log', Logger::DEBUG));

  return $logger;
}

$logger = initLogger();

$propsLoaderFactory = PropsLoaderFactory::init($logger);
$propsLoader = $propsLoaderFactory -> loadPure("global");

$props = $propsLoader->getProperties();

var_dump($props);
