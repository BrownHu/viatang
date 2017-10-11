<?php
/**
 +------------------------------------------------------------------------------
 * DaigouCMS 代购建站系统(淘宝版)
 *
 * 杂志
 +------------------------------------------------------------------------------
 * @author    	soitun <stone@zline.net.cn>
 * @copyright 上海子凌网络科技有限公司
 * @license   	http://www.zline.net.cn/license-agreement.html
 * @link      		http://www.zline.net.cn/
 +------------------------------------------------------------------------------
 */

import ( 'ORG.Util.Page' );
class MagazineAction extends Action{
	
	//------------------------------------------------------------------------------------------------
	public function index(){
		$DAO = M ( 'Magazine' );
		$count = $DAO->where ( 'status=1' )->count ();
		$p = new Page ( $count, 100 );
		$p->setConfig ( 'first', '1' );
		$p->setConfig ( 'theme', '%upPage% %first%  %linkPage%  %downPage%' );
		$page = $p->show ();
			
		$data_list = $DAO->where ( 'status=1' )->limit ( $p->firstRow . ',' . $p->listRows )->order ( 'create_time desc' )->select (); // 按时间升序，即最早下单的先处理
		$this->assign ( 'DataList', $data_list );
		$this->assign ( 'page', trim($page) ); 
		$this->display ();
	}
	
	//------------------------------------------------------------------------------------------------
	public function detail(){
		$DAO = M ( 'Magazine' );
		$id= trim($_GET['id']);
		$item =  $DAO->where("id=$id AND status=1")->find();
		if($item){
			$this->assign('Item',$item);
			$this->display();
		}else{
			$this->assign('jumpUrl','/magazine/index.html');
			$this->error('查看的主题不存在');			
		}
	}
}
?>