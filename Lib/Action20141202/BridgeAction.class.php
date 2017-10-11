<?php
/**
 +------------------------------------------------------------------------------
 * DaigouCMS 代购建站系统(淘宝版)
 *
 * 桥页用来实现跳转功能，后期将加入统计功能
 +------------------------------------------------------------------------------
 * @category   ulowi
 * @author    	soitun <stone@zline.net.cn>
 * @copyright 上海子凌网络科技有限公司
 * @license   	http://www.zline.net.cn/license-agreement.html
 * @link      		http://www.zline.net.cn/
 +------------------------------------------------------------------------------
 */

class BridgeAction extends Action{
	
	public function index(){
		$url = trim($_REQUEST['u']);
		if(!empty($url)){
			$gourl = str_replace('^','/',$url);
			$gourl = str_replace('@','==',$gourl);
			$gourl = base64_decode($gourl);
			if(!empty($gourl)){
				header('Location:'.$gourl);
			}else{
				header('Location:/');
			}
		}else{
			header('Location:/');
		}
	}
	
	public function _empty(){
		$this->redirect('index');
	}
}
?>