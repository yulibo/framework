<?php
namespace Core\Lib\Tools;

/**
 * 类名: mobile
 * 描述: 手机信息类
 * 其他: 偶然 编写
 */
class Mobile
{

    /**
     * 函数名称: getPhoneNumber
     * 函数功能: 取手机号
     * 输入参数: none
     * 函数返回值: 成功返回号码，失败返回false
     * 其它说明: 说明
     */
    public static function getPhoneNumber()
    {
        if (isset($_SERVER['HTTP_X_NETWORK_INFO'])) {
            $str1 = $_SERVER['HTTP_X_NETWORK_INFO'];
            $getstr1 = preg_replace('/(.*,)(11[d])(,.*)/i', '', $str1);
            return $getstr1;
        } elseif (isset($_SERVER['HTTP_X_UP_CALLING_LINE_ID'])) {
            $getstr2 = $_SERVER['HTTP_X_UP_CALLING_LINE_ID'];
            $getstr2 = substr($getstr2,-11);
            return $getstr2;
        } elseif (isset($_SERVER['HTTP_X_UP_SUBNO'])) {
            $str3 = $_SERVER['HTTP_X_UP_SUBNO'];
            $getstr3 = preg_replace('/(.*)(11[d])(.*)/i', '', $str3);
            return $getstr3;
        } elseif (isset($_SERVER['DEVICEID'])) {
            return $_SERVER['DEVICEID'];
        } else {
            return false;
        }
    }


    /**
     * 函数名称: getUA
     * 函数功能: 取UA
     * 输入参数: none
     * 函数返回值: 成功返回号码，失败返回false
     * 其它说明: 说明
     */
    public static function getUA()
    {
        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            return $_SERVER['HTTP_USER_AGENT'];
        } else {
            return false;
        }
    }

    /**
     * 函数名称: getPhoneType
     * 函数功能: 取得手机类型
     * 输入参数: none
     * 函数返回值: 成功返回string，失败返回false
     * 其它说明: 说明
     */
    public static function getPhoneType()
    {
        $ua = $this->getUA();
        if ($ua != false) {
            $str = explode(' ', $ua);
            return $str[0];
        } else {
            return false;
        }
    }

    /**
     * 函数名称: isOpera
     * 函数功能: 判断是否是opera
     * 输入参数: none
     * 函数返回值: 成功返回string，失败返回false
     * 其它说明: 说明
     */
    public static function isOpera()
    {
        $uainfo = $this->getUA();
        if (preg_match('/.*Opera.*/i', $uainfo)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 函数名称: isM3gate
     * 函数功能: 判断是否是m3gate
     * 输入参数: none
     * 函数返回值: 成功返回string，失败返回false
     * 其它说明: 说明
     */
    public static function isM3gate()
    {
        $uainfo = $this->getUA();
        if (preg_match('/M3Gate/i', $uainfo)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 函数名称: getHttpAccept
     * 函数功能: 取得HA
     * 输入参数: none
     * 函数返回值: 成功返回string，失败返回false
     * 其它说明: 说明
     */
    public static function getHttpAccept()
    {
        if (isset($_SERVER['HTTP_ACCEPT'])) {
            return $_SERVER['HTTP_ACCEPT'];
        } else {
            return false;
        }
    }

    /**
     * 函数名称: getIP
     * 函数功能: 取得手机IP
     * 输入参数: none
     * 函数返回值: 成功返回string
     * 其它说明: 说明
     */
    public static function getIP()
    {
        $ip = getenv('REMOTE_ADDR');
        $ip_ = getenv('HTTP_X_FORWARDED_FOR');
        if (($ip_ != "") && ($ip_ != "unknown")) {
            $ip = $ip_;
        }
        return $ip;
    }
}
