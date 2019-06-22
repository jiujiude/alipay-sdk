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
$ali = new Alipay($config);
$ali->setOtherContent($other_content);
$ali->setApiMethod('alipay.system.oauth.token');
$result = $ali->create();
if (!$result) {
	echo $ali->getMsg();
}
var_dump($result);