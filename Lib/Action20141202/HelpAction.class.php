<?php
/**
 +------------------------------------------------------------------------------
 * DaigouCMS 代购建站系统(淘宝版)
 *
 * 帮助中心
 +------------------------------------------------------------------------------
 * @category   ulowi
 * @author    	soitun <stone@zline.net.cn>
 * @copyright 上海子凌网络科技有限公司
 * @license   	http://www.zline.net.cn/license-agreement.html
 * @link      		http://www.zline.net.cn/
 +------------------------------------------------------------------------------
 */

import('ORG.Util.Page');
class HelpAction extends Action  {

	//------------------------------------------------------------------------------------------------
	//加载帮助类别
	function _initialize(){
		Session::set(c('RETURN_URL'),MODULE_NAME.','.ACTION_NAME);
		if(S('HelpCategoryList')){
			$CategoryList = S('HelpCategoryList');
		}else{
			$DAO = M('HelpType');
			$CategoryList = $DAO->where('status=1 AND caption !='."'网站介绍' AND caption !='" ."网站新闻' AND caption != '". "页脚帮助'  AND caption != '". "购物咨讯'" )->order('parent_id,sort')->select();
			$CategoryList = list_to_tree($CategoryList,'id','parent_id');
			S('HelpCategoryList',$CategoryList);
		}
		$this->assign('CategoryList',$CategoryList);

		//统计未读短信
		$memberId	   = intval(Session::get(C('MEMBER_AUTH_KEY')));
		if(!empty($memberId)){

			$NoticeDAO = M('Notice');
			$unrd_count = $NoticeDAO->where('user_id='.$memberId.' AND tag=0')->count();//未读短信
			Session::set('unrd_msg_count',$unrd_count);
		}
	}

	//------------------------------------------------------------------------------------------------
	public function index(){
		$DAO = M('Help');
		if(S('HelpfaqList')){
			$faqList = S('HelpfaqList');
		}else{			
			$faqList = $DAO->where('status=1 AND category_id=11')->limit('5')->order('sort')->select();
			S('HelpfaqList',$faqList);
		}
		$this->assign('faqList',$faqList);

		if(S('HelpfreightList')){
			$freightList = S('HelpfreightList');
		}else{
			$freightList = $DAO->where('status=1 AND category_id=16')->limit('5')->order('sort')->select();
			S('HelpfreightList',$freightList);
		}
		$this->assign('freightList',$freightList);
		$this->display();
	}

	//------------------------------------------------------------------------------------------------
	public function detail(){
		$id = trim($_GET['id']);
		if(!empty($id)){
			$DAO = D('Help');
			$entity = $DAO->where("id=$id")->find();
			$relayList = $DAO->where("id<>$id AND category_id=".$entity['category_id'])->limit('3')->order('rand()')->select();
			$this->assign('entity',$entity);
			$this->assign('relayList',$relayList);
			$this->assign('title',$entity['title']);
			$this->display();
		}else{
			$this->redirect('index');
		}
	}

	//------------------------------------------------------------------------------------------------
	//指定类别下的帮助列表
	public function lst(){
		$cid = trim($_GET['cid']);
		if(!empty($cid)){
			$DAO = M('Help');
			$count = $DAO->where("status=1 AND category_id=$cid")->count();
			$p = new Page($count,C('NUM_PER_PAGE'));
			$page = $p->show();

			$DataList = $DAO->where('category_id='.$cid)->limit($p->firstRow.','.$p->listRows)->order('sort')->select();
			$this->assign('DataList',$DataList);
			$this->assign('page',$page);
			$category = $this->getCategory($cid);
			$this->assign('category',$category);
			$this->display();
		}else{
			$this->redirect('index');
		}
	}

	//------------------------------------------------------------------------------------------------
	//返回类别
	private function getCategory($cid){
		$result = '';
		$DAO = M('HelpType');
		$entity = $DAO->where("id=$cid")->find();
		if($entity){
			$result = $entity['caption'];
			$prent = $DAO->where("id=".$entity['parent_id'])->find();
			$result = $prent['caption'] . ' > ' . $result;
		}
		return $result;
	}

	//------------------------------------------------------------------------------------------------
	Public function _empty(){
		$this->redirect('index');
	}
}
?>