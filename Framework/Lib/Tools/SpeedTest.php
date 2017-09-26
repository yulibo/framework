<?php

/**
 * name
 *
 * @param array
 * @param obj
 * @return array
 */
namespace Core\Lib\Tools;
class SpeedTest
{

    public static $dSpeed = 0; //下载网速初始值
    public static $uSpeed = 0; //上传网速初始值

    //下载
    public static function download($downTime){
        $dKBps = round(500000/$downTime,2);
        self::$dSpeed = $dKBps;
    }

    //上传
    public static function upload($upTime){
        $uKBps = round(500000/$upTime,2);
        self::$uSpeed = $uKBps;
    }

    public static function getTestText(){
        $result='';
        for($i = 0;$i<5000;$i++){
            $result .= '*';
        }
        return $result;
    }

    public static function getTextHtml(){
        $outText = self::getTestText();
        $result = '';
        for($i = 1;$i<100;$i++){
            $result .= '<!--'.$outText."-->\n";
            $result .= "<script type=\"text/javascript\">setDownProgress();</script>\n";
        }
        return $result;
    }
}