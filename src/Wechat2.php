<?php

namespace langdonglei\util;

use GuzzleHttp\Client;
use think\Request;

class Wechat2
{
    /**
     * 付款
     */
    public static function pay($app_id, $mch_id, $key_v2, $notify_url, $trade_type, $out_trade_no, $good_name, $total_fee, $openid_if_jsapi = ''): array
    {
        $params = [
            'appid'            => $app_id,
            'mch_id'           => $mch_id,
            'nonce_str'        => time(),
            'body'             => $good_name,
            'out_trade_no'     => $out_trade_no,
            'total_fee'        => $total_fee,
            'spbill_create_ip' => Request::instance()->ip(),
            'notify_url'       => $notify_url,
            'trade_type'       => $trade_type,
        ];
        if ($trade_type == 'JSAPI') {
            $params['openid'] = $openid_if_jsapi;
        }
        $params['sign'] = self::sign($params, $key_v2);
        $xml            = (new Client())->post('https://api.mch.weixin.qq.com/pay/unifiedorder', [
            'body' => self::toXml($params),
        ])->getBody()->getContents();
        return self::fromXml($xml);
    }

    /**
     * 退款
     */
    public static function refund($app_id, $mch_id, $key_v2, $cert_path, $cer_key_path, $out_trade_no, $refund_fee): array
    {
        $params         = [
            'mch_id'        => $mch_id,
            'appid'         => $app_id,
            'out_trade_no'  => $out_trade_no,
            'out_refund_no' => time(),
            'nonce_str'     => time(),
            'total_fee'     => $refund_fee,
            'refund_fee'    => $refund_fee,
        ];
        $params['sign'] = self::sign($params, $key_v2);
        $xml            = (new Client())->post('https://api.mch.weixin.qq.com/secapi/pay/refund', [
            'cert'    => $cert_path,
            'ssl_key' => $cer_key_path,
            'body'    => self::toXml($params),
        ])->getBody()->getContents();
        return self::fromXml($xml);
    }

    /**
     * 查询订单
     */
    public static function query($app_id, $mch_id, $key_v2, $out_trade_no): array
    {
        $params         = [
            'nonce_str'    => time(),
            'appid'        => $app_id,
            'mch_id'       => $mch_id,
            'out_trade_no' => $out_trade_no,
        ];
        $params['sign'] = self::sign($params, $key_v2);
        $xml            = (new Client())->post('https://api.mch.weixin.qq.com/pay/orderquery', [
            'body' => self::toXml($params),
        ])->getBody()->getContents();
        return self::fromXml($xml);
    }

    public static function sign($arr, $key): string
    {
        ksort($arr);
        $str = '';
        foreach ($arr as $k => $v) {
            $str .= $k . "=" . $v . "&";
        }
        $str .= "key=$key";
        return md5($str);
    }

    public static function toXml($data): string
    {
        $xml = '<xml>';
        foreach ($data as $key => $val) {
            $xml .= is_numeric($val) ? '<' . $key . '>' . $val . '</' . $key . '>' : '<' . $key . '><![CDATA[' . $val . ']]></' . $key . '>';
        }
        return $xml . '</xml>';
    }

    public static function fromXml($xml): array
    {
        libxml_disable_entity_loader();
        return json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA), JSON_UNESCAPED_UNICODE), true);
    }
}
