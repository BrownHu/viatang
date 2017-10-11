<?php
/**
 +------------------------------------------------------------------------------
 * DaigouCMS 代购建站系统(淘宝版)
 * 
 * 支付宝代充值
 +------------------------------------------------------------------------------
 * @category   ulowi
 * @author    	soitun <stone@zline.net.cn>
 * @copyright 上海子凌网络科技有限公司 
 * @license   	http://www.zline.net.cn/license-agreement.html 
 * @link      		http://www.zline.net.cn/
 +------------------------------------------------------------------------------
 */

class AlipayagentAction extends BaseAction {
	
	//------------------------------------------------------------------------------------------------
	function _initialize() {
		parent::_initialize();
		$this->error('已暂停代充业务');
		return;
	}
	
	//------------------------------------------------------------------------------------------------
	public function index() {
		if ($this->user) {			
			$this->display ( 'apply' );
		}
	}
	
	//------------------------------------------------------------------------------------------------
	//使用已有支付宝帐户
	public function apply() {
		if ($this->user) {
			$balance = $this->getBanlance ( $this->user ['id'] );
			$this->assign ( 'balance', $balance ); //余额			
		} 
		$this->display ();
	}
	
	//------------------------------------------------------------------------------------------------
	//使用悠乐提供的支付宝
	public function applyu() {
		if ($this->user) {
			//取当前ulowi帐户余额
			$balance = $this->getBanlance ( $this->user ['id'] );
			$this->assign ( 'balance', $balance );
			
			//取登录用户支付宝帐户
			$alipay = M ( 'AlipayAccount' )->where ( 'user_id=' . $this->user ['id'] )->find ();
			$this->assign ( 'alipay', $alipay );
			$this->display ();
		}
	}
	
	//------------------------------------------------------------------------------------------------
	//全部充值记录
	public function all() {
		if ($this->user) {
			$DataList = M ( 'AlipayAgent' )->where('user_id='.$this->user['id'])->order ( 'create_at asc' )->select ();
			$this->assign ( 'DataList', $DataList );
			$this->display ();
		}
	}
	
	//------------------------------------------------------------------------------------------------
	//款处理充值记录
	public function wcl() {
		if ($this->user) {
			$DataList = M ( 'AlipayAgent' )->where ( 'user_id='.$this->user['id'] . ' AND status=0' )->order ( 'create_at asc' )->select ();
			$this->assign ( 'DataList', $DataList );
			$this->display ();
		}
	}
	
	//------------------------------------------------------------------------------------------------
	//已充值金额
	public function ycz() {
		if ($this->user) {
			$DataList =  M ( 'AlipayAgent' )->where ('user_id='.$this->user['id'] . ' AND status=2' )->order ( 'create_at asc' )->select ();
			$this->assign ( 'DataList', $DataList );
			$this->display ();
		}
	}
	
	//------------------------------------------------------------------------------------------------
	//注册
	public function reg() {
		if ($this->user) {
			$account = M ( 'AlipayAccount' )->where ( "user_id=" . $this->user ['id'] )->find ();
			if ($account) {
				if (($account ['alipay_account'] == '') && (status == 0)) {
					$this->display ( 'reg_commit' );
				} else {
					$this->assign ( 'alipay', $account );
					$this->display ( 'reg_result' );
				}
			} else {
				$this->display ();
			}
		} 
	}
	
	//------------------------------------------------------------------------------------------------
	//提交注册
	public function doreg() {
		if ($this->user) {
			$account = M ( 'AlipayAccount' )->where ( "user_id=" . $this->user ['id'] )->find ();
			if ($account) {
				$this->display ( 'reg_commit' );
				return;
			}
			
			if (isset ( $_POST ['chk_taobao'] )) {
				$needTaobao = 1;
			} else {
				$needTaobao = 0;
			}
			
			$data ['user_id'] = $this->user ['id'];
			$data ['user_name'] = $this->user ['login_name'];
			$data ['need_taobao'] = $needTaobao;
			$data ['status'] = 0;
			$data ['create_at'] = time ();
			
			$id = M ( 'AlipayAccount' )->data ( $data )->add ();
			if ($id && ($id > 0)) {
				$this->display ( 'reg_commit' );
			} else {
				$this->error ( ' 您的操作失败，请稍后重试，如果一直有问题，请与客服联系！' );
			}
		} 
	}
	
	//------------------------------------------------------------------------------------------------
	//注册结果
	public function regresult() {
		if ($this->user) {
			$this->display ();
		}
	}
	
	//------------------------------------------------------------------------------------------------
	public function result() {
		if ($this->user) {
			$this->display ();
		} 
	}
	
