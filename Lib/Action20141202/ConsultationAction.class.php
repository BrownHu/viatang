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

class ConsultationAction extends BaseAction {
	
	//------------------------------------------------------------------------------------------------
	public function index() {
		$this->redirect ( 'My/consultation' );
	}
	
	//------------------------------------------------------------------------------------------------
	public function add() {
		if ($this->user) {
			if ($_GET ['oid']) {
				$oid = $_GET ['oid'];
			}
			if ($_GET ['pid']) {
				$pid = $_GET ['pid'];
			}
			if ($oid && $pid) {
				$this->assign ( 'note_caption', L("consulation_title") );
				$this->assign ( 'pid', $pid );
			}
			$this->display ();
		}
	}
	
	//------------------------------------------------------------------------------------------------
	public function ordadd() {
		if ($this->user) {
			if ($_GET ['oid']) {
				$oid = $_GET ['oid'];
			}
			if ($_GET ['pid']) {
				$pid = $_GET ['pid'];
			}
			
			if ($_GET ['bid']) {
				$bid = $_GET ['bid'];
			}
			if (!empty($oid) && !empty($pid)  && !empty($bid)) {
				$this->assign ( 'note_caption', L("consulation_title") );
				$this->assign ( 'pid', $pid );
				$this->assign ( 'bid', $bid );
			}
			$this->display ();
		}
	}
	
	//------------------------------------------------------------------------------------------------
	public function commit() {
		if ($this->user) {
			$id = $_POST ['id'];
			$pid = $_POST ['pid'];
			$bid = $_POST['bid'];
			$type = $_POST['type'];
			
			$data ['user_id'] = $this->user ['id'];
			$data ['user_name'] = $this->user ['login_name'];
			$data ['title'] = trim ( $_POST ['help_title'] );
			$data ['content'] = trim ( $_POST ['help_content'] );
			$data ['ip'] = get_client_ip ();
			$data ['create_at'] = time ();
			$data ['status'] = '0';
			$data ['customer_reply_tag'] = 1;
			if(!empty($pid)){
				$data ['product_id'] = $pid;
			}
			
			if(!empty($type)){
				$data ['type'] = $type;
			}
			if(!empty($bid)){
				$data ['admin_id'] = $bid;
			}
			
			$DAO = M ( 'Consultation' );
			if (empty ( $id )) {
				$DAO->data ( $data )->add ();
			} else {
				$DAO->where ( "id=$id" )->save ( $data );
			}
			if (! empty ( $pid )) {
				$DAO = new Model ();
				$DAO->execute ( 'UPDATE product SET tip_tag=1 where id=' . $pid );
			}
			$this->display ( 'Public/result' );
		}
	}
	
	//------------------------------------------------------------------------------------------------
	// 回复
	public function reply() {
		if ($this->user) {
			$cid = trim ( $_POST ['cid'] );
			$content = trim ( $_POST ['content'] );
			if ((strlen ( trim ( $content ) ) > 0) && ($cid > 0)) {
				$ConsulationDAO = M ( 'Consultation' );
				$entity = $ConsulationDAO->where ( "id=$cid" )->find ();
				$entity ['status'] = '0';
				$entity ['customer_reply_tag'] = 1;
				$entity ['reply_time'] = time ();
				$ConsulationDAO->where ( "id=$cid" )->save ( $entity ); // 更新为未处理
				
				$data ['consultation_id'] = $cid;
				$data ['content'] = $content;
				$data ['user_id'] = $this->user ['id'];
				$data ['user_name'] = $this->user ['login_name'];
				$data ['user_type'] = 1;
				$data ['attachment'] = '';
				$data ['ip'] = get_client_ip ();
				$data ['create_at'] = time ();
				
				M ( 'ConsultationReply' )->data ( $data )->add ();
				Session::set ( 'consultation_id', $cid );
			}
			$this->redirect ( 'detail' );
		}
	}
	
	//------------------------------------------------------------------------------------------------
	public function del() {
		$id = trim ( $_GET ['id'] );
		if ($this->user && $id) {
			M ( 'Consultation' )->where ( "id=$id AND user_id=" . $this->user ['id'] )->delete ();
		}
		$this->redirect ( 'My/consultation' );
	}
	
	//------------------------------------------------------------------------------------------------
	public function detail() {
		$id = trim ( $_GET ['id'] );
		$cid = Session::get ( 'consultation_id' );
		$id = ($id && (strlen ( $id ) > 0)) ? $id : $cid;
		if ($this->user && $id) {
			$DAO = M ( 'Consultation' );
			$entity = $DAO->where ( "id=$id AND user_id=" . $this->user ['id'] )->find ();
			if ($entity) {
				$entity ['admin_reply_tag'] = 0;
				$DAO->where ( "id=$id AND user_id=" . $this->user ['id'] )->save ( $entity );
			}
			//加载商品详情
			$item = M('Product')->where("id=".$entity['product_id'])->find();
			$this->assign('product',$item);
			
			$DataList = M ( 'ConsultationReply' )->where ( 'consultation_id=' . $entity ['id'] )->order ( 'create_at asc' )->select ();
			$this->assign ( 'entity', $entity );
			$this->assign ( 'DataList', $DataList );
		}
		$this->display ();
	}
	
	//------------------------------------------------------------------------------------------------
	Public function _empty() {
		$this->redirect ( 'My/consultation' );
	}
}
?>