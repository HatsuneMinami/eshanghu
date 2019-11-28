## 易商户支付扩展包

网址：[https://1shanghu.com](https://1shanghu.com)

注意，v2.1及之前版本对应易商户API的v1接口版本。

## 用法

首先，您需要安装依赖：

```
composer install
```

### 创建订单


```php
<?php
require_once '../vendor/autoload.php';
$config = require_once '../eshanghu.php';
$eshanghu = new \Eshanghu\Eshanghu($config);
$outTradeNo = 'A123123';
$subject = '商品名称';
$totalFee = 1; // 单位：分
$extra = 'diy your self';
$response = $eshanghu->create($outTradeNo, $subject, $totalFee, $extra);
```

### 微信jsapi


```php
<?php
require_once '../vendor/autoload.php';
$config = require_once '../eshanghu.php';
$eshanghu = new \Eshanghu\Eshanghu($config);

//获取openid
if(isset($_GET['openid'])){
    $openid = $_GET['openid'];
}else{
    //获取微信openid授权链接
    $callbackUrl = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    $callbackUrl = urlencode($callbackUrl);
    $url = $eshanghu->getOpenidUrl($callbackUrl);
    header('location:'.$url);
}

$outTradeNo = 'A123123';
$subject = '商品名称';
$totalFee = 1; // 单位：分
$extra = 'diy your self';
//调用Eshanghu mp方法时，request方法对返回状态已做异常处理
try{
    //获取返回data
    $response = $eshanghu->mp($outTradeNo, $subject, $totalFee, $openid, $extra);
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
```

### 微信小程序


```php
<?php
require_once '../vendor/autoload.php';
$config = require_once '../eshanghu.php';
$eshanghu = new \Eshanghu\Eshanghu($config);

$app_id = 'wx236dc72aa80fd3f5';
$openid = 'wx271749536665680c43faf==';
$outTradeNo = 'A123123';
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
```



### 查询订单

```php
<?php
require_once '../vendor/autoload.php';
$config = require_once '../eshanghu.php';
$eshanghu = new \Eshanghu\Eshanghu($config);
$outTradeNo = 'A123123123123';
$response = $eshanghu->queryUseOutTradeNo($outTradeNo);
var_dump($response);
```

### 关闭订单

```php
<?php
require_once '../vendor/autoload.php';
$config = require_once '../eshanghu.php';
$eshanghu = new \Eshanghu\Eshanghu($config);
$outTradeNo = 'A123123123123';

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
```

### 退款订单

```php
<?php
require_once '../vendor/autoload.php';
$config = require_once '../eshanghu.php';
$eshanghu = new \Eshanghu\Eshanghu($config);
$outTradeNo = 'A123123123123';
$refund_fee = 1;
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
```