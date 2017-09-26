<?php

namespace Module\Api;

use \Exception as Exception;

class Cart extends \Core\Lib\ApiModuleBase {


    //添加购物车
    public function addCart(array $data = array()) {
        try {
            $this->getService()->addCart($data);
            return $this->getResult();
        } catch (Exception $e) {
            $this->getService()->setException($e);
        }
    }

    //获取购物车列表
    public function getCartList(array $data) {
        $this->getService()->getCartList($data);
        $result = $this->getPageResult();
        foreach ($result as &$val) {
            $val['customerServer'] = json_decode($val['customerServer'], 1);
            $val['attr'] = unserialize($val['attr']);
            $val['attr'] = array_map(function ($n) {
                $n['value'] = explode(':', $n['value']);
				if(!isset($n['value'][1]) || empty($n['value'][1])){
					return false;
				}
                $n['value'] = $n['value'][1];
                return $n;
            }, (array)$val['attr']);
			$val['pn_id'] = intval($val['pn_id']);
            $val['attr'] = \Module\Common::getAttrByList($val['attr']);
        }
        return $result;
    }


	//获取购物车总数
	public function getCountData(array $data){
		$result = array('count'=>0);
		$cartList = $this->getCartList($data);
		foreach($cartList as $val){
			$result['count'] +=$val['number'];
		}
		return $result;
	}
	
	
    //删除购物车
    public function deleteCart(array $data) {
        $data['cartIds'] = (isset($data['cartIds']) && is_array($data['cartIds'])) ? array_filter($data['cartIds'], 'intval') : array();
        $this->getService()->deleteCart($data);
        return $this->getResult();
    }

    //计算购物车列表金额、炫贝、总数
    public function computeCartList(array $data) {
        $this->getService()->computeCartList($data);
        return $this->getResult();
    }

    //修改购物车商品数量
    public function setCartNum(array $data) {
        $this->getService()->setCartNum($data);
        return $this->getCode();
    }

}
