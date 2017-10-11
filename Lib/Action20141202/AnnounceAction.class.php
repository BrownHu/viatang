<?php
/**
 +------------------------------------------------------------------------------
 * DaigouCMS 代购建站系统(淘宝版)
 * 
 * 公告
 +------------------------------------------------------------------------------
 * @category   ulowi
 * @author    	soitun <stone@zline.net.cn>
 * @copyright 上海子凌网络科技有限公司 
 * @license   	http://www.zline.net.cn/license-agreement.html 
 * @link      		http://www.zline.net.cn/
 +------------------------------------------------------------------------------
 */
class AnnounceAction extends HomeAction  {

	public function index(){
		$this->redirect('detail');
	}

	public function detail(){
		$id = trim($_GET['id']);
		if(!empty($id)){
			$entity = M('Announce')->where("id=$id")->find();
			$this->assign('content',$entity['content']);
			$this->assign('entity',$entity);
			$this->assign('title',$entity['title']);
			$this->display();
		}else{
			$this->redirect('Index/index');
		}
	}
}
?>