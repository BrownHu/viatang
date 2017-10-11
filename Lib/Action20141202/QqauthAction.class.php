<?php
/**
 +------------------------------------------------------------------------------
 * DaigouCMS 代购建站系统(淘宝版)
 *
 * QQ登录
 +------------------------------------------------------------------------------
 * @category   ulowi
 * @author    	soitun <stone@zline.net.cn>
 * @copyright 上海子凌网络科技有限公司
 * @license   	http://www.zline.net.cn/license-agreement.html
 * @link      		http://www.zline.net.cn/
 +------------------------------------------------------------------------------
 */

load ( '@/functions' );
class QqauthAction extends Action {
	
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
			$return_url = trim ( $_GET ['r'] );
			$appid = trim ( C ( 'QQ_APPID' ) );
			$scope = trim ( C ( 'QQ_SCOPE' ) );
			$callback = trim ( C ( 'QQ_CALLBACK' ) ) . '?r=' . $return_url;
			
			$_SESSION ['QQAuthState'] = md5 ( uniqid ( rand (), TRUE ) );
			$login_url = "https://graph.qq.com/oauth2.0/authorize?response_type=code&client_id=" . $appid . "&redirect_uri=" . urlencode ( $callback ) . "&state=" . $_SESSION ['QQAuthState'] . "&scope=" . $scope;
			
