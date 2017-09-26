<?php
/**
 * name
 *
 * @param array
 * @param obj
 * @return array
 */

namespace Module\Mapper\Valid;


class FlowDetail
{
    public $getUserFlowUseDetailByTime = array(
        array('phone', 1, '手机号码不能为空', 'require'),
    ); //修改支付网关验证数据
}