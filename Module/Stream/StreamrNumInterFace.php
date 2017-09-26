<?php
namespace Module\Stream;

interface StreamrNumInterFace
{
	
	//获取流量包当月的已订购次数
	public function getOrderStreamMonthNum();
	
	//获取流量包当月的已订购次数
	public function getOrderStreamDayNum();
	
	
	//获取流量包当月的可订购次数
	public function getStreamMonthNum();
	
	//获取流量包当月的可订购次数
	public function getStreamDayNum();
	
	
}

?>