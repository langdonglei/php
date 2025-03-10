<?php
if (version_compare(phpversion(), '5.6.0', '<')) {
    exit('运行需 PHP 5.6以上');
} else {
    echo '1.40' . PHP_EOL;
}
set_time_limit(0);
ignore_user_abort(1);
error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
define('BIG_ENDIAN', pack('L', 1) === pack('N', 1));
$options        = ['authtoken' => 'd6ea6b1ee80135e3'];
$serverArr      = auth($options);
$seraddr        = $serverArr[0];
$port           = $serverArr[1];
$is_verify_peer = false;
$isDebug        = false;
$r              = [];
$w              = [];
$tunnels        = [];
$box            = [];
$client_id      = '';
$start_at       = time();
$ping_at        = 0;
$flag           = true;
while ($flag) {
    array_filter($box);
    sort($box);
    if (empty($main)) {
        $ip = ip($seraddr, $port);
        if (!$ip) {
            echo 'error' . PHP_EOL;
            sleep(1);
            continue;
        }
        $main = connect($ip, $port);
        if (!$main) {
            echo 'error' . PHP_EOL;
            sleep(10);
            continue;
        }
        $box[] = array('sock' => $main, 'linkstate' => 0, 'type' => 1);
    }

    if (php_sapi_name() != 'cli') {
        if ($start_at + 3600 < time()) {
            fclose($main);
            $flag = false;
            break;
        }
    }

    if ($ping_at + 25 < time() && $ping_at != 0) {
        send($main, '{"Type":"Ping","Payload":{}}');
        $ping_at = time();
    }

    //重新赋值
    $r = [];
    $w = [];
    foreach ($box as $index => $item) {
        if (is_resource($item['sock'])) {
            $r[] = $item['sock'];
            if ($item['linkstate'] == 0) {
                $w[] = $item['sock'];
            }
        } else {
            if ($item['type'] == 1) {
                $main = false;
            }
            unset($item['type']);
            unset($item['sock']);
            unset($item['tosock']);
            unset($item['recvbuf']);
            array_splice($box, $index, 1);
        }
    }

    //查询
    $res = stream_select($r, $w, $e, 1);
    if ($res === false) {
        var_dump($e);
    }

    //有事件
    if ($res > 0) {
        foreach ($box as $index => $info) {
            $sock = $info['sock'];
            //可读
            if (in_array($sock, $r)) {
                $receive = fread($sock, 1024);
                if (empty($receive) || strlen($receive) == 0) {
                    //主连接关闭，关闭所有
                    if ($info['type'] == 1) {
                        $main = false;
                    }
                    if ($info['type'] == 3) {
                        fclose($info['tosock']);
                    }
                    unset($info['type']);
                    unset($info['sock']);
                    unset($info['tosock']);
                    unset($info['recvbuf']);
                    unset($box[$index]);
                    continue;
                }
                if (strlen($receive) > 0) {
                    if (!isset($info['recvbuf'])) {
                        $info['recvbuf'] = $receive;
                    } else {
                        $info['recvbuf'] = $info['recvbuf'] . $receive;
                    }
                    $box[$index] = $info;
                }
                //控制连接，或者远程未连接本地连接
                if ($info['type'] == 1 || ($info['type'] == 2 && $info['linkstate'] == 1)) {
                    $allrecvbut = $info['recvbuf'];
                    //处理
                    $lenbuf = substr($allrecvbut, 0, 8);
                    $len    = tolen1($lenbuf);
                    if (strlen($allrecvbut) >= (8 + $len)) {
                        $json = substr($allrecvbut, 8, $len);
                        var_dump($json);
                        $js = json_decode($json, true);
                        //远程主连接
                        if ($info['type'] == 1) {
                            if ($js['Type'] == 'ReqProxy') {
                                $newsock = connect($seraddr, $port);
                                if ($newsock) {
                                    $box[] = array('sock' => $newsock, 'linkstate' => 0, 'type' => 2);
                                }
                            }
                            if ($js['Type'] == 'AuthResp') {
                                if ($js['Payload']['Error'] != null) {
                                    var_dump($js['Payload']['Error']);
                                    sleep(60);
                                    continue;
                                }
                                $client_id = $js['Payload']['ClientId'];
                                $ping_at   = time();
                                send($sock, Ping());
                            }
                            if ($js['Type'] == 'NewTunnel') {
                                $tunnels[$js['Payload']['Url']] = $js['Payload'];
                                var_dump($js['Payload']['Url']);
                            }
                        }
                        //远程代理连接
                        if ($info['type'] == 2) {
                            //未连接本地
                            if ($info['linkstate'] == 1) {
                                if ($js['Type'] == 'StartProxy') {
                                    $loacladdr = getloacladdr($js['Payload']['Url']);
                                    $ip        = ip($loacladdr[0], $loacladdr[1]);
                                    if (!$ip) {
                                        $body   = '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Web服务错误</title><meta name="viewport" content="initial-scale=1,maximum-scale=1,user-scalable=no"><meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"><style>html,body{height:100%%}body{margin:0;padding:0;width:100%%;display:table;font-weight:100;font-family:"Microsoft YaHei",Arial,Helvetica,sans-serif}.container{text-align:center;display:table-cell;vertical-align:middle}.content{border:1px solid #ebccd1;text-align:center;display:inline-block;background-color:#f2dede;color:#a94442;padding:30px}.title{font-size:18px}.copyright{margin-top:30px;text-align:right;color:#000}</style></head><body><div class="container"><div class="content"><div class="title">隧道 %s 无效<br>无法连接到<strong>%s</strong>. 此端口尚未提供Web服务</div><div class="copyright">Powered By natapp.cn</div></div></div></body></html>';
                                        $html   = sprintf($body, $js['Payload']['Url'], $loacladdr[0] . ':' . $loacladdr[1]);
                                        $header = "HTTP/1.0 502 Bad Gateway" . "\r\n";
                                        $header .= "Server: ngrok-php" . "\r\n";
                                        $header .= "Content-Type: text/html" . "\r\n";
                                        $header .= "Content-Length: %d" . "\r\n";
                                        $header .= "\r\n" . "%s";
                                        $buf    = sprintf($header, strlen($html), $html);
                                        send_buf($sock, $buf);
                                    } else {
                                        $newsock = connectlocal($ip, $loacladdr[1]);
                                        if ($newsock) {
                                            $box[] = array('sock' => $newsock, 'linkstate' => 0, 'type' => 3, 'tosock' => $sock);
                                        }
                                        //把本地连接覆盖上去
                                        $info['tosock']    = $newsock;
                                        $info['linkstate'] = 2;
                                    }
                                }
                            }
                        }
                        //edit buffer
                        if (strlen($allrecvbut) == (8 + $len)) {
                            $info['recvbuf'] = '';
                        } else {
                            $info['recvbuf'] = substr($allrecvbut, 8 + $len);
                        }
                        $box[$index] = $info;
                    }
                }
                //远程连接已连接本地跟本地连接，纯转发
                if ($info['type'] == 3 || ($info['type'] == 2 && $info['linkstate'] == 2)) {
                    send_buf($info['tosock'], $info['recvbuf']);
                    $info['recvbuf'] = '';
                    $box[$index]     = $info;
                }
            }
            //可写
            if (in_array($sock, $w)) {
                if ($info['linkstate'] == 0) {
                    if ($info['type'] == 1) {
                        send($sock, NgrokAuth($options), false);
                        $info['linkstate'] = 1;
                        $box[$index]       = $info;
                    }
                    if ($info['type'] == 2) {
                        send($sock, RegProxy($client_id), false);
                        $info['linkstate'] = 1;
                        $box[$index]       = $info;
                    }
                    if ($info['type'] == 3) {
                        $info['linkstate'] = 1;
                        $box[$index]       = $info;
                    }
                }
            }
        }
    }
}

