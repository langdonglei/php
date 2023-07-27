<?php

namespace langdonglei;

use Exception;
use think\Env;
use Throwable;
use Yansongda\Pay\Pay;

class Alipay
{
    /**
     * @throws Exception
     */
    public static function transferByYan($money, $alipay_account, $alipay_name, $out_biz_no)
    {
        try {
            $r = Pay::alipay([
                'app_id'              => Env::get('alipay.app_id'),
                'sign_type'           => 'RSA2',
                'private_key'         => Env::get('alipay.private_key'),
                'ali_public_key'      => APP_PATH . Env::get('alipay.ali_public_key'),
                'alipay_root_cert'    => APP_PATH . Env::get('alipay.alipay_root_cert'),
                'app_cert_public_key' => APP_PATH . Env::get('alipay.app_cert_public_key'),
                'http'                => [
                    'timeout'         => 15,
                    'connect_timeout' => 15,
                ]
            ])->transfer([
                'out_biz_no'   => $out_biz_no,
                'product_code' => 'TRANS_ACCOUNT_NO_PWD',
                'trans_amount' => $money,
                'biz_scene'    => 'DIRECT_TRANSFER',
                'remark'       => '支付宝提现',
                'payee_info'   => [
                    'identity'      => $alipay_account,
                    'identity_type' => 'ALIPAY_LOGON_ID',
                    'name'          => $alipay_name
                ]
            ]);
        } catch (Throwable $e) {
            $msg = $e->getMessage();
            if ($msg == 'ERROR_GATEWAY: Get Alipay API Error:Business Failed - PAYEE_NOT_EXIST') {
                throw new Exception('收款账户不存在');
            } else {
                throw new Exception('打款错误');
            }
        }
        return $r;
    }
}