<?php
/**
 +------------------------------------------------------------------------------
 * 国际转运系统
 *
 * 微信接口
 +------------------------------------------------------------------------------
 * @category   ulowi
 * @author     soitun <stone@zline.net.cn>
 * @copyright  上海子凌网络科技有限公司
 * @license    http://www.zline.net.cn/license-agreement.html
 * @link       http://www.zline.net.cn/
 +------------------------------------------------------------------------------
 */

use Com\Wechat;
import ( 'ORG.Wechat.Wechat' );
import ( 'ORG.Wechat.WechatAuth' );
load ( '@/functions' );
import ( 'ORG.Util.Page' );
class WeAction extends HomeAction {
//6UmXvxK4rRAbeF4DJlnHrDZmrhBFNTrcRP0vzxZ4hFY  EncodingAESKey
	protected $_server_url     = '';
	protected $wechat_token    = '';
	private   $requestCodeURL  = 'https://open.weixin.qq.com/connect/oauth2/authorize';
	private   $redirect_url    = 'http://vt.daigoucms.cn/we/requestToken/do/';
	private   $accesstoken_url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid=__APPID__&secret=__SECRET__&code=__CODE__&grant_type=authorization_code';
	
	protected $goods;
	protected $express;

	protected $address;
	protected $finance;
	protected $lang = LANG_SET;
	protected $country;
	
	// -------------------------------------------------------------------------------------------
	// 初始化
	function _initialize() {
        $this->wechat_token = 'ZLinkDaigouCMS';

		$this->_server_url = 'http://www.viatang.com/we/';//C('SITE_URL')
		if( !isset($_SESSION ['WechatState']) ){
			$_SESSION ['WechatState'] = md5 ( uniqid ( rand (), TRUE ) );
		}
		
//		$this->goods 	= M("Goods");
//		$this->express 	= M("Express");
//		$this->address 	= M('DeliverAddress');	//发货渠道
//		$this->finance 	= D("Finance");
//		$this->country  = M('DeliverZone');	//配送区域
	}
	
	// -------------------------------------------------------------------------------------------
 	private function getRequestCodeUrl($_second_redirect_uri){
 		$_redirect_uri = $this->redirect_url . $_second_redirect_uri; 
		$query = array(
				'appid'         => "wxdfaebc95aec271b5",
				'redirect_uri'  => $_redirect_uri,
				'response_type' => 'code',
				'scope'         => 'snsapi_base',
				'state'			=> md5 ( uniqid ( rand (), TRUE ) )
		);
		
		$query = http_build_query($query);
        return "{$this->requestCodeURL}?{$query}#wechat_redirect";
	}
	
	// -------------------------------------------------------------------------------------------
	// 获取微信AccessToken, 并从中取得openid
	// 将openid 传递到 do 所指定的方法中
	public function requestToken(){
		$_state = trim($_REQUEST ['state']);
		$_code  = trim($_REQUEST ["code"]);
		$openid = $this->getAuthAccessToken($_state, $_code);
		$do = $_REQUEST['do'];
		$this->redirect($do,array('openid'=>$openid));
	}
	
	// ----------------------------------------------------------------------------------------------------------------------------------------------------
	private function getAuthAccessToken($state, $code) {
		//if ($state == $_SESSION ['WechatState']) {
            $appid = 'wxdfaebc95aec271b5';//trim ( C ( 'Wechat_APPID' ) );
//			$appid = trim ( C ( 'WECHAT_AppID' ) );
//			$secret = trim ( C ( 'WECHAT_AppSecret' ) );
			$secret = "50fe03331a5905e1075fced73f673077";
			$token_url = str_replace('__APPID__', $appid, $this->accesstoken_url);
			$token_url = str_replace('__SECRET__', $secret, $token_url);
			$token_url = str_replace('__CODE__', $code, $token_url);
			$response = file_get_contents_ex($token_url);
			$_result = json_decode($response,true);
			
			if (isset($_result['errcode'])) { return false; }
	
			$_SESSION ["WechatAuthAccessToken"] = $_result ["access_token"];
			$_SESSION ['WechatAuthExpiresIn'] = time() + $_result ['expires_in'];
			$_SESSION ['WechatAuthOpenId'] = $_result ['openid'];
			return $_SESSION ['WechatAuthOpenId'];
		//} 
		//return false;
	}
	
