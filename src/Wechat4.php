<?php

namespace r;
class Wechat4
{
    private string $appId;
    private string $appSecret;
    private string $redisKey = 'wechat:access_token';
    private Redis  $redis;

    /**
     * @throws RedisException
     */
    public function __construct($appId, $appSecret)
    {
        $this->appId     = $appId;
        $this->appSecret = $appSecret;
        $this->redis     = new Redis();
        $this->redis->connect('192.168.50.11');
        $this->redis->auth('Rr11rrrd$$');
    }

    /**
     * @throws Exception
     */
    public function accessToken()
    {
        $cache = $this->redis->get($this->redisKey);
        if ($cache) {
            return $cache;
        }
        $u = sprintf("https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=%s&secret=%s", $this->appId, $this->appSecret);
        $r = json_decode(file_get_contents($u), true);
        if (isset($r['access_token'])) {
            $this->redis->set($this->redisKey, $r['access_token'], $r['expires_in']);
            return $r['access_token'];
        }
        throw new Exception('error accessToken ' . ($r['errmsg'] ?? '未知错误'));
    }
}
//
//try {
//    $token = (new Wechat('wxa75ad389d3b5701c', '0eddf2c04fd5e076a718379ebfb50f2f'))->accessToken();
//    echo $token;
//} catch (Throwable $e) {
//    echo $e->getMessage();
//}