function ip($address, $port)
{
    $ip = gethostbyname($address);
    if (!filter_var($ip, FILTER_VALIDATE_IP)) {
        var_dump($ip);
        return false;
    }
    if (empty(fsockopen($ip, $port, $code, $error, 3))) {
        var_dump($code, $error);
        return false;
    }
    return $ip;
}

/* 连接到本地 */
function connectlocal($localaddr, $localport)
{
    $socket = stream_socket_client('tcp://' . $localaddr . ':' . $localport, $errno, $errstr, 30);
    if (!$socket) {
        return false;
    }
    stream_set_blocking($socket, 0); //设置为非阻塞模式
    return $socket;
}

function getloacladdr($url)
{
    global $tunnels;
    $proto = explode(':', $tunnels[$url]['LocalAddr']);
    return $proto;
}

function NgrokAuth($token)
{
    $Payload = array(
        'ClientId'    => '',
        'OS'          => 'php',
        'Arch'        => 'amd64',
        'Version'     => '4',
        'MmVersion'   => '2.1',
        'User'        => 'user',
        'Password'    => '',
        'AuthToken'   => $token['authtoken'],
        'ClientToken' => $token['clienttoken'],
    );
    $json    = array(
        'Type'    => 'Auth',
        'Payload' => $Payload,
    );

    return json_encode($json);
}

