<?php
/**
 * 我为流量代言.
 */
namespace Module\Represent;

class ForStream extends \Module\Represent {
	
	/**
	 * Instances of the Represent.
	 * @var object
	 */
	protected static $instance;
	
	/**
	 * Get instance of ForStream.
	 * @return \Module\ForStream
	 */
	public static function instance()
	{
		if (!isset(self::$instance))
		{
			self::$instance = new ForStream();
		}
		return self::$instance;
	}
	
	public function getInitPrice($hp, $pid){
		
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
		$streamCut=\Model\StreamCut::instance()->getRow(array('id'=>$pid));
		$rpsCnf = unserialize($streamCut['cut_price']);
		if (empty($rpsCnf)) {
			return $res;
		}
		$proKey = array_keys($rpsCnf);
		$key = $this->getRand($proKey);
		if (!empty($rpsCnf[$key]['min']) && !empty($rpsCnf[$key]['max'])) {
			// 金额配置单位元,转化为分进行随机取整,再换算为单位元返回
			$res['price'] = mt_rand($rpsCnf[$key]['min'] * 100, $rpsCnf[$key]['max'] * 100) / 100;
		}
		if (isset($rpsCnf[$key]['note'])) {
			$res['note'] = $rpsCnf[$key]['note'];
		}
		return $res;
	}
}