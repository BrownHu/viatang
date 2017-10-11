<?php
/**
 +------------------------------------------------------------------------------
 * DaigouCMS 代购建站系统(淘宝版)
 *
 * 网银在线支付接口
 +------------------------------------------------------------------------------
 * @category   ulowi
 * @author    	soitun <stone@zline.net.cn>
 * @copyright 上海子凌网络科技有限公司
 * @license   	http://www.zline.net.cn/license-agreement.html
 * @link      		http://www.zline.net.cn/
 +------------------------------------------------------------------------------
 */

load('@/payutils');
import('@.ORG.Chinabank.Service');

class ChinabankAction extends Action {
	
	//------------------------------------------------------------------------------------------------
	public function index(){
		$this->redirect('Pay/creditcn');
	}

	//------------------------------------------------------------------------------------------------
	//向网银在线提交支付信息
	public function commit(){
		//必填参数
		$payer_id 	= $_POST['payer_uid'];	//用于对支付结果通知进行个人帐户入帐
		$payer_un	= $_POST['payer_un'];		//用户名
		if(empty($payer_id) || empty($payer_un)){
			$user = Session::get(C('MEMBER_INFO'));
			if($user){
				$payer_id 	=$user['id'];
				$payer_un	=$user['login_name'];
			}else{
				$this->assign('jumpUrl','/Pay/paypal.shtml');
				$this->error('充值失败! 请稍后重试,若仍有问题请即时联系我们的客服');
			}
		}

		$total_fee = $_POST['money'];
		if( empty($total_fee)){
			$this->assign('jumpUrl','/Pay/creditcn.shtml');
			$this->error('请输入正确的充值金额,重新提交！');
		}
		$invoice = "$payer_id|$payer_un|".time();
		$out_trade_no 	= $payer_id.date('_YmdHis').'_'.rand(1000,2000);		//唯一订单号匹配
		writePayTrace($payer_id,$payer_un,$out_trade_no,$total_fee,'pendding','102-网银在线充值');

		$Chinabank = new chinabank_service(C('chinabank_gate_way'),C('chinabank_v_mid'),C('chinabank_v_url'),C('chinabank_key'),$total_fee,$out_trade_no,C('chinabank_v_moneytype'),$invoice,'');
		$sHtmlText = $Chinabank->build_form();
		header("Content-Type:text/html; charset=utf-8");
		echo getProcessingHtml($sHtmlText);
	}

	//------------------------------------------------------------------------------------------------
	//同步对帐接口
	public function back(){
		$key 		= C('chinabank_key');
		$v_oid     	= trim($_POST['v_oid']);       		// 商户发送的v_oid定单编号
		$v_pmode   	= trim($_POST['v_pmode']);    	// 支付方式（字符串）
		$v_pstatus 	= trim($_POST['v_pstatus']);   		// 支付状态 ：20（支付成功）；30（支付失败）
		$v_pstring 	= trim($_POST['v_pstring']);   		// 支付结果信息 ： 支付完成（当v_pstatus=20时）；失败原因（当v_pstatus=30时,字符串）；
		$v_amount  	= trim($_POST['v_amount']);     	// 订单实际支付金额
		$v_moneytype 	= trim($_POST['v_moneytype']); 	// 订单实际支付币种
		$remark1   	= trim($_POST['remark1' ]);     	// 备注字段1
		$remark2   	= trim($_POST['remark2' ]);     	// 备注字段2
		$v_md5str  	= trim($_POST['v_md5str' ]);   	// 拼凑后的MD5校验值

		$md5string	= strtoupper(md5($v_oid.$v_pstatus.$v_amount.$v_moneytype.$key));
		if ($v_md5str==$md5string){
			if($v_pstatus=="20"){
				$UsserInfo = explode('|',$remark1);
				$pay_trace_log = getPayTrace($v_oid);
				if($pay_trace_log && ($pay_trace_log['result'] == 0) ){
					updatePayTrace($v_oid,1,'complete');	//更新交易过程记录
					$v_amount = $v_amount - $v_amount *0.01;//去掉1%的手续费
					writeFinaceLog($UsserInfo[0],$UsserInfo[1],$v_oid,$v_amount,102,'网银在线充值,交易号:'.$v_oid);
					updateFinance($UsserInfo[0],$v_amount);	//更新帐户余额
					writeReplenishingLog($UsserInfo[0],$UsserInfo[1],$v_amount,'',0,'','103','网银在线充值,交易号:'.$v_oid);
				}

				$this->assign('jumpUrl','/My/index.shtml');
				$this->success('充值成功，请检查您的账户余额!');
			}else{
				$this->assign('jumpUrl','Pay/creditcn.shtml');
				$this->error('充值失败，错误码为:'.$v_pstring);
			}
		}
	}

	//------------------------------------------------------------------------------------------------
	//接收自动对帐消息
	public function notify(){
		$key 			= C('chinabank_key');
		$v_oid     		= trim($_POST['v_oid']);
		$v_pmode   	= trim($_POST['v_pmode']);
		$v_pstatus 	= trim($_POST['v_pstatus']);
		$v_pstring 	= trim($_POST['v_pstring']);
		$v_amount  	= trim($_POST['v_amount']);
		$v_moneytype	= trim($_POST['v_moneytype']);
		$remark1   	= trim($_POST['remark1' ]);
		$remark2   	= trim($_POST['remark2' ]);
		$v_md5str  	= trim($_POST['v_md5str' ]);

		$md5string=strtoupper(md5($v_oid.$v_pstatus.$v_amount.$v_moneytype.$key)); //拼凑加密串
		if ($v_md5str==$md5string){
			if($v_pstatus=="20"){
				$UsserInfo = explode('|',$remark1);
				$pay_trace_log = getPayTrace($v_oid);
				if($pay_trace_log && ($pay_trace_log['result'] == 0) ){
					updatePayTrace($v_oid,1,'complete');	//更新交易过程记录
					$v_amount = $v_amount - $v_amount *0.01;//去掉手续费
					writeFinaceLog($UsserInfo[0],$UsserInfo[1],$v_oid,$v_amount,102,'网银在线充值,交易号:'.$v_oid);
					updateFinance($UsserInfo[0],$v_amount);	//更新帐户余额
					writeReplenishingLog($UsserInfo[0],$UsserInfo[1],$v_amount,'',0,'','103','网银在线充值,交易号:'.$v_oid);
				}
			}
			echo "ok";
		}else{
			echo "error";
		}
	}

	//------------------------------------------------------------------------------------------------
	Public function _empty(){
		$this->redirect('Pay/creditcn');
	}
}
?>