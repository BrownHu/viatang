<?php
/**
 +------------------------------------------------------------------------------
 * DaigouCMS 代购建站系统(淘宝版)
 *
 * 订单提醒
 +------------------------------------------------------------------------------
 * @category   ulowi
 * @author    	soitun <stone@zline.net.cn>
 * @copyright 上海子凌网络科技有限公司
 * @license   	http://www.zline.net.cn/license-agreement.html
 * @link      		http://www.zline.net.cn/
 +------------------------------------------------------------------------------
 */


class OrdertipAction extends BaseAction {
	
	//------------------------------------------------------------------------------------------------
	public function index() {
		$this->redirect ( 'My/ordertip' );
	}
	
	//------------------------------------------------------------------------------------------------
	public function commit() {
		if ($this->user) {			
			$data ['user_id'] =	$this->user['id'];
			$data ['user_name'] =	$this->user['login_name'];
			$data ['clz'] =   !empty( $_POST ['clz'] ) ? floatval($_POST ['clz']) : 0;
			$data ['ydg'] =   !empty( $_POST ['ydg'] ) ? floatval($_POST ['ydg']) : 0;
			$data ['ydh'] =   !empty( $_POST ['ydh'] ) ? floatval($_POST ['ydh']) : 0;
			$data ['wh'] =    !empty( $_POST ['wh'] ) ? floatval($_POST ['wh']) : 0;
			$data ['zsqh'] =  !empty( $_POST ['zsqh'] ) ? floatval($_POST ['zsqh']) : 0;
			$data ['wx'] =    !empty( $_POST ['wx'] ) ? floatval($_POST ['wx']) : 0;
			$data ['thhclz'] = !empty( $_POST ['thhclz'] ) ? floatval($_POST ['thhclz']) : 0;
			$data ['yth'] =   !empty( $_POST ['yth'] ) ? floatval($_POST ['yth']) : 0;
			$data ['yrk'] =   !empty( $_POST ['yrk'] ) ? floatval($_POST ['yrk']) : 0;
			$data ['ejwc'] =  !empty( $_POST ['ejwc'] ) ? floatval($_POST ['ejwc']) : 0;
			$data ['cqdd'] =  !empty( $_POST ['cqdd'] ) ? floatval($_POST ['cqdd']) : 0;
			$data ['zfdd'] =  !empty( $_POST ['zfdd'] ) ? floatval($_POST ['zfdd']) : 0;
			
			$DAO = M ('OrderTip' );
			$u_id = $this->user ['id'];
			$entity = $DAO->where ( 'user_id=' . $u_id )->find ();
			if ($entity) {
				$DAO->where ( 'user_id=' . $u_id )->save ( $data );			
			} else {
				$DAO->data ( $data )->add ();
			}
			
			$this->assign ( 'waitSecond', 5 );
			$this->assign ( 'jumpUrl', '/My/ordertip.shtml' );
			$this->success ( L('order_tip_succ') );
		} else {
			$this->assign ( 'waitSecond', 5 );
			$this->assign ( 'jumpUrl', '/Index/index.shtml' );
			$this->error ( L('order_tip_no_pri') );
		}
	}

}

?>