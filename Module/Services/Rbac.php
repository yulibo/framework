<?php

namespace Module\Services;
use Module\Common as Common;
/**
 +------------------------------------------------------------------------------
 * 基于角色的数据库方式验证类
 +------------------------------------------------------------------------------
 */


/*
-- --------------------------------------------------------
    CREATE TABLE `wm_access` (
      `role_id` smallint(6) unsigned NOT NULL COMMENT '角色Id',
      `node_id` smallint(6) unsigned NOT NULL COMMENT '节点Id',
      `level` tinyint(1) NOT NULL COMMENT '对应Node等级(冗余字段)',
      `module` varchar(50) DEFAULT NULL COMMENT '权限所属Module',
      KEY `groupId` (`role_id`),
      KEY `nodeId` (`node_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;


    CREATE TABLE `wm_node` (
      `id` smallint(6) unsigned NOT NULL AUTO_INCREMENT COMMENT '节点表自增Id',
      `name` varchar(50) NOT NULL COMMENT '节点名',
      `title` varchar(50) DEFAULT NULL COMMENT '节点实际保存字段',
      `status` tinyint(1) DEFAULT '0' COMMENT '节点启用状态',
      `remark` varchar(255) DEFAULT NULL COMMENT '注释',
      `sort` smallint(6) unsigned DEFAULT NULL COMMENT '排序',
      `pid` smallint(6) unsigned NOT NULL COMMENT '父节点Id',
      `level` tinyint(1) unsigned NOT NULL COMMENT '节点层级1.controller,2,action',
      PRIMARY KEY (`id`),
      KEY `level` (`level`),
      KEY `pid` (`pid`),
      KEY `status` (`status`),
      KEY `name` (`name`)
    ) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;



    CREATE TABLE `wm_role2` (
      `id` smallint(6) unsigned NOT NULL AUTO_INCREMENT COMMENT '角色自增Id',
      `name` varchar(20) NOT NULL COMMENT '角色名字',
      `pid` smallint(6) DEFAULT NULL COMMENT '父角色Id',
      `grade_son` tinyInt(2) DEFAULT 0 COMMENT '是否用于子账户',
      `status` tinyint(1) unsigned DEFAULT NULL COMMENT '角色启用状态',
      `remark` varchar(255) DEFAULT NULL COMMENT '注释',
      PRIMARY KEY (`id`),
      KEY `pid` (`pid`),
      KEY `status` (`status`)
    ) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

*/
class Rbac extends \Core\Lib\ModuleBase{

// 配置文件增加设置

    const USER_AUTH_ON        = true;  /*是否需要认证*/
    const USER_AUTH_TYPE      = 0;     /*认证类型*/
    const USER_AUTH_KEY       = 0;     /*认证识别号*/
    const REQUIRE_AUTH_MODULE = 'Store,Admin';  /*需要认证模块*/
    const NOT_AUTH_MODULE     = 'Admin';        /*无需认证模块*/
    const USER_AUTH_GATEWAY   = null; /*认证网关*/
    const RBAC_DB_DSN         = null;  /*数据库连接DSN*/
    const RBAC_ROLE_TABLE     = "wm_role2";  /*角色表名称*/
    const RBAC_USER_TABLE     = "wm_ap_role2";        /*用户表名称*/
    const RBAC_ACCESS_TABLE   = "wm_access";    /*权限表名称*/
    const RBAC_NODE_TABLE     = "wm_node";      /*节点表名称*/


    // 认证方法

    // static public function C($config){
    //     var_dump($config);
    //     return self::$config;
    // }

    static public function authenticate($map,$model='') {
        if(empty($model)) $model =  self::C('USER_AUTH_MODEL');
        //使用给定的Map进行认证
        return M($model)->where($map)->find();
    }

    //用于检测用户权限的方法,并保存到Session中
    static function saveAccessList($authId=null) {
        if(null===$authId)   $authId = $_SESSION[self::C('USER_AUTH_KEY')];
        // 如果使用普通权限模式，保存当前用户的访问权限列表
        // 对管理员开发所有权限
        if(self::C('USER_AUTH_TYPE') !=2 && !$_SESSION[self::C('ADMIN_AUTH_KEY')] )
            $_SESSION['_ACCESS_LIST']	=	self::getAccessList($authId);
        return ;
    }

