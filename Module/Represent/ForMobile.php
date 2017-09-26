<?php
/**
 * 沃为手机代言.
 */
namespace Module\Represent;
use \Module\Common;

class ForMobile extends \Module\Represent {
	
	/**
	 * Instances of the Represent.
	 * @var object
	 */
	protected static $instance;
	
	/**
	 * Get instance of ForMobile.
	 * @return \Module\ForMobile
	 */
	public static function instance()
	{
		if (!isset(self::$instance))
		{
			self::$instance = new ForMobile();
		}
		return self::$instance;
	}
	
	/**
	 * 获取商品初始化价格.
	 * 
	 * @param integer $hp   手机号.
	 * @param integer $pid  商品编号.
	 * 
	 * @return float
	 */
	public function getInitPrice($hp, $pid) {
		$this->userInfo = \Model\User::instance()->getUserInfoByPhone($hp);
		if (empty($this->userInfo)) {
			throw new RepresentException("user not find");
		}
		$hp = $this->userInfo['phone'];
		// 用户初始化折扣金额.
		$price = 0;
		// 获取用户网龄.
		$age = $this->getNetage($hp);
		if (!empty($age)) {
			// 老用户价格折扣.
			$price = $age * $this->getNetagePrice($pid, $age);
		}

		return $price;
	}
	
	/**
	 * 获取用户网龄.
	 * 
	 * @param integer $hp 手机号.
	 * 
	 * @return mixed
	 */
	private function getNetage($hp) {
		return \Model\UserNetage::instance()->getNetAgeByHpAndType($hp, \Model\UserNetage::NET_AGE_FORMOBILE);
	}
	
	/**
	 * 根据产品编号和网龄取老用户折扣金额.
	 * 
	 * @param integer $pid 产品编号.
	 * @param integer $age 网龄.
	 * 
	 * @throws RepresentException
	 * @return mixed
	 */
	private function getNetagePrice($pid, $age) {
		// $rpsFMOldUserCnf = $this->getBiz('rpsFMOldUserCnf');

		// ---------------------new logic start -------------------------------------
		$ProductCut_m = Common::Model("ProductCut");

		$ori_rpsFMOldUserCnf = $ProductCut_m -> get_netage($pid);

		if(!$ori_rpsFMOldUserCnf){
			throw new RepresentException('Mall biz rpsFMOldUserCnf be not set');
		}


		$rpsFMOldUserCnf = [$pid =>$ori_rpsFMOldUserCnf];

		// ---------------------new logic end -------------------------------------

		if (!isset($rpsFMOldUserCnf[$pid]) || empty($rpsFMOldUserCnf[$pid])) {
			throw new RepresentException('Mall biz rpsFMOldUserCnf be not set');
		}

		$netAgePriceArr = $rpsFMOldUserCnf[$pid];
		// 取到网龄对应金额则直接返回.
		if (!empty($netAgePriceArr[$age])) {
			return $netAgePriceArr[$age];
		}
		// 根据网龄计算对应网龄折扣金额.
		$ageArr = array_keys($netAgePriceArr);
		sort($ageArr);
		// 默认取最低网龄.
		$tmpKey = current($ageArr);
		foreach ($ageArr as $val) {
			if ($age >= $val) {
				$tmpKey = $val;
			} else {
				break;
			}
		}

		return $netAgePriceArr[$tmpKey];
	}
	
	/**
	 * 沃为手机代言砍价逻辑.
	 * 
	 * @param integer $pid 产品编号.
	 * 
	 * @return array 砍下金额,说明
	 */
	public function bargain($pid) {
		$res = array(
			'price' => 0,
			'note' => ''
		);

		// $rpsCnf = $this->getBiz('rpsFMCnf');
		// if (empty($rpsCnf[$pid])) {
		// 	return $res;
		// }

		// -------------new logic start -----------------------
		$ProductCut_m = Common::Model("ProductCut");

		$ori_rpsCnf = $ProductCut_m -> get_ori_cut_price($pid);
		
		if(!$ori_rpsCnf){
			return $res;
		}

		$rpsCnf = [$pid =>$ori_rpsCnf];
		// ------------- new logic end -----------------------

		$key = $this->getRand2($ori_rpsCnf);

		$res['key'] = $key;
		if (!empty($rpsCnf[$pid][$key]['min']) && !empty($rpsCnf[$pid][$key]['max'])) {
			// 金额配置单位元,转化为分进行随机取整,再换算为单位元返回
			$res['price'] = mt_rand($rpsCnf[$pid][$key]['min'] * 100, $rpsCnf[$pid][$key]['max'] * 100) / 100;
		}
		if (isset($rpsCnf[$pid][$key]['note'])) {
			$res['note'] = $rpsCnf[$pid][$key]['note'];
		}
		return $res;
	}
}