	// -------------------------------------------------------------------------------------------
	// 微信首页	
	public function home(){
		if(isset($_REQUEST['openid']) || isset($_SESSION ['WechatAuthOpenId']) ){
			$openid = isset($_REQUEST['openid'])? $_REQUEST['openid'] : $_SESSION ['WechatAuthOpenId'];
		}else{

			$this->processOpenid('home.html');
		}
		$this->assign('openid',$openid);
        echo $openid;
		//加载用户
		//$user = $this->getUserByWechatOpenId($openid);
//		$this->loadAnnounce();
//		$this->loadAd();
//		$this->display();
	}
	
	// -------------------------------------------------------------------------------------------
	// 加载公告
	private function loadAnnounce(){
		$this->dao = M("Announce");
		$this->_list(array('language'=>$this->lang),'last_update desc',C('NUM_PER_PAGE'));
		$this->assign('AnnounceList',$this->view->get('list'));
	}
	
	// -------------------------------------------------------------------------------------------
	// 加载广告
	private function loadAd(){
		$this->dao = M("AdBanner");
		$this->_list(array('status'=>1, 'type'=>10, 'language'=>$this->lang),'sort asc',C ( 'NUM_PER_PAGE' ));
		$this->assign('AdList',$this->view->get('list'));
	}
	
	// -------------------------------------------------------------------------------------------
	// 运单跟踪
	public function track(){
		$this->dao = M('ExpressLog');
		if(isset($_REQUEST['code']) && (trim($_REQUEST['code']) != '') ){
			$code = trim($_REQUEST['code']);
			$this->_list("trace_code='$code'",'create_at asc',50);
			$this->assign('trace_code',$code);
		}

		$this->assign('openid',$_REQUEST['openid']);
		$this->display();
	}
//hubing start
    // -------------------------------------------------------------------------------------------
    // 到库查询
    public function arriveQuery(){

    }

    // -------------------------------------------------------------------------------------------
    // 提交包裹清单
    public function commitPackage(){

    }

    // 商品管理
    public function goodsManage(){

    }
//
//    //
//    public function commitPackage(){
//
//    }
//
//    // 提交包裹清单
//    public function commitPackage(){
//
//    }
//
//    // 提交包裹清单
//    public function commitPackage(){
//
//    }
//    hubing end
	// -------------------------------------------------------------------------------------------
	// 公告详情
	public function detail(){
		$this->dao = M('Announce');
		$this->_load();
		$this->assign("item",$this->view->get('vo'));
		$this->assign('openid',$_REQUEST['openid']);
		$this->display();
	}
	
	
	//------------------------------------------------------------------------------------------------
	// 我的包裹
	public function parcel() {
		if(isset($_REQUEST['openid']) || isset($_SESSION ['WechatAuthOpenId']) ){
			$openid = isset($_REQUEST['openid'])? $_REQUEST['openid'] : $_SESSION ['WechatAuthOpenId'];
		}else{
			$this->processOpenid('register.html');
		}
		
		$this->user = $this->getUserByWechatOpenId($openid);
		$this->assign('user',$this->user);
		$this->assign('openid',$openid);
		
		if ($this->user) {
			$this->dao = M ( 'Package' );
			$count = $this->dao->where ( "status<>7 AND user_id=" . $this->user ['id'] )->count ();
			if ($count > 0) {
				//import ( 'ORG.Util.Page' );
				$p = new Page ( $count, C ( 'NUM_PER_PAGE' ) );
				$p->setConfig ( 'first', '1' );
				$p->setConfig ( 'theme', '%upPage% %first%  %linkPage%  %downPage%' );
				$page = $p->show ();
				$DataList = $this->dao->where ( 'status<>7 AND user_id=' . $this->user ['id'] )->limit ( $p->firstRow . ',' . $p->listRows )->order ( 'create_time desc' )->select ();
				$this->assign ( 'DataList', $DataList );
				$this->assign ( 'page', trim($page) );
	
				global $package_status_array;
				$this->assign ( 'PackageStatus', $package_status_array );
			}
		}
		
		$this->display ();
	}
	
