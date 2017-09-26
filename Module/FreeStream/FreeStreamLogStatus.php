<?php
namespace Module\FreeStream;

use \Exception as Exception;

class FreeStreamLogStatus
{

    public $stream; // 流量包
    private $streamLog; // 流量领取日子
    private $fId; // 流量ID
    private $bId; // 板块ID
    private $userId; // 用户ID

                     
    // 构造
    public function __construct($fId, $bId, $userId)
    {
        $this->fId = $fId; // 流量ID
        $this->bId = $bId; // 板块ID
        $this->userId = $userId; // 用户ID
        $this->getStreamRow(); // 获取流量
    }
    
    // 判断是否领取未开始
    public function getNotstart()
    {
        $row = $this->stream; // 流量
        if (empty($row)) {
            return true; // 已结束
        }
        $time = $this->getCurrentTime(); // 当前时间
        if ($row['start_time'] > $time && ! empty($row['start_time'])) {
            return true; // 已结束
        }
        return false;
    }
    
    // 判断领取是否已经结算
    public function getOver()
    {
        $time = $this->getCurrentTime(); // 当前时间
        if ($this->stream['end_time'] < $time && ! empty($this->stream['end_time'])) {
            return true; // 未开始
        } else {
            return false;
        }
    }
    
    // 判断是否可以领取
    public function getStart()
    {
        if (empty($this->userId)) {
            return true;
        }
        return true;
    }
    
    // 判断是否已经领取
    public function getReceive()
    {
        if (empty($this->userId)) {
            return true;
        }

        if ($this->getCurrentMonthNum()>0) {
            return false;
        } else {
            return true;
        }
    }
    
    // 获取当前时间
    private function getCurrentTime()
    {
        return time();
    }
    
    // 获取流量包
    private function getStreamRow()
    {
        return $this->stream = $this->getFreeStreamModel()->getOne(array(
            'id' => $this->fId
        ));
    }
	


    // 获取当月可领取次数
    private function getCurrentMonthNum()
    {
        if (($this->stream['number'] - $this->orderCountNum()) >= $this->getMonthMaxNum()) {
            return $this->getMonthMaxNum() - $this->alreadyMonthNum();
        } else {
            return $this->stream['number'] - $this->orderCountNum();
        }
    }
    
    // 每月最大订购次数
    private function getMonthMaxNum()
    {
        if (empty($this->stream['month'])) {
            return false;
        }
        $monthNum = $this->getAvgMonthNum();
        if (($this->getOrderMonthNum() + 1) == $this->stream['month']) {
            $monthNum = $this->stream['number'] - $this->getOrderMonthNum() * $monthNum;
        }
        return $monthNum;
    }
    
    // 获取平均订购次数
    private function getAvgMonthNum()
    {
        if (empty($this->stream['month'])) {
            return false;
        }
        return floor($this->stream['number'] / $this->stream['month']);
    }
    
    // 当月已经订购的次数
    private function alreadyMonthNum()
    {
        if (empty($this->userId)) {
            return false;
        }
        $time = date('Ym', $this->getCurrentTime());
        return $this->getFreeStreamLogModel()->orderMonthCount($time, $this->userId, $this->fId, $this->bId);
    }
    
    // 获取指定流量包订购了几个月 不含当前月
    private function getOrderMonthNum()
    {
        if (empty($this->stream['is_con_order'])) {
            return $this->getFreeStreamLogModel()->getOrderMonthNum($this->userId, $this->fId, $this->bId);
        }else{
            // 连续订购
            $monthNum = 0;
            // 获取第一次的领取时间
            $firstTime = $this->getFreeStreamLogModel()->getFirstOrderTime($this->userId, $this->fId, $this->bId);
            if (! empty($firstTime)) {
                $monthNum = $this->getMonthNum($firstTime, $this->getCurrentTime());
            }
            return $monthNum;
        }
    }
    
    // 已经订购的总次数
    private function orderCountNum()
    {
        // 不是连续订购
        if (empty($this->stream['is_con_order'])) {
            // 指定流量包 总领取次数
            $count = $this->getOrderMonthNum() * $this->getAvgMonthNum();
            if ($count >= $this->stream['number']) {
                return $this->stream['number'];
            } else {
                return $count - $this->alreadyMonthNum();
            }
        }
        return $this->getOrderMonthNum() * $this->getAvgMonthNum() + $this->alreadyMonthNum();
    }
    
    // 统计月差
    private function getMonthNum($date1_stamp, $date2_stamp)
    {
        list ($date_1['y'], $date_1['m']) = explode("-", date('Y-m', $date1_stamp));
        list ($date_2['y'], $date_2['m']) = explode("-", date('Y-m', $date2_stamp));
        $month = abs($date_1['y'] - $date_2['y']) * 12 + $date_2['m'] - $date_1['m'];
        return $month;
    }
    
    // 获取免费流量包model
    private function getFreeStreamModel()
    {
        return \Model\FreeStream\FreeStream::instance();
    }
    
    // 获取免费流量包日子model
    private function getFreeStreamLogModel()
    {
        return \Model\FreeStream\FreeStreamLog::instance();
    }
}
?>