	//------------------------------------------------------------------------------------------------
	//处理代充申请
	public function commit() {
		$code = trim ( $_POST ['verify'] );
		if (! $this->checkVerify ( $code )) {
			$this->error ( '验证码输入错误!' );
			return;
		}

		if ($this->user) {
			$alipayType = trim ( $_POST ['account_tyep'] ); //支付宝类别
			$alipayAccount = trim ( $_POST ['alipay'] );
			$realName = trim ( $_POST ['real_name'] );
			$money = trim ( $_POST ['money'] );
			
			if (empty ( $alipayType ) || empty ( $alipayAccount ) || empty ( $realName ) || empty ( $money ) || ! is_numeric ( $money )) {
				$this->error ( '输入的信息有误，请检查后重新提交' );
			} else {
				
				$alipayAccount = ($alipayType == 2) ? $this->getAlipayAccount ( $this->user ['id'] ) : $alipayAccount;
				
				$serviceRate = $this->getServiceRate (); //服务费比例
				$serviceFee = $money * $serviceRate; //服务费 		
				$costTotal = $money + $serviceFee; //本次总花费
				$balance = $this->getBanlance ( $this->user ['id'] ); //取得帐户当前余额 
				

				if ($balance >= $costTotal) {
					$data ['user_id'] = $this->user ['id'];
					$data ['user_name'] = $this->user ['login_name'];
					$data ['alipay_account'] = $alipayAccount;
					$data ['real_name'] = $realName;
					$data ['money'] = $money;
					$data ['service_fee'] = $serviceFee;
					$data ['cost_total'] = $costTotal;
					$data ['service_rate'] = $serviceRate;
					$data ['status'] = 0;
					$data ['type'] = $alipayType;
					$data ['create_at'] = time ();
					
					$id = M ( 'AlipayAgent' )->data ( $data )->add ();
					
					if ($id && ($id > 0)) {
						$remark = "代充:$money,服务费:$serviceFee,总计:$costTotal";
						$this->updateFinance ( $id, $this->user ['id'], $this->user ['login_name'], $costTotal,306, $remark );
						$this->success ( '已成功提交支付宝代充请求！' );
					} else {
						$this->error ( '操作失败，请与客服联系为您处理。' );
					}
				} else {
					$this->error ( '您的帐户余额不足，无法继续!' );
				}
			}
		}
	}
	
	//------------------------------------------------------------------------------------------------
	//已完成代充明细
	public function detail() {
		if ($this->user) {
			$id = trim ( $_GET ['id'] );
			if ($id && ($id > 0)) {
				$DataList = M ( 'AlipayAgent' )->where ( "id=$id AND user_id=" . $this->user ['id'] )->select ();
				$this->assign ( 'DataList', $DataList );
				$this->display ( 'all' );
			}
		}
	}
	
	//------------------------------------------------------------------------------------------------
	//撤销代付
	public function cancle() {
		$id = trim ( $_GET ['id'] );
		if ($this->user && $id) {
			$DAO = M ( 'AlipayAgent' );
			$entity = $DAO->where ( "id=$id AND status=0 AND user_id=" . $this->user ['id'] )->find ();
			if ($entity) {
				$entity ['status'] = 3; //置为已撤销
				$entity ['last_upate'] = time ();
				$money = 0 - $entity ['cost_total']; //取出代充金额
				$result = $DAO->where ( "id=$id AND user_id=" . $this->user ['id'] )->save ( $entity );
				if($result >0){
					$remark = '撤销代充支付宝申请';
					$this->updateFinance ( $id, $this->user ['id'], $this->user ['login_name'], $money,308, $remark );
				}
				$this->success ( '已撤销支付宝代充请求！' );
			}
		}
	}
	
	//------------------------------------------------------------------------------------------------
	public function _empty() {
		$this->redirect ( 'apply' );
	}
	
	//-----------------------------------------------------------------------------------------------------------
	//以下为内部功能函数
	//------------------------------------------------------------------------------------------------

	//------------------------------------------------------------------------------------------------
	//取得帐户余额
	private function getBanlance($uid) {
		$result = 0;
		if (! empty ( $uid )) {
			$finance =  D ( 'Finance' )->finace ( $uid );
			if ($finance) {
				$result = $finance ['money'];
			}
		}
		return $result;
	}
	
	//------------------------------------------------------------------------------------------------
	//取得服务费比例
	//代充值服务费比例为5%
	private function getServiceRate() {
		return 0.05;
	}
	
	//------------------------------------------------------------------------------------------------
	//更新帐户余额 
	private function updateFinance($oid, $uid, $un, $money,$type, $remark) {
		if (($uid > 0) && ($money)) {
			$FinaceDAO = D ( 'Finance' );
			$finance = $FinaceDAO->finace ( $uid );
			if ($finance && ($finance ['money'] >= $money)) {
				$money_before = $finance ['money'];
				$rebate_before = $finance ['rebate'];
				$point_before = $finance ['point'];
				$finance ['money'] = $finance ['money'] - $money;
				$FinaceDAO->where ( 'id=' . $finance ['id'] )->save ( $finance );
				
				//记财务变化日志
				$this->writeFinaceLog ( $uid, $un, $oid, $money, $money_before, $rebate_before, $point_before,$type, $remark );
			}
		}
	}
	
	//------------------------------------------------------------------------------------------------
	//记财务日志
	private function writeFinaceLog($uid, $un, $oid, $money, $mnybfr, $rbtbfr, $pntbfr,$type, $remark) {
		$data ['user_id'] = $uid;
		$data ['user_name'] = $un;
		$data ['type_id'] = $type; //见business.inc.php定义
		$data ['pay_id'] = 0;
		$data ['order_id'] = $oid;
		$data ['package_id'] = 0;
		$data ['product_id'] = 0;
		$data ['pointlog_id'] = 0;
		
		$data ['chagne_total'] = $money;
		$data ['money'] = $money;
		$data ['money_before'] = $mnybfr;
		$data ['money_after'] = $mnybfr - $money; //这里是消费，所以全记为减
		$data ['rebate'] = 0;
		$data ['rebate_before'] = $rbtbfr;
		$data ['rebate_after'] = $rbtbfr;
		$data ['point'] = 0;
		$data ['point_before'] = $pntbfr;
		$data ['point_after'] = $pntbfr;
		
		$data ['remark'] = $remark;
		$data ['create_time'] = time ();
		
		M ( 'FinanceLog' )->data ( $data )->add ();
	}
	
	//------------------------------------------------------------------------------------------------
	//取得代注册支付宝帐户
	private function getAlipayAccount($uid) {
		$result = '';
		if ($uid && ($uid > 0)) {
			$account = M ( 'AlipayAccount' )->where ( "user_id=$uid" )->find ();
			if ($account && ($account ['alipay_account'] != '')) {
				$result = $account ['alipay_account'];
			}
		}
		return $result;
	}
}
?>