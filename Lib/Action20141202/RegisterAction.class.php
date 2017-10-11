<?php
/**
 +------------------------------------------------------------------------------
 * DaigouCMS 代购建站系统(淘宝版)
 *
 * 用户注册
 +------------------------------------------------------------------------------
 * @category   ulowi
 * @author    	soitun <stone@zline.net.cn>
 * @copyright 上海子凌网络科技有限公司
 * @license   	http://www.zline.net.cn/license-agreement.html
 * @link      		http://www.zline.net.cn/
 +------------------------------------------------------------------------------
 */

//include ("Conf/uc.inc.php");
//if (defined ( 'UC_API' )) {include_once 'uc/uc_client/client.php';}
load ( '@/functions' );
class RegisterAction extends Action {
	protected $sid;

	//------------------------------------------------------------------------------------------------
	function _initialize() {
		$this->sid = intval ( Session::get ( C ( 'MEMBER_AUTH_KEY' ) ) );
	}
	
	//------------------------------------------------------------------------------------------------
	public function index() {
		if (isset ( $this->sid ) && ($this->sid > 0)) {$this->redirect ('Index/index' );}		
		
		$_SESSION ['spreader'] =isset($_SESSION ['spreader']) ? ( int ) $_REQUEST ['spreader'] : 0;
						
		$refer_url = strtolower(Session::get ( 'referer_url' ));		
		if (empty ( $refer_url )) {Session::set ( 'referer_url', $_SERVER ["HTTP_REFERER"] );}		
		Session::set ( C( 'RETURN_URL' ), 'Index,index' );
		$this->display ();
	}
	
	//------------------------------------------------------------------------------------------------
	// 会员注册
	public function register() {
		$member   =  mysql_escape_string(  htmlspecialchars(remove_xss(trim ( $_POST ['username'] ))));
		$email        =  strtolower(mysql_escape_string(  htmlspecialchars(remove_xss(trim ( $_POST ['email']))) ));
		$password  =  mysql_escape_string(  htmlspecialchars(remove_xss(trim ( $_POST ['password']))) );
		
		$SpreaderId = ( int ) ($_POST ['spreader']); //取得推广者id
		$code = trim ( $_POST ['verify'] );
		
		if (empty ( $code ) || ! is_numeric ( $code ) || ! $this->checkVerify ( $code )) {
			$this->assign ( 'waitSecond', 10 );
			$this->assign ( 'jumpUrl', U('/Register/index') );
			$this->error ( L('register_verify_error') );
		}
		
		if (empty ( $member ) || empty ( $email ) || empty ( $password )) {
			$this->assign ( 'waitSecond', 10 );
			$this->assign ( 'jumpUrl', U('/Register/index') );
			$this->error ( L('register_user_psw_empty') );
		}
		
		if ((strpos ( $member, "'" ) > 0) || (strpos ( strtolower ( $member ), " delete " ) > 0) || (strpos ( strtolower ( $member ), " drop " ) > 0) || (strpos ( $member, '"' ) > 0) || (strpos ( $member, ":" ) > 0) || (strpos ( $member, "=" ) > 0) || (strpos ( $member, " " ) > 0) || (strpos ( $email, "'" ) > 0) || (strpos ( $email, "'" ) > 0) || (strpos ( $password, "'" ) > 0)) {
			$this->assign ( 'waitSecond', 10 );
			$this->assign ( 'jumpUrl', U('/Register/index') );
			$this->error ( L('register_input_error') );
		}
		
		if ($this->isExists ( $member, $email )) {
			$this->assign ( 'waitSecond',30 );
			$this->assign ( 'jumpUrl', U('/Register/index') );
			$this->error ( L('register_forget_psw') );
		}
		
		if (! empty ( $SpreaderId ) && is_numeric ( $SpreaderId )) {
			$data ['spreader_id'] = intval($SpreaderId);
		}else{
			$data ['spreader_id'] = 0;
		}

		$refer_url = $this->getReferUrl ();
		if (! empty ( $refer_url )) {
			$refer_domain = parse_domain ( $refer_url, false );
			if ((strtolower ( $refer_domain ) == C('SITE_URL')) || (strtolower ( $refer_domain ) == C('DOMAIN'))) {
				$refer_domain = '';
			}
		} else {
			$refer_url = '';
			$refer_domain = '';
		}
		$email = strtolower ( $email );
		
		
		$clientIp = get_client_ip ();
		$now = time ();
		if (($now - $this->getSameIPLast ( $clientIp )) <= 600) {
			$this->assign ( 'msgTitle', L('register_reg_fail') );
			$this->assign ( 'waitSecond', 10 );
			$this->assign ( 'jumpUrl', U('/Register/index') );
			$this->error ( L('register_re_limit') );
		}
		
		$salt = rand_string(12);
		$password1 = md5 ( $password );
		$data ['login_name'] = $member;
		$data ['password'] = md5($password1.$salt);
		$data ['salt'] = $salt;
		$data ['email'] = mysql_escape_string ( htmlspecialchars (trim($email)));
		$data ['email2'] =  base64_encode(ulowi_encode(strtolower(mysql_escape_string ( htmlspecialchars (trim($email))))));
		$data ['refer_url'] = mysql_escape_string ( htmlspecialchars (trim($refer_url)));
		$data ['refer_domain'] = mysql_escape_string ( htmlspecialchars (trim($refer_domain)));
		$data ['active_status'] = 1;
		$data ['status'] = 1;
		$data ['is_qquser'] = 0;	//这里记为非qq用户
		$data ['reg_ip'] = $clientIp;
		$data ['last_ip'] = $clientIp;
		$data ['create_time'] = $now;
		$data ['last_login'] = $now;
		
		$uid = M ( 'User' )->data ( $data )->add ();	
		
		if (! empty ( $uid )) {
			$this->makeFinace ( $uid ); //生成对应财务数据
			write_point_log ( $uid, $member, 200, 401, L('register_point') );
			//$this->sendActiveMail($member,$email);

	 		if ( intval(C ( 'MANG_NOTIFY_TAG') ) == 1) { //新用户注册时发送邮件通知管理员
				$mail_content = $member . ':' . $email;
				$this->send_manage_mail ( '新注册会员', $mail_content . ',IP:' . $clientIp );
			} 
			
			//自动登录
			Session::set ( C ( 'MEMBER_INFO' ), $data );
			Session::set ( C ( 'MEMBER_AUTH_KEY' ), $uid );
			Session::set ( C ( 'MEMBER_NAME' ), $member );
			Session::set ( C ( 'CART_COUNT' ), 0 ); //统计购物车
			Session::set ( 'unrd_msg_count', 0 ); //统计未读短信
			
			//$this->assign ( 'nick', $member );
			$this->assign ( 'email', $email );			
			$this->display ( 'result' );
		} else {
			$this->assign ( 'waitSecond', 10 );
			$this->assign ( 'jumpUrl', U('/Register/index') );
			$this->error ( L('register_reg_fail2') );
		}
	}
	
