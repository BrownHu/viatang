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

class AboutAction extends HomeAction {

	//------------------------------------------------------------------------------------------------
	function _initialize() {
		parent::_initialize();
		$this->dao = M('Help');
	}
	
	//------------------------------------------------------------------------------------------------
	public function index() {
		$this->assign ('title','唯唐代购-全球最专业海外华人代购,淘宝代购,包裹转寄,淘宝转运,代购中国商品-viatang.com');
	    $this->assign ('keywords','代购,唯唐代购,中国代购,代购中国商品,淘宝代购,海外华人代购,美国华人代购,美国代购,海外代购,代购网站,加拿大代购,留学生代购,服装代购,图书代购');
        $this->assign ('description','唯唐代购-全球最专业代购中国商品网站,专为海外华人留学生代购淘宝、亚马逊、京东等中国购物网商品.支持paypal、国际信用卡支付方式.批量下单,多件商品集中寄送,专享超低国际运费.');
		if(isset($_GET['id'])){
			$entity = $this->dao->where ( "id=".$_GET['id'] )->find ();
			$this->assign ( 'entity', $entity );
		}
		
		$this->display();
	}
}
?>