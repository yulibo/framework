<?php
/**
 * name
 *
 * @param array
 * @param obj
 * @return array
 */

namespace Module\Mapper\Cache;


class Stream
{
    //流量包推荐缓存
    public $getFpRecommendByPhone = array(
        'cache'=>'redis',
        'time' =>30
    );
}