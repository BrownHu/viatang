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

	//----------------------------------------------------------------------------------------------------------------------------
	function _initialize() {
		
		//进行域名授权检测
		if (!check_license_domain(C('licensed_domain'))) {
			header('Content-Type:text/html;charset=utf-8');
			die(L('domain_fail'));
		}
		
		$this->block_list = $this->getBlockList();
		$this->client_ip = get_client_ip();
	
		if(in_array($this->client_ip, $this->block_list) || $this->checkCookie()){
			die('System error! Please contact web Administrator!');
			$this->upateBlockCookie();
		}
		
		Session::set (C ( 'RETURN_URL' ), MODULE_NAME . ',' . ACTION_NAME );//登录后返回url
		if (!isset($_SESSION['referer_url'])) { $_SESSION['referer_url'] = $_SERVER ["HTTP_REFERER"] ; }
		
		$this->user = Session::get ( C( 'MEMBER_INFO' ) );
		if ($this->user) {
			$count = M ( 'ShopingCart' )->where ( 'user_id=' . $this->user ['id'] )->count ();
			$_SESSION[C ( 'CART_COUNT' )] = $count;
			
			
			//系统通知
			$unrd_count = M ( 'Notice' )->where ( 'user_id=' . $this->user['id'] . ' AND tag=0' )->count (); //未读短信
			$unrd_ccount = M('Consultation')->where( 'user_id=' . $this->user ['id'] . " AND  admin_reply_tag=1")->count();
			
			$_SESSION['unrd_msg_count'] = $unrd_count + $unrd_ccount;
		}
		
		$this->setHelp();//常见问题
	}
	
	//----------------------------------------------------------------------------------------------------------------------------
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
	
	//----------------------------------------------------------------------------------------------------------------------------
	protected function _load($where=''){
		if($this->dao && isset($_GET['id']) && is_numeric($_GET['id'])){
			$id =intval(trim($_GET['id']));
			$r = $this->dao->where("id=$id".$where)->find();
			$this->assign('vo',$r);
		}
	}
			
	//----------------------------------------------------------------------------------------------------------------------------
	public function index(){
		$this->_list();
		$this->display();	
	}
	
	//----------------------------------------------------------------------------------------------------------------------------
	//生成验证码
	public function verify() {
		$type = isset ( $_GET ['type'] ) ? $_GET ['type'] : 'gif';
		import ( "ORG.Util.Image" );
		Image::buildImageVerify ( 4, 1, $type );
	}
	
	//----------------------------------------------------------------------------------------------------------------------------
	//检查验证码
	public function checkVerify($code) {
		return ((strlen ( $code ) > 0) && (md5 ( $code ) == Session::get ( 'verify' ))) ? true : false;
	}
	
	//---------------------------------------------------------------------------------------------------------------------------
	// 以下为私有公共方法
	//----------------------------------------------------------------------------------------------------------------------------
	
	//----------------------------------------------------------------------------------------------------------------------------
	//常见问题
	private function setHelp(){
		//$HelpList = F ( 'ULowiIdxHelpList' );
		//if (empty( $HelpList )) {
		$dao = M('Help' );
			$HelpList = $dao->field('id,title')->where ( 'category_id=11' )->limit ( '0,10' )->order ( 'sort asc' )->select ();
			//F ( 'ULowiIdxHelpList', $HelpList );
		//}
		
		$this->assign ( 'IdxHelpList', $HelpList );
	}
	
	//----------------------------------------------------------------------------------------------------------------------------
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
	
	//----------------------------------------------------------------------------------------------------------------------------
	private function checkCookie(){
		$cookie = Cookie::get('ulowi_client_t_c');
		return ( (trim($cookie) != '') &&  (ulowi_decode($cookie) == 'block') ) ? true : false;
	}
	
	//----------------------------------------------------------------------------------------------------------------------------
	private function upateBlockCookie(){
		Cookie::set('ulowi_client_t_c', ulowi_encode('block'),time()+3600*24*365,'/');
	}
	
	//---------------------------------------------------------------------------------------------------------------------------
	public function _empty() {
		$this->redirect ( 'index' );
	}
}

?>