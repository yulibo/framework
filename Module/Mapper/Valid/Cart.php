<?php

namespace Module\Mapper\Valid;

class Cart  {

    public $addCart = array(
        array('goodsId', 1, '商品ID不能为空', 'require'),
		array('productId', 1, '货品ID不能为空', 'require')
    ); //添加购物车自动验证
	
}
