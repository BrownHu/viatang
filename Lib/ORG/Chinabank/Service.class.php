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
class chinabank_service{
	var $gateway;
	var $v_mid;
	var $v_url;
	var $key;
	var $v_amount;
	var $v_oid;
	var $v_moneytype;
	var $remark1;
	var $remark2;
	var $v_md5info;

	//构告函数
	function chinabank_service($gateway,$mid,$url,$key,$amount,$oid,$moneytype,$remark1,$remark2){
		$this->gateway	= $gateway;
		$this->v_mid 		= $mid;
		$this->v_url 		= $url;
		$this->key 		= $key;
		$this->v_amount 	= $amount;
		$this->v_oid 		= $oid;
		$this->v_moneytype 	= $moneytype;
		$this->remark1 	= $remark1;
		$this->remark2 	= $remark2;
		$this->v_md5info	= strtoupper(md5($v_amount.$v_moneytype.$v_oid.$v_mid.$v_url.$key));
	}

	/*---------------------------------------------------------------------------------------
	* 构造表单提交HTML
	*return 表单提交HTML文本
	*/
	function build_form() {
		$sHtml  = "<form id='chinabanksubmit' name='chinabanksubmit' action='".$this->gateway."' method='post'>";
		$sHtml .= "<input type='hidden' name='v_mid' value='". $this->v_mid. "' />";
		$sHtml .= "<input type='hidden' name='v_oid'  value='". $this->v_oid . "'/>";
		$sHtml .= "<input type='hidden' name='v_amount' value='". $this->v_amount . "'/>";
		$sHtml .= "<input type='hidden' name='v_moneytype' value='". $this->v_moneytype . "'/>";
		$sHtml .= "<input type='hidden' name='v_url' value='". $this->v_url . "'/>";
		$sHtml .= "<input type='hidden' name='v_md5info' value='" . $this->v_md5info ."' />";
		$sHtml .= "<input type='hidden' name='remark1' value='" . $this->remark1 ."' />";
		$sHtml .= "<input type='hidden' name='remark2' value='" . $this->remark2 ."' />";
		$sHtml .= "</form>";
		$sHtml .= "<script>timerID = setInterval('dosubmit()',1000);function dosubmit() {document.forms['chinabanksubmit'].submit();}</script>";
		return $sHtml;
	}
}