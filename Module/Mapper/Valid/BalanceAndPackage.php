<?php
/**
 * name
 *
 * @param array
 * @param obj
 * @return array
 */

namespace Module\Mapper\Valid;


class BalanceAndPackage
{
    //余额查询
    public $queryPhoneBalance = array(
        array('phone', 1, '电话号码不能为空', 'require'),
    );

    //套餐查询
    public $queryPhoneInfo = array(
        array('phone', 1, '电话号码不能为空', 'require'),
    );
}