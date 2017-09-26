<?php 
namespace Module\Services;
use \Module\Common as Common;


class NumberPoolProgress extends \Core\Lib\ModuleBase{

    /**
    * 获取号码池号段
    *
    */
    static public function getPreOfNumPool(){

        return [130,131,132,155,156,185,186,176,145];
    }    

}