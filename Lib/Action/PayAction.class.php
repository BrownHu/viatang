<?php
/**
 +------------------------------------------------------------------------------
 * DaigouCMS 代购建站系统(淘宝版)
 *
 * 显示充值界面
 +------------------------------------------------------------------------------
 * @category   ulowi
 * @author    	soitun <stone@zline.net.cn>
 * @copyright 上海子凌网络科技有限公司
 * @license   	http://www.zline.net.cn/license-agreement.html
 * @link      		http://www.zline.net.cn/
 +------------------------------------------------------------------------------
 */

class PayAction extends BaseAction {
	
	//------------------------------------------------------------------------------------------------
	public function index() {
		$this->redirect ( 'My/pay' );
	}
	
	//------------------------------------------------------------------------------------------------
	public function paypal() {
		$this->assign ('title','paypal充值-账户充值管理-viatang.com');
	    $this->assign ('keywords','paypal充值,支付宝充值,微信支付,国际信用卡支付,外币支付,代购商品支付,淘宝代购支付,即时到帐,加入购物车，中国代购,代购中国商品,淘宝代购,海外华人代购,美国代购,美国代购网,代购网站,加拿大代购,留学生代购,服装代购,图书代购');
        $this->assign ('description','代购paypal、支付宝充值,淘宝代购商品支付，国际信用卡支付，外币支付，支持paypal、国际信用卡支付方式.批量下单,多件商品集中寄送,专享超低国际运费3折起.');
		if ($this->user) {
			$this->assign ( 'uid', $this->user ['id'] );
			$this->assign ( 'un', $this->user ['login_name'] );
			
			$DAO = M ( 'FinaceConfig' );
			$entity = $DAO->where ( "item='exchange_rate'" )->find ();
			if ($entity) {
				$this->assign ( 'exchange_rate', $entity ['value'] );
			}
			$this->assign ( 'charge_percent', floatval(C ( 'paypal_handling_charge_percent' ) ));
			$this->assign ( 'charge', floatval(C ( 'paypal_handling_charge' )) );
			$this->display ();
		}
	}
	
	//------------------------------------------------------------------------------------------------
	public function alipay() {
		$this->assign ('title','支付宝充值-账户充值管理-viatang.com');
	    $this->assign ('keywords','支付宝支付,paypal支付,微信支付,国际信用卡支付,外币支付,代购商品支付,淘宝代购支付,即时到帐,中国代购,代购中国商品,淘宝代购,海外华人代购,美国代购,美国代购网,代购网站,加拿大代购,留学生代购,服装代购,图书代购');
        $this->assign ('description','代购paypal、支付宝充值,淘宝代购商品支付，国际信用卡支付，外币支付，支持paypal、国际信用卡支付方式.批量下单,多件商品集中寄送,专享超低国际运费3折起.');
		if ($this->user) {
			$this->assign ( 'uid', $this->user ['id'] );
			$this->assign ( 'un', $this->user ['login_name'] );
			$this->display ();
		}
	}
	
	//------------------------------------------------------------------------------------------------
	public function ips() {
		if ($this->user) {
			$this->assign ( 'uid', $this->user ['id'] );
			$this->assign ( 'un', $this->user ['login_name'] );
			$this->display ();
		}
	}
	
	//------------------------------------------------------------------------------------------------
	public function other() {
		$this->assign ('title','网银转账-账户充值管理-viatang.com');
	    $this->assign ('keywords','网银转账,paypal支付,支付宝支付,微信支付,西联汇款,外币支付,银行电汇,中国代购,代购中国商品,淘宝代购,海外华人代购,美国代购,美国代购网,代购网站,加拿大代购,留学生代购,服装代购,图书代购');
        $this->assign ('description','代购paypal、支付宝充值,淘宝代购商品支付，国际信用卡支付，外币支付，支持paypal、国际信用卡支付方式.批量下单,多件商品集中寄送,专享超低国际运费3折起.');
		if ($this->user) {
			$today = Date('Y-m-d');
			$this->assign('today',$today);
			
			$this->assign ( 'uid', $this->user ['id'] );
			$this->assign ( 'un', $this->user ['login_name'] );
				
			$DAO = M ( 'FinaceConfig' );
			$entity = $DAO->where ( "item='exchange_rate'" )->find ();
			if ($entity) {
				$this->assign ( 'exchange_rate', $entity ['value'] );
			}
			$this->assign ( 'charge_percent', floatval(C ( 'paypal_handling_charge_percent' ) ));
			$this->assign ( 'charge', floatval(C ( 'paypal_handling_charge' )) );
			$this->display ();
		}
	}
	
	//------------------------------------------------------------------------------------------------
	public function creditcn() {
		if ($this->user) {
			$this->assign ( 'uid', $this->user ['id'] );
			$this->assign ( 'un', $this->user ['login_name'] );
			$this->display ();
		}
		
	}
	
	//------------------------------------------------------------------------------------------------
	public function wepay() {
		$this->assign ('title','微信支付-账户充值管理-viatang.com');
	    $this->assign ('keywords','微信支付,paypal支付,支付宝支付,网银转账,西联汇款,外币支付,银行电汇,中国代购,代购中国商品,淘宝代购,海外华人代购,美国代购,美国代购网,代购网站,加拿大代购,留学生代购,服装代购,图书代购');
        $this->assign ('description','唯唐代购提供多种支付方式供选择，微信支付、paypal、支付宝充值、淘宝代购商品支付、国际信用卡支付、外币支付、支持paypal、国际信用卡支付方式.批量下单,多件商品集中寄送,专享超低国际运费3折起.');
		if ($this->user) {
			$this->assign ( 'uid', $this->user ['id'] );
			$this->assign ( 'un', $this->user ['login_name'] );
			$this->display ();
		}
	
	}
	
	//------------------------------------------------------------------------------------------------
	Public function _empty() {
		$this->redirect ( 'Pay/paypal' );
	}
}
?>