	// -------------------------------------------------------------------------------------------
	// 微信推送入口
	public function index() {
	    echo time();
//			$WechatClient = !empty($this->wechat_token) ? new Wechat ( $this->wechat_token ) : false;
//			$WechatRequest = ($WechatClient) ? $WechatClient->request () : false;
//
//			 if ($WechatRequest && is_array ( $WechatRequest)) {
//				$_openid = $WechatRequest ['FromUserName'] ;
//
//				if (strtolower ( $WechatRequest ['MsgType'] ) == Wechat::MSG_TYPE_EVENT) {
//					if (strtoupper ( $WechatRequest['Event'] ) == Wechat::MSG_EVENT_CLICK) {
//						if ($this->getUserByWechatOpenId ($_openid) != false) {
//							$content = $this->eventRoute($WechatRequest,$_openid);
//						} else {
//							$content = '为了更好地为您服务，请先'
//									 . '<a href="'. $this->_server_url . 'register.html?oid='.$_openid.'">'
//									 . '绑定系统帐号哦'
//									 . '</a>';
//						}
//
//						$WechatClient->replyText ( $content );
//					}
//				}elseif(strtolower ( $WechatRequest ['MsgType'] ) == Wechat::MSG_TYPE_TEXT){
//
//				}elseif(strtolower ( $WechatRequest ['MsgType'] ) == Wechat::MSG_TYPE_IMAGE){
//
//				}
//		}

	}
	
	// -------------------------------------------------------------------------------------------
	// 显示文章
	public function article(){
		$item = M('Help')->where("id=".$_REQUEST['did'])->find();
		$this->assign('item',$item);
		$this->assign('title',$item['title']);
		$this->assign('openid',$_REQUEST['openid']);
		$this->display();
	}
	
	// -------------------------------------------------------------------------------------------
	// 咨询
	public function consulation(){
		if(isset($_REQUEST['openid']) || isset($_SESSION ['WechatAuthOpenId']) ){
			$openid = isset($_REQUEST['openid'])? $_REQUEST['openid'] : $_SESSION ['WechatAuthOpenId'];
		}else{
			$this->processOpenid('cosulation.html');
		}
		
		if($openid){
			$this->assign('openid',$openid);
			$this->user = $this->getUserByWechatOpenId($openid);
			$this->assign('user',$this->user);
		}
		$this->display();
	}
	
	public function doConsulation(){
		if(isset($_REQUEST['openid']) || isset($_SESSION ['WechatAuthOpenId']) ){
			$openid = isset($_REQUEST['openid'])? $_REQUEST['openid'] : $_SESSION ['WechatAuthOpenId'];
		}else{
			$this->processOpenid('cosulation.html');
		}
		
		if($openid){
			if( (trim ( $_POST ['help_title'] ) == '') || (trim ( $_POST ['help_content'] ) == '') ){
				$url = '/wechat/consulation.html?openid='.$openid;
				$this->disWechatMessage(L('lang_error'), $url,false,1);
				return;
			}
			
			$this->user = $this->getUserByWechatOpenId($openid);
			$this->assign('user',$this->user);
				
			$data ['user_id'] = $this->user ['id'];
			$data ['user_name'] = $this->user ['login_name'];
			$data ['title'] = trim ( $_POST ['help_title'] );
			$data ['content'] = trim ( $_POST ['help_content'] );
			$data ['ip'] = get_client_ip ();
			$data ['create_at'] = time ();
			$data ['status'] = '0';
			$data ['customer_reply_tag'] = 1;

			M("Consultation")->data ( $data )->add ();
			$url = '/wechat/consulation.html?openid='.$openid;
			$this->disWechatMessage(L('lang_success'), $url,true,1);
		}
	}
	
