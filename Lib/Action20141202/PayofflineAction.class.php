<?php
/**
 +------------------------------------------------------------------------------
 * DaigouCMS 代购建站系统(淘宝版)
 * 
 * 线下充值
 +------------------------------------------------------------------------------
 * @category   ulowi
 * @author    	soitun <stone@zline.net.cn>
 * @copyright 上海子凌网络科技有限公司 
 * @license   	http://www.zline.net.cn/license-agreement.html 
 * @link      		http://www.zline.net.cn/
 +------------------------------------------------------------------------------
 */

class PayofflineAction extends BaseAction {
	
	function _initialize() {
		parent::_initialize();
		$this->dao = M('PayOffline');
	}
	
	//------------------------------------------------------------------------------------------------
	public function index() {
		$this->redirect ( 'Pay/other' );
	}
	
	//------------------------------------------------------------------------------------------------
	public function commit() {
		if ($this->user) {
			$type = $_POST['payment_way'];	
			$data['user_id'] = $this->user['id'];
			$data['user_name'] = $this->user['login_name'];		
			$data['email'] = $_POST['email'];
			$data['pay_date'] = $_POST['date'];
			$data['type'] = intval($type);
			
			if(intval($type) == 1){
				$data['amount'] = $_POST['money_bank'];
				$data['currency'] = $_POST['currency_bank'];
				$data['bank'] = $_POST['bank'];
				$data['pay_first_name'] = $_POST['name_bank'];
				$data['pay_last_name'] = $_POST['lastname_bank'];
				$data['receive_bank'] = $_POST['receive_bank'];
			}else if(intval($type) == 2){
				$data['amount'] = $_POST['money_west_union'];
				$data['currency'] = $_POST['currency_west_union'];
				$data['from_country'] = $_POST['country'];
				$data['pay_first_name'] = $_POST['name_west_union'];
				$data['pay_last_name'] = $_POST['lastname_west_union'];
				$data['mtcn'] = $_POST['mtcn'];
			}
			$data['status'] = 0;
			$data['ceate_time'] = time();

			$id = $this->dao->data ( $data )->add ();
			if(!empty($id)){
				$this->assign ( 'jumpUrl', '/My/index.shtml' );
				$this->assign ( 'waitSecond', 10 );
				$this->success(L('payoffline_succ'));
			}else{
				$this->assign ( 'jumpUrl', '/Pay/other.shtml' );
				$this->assign ( 'waitSecond', 10 );
				$this->error(L('payoffline_fail'));
			}
		}else{
			$this->redirect('Public/login');
		}
	}
}
?>