#!/usr/bin/env php
<?php

use langdonglei\util\command\fastadmin\addon\Install;
use langdonglei\util\command\fastadmin\addon\Zip;
use Symfony\Component\Console\Application;

$root = dirname(__DIR__);
if (!is_file($root . '/vendor/autoload.php')) {
    $root = dirname(__DIR__, 2);
}
require_once $root . '/vendor/autoload.php';

$application = new Application();
$application->add(new Install());
$application->add(new Zip());
$application->run();