	//------------------------------------------------------------------------------------------------
	//取得来源地址
	private function getReferUrl() {
		$refer_url = Session::get ( 'referer_url' );
		$refer_url = (strpos(strtolower($refer_url), 'ulowi') > 0)?'':$refer_url;
		return $refer_url;
	}
	
	//------------------------------------------------------------------------------------------------
	//奖历推广者
	private function spreader($id) {
		if ($id > 0) {
			$entity = M ( 'User' )->where ( "id=$id" )->find ();
			if ($entity) { //确定推广者为注册会员
				$finaceDAO = M ( 'Finace' );
				$finace = $finaceDAO->where ( "user_id=$id" )->find ();
				if ($finace) { //存在财务信息，则进行奖励
					//$finace ['money'] = $spreader_register_consume [2];
					if ($finaceDAO->where ( 'id=' . $finace ['id'] )->save ( $finace )) { //记奖历后，记财务变更记录
						$FinaceLogDAO = M ( 'FinaceLog' );
						$log ['user_id'] = $id;
						$log ['user_name'] = $entity ['login_name'];
						$log ['type_id'] = 405; //推广链接注册
						$FinaceLogDAO->data ( $log )->add ();
					}
				}
			}
		}
	}
	
	//------------------------------------------------------------------------------------------------
	/**
	 * 发送激活邮件
	 *
	 * @param unknown_type $member
	 * @param unknown_type $email
	 */
	private function sendActiveMail($member = '', $email = '') {
		if (! empty ( $member ) && ! empty ( $email )) {
			import ( 'ORG.Crypt.Crypt' );
			
			$this->assign ( 'member', $member );
			$this->assign ( 'date', date ( 'Y-m-d H:i:s', time () ) );
			
			$CryptHelp = new Crypt ();
			$code = stringToHex ( $CryptHelp->encrypt ( $member . '|' . $email, C ( 'DES_KEY' ), false ) );
			$this->assign ( 'ActiveCode', $code );
			$this->assign ( 'site_url', C ( 'SITE_URL' ) );
			$this->assign ( 'logo_url', C ( 'LOGO_URL' ) );
			$this->assign ( 'email', $email );
			$Content = $this->fetch ( 'Public/ActiveMail' );	
			$subject = L("register_active");
			
			addToMailQuen(C ( 'SERVICE_MAIL' ), C ( 'SERVICE_NAME' ), $email,$subject,$Content,'');
		}
	}
	
