<?php
/**
 +------------------------------------------------------------------------------
 * DaigouCMS 代购建站系统(淘宝版)
 *
 * 前台公共类
 +------------------------------------------------------------------------------
 * @category   ulowi
 * @author    	soitun <stone@zline.net.cn>
 * @copyright 上海子凌网络科技有限公司
 * @license   	http://www.zline.net.cn/license-agreement.html
 * @link      		http://www.zline.net.cn/
 +------------------------------------------------------------------------------
 */

class HomeAction extends Action{

	protected $dao,
			  $user = false,
			  $block_list,
			  $client_ip;

	//----------------------------------------------------------------------------------------------
	function _initialize() {
		//$this->block_list = $this->getBlockList();
		$this->client_ip = get_client_ip();
	
		/*if(in_array($this->client_ip, $this->block_list) || $this->checkCookie()){
			die('System error! Please contact web Administrator!');
			$this->upateBlockCookie();
		}*/
		
		Session::set (C ( 'RETURN_URL' ), MODULE_NAME . ',' . ACTION_NAME );//登录后返回url
		if (!isset($_SESSION['referer_url'])) { $_SESSION['referer_url'] = $_SERVER ["HTTP_REFERER"] ; }
		
		$this->user = Session::get ( C( 'MEMBER_INFO' ) );

		if(empty($this->user) && !empty($_COOKIE['uid'])){
			$uid = ulowi_decode($_COOKIE['uid']);
			if(strpos($uid,'jjowisBVWgYF') !== false){
				$uid = str_replace('jjowisBVWgYF',"",$uid);
				$this->user = M ( "User" )->where ( "(active_status=1) AND (status=1) AND (is_qquser=0)  AND (id='$uid') " )->find ();

				if(!empty($this->user)){
					Session::set ( C ( 'MEMBER_INFO' ), $this->user );
					Session::set ( C ( 'MEMBER_AUTH_KEY' ), $this->user ['id'] );
					Session::set ( C ( 'MEMBER_NAME' ), $this->user ['login_name'] );
					Session::set ( 'ulowi_user_level', $this->user ['level'] );
				}
			}
		}

		if ($this->user) {
			$count = M ( 'ShopingCart' )->where ( 'user_id=' . $this->user ['id'] )->count ();
			$_SESSION[C ( 'CART_COUNT' )] = $count;
			
			$shiping_count = M("ShippingCart")->where("user_id=".$this->user['id'])->count();
			$_SESSION['ulowi_shipping_count'] = $shiping_count;
			$unrd_count = M ( 'Notice' )->where ( 'user_id=' . $this->user['id'] . ' AND tag=0' )->count (); //未读短信
			$_SESSION['unrd_msg_count'] = $unrd_count ;
		}
		
		$_hot_search_key = C('hot_search_key');
		if($_hot_search_key){
			$_search_ary = explode(',',$_hot_search_key);
			$this->assign('hot_search_key',$_search_ary);
		}
		
		$this->assign('web_navi',$this->getWebNavi(MODULE_NAME . ',' . ACTION_NAME));
		$this->setHelp();//常见问题

		$this->setProductType();
		$this->setFriendLink();
		
	}
	
	//----------------------------------------------------------------------------------------------
	protected function _list($where='',$order='',$page_size){
		if($this->dao){
			$count = $this->dao->where($where)->count();
			if($count >0){
				import('ORG.Util.Page');
				$psize = ($page_size && $page_size>0)?$page_size:C('NUM_PER_PAGE');
				$p = new Page($count,$psize);
				$p->setConfig ( 'first', '1' );
				$p->setConfig('theme','%upPage% %first%  %linkPage%  %downPage%');
				$page = $p->show();
				$list = $this->dao->where($where)->order($order)->limit($p->firstRow.','.$p->listRows)->select();
				$this->assign('list',$list);
				$this->assign('page',$page);
			}
		}
	}
	
