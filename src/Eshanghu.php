<?php

/*
 * This file is part of the eshanghu-pay.
 *
 * (c) XiaoTeng <616896861@qq.com>
 */

namespace Eshanghu;

use GuzzleHttp\Client;
use Eshanghu\Exceptions\EshanghuException;
use Eshanghu\Exceptions\SignErrorException;
use Eshanghu\Exceptions\HttpRequestErrorException;

class Eshanghu
{
    const WECHAT_NATIVE_URL = 'https://1shanghu.com/api/v2/wechat/native';
    const QUERY_URL = 'https://1shanghu.com/api/query';
    const WECHAT_JSAPI_URL = 'https://1shanghu.com/api/v2/wechat/mp';
    const WECHAT_MINI_URL = 'https://api.1shanghu.com/api/v2/wechat/mini';
    const CLOSE_URL = 'https://api.1shanghu.com/api/close';
    const REFUND_URL = 'https://api.1shanghu.com/api/v2/wechat/refund';

    public $appKey;
    public $appSecret;
    public $subMchId;
    public $notify;
    public $client;

    public function __construct(array $config)
    {
        $this->appKey = $config['app_key'];
        $this->appSecret = $config['app_secret'];
        $this->subMchId = $config['sub_mch_id'];
        $this->notify = $config['notify'];
        $timeout = isset($config['timeout']) ? $config['timeout'] : 10.0;

        $this->client = new Client([
            'timeout' => $timeout,
            'verify' => false,
        ]);
    }

    public function native($outTradeNo, $subject, $totalFee, $extra = '')
    {
        $data = [
            'app_key' => $this->appKey,
            'out_trade_no' => $outTradeNo,
            'total_fee' => $totalFee,
            'subject' => $subject,
            'extra' => $extra,
            'notify_url' => $this->notify,
            'sub_mch_id' => $this->subMchId,
        ];
        $data['sign'] = Signer::getSign($data, $this->appSecret);

        return $this->request(self::WECHAT_NATIVE_URL, $data);
    }

    /**
     * 微信JSAPI支付.
     *
     * @param string $outTradeNo
     * @param string $subject
     * @param int    $totalFee
     * @param string $openid
     * @param string $extra
     */
    public function mp($outTradeNo, $subject, $totalFee, $openid, $extra = '')
    {
        $data = [
            'app_key' => $this->appKey,
            'openid' => $openid,
            'out_trade_no' => $outTradeNo,
            'total_fee' => $totalFee,
            'subject' => $subject,
            'extra' => $extra,
            'notify_url' => $this->notify,
            'sub_mch_id' => $this->subMchId,
        ];
        $data['sign'] = Signer::getSign($data, $this->appSecret);

        return $this->request(self::WECHAT_JSAPI_URL, $data);
    }

    /**
     * 微信小程序支付.
     *
     * @param string $outTradeNo
     * @param string $subject
     * @param int    $totalFee
     * @param string $app_id
     * @param string $openid
     * @param string $extra
     */
    public function mini($outTradeNo, $subject, $totalFee, $app_id, $openid, $extra = '')
    {
        $data = [
            'app_key' => $this->appKey,
            'sub_openid' => $openid,
            'sub_app_id' => $app_id,
            'out_trade_no' => $outTradeNo,
            'total_fee' => $totalFee,
            'subject' => $subject,
            'extra' => $extra,
            'notify_url' => $this->notify,
            'sub_mch_id' => $this->subMchId,
        ];
        $data['sign'] = Signer::getSign($data, $this->appSecret);

        return $this->request(self::WECHAT_MINI_URL, $data);
    }

    /**
     * openid获取的url.
     *
     * @param string $callbackUrl
     * @param bool $snsapi_userinfo
     */
    public function getOpenidUrl(string $callbackUrl,$snsapi_userinfo = true)
    {
        if($snsapi_userinfo){
            $url = sprintf('https://1shanghu.com/v2/wechat/login?app_key=%s&sub_mch_id=%s&snsapi_userinfo=true&callback=%s', $this->appKey, $this->subMchId,$callbackUrl);
        }else{
            $url = sprintf('https://1shanghu.com/v2/wechat/login?app_key=%s&sub_mch_id=%s&snsapi_userinfo=false&callback=%s', $this->appKey, $this->subMchId,$callbackUrl);
        }
        return $url;
    }

