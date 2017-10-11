<?php
/**
 +------------------------------------------------------------------------------
 * DaigouCMS 代购建站系统(淘宝版)
 *
 * 时尚专题
 +------------------------------------------------------------------------------
 * @category   ulowi
 * @author    	soitun <stone@zline.net.cn>
 * @copyright 上海子凌网络科技有限公司
 * @license   	http://www.zline.net.cn/license-agreement.html
 * @link      		http://www.zline.net.cn/
 +------------------------------------------------------------------------------
 */

import ( 'ORG.Util.Page' );
class FashionAction extends PublicAction {
	
	//------------------------------------------------------------------------------------------------
	function _initialize() {
		parent::_initialize();
		Session::set ( C( 'RETURN_URL' ), MODULE_NAME . ',' . ACTION_NAME );
		$DataList = M ( 'FashionCategory' )->select ();
		$this->assign ( 'FashionCategoryList', $DataList );
	}
	
	//------------------------------------------------------------------------------------------------
	public function index() {
		$category = trim ( $_GET ['c'] );
		$DAO = M ( 'Fashion' );
		if (! empty ( $category )) {
			$count = $DAO->where ( "category=$category" )->count ();
		} else {
			$count = $DAO->count ();
		}
		
		$p = new Page ( $count, 10 );
		$p->setConfig ( 'first', '1' );
		$p->setConfig ( 'theme', '%upPage% %first% %linkPage% %downPage%' );
		$page = $p->show ();
		if (! empty ( $category )) {
			$DataList = $DAO->where ( "category=$category" )->limit ( $p->firstRow . ',' . $p->listRows )->order('id desc')->select ();
		} else {
			$DataList = $DAO->limit ( $p->firstRow . ',' . $p->listRows )->order('id desc')->select ();
		}
		$category = ! empty ( $category ) ? $category : 0;
		$this->assign ( 'DataList', $DataList );
		$this->assign ( 'page', trim($page) );
		$this->assign ( 'category', $category );
		$this->display ();
	}
	
	//------------------------------------------------------------------------------------------------
	public function topic() {
		$tpl = trim ( $_GET ['s'] );
		if (! empty ( $tpl )) {
			$this->display ( $tpl );
		} else {
			$this->redirect ( 'index' );
		}
	}
	
	//------------------------------------------------------------------------------------------------
	Public function _empty() {
		$this->redirect ( 'index' );
	}
}
?>