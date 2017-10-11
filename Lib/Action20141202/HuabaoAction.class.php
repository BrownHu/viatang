<?php
/**
 +------------------------------------------------------------------------------
 * DaigouCMS 代购建站系统(淘宝版)
 * 
 * 随便逛逛
 +------------------------------------------------------------------------------
 * @category   ulowi
 * @author    	soitun <stone@zline.net.cn>
 * @copyright 上海子凌网络科技有限公司 
 * @license   	http://www.zline.net.cn/license-agreement.html 
 * @link      		http://www.zline.net.cn/
 +------------------------------------------------------------------------------
 */

import ( 'ORG.Util.Page' );
class HuabaoAction extends Action {
	
	//------------------------------------------------------------------------------------------------
	public function index() {
		$this->display ();
	}
	
	//------------------------------------------------------------------------------------------------
	public function _empty() {
		$this->redirect ( 'index' );
	}
}
?>