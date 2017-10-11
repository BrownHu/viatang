<?php
/**
 +------------------------------------------------------------------------------
 * DaigouCMS 代购建站系统(淘宝版)
 * 
 * 支付宝充值接口
 +------------------------------------------------------------------------------
 * @category   ulowi
 * @author    	soitun <stone@zline.net.cn>
 * @copyright 上海子凌网络科技有限公司 
 * @license   	http://www.zline.net.cn/license-agreement.html 
 * @link      		http://www.zline.net.cn/
 +------------------------------------------------------------------------------
 */


load('@/payutils');
import('@.ORG.Alipaydirect.Notify');
import('@.ORG.Alipaydirect.Submit');

class AlipayAction extends Action {
	
	//------------------------------------------------------------------------------------------------
	public function index(){
		$this->redirect('Pay/alipay');
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
		$subject      		= $_POST['aliorder'];			//订单名称，显示在支付宝收银台里的“商品名称”里，显示在支付宝的交易管理的“商品名称”的列表里。
		$body         		= $_POST['alibody'];			//订单描述、订单详细、订单备注，显示在支付宝收银台里的“商品描述”里
		$total_fee    		= $_POST['cz_money'];		//订单总金额，显示在支付宝收银台里的“应付总额”里
		if(!$total_fee || !is_numeric($total_fee)){
			$this->assign('jumpUrl','/Pay/alipay.html');
			$this->error(L('alipay_input_invalid'));
		}
		
		$real_fee = $total_fee - ($total_fee * 1.2)/100;
		writePayTrace($payer_id,$payer_un,$out_trade_no,$real_fee,'pendding','105-支付宝充值',$total_fee,'');
		$pay_mode = $_POST['pay_bank'];
		
		//构造要请求的参数数组，无需改动
		$parameter = array(
		"service"    =>"create_direct_pay_by_user",
		"payment_type"	=> "1",    	//交易类型，不需要修改

		//获取配置文件(alipay_config.php)中的值
		"partner"					=> C('alipay_partner'),
		"seller_email"			=> C('alipay_seller_email'),
		"return_url"				=> C('alipay_return_url'),
		"notify_url"				=> C('alipay_notify_url'),
		"_input_charset"		=> C('alipay_input_charset'),
		"show_url"				=> C('alipay_show_url'),

		//从订单数据中动态获取到的必填参数
		"out_trade_no"			=> $out_trade_no,
		"subject"					=> $subject,
		"body"						=> $body,
		"price"						=> $total_fee,
 		"quantity"					=> '1',
		//"total_fee"				=> $total_fee,
				
		"anti_phishing_key"	=> '',
		"exter_invoke_ip"		=> '',
		"buyer_email"		 	=> '',
		"extra_common_param"=> $payer_id.'|'.$payer_un //自定义参数，可存放任何内容（除=、&等特殊字符外），不会显示在页面上,
		);
		
		$submit_config = array(
			'partner' 			=> C('alipay_partner'),
			'key' 					=> C('alipay_key'),
			'sign_type' 		=> C('alipay_sign_type'),
			'input_charset' 	=> C('alipay_input_charset')
		);

		//构造请求函数
		$alipay = new AlipaySubmit($submit_config);
		$sHtmlText = $alipay->buildRequestForm($parameter, "post", "确认");
		header("Content-Type:text/html; charset=utf-8");
		echo getProcessingHtml($sHtmlText);
	}

	//------------------------------------------------------------------------------------------------
	//返回地址
	public function back(){		
		$alipay_config=array();
		$alipay_config['partner'] 	= C('alipay_partner');
		$alipay_config['key'] 		= C('alipay_key');
		$alipay_config['sign_type'] 	= C('alipay_sign_type');
		$alipay_config['input_charset'] 	= C('alipay_input_charset');
		
		$alipay = new AlipayNotify ( $alipay_config );
		$verify_result = $alipay->verifyReturn ();
		if ($verify_result) { //验证成功
			if (strtoupper($_GET ['trade_status']) == 'TRADE_FINISHED' || strtoupper($_GET ['trade_status']) == 'TRADE_SUCCESS' ) {
				$orderno			= $_GET['out_trade_no']; 	//获取订单号
				$trade_no			= $_GET['trade_no'];			//支付宝交易号
				$total_fee			= $_GET['total_fee']; 			//获取总价格
				$buyer_emaill	= $_GET['buyer_email'];

				//更新财务信息
				$total_fee = $total_fee - ($total_fee * 1.2)/100;
				$pay_trace_log = getPayTrace($orderno);
				if($pay_trace_log && ($pay_trace_log['result'] == 0) ){
					updatePayTrace($orderno,1,'completed');	//更新交易过程记录
					writeFinaceLog($pay_trace_log['user_id'],$pay_trace_log['user_name'],$orderno,$total_fee,105,'支付宝充值,支付宝交易号:'.$trade_no);
					updateFinance($pay_trace_log['user_id'],$total_fee);	//更新帐户余额
					writeReplenishingLog($pay_trace_log['user_id'],$pay_trace_log['user_name'],$total_fee,$buyer_emaill,0,'','105','支付宝充值,支付宝交易号:'.$trade_no);
				}

				$this->assign('jumpUrl','/My/index.html');
				$this->success(L('alipay_pay_succ'));
			} else {
				$trade_status = $_GET ['trade_status'];
				$this->assign('jumpUrl','/Pay/alipay.shtml');
				$this->error(L('alipay_pay_fail').$trade_status);
			}
		} else {
			echo "fail";
		}
	}

	//------------------------------------------------------------------------------------------------
	//处理支付宝通知消息
	public function notify(){
		$alipay_config=array();
		$alipay_config['partner'] 			= C('alipay_partner');
		$alipay_config['key'] 					= C('alipay_key');
		$alipay_config['sign_type'] 		= C('alipay_sign_type');
		$alipay_config['input_charset'] 	= C('alipay_input_charset');
		
		$alipay = new AlipayNotify ( $alipay_config );
		$verify_result = $alipay->verifyNotify (); //计算得出通知验证结果
		if ($verify_result) { //验证成功
			if (strtoupper($_POST ['trade_status']) == 'TRADE_FINISHED' || strtoupper($_POST ['trade_status']) == 'TRADE_SUCCESS' ) { //交易成功结束
				$orderno			= $_POST['out_trade_no']; 	//获取订单号,这里将用户编号作为订单号
				$trade_no			= $_POST['trade_no'];		//支付宝交易号
				$total_fee			= $_POST['total_fee']; 			//获取总价格
				$buyer_emaill	= $_POST['buyer_email'];

				//更新会员帐户余额
				$total_fee = $total_fee - ($total_fee * 1.2)/100;
				$pay_trace_log = getPayTrace($orderno);
				if($pay_trace_log && ($pay_trace_log['result'] == 0) ){
					updatePayTrace($orderno,1,'completed');	//更新交易过程记录
					writeFinaceLog($pay_trace_log['user_id'],$pay_trace_log['user_name'],$orderno,$total_fee,105,'支付宝充值,支付宝交易号:'.$trade_no);
					updateFinance($pay_trace_log['user_id'],$total_fee);	//更新帐户余额
					writeReplenishingLog($pay_trace_log['user_id'],$pay_trace_log['user_name'],$total_fee,$buyer_emaill,0,'','105','支付宝充值,支付宝交易号:'.$trade_no);
				}
			}
			echo "success";
		}else{			
			//Log::write('verified fail');
			echo "fail";
		}
	}
	
	//------------------------------------------------------------------------------------------------
	Public function _empty(){
		$this->redirect('Pay/alipay');
	}
}
?>