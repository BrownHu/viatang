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
		if ($this->user) {
			$this->assign ( 'waitSecond', 5 );
			$this->assign ( 'jumpUrl', '/My/email.html' );
			$email =  strtolower ( mysql_escape_string ( htmlspecialchars ( trim ( $_POST ['n_email'] ))));
			$password = mysql_escape_string ( htmlspecialchars ( trim ( $_POST ['pw'] )));
			if ($this->user ['password'] == md5($password . $this->user['salt'] )) {
				$DAO = M ( 'User' );
				$email2 =  base64_encode ( ulowi_encode ($email));
				$entity = $DAO->where ( "email2='$email'" )->find ();
				if ($entity == false) {
					$user ['email'] = $email2;
					$user ['email2'] = $email2;
					Session::set ( C ( 'MEMBER_INFO' ), $user );
					$DAO->where ( " id=" . $user ['id'] )->save ( $user );
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