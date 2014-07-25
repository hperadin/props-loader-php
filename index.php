<?php
require_once 'Classes/JavaPropertiesReader.php';

$javaPropsReader = new JavaPropertiesReader("./test/test.props");
$props = $javaPropsReader->read();

var_dump($props);
