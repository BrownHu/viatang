<?php
/**
 +------------------------------------------------------------------------------
 * DaigouCMS 代购建站系统(淘宝版)
 * 
 * 微信扫码支付
 +------------------------------------------------------------------------------
 * @category   ulowi
 * @author    	soitun <stone@zline.net.cn>
 * @copyright 上海子凌网络科技有限公司 
 * @license   	http://www.zline.net.cn/license-agreement.html 
 * @link      		http://www.zline.net.cn/
 +------------------------------------------------------------------------------
 */


vendor('wepay.WxPayApi'); //微信API
Vendor('phpqrcode.phpqrcode');//二维码
import ( '@.Action.WePayNotifyCallBack' );

class WepayAction extends Action {
	//------------------------------------------------------------------------------------------------
	function _initialize() {
		$_SESSION['WEPAY_APPID'] 	= C('WEPAY_APPID');
		$_SESSION['WEPAY_MCHID'] 	= C('WEPAY_MCHID');
		$_SESSION['WEPAY_KEY'] 		= C('WEPAY_KEY');
		
		$_SESSION['WEPAY_APPSECRET'] 	= C('WEPAY_APPSECRET');
		$_SESSION['WEPAY_SSLCERT_PATH'] = C('WEPAY_SSLCERT_PATH');
		$_SESSION['WEPAY_SSLKEY_PATH'] 	= C('WEPAY_SSLKEY_PATH');
		
		$_SESSION['WEPAY_CURL_PROXY_HOST'] 	= C('WEPAY_CURL_PROXY_HOST');
		$_SESSION['WEPAY_CURL_PROXY_PORT'] 	= C('WEPAY_CURL_PROXY_PORT');
		$_SESSION['WEPAY_REPORT_LEVENL'] 	= C('WEPAY_REPORT_LEVENL');
	}	
	
	//------------------------------------------------------------------------------------------------
	public function index(){
		$this->redirect('Pay/Wepay');
	}

	//------------------------------------------------------------------------------------------------
	//提交支付宝网关进行充值
	public function commit(){
		$payer_id 	= $_POST['payer_uid'];	//用于对支付结果通知进行个人帐户入帐
		$payer_un	= $_POST['payer_un'];		//用户名
		
		if(empty($payer_id) || empty($payer_un)){
			$user = Session::get(C('MEMBER_INFO'));
			if($user){
				$payer_id 	=$user['id'];
				$payer_un	=$user['login_name'];
			}else{
				$this->assign('jumpUrl','/Pay/alipay.shtml');
				$this->error(L('alipay_pay_error'));
			}
		}
		
		
		$out_trade_no 	= $payer_id.date('_YmdHis').'_'.rand(1000,2000);		//唯一订单号匹配
		
		$body         		= $_POST['alibody'];			//订单描述、订单详细、订单备注，显示在支付宝收银台里的“商品描述”里
		$total_fee    		= $_POST['cz_money'] * 100;		//订单总金额，显示在支付宝收银台里的“应付总额”里
		if(!$total_fee || !is_numeric($total_fee)){
			$this->assign('jumpUrl','/Pay/wepay.html');
			$this->error(L('alipay_input_invalid'));
		}
		
		$real_fee = $total_fee - ($total_fee * C('WEPAY_FEE'))/100;
		$real_fee = $real_fee /100;
		
		//构造统一支付订单
		$input = new WxPayUnifiedOrder();
		$input->SetBody($body);
		$input->SetAttach($payer_id.'|'.$real_fee);
		$input->SetOut_trade_no($out_trade_no);
		$input->SetTotal_fee($total_fee);
		$input->SetTime_start(date("YmdHis"));
		$input->SetTime_expire(date("YmdHis", time() + 600));
		$input->SetGoods_tag($payer_id);
		$input->SetNotify_url(C('WEPAY_NOTIFY_URL'));
		$input->SetTrade_type("NATIVE");
		$input->SetProduct_id("11");
		
		//写入支付跟踪
		
		$total_fee = $total_fee /100;
		writePayTrace($payer_id,$payer_un,$out_trade_no,$real_fee,'pendding','109-微信充值',$total_fee,'');
		$this->assign('wepaymoney',$total_fee);		
		$this->assign('trade_no',$out_trade_no);
		
		$result = $this->GetPayUrl($input);
		
		$url2 = !empty($result) ? $result["code_url"] : '';
		$this->assign('url',urlencode($url2));
		
		$this->display('Pay:qrcode');
	}

	//构造统一支付订单
	private function GetPayUrl($data){
		if($data->GetTrade_type() == "NATIVE"){
			return WxPayApi::unifiedOrder($data);
		}else{
			return false;
		}
	}
	
	//将订单生成二维码
	public function qrcode(){
		$url = $_GET['data'];
		$level=3;
		$size=6;
		Vendor('phpqrcode.phpqrcode');
		
		$errorCorrectionLevel =intval($level) ;//容错级别
		$matrixPointSize = intval($size);//生成图片大小
		
		//生成二维码图片		
		$object = new QRcode();
		
		$object->png(urldecode($url), false, $errorCorrectionLevel, $matrixPointSize, 2);
	}

	//------------------------------------------------------------------------------------------------
	//处理微信支信结果通知消息
	public function notify(){
		$notify = new WePayNotifyCallBack();
		$notify->Handle(false);
	}
	
	//------------------------------------------------------------------------------------------------
	Public function _empty(){
		$this->redirect('Pay/alipay');
	}
}
?>