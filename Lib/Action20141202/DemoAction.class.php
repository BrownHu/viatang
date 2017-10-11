<?php
/**
 +------------------------------------------------------------------------------
 * DaigouCMS 代购建站系统(淘宝版)
 *
 * 代购演示
 +------------------------------------------------------------------------------
 * @category   ulowi
 * @author    	soitun <stone@zline.net.cn>
 * @copyright 上海子凌网络科技有限公司
 * @license   	http://www.zline.net.cn/license-agreement.html
 * @link      		http://www.zline.net.cn/
 +------------------------------------------------------------------------------
 */


class DemoAction extends Action {

	//------------------------------------------------------------------------------------------------
	public function index(){
		$this->display();
	}

	//------------------------------------------------------------------------------------------------
	//代购演示
	public function demo(){
		$step = $_GET['s'];
		if($step){
			$this->display('demo_'.$step);
		}else {
			$this->redirect('index');
		}
	}

	//------------------------------------------------------------------------------------------------
	Public function _empty(){
		$this->redirect('index');
	}
}
?>