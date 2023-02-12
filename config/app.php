<?php

return [
    //consul注册配置
    'consul' => [
        'address' => '127.0.0.1',
        'port' => 18308,
        'name' => 'sys',
        'id' => 'sys',
    ],

    //发送验证码操作类型
    'actions' => [
        'login' => '登录',
        'register' => '注册',
        'bind_email' => '绑定邮箱',
        'bind_mobile' => '绑定手机号码',
        'set_trade_pwd' => '设置交易密码',
        'reset_trade_pwd' => '重置交易密码',
        'off_email_verify' => '关闭邮箱验证',
        'off_mobile_verify' => '关闭手机验证',
        'off_ga_verify' => '关闭谷歌验证',
        'on_email_verify' => '开启邮箱验证',
        'on_mobile_verify' => '开启手机验证',
        'on_ga_verify' => '开启谷歌验证',
        'bind_google_auth' => '绑定谷歌验证',
        'modify_google_auth' => '修改谷歌验证',
        'reset_pwd' => '重置登录密码',
        'withdraw' => '提现',
        'modify_mobile' => '修改手机号码'
    ],

    //发送验证码操作的过期时间 单位：秒
    'code_operate_expire_time' => 60,

    //验证码过期时间 单位分钟
    'code_expire_time' => 15,

    //阿波罗配置
    'apollo' => require_once __DIR__."/../../../apollo.php",

    'qi_niu_ak' => 'LwXbwtlypcFrWkOQuUCiIgosKN0HW05xGDXy5wkU',
    'qi_niu_sk' => 'gm2_QVnh_Cg6F5g8opKppOHz98c0L7wC_JIlNI6t',
    'qi_niu_bucket' => 'bitsw',
    'file_aes_key' => '43743c86523cd414916415229e3241d2f687dc14133999c4f093054172ec1261',

    'coin_last_price_key' => 'coin:last:price:info',

    'table_coin' => 'cache:table:coin',

    'jpush' => array(
        'app_key' => '8164e94be76cd51e7ed1b64a',
        'master_secret' => '2f6cf493604c12a29adfb424',
        'log_path' => '/logs/jpush/jpush.log',
    ),
];

