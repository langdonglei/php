<?php

namespace langdonglei;

use GuzzleHttp\Client;

class WeChat
{
    public static function getAccessCode($appid,$secret): string
    {
        $api    = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$appid&secret=$secret";
        $str    = (new Client())->request('post', $api)->getBody()->getContents();
        $arr    = json_decode($str, true);
        return $arr['access_token'];
    }

    public static function getUnlimitedQRCode($access_token, $scene = ''): string
    {
        $api = "https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=$access_token";
        return (new Client())->request('post', $api, [
            'json' => [
                'scene' => $scene,
            ]
        ])->getBody()->getContents();
    }

    public static function generateShortLink($access_token, $page_url = 'pages/index/index'): string
    {
        $api = "https://api.weixin.qq.com/wxa/genwxashortlink?access_token=$access_token";
        $str = (new Client())->request('post', $api, [
            'json' => [
                'page_url' => $page_url,
            ]
        ])->getBody()->getContents();
        $arr = json_decode($str, true);
        return $arr['link'];
    }
}