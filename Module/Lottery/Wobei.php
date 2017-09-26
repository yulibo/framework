<?php
namespace Module\Lottery;

//用沃贝抽奖
class Wobei extends Lottery
{
	
	//检查抽奖机会
	protected function checkLuckyDraw(){
		throw new Exception('抽奖机会已经用完');
	}
	
	
	

}
?>