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
        $this->assign ('title','用户评论-淘宝代购及淘宝包裹转运用户分享点评，唯唐代购怎么样，viatang代购怎么样，viatang服务评价-viatang.com');
	    $this->assign ('keywords','代购，代购经验分享，唯唐代购怎么样，viatang代购怎么样，淘宝代购，淘宝转运，海外华人代购，代购中国，代购网站评论，代购介绍，服务评论，代购评价，代购包裹评价，包裹转运评价，服装代购，图书代购，鞋包代购');
        $this->assign ('description','我们一直在努力，为用户提供更好的购物体验，客服1对1全程贴心服务，免费验货及退换货，多件商品集中邮寄、转寄，配送全球享最低国际运费12元起');		
		$this->_list('is_display=1','create_time desc',14);
		$list = $this->get('list');

		$commentDetailDao = M('CommentDetail');
		foreach($list as $key => $info){
			$list[$key]['count'] =  $commentDetailDao->where('comment_id='.$info['id'].' and status=1')->count();
		}

		$this->assign('list',$list);
		$this->setCustomerpic();
		$this->display();
	}

	public function detail(){
		$id = $_GET['id'];
		$commentInfo = $this->dao->where("id=$id")->find();
		$commentDetailDao = M('CommentDetail');

		if($this->user && $commentInfo['id'] && $_POST){
			$data['status'] 	= 1;
			$data['comment_id'] 	= $id;
			$data['user_id'] 	= $this->user['id'];
			$data['user_name']	= $this->user['login_name'];
			$data['content']	= trim($_POST['content']);
			$data['create_time']	= time();

			$commentDetailDao->data($data)->add();
			$this->redirect ( 'detail',array('id'=>$id) );
			return;
		}

		$list =  $commentDetailDao->where('comment_id='.$id.' and status=1')->order('create_time desc')->select();

		$this->assign('commentInfo',$commentInfo);
		$this->assign('list',$list);
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

	private function setCustomerpic(){
		$_list = M('AdBanner')->where("type=9 and img !=''")->select();
		$this->assign ( 'Customerpic', $_list );
	}
}
?>