			//if (C ( 'QQ_DEBUG' )) {
			//	echo "<a href='$login_url' > 用qq登录</a>";
			//} else {
				header ( "Location:$login_url" );
			//}
		}
	}
	
	//------------------------------------------------------------------------------------------------
	public function callback() {
		$_SESSION ['QQState'] = trim($_REQUEST ['state']);
		$_SESSION ['QQAuthCode'] = trim($_REQUEST ["code"]);
		$return_url = trim ( $_GET ['r'] );
		$this->assign('return_url',$return_url);
		$this->assign('QQAuthCode', $_REQUEST ["code"]);
		$this->assign('QQState', $_REQUEST ['state']);
		$this->display ();
	}
	
	//------------------------------------------------------------------------------------------------
	// 接收页面AJAX请求，并向qq服务器请求会话授权
	public function ajaxLogin() {
		$return_url = trim($_GET['r']);
		$this->getAuthAccessToken ( $_SESSION ['QQState'], $_SESSION ['QQAuthCode'] );
		$this->getOpenId ();

		if (isset ( $_SESSION ["QQAuthAccessToken"] ) && isset ( $_SESSION ["QQAuthOpenId"] )) {
			$user = $this->getUserByOpenid ( $_SESSION ["QQAuthOpenId"] );
			if (! empty ( $user )) {
				$clientIp = get_client_ip ();
				$user ['last_login'] = time ();
				$user ['last_ip'] = $clientIp;
				
				$UserDAO = M ( 'User' );
				$UserDAO->where ( 'id=' . $user ['id'] )->save ( $user );
				
				$this->saveLoginSession ( $user ); // 保存登录会话
				$this->logLogin ( $user ['id'], $user ['login_name'], $clientIp );
				if($return_url && $return_url != ''){
					echo "/qqauth/goresult/r/$return_url.html";
				}else{
					echo '/qqauth/goresult.html';
				}
			} else {
				echo '/qqauth/showQqRegister.html';
			}
		} else {
			echo '/qqquth/showerror.html';
		}
	}
	
	//------------------------------------------------------------------------------------------------
	// 用qq信息注册
	public function showQqRegister() {
		$userInfo = $this->getUserInfo ();
		$this->assign ( 'QQUserInfo', $userInfo );
		$this->assign ( 'openid', $_SESSION ["QQAuthOpenId"] );
		$this->display ( 'register' );
	}
	
	//------------------------------------------------------------------------------------------------
	public function showerror() {
		$this->display ();
	}
	
	//------------------------------------------------------------------------------------------------
	public function registerAsQq() {
		$code = trim ( $_POST ['verify'] );
		if (empty ( $code )) {
			$this->ajaxReturn ( null, '请输入验证码哦', 0 );
		} else {
			if (! is_numeric ( $code ) || ! $this->checkVerify ( $code )) {
				$this->ajaxReturn ( null, '验证码输入错误', 0 );
			}
		}
		
		$email =mysql_escape_string ( htmlspecialchars ( trim ( $_POST ['email'] )));
		$qq = mysql_escape_string ( htmlspecialchars (trim ( $_POST ['qq'] )));
		
		if (empty ( $email ) || empty ( $qq )) {
			$this->ajaxReturn ( null, '请填写email 和 qq 号', 0 );
		}
		
		$openId = mysql_escape_string ( htmlspecialchars (trim ( $_POST ['openid'] )));
		if (empty ( $openId )) {
			$this->ajaxReturn ( null, '非法操作，系统已拒绝', 0 );
		}
		
		if ($openId == $_SESSION ['QQAuthOpenId']) {
			$nick =mysql_escape_string ( htmlspecialchars (trim ( $_POST ['nick'] )));
			$nick = empty ( $nick ) ? $qq : $nick;
			
			$DAO = M ( 'User' );
			$count = $DAO->where ( "email='$email' OR qq='$qq' OR qq_openid='$openId' " )->count ();
			if ($count == 0) {
				$clientIp = get_client_ip ();
				$data ['login_name'] = $email;
				$data ['email'] = base64_encode(ulowi_encode($email));
				$data ['email2'] =  base64_encode(ulowi_encode($email));
				$data ['nick'] = $nick;
				$data ['qq'] = $qq;
				$data ['qq_openid'] = $openId;
				$data ['is_qquser'] = 1;
				
				$data ['active_status'] = 1;
				$data ['status'] = 1;
				$data ['reg_ip'] = $clientIp;
				$data ['last_ip'] = $clientIp;
				$data ['create_time'] = time ();
				$data ['last_login'] = time ();
				$uid = $DAO->data ( $data )->add ();
				
				if (! empty ( $uid )) {
					$this->makeFinace ( $uid ); // 生成对应财务数据
					write_point_log ( $uid, $email, 200, 401, '注册送积分' );
					
					if (intval(C ( 'MANG_NOTIFY_TAG' ) ) == 1) { // 新用户注册时发送邮件通知管理员
						$mail_content = 'qq用户注册:' . $email;
						$this->send_manage_mail ( '新注册会员', $mail_content . ',IP:' . $clientIp );
					}
					// 自动登录
					Session::set ( C ( 'MEMBER_INFO' ), $data );
					Session::set ( C ( 'MEMBER_AUTH_KEY' ), $uid );
					Session::set ( C ( 'MEMBER_NAME' ), $email );
					Session::set ( C ( 'CART_COUNT' ), 0 ); // 统计购物车
					Session::set ( 'unrd_msg_count', 0 ); // 统计未读短信
					
					$this->ajaxReturn ( null, '恭喜您注册成功', 1 );
				} else {
					$this->ajaxReturn ( null, '注册失败，请稍后重试', 0 );
				}
			} else {
				$this->ajaxReturn ( null, 'email 或者 qq 号已经被注册过', 0 );
			}
		}
	}
	
	//------------------------------------------------------------------------------------------------
	public function ulowiLogin() {
		if (isset ( $this->sid ) && ($this->sid > 0)) {
			$this->ajaxReturn ( null, '已经登录', 1 );
		}
		// 验证码
		$code = trim ( $_POST ['verify'] );
		if (empty ( $code ) || ! is_numeric ( $code ) || ! $this->checkVerify ( $code )) {
			$this->ajaxReturn ( null, '验证码输入错误', 0 );
		}
		
		$email = htmlspecialchars ( $_POST ['email'] );
		$email = mysql_escape_string ( $email );
		$password = htmlspecialchars ( $_POST ['password'] );
		$password = mysql_escape_string ( $password );
		$email2 = base64_encode ( ulowi_encode ($email));
		
		if ($email == '' || $password == '') {
			$this->ajaxReturn ( null, '登录email和密码不能为空!', 0 );
		}
		$nick = trim ( $_POST ['nick'] );
		$openid = trim ( $_POST ['openid'] );
		$qq = trim ( $_POST ['qq'] );
		
		$UserDAO = M ( "User" );
		$user = $UserDAO->where ( "(active_status=1) AND (is_qquser=0) AND (status=1)  AND (login_name='$email' OR email2='$email2' )" )->find ();
		if ($user && ($user ['password'] == md5($password . $user['salt'] ))) {
			$clientIp = get_client_ip ();
			$user ['last_login'] = time ();
			$user ['last_ip'] = $clientIp;
			$UserDAO->where ( 'id=' . $user ['id'] )->save ( $user );
			
			$this->saveLoginSession ( $user ); // 保存登录会话
			$this->logLogin ( $user ['id'], $user ['login_name'], $clientIp );
			$this->bindQQAccount ( $openid, $qq, $nick, $user );
			$this->ajaxReturn ( null, '登录成功,已绑定QQ帐户', 1 );
		} else {
			$this->ajaxReturn ( null, '用户名或密码错误 ', 0 );
		}
	}
	
	//------------------------------------------------------------------------------------------------
	public function goresult() {
		$return_url = trim($_GET['r']);
		if (! empty ( $return_url ) && $return_url != '') {
			$return_url = '/' . str_replace ( ',', '/', $return_url ) . '.html'; //修正路径
		} else {
			$return_url = '/';
		}
		$this->assign ( 'return_url', $return_url );
		$this->display ( 'result' );
	}
	
	
	// ----------------------------------------------------------------------------------------------------------------------------------------------------
	private function getAuthAccessToken($state, $code) {
		if ($state == $_SESSION ['QQAuthState']) {
			$token_url = "https://graph.qq.com/oauth2.0/token?grant_type=authorization_code&client_id=" . C ( 'QQ_APPID' ) . "&redirect_uri=" . urlencode ( C ( 'QQ_CALLBACK' ) ) . "&client_secret=" . C ( 'QQ_APPKEY' ) . "&code=" . $code;
			if (C ( 'QQ_DEBUG' )) {
				Log::write ( $token_url );
			}
			$response = curl_get_contents ( $token_url );
			if (strpos ( $response, "callback" ) !== false) {
				$lpos = strpos ( $response, "(" );
				$rpos = strrpos ( $response, ")" );
				$response = substr ( $response, $lpos + 1, $rpos - $lpos - 1 );
				$msg = json_decode ( $response );
				if (isset ( $msg->error )) {
					Log::write ( $msg->error . '  > ' . $msg->error_description );
					exit ();
				}
			}
			
			$params = array ();
			parse_str ( $response, $params );
			$_SESSION ["QQAuthAccessToken"] = $params ["access_token"];
			$_SESSION ['QQAuthExpiresIn'] = $params ['expires_in'];
		} else {
			Log::write ( "The state does not match. You may be a victim of CSRF." );
		}
	}
	
	//------------------------------------------------------------------------------------------------
	// 获取用户资料
	private function getUserInfo() {
		$get_user_info = "https://graph.qq.com/user/get_user_info?" . "access_token=" . $_SESSION ['QQAuthAccessToken'] . "&oauth_consumer_key=" . C ( 'QQ_APPID' ) . "&openid=" . $_SESSION ["QQAuthOpenId"] . "&format=json";
		$info = curl_get_contents ( $get_user_info );
		$arr = json_decode ( $info, true );
		return $arr;
	}
	
	//------------------------------------------------------------------------------------------------
	// 获取登录用户微博信息
	private function getInfo() {
		$gate_url = "https://graph.qq.com/user/get_info?" . "access_token=" . $_SESSION ['QQAuthAccessToken'] . "&oauth_consumer_key=" . C ( 'QQ_APPID' ) . "&openid=" . $_SESSION ["QQAuthOpenId"] . "&format=json";
		$info = curl_get_contents ( $gate_url );
		$arr = json_decode ( $info, true );
		return $arr;
	}
	
	//------------------------------------------------------------------------------------------------
	private function getOpenId() {
		$graph_url = "https://graph.qq.com/oauth2.0/me?access_token=" . $_SESSION ['QQAuthAccessToken'];
		if (C ( 'QQ_DEBUG' )) {
			Log::write ( $graph_url );
		}
		$str = curl_get_contents ( $graph_url );
		if (strpos ( $str, "callback" ) !== false) {
			$lpos = strpos ( $str, "(" );
			$rpos = strrpos ( $str, ")" );
			$str = substr ( $str, $lpos + 1, $rpos - $lpos - 1 );
		}
		
		$user = json_decode ( $str );
		if (isset ( $user->error )) {
			Log::write ( $user->error . '  > ' . $user->error_description );
			$_SESSION ["QQAuthOpenId"] = '';
		} else {
			$_SESSION ["QQAuthOpenId"] = $user->openid;
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
	
	//------------------------------------------------------------------------------------------------
	// 发送管理员通知
	private function send_manage_mail($subject, $mail_content) {
		addToMailQuen('notify@'.C('DOMAIN') ,'daigou', C('SERVICE_MAIL'),$subject,$mail_content,'');
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
			$LogDAO = M ( 'UserLogin' );
			$log = array ();
			$log ['user_id'] = $uid;
			$log ['user_name'] = $un;
			$log ['ip'] = $ip;
			$log ['login_at'] = time ();
			$log ['login_out'] = 0;
			$LogDAO->data ( $log )->add ();
		} catch ( Exception $e ) {
			Log::write ( '记录登录日志时出错' );
		}
	}
	
	//------------------------------------------------------------------------------------------------
	// 绑定QQ 帐号
	private function bindQQAccount($openid, $qq, $nick, $user) {
		$result = false;
		if (! empty ( $openid ) && ! empty ( $user ) && empty ( $user ['qq_openid'] )) {
			$DAO = M ( 'User' );
			$count = $DAO->where ( "qq_openid='$openid'" )->count ();
			if ($count == 0) {
				$user ['qq_openid'] = trim ( $openid );
				$user ['qq'] = trim ( $qq );
				$user ['nick'] = trim ( $nick );
				$DAO->where ( 'id=' . $user ['id'] )->save ( $user );
				$result = true;
			}
		}
		return $result;
	}
	
	//------------------------------------------------------------------------------------------------
	private function getUserByOpenid($openid) {
		$result = false;
		if (! empty ( $openid )) {
			$DAO = M ( 'User' );
			$result = $DAO->where ( "qq_openid='$openid'" )->find ();
		}
		return $result;
	}
}
?>