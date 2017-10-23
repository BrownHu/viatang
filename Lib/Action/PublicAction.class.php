<?php
/**
 +------------------------------------------------------------------------------
 * DaigouCMS 代购建站系统(淘宝版)
 *
 * 公共模块，定义每个模块都需要用到的业务
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

class PublicAction extends Action {
	protected $sid;
	
	//------------------------------------------------------------------------------------------------
	function _initialize() {
		$this->sid = intval ( Session::get ( C ( 'MEMBER_AUTH_KEY' ) ) );
	}
	
	//------------------------------------------------------------------------------------------------
	public function index() {
		if (isset ( $this->sid ) && ($this->sid > 0)) {
			$this->redirect ( U( 'Index/index' ) );
		} else {
			$this->redirect (U('Public/login')  );
		}
	}
	
	//------------------------------------------------------------------------------------------------
	public function login() {
		if (isset ( $this->sid ) && ($this->sid > 0)) {
			$this->redirect ( U('Index/index') );
		} else {
			Session::set ( C ( 'MEMBER_INFO' ), null );
			Session::set ( C ( 'MEMBER_AUTH_KEY' ), null );
			Session::set ( C ( 'MEMBER_NAME' ), null );

			if (! empty ( $_GET ['go'] )) {
				$return_url = $_GET ['go'];
			} elseif (Session::get ( C ( 'RETURN_URL' ) ) != NULL) {
				$return_url = Session::get ( C ( 'RETURN_URL' ) );
			}
			
			if (! empty ( $return_url )) {
				Session::set ( C ( 'RETURN_URL' ), $return_url );
				$this->assign ( 'RETURN_URL', $return_url );
			}
			$this->display ();
		}
	}
	
	//------------------------------------------------------------------------------------------------
	public function stop() {
		$this->display ();
	}
	
	//------------------------------------------------------------------------------------------------
	// 会员登录
	public function check() {
		if (! isset ( $_POST )) {
			$this->redirect ( U ( 'Public/login' ) );
		}
		$return_url = $_POST ['return_url'];
		$return_url = str_replace ( ',', '/', $return_url ); // 这里修正路径,确保只有“/”分隔的URL
		
		if (isset ( $this->sid ) && ($this->sid > 0)) {
			if ( $return_url && ($return_url != 'Item/loadItem') && ($return_url != 'Item/index')) {
				$this->redirect (U( $return_url) );
			} else {
				$this->redirect ( U ( 'Index/index' ) );
			}
		}
		
		$username = strtolower ( mysql_escape_string ( htmlspecialchars ( trim ( $_POST ["u_userName"] ) ) ) );
		$password = mysql_escape_string ( htmlspecialchars ( trim ( $_POST ["u_userPw"] ) ) );
		$code = trim ( $_POST ['verify'] );
		$ajax = $_POST ['ajax'];
		$username = str_replace ( "'", '', $username );
		$password = str_replace ( "'", '', $password );
		$userEmail = base64_encode ( ulowi_encode ($username));

		if ($username == '' || $password == '') {
			$this->goError ( L('public_user_psw_empty') );
		} elseif (! $this->checkVerify ( $code )) {
			if (! empty ( $ajax )) {
				$this->ajaxReturn ( NULL, L('public_verify_error'), 0 );
			} else {
				if ($return_url && (strtolower ( $return_url ) == 'item/index')) {
					$this->assign ( 'errorMsg', L('public_verify_error') );
					$this->display ( 'Public:login_min' );
					exit ();
				} else {
					$this->goError ( L('public_verify_error') );
					exit ();
				}
			}
		}
		
		if ((strpos ( $username, "'" ) > 0) || (strpos ( $password, "'" ) > 0)) { $this->goError ( L('public_input_error') );}
		/*for  wechat  login temp hide*/
