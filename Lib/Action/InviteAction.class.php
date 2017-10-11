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
		    $this->assign ('title','代购分享邀请好友-viatang.com');
	        $this->assign ('keywords','代购，海外华人代购，代购中国，淘宝代购，中国商品代购，美国代购，服装代购，饰品代购，包包代购，图书代购，食品代购，生活用品代购');
            $this->assign ('description','海外华人、留学生一站式代购中国商品，商品集中打包配送至海外，国际运费最低3折起');
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

			$subject = $sender."给您的私人推荐信";
			$toLst = explode ( ',', $to );
			if (! empty ( $to ) && ! empty ( $content )) {
				foreach ( $toLst as $i => $email ) {
					addToMailQuen(C ( 'NOREPLY_MAIL' ),$sender,$email,$subject,$content,'');
				}
				
				$this->success ( '您的邮件已成功发送给您的好友' );
			}else{
				$this->error('收件人和邮件内容不能为空哦!');
			}
		}
	}

}
?>