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
    protected $access_token;
    protected $client;

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
            $r = File::generateFileName('qrcode', '.png');
            file_put_contents($r, $content);
            return $r;
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

    public static function refundByYan($sn, $money): string
    {
        try {
            Pay::wechat([
                'miniapp_id'  => Env::get('wechat.mini_app_id'),
                'mch_id'      => Env::get('wechat.mini_app_mch_id'),
                'key'         => Env::get('wechat.mini_app_mch_v2'),
                'cert_client' => APP_PATH . '/certificate/apiclient_cert.pem',
                'cert_key'    => APP_PATH . '/certificate/apiclient_key.pem',
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

    public static function miniappByYan($openid, $memo, $yuan, $sn, $callback): Collection
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

    public static function verify()
    {
        return Pay::wechat([
            'miniapp_id' => Env::get('wechat.mini_app_id'),
            'mch_id'     => Env::get('wechat.mini_app_mch_id'),
            'key'        => Env::get('wechat.mini_app_mch_v2'),
        ])->verify();
    }


    public static function success(): void
    {
        Pay::wechat([
            'miniapp_id' => Env::get('wechat.mini_app_id'),
            'mch_id'     => Env::get('wechat.mini_app_mch_id'),
            'key'        => Env::get('wechat.mini_app_mch_v2'),
        ])->success()->send();
    }

    public static function mp_submit($token)
    {
        $arr = [$token, $_GET["timestamp"], $_GET["nonce"]];
        sort($arr, SORT_STRING);
        if ($_GET["signature"] == sha1(implode($arr))) {
            echo $_GET["echostr"];
        }
    }

    public static function mp_js_sdk($url, $app_id, $app_secret): array
    {
        $js_api_list = [
            'updateAppMessageShareData',
            'updateTimelineShareData',
            'onMenuShareTimeline', // 即将废弃
            'onMenuShareAppMessage', // 即将废弃
            'onMenuShareQQ', // 即将废弃
            'onMenuShareWeibo',
            'onMenuShareQZone',
            'startRecord',
            'stopRecord',
            'onVoiceRecordEnd',
            'playVoice',
            'pauseVoice',
            'stopVoice',
            'onVoicePlayEnd',
            'uploadVoice',
            'downloadVoice',
            'chooseImage',
            'previewImage',
            'uploadImage',
            'downloadImage',
            'translateVoice',
            'getNetworkType',
            'openLocation',
            'getLocation',
            'hideOptionMenu',
            'showOptionMenu',
            'hideMenuItems',
            'showMenuItems',
            'hideAllNonBaseMenuItem',
            'showAllNonBaseMenuItem',
            'closeWindow',
            'scanQRCode',
            'openProductSpecificView',
            'addCard',
            'chooseCard',
            'openCard'
        ];
        $timestamp   = time();
        $nonceStr    = substr(md5($timestamp), 0, 15);
        $token       = file_get_contents("https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$app_id&secret=$app_secret");
        $token       = json_decode($token, true)['access_token'];
        $ticket      = file_get_contents("https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=$token&type=jsapi");
        $ticket      = json_decode($ticket, true)['ticket'];
        $signature   = sha1("jsapi_ticket=$ticket&noncestr=$nonceStr&timestamp=$timestamp&url=$url");
        return [
            'appId'     => $app_id,
            'timestamp' => $timestamp,
            'nonceStr'  => $nonceStr,
            'signature' => $signature,
            'jsApiList' => $js_api_list
        ];
    }

    public static function mp_grant($app_id, $app_secret)
    {
        // <a href="https://open.weixin.qq.com/connect/oauth2/authorize?appid=wx726f762a9fbcdaeb&redirect_uri=http://gv2wjv.natappfree.cc/code.php&response_type=code&scope=snsapi_userinfo&state=1#wechat_redirect">授权登录</a>
        $code = $_GET['code'];
        $r    = file_get_contents("https://api.weixin.qq.com/sns/oauth2/access_token?appid=$app_id&secret=$app_secret&code=$code&grant_type=authorization_code");
        $r    = json_decode($r, true);
        $s    = file_get_contents("https://api.weixin.qq.com/sns/userinfo?access_token=$r[access_token]&openid=$r[openid]&lang=zh_CN");
        return json_decode($s, true);
    }
}