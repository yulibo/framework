<?php 
namespace Module\Services;
use \Module\Common as Common;



class WoGrabProgress extends \Core\Lib\ModuleBase{

/**
 * 沃贝抢购操作服务相关接口
 *
 * @author Bean 
 *
 */
    
    //抢购商品本身是否叨叨抢购条件

    static public function grabValidCheck($vg_id){
        $res['sta'] = 1;
        $res['msg'] = "success";

        $_nowtime = time();
        $virgoods_m = Common::Model("VirGoods");
        $panic_re_m = Common::Model("PanicRecord");
        
        $vgInfo = $virgoods_m->getPanicVgInfo($vg_id);
        if(!$vgInfo){
            $res['sta'] = -1;
            $res['msg'] = "商品不存在";
        }
        //上架时间检验
        // if($vgInfo['sell_time'] > $_nowtime){
        //     $res['sta'] = -1;
        //     $res['msg'] = "商品发布时间未到";
        // }
        // 抢购时间检验


        if($vgInfo['p_begintime'] > $_nowtime){
            $res['sta'] = -1;
            $res['msg'] = "商品未开抢";
        }

        if($vgInfo['p_endtime'] < $_nowtime){
            $res['sta'] = -1;
            $res['msg'] = "商品抢兑时间已过";
        }


        $totalAmt = $panic_re_m->getGrabAmtInDb($vg_id);

        if($vgInfo['p_amount'] <= 0 ||$totalAmt >= $vgInfo['p_amount']){
            $res['sta'] = -1;
            $res['msg'] = "该商品已经抢完";
        }


        // $vgInfo

        return $res;
    }

    //添加抢购记录
    public function panicHandle($user_id,$pid){

    } 

}