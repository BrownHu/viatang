<?php
/**
 +------------------------------------------------------------------------------
 * DaigouCMS 代购建站系统(淘宝版)
 *
 * 意见反馈
 +------------------------------------------------------------------------------
 * @category   ulowi
 * @author    	soitun <stone@zline.net.cn>
 * @copyright 上海子凌网络科技有限公司
 * @license   	http://www.zline.net.cn/license-agreement.html
 * @link      		http://www.zline.net.cn/
 +------------------------------------------------------------------------------
 */

load ( '@/filter_word' );
class FeedbackAction extends Action {
	
	//------------------------------------------------------------------------------------------------
	public function index() {
		$this->assign ('title','意见反馈-viatang.com');
	    $this->assign ('keywords','代购，淘宝代购，代购意见与建议，代购投诉，代购评价');
        $this->assign ('description','代购用户评论，意见反馈，提供淘宝、天猫、京东网商品专业代购评论');
		$this->display ();
	}
	
	//------------------------------------------------------------------------------------------------
	public function commit() {
		$data ['category_id'] = $_POST ['category'];
		$data ['content'] = trim ( $_POST ['content'] );
		$data ['ip'] = get_client_ip ();
		$data ['create_time'] = time ();
		$data ['status'] = '0';
		
		$verify = $_POST ['verify'];
		if ($this->checkVerify ( $verify )) {
			M ( 'Feedback' )->data ( $data )->add ();
			$this->assign ( 'waitSecond', 10 );
			$this->assign ( 'jumpUrl', '/Index/index.shtml' );
			$this->success ( L('feed_succ') );
		} else {
			$this->error ( L('feed_fail') );
		}
	}
	
	//------------------------------------------------------------------------------------------------
	public function commitForMy() {
		$data ['category_id'] = 0;
		$data ['content'] = trim ( $_POST ['content'] );
		$data ['ip'] = get_client_ip ();
		$data ['create_time'] = time ();
		$data ['status'] = '0';
		M ( 'Feedback' )->data ( $data )->add ();
		$this->ajaxReturn('提交成功','感谢您对唯唐代购的支持，您的意见已成功提交!',1);
	}
	
	//------------------------------------------------------------------------------------------------
	private function checkVerify($code) {
		$result = false;
		
		if ((strlen ( $code ) > 0) && (md5 ( $code ) == Session::get ( 'verify' ))) {
			$result = true;
		}
		return $result;
	}
	
	//------------------------------------------------------------------------------------------------
	//客户端实时和谐词检测
	public function filter_check() {
		$str = $_GET ['s'];
		if (contain_badkey ( $str )) {
			echo '1';
		} else {
			echo '0';
		}
	}
	
	//------------------------------------------------------------------------------------------------
	Public function _empty() {
		$this->redirect ( 'index' );
	}
}
?>