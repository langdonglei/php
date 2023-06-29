<?php

include __DIR__ . '/vendor/autoload.php';


$appid  = 'wxca50400cc02da30b';
$secret = '84ffae23adbfb6aa652ce8dfdc17738a';

$access_token = \langdonglei\WeChat::getAccessCode($appid,$secret);

$a=\langdonglei\WeChat::generateShortLink($access_token,'pages/index/index?a=1');
dd($a);