	// 取得模块的所属记录访问权限列表 返回有权限的记录ID数组
	static function getRecordAccessList($authId=null,$module='') {
        if(null===$authId)   $authId = $_SESSION[self::C('USER_AUTH_KEY')];
        if(empty($module))  $module	=	CONTROLLER_NAME;
        //获取权限访问列表
        $accessList = self::getModuleAccessList($authId,$module);
        return $accessList;
	}

    //检查当前操作是否需要认证
    static function checkAccess() {
        //如果项目要求认证，并且当前模块需要认证，则进行权限认证
        if( self::C('USER_AUTH_ON') ){
			$_module	=	array();
			$_action	=	array();
            if("" != self::C('REQUIRE_AUTH_MODULE')) {
                //需要认证的模块
                $_module['yes'] = explode(',',strtoupper(self::C('REQUIRE_AUTH_MODULE')));
            }else {
                //无需认证的模块
                $_module['no'] = explode(',',strtoupper(self::C('NOT_AUTH_MODULE')));
            }
            //检查当前模块是否需要认证
            if((!empty($_module['no']) && !in_array(strtoupper(CONTROLLER_NAME),$_module['no'])) || (!empty($_module['yes']) && in_array(strtoupper(CONTROLLER_NAME),$_module['yes']))) {
				if("" != self::C('REQUIRE_AUTH_ACTION')) {
					//需要认证的操作
					$_action['yes'] = explode(',',strtoupper(self::C('REQUIRE_AUTH_ACTION')));
				}else {
					//无需认证的操作
					$_action['no'] = explode(',',strtoupper(self::C('NOT_AUTH_ACTION')));
				}
				//检查当前操作是否需要认证
				if((!empty($_action['no']) && !in_array(strtoupper(ACTION_NAME),$_action['no'])) || (!empty($_action['yes']) && in_array(strtoupper(ACTION_NAME),$_action['yes']))) {
					return true;
				}else {
					return false;
				}
            }else {
                return false;
            }
        }
        return false;
    }

	// 登录检查
	static public function checkLogin() {
        //检查当前操作是否需要认证
        if(self::checkAccess()) {
            //检查认证识别号
            if(!$_SESSION[self::C('USER_AUTH_KEY')]) {
                if(self::C('GUEST_AUTH_ON')) {
                    // 开启游客授权访问
                    if(!isset($_SESSION['_ACCESS_LIST']))
                        // 保存游客权限
                        self::saveAccessList(self::C('GUEST_AUTH_ID'));
                }else{
                    // 禁止游客访问跳转到认证网关
                    redirect(PHP_FILE.self::C('USER_AUTH_GATEWAY'));
                }
            }
        }
        return true;
	}

    //权限认证的过滤器方法
    static public function AccessDecision($appName=MODULE_NAME) {
        //检查是否需要认证
        if(self::checkAccess()) {
            //存在认证识别号，则进行进一步的访问决策
            $accessGuid   =   md5($appName.CONTROLLER_NAME.ACTION_NAME);
            if(empty($_SESSION[self::C('ADMIN_AUTH_KEY')])) {
                if(self::C('USER_AUTH_TYPE')==2) {
                    //加强验证和即时验证模式 更加安全 后台权限修改可以即时生效
                    //通过数据库进行访问检查
                    $accessList = self::getAccessList($_SESSION[self::C('USER_AUTH_KEY')]);
                }else {
                    // 如果是管理员或者当前操作已经认证过，无需再次认证
                    if( $_SESSION[$accessGuid]) {
                        return true;
                    }
                    //登录验证模式，比较登录后保存的权限访问列表
                    $accessList = $_SESSION['_ACCESS_LIST'];
                }
                //判断是否为组件化模式，如果是，验证其全模块名
                if(!isset($accessList[strtoupper($appName)][strtoupper(CONTROLLER_NAME)][strtoupper(ACTION_NAME)])) {
                    $_SESSION[$accessGuid]  =   false;
                    return false;
                }
                else {
                    $_SESSION[$accessGuid]	=	true;
                }
            }else{
                //管理员无需认证
				return true;
			}
        }
        return true;
    }

