<?php
/**
 +------------------------------------------------------------------------------
 * DaigouCMS 代购建站系统(淘宝版)
 *
 * 在线客服
 +------------------------------------------------------------------------------
 * @category   ulowi
 * @author    	soitun <stone@zline.net.cn>
 * @copyright 上海子凌网络科技有限公司
 * @license   	http://www.zline.net.cn/license-agreement.html
 * @link      		http://www.zline.net.cn/
 +------------------------------------------------------------------------------
 */

class CsrAction extends PublicAction {
	
	//------------------------------------------------------------------------------------------------
	public function index() {
		//常见问题
		if (S ( 'IdxHelpList' )) {
			$HelpList = S ( 'IdxHelpList' );
		} else {
			$HelpList = M ( 'Help' )->where ( 'category_id=11' )->limit ( '9' )->order ( 'sort desc' )->select ();
			S ( 'IdxHelpList', $HelpList );
		}
		
		$now = date ( 'H', time () );
		if (($now > 8) && ($now < 20)) {
			$this->assign ( 'canchat', '1' );
		} else {
			$this->assign ( 'canchat', '0' );
		}
		$this->assign ( 'IdxHelpList', $HelpList );
		$this->display ();
	}
	
	//------------------------------------------------------------------------------------------------
	public function ask() {
		if ($this->user) {
			$this->assign('user_id',$this->user['id']);
		} else {
			Session::set ( c ( 'RETURN_URL' ), MODULE_NAME . '/' . ACTION_NAME );
			$this->redirect ( 'Public/login' );
		}
	}

}
?>