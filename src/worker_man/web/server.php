<?php

use Workerman\Connection\TcpConnection;
use Workerman\Protocols\Http\Request;
use Workerman\Protocols\Http\Response;
use Workerman\Worker;

require_once __DIR__ . '/../../../vendor/autoload.php';
const WEBROOT = __DIR__ . DIRECTORY_SEPARATOR . 'page';
function get_content($file)
{
    ob_start();
    try {
        include $file;
    } catch (Exception $e) {
        echo $e;
    }
    return ob_get_clean();
}

$web = new Worker("http://0.0.0.0:80");
$web->count = 2;
$web->onMessage = function (TcpConnection $connection, Request $request) {
    $_GET = $request->get();
    $path = $request->path();
    if ($path === '/') {
        $connection->send(get_content(WEBROOT . '/index.php'));
        return;
    }
    $file = realpath(WEBROOT . $path);
    if (false === $file) {
        $connection->send(new Response(404, array(), '<h3>404 Not Found</h3>'));
        return;
    }
    # Security check! Very important!!!
    if (strpos($file, WEBROOT) !== 0) {
        $connection->send(new Response(400));
        return;
    }
    if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
        $connection->send(get_content($file));
        return;
    }
    $if_modified_since = $request->header('if-modified-since');
    if (!empty($if_modified_since)) {
        // Check 304.
        $info = stat($file);
        $modified_time = $info ? date('D, d M Y H:i:s', $info['mtime']) . ' ' . date_default_timezone_get() : '';
        if ($modified_time === $if_modified_since) {
            $connection->send(new Response(304));
            return;
        }
    }
    $connection->send((new Response())->withFile($file));
};
Worker::runAll();
