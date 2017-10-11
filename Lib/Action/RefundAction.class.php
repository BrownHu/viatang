<?php
/**
 +------------------------------------------------------------------------------
 * DaigouCMS 代购建站系统(淘宝版)
 * 
 * 	退款处理
 +------------------------------------------------------------------------------ 
 * @copyright 上海子凌网络科技有限公司˾
 * @author    stone@ulowi.com
 * @version   1.0
 +------------------------------------------------------------------------------
 */


import ( 'ORG.Util.Page' );
class RefundAction extends BaseAction {
	
	//------------------------------------------------------------------------------------------------
	function _initialize() {
		parent::_initialize();
		$this->dao = M ( 'Refundment' );
	}
	
	//------------------------------------------------------------------------------------------------
	public function index() {
		$this->redirect ( 'My/refund' );
	}
	
	//------------------------------------------------------------------------------------------------
	public function commit() {
		       $this->assign ('title','账户余额提现操作成功-viatang.com');
	           $this->assign ('keywords','代购账户余额提现申请,会员账户余额,余额返现,账户余额,会员消费记录,paypal充值,消费余额,余额提现,中国代购,代购中国商品,淘宝代购,海外华人代购,美国代购,代购网站,加拿大代购,留学生代购,服装代购,图书代购');
               $this->assign ('description','代购会员账户余额提现,消费信息查询,paypal充值余额,购物车商品管理余额结算,提交购物车商品管理，支持paypal、国际信用卡支付方式.批量下单,多件商品集中寄送,专享超低国际运费3折起.');
		
		if ($this->user) {
			$refundMoney = trim($_POST ['refund_money'] );
			$balance = $this->getBalanceXianjin($this->user['id']);
			if( is_numeric($refundMoney) && (floatval($refundMoney) >  0) && ($balance >= $refundMoney) ){
				$data ['user_id'] 		= $this->user ['id'];
				$data ['user_name'] 	= $this->user ['login_name'];
				$data ['pay_time']		= trim ( $_POST ['pay_time'] ); //充值时间
				$data ['pay_account'] 	= trim ( $_POST ['refund_account'] ); //充值号
				$data ['money'] 		= $refundMoney;
				$data ['balance'] 		= $balance - $refundMoney;//更新余额 
				$data ['way'] 			= trim ( $_POST ['refund_way'] );
				$data ['create_time'] 	= time ();
			
				$id = $this->dao->data ( $data )->add ();
				if($id && ($id>0)){
					$money = 0 -$refundMoney;
					$this->doFinace($this->user['id'],$this->user['login_name'],$id,$money,501,L('refund_type'));
					$this->assign ( 'waitSecond', 5 );
					$this->assign ( 'jumpUrl', '/Refund/processing.shtml' );
					$this->success( L('refund_sumbit_succ') );
				}
			}else{
				$this->error(L('refund_input_error'));
			}
		}else{
			$this->redirect('Public/login');
		}
	}
	
	//------------------------------------------------------------------------------------------------
	public function all(){
		$this->assign ('title','账户提现记录-viatang.com');
	    $this->assign ('keywords','代购账户余额提现申请,会员账户余额,账户余额,余额返现,会员消费记录,paypal充值,消费余额,余额提现,中国代购,代购中国商品,淘宝代购,海外华人代购,美国代购,代购网站,加拿大代购,留学生代购,服装代购,图书代购');
        $this->assign ('description','唯唐代购会员账户余额提现,消费信息查询,paypal充值余额,购物车商品管理余额结算,提交购物车商品管理，支持paypal、国际信用卡支付方式.批量下单,多件商品集中寄送,专享超低国际运费3折起.');
		if ($this->user) {
			$this->_list("user_id=" . $this->user ['id'],'create_time asc');
		}
		$this->display ();
	}
	
	//------------------------------------------------------------------------------------------------
	//未处理退款申请
	public function lst() {
		$this->assign ('title','已处理退款详情-viatang.com');
	    $this->assign ('keywords','商品退款成功,代购商品结算余额,代购中国商品,淘宝代购,海外华人代购,美国代购,美国代购网,代购网站,加拿大代购,留学生代购,服装代购,图书代购');
        $this->assign ('description','代购会员账户余额提现,消费信息查询,paypal充值余额,购物车商品管理余额结算,提交购物车商品管理，支持paypal、国际信用卡支付方式.批量下单,多件商品集中寄送,专享超低国际运费3折起.');
		if ($this->user) {
			$this->_list( "status='1' AND user_id=" . $this->user ['id'],'create_time asc');
		}
		$this->display ();
	}
	