    /**
     * 回调验证
     *
     * @param array $data
     *
     * @return array
     *
     * @throws SignErrorException
     */
    public function callback(array $data)
    {
        if (! Signer::verify($data, $data['sign'], $this->appSecret)) {
            throw new SignErrorException('签名校验失败');
        }

        return $data;
    }

    /**
     * 使用OrderSn进行查询.
     *
     * @param $orderSn
     *
     * @return mixed
     *
     * @throws EshanghuException
     * @throws HttpRequestErrorException
     */
    public function queryUseOrderSn($orderSn)
    {
        $data = [
            'app_key' => $this->appKey,
            'order_sn' => $orderSn,
        ];

        return $this->query($data);
    }

    /**
     * 使用OutTradeNo进行查询.
     *
     * @param $outTradeNo
     *
     * @return mixed
     *
     * @throws EshanghuException
     * @throws HttpRequestErrorException
     */
    public function queryUseOutTradeNo($outTradeNo)
    {
        $data = [
            'app_key' => $this->appKey,
            'out_trade_no' => $outTradeNo,
        ];

        return $this->query($data);
    }

    /**
     * 订单查询.
     *
     * @param array $data
     *
     * @return mixed
     *
     * @throws EshanghuException
     * @throws HttpRequestErrorException
     */
    public function query(array $data)
    {
        $data['sign'] = Signer::getSign($data, $this->appSecret);

        return $this->request(self::QUERY_URL, $data);
    }

    /**
     * 使用OrderSn进行查询.
     *
     * @param $orderSn
     *
     * @return mixed
     *
     * @throws EshanghuException
     * @throws HttpRequestErrorException
     */
    public function closeUseOrderSn($orderSn)
    {
        $data = [
            'app_key' => $this->appKey,
            'order_sn' => $orderSn,
        ];

        return $this->close($data);
    }

    /**
     * 使用OutTradeNo进行关闭.
     *
     * @param $outTradeNo
     *
     * @return mixed
     *
     * @throws EshanghuException
     * @throws HttpRequestErrorException
     */
    public function closeUseOutTradeNo($outTradeNo)
    {
        $data = [
            'app_key' => $this->appKey,
            'out_trade_no' => $outTradeNo,
        ];
        return $this->close($data);
    }

    /**
     * 关闭订单.
     *
     * @param array $data
     *
     * @return mixed
     *
     * @throws EshanghuException
     * @throws HttpRequestErrorException
     */
    public function close(array $data)
    {
        $data['sign'] = Signer::getSign($data, $this->appSecret);

        return $this->request(self::CLOSE_URL, $data);
    }

    /**
     * 使用OrderSn进行查询.
     *
     * @param $orderSn
     * @param $refund_fee
     * @return mixed
     *
     * @throws EshanghuException
     * @throws HttpRequestErrorException
     */
    public function refundUseOrderSn($orderSn,$refund_fee)
    {
        $data = [
            'app_key' => $this->appKey,
            'sub_mch_id' => $this->subMchId,
            'refund_fee' => $refund_fee,
            'order_sn' => $orderSn,
        ];

        return $this->refund($data);
    }

    /**
     * 使用OutTradeNo进行关闭.
     *
     * @param $outTradeNo
     * @param $refund_fee
     * @return mixed
     *
     * @throws EshanghuException
     * @throws HttpRequestErrorException
     */
    public function refundUseOutTradeNo($outTradeNo,$refund_fee)
    {
        $data = [
            'app_key' => $this->appKey,
            'sub_mch_id' => $this->subMchId,
            'refund_fee' => $refund_fee,
            'out_trade_no' => $outTradeNo,
        ];

        return $this->refund($data);
    }

    /**
     * 交易退款.
     *
     * @param array $data
     *
     * @return mixed
     *
     * @throws EshanghuException
     * @throws HttpRequestErrorException
     */
    public function refund(array $data)
    {
        $data['sign'] = Signer::getSign($data, $this->appSecret);

        return $this->request(self::REFUND_URL, $data);
    }

    /**
     * 请求
     *
     * @param $url
     * @param array $data
     *
     * @return mixed
     *
     * @throws EshanghuException
     * @throws HttpRequestErrorException
     */
    public function request($url, array $data)
    {
        $response = $this->client->post($url, [
            'form_params' => $data,
        ]);
        if ($response->getStatusCode() != 200) {
            throw new HttpRequestErrorException('无法创建远程支付订单');
        }
        $responseContent = json_decode($response->getBody(), true);
        if ($responseContent['code'] != 200) {
            throw new EshanghuException($responseContent['message']);
        }

        return $responseContent['data'];
    }
}
