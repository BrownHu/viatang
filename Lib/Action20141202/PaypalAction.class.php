<?php
/**
 +------------------------------------------------------------------------------
 * DaigouCMS 代购建站系统(淘宝版)
 *
 * Paypal 充值 
 +------------------------------------------------------------------------------
 * @category   ulowi
 * @author    	soitun <stone@zline.net.cn>
 * @copyright 上海子凌网络科技有限公司
 * @license   	http://www.zline.net.cn/license-agreement.html
 * @link      		http://www.zline.net.cn/
 +------------------------------------------------------------------------------
 */

load ( '@/payutils' );
import ( '@.ORG.Paypal.Service' );
load ( '@/functions' );
class PaypalAction extends Action {
	
	//------------------------------------------------------------------------------------------------
	public function index() {
		$this->redirect ( 'Pay/paypal' );
	}
	
	//------------------------------------------------------------------------------------------------
	//提交paypal网关进行充值
	public function commit() {
		//必填参数
		$payer_id = $_POST ['payer_uid']; //用于对支付结果通知进行个人帐户入帐
		$payer_un = $_POST ['payer_un']; //用户名
		if (empty ( $payer_id ) || empty ( $payer_un )) {
			if ($this->user) {
				$payer_id = $this->user ['id'];
				$payer_un = $this->user ['login_name'];
			} else {
				$this->assign ( 'jumpUrl', '/Pay/paypal.shtml' );
				$this->error ( L('paypal_fail') );
			}
		}
		
		$total_fee = $_POST ['cz_money'];
		if (empty ( $total_fee ) || ! is_numeric ( $total_fee )) {
			$this->assign ( 'jumpUrl', '/Pay/paypal.shtml' );
			$this->error ( L('paypal_input_error') );
		}
		
		$invoice = $payer_id .'_'.time().rand ( 1000, 2000 ); //唯一订单号匹配
		$out_trade_no = base64_encode($payer_un);
		
		$money_real_receive = $this->compute_real_receive_money ( $total_fee ); //计算实际到帐金额
		writePayTrace ( $payer_id, $payer_un, $invoice, $money_real_receive, 'pendding', '103-paypal',$total_fee,'');
		
		$Paypal = new paypal_service ( C ( 'paypal_gate_way' ), C ( 'paypal_return_url' ), C ( 'paypal_notify_url' ), C ( 'paypal_business' ), C ( 'paypal_item_name' ), $invoice, $total_fee, $out_trade_no );
		$sHtmlText = $Paypal->build_form ();
		header ( "Content-Type:text/html; charset=utf-8" );
		echo getProcessingHtml ( $sHtmlText );
	}
	
	//------------------------------------------------------------------------------------------------
	//处理paypal支付完成后返回界面
	public function back() {
		$this->assign ( 'jumpUrl', '/My/index.shtml' );
		$this->assign ( 'waitSecond', 60 );
		$this->success ( L('paypal_succ') );
	}
	
