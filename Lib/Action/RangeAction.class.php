<?php
/**
 +------------------------------------------------------------------------------
 * DaigouCMS 代购建站系统(淘宝版)
 * 
 * 代购商品范围
 +------------------------------------------------------------------------------
 * @category  上海唐悦信息技术有限公司   
 * @package  
 * @subpackage  
 * @author    viatang <allan@viatang.com>
 * @version   Id
 +------------------------------------------------------------------------------
 */

class RangeAction extends HomeAction {
	
	public function index() {
		$this->assign ('title','代购商品范围说明-为海外华人提供一站式代购淘宝商品平台-viatang.com');
	         $this->assign ('keywords','代购，代购网，淘宝代购，当当代购，亚马逊代购，京东代购，代购中国商品，服装代购，饰品代购，包包代购，图书代购，食品代购，生活用品代购');
                  $this->assign ('description','关于代购商品范围说明，违禁商品代购政策介绍，专为海外华人、留学生提供一站式代购中国商品，不同店铺商品集中打包配送至全球，国际运费最低12元起');
		$this->display ();
	}
	
}

?>