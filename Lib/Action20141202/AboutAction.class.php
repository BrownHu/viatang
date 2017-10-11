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

class AboutAction extends HomeAction {

	//------------------------------------------------------------------------------------------------
	function _initialize() {
		parent::_initialize();
		$CategoryList = S ( 'AboutCategoryList' );
		if (empty ( $CategoryList)) {
			$CategoryList = M ( 'HelpType' )->where ( 'parent_id=20' )->order ( 'parent_id,sort' )->select ();
			S ( 'AboutCategoryList', $CategoryList );
		}
		$this->assign ( 'CategoryList', $CategoryList );
	}
	
	//------------------------------------------------------------------------------------------------
	public function index() {
		$tpls= array('a0'=>'index','a1'=>'index', 'a2'=>'contact','a3'=>'link','a4'=>'detail','a5'=>'private','a7'=>'partner');
		$id = trim ( $_GET ['s'] );
		if(key_exists('a'.$id, $tpls)){
			$tpl = $tpls['a'.$id];
		}else{
			$tpl = 'a_'.$id;
		}
		
		if($id == 3){
			$DataList = M ( 'SiteLink' )->where('status=1')->select ();
			$this->assign('SiteList',$DataList);
		}elseif($id == 4){
			$this->assign ( 'item', 6 );
			$this->load ( 24 );
		}
		$this->display ( $tpl );
	}
	
	//------------------------------------------------------------------------------------------------
	//显示详情
	public function detail() {
		if (isset( $_GET ['s'])) {
			$this->load(trim($_GET ['s']));
			$this->display ();
		} else {
			$this->redirect ( 'index' );
		}
	}
	
	//------------------------------------------------------------------------------------------------
	private function load($id) {
		if(empty($id)) return;
		$entity = M ( 'Help' )->where ( "category_id=$id" )->find ();
		$this->assign ( 'entity', $entity );
	}

}
?>