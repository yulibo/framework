<?php
/**
 * name
 *
 * @param array
 * @param obj
 * @return array
 */

namespace Module\Mapper\Valid;


class Stream
{
    //4G流量流水号获取
    public $getTaskId = array(
        array('phone', 1, '电话号码不能为空', 'require'),
        array('action', 1, '操作名称不能为空', 'require'),
    );

    //4G流量办理
    public $productFlowOperate = array(
        array('phone', 1, '电话号码不能为空', 'require'),
        array('packagecode', 1, '业务包产品编码不能为空', 'require'),
        array('taskId', 1, '业务流水号不能为空', 'require'),
        array('modify', 1, '业务操作标识不能为空', 'require'),
    );

    //获取推荐流量包
    public $getFpAllowByPhone = array(
        array('phone', 1, '电话号码不能为空', 'require'),
    );

    //获取推荐流量包
    public $getFpRecommendByPhone = array(
        array('phone', 1, '电话号码不能为空', 'require'),
    );
}