<?php

use langdonglei\util\command\fastadmin\addon\Install;
use langdonglei\util\command\fastadmin\addon\Zip;
use Symfony\Component\Console\Application;

include __DIR__ . '/../../vendor/autoload.php';

$application = new Application();
$application->add(new Install());
$application->add(new Zip());
$application->run();
