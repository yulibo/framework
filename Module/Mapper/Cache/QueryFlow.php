<?php
/**
 * name
 *
 * @param array
 * @param obj
 * @return array
 */

namespace Module\Mapper\Cache;


class QueryFlow
{
    //流量查询缓存
    public $queryflow = array(
        'cache'=>'redis',
        'time' =>30
    );

    //流量套餐详情查询缓存
    public $queryinfoNew = array(
        'cache'=>'redis',
        'time' =>30
    );
}