<?php
/* *-------------------------------------------------------------------------------------------------------
 * 类名：AlipaySubmit
 * 功能：支付宝各接口请求提交类
 * 详细：构造支付宝各接口表单HTML文本，获取远程HTTP数据
 * 版本：3.2
 * 日期：2011-03-25
 * 说明：
 * 以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己网站的需要，按照技术文档编写,并非一定要使用该代码。
 * 该代码仅供学习和研究支付宝接口使用，只是提供一个参考。
 */
require_once("function.php");
class AlipaySubmit {
	var $key;
	var $sign_type;
	var $charset;
	
 	//-----------------------------------------------------------------------------------------------------
 	//构造函数
    function AlipaySubmit($key,$sign_type,$charset) {
		$this->key 		 = $key;
		$this->sign_type = $sign_type;
		$this->charset	 = $charset;
    }
    	
	/**----------------------------------------------------------------------------------------------------
     * 生成要请求给支付宝的参数数组
     * @param $para_temp 请求前的参数数组
     * @param $aliapy_config 基本配置信息数组
     * @return 要请求的参数数组
     */
	function buildRequestPara($para_temp) {		
		$para_filter = paraFilter($para_temp);	//除去待签名参数数组中的空值和签名参数
		$para_sort 	 = argSort($para_filter);	//对待签名参数数组排序
		$mysign 	 = buildMysign($para_sort, trim($this->key), strtoupper(trim($this->sign_type)));//生成签名结果
		
		$para_sort['sign'] 		= $mysign;//签名结果与签名方式加入请求提交参数组中
		$para_sort['sign_type'] = strtoupper(trim($this->sign_type));
		return $para_sort;
	}

	/**-------------------------------------------------------------------------------------------------
     * 生成要请求给支付宝的参数数组
     * @param $para_temp 请求前的参数数组
	 * @param $aliapy_config 基本配置信息数组
     * @return 要请求的参数数组字符串
     */
	function buildRequestParaToString($para_temp) {		
		$para = $this->buildRequestPara($para_temp);//待请求参数数组
		
		//把参数组中所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串，并对参数值做urlencode编码
		$request_data = createLinkstringUrlencode($para);
		return $request_data;
	}
	
    /**------------------------------------------------------------------------------------------------
     * 构造提交表单HTML数据
     * @param $para_temp 请求参数数组
     * @param $gateway 网关地址
     * @param $method 提交方式。两个值可选：post、get
     * @param $button_name 确认按钮显示文字
     * @return 提交表单HTML文本
     */
	function buildForm($para_temp, $gateway, $method, $button_caption ) {
		//待请求参数数组
		$para = $this->buildRequestPara($para_temp);
		$sHtml = "<form id='alipaysubmit' name='alipaysubmit' action='".$gateway."_input_charset=".trim(strtolower($this->charset))."' method='".$method."'>";
		while (list ($key, $val) = each ($para)) {
            $sHtml.= "<input type='hidden' name='".$key."' value='".$val."'/>";
        }
        $sHtml = $sHtml."<input id='Submit1' type='submit' value='".$button_caption."'></form>";
		//$sHtml = $sHtml."<script>document.forms['alipaysubmit'].submit();</script>";
		return $sHtml;
	}
	
	/**---------------------------------------------------------------------------------------------------
     * 构造模拟远程HTTP的POST请求，获取支付宝的返回XML处理结果
	 * 注意：该功能PHP5环境及以上支持，因此必须服务器、本地电脑中装有支持DOMDocument、SSL的PHP配置环境。
	 * 建议本地调试时使用PHP开发软件
     * @param $para_temp 请求参数数组
     * @param $gateway 网关地址
	 * @param $aliapy_config 基本配置信息数组
     * @return 支付宝返回XML处理结果
     */
	function sendPostInfo($para_temp, $gateway) {
		$xml_str 	  = '';
		$request_data = $this->buildRequestParaToString($para_temp);//待请求参数数组字符串
		$url 		  = $gateway . $request_data;//请求的url完整链接
		$xml_data     = getHttpResponse($url,trim(strtolower($this->charset)));//远程获取数据
		$doc 		  = new DOMDocument();//解析XML
		$doc->loadXML($xml_data);
		return $doc;
	}
}
?>