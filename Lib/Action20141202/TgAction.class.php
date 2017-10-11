<?php
/**
 +------------------------------------------------------------------------------
 * DaigouCMS 代购建站系统(淘宝版)
 * 
 +------------------------------------------------------------------------------
 * @category   ulowi
 * @author    	soitun <stone@zline.net.cn>
 * @copyright 上海子凌网络科技有限公司 
 * @license   	http://www.zline.net.cn/license-agreement.html 
 * @link      		http://www.zline.net.cn/
 +------------------------------------------------------------------------------
 */

class TgAction extends Action {

	//------------------------------------------------------------------------------------------------
	public function index() {
		$this->setReview();//评论
		$this->display ();
	}
	
	//--------------------------------------------------------------------------------------------------------------
	//评论
	private function setReview(){
		$DAO = new Model();
		$ReviewList = $DAO->query("SELECT a.user_id, a.user_name,a.create_time,a.content,a.country from comment a  WHERE a.is_display=1  ORDER BY a.create_time Desc LIMIT 10 ");
		$this->assign ( 'ReviewList', $ReviewList );
	}
	

}
?>