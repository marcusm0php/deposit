<?php
/**
 * Created by PhpStorm.
 * User: zhao
 * Date: 2018/6/8
 * Time: 9:40
 */

return [
    // HTTP 请求的超时时间（秒）
    'timeout' => 5.0,

    // 默认发送配置
    'default' => [
        // 网关调用策略，默认：顺序调用
        'strategy' => \Overtrue\EasySms\Strategies\OrderStrategy::class,

        // 默认可用的发送网关
        'gateways' => [
            'aliyun'
        ],
    ],
    // 可用的网关配置
    'gateways' => [
        'errorlog' => [
            'file' => 'tmp/easy-sms.log',
        ],
        'aliyun' => [
            'access_key_id' => env('ALIYUN_KEY_ID','LTAIM9XmC2lNteVe'),
            'access_key_secret' => env('ALIYUN_KEY_SECRET','KF4gy6YwtPyBs5OGsxZBz4YnaXvtOi'),
            'sign_name' => env('ALIYUN_SIGN_NAME','摇钱吧'),
        ],
    ],
];