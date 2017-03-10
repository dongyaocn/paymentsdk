<?php
include dirname(dirname(dirname(__DIR__))) . '/vendor/autoload.php';

date_default_timezone_set('PRC');

use Payment\JdPay\AsynNotifyAction;
use Payment\JdPay\Pay;
use Payment\JdPay\Util\Config;
use Payment\JdPay\Util\Sign;

use Payment\JdPay\Attribute\Order;

$m = new AsynNotifyAction();
// $m->execute();exit;


//
$now = time();

$attribute = [
    'tradeNum' => '8892142122',
    'tradeName' => '微仓测试订单',
    'tradeDesc' => '测试订单描述',
    'tradeTime' => date('YmdHis'),
    'amount' => '1',
    'currency' => 'CNY',
    'callbackUrl' => Config::get('return_url'),
    'notifyUrl' => Config::get('notify_url'),
    'ip' => $_SERVER["REMOTE_ADDR"],
    'orderType' => '1',
    'userId' => '18829553701',
    'expireTime' => '259200', // 86400 * 3
];

$order = new Order($attribute, false);

// var_export($attribute);
// echo "\r\n";
// echo "\r\n";
// var_export($order->all());exit;

$pay = new Pay();
// echo $pay->buildRequestFormH5($order);
echo $pay->getOrderId($order);





