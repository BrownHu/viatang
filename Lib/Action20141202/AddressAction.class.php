<?php
/**
 +------------------------------------------------------------------------------
 * DaigouCMS 代购建站系统(淘宝版)
 * 
 * 常用收货地址
 +------------------------------------------------------------------------------
 * @category   ulowi
 * @author    	soitun <stone@zline.net.cn>
 * @copyright 上海子凌网络科技有限公司 
 * @license   	http://www.zline.net.cn/license-agreement.html 
 * @link      		http://www.zline.net.cn/
 +------------------------------------------------------------------------------
 */

class AddressAction extends BaseAction  {
	
	//------------------------------------------------------------------------------------------------
	function _initialize() {
		parent::_initialize();
		$this->dao = M('Address');
	}
	
	//------------------------------------------------------------------------------------------------
	public function index(){
		$this->redirect('My/address');
	}
	
	//------------------------------------------------------------------------------------------------
	public function showAdd(){
		$this->loadZone();
		$this->display('add');
	}

	//------------------------------------------------------------------------------------------------
	public function update(){
		if($this->user && $this->dao && $_POST){			
			$ret = $this->_update();
			$this->display('Public/result');
		}	
	}
	
	//------------------------------------------------------------------------------------------------
	//加载
	public function load(){
		if($this->user){
			$this->loadZone();
			$this->_load(' and user_id='.$this->user['id']);
			$this->display('edit');
		}	
	}

	//------------------------------------------------------------------------------------------------
	//删除
	public function del(){
		$this->_delete();
		$this->redirect('My/address');
	}

	//------------------------------------------------------------------------------------------------
	//设为默认
	public function setdefault(){
		if(isset($_GET['id']) && is_numeric(trim($_GET['id'])) && $this->dao && $this->user){
			$this->dao->execute('update '. $this->dao->getTableName(). ' set default=1 where id='.intval(trim($_GET['id'])). ' and user_id='.$this->user['id']);
		}
		$this->redirect('My/address');
	}
	
	//------------------------------------------------------------------------------------------------
	private function loadZone(){
		$list= M('DeliverZone')->where('status = 1')->order('sort')->select();
		$this->assign('CountryList',$list);
	}
}
?>