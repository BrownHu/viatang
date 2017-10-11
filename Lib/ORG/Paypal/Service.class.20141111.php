<?php
/**
 +------------------------------------------------------------------------------
 * 类名：paypal_service
 * 功能：paypal外部服务接口控制
 * 详细：该页面是请求参数核心处理文件，不需要修改
 * 版本：1.0
 * 
 * 修改日期：2010-10-29
 * 说明：
 +------------------------------------------------------------------------------
 */

class paypal_service{
	var $gateway;		//网关地址
	var $invoice;		//原样回传 参数
	var $return_url ;	//返回地址
	var $notify_url;		//通知地址
	var $bussiness;	//paypal收款帐号
	var $item_name;	//显示名称
	var $amount;		//付款金额 
	var $custom; 		//回传参数，这里用来保存，要次充值产生的流水号(ulowi方) 

	function paypal_service($gateway,$return_url,$notify_url,$bussiness,$item_name,$invoice,$amount,$custom){
		$this->gateway 	= $gateway;
		$this->return_url 	= $return_url;
		$this->notify_url 	= $notify_url;
		$this->bussiness 	= $bussiness;
		$this->item_name 	= $item_name;
		$this->invoice 	= $invoice;
		$this->amount 	= $amount;
		$this->custom		= $custom;
	}

	/*---------------------------------------------------------------------------------------
	* 构造表单提交HTML
	*return 表单提交HTML文本
	*/
	function build_form() {
		$sHtml  = "<form id='paypalsubmit' name='paypalsubmit' action='".$this->gateway."' method='post'>";
		$sHtml .= "<input type='hidden' name='cmd' value='_xclick' />";
		$sHtml .= "<input type='hidden' name='return' value='". $this->return_url . "'/>";
		$sHtml .= "<input type='hidden' name='notify_url' value='". $this->notify_url . "'/>";
		$sHtml .= "<input type='hidden' name='business' value='". $this->bussiness . "'/>";
		$sHtml .= "<input type='hidden' name='item_name' value='". $this->item_name . "'/>";
		$sHtml .= "<input type='hidden' name='rm' value='2' />";
		$sHtml .= "<input type='hidden' name='currency_code' value='USD' />";
		$sHtml .= "<input type='hidden' name='invoice' value='" . $this->invoice ."' />";
		$sHtml .= "<input type='hidden' name='amount' value='" . $this->amount ."' />";
		$sHtml .= "<input type='hidden' name='custom' value='" . $this->custom ."' />";		
		$sHtml .= "<input type='hidden' name='cpp_logo_image' value='".C('LOGO_URL')."'/>";
		$sHtml .= "<input type='hidden' name='cpp_cart_border_color' value='f38f00'/>";
		$sHtml .= "<input id='Submit1' type='submit' value='submit' style='display: none' />";
		$sHtml .= "</form>";
		//$sHtml .= "<script>timerID = setInterval('dosubmit()',500);function dosubmit() {document.forms['paypalsubmit'].submit();}</script>";
		return $sHtml;
	}
}

?>