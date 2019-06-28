<?php
/**
 * Created by PhpStorm.
 * User: hgq <393210556@qq.com>
 * Date: 2019/06/21
 * Time: 上午 9:33
 * alipay SDK 全功能服务类
 */
final class Alipay {
	//配置信息
	public $config;
	//请求API方法 如：alipay.system.oauth.token
	public $api_method;
	//请求参数的集合，对应官方文档中的biz_content
	public $biz_content = [];
	//其他请求数据，官方文档中没有biz_content，但有额外请求参数的
	public $other_content = [];
	//参数的集合
	private $param;
	//请求提示
	public $msg;

	function __construct($config) {
		$this->config = $config;
		$this->param['notify_url'] = $config['notify_url'];
		$this->param['return_url'] = $config['return_url'];
	}

	/**
	 * @return string
	 * @author hgq <393210556@qq.com>.
	 * @date: 2019/06/21 下午 14:49
	 */
	public function getApiMethod() {
		return '\\' . parse_name(str_replace('.', '_', $this->api_method), true) . 'Request';
	}

	/**
	 * @param $api_method
	 * @author hgq <393210556@qq.com>.
	 * @date: 2019/06/21 下午 14:49
	 */
	public function setApiMethod($api_method) {
		$this->api_method = $api_method;
	}

	/**
	 * @return mixed
	 * @author hgq <393210556@qq.com>.
	 * @date: 2019/06/21 下午 14:49
	 */
	public function getBizContent() {
		return $this->biz_content;
	}

	/**
	 * @param $biz_content
	 * @author hgq <393210556@qq.com>.
	 * @date: 2019/06/21 下午 14:49
	 */
	public function setBizContent($biz_content) {
		$biz_content['extend_params'] = [
			'sys_service_provider_id' => $this->config['sys_service_provider_id'] //系统商编号 该参数作为系统商返佣数据提取的依据，请填写系统商签约协议的PID
		];
		$this->biz_content = json_encode($biz_content, JSON_UNESCAPED_UNICODE);
		$this->param["biz_content"] = $this->biz_content;
	}

	/**
	 * @return mixed
	 * @author hgq <393210556@qq.com>.
	 * @date: 2019/06/21 下午 14:49
	 */
	public function getOtherContent() {
		return $this->other_content;
	}

	/**
	 * @param $other_content
	 * @author hgq <393210556@qq.com>.
	 * @date: 2019/06/21 下午 14:49
	 */
	public function setOtherContent($other_content) {
		$this->param = array_merge($this->param, $other_content);
	}

	/**
	 * @return mixed
	 * @author hgq <393210556@qq.com>.
	 * @date: 2019/06/21 下午 14:49
	 */
	public function getMsg() {
		return $this->msg;
	}

	/**
	 * @param $msg
	 * @author hgq <393210556@qq.com>.
	 * @date: 2019/06/21 下午 14:49
	 */
	public function setMsg($msg) {
		$this->msg = $msg;
	}

	/**
	 * 支付宝请求创建
	 * @param null $appInfoAuthtoken
	 * @return bool
	 * @author hgq <393210556@qq.com>.
	 * @date: 2019/06/21 下午 14:48
	 */
	public function create($appInfoAuthtoken = null) {
		$aop = new \ReflectionClass('\AopClient');
		//1.配置设置
		$aop_instance = $aop->newInstanceArgs();
		foreach ($this->config as $index => $item) {
			$property = parse_name($index, true, false);
			if ($aop->hasProperty($property)) {
				$aop_instance->$property = $item;
			}
		}
		//2.请求方法，请求参数
		$method = $this->getApiMethod();
		$request = new \ReflectionClass($method);
		$param = $this->param;
		$request_instance = $request->newInstanceArgs();
		foreach ($param as $index => $item) {
			$property = parse_name($index, true, false);
			$set_method = 'set' . parse_name($index, true);
			if ($request->hasProperty($property) && $request->hasMethod($set_method)) {
				$request_instance->$set_method($item);
			}
		}
		//3.返回值
		$result = $aop_instance->execute($request_instance, null, $appInfoAuthtoken);
		$result = json_encode($result);
		$result = json_decode($result, true);
		$responseNode = str_replace(".", "_", $this->api_method) . "_response";
		/*
		 * 这里可能带来不兼容问题,使用过程中发现支付宝返回的数据格式有可能不统一
		 * 当前发现了几种可能：
		 * 1、response Key 存在，且有code，且是 10000 ，正常
		 * 2、response Key 存在，但没有code,有sub_msg，错误
		 * 3、response Key 存在，但没有code,或无sub_msg 正常
		 * 4、response Key 存在，且有code，不是10000，错误
		 * 5、response Key 不存在，error_response存在 错误
		 * 6、其他可能性
		 * By hgq <393210556@qq.com> 2019/06/28 下午 18:42
		 */
		if (isset($result[$responseNode])) {
			if (isset($result[$responseNode]['code']) && $result[$responseNode]['code'] == 10000) {
				return $result[$responseNode];
			} elseif(!isset($result[$responseNode]['code'])) {
				if(isset($result[$responseNode]['sub_msg'])) {
					$this->setMsg($result[$responseNode]['sub_msg']);
					return false;
				}
				return $result[$responseNode];
			}else {
				$this->setMsg($result[$responseNode]['sub_msg']);
				return false;
			}
		} elseif(isset($result['error_response'])) {
			$this->setMsg($result['error_response']['sub_msg']);
			return false;
		} else {
			$this->setMsg('支付宝系统故障或非法请求');
			return false;
		}
	}

	/**
	 * 验签支付宝返回的信息，使用支付宝公钥。
	 * @param $arr
	 * @return bool
	 * @author hgq <393210556@qq.com>.
	 * @date: 2019/06/21 下午 14:47
	 */
	public function verifySign($arr) {
		$aop = new \AopClient();
		$aop->alipayrsaPublicKey = $this->config['alipayrsa_public_key'];
		$result = $aop->rsaCheckV1($arr, $this->config['alipayrsa_public_key'], $this->config['sign_type']);
		return $result;
	}
}