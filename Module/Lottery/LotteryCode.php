<?php
namespace Module\Lottery;


class LotteryCode
{
	const PRIZE_NOT = 1;//奖品不存在
	const PRIZE_OVER = 2;//奖品已经抽完
	const PRIZE_MAX_NUM = 3;//奖品达到最大抽奖次数
	const NOT_LOTTERY_OP = 4;//没有抽奖机会
	const DEDU_OP_FAIL = 5;//扣除抽奖机会失败
}
?>