<?php
/**
 +------------------------------------------------------------------------------
 * DaigouCMS 代购建站系统(淘宝版)
 *
 * 微信登录接口
 +------------------------------------------------------------------------------
 * @category   ulowi
 * @author    	soitun <stone@zline.net.cn>
 * @copyright 上海子凌网络科技有限公司
 * @license   	http://www.zline.net.cn/license-agreement.html
 * @link      		http://www.zline.net.cn/
 +------------------------------------------------------------------------------
 */
load ( '@/functions' );
class WechatAction extends Action {
	protected $sid;
	
	//------------------------------------------------------------------------------------------------
	function _initialize() {
		$this->sid = intval ( Session::get ( C ( 'MEMBER_AUTH_KEY' ) ) );
	}
	
	//------------------------------------------------------------------------------------------------
	public function index() {
		$this->redirect ( 'login' );
	}
	
	//------------------------------------------------------------------------------------------------
	public function login() {
		if (isset ( $this->sid ) && ($this->sid > 0)) {
			$this->redirect (U( 'Index/index.html') );
		} else {
			$appid = 'wxee7a2d884f48cc65';//trim ( C ( 'Wechat_APPID' ) );
			$callback = 'http://www.viatang.com/wechat/callback.html';//trim ( C ( 'Wechat_CALLBACK' ) );
				
			$_SESSION ['WechatState'] = md5 ( uniqid ( rand (), TRUE ) );
			$login_url = "https://open.weixin.qq.com/connect/qrconnect?response_type=code&appid=" . $appid . "&redirect_uri=" . urlencode ( $callback ) . "&state=" . $_SESSION ['WechatState'] . "&scope=snsapi_login#wechat_redirect";
			header ( "Location:$login_url" );
		}
	}
	
	//------------------------------------------------------------------------------------------------
	public function callback() {
		$_state = trim($_REQUEST ['state']);
		$_code  = trim($_REQUEST ["code"]);
		
		$open_id = $this->getAuthAccessToken($_state, $_code);
		if( $open_id ){
			if ($this->doLogin() == 1){
				$this->redirect('Index/index'); //登录成功
			}else{
				$wechat_user = $this->getUserInfo(); //注册
				$this->assign('QQUserInfo',$wechat_user);
				$this->assign('openid',$open_id);
				$this->display('register');
			}
		}else{
			$this->error('系统获取数据异常，请稍后重试');
		}
		
	}
	
	//------------------------------------------------------------------------------------------------
	public function registerAsWechat() {
		$code = trim ( $_POST ['verify'] );
		//echo $code;
		if (empty ( $code )) {
			$this->ajaxReturn ( null, '请输入验证码哦', 0 );
		} else {
			if (! is_numeric ( $code ) || ! $this->checkVerify ( $code )) {
				$this->ajaxReturn ( null, '验证码输入错误', 0 );
			}
		}
	
		$email =mysql_escape_string ( trim ( $_POST ['email'] ));
		if (empty ( $email ) ) {
			$this->ajaxReturn ( null, '请填写email ', 0 );
		}
	
		$openId = mysql_escape_string ( trim ( $_POST ['openid'] ));
		if (empty ( $openId )) {
			$this->ajaxReturn ( null, '非法操作，系统已拒绝', 0 );
		}
	
		if ($openId == $_SESSION ['WechatAuthOpenId']) {
			$nick = mysql_escape_string ( trim ( $_POST ['nick'] ));
			$DAO = M ( 'User' );
			$count = $DAO->where ( "email='$email' OR qq_openid='$openId' " )->count ();
			if ($count == 0) {
				$clientIp = get_client_ip ();
				$data ['login_name'] = $email;
				$data ['email'] 	=  base64_encode(ulowi_encode($email));
				$data ['email2'] 	=  base64_encode(ulowi_encode($email));
				$data ['nick'] 		= $nick;
				$data ['qq'] 		= '';
				$data ['qq_openid'] = $openId;
				$data ['is_qquser'] = 2;
	
				$data ['active_status'] = 1;
				$data ['status'] 		= 1;
				$data ['reg_ip'] 		= $clientIp;
				$data ['last_ip'] 		= $clientIp;
				$data ['create_time'] 	= time ();
				$data ['last_login'] 	= time ();
				$uid = $DAO->data ( $data )->add ();
				if (! empty ( $uid )) {
					$this->makeFinace ( $uid ); // 生成对应财务数据
					write_point_log ( $uid, $email, 200, 401, '注册送积分' );
						
					if (intval(C ( 'MANG_NOTIFY_TAG' ) ) == 1) { // 新用户注册时发送邮件通知管理员
						$mail_content = 'wechat用户注册:' . $email;
						$this->send_manage_mail ( '新注册会员', $mail_content . ',IP:' . $clientIp );
					}
					// 自动登录
					Session::set ( C ( 'MEMBER_INFO' ), $data );
					Session::set ( C ( 'MEMBER_AUTH_KEY' ), $uid );
					Session::set ( C ( 'MEMBER_NAME' ), $email );
					Session::set ( C ( 'CART_COUNT' ), 0 ); // 统计购物车
					Session::set ( 'unrd_msg_count', 0 ); // 统计未读短信
					$this->assign('jumpUrl','/Index/index.html');
					$this->success('恭喜您注册成功');	
					//$this->ajaxReturn ( null, '恭喜您注册成功', 1 );
				} else {
					//$this->ajaxReturn ( null, '注册失败，请稍后重试', 0 );
					$this->error('注册失败，请稍后重试');
				}
			} else {
				//$this->ajaxReturn ( null, 'email 或者 qq 号已经被注册过', 0 );
				$this->error('eamil或微信已经存在');
			}
		}
	}
	
	
	//------------------------------------------------------------------------------------------------
	// 接收页面AJAX请求，并向qq服务器请求会话授权
	private function doLogin() {
		if (isset ( $_SESSION ["WechatAuthAccessToken"] ) && isset ( $_SESSION ["WechatAuthOpenId"] )) {
			$user = $this->getUserByOpenid ( $_SESSION ["WechatAuthOpenId"] );
			if (! empty ( $user )) {
				$clientIp = get_client_ip ();
				$user ['last_login'] = time ();
				$user ['last_ip'] = $clientIp;
				M ( 'User' )->where ( 'id=' . $user ['id'] )->save ( $user );
	
				$this->saveLoginSession ( $user ); // 保存登录会话
				$this->logLogin ( $user ['id'], $user ['login_name'], $clientIp );
				return 1;
				//$this->redirect('Index/index');
			} else { //用户不存在
				return 2;
				//$this->redirect('wechat/showerror');
			}
		}
		return 0; 
	}
	
	
	
