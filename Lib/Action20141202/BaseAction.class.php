<?php
/**
 +------------------------------------------------------------------------------
 * DaigouCMS 代购建站系统(淘宝版)
 * 
 +------------------------------------------------------------------------------
 * @category   ulowi
 * @author    	soitun <stone@zline.net.cn>
 * @copyright 上海子凌网络科技有限公司 
 * @license   	http://www.zline.net.cn/license-agreement.html 
 * @link      		http://www.zline.net.cn/
 +------------------------------------------------------------------------------
 */

class BaseAction extends HomeAction {

	//------------------------------------------------------------------------------------------------
	function _initialize() {
		parent::_initialize();		
		if ($this->user) {	
			$finance = D ( 'Finance' )->finace ( $this->user['id'] );
			if ($finance) {
				$this->assign ( 'balance', $finance ['money'] ); //余额
				$this->assign ( 'rebate', $finance ['rebate'] ); //折扣余额
			}					
		} else { 
			$this->redirect ( 'Public/login' );
		}
	}
	
	//------------------------------------------------------------------------------------------------
	//数据库公共操作开始
	//------------------------------------------------------------------------------------------------
	
	//------------------------------------------------------------------------------------------------
	//新增或更新数据
	protected function _update($where=''){
		if($this->dao && $_POST && $this->user){
			if(isset($_POST['id']) && is_numeric(trim($_POST['id']))){
				$ret = $this->dao->where("id=".intval($_POST['id']). ' and user_id=' .$this->user['id'] . $where)->save($_POST);
			}else{
				$_POST['user_id'] = $this->user['id'];
				$_POST['user_name'] = $this->user['login_name'];
				$ret = $this->dao->data($_POST)->add();
			}
			if(is_numeric($ret) || $ret != false) return true;
		}
		return false;
	}
	
	//------------------------------------------------------------------------------------------------
	// 显示添加/ 新增界面
	protected function add(){
		if($this->dao && $this->user){
			$this->display();
		}
	}
	
	//------------------------------------------------------------------------------------------------
	// 显示编缉界面
	protected function _edit($where=''){
		$this->_load($where);
	}

	//------------------------------------------------------------------------------------------------
	// 删除单条
	protected function _delete($where = ''){
		if($this->dao && $this->user && isset($_REQUEST['id']) && is_numeric(trim($_REQUEST['id']))){
			$ret = $this->dao->where('id='.intval(trim($_REQUEST['id'])). ' and user_id='.$this->user['id']. $where )->delete();
			if(is_numeric($ret) || $ret != false) return true;
		}
		return false;
	}
	
	//------------------------------------------------------------------------------------------------
	// 批量删除
	protected function _deleteMore($where=''){
		if($this->dao && $this->user && isset($_POST['id'])){
			$ids = $_POST['id'];
			if(!is_array($ids)) return false;
			$id_str = implode(',', $ids);
			$ret = $this->dao->where("id in ($id_str) AND user_id=" . $this->user ['id'] .$where )->delete();
			if(is_numeric($ret) || $ret != false) return true;
		}
		return false;
	}
	
	//------------------------------------------------------------------------------------------------
	// 数据库公共操作结束
	//---------------------------------------------------------------------------------------------------
	
	//------------------------------------------------------------------------------------------------
	// 字符串处理
	private function strFilter(){
		load('@/functions');
		foreach($_POST as $k=>$v){
			$r = remove_xss($v);
			$r = stripslashes($v);
			$_POST[$k] = str_replace('\r\n','', mysql_escape_string(htmlspecialchars($r))) ;//对输入进行过滤
		}
	}
}
?>