<?php

namespace langdonglei;
use GuzzleHttp\Client;

class Notice
{
    public static function WechatMediaPlatform($openid)
    {
        $app_id     = '';
        $app_secret = '';
        $str        = file_get_contents("https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$app_id&secret=$app_secret");
        $arr        = json_decode($str, true);
        $res        = (new Client())->post("https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=$arr[access_token]", [
            'body' => [
                'touser'      => $openid,
                'template_id' => '',
                'url'         => '',
                'data'        => '',
            ]
        ])->getBody()->getContents();
        return json_decode($res, true);
    }
}