<?php
/**
 +------------------------------------------------------------------------------
 * DaigouCMS 代购建站系统(淘宝版)
 *
 * 新闻
 +------------------------------------------------------------------------------
 * @category   ulowi
 * @author    	soitun <stone@zline.net.cn>
 * @copyright 上海子凌网络科技有限公司
 * @license   	http://www.zline.net.cn/license-agreement.html
 * @link      		http://www.zline.net.cn/
 +------------------------------------------------------------------------------
 */


class NewsAction extends HomeAction  {
	
	function _initialize(){
		parent::_initialize();
		Session::set(c('RETURN_URL'),MODULE_NAME.','.ACTION_NAME);
		//常见问题
		$HelpList = M('Help')->where('category_id=11')->limit('6')->order('sort')->select();
		$this->assign('HelpList',$HelpList);
	}

	//------------------------------------------------------------------------------------------------
	public function index(){
		$this->redirect('detail');
	}

	//------------------------------------------------------------------------------------------------
	public function detail(){
		$id = trim($_GET['id']);
		if(!empty($id)){
			$entity = M('Help')->where("id=$id")->find();
			$this->assign('entity',$entity);
			$this->assign('title',$entity['title']);
			$this->display();
		}else{
			$this->redirect('Index/index.shtml');
		}
	}

	//------------------------------------------------------------------------------------------------
	Public function _empty(){
		$this->redirect('index');
	}
}
?>