	//------------------------------------------------------------------------------------------------
	//处理中
	public function processing() {
		$this->assign ('title','处理中退款详情-viatang.com');
        $this->assign ('keywords','代购商品管理,退款处理中,申请退款,账户退款结算,中国代购,代购中国商品,淘宝代购,海外华人代购,美国代购,美国代购网,代购网站,加拿大代购,留学生代购,服装代购,图书代购');
        $this->assign ('description','代购会员账户余额提现,消费信息查询,paypal充值余额,购物车商品管理余额结算,提交购物车商品管理，支持paypal、国际信用卡支付方式.批量下单,多件商品集中寄送,专享超低国际运费3折起.');
		if ($this->user) {
			$this->_list( "status='0' AND user_id=" . $this->user ['id'] ,'create_time asc');
		}
		$this->display ();
	}
	
	//------------------------------------------------------------------------------------------------
	public function del() {
		$id = $_GET ['id'];
		if ($this->user && $id) {
			$refund = $this->dao->where("id=$id AND status='0' AND user_id=".$this->user['id'])->find(); 
			if($refund ){
				$refund1['status'] = '2';//置2来表示撤销，不作物理删除
				$refund1['last_update'] = time();
				
				$result = $this->dao->where ( "id=$id AND status='0' AND user_id=".$this->user['id'] )->save ($refund1);
				if($result){				
					$this->doFinace($this->user['id'],$this->user['login_name'],$id,$refund['money'],507,L('refund_cancel'));
				}else{
					$this->assign ( 'waitSecond', 5 );
					$this->assign ( 'jumpUrl', '/Refund/processing.shtml' );
					$this->error( L('refund_in_process') );
				}
			}
		}
		$this->redirect ( 'processing' );
	}
	
	//------------------------------------------------------------------------------------------------
	public function detail(){
		$id = trim($_GET['id']);
		if($this->user && $id && ($id >0)){
			$DataList = $this->dao->where("id=$id AND user_id=".$this->user['id'])->select();
			$this->assign('DataList',$DataList);
			$this->display('all');
		}else{
			$this->redirect('all');
		}
		
	}
	
	//------------------------------------------------------------------------------------------------
	Public function _empty() {
		$this->redirect ( 'My/refund' );
	}
	
	//------------------------------------------------------------------------------------------
	//记财务记录
	private function doFinace($uid,$un,$oid, $money,$type,$remark) {
		$result = false;
		if($uid && $oid && $money){
			$FinaceDAO = D ( 'Finance' );
			$finance = $FinaceDAO->finace ( $uid );
			if ($finance) {
				$money_before = $finance ['money'];
				$rebate_before = $finance ['rebate'];
				$point_before = $finance ['point'];
							
				$finance ['money'] = $finance ['money'] + $money;//重新计算余额
				$FinaceDAO->updateInfo ( $finance ); //更新财务数据
				$this->writeFinaceLog ( $uid, $un, $oid, $money, $money_before, $rebate_before, $point_before,$type, $remark ); //记财务变更记录
				$result = true;
			}
		}
		return $result;
	}
	
	//------------------------------------------------------------------------------------------------
	//取现金余额
	private function getBalanceXianjin($uid){
		$result = 0;
		if($uid && ($uid>0) ){
			$FinaceDAO = D ( 'Finance' );
			$finance = $FinaceDAO->finace ( $uid );
			if($finance ){
				$result = $finance['money']; 
			}
		}
		return $result;
	}
	
	//------------------------------------------------------------------------------------------------
	//记财务记录
	private function writeFinaceLog($uid, $unam, $oid, $money, $mnybfr, $rbtbfr, $pntbfr,$type, $remark) {
		$data ['user_id'] 		= $uid;
		$data ['user_name'] 	= $unam;
		$data ['type_id'] 		= $type; //商品退单，见business.inc.php定义
		$data ['pay_id'] 		= 0;
		$data ['order_id'] 		= $oid; //这里记 订单号
		$data ['package_id'] 	= 0;
		$data ['product_id'] 	= 0;
		$data ['pointlog_id'] 	= 0;
		
		$data ['chagne_total'] 	= $money;
		$data ['money'] 		= $money;
		$data ['money_before'] 	= $mnybfr;
		$data ['money_after'] 	= $mnybfr + $money; 
		$data ['rebate'] = 0;
		$data ['rebate_before'] = $rbtbfr;
		$data ['rebate_after'] 	= $rbtbfr;
		$data ['point'] 		= 0;
		$data ['point_before'] 	= $pntbfr;
		$data ['point_after'] 	= $pntbfr;
		
		$data ['remark'] 		= $remark;
		$data ['create_time'] 	= time ();
		
		M ( 'FinanceLog' )->data ( $data )->add ();
	}
}
?>