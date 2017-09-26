<?php

/**
 * Log配置
 */

namespace Core\Config;

class Log extends ConfigBase {

    /**
     * 文件日志的根目录.请确认php进程对此目录可写
     * @var string
     */
    public $FILE_LOG_ROOT = SYS_LOG;

    /**
     * logger is configured to use error_log
     *
     * @var array
     */
    public $default = array(
        'logger' => 'php'
    );

    /**
     * configured to use jsonfile.  fields is require, which indicates how to spplit the log messages
     *
     * @var array
     */
    public $db = array(
        'logger' => 'file',
    );
    public $api = array(
        'logger' => 'file',
    );
    public $smsLocked = array(
        'logger' => 'file',
    );
    public $roleAuth = array(
        'logger' => 'jsonfile',
        'fields' => array()
    );
    public $mallUser = array(
        'logger' => 'jsonfile',
        'fields' => array()
    );
    public $addCollect = array(
        'logger' => 'jsonfile',
        'fields' => array()
    );
    public $publishComment = array(
        'logger' => 'jsonfile',
        'fields' => array()
    );
    public $replyComment = array(
        'logger' => 'jsonfile',
        'fields' => array()
    );
    public $updateStore = array(
        'logger' => 'jsonfile',
        'fields' => array()
    );

    public $Admin = array(
        'logger' => 'jsonfile',
        'fields' => array()
    );


    // public $adminLottery = array(
    //     'logger' => 'jsonfile',
    //     'fields' => array()
    // );

    // public $adminEcoupon = array(
    //     'logger' => 'jsonfile',
    //     'fields' => array()
    // );

    public $WmApi = array(
        'logger' => 'jsonfile',
        'fields' => array()
    );
    public $updateStoreGoodsClass = array(
        'logger' => 'jsonfile',
        'fields' => array()
    );
    public $updateStoreColumn = array(
        'logger' => 'jsonfile',
        'fields' => array()
    );
    public $storeRecommendOp = array(
        'logger' => 'jsonfile',
        'fields' => array()
    );
    public $message = array(
        'logger' => 'jsonfile',
        'fields' => array()
    );
    public $complaint = array(
        'logger' => 'jsonfile',
        'fields' => array()
    );
    public $apRole = array(
        'logger' => 'jsonfile',
        'fields' => array()
    );
    public $returnBack = array(
        'logger' => 'jsonfile',
        'fields' => array()
        );
    public $exchange = array(
        'logger' => 'jsonfile',
        'fields' => array()
        );
    public $refund = array(
        'logger' => 'jsonfile',
        'fields' => array()
        );
    public $phoneNumber = array(
        'logger' => 'jsonfile',
        'fields' => array()
        );
    public $storeRole=array(
        'logger' => 'jsonfile',
        'fields' => array()
        );
    public $phoneNumberClass=array(
        'logger' => 'jsonfile',
        'fields' => array()
        );
    public $lotteryStream=array(
        'logger' => 'jsonfile',
        'fields' => array()
        );
    public $lotteryStreamPackage=array(
        'logger' => 'jsonfile',
        'fields' => array()
        );
    public $lotterySonPackage=array(
        'logger' => 'jsonfile',
        'fields' => array()
        );
	public $addOrderFlow=array(
        'logger' => 'jsonfile',
        'fields' => array()
        );
		
	public $addOrderFlowConfig=array(
        'logger' => 'jsonfile',
        'fields' => array()
        );
	// public $adminStream=array(
	// 	'logger' => 'jsonfile',
	// 	'fields' => array()
	// );
	// public $adminSales=array(
	//     'logger' => 'jsonfile',
	//     'fields' => array()
	// );
    public $register=array(
        'logger' => 'jsonfile',
        'fields' => array()
        );
    public $returnPay=array(
        'logger' => 'jsonfile',
        'fields' =>  array()
        );
    //账户变更记录日志
    public $accountExchange=array(
        'logger' => 'jsonfile',
        'fields' =>  array()
        );
    //订单支付修改失败时修改状态日志
    public $orderCallback=array(
        'logger' => 'jsonfile',
        'fields' => array()
        );
    //订单退款api接口调用
    public $refundApi=array(
        'logger' => 'jsonfile',
        'fields' => array()
        );
    //订单查询api调用
    public $queryOrderApi=array(
        'logger' =>'jsonfile',
        'fields' => array()
        );
		
	//导入电子码
    public $addElectronicCode=array(
        'logger' =>'jsonfile',
        'fields' => array()
        );
		
	//删除电子码
    public $deleteElectronicCode=array(
        'logger' =>'jsonfile',
        'fields' => array()
        );
		
	//添加虚拟商品
    public $addVGoods=array(
        'logger' =>'jsonfile',
        'fields' => array()
        );
		
	//修改虚拟商品
    public $editVGoods=array(
        'logger' =>'jsonfile',
        'fields' => array()
        );

    //炫贝账户余额接口
    public $accountXuanBei=array(
        'logger' => 'jsonfile',
        'fields' => array()
        );
	public $privilegeApply = array(
        'logger' => 'jsonfile',
        'fields' => array()
        );
	// public $adminSignAddLog = array(
	// 	'logger' => 'jsonfile',
	// 	'fields' => array()
	// );
    public $privilegeGoods=array(
        'logger' => 'jsonfile',
        'fields' => array()
        );
    public $privilegeGoodsBind=array(
        'logger' => 'jsonfile',
        'fields' => array()
        );
    public $privilegeNumber=array(
        'logger' => 'jsonfile',
        'fields' => array()
        );
    public $pay=array(
        'logger' => 'jsonfile',
        'fields' => array()
        );
    //关于业务流水号的日志
    public $taskId=array(
        'logger' => 'jsonfile',
        'fields' => array()
        );
    //关于客户产品进行变更（订购和退订）的日志
    public $productOperate=array(
        'logger' => 'jsonfile',
        'fields' => array()
        );
    //客户产品进行变更（订购和退订），资源购买的办理结果查询
    public $productResult=array(
        'logger' => 'jsonfile',
        'fields' => array()
        );
    //领取游戏激活码
    public $gameCode=array(
        'logger' => 'jsonfile',
        'fields' => array()
        );
	//注册乐视会员
	public $openVipLog = array(
		'logger' => 'jsonfile',
        'fields' => array()
		);



    public $ap_log = array(
        'logger' => 'jsonfile',
        'fields' => array()
        );

    // public $adminGoods = array(
    //     'logger' => 'jsonfile',
    //     'fields' => array()
    // );
    // public $adminSign = array(
    //     'logger' => 'jsonfile',
    //     'fields' => array()
    // );
	public $ChApi = array(
        'logger' => 'jsonfile',
        'fields' => array()
    );
}
