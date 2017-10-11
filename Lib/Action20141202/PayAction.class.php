<?php
/**
 +------------------------------------------------------------------------------
 * DaigouCMS 代购建站系统(淘宝版)
 *
 * 显示充值界面
 +------------------------------------------------------------------------------
 * @category   ulowi
 * @author    	soitun <stone@zline.net.cn>
 * @copyright 上海子凌网络科技有限公司
 * @license   	http://www.zline.net.cn/license-agreement.html
 * @link      		http://www.zline.net.cn/
 +------------------------------------------------------------------------------
 */

class PayAction extends BaseAction {
	
	//------------------------------------------------------------------------------------------------
	public function index() {
		$this->redirect ( 'My/pay' );
	}
	
	//------------------------------------------------------------------------------------------------
	public function paypal() {
		if ($this->user) {
			$this->assign ( 'uid', $this->user ['id'] );
			$this->assign ( 'un', $this->user ['login_name'] );
			
			$DAO = M ( 'FinaceConfig' );
			$entity = $DAO->where ( "item='exchange_rate'" )->find ();
			if ($entity) {
				$this->assign ( 'exchange_rate', $entity ['value'] );
			}
			$this->assign ( 'charge_percent', floatval(C ( 'paypal_handling_charge_percent' ) ));
			$this->assign ( 'charge', floatval(C ( 'paypal_handling_charge' )) );
			$this->display ();
		}
	}
	
	//------------------------------------------------------------------------------------------------
	public function alipay() {
		if ($this->user) {
			$this->assign ( 'uid', $this->user ['id'] );
			$this->assign ( 'un', $this->user ['login_name'] );
			$this->display ();
		}
	}
	
	//------------------------------------------------------------------------------------------------
	public function ips() {
		if ($this->user) {
			$this->assign ( 'uid', $this->user ['id'] );
			$this->assign ( 'un', $this->user ['login_name'] );
			$this->display ();
		}
	}
	
	//------------------------------------------------------------------------------------------------
	public function other() {
		if ($this->user) {
			$today = Date('Y-m-d');
			$this->assign('today',$today);
			$this->display ();
		}
	}
	
	//------------------------------------------------------------------------------------------------
	public function creditcn() {
		if ($this->user) {
			$this->assign ( 'uid', $this->user ['id'] );
			$this->assign ( 'un', $this->user ['login_name'] );
			$this->display ();
		}
		
	}
	
	//------------------------------------------------------------------------------------------------
	Public function _empty() {
		$this->redirect ( 'Pay/paypal' );
	}
}
?>