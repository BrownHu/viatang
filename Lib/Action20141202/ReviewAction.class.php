<?php
/**
 +------------------------------------------------------------------------------
 * DaigouCMS 代购建站系统(淘宝版)
 *
 *  互动反馈
 +------------------------------------------------------------------------------
 * @category   ulowi
 * @author    	soitun <stone@zline.net.cn>
 * @copyright 上海子凌网络科技有限公司
 * @license   	http://www.zline.net.cn/license-agreement.html
 * @link      		http://www.zline.net.cn/
 +------------------------------------------------------------------------------
 */

import('ORG.Util.Page');
class ReviewAction extends HomeAction  {
	
	//------------------------------------------------------------------------------------------------
	function _initialize(){
	   parent::_initialize();
	   $this->dao = M('Comment');
	   
		if(S('ReviewCountryList')){
			$CountryList = S('ReviewCountryList');
		}else{
			$CountryList =  M('DeliverZone')->where('status=1')->order('sort')->select();
			S('ReviewCountryList',$CountryList);
		}
		$this->assign('CountryList',$CountryList);

		global $shipping_way_array;
		$this->assign('WayList',$shipping_way_array);
	}

	//------------------------------------------------------------------------------------------------
	public function index(){		
		$this->_list('is_display=1','create_time desc');
		$this->display();
	}

	//------------------------------------------------------------------------------------------------
	public function condition(){
		$zid = trim($_GET['z']);
		$wid = trim($_GET['w']);
		$sql = 'is_display=1';
		if(!empty($zid) &&  !empty($wid)){
			global $shipping_way_array;
			$way_name = $shipping_way_array[$wid];
			$sql = $sql . " AND zone_id=$zid AND way_name like '%$way_name%'";
		}elseif(!empty($zid)){
			$sql = $sql . " AND zone_id=$zid";
		}elseif(!empty($wid)){
			global $shipping_way_array;
			$way_name = $shipping_way_array[$wid];
			$sql = $sql . " AND way_name like '%$way_name%'";
		}
		
		$this->_list($sql,'create_time desc');
		$this->assign('zid',$zid);
		$this->assign('wid',$wid);
		$this->display('index');
	}

	//------------------------------------------------------------------------------------------------
	//发表评论
	public function commit(){
		$id =trim($_POST['id']);//包裹id
		if($this->user && $id){
			$PackageDAO = M('Package');
			$package = $PackageDAO->where("id=$id")->find();
			if($package){
				$data['package_id'] 	= $id;
				$data['package_no'] 	= $package['package_code'];
				$data['zone_id'] 	= $package['zone_id'];
				$data['way_id'] 	= $package['deliver_id'];
				$data['way_name'] 	= $package['deliver_way'];
				$data['country']	= $package['country'];
				$data['user_id'] 	= $this->user['id'];
				$data['user_name']	= $this->user['login_name'];
				$data['content']	= trim($_POST['review']);
				$data['ip']		= $this->client_ip;

				$data['reply_content']	= '';
				$data['reply_time']	= 0;
				$data['admin_id']	= 0;
				$data['admin_name']	= '';
				$data['is_display']	= 0;
				$data['create_time']	= time();

				M('Comment')->data($data)->add();
			}
		}
		$this->display('Public/result');
	}

	//------------------------------------------------------------------------------------------------
	public function del(){
		$id =trim($_GET['id']);//评论id
		if($this->user && !empty($id)){
			$this->dao->where("id=$id  AND reply_time=0")->delete();
		}
		$this->redirect('My/review');
	}	
}
?>