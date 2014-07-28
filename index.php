<?php

require_once 'vendor/autoload.php';

require_once 'Classes/PropsLoaderImpl.php';
require_once 'Classes/PropsLoaderFactory.php';

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$logger = new Logger("PropsLoaderPhpTestLogger");
$logger->pushHandler(new StreamHandler(__DIR__.'/props_loader.log', Logger::DEBUG));

$projectName = "global";

$propsLoaderFactory = PropsLoaderFactory::init($logger);
$propsLoader = $propsLoaderFactory -> loadBranch($projectName);

$props = $propsLoader->getProperties();

var_dump($props);