	// -------------------------------------------------------------------------------------------
	// 财务记录
	public function finance_log(){
		if(isset($_REQUEST['openid']) || isset($_SESSION ['WechatAuthOpenId']) ){
			$openid = isset($_REQUEST['openid'])? $_REQUEST['openid'] : $_SESSION ['WechatAuthOpenId'];
		}else{
			$this->processOpenid('finance_log.html');
		}
		
		if($openid){
			$this->assign('openid',$openid);
			$this->user = $this->getUserByWechatOpenId($openid);
			if($this->user){
				$this->dao = M ( 'FinanceLog' );
				$count = $this->dao->where ( "user_id=" . $this->user ['id'] )->count ();
				if ($count > 0) {				
					$p = new Page ( $count, 50 );
					$p->setConfig('first','1');
					$p->setConfig('theme','%upPage% %first%  %linkPage%  %downPage%');
					$page = $p->show ();
					$DataList = $this->dao->where ( 'user_id=' . $this->user ['id'] )->limit ( $p->firstRow . ',' . $p->listRows )->order ( 'create_time desc' )->select ();
					$this->assign ( 'DataList', $DataList );
					$this->assign ( 'page', trim($page) );
				}
			}
			$this->display ();
		}
	}
	
	//---------------------------------------------------------------------------------------------//
	
	// -------------------------------------------------------------------------------------------
	// 根据关键词进行分发回复
	private function eventRoute($_r,$oid){
		$url = $this->_server_url.'wareaddress.html?oid='.$oid;
		switch ($_r['EventKey']){
			case 'user_reg' : $content = '您的微信已经与86OF绑定过了哦 !点击<a href="'.$url.'"  >这里</a>查看仓库地址';
						      break;
			case 'MY_WAREHOUSE': $content = ' Please click to check my address >><a href="'.$url.'">CLICK HERE</a><<';
							  break;
			default: $content = '建设中,敬请期待...';
		}
		return $content;
	}
	
	// --------------------------------------------------------------------------------------------
	// 根据微信openid 注册用户信息
	public function register(){
		if(isset($_REQUEST['openid']) || isset($_SESSION ['WechatAuthOpenId']) ){
			$openid = isset($_REQUEST['openid'])? $_REQUEST['openid'] : $_SESSION ['WechatAuthOpenId'];
		}else{
			$this->processOpenid('register.html');
		}
		
		if($openid){
			$this->assign('openid',$openid);
			if ($this->getUserByWechatOpenId($openid)) {
				$url = '/wechat/wareaddress.html?openid='.$openid;
				$this->disWechatMessage('您的微信已经与86OF绑定过了哦，可以直接点击相应的菜单，来享受86OF提供的各种服务啦!', $url,false,1);
			}else{
				$this->assign('openid',$openid);
				$this->display();
			}
		}else{			
			$this->disWechatMessage('读取OPENID失败，或没有填写您在86OF的帐户信息！', '',false,0);
		}		
	}
	
	// --------------------------------------------------------------------------------------------
	// 仓库地址
	public function wareaddress(){
		if(isset($_REQUEST['openid']) || isset($_SESSION ['WechatAuthOpenId']) ){
			$openid = isset($_REQUEST['openid'])? $_REQUEST['openid'] : $_SESSION ['WechatAuthOpenId'];
		}else{
			$this->processOpenid('wareaddress.html');
		}
		$this->assign('openid',$openid);
		$this->assign('user',$this->getUserByWechatOpenId($openid));
		$this->display();
	}
	

