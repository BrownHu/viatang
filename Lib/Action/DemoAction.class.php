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
		$this->assign ('title','代购流程演示-viatang.com');
	    $this->assign ('keywords','代购,唯唐代购,中国代购,代购中国商品,淘宝代购,海外华人代购,美国华人代购,美国代购,海外代购,代购网站,加拿大代购,留学生代购,服装代购,图书代购');
        $this->assign ('description','全球最专业代购中国商品网站,专为海外华人留学生代购淘宝、亚马逊、京东等中国购物网商品.支持paypal、国际信用卡支付方式.批量下单,多件商品集中寄送,专享超低国际运费.');
		$this->display();
	}

	//------------------------------------------------------------------------------------------------
	//代购演示
	public function demo(){
	    $this->assign ('keywords','代购,唯唐代购,中国代购,代购中国商品,淘宝代购,海外华人代购,美国华人代购,美国代购,海外代购,代购网站,加拿大代购,留学生代购,服装代购,图书代购');
        $this->assign ('description','全球最专业代购中国商品网站,专为海外华人留学生代购淘宝、亚马逊、京东等中国购物网商品.支持paypal、国际信用卡支付方式.批量下单,多件商品集中寄送,专享超低国际运费.');
		$step = $_GET['s'];
		if($step){
			
			if( ($step == '1_2') || ($step == '1_3') ){
				$this->assign ('title','挑选商品-代购流程演示-viatang.com');
			}
			if( ($step == '2_1') || ($step == '2_2') || ($step == '2_3') ){
				$this->assign ('title','确认并结算-代购流程演示-viatang.com');
			}
			if( ($step == '3_1') || ($step == '3_2') || ($step == '3_3') || ($step == '3_4') || ($step == '3_5') ){
				$this->assign ('title','为您代购-代购流程演示-viatang.com');
			}
			if( ($step == '4_1') || ($step == '4_2') || ($step == '4_3') ){
				$this->assign ('title','提交运送-代购流程演示-viatang.com');
			}
			if( ($step == '5_1') ){
				$this->assign ('title','确定收货-代购流程演示-viatang.com');
			}
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