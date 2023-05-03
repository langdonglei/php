<?php

use langdonglei\util\command\fastadmin\addon\Install;
use langdonglei\util\command\fastadmin\addon\Zip;
use Symfony\Component\Console\Application;


$root = dirname(__DIR__);

if (!is_file($root . '/vendor/autoload.php')) {
    $root = dirname(__DIR__, 2);
}
var_dump($root);exit();
include __DIR__ . '/../../vendor/autoload.php';

$application = new Application();
$application->add(new Install());
$application->add(new Zip());
$application->run();
