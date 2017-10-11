<?php
/**
 +------------------------------------------------------------------------------
 * DaigouCMS 代购建站系统(淘宝版)
 * 
 * 	站点管理模块
 +------------------------------------------------------------------------------ 
 * @copyright 上海子凌网络科技有限公司˾
 * @author    stone@ulowi.com
 * @version   1.0
 +------------------------------------------------------------------------------
 */

import('ORG.Util.Page');
class SiteAction extends Action  {

	//------------------------------------------------------------------------------------------------
	function _initialize(){
		Session::set(c('RETURN_URL'),MODULE_NAME.','.ACTION_NAME);

		//类别
		if(S('SiteCategoryList')){
			$CategoryList = S('SiteCategoryList');
		}else{
			$CategoryList = M('SiteType')->where('status=1')->select();
			S('SiteCategoryList');
		}
		$this->assign('CategoryList',$CategoryList);

		//名站导航
		if(S('SiteRecommentSite')){
			$DataList = S('SiteRecommentSite');
		}else{
			$DataList = M('Site')->where('recomment=1 AND status=1')->limit('16')->order('sort')->select();
			S('SiteRecommentSite',$DataList);
		}
		$this->assign('recomment_site',$DataList);

		//关注网店
		if(S('SiteShopList')){
			$ShopList = S('SiteShopList');
		}else{
			$ShopList = M('Shop')->where('status=1')->limit('10')->order('sort')->select();
			S('SiteShopList',$ShopList);
		}
		$this->assign('ShopList',$ShopList);

		//统计未读短信
		$memberId	   = intval(Session::get(C('MEMBER_AUTH_KEY')));
		if(!empty($memberId)){
			$unrd_count = M('Notice')->where('user_id='.$memberId.' AND tag=0')->count();//未读短信
			Session::set('unrd_msg_count',$unrd_count);
		}
	}

	//------------------------------------------------------------------------------------------------
	public function index(){
		$DAO = M('Site');
		$count =  $DAO->where('status=1')->count();
		$p = new Page($count,36);
		$page = $p->show();

		$SiteList = $DAO->where('status=1')->limit($p->firstRow.','.$p->listRows)->order('sort')->select();
		$this->assign('SiteList',$SiteList);
		$this->assign('page',$page);
		$this->display();
	}

	//------------------------------------------------------------------------------------------------
	//加载指定类别网站列表
	public function s(){
		$c = $_GET['c'];
		if(empty($c)){
			$sql = "status=1";
		}else{
			$sql = "status=1 AND category=$c";
		}
		$DAO = D('Site');
		$count = $DAO->where($sql)->count();
		$p = new Page($count,36);
		$page = $p->show();

		$DataList = $DAO->where($sql)->limit($p->firstRow.','.$p->listRows)->order('last_update desc')->select();
		$this->assign('SiteList',$DataList);
		$this->assign('page',$page);
		$this->assign('typ',$c);
		$this->display('index');
	}

	//------------------------------------------------------------------------------------------------
	public function go(){
		$id = $_GET['i'];
		$DAO = M('Site');
		$entity = $DAO->where("id=$id")->find();
		if($entity){
			if(strlen($entity['unurl']) > 0){
				$this->assign('go_url',$entity['unurl']);
			}else{
				$this->assign('go_url',$entity['url']);
			}
			$this->display('loading');
		}else {
			$this->redirect('index');
		}
	}

	//------------------------------------------------------------------------------------------------
	//点击跟踪统计
	public function trace(){
		$result = '0';
		$id = $_GET['id'];
		if(!empty($id)){
			$DAO = new Model();
			$DAO->execute("update Site set clicks=clicks+1 where id=$id");
			$result = '1';
		}
		echo  $result;
	}

	//------------------------------------------------------------------------------------------------
	Public function _empty(){
		$this->redirect('index');
	}
}
?>