<?php
/**
 +------------------------------------------------------------------------------
 * DaigouCMS 代购建站系统(淘宝版)
 *
 * 记录
 +------------------------------------------------------------------------------
 * @category   ulowi
 * @author    	soitun <stone@zline.net.cn>
 * @copyright 上海子凌网络科技有限公司
 * @license   	http://www.zline.net.cn/license-agreement.html
 * @link      		http://www.zline.net.cn/
 +------------------------------------------------------------------------------
 */

import ( 'ORG.Util.Page' );
class LogAction extends BaseAction {
	
	//------------------------------------------------------------------------------------------------
	public function index() {
		$this->redirect ( 'My/log' );
	}
	
	//------------------------------------------------------------------------------------------------
	public function package() {
		if ($this->user) {
			$DAO = M ( 'Package' );
			$count = $DAO->where ( "user_id=" . $this->user ['id'] )->count ();
			if ($count > 0) {
				$p = new Page ( $count, C ( 'NUM_PER_PAGE' ) );
				$page = $p->show ();
				$DataList = $DAO->where ( 'user_id=' . $this->user ['id'] )->limit ( $p->firstRow . ',' . $p->listRows )->order ( 'id' )->select ();
				$this->assign ( 'DataList', $DataList );
				$this->assign ( 'page', trim($page) );
				global $package_status_array;
				$this->assign ( 'PackageStatus', $package_status_array );
			}
			$this->display ();
		} else {
			Session::set ( C ( 'RETURN_URL' ), 'Log,package' );
			$this->redirect ( 'Public/login' );
		}
	}
	
	//------------------------------------------------------------------------------------------------
	public function pay() {
		if ($this->user) {
			$DAO = M ( 'PaymentTrace' );
			$count = $DAO->where ( "user_id=" . $this->user ['id'] )->count ();
			if ($count > 0) {
				$p = new Page ( $count, C ( 'NUM_PER_PAGE' ) );
				$page = $p->show ();
				$DataList = $DAO->where ( 'user_id=' . $this->user ['id'] )->limit ( $p->firstRow . ',' . $p->listRows )->order ( 'create_time asc' )->select ();
				$this->assign ( 'DataList', $DataList );
				$this->assign ( 'page', trim($page) );
			}
			$this->display ();
		} else {
			Session::set ( C ( 'RETURN_URL' ), 'Log,pay' );
			$this->redirect ( 'Public/login' );
		}
	}
	
	//------------------------------------------------------------------------------------------------
	public function credit() {
		$this->display ();
	}
	
	//------------------------------------------------------------------------------------------------
	public function spend() {
		if ($this->user) {
			$DAO = M ( 'FinanceLog' );
			$count = $DAO->where ( "user_id=" . $this->user ['id'] )->count ();
			if ($count > 0) {
				$p = new Page ( $count, 50 );
				$p->setConfig('first','1');
				$p->setConfig('theme','%upPage% %first%  %linkPage%  %downPage%');
				$page = $p->show ();
				$DataList = $DAO->where ( 'user_id=' . $this->user ['id'] )->limit ( $p->firstRow . ',' . $p->listRows )->order ( 'create_time asc' )->select ();
				$this->assign ( 'DataList', $DataList );
				$this->assign ( 'page', trim($page) );
			}
			$this->display ();
		} else {
			Session::set ( C ( 'RETURN_URL' ), 'Log,spend' );
			$this->redirect ( 'Public/login' );
		}
	}
	
	//------------------------------------------------------------------------------------------------
	public function point() {
		if ($this->user) {
			$DAO = M ( 'PointLog' );
			$count = $DAO->where ( "user_id=" . $this->user ['id'] )->count ();
			if ($count > 0) {
				$p = new Page ( $count, C ( 'NUM_PER_PAGE' ) );
				$page = $p->show ();
				$DataList = $DAO->where ( 'user_id=' . $this->user ['id'] )->limit ( $p->firstRow . ',' . $p->listRows )->order ( 'create_time asc' )->select ();
				$this->assign ( 'DataList', $DataList );
				$this->assign ( 'page', trim($page) );
			}
			$this->display ();
		} else {
			Session::set ( C ( 'RETURN_URL' ), 'Log,point' );
			$this->redirect ( 'Public/login' );
		}
	}
	
	//------------------------------------------------------------------------------------------------
	//查询指定日期范围内的财务变化记录
	public function search() {
		if ($this->user) {
			$start = strtotime ( $_REQUEST ['start'] );
			$end = strtotime ( $_REQUEST ['end'] );
			
			if ($_REQUEST ['start'] && $_REQUEST ['end']) {
				$map ['create_time'] = array (array ('egt', $start ), array ('elt', $end ), 'and' );
				$this->assign ( 'start', $_REQUEST ['start'] );
				$this->assign ( 'end', $_REQUEST ['end'] );
			} elseif ($_REQUEST ['start']) {
				$map ['create_time'] = array ('egt', $start );
				$this->assign ( 'start', $_REQUEST ['start'] );
			} elseif ($_REQUEST ['end']) {
				$map ['create_time'] = array ('elt', $end );
				$this->assign ( 'end', $_REQUEST ['end'] );
			}
			
			$map ['user_id'] = $this->user ['id'];
			
			$DAO = M ( 'FinanceLog' );
			$count = $DAO->where ( $map )->count ();
			
			if ($count > 0) {
				$p = new Page ( $count, C ( 'NUM_PER_PAGE' ) );
				$p->parameter .= "start=" . $_REQUEST ['start'] . "&end=" . $_REQUEST ['end'] . '&user_id=' . $this->user ['id'];
				
				$page = $p->show ();
				$DataList = $DAO->where ( $map )->limit ( $p->firstRow . ',' . $p->listRows )->order ( 'create_time asc' )->select ();
				$this->assign ( 'DataList', $DataList );
				$this->assign ( 'page', trim($page) );
			}
			$this->display ();
		} else {
			Session::set ( C ( 'RETURN_URL' ), 'Log,search' );
			$this->redirect ( 'Public/login' );
		}
	}
	
	//------------------------------------------------------------------------------------------------
	Public function _empty() {
		$this->redirect ( 'My/log' );
	}
}
?>