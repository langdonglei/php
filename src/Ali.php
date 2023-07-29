<?php

namespace langdonglei;

use AlibabaCloud\Client\AlibabaCloud;
use app\api\controller\BC;
use app\api\service\EnvS;
use GuzzleHttp\Client;
use OSS\OssClient;
use think\facade\Cache;

class Ali
{
    /**
     * composer require alibabacloud/sdk
     */
    public function textToSound()
    {
        $secret         = input('secret');
        $secret_confirm = EnvS::get('audio_secret');
        if ($secret !== $secret_confirm) {
            BC::n('');
        }
        $access_key      = EnvS::get('ali_access_key');
        $access_secret   = EnvS::get('ali_access_secret');
        $endpoint        = EnvS::get('ali_endpoint');
        $bucket          = EnvS::get('ali_bucket');
        $stream_key      = EnvS::get('ali_stream_key');
        $text            = input('text');
        $cache_is        = input('cache_is', 1);
        $text_md5        = md5($text);
        $ext             = 'wav';
        $name_save       = 'audio/' . $text_md5 . '.' . $ext;
        $cache           = Cache::instance('file');
        $cache_key_url   = 'audio_' . $text_md5;
        $cache_key_token = 'ali_token';
        $url             = $cache->get($cache_key_url);
        if ($cache_is == 0 || empty($url)) {
            $token = $cache->get($cache_key_token);
            if (empty($token)) {
                AlibabaCloud::accessKeyClient($access_key, $access_secret)->regionId("cn-shanghai")->asDefaultClient();
                $res         = AlibabaCloud::nlsCloudMeta()->v20180518()->createToken()->request();
                $token_info  = $res['Token'] ?? '';
                $token       = $token_info['Id'];
                $expire_time = $token_info['ExpireTime'];
                $cache->set($cache_key_token, $token, $expire_time - time() - 60);
            }
            $client = new Client();
            $str    = $client->post('https://nls-gateway-cn-shanghai.aliyuncs.com/stream/v1/tts', [
                'verify' => false,
                'json'   => [
                    'appkey'      => $stream_key,
                    'token'       => $token,
                    'format'      => $ext,
                    'text'        => $text,
                    'sample_rate' => '16000'
                ]
            ])->getBody()->getContents();
            $oss    = new OssClient($access_key, $access_secret, $endpoint);
            $url    = $oss->putObject($bucket, $name_save, $str)['info']['url'];
            $cache->set($cache_key_url, $url);
        }
        BC::y($url);
    }
}
