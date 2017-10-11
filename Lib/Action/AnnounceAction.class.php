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
import('ORG.Util.Page');
class AnnounceAction extends HomeAction  {

	function _initialize(){
		parent::_initialize();
		$this->dao = M('Announce');
	}
	
	public function index(){
		$this->redirect('detail');
	}

	public function detail(){
		$this->assign('keywords','代购,购物网址导航,淘宝网,服装,代购商品,购物网站,全球直邮,超低运费');
		$this->assign('description','发布的公告，通过这里了解我们的最新动态');
		
		$id = trim($_GET['id']);
		
		if(isset($_GET['id']) && !empty($id) && $id != 'all'){
			$entity = $this->dao->where("id=$id")->find();
			$this->assign('content',$entity['content']);
			$this->assign('entity',$entity);
			$this->assign('title',$entity['title']);
			$this->display();
		}else{
			$this->redirect('Announce/all');
		}
	}
	
	public function all(){		
		$count = $this->dao->count();
		$p = new Page($count,C('NUM_PER_PAGE'));
		$page = $p->show();
		$_list = $this->dao->limit($p->firstRow.','.$p->listRows)->order('last_update desc')->select();
		
		$this->assign('page',$page);
		$this->assign('list',$_list);
		$this->display();
	}
}
?>