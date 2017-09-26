<?php

/**
 * name
 *
 * @param array 
 * @param obj 
 * @return array 
 */

namespace Core\Lib\Util;

class RequestCurl {

    protected static $instance;

    /**
     *
     * @return self
     */
    public static function instance() {
        if (!static::$instance) {
            static::$instance = new static;
        }
        return static::$instance;
    }

    function httpGetRequest($url, $data, $secret) {
        $reqData = array(
            'req_data' => json_encode($data), 'sign' => md5(json_encode($data) . $secret)
        );


        $ch = curl_init($url . "?" . http_build_query($reqData));
        // 缺少这个头会导致提交失败.
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-type: text/xml; charset=utf-8"));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

    /**
     * 发送短信验证吗
     *
     * @param unknown $phone int
     * @param unknown $msg   string
     */
    function sendMessage($phone, $msg) {
        $data = array(
            'toPhone' => $phone,
            'content' => $msg
        );
        $smsUrl = C('sms_url');
        $smsSecret = C('sms_secret');

        $result = self::httpGetRequest($smsUrl, $data, $smsSecret);
        //是否默认发送成功
//		if(!C('is_return')){
//			$result = true;
//		}
        return json_encode($result);
    }

    /**
     *
     * @param unknown $phone 注册的手机号
     */
    function registerPhone($phone) {
        $data = array('accountName' => "$phone");
        $regUrl = C('reg_url');
        $regSecret = C('reg_secret');
        $result = self::httpGetRequest($regUrl, $data, $regSecret);
        //是否默认发送成功
        if (!C('is_return')) {
            $result = true;
        }

        return $result;
    }

}