	// --------------------------------------------------------------------------------------------
	//加载当前微信用户的仓库信息
	public function warehouse(){
		if(isset($_REQUEST['openid']) || isset($_SESSION ['WechatAuthOpenId']) ){
			$openid = isset($_REQUEST['openid'])? $_REQUEST['openid'] : $_SESSION ['WechatAuthOpenId'];
		}else{
			$this->processOpenid('warehouse.html');
		}
		
		$user = $this->getUserByWechatOpenId($openid);
		if($user){
			$this->assign('user',$user);
			$where = (isset($_REQUEST['ware_id']) && $_REQUEST['ware_id']!='0')?" AND ware_id=".$_REQUEST['ware_id'] :'';
			$ware_id = isset($_REQUEST['ware_id']) ? $_REQUEST['ware_id'] : 0;
			
			$this->dao = M("Express");
			$list = $this->_list("status=7 AND user_id=".$user['id'].$where,'id asc', 300);
			$_volume = $this->dao->where('status=7 AND user_id='.$user['id'].$where)->sum('volume');
			$_count  = $this->dao->where('status=7 AND user_id='.$user['id'].$where)->count();
			
			$this->assign('_count',$_count);
			$this->assign('volume',$_volume);
			$this->assign('list',$list);

			$this->assign('ware_id',$ware_id);
		}
		
		$this->setWareList();
		$this->assign('openid',$openid);	
		$this->display();
	}
	
	// --------------------------------------------------------------------------------------------
	// 合箱转运
	public function hxzy(){
		if(isset($_REQUEST['openid']) || isset($_SESSION ['WechatAuthOpenId']) ){
			$openid = isset($_REQUEST['openid'])? $_REQUEST['openid'] : $_SESSION ['WechatAuthOpenId'];
		}else{
			$this->processOpenid('hxzy.html');
		}
		$this->assign('openid',$openid);
		$user = $this->getUserByWechatOpenId($openid);
		$this->assign("user",$user);
		
		$idary = $_POST['id'];
			
		//过滤掉没有申报的商品
		if($this->checkExpressReport($idary)){
			$url ='/wechat/warehouse.html?openid='.$_POST['openid'];
			$this->disWechatMessage('存在未申报货品的包裹，请补全后再提交', $url,false,1);
		}
			
		$ware_id = $_POST['ware_id'];
		if(empty($ware_id)){
			$url ='/wechat/warehouse.html?openid='.$_REQUEST['openid'];
			$this->disWechatMessage('请先按仓库过滤后再操作', $url,false,1);
			return;
		}
		
		if( (count($idary)>0) && ($ware_id>0)  && $user){
			$ids = implode(',', $idary);
			$this->dao = M("Express");
			$this->_list("id in ($ids)",'id asc', C('NUM_PER_PAGE'));
			$this->assign('DataList',$this->view->get('list'));
			$_volume = $this->dao->where("id in ($ids)")->sum('volume');
			$_count = $this->dao->where("id in ($ids)")->count();
			$_value_total = $this->computeExpressValueSum($ids);
			$_value_gst_fee = $_value_total *  $this->getCustomFee();
			$_value_gst_fee = round($_value_gst_fee,2);
		
			$this->assign('ids',$ids);
			$this->assign('PackageVolume',$_volume);
			$this->assign('count',$_count);
			$this->assign('PackageValue',$_value_total);
			$this->assign('value_gst_fee',$_value_gst_fee);
		
			//收货地址本
			$this->assign('AddressList',$this->getReceiveAddressList($user['id']));
		
			//报价列表
			$this->assign('price_list',$this->computeWareFee($ware_id, $_volume,$_value_gst_fee));
			$this->assign('balance',$this->getBalance($user));
			$this->display();
		}else{
			$url ='/wechat/warehouse.html?openid='.$_REQUEST['openid'];
			$this->disWechatMessage('生成运单出错', $url,false,1);
		}
	}
	
	//---------------------------------------------------------------------------------------------
	//设置帐户余额
	private function getBalance($user){
		if($user){
			$vo = $this->finance->finace($user['id']);
			return ($vo)?$vo['money']:0;
		}
		return 0;
	}
	