	//----------------------------------------------------------------------------------------------
	protected function _load($where=''){
		if($this->dao && isset($_GET['id']) && is_numeric($_GET['id'])){
			$id =intval(trim($_GET['id']));
			$r = $this->dao->where("id=$id".$where)->find();
			$this->assign('vo',$r);
		}
	}
			
	//----------------------------------------------------------------------------------------------
	//生成验证码
	public function verify() {
		$type = isset ( $_GET ['type'] ) ? $_GET ['type'] : 'gif';
		import ( "ORG.Util.Image" );
		Image::buildImageVerify ( 4, 1, $type );
	}
	
	//----------------------------------------------------------------------------------------------
	//检查验证码
	public function checkVerify($code) {
		return ((strlen ( $code ) > 0) && (md5 ( $code ) == Session::get ( 'verify' ))) ? true : false;
	}
	
	//----------------------------------------------------------------------------------------------
	// 以下为私有公共方法
	//----------------------------------------------------------------------------------------------
	

	//----------------------------------------------------------------------------------------------
	//加载被阻止用户ip列表
	private function getBlockList(){
		$list = M('BlockIp')->field('ip')->select();
		if($list && (count($list)>0) ) {
			$result = array();
			foreach ($list as $r){
				array_push($result, trim($r['ip']));
			}
			return $result;
		}else{
			return false;
		}
	}
	
	//----------------------------------------------------------------------------------------------
	private function checkCookie(){
		$cookie = Cookie::get('ulowi_client_t_c');
		return ( (trim($cookie) != '') &&  (ulowi_decode($cookie) == 'block') ) ? true : false;
	}
	
	//----------------------------------------------------------------------------------------------
	private function upateBlockCookie(){
		Cookie::set('ulowi_client_t_c', ulowi_encode('block'),time()+3600*24*365,'/');
	}
	
	//----------------------------------------------------------------------------------------------
	// 设置网站导航
	private function getWebNavi($_act){
		global $web_navi;
		if(key_exists($_act, $web_navi)){
			return $web_navi[$_act];
		}
		return $web_navi['Index,index'];
	}
	
	//常见问题
	private function setHelp(){
		$HelpList = M ( 'Help' )->field('id,title')->where ( 'category_id=11' )->limit ( '1,10' )->order ( 'sort asc' )->select ();
		$this->assign ( 'IdxHelpList', $HelpList );
	}

	//商品分类
	private function setProductType(){
		if(S('HOME_PRODUCT_CATEGORY_EASYBUYCN')){
			$DataList = S('HOME_PRODUCT_CATEGORY_EASYBUYCN'); 
		}else{
		
			$DataList = array();
			$list = M ( 'CampaignProducttype' )->where('status=1')->order('sort')->select();
			foreach($list as $info){
				if($info['parent_cid']!=0) continue;
				if($info['level']!=1) continue;
				$DataList[] = $info;
			}

			foreach($DataList as $key => $Data){
				$Data['childs'] = array();

				foreach($list as $info2){
					if($info2['parent_cid']!=$Data['id']) continue;

					$info2['childs'] = array();

					foreach($list as $info3){
						if($info3['parent_cid']!=$info2['id']) continue;
						$info2['childs'][] = $info3;
					}

					$Data['childs'][] = $info2;
				}

				$DataList[$key] = $Data;
			}
			S('HOME_PRODUCT_CATEGORY_EASYBUYCN',$DataList);
		}

		$this->assign ( 'topProductTypes', $DataList );
	}
	
	// ------------------------------------------------------------------------------------------------
	// 过滤限制品牌
	protected function isLimtBrand($brand){
		$_list  = M('Brand')->select();
		foreach ($_list as $_item) {
			if(strpos(strtolower($brand), strtolower($_item['title'])) !== false){
				return true;
			}
		}
	
		return false;
	}
	
	//----------------------------------------------------------------------------------------------
	public function _empty() {
		$this->redirect ( 'index' );
	}
	
	//友情链接
	private function setFriendLink(){
		$_list = M ( 'SiteLink' )->limit(0,20)->order('sort asc')->where('status=1')->select();
		$this->assign ( 'SiteLink', $_list );
	}
}

?>