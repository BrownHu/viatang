<?php
/* *-------------------------------------------------------------------------------------------------------------
 * 类名：AlipayService
 * 功能：支付宝各接口构造类
 * 详细：构造支付宝各接口请求参数
 * 版本：3.2
 * 日期：2011-03-25
 * 说明：
 * 以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己网站的需要，按照技术文档编写,并非一定要使用该代码。
 * 该代码仅供学习和研究支付宝接口使用，只是提供一个参考。
 */

require_once("Submit.class.php");
class AlipayService {
	
	var $alipay_gateway_new = 'https://mapi.alipay.com/gateway.do?';
	var $partner;
	var $key;
	var $charset;
	var $sign_type;
	
    function AlipayService($partner,$key,$signtype,$charset) {
		$this->partner 	= $partner;
		$this->key 		= $key;
		$this->sign_type= $signtype;
		$this->charset 	= $charset;
    }
    
	/**-----------------------------------------------------------------------------------------------------------
     * 构造标准双接口
     * //生成表单提交HTML文本信息
     * @param $para_temp 请求参数数组
     * @return 表单提交HTML信息
     -------------------------------------------------------------------------------------------------------------*/
	function trade_create_by_buyer($para_temp) {
		$alipaySubmit = new AlipaySubmit($this->key,$this->sign_type,$this->charset);
		$html_text = $alipaySubmit->buildForm($para_temp, $this->alipay_gateway_new, "get", '确认');
		return $html_text;
	}
	
	
	/**-----------------------------------------------------------------------------------------------------------
     * 构造确认发货接口
     * @param $para_temp 请求参数数组
     * @return 获取支付宝的返回XML处理结果
     */
	function send_goods_confirm_by_platform($para_temp) {

		//获取支付宝的返回XML处理结果
		$alipaySubmit = new AlipaySubmit($this->key,$this->sign_type,$this->charset);
		$html_text = $alipaySubmit->sendPostInfo($para_temp, $this->alipay_gateway_new);

		return $html_text;
	}
	
	/**--------------------------------------------------------------------------------------------------------------
     * 用于防钓鱼，调用接口query_timestamp来获取时间戳的处理函数
	 * 注意：该功能PHP5环境及以上支持，因此必须服务器、本地电脑中装有支持DOMDocument、SSL的PHP配置环境。建议本地调试时
	 * 使用PHP开发软件
     * return 时间戳字符串
	 */
	function query_timestamp() {
		$url = $this->alipay_gateway_new."service=query_timestamp&partner=".trim($this->partner);
		$encrypt_key = "";

		$doc = new DOMDocument();
		$doc->load($url);
		$itemEncrypt_key = $doc->getElementsByTagName( "encrypt_key" );
		$encrypt_key = $itemEncrypt_key->item(0)->nodeValue;
		
		return $encrypt_key;
	}
	
	/**-------------------------------------------------------------------------------------------------------------
     * 构造支付宝其他接口
     * @param $para_temp 请求参数数组
     * @return 表单提交HTML信息/支付宝返回XML处理结果
     */
	function alipay_interface($para_temp) {
		$alipaySubmit = new AlipaySubmit();
		$html_text = "";
		return $html_text;
	}
}
?>