	//---------------------------------------------------------------------------------------------
	// 加载用户收货地址
	private function getReceiveAddressList($uid){
		return ($uid && is_numeric($uid))? M('Address')->where("user_id=$uid")->select() : array();
	}
	
	
	//---------------------------------------------------------------------------------------------
	// 计算仓库的各种费用
	// $ware_id ：仓库ID，
	// $volume : 体积
	// $_value_gst_fee : 申报价值对应的消费税
	private function computeWareFee($ware_id,$volume,$_value_gst_fee){
		$deliver_price = $this->address->where("(ware_id=$ware_id) and (status=1) and ($volume <= limit_weight)")->select();
		foreach ($deliver_price as &$r){
			$shipping_fee = ($volume > $r['customer_special_unit']) ? $volume * $r['customer_volume_unit_price'] : $r['customer_special_unit_price'];
			$shipping_gst_fee = ($r['include_customefee'] == 1) ? $shipping_fee * $this->getCustomFee():0;
			$r['shipping_fee'] = round($shipping_fee,2);//运费
			$r['shipping_gst_fee'] = round($shipping_gst_fee,2);//消费税
				
			$r['gst_fee_total'] = round($_value_gst_fee + $shipping_gst_fee,2);
			$r['pay_total'] = round($shipping_fee + $shipping_gst_fee + $_value_gst_fee + $r['customfee'],2);
		}
		return $deliver_price;
	}
	
	//---------------------------------------------------------------------------------------------
	// 计算快递的申报价值
	private function computeExpressValueSum($ids){
		return ($ids!='')?$this->express->where("id in ($ids)")->sum('value'):0;
	}

	//--------------------------------------------------------------------------------------------
	// 取报关费
	private function getCustomFee() {
		$entity = M ( 'FinaceConfig' )->where ( "item='custom'" )->find ();
		return  ($entity && ($entity ['value'] > 0)) ? $entity ['value'] :0.07;
	}
	
	// -------------------------------------------------------------------------------------------
	//检查包裹是否有申报过
	private function checkExpressReport($ids){
		$result = false;
		if(!empty($ids)){
			foreach($ids as $r){
				$_count = $this->goods->where("trans_id=$r")->count();
					
				if($_count == 0){
					$result = true;
					break;
				}
			}
		}
	
		return $result;
	}

	// --------------------------------------------------------------------------------------------
	// 预处理仓库列表
	private function setWareList(){
		$_list = M('Ware')->where('status=1')->select();
		$this->assign('WareList',$_list);
	}
	
	// --------------------------------------------------------------------------------------------
	// 预处理openid
	public function processOpenid($redirect_uri){
		$url = $this->getRequestCodeUrl($redirect_uri);
		header ( "Location:$url" );
	}
		
	// --------------------------------------------------------------------------------------------
	// 认领包裹
	public function claim(){
		if(isset($_REQUEST['openid']) || isset($_SESSION ['WechatAuthOpenId']) ){
			$openid = isset($_REQUEST['openid'])? $_REQUEST['openid'] : $_SESSION ['WechatAuthOpenId'];
		}else{
			$this->processOpenid('claim.html');
		}
		
		$user = $this->getUserByWechatOpenId($openid);
		$this->assign('user',$user);
		$this->assign('openid',$openid);
		$this->display();
	}
	
	// -------------------------------------------------------------------------------------------
	// 申报
	public function declaration(){
		$trace_code = trim($_REQUEST['trace_code']);
		$openid = trim($_REQUEST['openid']);
		
		if(empty($openid) || ($trace_code == '') ){
			$url ='/wechat/claim.html?openid='.$openid;
			$this->disWechatMessage('跟踪单号为空或获取openid失败', $url,false,1);
		}else{
			$this->assign('openid',$openid);
			$additional = isset($_REQUEST['claim_tag']) ? ' and user_id=0' : '';
			$express = $this->findExpress($trace_code, $additional);
			if($express){
				$this->assign('express',$express);
				$user = $this->getUserByWechatOpenId($openid);
				$this->assign('user',$user);
				$this->assign('openid',$openid);
				if(isset($_REQUEST['claim_tag'])) $this->assign('claim','1');
				$this->display();
			}else{
				$url ='/wechat/claim.html';
				$this->disWechatMessage('您输入的单号不存在或已被认领过啦', $url,false,1);
			}			
		}
	}
	
