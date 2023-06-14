<?php

namespace langdonglei;

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

    public static function sign($arr, $key): Str
    {
        ksort($arr);
        $str = '';
        foreach ($arr as $k => $v) {
            $str .= $k . "=" . $v . "&";
        }
        $str .= "key=$key";
        return md5($str);

        //所有请求参数按照字母先后顺序排
        ksort($arr);
        //定义字符串开始所包括的字符串
        $stringToBeSigned = '';
        //把所有参数名和参数值串在一起
        foreach ($params as $k => $v) {
            $stringToBeSigned .= urldecode($k . $v);
        }
        unset($k, $v);
        //定义字符串结尾所包括的字符串
        $stringToBeSigned .= '&key=rvxazd4qghgYjnkqihu1mdkiSizgkslx';
        //使用MD5进行加密，再转化成大写
        return strtoupper(md5($stringToBeSigned));
    }

    public static function toXml($data): Str
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

    public function transfer($data)
    {
        header("Content-type: text/html; charset=utf-8");
        //CA证书及支付信息
        $config             = get_addon_config('wxjssdk');
        $wxchat['appid']    = $config['appid'];
        $wxchat['mchid']    = $config['mchid']; //商户号
        $wxchat['api_cert'] = __DIR__ . '/weixin/apiclient_cert.pem';
        $wxchat['api_key']  = __DIR__ . '/weixin/apiclient_key.pem';

        $webdata = array(
            'mch_appid'        => $wxchat['appid'],
            'mchid'            => $wxchat['mchid'],
            'nonce_str'        => md5(time()),
            //商户订单号，需要唯一
            'partner_trade_no' => $data['pay_code'],
            //转账用户的openid
            'openid'           => $data['openid'],
            ////OPTION_CHECK不强制校验真实姓名, FORCE_CHECK：强制 NO_CHECK：
            'check_name'       => 'NO_CHECK',
            //付款金额单位为分
            'amount'           => $data['money'] * 100,
            'desc'             => $data['desc'],
            'spbill_create_ip' => $_SERVER['SERVER_ADDR'],
        );

        foreach ($webdata as $k => $v) {
            $tarr[] = $k . '=' . $v;
        }

        sort($tarr);
        $sign            = implode($tarr, '&');
        $sign            .= '&key=rvxazd4qghgYjnkqihu1mdkiSizgkslx';
        $webdata['sign'] = strtoupper(md5($sign));

        $wget = $this->array2xml($webdata);

        $pay_url = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers';

        $res = $this->http_post($pay_url, $wget, $wxchat);

        if (!$res) {
            return array('status' => 1, 'msg' => "Can't connect the server");
        }

        $content = simplexml_load_string($res, 'SimpleXMLElement', LIBXML_NOCDATA);

        if (strval($content->return_code) == 'FAIL') {
            return array('status' => 1, 'msg' => strval($content->return_msg));
        }
        if (strval($content->result_code) == 'FAIL') {
            return array('status' => 1, 'msg' => strval($content->err_code), ':' . strval($content->err_code_des));
        }

        $rdata = array(
            'status'           => 0,
            'mch_appid'        => strval($content->mch_appid),
            'mchid'            => strval($content->mchid),
            'device_info'      => strval($content->device_info),
            'nonce_str'        => strval($content->nonce_str),
            'result_code'      => strval($content->result_code),
            'partner_trade_no' => strval($content->partner_trade_no),
            'payment_no'       => strval($content->payment_no),
            'payment_time'     => strval($content->payment_time),
        );

        return $rdata;
    }
}
