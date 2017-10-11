<?php
/**
 +------------------------------------------------------------------------------
 * DaigouCMS 代购建站系统(淘宝版)
 * 
 * 资讯
 +------------------------------------------------------------------------------
 * @category   ulowi
 * @author    	soitun <stone@zline.net.cn>
 * @copyright 上海子凌网络科技有限公司 
 * @license   	http://www.zline.net.cn/license-agreement.html 
 * @link      		http://www.zline.net.cn/
 +------------------------------------------------------------------------------
 */
class ArticleAction extends HomeAction{

	function _initialize(){
		parent::_initialize();
		$this->dao = M('Article');	
	}
	
	public function index(){
		$this->_list('',' id desc',10);
		$this->display();
	}	
}
?>