	//------------------------------------------------------------------------------------------------
	//处理paypal支付完后服务器通知
	public function notify() {
		if (isset ( $_POST ['txn_id'] ) && isset ( $_POST ['invoice'] )) {
			$ulowi_ord_no = trim ( $_POST ['invoice'] ); 

			if (empty ( $ulowi_ord_no) ){
				Log::write ( '回传的充值跟踪号为空,EERROR_CODE:1001', Log::ERR );
				return;
			}

			//根据交易号检查是否需要, 这里改为ulowi的充值流水号,而不用paypal的交易号$txn_id
			$pay_trace_log = getPayTrace ( $ulowi_ord_no ); 
			if(empty($pay_trace_log)){
				Log::write ( '回传的充值跟踪号不存在对应的充值记录,EERROR_CODE:1002', Log::ERR );
				return;
			}
			
			//-----------------------------------------------------------------
			// 以下为付款信息
			$payer_id 		= $pay_trace_log ['user_id'];  		  //付款人id
			$payer_un 		= $pay_trace_log ['user_name']; 	  //付款人登录名
			$payment_status = trim ( $_POST ['payment_status'] ); //付款状态
			$payment_amount = floatval(trim ( $_POST ['mc_gross'] )); 	  //未扣除交易手续费时的金额
			$txn_id 		= trim ( $_POST ['txn_id'] ); 		  //交易号,这里是paypal的交易号
			$payer_email 	= trim ( $_POST ['payer_email'] ); 	  //付款人的paypal帐号
			$receiver_email = trim ( $_POST ['receiver_email'] );
			$trans_payer_un = trim ( $_POST['custom']);
			$verifiedResult = 0; //对充值结果进行验证
			
			//收款人和我们的收款人不一致时不作处理
			if (strtolower ( $receiver_email ) != strtolower ( C ( 'paypal_business' ) )) {
				Log::write ( '可疑的充值数据,EERROR_CODE:1003', Log::ERR );
				return;
			}
						
			if($payment_amount != $pay_trace_log['pay_total']){
				Log::write ( '跟踪金额与返回的金额不一致,EERROR_CODE:1004', Log::ERR );
				return;
			}
			
			Log::write($trans_payer_un,Log::EMERG);//记下传输的用户名，用于分析
			//构造验证参数
			$req = 'cmd=_notify-validate';
			foreach ( $_POST as $key => $value ) {
				$value = urlencode ( stripslashes ( $value ) );
				$req .= "&$key=$value";
			}
			
			$verifiedResult = intval($this->verify ( $req ));
			$payment_amount = abs ( $payment_amount ); //先进行正数处理
			$money_real_receive = $this->compute_real_receive_money ( $payment_amount );
			if (($verifiedResult == 1) && ($payment_amount > 0) && (strlen ( $ulowi_ord_no ) > 0)) { 
				$need_process_tag = false;
				
				$payment_status = strtolower ( $payment_status );
				switch ($payment_status) {
					case 'completed' :
						if ($pay_trace_log ['result'] == 0) { //存在充值跟踪记录，且没有被处理过，置为需要充值
							$need_process_tag = true;
						} else {
							$need_process_tag = false;
						}
						break;
					
					case 'canceled-reversal' :
						$need_process_tag = false;
						$money_real_receive = 0 - $money_real_receive;
						writeFinaceLog ( $payer_id, $payer_un, $txn_id, $money_real_receive, 106, 'paypal撤销付款,paypal交易号:' . $txn_id );
						updateFinance ( $payer_id, $money_real_receive );
						
						if ($pay_trace_log != false) {
							updatePayTrace ( $txn_id, 2, 'canceled-reversal' ); //这里标记为2,即不合计到冻结金额 中
						}
						break;
					
					default :
						$need_process_tag = false;
						updatePayTrace ( $txn_id, 0, 'false' ); //更新交易过程记录, 这里直接置为未完成标志
				} //switch
				

				if ($need_process_tag) {
					writeFinaceLog ( $payer_id, $payer_un, $ulowi_ord_no, $money_real_receive, 103, 'paypal充值,paypal交易号:' . $ulowi_ord_no ); //记财务变化记录
					updateFinance ( $payer_id, $money_real_receive ); //更新帐户余额
					updatePayTrace ( $ulowi_ord_no, 1, 'completed' ); //置为已处理
					writeReplenishingLog ( $payer_id, $payer_un, $money_real_receive, $ulowi_ord_no, 0, '', '103', '用户paypal充值,paypal交易号:' . $txn_id );
				}
			} //if
		}
	}
	
	//-------------------------------------------------------------------------------------------------
	// 以下为内部功能函数
	

	//------------------------------------------------------------------------------------------------
	//对接收到的IPN消息进行验证
	private function verify($req) {
		$result = 0;
		$header .= "POST /cgi-bin/webscr HTTP/1.0\r\n";
		$header .= "Content-Type:application/x-www-form-urlencoded\r\n";
		$header .= "Content-Length:" . strlen ( $req ) . "\r\n\r\n";
		
		$fp = fsockopen ( 'www.paypal.com', 80, $errno, $errstr, 30 );
		if (! $fp) { //HTTP 错误
			$result = 2; //
		} else {
			fputs ( $fp, $header . $req );
			while ( ! feof ( $fp ) ) {
				$res = fgets ( $fp, 1024 );
			}
			
			if (strcmp ( $res, "VERIFIED" ) == 0) {
				$result = 1; //验证通过
			} else if (strcmp ( $res, "INVALID" ) == 0) {
				$result = 0; //验证失败
			}
		}
		fclose ( $fp );
		return $result;
	}
	
	//------------------------------------------------------------------------------------------------
	Public function _empty() {
		$this->redirect ( 'Pay/paypal' );
	}
	
	//------------------------------------------------------------------------------------------------
	//计算实际到帐金额
	//$money_usd 为充值美元  (实际入帐 = ($money_usd -  $money_usd x 3.9% - 0.3) x 汇率
	private function compute_real_receive_money($money_usd) {
		$result = 0;
		$entity = M ( 'FinaceConfig' )->where ( "item='exchange_rate'" )->find ();
		if ($entity) {
			$exchange_rate = $entity ['value']; //汇率
			$charge_percent = floatval(C ( 'paypal_handling_charge_percent' )); //交易手续费
			$charge = floatval(C ( 'paypal_handling_charge' )); //每次固定手续费
			$result = ($money_usd - ($money_usd * $charge_percent) / 100 - $charge) * $exchange_rate;
		}
		return $result;
	}
	
}
?>