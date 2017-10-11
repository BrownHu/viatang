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
                $this->assign ('title','用户密码修改操作失败-用户资料管理-viatang.com');
	            $this->assign ('keywords','用户资料修改,用户登录密码,免费注册唯唐代购会员,唯唐代购会员注册,登录密码修改,交易密码,密码管理,会员登录密码,代购中国商品,淘宝代购,海外华人代购,美国代购,美国代购网,代购网站,加拿大代购,留学生代购,服装代购,图书代购');
                $this->assign ('description','代购用户资料修改，用户登录密码修改，修改支付密码，免费注册唯唐代购会员，支持paypal、国际信用卡支付方式.批量下单,多件商品集中寄送,专享超低国际运费3折起.');		
		if ($this->user) {
			$code = trim ( $_POST ['verify'] );
			if (! $this->checkVerify ( $code )) {
				$this->assign ( 'waitSecond', 5 );
				$this->assign ( 'jumpUrl', U('/My/password') );
				$this->error ( L('verify_code_error') );
			} else {
				$password = mysql_escape_string (  trim ( $_POST ['o_pw'] ));
				$new_password = mysql_escape_string (  trim ( $_POST ['n_pw'] ));
				
				$password1 = md5($password);
				$password2 = ($this->user['salt']=='')?$password1:md5($password1.$this->user['salt']);
		
				if ($this->user ['password'] == $password2) {
					$salt = rand_string(12);
					$this->user ['salt'] = $salt;
					$this->user ['password'] = md5(md5($new_password).$salt);
					Session::set ( C ( 'MEMBER_INFO' ), $this->user );
					
					$user['salt'] = $salt;
					$user['password'] = md5(md5($new_password).$salt);
					
					M ( 'User' )->where ( 'id=' . $this->user ['id'] )->save ( $user);
					//echo M ( 'User' )->getLastSql();exit;
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