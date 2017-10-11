<?php
/**
 +------------------------------------------------------------------------------
 * DaigouCMS 代购建站系统(淘宝版)
 * 
 * AJAX 验证
 +------------------------------------------------------------------------------
 * @category   ulowi
 * @author    	soitun <stone@zline.net.cn>
 * @copyright 上海子凌网络科技有限公司 
 * @license   	http://www.zline.net.cn/license-agreement.html 
 * @link      		http://www.zline.net.cn/
 +------------------------------------------------------------------------------
 */


class AjaxAction extends Action {
	
	//------------------------------------------------------------------------------------------------
	// 登录名检查
	public function isExists() {
		$_count = 0;
		$u = trim ( $_GET ['usr'] );
		$email = strtolower ( trim ( $_GET ['mail'] ) );
		$act = trim ( $_GET ['act'] );
		if ((strpos ( $email, '86daigou' ) > 0) || (strpos ( $email, 'panli' ) > 0) || (strpos ( $email, 'daigou86' ) > 0)) {
			$_count = 0;
		} else {
			$DAO = M ( 'User' );
			if ((! empty ( $u ) || ! empty ( $email )) && ! empty ( $act )) {
				switch ($act) {
					case 'usr' :
						$u = strtolower ( $u );
						$_count = $DAO->where ( "login_name='$u' " )->count ();
						break;
					case 'mail' :
						//$email = base64_encode(ulowi_encode(strtolower ( $email )));
						$_count = $DAO->where ( "email='$email' " )->count ();
				}
				//if ($_count) {
				//	$result = '1';
				//}
			}
		}
		echo $_count;
	}
	
	//------------------------------------------------------------------------------------------------
	//顶部信息延迟加载
	public function showinfo() {
		$uid = intval ( Session::get ( C ( 'MEMBER_AUTH_KEY' ) ) );
		if ($uid && is_numeric ( $uid ) && ($uid > 0)) {
			$this->countNotice($uid);
			$this->countCar($uid);
		}
	}
	
	//------------------------------------------------------------------------------------------------
	//统计未读短信
	private function countNotice($uid) {
		if ($uid && is_numeric ( $uid ) && ($uid > 0)) {
			$count = M ( 'Notice' )->where ( 'user_id=' . $uid . ' AND tag=0' )->count (); //未读短信
			Session::set ( 'unrd_msg_count', $count );
		}
	}
	
	//------------------------------------------------------------------------------------------------
	//统计购物车
	private function countCar($uid) {
		if ($uid && is_numeric ( $uid ) && ($uid > 0)) {
			$cart_count = M ( 'ShopingCart' )->where ( 'user_id=' . $uid )->count ();
			if ($cart_count) {
				Session::set ( C ( 'CART_COUNT' ), $cart_count );
			} else {
				Session::set ( C ( 'CART_COUNT' ), 0 );
			}
		}
	}
}