<?php
/**
 +------------------------------------------------------------------------------
 * DaigouCMS 代购建站系统(淘宝版)
 *
 * 用户资料
 +------------------------------------------------------------------------------
 * @category   ulowi
 * @author    	soitun <stone@zline.net.cn>
 * @copyright 上海子凌网络科技有限公司
 * @license   	http://www.zline.net.cn/license-agreement.html
 * @link      		http://www.zline.net.cn/
 +------------------------------------------------------------------------------
 */


class ProfileAction extends BaseAction {
	
	//------------------------------------------------------------------------------------------------
	public function index() {
		$this->redirect ( 'My/email' );
	}
	
	//------------------------------------------------------------------------------------------------
	public function updateMail() {
		    $this->assign ('title','修改用户注册邮箱-viatang.com');
	        $this->assign ('keywords','代购会员注册邮箱,邮箱注册,免费注册会员,用户信息,代购邮箱,邮箱修改,邮箱登录,代购中国商品,淘宝代购,海外华人代购,美国代购,美国代购网,代购网站,加拿大代购,留学生代购,服装代购,图书代购');
            $this->assign ('description','免费注册唯唐代购会员，邮箱注册、会员邮箱登录、用户邮箱资料修改，支持paypal、国际信用卡支付方式.批量下单,多件商品集中寄送,专享超低国际运费3折起.');
		if ($this->user) {
			$this->assign ( 'waitSecond', 5 );
			$this->assign ( 'jumpUrl', '/My/email.html' );
			$email =  strtolower ( mysql_escape_string ( htmlspecialchars ( trim ( $_POST ['n_email'] ))));
			$password = mysql_escape_string ( htmlspecialchars ( trim ( $_POST ['pw'] )));
			
			$password1 = md5($password);
			$password2 = ($this->user['salt']=='')?$password1:md5($password1.$this->user['salt']);
				
			if ($this->user ['password'] == $password2) {
				$DAO = M ( 'User' );
				$email2 =  base64_encode ( ulowi_encode ($email));
				$entity = $DAO->where ( "email='$email'" )->find ();
				if ($entity == false) {
					$this->user['email'] = $email;
					$this->user['email2'] = $email2;
					$user ['email']  = $email2;
					$user ['email2'] = $email2;
					Session::set ( C ( 'MEMBER_INFO' ), $this->user );
					$DAO->where ( " id=" . $this->user ['id'] )->save ( $user );
					$this->success ( L('profile_succ') );
				} else {
					$this->error ( L('profile_fail') );
				}
			} else {
				$this->error ( L('profile_psw_error') );
			}
		}else{
			$this->redirect('Public/login');
		}
	}
	
	//------------------------------------------------------------------------------------------------
	//更新个人资料
	public function commit() {
		    $this->assign ('title','用户基本资料修改-viatang.com');
	        $this->assign ('keywords','用户基本信息,代购用户信息,会员资料,免费注册代购用户,唯唐代购注册,中国代购,代购中国商品,淘宝代购,海外华人代购,美国代购,美国代购网,代购网站,加拿大代购,留学生代购,服装代购,图书代购');
            $this->assign ('description','唯唐代购用户基本资料修改，免费注册唯唐代购用户，享受超低优惠代购中国商品，支持paypal、国际信用卡支付方式.批量下单,多件商品集中寄送,专享超低国际运费3折起.');
		if ($this->user) {
			$data['user_id'] = $this->user['id'];
			$data['user_name'] = $this->user['login_name'];
			$data['true_name'] = trim($_POST['true_name']);
			$data['sex'] = intval($_POST['sex']);
			$data['country'] = trim($_POST['country']);
			$data['state'] = trim($_POST['state']);
			$data['city'] = trim($_POST['city']);
			$data['address'] = trim($_POST['address']);
			$data['zip'] = trim($_POST['zip']);
			$data['tel'] = trim($_POST['tel']);
			$data['last_update'] = time();
			
			$DAO = M('UserInfo');
			$u_id = $this->user['id'];
			$entity = $DAO->where('user_id='.$u_id)->find();
			if($entity){
				$DAO->where('user_id='.$u_id)->save($data);
			}else{
				$DAO->data($data)->add();
			}
			$this->assign('waitSecond',5);
			$this->assign('jumpUrl','/My/profile.html');
			$this->success(L('profile_update_succ'));			
		}else{
			$this->redirect('Public/login');
		}
	}
	
	//------------------------------------------------------------------------------------------------
	Public function _empty() {
		$this->redirect ( 'My/profile' );
	}
}
?>