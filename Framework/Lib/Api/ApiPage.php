<?php

namespace Core\Lib\Api;

interface ApiPage{
	
	 /**
     * 格式化列表结果
     * @return array
     */
    function getFormatListResult();
	
	
	/**
     * 获取分页的列表数据
     * @return array
     */
	function getPageResult();
	

    /**
     * 格式化分页列表结果 for web
     * @param int $pageNow 当前页
     * @param int $pageSize 页面条数
     * @param int $pageType 分页风格
     * @return array
     */
    function getPageFormatListResult($pageNow = 1, $pageSize = 10, $pageType = 0);
	
	
	 /**
     * 格式化分页列表结果 for mobile
     * @param int $pageNow 当前页
     * @param int $pageSize 页面条数
     * @return array
     */
    function getListPageMobile($pageNow = 1,$pageSize = 10);
	
}
