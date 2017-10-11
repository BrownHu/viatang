<?php
/**
 +------------------------------------------------------------------------------
 * DaigouCMS 代购建站系统(淘宝版)
 *
 * 政策法规
 +------------------------------------------------------------------------------
 * @category   ulowi
 * @author    	soitun <stone@zline.net.cn>
 * @copyright 上海子凌网络科技有限公司
 * @license   	http://www.zline.net.cn/license-agreement.html
 * @link      		http://www.zline.net.cn/
 +------------------------------------------------------------------------------
 */

class FaguiAction extends Action{
	
	//------------------------------------------------------------------------------------------------
	public function index(){
		$this->display();
	}

	//------------------------------------------------------------------------------------------------
	public function topic(){
		$tpl = trim($_REQUEST['c']);
		$tpl = (!empty($tpl))?$tpl:'index';
		$this->display($tpl);
	}
	
	//------------------------------------------------------------------------------------------------
	Public function _empty() {
		$this->redirect ( 'index' );
	}
}
?>