### jiujiude/alipay-sdk

####支付宝SDK全功能反射服务类
####利用支付宝官方SDK封装的服务类，理论上包含了官方SDK的所有功能，支持服务商模式

[支付宝官方文档](https://docs.open.alipay.com/api)

#### 安装(PHP>=5.4)
> composer require jiujiude/alipay-sdk

#### 示例
    <?php
    /**
     * Created by PhpStorm.
     * User: hgq <393210556@qq.com>
     * Date: 2019/06/22
     * Time: 上午 10:19
     */
    require_once __DIR__ . '/../vendor/autoload.php';
    date_default_timezone_set('Asia/Shanghai');
    $config = require_once __DIR__ . '/config.php';

    //测试, 用户信息授权
    $other_content = [
    	'grant_type' => 'authorization_code',
    	'code' => '111',
    ];
    $ali = new \Alipay($config);
    $ali->setOtherContent($other_content);
    $ali->setApiMethod('alipay.system.oauth.token');
    $result = $ali->create();
    if (!$result) {
    	echo $ali->getMsg();
    }
    var_dump($result);

#### 说明
1.请假支付宝包的配置添加到config中

2.进行编码引用autoload.php

3.new \Alipay($config)，传入配置参数

#### 使用

设置请求API方法 如：alipay.system.oauth.token

> $ali->setApiMethod();

设置请求参数的集合，对应官方文档中的biz_content

> $ali->setBizContent();

设置其他请求数据，官方文档中没有biz_content，但有额外请求参数的

> $ali->setOtherContent();

支付宝请求创建，返回支付宝请求结果

> $ali->create();

验签支付宝返回的信息，使用支付宝公钥。

> $ali->verifySign();
