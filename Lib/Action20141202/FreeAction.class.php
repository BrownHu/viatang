<?php
/**
 +------------------------------------------------------------------------------
 * DaigouCMS 代购建站系统(淘宝版)
 *
 * 免邮商家
 +------------------------------------------------------------------------------
 * @category   ulowi
 * @author    	soitun <stone@zline.net.cn>
 * @copyright 上海子凌网络科技有限公司
 * @license   	http://www.zline.net.cn/license-agreement.html
 * @link      		http://www.zline.net.cn/
 +------------------------------------------------------------------------------
 */

class FreeAction extends Action {
	
	//------------------------------------------------------------------------------------------------
	function _initialize() {
		Session::set ( C( 'RETURN_URL' ), MODULE_NAME . ',' . ACTION_NAME );
	}
	
	//------------------------------------------------------------------------------------------------
	public function index() {
		$refer_url = Session::get('referer_url');
		if(empty($refer_url)){			
			Session::set ( 'referer_url',$_SERVER ["HTTP_REFERER"] );
		}
		
		$this->display ();
	}
	
	//------------------------------------------------------------------------------------------------
	Public function _empty() {
		$this->redirect ( 'index' );
	}
}
?>