function RegProxy($ClientId)
{
    $Payload = array('ClientId' => $ClientId);
    $json    = array(
        'Type'    => 'RegProxy',
        'Payload' => $Payload,
    );
    return json_encode($json);
}

function Ping()
{
    $Payload = (object)[];
    $str     = json_encode([
        'Type'    => 'Ping',
        'Payload' => (object)[],
    ]);
    var_dump($str);
    return $str;
}

function byte($len): string
{
    // 机器字节序 小端 只支持整型范围
    return pack("L", $len) . pack("C4", 0, 0, 0, 0);
}

function send($sock, $msg, $block_is = true)
{
    if ($block_is) {
        stream_set_blocking($sock, 1);
    }
    fwrite($sock, byte(strlen($msg)) . $msg);
    if ($block_is) {
        stream_set_blocking($sock, 0);
    }
}

function send_buf($sock, $buf, $block_is = true)
{
    if ($block_is) {
        stream_set_blocking($sock, 1);
    }
    fwrite($sock, $buf);
    if ($block_is) {
        stream_set_blocking($sock, 0);
    }
}

/* 网络字节序 （只支持整型范围） */
function tolen($v)
{
    $array = unpack("N", $v);
    return $array[1];
}

/* 机器字节序 （小端） 只支持整型范围 */
function tolen1($v)
{
    $array = unpack("L", $v);
    return $array[1];
}

function ConsoleOut($log, $level = 'info')
{
    global $isDebug;
    if ($level == 'debug' and $isDebug == false) {
        return;
    }
    if (php_sapi_name() === 'cli') {
        if (DIRECTORY_SEPARATOR == "\\") {
            // $log = iconv('UTF-8', 'GB18030', $log);
        }

        echo $log . "\r\n";
    } else {
        echo $log . "<br/>";
        ob_flush();
        flush();
    }
}

function auth($token)
{
    $host = 'auth.natapp.cn';
    $fp   = stream_socket_client("tcp://$host:443", $code, $error, 4);
    if (empty($fp)) {
        var_dump($code, $error);
        exit('认证失败');
    }
    stream_context_set_option($fp, 'ssl', 'verify_host', false);
    stream_context_set_option($fp, 'ssl', 'verify_peer_name', false);
    stream_context_set_option($fp, 'ssl', 'verify_peer', false);
    stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT);
    $payload = json_encode(['Authtoken' => $token['authtoken'], 'Clienttoken' => $token['clienttoken'], 'Token' => 'fffeephptokenkhd672']);
    $header  = "POST " . "/auth" . " HTTP/1.1" . "\r\n";
    $header  .= "Content-Type: text/html" . "\r\n";
    $header  .= "Host: %s" . "\r\n";
    $header  .= "Content-Length: %d" . "\r\n";
    $header  .= "\r\n" . "%s";
    $buf     = sprintf($header, $host, strlen($payload), $payload);
    var_dump($buf);
    fputs($fp, $buf);
    $body = null;
    while (!feof($fp)) {
        $line = fgets($fp, 1024); //去除请求包的头只显示页面的返回数据
        if ($line == "\n" || $line == "\r\n") {
            $body = fread($fp, 4096);
            break;
        }
    }
    fclose($fp);
    $pattern = '|"ServerAddr"\s*:\s*"(?<vv>.*?)"|';
    preg_match($pattern, $body, $matches);
    $r = $matches['vv'];
    if (empty($r)) {
        exit('认证错误');
    }
    return explode(':', $r);
}

function connect($ip, $port)
{
    $r = stream_socket_client("tcp://$ip:$port", $errno, $errstr, 30);
    if (empty($r)) {
        exit('error');
    }
    stream_context_set_option($r, 'ssl', 'verify_host', false);
    stream_context_set_option($r, 'ssl', 'verify_peer_name', false);
    stream_context_set_option($r, 'ssl', 'verify_peer', false);
    stream_socket_enable_crypto($r, true, STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT);
    stream_set_blocking($r, 0);
    register_shutdown_function(function ($r) {
        send($r, 'close');
        fclose($r);
    }, $r);
    return $r;
}