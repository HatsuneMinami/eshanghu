<?php

require_once '../vendor/autoload.php';

$config = require_once './eshanghu-dev.php';

$eshanghu = new \Eshanghu\Eshanghu($config);

$refund_fee = 1;

$outTradeNo = 'A12312366798903';
try{
    $response = $eshanghu->refundUseOutTradeNo($outTradeNo,$refund_fee);
    try{
        $data = $eshanghu->callback($response);
        var_dump($data);
    }catch (\Eshanghu\Exceptions\SignErrorException $error){
        var_dump($error->getMessage());
    }
}catch (\Exception $e){
    var_dump($e->getMessage());
}


/*$orderSn = '201911271749531243115205';
try{
    $response = $eshanghu->refundUseOrderSn($orderSn,$refund_fee);
    var_dump($response);
}catch (\Exception $e){
    var_dump($e->getMessage());
}*/


//SUCCESS:
//array(4) {
//    ["order_sn"]=>
//  string(16) "2019030723170822"
//    ["out_trade_no"]=>
//  string(7) "A123123"
//    ["code_url"]=>
//  string(35) "weixin://wxpay/bizpayurl?pr=f3kYiAm"
//    ["sign"]=>
//  string(32) "BAAE27C137DEC7EF300B9E8CCD48D36C"
//}