	//------------------------------------------------------------------------------------------------
	//重发激活邮件
	public function resendMail() {
		$member = trim ( $_GET ['member'] );
		$email = trim ( $_GET ['email'] );
		$token = trim ( $_GET ['token'] );
		
		if (($this->getToken () == $token) && ! empty ( $member ) && ! empty ( $email )) {
			$this->sendActiveMail ( $member, $email );
			$MailAddress = explode ( '@', $email );
			$this->assign ( 'email', $email );
			$this->assign ( 'email_host', $MailAddress [1] );
			$this->assign ( 'member', $member );
			
			$reg_token = rand_string ( 12 );
			Session::set ( 'reg_token', $reg_token );
			$this->assign ( 'token', $reg_token );
			$this->display ( 'result' );
		} else {
			$this->assign ( 'waitSecond', 10 );
			$this->assign ( 'jumpUrl', U('/Index/index') );
			$this->error ( '对不起，您的请求验证失败，无法重发激活邮件，您可以直接与我们的客服联系!' );
		}
	}
	
	//------------------------------------------------------------------------------------------------
	private function getToken() {
		return Session::get ( 'reg_token' );
	}
	
	//------------------------------------------------------------------------------------------------
	//激活帐号
	public function active() {
		$ActiveCode = trim ( $_GET ['code'] );
		if (strlen ( trim ( $ActiveCode ) ) == 0) {
			$this->assign ( 'waitSecond', 10 );
			$this->assign ( 'jumpUrl', U('/Index/index') );
			$this->error ( L('register_active_error') );
		}
		
		import ( 'ORG.Crypt.Crypt' );
		$CryptHelp = new Crypt ();
		$DeActiveCode = $CryptHelp->decrypt ( HexToStr ( $ActiveCode ), C ( 'DES_KEY' ), false );
		
		$UserInfo = explode ( '|', $DeActiveCode );
		if (count ( $UserInfo ) != 2) {
			$this->assign ( 'waitSecond', 10 );
			$this->assign ( 'jumpUrl', U('/Index/index') );
			$this->error ( L('register_active_error') );
		}
		
		$email = $UserInfo [1];
		$DAO = M ( 'User' );
		$entity = $DAO->where ( "email='$email' " )->find ();
		if ($entity && ((time () - $entity ['create_time']) <= 86400) && ($entity ['active_status'] == 0)) {
			$entity ['active_status'] = 1;
			$DAO->where ( 'id=' . $entity ['id'] )->save ( $entity );
			$this->assign ( 'login_name', $entity ['login_name'] );
			$this->assign ( 'waitSecond', 3 );
			$this->assign ( 'jumpUrl', U('/Public/login') );
			$this->display ( 'success' );
		} else {
			$reg_token = rand_string ( 12 );
			Session::set ( 'reg_token', $reg_token );
			$this->assign ( 'token', $reg_token );
			$this->assign ( 'email', $email );
			$this->assign ( 'member', $UserInfo [0] );
			$this->display ( 'error' );
		}
	}
	