	// --------------------------------------------------------------------------------------------
	// 执行申报
	public function dodeclaration(){
		if(trim($_POST['name']) =='' ){
			$url ='/wechat/declaration.html?openid='.$_POST['openid'].'&trace_code='.$_POST['express_code'];
			$this->disWechatMessage('填写的申报信息有误 ', $url,false,1);
		}
		
		$_POST['count'] = (trim($_POST['count']) != '') ? $_POST['count'] : 1;
		$_POST['price'] = (trim($_POST['price']) != '') ? $_POST['price'] : 0;
		
		@$this->express->execute("update __TABLE__ set value=".$_POST['price'].", user_id=".$_POST['user_id']. ",user_name='".$_POST['login_name']."',service=0 where id=".trim($_POST['express_id']));
		@$this->writeExpressGoods(trim($_POST['express_id']), $_POST);
		
		if(isset($_REQUEST['claim'])){
			$url ='/wechat/claim.html?openid='.$_POST['openid'];
		}else{
			$url ='/wechat/warehouse.html?openid='.$_POST['openid'];
		}
		$this->assign('openid',$_REQUEST['openid']);
		$this->disWechatMessage('恭喜！操作成功', $url,true,1);
	}
	
	// --------------------------------------------------------------------------------------------
	// 写入包裹的申报信息
	private function writeExpressGoods($express_id,$goods){
		$item = array(
				'trans_id' => $express_id,
				'title' => $goods['name'],
				'count' => $goods['count'],
				'value' => $goods['price'],
				'create_time' => time()
				);
		$this->goods->data($item)->add();		
	}
	
	// --------------------------------------------------------------------------------------------
	// 根据单号和用户查找快递
	private function findExpress($express_code, $additional){
		if($express_code != '' ){
			return M("Express")->where("trace_code='$express_code' $additional ")->find();	
		}
		return false;
	}
	
	// --------------------------------------------------------------------------------------------
	// 进行绑定
	public function doBind(){
		if(!isset($_POST['openid']) || ($_POST['openid'] == '') || ( trim($_POST['user_name']) == '' ) || ( trim($_POST['password']) == '')  ){
			$url = '/wechat/register';
			$this->disWechatMessage('读取OPENID失败，或没有填写您在86OF的帐户信息！', $url,false);
		}else{
			$user_name = mysql_escape_string(trim($_POST['user_name']));
			$password = mysql_escape_string(trim($_POST['password']));
			$openid = mysql_escape_string(trim($_POST['openid']));
			$this->assign('openid',$openid);
			
			$user = M ( "User" )->where ( "(status=1) AND (login_name='$user_name') " )->find ();
			if (!empty($user) && ($user['password'] != '')  &&  ($user['password'] == md5(md5($password) . $user['salt'] ))) {
				M()->execute('UPDATE user SET last_login='.time()." ,wechat_openid='$openid'  WHERE id=".$user ['id']);				
				$url = '/wechat/wareaddress.html?openid='.$openid;
				$this->disWechatMessage('恭喜您，您的微信已与86OF绑定，以后可以直接微信中享用86OF提供的服务啦！', $url,true,1);
			}else{
				$url = '/wechat/register/openid/'.$openid;
				$this->disWechatMessage('对不起，您输入的用户或密码不正确！', $url,false);
			}
		}
	}
	
