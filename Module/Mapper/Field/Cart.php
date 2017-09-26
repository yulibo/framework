<?php

namespace Module\Mapper\Field;

class Cart  {

    public $addCart = array(
        'goodsId', //商品ID
        'productId', //货品ID
        'wId', //门店ID
        'number', //购买数量
        'userId', //用户ID
		'buyPhoneNumber', //电话号码
		'userName', //用户名
		'idCard', //身份证
		'userPreId',//优惠ID
        'type', //0 加入购物车 1 立即购买
		'goodsDis' //手动指定物流方式
		); //插入购物车数据格式
}
