<?php

require_once '../vendor/autoload.php';

$config = require_once './eshanghu-dev.php';

$eshanghu = new \Eshanghu\Eshanghu($config);

$app_id = 'wx236dc72aa80fd3f5';
$openid = 'wx271749536665680c43faf==';
$outTradeNo = 'A12312366798903';
$subject = '商品名称';
$totalFee = 1; // 单位：分
$extra = 'diy your self';

//调用Eshanghu mp方法时，request方法对返回状态已做异常处理
try{
    //获取返回data
    $response = $eshanghu->mini($outTradeNo, $subject, $totalFee, $app_id, $openid, $extra);
    //验签
    try{
        $data = $eshanghu->callback($response);
        var_dump($data);
    }catch (\Eshanghu\Exceptions\SignErrorException $error){
        var_dump($error->getMessage());
    }
}catch (\Exception $e){
    var_dump($e->getMessage());
}

//SUCCESS:
//array(10) {
//    ["order_sn"] => string(24)
//	"201911271749531243115205"
//    ["out_trade_no"] => string(15)
//	"A12312366798903"
//    ["total_fee"] => int(1)
//    ["jsapi_app_id"] => string(18)
//	"wx25db1b0b82771257"
//    ["jsapi_timeStamp"] => string(10)
//	"1574848193"
//    ["jsapi_nonceStr"] => string(16)
//	"fu3QP4FPzNwF8LC3"
//    ["jsapi_package"] => string(46)
//	"prepay_id=wx271749536665680c43faf80e1051744200"
//    ["jsapi_signType"] => string(3)
//	"MD5"
//    ["jsapi_paySign"] => string(32)
//	"753B845ABB4A198CC1ECCD1DAA1F802F"
//    ["sign"] => string(32)
//	"6EEEC24BE6DB7F2CF486C4421E7FD0A0"
//}