	// --------------------------------------------------------------------------------------------
	// 通过微信注册
	public function doReg(){
		if(!isset($_POST['openid']) || ($_POST['openid'] == '') || ( trim($_POST['user_name']) == '' ) || ( trim($_POST['password']) == '')  ){
			$url = '/wechat/register';
			$this->disWechatMessage('读取OPENID失败，或没有填写您在86OF的帐户信息！', $url,false);
		}else{
			$user_name = mysql_escape_string(trim($_POST['user_name']));
			$password = mysql_escape_string(trim($_POST['password']));
			$openid = mysql_escape_string(trim($_POST['openid']));
			$tel =  mysql_escape_string(trim($_POST['tel']));
			$email =  mysql_escape_string(trim($_POST['email']));
			$this->assign('openid',$openid);
		
			$url = '/wechat/wareaddress.html?openid='.$openid;
			$_count = M ( "User" )->where ( "login_name='$user_name' " )->count ();
			
			if($_count > 0){
				$this->disWechatMessage('对不起，用户名已经存在了，请更换一个再试试！', $url,false);
			}else{
				$clientIp = get_client_ip ();
				
				$now = time ();
				$data = $this->buildUserData($user_name,$password,$email,$tel,$clientIp,$now,$openid);
				
				$uid = M ( "User" )->data ( $data )->add ();	
		
				if (! empty ( $uid )) {
					$this->makeFinace ( $uid ); //生成对应财务数据
					$this->makeUserInfo($uid,$user_name,$tel);
				}
				$this->disWechatMessage('恭喜您，已注册成功,以后就可以直接在微信里享受86OF提供的服务啦', $url,true,0);
			}
		}
	}
	
	
	//------------------------------------------------------------------------------------------------
	//产生财务数据
	private function makeFinace($uid) {
		$data ['user_id'] = $uid;
		$data ['money'] = 0;
		$data ['rebate'] = 0;
		$data ['point'] = 0;
		$data ['consumption_total'] = 0;
		$data ['consumption_point'] = 0;
		$data ['status'] = 1;
		$data ['last_update'] = time ();
	
		D ( 'Finance' )->data ( $data )->add ();
	}
	
	// -------------------------------------------------------------------------------------------
	//更新用户
	private function makeUserInfo($uid,$un,$tel){
	
		$count = M('UserInfo')->where("user_id=$uid")->count();
	
		if( $count == 0 ){
			M('UserInfo')->data(array(
					'user_id'=>$uid,
					'user_name'=>$un,
					'tel'=>$tel
			))->add();
				
		}
	}
	
	// -------------------------------------------------------------------------------------------
	// 显示微信端的操作消息
	private function disWechatMessage($msg,$url,$tag,$doCount='1'){
		$this->assign('jumpUrl',$url);
		$msgTitle = $tag ? '操作成功' : '操作失败';
		$class = $tag ? 'success' : 'panel-warning';
		$this->assign('panle_class',$class);
		$this->assign('docount',$doCount);
		$this->assign('waitSecond',5);
		$this->assign('msgTitle',$msgTitle);
		$this->assign('message', $msg);
		$this->display('Public/wechatsuccess');
	}
		
	// -------------------------------------------------------------------------------------------
	// 构造用户数据
	private function buildUserData($member,$password,$email,$tel,$clientIp,$now,$openid){
		$data = array();
		$salt = rand_string(12);
		$password1 = md5( $password );
		$data ['login_name'] 	= $member;
		$data ['password'] 		= md5($password1.$salt);
		$data ['salt'] 			= $salt;
		$data ['email'] 		= mysql_escape_string ( htmlspecialchars (trim($email)));
		$data ['email2'] 		= base64_encode(ulowi_encode(strtolower(trim($email))));
		$data ['refer_url'] 	= '';
		$data ['refer_domain'] 	= '';
		$data ['active_status'] = 1;
		$data ['spreader_id'] 	= 0;
		$data ['mobile']		= $tel;
		$data ['wechat_openid'] 	= $openid;
		
		$data ['status'] 		= 1;
		$data ['is_qquser'] 	= 0;	//这里记为非qq用户
		$data ['reg_ip'] 		= $clientIp;
		$data ['last_ip'] 		= $clientIp;
		$data ['create_time'] 	= $now;
		$data ['last_login'] 	= $now;

		return $data;
	}
	
	// --------------------------------------------------------------------------------------------
	// 根据微信openid 加载用户信息
	private function getUserByWechatOpenId($_openid) {
		if (! empty ( $_openid )) {
			return M ( 'User' )->field ( 'id,login_name' )->where ( "wechat_openid='$_openid'" )->find ();
		}
		return false;
	}

	
}
?>