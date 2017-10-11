<?php
/**
 +------------------------------------------------------------------------------
 * DaigouCMS 代购建站系统(淘宝版)
 * 
 * 	好友推荐
 +------------------------------------------------------------------------------ 
 * @copyright 上海子凌网络科技有限公司
 * @author    stone@ulowi.com
 * @version   1.0
 +------------------------------------------------------------------------------
 */

import('ORG.Util.Page');
class SpreadAction extends Action  {
	
	//------------------------------------------------------------------------------------------------
	public function index(){
		$this->redirect('My/spread');
	}

	//------------------------------------------------------------------------------------------------
	//消费记录
	public function consumption(){
		if($this->user){
			$this->assign('spread_url',C('SITE_URL').'/?spreader='.$this->user['id']);
			$DAO = D('FinaceView');
			$count = $DAO->where('id='.$this->user['d'])->count();
			$p = new Page($count,C('NUM_PER_PAGE'));
			$page = $p->show();

			$DataList = $DAO->where("id=".$this->user['id'])->limit($p->firstRow.','.$p->listRows)->select();
			$this->assign('DataList',$DataList);
			$this->assign('page',$page);
		}

		$this->display();
	}

	//------------------------------------------------------------------------------------------------
	//奖历记录
	public function reward(){
		if($this->user){
			$this->assign('spread_url',C('SITE_URL').'/?spreader='.$this->user['id']);
			$DAO = M('FinanceLog');
			$count = $DAO->where('type_id=406 AND user_id='.$this->user['d'])->count();
			$p = new Page($count,C('NUM_PER_PAGE'));
			$page = $p->show();

			$DataList = $DAO->where("type_id=406 AND user_id=".$this->user['id'])->limit($p->firstRow.','.$p->listRows)->select();
			$this->assign('DataList',$DataList);
			$this->assign('page',$page);
		}

		$this->display();
	}
}
?>