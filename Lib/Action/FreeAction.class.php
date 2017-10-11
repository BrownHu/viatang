<?php
/**
 +------------------------------------------------------------------------------
 * DaigouCMS 代购建站系统(淘宝版)
 *
 * 免邮商家
 +------------------------------------------------------------------------------
 * @category   ulowi
 * @author    	soitun <stone@zline.net.cn>
 * @copyright 上海子凌网络科技有限公司
 * @license   	http://www.zline.net.cn/license-agreement.html
 * @link      		http://www.zline.net.cn/
 +------------------------------------------------------------------------------
 */

class FreeAction extends Action {
	
	//------------------------------------------------------------------------------------------------
	function _initialize() {
		Session::set ( C( 'RETURN_URL' ), MODULE_NAME . ',' . ACTION_NAME );
	}
	
	//------------------------------------------------------------------------------------------------
	public function index() {
		$this->assign ('title','免邮商家-一站式提供淘宝代购平台-viatang.com');
	    $this->assign ('keywords','商品包邮，商品免运费，全场包邮，免国内运费，淘宝包邮，中国商品代购，服装代购，饰品代购，包包代购，图书代购，食品代购，生活用品代购');
        $this->assign ('description','海外华人、留学生代购淘宝，天猫代购，京东代购，亚马逊代购，一号店代购等网站商品，商品国内运费全免');
		$refer_url = Session::get('referer_url');
		if(empty($refer_url)){			
			Session::set ( 'referer_url',$_SERVER ["HTTP_REFERER"] );
		}
		
		$this->display ();
	}
	
	//------------------------------------------------------------------------------------------------
	Public function _empty() {
		$this->redirect ( 'index' );
	}
}
?>