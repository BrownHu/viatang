<?php
/**
 +------------------------------------------------------------------------------
 * DaigouCMS 代购建站系统(淘宝版)
 *
 * 商品导购
 +------------------------------------------------------------------------------
 * @category   ulowi
 * @author    	soitun <stone@zline.net.cn>
 * @copyright 上海子凌网络科技有限公司
 * @license   	http://www.zline.net.cn/license-agreement.html
 * @link      		http://www.zline.net.cn/
 +------------------------------------------------------------------------------
 */


class MallAction extends HomeAction {
	
	//------------------------------------------------------------------------------------------------
	function _initialize() {
		parent::_initialize ();
		Session::set ( C ( 'RETURN_URL' ), MODULE_NAME . ',' . ACTION_NAME );
	}
	
	//------------------------------------------------------------------------------------------------
	public function index() {
		$this->assign ( 'title', '代购商品目录, 商品导购, B2C商城商品分类' );
		$this->assign('fixblock','#J_navbar');
		$this->display ();
	}
	
	public function brands(){
		$_list = M ( 'Site' )->limit(0,20)->order('sort asc')->select();
		$this->assign ( 'SiteList', $_list );
		$this->display ();
	}
}

?>