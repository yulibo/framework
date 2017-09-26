<?php
/**
 * name
 *
 * @param array
 * @param obj
 * @return array
 */

namespace Module\Mapper\Valid;


class ChinaUnicomActivites2016
{

    public $getUnicomUserInfoByPhone = array(
        array('phone', 1, '电话号码不能为空', 'require'),
    ); //联通用户信息查询

    public $getNotUnicomUserInfoByPhone = array(
        array('phone', 1, '电话号码不能为空', 'require'),
    ); //非联通用户信息查询
}