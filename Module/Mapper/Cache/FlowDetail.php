<?php
/**
 * name
 *
 * @param array
 * @param obj
 * @return array
 */

namespace Module\Mapper\Cache;


class FlowDetail
{
    //流量明细缓存
    public $getUserFlowUseDetailByTime = array(
        'cache'=>'redis',
        'time' =>30
    );
}