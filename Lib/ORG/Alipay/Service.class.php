<?php
/**
 +------------------------------------------------------------------------------
 *类名：alipay_service
 *功能：支付宝外部服务接口控制
 *详细：该页面是请求参数核心处理文件，不需要修改
 *版本：3.1
 *修改日期：2010-10-29
 *说明：
 +------------------------------------------------------------------------------
 */

require_once("function.php");
class alipay_service {
	var $gateway;		//网关地址
	var $_key;		//安全校验码
	var $mysign;		//签名结果
	var $sign_type;	//签名类型
	var $parameter;	//需要签名的参数数组
	var $_input_charset;    //字符编码格式

	//---------------------------------------------------------------------------------------
	//  从配置文件及入口文件中初始化变量
	//  $parameter 需要签名的参数数组
	//  $key 安全校验码
	//  $sign_type 签名类型
	function alipay_service($parameter,$key,$sign_type) {
		$this->gateway    = "https://www.alipay.com/cooperate/gateway.do?";
		$this->_key	     = $key;
		$this->sign_type  = $sign_type;
		$preParameter     = para_filter($parameter);

		if($parameter['_input_charset'] == ''){
			$this->parameter['_input_charset'] = 'GBK';
		}
		$this->_input_charset   = $this->parameter['_input_charset'];

		//获得签名结果
		$this->parameter = arg_sort($preParameter);    //得到从字母a到z排序后的签名参数数组
		$this->mysign = build_mysign($this->parameter,$this->_key,$this->sign_type);
	}

	/*---------------------------------------------------------------------------------------
	* 构造表单提交HTML
	*return 表单提交HTML文本
	*/
	function build_form() {
		$sHtml = "<form id='alipaysubmit' name='alipaysubmit' action='".$this->gateway."_input_charset=".$this->parameter['_input_charset']."' method='post'>";

		while (list ($key, $val) = each ($this->parameter)) {
			$sHtml.= "<input type='hidden' name='".$key."' value='".$val."'/>";
		}

		$sHtml = $sHtml."<input type='hidden' name='sign' value='".$this->mysign."'/>";
		$sHtml = $sHtml."<input type='hidden' name='sign_type' value='".$this->sign_type."'/>";
		$sHtml .= "<input id='Submit1' type='submit' value='submit' style='display: none' />";

		//submit按钮控件请不要含有name属性
		$sHtml = $sHtml."</form>";
		//$sHtml = $sHtml."<script>timerID = setInterval('dosubmit()',1000);function dosubmit() {document.forms['alipaysubmit'].submit();}</script>";
		return $sHtml;
	}
}
?>