	//------------------------------------------------------------------------------------------------
	public function setpass() {
		$token = trim ( $_REQUEST ['token'] );
		if (! empty ( $token )) {
			$LogDAO = M('FindpassLog');
			$log = $LogDAO->where("token_key='".md5($token)."'  AND modified=0")->find();
			if($log == false ){
				$this->assign ( 'waitSecond', 10 );
				$this->assign ( 'jumpUrl', U('/Register/password') );
				$this->error ( L('register_active_error') );
			}
			
			import ( 'ORG.Crypt.Crypt' );
			$CryptHelp = new Crypt ();
			$DeToken = $CryptHelp->decrypt ( HexToStr ( $token ), C ( 'DES_KEY' ), false );
			$result = explode ( '|', $DeToken );
			if (count ( $result ) != 2) {
				$this->assign ( 'waitSecond', 30 );
				$this->assign ( 'jumpUrl', U('/Register/password') );
				$this->error ( L('register_active_error') );
			} else {
				$uid = $result [0];
				$time = $result [1];
				$now = time ();
				if (($now - $time) > 86400) {
					$this->assign ( 'waitSecond', 30 );
					$this->assign ( 'jumpUrl', U('/Register/password') );
					$this->error ( L('register_active_error') );
				}
				$DAO = M ( 'User' );
				$item = $DAO->where ( "id=$uid" )->find ();
				if ($item) {
					$this->assign ( 'ulowi_token', $token );
					$this->assign ( 'uid', $uid );
					$this->assign ( 'login_name', $item ['login_name'] );
					$this->display ();
				} else {
					$this->assign ( 'waitSecond', 30 );
					$this->assign ( 'jumpUrl', U('/Register/password') );
					$this->error ( L('register_active_error') );
				}
			}
		} else {
			$this->assign ( 'waitSecond', 10 );
			$this->assign ( 'jumpUrl', U('/Register/password') );
			$this->error ( L('register_active_error') );
		}
	}
	
	//------------------------------------------------------------------------------------------------
	public function passcommit() {
		$token = mysql_escape_string ( htmlspecialchars (trim ( $_REQUEST ['token'] )));
		$uid = trim ( $_POST ['uid'] );
		$name = mysql_escape_string ( htmlspecialchars (trim ( $_REQUEST ['login_name'] )));
		$code = trim ( $_POST ['verify'] );
		$password = mysql_escape_string ( htmlspecialchars (trim ( $_REQUEST ['password'] )));
		if (empty ( $uid ) || empty ( $name ) || empty ( $code ) || empty ( $token ) || empty ( $password )) {
			$this->assign ( 'waitSecond', 10 );
			$this->assign ( 'jumpUrl', U('/Register/password') );
			$this->error ( L('register_active_error') );
		} else {
			if (! is_numeric ( $code ) || ! $this->checkVerify ( $code )) {
				$this->assign ( 'waitSecond', 10 );
				$this->assign ( 'jumpUrl', U('/Register/password') );
				$this->error ( L('register_verify_error') );
			}
			
			$LogDAO = M('FindpassLog');
			$log = $LogDAO->where("token_key='".md5($token)."'  AND modified=0")->find();	
			if($log == false ){
				$this->assign ( 'waitSecond', 10 );
				$this->assign ( 'jumpUrl', U('/Register/password') );
				$this->error ( L('register_active_error') );
			}		
			
			import ( 'ORG.Crypt.Crypt' );
			$CryptHelp = new Crypt ();
			$DeToken = $CryptHelp->decrypt ( HexToStr ( $token ), C ( 'DES_KEY' ), false );
			$result = explode ( '|', $DeToken );
			if (count ( $result ) != 2) {
				$this->assign ( 'waitSecond', 10 );
				$this->assign ( 'jumpUrl', U('/Register/password') );
				$this->error ( L('register_active_error') );
			} else {
				if ($uid == $result [0]) {
					$DAO = M ( 'User' );
					$user = $DAO->where ( "id=$uid AND login_name='$name'" )->find ();
					if ($user) {
						$salt = rand_string(12);
						$password1 = md5 ( $password ); 
						$user ['salt'] = $salt;
						$user ['password'] =  md5($password1.$salt);
						$DAO->where ( "id=$uid" )->save ( $user );
						
						//更新找回密码日志
						$log['modified'] = time();
						$LogDAO->where('id='.$log['id'] . " AND token='".$token."'")->save($log);
						
						$this->assign ( 'waitSecond', 10 );
						$this->assign ( 'jumpUrl', U('/Public/login') );
						$this->success ( L('register_succ') );
					} else {
						$this->assign ( 'waitSecond', 10 );
						$this->assign ( 'jumpUrl', U('/Register/password') );
						$this->error ( L('register_active_error') );
					}
				} else {
					$this->assign ( 'waitSecond', 10 );
					$this->assign ( 'jumpUrl', U('/Register/password') );
					$this->error (L('register_active_error') );
				}
			}
		}
	}
	
