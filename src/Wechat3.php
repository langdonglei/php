<?php

namespace langdonglei;

use Exception;
use WeChatPay\Builder;
use WeChatPay\BuilderChainable;
use WeChatPay\Crypto\Rsa;
use WeChatPay\Util\PemUtil;

class Wechat3
{
    const CERTIFICATE_TMP = ROOT_PATH . 'certificate/tmp.pem';
    const CERTIFICATE_APP = ROOT_PATH . 'certificate/cert.pem';
    const CERTIFICATE_KEY = ROOT_PATH . 'certificate/cert_key.pem';
    /**
     * @var BuilderChainable
     */
    private $instance;

    /**
     * @throws Exception
     */
    public function __construct()
    {
        if (!defined('ROOT_PATH')) {
            throw new Exception('缺少环境变量 ROOT_PATH');
        }
        if (!is_file(self::CERTIFICATE_TMP)) {
            # composer require wechatpay/wechatpay & bin/CertificateDownloader.php
            throw new Exception('没有发现证书 ' . self::CERTIFICATE_TMP . ' 如果是第一次,请手动下载平台证书(非app证书)');
        }
        $env            = function ($field) {
            $v = \think\Env::get($field);
            if (empty($v)) {
                throw new Exception("缺少环境变量 $field");
            }
            return $v;
        };
        $this->app_id   = $env('wechat_mini_app_id');
        $this->mch_id   = $env('wechat_mini_app_mch_id');
        $this->mch_v3   = $env('wechat_mini_app_mch_v3');

        # composer require wechatpay/wechatpay
        $this->instance = Builder::factory([
            'mchid'      => $this->mch_id,
            # 签名证书私钥
            'privateKey' => Rsa::from('file://' . self::CERTIFICATE_KEY, Rsa::KEY_TYPE_PRIVATE),
            # 与签名证书私钥匹配的证书公钥编号
            'serial'     => PemUtil::parseCertificateSerialNo('file://' . self::CERTIFICATE_APP),
            'certs'      => [
                # 平台公钥证书及编号 第一次手动获取 有效期7天 且 需要动态更新
                PemUtil::parseCertificateSerialNo('file://' . self::CERTIFICATE_TMP) => Rsa::from('file://' . self::CERTIFICATE_TMP, Rsa::KEY_TYPE_PUBLIC),
            ],
        ]);
    }

    /**
     * @throws Exception
     */
    public function certificate()
    {
        $str = $this->instance->chain('v3/certificates')->get()->getBody()->getContents();
        $arr = json_decode($str, true);

        $associate_data = $arr['data'][0]['encrypt_certificate']['associated_data'] ?? '';
        $nonce_str      = $arr['data'][0]['encrypt_certificate']['nonce'] ?? '';
        $ciphertext     = $arr['data'][0]['encrypt_certificate']['ciphertext'] ?? '';
        if (!$ciphertext) {
            throw new \Exception('证书请求失败');
        }

        $str = $this->decrypt($associate_data, $nonce_str, $ciphertext);
        file_put_contents(self::CERTIFICATE_TMP, $str);
    }

    private function decrypt($associatedData, $nonceStr, $ciphertext)
    {
        $key                  = $this->mch_v3;
        $auth_tag_length_byte = 16;

        if (strlen($key) != 32) {
            throw new Exception('无效的ApiV3Key，长度应为32个字节');
        }

        $ciphertext = base64_decode($ciphertext);
        if (strlen($ciphertext) <= $auth_tag_length_byte) {
            throw new Exception('无效的ApiV3Key，长度应为大于16个字节');
        }

        // ext-sodium (default installed on >= PHP 7.2)
        if (function_exists('\sodium_crypto_aead_aes256gcm_is_available') && \sodium_crypto_aead_aes256gcm_is_available()) {
            return \sodium_crypto_aead_aes256gcm_decrypt($ciphertext, $associatedData, $nonceStr, $key);
        }

        // ext-libsodium (need install libsodium-php 1.x via pecl)
        if (function_exists('\Sodium\crypto_aead_aes256gcm_is_available') && \Sodium\crypto_aead_aes256gcm_is_available()) {
            return \Sodium\crypto_aead_aes256gcm_decrypt($ciphertext, $associatedData, $nonceStr, $key);
        }

        // openssl (PHP >= 7.1 support AEAD)
        if (PHP_VERSION_ID >= 70100 && in_array('aes-256-gcm', \openssl_get_cipher_methods())) {
            $ctext   = substr($ciphertext, 0, -$auth_tag_length_byte);
            $authTag = substr($ciphertext, -$auth_tag_length_byte);

            return \openssl_decrypt($ctext, 'aes-256-gcm', $key, \OPENSSL_RAW_DATA, $nonceStr,
                $authTag, $associatedData);
        }

        throw new Exception('AEAD_AES_256_GCM需要PHP 7.1以上或者安装libsodium-php');
    }

    public function transfer($open_id, int $fen, $desc = '佣金提现')
    {
        return json_decode($this->instance->chain('v3/transfer/batches')->post([
            'headers' => [
                'Wechatpay-Serial' => PemUtil::parseCertificateSerialNo('file://' . self::CERTIFICATE_TMP),
            ],
            'json'    => [
                "appid"                => $this->app_id,
                "out_batch_no"         => (string)time(),
                "batch_name"           => $desc,
                "batch_remark"         => $desc,
                "total_amount"         => $fen,
                "total_num"            => 1,
                "transfer_detail_list" => [
                    [
                        "out_detail_no"   => (string)time(),
                        "transfer_amount" => $fen,
                        "transfer_remark" => $desc,
                        "openid"          => $open_id,
                    ]
                ]
            ]
        ])->getBody()->getContents(), true);
    }
}