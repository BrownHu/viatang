<?php
/**
 +------------------------------------------------------------------------------
 * DaigouCMS 代购建站系统(淘宝版)
 * 
 * 购物资讯
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
		$this->assign ('title','购物咨询-专为海外华人、留学生提供淘宝代购平台—viatang.com');
	    $this->assign ('keywords','代购，代购网，代购推荐，购物咨询，商品咨询，淘宝购物，代购中国商品，淘宝代购，服装代购，图书代购，鞋包代购，生活用品代购');
        $this->assign ('description','唯唐代购-专海外华人、留学生提供一站式代购中国商品，多件商品集中打包配送至海外，国际运费最低3折起');
		$this->_list('',' id desc',10);
		$this->display();
	}	
}
?>