	//------------------------------------------------------------------------------------------------
	//找回密码
	public function resetpsw() {
		//$login_name = trim ( $_POST ['u_username'] );
		$email = strtolower(mysql_escape_string(htmlspecialchars(trim ( $_POST ['u_regemail'] ))));
		
		if ( ! empty ( $email )) {
			$DAO = M ( 'User' );
			$email2 = base64_encode(ulowi_encode(strtolower($email)));
			$entity = $DAO->where ( "(email2='$email2') AND (is_qquser=0)  AND (active_status=1) AND (status=1)" )->find ();
			
			if ($entity) {
				$this->assign ( 'member', $entity['login_name'] );
				$now = time ();
				$uid = $entity ['id'];
				import ( 'ORG.Crypt.Crypt' );
				$CryptHelp = new Crypt ();
				$code = stringToHex ( $CryptHelp->encrypt ( "$uid|$now", C ( 'DES_KEY' ), false ) );
				$body = L("register_click") . "<a href='".C('SITE_URL')."/Register/setpass/token/$code.shtml' target='_blank'>".L('register_reset_psw')."</a>" . "<br>".C('SITE_URL')."/Register/setpass/token/$code.html";
				$this->assign ( 'body', $body );
				
				$Content = $this->fetch ( 'resetpsw' );
				$subject = L("register_find_psw").C('DOMAIN');
				
				$this->sendMailNotice ( $subject, $email, $email, $Content );
				$ip = get_client_ip ();
				$this->writeFindPasswordLog ( $email, $email, $ip,$code );
				$this->assign ( 'message', L('register_mail_send').' <span style="color:#f60;">' . $email . '</span>' );
				$this->display ( 'message' );
			} else {
				$this->assign ( 'waitSecond', 10 );
				$this->assign ( 'jumpUrl', U('Public/login') );
				$this->error ( L('register_email_error') );
			}
		}
	}
	
	//------------------------------------------------------------------------------------------------
	public function password() {
		$this->display ();
	}
	
	//------------------------------------------------------------------------------------------------
	//记找回密码日志
	private function writeFindPasswordLog($loginname, $email, $ip,$token) {
		if (! empty ( $loginname ) && ! empty ( $email )) {
			$DAO = M ( 'FindpassLog' );
			$data ['login_name'] = $loginname;
			$data ['email'] = $email;
			$data ['ip'] = $ip;
			$data ['token'] = $token;
			$data ['token_key'] = md5($token);
			$data ['create_at'] = time ();
			$DAO->data ( $data )->add ();
		}
	}
	
	//------------------------------------------------------------------------------------------------
	//产生财务数据
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
	//发送管理员通知
	private function sendMailNotice($subject, $to, $tomail, $mail_content) {
		addToMailQuen('noreply@'.C('DOMAIN'),C('SITE_NAME'),$to,$subject,$mail_content,'');
	}
	
	//------------------------------------------------------------------------------------------------
	//发送管理员通知
	private function send_manage_mail($subject, $mail_content) {
		addToMailQuen('noreply@'.C('DOMAIN'),C('SITE_NAME'),C('MANG_NOTIFY_MAIL'),$subject,$mail_content,'');
	}
	
	//------------------------------------------------------------------------------------------------
	//检查验证码
	private function checkVerify($code) {
		$result = false;
		if ((strlen ( $code ) > 0) && (md5 ( $code ) == Session::get ( 'verify' ))) {
			$result = true;
		}
		return $result;
	}
	
	//------------------------------------------------------------------------------------------------
	private function isExists($uername, $email) {
		$result = false;
		if (! empty ( $uername ) && ! empty ( $email )) {
			$username1 = strtolower ( $uername );
			$email1 = strtolower ( $email );
			$count = M ( 'User' )->where ( "login_name='$uername' OR login_name='$username1' OR email='$email' OR email='$email1'" )->count ();
			$result = (intval($count) > 0)?true:false;
		}
		return $result;
	}
	
	//------------------------------------------------------------------------------------------------
	//取同IP最后注册时间
	private function getSameIPLast($ip) {
		$result = 0;
		if (! empty ( $ip )) {
			$DAO = M ( 'User' );
			$DataList = $DAO->field ( 'create_time' )->where ( "reg_ip='$ip'" )->select ();
			foreach ( $DataList as $item ) {
				if ($item ['create_time'] > $result) {
					$result = $item ['create_time'];
				}
			}
		}
		return $result;
	}
	
	//------------------------------------------------------------------------------------------------
	Public function _empty() {
		$this->redirect (U( 'Index/index') );
	}

}
