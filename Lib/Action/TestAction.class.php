<?php
load ( '@/functions' );
import ( 'ORG.Util.Snoopy' );
class TestAction extends Action {
	
	
	public function mailtest() {
		/*vendor ( "phpmailer.class#mailer" );
		$Mailer = new Mailer ();
		$Mailer->SendWay = 'smtp';
		$result = $Mailer->sendmail ( 'noreply@easybuycn.com', 'notify', 'soitun@qq.com', 'soitun', '订单通知', '请查收' );
		dump ( $result );*/
		
		$subject = '订单提醒'.time();
		$Content = '订单已出库';
		addToMailQuen(C ( 'NOREPLY_MAIL' ), C ( 'SERVICE_NAME' ), 'soitun@qq.com',$subject,$Content,'');
	}
	
	
	public function taobao(){
		$this->display();
	}
}


?>