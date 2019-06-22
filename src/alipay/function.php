<?php
/**
 * Created by PhpStorm.
 * User: hgq <393210556@qq.com>
 * Date: 2019/06/22
 * Time: 上午 10:28
 */
if (!function_exists('parse_name')) {
	/**
	 * 字符串命名风格转换
	 * type 0 将Java风格转换为C的风格 1 将C风格转换为Java的风格
	 * @param string $name 字符串
	 * @param integer $type 转换类型
	 * @param bool $ucfirst 首字母是否大写（驼峰规则）
	 * @return string
	 */
	function parse_name($name, $type = 0, $ucfirst = true) {
		if ($type) {
			$name = preg_replace_callback('/_([a-zA-Z])/', function($match) {
				return strtoupper($match[1]);
			}, $name);

			return $ucfirst ? ucfirst($name) : lcfirst($name);
		} else {
			return strtolower(trim(preg_replace("/[A-Z]/", "_\\0", $name), "_"));
		}
	}
}