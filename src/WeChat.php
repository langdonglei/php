<?php

namespace langdonglei;

use Exception;
use GuzzleHttp\Client;
use think\Env;

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

    public function getUnlimitedQRCode($scene, $return_content = false): string
    {
        if($scene){
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
}