<?php
/**
 +------------------------------------------------------------------------------
 * DaigouCMS 代购建站系统(淘宝版)
 *
 * 我的帐户－修改密码
 +------------------------------------------------------------------------------
 * @category   ulowi
 * @author    	soitun <stone@zline.net.cn>
 * @copyright 上海子凌网络科技有限公司
 * @license   	http://www.zline.net.cn/license-agreement.html
 * @link      		http://www.zline.net.cn/
 +------------------------------------------------------------------------------
 */

load ( '@/functions' );
class PasswordAction extends BaseAction {
	
	//------------------------------------------------------------------------------------------------
	public function index() {
		if ($this->user) {
			$this->redirect ( U('My/password') );
		}
	}
	
	//------------------------------------------------------------------------------------------------
	public function update() {		
		if ($this->user) {
			$code = trim ( $_POST ['verify'] );
			if (! $this->checkVerify ( $code )) {
				$this->assign ( 'waitSecond', 5 );
				$this->assign ( 'jumpUrl', U('/My/password') );
				$this->error ( L('verify_code_error') );
			} else {
				$password = mysql_escape_string (  trim ( $_POST ['o_pw'] ));
				$new_password = mysql_escape_string (  trim ( $_POST ['n_pw'] ));
				
				if ($this->user ['password'] == md5(md5($password).$this->user['salt'])) {
					$salt = rand_string(12);
					$this->user ['salt'] = $salt;
					$this->user ['password'] = md5(md5($new_password).$salt);
					Session::set ( C ( 'MEMBER_INFO' ), $this->user );
					M ( 'User' )->where ( 'id=' . $this->user ['id'] )->save ( $this->user );
					$this->assign ( 'waitSecond', 5 );
					$this->assign ( 'jumpUrl', U('/My/password') );
					$this->success ( L('password_change_succ') );
				} else {
					$this->assign ( 'waitSecond', 5 );
					$this->assign ( 'jumpUrl', U('/My/password') );
					$this->error ( L('password_old_incorect') );
				}
			}
		}
	}
	
	//------------------------------------------------------------------------------------------------
	Public function _empty() {
		$this->redirect ( U('My/password') );
	}
}
?>