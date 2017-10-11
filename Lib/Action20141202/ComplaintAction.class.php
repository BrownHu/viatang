<?php
/**
 +------------------------------------------------------------------------------
 * DaigouCMS 代购建站系统(淘宝版)
 *
 * 我的咨询
 +------------------------------------------------------------------------------
 * @category   ulowi
 * @author    	soitun <stone@zline.net.cn>
 * @copyright 上海子凌网络科技有限公司
 * @license   	http://www.zline.net.cn/license-agreement.html
 * @link      		http://www.zline.net.cn/
 +------------------------------------------------------------------------------
 */

class ComplaintAction extends BaseAction {
	
	//------------------------------------------------------------------------------------------------
	function _initialize() {
		parent::_initialize();
		$this->dao =   M ( 'Complaint' );
	}
	
	//------------------------------------------------------------------------------------------------
	public function index() {
		$this->redirect ( 'My/complaint' );
	}
	
	//------------------------------------------------------------------------------------------------
	public function commit() {
		if ($this->user) {
			$data ['user_id'] = $this->user ['id'];
			$data ['user_name'] = $this->user ['login_name'];
			$data ['category'] = $_POST ['c_category'];
			$data ['title'] = trim ( $_POST ['c_title'] );
			$data ['content'] = trim ( $_POST ['c_content'] );
			$data ['create_at'] = time ();
			$data ['status'] = 0;
			
			M ( 'Complaint' )->data ( $data )->add ();
			$this->assign('waitSecond',5);
			$this->assign('msgTitle',L('complaint_form_title'));
			$this->assign('jumpUrl','/My/complaint');
			$this->success(L('complaint_submit_succ'));
		}
	}
}
?>