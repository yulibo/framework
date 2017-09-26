<?php

namespace Module\Mapper\Valid;

class Order {

	public $updateOrderPayGateway = array(
	  array('userId', 1, '用户ID不能为空', 'require'),
	  array('orderSn', 1, '订单编号不能为空', 'require'),
	  array('aid', 1, '网关类型不能为空', 'require')
	); //修改支付网关验证数据
	
	public $makeOrder = array(
	  array('buyerId', 1, '用户ID不能为空', 'require'),
	  array('cids', 1, '请选择结算商品', 'require')
	); //生成订单数据验证
	
	public $pay = array(
	  array('uid', 1, '用户ID不能为空', 'require'),
	  array('orderSNs', 1, '订单编号集合不能为空', 'require')
	);//支付订单数据验证
	
	public $queryOrderSnS = array(
	  array('uid', 1, '用户ID不能为空', 'require'),
	  array('orderNo', 1, '交易流水号不能为空', 'require')
	);//根据流水号 查询订单
}
