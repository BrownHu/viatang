<?php
/**
 +------------------------------------------------------------------------------
 * DaigouCMS 代购建站系统(淘宝版)
 *
 * 系统通知
 +------------------------------------------------------------------------------
 * @category   	ulowi
 * @author    	soitun <stone@zline.net.cn>
 * @copyright 	上海子凌网络科技有限公司
 * @license   	http://www.zline.net.cn/license-agreement.html
 * @link      	http://www.zline.net.cn/
 +------------------------------------------------------------------------------
 */

class NoticeAction extends BaseAction {
	
	//------------------------------------------------------------------------------------------------
	public function index() {
		$this->redirect ( 'My/notice' );
	}
	
	//------------------------------------------------------------------------------------------------
	public function del() {
		$id = $_GET ['id'];
		M ( 'Notice' )->where ( "id=$id" )->delete ();
		$this->redirect ( 'My/notice' );
	}
	
	//------------------------------------------------------------------------------------------------
	public function detail() {
		if ($this->user) {
			$id = $_GET ['id'];
			$DAO = M ( 'Notice' );
			$entity = $DAO->where ( "id=$id" )->find ();
			if ($entity) {
				$entity ['tag'] = 1;
				$DAO->where ( "id=$id" )->save ( $entity );
				$this->assign ( 'entity', $entity );
			}
		}
		$this->display ();
	}
	
	//------------------------------------------------------------------------------------------------
	public function doread(){
		if ($this->user) {
			$id = $_GET ['id'];
			$data ['tag'] = 1;
			 M ( 'Notice' )->where ( "id=$id AND user_id=".$this->user['id'] )->save ($data);
		}
		echo '1';
	}	
	
	//------------------------------------------------------------------------------------------------
	public function unrd() {
		$count = 0;
		if ($this->user) {
			$count = M ( 'Notice' )->where("tag=0 AND user_id=".$this->user['id'])->count();			
		}
		echo $count;
	}
	
	//------------------------------------------------------------------------------------------------
	Public function _empty() {
		$this->redirect ( 'My/notice' );
	}
}