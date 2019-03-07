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
    const WECHAT_NATIVE_URL = 'https://1shanghu.com/api/wechat/native';
    const QUERY_URL = 'https://1shanghu.com/api/query';

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

    public function create($outTradeNo, $subject, $totalFee, $extra = '')
    {
        $data = [
            'app_key' => $this->appKey,
            'out_trade_no' => $outTradeNo,
            'total_fee' => $totalFee,
            'subject' => $subject,
            'extra' => $extra,
            'notify_url' => $this->notify,
        ];
        $data['sign'] = Signer::getSign($data, $this->appSecret);

        return $this->request(self::WECHAT_NATIVE_URL, $data);
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
