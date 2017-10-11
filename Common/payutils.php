<?php
/**
 +------------------------------------------------------------------------------
 * 悠乐代购系统(淘宝版)
 *
 * 充值功能函数
 +------------------------------------------------------------------------------
 * @category   ulowi
 * @author    	soitun <stone@zline.net.cn>
 * @copyright 上海子凌网络科技有限公司
 * @license   	http://www.zline.net.cn/license-agreement.html
 * @link      		http://www.zline.net.cn/
 +------------------------------------------------------------------------------
 */

//记充值过程跟踪日志
function writePayTrace($uid,$un,$trade_no,$money,$payment_status,$remark,$pay_total,$token=''){	
	$data['user_id']  		= $uid;
	$data['user_name'] 		= $un;
	$data['trade_no']    	= $trade_no;
	$data['money']	       	= $money;
	$data['pay_total']		= $pay_total;//充值总额
	$data['payment_status'] = $payment_status;
	$data['result']			= 0;
	$data['remark']			= $remark;
	$data['token']			= $token;
	$data['create_time']	= time();
	$data['last_update']	= 0;

	$DAO = M('PaymentTrace');
	$DAO->data($data)->add();
}

function getPayTrace($trade_no){
	$DAO = M('PaymentTrace');
	$data = $DAO->where("trade_no='$trade_no'")->find();
	return $data;
}

//更新充值结果
function updatePayTrace($trade_no,$result,$status){
	$DAO = M('PaymentTrace');
	$data['result'] = $result;
	$data['payment_status'] = $status;
	$data['last_update'] = time();
	$DAO->where("trade_no='$trade_no' AND result=0")->save($data);
}

//更新指定用户帐户余额
function updateFinance($uid,$money){
	if($uid && $money){
		$DAO = D('Finance');
		$finance = $DAO->finace($uid);
		if($finance){
			$finance['money'] = $finance['money'] + $money;
			$DAO->updateInfo($finance);
		}
	}
}

//记财务变更记录
function writeFinaceLog($uid,$un,$payid,$money,$typ,$remark){
	$result = false;
	$FinanceDAO  = D('Finance');
	$finance = $FinanceDAO->finace($uid);
	if($finance){
		$money_bfore   = $finance['money'];
		$rebate_before = $finance['rebate'];
		$point_febore 	  = $finance['point'];
	}
	$data['user_id']	= $uid;
	$data['user_name']	= $un;
	$data['type_id']	= $typ;	//商品退单，见business.inc.php定义
	$data['pay_id']		= $payid;
	$data['order_id']	= 0;
	$data['package_id']	= 0;
	$data['product_id']	= 0;
	$data['pointlog_id']	= 0;

	$data['chagne_total']	= $money;
	$data['money']		= $money;
	$data['money_before']	= $money_bfore;
	$data['money_after']	= $money_bfore + $money;//这里是退单，所以全记为加
	$data['rebate']		= 0;
	$data['rebate_before']	= $rebate_before;
	$data['rebate_after']	= $rebate_before ;
	$data['point']		= 0;
	$data['point_before']	= $point_febore;
	$data['point_after']	= $point_febore;

	$data['remark']	= $remark;
	$data['create_time']	= time();

	$DAO = M('FinanceLog');
	$result = $DAO->data($data)->add();
	return $result;
}

//记充值成功的，充值日志
function writeReplenishingLog($uid,$un,$money,$relAct,$amdid,$amdun,$way,$remark){
	$result = false;
	$data['user_id'] 	= $uid;
	$data['user_name'] 	= $un;
	$data['money'] 	= $money;
	$data['relay_account'] = $relAct;
	$data['admin_id'] 	= $amdid;
	$data['amdin_name'] 	= $amdun;
	$data['pay_way'] 	= $way;
	$data['remark'] 	= $remark;
	$data['create_time'] 	= time();

	$DAO = M('ReplenishingLog');
	$result = $DAO->data($data)->add();
	return $result;
}

//系统汇率
function getExchangeRate(){
	$result = 6.55;
	$DAO = M('FinaceConfig');
	$entity = $DAO->where("item='exchange_rate'")->find();
	if($entity){
		$result = $entity['value'];
	}
	return $result;
}

//显示正在请求的界面
function getProcessingHtml($html){
	$result = '<html xmlns="http://www.w3.org/1999/xhtml">
			    <head>
				<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
				<link href="/Ulowi/Tpl/default/Public/css/base-min.css" charset="utf-8" rel="stylesheet">
				<title>正在提交……</title>
				<style>
					html {height:100%;}
					body {height:100%;text-align:center;background:transparent;}
					.center_div {display:inline-block;zoom:1;*display:inline;vertical-align:middle;width:200px;padding:10px;}
					.hiddenDiv {height:100%;overflow:hidden;display:inline-block;width:1px;overflow:hidden;margin-left:-1px;zoom:1;*display:inline;*margin-top:-1px;_margin-top:0;vertical-align:middle;}
					.loading_logo {margin:18px 0 10px 0;}
					.loading_block{background:url(/Ulowi/Tpl/default/Public/images/load_bg.gif) left top no-repeat; width:506px; height:207px;}
					.loading_onclick {text-align:right;padding:38px 25px 0 0}
				</style>
			</ head>
			<body onLoad="document.getElementById('."'Submit1'".').click()">
				<div class="center_div loading_block">
  				<div class="loading_logo"><img src="/Ulowi/Tpl/default/Public/images/load_logo.gif" width="210" height="59"></div>
  				<div class="loading_bar"><img src="/Ulowi/Tpl/default/Public/images/going.gif" width="220" height="19">
    					<p>正在提交申请，请稍后……</p>
  				</div>
  				<div class="loading_onclick">国际间通信可能会有延迟,如果停留时间很长,请点<a href="#nogo" onclick="document.getElementById('."'Submit1'".').click();">重发请求</a></div>
				</div>
			<div class="hiddenDiv">'.$html.'</div></body></html>';
	return $result;
}


?>