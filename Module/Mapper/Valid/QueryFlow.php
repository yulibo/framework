<?php
/**
 * name
 *
 * @param array
 * @param obj
 * @return array
 */

namespace Module\Mapper\Valid;


class QueryFlow
{

    public $queryflow = array(
        array('PHONE', 1, '电话号码不能为空', 'require'),
    );
}