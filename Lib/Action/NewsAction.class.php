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
		    $this->assign ('title','商品保管期限-viatang.com');
	             $this->assign ('keywords','海外华人代购，代购中国，淘宝代购，中国商品代购，服装代购，饰品代购，包包代购，图书代购，食品代购，生活用品代购');
                      $this->assign ('description','海外华人、留学生一站式代购中国商品，商品集中打包配送至海外，国际运费最低3折起');
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