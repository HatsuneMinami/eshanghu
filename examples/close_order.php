<?php
require_once '../vendor/autoload.php';

$config = require_once './eshanghu-dev.php';

$eshanghu = new \Eshanghu\Eshanghu($config);

$outTradeNo = 'A12312366798903';

try{
    $response = $eshanghu->closeUseOutTradeNo($outTradeNo);
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
    $response = $eshanghu->closeUseOrderSn($orderSn);
    var_dump($response);
}catch (\Exception $e){
    var_dump($e->getMessage());
}*/



//SUCCESS:
//array(6) {
//    ["status"]=> int(7)
//    ["total_fee"]=> int(1)
//    ["order_sn"]=> string(24) "201911271749531243115205"
//    ["out_trade_no"]=> string(15) "A12312366798903"
//    ["status_text"]=> string(9) "已取消"
//    ["sign"]=> string(32) "4EB42D224BDA94109549D018B0106238"
//}