    /**
     +----------------------------------------------------------
     * 取得当前认证号的所有权限列表
     +----------------------------------------------------------
     * @param integer $authId 用户ID
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     */
    static public function getAccessList($authId) {
        // Db方式权限数据
        $db     =   Common::cDb();
        $table = array( 'role'=>self::RBAC_ROLE_TABLE,
                        'user'=>self::RBAC_USER_TABLE,
                        'access'=>self::RBAC_ACCESS_TABLE,
                        'node'=>self::RBAC_NODE_TABLE);
        
        $sql    =   "select node.id,node.name from ".
                    $table['role']." as role,".
                    $table['user']." as user,".
                    $table['access']." as access ,".
                    $table['node']." as node ".
                    "where user.user_id='{$authId}' and user.role_id=role.id and ( access.role_id=role.id  or (access.role_id=role.pid and role.pid!=0 ) ) and role.status=1 and access.node_id=node.id and node.level=1 and node.status=1";

        $apps =   $db->query($sql);
        $access =  array();
        foreach($apps as $key=>$app) {
            $appId	=	$app['id'];
            $appName	 =	 $app['name'];
            // 读取项目的模块权限
            $access[strtoupper($appName)]   =  array();
            $sql    =   "select node.id,node.name from ".
                    $table['role']." as role,".
                    $table['user']." as user,".
                    $table['access']." as access ,".
                    $table['node']." as node ".
                    "where user.user_id='{$authId}' and user.role_id=role.id and ( access.role_id=role.id  or (access.role_id=role.pid and role.pid!=0 ) ) and role.status=1 and access.node_id=node.id and node.level=2 and node.pid={$appId} and node.status=1";
            $modules =   $db->query($sql);
            // 判断是否存在公共模块的权限
            $publicAction  = array();
            foreach($modules as $key=>$module) {
                $moduleId	 =	 $module['id'];
                $moduleName = $module['name'];
                if('PUBLIC'== strtoupper($moduleName)) {
                $sql    =   "select node.id,node.name from ".
                    $table['role']." as role,".
                    $table['user']." as user,".
                    $table['access']." as access ,".
                    $table['node']." as node ".
                    "where user.user_id='{$authId}' and user.role_id=role.id and ( access.role_id=role.id  or (access.role_id=role.pid and role.pid!=0 ) ) and role.status=1 and access.node_id=node.id and node.level=3 and node.pid={$moduleId} and node.status=1";
                    $rs =   $db->query($sql);
                    foreach ($rs as $a){
                        $publicAction[$a['name']]	 =	 $a['id'];
                    }
                    unset($modules[$key]);
                    break;
                }
            }
            // 依次读取模块的操作权限
            foreach($modules as $key=>$module) {
                $moduleId	 =	 $module['id'];
                $moduleName = $module['name'];
                $sql    =   "select node.id,node.name from ".
                    $table['role']." as role,".
                    $table['user']." as user,".
                    $table['access']." as access ,".
                    $table['node']." as node ".
                    "where user.user_id='{$authId}' and user.role_id=role.id and ( access.role_id=role.id  or (access.role_id=role.pid and role.pid!=0 ) ) and role.status=1 and access.node_id=node.id and node.level=3 and node.pid={$moduleId} and node.status=1";
                $rs =   $db->query($sql);
                $action = array();
                foreach ($rs as $a){
                    $action[$a['name']]	 =	 $a['id'];
                }
                // 和公共模块的操作权限合并
                $action += $publicAction;
                $access[strtoupper($appName)][strtoupper($moduleName)]   =  array_change_key_case($action,CASE_UPPER);
            }
        }
        return $access;
    }

	// 读取模块所属的记录访问权限
	static public function getModuleAccessList($authId,$module) {
        // Db方式
        $db     =   Db::getInstance(self::C('RBAC_DB_DSN'));
        $table = array('role'=>self::C('RBAC_ROLE_TABLE'),'user'=>self::C('RBAC_USER_TABLE'),'access'=>self::C('RBAC_ACCESS_TABLE'));
        $sql    =   "select access.node_id from ".
                    $table['role']." as role,".
                    $table['user']." as user,".
                    $table['access']." as access ".
                    "where user.user_id='{$authId}' and user.role_id=role.id and ( access.role_id=role.id  or (access.role_id=role.pid and role.pid!=0 ) ) and role.status=1 and  access.module='{$module}' and access.status=1";
        $rs =   $db->query($sql);
        $access	=	array();
        foreach ($rs as $node){
            $access[]	=	$node['node_id'];
        }
		return $access;
	}
}