<?php

namespace langdonglei;

use Exception;
use GuzzleHttp\Client;
use think\Env;
use Yansongda\Pay\Pay;
use Yansongda\Supports\Collection;

class WeChat
{
    protected $mini_app_id;
    protected $mini_app_secret;
    /**
     * @var mixed
     */
    protected      $access_token;
    private Client $client;

    /**
     * @throws Exception
     */
    public function __construct()
    {
        $mini_app_id = Env::get('wechat.mini_app_id');
        if (!$mini_app_id) {
            throw new Exception('未配置环境变量 wechat.mini_app_id');
        }
        $this->mini_app_id = $mini_app_id;

        $mini_app_secret = Env::get('wechat.mini_app_secret');
        if (!$mini_app_secret) {
            throw new Exception('未配置环境变量 wechat.mini_app_secret');
        }
        $this->mini_app_secret = $mini_app_secret;
        $this->client          = new Client();
        $str                   = $this->client->post("https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$this->mini_app_id&secret=$this->mini_app_secret")->getBody()->getContents();
        $arr                   = json_decode($str, true);
        $this->access_token    = $arr['access_token'];
    }


    /**
     * @throws Exception
     */
    public function getUnlimitedQRCode($scene, $return_content = false): string
    {
        if (!$scene) {
            throw new Exception('scene 不能为空');
        }
        $content = $this->client->post("https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=$this->access_token", [
            'json' => [
                'scene' => $scene,
            ]
        ])->getBody()->getContents();
        if ($return_content) {
            return $content;
        } else {
            $path = File::getSaveName('qrcode');
            file_put_contents($path, $content);
            return $path;
        }
    }

    public function generateShortLink($page_url = 'pages/index/index'): string
    {
        $str = $this->client->post("https://api.weixin.qq.com/wxa/genwxashortlink?access_token=$this->access_token", [
            'json' => [
                'page_url' => $page_url,
            ]
        ])->getBody()->getContents();
        $arr = json_decode($str, true);
        return $arr['link'];
    }

    public static function refundByYan($sn, $money)
    {
        try {
            Pay::wechat([
                'miniapp_id'  => Env::get('wechat.mini_app_id'),
                'mch_id'      => Env::get('wechat.mini_app_mch_id'),
                'key'         => Env::get('wechat.mini_app_mch_v2'),
                'cert_client' => ROOT_PATH . '/cerificate/apiclient_cert.pem',
                'cert_key'    => ROOT_PATH . '/cerificate/apiclient_key.pem',
                'http'        => [
                    'timeout'         => 0,
                    'connect_timeout' => 0,
                    'http_errors'     => false,
                    'verify'          => false
                ]
            ])->refund([
                'type'          => 'miniapp',
                'out_trade_no'  => $sn,
                'out_refund_no' => time(),
                'total_fee'     => $money * 100,
                'refund_fee'    => $money * 100,
            ]);
        } catch (Throwable $e) {
            preg_match('/(?P<message>[一-龟].*)/u', $e->getMessage(), $matches);
            $message = $matches['message'];
        }
        return $message ?? 'ok';
    }

    public static function miniappByYan($callback, $yuan, $sn, $openid, $memo = ''): Collection
    {
        return Pay::wechat([
            'miniapp_id' => Env::get('wechat.mini_app_id'),
            'mch_id'     => Env::get('wechat.mini_app_mch_id'),
            'key'        => Env::get('wechat.mini_app_mch_v2'),
            'notify_url' => $callback,
        ])->miniapp([
            'out_trade_no' => $sn,
            'body'         => $memo,
            'total_fee'    => $yuan * 100,
            'openid'       => $openid,
        ]);
    }
}