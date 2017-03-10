<?php

return  array(
    'debug' => true,

    'merchantNum' => '22294531',
    'desKey' => 'ta4E/aspLA3lgFGKmNDNRYU92RkZ4w2t',
    'md5Key' => '',

    //商户的私钥（后缀是.pen）文件相对路径
    'private_key_path' => __DIR__ . '/rsa_private_key.pem',

    //支付宝公钥（后缀是.pen）文件相对路径
    'public_key_path' => __DIR__ . '/rsa_public_key.pem',


    // 支付完成异步通知调用地址
    'notify_url' => 'http://servicetest.weicang.me/payment/jdpay/notify.php',

    // 支付完成同步返回地址
    'return_url' => 'http://servicetest.weicang.me/payment/jdpay/notify.php'
);