	// ----------------------------------------------------------------------------------------------------------------------------------------------------
	private function getAuthAccessToken($state, $code) {
		if ($state == $_SESSION ['WechatState']) {
			$appid = 'wxee7a2d884f48cc65';//trim ( C ( 'Wechat_APPID' ) );
			$secret = '50fe03331a5905e1075fced73f673077';//trim ( C ( 'Wechat_SECRET' ) );
			$token_url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=$appid&secret=$secret&code=$code&grant_type=authorization_code";
			
			$response = curl_get_contents ( $token_url );
			$_result = json_decode($response,true);
			if (isset($_result['errcode'])) {
				Log::write ( $_result['errcode'] . ' : ' . $_result['errmsg'] );
				return false;
			}
				
			$_SESSION ["WechatAuthAccessToken"] = $_result ["access_token"];
			$_SESSION ['WechatAuthExpiresIn'] = $_result ['expires_in'];
			$_SESSION ['WechatAuthOpenId'] = $_result ['openid'];
			return $_SESSION ['WechatAuthOpenId'];
		} else {
			Log::write ( "The state does not match. You may be a victim of CSRF." );
		}
		return false;
	}
	
	//------------------------------------------------------------------------------------------------
	// 获取用户资料
	private function getUserInfo() {
		$get_user_info = "https://api.weixin.qq.com/sns/userinfo?access_token=" . $_SESSION ['WechatAuthAccessToken'] . "&openid=" . $_SESSION ["WechatAuthOpenId"] . "&scope=snsapi_userinfo&lang=zh_CN";
		$info = curl_get_contents ( $get_user_info );
		return json_decode ( $info, true );
	}
	
	//------------------------------------------------------------------------------------------------
	// 根据openid 加载用户用户
	private function getUserByOpenid($openid) {
		if (! empty ( $openid )) {
			return M ( 'User' )->where ( "qq_openid='$openid' and is_qquser=2" )->find ();
		}
		return false;
	}
	

	//------------------------------------------------------------------------------------------------
	// 存储登录会话信息
	private function saveLoginSession($user) {
		if (! $user) {
			return false;
		}
		try {
			Session::set ( C ( 'MEMBER_INFO' ), $user );
			Session::set ( C ( 'MEMBER_AUTH_KEY' ), $user ['id'] );
			Session::set ( C ( 'MEMBER_NAME' ), $user ['login_name'] );
			Session::set ( 'ulowi_user_level', $user ['level'] );
		} catch ( Exception $e ) {
			Log::write ( '存储会话失败' );
		}
	}
	
	//------------------------------------------------------------------------------------------------
	// 记录登录日志
	private function logLogin($uid, $un, $ip) {
		try {
			$log = array ();
			$log ['user_id'] = $uid;
			$log ['user_name'] = $un;
			$log ['ip'] = $ip;
			$log ['login_at'] = time ();
			$log ['login_out'] = 0;
			M ( 'UserLogin' )->data ( $log )->add ();
		} catch ( Exception $e ) {
			Log::write ( '记录登录日志时出错' );
		}
	}
	
	//------------------------------------------------------------------------------------------------
	private function checkVerify($code) {
		$result = false;
		if ((strlen ( $code ) > 0) && (md5 ( $code ) == Session::get ( 'verify' ))) {
			$result = true;
		}
		return $result;
	}
	
	//------------------------------------------------------------------------------------------------
	// 发送管理员通知
	private function send_manage_mail($subject, $mail_content) {
		addToMailQuen('notify@'.C('DOMAIN') ,'daigou', C('SERVICE_MAIL'),$subject,$mail_content,'');
	}
	
	//------------------------------------------------------------------------------------------------
	// 产生财数据
	private function makeFinace($uid) {
		$data ['user_id'] = $uid;
		$data ['money'] = 0;
		$data ['rebate'] = 0;
		$data ['point'] = 200;
		$data ['consumption_total'] = 0;
		$data ['consumption_point'] = 0;
		$data ['status'] = 1;
		$data ['last_update'] = time ();
	
		$FinanceDAO = D ( 'Finance' );
		$FinanceDAO->data ( $data )->add ();
	}
}
?>