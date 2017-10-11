<?php
/**
 +------------------------------------------------------------------------------
 * DaigouCMS 代购建站系统(淘宝版)
 *
 *  朋友邀请
 +------------------------------------------------------------------------------
 * @category   ulowi
 * @author    	soitun <stone@zline.net.cn>
 * @copyright 上海子凌网络科技有限公司
 * @license   	http://www.zline.net.cn/license-agreement.html
 * @link      		http://www.zline.net.cn/
 +------------------------------------------------------------------------------
 */

load ( '@/functions' );
class InviteAction extends BaseAction {
	
	//------------------------------------------------------------------------------------------------
	public function index() {
		if ($this->user) {
			$this->assign ( 'spreat', $this->user ['login_name'] );
			$this->assign ( 'spread_url', C ( 'SITE_URL' ) . '/?spreader=' . $this->user ['id'] );
			$this->display ();
		} else {
			$this->redirect ( 'Public/login' );
		}
	}
	
	//------------------------------------------------------------------------------------------------
	//分享
	public function mail() {
		if ($this->user) {
			$this->assign ( 'waitSecond', 5 );
			$this->assign ( 'jumpUrl', '/Invite/index.shtml' );
			
			$sender = trim ( $_POST ['user'] );
			$to = trim ( $_POST ['recever'] );
			$content = trim ( $_POST ['content'] );
			/* vendor ( "phpmailer.class#mailer" );
			$Mailer = new Mailer ();
			$Mailer->SendWay = 'smtp'; */
			$subject = L("invite_title");
			$toLst = explode ( ',', $to );
			if (! empty ( $to ) && ! empty ( $content )) {
				foreach ( $toLst as $i => $email ) {
					addToMailQuen(ulowi_decode(base64_decode($this->user ['email2'])),$sender,$email,$subject,$content,'');
				}
				
				$this->success ( L('invite_send_succ') );
			}else{
				$this->error(L('invite_input_error'));
			}
		}
	}

}
?>