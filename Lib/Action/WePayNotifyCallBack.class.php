<?php
/**
 +------------------------------------------------------------------------------
 * DaigouCMS 代购建站系统(淘宝版)
 *
 * 微信扫码支付
 +------------------------------------------------------------------------------
 * @category   ulowi
 * @author     soitun <stone@zline.net.cn>
 * @copyright  上海子凌网络科技有限公司
 * @license    http://www.zline.net.cn/license-agreement.html
 * @link       http://www.zline.net.cn/
 +------------------------------------------------------------------------------
 */

vendor('wepay.WxPayApi');
vendor('wepay.WxPayNotify');
load('@/payutils');

class WePayNotifyCallBack extends WxPayNotify {
	
	// 查询订单
	public function Queryorder($transaction_id) {
		$input = new WxPayOrderQuery ();
		$input->SetTransaction_id ( $transaction_id );
		$result = WxPayApi::orderQuery ( $input );
		
		if (array_key_exists ( "return_code", $result ) && array_key_exists ( "result_code", $result ) && $result ["return_code"] == "SUCCESS" && $result ["result_code"] == "SUCCESS") {
			return true;
		}
		return false;
	}
	
	// 重写回调处理函数
	public function NotifyProcess($data, &$msg) {
		//$notfiyOutput = array ();
		
		if (! array_key_exists ( "transaction_id", $data )) {
			$msg = "输入参数不正确";
			return false;
		}
		// 查询订单，判断订单真实性
		if (! $this->Queryorder ( $data ["transaction_id"] )) {
			$msg = "订单查询失败";
			return false;
		}
		
		$this->doFinance($data);
		return true;
	}
	
	private function doFinance($data){
		$orderno			= $data['out_trade_no']; 	//获取订单号,这里将用户编号作为订单号
		$trade_no			= $data['transaction_id'];		//微信支付订单号
		$total_fee			= $data['total_fee']; 			//获取总价格
		$buyer_emaill		= $data['attach'];
		
		$total_fee = $total_fee / 100;
		$total_fee = round($total_fee,2);
		//更新会员帐户余额
		$total_fee = $total_fee - ($total_fee * C('WEPAY_FEE'))/100;
		$pay_trace_log = getPayTrace($orderno);
		if($pay_trace_log && ($pay_trace_log['result'] == 0) ){
			updatePayTrace($orderno,1,'completed');	//更新交易过程记录
			writeFinaceLog($pay_trace_log['user_id'],$pay_trace_log['user_name'],$orderno,$total_fee,109,'微信充值,微信订单号:'.$trade_no);
			updateFinance($pay_trace_log['user_id'],$total_fee);	//更新帐户余额
			writeReplenishingLog($pay_trace_log['user_id'],$pay_trace_log['user_name'],$total_fee,$buyer_emaill,0,'','109','微信充值,微信订单号:'.$trade_no);
		}
	}
}
?>