//		$user = M ( "User" )->where ( "(active_status=1) AND (status=1) AND (is_qquser=0)  AND (login_name='$username' OR email2='$userEmail' ) " )->find ();
        /*end*/
		$user = M ( "User" )->where ( "(active_status=1) AND (status=1) AND (login_name='$username' OR email2='$userEmail' ) " )->find ();

		$password1 = md5($password);
		$password2 = ($user['salt']=='')?$password1:md5($password1.$user['salt']);
		if (!empty($user) && ($user['password'] != '')  &&  ($user['password'] == $password2 )) {
			$this->updateUser($user ['id'],$user ['login_name'], get_client_ip ());
			if (! empty ( $ajax )) { $this->ajaxReturn ( NULL, L('public_login_succ'), 1 );} else {$this->doSuccess($return_url);}
		} else {
			if (! empty ( $ajax )) {$this->ajaxReturn ( NULL, L('public_user_psw_error'), 0 );}else{$this->doError($return_url);}
		}
	}
	
	//------------------------------------------------------------------------------------------------
	// 生成验证码
	public function verify() {
		$type = isset ( $_GET ['type'] ) ? $_GET ['type'] : 'gif';
		import ( "ORG.Util.Image" );
		Image::buildImageVerify ( 4, 1, $type );
	}
	
	//------------------------------------------------------------------------------------------------
	// 检查验证码
	public function checkVerify($code) {
		$result = false;
		if ((strlen ( $code ) > 0) && (md5 ( $code ) == $_SESSION ['verify'])) {
			$result = true;
		}
		return $result;
	}
	
	//------------------------------------------------------------------------------------------------
	public function logout() {
		unset ( $this->sid );
		//$return_url = Session::get ( C ( 'RETURN_URL' ) );
		Session::set ( C ( 'MEMBER_INFO' ), null );
		Session::set ( C ( 'MEMBER_AUTH_KEY' ), null );
		Session::set ( C ( 'MEMBER_NAME' ), null );
		Session::destroy ();
		//if (! empty ( $return_url )) {
		//	$this->redirect ( $return_url );
		//} else {
			$this->redirect ( 'Index/index');
		//}
	}
	
	//------------------------------------------------------------------------------------------------
	// 小窗口登录界面
	public function login_min() {
		$url = $_GET ['u'];
		$w = $_GET ['w'];
		$h = $_GET ['h'];
		$flag = $_GET ['t'];
		$is_ulowi = $_GET ['is_ulowi'];
		$is_ulowi = (is_null ( $is_ulowi ) || ! is_numeric ( $is_ulowi )) ? 1 : 0;
		$this->assign ( 'is_ulowi', $is_ulowi );
		if (! empty ( $url )) {
			$url = str_replace ( ',', '/', $url );
			$this->assign ( 'url', $url );
		}
		
		if (! empty ( $w )) {
			$this->assign ( 'tb_w', $w );
		}
		
		if (! empty ( $h )) {
			$this->assign ( 'tb_h', $h );
		}
		
		if (! empty ( $flag )) {
			$this->assign ( 'tb_f', $flag );
		}
		$this->display ();
	}
	
	//------------------------------------------------------------------------------------------------
	public function isLogin() {
		$result = 0;
		$user = Session::get ( C ( 'MEMBER_INFO' ) );
		if ($user) {
			$result = 1;
		}
		echo $result;
	}
	
	//------------------------------------------------------------------------------------------------
	public function goUrl(){
		$url = trim($_POST['url']);
		if (! empty ( $url )) {
			   if(strpos($url, 'http')){
			   		header("location:".$url);
			   }else{
					$this->redirect ( U ( $url ) );
			   }
		} else {
				$this->redirect ( U ( 'Index/index' ) );
		}
	}
	
	// ------------------------------------------------------------------------------------------------------------
	private function updateUser($uid,$un,$ip){
		$DAO = new Model();
		$DAO->execute('UPDATE user SET last_login='.time()." ,last_ip='$ip'  WHERE id=$uid");
		$user = M ( "User" )->where("id=$uid")->find();
		$this->saveLoginSession ( $user ); // 保存登录会话
		$this->logLogin ($uid,$un,$ip ); // 记录登录日志,
	}
	
	//------------------------------------------------------------------------------------------------
	private function doSuccess($url){	
	 if($url && ($url != 'Item/loadItem') && ($url != 'Item/index')){
		   		$this->assign('return_url',$url);
		   }else{
		   		$this->assign('return_url','/Index/index.html');
		   }
			$this->display('Public:go');
	}
	
	//------------------------------------------------------------------------------------------------
	private function doError($url){
			if ($url && (strtolower ( $url ) == 'item/index')) {
				$this->assign ( 'errorMsg', L('public_user_psw_error') );
				$this->display ( 'Public:login_min' );
				return false;
			} else {
				$this->goError ( L('public_user_psw_error') );
			}
	}
	
	//------------------------------------------------------------------------------------------------
	private function goError($msg) {
		$return_url = Session::get ( C ( 'RETURN_URL' ) );
		Session::set ( C ( 'RETURN_URL' ), $return_url );
		$this->assign ( 'waitSecond', 10 );
		$this->error ( $msg );
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
	// 存储登录会话信息
	private function saveLoginSession($user) {
		if (! $user) {
			return false;
		}
		try {
			/*$u = array(	'id'		=> $user ['id'],
						'login_name'=> $user ['login_name'],
						'nick'		=> $user ['nick'],
						'nick'		=> $user ['nick'],
						'email2'	=> $user ['email2'],
						'password' 	=> $user ['password'],
						'salt' 		=> $user ['salt']);*/
			
			Session::set ( C ( 'MEMBER_INFO' ), $user );
			Session::set ( C ( 'MEMBER_AUTH_KEY' ), $user ['id'] );
			Session::set ( C ( 'MEMBER_NAME' ), $user ['login_name'] );
			Session::set ( 'ulowi_user_level', $user ['level'] );
		} catch ( Exception $e ) {
			Log::write ( '存储会话失败' );
		}
	}
	
	//------------------------------------------------------------------------------------------------
	// 增加会员积分
	private function addUserPoint($uid, $point) {
		$DAO = D ( 'Finance' );
		$finance = $DAO->finace ( $uid );
		if ($finance) {
			$finance ['point'] = $finance ['point'] + $point;
			$DAO->where ( 'id=' . $finance ['id'] )->save ( $finance );
		}
	}
}
?>